<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/4/20
 * Time: 15:16
 */
namespace app\admin\controller;

use think\Controller;
use think\Db;

class Cron extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 定时任务
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cron_add_order_count()
    {
        $order_count        = Db::name('hunuo_order_info')->field('count(0) as order_count')->fetchSql(false)->find();
        $order_handle_count = Db::name('hunuo_order_handle_user')->field('count(0) as order_handle_count')->where('order_state', 'neq', 1)->fetchSql(false)->find();
        $add_data           = array(
            'order_time'         => date('Y-m-d'),
            'order_count'        => $order_count['order_count'],
            'order_handle_count' => $order_handle_count['order_handle_count'],
        );
        Db::name('hunuo_echart_order_count')->insert($add_data);
    }

    /**
     * 承诺还款定时任务修改订单状态
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function cron_check_collection_log()
    {
        // 查询 承诺还款 中承诺还款期间未还款 的订单
        $list = Db::table('hunuo_order_collection_log cl')
            ->field('cl.*,r.success_time,bill_status')
            ->join('hunuo_order_repayment r','r.order_no = cl.order_no')
            ->where('bill_status','in','2,3')
            ->where('cl.status','in','1,2')
            ->fetchSql(false)
            ->select();
        if(!empty($list) && is_array($list)){
            foreach ($list as $key => $value){
                // 1 未还款 逾期 且 承诺还款状态为1（默认） 且 在承诺还款期间内
                if(empty($value['success_time']) && $value['bill_status'] == 3 && $value['status'] == 1 && time() < $value['add_time']+3600*24*2){
                    Db::name('hunuo_order_collection_log')->where('id',$value['id'])->update(array('status'=>2));
                    continue;
                }

                // 2 已还款 且 承诺期间内还款 还款时间 在2天内
                if(!empty($value['success_time']) && $value['bill_status'] == 2 && $value['success_time'] < $value['add_time'] + 3600*24*2){
                    Db::name('hunuo_order_collection_log')->where('id',$value['id'])->update(array('status'=>3));
                    continue;
                }

                // 3 超出还款期限
                if( ($value['bill_status'] == 3 && time() > $value['add_time'] + 3600*24*2) || ($value['success_time'] > $value['add_time'] + 3600*24*2 && $value['bill_status'] == 2)){
                    Db::name('hunuo_order_collection_log')->where('id',$value['id'])->update(array('status'=>4));
                    continue;
                }
            }
        }
    }
}