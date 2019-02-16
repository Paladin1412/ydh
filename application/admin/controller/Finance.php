<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/29
 * Time: 10:48
 */

namespace app\admin\controller;

use think\Db;

class Finance extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    
    //放款统计
    public function pay_list()
    {
        $request   = request();
        $post_data = $request->param();
        $post_data['company_id'] = $request->post('company_id', '', 'trim');
        $condition = array();
        if (!empty($post_data['date'])) {
            $time_data             = getSearchData($post_data['date']);
            $condition['date_str'] = array(array('egt', date('Y-m-d', strtotime($time_data['start_time']))), array('elt', date('Y-m-d', strtotime($time_data['end_time']))));
        }
        if (session('admin_info.company_id') == '0') {
            // 总公司
            if ($post_data['company_id'] == '0' || !empty($post_data['company_id'])) {
                $company_code = getCompanyCode($post_data['company_id']);
            } else {
                $company_code = '5aab2f49c3ec9';
            }
        } else {
            // 分公司
            $company_code = session('admin_info.company_code');
        }
        $condition['company_code'] = $company_code;
        $limit                     = isset($post_data['limit']) ? $post_data['limit'] : 20;
        $list                      = Db::table('report_finance_order_day')
            ->where($condition)
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $limit, $limit)
            ->order('date_str desc')
            ->fetchSql(false)
            ->select();
        $order_list_count          = Db::table('report_finance_order_day')->where($condition)->count();
        $data['list']              = $list;
        $data['page']              = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => isset($post_data['limit']) ? $post_data['limit'] : 20,
            'cols'  => ceil($order_list_count / 20),
        );
        $data['field']             = lang('finance_pay_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    //还款统计
    public function all_list()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        $condition2 = array();
        $condition3 = array();
        if (!empty($post_data['date'])) {
            $time_data = getSearchData($post_data['date']);

            $condition['date_str'] = array(array('egt', date('Y-m-d', strtotime($time_data['start_time']))), array('elt', date('Y-m-d', strtotime($time_data['end_time']))));
            $condition2['date_str'] = array(array('egt', date('Y-m-d',strtotime($time_data['start_time'])-60*60*24*14)), array('elt',  date('Y-m-d',strtotime($time_data['end_time'])-60*60*24*14)));

            $condition3['due_time'] = array(array('egt', strtotime($time_data['start_time'])), array('elt', strtotime($time_data['end_time'])));
        }

        if (session('admin_info.company_id') == '0') {
            // 总公司
            if ($post_data['company_id'] == '0' || !empty($post_data['company_id'])) {
                $company_code = getCompanyCode($post_data['company_id']);
            } else {
                $company_code = '5aab2f49c3ec9';
            }

        } else {
            // 分公司
            $company_code = session('admin_info.company_code');
        }
        $condition['company_code'] = $company_code;
        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }

        $list = Db::table('report_finance_day')
            ->field('date_str,yinghuan_order_cnt,yihuan_order_cnt,weihuan_order_cnt,yingshou_benjin_sum,yingshou_benxi_sum,yingshou_zongjine_sum,huankuan_benxi_sum
            ,huankuan_zonge_sum,benjin_huishou_rate,yingshou_huishou_rate,zong_huishou_rate')
            ->where($condition)
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order('date_str desc')
            ->fetchSql(false)
            ->select();
        if (is_array($list) && !empty($list)) {
            foreach ($list as $key => &$value) {
                $value['benjin_huishou_rate']   = sprintf('%.2f', $value['benjin_huishou_rate']);
                $value['yingshou_huishou_rate'] = sprintf('%.2f', $value['yingshou_huishou_rate']);
                $value['zong_huishou_rate']     = sprintf('%.2f', $value['zong_huishou_rate']);
            }
        }
        $order_list_count = Db::table('report_finance_day')->where($condition)->count();

        // 申请笔数 信审通过笔数
        $order_info = Db::table('hunuo_report_day')
            ->field('sum(order_count) as order_apply_sum,sum(xinshen_count) as order_handle_sum')
            ->where($condition2)
            ->where('company_code',$company_code)
            ->find();

        // 放款合同金额 实际放款金额 总回收金额
        $money_info = Db::table('report_finance_day')
            ->field('sum(yingshou_benxi_sum) as ys_ht_amount,sum(yingshou_benjin_sum) as ys_bj_amount,sum(huankuan_zonge_sum) as hk_ze_amount,sum(yihuan_order_cnt) as yihuan_order_cnt_sum')
            ->where($condition)
            ->find();

        $ht_money = Db::table('hunuo_order_repayment r')
            ->join('hunuo_order_info o', 'o.order_id = r.order_id')
            ->where('o.company_code', $company_code)
            ->where($condition3)
            ->fetchSql(false)
            ->sum('application_amount');

        $sum_list[] = array(
            'name'               => lang('finance_all_sum_name'),
            'order_apply_sum'    => (int)$order_info['order_apply_sum'],// 申请笔数
            'order_handle_sum'   => (int)$order_info['order_handle_sum'],// 信审通过笔数
            'order_handle_rate'  => $order_info['order_apply_sum'] ? (sprintf('%.4f', ($order_info['order_handle_sum'] / $order_info['order_apply_sum'])) * 100) : 0,// 过件率
            'order_ht_amount'    => (int)$ht_money,// 放款合同金额
            'order_bj_amount'    => (int)$money_info['ys_bj_amount'],//实际放款金额
            'order_repay_sum'    => (int)$money_info['yihuan_order_cnt_sum'],// 总回收笔数
            'order_repay_amount' => (int)$money_info['hk_ze_amount'],//总回收金额
            'order_profit'       => $money_info['hk_ze_amount'] - $money_info['ys_bj_amount'],// 利润
            'order_profit_rate'  => $money_info['ys_bj_amount'] ? (sprintf('%.4f', ($money_info['hk_ze_amount'] - $money_info['ys_bj_amount']) / $money_info['ys_bj_amount']) * 100) : 0,// 利润率
        );

        $data['list']      = $list;
        $data['page']      = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / 20),
        );
        $data['field']     = lang('finance_all_list');
        $data['sum_list']  = $sum_list;
        $data['sum_field'] = lang('finance_all_sum_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

}