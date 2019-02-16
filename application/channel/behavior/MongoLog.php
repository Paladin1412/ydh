<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/8/17
 * Time: 16:05
 */

namespace app\index\behavior;

use think\Db;

class MongoLog
{

    /**
     * 记录日志
     * @param $data
     */
    public function run(&$data)
    {
        $data         = is_object($data) ? $data : (object)$data;
        $now_time     = time();
        $mongo_config = config('config_' . check_env() . '.MONGO');
        $log_data     = [
            'from_ip'     => $_SERVER['SERVER_ADDR'],
            'method'      => request()->controller() . '/' . request()->action(),
            //'user_id'     => request()->post('user_id','0'),
            'header_data' => (request()->header()),
            'param_data'  => (request()->param()),
            //'return_data' => ($data->getData()),
            'add_date'    => date('Y-m-d H:i:s', $now_time),
            'add_time'    => $now_time,
        ];
        try {
            $res = Db::connect($mongo_config)->name('channel_log')->insert($log_data);
            trace('channel_log123445522werwerwe'.$res);
        } catch (\Exception $exception) {
            //dump($log_data);

        }
    }
}