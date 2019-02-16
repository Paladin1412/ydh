<?php


namespace app\loan\controller;

use Message\JPush;
use think\Config;
use think\Db;
use think\Lang;

class RiskManage extends Common
{
    /**
     * 异步获取风控返回审批结果
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function getrest_state()
    {
        $result = file_get_contents('php://input');
        $data   = json_decode($result, true);
        // 记录mongodb
        $mongo_data = array(
            'order_no'     => $data["orderNo"],
            'request_data' => json_encode($data),
            'desc'         => '审批结果回调',
            'return_data'  => '',
            'add_date'     => date('Y-m-d H:i:s'),
            'add_time'     => time(),
            'type'         => 3
        );
        //修改开始 赵光帅
        if($this->is_open_mongo == true) mongo_log('risk_log', $mongo_data);
        //表里面存一份
        $risk_log = [
            'order_no' => $data["orderNo"],
            'result' => json_encode($data),
            'desc' => '审批结果回调',
            'addtime' => time(),
        ];
        Db::name('risk_log')->insert($risk_log);
        //修改结束 赵光帅


        $order = Db::name('order_info')
            ->where('order_no', $data["orderNo"])
            ->find();//查询订单信息
        if (!empty($data)) {
            $getrest_data = array(
                'name'     => $order["name"],                                          //贷款用户
                'phone'    => $order["phone"],                                         //用户手机号码
                'order_no' => $order["order_no"],                                      //AJAX返回的订单号
                'code'     => $data["code"],                                           //请求状态码
                'descr'    => $data["descr"],                                          //审核说明
                'pass'     => $data["pass"],                                           //审核结果
                'useTime'  => '',                                        //风控系统计算耗时，毫秒
                'version'  => '',                                        //版本号
                'add_time' => time(),                                                 //请求时间
            );
            Db::name('risk_callback')->insert($getrest_data);
            if ($order["order_status"] != 100) {
                if ($data['pass'] == 1) {
                    $loan_info = Db::name('loan_type')->find($order['type']);
                    //计算应还款金额
                    $loan_amount     = $loan_info['apply_amount'];                                               //借款金额
                    $loan_term       = $loan_info['apply_term'];                                                 //借款期限
                    $term_fee        = empty($loan_info['term_fee']) ? $loan_amount * $loan_term * $loan_info['rate'] : $loan_info['term_fee'];                    //利息 = 借款金额 * 借款期限 * 日费率2998.2
                    $approval_amount = $loan_amount - $loan_amount * ($loan_info['service_fee'] + $loan_info['approval_fee'] + $loan_info['manage_fee']);
                    $amount          = $loan_amount + round($term_fee);                                          //还款金额 = 本金 + 利息 + 逾期费

                    //修改订单状态及记录审批时间
                    $order_data = array(
                        'order_id'        => $order['order_id'],
                        'refuse_time'     => time(),                                                        //审批时间
                        'loan_term'       => $loan_term,                                                    //审批期限
                        'loan_amount'     => $loan_amount,                                                  //审批金额
                        'approval_amount' => $approval_amount,                                           //到账金额
                        'repay_amount'    => $amount,                                                            //到期还款金额
                        'risk_status'     => 1, // 风控审核 未进行0 ，通过1，未通过2
                        'pay_status'      => 0, // 放款 未进行0 ，通过1，未通过2
                    );

                    //已开启人工审核 在原基础上改动订单状态为90 确认时间在手动确认时添加 手动确认完成后再请求放款操作
                    if ($order['audit_method'] == 2 && $order['handle_state'] == 1) {
                        $order_data['order_status'] = 90;
                    } elseif ($order['audit_method'] == 1) {
                        $order_data['order_status'] = 100;
                        //$url                        = GetHttpsUrl() . ('/index.php/Pay/yibu_pay');
                        //$this->do_request($url, array('order_no' => $data['orderNo']));
                        //                                //跳到放款操作
                    }
                    Db::name('order_info')->update($order_data);
                } else {
                    //修改订单状态及记录审批时间
                    $order_data = array(
                        'order_id'     => $order['order_id'],
                        'order_status' => 110,                                                             //审批状态
                        'refuse_time'  => time(),                                                          //审批时间
                        'confirm_time' => time(),                                                          //确认时间
                        'risk_status'  => 2, // 风控审核 未进行0 ，通过1，未通过2
                    );
                    Db::name('order_info')->update($order_data);
                    $this->message_send($order['user_id'], 2, []);
                }
            }
            return json(['status' => '200', 'message' => '接收审批结果正常', 'data' => $data]);
        } else {
            $getrest_data = array(
                'name'     => '',                                                         //贷款用户
                'phone'    => '',                                                         //用户手机号码
                'order_no' => '',                                                         //AJAX返回的订单号
                'code'     => '500',                                                      //请求状态码
                'desc'     => '接收出错,未接收到数据',                                    //审核说明
                'pass'     => '0',                                                        //审核结果
                'useTime'  => '',                                                         //风控系统计算耗时，毫秒
                'version'  => '0',                                                        //版本号
                'add_time' => time(),                                                    //请求时间
            );
            Db::name('risk_callback')->insert($getrest_data);
            return json(['status' => '500', 'message' => '接收出错', 'data' => $data]);
        }
    }

}
