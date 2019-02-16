<?php
/*
 *		秒嘀验证码发送接口类
 * 		By:somnus  
 * 		Time:2016-12-15 21:33 
 * */
namespace Sms;

use think\Config;
class MiaoDi{
	/**
	 * url前半部分
	 */
	protected $BASE_URL = "https://api.miaodiyun.com/20150822/";

	// 时间戳
	//date_default_timezone_set("Asia/Shanghai");

	/**
	 * 请求的内容类型，application/x-www-form-urlencoded
	 */
	protected $CONTENT_TYPE = "application/x-www-form-urlencoded";

	/**
	 * 期望服务器响应的内容类型，可以是application/json或application/xml
	 */
	protected $ACCEPT = "application/json";

	/**
	* url中{function}/{operation}?部分
	 */
	protected $funAndOperate = null;
	protected $ACCOUNT_SID = null;
	protected $AUTH_TOKEN = null;
	protected $type = null;
	protected $company_code = null;

	public function __construct($type,$company_code=null){
		$miaodi_config = Config::get('auth_dev.MIAODI');

		$this->ACCOUNT_SID = $miaodi_config['account_sid'];
		$this->AUTH_TOKEN = $miaodi_config['auth_token'];
		$this->type = (int)$type;
		$this->company_code = $company_code;
		if((int)$type===1){
			$this->funAndOperate = 'industrySMS/sendSMS';
		}else{
			$this->funAndOperate = "affMarkSMS/sendSMS";
		}
	}

	//发送验证码
	//成功返回0,失败返回错误代码
	public function send($number,$content,$template=1){
		// 生成body
		$body = $this->createBasicAuthData();
		// 在基本认证参数的基础上添加短信内容和发送目标号码的参数
		$body['to'] = $number;
		if($this->type === 1){
			$company_code = $this->company_code;
			if($company_code === '5aab2f49c3ec9' || $company_code === '5bdfa4fb4a6d7'){//大众易贷
				if($template===1){
					$body['templateid'] = '795710126';//注册
				}else if($template===2){
					$body['templateid'] = '795712920';//忘记密码
				}else if($template===3){
					$body['templateid'] = '795719742';//登入
				}
			}else if($company_code === '5aab9fb19ecea'){//易贷还
				if($template===1){
					$body['templateid'] = '795728938';
				}else if($template===2){
					$body['templateid'] = '795731946';
				}else if($template===3){
					$body['templateid'] = '795733573';
				}
			}
			$body['param'] = $content.',30';//30分钟过期
		}else{
			$body['smsContent'] = $content;
		}
		// 提交请求
		$result = $this->post($this->funAndOperate, $body);
		$res = json_decode($result,true);
		return $res;
	}
	
	/**
	 * 创建url
	 *
	 * @param funAndOperate
	 *            请求的功能和操作
	 * @return
	 */
	protected function createUrl($funAndOperate)
	{
	    return $this->BASE_URL . $funAndOperate;
	}

	protected function createSig()
	{
	    $timestamp = date("YmdHis");
	    // 签名
	    $sig = md5($this->ACCOUNT_SID . $this->AUTH_TOKEN . $timestamp);
	    return $sig;
	}

	protected function createBasicAuthData()
	{
		date_default_timezone_set("Asia/Shanghai");
	    $timestamp = date("YmdHis");
	    // 签名
	    $sig = md5($this->ACCOUNT_SID . $this->AUTH_TOKEN . $timestamp);
	    return array("accountSid" => $this->ACCOUNT_SID, "sig" => $sig, 'timestamp' => $timestamp, "respDataType"=> "JSON");
	}

	/**
	 * 创建请求头
	 * @param body
	 * @return
	 */
	protected function createHeaders()
	{
	    $headers = array('Content-type: ' . $this->CONTENT_TYPE, 'Accept: ' . $this->ACCEPT);
	    return $headers;
	}

	/**
	 * post请求
	 *
	*/
	protected function post($funAndOperate, $body)
	{
	    // 构造请求数据
	    $url = $this->createUrl($funAndOperate);
	   	//$this->logContent("url:". $url);
	    $headers = $this->createHeaders();

	   	
	    // 要求post请求的消息体为&拼接的字符串，所以做下面转换
	    $fields_string = "";
	    //unset($body['sig']);
	    foreach ($body as $key => $value) {
	        $fields_string .= $key . '=' . $value . '&';
	    }
	    $fields_string = rtrim($fields_string, '&');
	    // dump($fields_string);
	    // exit;
		//$this->logContent("post data:". $fields_string);
	
	    // 提交请求
	    $con = curl_init();
	    curl_setopt($con, CURLOPT_URL, $url);
	    curl_setopt($con, CURLOPT_SSL_VERIFYHOST, FALSE);
	    curl_setopt($con, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($con, CURLOPT_HEADER, 0);
	    curl_setopt($con, CURLOPT_POST, 1);
	    curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($con, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($con, CURLOPT_POSTFIELDS, $fields_string);

	    $result = curl_exec($con);
	    //$this->logContent("result:" .curl_error($con) . "(" .curl_errno($con) .")");
	    curl_close($con);
	    //$this->logContent("response:". $result);
	    return $result;
	}
	
	protected function logContent($content)
	{
    	$fp = fopen('../runtime/miaodi_log.txt','a+');
    	$time = date("Y-m-d H:i:s");
    	fwrite($fp, "[$time] $content\r\n");
    	fclose($fp);
	}
}
