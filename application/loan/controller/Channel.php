<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 14:19
 */

namespace app\loan\controller;

use think\Db;
use think\Controller;

class Channel extends Controller
{
    // 渠道类 用于记录渠道统计
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 记录渠道
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $db_config = config('auth_'.check_env().'.DB2');
        $request = request();
        $data    = $request->param();
        if (!empty($data['code'])) {
            $adv = Db::table('statistical_adv')->where('code', $data['code'])->find();
        }
        if (!empty($data['activity_id'])) {
            $activity = Db::table('hunuo_activity')->where('activity_id', $data['activity_id'])->find();
        }
        if (!empty($data['user_id'])) {
            $user = Db::table('hunuo_users')->where('user_id', $data['user_id'])->find();
        }
        if (!empty($adv) || !empty($activity) || !empty($user)) {
            //请求记录入库
            $package_name = config('loan.channel.package_name');//包名

            $request_data = [
                'url'          => http_build_query($data),
                'addtime'      => date("Y-m-d H:i:s", time()),
                'status'       => 0,
                'code'         => empty($data['code']) ? 0 : $data['code'],
                'package_name' => $package_name,
                'activity_id'  => empty($data['activity_id']) ? 0 : $data['activity_id'],
                'user_id'      => empty($data['user_id']) ? 0 : $data['user_id'],
                'ip'           => request()->ip(),
            ];
            $click_id     = Db::table('statistical_click')->insertGetId($request_data);
            if (!empty($click_id)) {
                $data = [
                    'click_id' => $click_id,
                ];

                $referrer = http_build_query($data);
                $this->redirect('https://play.google.com/store/apps/details?id=' . $package_name . '&referrer=' . urlencode($referrer), 302);
            } else {
                echo "入库失败，请联系管理员";
            }
        } else {
            echo "请传入参数，或联系管理员";
        }
    }

}