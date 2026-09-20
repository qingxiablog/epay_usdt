<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <meta name="HandheldFriendly" content="True"/>
    <meta name="MobileOptimized" content="320"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="default"/>
    <link rel="shortcut icon" href="<?= PLUGIN_STATIC ?>/img/tether.svg"/>
    <title>USDT 在线收银台</title>
    <style>
        :root {
            --apple-bg: #F5F5F7;
            --apple-card-bg: rgba(255, 255, 255, 0.82);
            --apple-label: #1D1D1F;
            --apple-secondary: #86868B;
            --apple-border: rgba(0, 0, 0, 0.08);
            --apple-green: #34C759;
            --apple-blue: #0071E3;
            --apple-radius: 20px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "PingFang SC", "Helvetica Neue", Helvetica, Arial, sans-serif;
            background-color: var(--apple-bg);
            color: var(--apple-label);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            width: 100%;
            max-width: 440px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* 顶部品牌与商户 */
        .header {
            text-align: center;
            margin-bottom: 24px;
            width: 100%;
        }

        .header .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            background: #FFFFFF;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.04);
            margin-bottom: 12px;
        }

        .header .logo {
            width: 32px;
            height: 32px;
        }

        .header h1 {
            font-size: 19px;
            font-weight: 600;
            letter-spacing: -0.015em;
            color: var(--apple-label);
            margin-bottom: 8px;
        }

        .header label {
            display: block;
            font-size: 13px;
            line-height: 1.5;
            color: var(--apple-secondary);
            font-weight: 400;
            padding: 0 12px;
        }

        .header label b {
            display: block;
            margin-top: 4px;
            font-weight: 500;
            color: #E03E3E;
        }

        /* 玻璃拟态卡片主体 */
        .content {
            width: 100%;
            background: var(--apple-card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--apple-radius);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--apple-border);
            padding: 32px 24px 28px;
            text-align: center;
        }

        /* 金额展示 */
        .section .title {
            margin-bottom: 20px;
        }

        .amount {
            display: inline-flex;
            align-items: baseline;
            justify-content: center;
            cursor: pointer;
            user-select: none;
            transition: opacity 0.15s ease;
            font-size: 38px;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--apple-label);
        }

        .amount:active {
            opacity: 0.6;
        }

        .amount span {
            font-size: 14px;
            font-weight: 600;
            margin-left: 6px;
            color: var(--apple-green);
            background: rgba(52, 199, 89, 0.12);
            padding: 2px 8px;
            border-radius: 6px;
            letter-spacing: normal;
        }

        /* 二维码区域 */
        .main {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .qr-image {
            background: #FFFFFF;
            padding: 14px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.04);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .qr-image canvas, .qr-image img {
            display: block;
            border-radius: 4px;
        }

        /* 地址展示 */
        .address {
            margin: 0 auto 24px;
            padding: 12px 14px;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 12px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 13px;
            color: var(--apple-label);
            word-break: break-all;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            border: 1px solid transparent;
        }

        .address:active {
            background: rgba(0, 0, 0, 0.06);
            border-color: rgba(0, 0, 0, 0.08);
            transform: scale(0.99);
        }

        /* 倒计时 */
        .timer {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 18px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }

        .downcount {
            list-style: none;
            display: inline-flex;
            align-items: center;
            font-variant-numeric: tabular-nums;
        }

        .downcount li {
            display: inline-flex;
            align-items: baseline;
        }

        .downcount li span {
            font-size: 16px;
            font-weight: 600;
            color: var(--apple-label);
        }

        .downcount li p {
            font-size: 11px;
            color: var(--apple-secondary);
            margin-left: 2px;
            margin-right: 4px;
            font-weight: 400;
        }

        .downcount .seperator {
            font-size: 14px;
            font-weight: 600;
            color: var(--apple-secondary);
            margin: 0 4px;
            opacity: 0.5;
        }

        /* 页脚 */
        .footer {
            margin-top: 24px;
            text-align: center;
        }

        .footer p {
            font-size: 12px;
            color: var(--apple-secondary);
        }

        .footer a {
            color: var(--apple-secondary);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .footer a:hover {
            color: var(--apple-label);
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="icon">
            <img class="logo" src="<?= PLUGIN_STATIC ?>/img/tether.svg" alt="logo">
        </div>
        <h1>
            <?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <label>
            请扫描二维码或点击金额与地址复制转账 USDT (TRC-20)。
            <b>转账金额需与下方保持一致，并在倒计时内完成。</b>
        </label>
    </div>
    <div class="content">
        <div class="section">
            <div class="title">
                <h1 class="amount parse-amount" data-clipboard-text="<?= htmlspecialchars((string) $usdt, ENT_QUOTES, 'UTF-8'); ?>" id="usdt">
                    <?= htmlspecialchars((string) $usdt, ENT_QUOTES, 'UTF-8'); ?> <span>USDT.TRC20</span>
                </h1>
            </div>
            <div class="main">
                <div class="qr-image" id="qrcode"></div>
            </div>
            <div class="address parse-action" data-clipboard-text="<?= htmlspecialchars((string) $address, ENT_QUOTES, 'UTF-8'); ?>" id="address">
                <?= htmlspecialchars((string) $address, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="timer">
                <ul class="downcount">
                    <li>
                        <span class="hours">00</span>
                        <p class="hours_ref">时</p>
                    </li>
                    <li class="seperator">:</li>
                    <li>
                        <span class="minutes">00</span>
                        <p class="minutes_ref">分</p>
                    </li>
                    <li class="seperator">:</li>
                    <li>
                        <span class="seconds">00</span>
                        <p class="seconds_ref">秒</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer">
        <p>Powered by <a href="https://XIUXUI.XYZ" target="_blank">XIUXI.XYZ</a></p>
    </div>
</div>

<script src="<?= PLUGIN_STATIC ?>/js/jquery.min.js"></script>
<script src="<?= PLUGIN_STATIC ?>/js/clipboard.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.1.1/layer.js"></script>
<script src="<?php echo $cdnpublic ?>jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script>
    // 检查是否支付完成
    function loadmsg() {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "/getshop.php",
            timeout: 10000, //ajax请求超时时间10s
            data: {trade_no: <?= json_encode((string) $order['trade_no']); ?>},
            success: function (data, textStatus) {
                //从服务器得到数据，显示数据并继续查询
                if (data.code == 1) {
                    layer.msg('支付成功，正在跳转中...', {icon: 16, shade: 0.1, time: 15000});
                    setTimeout(function () { window.location.href = data.backurl; }, 1000);
                } else {
                    setTimeout(loadmsg, 2000);
                }
            },
            //Ajax请求超时，继续查询
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus == "timeout") {
                    setTimeout(loadmsg, 1000);
                } else { //异常
                    setTimeout(loadmsg, 3000);
                }
            }
        });
    }

    function checkresult() {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "/getshop.php",
            timeout: 10000, //ajax请求超时时间10s
            data: {trade_no: <?= json_encode((string) $order['trade_no']); ?>},
            success: function (data, textStatus) {
                //从服务器得到数据，显示数据并继续查询
                if (data.code == 1) {
                    layer.msg('支付成功，正在跳转中...', {icon: 16, shade: 0.1, time: 15000});
                    setTimeout(function () { window.location.href = data.backurl; }, 1000);
                } else {
                    layer.msg('您还未完成付款，请继续付款', {shade: 0, time: 1500});
                }
            }
        });
    }

    $(function () {
        $('#qrcode').qrcode({
            text: <?= json_encode((string) $address); ?>,
            width: 200,
            height: 200,
            foreground: "#1D1D1F",
            background: "#ffffff",
            typeNumber: -1
        });

        (new Clipboard('#usdt')).on('success', function (e) {
            layer.msg('金额复制成功');
        });
        (new Clipboard('#address')).on('success', function (e) {
            layer.msg('地址复制成功');
        });

        // 支付时间倒计时
        function clock() {
            let timeout = new Date(<?=$valid; ?>);
            let now = new Date();
            let ms = timeout.getTime() - now.getTime();//时间差的毫秒数
            let second = Math.round(ms / 1000);
            let minute = Math.floor(second / 60);
            let hour = Math.floor(minute / 60);
            if (ms <= 0) {
                layer.alert("支付超时，请重新发起支付！", {icon: 5});
                return;
            }

            $('.hours').text(hour.toString().padStart(2, '0'));
            $('.minutes').text(minute.toString().padStart(2, '0'));
            $('.seconds').text((second % 60).toString().padStart(2, '0'));

            return setTimeout(clock, 1000);
        }

        setTimeout(clock, 1000);
        setTimeout(loadmsg, 2000);
    });
</script>
</body>
</html>
