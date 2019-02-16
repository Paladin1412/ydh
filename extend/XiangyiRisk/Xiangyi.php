<?php
/**
 * 翔一-风控策略接口类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 17:29
 */
namespace XiangyiRisk;

use think\Config;
class Xiangyi{

    protected $gateway_url = null;
    protected $merchantId = null;
    protected $productType = null;

    public function __construct($env){
        $config_key = 'auth_'.$env.'.XIANGYI';
        $config = Config::get($config_key);
        $this->gateway_url = $config['gateway_url'];
        $this->merchantId = $config['merchantId'];
        $this->productType = $config['productType'];
    }

    //风险决策api
    public function decision($post_parms){
        $url = $this->gateway_url;
        $post_parms['merchantId'] = $this->merchantId;
        $post_parms['productType'] = $this->productType;
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
