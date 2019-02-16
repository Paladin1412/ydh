<?php
// +----------------------------------------------------------------------
// | 合利宝快捷支付模型类
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.tupulian.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.tupulian.com )
// +----------------------------------------------------------------------
// | Author: andy.deng
// +----------------------------------------------------------------------
namespace app\loan\model;

use think\Model;
use think\Db;
use think\Config;
use RSA\Rsa;
// use AgencyPay\Crypt_Hash;
// use AgencyPay\Crypt_RSA;
// use AgencyPay\HttpClient;
// use AgencyPay\Math_BigInteger;
// use AES\Aes;

class QuickPayModel extends Model
{
	//接口请求地址
	private $quickpay_request_url = null;
	private $customer_number = null;
	private $quickpay_notify_url = null;
	private $pay_sign_key = null;

	public function __construct($env='dev'){
		$config_key = 'auth_'.$env.'.HELIBAO';
		$config = Config::get($config_key);
		$this->quickpay_request_url = $config['quickpay_request_url'];
		$this->customer_number = $config['customer_number'];
		$this->quickpay_notify_url = $config['quickpay_notify_url'];
		$this->quickpay_sign_key = $config['quickpay_sign_key'];
	}

	//首次支付(订单不存在或已过期或异常才调用此接口)
	public function quickPayCreateOrder($order_info){
		//随机生成一个交易订单号
		$rand_order_no = make_sn();
		$sign_params = [
		  'P1_bizType' => 'QuickPayCreateOrder',//首次支付下单
		  'P2_customerNumber' => $this->customer_number,//商户号
		  'P3_userId' => $order_info['user_id'],//用户id
		  'P4_orderId' => $rand_order_no,//交易订单号
		  'P5_timestamp' => date('YmdHis'),
		  'P6_payerName' => $order_info['name'],//用户姓名
		  'P7_idCardType' => 'IDCARD',//身份证
		  'P8_idCardNo' => $order_info['idCardNo'],//身份证号 AES加密
		  'P9_cardNo' => $order_info['cardNo'],//银行卡 AES加密
		  'P10_year' => '',//信用卡有效期年 当银行卡是信用卡时必输，AES加密 
		  'P11_month' => '',//信用卡有效期月 当银行卡是信用卡时必输，AES加密 
		  'P12_cvv2' => '',//信用卡安全码 当银行卡是信用卡时必输，AES加密
		  'P13_phone' => $order_info['phone'],//银行预留手机号码
		  'P14_currency' => 'CNY',//暂只支持人民币：CNY
		  'P15_orderAmount' => $order_info['application_amount'],//订单金额，以元为单位，最小金额为0.01
		  'P16_goodsName' => '大西瓜',//商品名称
		  'P17_goodsDesc' => '大西瓜',//商品描述
		  'P18_terminalType' => 'IMEI',//终端类型
		  'P19_terminalId' => '122121212121',//终端唯一标识
		  'P20_orderIp' => '127.0.0.1',//用户支付时使用的网络终端 IP
		  'P21_period' => '1',//订单有效时间
		  'P22_periodUnit' => 'Day',//有效时间单位
		  'P23_serverCallbackUrl' => $this->quickpay_notify_url,//服务器通知回调地址
		];

		$must_params = ['P1_bizType','P2_customerNumber','P3_userId','P4_orderId','P5_timestamp','P6_payerName','P7_idCardType','P8_idCardNo','P9_cardNo','P13_phone','P14_currency','P15_orderAmount','P16_goodsName','P18_terminalType','P19_terminalId','P20_orderIp','P23_serverCallbackUrl'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//部份参数进行AES加密
		$RSA = new Rsa();
		$aes_key = substr(md5(rand(10000,99999)),8,16) ;//AES加密key 16字符
		$aes_array = ['P8_idCardNo','P9_cardNo','P10_year','P11_month','P12_cvv2','P13_phone'];
		foreach($sign_params as $key=>$val){
			if(in_array($key,$aes_array)){
				$sign_params[$key] = $RSA->aes_encrypt($val,$aes_key);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= "&".$val;
		}

		//获取签名
		$sign = $RSA->genSign($source);

		$params = $sign_params;
		$params['P24_isEncrypt'] = 'true';//签名方式
		$params['signatureType'] = 'MD5WITHRSA';//签名方式
		$params['encryptionKey'] = $RSA->rsaEnc($aes_key);
		$params['sign'] = $sign;

		$url = $this->quickpay_request_url;
		$result = $this->curlPost($url, $params); 

		//订单日志不存在则写入
		$log_info = Db::name('quickpay_log')->where(['order_no'=>$order_info['order_no'],'is_available'=>1])->find();
		if(!is_array($log_info)){
			//纪录mysql日志
			$quickpay_log = [
				'user_id' => $order_info['user_id'],
				'phone' => $order_info['phone'],
				'order_no' => $order_info['order_no'],
				'rand_order_no' => $rand_order_no,
				'create_request_data' => json_encode($params),
				'create_return_data' => $result,
				'create_date' => date('Y-m-d H:i:s'),
				'is_available' => 1,//待付订单有效
				'company_code' => $order_info['company_code'],
			];
			Db::name('quickpay_log')->insert($quickpay_log);
		}
		
		$result  = json_decode($result,true);
		return $result;
	}


	//首次支付短信
	public function quickPaySendValidateCode($order_info){
		$sign_params = [
	        'P1_bizType'=>'QuickPaySendValidateCode',
	        'P2_customerNumber'=>$this->customer_number,
	        'P3_orderId'=>$order_info['order_no'],
	        'P4_timestamp'=>date('YmdHis'),
	        'P5_phone'=>$order_info['phone'],
	    ];

		$must_params = ['P1_bizType','P2_customerNumber','P3_orderId','P4_timestamp','P5_phone'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//部份参数进行AES加密
		$RSA = new Rsa();
		$aes_key = substr(md5(rand(10000,99999)),8,16) ;//AES加密key 16字符
		$aes_array = ['P5_phone'];
		foreach($sign_params as $key=>$val){
			if(in_array($key,$aes_array)){
				$sign_params[$key] = $RSA->aes_encrypt($val,$aes_key);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= "&".$val;
		}

		//获取签名
		$sign = $RSA->genSign($source);

		$params = $sign_params;
		$params['signatureType'] = 'MD5WITHRSA';//签名方式
		$params['encryptionKey'] = $RSA->rsaEnc($aes_key);
		$params['sign'] = $sign;
		//dump($params);

		$url = $this->quickpay_request_url;
		$json_result = $this->curlPost($url, $params); 
		$result  = json_decode($json_result,true);
		if($result['rt2_retCode']==='0000'){
			$is_available = 1;
		}else{
			$is_available = 0;
		}
		//纪录mysql日志
		$quickpay_log = [
			'code_request_data' => json_encode($params),
			'code_return_data' => $json_result,
			'code_date' => date('Y-m-d H:i:s'),
			'is_available'=> $is_available,
		];
		Db::name('quickpay_log')->where(['rand_order_no'=>$order_info['order_no'],'company_code'=>$order_info['company_code']])->update($quickpay_log);
		
		return $result;
	}

	//确认支付
	public function quickPayConfirmPay($order_info){
		$sign_params = [
	        'P1_bizType'=>'QuickPayConfirmPay',
	        'P2_customerNumber'=>$this->customer_number,
	        'P3_orderId'=>$order_info['order_no'],
	        'P4_timestamp'=>date('YmdHis'),
	        'P5_validateCode'=>$order_info['code'],
	        'P6_orderIp'=>$order_info['orderIp'],
	    ];
		$must_params = ['P1_bizType','P2_customerNumber','P3_orderId','P4_timestamp','P5_validateCode','P6_orderIp'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//部份参数进行AES加密
		$RSA = new Rsa();
		$aes_key = substr(md5(rand(10000,99999)),8,16) ;//AES加密key 16字符
		$aes_array = ['P5_validateCode'];
		foreach($sign_params as $key=>$val){
			if(in_array($key,$aes_array)){
				$sign_params[$key] = $RSA->aes_encrypt($val,$aes_key);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= "&".$val;
		}

		//获取签名
		$sign = $RSA->genSign($source);

		$params = $sign_params;
		$params['signatureType'] = 'MD5WITHRSA';//签名方式
		$params['encryptionKey'] = $RSA->rsaEnc($aes_key);
		$params['sign'] = $sign;
		//dump($params);

		$url = $this->quickpay_request_url;
		$json_result = $this->curlPost($url, $params); 
		$result  = json_decode($json_result,true);

		if($result['rt2_retCode']==='0000'){
			$is_available = 1;
		}else{
			$is_available = 0;
		}
		//纪录mysql日志
		$quickpay_log = [
			'confirm_request_data' => json_encode($params),
			'confirm_return_data' => $json_result,
			'confirm_date' => date('Y-m-d H:i:s'),
			'bindid' => $result['rt10_bindId'],
			'is_available'=> $is_available,
		];
		Db::name('quickpay_log')->where(['rand_order_no'=>$order_info['order_no'],'company_code'=>$order_info['company_code']])->update($quickpay_log);
		return $result;
	}

	//订单查询
	public function quickPayQuery($order_no){
		$sign_params = [
	        'P1_bizType'=>'QuickPayQuery',
	        'P2_orderId'=>$order_no,
	        'P3_customerNumber'=>$this->customer_number,
	    ];
		$must_params = ['P1_bizType','P2_orderId','P3_customerNumber'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= "&".$val;
		}

		//获取签名
		$RSA = new Rsa();
		$sign = $RSA->genSign($source);

		$params = $sign_params;
		$params['signatureType'] = 'MD5WITHRSA';//签名方式
		//$params['encryptionKey'] = $RSA->rsaEnc($aes_key);
		$params['sign'] = $sign;
		//dump($params);

		$url = $this->quickpay_request_url;
		$json_result = $this->curlPost($url, $params); 
		$result  = json_decode($json_result,true);
		//$this->check_sign($result);
		return $result;
	}


	//鉴权绑卡短信
	public function agreementPayBindCardValidateCode($bank_info){
		$mingwen = $sign_params = [
	        'P1_bizType'=>'AgreementPayBindCardValidateCode',//鉴权绑卡短信接口
	        'P2_customerNumber'=>$this->customer_number,
	        'P3_userId'=>$bank_info['user_id'],
	        'P4_orderId'=>$bank_info['order_no'],//商户订单号（该订单号必须跟鉴权绑卡订单号一致） 这里的订单号只是绑卡订单号
	        'P5_timestamp'=>date('YmdHis'),
	        'P6_cardNo'=>$bank_info['card_no'],//银行卡号 AES加密
	        'P7_phone'=>$bank_info['phone'],//手机号 AES加密
	        'P8_idCardNo'=>$bank_info['idcard'],//证件号码 AES加密
	        'P9_idCardType'=>'IDCARD',//证件类型  身份证
	        'P10_payerName'=>$bank_info['name'],//姓名
	        //'P12_year'=>'',//信用卡年份
	        //'P13_month'=>'',//信用卡月份
	        //'P14_cvv2'=>'',//安全码
	    ];
		$must_params = ['P1_bizType','P2_customerNumber','P3_userId','P4_orderId','P5_timestamp','P6_cardNo','P7_phone','P8_idCardNo','P9_idCardType','P10_payerName'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//部份参数进行AES加密
		$RSA = new Rsa();
		$aes_key = substr(md5(rand(10000,99999)),8,16) ;//AES加密key 16字符
		$aes_array = ['P6_cardNo','P7_phone','P8_idCardNo'];
		foreach($sign_params as $key=>$val){
			if(in_array($key,$aes_array)){
				$sign_params[$key] = $RSA->aes_encrypt($val,$aes_key);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= "&".$val;
		}

		//获取签名
		$sign = $RSA->genSign($source);

		$params = $sign_params;
		$params['signatureType'] = 'MD5WITHRSA';//签名方式
		$params['encryptionKey'] = $RSA->rsaEnc($aes_key);
		$params['sign'] = $sign;

		$url = $this->quickpay_request_url;
		$json_result = $this->curlPost($url, $params); 
		$result  = json_decode($json_result,true);
		//dump($result);
		//纪录mysql日志
		$quick_bindbank_log = [
			'user_id' => $bank_info['user_id'],
			'order_no' => $bank_info['order_no'],
			'card_no' => $bank_info['card_no'],
			'code_request_data' => json_encode($mingwen),
			'code_return_data' => $json_result,
			'code_date' => date('Y-m-d H:i:s'),
			'result' => 0,//0未绑定 1已绑定
			'phone' => $bank_info['phone'],
		];
		$bindbank_info = Db::name('quick_bindbank_log')->where('order_no',$bank_info['order_no'])->find();
		if(is_array($bindbank_info)){
			Db::name('quick_bindbank_log')->where('order_no',$bank_info['order_no'])->update($quick_bindbank_log);
		}else{
			Db::name('quick_bindbank_log')->insert($quick_bindbank_log);
		}
		return $result;
	}

	//鉴权绑卡
	public function quickPayBindCard($bank_info){
		$mingwen = $sign_params = [
	        'P1_bizType'=>'QuickPayBindCard',
	        'P2_customerNumber'=>$this->customer_number,
	        'P3_userId'=>$bank_info['user_id'],
	        'P4_orderId'=>$bank_info['order_no'],
	        'P5_timestamp'=>date('YmdHis'),
	        'P6_payerName'=>$bank_info['name'],
	        'P7_idCardType'=>'IDCARD',
	        'P8_idCardNo'=>$bank_info['idcard'],
	        'P9_cardNo'=>$bank_info['card_no'],
	        'P10_year'=>'',
	        'P11_month'=>'',
	        'P12_cvv2'=>'',
	        'P13_phone'=>$bank_info['phone'],
	        'P14_validateCode'=>$bank_info['code'],
	    ];
		$must_params = ['P1_bizType','P2_customerNumber','P3_userId','P4_orderId','P5_timestamp','P6_payerName','P7_idCardType','P8_idCardNo','P9_cardNo','P13_phone','P14_validateCode'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//部份参数进行AES加密
		$RSA = new Rsa();
		$aes_key = substr(md5(rand(10000,99999)),8,16) ;//AES加密key 16字符
		$aes_array = ['P8_idCardNo','P9_cardNo','P13_phone','P14_validateCode'];
		foreach($sign_params as $key=>$val){
			if(in_array($key,$aes_array)){
				$sign_params[$key] = $RSA->aes_encrypt($val,$aes_key);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= "&".$val;
		}

		//获取签名
		$sign = $RSA->genSign($source);

		$params = $sign_params;
		$params['P15_isEncrypt'] = 'true';//银行卡信息参数是否加密
		$params['signatureType'] = 'MD5WITHRSA';//签名方式
		$params['encryptionKey'] = $RSA->rsaEnc($aes_key);
		$params['sign'] = $sign;

		$url = $this->quickpay_request_url;
		$json_result = $this->curlPost($url, $params); 
		$result  = json_decode($json_result,true);

		if($result['rt2_retCode']==='0000' && $result['rt7_bindStatus']==='SUCCESS'){
			$final_result = 1;
			//$is_available = 1;
		}else{
			$final_result = 0;
			//$is_available = 0;
		}
		//纪录mysql日志
		$quick_bindbank_log = [
			'bind_request_data' => json_encode($mingwen),
			'bind_return_data' => $json_result,
			'bind_date' => date('Y-m-d H:i:s'),
			'result' => $final_result,//0未绑定 1已绑定
			'bankid' => $result['rt10_bindId'],
			//'is_available' => $is_available,
		];
		// dump($quick_bindbank_log);
		// exit;
		Db::name('quick_bindbank_log')->where('order_no',$bank_info['order_no'])->update($quick_bindbank_log);
		return $result;
	}

	//验签
	public function check_sign($response){
		dump($response);
		$params_sign = $response['sign'];
		//待签名字符串
		ksort($response);
		unset($response['sign']);
		dump($response);
		$new_arr=array_slice($response,0,5);
		for($i=1;$i<=5;$i++){
			array_shift($response);
		}
		$response = array_merge($response,$new_arr);
		dump($new_arr);
		dump($response);
		$source = '';
		foreach($response as $key=>$val){
			$source .= "&".$val;
		}
		$RSA = new Rsa();
		$result = $RSA->check_sign($source,$params_sign);
		dump($result);
		exit;


	}






	// //绑卡支付短信
	// public function quickPayBindPayValidateCode($sign_params){
	// 	$must_params = ['P1_bizType','P2_customerNumber','P3_bindId','P4_userId','P5_orderId','P6_timestamp','P7_currency','P8_orderAmount','P9_phone'];
	// 	//判断是否有必填参数
	// 	foreach($must_params as $key=>$val){
	// 		if(!isset($sign_params[$val])){
	// 			exit('缺少必填参数:'.$val);
	// 		}
	// 	}
	// 	$source = '';
	// 	foreach($sign_params as $key=>$val){
	// 		$source .= "&".$val;
	// 	}
	// 	$sign = md5($source);
	// 	$params = $sign_params;
	// 	$params['sign'] = $sign;

	// 	$url = $this->request_url;
	// 	$pageContents = $this->curlPost($url, $params); 
	// 	echo "back msg:".$pageContents."<br/>";
	// }

	// //绑卡支付
	// public function quickPayBindPay($sign_params){
	// 	$must_params = ['P1_bizType','P2_customerNumber','P3_bindId','P4_userId','P5_orderId','P6_timestamp','P7_currency','P8_orderAmount','P9_goodsName','P11_terminalType','P12_terminalId','P13_orderIp','P16_serverCallbackUrl'];
	// 	//判断是否有必填参数
	// 	foreach($must_params as $key=>$val){
	// 		if(!isset($sign_params[$val])){
	// 			exit('缺少必填参数:'.$val);
	// 		}
	// 	}
	// 	$source = '';
	// 	foreach($sign_params as $key=>$val){
	// 		$source .= "&".$val;
	// 	}
	// 	$sign = md5($source);
	// 	$params = $sign_params;
	// 	$params['sign'] = $sign;

	// 	$url = $this->request_url;
	// 	$pageContents = $this->curlPost($url, $params); 
	// 	echo "back msg:".$pageContents."<br/>";
	// }

	// //银行卡解绑
	// public function bankCardUnbind($sign_params){
	// 	$must_params = ['P1_bizType','P2_customerNumber','P3_userId','P4_bindId','P5_orderId','P6_timestamp'];
	// 	//判断是否有必填参数
	// 	foreach($must_params as $key=>$val){
	// 		if(!isset($sign_params[$val])){
	// 			exit('缺少必填参数:'.$val);
	// 		}
	// 	}
	// 	$source = '';
	// 	foreach($sign_params as $key=>$val){
	// 		$source .= "&".$val;
	// 	}
	// 	$sign = md5($source);
	// 	$params = $sign_params;
	// 	$params['sign'] = $sign;

	// 	$url = $this->request_url;
	// 	$pageContents = $this->curlPost($url, $params); 
	// 	echo "back msg:".$pageContents."<br/>";
	// }

	// //用户绑定银行卡信息查询（仅限于交易卡）
	// public function bankCardbindList($sign_params){
	// 	$must_params = ['P1_bizType','P2_customerNumber','P3_userId','P4_bindId','P5_timestamp'];
	// 	//判断是否有必填参数
	// 	foreach($must_params as $key=>$val){
	// 		if(!isset($sign_params[$val])){
	// 			exit('缺少必填参数:'.$val);
	// 		}
	// 	}
	// 	$source = '';
	// 	foreach($sign_params as $key=>$val){
	// 		$source .= "&".$val;
	// 	}
	// 	$sign = md5($source);
	// 	$params = $sign_params;
	// 	$params['sign'] = $sign;

	// 	$url = $this->request_url;
	// 	$pageContents = $this->curlPost($url, $params); 
	// 	echo "back msg:".$pageContents."<br/>";
	// }





	//curl post提交
	private function curlPost($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        //         'Content-Type: multipart/form-data; charset=utf-8'
        //     )
        // );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 " . $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);
        return $result;
    }













}