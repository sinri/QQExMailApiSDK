<?php


namespace sinri\QQExMailApiSDK\AppMembership;


use Exception;
use sinri\ark\cache\ArkCache;
use sinri\ark\core\ArkLogger;
use sinri\QQExMailApiSDK\core\ApiCore;
use sinri\QQExMailApiSDK\core\ApiResponseEntity;
use sinri\QQExMailApiSDK\QQExMailApiSDK;

class AppMembershipSDK
{
    protected $apiCore;

    /**
     * AppLogSDK constructor.
     * @param string $corpId
     * @param string $appSecret
     * @param ArkCache $cache
     * @param ArkLogger $logger
     */
    public function __construct($corpId, $appSecret, $cache, $logger)
    {
        $this->apiCore = new ApiCore(QQExMailApiSDK::APP_CODE_MEMBERSHIP, $corpId, $appSecret);
        $this->apiCore->setArkCacheInstance($cache);
        $this->apiCore->setLogger($logger);
    }

    const USER_ATTRIBUTE_EMAIL = "userid"; // email
    const USER_ATTRIBUTE_NAME = "name";
    const USER_ATTRIBUTE_DEPARTMENTS = "department"; // int[]
    const USER_ATTRIBUTE_POSITION = "position";
    const USER_ATTRIBUTE_MOBILE = "mobile";
    const USER_ATTRIBUTE_TEL = "tel";
    const USER_ATTRIBUTE_EXT_ID = "extid"; // int
    const USER_ATTRIBUTE_GENDER = "gender"; // 1 male 2 female
    const USER_ATTRIBUTE_SLAVES = "slaves"; // string[] of emails
    const USER_ATTRIBUTE_PASSWORD = "password";
    const USER_ATTRIBUTE_NEXT_LOGIN_NEED_CHANGE_PASSWORD = "cpwd_login"; // default 0 NO 1 YES

    /**
     * @param $userId
     * @param $name
     * @param $departmentIdList
     * @param array $otherAttributes
     * @return bool
     * @throws Exception
     */
    public function createUser($userId, $name, $departmentIdList, $otherAttributes = [])
    {
        $url = "user/create";
        $params = [
            self::USER_ATTRIBUTE_EMAIL => $userId,
            self::USER_ATTRIBUTE_NAME => $name,
            self::USER_ATTRIBUTE_DEPARTMENTS => $departmentIdList,
        ];
        foreach ($otherAttributes as $key => $value) {
            $params[$key] = $value;
        }

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param $userId
     * @param array $otherAttributes
     * @return bool
     * @throws Exception
     */
    public function updateUser($userId, $otherAttributes = [])
    {
        $url = "user/update";
        $params = [
            self::USER_ATTRIBUTE_EMAIL => $userId,
        ];
        foreach ($otherAttributes as $key => $value) {
            $params[$key] = $value;
        }

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param $userId
     * @return bool
     * @throws Exception
     */
    public function deleteUser($userId)
    {
        $url = "user/delete";
        $params = [
            self::USER_ATTRIBUTE_EMAIL => $userId,
        ];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param $userId
     * @return ApiResponseEntity
     * @throws Exception
     */
    public function getUser($userId)
    {
        $url = "user/get";
        $params = [
            self::USER_ATTRIBUTE_EMAIL => $userId,
        ];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();

        return $response;
    }

    /**
     * @param $departmentId
     * @param int $fetch_child // 1 or 0 from sub departments?
     * @return ApiResponseEntity
     * @throws Exception
     */
    public function getUserListInDepartmentLite($departmentId, $fetch_child = 0)
    {
        $url = "user/simplelist";
        $params = [
            "department_id" => $departmentId,
            "fetch_child" => $fetch_child,
        ];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();

        return $response->getProperty(['userlist'], []);
    }

    /**
     * @param $departmentId
     * @param int $fetch_child // 1 or 0 from sub departments?
     * @return ApiResponseEntity
     * @throws Exception
     */
    public function getUserListInDepartment($departmentId, $fetch_child = 0)
    {
        $url = "user/list";
        $params = [
            "department_id" => $departmentId,
            "fetch_child" => $fetch_child,
        ];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();

        return $response->getProperty(['userlist'], []);
    }

    /*
     * 帐号类型。-1:帐号号无效; 0:帐号名未被占用; 1:主帐号; 2:别名帐号; 3:邮件群组帐号
     */

    const ACCOUNT_CHECK_RESULT_INVALID = -1;
    const ACCOUNT_CHECK_RESULT_AVAILABLE = 0;
    const ACCOUNT_CHECK_RESULT_MAIN = 1;
    const ACCOUNT_CHECK_RESULT_SLAVE = 2;
    const ACCOUNT_CHECK_RESULT_GROUP = 3;


    /**
     * @param string[] $userList
     * @return array
     * @throws Exception
     */
    public function batchCheckAccounts($userList)
    {
        $url = "user/batchcheck";
        $params = ["userlist" => $userList];
        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return $response->getProperty('list');
    }

    /**
     * @param string $name strlen no more than 64 and not including `\:*?"<>｜`
     * @param int $parentId
     * @param int $order
     * @return int
     * @throws Exception
     */
    public function createDepartment($name, $parentId = 1, $order = 0)
    {
        $url = "department/create";
        $params = [
            "name" => $name,
            "parentid" => $parentId,
            "order" => $order,
        ];
        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return $response->getProperty('id');
    }

    /**
     * @param $id
     * @param string $name strlen no more than 64 and not including `\:*?"<>｜`
     * @param int $parentId
     * @param int $order
     * @return int
     * @throws Exception
     */
    public function updateDepartment($id, $name = null, $parentId = null, $order = null)
    {
        $url = "department/update";
        $params = [
            "id" => $id,
        ];
        if ($name !== null) $params['name'] = $name;
        if ($parentId !== null) $params['parentid'] = $parentId;
        if ($order !== null) $params['order'] = $order;

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function deleteDepartment($id)
    {
        $url = "department/delete";
        $params = [
            "id" => $id,
        ];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param int $id would include nested
     * @return array
     * @throws Exception
     */
    public function getDepartmentList($id)
    {
        $url = "department/list";
        $params = [
            "id" => $id,
        ];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return $response->getProperty("department");
    }

    /**
     * @param string $name
     * @param int $fuzzy 0 or 1
     * @return array
     * @throws Exception
     */
    public function searchDepartmentList($name, $fuzzy = 0)
    {
        $url = "department/search";
        $params = [
            "name" => $name,
            "fuzzy" => $fuzzy,
        ];

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return $response->getProperty("department");
    }

    /*
     * 群发权限。0: 企业成员, 1任何人， 2:组内成员，3:指定成员
     */
    const ALLOW_GROUP_FOR_ORG = 0;
    const ALLOW_GROUP_FOR_ALL = 1;
    const ALLOW_GROUP_FOR_GROUP = 2;
    const ALLOW_GROUP_FOR_LISTED = 3;

    /**
     * @param $groupEmail
     * @param $groupName
     * @param array $userList
     * @param array $groupList
     * @param array $departmentList
     * @param int $allow_type
     * @param null $allowUserList if allow type is 3
     * @return bool
     * @throws Exception
     */
    public function createGroup($groupEmail, $groupName, $userList = [], $groupList = [], $departmentList = [], $allow_type = 1, $allowUserList = null)
    {
        $url = "group/create";
        $params = [
            "groupid" => $groupEmail,
            "groupname" => $groupName,
        ];
        if ($userList !== null) $params['userlist'] = $userList;
        if ($groupList !== null) $params['grouplist'] = $groupList;
        if ($departmentList !== null) $params['department'] = $departmentList;
        if ($allow_type !== null) $params['allow_type'] = $allow_type;
        if ($allowUserList != null) $params['allow_userlist'] = $allowUserList;

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param $groupEmail
     * @param null $groupName
     * @param null $userList
     * @param null $groupList
     * @param null $departmentList
     * @param null $allow_type
     * @param null $allowUserList
     * @return bool
     * @throws Exception
     */
    public function updateGroup($groupEmail, $groupName = null, $userList = null, $groupList = null, $departmentList = null, $allow_type = null, $allowUserList = null)
    {
        $url = "group/update";
        $params = [
            "groupid" => $groupEmail,
        ];

        if ($groupName !== null) $params['groupname'] = $groupName;
        if ($userList !== null) $params['userlist'] = $userList;
        if ($groupList !== null) $params['grouplist'] = $groupList;
        if ($departmentList !== null) $params['department'] = $departmentList;
        if ($allow_type !== null) $params['allow_type'] = $allow_type;
        if ($allowUserList != null) $params['allow_userlist'] = $allowUserList;

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param $groupEmail
     * @return bool
     * @throws Exception
     */
    public function deleteGroup($groupEmail)
    {
        $url = "group/delete";
        $params = ["groupid" => $groupEmail];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return true;
    }

    /**
     * @param $groupEmail
     * @return ApiResponseEntity
     * @throws Exception
     */
    public function getGroup($groupEmail)
    {
        $url = "group/get";
        $params = ["groupid" => $groupEmail];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();
        return $response;
    }
}