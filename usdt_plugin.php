<?php

class usdt_plugin
{

    public static $info = [
        'name'     => 'usdt',
        'showname' => 'USDT 收款插件',
        'author'   => '莫名',
        'link'     => 'https://github.com/v03413/epay_usdt',
        'types'    => ['usdt'],
        'inputs'   => [
            'appid'  => [
                'name' => 'USDT-TRC20 收款地址',
                'type' => 'input',
                'note' => '确保地址正确，收款错误无法追回',
            ],
            'appkey' => [
                'name' => '交易汇率（CNY）',
                'type' => 'input',
                'note' => '如果填AUTO则实时获取市场汇率，推荐填AUTO；举例：6.3',
            ],
            'appurl' => [
                'name' => '超时时长（秒）',
                'type' => 'input',
                'note' => '建议20分钟；填：1200',
            ],
            'appsecret' => [
                'name' => 'TRONSCAN API Key（可选）',
                'type' => 'input',
                'note' => '主网建议填写，用于避免接口限流',
            ],
        ],
        'select'   => null,
        'note'     => '',
    ];

    public static function submit()
    {
        global $channel, $order, $conf, $DB, $cdnpublic;

        $valid   = (strtotime($order['addtime']) + intval($channel['appurl'])) * 1000;
        $address = $channel['appid'];
        $rate    = self::getRate();
        if ($rate <= 0) {
            return ['type' => 'error', 'msg' => '汇率接口暂时不可用，请稍后重试'];
        }
        $usdt    = round($order['realmoney'] / $rate, 2);
        $expire  = date('Y-m-d H:i:s', strtotime($order['addtime']) - intval($channel['appurl']));
        $params = [$channel['id'], 0, $expire, $order['trade_no'], $order['money']];
        $row    = $DB->getRow('select * from pre_order where channel = ? and status = ? and addtime >= ? and trade_no != ? and money = ? order by CAST(param AS DECIMAL(20, 6)) desc limit 1', $params);
        if ($row) {
            $usdt = bcadd($row['param'], 0.01, 2);
        }

        $DB->exec('update pre_order set param = ? where trade_no = ?', [$usdt, $order['trade_no']]);
        $order['param'] = $usdt;

        if (defined('PAY_PLUGIN')) {
            self::render();
            exit(0);
        }

        return ['type' => 'jump', 'url' => '/pay/pay/' . TRADE_NO . '/'];
    }

    public static function pay()
    {
        self::render();
        exit(0);
    }

    private static function render()
    {
        global $channel, $order, $cdnpublic;

        $valid   = (strtotime($order['addtime']) + intval($channel['appurl'])) * 1000;
        $address = $channel['appid'];
        $usdt    = $order['param'];

        ob_clean();
        header('Content-Type: text/html; charset=UTF-8');
        if (!defined('PLUGIN_STATIC')) define('PLUGIN_STATIC', 'https://epay-usdt.pages.dev');
        require __DIR__ . '/pay.php';
    }

    public static function getRate(): float
    {
        global $channel;

        if (isset($channel['appkey']) && $channel['appkey'] > 0) {

            return floatval($channel['appkey']);
        }

        $api    = 'https://api.coinmarketcap.com/data-api/v3/cryptocurrency/detail/chart?id=825&range=1H&convertId=2787';
        $data   = self::getJson($api);
        $points = $data['data']['points'] ?? [];
        if (!is_array($points) || !$points) return 0;
        $point  = array_pop($points);

        return floatval($point['c'][0] ?? 0);
    }

    public static function cron(array $channel)
    {
        global $DB;

        $list    = self::getTransferInList($channel['appid'], 24, $channel['appsecret'] ?? '');
        $addtime = date('Y-m-d H:i:s', time() - intval($channel['appurl']));
        $rows    = $DB->query('select * from pre_order where channel = ? and status = ? and addtime >= ?', [$channel['id'], 0, $addtime]);
        while ($order = $rows->fetch(PDO::FETCH_ASSOC)) {
            foreach ($list as $item) {
                if ($item['money'] == $order['param'] && $item['time'] >= strtotime($order['addtime']) && !self::isTradeUsed($item['trade_id'])) {

                    processNotify($order, $item['trade_id'], $item['buyer']);
                    echo sprintf("订单回调成功：%s\n", $order['trade_no']);
                    break;
                }
            }
        }

        echo "---[监控执行结束： " . date('Y-m-d H:i:s') . "]---\n";
    }

    public static function getTransferInList(string $address, int $hour = 3, string $apiKey = ''): array
    {
        $result = [];
        $end   = time() * 1000;
        $start = strtotime("-$hour hour") * 1000;
        $offset = 0;

        do {
            $params = [
                'limit'           => 50,
                'start'           => $offset,
                'direction'       => 2,
                'address'         => $address,
                'trc20Id'         => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
                'start_timestamp' => $start,
                'end_timestamp'   => $end,
                'reverse'         => 'true',
            ];
            $url = 'https://apilist.tronscanapi.com/api/token_trc20/transfers-with-status?' . http_build_query($params);
            $headers = $apiKey ? ['TRON-PRO-API-KEY: ' . $apiKey] : [];
            $data = self::getJson($url, $headers);
            $transfers = $data['data'] ?? [];

            foreach ($transfers as $transfer) {
                if (($transfer['to'] ?? '') === $address && ($transfer['final_result'] ?? '') === 'SUCCESS' && !empty($transfer['confirmed'])) {
                    $decimals = max(0, intval($transfer['decimals'] ?? 6));
                    $result[] = [
                        'time'     => intval($transfer['block_timestamp'] ?? 0) / 1000,
                        'money'    => floatval($transfer['amount'] ?? 0) / (10 ** $decimals),
                        'trade_id' => $transfer['hash'] ?? '',
                        'buyer'    => $transfer['from'] ?? '',
                    ];
                }
            }
            $count = count($transfers);
            $offset += $count;
        } while ($count === 50 && $offset < 10000);

        return array_values(array_filter($result, static function ($item) {
            return $item['trade_id'] !== '' && $item['money'] > 0;
        }));
    }

    private static function isTradeUsed(string $tradeId): bool
    {
        global $DB;

        if ($tradeId === '') return true;
        return (bool) $DB->getRow('select trade_no from pre_order where status > ? and api_trade_no = ? limit 1', [0, $tradeId]);
    }

    private static function getJson(string $url, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'epay-usdt/1.1',
            CURLOPT_HTTPHEADER     => $headers,
        ]);
        $body   = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) return [];
        $data = json_decode($body, true);
        return is_array($data) ? $data : [];
    }
}
