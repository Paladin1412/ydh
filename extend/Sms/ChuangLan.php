<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/8/28
 * Time: 15:31
 */

namespace Sms;

class ChuangLan
{
    /**
     * 发送短信
     * @param $mobile
     * @param $content
     * @return mixed
     */
    public function send_sms($mobile, $content)
    {
        $config = [
            'send_url'     => 'http://intapi.253.com/send/json',
            'query_url'    => 'http://smssh1.253.com/msg/balance/json',
            'variable_url' => 'http://smssh1.253.com/msg/variable/json',
            'api_account'  => 'I2701711',
            'api_password' => 'Xcs4hfm5rq811b',
        ];
        //创蓝接口参数
        $post_arr = [
            'account'  => $config['api_account'],
            'password' => $config['api_password'],
            'msg'      => $content,//国际版本参数
            'mobile'   => $mobile,//国际版本手机号码参数
            'report'   => 'true'
        ];
        $result   = $this->curl_post($config['send_url'], $post_arr);
        // 类库切入 记录mongo
        $now_time     = time();
        $mongo_config = config('auth_' . check_env() . '.MONGO');
        $log_data     = [
            'phone'    => $mobile,
            'content'  => $content,
            'add_date' => date('Y-m-d H:i:s', $now_time),
            'add_time' => $now_time,
            'type'     => 'ChuangLan',
            'result'   => json_decode($result, true)
        ];
        try {
            \think\Db::connect($mongo_config)->name('sms_log')->insert($log_data);
        } catch (\Exception $exception) {

        }
        return $result;
    }

    /**
     * 发起请求
     * @param $url
     * @param $post_arr
     * @return mixed|string
     */
    private function curl_post($url, $post_arr)
    {
        $post_arr = json_encode($post_arr);
        $ch       = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_arr);
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