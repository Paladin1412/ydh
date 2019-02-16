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
            $transfer_log = Db::name('transfer_log')->where(['order_no'=>$order_no,'company_code'=>$company_code,'status_code'=>2001])->find();
            if(is_array($transfer_log)){
                return json(['status' => '500', 'message' => '请勿重复放款！', 'data' => []]);
            }
            $order_info = [
                'order_no' => $order_no,
                'amount' => $order_info['approval_amount'],//应到帐金额
                'bankcard_no' => $bank_info['card_num'],
                'name' => $bank_info['name'],
                'company_code' => $company_code,
            ];
            $agencypay = new AgencyPayModel($this->env,$this->is_open_mongo);
            $response = $agencypay->pay($order_info);
            if($response['statusCode']===2001){
                Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>160]);//放款中
                return json(['status' => '200', 'message' => '放款订单创建成功！', 'data' => []]);
            }else{
                Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>169]);//放款失败
                return json(['status' => '500', 'message' => $response['message'], 'data' => []]);
            }
        }else{
            return json(['status' => '500', 'message' => '没有符合放款要求的订单！', 'data' => []]);
        }
    }


    //快捷支付-获取支付短信(回款)
    public function code_quick_pay(){
        $user_id = request()->post('user_id');
        $company_code = request()->header('COMPANYCODE');
        $this->check_login();
        //根据用户id查询待还款订单
        $order_info = Db::name('order_info')->field('order_no,application_amount,repay_amount')->where(['user_id'=>$user_id,'order_status'=>['in',[170,180,195]],'company_code'=>$company_code])->find();
        if(is_array($order_info)){
            $bank_info = Db::name('bankcard')->field('user_id,card_num,phone')->where('user_id',$user_id)->find();
            if(is_array($bank_info)){
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
                $user_info = $this->get_userinfo($user_id);
                $pay_info = [
                    'user_id' => $user_id,
                    'order_no' => $order_info['order_no'],
                    'name' => $user_info['name'],
                    'idcard_no' => $user_info['idcode'],
                    'bankcard_no' => $bank_info['card_num'],
                    'mobile' => $bank_info['phone'],//银行卡绑定的手机号,不一定是注册手机
                    'amount' =>(string)$should_repay_money,//
                    'company_code' =>$company_code,
                ];
                //dump($pay_info);
                $quickpay = new QuickPayModel($this->env);
                $result = $quickpay->quickPaySentSms($pay_info);
                if($result['ra_Status']==='100'){
                    return json(['status' => '200', 'message' => '成功！', 'data' => ['order_no'=>$order_info['order_no'],'name'=>$user_info['name'],'idcard_no'=>$user_info['idcode'],'bankcard_no'=>$bank_info['card_num'],'mobile'=>$bank_info['phone'],'amount'=>$should_repay_money]]);
                }else{
                    return json(['status' => '500', 'message' => $result['rb_Msg'], 'data' => []]);
                }
            }else{
                return json(['status' => '500', 'message' => '请先绑定银行卡！', 'data' => []]);
            } 
        }else{
            return json(['status' => '500', 'message' => '暂无待还款订单！', 'data' => []]);
        }
    }

    //快捷支付-确认支付(回款)
    public function confirm_quick_pay(){
        $this->check_login();
        $user_id = request()->post('user_id');
        $order_no = request()->post('order_no');
        $code = request()->post('code');
        $name = request()->post('name');
        $idcard_no = request()->post('idcard_no');
        $bankcard_no = request()->post('bankcard_no');
        $mobile = request()->post('mobile');
        $amount = request()->post('amount');
        $company_code = request()->header('COMPANYCODE');
        if(!isset($user_id) || !isset($order_no) || !isset($code) || !isset($name) || !isset($idcard_no) || !isset($bankcard_no) || !isset($mobile) || !isset($company_code) || !isset($amount)){
            return json(['status' => '500', 'message' => '缺少必要参数！', 'data' => []]);
        }

        $order_info = [
            'order_no' => $order_no,
            'code' => $code,
            'amount' => $amount,
            'name' => $name,
            'idcard_no' => $idcard_no,
            'bankcard_no' => $bankcard_no,
            'mobile' => $mobile,
            'user_id' => $user_id,
            'company_code' => $company_code,
        ];
        $pay = new QuickPayModel($this->env);
        $result = $pay->quickPayConfirmPay($order_info);

        if($result['ra_Status']==='102'){//已创建
            Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>190]);//还款中
            return json(['status' => '200', 'message' => '确认成功！', 'data' => []]);
        }else{
            //Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>195]);//放款失败
            return json(['status' => '500', 'message' => $result['rb_Msg'], 'data' => []]);
        }
    }


    //合利宝代付回调地址
    public function agency_pay_callback(){
        $back_data = request()->post();
        $jsonstr = json_encode($back_data);

        $export = var_export($back_data,true);
        file_put_contents('../agency.txt', $export.PHP_EOL,FILE_APPEND);
        //exit;
        // $jsonstr = '{"errorCode":"","errorCodeDesc":"","fee":1,"hmac":"641129b809465f5d2a9183cc46c9e1ad","merchantOrderNo":"37060404529011392","paidAmount":0.01,"platformSerialNo":"300318120729114828","receiverAccountNoEnc":"6222034000015132024","receiverNameEnc":"\u9093\u7389\u5c71","status":205,"userNo":"888105200007262"}';
        // $back_data = json_decode($jsonstr,true);

        //回调验签
        $agencypay = new AgencyPayModel($this->env);
        $check_result = $agencypay->check_sign($back_data);
        // dump($back_data);
        // exit;
        if($check_result){
            //纪录mysql 日志
            $agency_callback_log = [
                'order_no'=>$back_data['merchantOrderNo'],
                'order_status'=>$back_data['status'],
                'msg'=>$back_data['errorCode'],
                'reason'=>$back_data['errorCodeDesc'],
                'serial_number'=>$back_data['platformSerialNo'],//平台流水号
                'return_data'=>$jsonstr,
                'add_date'=>date('Y-m-d H:i:s'),
                'add_time'=>time(),
            ];
            Db::name('agency_callback_log')->insert($agency_callback_log);
            if($this->is_open_mongo == true){
                mongo_log('agency_callback_log', $agency_callback_log);
            }

            if($back_data['status']===205){
                $order_info = Db::name('order_info')->where(['order_no'=>$back_data['merchantOrderNo']])->find();
                if((int)$order_info['order_status']===160){//放款中
                    Db::name('order_info')->where(['order_no'=>$back_data['merchantOrderNo']])->update(['order_status'=>170,'pay_status'=>1]);//放款成功
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
                    $order_res = Db::name('order_info')->where(['order_no'=>$back_data['merchantOrderNo']])->update($order_data);
                    //放款完成推送
                    $this->message_send($order_info['user_id'],4);
                }
            }else{
                Db::name('order_info')->where(['order_no'=>$back_data['merchantOrderNo']])->update(['order_status'=>169]);//放款失败
            }
        }else{
            exit('不合法的回调信息');
        }

    }

    //合利宝快捷支付回调地址(成功才会通知)
    public function quick_pay_callback(){
        $back_data = request()->param();
        $jsonstr = json_encode($back_data);
        $export = var_export($back_data,true);
        //file_put_contents('../quick.txt', $export.PHP_EOL,FILE_APPEND);
        //$type = gettype($back_data);
        //$jsonstr = '{"r1_MerchantNo":"888100000002340","r2_OrderNo":"35101215070861630","r3_Amount":"0.01","r4_Cur":"1","r5_Mp":"%E5%93%88%E5%93%88%E5%93%88","r6_Status":"101","r7_TrxNo":"1205101227112859","r8_BankOrderNo":"1205101227112859","r9_BankTrxNo":"null ","ra_PayTime":"","rb_DealTime":"2018-12-05+10%3A13%3A33","rc_BankCode":"ICBC","rd_ErrCode":"CP110026","re_ErrMsg":"%E7%9F%AD%E4%BF%A1%E9%AA%8C%E8%AF%81%E7%A0%81%E4%B8%8D%E7%AC%A6%C2%A0","hmac":"09bd27beb78589ab046a1e3fb842e6ee"}';
        
        // $jsonstr = '{"r1_MerchantNo":"888100000002340","r2_OrderNo":"35101351676387915","r3_Amount":"0.01","r4_Cur":"1","r5_Mp":"%E5%93%88%E5%93%88%E5%93%88","r6_Status":"100","r7_TrxNo":"1205101403115309","r8_BankOrderNo":"1205101403115309","r9_BankTrxNo":"041812051014031577608","ra_PayTime":"2018-12-05+10%3A15%3A26","rb_DealTime":"2018-12-05+10%3A15%3A26","rc_BankCode":"ICBC","rd_ErrCode":"","re_ErrMsg":"","hmac":"18218d0b2c3c7fe495e2866ffbf29417"}';
        $back_data = json_decode(urldecode($jsonstr),true);
        // dump($back_data);
        // exit;

        $pay = new QuickPayModel($this->env);
        $sign_result = $pay->check_sign($back_data);
        if($sign_result){
            //接收到的是随机单号
            $rand_order_no = $back_data['r2_OrderNo'];
            //获取真实的借款订单号
            $order_no = Db::name('quickpay_log')->where('rand_order_no',$rand_order_no)->value('order_no');
            
            //纪录mysql 日志
            $quick_callback_log = [
                'order_no'=>$order_no,//订单号
                'order_status'=>$back_data['r6_Status'],//100 支付成功 101支付失败
                'msg'=>$back_data['re_ErrMsg'],//支付失败才有msg
                'serial_number'=>$back_data['r7_TrxNo'],//平台流水号
                'bank_no' => $back_data['r9_BankTrxNo'],//银行流水号
                'order_amount'=>$back_data['r3_Amount'],//订单金额
                'return_data'=>$jsonstr,
                'add_date'=>date('Y-m-d H:i:s'),//入库时间
                'add_time'=>time(),
                'pay_date' => $back_data['ra_PayTime'],//汇聚支付时间
            ];
            //dump($quick_callback_log);
            //exit;
            Db::name('quick_callback_log')->insert($quick_callback_log);

            if($this->is_open_mongo == true){
                mongo_log('quick_callback_log', $quick_callback_log);
            }
            if($back_data['r6_Status']==='100'){
                sleep(5);//睡眠5秒，等待同步执行完成
                $order_info = Db::name('order_info')->where(['order_no'=>$order_no])->find();
                if((int)$order_info['order_status']===190){//还款中
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
                        'paid_amount'  => $back_data['r3_Amount'],//已还款金额
                    );
                    Db::name('order_repayment')->where('order_no',$order_no)->update($repay_data);

                    //还款入库信息
                    $case_data = array(
                        'periods'           => 1,//还款期数(已还期数)
                        'has_pay_amount'    => $back_data['r3_Amount'],//已还款金额(已经还成功的金额)
                        'repay_data'        => date('Y-m-d H:i:s', time()),//还款日期(还款成功时间)
                        'lately_pay_amount' => $back_data['r3_Amount'],//最近还款金额
                    );
                    //更新案件还款入库信息
                    Db::table('daihou_case_info')->where('order_no', $order_no)->update($case_data);

                    //还款完成推送
                    $this->message_send($order_info['user_id'],5,['price'=>$back_data['r3_Amount']]);
                }else{
                    exit('订单状态异常');
                }
            }else{
                Db::name('order_info')->where(['order_no'=>$order_no])->update(['order_status'=>195]);//还款失败
            }
        }else{
            exit('不合法的回调信息');
        }
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
