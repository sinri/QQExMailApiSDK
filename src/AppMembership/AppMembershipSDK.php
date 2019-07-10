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
    /**
     * @var ApiCore
     */
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
     * 创建成员
     *
     * @param string $userId 邮箱
     * @param string $name 成员名
     * @param int[] $departmentIdList 部门ID数组
     * @param array $otherAttributes 其他可选属性
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
     * 更新成员
     *
     * @param string $userId 邮箱
     * @param array $otherAttributes 要变更的属性
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
     * 删除成员
     *
     * @param string $userId 邮箱
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
     * 获取成员
     *
     * @param string $userId 邮箱
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
     * 获取部门成员
     *
     * @param int $departmentId 部门ID，为1时可获取根部门下的成员
     * @param bool $fetch_child // 1 or 0 是否递归获取子部门下面的成员
     * @return ApiResponseEntity
     * @throws Exception
     */
    public function getUserListInDepartmentLite($departmentId, $fetch_child = false)
    {
        $url = "user/simplelist";
        $params = [
            "department_id" => $departmentId,
            "fetch_child" => ($fetch_child ? 1 : 0),
        ];

        $response = $this->apiCore->getFromApi($url, $params);
        $response->throwExceptionErrorOccurs();

        return $response->getProperty(['userlist'], []);
    }

    /**
     * 获取部门成员（详情）
     *
     * @param int $departmentId 部门ID，为1时可获取根部门下的成员
     * @param int $fetch_child // 1 or 0 是否递归获取子部门下面的成员
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
     * 批量检查帐号
     *
     * @param string[] $userList 邮箱数组，不超过20个
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
     * 创建部门
     *
     * @param string $name 部门名，长度限制为1~64个字节，字符不能包括\:*?"<>｜
     * @param int $parentId 部门ID，为1可表示根部门
     * @param int $order 在父部门中的次序值。order值小的排序靠前，1-10000为保留值，若使用保留值，将被强制重置为0。
     * @return int 创建的部门id。 id为64位整型数
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
     * 更新部门
     * @param int $id 部门ID
     * @param string $name 更新的部门名称。长度限制为1~64个字节，字符不能包括\:*?"<>｜。修改部门名称时指定该参数
     * @param int $parentId 父部门id。id为1可表示根部门
     * @param int $order 在父部门中的次序值。 order值小的排序靠前，1-10000为保留值，若使用保留值，将被强制重置为0。
     * @return bool
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
     * 删除部门
     *
     * （注：不能删除根部门；不能删除含有子部门、成员的部门）
     *
     * @param int $id 部门ID
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
     * 获取部门列表
     *
     * @param int $id 部门id。获取指定部门及其下的子部门。id为1时可获取根部门下的子部门。
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
     * 查找部门
     *
     * @param string $name 部门名字
     * @param int $fuzzy 0 or 1 是否模糊搜索
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
     * 创建邮件群组
     *
     * @param string $groupEmail 邮箱组
     * @param string $groupName 组名
     * @param string[] $userList 成员帐号的数组
     * @param string[] $groupList 成员邮件群组的数组
     * @param int[] $departmentList 成员部门的数组
     * @param int $allow_type 群发权限。0: 企业成员, 1任何人， 2:组内成员，3:指定成员
     * @param string[] $allowUserList 群发权限为指定成员(3)时，需要指定成员，为成员邮箱数组
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
     * 更新邮件群组
     *
     * @param string $groupEmail 邮件组
     * @param string $groupName 邮件组命名
     * @param string[] $userList 成员帐号
     * @param string[] $groupList 成员邮件群组
     * @param int[] $departmentList 成员部门
     * @param int $allow_type 群发权限。0: 企业成员,1任何人，2:组内成员，3:指定成员
     * @param string[] $allowUserList 群发权限为指定成员(3)时，需要指定成员
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
     * 删除邮件群组
     *
     * @param string $groupEmail 邮件组
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
     * 获取邮件群组信息
     *
     * @param string $groupEmail 邮件组
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