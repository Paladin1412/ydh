<?php
/**风控-翔一异步通知接口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 14:31
 */
namespace app\loan\controller;

use Redis\redisServer;
use think\Config;
use think\Db;
use XiangyiRisk\Xiangyi;

class Xynotice extends Common
{
     const CALLBACK_URL = 'http://developing.api.ydh.china.tupulian.com/loan/Xynotice/pay_callback';

    /**
     * 授信请求翔一异步通知
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function notice_credit_request()
    {
        $post = file_get_contents("php://input");
        $resInfo = json_decode($post,true);
        $recived = json_decode($resInfo['content'] ,true);
        $addDate['phone'] = !empty($recived['phone'])?$recived['phone']:'';
        $addDate['order_no'] = !empty($recived['orderNo'])?$recived['orderNo']:0000000001;
        $addDate['applTime'] = !empty($recived['withDrawReqResult']['applTime'])?$recived['withDrawReqResult']['applTime']:'';
        $addDate['status'] = !empty($recived['withDrawReqResult']['status'])?$recived['withDrawReqResult']['status']:'';
        $addDate['allowResult'] = !empty($recived['withDrawReqResult']['allowResult'])?$recived['withDrawReqResult']['allowResult']:'';
        $addDate['rejectDays'] = !empty($recived['withDrawReqResult']['rejectDays'])?$recived['withDrawReqResult']['rejectDays']:'';
        $addDate['rejectType'] = !empty($recived['withDrawReqResult']['rejectType'])?$recived['withDrawReqResult']['rejectType']:'';
        $addDate['otherRejectDays'] = !empty($recived['withDrawReqResult']['otherRejectDays'])?$recived['withDrawReqResult']['otherRejectDays']:'';
        $addDate['create_time'] = time();
        try{
            //添加风控记录
            Db::name('xiangyi_credit')->insert($addDate);
            //添加风控日志
            $risk_log = [
                'order_no' => $addDate['order_no'],
                'recived' => $recived,
                'desc' => $resInfo,
                'addtime' => time(),
            ];
            mongo_log('risk_log', $risk_log);

            $order_info = Db::name('order_info')->where(['order_no' => $addDate['order_no']])->find();
            if(empty($order_info)){
                trace('该订单号不存在'.$addDate['order_no']);
                return json(['code' => -10001, 'msg' => '该订单号不存在' ,'status' => 0]);
            }

            if($order_info['order_status'] != 1){
                trace('该订单状态异常'.$addDate['order_no']);
                return json(['code' => -10002, 'msg' => '该订单状态异常' ,'status' => 0]);
            }

            if($addDate['status'] == 3){ //风控审批通过
                $res = Db::name('order_info')->where(['order_no' => $addDate['order_no']])->update(['risk_status'=>1,'order_status'=>100,'confirm_time'=>time()]);
                if(!empty($res)){
                    //开始调用第三方支付
                    //return json(['code' => 200, 'msg' => '风控审批通过,订单状态更改成功' ,'status' => 1]);

                    $company_code = Db::table('hunuo_users')->where(['user_id' => $order_info['user_id']])->value('company_code');
                    $key        = 'tupulian2018@andy';
                    $rand_str   = substr(md5(microtime(true)), 0, 6);
                    $time_stamp = time();
                    $sign       = md5($key . $rand_str . $time_stamp . $addDate['order_no']);
                    $data       = array(
                        "order_no"   => $addDate['order_no'],
                        "rand_str"   => $rand_str,
                        "time_stamp" => $time_stamp,
                        "sign"       => $sign,
                        'company_code'   => $company_code,
                    );
                    $site_web   = Config::get('database.app_site_'.$this->env);
                    $postUrl    = $site_web . "/loan/Pay/agency_pay";
                    //$response   = httpPost($postUrl, $data);
                    //$res        = json_decode($response, true);
                    //trace('支付同步放回参数'.$response);
                    return json(['code' => 200, 'msg' => '风控审批通过,订单状态更改成功' ,'status' => 1]);
                    //假设支付成功，调用支付回调接口
                    //$url = self::CALLBACK_URL;
                    //return $this->curlPost($url,['order_no' => $addDate['order_no'], 'status' => 200, 'rt7_orderStatus' => 'SUCCESS']);
                }else{
                    trace('订单状态更改失败0001'.$addDate['order_no']);
                    return json(['code' => -10003, 'msg' => '订单状态更改失败' ,'status' => 0]);
                }
            }else{ //风控审批不通过
                $update_data = [
                    'risk_status'=>2,//风控不通过
                    'order_status'=>110,//审批不通过
                    'refuse_time'=>time(),//风控审核时间
                ];
                $res = Db::name('order_info')->where('order_no',$addDate['order_no'])->update($update_data);
                if(empty($res)){
                    trace('订单状态更改失败0002'.$addDate['order_no']);
                    return json(['code' => -10004, 'msg' => '订单状态更改失败' ,'status' => 0]);
                }
                return json(['code' => 200, 'msg' => '风控审批不通过,订单状态更改成功' ,'status' => 1]);
            }
        }catch (\Exception $e){
            trace('授信请求异步通知错误信息'.$e->getMessage());
            return json(['code' => -20000, 'msg' => '脚本错误信息'.$e->getMessage() ,'status' => 0]);
        }
    }


    /**
     * 用户额度变更翔一异步通知
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function expire_remind()
    {
        $post = file_get_contents("php://input");
        $resInfo=json_decode($post,true);
        $recived = json_decode($resInfo['content'] ,true);
        $addDate['phone'] = !empty($recived['phone'])?$recived['phone']:'';
        $addDate['giveTime'] = !empty($recived['limitAmtChangeInfo']['giveTime'])?$recived['limitAmtChangeInfo']['giveTime']:'';
        $addDate['creditLimitAmt'] = !empty($recived['limitAmtChangeInfo']['creditLimitAmt'])?$recived['limitAmtChangeInfo']['creditLimitAmt']:'';
        $addDate['validStartDate'] = !empty($recived['limitAmtChangeInfo']['validStartDate'])?$recived['limitAmtChangeInfo']['validStartDate']:'';
        $addDate['validEndDate'] = !empty($recived['limitAmtChangeInfo']['validEndDate'])?$recived['limitAmtChangeInfo']['validEndDate']:'';
        $addDate['create_time'] = time();
        if(empty($addDate['phone'])) return json(['code' => -10010, 'msg' => '手机号不能为空' ,'status' => 0] );
        $res1_info = Db::name('users')->where(['user_name' => $addDate['phone']])->value('user_id');
        if(empty($res1_info)) return json(['code' => -10011, 'msg' => '不存在该用户' ,'status' => 0]);
        try{
            //添加风控日志
            $risk_log = [
                'phone' => $addDate['phone'],
                'recived' => $recived,
                'resinfo' => $resInfo,
                'addtime' => time(),
            ];
            mongo_log('xiangyi_change_log', $risk_log);
            $res = Db::name('xiangyi_change')->insert($addDate);
            if(empty($res)){
                trace('额度变更更改失败'.$addDate['phone']);
                return json(['code' => -10005, 'msg' => '额度变更更改失败' ,'status' => 0]);
            }
            return json(['code' => 200, 'msg' => '额度变更更改成功' ,'status' => 1]);
        }catch (\Exception $e){
            trace('额度变更异步通知信息添加失败'.$e->getMessage());
            return json(['code' => -20000, 'msg' => '脚本错误信息'.$e->getMessage() ,'status' => 0]);
        }
    }

    //易贷还第三方支付回调
    public function pay_callback(){
        $post = file_get_contents("php://input");
        $callbackInfo = json_decode($post,true);
        //$callbackInfo = ['order_no' => '56115033351397574', 'status' => 200, 'rt7_orderStatus' => 'SUCCESS'];
        if($callbackInfo['status'] == 200) {
            //纪录mysql 日志
            $agency_callback_log = [
                'return_data' => $post,
                'add_date' => date('Y-m-d H:i:s'),
            ];
            Db::name('agency_callback_log')->insert($agency_callback_log);

            //修改开始 赵光帅
            if ($this->is_open_mongo == true) {
                //记录mongodb
                $mongo_data = array(
                    'add_date' => date('Y-m-d H:i:s'),
                    'res' => $callbackInfo,
                );
                mongo_log('agency_callback_log', $mongo_data);
            }
            //修改结束 赵光帅
            if ($callbackInfo['rt7_orderStatus'] == 'SUCCESS') {
                $order_info = Db::name('order_info')->where(['order_no' => $callbackInfo['order_no']])->find();
                if ((int)$order_info['order_status'] === 100 && (int)$order_info['risk_status'] === 1) {//风控审批通过
                    Db::name('order_info')->where(['order_no' => $callbackInfo['order_no']])->update(['order_status' => 170, 'pay_status' => 1]);//放款成功
                    //添加还款表hunuo_order_repayment
                    $due_time = strtotime(date('Y-m-d 23:59:59')) + (24 * 3600 * $order_info['loan_term']);  //还款日期
                    $can_repay_time = strtotime("+1 day");  //最早可还款日期
                    $repay_data = array(
                        'order_id' => $order_info['order_id'],
                        'period_no' => 1,
                        'amount' => $order_info['loan_amount'],    //借款金额
                        'pay_amount' => $order_info['approval_amount'],  //到账金额
                        'repay_amount' => $order_info['repay_amount'],   //到期应还款金额
                        'loan_term' => $order_info['application_term'],//贷款期限
                        'due_time' => $due_time,                    //还款到期时间
                        'can_repay_time' => $can_repay_time,              //最早可还款时间
                        'order_no' => $order_info['order_no'],      //订单号
                        'over_fee' => $order_info["over_fee"],      //逾期费率
                        'lending_time' => time(),//放款到帐时间
                    );
                    $repay_res = Db::name('order_repayment')->insert($repay_data);

                    //添加案件
                    $case_data = array(
                        'order_id' => $order_info['order_id'],//订单ID
                        'order_no' => $order_info['order_no'],//订单ID
                        'personal_id' => $order_info['user_id'],//客户ID
                        'identification' => $order_info['user_id'],//客户ID
                        'case_number' => $order_info['order_no'],//案件编号 即 订单号
                        'contract_amount' => $order_info['application_amount'],//合同金额即借款金额
                        'case_follow_in_time' => date('Y-m-d h:i:s', time()),//案件流入时间
                        'loan_date' => date('Y-m-d h:i:s', time()),//放款时间
                        'credit_amount' => $order_info['application_amount'],//授信金额
                        'company_code' => $order_info['company_code'],
                    );
                    //添加案件池订单信息
                    $case_res = Db::table('daihou_case_info')->insert($case_data);

                    $company_code = Db::name('users')->where(['user_id' => $order_info['user_id']])->value('company_code');
                    $loan_info = Db::name('loan_type')->where(['company_code' => $company_code])->find();
                    //修改订单状态
                    $repay_time = strtotime(date('Y-m-d 23:59:59')) + (24 * 3600 * $order_info['loan_term']);  //还款日期
                    $order_data = [
                        'lending_time' => time(),//放款时间
                        'repay_time' => $repay_time,//应还款时间
                    ];
                    Db::name('order_info')->where(['order_no' => $callbackInfo['order_no']])->update($order_data);
                    //放款完成推送
                    $this->message_send($order_info['user_id'], 4);

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


                    return json(['msg' => '放款成功']);
                }else{
                    trace('风控审批不通过'.$callbackInfo['order_no']);
                    return json(['msg' => '风控审批不通过']);
                }
            } else {
                Db::name('order_info')->where(['order_no' => $callbackInfo['order_no']])->update(['order_status' => 169]);//放款失败
                trace('放款失败'.$callbackInfo['order_no']);
                return json(['msg' => '放款失败']);
            }
        }else{
            trace('回调异常'.$callbackInfo['order_no']);
            return json(['msg' => '回调异常']);
        }
    }


    //post请求
    private function curlPost($url, $postFields)
    {
        $postFields = json_encode($postFields);
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 " . $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);
        return $result;
    }

    /**
     * 征信统一查询
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function credit_enquiry()
    {
        $post = file_get_contents("php://input");
        $resInfo = json_decode($post,true);
        $paramInfo = json_decode($resInfo['content'] ,true);
        //添加风控日志
        $risk_log = [
            'result' => $resInfo,
            'paramInfo' => $paramInfo,
            'addtime' => time(),
        ];
        mongo_log('credit_enquiry_log', $risk_log);
        /*$paramInfo = [
            'phone' => '13417977171',
            'creditChannel' => 'libInfo'
        ];*/
        if(empty($paramInfo['phone'])) return json(['code' => -10006, 'msg' => '手机号不能为空' ,'status' => 0]);
        //根据手机号查询出用户ID
        $user_id = Db::name('users')->where(['user_name' => $paramInfo['phone']])->value('user_id');
        if(empty($user_id)) return json(['code' => -10007, 'msg' => '不存在该用户' ,'status' => 0]);
        $returnInfo = [];
        if(!empty($paramInfo['creditChannel'])){
            //需要查询运营商数据
            if('operatorInfo' == $paramInfo['creditChannel']){
                $resYys = Db::name('operator_info')->where(['mobile' => $paramInfo['phone']])->value('info');
                $returnInfo = json_decode($resYys, true);

            }
            //需要查询通讯录
            if('mailInfo' == $paramInfo['creditChannel']){
                $returnInfo = Db::name('phone_list')->where(['user_id' => $user_id])->field('phone phoneNo,name bookName,addTime')->select();
            }
            //需要查询通话记录
            if('callInfo' == $paramInfo['creditChannel']){
                $returnInfo = Db::name('user_call')->where(['user_id' => $user_id])->field('phoneNo,callTime,callType,callDuration,isThrough')->select();
            }
            //需要查询短信记录
            if('smsInfo' == $paramInfo['creditChannel']){
                $returnInfo = Db::name('user_sms')->where(['user_id' => $user_id])->field('send_phone phoneNo,send_time smsTime,sms_type smsType,send_content smsContent')->select();
            }
            //需要查询app安装列表信息
            if('appInfo' == $paramInfo['creditChannel']){
                $returnInfo = Db::name('user_app')->where(['user_id' => $user_id])->field('app_name appName,create_time captureTime')->select();
            }
            //需要查询登录流水
            if('loginInfo' == $paramInfo['creditChannel']){
                $returnInfo = Db::name('user_login_list')->where(['user_id' => $user_id])->field('deviceId,captureTime')->select();
            }
            //获取设备位置信息
            if('libInfo' == $paramInfo['creditChannel']){
                $returnInfo = Db::name('order_info')->where(['user_id' => $user_id])->field('add_time,gps_location')->select();
                if(!empty($returnInfo)){
                    foreach ($returnInfo as $k=>$v){
                        $returnInfo[$k]['captureTime'] = date('Y-m-d H:i:s' , $v['add_time']);
                        $resArr = explode(',' ,$v['gps_location']);
                        $returnInfo[$k]['lon'] = $resArr[0];
                        $returnInfo[$k]['lat'] = $resArr[1];
                    }
                }
            }
            return json(['code' => 200, 'data' => $returnInfo ,'status' => 1]);
        }else{
            return json(['code' => 200, 'data' => $returnInfo ,'status' => 1]);
        }

    }



}