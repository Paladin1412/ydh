<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 10:24
 */

namespace app\loan\controller;

use think\Db;

class Bill extends Common
{
    // 业务接口
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 申请记录
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function application()
    {
        $this->check_login();
        $user_id    = request()->post('user_id', 0, 'intval');
        $type_appli = request()->post('type_appli');
        if ($type_appli == 1) {
            //未还账单
            $where = '1 and user_id = ' . $user_id;
        } elseif ($type_appli == 2) {
            //逾期账单
            $where = 'order_status <> 200 and user_id = ' . $user_id;
        } else {
            //历史账单
            $where = 'order_status = 200 and user_id = ' . $user_id;
        }
        $application = Db::name('order_info')->alias('o')
            ->field('o.order_id,o.order_no,r.amount,o.order_status,o.type,o.add_time,r.due_time,o.application_amount,o.loan_amount')
            ->join('order_repayment r', 'r.order_id=o.order_id', 'LEFT')
            ->order('o.add_time desc')
            ->where($where)
            ->select();
        foreach ($application as $key => $value) {
            // 	订单列表页的状态需要转换 未放款完成前都属于审批中
            if (in_array($value['order_status'], ['90', '100', '160'])) {
                $application[$key]['order_status'] = 90;
            }
            if (in_array($value['order_status'], ['169'])) {
                $application[$key]['order_status'] = 110;
            }

            if ($value['order_status'] < 170) {
                $application[$key]['amount']   = intval($value['application_amount']);
                if ($value['order_status'] != 100) {
                    $application[$key]['due_time'] = '0';
                }
            } else {
                $application[$key]['amount'] = intval($value['loan_amount']);
            }
        }
        return json(['status' => '200', 'message' => lang('success'), 'data' => $application]);
    }

    /**
     * 借款滚动列表
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function scroll_list()
    {
        $BillList = Db::name('order_info')->alias('o')
            ->field('o.order_id,o.phone,r.amount,o.add_time')
            ->join('order_repayment r', 'r.order_id=o.order_id', 'LEFT')
            ->order('r.repay_id desc')
            ->where('order_status', 200)
            ->limit(10)
            ->select();
        // echo Db::name('order_info')->alias('o')->getlastsql();
        // dump($BillList);
        //exit;
        $BillList       = empty($BillList) ? array() : $BillList;
        $free_fase_data = $this->free_admission();
        $loan_type      = Db::name('loan_type')->where(array("status" => 1))->find();
        if (!empty($BillList)) {
            foreach ($BillList as $key => $value) {
                $BillList[$key]['istrue'] = 1;
                $BillList[$key]['type']   = 1;
                $BillList[$key]['amount'] = 1000;
            }
        }
        $fase_data = $this->false_bill($BillList, $loan_type["apply_amount"]);
        if (!empty($fase_data)) {
            $BillList = array_merge($BillList, $fase_data);
        }
        if (NULL != $free_fase_data) {
            $BillList = array_merge($BillList, $free_fase_data);
        }
        $arr           = array(
            'BillList' => $BillList,
        );
        $arr["canUse"] = $loan_type["apply_amount"];
        return json(['status' => '200', 'message' => lang('success'), 'data' => $arr]);
    }

    private function false_bill($billlist, $money, $istrue = 0, $type = 1)
    {
        $false_count = count($billlist) * 2;
        $data        = array();
        while ($false_count-- && $false_count > 0) {
            $arr = range(0, 10);
            shuffle($arr);
            $phone = "1";
            foreach ($arr as $k => $v) {
                $phone .= $v;
            }
            $return_data = array(
                "amount"   => $money,
                "phone"    => substr($phone, 0, 10),
                "add_time" => "",
                "order_id" => "",
                "istrue"   => $istrue,
                "type"     => $type
            );
            array_push($data, $return_data);
        }
        return $data;
    }

    private function free_admission()
    {
        $free_data = Db::name("free_admission")->field("phone,money as amount")->select();
        if (!empty($free_data)) {
            $amount = 0;
            foreach ($free_data as $key => $value) {
                $free_data[$key]['type']     = 2;
                $free_data[$key]['istrue']   = 1;
                $free_data[$key]['add_time'] = "";
                $free_data[$key]['order_id'] = "";
                $amount                      = $value["amount"];
            }
            $free_fase_data = $this->False_bill($free_data, $amount, 0, 2);
            return array_merge($free_data, $free_fase_data);
        }
        return NULL;
    }
}