<?php
// +----------------------------------------------------------------------
// | 合利宝代付模型类
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
use AgencyPay\Crypt_Hash;
use AgencyPay\Crypt_RSA;
use AgencyPay\HttpClient;
use AgencyPay\Math_BigInteger;
class AgencyPayModel extends Model
{
	//接口请求地址
	private $agencypay_request_url = null;
	private $customer_number = null;
	private $agencypay_notify_url = null;
	private $agencypay_sign_key = null;

	public function __construct($env='dev'){
		$config_key = 'auth_'.$env.'.HELIBAO';
		$config = Config::get($config_key);
		$this->agencypay_request_url = $config['agencypay_request_url'];
		$this->customer_number = $config['customer_number'];
		$this->agencypay_notify_url = $config['agencypay_notify_url'];
		$this->agencypay_sign_key = $config['agencypay_sign_key'];
	}

	//单笔代付
	public function pay($order_info){
		$sign_params = [
			'P1_bizType'=>'Transfer',//单笔代付接口
		    'P2_orderId'=>$order_info['order_no'],//同一商户号下订单号唯一
		    'P3_customerNumber'=>$this->customer_number,//合利宝分配商户号
		    'P4_amount'=>$order_info['amount'],//金额单位为元，最少值0.01
		    'P5_bankCode'=>$order_info['bankCode'],//银行编码表
		    'P6_bankAccountNo'=>$order_info['bankAccountNo'],//银行账户号
		    'P7_bankAccountName'=>$order_info['bankAccountName'],//银行账户名
		    'P8_biz'=>'B2C',//B2B:对公 B2C:对私
		    'P9_bankUnionCode'=>'313584012022',//银行联行号 对公联行号必填
		    'P10_feeType'=>'PAYER',//手续费收取方式 PAYER:付款方收取手续费 RECEIVER:收款方收取手续费
		    'P11_urgency'=>'true',//是否加急
		    'P12_summary'=>'结算款',//打款备注
		];

		//获取私钥
		$privatekey = file_get_contents('../extend/AgencyPay/privatekey.key');
		$must_params = ['P1_bizType','P2_orderId','P3_customerNumber','P4_amount','P5_bankCode','P6_bankAccountNo','P7_bankAccountName','P8_biz','P9_bankUnionCode','P10_feeType','P11_urgency','P12_summary'];
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

		//获取签名字符串
		$rsa = new Crypt_RSA();
		$rsa->setHash('md5');
		$rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
		$rsa->loadKey($privatekey);
		$sign= base64_encode($rsa->sign($source));
		//echo "sign:".$sign."<br/>";

		//组装请求参数
		$params = $sign_params;
		$params['notifyUrl'] = $this->agencypay_notify_url;
		$params['sign'] = $sign;
		// dump($params);
		// exit;

		$url = $this->agencypay_request_url;//请求的页面地址  request url
		$json_response = $this->curlPost($url, $params);  //发送请求 send request
		$response = json_decode($json_response,true);
		//echo "back msg:".$response."<br/>";  //返回的结果   The returned result
		//$check = $this->check_sign($response);
		if($response['rt2_retCode']==='0000'){
			$result = 1;
		}else{
			$result = 0;
		}

		//transfer_log代付下单日志
		$transfer_log_data = [
			'order_no' => $order_info['order_no'],
			'request_data' => json_encode($params),
			'return_data' => $json_response,
			'result' => $result,
			'add_date' => date('Y-m-d H:i:s'),
			'add_time' => time(),
			'company_code' => $order_info['company_code'],
		];
		Db::name('transfer_log')->insert($transfer_log_data);

		//修改开始 赵光帅
		if(config('auth_' . check_env() . '.IS_OPEN_MONGO') == true){
            //记录mongodb
            $mongo_data = array(
                'order_no' => $order_info['order_no'],
                'request_data' => json_encode($params),
                'return_data' => $json_response,
                'result' => $result,
                'add_date' => date('Y-m-d H:i:s'),
                'add_time' => time(),
                'company_code' => $order_info['company_code'],
            );
            mongo_log('transfer_log', $mongo_data);
        }
        //修改结束 赵光帅

		return $response;
	}



	//查询代付结果
	public function query($orderId){
		$sign_params = [
			'P1_bizType'=>'TransferQuery',
			'P2_orderId'=>$orderId,
			'P3_customerNumber'=>$this->customer_number,
		];
		$privatekey = file_get_contents('../extend/AgencyPay/privatekey.key');
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= "&".$val;
		}
		//echo "source:".$source."<br/>";

		$rsa = new Crypt_RSA();
	  	$rsa->setHash('md5');
	  	$rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
	  	$rsa->loadKey($privatekey);

		$sign= base64_encode($rsa->sign($source));
	  	//echo "sign:".$sign."<br/>";

	    $url = $this->agencypay_request_url;
	    //post的参数 
	    $params = $sign_params;
	    $params['sign'] = $sign;

	    $response = $this->curlPost($url, $params); 
	    //$check = $this->check_sign($response);
	    //echo "back msg:".$response."<br/>"; 
		$response = json_decode($response,true);
		return $response;
	}

	//代付回调验签
	public function  check_sign($response){
		$params_sign = $response['sign'];
		ksort($response);
		unset($response['sign']);
		unset($response['UTF-8']);
		$rt10_createDate =array_shift($response);
		$rt11_completeDate =array_shift($response);
		$response['rt10_createDate'] = $rt10_createDate;
		$response['rt11_completeDate'] = $rt11_completeDate;

		$source = '';
		foreach($response as $key=>$val){
			$source .= "&".$val;
		}
		$sign = md5($source.'&'.$this->agencypay_sign_key);
		if($sign === $params_sign){
			return true;
		}else{
			return false;
		}
	}

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