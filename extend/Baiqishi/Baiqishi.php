<?php
/*
 *白骑士-风控策略接口类
 *By:andy.deng  
 *Time:2018-09-28 08:33 
 * */
namespace Baiqishi;

use think\Config;
class Baiqishi{

	protected $decision_request_url = null;
	protected $upload_request_url = null;
	protected $partnerid = null;
	protected $verifykey = null;
	protected $appid = null;

	public function __construct($env){
		$config_key = 'auth_'.$env.'.BAIQISHI';
		$config = Config::get($config_key);
		$this->decision_request_url = $config['decision_request_url'];
		$this->upload_request_url = $config['upload_request_url'];
		$this->partnerid = $config['partnerid'];
		$this->verifykey = $config['verifykey'];
		$this->appid = $config['appid'];
	}

	//风险决策api
	public function decision($post_parms){
		$post_parms['partnerId'] = $this->partnerid;
		$post_parms['verifyKey'] = $this->verifykey;
		$post_parms['appId'] = $this->appid;
		$post_parms['eventType'] = 'loan';
		$url = $this->decision_request_url;
		$result = $this->curlPost($url,$post_parms);
        trace('白骑士上传决策返回结果'.$result);
		$result = json_decode($result,true);
		if(is_array($result)){
			//上传决策结果
			$this->upload_result($result);
		}
		return $result;
	}

	//上传决策结果
	public function upload_result($result){
		if($result['finalDecision']==='Accept'){
			$bizResult = 'success';
		}else{
			$bizResult = 'fail';
		}
		$post_parms = [
			'partnerId' => $this->partnerid,//商户编号
			'verifyKey' => $this->verifykey,//认证密钥，
			'appId' => $this->appid,//应用编号
			'eventType' => 'loan',//事件类型 //loan 代款
			'flowNo' => $result['flowNo'],//决策引擎 API 接口返回的 flowNo 值
			'bizResult' => $bizResult,//success/fail 
		];
		$url = $this->upload_request_url;
		$result = $this->curlPost($url,$post_parms);
		$result = json_decode($result,true);
		return $result;
	}


    //post请求
    private function curlPost($url, $postFields)
    {
        $postFields = json_encode($postFields);
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
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
