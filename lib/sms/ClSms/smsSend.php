<?php
/* *
 * 功能：创蓝发送信息DEMO
 * 版本：1.3
 * 日期：2014-07-16
 */
require_once 'ChuanglanSmsApi.php';

$config = [
    'apiSendUrl' => 'http://222.73.117.156/msg/HttpBatchSendSM',//创蓝发送短信接口URL
    'apiBalanceQueryUrl' => 'http://222.73.117.156/msg/QueryBalance',//创蓝短信余额查询接口URL
    'apiAccount' => '',//创蓝账号
    'apiPassword' => '',
];

$clapi = new ChuanglanSmsApi;
$clapi->account = $config['apiAccount'];
$clapi->password = $config['apiPassword'];
$clapi->sendUrl = $config['apiSendUrl'];

$result = $clapi->sendSMS('18888888888', '您好，您的验证码是888888', 'true');
$result = $clapi->execResult($result);
if ($result[1]==0) {
	echo '发送成功';
} else {
	echo "发送失败{$result[1]}";
}