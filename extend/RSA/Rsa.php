<?php
/**
@author andy.deng
@description快捷支付加密类
**/
namespace RSA;

use think\Config;
use think\Db;
class Rsa
{
	private $filePath = null;//.pfx和.cer文件放置的地址 F:\\www\china_api\\extend\RSA\\///alidata/www/api_china_tupulian/extend/RSA/
	private $pfxFileName = "quickpay.pfx";//.pfx文件名
	private $password = "tupulian2018";//.pfx文件的密码
	private $cerFileName = "helipay.cer";//.cer文件名
	private $private_key_file = null;
	private $public_key_file = null;

	public function __construct(){
		if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
			$this->filePath = 'F:\\www\china_api\\extend\RSA\\';
		}else{
			$this->filePath = '/alidata/www/api_china_tupulian/extend/RSA/';
		}
		/*实现.pfx文件转为.pem文件*/
		$file = $this->filePath.$this->pfxFileName;
		$results = array();
		$worked = openssl_pkcs12_read(file_get_contents($file), $results, $this->password);
		$certificateCApem = $file.'.pem';
		$this->private_key_file = $certificateCApem;
		//@file_put_contents($certificateCApem, $results);

		/*实现.cer文件转为.pem文件*/
		$certificateCAcer = $this->filePath.$this->cerFileName;
		$certificateCAcerContent = file_get_contents($certificateCAcer);
		$certificateCApem=$this->filePath.$this->cerFileName.'.pem';
		$this->public_key_file = $certificateCApem;
		//file_put_contents($certificateCApem,$certificateCAcerContent);
	}
    
    /*实现加签功能*/ //MD5WITHRSA
	function genSign($data) {
		$priKey = file_get_contents($this->private_key_file);
		$res = openssl_get_privatekey($priKey);
		openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}

    /*实现加签功能*/ //MD5WITHRSA
	function check_sign($data) {
		$pubKey = file_get_contents($this->public_key_file);
		$res = openssl_pkey_get_public($pubKey);
		openssl_sign($data, $sign, $res, OPENSSL_ALGO_MD5);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}

	/*实现公钥加密功能*/
	function rsaEnc($keyStr){
		$res = file_get_contents($this->public_key_file);
		$public_key= openssl_pkey_get_public($res);
		openssl_public_encrypt(str_pad($keyStr, 256, "\0", STR_PAD_LEFT), $encrypted, $public_key, OPENSSL_NO_PADDING);
        $jiami = base64_encode($encrypted);
        return $jiami;
	}


	//AES加密
	function aes_encrypt($text,$key){
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
		$padding = $size - (strlen($text) % $size);
		$phone_padding = $text . str_repeat(chr($padding), $padding);

		mcrypt_generic_init($td, $key, $iv);
		$cyper_text = mcrypt_generic($td, $phone_padding);
		$result = base64_encode($cyper_text);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $result;
	}
}
