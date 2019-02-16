<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/10
 * Time: 17:23
 */

namespace app\loan\controller;

use think\Db;

class Activity extends Common
{

    // 活动类接口
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取最新活动信息
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function home()
    {
        $now_time      = date('Y-m-d H:i:s');
        $activity_info = Db::name('activity')
            ->field('id,title,start_time,end_time,content,author_image,list_image,dec_image,addtime')
            ->where('status', 0)
            ->where('start_time', '<', $now_time)
            ->where('end_time', '>', $now_time)
            ->order('addtime', 'desc')
            ->find();
        $oss_config = config('auth_'.$this->env.'.OSS');
        if (!empty($activity_info)) {
            $activity_info['start_time']   = strtotime($activity_info['start_time']);
            $activity_info['end_time']     = strtotime($activity_info['end_time']);
            $activity_info['addtime']      = strtotime($activity_info['addtime']);
            $activity_info['author_image'] = get_oss_image($oss_config,$activity_info['author_image']);
            $activity_info['list_image']   = get_oss_image($oss_config,$activity_info['list_image']);
            $activity_info['dec_image']    = get_oss_image($oss_config,$activity_info['dec_image']);
        } else {
            $activity_info = array();
        }
        return json(['status' => "200", 'message' => 'success', 'data' => $activity_info]);
    }

    /**
     * 获取活动列表
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function activity_list()
    {
        $activity_list = Db::name('activity')
            ->field('id,title,start_time,end_time,content,list_image,dec_image,addtime')
            ->where('status', 0)
            ->order('addtime', 'desc')
            ->select();
        $oss_config = config('auth_'.$this->env.'.OSS');
        if (!empty($activity_list)) {
            foreach ($activity_list as $key => &$value) {
                $value['start_time'] = strtotime($value["start_time"]);
                $value['end_time']   = strtotime($value["end_time"]);
                $value['addtime']    = strtotime($value["addtime"]);
                $value['author_image'] = get_oss_image($oss_config,$value['author_image']);
                $value['list_image']   = get_oss_image($oss_config,$value['list_image']);
                $value['dec_image']    = get_oss_image($oss_config,$value['dec_image']);
            }
        }
        return json(['status' => "200", 'message' => 'success', 'data' => $activity_list]);
    }
}