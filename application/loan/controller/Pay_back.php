<?php


namespace app\loan\controller;

use think\Db;
use think\Lang;
use think\Config;
use app\loan\model\AgencyPayModel;
use app\loan\model\QuickPayModel;
use XiangyiRisk\Xiangyi;
class Pay extends Common
{

    //调用代付(放款)
    public function agency_pay(){
        $postdata    = request()->post();
        $order_no = $postdata['order_no'];
        $company_code = $postdata['company_code'];

        $config_key = 'auth_'.$this->env.'.api_md5_key';
        $md5_key = Config::get($config_key);
        $verify_sign = md5($md5_key.$postdata['rand_str'] . $postdata['time_stamp'] . $postdata['order_no']);
        if ($postdata['sign'] !== $verify_sign) {
            return json(['status' => '500', 'message' => '非法访问!', 'data' => []]);
        }
        if(!isset($order_no) || !isset($company_code)){
            return json(['status' => '500', 'message' => '缺少必要参数！', 'data' => []]);
        }
        $order_info = Db::name('order_info')->field('order_no,application_amount,user_id,approval_amount')->where(['order_no'=>$order_no,'order_status'=>100])->find();
        if(is_array($order_info)){
            $bank_info = Db::name('bankcard')->alias('bc')
                ->where('bc.user_id',$order_info['user_id'])
                ->field('bc.*,b.code')
                ->join('bank b','bc.bank_id = b.id')
                ->find();
            //echo Db::name('bankcard')->alias('bc')->getlastsql();
            $transfer_log = Db::name('transfer_log')->where(['order_no'=>$order_no,'company_code'=>$company_code,'result'=>1])->find();
            if(is_array($transfer_log)){
                return json(['status' => '500', 'message' => '请勿重复放款！', 'data' => []]);
            }
            $env = check_env();
            $agencypay = new AgencyPayModel($env);
            $order_info = [
                'order_no' => $order_no,
                'amount' => $order_info['approval_amount'],////应到帐金额
                'bankCode' => $bank_info['code'],
                'bankAccountNo' => $bank_info['card_num'],
                'bankAccountName' => $bank_info['name'],
                'company_code' => $company_code,
            ];
            $response = $agencypay->pay($order_info);
            if($response['rt2_retCode']==='0000'){
                Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>160]);//放款中
                return json(['status' => '200', 'message' => '放款订单创建成功！', 'data' => []]);
            }else{
                Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>169]);//放款失败
                return json(['status' => '500', 'message' => $response['rt3_retMsg'], 'data' => []]);
            }
        }else{
            return json(['status' => '500', 'message' => '没有符合放款要求的订单！', 'data' => []]);
        }
    }

    //快捷支付-创建订单(回款)
    public function create_quick_pay(){
        $this->check_login();
        $user_id = request()->post('user_id');
        $company_code = request()->header('COMPANYCODE');
        if(!isset($company_code)){
            return json(['status' => '500', 'message' => '缺少必要参数！', 'data' => []]);
        }
        //根据用户id查询待还款订单
        $order_info = Db::name('order_info')->field('order_no,application_amount,repay_amount')->where(['user_id'=>$user_id,'order_status'=>['in',[170,180,195]],'company_code'=>$company_code])->find();
        // echo Db::name('order_info')->getlastsql();
        // dump($order_info);
        $user_info = $this->get_userinfo($user_id);
        if(is_array($order_info)){
            $bank_info = Db::name('bankcard')->field('user_id,card_num,phone')->where('user_id',$user_id)->find();
            if(is_array($bank_info)){
                //订单不存在或已过期或异常才调用创建订单接口
                $quickpay_log = Db::name('quickpay_log')->where(['order_no'=>$order_info['order_no'],'is_available'=>1,'company_code'=>$company_code])->find();
                //获取还款信息
                $repayment_info = Db::name('order_repayment')->where(['order_no'=>$order_info['order_no']])->find();
                //获取减免信息
                $reduction_info = Db::table('daihou_case_reduction')->where(['order_no'=>$order_info['order_no'],'reduction_status'=>1])->find();
                if(is_array($reduction_info)){
                    $should_repay_money = $repayment_info['repay_amount'] + $repayment_info['overdue_fee'] - $reduction_info['reduction_fee'];
                    //还款金额=到期应还金额+逾期费用-减免金额
                }else{
                    $should_repay_money = $repayment_info['repay_amount'] + $repayment_info['overdue_fee'];
                    //还款金额=到期应还金额+逾期费用
                }

                //实例化快捷支付模型
                $env = check_env();
                $quickpay = new QuickPayModel($env);
                if(is_array($quickpay_log)){
                    //查看代付订单是否已过期
                    $query_order = $quickpay->quickPayQuery($order_info['order_no']);
                    // dump($query_order);
                    // exit;
                    if($query_order['rt2_retCode']==='8000' && $query_order['rt9_orderStatus']==='INIT'){
                        //待支付订单
                        return json(['status' => '200', 'message' => '成功！', 'data' => ['order_no'=>$quickpay_log['order_no'],'phone'=>$quickpay_log['phone']]]);
                    }else{
                        //把待支付订单改为失效状态
                        Db::name('quickpay_log')->where(['order_no'=>$order_info['order_no'],'is_available'=>1,'company_code'=>$company_code])->update(['is_available'=>0]);
                        //重新下单
                        $pay_info = [
                            'user_id' => $user_info['user_id'],
                            'order_no' => $order_info['order_no'],
                            'name' => $user_info['name'],
                            'idCardNo' => $user_info['idcode'],
                            'cardNo' => $bank_info['card_num'],
                            'phone' => $bank_info['phone'],//银行卡绑定的手机号,不一定是注册手机
                            'application_amount' =>$should_repay_money,//$should_repay_money
                            'company_code' =>$company_code,
                        ];
                        // dump($pay_info);
                        // exit;
                        $result = $quickpay->quickPayCreateOrder($pay_info);

                        if($result['rt2_retCode']==='0000'){
                            return json(['status' => '200', 'message' => '成功！', 'data' => ['order_no'=>$order_info['order_no'],'rand_order_no'=>$result['rt5_orderId'],'phone'=>$bank_info['phone']]]);
                        }else{
                            return json(['status' => '500', 'message' => $result['rt3_retMsg'], 'data' => []]);
                        }
                    }
                }else{
                    $pay_info = [
                        'user_id' => $user_info['user_id'],
                        'order_no' => $order_info['order_no'],
                        'name' => $user_info['name'],
                        'idCardNo' => $user_info['idcode'],
                        'cardNo' => $bank_info['card_num'],
                        'phone' => $bank_info['phone'],//银行卡绑定的手机号,不一定是注册手机
                        'application_amount' =>$should_repay_money,//
                        'company_code' =>$company_code,
                    ];
                    // dump($pay_info);
                    // exit;
                    $result = $quickpay->quickPayCreateOrder($pay_info);
                    if($result['rt2_retCode']==='0000'){
                        return json(['status' => '200', 'message' => '成功！', 'data' => ['order_no'=>$order_info['order_no'],'rand_order_no'=>$result['rt5_orderId'],'phone'=>$bank_info['phone']]]);
                    }else{
                        return json(['status' => '500', 'message' => $result['rt3_retMsg'], 'data' => []]);
                    }
                }
                
            }else{
                return json(['status' => '500', 'message' => '请先绑定银行卡！', 'data' => []]);
            }
        }else{
            return json(['status' => '500', 'message' => '暂无待还款订单！', 'data' => []]);
        }
    }

    //快捷支付-获取支付短信(回款)
    public function code_quick_pay(){
        $this->check_login();
        $user_id = request()->post('user_id');
        $order_no = request()->post('rand_order_no');
        $phone = request()->post('phone');
        $company_code = request()->header('COMPANYCODE');
        if(!isset($order_no) || !isset($phone) || !isset($company_code)){
            return json(['status' => '500', 'message' => '缺少必要参数！', 'data' => []]);
        }
        $order_info = [
            'order_no' => $order_no,
            'phone' => $phone,
            'company_code' => $company_code,
        ];
        $env = check_env();
        $quickpay = new QuickPayModel($env);
        $result = $quickpay->quickPaySendValidateCode($order_info);
        if($result['rt2_retCode']==='0000'){
            return json(['status' => '200', 'message' => '短信发送成功！', 'data' => ['order_no'=>$order_no]]);
        }else{
            return json(['status' => '500', 'message' => '短信发送失败！', 'data' => ['order_no'=>$order_no]]);
        }
    }

    //快捷支付-确认支付(回款)
    public function confirm_quick_pay(){
        $this->check_login();
        $user_id = request()->post('user_id');
        $rand_order_no = request()->post('rand_order_no');
        $code = request()->post('code');
        $order_ip = request()->post('order_ip');
        $company_code = request()->header('COMPANYCODE');
        if(!isset($rand_order_no) || !isset($code) || !isset($order_ip) || !isset($company_code)){
            return json(['status' => '500', 'message' => '缺少必要参数！', 'data' => []]);
        }

        $order_info = [
            'order_no' => $rand_order_no,
            'code' => $code,
            'orderIp' => $order_ip,//用户支付时使用的网络终端IP
            'company_code' => $company_code,
        ];
        $env = check_env();
        $pay = new QuickPayModel($env);
        $result = $pay->quickPayConfirmPay($order_info);

        //根据随机交易号获取真实的借款订单号
        $order_no = Db::name('quickpay_log')->where('rand_order_no',$rand_order_no)->value('order_no');
        if($result['rt2_retCode']==='0000'){
            Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>190]);//还款中
            return json(['status' => '200', 'message' => '确认成功！', 'data' => []]);
        }else{
            //Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>195]);//放款失败
            return json(['status' => '500', 'message' => $result['rt3_retMsg'], 'data' => []]);
        }
    }


    //合利宝代付回调地址
    public function agency_pay_callback(){
        $back_data = request()->post();
        $jsonstr = json_encode($back_data);

        $export = var_export($back_data,true);
        //file_put_contents('../agency.txt', $export.PHP_EOL,FILE_APPEND);

        //$jsonstr = '{"rt2_retCode":"0000","rt8_notifyType":"ORDER_STATUS","rt9_reason":"\u4ea4\u6613\u6210\u529f\uff0c\u53c2\u52a0\u6e05\u7b97","rt10_createDate":"2018-10-26 18:29:28","rt7_orderStatus":"SUCCESS","sign":"6b55f785c82039112d4ba3edf613376d","rt1_bizType":"Transfer","rt5_orderId":"54061336903956058","rt4_customerNumber":"C1800363646","rt11_completeDate":"2018-10-26 18:29:29","rt3_retMsg":"\u4ea4\u6613\u6210\u529f\u53d7\u7406","rt6_serialNumber":"TRANSFER115145530","UTF-8":""}';
        $back_data = json_decode($jsonstr,true);

        //回调验签
        $env = check_env();
        $agencypay = new AgencyPayModel($env);
        $check_result = $agencypay->check_sign($back_data);

        if($check_result){
            //纪录mysql 日志
            $agency_callback_log = [
                'order_no'=>$back_data['rt5_orderId'],
                'order_status'=>$back_data['rt7_orderStatus'],
                'msg'=>$back_data['rt3_retMsg'],
                'reason'=>$back_data['rt9_reason'],
                'serial_number'=>$back_data['rt6_serialNumber'],
                'return_data'=>$jsonstr,
                'add_date'=>date('Y-m-d H:i:s'),
                'add_time'=>time(),
            ];
            Db::name('agency_callback_log')->insert($agency_callback_log);

            //修改开始 赵光帅
            if($this->is_open_mongo == true){
                //记录mongodb
                $mongo_data = array(
                    'order_no'=>$back_data['rt5_orderId'],
                    'order_status'=>$back_data['rt7_orderStatus'],
                    'msg'=>$back_data['rt3_retMsg'],
                    'reason'=>$back_data['rt9_reason'],
                    'serial_number'=>$back_data['rt6_serialNumber'],
                    'return_data'=>$jsonstr,
                    'add_date'=>date('Y-m-d H:i:s'),
                    'add_time'=>time(),
                );
                mongo_log('agency_callback_log', $mongo_data);
            }
            //修改结束 赵光帅


            if($back_data['rt2_retCode']==='0000' && $back_data['rt7_orderStatus']==='SUCCESS'){
                $order_info = Db::name('order_info')->where(['order_no'=>$back_data['rt5_orderId']])->find();
                if((int)$order_info['order_status']===160){//放款中
                    Db::name('order_info')->where(['order_no'=>$back_data['rt5_orderId']])->update(['order_status'=>170,'pay_status'=>1]);//放款成功
                    //添加还款表hunuo_order_repayment
                    $due_time       = strtotime(date('Y-m-d 23:59:59')) + (24 * 3600 * $order_info['loan_term']);  //还款日期
                    $can_repay_time = strtotime("+1 day");  //最早可还款日期
                    $repay_data     = array(
                        'order_id'       => $order_info['order_id'],
                        'period_no'      => 1,
                        'amount'         => $order_info['loan_amount'],    //借款金额
                        'pay_amount'     => $order_info['approval_amount'],  //到账金额
                        'repay_amount'   => $order_info['repay_amount'],   //到期应还款金额
                        'loan_term'      => $order_info['application_term'],//贷款期限
                        'due_time'       => $due_time,                    //还款到期时间
                        'can_repay_time' => $can_repay_time,              //最早可还款时间
                        'order_no'       => $order_info['order_no'],      //订单号
                        'over_fee'       => $order_info["over_fee"],      //逾期费率
                        'lending_time'   => time(),//放款到帐时间
                    );
                    $repay_res = Db::name('order_repayment')->insert($repay_data);

                    //添加案件
                    $case_data = array(
                        'order_id'            => $order_info['order_id'],//订单ID
                        'order_no'            => $order_info['order_no'],//订单ID
                        'personal_id'         => $order_info['user_id'],//客户ID
                        'identification'      => $order_info['user_id'],//客户ID
                        'case_number'         => $order_info['order_no'],//案件编号 即 订单号
                        'contract_amount'     => $order_info['application_amount'],//合同金额即借款金额
                        'case_follow_in_time' => date('Y-m-d h:i:s', time()),//案件流入时间
                        'loan_date'           => date('Y-m-d h:i:s', time()),//放款时间
                        'credit_amount'       => $order_info['application_amount'],//授信金额
                        'company_code'        => $order_info['company_code'],
                    );
                    //添加案件池订单信息
                    $case_res = Db::table('daihou_case_info')->insert($case_data);

                    $company_code = Db::name('users')->where(['user_id'=>$order_info['user_id']])->value('company_code');
                    $loan_info = Db::name('loan_type')->where(['company_code'=>$company_code])->find();
                    //修改订单状态
                    $repay_time = strtotime(date('Y-m-d 23:59:59')) + (24 * 3600 * $order_info['loan_term']);  //还款日期
                    $order_data = [
                        'lending_time'=>time(),//放款时间
                        'repay_time'=>$repay_time,//应还款时间
                    ];
                    $order_res = Db::name('order_info')->where(['order_no'=>$back_data['rt5_orderId']])->update($order_data);
                    //放款完成推送
                    $this->message_send($order_info['user_id'],4);

                    //给翎一推送4.5  4.6  4.8的接口
                    $xy = new Xiangyi($this->env);
                    $content1 = [
                        'phone' => $order_info['phone'],
                        'closeOrderNotify' => [
                            'orderNo' => $order_info['order_no'],
                            'orderStatus' => 5,
                            'payOutResultTime' => date('Y-m-d H:i:s'),
                            'expectRepayDate' => date('Y-m-d H:i:s' ,time() + 86400 * $order_info['application_term']),
                        ]

                    ];
                    $post_parms1 = [
                        'phone' => $order_info['phone'],//手机号y
                        'serviceId' => 'closeOrderNotify',//服务识别号y
                        'content' => $content1,//业务数据y
                        'sign' => 'test',//签名y
                        'requestTime' => date('Y-m-d H:i:s'),//请求时间y
                    ];
                    //调用4.5订单状态同步
                    $res1 = $xy->decision($post_parms1);
                    trace($order_info['order_no'].'订单状态同步'.json_encode($res1));

                    $content2 = [
                        'phone' => $order_info['phone'],
                        'orderNo' => $order_info['order_no'],
                        'ratePeriod' => 1,  //利率期数
                        'rateDays' => 1,  //利率天数
                        'monthRate' => 0.01,  //月利率
                        'punishDayRate' => $order_info["over_fee"],  //罚息日利率
                        'latePunishAmount' => 1,  //滞纳金
                    ];
                    $post_parms2 = [
                        'phone' => $order_info['phone'],//手机号y
                        'serviceId' => 'synPrdRateCfg',//服务识别号y
                        'content' => $content2,//业务数据y
                        'sign' => 'test',//签名y
                        'requestTime' => date('Y-m-d H:i:s'),//请求时间y
                    ];
                    //调用4.6利率同步
                    $res2 = $xy->decision($post_parms2);
                    trace($order_info['order_no'].'利率同步'.json_encode($res2));

                    $content3 = [
                        'phone' => $order_info['phone'],
                        'orderNo' => $order_info['order_no'],
                        'orderScheduleList' => [  //还款计划列表
                            'periodCurrentNo' => 1, //当期期数
                            'periodStatus' => 1,  //期数状态
                            'periodCapital' => $order_info['application_amount'],  //当期本金
                            'interest' => $order_info['application_amount'] - $order_info['approval_amount'],  //当期利息
                            'dueDate' => date('Y-m-d H:i:s' ,time() + 86400 * $order_info['application_term']), //到期日
                        ]
                    ];
                    $post_parms3 = [
                        'phone' => $order_info['phone'],//手机号y
                        'serviceId' => 'synOrderSchedule',//服务识别号y
                        'content' => $content3,//业务数据y
                        'sign' => 'test',//签名y
                        'requestTime' => date('Y-m-d H:i:s'),//请求时间y
                    ];
                    //调用4.8还款计划同步
                    $res3 = $xy->decision($post_parms3);
                    trace($order_info['order_no'].'还款计划同步'.json_encode($res3));
                }
            }else{
                Db::name('order_info')->where(['order_no'=>$back_data['rt5_orderId']])->update(['order_status'=>169]);//放款失败
            }
        }else{
            exit('验签失败');
        }

    }

    //合利宝快捷支付回调地址(成功才会通知)
    public function quick_pay_callback(){
        $back_data = request()->post();
        $jsonstr = json_encode($back_data);

        $export = var_export($back_data,true);
        //file_put_contents('../quick.txt', $export.PHP_EOL,FILE_APPEND);
        //$type = gettype($back_data);
        //$jsonstr = '{"rt10_bindId":"e63660916a7b416abd6e59f7cd1d9a68","sign":"LhLCbb4eqxI6JT1XOgfi3LaEdYv8dt\/0w+x\/QP+CEa6vw4pBkynHcyoO6HD6NDcPQA\/2RBohBX3JtYT7XRjACd2dkKXsS\/fBwSAz+2i7m7AGIHKYyvGPzcytJTyT1CV0nTuT8F003Pz\/Zpul7WUi4FzXbXHJrbMJeXj6eSAuQTk5atM+8p+aIED+ZF38svjjzHAww31nDIXSMYAKS5rnlIOsHnC7P2oLrMELtOcQpuy3O3\/RElYiLt3Xbq3j8TXfc24vuiNEzKMmKpzkg4Pye5fyY2EmZ9HDO2RfaSEjp6\/YrmX8qPBSd1fYQJDE8YeRWEPiDq78YON\/hjj9YB1Y3Q==","rt1_bizType":"QuickPayConfirmPay","rt9_orderStatus":"SUCCESS","rt6_serialNumber":"QUICKPAY181025154429LD5W","rt14_userId":"2069","rt2_retCode":"0000","rt12_onlineCardType":"DEBIT","rt11_bankId":"CCB","rt13_cardAfterFour":"9801","rt5_orderId":"53034429606670355","rt4_customerNumber":"C1800363646","rt8_orderAmount":"1.01","rt3_retMsg":"\u6210\u529f","rt7_completeDate":"2018-10-25 15:44:55"}';
        $back_data = json_decode($jsonstr,true);

        // $env = check_env();
        // $pay = new QuickPayModel($env);
        // $result = $pay->check_sign($back_data);
        // exit;

        //返回的订单号是交易订单号
        $rand_order_no = $back_data['rt5_orderId'];
        //获取真实的借款订单号
        $order_no = Db::name('quickpay_log')->where('rand_order_no',$rand_order_no)->value('order_no');
        //纪录mysql 日志
        $quick_callback_log = [
            'order_no'=>$order_no,
            'rand_order_no' => $rand_order_no,
            'order_status'=>$back_data['rt9_orderStatus'],//INIT:未支付 SUCCESS：成功 CANCELLED：已取消 REFUNDED：已退款 FAILED：失败 DOING：处理中
            'msg'=>$back_data['rt3_retMsg'],
            'serial_number'=>$back_data['rt6_serialNumber'],//平台流水号
            'card_type'=>$back_data['rt12_onlineCardType'],//银行卡类型 DEBIT:借记卡 CREDIT:信用卡
            'order_amount'=>$back_data['rt8_orderAmount'],//订单金额
            'return_data'=>$jsonstr,
            'add_date'=>date('Y-m-d H:i:s'),
            'add_time'=>time(),
        ];
        Db::name('quick_callback_log')->insert($quick_callback_log);

        //修改开始 赵光帅
        if($this->is_open_mongo == true){
            //记录mongodb
            $mongo_data = array(
                'order_no'=>$order_no,
                'rand_order_no' => $rand_order_no,
                'order_status'=>$back_data['rt9_orderStatus'],//INIT:未支付 SUCCESS：成功 CANCELLED：已取消 REFUNDED：已退款 FAILED：失败 DOING：处理中
                'msg'=>$back_data['rt3_retMsg'],
                'serial_number'=>$back_data['rt6_serialNumber'],//平台流水号
                'card_type'=>$back_data['rt12_onlineCardType'],//银行卡类型 DEBIT:借记卡 CREDIT:信用卡
                'order_amount'=>$back_data['rt8_orderAmount'],//订单金额
                'return_data'=>$jsonstr,
                'add_date'=>date('Y-m-d H:i:s'),
                'add_time'=>time(),
            );
            mongo_log('quick_callback_log', $mongo_data);
        }
        //修改结束 赵光帅

        if($back_data['rt2_retCode']==='0000' && $back_data['rt9_orderStatus']==='SUCCESS'){
            sleep(10);//睡眠10秒，等待同步执行完成
            $order_info = Db::name('order_info')->where(['order_no'=>$order_no])->find();
            // dump((int)$order_info['order_status']);
            // exit;
            $order_info['order_status'] = (int)$order_info['order_status'];
            if($order_info['order_status']===190){//还款中
                //修改订单状态
                $order_data = array(
                        'order_status' => 200,//贷款结清
                        'end_time'     =>time(),//结清时间
                        //'suc_repay'    => 1,//已还期数
                );
                Db::name('order_info')->where('order_no',$order_no)->update($order_data);
                
                //修改还款状态 
                $repay_data = array(
                    'bill_status'  => 2,//还款状态，成功
                    'success_time' => time(),//还款成功时间
                    'paid_amount'  => $back_data['rt8_orderAmount'],//已还款金额
                );
                Db::name('order_repayment')->where('order_no',$order_no)->update($repay_data);

                //还款入库信息
                $case_data = array(
                    'periods'           => 1,//还款期数(已还期数)
                    'has_pay_amount'    => $back_data['rt8_orderAmount'],//已还款金额(已经还成功的金额)
                    'repay_data'        => date('Y-m-d H:i:s', time()),//还款日期(还款成功时间)
                    'lately_pay_amount' => $back_data['rt8_orderAmount'],//最近还款金额
                );
                //更新案件还款入库信息
                Db::table('daihou_case_info')->where('order_no', $order_no)->update($case_data);

                //还款完成推送
                $this->message_send($order_info['user_id'],5,['price'=>$back_data['rt8_orderAmount']]);
            }
        }else{
            Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>195]);//还款失败
        }
        //return 'success';
    }

    //终审或初审不通过,后台请求推送
    public function audit_failure(){
        $postdata    = request()->post();
        $user_id = $postdata['user_id'];

        $config_key = 'auth_'.$this->env.'.api_md5_key';
        $md5_key = Config::get($config_key);
        $verify_sign = md5($md5_key.$postdata['rand_str'] . $postdata['time_stamp'] . $user_id);
        if ($postdata['sign'] !== $verify_sign) {
            return json(['status' => '500', 'message' => '非法访问!', 'data' => []]);
        }
        //订单提交推送
        $this->message_send($user_id,2);
        return json(['status' => '200', 'message' => '推送成功！', 'data' => []]);
    }






}
