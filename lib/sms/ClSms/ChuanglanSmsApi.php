<?php
/* *
 * 类名：ChuanglanSmsApi
 * 功能：创蓝接口请求类
 * 详细：构造创蓝短信接口请求，获取远程HTTP数据
 * 版本：1.3
 * 日期：2014-07-16
 */

class ChuanglanSmsApi
{
    public $account;
    public $password;
    public $sendUrl;
    public $queryUrl;

    public function __construct($account, $password)
    {
        $this->account = $account;
        $this->password = $password;
    }

	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $product 产品id，可选
	 * @param string $extno   扩展码，可选
	 */
	public function sendSMS($mobile, $msg, $needstatus = 'false', $product = '', $extno = '')
    {
		//创蓝接口参数
        $postArr = [
            'account' => $this->account,
            'pswd' => $this->password,
            'msg' => $msg,
            'mobile' => $mobile,
            'needstatus' => $needstatus,
            'product' => $product,
            'extno' => $extno
        ];
		$result = $this->curlPost($this->sendUrl, $postArr);
		return $result;
	}
	
	/**
	 * 查询额度
	 */
	public function queryBalance()
    {
		//查询参数
		$postArr = [
            'account' => $this->account,
            'pswd' => $this->password,
		];
		$result = $this->curlPost($this->queryUrl, $postArr);
		return $result;
	}

	/**
	 * 处理返回值
	 */
	public function execResult($result)
    {
        return preg_split("/[,\r\n]/",$result);
	}

	/**
	 * 通过CURL发送HTTP请求
	 * @param string $url  //请求URL
	 * @param array $postFields //请求参数 
	 * @return mixed
	 */
	private function curlPost($url,$postFields)
    {
        $postFields = http_build_query($postFields);
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        return $result;
	}
	
	//魔术获取
	public function __get($name)
    {
		return $this->$name;
	}
	
	//魔术设置
	public function __set($name,$value)
    {
		$this->$name=$value;
	}
}