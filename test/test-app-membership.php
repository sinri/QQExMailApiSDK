<?php

use Psr\Log\LogLevel;
use sinri\ark\cache\implement\ArkFileCache;
use sinri\ark\core\ArkLogger;
use sinri\QQExMailApiSDK\AppMembership\AppMembershipSDK;
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

try {
    $logger->info("fetching departments...");
    $departments = $sdk->appMembership()->getDepartmentList(1);
    $logger->info("departments", $departments);
} catch (Exception $exception) {
    $logger->error("Cannot fetch departments. " . $exception->getMessage(), ["code" => $exception->getCode()]);
}

try {
    $logger->info("checking accounts' availability...");
    $list = $sdk->appMembership()->batchCheckAccounts(['a@v.com', 'b@v.com', 'c@v.com']);
    $logger->info("the checked result", ['accounts' => $list]);
} catch (Exception $exception) {
    $logger->error("Cannot check accounts. " . $exception->getMessage(), ["code" => $exception->getCode()]);
}

die();

try {
    $logger->info("create a temp email account...");
    $done = $sdk->appMembership()->createUser(
        "sinri-api-sdk-tester@v.com",
        "Tester For Api SDK",
        [1],
        [
            AppMembershipSDK::USER_ATTRIBUTE_PASSWORD => "A1s2d3f43e",
        ]
    );
    $logger->info("created temp email", ["done" => $done]);
} catch (Exception $exception) {
    $logger->error("Cannot create email. " . $exception->getMessage(), ["code" => $exception->getCode()]);
}

try {
    $logger->info("delete a temp email account...");
    $done = $sdk->appMembership()->deleteUser("sinri-api-sdk-tester@leqee.com");
    $logger->info("deleted temp email", ["done" => $done]);
} catch (Exception $exception) {
    $logger->error("Cannot create email. " . $exception->getMessage(), ["code" => $exception->getCode()]);
}