<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/7
 * Time: 16:37
 */
namespace app\admin\controller;


use think\Controller;
use think\Db;

class Statistics extends Controller
{
    public function detail(){
        $params = request()->param();
        $code  = $params['code'];
        $where = ['code'=>$code];
        if(isset($params['date'])){
            $data_arr = explode(' - ', $params['date']);
            $start_time = $data_arr[0].' 00:00:00';
            $end_time = $data_arr[1].' 23:59:59';
            $where['addtime'] = ['between',[$start_time,$end_time]];
        }
        $click = Db::table('statistical_click')->where($where)->count();
        $download = Db::table('statistical_download')->where($where)->count();
        $order = Db::table('statistical_order')->where($where)->count();
        $register = Db::table('statistical_register')->where($where)->count();
        $this->assign('click', $click);
        $this->assign('download', $download);
        $this->assign('order', $order);
        $this->assign('register', $register);
        return $this->fetch();
    }
}