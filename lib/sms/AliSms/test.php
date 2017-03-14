<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai');

    $config = [
        'appkey' => '',
        'secretKey' => '',
        'smsFreeSignName' => '',
        'smsTemplateCode' => '',
    ];

    $c = new TopClient;
    $c->appkey = $config['appkey'];
    $c->secretKey = $config['secretKey'];
    $c->format = "json";

    $code = '355658';
    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req->setExtend($mobile); //公共回传参数
    $req->setSmsType("normal");
    $req->setSmsFreeSignName($config['smsFreeSignName']);
    $req->setSmsParam("{\"code\":\"$code\"}");
    $req->setRecNum($mobile);
    $req->setSmsTemplateCode($config['smsTemplateCode']);
    $resp = $c->execute($req);

    if (isset($resp->result->err_code) && $resp->result->err_code == '0') {
        //发送成功
        return 200;
    } else {
        //var_dump($resp);
        DI()->logger->debug('send sms error', json_encode($resp, JSON_UNESCAPED_UNICODE));
        return 500;
    }
?>