<?php


namespace sinri\QQExMailApiSDK\AppLog;


use Exception;
use sinri\ark\cache\ArkCache;
use sinri\ark\core\ArkLogger;
use sinri\QQExMailApiSDK\core\ApiCore;
use sinri\QQExMailApiSDK\QQExMailApiSDK;

class AppLogSDK
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
        $this->apiCore = new ApiCore(QQExMailApiSDK::APP_CODE_LOG, $corpId, $appSecret);
        $this->apiCore->setArkCacheInstance($cache);
        $this->apiCore->setLogger($logger);
    }

    /**
     * @param $domain
     * @param $begin_date
     * @param $end_date
     * @return array
     * @throws Exception
     */
    public function getMailsSummary($domain, $begin_date, $end_date)
    {
        $url = 'log/mailstatus';
        $params = [
            'domain' => $domain,
            'begin_date' => $begin_date,
            'end_date' => $end_date,
        ];
        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();
        $sendsum = $response->getProperty('sendsum', 0);
        $recvsum = $response->getProperty('recvsum', 0);
        return ["sent" => $sendsum, "received" => $recvsum];
    }

    /*
     * 0:收信+发信 1:发信 2:收信
     */
    const MAIL_TYPE_RECEIVED_SENT = 0;
    const MAIL_TYPE_SENT = 1;
    const MAIL_TYPE_RECEIVED = 2;

    /*
     * 0: 其他状态
        1: 发信中
        2: 被退信
        3: 发信成功
        4: 发信失败
        11: 收信被拦截
        12: 收信，邮件进入垃圾箱
        13: 收信成功，邮件在收件箱
        14: 收信成功，邮件在个人文件夹
     */
    const MAIL_STATUS_UNKNOWN = 0;
    const MAIL_STATUS_SENDING = 1;
    const MAIL_STATUS_REJECTED = 2;
    const MAIL_STATUS_SENT = 3;
    const MAIL_STATUS_FAILED = 4;
    const MAIL_STATUS_INTERCEPTED = 11;
    const MAIL_STATUS_RECEIVED_TO_TRASH = 12;
    const MAIL_STATUS_RECEIVED_TO_INBOX = 13;
    const MAIL_STATUS_RECEIVED_TO_FOLDER = 14;

    /**
     * @param string $begin_date y-m-d
     * @param string $end_date y-m-d
     * @param int $mail_type MAIL_TYPE_*
     * @param string $user_id
     * @param string $subject
     * @return array
     * @throws Exception
     */
    public function getConfirmMailsStatus($begin_date, $end_date, $mail_type, $user_id = null, $subject = null)
    {
        $url = 'log/mail';
        $params = [
            "begin_date" => $begin_date,//y-m-d
            "end_date" => $end_date,//y-m-d
            "mailtype" => $mail_type,// int
            //	"userid":"zhangsanp@gzdev.com",
            //	"subject":"test"
        ];
        if ($user_id !== null) {
            $params['userid'] = $user_id;
        }
        if ($subject !== null) {
            $params['subject'] = $subject;
        }

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();

        $list = $response->getProperty('list', []);
        return $list;
    }

    /*
     * 登录类型
        1：网页登录
        2：手机登录
        3：QQ邮箱App登录
        4：客户端登录:包括imap,pop,exchange
        5：其他登录方式
     */
    const LOGIN_TYPE_WEB = 1;
    const LOGIN_TYPE_MOBILE = 2;
    const LOGIN_TYPE_APP = 3;
    const LOGIN_TYPE_CLIENT = 4;
    const LOGIN_TYPE_ELSE = 5;

    /**
     * @param $begin_date
     * @param $end_date
     * @param null $user_id
     * @param null $subject
     * @return mixed|null
     * @throws Exception
     */
    public function getMemberLoginStat($begin_date, $end_date, $user_id = null, $subject = null)
    {
        $url = "log/login";
        $params = [
            "begin_date" => $begin_date,//y-m-d
            "end_date" => $end_date,//y-m-d
            //	"userid":"zhangsanp@gzdev.com",
            //	"subject":"test"
        ];
        if ($user_id !== null) {
            $params['userid'] = $user_id;
        }
        if ($subject !== null) {
            $params['subject'] = $subject;
        }

        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();

        $list = $response->getProperty('list', []);
        return $list;
    }

    /*
     * 操作类型
        1：群发邮件
        2：批量导入成员
        3：删除公告
        4：批量添加别名
        5：发布公告
        6：RTX帐号关联
        7：设置企业签名档
        8：取消企业签名档
        9：开通成员
        0：其他
     */
    const JOB_TYPE_BATCH_MAIL = 1;
    const JOB_TYPE_BATCH_IMPORT_MEMBER = 2;
    const JOB_TYPE_BATCH_DELETE_NEWS = 3;
    const JOB_TYPE_BATCH_ADD_NICKNAME = 4;
    const JOB_TYPE_BATCH_ADD_NEWS = 5;
    const JOB_TYPE_BATCH_BIND_RTX_ACCOUNT = 6;
    const JOB_TYPE_BATCH_CONFIG_ORG_SIGN = 7;
    const JOB_TYPE_BATCH_CANCEL_ORG_SIGN = 8;
    const JOB_TYPE_BATCH_ADD_MEMBER = 9;
    const JOB_TYPE_BATCH_ELSE = 0;

    /**
     * @param $begin_date
     * @param $end_date
     * @return mixed|null
     * @throws Exception
     */
    public function getBatchJobStatus($begin_date, $end_date)
    {
        $url = "log/batchjob";
        $params = [
            "begin_date" => $begin_date,//y-m-d
            "end_date" => $end_date,//y-m-d
        ];
        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();

        $list = $response->getProperty('list', []);
        return $list;
    }

    /*
     * 登录类型
        1：登录
        2：修改密码
        3：添加域名
        4：注销域名
        5：设置LOGO
        6：删除LOGO
        7：修改密保邮箱
        8：修改管理员邮箱
        9：发表公告
        10：群发邮件
        11：新增黑名单
        12：删除黑名单
        13：清空黑名单
        14：新增白名单
        15：删除白名单
        16：清空白名单
        17：新增域白名单
        18：删除域白名单
        19：新增用户
        20：删除用户
        21：启用用户
        22：禁用用户
        23：编辑用户
        24：编辑别名
        25：批量导入用户
        26：添加分级管理员
        27：删除分级管理员
        28：新增部门
        29：删除部门
        30：编辑部门
        31：移动部门
        32：新增邮件组
        33：删除邮件组
        34：编辑邮件组
        35：设置邮件备份
        36：邮件转移
        37：IP登录权限
        38：限制成员外发
        39：开启接口
        40：重新获取KEY
        41：停用接口
     */
    const OPERATION_TYPE_LOGIN = 1;//登录
    const OPERATION_TYPE_CHANGE_PASSWORD = 2;//修改密码
    const OPERATION_TYPE_ADD_DOMAIN = 3;//添加域名
    const OPERATION_TYPE_REVOKE_DOMAIN = 4;//注销域名
    const OPERATION_TYPE_SET_LOGO = 5;//设置LOGO
    const OPERATION_TYPE_DELETE_LOGO = 6;//删除LOGO
    const OPERATION_TYPE_CHANGE_SECURITY_MAIL = 7;//修改密保邮箱
    const OPERATION_TYPE_CHANGE_ADMIN_MAIL = 8;//修改管理员邮箱
    const OPERATION_TYPE_ADD_NEWS = 9;//发表公告
    const OPERATION_TYPE_BATCH_MAIL = 10;//群发邮件
    const OPERATION_TYPE_ADD_BLACK_LIST_ITEM = 11;//新增黑名单
    const OPERATION_TYPE_DELETE_BLACK_LIST_ITEM = 12;//删除黑名单
    const OPERATION_TYPE_CLEAR_BLACK_LIST = 13;//清空黑名单
    const OPERATION_TYPE_ADD_WHITE_LIST_ITEM = 14;//新增白名单
    const OPERATION_TYPE_DELETE_WHITE_LIST_ITEM = 15;//删除白名单
    const OPERATION_TYPE_CLEAR_WHITE_LIST = 16;//清空白名单
    const OPERATION_TYPE_ADD_DOMAIN_WHITE_LIST_ITEM = 17;//新增域白名单
    const OPERATION_TYPE_DELETE_DOMAIN_WHITE_LIST_ITEM = 18;//删除域白名单
    const OPERATION_TYPE_ADD_USER = 19;//新增用户
    const OPERATION_TYPE_DELETE_USER = 20;//删除用户
    const OPERATION_TYPE_ENABLE_USER = 21;//启用用户
    const OPERATION_TYPE_DISABLE_USER = 22;//禁用用户
    const OPERATION_TYPE_EDIT_USER = 23;//编辑用户
    const OPERATION_TYPE_EDIT_USER_NICKNAME = 24;//编辑别名
    const OPERATION_TYPE_BATCH_IMPORT_USER = 25;//批量导入用户
    const OPERATION_TYPE_ADD_LEVEL_ADMIN = 26;//添加分级管理员
    const OPERATION_TYPE_DELETE_LEVEL_ADMIN = 27;//删除分级管理员
    const OPERATION_TYPE_ADD_DEPARTMENT = 28;//新增部门
    const OPERATION_TYPE_DELETE_DEPARTMENT = 29;//删除部门
    const OPERATION_TYPE_EDIT_DEPARTMENT = 30;//编辑部门
    const OPERATION_TYPE_MOVE_DEPARTMENT = 31;//移动部门
    const OPERATION_TYPE_ADD_GROUP = 32;//新增邮件组
    const OPERATION_TYPE_DELETE_GROUP = 33;//删除邮件组
    const OPERATION_TYPE_EDIT_GROUP = 34;//编辑邮件组
    const OPERATION_TYPE_SET_BACKUP = 35;//设置邮件备份
    const OPERATION_TYPE_TRANSFER_MAIL = 36;//邮件转移
    const OPERATION_TYPE_LIMIT_LOGIN_IP = 37;//IP登录权限
    const OPERATION_TYPE_LIMIT_SEND_OUTSIDE = 38;//限制成员外发
    const OPERATION_TYPE_ENABLE_API = 39;//开启接口
    const OPERATION_TYPE_RENEW_KEY = 40;//重新获取KEY
    const OPERATION_TYPE_DISABLE_API = 41;//停用接口

    /**
     * @param $begin_date
     * @param $end_date
     * @param $type
     * @return array
     * @throws Exception
     */
    public function getOperationRecords($begin_date, $end_date, $type)
    {
        $url = "log/operation";
        $params = [
            "type" => $type,
            "begin_date" => $begin_date,//y-m-d
            "end_date" => $end_date,//y-m-d
        ];
        $response = $this->apiCore->postJsonToApi($url, $params);
        $response->throwExceptionErrorOccurs();

        $list = $response->getProperty('list', []);
        return $list;
    }
}