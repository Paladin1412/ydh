<?php
use think\Config;


//加密
function encrypt($str){
    $md5_key = Config::get('config_common.AUTH_CODE');
    return md5($md5_key.$str);
}

/**
 * 获取公司code
 * @param $company_id
 * @return mixed|string
 */
function getCompanyCode($company_id){
    $company_code = \think\Db::name('system_company')->where('cp_id',$company_id)->value('cp_code');
    return $company_code ? $company_code : "";
}

/**
 * 检测图片地址是否为OSS
 * @param $str
 * @return bool
 */
function check_oss_image_url($str)
{
    if (strstr($str, "Uploads")) {
        return false;
    }
    return true;
}

/**
 * 二维数组排序
 * @param $arrays
 * @param $sort_key
 * @param int $sort_order
 * @param int $sort_type
 * @return array
 */
function array_sort_chat($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
    if(is_array($arrays) && count($arrays) > 1){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return [];
            }
        }
    }else{
        return [];
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}

/**
 * 过滤空数组
 * @param $str
 * @return string
 */
function filter_arr($str)
{
    return implode(',',array_filter(explode(',',$str)));
}

/**
 * 数字转大写
 * @param $num
 * @return mixed|string
 */
function get_amount($num)
{
    $c1  = lang('m_num');
    $c2  = lang('m_dw');
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        return lang('data_lf');
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i   = $i + 1;
        $num = $num / 10;
        $num = (int)$num;
        if ($num == 0) {
            break;
        }
    }
    $j    = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left  = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c     = $left . $right;
            $j     = $j - 3;
            $slen  = $slen - 3;
        }
        $j = $j + 3;
    }

    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    if (empty($c)) {
        //           return "零元整";
        return $c . lang('lyz');
    } else {
        //           return $c . "整";
        return $c . lang('ints');
    }
}

/**
 * 获取用户角色类型
 * @return int|mixed
 */
function getAdminRoleType($type=1)
{
    $role_info = \think\Db::name('system_admin_role_v2')->where('role_id', 'in', session('admin_info.role_id'))->fetchSql(false)->column('admin_class');
    $role_info = ($type == 1) ? array_diff($role_info, array(2, 4)) : array_diff($role_info, array(1, 3));
    return session('admin_id') == 1 ? 6 : (empty($role_info) ? 0 : max($role_info));
}

/**
 * 获取用户角色类型列表
 */
function get_admin_class(){
    $role_info = \think\Db::name('system_admin_role_v2')->where('role_id', 'in', session('admin_info.role_id'))->fetchSql(false)->column('admin_class');
    $admin_class_str = implode($role_info,',');
    return $admin_class_str;
}

/**
 * post请求
 * @param $url
 * @param $data
 * @return mixed|string
 */
function httpPost($url, $data)
{
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
            CURLOPT_HTTPHEADER     => array("cache-control: no-cache",),
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

/**
 * 时间转换
 * @param $string
 * @return mixed
 */
function getSearchData($string)
{
    $arr = explode('-', $string);
    $data['start_time'] = $arr[0] . '-' . $arr[1] . '-' . trim($arr[2]) . ' 00:00:00';
    $data['end_time']   = trim($arr[3]) . '-' . $arr[4] . '-' . $arr[5] . ' 23:59:59';
    return $data;
}

/**
 * 获取用户所属角色
 * @param $admin_id
 * @return string
 */
function getUserRoles($admin_id)
{
    $role_list = \think\Db::table('system_admin_role_relation_v2')->alias('arr')->join('system_admin_role_v2 ar', 'arr.role_id = ar.role_id')->fetchSql(false)->where(array('arr.admin_id' => $admin_id))->column('role_name');
    if (!empty($role_list) && is_array($role_list)) {
        return implode('|', $role_list);
    } else {
        return '';
    }
}

/**
 *  去空格
 */

function trimall($str)
{
    return trim($str);
}


/**
 * 单张获取OSS图片url
 */
function getOssImageurl($code)
{
    if (!empty($code)) {
        $env = check_env();
        $oss_config = Config::get('config_'.$env.'.OSS');
        $Oss        = new \AliOss\ImageUpload($code, $oss_config);
        $image_url  = $Oss->getSignedUrl($code);
    } else {
        $image_url = null;
    }

    return $image_url;
}

/**
 * 管理员操作记录
 * @param $log_info
 */
function adminLog($log_info)
{
    $add['log_time']   = time();
    $add['admin_id']   = session('admin_id');
    $add['log_info']   = $log_info;
    $add['company_id'] = session('company_id');
    $add['log_ip']     = request()->ip();
    $add['log_url']    = request()->baseUrl();
    \think\Db::table('system_admin_log')->insert($add);
}


/**
 * 格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}


/**
* 异步请求
* @param $url
* @param array $param
*/
function yibu_request($url, $param = array())
{
    ignore_user_abort (true);//如果设置为 true，则忽略与用户的断开，如果设置为 false，会导致脚本停止运行。
    set_time_limit (30);//php超时设置
    $urlinfo = parse_url($url);
    $host    = $urlinfo['host'];
    $path    = $urlinfo['path'];
    $query   = isset($param) ? http_build_query($param) : '';
    $port    = 80;
    $errno   = 0;
    $errstr  = '';
    $timeout = 10;
    $fp      = fsockopen($host, $port, $errno, $errstr, $timeout);

    stream_set_blocking($fp,0); //开启非阻塞模式
    stream_set_timeout($fp, 30); //设置超时时间（s）

    $out     = "POST " . $path . " HTTP/1.1\r\n";
    $out     .= "host:" . $host . "\r\n";
    $out     .= "content-length:" . strlen($query) . "\r\n";
    $out     .= "content-type:application/x-www-form-urlencoded\r\n";
    $out     .= "connection:close\r\n\r\n";
    $out     .= $query;
    fputs($fp, $out);
    usleep(300000); //等待300ms
    fclose($fp);
}

//获取请求地址
function get_request_url()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url      = $protocol.$_SERVER['HTTP_HOST'];
    return $url;
}


//获取oss图片
function get_oss_image($config, $code)
{
    $oss_config = $config;
    $Oss        = new \AliOss\ImageUpload($code, $oss_config['endpoint'], $oss_config['bucket']);
    $image_url  = $Oss->getSignedUrl($code);
    return $image_url;
}

/**
 * 上传OSS图片url
 */
function uploadOssImageurl($image)
{ 
    if (!empty($image)) {
        $env = check_env();
        $oss_config = Config::get('config_'.$env.'.OSS');
        $Oss        = new \AliOss\ImageUpload($image, $oss_config);
        $image_code = $Oss->AliyunUpload();
    } else {
        $image_code = null;
    }

    return $image_code;
}


//获取角色管理的菜单列表
function get_auth_menu_list($auth_menu_list,$role_id){
  
    $res_tree = get_auth_menu_tree(0,0,$auth_menu_list,$role_id); 
    return $res_tree ;
}

function get_auth_menu_tree($pid=0,$level=0,$auth_menu_list,$role_id){
    $where['pid'] = $pid;
    $where['status'] = 0;
    if( $pid===0 ){
        $all_menu = \think\Db::table('system_menu')->where($where)->order('sort asc')->field('id,name,level')->select();
        //如果不是超级管理员，不显示【合作公司】模块
        if($role_id != 1 ){
            unset($all_menu[3]);
        }
    }else{
        //如果不是超级管理员，只显示自己的节点
        if($role_id != 1 ){
            $all_menu = \think\Db::table('system_role_menu_relation_v2 r')
                ->field('m.id,m.name,m.level')
                ->join('system_menu m','r.menu_id = m.id')
                ->where(['r.role_id'=>$role_id,'m.pid'=>$pid])
                ->select();
        }else{
            $all_menu = \think\Db::table('system_menu')
                ->field('id,name,level')
                ->where(['pid'=>$pid])
                ->select();
        }

    }
    
    $level ++;
    $tree = array();
    if(!empty($all_menu)){
        foreach ($all_menu as $val) {
            $val['value'] = $val['id'];
            if(in_array($val['id'],$auth_menu_list)){
                $val['checked'] = true;
            }
            $val['list'] = get_auth_menu_tree($val['id'],$level,$auth_menu_list,$role_id);
            if($val['list'] == null){
                $val['list'] = [];
            }
            $tree[] = $val;
        }
    }
    return $tree;
}