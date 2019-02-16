<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 10:42
 */


namespace app\loan\controller;

use Redis\redisServer;
use think\Config;
use think\Db;
use Baiqishi\Operator;
class Help extends Common
{
    // 定时任务 和 扩展类库
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 定时任务，改变账单状态，修改逾期费用
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function update_overdue_fee()
    {
        set_time_limit(0);
        $repay_list = Db::name('order_repayment')
            ->where('(due_time <= ' . time() . ' and bill_status = 1) or bill_status = 3 ')
            ->select();
        foreach ($repay_list as $key => $value) {
            //修改还款逾期费用
            $data = array('repay_id' => $value['repay_id']);
            if ($value['bill_status'] == 1) {
                //第一次逾期
                $data['bill_status'] = 3;
                $data['overdue_fee'] = $value['amount'] * $value["over_fee"];
                $due_day             = ceil((time() - $value["due_time"]) / (24 * 60 * 60));
                $data['due_day']     = $due_day;
            } else {
                $due_day             = ceil((time() - $value["due_time"]) / (24 * 60 * 60));
                $data['overdue_fee'] = $due_day * $value["over_fee"] * $value['amount'];
                $data['due_day']     = $due_day;
            }
            Db::name('order_repayment')->fetchSql(false)->update($data);
            $order_info = Db::name('order_info')->where('order_id', $value['order_id'])->find();
            $user_id    = $order_info["user_id"];

            //提醒短信：前三天  逾期提醒：PD5内（含）
            if (!empty($user_id) && $data['due_day'] < 6) {
                //修改账单状态
                $order_data = array(
                    'order_id'     => $value['order_id'],
                    'order_status' => 180,
                );
                Db::name('order_info')->update($order_data);
            }
        }
    }


    /**
     * 定时任务 发送短信提醒
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function expire_remind()
    {
        // eg. 订单 2019.4.10 11.00逾期 在 2019.4.9 15.00收到短信提醒1 在 2019.4.10 15.00收到短信提醒2
        $start_time = strtotime(date('Y-m-d 00:00:00'));
        $end_time   = strtotime(date('Y-m-d 23:59:59', strtotime("+1 day")));
        $order_list = Db::name('order_repayment ors')
            ->field('u.user_name,ors.due_time,o.user_id')
            ->where(array('ors.due_time' => array('between', array($start_time, $end_time)), 'ors.bill_status' => 1))
            ->join('order_info o', 'o.order_id = ors.order_id')
            ->join('users u', 'u.user_id = o.user_id')
            ->select();
        if (!empty($order_list) && is_array($order_list)) {
            foreach ($order_list as $key => $value) {
                if ($value['due_time'] <= strtotime(date('Y-m-d 23:59:59'))) {
                    if (!empty($value['user_name'])) {
                        $this->message_send($value['user_id'],7);//今天是您最后的还款日,请及时还款，保持良好信用
                    }
                } else {
                    if (!empty($value['user_name'])) {
                        $this->message_send($value['user_id'],6);//明天是你最后的还款日,请及时还款，保持良好信用
                    }
                }
            }
        }
    }

    //获取运营商h5数据
    public function get_operators_h5_data(){
        set_time_limit(999);
        //运营商认证成功60s后才能抓取到数据
        $compare_time = time()-60;
        $operator_info = Db::name('operator_info')
            ->where(['create_h5'=>0,'add_time'=>['elt',$compare_time]])
            ->limit(10)
            ->select();
        if(count($operator_info)>0){
            $bqs = new Operator('online');
            foreach($operator_info as $key=>$val){
                $params = [];
                $params['name'] = $val['name'];
                $params['certNo'] = $val['cert_no'];
                $params['mobile'] = $val['mobile'];
                $params['timeStamp'] = time();
                $token = $bqs->get_token($params);
                //获取h5页面内容
                $params['name'] = urlencode($val['name']);
                //$params['mobile'] = $val['mobile'];
                $params['token'] = $token['data'];
                //dump($params);
                $h5_html = $bqs->getreportpage($params);
                //echo $h5_html;
                //保存到后台文件
                $t = file_put_contents('./houtai/operators/'.$val['mobile'].'.html', $h5_html);
                if($t){
                    Db::name('operator_info')->where(['mobile'=>$val['mobile']])->update(['create_h5'=>1]);
                    //echo Db::name('operator_info')->getlastsql();
                }
            }
        }else{
            exit('暂无需要更新用户！');
        }

    }

        //获取运营商json数据
    public function get_operators_json_data(){
        set_time_limit(999);
        //运营商认证成功60s后才能抓取到数据
        $compare_time = time()-60;
        $operator_info = Db::name('operator_info')
            ->where(['create_json'=>0,'add_time'=>['elt',$compare_time]])
            ->limit(10)
            ->select();
        if(count($operator_info)>0){
            $bqs = new Operator('online');
            foreach($operator_info as $key=>$val){
                $params = [];
                $params['name'] = $val['name'];
                $params['certNo'] = $val['cert_no'];
                $params['mobile'] = $val['mobile'];
                $result = $bqs->getreport($params);
                if($result['resultCode']==='CCOM1000'){
                    $json_info = json_encode($result);
                    Db::name('operator_info')->where(['mobile'=>$val['mobile']])->update(['create_json'=>1,'info'=>$json_info]);
                }
            }
        }else{
            exit('暂无需要更新用户！');
        }

    }
}