<?php
/**
 * Created by sublime
 * User: andy.deng
 * Date: 2018/9/15
 * Time: 10:42
 */

namespace TupulianRisk;

use Redis\redisServer;
use think\Db;
use Baiqishi\Baiqishi;
use BaiRong\VerifyBlacklist;
use XinYan\NewLook;
class Risk
{

    protected $order_info = null;
    protected $user_id = null;
    protected $env = null;
    protected $user_info = null;
    protected $is_open_mongo = null;

    public function __construct($env, $is_open_mongo = false)
    {
        $this->env = $env;
        $this->is_open_mongo = $is_open_mongo;
    }

    /**
     * 过滤订单信息
     * @param $order_no
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function verifyData($order_no, $xinyan_token)
    {

        if (empty($order_no)) {
            return json(['status' => '400', 'message' => '订单号不能为空']);
        }
        $order_info = Db::name('order_info')
            ->where('order_no', $order_no)
            ->find(); 
        if (empty($order_info)) {
            return json(['status' => '400', 'message' => '订单不存在']);
        }
        $this->order_info = $order_info;
        $this->user_id  = $order_info['user_id'];;
        $this->user_info = Db::name('users')->where('user_id',$this->user_id)->find();

        //百融验证 因为对ip地址有要求所以得加这个判断
        if($_SERVER['SERVER_NAME'] == 'developing.api.ydh.china.tupulian.com' || $_SERVER['SERVER_NAME'] == '39.108.26.98'){
            $brinfo = $this->risk_bairong();
            if(empty($brinfo)) exit;
        }

        //新颜黑镜验证
        $obj = new NewLook($this->env);
        $mirrorRes = $obj->get_black_mirror($xinyan_token,$this->user_info['idcode'],$this->user_info['phone'],$this->user_info['name'],$order_no,$this->is_open_mongo);
        if(empty($mirrorRes)) exit;

        //调用白骑士
        $rescheck = $this->risk_check($order_no);
        if(empty($rescheck)) exit;

        //调用运营商
        $ocr_result = action('loan/Operators/operator_risk',[['phone'=>$this->user_info['phone'],'order_no'=>$order_no]]);

        //新颜行为雷达验证
        $xwldRes = $obj->get_behavioral_radar_info($this->user_info['idcode'],$this->user_info['phone'],$this->user_info['name'],$order_no,$this->is_open_mongo);
        if(empty($xwldRes)) exit;

        //新颜负面探针 负面拉黑
        $fmlhRes = $obj->get_negative_pull_black_info($this->user_info['idcode'],$this->user_info['phone'],$this->user_info['name'],$order_no,$this->is_open_mongo);
        if(empty($fmlhRes)) exit;

        //新颜负面探针 负面洗白  我们暂时不支持该产品
        /*$fmxbRes = $obj->get_negative_wash_info($this->user_info['idcode'],$this->user_info['phone'],$this->user_info['name'],$order_no,$this->is_open_mongo);
        if(empty($fmxbRes)) exit;*/
    }


    /**
     * 风控百融验证
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function risk_bairong()
    {
        //进入风控 开始百融黑名单验证 获取tokenid
        $baiRong_tokenid = redisServer::getInstance()->get('bairong_tokenid');
        $obj = new VerifyBlacklist($this->env);
        if(empty($baiRong_tokenid)){
            $resInfo = $obj->get_tokenid($this->order_info['order_no']);
            if(empty($resInfo['status'])) return false;
            $baiRong_tokenid = $resInfo['tokenid'];
        }
        //根据tokenid对百融黑名单进行验证
        $checkRes = $obj->get_check_bairong($baiRong_tokenid,$this->user_info['idcode'],$this->user_info['phone'],$this->user_info['name'],$this->order_info['order_no'],$this->is_open_mongo);
        if(empty($checkRes)) return false;
        return true;
    }


    /**
     * 风控白骑士验证
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function risk_check($order_no)
    {
        $user_info = $this->user_info;
        $bqs = new Baiqishi($this->env);
        $post_parms = [
            'mobile' => $user_info['phone'],//手机号
            'certNo' => $user_info['idcode'],//身份证号
        ];
        $result = $bqs->decision($post_parms);
        if($result['finalDecision']==='Accept'){
            $risk_result = 1;
            $msg = '白骑士反欺诈风控审批通过';
        }else{
            $risk_result = 0;
            $msg = '白骑士反欺诈风控审批未通过';
        }
        //添加风控日志
        Db::name('risk_log')->insert(['order_no' => $order_no, 'result' => $risk_result, 'desc' => $msg, 'addtime' => time(),'risk_type'=>3]);
        trace($order_no.'白骑士反欺诈返回信息'.json_encode($result));

        if($this->is_open_mongo == true) mongo_log('risk_log', ['order_no' => $order_no, 'result' => $risk_result, 'desc' => $result, 'addtime' => time()]);

        if ($result['finalDecision'] != 'Accept') {
            //风控不通过
            $update_data = [
                'risk_status'=>2,//风控不通过
                'order_status'=>110,//审批不通过
                'refuse_time'=>time(),//风控审核时间
                'hit_type'=>3,//风控命中类型
            ];
            $resdd = Db::name('order_info')->where('order_no',$order_no)->update($update_data);
            if(empty($resdd)){
                //添加风控日志
                Db::name('risk_log')->insert(['order_no' => $order_no, 'result' => $risk_result, 'desc' => '白骑士反欺诈风控审批未通过,更该订单状态失败', 'addtime' => time(),'risk_type'=>3]);
            }
            return false;
        }
        //通过也要添加日志
        //Db::name('risk_log')->insert(['order_no' => $order_no, 'result' => $risk_result, 'desc' => '通过', 'addtime' => time(),'risk_type'=>3]);
        return true;
    }
  

}