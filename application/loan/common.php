<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/8/30
 * Time: 9:21
 */


function getRegionList($type)
{
    if ($type == 'city') {
        $sql = 'SELECT region_id,region_name FROM hunuo_region ORDER BY convert(`region_name` using gb2312) ASC';
    } else {
        $sql = 'SELECT * FROM hunuo_region ORDER BY convert(`region_name` using gb2312) ASC';
    }
    $data = \think\Db::query($sql);
    return $data ? $data : array();
}

/**
 * 检测验证码
 * @param string $mobile
 * @param string $authCode
 * @return bool
 * @throws \think\Exception
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 * @throws \think\exception\PDOException
 */
function check_code($mobile = '', $authCode = '', $type)
{
    $phone     = $mobile;
    $code      = \think\Db::name('session_code')->where(array('code' => $authCode, 'phone' => $phone, 'type' => $type))->find();
    if (!$code) {
        return false;
    } else {
        //计算过期时间
        $over_time = $code['add_time']+30*60;
        \think\Db::name('session_code')->where(array('code' => $authCode, 'phone' => $phone))->delete();
        if(time()>$over_time){
            return false;
        }else{
            return true;
        }
    }
}


/**
 * 加密函数
 * @param $data
 * @param $key
 * @return string
 */
function AES($data, $key)
{
    $iv        = 'zxcvbnmk09876543';
    $block     = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $pad       = $block - (strlen($data) % $block); //计算是否需要填充
    $data      .= str_repeat(chr($pad), $pad); //不足16的倍数在末尾填充
    $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, getKey($key), $data, MCRYPT_MODE_CBC, $iv);
    $encrypted = bin2hex($encrypted);
    $encrypted = base64_encode($encrypted);
    $encrypted = urlencode($encrypted);
    return $encrypted;
}

/**
 * 加密
 * @param $key
 * @return bool|string
 */
function getKey($key)
{
    if (empty($key) || $key == null) {
        $key = '0000000000000000';
    }
    $len = strlen($key);
    if (strlen($key) > 16) {
        $key = substr($key, 0, 16);
    } else {
        $i = 16 - $len;
        while ($i > 0) {
            $key = $key . "0";
            $i--;
        }
    }
    return $key;
}



function ajaxRequest($url, $data, $applyOid = '')
{
    $param    = json_encode($data, true);
    $ch       = curl_init();
    $str      = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";      //字符池
    $signRand = substr(str_shuffle($str), 26, 6);                                 //生成随机数
    $sign     = md5('wlh-indonesia-1' . $signRand);
    $organMD5 = md5('gtl2018');
    //如果$param是数组的话直接用
    curl_setopt($ch, CURLOPT_URL, $url);
    //如果$param是json格式的数据，则打开下面这个注释
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            "Authorization:$organMD5,$signRand,$sign",
            "applyOid:$applyOid",
        )
    );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 获取当前域名
 * @return string
 */
function GetHttpsUrl()
{
    $env = check_env();
    $online_config = think\Config::get('auth_'.$env);
    if(strpos('abc'.$online_config['APP_SITE'],'https')){
        $protocol = 'https://';
    }else{
        $protocol = 'http://';
    }
    return $protocol.$_SERVER['HTTP_HOST'];
}


/**
 * 数字转大写
 * @param $num
 * @return string
 */
function get_amount($num)
{
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    // $c1  = lang('m_num');
    // $c2  = lang('m_dw');
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        //           return "数据太长，没有这么大的钱吧，检查下";
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
        return $c . '零元整';
    } else {
        return $c . '整';
    }
}

/**
 * 兼容以前系统
 * @param $table_name
 * @param $mongo_data
 */
function mongo_log($table_name, $mongo_data)
{
    try {
        $config = config('auth_' . check_env() . '.MONGO');
        $result = \think\Db::connect($config)->name($table_name)->insert($mongo_data);
        return $result;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
/**
 * 过滤表情包
 * @param $str
 * @return null|string|string[]
 */
function filter_emoji($str)
{
    $str = preg_replace_callback( '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
    return $str;
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
 * 获取OSS地址
 * @param $config
 * @param $code
 * @return bool|null
 */
function get_oss_image($config, $code)
{
    $oss_config = $config;
    $Oss        = new \AliOss\ImageUpload($code, $oss_config);
    $image_url  = $Oss->getSignedUrl($code);
    return $image_url;
}

/**
 * 上传OSS图片
 * @param $config
 * @param $image
 * @return bool|string
 */
function upload_oss_image($config, $image)
{
    $oss_config = $config;
    $Oss        = new \AliOss\ImageUpload($image, $oss_config);
    $image_code = $Oss->AliyunUpload();
    return $image_code;
}

//获取请求地址
function get_request_url()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url      = $protocol.$_SERVER['HTTP_HOST'];
    return $url;
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