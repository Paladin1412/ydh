<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 9:17
 */

namespace JPush;

class JPush
{
    private $key;
    private $secret;
    private $url = "https://api.jpush.cn/v3/push";//推送的地址

    public function __construct($config = [])
    {
        if ($config['key']) $this->key = $config['key'];
        if ($config['secret']) $this->secret = $config['secret'];
    }

    /**
     * 推送参数
     * 文档地址：https://docs.jiguang.cn/jpush/server/push/rest_api_v3_push/
     * @param string $user
     * @param string $content
     * @param array $params
     * @param int $time
     * @return bool|mixed
     */
    public function push($user = 'all', $content = '', $params = [], $time = 86400)
    {
        if(empty($user)){
            return false;
        }
        $base64 = base64_encode("$this->key:$this->secret");
        $header = array("Authorization:Basic $base64", "Content-Type:application/json");
        $data   = [
            'platform'     => 'android,ios',
            'audience'     => [
                'alias' => [
                    $user
                ]
            ],//目标用户
            'notification' => [
                'alert'   => $content,
                'android' => [
                    'alert'      => $content,
                    'title'      => '',
                    'builder_id' => 1,
                    //'extras'     => $params
                ],
                'ios'     => [
                    'alert' => $content,
                    'badge' => '1',
                    'sound' => 'default',
                    //'extras' => $params
                ],
            ],
            'options'      => [
                "sendno"          => time(),
                "time_to_live"    => (int)$time,//保存离线时间的秒数默认为一天
                "apns_production" => 0,//指定 APNS 通知发送环境：0开发环境，1生产环境。
            ],
        ];
        if (!empty($params)) {
            $data['notification']['android']['extras'] = $params;
        }

        $res = $this->push_curl($header, json_encode($data));

        // 类库切入 记录mongo
        $now_time     = time();
        $mongo_config = config('auth_' . check_env() . '.MONGO');
        $log_data     = [
            'user'     => $user,
            'data'     => $data,
            'content'  => $content,
            'add_date' => date('Y-m-d H:i:s', $now_time),
            'add_time' => $now_time,
            'result'   => json_decode($res, true)
        ];
        try {
            \think\Db::connect($mongo_config)->name('push_log')->insert($log_data);
        } catch (\Exception $exception) {

        }

        if ($res) {
            return $res;
        } else {
            return false;
        }
    }

    //推送的Curl方法
    public function push_curl($header, $param)
    {
        $postUrl  = $this->url;
        $curlPost = $param;
        $ch       = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
