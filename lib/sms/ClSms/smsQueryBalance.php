<?php
/* *
 * 功能：创蓝查询余额DEMO
 * 版本：1.3
 * 日期：2014-07-16
 */
require_once 'ChuanglanSmsApi.php';

$config = [
    'apiSendUrl' => 'http://222.73.117.158/msg/HttpBatchSendSM',//创蓝发送短信接口URL
    'apiBalanceQueryUrl' => 'http://222.73.117.158/msg/QueryBalance',//创蓝短信余额查询接口URL
    'apiAccount' => '',//创蓝账号
    'apiPassword' => '',
];

$clapi = new ChuanglanSmsApi;
$clapi->account = $config['apiAccount'];
$clapi->password = $config['apiPassword'];
$clapi->queryUrl = $config['apiBalanceQueryUrl'];

$result = $clapi->queryBalance();
$result = $clapi->execResult($result);
switch ($result[1]) {
	case 0:
		echo "剩余{$result[3]}条";
		break;
	case 101:
		echo '无此用户';
		break;
	case 102:
		echo '密码错';
		break;
	case 103:
		echo '查询过快';
		break;
}