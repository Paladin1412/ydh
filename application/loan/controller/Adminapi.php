<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/10
 * Time: 18:52
 */

namespace app\loan\controller;

use Redis\redisServer;
use think\Db;

class Adminapi extends Common
{

    // 此接口为后台信审订单完成后异步请求接口

    /**
     * 订单人工审核接口
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handle_change()
    {
        $key         = "8QXp1kK6yI3rwBgj";
        $postdata    = request()->post();
        $verify_sign = md5($key . $postdata['rand_str'] . $postdata['time_stamp'] . $postdata['order_no']);
        if ($postdata['sign'] !== $verify_sign) {
            return json(['code' => 500, 'message' => '非法访问']);
        }

        //修改开始 赵光帅
        if($this->is_open_mongo == true){
            //记录日志缓存
            $request_info = array(
                'request_data' => $postdata,
                'add_time'     => time(),
                'add_date'     => date('Y-m-d H:i:s'),
                'desc'         => ($postdata['type'] == 1 ? '审核通过' : '审核不通过') . '信审结果通知',
                'order_no'     => $postdata['order_no'],
            );
            mongo_log('handle_log', $request_info);
        }

        //记录日志表
        $request_info = array(
            'request_data' => json_encode($postdata),
            'add_time'     => time(),
            'desc'         => ($postdata['type'] == 1 ? '审核通过' : '审核不通过') . '信审结果通知',
            'order_no'     => $postdata['order_no'],
        );
        Db::name('handle_log')->insert($request_info);
        //修改结束

        $order_info = Db::name('order_info')->where(array('order_no' => $postdata['order_no']))->find();
        if ($order_info['audit_method'] == 2 && $order_info['order_status'] == 90 && $order_info['handle_state'] == 1) {//同时满足人工审核 且订单状态为待审核
            if ($postdata['type'] == 1) {
                //Db::startTrans();
                $order_data = array(
                    'order_id'     => $order_info['order_id'],
                    'order_status' => 170,                                                           //审批状态已通过
                    'confirm_time' => time(),                                                        //确认时间
                    'refuse_time'  => time(),
                    'handle_state' => 2,
                    'lending_time' => time(),
                    'pay_status'   => 1,//放款状态 未进行0 ，成功1，失败2
                );
                Db::name('order_info')->update($order_data);

                // 完成放款的回调也写在这里
                $repayment_info = Db::name('order_repayment')->where('order_no',$postdata['order_no'])->find();
                if ($repayment_info) {
                    return json(['status' => '200', 'message' => '已收到数据']);
                }

                //单期,计算到期还款时间
                $due_time       = strtotime(date('Y-m-d 23:59:59')) + (24 * 3600 * $order_info['loan_term']);  //还款日期
                $can_repay_time = strtotime("+1 day");  //最早可还款日期
                $repay_data     = array(
                    'order_id'       => $order_info['order_id'],
                    'period_no'      => 1,
                    'amount'         => $order_info['loan_amount'],    //借款金额
                    'pay_amount'     => $order_info['approval_amount'],           //到账金额
                    'repay_amount'   => $order_info['repay_amount'],   //还款金额*/
                    'loan_term'      => $order_info['loan_term'],     //贷款期限
                    'due_time'       => $due_time,                    //还款到期时间
                    'can_repay_time' => $can_repay_time,              //最早可还款时间
                    'order_no'       => $order_info['order_no'],
                    'over_fee'       => $order_info["over_fee"],                 //逾期费率
                    'order_new_no'   => '',
                    'bt_id'          => '',
                );
                Db::name('order_repayment')->insert($repay_data);

                $case_data       = array(
                    'order_id'            => $order_info['order_id'],                                         //订单ID
                    'order_no'            => $order_info['order_no'],                                                       //订单ID
                    'personal_id'         => $order_info['user_id'],                                          //客户ID
                    'identification'      => $order_info['user_id'],                                          //客户ID
                    'case_number'         => $order_info['order_no'],                                                       //案件编号 即 订单号
                    'contract_amount'     => $order_info['application_amount'],                               //合同金额即借款金额
                    'case_follow_in_time' => date('Y-m-d h:i:s', time()),                             //案件流入时间
                    'loan_date'           => date('Y-m-d h:i:s', $order_info['lending_time']),        //放款时间
                    'credit_amount'       => $order_info['application_amount'],                               //授信金额
                );
                //添加案件池订单信息
                Db::table('daihou_case_info')->insert($case_data);

                // 推送消息
                $this->message_send($order_info['user_id'],4);
                $redis_data = [
                    'user_id' => $order_info['user_id'],
                    'type'    => 5,
                ];
                redisServer::getinstance('app_invest')->lPush(['key' => 'message_list', 'value' => json_encode($redis_data)]);

                return json(['code' => 200, 'message' => '审核通过修改订单完成']);
            } elseif ($postdata['type'] == 2) {
                //修改订单状态及记录审批时间
                $order_data = array(
                    'order_id'     => $order_info['order_id'],
                    'order_status' => 110,                                                             //审批状态
                    'refuse_time'  => time(),                                                          //审批时间
                    'confirm_time' => time(),                                                          //确认时间
                    'handle_state' => 3
                );
                Db::name('order_info')->update($order_data);
                $this->message_send($order_info['user_id'],2,[]);
                return json(['code' => 200, 'message' => '审核不通过修改订单完成']);
            }
        } else {
            return json(['code' => 400, 'message' => '订单状态不符合']);
        }
    }
}