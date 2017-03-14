<?php

use Phalcon\Di;

class ShermanWechat
{
    public static function getAccessToken()
    {
        $app = Di::getDefault()->get('easywechat');
        $accessToken = $app->access_token;
        return $accessToken->getToken();
    }

    public static function getConfig()
    {
        return Di::getDefault()->get('config')->wechat->toArray();
    }

    public static function getJssdk($url)
    {
        //从DI中获取easyWeChat实例
        $app = Di::getDefault()->get('easywechat');
        $js = $app->js;
        $js->setUrl($url);
        $data = $js->config(['scanQRCode','showMenuItems','showOptionMenu','hideMenuItems'], true);

        return $data;
    }

//********************************************摇一摇红包********************************************************
    /**
     * 创建红包活动
     * @param $param
     * @return mixed
     */
    public static function addLotteryInfo($param)
    {
        $wcConfig = self::getConfig();
        $appId = $wcConfig['app_id'];
        $aroundKey = $wcConfig['around']['lottery_key'];

        $url = 'https://api.weixin.qq.com/shakearound/lottery/addlotteryinfo?access_token=%s&use_template=%s';
        $url = sprintf($url, self::getAccessToken(), $param['use_template']);

        $data = array(
            'title'			=> $param['title'],//抽奖活动名称
            'desc' 			=> $param['desc'],//抽奖活动描述
            'onoff' 		=> $param['onoff'],//抽奖开关
            'begin_time' 	=> $param['begin_time'],//抽奖活动开始时间
            'expire_time' 	=> $param['expire_time'],//抽奖活动结束时间
            'sponsor_appid' => $appId,//红包提供商户公众号的appid
            'total'   		=> $param['total'],//红包总数
            'jump_url'   	=> $param['jump_url'],//红包关注界面后可以跳转到第三方自定义的页面
            'key'   		=> $aroundKey,//开发者自定义的key,用来生成活动抽奖接口的签名参数
        );

        $res = self::http_request($url, self::decodeUnicode(json_encode($data)));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 设置红包活动抽奖开关
     * @param int $lotteryId
     * @param int $switch: 0 关闭 1 开启
     * @return mixed
     */
    public static function setOnoff($lotteryId, $switch)
    {
        $url = "https://api.weixin.qq.com/shakearound/lottery/setlotteryswitch?access_token=%s&lottery_id=%s&onoff=%s";
        $url = sprintf($url, self::getAccessToken(), $lotteryId, $switch);
        $res = self::http_request($url);
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 录入红包信息
     * @param $lotteryId
     * @param $prizeInfoList
     * @return mixed
     */
    public static function setPrizeBucket($lotteryId, $prizeInfoList)
    {
        $url = 'https://api.weixin.qq.com/shakearound/lottery/setprizebucket?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $wcConfig = self::getConfig();
        $appId = $wcConfig['app_id'];
        $merchantId = $wcConfig['payment']['merchant_id'];

        $data = array(
            'lottery_id'		=> $lotteryId,
            'mchid' 			=> $merchantId,
            'sponsor_appid' 	=> $appId,
            'prize_info_list'   => $prizeInfoList
        );
        $data = json_encode($data);
        $data = str_replace("\\\/", "/",  $data);

        $res = self::http_request($url, $data);
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 摇一摇红包活动查询
     * @param $lotteryId
     * @return mixed
     */
    public static function queryLottery($lotteryId)
    {
        $url = 'https://api.weixin.qq.com/shakearound/lottery/querylottery?access_token=%s&lottery_id=%s';
        $url = sprintf($url, self::getAccessToken(), $lotteryId);

        $res = self::http_request($url);
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 微信发红包生成本地订单号,不能重复
     * @return string
     */
    public static function createBill()
    {
        $wcConfig = self::getConfig();
        $merchantId = $wcConfig['payment']['merchant_id'];

        $range_num = substr(uniqid(rand()),0,4).substr(microtime(),2,4).sprintf('%02d',rand(0,99));
        return $merchantId.date('Ymd').$range_num;
    }

    public static function decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str);
    }

//********************************************设备********************************************************
    /**
     * 批量查询设备统计数据接口
     * @param $date
     * @param int $pageIndex
     * @return mixed
     */
    public static function deviceListStatics($date, $pageIndex = 1)
    {
        $url = 'https://api.weixin.qq.com/shakearound/statistics/devicelist?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'date' 			=> $date,
            'page_index' 	=> $pageIndex,
        ];
        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 根据设备UUID查询微信统计数据
     * @param $uuid
     * @param $major
     * @param $minor
     * @param $beginDate
     * @param $endDate
     * @return mixed
     */
    public static function deviceStatics($uuid, $major, $minor, $beginDate, $endDate)
    {
        $url = 'https://api.weixin.qq.com/shakearound/statistics/device?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'device_identifier' => [
                'device_id' => 0,
                'uuid' 	=> $uuid,
                'major' => intval($major),
                'minor' => intval($minor),
            ],
            'begin_date' => $beginDate,
            'end_date'   => $endDate,
        ];

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 上传图片素材
     * @param $pic
     * @return mixed
     */
    public static function uploadPic($pic)
    {
        $url = 'https://api.weixin.qq.com/shakearound/material/add?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $file_info = [
            'filename' => $pic,
        ];

        $res = self::addMaterial($url,$file_info);
        $result = json_decode($res, true);
        return $result;
    }
    /**
     * 模拟表单上传图片
     */
    public static function addMaterial($wxurl,$file_info)
    {
        $file = $file_info['filename'];

        $url = $file;

        $header = array("Connection: Keep-Alive","Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Pragma: no-cache", "Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3","User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        $content = curl_exec($ch);

        $curlinfo = curl_getinfo($ch);

        curl_close($ch);

        if ($curlinfo['http_code'] == 200) {
            if($curlinfo['content_type'] == 'image/jpeg') {
                $exf = '.jpeg';
            }else if($curlinfo['content_type'] == 'image/png') {
                $exf = '.png';
            }else if($curlinfo['content_type'] == 'image/gif') {
                $exf = '.gif';
            }
            //存放图片的路径及图片名称
            $filename =  '/tmp/ico-'.date("YmdHis").uniqid().$exf;
            file_put_contents($filename, $content);
            $res = self::post($wxurl,$filename);

            unlink($filename);
            return $res;
        }
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    public static function post($url, $files)
    {
        //设置请求的参数
        $curl = curl_init($url);
        if (class_exists('\CURLFile')) {
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
            $data = array('file' => new \CURLFile(realpath($files)));//>=php5.5
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1 );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);  //执行发送

        return $res;//返回
    }

    /**
     * 新增页面
     * @param $pageArr
     * @return mixed
     */
    public static function addPage($pageArr)
    {
        return self::operatePage($pageArr, 'add');
    }

    /**
     * 编辑页面
     * @param $pageArr
     * @return mixed
     */
    public static function modifyPage($pageArr)
    {
        return self::operatePage($pageArr, 'update');
    }
    /**
     * 删除页面
     * @param $pageArr
     * @return mixed
     */
    public static function delPage($pageArr)
    {
        return self::deletePage($pageArr);
    }

    /**
     * 新增/编辑页面
     * @param array $pageArr
     * @param string $operateType
     * @return mixed
     */
    private static function operatePage($pageArr, $operateType = 'add')
    {
        switch ($operateType) {
            case 'update' :
                $url = 'https://api.weixin.qq.com/shakearound/page/update?access_token=%s';
                break;
            default :
                $url = 'https://api.weixin.qq.com/shakearound/page/add?access_token=%s';
        }
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'title' 		=> $pageArr['title'],
            'comment' 		=> $pageArr['comment'],
            'icon_url' 		=> $pageArr['icon_url'],
            'description' 	=> $pageArr['description'],
            'page_url'   	=> $pageArr['page_url'],
        ];
        if ($operateType == 'update') {
            $data['page_id'] = intval($pageArr['page_id']);
        }

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 删除
     * @param array $pageArr
     * @param string $operateType
     * @return mixed
     */
    private static function deletePage($pageArr)
    {

        $url = 'https://api.weixin.qq.com/shakearound/page/delete?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = ['page_id' => intval($pageArr['page_id'])];
        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 绑定页面与设备的关系bd
     * @param $uuid
     * @param $major
     * @param $minor
     * @param array $pageIdArr:该设备原有的关联关系将被直接清除；传空则会清除该设备的所有关联关系
     * @return mixed
     */
    public static function bindPage($device,$uuid, $major, $minor, $pageIdArr = [])
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/bindpage?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'device_identifier' => [
                'device_id' => $device,
                'uuid' 	=> $uuid,
                'major' => intval($major),
                'minor' => intval($minor),
            ],
            'page_ids' 	=> array_map('intval', $pageIdArr),
        ];

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 解除设备与单个页面的关系jb
     * @param $uuid
     * @param $major
     * @param $minor
     * @param $pageId:页面ID
     * @return mixed
     */
    public static function removePage($device,$uuid, $major, $minor, $pageId)
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/bindpage?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'device_identifier' => [
                'device_id' => $device,
                'uuid' 	=> $uuid,
                'major' => intval($major),
                'minor' => intval($minor),
            ],
            'page_ids' 	=> array_map('intval', $pageId),
            'bind'   	=> 0,//关联操作标志位， 0为解除关联关系，1为建立关联关系
            'append'   	=> 0,//新增操作标志位， 0为覆盖，1为新增
        ];

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 查询页面列表
     * @param $begin
     * @param $count
     * @return mixed
     */
    public static function queryPage($begin, $count)
    {
        $url = 'https://api.weixin.qq.com/shakearound/page/search?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'type' 	=> 2,
            'begin' => $begin,
            'count' => $count,
        ];

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 查询指定设备所关联的页面
     * @param $uuid
     * @param $major
     * @param $minor
     * @return mixed
     */
    public static function queryDevicePage($uuid, $major, $minor)
    {
        $url = 'https://api.weixin.qq.com/shakearound/relation/search?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'type' 	=> 1,
            'device_identifier' => [
                'device_id' => 0,
                'uuid' 	=> $uuid,
                'major' => intval($major),
                'minor' => intval($minor),
            ],
        ];

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 拉取门店列表
     * @param int $begin 起始
     * @param int $limit
     * @return mixed
     */
    public static function getpolist($begin=0,$limit=50)
    {
        $url='https://api.weixin.qq.com/cgi-bin/poi/getpoilist?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'begin' => intval($begin),
            'limit' => intval($limit),
        ];

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 绑定设备到门店
     * @param $device_id
     * @param $poi_id
     * @return mixed
     */
    public static function updateDevice($device_id,$poi_id)
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/bindlocation?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'device_identifier' => [
                'device_id' => intval($device_id),
                'uuid' 	=> 0,
                'major' => 0,
                'minor' => 0
            ],
            'poi_id' => intval($poi_id)
        ];

        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 新增设备分组
     * @param string $groupName:分组名称
     * @return mixed
     */
   public static function addGroup($groupName = 'group01')
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/group/add?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = ['group_name' => $groupName];
        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }


    /**
     * 查询设备分组列表
     * @return mixed
     */
    public static function getGroupList()
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/group/getlist?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'begin' => 0,
            'count' => 1000,// 待查询的分组数量，不能超过1000个
        ];
        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }


    /**
     * 查询制定分组详情
     * @param $groupId
     * @return mixed
     */
    public static function getGroupDetail($groupId)
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/group/getdetail?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'group_id'  => $groupId,
            'begin' 	=> 0,
            'count' 	=> 1000,// 	待查询的分组里设备的数量，不能超过1000个
        ];
        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 添加设备到分组(每个分组能够持有的设备上限为10000，并且每次添加操作的添加上限为1000)
     * @param $groupId
     * @param $deviceList
     * @return mixed
     */
    public static function addGroupDevice($groupId, $deviceList)
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/group/adddevice?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'group_id'  		 => $groupId,
            'device_identifiers' => $deviceList,
        ];
        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 从分组中移除设备(每次删除操作的上限为1000)
     * @param $groupId
     * @param $deviceList
     * @return mixed
     */
    public static function deleteGroupDevice($groupId, $deviceList)
    {
        $url = 'https://api.weixin.qq.com/shakearound/device/group/deletedevice?access_token=%s';
        $url = sprintf($url, self::getAccessToken());

        $data = [
            'group_id'  		 => $groupId,
            'device_identifiers' => $deviceList,
        ];
        $res = self::http_request($url, json_encode($data));
        $result = json_decode($res, true);
        return $result;
    }

    //HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    public static function http_request($url, $data = null)
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
        error_log( $output . '===>日志日期:' . date('Y-m-d H:i:s') . "\r\n", 3, '/var/log/saas/token.log');
        $result = json_decode($output, true);
        if($result['errcode']=='40001')
            {
                $app = Di::getDefault()->get('easywechat');
                $accessToken = $app->access_token;
                $accessToken->getToken(true);
            }
        return $output;
    }

    public static function httpGet($url)
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

    public function curlPostSsl($url, $vars, $second = 30, $aHeader = array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        //以下两种方式需选择一种
        //第一种方法，cert 与 key 分别属于两个.pem文件
        curl_setopt($ch,CURLOPT_SSLCERT,BASE_ROOT_PATH.DS.'wechat'.DS.'include'.DS.'cer'.DS.'apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLKEY,BASE_ROOT_PATH.DS.'wechat'.DS.'include'.DS.'cer'.DS.'apiclient_key.pem');
        curl_setopt($ch,CURLOPT_CAINFO,BASE_ROOT_PATH.DS.'wechat'.DS.'include'.DS.'cer'.DS.'rootca.pem');

        //第二种方式，两个文件合成一个.pem文件
        //curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

        if( count($aHeader) >= 1 ) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
        $data = curl_exec($ch);
        if($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            //echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
}
