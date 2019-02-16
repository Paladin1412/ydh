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

class QuickPayModel extends Model
{
	//接口请求地址
	private $merchant_no = null;
	private $merchant_name = null;
	private $md5_sign_key = null;
	private $quickpay_sms_url = null;
	private $quickpay_request_url = null;
	private $quickpay_notify_url = null;
	private $quickpay_sms_notify_url = null;
	private $mongo_is_open = null;

	public function __construct($env='dev',$mongo_is_open=false){
		$config_key = 'auth_'.$env.'.HUIJU';
		$config = Config::get($config_key);
		$this->merchant_no = $config['merchant_no'];
		$this->merchant_name = $config['merchant_name'];
		$this->md5_sign_key = $config['md5_sign_key'];
		$this->quickpay_sms_url = $config['quickpay_sms_url'];
		$this->quickpay_request_url = $config['quickpay_request_url'];
		$this->quickpay_notify_url = $config['quickpay_notify_url'];
		$this->quickpay_sms_notify_url = $config['quickpay_sms_notify_url'];
		$this->mongo_is_open = $mongo_is_open;
	}

	//获取支付短信
	public function quickPaySentSms($order_info){
		//随机生成一个交易订单号
		$rand_order_no = make_sn();
		$sign_params = [
		  'p0_Version' => '2.0',//首次支付下单
		  'p1_MerchantNo' => $this->merchant_no,//商户号
		  'p2_MerchantName' => $this->merchant_name,//商户名称
		  'p3_SubMerchantNo' => '',//子商户号
		  'p4_PayerId' => '',//商户身份标识
		  'p5_TradeMerchantNo' => '',//交易商户号
		  'q1_OrderNo' => $rand_order_no,//商品订单号
		  'q2_Amount' => $order_info['amount'],//订单金额
		  'q3_Cur' => '1',//交易币种
		  'q4_ProductName' => '回款',//商品名称
		  'q5_OrderExpire' => '',//订单有效期
		  'q6_ReturnUrl' => '',//页面通知地址
		  'q7_NotifyUrl' => $this->quickpay_notify_url,//异步通知地址
		  'q8_FrpCode' => 'FAST',//银行编码
		  'q9_Mp' => '哈哈哈',//订单回传信息
		  's1_PayerName' => $order_info['name'],//支付人姓名
		  's2_PayerCardType' => '1',//支付人证件类型
		  's3_PayerCardNo' => $order_info['idcard_no'],//支付人证件号
		  's4_PayerBankCardNo' => $order_info['bankcard_no'],//支付人银行卡号
		  's5_BankCardExpire' => '',//信用卡有效期
		  's6_CVV2' => '',//信用卡CVV2
		  's7_BankMobile' => $order_info['mobile'],//银行预留手机号
		  's8_IsBindCard' => '',//绑卡标识
		  't1_Rcms' => '',//风险控制标识
		  't2_ext' => '',//预留字段
		  't3_ext' => '',//预留字段
		];

		$must_params = ['p1_MerchantNo','p2_MerchantName','q1_OrderNo','q2_Amount','q4_ProductName','q7_NotifyUrl','s1_PayerName','s3_PayerCardNo','s4_PayerBankCardNo','s7_BankMobile'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= $val;
		}

		$sign_params['hmac'] = md5($source.$this->md5_sign_key);

		$url = $this->quickpay_sms_url;
		$result = $this->curlPost($url, $sign_params); 

		//删除此订单旧的支付日志
		Db::name('quickpay_log')->where(['order_no'=>$order_info['order_no']])->delete();

		//纪录mysql日志
		$quickpay_log = [
			'user_id' => $order_info['user_id'],
			'mobile' => $order_info['mobile'],
			'order_no' => $order_info['order_no'],
			'rand_order_no' => $rand_order_no,
			'code_request_data' => json_encode($sign_params),
			'code_return_data' => $result,
			'code_date' => date('Y-m-d H:i:s'),
			'company_code' => $order_info['company_code'],
		];
		Db::name('quickpay_log')->insert($quickpay_log);
		$result  = json_decode($result,true);
		return $result;
	}

	//确认支付
	public function quickPayConfirmPay($order_info){
		//获取随机单号
		$rand_order_no = Db::name('quickpay_log')->where(['order_no'=>$order_info['order_no']])->value('rand_order_no');

		$sign_params = [
		  'p0_Version' => '2.0',//首次支付下单
		  'p1_MerchantNo' => $this->merchant_no,//商户号
		  'p2_MerchantName' => $this->merchant_name,//商户名称
		  'p3_SubMerchantNo' => '',//子商户号
		  'p4_PayerId' => '',//商户身份标识
		  'p5_TradeMerchantNo' => '',//交易商户号
		  'q1_OrderNo' => $rand_order_no,//商品订单号
		  'q2_Amount' => $order_info['amount'],//订单金额
		  'q3_Cur' => '1',//交易币种
		  'q4_ProductName' => '回款',//商品名称
		  'q5_OrderExpire' => '',//订单有效期
		  'q6_ReturnUrl' => '',//页面通知地址
		  'q7_NotifyUrl' => $this->quickpay_notify_url,//异步通知地址
		  'q8_FrpCode' => 'FAST',//银行编码
		  'q9_Mp' => '哈哈哈',//订单回传信息
		  's1_PayerName' => $order_info['name'],//支付人姓名
		  's2_PayerCardType' => '1',//支付人证件类型
		  's3_PayerCardNo' => $order_info['idcard_no'],//支付人证件号
		  's4_PayerBankCardNo' => $order_info['bankcard_no'],//支付人银行卡号
		  's5_BankCardExpire' => '',//信用卡有效期
		  's6_CVV2' => '',//信用卡CVV2
		  's7_BankMobile' => $order_info['mobile'],//银行预留手机号
		  's8_IsBindCard' => '',//绑卡标识
		  't1_Rcms' => '',//风险控制标识
		  't2_SmsCode' => $order_info['code'],//预留字段
		  't3_ext' =>'',//预留字段
		];

		$must_params = ['p1_MerchantNo','p2_MerchantName','q1_OrderNo','q2_Amount','q4_ProductName','q7_NotifyUrl','s1_PayerName','s3_PayerCardNo','s4_PayerBankCardNo','s7_BankMobile','t2_SmsCode'];
		//判断是否有必填参数
		foreach($must_params as $key=>$val){
			if(!isset($sign_params[$val])){
				exit('缺少必填参数:'.$val);
			}
		}

		//待签名字符串
		$source = '';
		foreach($sign_params as $key=>$val){
			$source .= $val;
		}

		$sign_params['hmac'] = md5($source.$this->md5_sign_key);

		$url = $this->quickpay_request_url;
		$json_result = $this->curlPost($url, $sign_params); 
		$result  = json_decode($json_result,true);

		//纪录mysql日志
		$quickpay_log = [
			'confirm_request_data' => json_encode($sign_params),
			'confirm_return_data' => $json_result,
			'confirm_date' => date('Y-m-d H:i:s'),
			//'is_available'=> 0,
		];
		Db::name('quickpay_log')->where(['order_no'=>$order_info['order_no'],'company_code'=>$order_info['company_code']])->update($quickpay_log);
		return $result;
	}

	//回调验签
	public function check_sign($back_data){
		$back_sign = $back_data['hmac'];
		unset($back_data['hmac']);
		$source = '';
		foreach($back_data as $key=>$val){
			$source .= $val;
		}
		$sign = md5($source.$this->md5_sign_key);
		if($back_sign===$sign){
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: multipart/form-data; charset=utf-8'
            )
        );
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