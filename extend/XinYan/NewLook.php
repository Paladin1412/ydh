<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/30
 * Time: 15:30
 */

namespace XinYan;

use think\Config;
use think\Db;
use Redis\redisServer;
use XinYan\EncryptUtil;
class NewLook{

    protected $member_id = null;
    protected $terminal_id = null;//ase终端号
    protected $aes_key = null;  //AES加密key
    protected $xwld_url = null;
    protected $fmlh_url = null;
    protected $fmxb_url = null;
    protected $pfx_pwd = null; //RSA加密私钥密码
    protected $rsa_member_id = null; //rsa加密商户号
    protected $rsa_terminal_id = null; //rsa终端号
    protected $pri_key_name = null;//私钥文件名称
    const ADVANCE_ORDER = 'https://dfp.xinyan.com/gateway/dfpas/blackMirror/v1/queryBlackMirror';//预订单号获取接口地址
    const DEVICE_QUERY = 'https://dfp.xinyan.com/gateway/device-engine-query/deviceBlack/v2/queryDeviceAndBlack';//黑镜设备信息查询接口
    const IV = 'DEVICE-AES000000';

    public function __construct($env){
        $config_key = 'auth_'.$env.'.XIN_YAN';
        $config = Config::get($config_key);
        $this->member_id = $config['member_id'];
        $this->terminal_id = $config['terminal_id'];
        $this->aes_key = $config['aes_key'];
        $this->xwld_url = $config['xwld_url'];
        $this->fmlh_url = $config['fmlh_url'];
        $this->fmxb_url = $config['fmxb_url'];
        $this->pfx_pwd = $config['pfx_pwd'];
        $this->rsa_member_id = $config['rsa_member_id'];
        $this->rsa_terminal_id = $config['rsa_terminal_id'];
        $this->pri_key_name = $config['pri_key_name'];
    }

    //获取新颜黑镜信息
    public function get_black_mirror($token,$id_card,$phone,$user_name,$order_no,$is_open_mongo){
        $url = self::ADVANCE_ORDER;
        $data_content = [
            'token' => $token,
            'trans_id' => make_sn().md5(uniqid()),  //商户订单号
            'trade_date' => date('YmdHis'),
            'industry_type' => 'B19',
            'id_no' => $id_card,
            'id_name' => $user_name,
            'phone_no' => $phone,
            'versions' => '1.3.0',
        ];
        $aes_data_content = $this->encryptNew(json_encode($data_content), $this->aes_key, self::IV);
        $post_parms = [
            'member_id' => $this->member_id,
            'terminal_id' =>  $this->terminal_id,
            'encryption_type' => 'AES',
            'data_type' => 'json',
            'data_content' => $aes_data_content,
        ];
        $contentType = array(
            'Content-Type: application/json;charset=utf-8'
        );
        $result_json = $this->curlPost($url,$post_parms,$contentType);
        $result = json_decode($result_json,true);
        //halt($result);
        //存入日志
        $sms_log = [
            'order_no' => $order_no,
            'resInfo' => $result,
            'result_json' => $result_json,
            'date' => date('Y-m-d H:i:s'),
        ];
        trace($order_no.'新颜黑镜返回信息'.$result_json);
        if($is_open_mongo == true) mongo_log('xinyan_hj_log', $sms_log);

        if($result['success'] == false || $result['data']['code'] == 9){
            $msg = '新颜黑镜返回数据异常，验证不通过';
            $this->save_order($order_no,$msg,2);
            return false;
        }

        //将返回信息插入到数据库展示
        $resAddinfo = $this->addresHeiJing($result, $order_no);
        if(empty($resAddinfo)){
            $msg = '新颜黑镜返回信息插入数据库失败';
            $this->save_order($order_no,$msg,2);
            return false;
        }

        //添加风控日志
        $risk_log = [
            'order_no' => $order_no,
            'risk_type' => 2,
            'result' => 1,
            'desc' => '新颜黑镜验证通过',
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);
        return true;

    }

    //获取新颜行为雷达信息
    public function get_behavioral_radar_info($id_card,$phone,$user_name,$order_no,$is_open_mongo){
        $url = $this->xwld_url;
        $pfx_pwd = $this->pfx_pwd;
        $pfxpath = EXTEND_PATH.'XinYan'.DS.$this->pri_key_name;
        $data_content = [
            'member_id' => $this->rsa_member_id,
            'terminal_id' =>  $this->rsa_terminal_id,
            'trans_id' => make_sn().md5(uniqid()),  //商户订单号
            'trade_date' => date('YmdHis'),
            'industry_type' => 'B19',
            'id_no' => md5($id_card),
            'id_name' => md5($user_name),
            'phone_no' => md5($phone),
            'versions' => '1.3.0',
        ];
        $data_content = str_replace("\\/", "/", json_encode($data_content));//转JSON
        $encryptUtil = new EncryptUtil($pfxpath, "", $pfx_pwd, TRUE); //实例化加密类。
        $rsa_data_content = $encryptUtil->encryptedByPrivateKey($data_content);
        $post_parms = [
            'member_id' => $this->rsa_member_id,
            'terminal_id' => $this->rsa_terminal_id,
            'data_type' => 'json',
            'data_content' => $rsa_data_content,
        ];
        $contentType = array(
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
        );
        //halt($post_parms);
        $result_json = $this->curlPost_new($url,$post_parms,$contentType);
        $result = json_decode($result_json,true);
        //halt($result);
        //存入日志
        $sms_log = [
            'order_no' => $order_no,
            'resInfo' => $result,
            'result_json' => $result_json,
            'date' => date('Y-m-d H:i:s'),
        ];
        trace($order_no.'新颜行为雷达返回信息'.$result_json);
        if($is_open_mongo == true) mongo_log('xinyan_xwld_log', $sms_log);

        if($result['success'] == false || $result['data']['code'] == 9){
            $msg = '新颜行为雷达返回数据异常，验证不通过';
            $this->save_order($order_no,$msg,5);
            return false;
        }

        if($result['data']['code'] == 0){
            if($result['data']['result_detail']['loans_overdue_count'] > 5 || $result['data']['result_detail']['loans_score'] < 500 || $result['data']['result_detail']['loans_credibility'] < 70 || $result['data']['result_detail']['history_fail_fee'] > 20 || $result['data']['result_detail']['latest_one_month_fail'] > 10){
                $msg = '新颜行为雷达验证不通过';
                $this->save_order($order_no,$msg,5);
                return false;
            }
        }

        //将返回信息插入到数据库展示
        $resAddinfo = $this->addresXwld($result, $order_no);
        if(empty($resAddinfo)){
            $msg = '新颜行为雷达返回信息插入数据库失败';
            $this->save_order($order_no,$msg,5);
            return false;
        }

        //添加风控日志
        $risk_log = [
            'order_no' => $order_no,
            'risk_type' => 5,
            'result' => 1,
            'desc' => '新颜行为雷达验证通过',
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);

        return true;

    }

    //添加新颜行为雷达返回结果
    public function addresXwld($result,$order_no){
        $add_data = [];
        if($result['data']['code'] == 0){
            $add_data = $result['data']['result_detail'];
        }
        $add_data['order_no'] = $order_no;
        $add_data['trans_id'] = $result['data']['trans_id'];
        $add_data['trade_no'] = $result['data']['trade_no'];
        $add_data['desc'] = $result['data']['desc'];
        $add_data['fee'] = $result['data']['fee'];
        $add_data['add_time'] = time();
        return Db::name('order_risk_xwld')->insert($add_data);
    }

    //添加新颜黑镜返回结果
    public function addresHeiJing($result,$order_no){
        $add_data = [];
        if($result['data']['code'] == 0 && !empty($result['data']['device_result_detail'])){
            $add_data = array_merge($result['data']['result_detail'], $result['data']['device_result_detail']);
        }elseif ($result['data']['code'] == 0 && empty($result['data']['device_result_detail'])){
            $add_data = $result['data']['result_detail'];
        }elseif ($result['data']['code'] != 0 && !empty($result['data']['device_result_detail'])){
            $add_data = $result['data']['device_result_detail'];
        }elseif ($result['data']['code'] != 0 && empty($result['data']['device_result_detail'])){
            $add_data = [];
        }
        $add_data['order_no'] = $order_no;
        $add_data['trans_id'] = $result['data']['trans_id'];
        $add_data['trade_no'] = $result['data']['trade_no'];
        $add_data['desc'] = $result['data']['desc'];
        $add_data['fee'] = $result['data']['fee'];
        $add_data['add_time'] = time();
        return Db::name('order_risk_heijing')->insert($add_data);
    }

    //更改订单状态，添加风控日志
    public function save_order($order_no,$msg,$hit_type){
        $update_data = [
            'risk_status'=>2,//风控不通过
            'order_status'=>110,//审批不通过
            'refuse_time'=>time(),//风控审核时间
            'hit_type'=>$hit_type,//风控命中类型
        ];
        Db::name('order_info')->where('order_no',$order_no)->update($update_data);
        //添加风控日志
        $risk_log = [
            'order_no' => $order_no,
            'risk_type' => $hit_type,
            'result' => 0,
            'desc' => $msg,
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);
    }

    //post请求
    private function curlPost($url, $postFields, $contentType)
    {
        $postFields = json_encode($postFields);
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

    //post请求
    private function curlPost_new($url, $postFields, $contentType)
    {
        $postFields = http_build_query($postFields);//格式化参数
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

    /**
     * AES/CBC/PKCS5Padding Encrypter
     *
     * @param $str
     * @param $key
     * @param $iv
     * @return string
     */
    public function encryptNew($str, $key,$iv)
    {
        return base64_encode(openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv));
    }
    /**
     * AES/CBC/PKCS5Padding Decrypter
     *
     * @param $encryptedStr
     * @param $key
     * @param $iv
     * @return string
     */
    public function decryptNew($encryptedStr,$key,$iv)
    {
        return openssl_decrypt(base64_decode($encryptedStr), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    //获取新颜负面拉黑信息
    public function get_negative_pull_black_info($id_card,$phone,$user_name,$order_no,$is_open_mongo){
        $url = $this->fmlh_url;
        $pfx_pwd = $this->pfx_pwd;
        $pfxpath = EXTEND_PATH.'XinYan'.DS.$this->pri_key_name;
        $data_content = [
            'member_id' => $this->rsa_member_id,
            'terminal_id' =>  $this->rsa_terminal_id,
            'trans_id' => make_sn().md5(uniqid()),  //商户订单号
            'trade_date' => date('YmdHis'),
            'industry_type' => 'B19',
            'id_no' => md5($id_card),
            'id_name' => md5($user_name),
            'phone_no' => md5($phone),
            'versions' => '1.3.0',
        ];
        $data_content = str_replace("\\/", "/", json_encode($data_content));//转JSON
        $encryptUtil = new EncryptUtil($pfxpath, "", $pfx_pwd, TRUE); //实例化加密类。
        $rsa_data_content = $encryptUtil->encryptedByPrivateKey($data_content);
        $post_parms = [
            'member_id' => $this->rsa_member_id,
            'terminal_id' => $this->rsa_terminal_id,
            'data_type' => 'json',
            'data_content' => $rsa_data_content,
        ];
        $contentType = array(
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
        );
        $result_json = $this->curlPost_new($url,$post_parms,$contentType);
        $result = json_decode($result_json,true);
        //halt($result);
        //存入日志
        $sms_log = [
            'order_no' => $order_no,
            'resInfo' => $result,
            'result_json' => $result_json,
            'date' => date('Y-m-d H:i:s'),
        ];
        trace($order_no.'新颜负面拉黑返回信息'.$result_json);
        if($is_open_mongo == true) mongo_log('xinyan_fmlh_log', $sms_log);

        if($result['success'] == false || $result['data']['code'] == 9){
            $msg = '新颜负面拉黑返回数据异常，验证不通过';
            $this->save_order($order_no,$msg,6);
            return false;
        }

        //将返回信息插入到数据库展示
        $resAddinfo = $this->addresFmlh($result, $order_no);
        if(empty($resAddinfo)){
            $msg = '新颜负面拉黑返回信息插入数据库失败';
            $this->save_order($order_no,$msg,6);
            return false;
        }

        //风控通过，进入信审
        $newRes = $this->new_save_order($order_no, '新颜负面拉黑验证通过');
        if(empty($newRes)){
            $msg = '风控审批通过,订单状态更改失败';
            $this->save_order($order_no,$msg,6);
            return false;
        }

        //添加风控日志
        /*$risk_log = [
            'order_no' => $order_no,
            'risk_type' => 6,
            'result' => 1,
            'desc' => '新颜负面拉黑验证通过',
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);*/

        return true;

    }

    //添加新颜负面拉黑返回结果
    public function addresFmlh($result,$order_no){
        $add_data = [];
        if($result['data']['code'] == 0 || $result['data']['code'] == 1){
            $add_data = $result['data']['result_detail'];
        }
        $add_data['order_no'] = $order_no;
        $add_data['trans_id'] = $result['data']['trans_id'];
        $add_data['trade_no'] = $result['data']['trade_no'];
        $add_data['desc'] = $result['data']['desc'];
        $add_data['fee'] = $result['data']['fee'];
        $add_data['add_time'] = time();
        return Db::name('order_risk_fmlh')->insert($add_data);
    }

    //获取新颜负面洗白信息
    public function get_negative_wash_info($id_card,$phone,$user_name,$order_no,$is_open_mongo){
        $url = $this->fmxb_url;
        $pfx_pwd = $this->pfx_pwd;
        $pfxpath = EXTEND_PATH.'XinYan'.DS.$this->pri_key_name;
        $data_content = [
            'member_id' => $this->rsa_member_id,
            'terminal_id' =>  $this->rsa_terminal_id,
            'trans_id' => make_sn().md5(uniqid()),  //商户订单号
            'trade_date' => date('YmdHis'),
            'industry_type' => 'B19',
            'id_no' => md5($id_card),
            'id_name' => md5($user_name),
            'phone_no' => md5($phone),
            'versions' => '1.3.0',
        ];
        $data_content = str_replace("\\/", "/", json_encode($data_content));//转JSON
        $encryptUtil = new EncryptUtil($pfxpath, "", $pfx_pwd, TRUE); //实例化加密类。
        $rsa_data_content = $encryptUtil->encryptedByPrivateKey($data_content);
        $post_parms = [
            'member_id' => $this->rsa_member_id,
            'terminal_id' => $this->rsa_terminal_id,
            'data_type' => 'json',
            'data_content' => $rsa_data_content,
        ];
        $contentType = array(
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8'
        );
        $result_json = $this->curlPost_new($url,$post_parms,$contentType);
        $result = json_decode($result_json,true);
        //halt($result);
        //存入日志
        $sms_log = [
            'order_no' => $order_no,
            'resInfo' => $result,
            'result_json' => $result_json,
            'date' => date('Y-m-d H:i:s'),
        ];
        trace($order_no.'新颜负面洗白返回信息'.$result_json);
        if($is_open_mongo == true) mongo_log('xinyan_fmxb_log', $sms_log);

        if($result['success'] == false || $result['data']['code'] == 9){
            $msg = '新颜负面洗白返回数据异常，验证不通过';
            $this->save_order($order_no,$msg,6);
            return false;
        }

        //将返回信息插入到数据库展示
        $resAddinfo = $this->addresFmxb($result, $order_no);
        if(empty($resAddinfo)){
            $msg = '新颜负面洗白返回信息插入数据库失败';
            $this->save_order($order_no,$msg,6);
            return false;
        }

        //风控通过，进入信审
        $newRes = $this->new_save_order($order_no, '新颜负面洗白验证通过');
        if(empty($newRes)){
            $msg = '风控审批通过,订单状态更改失败';
            $this->save_order($order_no,$msg,6);
            return false;
        }

        return true;

    }

    //更改订单状态，添加风控日志
    public function new_save_order($order_no,$msg){
        //添加风控日志
        $risk_log = [
            'order_no' => $order_no,
            'risk_type' => 6,
            'result' => 1,
            'desc' => $msg,
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);
        $update_data = [
            'risk_status'=>1,//风控通过
            'order_status'=>90,//审批中
            'refuse_time'=>time(),//风控审核时间
        ];
        return Db::name('order_info')->where('order_no',$order_no)->update($update_data);
    }

    //添加新颜负面拉黑返回结果
    public function addresFmxb($result,$order_no){
        $add_data = [];
        if($result['data']['code'] == 0 || $result['data']['code'] == 1 || $result['data']['code'] == 2){
            $add_data = $result['data']['result_detail'];
        }
        $add_data['order_no'] = $order_no;
        $add_data['trans_id'] = $result['data']['trans_id'];
        $add_data['trade_no'] = $result['data']['trade_no'];
        $add_data['desc'] = $result['data']['desc'];
        $add_data['fee'] = $result['data']['fee'];
        $add_data['add_time'] = time();
        return Db::name('order_risk_fmxb')->insert($add_data);
    }



}
