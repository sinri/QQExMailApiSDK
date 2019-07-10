<?php


namespace sinri\QQExMailApiSDK\core;


use sinri\ark\cache\ArkCache;
use sinri\ark\cache\implement\ArkDummyCache;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\curl\ArkCurl;

class ApiCore
{
    const API_ROOT = "https://api.exmail.qq.com/cgi-bin/";

    /**
     * @var string
     */
    protected $appCode;
    /**
     * @var string
     */
    protected $corpId;
    /**
     * @var string
     */
    protected $appSecret;
    /**
     * @var ArkLogger
     */
    protected $logger;

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

    /**
     * ApiCore constructor.
     * @param string $appCode
     * @param string $corpId
     * @param string $appSecret
     */
    public function __construct($appCode, $corpId, $appSecret)
    {
        $this->appCode = $appCode;
        $this->corpId = $corpId;
        $this->appSecret = $appSecret;
        $this->logger = ArkLogger::makeSilentLogger();
        $this->cacheInstance = new ArkDummyCache();
    }

    /**
     * @return string|bool
     */
    public function getAccessTokenForApp()
    {
        $cacheKey = "QQExMailApiSDK_" . $this->appCode . "_access_token";

        if (self::getArkCacheInstance()) {
            $accessToken = self::getArkCacheInstance()->getObject($cacheKey);
            if ($accessToken) {
                $this->logger->debug("getAccessTokenForApp: cache available, use cached access token!");
                return $accessToken;
            }
        }

        $url = self::API_ROOT . "gettoken?corpid=" . urlencode($this->corpId) . "&corpsecret=" . urlencode($this->appSecret);

        $applyTime = time();
        $curl = new ArkCurl();
        $curl->setLogger($this->logger);
        $response = $curl->prepareToRequestURL("GET", $url)->execute();

        $result = json_decode($response, true);
        $accessToken = ArkHelper::readTarget($result, ['access_token']);
        $expiresIn = ArkHelper::readTarget($result, ['expires_in'], 0);
        $expireTime = $applyTime + $expiresIn;

        if (!$accessToken || $expireTime < time()) {
            $this->logger->debug("getAccessTokenForApp: failed to get access token!");
            return false;
        }

        if (self::getArkCacheInstance()) {
            $saved = self::getArkCacheInstance()->saveObject($cacheKey, $accessToken, $expiresIn);
            $this->logger->debug("getAccessTokenForApp: cached access token!", ["saved" => $saved, 'raw' => [$this->appCode . ".access_token", $accessToken, $expiresIn]]);
        }

        return $accessToken;
    }

    /**
     * @param string $url without query part of access_token
     * @param array $params
     * @return ApiResponseEntity
     */
    public function postJsonToApi($url, $params = [])
    {
        $curl = (new ArkCurl());
        $curl->setLogger($this->logger);
        $response = $curl
            ->prepareToRequestURL('POST', self::API_ROOT . $url)
            ->setQueryField("access_token", $this->getAccessTokenForApp())
            ->setPostContent($params)
            ->execute(true);
        $result = new ApiResponseEntity($response);
        return $result;
    }

    /**
     * @param string $url without query part of access_token
     * @param array $queries
     * @return ApiResponseEntity
     */
    public function getFromApi($url, $queries = [])
    {
        $curl = (new ArkCurl())
            ->prepareToRequestURL('GET', self::API_ROOT . $url)
            ->setQueryField("access_token", $this->getAccessTokenForApp());
        $curl->setLogger($this->logger);
        foreach ($queries as $key => $value) {
            $curl->setQueryField($key, $value);
        }
        $response = $curl->execute();
        $result = new ApiResponseEntity($response);
        return $result;
    }

    /**
     * @var ArkCache
     */
    protected $cacheInstance;

    /**
     * @return ArkCache
     */
    public function getArkCacheInstance()
    {
        return $this->cacheInstance;
    }

    /**
     * @param ArkCache $cacheInstance
     */
    public function setArkCacheInstance($cacheInstance)
    {
        $this->cacheInstance = $cacheInstance;
    }
}