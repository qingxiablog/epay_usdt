<?php
if (PHP_SAPI !== 'cli' || empty($argv[1]) || !ctype_digit((string) $argv[1])) {
    exit("用法：php cron.php <通道ID>\n");
}

$lock = fopen(__DIR__ . '/cron.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    exit("已有监控任务正在执行\n");
}

define('CURR_PATH', dirname(__DIR__));
require CURR_PATH . '/../includes/common.php';
require CURR_PATH . '/usdt/usdt_plugin.php';

if (function_exists("set_time_limit")) {
    @set_time_limit(0);
}

$id      = intval($argv[1]);
$channel = class_exists('lib\\Channel')
    ? \lib\Channel::get($id)
    : $DB->getRow('select * from pre_channel where id = ? limit 1', [$id]);
if (!$channel) {

    exit("错误：没找到该USDT支付通道\n");
}
if ($channel['plugin'] != 'usdt') {

    exit("错误：该支付通道不是USDT插件\n");
}

usdt_plugin::cron($channel);

flock($lock, LOCK_UN);
fclose($lock);
