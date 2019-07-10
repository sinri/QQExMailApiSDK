<?php

use Psr\Log\LogLevel;
use sinri\ark\cache\implement\ArkFileCache;
use sinri\ark\core\ArkLogger;
use sinri\QQExMailApiSDK\QQExMailApiSDK;

require_once __DIR__ . '/../vendor/autoload.php';

$secret = [
    "corp_id" => '',
    'app_membership' => '',
    'app_log' => '',
    'app_settings' => '',
    'app_sso' => '',
    'app_notify' => '',
];
// override it
require_once __DIR__ . '/../debug/secret.php';

$logger = new ArkLogger(__DIR__ . '/../debug/log', 'AppLog');
$logger->setIgnoreLevel(LogLevel::DEBUG);

$cache = new ArkFileCache(__DIR__ . '/../debug/cache', 0777);

$sdk = new QQExMailApiSDK($secret);
$sdk->setCache($cache);
$sdk->setLogger($logger);

$begin_date = '2019-07-05';
$end_date = '2019-07-06';

try {
    $logger->info("--- Mails Summary ---");
    $result = $sdk->appLog()->getMailsSummary("leqee.com", $begin_date, $end_date);
    $logger->info("Mails Summary Result", $result);
} catch (Exception $e) {
    $logger->error("Exception: " . $e->getMessage(), ['code' => $e->getCode()]);
}
