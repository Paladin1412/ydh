<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 发起异步执行任务
 * @param $url
 * @param array $param
 */
function do_request($url, $param = array())
{
    $url_info = parse_url($url);
    $host     = $url_info['host'];
    $path     = $url_info['path'];
    $query    = isset($param) ? http_build_query($param) : '';
    $port     = 80;
    $err_no   = 0;
    $err_str  = '';
    $timeout  = 10;
    $fp       = fsockopen($host, $port, $err_no, $err_str, $timeout);
    $out      = "POST " . $path . " HTTP/1.1\r\n";
    $out      .= "host:" . $host . "\r\n";
    $out      .= "content-length:" . strlen($query) . "\r\n";
    $out      .= "content-type:application/x-www-form-urlencoded\r\n";
    $out      .= "connection:close\r\n\r\n";
    $out      .= $query;
    fputs($fp, $out);
    usleep(300000);
    fclose($fp);
}



/**
 * 生成18位不重复订单号
 * @return string
 */
function make_sn()
{
    return (date('y') + date('m') + date('d')) . date('his') . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
}

//检验中文名格式
function check_china_name($name){
    $check = preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $name);
    if($check){
        return true;
    }else{
        return false;
    }
}




/**
 * 密码正则
 * @param $candidate
 * @return bool
 */
function valid_pass($candidate)
{
    $r1 = '/[a-zA-Z]/';  //uppercase
    $r3 = '/[0-9]/';  //numbers
    if (preg_match_all($r1, $candidate, $o) < 1) {
        return 1;//json(array('status' => '500', 'message' => lang('reg_pwd_1')));//密码必须包含至少一个字母
    }
    if (preg_match_all($r3, $candidate, $o) < 1) {
        return 2;//json(array('status' => '500', 'message' => lang('reg_pwd_2')));//密码必须包含至少一个数字
    }
    if (strlen($candidate) < 8 || strlen($candidate) > 14) {
        return 3;//json(array('status' => '500', 'message' => lang('reg_pwd_3')));//密码必须包含至少含有8个字符
    }
}

//检验手机号格式
function check_phone($phone){
    $check = preg_match("/^\d{11}$/", $phone);
    if($check){
        return true;
    }else{
        return false;
    }
}

//检验身份证号码格式
function check_idcode($idcode){
    $check1 = preg_match("/^\d{17}(\d|x|X)$/", $idcode);
    $check2 = preg_match("/^\d{14}(\d|x|X)$/", $idcode);
    if($check1 || $check2){
        return true;
    }else{
        return false;
    }
}

//判断手机类型
function get_device_type()
{
    //全部变成小写字母
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type = 'other';
    //分别进行判断
    if(strpos($agent, 'iphone') || strpos($agent, 'ipad'))
    {
        $type = 'ios';
    }

    if(strpos($agent, 'android'))
    {
        $type = 'android';
    }
    return $type;
}

//检验银行卡号格式
function check_bankcode($bankcode){
    $check = preg_match("/^\d{15,19}$/", $bankcode);
    if($check){
        return true;
    }else{
        return false;
    }
}

//检测开发环境
function check_env(){
    $server_name = $_SERVER['SERVER_NAME'];
    if($server_name == 'ydh.tupulian.com' || $server_name == '39.108.26.98'){
        return 'online';
    }else{
        return 'dev';
    }
}

//php异步
function doRequest_yb($host,$path, $param=array()){
    $query = isset($param)? http_build_query($param) : '';

    $port = 80;
    $errno = 0;
    $errstr = '';
    $timeout = 10;
    sleep(1);
    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);

    $out = "POST ".$path." HTTP/1.1\r\n";
    $out .= "host:".$host."\r\n";
    $out .= "content-length:".strlen($query)."\r\n";
    $out .= "content-type:application/x-www-form-urlencoded\r\n";
    $out .= "connection:close\r\n\r\n";
    $out .= $query;

    fputs($fp, $out);
    fclose($fp);
}
