<?php

use Psr\Log\LogLevel;
use sinri\ark\cache\implement\ArkFileCache;
use sinri\ark\core\ArkLogger;
use sinri\QQExMailApiSDK\AppSettings\AppSettingsSDK;
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

$userId = "sdwang@leqee.com";

try {
    $logger->info("Set email option settings...");
    $done = $sdk->appSettings()->updateUserOption(
        $userId,
        [
            ['type' => AppSettingsSDK::OPTION_TYPE_FORCE_ENABLE_SECURE_LOGIN, 'value' => '0'],
            ['type' => AppSettingsSDK::OPTION_TYPE_IMAP_SERVICE, 'value' => '1']
        ]
    );
    $logger->info("Set email option settings", ["done" => $done]);
} catch (Exception $exception) {
    $logger->error("Failed Set email option settings. " . $exception->getMessage(), ["code" => $exception->getCode()]);
}

try {
    $logger->info(" Get email option settings...");
    $settings = $sdk->appSettings()->getUserOption(
        $userId,
        [AppSettingsSDK::OPTION_TYPE_FORCE_ENABLE_SECURE_LOGIN, AppSettingsSDK::OPTION_TYPE_IMAP_SERVICE]
    );
    $logger->info("Email[{$userId}] option settings", $settings);
} catch (Exception $exception) {
    $logger->error("Cannot get email[{$userId}] option settings. " . $exception->getMessage(), ["code" => $exception->getCode()]);
}