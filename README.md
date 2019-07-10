# QQExMailApiSDK

腾讯企业邮箱接口套件

[腾讯企业邮箱开发者中心开发文档](https://exmail.qq.com/qy_mng_logic/doc#10001) 

![GitHub](https://img.shields.io/github/license/sinri/QQExMailApiSDK.svg)

本软件运行库以MIT证书形式开源发布。可以利用Composer获取在Packagist上发布的公开源码包。

![Packagist Version](https://img.shields.io/packagist/v/sinri/qq-exmail-api-sdk.svg)

`composer require sinri/qq-exmail-api-sdk`

建议使用 PHP 7 及以上版本；目前目测也支持5.6之类的古典环境。 

## 实装说明

* Access Token 虽然看起来每次去调用新生成也够，但是总觉得不靠谱。所以就用ArkCache的实例提供标准缓存支持了。
* 如果需要日志服务记录每次API交互，用ArkLogger。
* 需要提供一个CorpId和各应用的Secret，但目前只实装了日志应用和通讯录应用的相关API。

具体用法参见测试实例。