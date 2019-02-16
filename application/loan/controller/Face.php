<?php
/**
 * Created by PhpStorm.
 * User: andy.deng
 * Date: 2018/5/11
 * Time: 14:00
 */

namespace app\loan\controller;

use think\Db;
use think\Config;
class Face extends Common
{
	private $app_key = null;
	private $app_secret = null;
	private $app_token_url = 'https://api.megvii.com/faceid/v3/sdk/get_biz_token';
	private $app_verify_url = 'https://api.megvii.com/faceid/v3/sdk/verify';
	private $app_ocr_url = 'https://api.megvii.com/faceid/v3/ocridcard';

    //订单类接口
    public function __construct()
    {
        parent::__construct();
		$config = Config::get('auth_'.$this->env.'.FACE');
		$this->app_key = $config['app_key'];
		$this->app_secret = $config['app_secret'];
    }

    //获取token
    public function assay_get_token(){
    	//$this->check_login();
        $user_id  = request()->post('user_id', 0, 'intval');
        $order_count = Db::name('order_info')->where(array('user_id' => $user_id, 'order_status' => array('neq', 200)))->count();
        if ($order_count > 0) {
           return json(['status' => '500', 'message' => '您已存在进行中的账单！', 'data' => []]);
        }
        //查询用户已做活体次数
        $assay_num = Db::name('face_log')->where(['user_id'=>$user_id,'result'=>['gt',0]])->count();
        if($assay_num>=3){
            return json(['status' => '500', 'message' => '活体验证次数达到上限！', 'data' => []]);
        }

    	$face_card  = Db::name('users')->where('user_id', $user_id)->value('face_card');
    	if(!$face_card){
    		return json(['status' => '500', 'message' => '请上传身份证照片！', 'data' => []]);
    	}

    	$oss_config = config('auth_' . $this->env . '.OSS');
	    $face_card  = get_oss_image($oss_config, $face_card);
	    $image_content = file_get_contents($face_card);
	    $local_idcard_temp_image =  './Uploads' . DS . 'user'. DS . $user_id . '_photo_idcard.jpg';
	    //保存到本地
	    file_put_contents($local_idcard_temp_image, $image_content);

	   	$sign = $this->_get_sign($this->app_key,$this->app_secret);
	   	//获取图片的二进制文件流
	    $imageContent = fread(fopen($local_idcard_temp_image, 'rb'), filesize($local_idcard_temp_image));
	   	//删除临时文件
	    $r = @unlink($local_idcard_temp_image);
	    //组装相关参数
	    $params = array(
	        'sign' => $sign,
	        'sign_version' => 'hmac_sha1', 
	        'liveness_type' => 'meglive',//meglive：动作活体 still：静默活体
	        'comparison_type' => '0',//“有源比对”或“无源比对”。取值只为“1”或“0”
	        'uuid' => $user_id,
	        'image_ref1";filename="image' => $imageContent,//本地身份证图片的二进制文件流
	        'liveness_timeout' => '60',//用户进入活体识别流程后的超时时间
	        'liveness_action_count' => '3',//默认3个动作
	        'verbose' => '1',//0：默认值，仅返回结论 1：返回结论与摘要信息
	        'security_level' => '2',//1：宽松 2：常规（默认值）3：严格 4：非常严格
	        'force_compare' => '0',//0：默认值，不开启比对。 1：进行强制比对。云端判断为假脸后，但依然进行比对
	        'multi_oriented_detection' => '0',//当检测不出人脸时，是否旋转90度、180度、270度后再检测人脸。本参数取值只能是 “1” 或 "0"
	    );
	    $result_json = $this->_curl_post($this->app_token_url,$params);
	    $result = json_decode($result_json,true);

	    if(isset($result['biz_token'])){
	        $biz_token = $result['biz_token'];
	    }else{
	        $biz_token = '';
	    }

	    //文件流不能保存为json格式，所以这里保存文件oss上地址
	    $params['image_ref1";filename="image'] = $face_card;
	    //加入日志
	    $log_data = [
	        'user_id' => $user_id,
	        'biz_token' => $biz_token,
	        'token_request_data' => json_encode($params),
	        'token_return_data' => $result_json,
	        'token_date' => date('Y-m-d H:i:s'),
	    ];
	    Db::name('face_log')->insert($log_data);
	    if(isset($result['biz_token'])){
	       	return json(['status' => '200', 'message' => '获取成功！', 'data' => ['biz_token'=>$biz_token]]);
	    }else{
	    	if($result['error']==='NO_FACE_FOUND'){
	    		$message = '身份证照片没有找到人脸！';
	    	}else{
	    		$message = '获取失败';
	    	}
	       	return json(['status' => '500', 'message' => $message, 'data' => '']);
	    }

    }

    //活体对比
    public function assay(){
    	$meglive_data = request()->file('meglive_data');
    	$user_id = request()->post('user_id');
    	$biz_token = request()->post('biz_token');

    	//包存本地临时数据包
    	$image_save_path = './Uploads' . DS . 'user';
        $meglive_data_info = $meglive_data->move($image_save_path, $user_id . '_photo_assay_meglive_data.txt');
        $meglive_data_url = $image_save_path . DS . $meglive_data_info->getSaveName();
    	
    	//获取签名
	    $sign = $this->_get_sign($this->app_key,$this->app_secret);
	    $imageContent = fread(fopen($meglive_data_url, 'rb'), filesize($meglive_data_url));
	    //组装相关参数
	    $params = array(
	        'sign' => $sign,
	        'sign_version' => 'hmac_sha1', 
	        'biz_token' => $biz_token,//通过”App-GetBizToken“ API接口获取到的biz_token
	        'meglive_data";filename="image' => $imageContent,//活体验证过程中的数据
	    );
	    $result_json = $this->_curl_post($this->app_verify_url,$params);
	    $result = json_decode($result_json,true);
	    //删除本地临时数据包
	    @unlink($meglive_data_url);
	    //file_put_contents('../face_back.txt', $result_json.PHP_EOL,FILE_APPEND);

	    if($result['result_code']===1000 || $result['result_code']===2000){
	    	$confidence = $result['verification']['ref1']['confidence'];
	    	//大于等于60分为通过
	    	if($confidence>=60){
	    		$res = 1;//通过
	    	}else{
	    		$res = 2;//不通过
	    	}

	    	//上传图片
	    	$best_image_url = $image_save_path . DS . $user_id . '_photo_assay_image_best.jpg';
	    	file_put_contents($best_image_url, base64_decode($result['images']['image_best']));
	    	$oss_config = config('auth_' . $this->env . '.OSS');
	    	$best_image_code = upload_oss_image($oss_config,$best_image_url);
	    	//删除本地最佳图片
	    	@unlink($best_image_url);
	    }else{
	    	$confidence = 0;
	    	$res = 2;//不通过
	    	$best_image_code = 0;
	    }

	    //加入日志
	    $log_data = [
	        'verify_request_data' => json_encode($params),
	        'verify_return_data' => $result_json,
	        'verify_date' => date('Y-m-d H:i:s'),
	        'confidence' => $confidence,
	        'best_image_code' => $best_image_code,
	        'result' => $res,
	    ];
	    Db::name('face_log')->where(['biz_token'=>$biz_token])->update($log_data);

	    if ($result['result_code']===1000 || $result['result_code']===2000) {
            $user_data = array(
                'scores_assay'       => $confidence,//比对结果的置信度，Float类型，取值［0，100］，数字越大表示两张照片越可能是同一个人。
                'pair_verify_result' => $res===1 ? 1:0,//Face 活体同人验证结果 1为同一个人  0为非同一个人
                'photo_assay'        => $best_image_code,// 活体图片
                'assay_time'         => time(),  //活体通过时间
            );
            //保存用户活体验证结果信息
            Db::name('users')->where('user_id', $user_id)->update($user_data);

            if($res===1){
            	return json(['status' => '200', 'message' => '验证通过', 'data' => '']);
            }else{
            	return json(['status' => '500', 'message' => '验证失败', 'data' => '']);
            }
        }else{
            return json(['status' => '500', 'message' => '验证失败', 'data' => '']);
        } 	


    }

    //ocr验证
    public function face_ocr($user_info){
    	$oss_config = config('auth_' . $this->env . '.OSS');
        $face_card  = get_oss_image($oss_config, $user_info['face_card']);
        $image_content = file_get_contents($face_card);
        $local_idcard_temp_image = './Uploads' . DS . 'user'. DS . $user_info['user_id'] . '_photo_idcard.jpg';
        //保存到本地
        file_put_contents($local_idcard_temp_image, $image_content);

    	$imageContent = fread(fopen($local_idcard_temp_image, 'rb'), filesize($local_idcard_temp_image));
    	//组装相关参数
	    $params = array(
	        'api_key' => $this->app_key,
	        'api_secret' => $this->app_secret, 
	        'image";filename="image' => $imageContent,
	        'return_portrait' => '0',//设定是否返回身份证上的人像
	    );
	    $result_json = $this->_curl_post($this->app_ocr_url,$params);
	    $result = json_decode($result_json,true);
	    //删除临时文件
	    @unlink($local_idcard_temp_image);

	    //写入日志
        $ocr_log = [
            'user_id' => $user_info['user_id'],
            'request_face_card' => $user_info['face_card'],
            'return_data' => $result_json,
            'add_date' => date('Y-m-d H:i:s'),
            'add_time' => time(),
        ];
        Db::name('ocr_log')->insert($ocr_log);
        if($this->is_open_mongo == true){
        	mongo_log('ocr_log', $ocr_log);
        }
        if((int)$result['result']===1001 || (int)$result['result']===1002){
        	//ocr验证信息
		    $ocr['name'] = $result['name']['result'];
		    $ocr['idcard_number'] = $result['idcard_number']['result'];
		    $ocr['gender'] = $result['gender']['result'];
		    $ocr['address'] = $result['address']['result'];
		   	if($user_info['name']===$ocr['name'] && $user_info['idcode']===$ocr['idcard_number']){
	    		$ocr['result'] = true;
	    	}else{
	    		$ocr['result'] = false;
	    	}
	    }else{	
	    	$ocr['result'] = false;
	    }
	   	return $ocr;
    }


    //生成签名字符串
	private function _get_sign($apiKey, $apiSecret, $expired=3600){
	    $rdm = rand();
	    $current_time = time();
	    $expired_time = $current_time + $expired;
	    $srcStr = "a=%s&b=%d&c=%d&d=%d";
	    $srcStr = sprintf($srcStr, $apiKey, $expired_time, $current_time, $rdm);
	    $sign = base64_encode(hash_hmac('SHA1', $srcStr, $apiSecret, true).$srcStr);
	    return $sign;
	}

	//post发送数据
	private function _curl_post($url,$data){
		$curl = curl_init();
	    curl_setopt_array($curl, array(
	            CURLOPT_URL            => $url,
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_ENCODING       => "",
	            CURLOPT_MAXREDIRS      => 10,
	            CURLOPT_TIMEOUT        => 30,
	            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
	            CURLOPT_CUSTOMREQUEST  => "POST",
	            CURLOPT_POSTFIELDS     => $data,
	            CURLOPT_HTTPHEADER     => array("cache-control: no-cache"),
	        )
	    );
	    $response = curl_exec($curl);
	    $err      = curl_error($curl);
	    curl_close($curl);
	    if ($err) {
	        return "cURL Error #:" . $err;
	    } else {
	        return $response;
	    }
	}






}