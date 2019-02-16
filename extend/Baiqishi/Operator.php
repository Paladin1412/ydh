<?php
/*
 *白骑士-运营商接口类
 *By:somnus  
 *Time:2018-09-28 08:33 
 * */
namespace Baiqishi;

use think\Config;
use think\Db;
class Operator{
	protected $partnerid = null;
	protected $verifykey = null;
	protected $appid = null;
	protected $app_site = null;

	public function __construct($env){
		$config_key = 'auth_'.$env.'.BAIQISHI';
		$config = Config::get($config_key);
		$this->partnerid = $config['partnerid'];
		$this->verifykey = $config['verifykey'];
		$this->appid = $config['appid'];
		$this->app_site = Config::get('auth_'.$env.'.APP_SITE');
	}


	//第一次请求登入接口
	public function login($post_parms){
		$post_parms['partnerId'] = $this->partnerid;
		$url = 'https://credit.baiqishi.com/clweb/api/mno/login';
		$result = $this->curlPost($url,$post_parms);

		$log_data = [
			'name' => $post_parms['name'],
			'certno' => $post_parms['certNo'],
			'mobile' => $post_parms['mobile'],
			'pwd' => $post_parms['pwd'],
			'add_time' => time(),
		];
		$log_data['login_request_data'] = json_encode($post_parms);
		$log_data['login_back_data'] = json_encode($result);
		$log_data['reqid'] = $result['data']['reqId'];
		if($result['resultCode']==='CCOM1000'){
			$log_data['result'] = 1;
		}
		//记录请求日志
		Db::name('operator_auth_log')->where(['mobile'=>$post_parms['mobile']])->delete();
		Db::name('operator_auth_log')->insert($log_data);
		return $result;
	}

	//第二次请求登入接口
	public function login2($post_parms){
		$post_parms['partnerId'] = $this->partnerid;
		$url = 'https://credit.baiqishi.com/clweb/api/mno/login';
		$result = $this->curlPost($url,$post_parms);

		//记录请求日志
		$log_data['login2_request_data'] = json_encode($post_parms);
		$log_data['login2_back_data'] = json_encode($result);
		if($result['resultCode']==='CCOM1000'){
			$log_data['result'] = 1;
		}
		Db::name('operator_auth_log')->where(['mobile'=>$post_parms['mobile']])->update($log_data);
		return $result;
	}

	//校验二次鉴权短信验证码
	public function verifyauthsms(){
		$post_parms = [
			'reqId' => $this->partnerid,//任务 Id【来源于上一次请求结果中的 data 字段】
			'smsCode' => 'success',//二次鉴权验证码【来源于用户收到的短信验证码】
		];
		$url = 'https://credit.baiqishi.com/clweb/api/mno/verifyauthsms';
		$result = $this->curlPost($url,$post_parms);
		//记录请求日志
		$log_data['auth_request_data'] = json_encode($post_parms);
		$log_data['auth_back_data'] = json_encode($result);
		if($result['resultCode']==='CCOM1000'){
			$log_data['result'] = 1;
		}
		Db::name('operator_auth_log')->where(['mobile'=>$post_parms['mobile']])->update($log_data);
		return $result;
	}

	//重发登录短信验证码 
	public function sendloginsms($post_parms){
		$url = 'https://credit.baiqishi.com/clweb/api/mno/sendloginsms';
		$result = $this->curlPost($url,$post_parms);
		return $result;
	}

	//重发二次鉴权短信验证码
	public function sendauthsms($post_parms){
		$url = 'https://credit.baiqishi.com/clweb/api/mno/sendauthsms';
		$result = $this->curlPost($url,$post_parms);
		return $result;
	}

	//用户主动调用-查询原始数据(此接口需要用户正式环境的verifyKey)
	public function getoriginal($post_parms){
		$post_parms['partnerId'] = $this->partnerid;
		$post_parms['verifyKey'] = $this->verifykey;
		$url = 'https://credit.baiqishi.com/clweb/api/mno/getoriginal';
		$result = $this->curlPost($url,$post_parms);
		return $result;
	}

	//资讯云查询报告数据
	public function getreport($post_parms){
		$post_parms['partnerId'] = $this->partnerid;
		$post_parms['verifyKey'] = $this->verifykey;
		$url = 'https://credit.baiqishi.com/clweb/api/common/getreport';
		$result = $this->curlPost($url,$post_parms);
		return $result;
	}


    //h5接入,不传用户信息
    public function h5_no_userinfo($user_info){
		$url = 'https://credit.baiqishi.com/clclient/mno/login';
		$url .= '?partnerId='.$this->partnerid;
		$url .= '&name='.urlencode($user_info['name']);
		$url .= '&certNo='.$user_info['idcode'];
		$url .= '&mobile='.$user_info['phone'];
		$url .= '&backUrl='.$this->app_site.'/loan/Operators/h5_backurl';
		$url .= '&failUrl='.$this->app_site.'/loan/Operators/h5_failurl';
		$url .= '&pageConfig=';
		$url .= '&skip=false';
		return $url;
	}

	//获取token
    public function get_token($post_parms){
    	$post_parms['partnerId'] = $this->partnerid;
		$post_parms['verifyKey'] = $this->verifykey;
		$post_parms['certNo'] = $post_parms['certNo'];
		$url = 'https://credit.baiqishi.com/clweb/api/common/gettoken';
		$result = $this->curlPost($url,$post_parms);
		return $result;
    }

    //获取资信云报报告
    public function getreportpage($post_parms){
    	$post_parms['partnerId'] = $this->partnerid;
    	$param_url = http_build_query($post_parms);
		$url = 'https://credit.baiqishi.com/clweb/api/common/getreportpage?'.$param_url;
		$result = $this->curl_get($url);
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
        return json_decode($result,true);
    }

    //curl get方式请求
    public function curl_get($url){
    	//初始化
	    $curl = curl_init();
	    //设置抓取的url
	    curl_setopt($curl, CURLOPT_URL, $url);
	    //设置头文件的信息作为数据流输出
	    curl_setopt($curl, CURLOPT_HEADER, 0);
	    //设置获取的信息以文件流的形式返回，而不是直接输出。
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    //执行命令
	    $data = curl_exec($curl);
	    //关闭URL请求
	    curl_close($curl);
	    //显示获得的数据
	    return $data;
    }
}
