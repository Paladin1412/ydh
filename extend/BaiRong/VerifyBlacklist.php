<?php
/**
 * 百融黑名单
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 17:29
 */
namespace BaiRong;

use think\Config;
use think\Db;
use Redis\redisServer;
class VerifyBlacklist{

    protected $user_name = null;
    protected $password = null;
    protected $api_code = null;
    protected $login_url = null;
    const STRATEGY_URL = 'https://api.100credit.cn/strategyApi/v1/hxQuery';

    public function __construct($env){
        $config_key = 'auth_'.$env.'.VERIFY_BLACK';
        $config = Config::get($config_key);
        $this->user_name = $config['user_name'];
        $this->password = $config['password'];
        $this->api_code = $config['api_code'];
        $this->login_url = $config['login_url'];
    }

    //获取tokenid
    public function get_tokenid($order_no){
        $url = $this->login_url;
        $post_parms = 'userName='.$this->user_name.'&password='.$this->password.'&apiCode='.$this->api_code;
        $contentType = array(
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
        );
        $result = $this->curlPost($url,$post_parms,$contentType);
        $result = json_decode($result,true);
        if($result['code'] == 00){
            redisServer::getInstance()->set('bairong_tokenid', $result['tokenid'], 50 * 60);
            return ['status' => '1', 'tokenid' => $result['tokenid']];
        }else{
            trace($order_no.'获取百融tokenid失败'.json_encode($result));
            $msg = '获取百融tokenid失败';
            $this->save_order($order_no,$msg);
            return ['status' => '0', 'tokenid' => ''];
        }
    }

    //获取百融黑名单验证结果
    public function get_check_bairong($tokenid,$id_card,$phone,$user_name,$order_no,$is_open_mongo){
        $url = self::STRATEGY_URL;
        $jsonData = json_encode([
            'id' => $id_card,
            'cell' => $phone,
            'strategy_id' => 'STR0002733',
            'name' => $user_name
        ]);
        //数据策略 SpecialList_c
        //规则策略 STR0002735
        $checkCode = md5($jsonData.md5($this->api_code.$tokenid));
        $post_parms = 'tokenid='.$tokenid.'&jsonData='.$jsonData.'&apiCode='.$this->api_code.'&checkCode='.$checkCode;
        $contentType = array(
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
        );
        $result_json = $this->curlPost($url,$post_parms,$contentType);
        $result = json_decode($result_json,true);
        //存入日志
        $sms_log = [
            'order_no' => $order_no,
            'resInfo' => $result,
            'result_json' => $result_json,
            'date' => date('Y-m-d H:i:s'),
        ];
        trace($order_no.'百融黑名单验证返回信息'.json_encode($result));
        if($is_open_mongo == true) mongo_log('bairong_log', $sms_log);
        //将返回信息插入到数据库展示
        $resAddinfo = $this->addresBaiRong($result, $order_no);
        if(empty($resAddinfo)){
            $msg = '百融返回信息插入数据库失败';
            $this->save_order($order_no,$msg);
            return false;
        }

        if(($result['code'] != 00 && $result['code'] != 100002) || $result['flag_rulespeciallist_c'] == 99 || $result['Rule_final_decision'] == 'Reject' || $result['Rule_final_decision'] == 'Review'){
            $msg = '百融黑名单验证不通过';
            $this->save_order($order_no,$msg);
            return false;
        }
        //添加风控日志
        $risk_log = [
            'order_no' => $order_no,
            'risk_type' => 1,
            'result' => 1,
            'desc' => '百融黑名单验证通过',
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);
        return true;

    }

    //更添加百融返回结果
    public function addresBaiRong($result,$order_no){
        $arr3_key3 = [];
        foreach ($result as $k1 => $v1){
            $key_arr = explode('_', $k1);
            if(!empty($key_arr[1])){
                if($key_arr[1] == 'name'){
                    $arr3_key3[] = $key_arr[2];
                }
            }
        }
        $rule = [];
        foreach ($result as $k2 => $v2){
            $key_arr2 = explode('_', $k2);
            if(!empty($key_arr2[2])){
                if(in_array($key_arr2[2], $arr3_key3)){
                    $rule[$key_arr2[2]][$key_arr2[0].'_'.$key_arr2[1]] = $v2;
                }
            }
        }

        //添加
        $risk_log = [
            'order_no' => $order_no,
            'flag_rulespeciallist_c' => $result['flag_rulespeciallist_c'],
            'Rule_final_decision' => $result['Rule_final_decision'],
            'Rule_final_weight' => $result['Rule_final_weight'],
            'rule' => json_encode($rule),
            'add_time' => time(),
        ];
        return Db::name('order_risk_beirong')->insert($risk_log);
    }

    //更改订单状态，添加风控日志
    public function save_order($order_no,$msg){
        $update_data = [
            'risk_status'=>2,//风控不通过
            'order_status'=>110,//审批不通过
            'refuse_time'=>time(),//风控审核时间
            'hit_type'=>1,//风控命中类型
        ];
        Db::name('order_info')->where('order_no',$order_no)->update($update_data);
        //添加风控日志
        $risk_log = [
            'order_no' => $order_no,
            'risk_type' => 1,
            'result' => 0,
            'desc' => $msg,
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);
    }


    //post请求
    private function curlPost($url, $postFields, $contentType)
    {
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $contentType);
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
