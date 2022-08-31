<?php


namespace sinri\QQExMailApiSDK\AppSettings;


use sinri\ark\cache\ArkCache;
use sinri\ark\core\ArkLogger;
use sinri\QQExMailApiSDK\core\ApiCore;
use sinri\QQExMailApiSDK\core\QQExMailApiError;
use sinri\QQExMailApiSDK\QQExMailApiSDK;

class AppSettingsSDK
{
    /**
     * @var ApiCore
     */
    protected $apiCore;

    /**
     * AppSettingSDK constructor.
     * @param string $corpId
     * @param string $appSecret
     * @param ArkCache $cache
     * @param ArkLogger $logger
     */
    public function __construct($corpId, $appSecret, $cache, $logger)
    {
        $this->apiCore = new ApiCore(QQExMailApiSDK::APP_CODE_SETTINGS, $corpId, $appSecret);
        $this->apiCore->setArkCacheInstance($cache);
        $this->apiCore->setLogger($logger);
    }

    /*
     * 功能设置属性。1:强制启用安全登录，2:IMAP/SMTP服务，3:POP/SMTP服务，4:是否启用安全登录，不可用
     */
    const OPTION_TYPE_FORCE_ENABLE_SECURE_LOGIN = 1;
    const OPTION_TYPE_IMAP_SERVICE = 2;
    const OPTION_TYPE_POP_SERVICE = 3;
    const OPTION_TYPE_ENABLE_SECURE_LOGIN = 4;


    /**
     * 获取功能属性
     *
     * @param string $userId 邮箱
     * @param array $type 功能设置属性类型 [1,2,3]
     * @return array
     * @throws QQExMailApiError
     */
    public function getUserOption($userId, $type)
    {
        $url = "useroption/get";
        $params = [
            "userid" => $userId,
            "type" => $type
        ];

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return $response->getProperty("option");
    }

    /**
     * 更改功能属性
     *
     * @param string $userId 邮箱
     * @param array $option 功能设置属性 [["type"=>1,"value"=>"0"],["type"=>2,"value"=>"1"],["type"=>3,"value"=>"0"]]
     * @return bool
     * @throws QQExMailApiError
     */
    public function updateUserOption($userId, $option)
    {
        $url = "useroption/update";
        $params = [
            'userid' => $userId,
            'option' => $option
        ];

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }
}