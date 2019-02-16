<?php
/**
 * User: andy.deng
 * Date: 2018/10/09
 * Time: 13:44
 */
namespace app\loan\controller;

use think\Db;
use Baiqishi\Operator;
use Redis\redisServer;
use think\Config;
class Operators extends Common{

    //运营商授权
    public function operator_auth(){
		$this->check_login();
        $user_id = request()->param('user_id');
        if($this->is_open_redis == true){
            $user_info_keyexists = redisServer::getInstance()->exists('user_info:user_' . $user_id);
        }else{
            $user_info_keyexists = \think\Cache::has('user_info:user_' . $user_id);
        }

        if (!$user_info_keyexists) {
            exit(json_encode(['status' => '300', 'message' => lang('p_login'), 'data' => []]));
        }
        if($this->is_open_redis == true){
            $user_info_json = redisServer::getInstance()->get('user_info:user_' . $user_id);
        }else{
            $user_info_json = \think\Cache::get('user_info:user_' . $user_id);
        }

        $user_info      = json_decode($user_info_json, true);
        $bqs = new Operator($this->env);
        $request_url = $bqs->h5_no_userinfo($user_info);
        return json(['status' => '200', 'message' => '成功', 'data' =>['request_url'=>$request_url,'operator_auth'=>$user_info['operator_auth']]]);
    }

    //运营商授权成功回调地址（已授权号码将不再验证服务密码）
    public function h5_backurl(){
        $params = request()->param('params');
        $params = json_decode($params,true);
        //修改运营商状态为已认证
        Db::name('users')->where('phone', $params['mobile'])->update(['operator_auth'=>1]);

        //添加运营商数据
        $data = [
            'mobile'=>$params['mobile'],
            'cert_no'=>$params['certNo'],
            'info'=>'',
            'add_time'=>time(),
            'name' => urldecode($params['name']),
            'create_h5' => 0,
        ];
        $result = Db::name('operator_info')->where('mobile', $params['mobile'])->find();
        if($result){
            Db::name('operator_info')->where('mobile', $params['mobile'])->update($data);
        }else{
            Db::name('operator_info')->insert($data);
        }
        //$this->create_file($params);
        $config_key = 'auth_'.$this->env.'.APP_SITE';
        $app_site = Config::get($config_key);
        $url = $app_site.'/loan/Operators/h5_failurl?code=200';
        header('Location:'. $url);
        exit;

    }

    public function h5_failurl(){

    }

    public function test(){
        $result = action('loan/Operators/operator_risk',[['phone'=>'13417977171','order_no'=>'123456789']]);
        dump($result);
        exit;
    }
    

    //运营商风控规则判断
    public function operator_risk($param){
        // 1、  手机号关联身份证个数超过2个拒绝 
        // 2、  手机号码使用时长小于180天  拒绝
        // 3、  互通号码小于5%拒绝
        // 4、  全天未使用通话半年之内大于30天，拒绝
        // 5、  公安、法院同话大于1 拒绝
        // 6、  半年内通话时长小于500分钟  拒绝
        // 7、  半年内拨出时长小于200分钟  拒绝
        $phone = $param['phone'];
        $order_no = $param['order_no'];
        $info = Db::name('operator_info')->where('mobile',$phone)->value('info');
        $operator_info = json_decode($info,true);

        //1.手机号关联身份证个数超过2个拒绝 
        //$operator_info['data']['bqsAntiFraudCloud']['idcCount'] = 3;
        if(isset($operator_info['data']['bqsAntiFraudCloud']['idcCount'])){
            $idcCount = (int)$operator_info['data']['bqsAntiFraudCloud']['idcCount'];
            if($idcCount>2){
                $msg = '手机号关联身份证个数超过2个';
                $this->risk_not_pass($order_no,$msg);
                return false;
            }
        }

        //2.手机号码使用时长小于180天
        //$operator_info['data']['crossValidation']['numberUsedLong']['result'] = 195;
        if(isset($operator_info['data']['crossValidation']['numberUsedLong']['result'])){
            preg_match('/\d+/',$operator_info['data']['crossValidation']['numberUsedLong']['result'],$match);
            $numberUsedLong = (int)$match[0];
            if($numberUsedLong < 180){
                $msg = '手机号码使用时长小于180天';
                $this->risk_not_pass($order_no,$msg);
                return false;
            }
        }

        //3.互通号码小于5%拒绝
        //$operator_info['data']['crossValidation']['contactsSize']['result'] = 100;
        //$operator_info['data']['crossValidation']['exchangeCallMobileCount']['result'] = 4;
        if(isset($operator_info['data']['crossValidation']['contactsSize']['result']) && isset($operator_info['data']['crossValidation']['exchangeCallMobileCount']['result'])){
            //朋友圈大小
            preg_match('/\d+/',$operator_info['data']['crossValidation']['contactsSize']['result'],$match);
            $contactsSize = (int)$match[0];
            //互通号码数
            preg_match('/\d+/',$operator_info['data']['crossValidation']['exchangeCallMobileCount']['result'],$match);
            $exchangeCallMobileCount = (int)$match[0];
            $rate = round($exchangeCallMobileCount/$contactsSize,2)*100;
            if($rate < 5){
                $msg = '互通号码小于5%';
                $this->risk_not_pass($order_no,$msg);
                return false;
            }
        }

        //4.全天未使用通话半年大于30天，拒绝
        //$operator_info['data']['crossValidation']['notCallAndSmsDayCount']['result'] = 10;
        if(isset($operator_info['data']['crossValidation']['notCallAndSmsDayCount']['result'])){
            preg_match('/\d+/',$operator_info['data']['crossValidation']['notCallAndSmsDayCount']['result'],$match);
            $notCallAndSmsDayCount = (int)$match[0];
            if($notCallAndSmsDayCount > 30){
                $msg = '全天未使用通话半年大于30天';
                $this->risk_not_pass($order_no,$msg);
                return false;
            }
        }

        //5.公安、法院同话大于1 拒绝
        //$operator_info['data']['crossValidation']['number110ConnectInfo']['result'] = 2;
        //$operator_info['data']['crossValidation']['inspectionItems']['result'] = 2;
        if(isset($operator_info['data']['crossValidation']['number110ConnectInfo']['result']) && isset($operator_info['data']['crossValidation']['inspectionItems']['result'])){
            //110次数
            preg_match('/\d+/',$operator_info['data']['crossValidation']['number110ConnectInfo']['result'],$match);
            $number110ConnectInfo = (int)$match[0];
            //法院次数
            preg_match('/\d+/',$operator_info['data']['crossValidation']['inspectionItems']['result'],$match);
            $inspectionItems = (int)$match[0];
            if($number110ConnectInfo>1 ||$inspectionItems>1 ){
                $msg = '公安或法院通话大于1';
                $this->risk_not_pass($order_no,$msg);
                return false;
            }
        }

        //6.半年内通话时长小于500分钟  拒绝
        //$operator_info['data']['crossValidation']['terminatingCallDuration']['result'] = 500;
        if(isset($operator_info['data']['crossValidation']['terminatingCallDuration']['result']) && isset($operator_info['data']['crossValidation']['originatingCallDuration']['result'])){
            //拨入通话总时长
            preg_match('/\d+/',$operator_info['data']['crossValidation']['terminatingCallDuration']['result'],$match);
            $terminatingCallDuration = (int)$match[0];
            //拨出通话总时长
            preg_match('/\d+/',$operator_info['data']['crossValidation']['originatingCallDuration']['result'],$match);
            $originatingCallDuration = (int)$match[0];
            $total_minute = $terminatingCallDuration + $originatingCallDuration;
            if($total_minute < 500){
                $msg = '半年内通话时长小于500分钟';
                $this->risk_not_pass($order_no,$msg);
                return false;
            }
        }

        //7.半年内拨出时长小于200分钟  拒绝
        if(isset($operator_info['data']['crossValidation']['originatingCallDuration']['result'])){
            //拨出通话总时长
            preg_match('/\d+/',$operator_info['data']['crossValidation']['originatingCallDuration']['result'],$match);
            $originatingCallDuration = (int)$match[0]; 
            if($originatingCallDuration < 200){
                $msg = '半年内拨出时长小于200分钟';
                $this->risk_not_pass($order_no,$msg);
                return false;
            }
        }

        //能走到这里,说明运营商风控通过了
        $risk_log = [   'order_no' => $order_no,
                        'result' => 1,
                        'desc' => '通过', 
                        'addtime' => time(),
                        'risk_type' => 4,
                    ];
        Db::name('risk_log')->insert($risk_log);
        return true;
    }

    private function risk_not_pass($order_no,$msg){
            //风控不通过,修改订单状态
            $update_data = [
                'risk_status'=>2,//风控不通过
                'order_status'=>110,//审批不通过
                'refuse_time'=>time(),//风控审核时间
                'hit_type'=>4,//运营商数据验证命中
            ];
            $resdd = Db::name('order_info')->where('order_no',$order_no)->update($update_data);
            //添加风控日志
            $risk_log = ['order_no' => $order_no,
                         'result' => 0,
                         'desc' => $msg, 
                         'addtime' => time(),
                         'risk_type' => 4,
                        ];
            Db::name('risk_log')->insert($risk_log);
    }




}