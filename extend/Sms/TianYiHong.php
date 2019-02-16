<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/8/28
 * Time: 16:04
 */

namespace Sms;
class TianYiHong
{
    private static $config = [
        'api_send_url'          => 'http://sms.skylinelabs.cc:20003/sendsms?',
        'api_getreport_url'     => 'http://sms.skylinelabs.cc:20003/getreport?',
        'api_balance_query_url' => 'http://sms.skylinelabs.cc:20003/getbalance?',
        'api_getsms_url'        => 'http://sms.skylinelabs.cc:20003/getsms?',

        1 => [
            'api_account'  => 'cs_jx5e2o',
            'api_password' => 'YTq4m5bJ',
        ],
        2 => [
            'api_account'  => 'cs_y8rkbg',
            'api_password' => 'gOSo5uck',
        ],
    ];

    /**
     * 发送短信
     * @param $numbers string or array
     * @param $content string
     * @param $type 1成功率高通道 2成功率低通道
     * @param $version 1.0
     * @return mixed
     */
    public function send_sms($numbers, $content, $type = 2, $version = '1.0')
    {
        if (is_array($numbers)) {
            $numbers = implode($numbers, ',');
        }
        $config      = self::$config;
        $get_params  = array(
            'account'  => $config[$type]['api_account'],
            'password' => $config[$type]['api_password'],
            'version'  => $version,
            'content'  => $content,
        );
        $params_url  = http_build_query($get_params) . '&numbers=' . $numbers;
        $request_url = $config['api_send_url'] . $params_url;
        $result      = $this->curlGet($request_url);
        // 类库切入 记录mongo
        $now_time     = time();
        $mongo_config = config('auth_' . check_env() . '.MONGO');
        $log_data     = [
            'phone'    => $numbers,
            'content'  => $content,
            'add_date' => date('Y-m-d H:i:s', $now_time),
            'add_time' => $now_time,
            'type'     => 'TianYiHong',
            'result'   => json_decode($result, true)
        ];
        try {
            \think\Db::connect($mongo_config)->name('sms_log')->insert($log_data);
        } catch (\Exception $exception) {

        }
        return $result;
    }


    /**
     * 查询额度
     * @return mixed
     */
    public function query_balance($type)
    {
        $config = self::$config;
        //查询参数
        $get_params  = array(
            'account'  => $config[$type]['api_account'],
            'password' => $config[$type]['api_password'],
        );
        $params_url  = http_build_query($get_params);
        $request_url = $config['api_balance_query_url'] . $params_url;
        $result      = $this->curlGet($request_url);
        return $result;
    }

    /**
     * 查看发送结果
     * @param $ids int or string
     * @return mixed
     */
    public function get_report($type, $ids)
    {
        $config = self::$config;
        if (is_array($ids)) {
            $ids = implode($ids, ',');
        }
        //查询参数
        $get_params  = array(
            'account'  => $config[$type]['api_account'],
            'password' => $config[$type]['api_password'],
        );
        $params_url  = http_build_query($get_params) . '&ids=' . $ids;
        $request_url = $config['api_getreport_url'] . $params_url;
        $result      = $this->curlGet($request_url);
        return $result;
    }

    /**
     * 接收短信
     * @return mixed
     */
    public function get_sms($type)
    {
        $config = self::$config;
        //查询参数
        $get_params  = array(
            'account'  => $config[$type]['api_account'],
            'password' => $config[$type]['api_password'],
        );
        $params_url  = http_build_query($get_params);
        $request_url = $config['api_getsms_url'] . $params_url;
        $result      = $this->curlGet($request_url);
        return $result;
    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     */
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

    /**
     * 发起请求
     * @param $url
     * @return mixed
     */
    private function curlGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);// 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);// 从证书中检查SSL加密算法是否存在
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}