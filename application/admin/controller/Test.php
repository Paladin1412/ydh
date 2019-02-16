<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/6/15
 * Time: 13:56
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Config;
class Test extends Controller
{

    //更新超级管理员的菜单权限（因为业务变更，有新菜单加入）
    public function update_super_menu_auth(){
        $menuid_arr = Db::table('system_menu')->where(['pid'=>['gt',0],'status'=>0])->column('id');
        Db::table('system_role_menu_relation_v2')->where(['role_id'=>1])->delete();
        foreach($menuid_arr as $key=>$val){
            $add_data = [
                'role_id' => 1,
                'menu_id' => $val,
                'addtime' => date('Y-m-d H:i:s'),
            ];
            Db::table('system_role_menu_relation_v2')->insert($add_data);
        }
        exit('更新完成！');
    }

    public function index(){
        echo "跑入订单表的风控和放款状态表字段";
        $order_list = Db::name('hunuo_order_info')
            ->field('order_id,order_status,handle_state')
            ->select();
        foreach ($order_list as $key => $value){
            switch ($value['order_status']){
                case 1:
                    $data = [
                        'risk_status' => 0,
                        'pay_status'  => 0,
                    ];
                    break;
                case 80:
                    $data = [
                        'risk_status' => 2,
                        'pay_status'  => 0,
                    ];
                    break;
                case 90:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 0,
                    ];
                    break;
                case 100:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 0,
                    ];
                    break;
                case 110:
                    if($value['handle_state'] == 3){
                        $data = [
                            'risk_status' => 1,
                            'pay_status'  => 0,
                        ];
                    }else{
                        $data = [
                            'risk_status' => 0,
                            'pay_status'  => 0,
                        ];
                    }
                    break;
                case 160:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 0,
                    ];
                    break;
                case 161:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 2,
                    ];
                    break;
                case 169:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 2,
                    ];
                    break;
                case 170:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 1,
                    ];
                    break;
                case 180:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 1,
                    ];
                    break;
                case 200:
                    $data = [
                        'risk_status' => 1,
                        'pay_status'  => 1,
                    ];
                    break;
            }
            Db::name('hunuo_order_info')->where('order_id',$value['order_id'])->update($data);
        }

    }

    // 跑入承诺还款初始数据
    public function _index()
    {
        $list = Db::table('daihou_case_info d')
            ->field('d.order_no,d.current_collector,f.operator_time')
            ->join("(select * from ( SELECT * from daihou_case_followup_record ORDER BY id desc ) temp group by temp.case_id  order by temp.id desc ) f", 'f.case_id = d.id')
            ->where('collection_feedback',181)
            ->group('order_no')
            ->select();

        if(!empty($list) && is_array($list))
        {
            foreach ($list as $key => $value){
                $add_data = array(
                    'admin_id' => $value['current_collector'],
                    'order_no' => $value['order_no'],
                    'add_time' => strtotime($value['operator_time']),
                    'status'   => 1
                );
                Db::name('hunuo_order_collection_log')->insert($add_data);
            }
        }
        dump($list);
    }

    public function admin_id()
    {
        $list = Db::table('hunuo_order_handle_user')
            ->where('admin_id', 0)
            ->limit(200)
            ->select();
        $data = [];
        foreach ($list as $k => $value) {
            $data[$k]["id"]       = $value["id"];
            $data[$k]["admin_id"] = $value["user_id"];
            Db::name('hunuo_order_handle_user')->where(['id' => $value["id"]])->update(['admin_id' => $value["user_id"]]);
        }
        dump($list);
    }


    public function delete_order()
    {
        $data = request()->param();
        if(!empty($data['order_no']) && !empty($data['phone'])){
            if(session('admin_id') != 1){

                $order_data = Db::name('hunuo_order_info')->where(array('order_no'=>$data['order_no'],'phone'=>$data['phone']))->find();
                if(empty($order_data)){
                    echo '订单不存在';
                }

                if(!isset($data['delete'])){
                    dump($order_data);
                }else{
                    // 删除订单
                    Db::name('hunuo_order_info')->where('order_no',$data['order_no'])->delete();
                    // 删除还款表
                    Db::name('hunuo_order_repayment')->where('order_no',$data['order_no'])->delete();
                    // 删除案件表
                    Db::name('daihou_case_info')->where('order_no',$data['order_no'])->delete();

                    echo "删除订单完成";
                }
            }else{
                echo "没有权限删除订单";
            }

        }else{
            echo "订单和手机号不能为空";
        }
    }

}
