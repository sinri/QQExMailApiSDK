<?php


namespace sinri\QQExMailApiSDK;


use sinri\ark\cache\ArkCache;
use sinri\ark\cache\implement\ArkDummyCache;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\QQExMailApiSDK\AppLog\AppLogSDK;
use sinri\QQExMailApiSDK\AppMembership\AppMembershipSDK;

class QQExMailApiSDK
{
    const CORP_ID = "corp_id";
    const APP_CODE_MEMBERSHIP = "app_membership";
    const APP_CODE_LOG = "app_log";
    const APP_CODE_SETTINGS = "app_settings";
    const APP_CODE_SSO = "app_sso";
    const APP_CODE_NOTIFY = "app_notify";
    /**
     * @var ArkCache
     */
    protected $cache;
    protected $config = [];
    /**
     * @var ArkLogger
     */
    protected $logger;
    /**
     * @var AppLogSDK
     */
    protected $appLogSDK;
    /**
     * @var AppMembershipSDK
     */
    protected $appMembershipSDK;

    public function __construct($config)
    {
        $this->config = $config;
        $this->cache = new ArkDummyCache();
    }

    /**
     * @return ArkCache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param ArkCache $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return AppLogSDK
     */
    public function appLog()
    {
        if (!$this->appLogSDK) {
            $this->appLogSDK = new AppLogSDK(
                ArkHelper::readTarget($this->config, [self::CORP_ID]),
                ArkHelper::readTarget($this->config, [self::APP_CODE_LOG]),
                $this->cache,
                $this->logger
            );
        }
        return $this->appLogSDK;
    }

    /**
     * @return AppMembershipSDK
     */
    public function appMembership()
    {
        if (!$this->appMembershipSDK) {
            $this->appMembershipSDK = new AppMembershipSDK(
                ArkHelper::readTarget($this->config, [self::CORP_ID]),
                ArkHelper::readTarget($this->config, [self::APP_CODE_MEMBERSHIP]),
                $this->cache,
                $this->logger
            );
        }
        return $this->appMembershipSDK;
    }

    /**
     * @return ArkLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}