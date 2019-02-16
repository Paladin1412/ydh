<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/10
 * Time: 19:01
 */

namespace app\loan\controller;

use Redis\redisServer;
use think\Db;

class Article extends Common
{
    // 文章类 展示合同和意见反馈
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 平台服务协议-借款协议
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function agreement()
    {
        $company_code = request()->param('company_code','','trim');
        $user_id = request()->param('user_id',0);
        if (empty($company_code)){
            return json(['status' => '500', 'message' => '公司编号不能为空', 'data' => []]);
        }


        if($this->is_open_redis == true){
            if (!empty($user_id) && redisServer::getInstance()->exists('user_info:user_' . $user_id)) {
                $user = json_decode(redisServer::getInstance()->get('user_info:user_' . $user_id), true);
            } else {
                $user = null;
            }
        }else{
            if (!empty($user_id) && \think\Cache::has('user_info:user_' . $user_id)) {
                $user = json_decode(\think\Cache::get('user_info:user_' . $user_id), true);
            } else {
                $user = null;
            }
        }

        $bank = Db::name('bankcard')->alias('bc')
            ->field('bc.bank_id,bc.name,bc.card_num,bc.bankcard_name')
            ->where('user_id', $user_id)
            ->order('bankcard_id', 'desc')
            ->find();

       $loan_type =  Db::name('loan_type')->where(['company_code'=>$company_code])->find();

        //生成保存合同编号
        $contract_number             = $contract_number2 = date('Ymd') . $user_id;
        $fee['amount']               = $loan_type['apply_amount'];
        $fee['b_amount']             = get_amount($fee['amount']);
        $fee['platform_service_fee'] = $fee['amount'] * $loan_type['manage_fee'];
        $fee['info_fee']             = $fee['amount'] * $loan_type['approval_fee'];
        $fee['service_fee']          = $fee['amount'] * $loan_type['service_fee'];
        $day                         = $loan_type['apply_term'];
        $ymd                         = date('Y-m-d');
        $due_ymd                     = date('Y-m-d', strtotime("+$day day"));
        $this->assign([
            'user'             => $user,
            'bank'             => $bank,
            'fee'              => $fee,
            'ymd'              => $ymd,
            'due_ymd'          => $due_ymd,
            'contract_number'  => $contract_number,
            'contract_number2' => $contract_number2,

        ]);
        return $this->fetch('article/agreement');
    }

    /**
     * 意见反馈
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function feed_back()
    {
        $this->check_login();
        $user_id = request()->post('user_id', 0, 'intval');
        $content = request()->post('content');
        $data    = array(
            'user_id'  => $user_id,
            'content'  => $content,
            'add_time' => time(),
            'is_read'  => 0,
        );

        $res = Db::name('feed_back')->insert($data);
        if ($res !== false) {
            return json(['status' => '200', 'message' => lang('feed_back_succ'), 'data' => []]);
        } else {
            return json(['status' => '500', 'message' => lang('feed_back_err'), 'data' => []]);
        }
    }
}