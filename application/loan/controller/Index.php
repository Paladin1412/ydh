<?php


namespace app\loan\controller;

use think\Db;

class Index extends Common
{
    // APP首页 和 广告 版本接口
    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        exit('欢迎来到客户端');
    }

    /**
     * 获取最新版本
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function version_up()
    {
        $version     = request()->post('version');
        $app_type    = request()->post('app_type');
        $new_version = Db::name('app_version')->where(array('app_type' => $app_type, 'status' => 2))->order('id desc')->find();
        if (!empty($new_version) && $new_version['version'] > $version) {
            $data = array(
                'is_update'  => 1,
                'update_url' => $new_version['update_url'],
                'content'    => $new_version['content']
            );
            return json(['status' => '200', 'message' => lang('app_version_allow_update'), 'data' => $data]);
        } else {
            return json(['status' => '200', 'message' => lang('app_version_not_update'), 'data' => array('is_update' => 0)]);
        }
    }


    /**
     * APP 2.0 新版首页数据
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function app_home()
    {
        $company_code = request()->header('COMPANYCODE');
        $company_code = !empty($company_code) ? $company_code : '5aab2f49c3ec9';
        $user_id      = request()->post('user_id');
        // dump($user_id);
        // exit;
        $order_info   = Db::name('order_info')
            ->field('type,order_id,order_no,type,add_time,application_amount,application_term,order_status,handle_state,risk_status,pay_status,repay_time')
            ->where(array('user_id' => $user_id))
            ->order('order_id', 'desc')
            ->find();   
        if (empty($order_info)) {
            $product_info = Db::name('loan_type')->where(array("status" => 1, 'company_code' => $company_code))->find();
        } else {
            $product_info = Db::name('loan_type')->where(array("status" => 1, 'type_id' => $order_info['type'],'company_code' => $company_code))->find();
        }
        $actual_money = $product_info['apply_amount'] * (1 - $product_info['service_fee'] - $product_info['approval_fee'] - $product_info['manage_fee']);
        $rate_money   = $product_info['apply_amount'] * $product_info['rate'] * $product_info['apply_term'];
        //$handling_money = $product_info['apply_amount'] * $product_info['handling_fee'] * $product_info['apply_term'];
        // 用户不存在最近的订单或最近订单已完成
        /*if ($this->env == 'dev') {
            $product_info['open'] = 1;
        }*/
        // 用户不存在订单或者订单为已完成
        if (empty($order_info) || $order_info['order_status'] == 200) {
            $result = array(
                'app_type'      => (string)($order_info['order_status'] == 200) ? 7 : 1,
                'apply_amount'  => (string)$product_info['apply_amount'],
                'apply_term'    => (string)$product_info['apply_term'],
                //'service_fee'   => (string)($product_info['service_fee'] + $product_info['approval_fee'] + $product_info['manage_fee']),
                'service_fee'   => '0.03504',
                //'service_money' => (string)($product_info['apply_amount'] - $actual_money),
                'service_money' => '245.28',
                //'rate'          => (string)$product_info['rate'],
                'rate'          => '0.00067',
                //'rate_money'    => (string)$rate_money,
                'rate_money'    => '0.67',
                'due_money'     => (string)($product_info['apply_amount'] + $rate_money),
                'actual_money'  => (string)$actual_money,
                'has_order'     => (string)($order_info['order_status'] == 200) ? 1 : 0,
                'is_open'       => (string)($product_info['open'] == 1) ? 1 : 0,
            );
        }
        if (in_array($order_info['order_status'], array(1,80, 90, 100, 110, 160, 169))) {
            switch ($order_info['order_status']) {
                case 1:
                    $app_type = 2;//用户下单在审核中
                    break;
                case 90:
                    $app_type = 2;//用户下单在审核中
                    break;
                case 100:
                    $app_type = 2;//2018年7月25日15:35:22 需求调整 关闭审批通过 改为 审批中
                    //$app_type = 4;//用户下单在审核中
                    break;
                case 110:
                    $app_type = 3;//用户下单在审核失败
                    break;
                case 160:
                    //$app_type = 4;//用户存在放款中的订单
                    $app_type = 2;//2018年7月25日15:35:22 需求调整 关闭放款中 改为 审批中
                    break;
                case 169:
                    $app_type = 3;//2018年7月25日15:35:22 需求调整 关闭放款失败
                    //$app_type = 5;//用户存在放款失败的订单
                    break;
                case 80:
                    $app_type = 3;//2018年9月14日17:13:35 需求调整 添加等待face++识别
                    break;
                // case 89:
                //     $app_type = 9;//2018年9月14日17:13:55 需求调整 face++识别失败3次 审核失败
                //     break;
            }
            $result = array(
                'app_type'           => (string)$app_type,
                'application_amount' => (string)$order_info['application_amount'],
                'application_term'   => (string)$order_info['application_term'],
                'add_time'           => (string)$order_info['add_time'],
                'actual_money'       => (string)$actual_money,
                'order_id'           => (string)$order_info['order_id'],
                'risk_status'        => (string)$order_info['risk_status'],
                'pay_status'         => (string)$order_info['pay_status'],
                'handle_state'       => (string)$order_info['handle_state']
            );
        }
        // 用户存在待还款订单
        if (in_array($order_info['order_status'], array(170, 180))) {
            $bill_info = Db::name('order_repayment')
                ->field('repay_id,amount,repay_amount,due_time,loan_term,bill_status,overdue_fee,due_day')
                ->where(array('order_id' => $order_info['order_id']))
                ->find();
            if ((int)$bill_info['bill_status'] === 3) {
                $repay_money = $bill_info['repay_amount'] + $bill_info['overdue_fee'];
            } else {
                $repay_money = $bill_info['repay_amount'];
            }
            $is_reduction = Db::table('daihou_case_reduction')
                ->where(array('order_no' => $order_info['order_no'], 'reduction_status' => 1))
                ->value('reduction_fee');
            if (!empty($is_reduction)) {
                $repay_money -= $is_reduction;
            }
            $result = array(
                'app_type'           => (string)6,
                'application_amount' => (string)$order_info['application_amount'],
                'application_term'   => (string)$order_info['application_term'],
                'add_time'           => (string)$order_info['add_time'],
                'repay_money'        => (string)$repay_money,
                'repay_time'         => (string)$order_info['repay_time'],
                'due_day'            => (string)($bill_info['bill_status'] == 3) ? $bill_info['due_day'] : 0,
                'repay_id'           => (string)$bill_info['repay_id'],
                'order_id'           => (string)$order_info['order_id'],
                'risk_status'        => (string)$order_info['risk_status'],
                'pay_status'         => (string)$order_info['pay_status'],
                'handle_state'       => (string)$order_info['handle_state']
            );
        }
        if(in_array($order_info['order_status'], array(190))){//还款中
            $bill_info = Db::name('order_repayment')
                ->field('repay_id,amount,repay_amount,due_time,loan_term,bill_status,overdue_fee,due_day')
                ->where(array('order_id' => $order_info['order_id']))
                ->find();
            if ($bill_info['bill_status'] == 3) {
                $repay_money = $bill_info['repay_amount'] + $bill_info['overdue_fee'];
            } else {
                $repay_money = $bill_info['repay_amount'];
            }
            $is_reduction = Db::table('daihou_case_reduction')
                ->where(array('order_no' => $order_info['order_no'], 'reduction_status' => 1))
                ->value('reduction_fee');
            if (!empty($is_reduction)) {
                $repay_money -= $is_reduction;
            }
            $result = array(
                'app_type'           => (string)10,
                'application_amount' => (string)$order_info['application_amount'],
                'application_term'   => (string)$order_info['application_term'],
                'add_time'           => (string)$order_info['add_time'],
                'repay_money'        => (string)$order_info['application_amount'],
                'repay_time'         => (string)$order_info['repay_time'],
                'due_day'            => (string)($bill_info['bill_status'] == 3) ? $bill_info['due_day'] : 0,
                'repay_id'           => (string)$bill_info['repay_id'],
                'order_id'           => (string)$order_info['order_id'],
                'risk_status'        => (string)$order_info['risk_status'],
                'pay_status'         => (string)$order_info['pay_status'],
                'handle_state'       => (string)$order_info['handle_state']
            );
        }
        if(in_array($order_info['order_status'], array(195))){//还款中
            $bill_info = Db::name('order_repayment')
                ->field('repay_id,amount,repay_amount,due_time,loan_term,bill_status,overdue_fee,due_day')
                ->where(array('order_id' => $order_info['order_id']))
                ->find();
            if ($bill_info['bill_status'] == 3) {
                $repay_money = $bill_info['repay_amount'] + $bill_info['overdue_fee'];
            } else {
                $repay_money = $bill_info['repay_amount'];
            }
            $is_reduction = Db::table('daihou_case_reduction')
                ->where(array('order_no' => $order_info['order_no'], 'reduction_status' => 1))
                ->value('reduction_fee');
            if (!empty($is_reduction)) {
                $repay_money -= $is_reduction;
            }
            $result = array(
                'app_type'           => (string)11,
                'application_amount' => (string)$order_info['application_amount'],
                'application_term'   => (string)$order_info['application_term'],
                'add_time'           => (string)$order_info['add_time'],
                'repay_money'        => (string)$order_info['application_amount'],
                'repay_time'         => (string)$order_info['repay_time'],
                'due_day'            => (string)($bill_info['bill_status'] == 3) ? $bill_info['due_day'] : 0,
                'repay_id'           => (string)$bill_info['repay_id'],
                'order_id'           => (string)$order_info['order_id'],
                'risk_status'        => (string)$order_info['risk_status'],
                'pay_status'         => (string)$order_info['pay_status'],
                'handle_state'       => (string)$order_info['handle_state']
            );
        }
        return json(['status' => '200', 'message' => lang('success'), 'data' => $result]);
    }


    /**
     * 获取最新banner图
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function ad_banner()
    {
        $ad_info = Db::name('ads')
            ->field('original_img,link')
            ->where('cat_id', 1)
            ->order('ads_id desc')
            ->find();

        $ad_info['original_img'] = $ad_info ? get_oss_image(config('auth_' . $this->env . '.OSS'), $ad_info['original_img']) : '';
        return json(['status' => '200', 'message' => lang('success'), 'data' => $ad_info]);
    }
}
