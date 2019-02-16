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
class AgencyPayModel extends Model
{
	//接口请求地址
	private $merchant_no = null;
	private $merchant_name = null;
	private $md5_sign_key = null;
	private $agencypay_request_url = null;
	private $agencypay_notify_url = null;
	private $mongo_is_open = null;

	public function __construct($env='dev',$mongo_is_open=false){
		$config_key = 'auth_'.$env.'.HUIJU';
		$config = Config::get($config_key);
		$this->merchant_no = $config['merchant_no'];
		$this->merchant_name = $config['merchant_name'];
		$this->md5_sign_key = $config['md5_sign_key'];
		$this->agencypay_request_url = $config['agencypay_request_url'];
		$this->agencypay_notify_url = $config['agencypay_notify_url'];
		$this->mongo_is_open = $mongo_is_open;
	}

	//单笔代付
	public function pay($order_info){


		$sign_params = [
		  'userNo' => $this->merchant_no,//商户号
		  'productCode' => 'BANK_PAY_MAT_ENDOWMENT_ORDER',//朝夕付BANK_PAY_DAILY_ORDER  任意付BANK_PAY_MAT_ENDOWMENT_ORDER
		  'requestTime' => date('Y-m-d H:i:s'),//交易请求时间
		  'merchantOrderNo' => $order_info['order_no'],//商户订单号
		  'receiverAccountNoEnc' => $order_info['bankcard_no'],//收款人银行卡卡号
		  'receiverNameEnc' => $order_info['name'],//收款人银行卡持卡人名称
		  'receiverAccountType' => '201',//对私帐户 201 对公账户：204
		  'receiverBankChannelNo' => '',//收款账户联行号
		  'paidAmount' => $order_info['amount'],//交易金额
		  'currency' => '201',//人民币
		  'isChecked' => '202',//复核：201，不复核：202
		  'paidDesc' => '放款',//代付说明
		  'paidUse' => '204',//代付用途 贷款 204
		  'callbackUrl' => $this->agencypay_notify_url,//代付完成后异步回调通知地址
		];

		$must_params = ['userNo','merchantOrderNo','receiverAccountNoEnc','receiverNameEnc','paidAmount','callbackUrl'];
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
		//dump($sign_params);
		$url = $this->agencypay_request_url;
		$json_response = $this->curlPost($url, json_encode($sign_params));
		$result = json_decode($json_response,true);
		// dump($result);
		// exit;

		//transfer_log代付下单日志
		$transfer_log_data = [
			'order_no' => $order_info['order_no'],
			'request_data' => json_encode($sign_params),
			'return_data' => $json_response,
			'add_date' => date('Y-m-d H:i:s'),
			'add_time' => time(),
			'company_code' => $order_info['company_code'],
			'status_code' => $result['statusCode'],
		];
		Db::name('transfer_log')->insert($transfer_log_data);
		if($this->mongo_is_open){
			mongo_log('transfer_log', $transfer_log_data);
		}

		return $result;
	}

	//回调验签
	public function check_sign($back_data){
		//dump($back_data);
		$back_sign = $back_data['hmac'];
		//echo $back_sign;
		$source = '';
		//dump($back_data);
		$sign_data = [];
		$sign_data['status '] = $back_data['status'];
		$sign_data['errorCode '] = $back_data['errorCode'];
		$sign_data['errorCodeDesc '] = $back_data['errorCodeDesc'];
		$sign_data['userNo '] = $back_data['userNo'];
		$sign_data['merchantOrderNo '] = $back_data['merchantOrderNo'];
		$sign_data['platformSerialNo '] = $back_data['platformSerialNo'];
		$sign_data['receiverAccountNoEnc '] = $back_data['receiverAccountNoEnc'];
		$sign_data['receiverNameEnc '] = $back_data['receiverNameEnc'];
		$sign_data['paidAmount '] = $back_data['paidAmount'];
		$fee = $back_data['fee'];
		if(strpos($fee,'.')===false){
			$fee = $fee.'.00';
		}
		$sign_data['fee '] = $fee;
		//dump($sign_data);
		foreach($sign_data as $key=>$val){
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
                'Content-Type:application/json'
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