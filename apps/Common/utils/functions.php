<?php

use Phalcon\Di;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

/**
 * 上传图片
 * @param string $pic base64数据
 * @param string $type 图片类型，如：coupon,sign...
 * @return bool|string
 */
if (! function_exists('uploadBase64Pic'))
{
    function uploadBase64Pic($pic, $type, $accountId = 0)
    {
        $data = base64_decode($pic);
        $fileName = createFileName();

        $file = '/tmp/'.$fileName;
        file_put_contents($file, $data);

        $prefix = 'data/php/'.$type.'/';
        if (intval($accountId) != 0) {
            //如果账号不为空
            $prefix = $accountId.'/'.$prefix;
        }
        $key = $prefix.$fileName;
        $result = uploadQiniu($file, $key);
        unlink($file);

        return $result ? $key : false;
    }
}

/**
 * 上传图片
 */
if (! function_exists('uploadQiniu'))
{
    function uploadQiniu($filename, $targetname = '')
    {
        $config = Di::getDefault()->get('config');
        $config = $config->qiniu->toArray();

        try {
            $auth = new Auth($config['accessKey'], $config['secretKey']);
            $bucket = $config['bucket'];
            $token = $auth->uploadToken($bucket);
            $uploadMgr = new UploadManager();
            list($ret, $err) = $uploadMgr->putFile($token, $targetname, $filename);
            if ($err !== null) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}


/**
 * 删除图片
 */
if (! function_exists('delQiniuPic'))
{
    function delQiniuPic($filename)
    {
        $config = Di::getDefault()->get('config');
        $config = $config->qiniu->toArray();
        $auth = new Auth($config['accessKey'], $config['secretKey']);
        //初始化BucketManager
        $bucketMgr = new BucketManager($auth);
        $bucket = $config['bucket'];
        $bucketMgr->delete($bucket, $filename);
    }
}


/**
 * 从缓存中取七牛token,存3500秒
 * @return bool|int|mixed|string
 */
if (! function_exists('getQiniuToken'))
{
    function getQiniuToken()
    {
        $config = Di::getDefault()->get('config');

        $cacheKey = $config->cacheKey->toArray();
        $qiniuTokenKey = $cacheKey['key_qiniu']['key'];
        $shermanRedis = \ShermanRedis::getInstance();
        $token = $shermanRedis->getValue($qiniuTokenKey);
        if (empty($token)) {
            $config = $config->qiniu->toArray();
            try {
                $auth = new Auth($config['accessKey'], $config['secretKey']);
                $bucket = $config['bucket'];
                $token = $auth->uploadToken($bucket);
                if (!empty($token)) {
                    $shermanRedis->setValue($qiniuTokenKey, $token, $cacheKey['key_qiniu']['expire']);
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return $token;
    }
}


/**
 * 设置文件名称 不包括 文件路径
 * 生成(从2000-01-01 00:00:00 到现在的秒数+微秒+四位随机)
 */
if (! function_exists('createFileName'))
{
    function createFileName()
    {
        return sprintf('%010d',time() - 946656000)
        . sprintf('%03d', microtime() * 1000)
        . sprintf('%04d', mt_rand(0,9999))
        . '.jpg';
    }
}


/**
 * 调试打印函数
 * @param $data
 * @return mixed
 */
if (! function_exists('p'))
{
    function p($data)
    {
        return print_r($data, true);
    }
}


if (! function_exists('debug'))
{
    function debug($data)
    {
        $logger = new \Phalcon\Logger\Adapter\File(APP_PATH."/logs/test.log");
        $logger->log(json_encode($data, JSON_UNESCAPED_UNICODE), \Phalcon\Logger::INFO);
    }
}


if (! function_exists('errorLog'))
{
    function errorLog($data)
    {
        $logger = new \Phalcon\Logger\Adapter\File(APP_PATH."/logs/error.log");
        $logger->log(json_encode($data, JSON_UNESCAPED_UNICODE), \Phalcon\Logger::ERROR);
    }
}


if (! function_exists('taskLog'))
{
    function taskLog($data)
    {
        $logger = new \Phalcon\Logger\Adapter\File(APP_PATH."/logs/task.log");
        $logger->log(json_encode($data, JSON_UNESCAPED_UNICODE), \Phalcon\Logger::INFO);
    }
}


if (! function_exists('mkFileDir'))
{
    function mkFileDir($path)
    {
        $flag = true;
        if (!is_dir($path)) {
            $dirArray = explode(DIRECTORY_SEPARATOR, $path);
            $basePath = '';
            foreach ($dirArray as $k => $v) {
                $basePath = $basePath.DIRECTORY_SEPARATOR.$v;
                if (!is_dir($basePath)) {
                    if (!mkdir($basePath,0777)) {
                        $flag = false;
                        break;
                    }
                }
            }
        }
        return $flag;
    }
}


if (! function_exists('createReturnMsg'))
{
    function createReturnMsg($code, $data = '')
    {
        return ['code' => $code, 'msg' => $data];
    }
}


/**
 * 加密
 * @param $str
 * @return string
 */
if (! function_exists('encode'))
{
    function encode($str)
    {
        $hashids = Di::getDefault()->get('hashids');
        return $hashids->encode($str);
    }
}


/**
 * 解密
 * @param $encryptStr
 * @return mixed
 */
if (! function_exists('decode'))
{
    function decode($encryptStr)
    {
        $hashids = Di::getDefault()->get('hashids');
        $arr = $hashids->decode($encryptStr);
        return is_array($arr) ? $arr[0] : 0;
    }
}


/**
 * 手机号码验证
 * @param $mobile
 * @return bool
 */
if (! function_exists('checkMobile'))
{
    function checkMobile($mobile)
    {
        //$exp = "/^1[0-9]{10}$/";
        //$exp = "/^13\d{9}|14[57]\d{8}|15[0123456789]\d{8}|18[0123456789]\d{8}|17[0678]\d{8}$/";
        $exp = "/^13\d{9}|14[57]\d{8}|15\d{9}|18\d{9}|17\d{9}$/";
        if(preg_match($exp, $mobile)) {
            return true;
        } else {
            return false;
        }
    }
}


/**
 * 邮箱验证
 * @param $email
 * @return bool
 */
if (! function_exists('checkEmail'))
{
    function checkEmail($email)
    {
        $exp = "/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/";
        if(preg_match($exp, $email)) {
            return true;
        } else {
            return false;
        }
    }
}


/**
 * 判断是否是微信内置浏览器
 * @return bool
 */
if (! function_exists('isWechatBrowser'))
{
    function isWechatBrowser()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
}


/**
 * 发送短信
 * 必须确保阿里大于和创蓝短信模板内容一致
 * @param $mobile
 * @param $param
 */
if (! function_exists('sendSms'))
{
    function sendSms($mobile, $param)
    {
        $return = 200;
        $smsConfig = Di::getDefault()->getConfig()->sms->toArray();
        switch ($smsConfig['smsType']) {
            case 'ali' :
                $return = sendAliSms($mobile, $param, $smsConfig);
                break;
            case 'cl' :
                $return = sendClSms($mobile, $param, $smsConfig);
                break;
            case 'mq' :
                $return = sendRpcSms($mobile, $param);
                break;
        }

        $msg = $return != 200 ? '发送失败' : '';
        createReturnMsg($return, $msg);
    }
}


if (! function_exists('sendRpcSms'))
{
    function sendRpcSms($mobile, $param)
    {
        $content = $param['content'];
        $param = json_encode(['mobile' => $mobile, 'content' => $content], JSON_UNESCAPED_UNICODE);

        $mqConfig = Di::getDefault()->getConfig()->mq->toArray();
        //todo: use mq to send sms

        return 200;
    }
}


/**
 * @param string $mobile 手机号码
 * @param array $param 短信内容参数
 * @param array $smsConfig  短信相关配置
 * @return int
 */
if (! function_exists('sendAliSms'))
{
    function sendAliSms($mobile, $param, $smsConfig)
    {
        $code = $param['code'];

        $sms = new TopClient;
        $sms->appkey = $smsConfig['ali']['appkey'];
        $sms->secretKey = $smsConfig['ali']['secretKey'];
        $sms->format = $smsConfig['ali']['format'];
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend($mobile); //公共回传参数
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($smsConfig['smsFreeSignName']);
        $req->setSmsParam("{\"code\":\"$code\"}");
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode($smsConfig['smsTemplateCode']);
        $resp = $sms->execute($req);
        if (isset($resp->result->err_code) && $resp->result->err_code == '0') {
            //发送成功
            return 200;
        } else {
            //发送短信失败
            return 500;
        }
    }
}


if (! function_exists('sendClSms'))
{
    function sendClSms($mobile, $param, $smsConfig)
    {
        $clapi = new ChuanglanSmsApi;
        $clapi->account = $smsConfig['cl']['apiAccount'];
        $clapi->password = $smsConfig['cl']['apiPassword'];
        $clapi->sendUrl = $smsConfig['cl']['apiSendUrl'];

        $code = $param[0];
        $context = $smsConfig['cl']['context'];
        $context = str_replace('{code}', $code, $context);
        $result = $clapi->sendSMS($mobile, $context, 'true');
        $result = $clapi->execResult($result);
        if ($result[1]==0) {
            //发送成功
            return 200;
        } else {
            // TODO 发送短信失败，记录日志
            //echo "发送失败{$result[1]}";
            return 500;
        }
    }
}


/**
 * 递归转换对象为数组
 * @param $ary
 */
if (! function_exists('recurseToArray'))
{
    function recurseToArray(&$ary)
    {
        if (is_object($ary)) {
            $ary = (array)($ary);
            recurseToArray($ary);
        } else if (is_array($ary)) {
            foreach ($ary as &$item) {
                if (is_object($item)) {
                    $item = (array)$item;
                } else {
                    recurseToArray($item);
                }
            }
        }
    }
}


//HTTP请求（支持HTTP/HTTPS，支持GET/POST）
if (! function_exists('httpRequest'))
{
    function httpRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}


if (! function_exists('httpGet'))
{
    function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}
