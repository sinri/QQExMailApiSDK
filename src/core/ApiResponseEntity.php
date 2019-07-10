<?php


namespace sinri\QQExMailApiSDK\core;


use Exception;
use sinri\ark\core\ArkHelper;

class ApiResponseEntity
{
    public $errCode;
    public $errMsg;

    protected $rawProperties;

    /**
     * ApiResponseEntity constructor.
     * @param string $response
     */
    public function __construct($response)
    {
        $this->rawProperties = json_decode($response, true);
        $this->errCode = ArkHelper::readTarget($this->rawProperties, ['errcode'], -999);
        $this->errMsg = ArkHelper::readTarget($this->rawProperties, ['errmsg'], 'No Error Message Field');
    }

    /**
     * @throws Exception
     */
    public function throwExceptionErrorOccurs()
    {
        if (!empty($this->errCode)) {
            throw new Exception($this->errMsg, $this->errCode);
        }
    }

    /**
     * @param string|array $property
     * @param mixed $default
     * @return mixed|null
     */
    public function getProperty($property, $default = null)
    {
        return ArkHelper::readTarget($this->rawProperties, $property, $default);
    }
}