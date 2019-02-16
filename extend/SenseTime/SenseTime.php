<?php
/*
 *		商汤验证接口类
 * 		By:somnus  
 * 		Time:2016-12-15 21:33 
 * */
namespace SenseTime;

use think\Config;
class SenseTime{

	protected $API_KEY = null;
	protected $API_SECRET = null;

	public function __construct($env){
		$config_key = 'auth_'.$env.'.SENSE_TIME';
		$st_config = Config::get($config_key);
		$this->API_KEY = $st_config['api_key'];
		$this->API_SECRET = $st_config['api_secret'];
	}


	public function compare($livenessPath,$photoPath){
	    $testurl = 'https://v2-auth-api.visioncloudapi.com/identity/image_verification/stateless'; // url
	    // dump(function_exists('curl_file_create'));
	    // exit;
	    $livenessContent = new \CURLFile($livenessPath);
	    $photoContent = new \CURLFile($photoPath);
	    // dump($livenessContent);
	    // dump($photoContent);
	    // exit;
	    $post_data = array ('first_image_file' => $livenessContent,'second_image_file' => $photoContent ,'auto_rotate'=>true);
	    $ch = curl_init();
	    //请将AUTHORIZATION替换为根据API_KEY和API_SECRET得到的签名认证串   
	    $Authorization = $this->author();
	    // echo $Authorization;
	    // exit;
	    //echo $Authorization;
	    $header[] = 'Authorization: '.$Authorization;
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($ch, CURLOPT_URL, $testurl);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
	    curl_setopt($ch, CURLOPT_POST,1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	    $output = curl_exec($ch);
	    curl_close($ch);
	    return $output;		
	}



	public function idcard_ocr($imagePath,$side){
	    $testurl = 'https://v2-auth-api.visioncloudapi.com/ocr/idcard/stateless'; 
	    $imageContent = new \CURLFile($imagePath);
	    $post_data = array (
	    	'image_file' => $imageContent,//图片的文件
	    	'auto_rotate' => true,//值为 true 时，对图片进行自动旋转
	    	'side' => $side,//idcard正反面，front表示正面，back表示背面， auto表示自动
	    	'classify' => true,//值为 true 时，返回身份证来源类型
	    	'return_score' => true,//值为 true 时，返回身份证各字段置信度
	    );

	    $ch = curl_init();
	    //请将AUTHORIZATION替换为根据API_KEY和API_SECRET得到的签名认证串   
	    $Authorization = $this->author();
	    // echo $Authorization;
	    // exit;
	    //echo $Authorization;
	    $header[] = 'Authorization: '.$Authorization;
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($ch, CURLOPT_URL, $testurl);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
	    curl_setopt($ch, CURLOPT_POST,1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	    $output = curl_exec($ch);
	    curl_close($ch);
	    return $output;		
	}




	public function author(){
		//生成nonce
		$nonce = $this->makeNonce(16);
		//生成unix 时间戳timestamp
		$timestamp = (string) time();
		//将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串stringSignature
		$stringSignature = $this->makeStringSignature($nonce,$timestamp,$this->API_KEY);
		//对stringSignature和API_SECRET做hamc-sha256 签名，生成signature
		$signature = $this->signString($stringSignature, $this->API_SECRET);
		//将签名认证字符串赋值给HTTP HEADER 的 Authorization 中
		$Authorization = "key=".$this->API_KEY.",timestamp=".$timestamp.",nonce=".$nonce.",signature=".$signature;
		return $Authorization;
		// echo "Authorization:";
		// echo "<br>";
		// echo($Authorization);
		// //exit;
		// $testurl = 'https://v2-auth-api.visioncloudapi.com/info/api';  
		// $ch = curl_init();
		// $header= array(                                                                          
		//   'Content-Type: application/json',                                                                                
		//   'Authorization: '.$Authorization
		//   );

		// curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		// curl_setopt($ch, CURLOPT_URL, $testurl); 
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		// //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
		// $output = curl_exec($ch);   
		// var_dump($output);   
		// $output_array = json_decode($output,true);
		// curl_close($ch); 
	}

	private function signString($string_to_sign, $API_SECRET) {
	    //对两个字符串做hamc-sha256 签名
	    return hash_hmac("sha256", $string_to_sign, $API_SECRET);
	}

	private function makeNonce( $length) {  
	    // 生成随机 nonce。位数可以自己定
	    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';  
	    $nonce = '';  
	    for ( $i = 0; $i < $length; $i++ )  {  
	        $nonce .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
	    }  
	    return $nonce; 
	} 

	private function makeStringSignature($nonce,$timestamp,$API_KEY){
	    //将timestamp、nonce、API_KEY 这三个字符串进行升序排列（依据字符串首位字符的ASCII码)，并join成一个字符串
	    $payload = array(
		    'API_KEY' => $API_KEY,
		    'nonce' => $nonce,
		    'timestamp' => $timestamp
	    );
	    //对首字母排序
	    sort($payload);
	    //join到一个字符串
	    $signature = join($payload);
	    return $signature;
	}

}
