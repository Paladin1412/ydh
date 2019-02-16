<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/4/17
 * Time: 17:48
 */

namespace app\admin\controller;

use think\Db;

class Echart extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_due_list_data()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['date'])) {
            $time_data             = getSearchData($post_data['date']);
            $condition['date_str'] = array(array('egt', date('Y-m-d', strtotime($time_data['start_time']))), array('elt', date('Y-m-d', strtotime($time_data['end_time']))));
        }else {
            //为了展示数据，暂时去掉默认查询当前月的数据
//            $start_time            = date('Y-m') . '-01';
//            $end_time              = date('Y-m-d');
//            $condition['date_str'] = array(array('egt', $start_time), array('elt', $end_time));
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

        $list = Db::table('report_due_day')
            ->where($condition)
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
//            ->order('date_str asc')
            ->order('date_str desc')
            ->fetchSql(false)
            ->select();

        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => &$value) {
                // 计算逾期率
                if (empty($value['order_pay_sum'])) {
                    $value['first_overdue_rate']      = $value['current_overdue_rate'] = 0;
                    $value['order_due_pd_1_3_rate']   = $value['order_due_pd_4_8_rate'] = $value['order_due_pd_9_18_rate'] = 0;
                    $value['order_due_pd_19_30_rate'] = $value['order_due_pd_31_60_rate'] = $value['order_due_pd_61_rate'] = 0;
                } else {
                    //首次逾期比
                    $value['first_overdue_rate'] = sprintf('%.4f', $value['order_today_due_sum'] / $value['order_pay_sum']) * 100;
                    //当前逾期比
                    $value['current_overdue_rate'] = sprintf('%.4f', $value['order_due_sum'] / $value['order_pay_sum']) * 100;

                    $value['order_due_pd_1_3_rate']   = sprintf('%.4f', $value['order_due_pd_1_3'] / $value['order_pay_sum']) * 100;
                    $value['order_due_pd_4_8_rate']   = sprintf('%.4f', ($value['order_due_pd_4_8'] + $value['order_due_pd_1_3']) / $value['order_pay_sum']) * 100;
                    $value['order_due_pd_9_18_rate']  = sprintf('%.4f', ($value['order_due_pd_4_8'] + $value['order_due_pd_1_3'] + $value['order_due_pd_9_18']) / $value['order_pay_sum']) * 100;
                    $value['order_due_pd_19_30_rate'] = sprintf('%.4f', ($value['order_due_pd_4_8'] + $value['order_due_pd_1_3'] + $value['order_due_pd_9_18'] + $value['order_due_pd_19_30']) / $value['order_pay_sum']) * 100;
                    $value['order_due_pd_31_60_rate'] = sprintf('%.4f', ($value['order_due_pd_4_8'] + $value['order_due_pd_1_3'] + $value['order_due_pd_9_18'] + $value['order_due_pd_19_30'] + $value['order_due_pd_31_60']) / $value['order_pay_sum']) * 100;
                    $value['order_due_pd_61_rate']    = sprintf('%.4f', ($value['order_due_pd_4_8'] + $value['order_due_pd_1_3'] + $value['order_due_pd_9_18'] + $value['order_due_pd_19_30'] + $value['order_due_pd_31_60'] + $value['order_due_pd_61']) / $value['order_pay_sum']) * 100;
                }
                // 计算分段催回率
                if (empty($value['order_today_due_sum'])) {
                    $value['order_today_repay_pd_1_3_rate']   = $value['order_today_repay_pd_4_8_rate'] = $value['order_today_repay_pd_9_18_rate'] = 0;
                    $value['order_today_repay_pd_19_30_rate'] = $value['order_today_repay_pd_31_60_rate'] = $value['order_today_repay_pd_61_rate'] = 0;
                } else {
                    $value['order_today_repay_pd_1_3_rate']   = sprintf('%.4f', $value['order_due_repay_pd_1_3'] / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_pd_4_8_rate']   = sprintf('%.4f', $value['order_due_repay_pd_4_8'] / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_pd_9_18_rate']  = sprintf('%.4f', $value['order_due_repay_pd_9_18'] / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_pd_19_30_rate'] = sprintf('%.4f', $value['order_due_repay_pd_19_30'] / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_pd_31_60_rate'] = sprintf('%.4f', $value['order_due_repay_pd_31_60'] / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_pd_61_rate']    = sprintf('%.4f', $value['order_due_repay_pd_61'] / $value['order_today_due_sum']) * 100;
                }
                // 计算累积催回率
                if (empty($value['order_today_due_sum'])) {
                    $value['order_today_repay_all_pd_1_3_rate']  = $value['order_today_repay_all_pd_1_8_rate'] = $value['order_today_repay_all_pd_1_18_rate'] = 0;
                    $value['order_today_repay_all_pd_1_30_rate'] = $value['order_today_repay_all_pd_1_60_rate'] = $value['order_today_repay_all_pd_61_rate'] = 0;
                } else {
                    $value['order_today_repay_all_pd_1_3_rate']  = sprintf('%.4f', $value['order_due_repay_pd_1_3'] / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_all_pd_1_8_rate']  = sprintf('%.4f', ($value['order_due_repay_pd_4_8'] + $value['order_due_repay_pd_1_3']) / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_all_pd_1_18_rate'] = sprintf('%.4f', ($value['order_due_repay_pd_9_18'] + $value['order_due_repay_pd_1_3'] + $value['order_due_repay_pd_4_8']) / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_all_pd_1_30_rate'] = sprintf('%.4f', ($value['order_due_repay_pd_19_30'] + $value['order_due_repay_pd_1_3'] + $value['order_due_repay_pd_4_8'] + $value['order_due_repay_pd_9_18']) / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_all_pd_1_60_rate'] = sprintf('%.4f', ($value['order_due_repay_pd_31_60'] + $value['order_due_repay_pd_1_3'] + $value['order_due_repay_pd_4_8'] + $value['order_due_repay_pd_9_18'] + $value['order_due_repay_pd_19_30']) / $value['order_today_due_sum']) * 100;
                    $value['order_today_repay_all_pd_61_rate']   = sprintf('%.4f', ($value['order_due_repay_pd_31_60'] + $value['order_due_repay_pd_1_3'] + $value['order_due_repay_pd_4_8'] + $value['order_due_repay_pd_9_18'] + $value['order_due_repay_pd_19_30'] + $value['order_due_repay_pd_61']) / $value['order_today_due_sum']) * 100;
                }
            }
        }

        $order_list_count = Db::table('report_finance_order_day')->where($condition)->count();
        $data['list']     = $list;
        $data['page']     = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / 20),
        );
        $data['field']    = lang('chart_due_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 获取各个渠道的详细
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_channel_data_list()
    {
        $post_data = request()->request();
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
        $condition['rb.company_code'] = $company_code;
        if (!empty($post_data['date'])) {
            $time_data                = getSearchData($post_data['date']);
            $condition['rb.date_str'] = array('between', array($time_data['start_time'], $time_data['end_time']));
        }
        $list = Db::table('report_business_channel_day rb')
            ->field('sum(click_cnt) as click,sum(down_cnt) as download,sum(register_cnt) as register,sum(order_cnt) as apply_order,sa.name')
            ->join('statistical_adv sa', 'sa.code = rb.channel_code')
            ->group('rb.channel_code')
            ->where($condition)
            ->fetchSql(false)
            ->select();
        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => &$value) {
                //下载/点击 转化率
                $value['download_click_rate'] = ($value['click'] == 0) ? 0 : sprintf('%.4f', $value['download'] / $value['click']) * 100;
                //注册/下载 转化率
                $value['register_download_rate'] = ($value['download'] == 0) ? 0 : sprintf('%.4f', $value['register'] / $value['download']) * 100;
                //下单/注册 转化率
                $value['order_register_rate'] = ($value['register'] == 0) ? 0 : sprintf('%.4f', $value['apply_order'] / $value['register']) * 100;
                //下单/点击 转化率
                $value['order_click_rate'] = ($value['click'] == 0) ? 0 : sprintf('%.4f', $value['apply_order'] / $value['click']) * 100;
                //下单/下载 转化率
                $value['order_download_rate'] = ($value['download'] == 0) ? 0 : sprintf('%.4f', $value['apply_order'] / $value['download']) * 100;
            }
        }
        $data = [
            'list'  => $list,
            'field' => lang('get_channel_data_list')
        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 财务统计订单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_finance_data()
    {
        $post_data = request()->post();
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
        if (!empty($post_data['date'])) {
            $time_data             = getSearchData($post_data['date']);
            $date_str              = date('Y-m-d', strtotime($time_data['start_time'])) . ' - ' . date('Y-m-d', strtotime($time_data['end_time']));
            $condition['date_str'] = array(array('egt', date('Y-m-d', strtotime($time_data['start_time']))), array('elt', date('Y-m-d', strtotime($time_data['end_time']))));
        } else {
            $start_time            = date('Y-m') . '-01';
            $end_time              = date('Y-m-d');
            $condition['date_str'] = array(array('egt', $start_time), array('elt', $end_time));

            $date_str = $start_time . ' - ' . $end_time;
        }
        // 查询回收报告柱状图信息
        // 累积放款总金额 累积还款总金额
        $company_data = Db::table('report_finance_day')
            ->field('sum(yingshou_benjin_sum) as yingshou_benjin_sum_sum,sum(huankuan_zonge_sum) as huankuan_zonge_sum_sum,sum(yingshou_benxi_sum) as yingshou_benxi_sum_sum,sum(huankuan_benxi_sum) as huankuan_benxi_sum_sum')
            ->where('company_code', $company_code)
            ->where($condition)
            ->find();

        // 债库金额
        $company_data['weihuankuan_benxi_sum_sum'] = (string)($company_data['yingshou_benxi_sum_sum'] - $company_data['huankuan_benxi_sum_sum']);
        $company_data['date_str']                  = $date_str;

        $chart_principal_list = Db::table('report_finance_day')
            ->field('date_str,benjin_huishou_rate,yingshou_benjin_sum,huankuan_zonge_sum')
            ->where('company_code', $company_code)
            ->where($condition)
            ->order('date_str asc')
            ->select();

        $data = array(
            'loan_repayment_tag'   => lang('chart_finance_loan_repayment_tag'),//回收报告
            'loan_repayment_ratio' => [
                'list'  => [
                    $company_data,
                ],
                'field' => lang('chart_loan_repayment_field'),
            ],
            'principal_tag'        => lang('chart_finance_principal_tag'), //本金回收
            'principal_ratio'      => [
                'list'  => $chart_principal_list,
                'field' => lang('chart_principal_field'),
            ],
        );
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 财务统计订单-首页
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_finance_data_all()
    {
        $company_code = session('admin_info.company_code') ? session('admin_info.company_code') : '5aab2f49c3ec9';
        // 查询回收报告柱状图信息
        // 累积放款总金额 累积还款总金额
        $company_data = Db::table('report_finance_day')
            ->field('sum(huankuan_zonge_sum) as huankuan_zonge_sum_sum,sum(yingshou_benxi_sum) as yingshou_benxi_sum_sum,sum(huankuan_benxi_sum) as huankuan_benxi_sum_sum')
            ->where('company_code', $company_code)
            ->find();

        $money_data = Db::table('report_finance_order_day')
            ->field('sum(order_success_sum) as order_success_sum_sum')
            ->where('company_code', $company_code)
            ->find();
        $company_data['yingshou_benjin_sum_sum'] = $money_data['order_success_sum_sum'];

        // 债库金额
        $company_data['weihuankuan_benxi_sum_sum'] = (string)($company_data['yingshou_benxi_sum_sum'] - $company_data['huankuan_benxi_sum_sum']);
        $company_data['date_str']                  = '2018-02-09 - ' . date('Y-m-d');

        // 本金回收图
        $chart_principal_list = Db::table('report_finance_day')
            ->field('date_str,benjin_huishou_rate,yingshou_benjin_sum,huankuan_zonge_sum')
            ->where('company_code', $company_code)
            ->order('date_str asc')
            ->select();

        $data = array(
            'loan_repayment_tag'   => lang('chart_finance_loan_repayment_tag'),//回收报告
            'loan_repayment_ratio' => [
                'list'  => [
                    $company_data,
                ],
                'field' => lang('chart_loan_repayment_field'),
            ],
            'principal_tag'        => lang('chart_finance_principal_tag'), //本金回收
            'principal_ratio'      => [
                'list'  => $chart_principal_list,
                'field' => lang('chart_principal_field'),
            ],
        );
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 逾期统计
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_due_data()
    {
        $post_data = request()->request();
        $condition = '';
        $due_where = '';
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
        if (!empty($post_data['date'])) {
            $time_data = getSearchData($post_data['date']);

            $condition['r.due_time'] = array(array('egt', strtotime($time_data['start_time'])), array('elt', strtotime($time_data['end_time'])));
            $due_where               = ' and r.due_time <= ' . strtotime($time_data['end_time']) . ' and r.due_time >= ' . strtotime($time_data['start_time']);
        }

        // 逾期未还款订单量 + 逾期已还款
        $today_due_order_num = Db::table('hunuo_order_info o')
            ->join('hunuo_order_repayment r', 'r.order_id = o.order_id')
            ->where($condition)
            ->where('o.company_code', $company_code)
            ->where('r.due_day', 'neq', 0)
            ->count();
        // 总订单
        $today_all_order_num = Db::table('hunuo_order_info o')
            ->join('hunuo_order_repayment r', 'r.order_id = o.order_id')
            ->where($condition)
            ->where('o.company_code', $company_code)
            ->count();

        // 逾期未还款订单量 + 逾期已还款
        // 总订单

        // 逾期未还款
        // 总订单


        $yihuan_order_cnt_sum = Db::table('hunuo_order_repayment')->where('bill_status', 2)->count();
        $due_order_cnt_sum    = Db::table('hunuo_order_repayment')->where('bill_status', 3)->count();;
        $weidaoqi_order_cnt_sum = Db::table('hunuo_order_repayment')->where('bill_status', 1)->count();
        // 各渠道首逾量 带时间筛选
        $channel_due_list = Db::table('statistical_adv s')
            ->field('s.name,t1.due_sum,t2.due_sum_1,t3.due_sum_2,t4.due_sum_3,t5.due_sum_4,t6.due_sum_5,t7.due_sum_6')
            ->join('(SELECT c. code, count(*) AS due_sum FROM `statistical_click` c 
            LEFT JOIN `statistical_order` `so` ON `so`.`click_id` = `c`.`id` 
            LEFT JOIN `hunuo_order_repayment` `r` ON `r`.`order_no` = `so`.`order_no` 
            LEFT JOIN `hunuo_order_info` `o` ON `o`.order_id = r.order_id           
            WHERE `r`.`due_day` != 0 and o.company_code = "' . $company_code . '" ' . $due_where . ' GROUP BY code) t1', 't1.code = s.code', 'left')
            ->join('(SELECT c. code, count(*) AS due_sum_1 FROM `statistical_click` c 
            LEFT JOIN `statistical_order` `so` ON `so`.`click_id` = `c`.`id` 
            LEFT JOIN `hunuo_order_repayment` `r` ON `r`.`order_no` = `so`.`order_no` 
            WHERE `r`.`bill_status` = 3 and due_day <= 3 GROUP BY code) t2', 't2.code = s.code', 'left')
            ->join('(SELECT c. code, count(*) AS due_sum_2 FROM `statistical_click` c 
            LEFT JOIN `statistical_order` `so` ON `so`.`click_id` = `c`.`id` 
            LEFT JOIN `hunuo_order_repayment` `r` ON `r`.`order_no` = `so`.`order_no` 
            WHERE `r`.`bill_status` = 3 and due_day <= 10 and due_day >= 4 GROUP BY code) t3', 't3.code = s.code', 'left')
            ->join('(SELECT c. code, count(*) AS due_sum_3 FROM `statistical_click` c 
            LEFT JOIN `statistical_order` `so` ON `so`.`click_id` = `c`.`id` 
            LEFT JOIN `hunuo_order_repayment` `r` ON `r`.`order_no` = `so`.`order_no` 
            WHERE `r`.`bill_status` = 3 and due_day <= 15 and due_day >= 11 GROUP BY code) t4', 't4.code = s.code', 'left')
            ->join('(SELECT c. code, count(*) AS due_sum_4 FROM `statistical_click` c 
            LEFT JOIN `statistical_order` `so` ON `so`.`click_id` = `c`.`id` 
            LEFT JOIN `hunuo_order_repayment` `r` ON `r`.`order_no` = `so`.`order_no` 
            WHERE `r`.`bill_status` = 3 and due_day >= 16 and due_day <= 30 GROUP BY code) t5', 't5.code = s.code', 'left')
            ->join('(SELECT c. code, count(*) AS due_sum_5 FROM `statistical_click` c 
            LEFT JOIN `statistical_order` `so` ON `so`.`click_id` = `c`.`id` 
            LEFT JOIN `hunuo_order_repayment` `r` ON `r`.`order_no` = `so`.`order_no` 
            WHERE `r`.`bill_status` = 3 and due_day > 30 GROUP BY code) t6', 't6.code = s.code', 'left')
            ->join('(SELECT c. code, count(*) AS due_sum_6 FROM `statistical_click` c 
            LEFT JOIN `statistical_order` `so` ON `so`.`click_id` = `c`.`id` 
            LEFT JOIN `hunuo_order_repayment` `r` ON `r`.`order_no` = `so`.`order_no` 
            WHERE `r`.`bill_status` = 3 GROUP BY code) t7', 't7.code = s.code', 'left')
            ->where('s.company_code', $company_code)
            ->where('s.status', 1)
            ->fetchSql(false)
            ->select();
        //逾期占比
        $due_day_1 = Db::table('hunuo_order_repayment')->where('due_day', 'elt', 3)->where('bill_status', 3)->count();
        $due_day_2 = Db::table('hunuo_order_repayment')->where('due_day', 'elt', 10)->where('due_day', 'egt', 4)->where('bill_status', 3)->count();
        $due_day_3 = Db::table('hunuo_order_repayment')->where('due_day', 'elt', 15)->where('due_day', 'egt', 11)->where('bill_status', 3)->count();
        $due_day_4 = Db::table('hunuo_order_repayment')->where('due_day', 'elt', 30)->where('due_day', 'egt', 16)->where('bill_status', 3)->count();
        $due_day_5 = Db::table('hunuo_order_repayment')->where('due_day', 'egt', 30)->where('bill_status', 3)->count();
        $chart_channel_in_collect_pass_list = [];
        $channel_due_list_all = [];
        $chart_due_three_days_list = [];
        $chart_due_ten_days_list = [];
        $chart_due_fifteen_days_list = [];
        $chart_due_thirty_days_list = [];
        $chart_due_over_thirty_days_list = [];
        // 首逾总量
        $due_count_sum = $due_count_sum_1 = $due_count_sum_2 = $due_count_sum_3 = $due_count_sum_4 = $due_count_sum_5 = $due_count_sum_6 = 0;
        if (is_array($channel_due_list) && !empty($channel_due_list)) {
            foreach ($channel_due_list as $key => $value) {
                $chart_channel_in_collect_pass_list[] = [
                    'name'  => $value['name'],
                    'value' => (int)$value['due_sum']
                ];
                $chart_due_three_days_list[]          = [
                    'name'  => $value['name'],
                    'value' => (int)$value['due_sum_1']
                ];
                $chart_due_ten_days_list[]            = [
                    'name'  => $value['name'],
                    'value' => (int)$value['due_sum_2']
                ];
                $chart_due_fifteen_days_list[]        = [
                    'name'  => $value['name'],
                    'value' => (int)$value['due_sum_3']
                ];
                $chart_due_thirty_days_list[]         = [
                    'name'  => $value['name'],
                    'value' => (int)$value['due_sum_4']
                ];
                $chart_due_over_thirty_days_list[]    = [
                    'name'  => $value['name'],
                    'value' => (int)$value['due_sum_5']
                ];
                $channel_due_list_all[]               = [
                    'name'  => $value['name'],
                    'value' => (int)$value['due_sum_6']
                ];
                $due_count_sum                        += $value['due_sum'];
                $due_count_sum_1                      += $value['due_sum_1'];
                $due_count_sum_2                      += $value['due_sum_2'];
                $due_count_sum_3                      += $value['due_sum_3'];
                $due_count_sum_4                      += $value['due_sum_4'];
                $due_count_sum_5                      += $value['due_sum_5'];
                $due_count_sum_6                      += $value['due_sum_6'];
            }
            $chart_channel_in_collect_pass_list[] = [
                'name'  => 'default',
                'value' => $today_due_order_num - $due_count_sum,
            ];
            $chart_due_three_days_list[]          = [
                'name'  => 'default',
                'value' => $due_day_1 - $due_count_sum_1
            ];
            $chart_due_ten_days_list[]            = [
                'name'  => 'default',
                'value' => $due_day_2 - $due_count_sum_2
            ];
            $chart_due_fifteen_days_list[]        = [
                'name'  => 'default',
                'value' => $due_day_3 - $due_count_sum_3
            ];
            $chart_due_thirty_days_list[]         = [
                'name'  => 'default',
                'value' => $due_day_4 - $due_count_sum_4
            ];
            $chart_due_over_thirty_days_list[]    = [
                'name'  => 'default',
                'value' => $due_day_5 - $due_count_sum_5
            ];
            $channel_due_list_all[]               = [
                'name'  => 'default',
                'value' => $due_order_cnt_sum - $due_count_sum_6
            ];
        }

        $data = array(
            // 入催率
            'in_collect_ratio_tag'              => lang('chart_in_collect_ratio_tag'),
            'in_collect_ratio'                  => [
                'list' => [
                    [
                        'name'  => lang('chart_in_collect_ratio_ok'),
                        'value' => $today_all_order_num - $today_due_order_num,
                    ],
                    [
                        'name'  => lang('chart_in_collect_ratio_due'),
                        'value' => $today_due_order_num,
                    ]
                ],
            ],
            // 渠道入催率
            'channel_in_collect_pass_ratio_tag' => lang('chart_channel_in_collect_pass_ratio_tag'),
            'channel_in_collect_pass_ratio'     => [
                'list' => $chart_channel_in_collect_pass_list,
            ],
            // 逾期/订单占比
            'due_order_ratio_tag'               => lang('chart_due_order_ratio_tag'),
            'due_order_ratio'                   => [
                'list' => [
                    [
                        'name'  => lang('chart_due_ratio_on_due'),//逾期中
                        'value' => (int)$due_order_cnt_sum,
                    ],
                    [
                        'name'  => lang('chart_due_ratio_ok'),//已还款
                        'value' => (int)$yihuan_order_cnt_sum,
                    ],
                    [
                        'name'  => lang('chart_due_ratio_not_over'),//未到期
                        'value' => (int)$weidaoqi_order_cnt_sum,
                    ],
                ]
            ],
            //逾期占比
            'due_ratio_tag'                     => lang('chart_due_ratio_tag'),
            'due_ratio'                         => [
                'list' => [
                    [
                        'name'  => lang('chart_due_day_1'),
                        'value' => $due_day_1,
                    ],
                    [
                        'name'  => lang('chart_due_day_2'),
                        'value' => $due_day_2,
                    ],
                    [
                        'name'  => lang('chart_due_day_3'),
                        'value' => $due_day_3,
                    ],
                    [
                        'name'  => lang('chart_due_day_4'),
                        'value' => $due_day_4,
                    ],
                    [
                        'name'  => lang('chart_due_day_5'),
                        'value' => $due_day_5,
                    ],
                ],
            ],
            'channel_due_ratio_tag'             => lang('chart_channel_due_ratio_tag'),
            'channel_due_ratio'                 => [
                'list' => $channel_due_list_all
            ],
            'due_three_days_ratio_tag'          => lang('chart_due_three_days_ratio_tag'),
            'due_three_days_ratio'              => [
                'list' => $chart_due_three_days_list,
            ],
            'due_ten_days_ratio_tag'            => lang('chart_due_ten_days_ratio_tag'),
            'due_ten_days_ratio'                => [
                'list' => $chart_due_ten_days_list,
            ],
            'due_fifteen_days_ratio_tag'        => lang('chart_due_fifteen_days_ratio_tag'),
            'due_fifteen_days_ratio'            => [
                'list' => $chart_due_fifteen_days_list,
            ],
            'due_thirty_days_ratio_tag'         => lang('chart_due_thirty_days_ratio_tag'),
            'due_thirty_days_ratio'             => [
                'list' => $chart_due_thirty_days_list,
            ],
            'due_over_thirty_days_ratio_tag'    => lang('chart_due_over_thirty_days_ratio_tag'),
            'due_over_thirty_days_ratio'        => [
                'list' => $chart_due_over_thirty_days_list,
            ]
        );
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 风控图表接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_risk_data()
    {
        $post_data = request()->request();
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

        $condition = [];

        if (!empty($post_data['date'])) {
            $time_data = getSearchData($post_data['date']);
            $start_time = strtotime($time_data['start_time']);
            $end_time = strtotime($time_data['end_time']);
            $where = ['add_time'=>['between',[$start_time,$end_time]]];
            $condition['oi.add_time'] =['between',[$start_time,$end_time]];
        }

        $where['company_code'] = $company_code;
        $condition['oi.company_code'] = $company_code;
        $condition['oi.risk_status'] = 1;

        //查找所有渠道的风控通过数
        $risk_list = Db::table('statistical_adv a')
            ->field('a.name,a.code,count(1) as num')
            ->join('statistical_order so','so.code = a.code','left')
            ->join('hunuo_order_info oi','so.order_no = oi.order_no')
            ->where($condition)
            ->group('a.code')
            ->select();

        //查找所有渠道的信审通过数
        unset($condition['oi.risk_status']);
        $condition['oi.handle_state'] = 2;
        $handle_list = Db::table('statistical_adv a')
            ->field('a.name,a.code,count(1) as num')
            ->join('statistical_order so','so.code = a.code','left')
            ->join('hunuo_order_info oi','so.order_no = oi.order_no')
            ->where($condition)
            ->group('a.code')
            ->select();

        // 总申请量
        $order_all_count = Db::table('hunuo_order_info')->where($where)->fetchSql(false)->count();

        // 风控过件量
        $where['risk_status'] = 1;
        $order_risk_pass_count = Db::table('hunuo_order_info')->where($where)->fetchSql(false)->count();

        // 风控未过件量
        $where['risk_status'] = 2;
        $order_risk_notpass_count = Db::table('hunuo_order_info')->where($where)->fetchSql(false)->count();

        //信审通过量
        unset($where['risk_status']);
        $where['handle_state'] = 2;
        $order_handle_pass_count = Db::table('hunuo_order_info')->where($where)->fetchSql(false)->count();

        //信审未通过量
        $where['handle_state'] = 3;
        $order_handle_notpass_count = Db::table('hunuo_order_info')->where($where)->fetchSql(false)->count();

        //获取所有启用的渠道
        $channel = Db::table('statistical_adv')->field('code,name')->where(['status'=>1,'company_code'=>$company_code])->fetchSql(false)->select();

        //风控渠道赋值
        $channel1 = $channel2 = $channel;
        if(count($risk_list)>0){
            foreach ($channel1 as $key => $value) {
                foreach ($risk_list as $k => &$val){
                    if($value['code']===$val['code']){
                        $channel1[$key]['value'] = $val['num'];
                        break;
                    }else{
                        $channel1[$key]['value'] = 0;
                    }
                }
            }
        }else{
                foreach ($channel1 as $k => &$val){
                    $channel1[$k]['value'] = 0;
                }          
        }

        //信审渠道赋值
        if(count($handle_list)>0){
            foreach ($channel2 as $key => $value) {
                foreach ($handle_list as $k => &$val){
                    if($value['code']===$val['code']){
                        $channel2[$key]['value'] = $val['num'];
                        break;
                    }else{
                        $channel2[$key]['value'] = 0;
                    }
                }
            }
        }else{
                foreach ($channel2 as $k => &$val){
                    $channel2[$k]['value'] = 0;
                }           
        }

        $data = array(
            // 跑分占比(相当于风控通过占比)
            'risk_pass_ratio_tag'             => '风控过件占比',
            'risk_pass_ratio'                 => [
                'list' => [
                    [
                        'value' => (int)$order_risk_pass_count,
                        'name'  => '风控过件量'
                    ],
                    [
                        'value' => $order_risk_notpass_count,
                        'name'  => '风控未过件量'
                    ],
                ],
            ],
            // 渠道/风控过件占比
            'channel_risk_pass_ratio_tag'     => '渠道/过件占比',
            'channel_risk_pass_ratio'         => [
                'list' => $channel1,
            ],
            // 信审通过量占比
            'approval_pass_ratio_tag'         => '信审/风控过件占比',
            'approval_pass_ratio'             => [
                'list' => [
                    [
                        'name'  => '信审通过量',
                        'value' => (int)$order_handle_pass_count,
                    ],
                    [
                        'name'  => '信审未通过量',
                        'value' => $order_handle_notpass_count,
                    ]
                ],

            ],
            // 渠道/信审过件占比
            'channel_approval_pass_ratio_tag' => '渠道/信审占比',
            'channel_approval_pass_ratio'     => [
                'list' => $channel2,
            ],
        );
        return json(['code' => 200, 'message' => '完成', 'data' => $data]);
    }

    /**
     * 经营分析接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_business_data()
    {
        $post_data = request()->request();
        if (session('admin_info.company_id') == '0') {
            // 总公司
            if ($post_data['company_id'] == '0' || !empty($post_data['company_id'])) {
                $company_code = getCompanyCode($post_data['company_id']);
            } else {
                $company_code = '5aab2f49c3ec9';
            }
        } else {
            $company_code = session('admin_info.company_code');
        }
        if (!empty($post_data['date'])) {
            $time_data = getSearchData($post_data['date']);
            $where     = 'where c.addtime  between "' . $time_data['start_time'] . '" and "' . trim($time_data['end_time']) . '" ';
        } else {
            $where = '';
        }

        $list = Db::table('statistical_adv s')
            ->field('t1.click_cnt_sm,t2.down_cnt_sum,t3.register_cnt_sum,t4.order_cnt_sum,s.name')
            ->join('(select code,count(*) as click_cnt_sm from statistical_click c ' . $where . '  group by code) t1', 't1.code = s.code', 'left')
            ->join('(select c.code,count(*) as down_cnt_sum from statistical_download d LEFT JOIN statistical_click c  ON d.click_id = c.id ' . $where . ' group by code) t2', 't2.code = s.code', 'left')
            ->join('(select c.code,count(*) as register_cnt_sum from statistical_register r LEFT JOIN statistical_click c  ON r.click_id = c.id ' . $where . ' group by code) t3', 't3.code = s.code', 'left')
            ->join('(select c.code,count(*) as order_cnt_sum from statistical_order o LEFT JOIN statistical_click c  ON o.click_id = c.id INNER JOIN hunuo_order_info o2 ON o2.order_no = o.order_no ' . $where . ' group by code) t4', 't4.code = s.code', 'left')
            ->where('s.status', 1)
            ->fetchSql(false)
            ->where('s.company_code', $company_code)
            ->select();

        $company_data = [];

        if (is_array($list) && !empty($list)) {
            foreach ($list as $key => $value) {
                $chart_channer_list[]             = [
                    'name'  => $value['name'],
                    'value' => (int)$value['click_cnt_sm'],
                ];
                $chart_download_list[]            = [
                    'name'  => $value['name'],
                    'value' => (int)$value['down_cnt_sum'],
                ];
                $chart_regiest_list[]             = [
                    'name'  => $value['name'],
                    'value' => (int)$value['register_cnt_sum'],
                ];
                $chart_apply_list[]               = [
                    'name'  => $value['name'],
                    'value' => (int)$value['order_cnt_sum'],
                ];
                $company_data['click_cnt_sum']    += $value['click_cnt_sm'];
                $company_data['down_cnt_sum']     += $value['down_cnt_sum'];
                $company_data['register_cnt_sum'] += $value['register_cnt_sum'];
                $company_data['order_cnt_sum']    += $value['order_cnt_sum'];
            }
        } else {
            $chart_channer_list            = $chart_download_list = $chart_regiest_list = $chart_apply_list = [];
            $company_data['click_cnt_sum'] = $company_data['down_cnt_sum'] = $company_data['register_cnt_sum'] = $company_data['order_cnt_sum'] = 0;
        }

        $data = array(
            //总点击量
            'click_amount_tag'           => lang('chart_click_amount_tag'),
            'click_amount'               => [
                'list' => [
                    [
                        'value' => $company_data['click_cnt_sum'],
                        'name'  => lang('chart_click_amount_tag')
                    ]
                ]
            ],
            // 渠道占比
            'channel_ratio_tag'          => lang('chart_channel_ratio_tag'),
            'channel_ratio'              => [
                'list' => $chart_channer_list
            ],
            // 下载占比
            'download_ratio_tag'         => lang('chart_download_ratio_tag'),
            'download_ratio'             => [
                'list' => [
                    [
                        'value' => (int)$company_data['down_cnt_sum'],
                        'name'  => lang('chart_download_ratio_tag_success')
                    ],
                    [
                        'value' => $company_data['click_cnt_sum'] - $company_data['down_cnt_sum'],
                        'name'  => lang('chart_download_ratio_tag_fail')
                    ],
                ],
            ],
            // 渠道下载占比
            'channel_download_ratio_tag' => lang('chart_channel_download_ratio_tag'),
            'channel_download_ratio'     => [
                'list' => $chart_download_list
            ],
            // 注册占比
            'reg_ratio_tag'              => lang('chart_reg_ratio_tag'),
            'reg_ratio'                  => [
                'list' => [
                    [
                        'value' => (int)$company_data['register_cnt_sum'],
                        'name'  => lang('chart_reg_ratio_tag_success')
                    ],
                    [
                        'value' => $company_data['down_cnt_sum'] - $company_data['register_cnt_sum'],
                        'name'  => lang('chart_reg_ratio_tag_fail')
                    ]
                ],
            ],
            // 渠道注册占比
            'channel_reg_ratio_tag'      => lang('chart_channel_reg_ratio_tag'),
            'channel_reg_ratio'          => [
                'list' => $chart_regiest_list,
            ],
            // 申请占比
            'apply_ratio_tag'            => lang('chart_apply_ratio_tag'),
            'apply_ratio'                => [
                'list' => [
                    [
                        'value' => (int)$company_data['order_cnt_sum'],
                        'name'  => lang('chart_apply_ratio_tag_success')
                    ],
                    [
                        'value' => $company_data['register_cnt_sum'] - $company_data['order_cnt_sum'],
                        'name'  => lang('chart_apply_ratio_tag_fail')
                    ]
                ],
            ],
            // 渠道申请占比
            'channel_apply_ratio_tag'    => lang('chart_channel_apply_ratio_tag'),
            'channel_apply_reg_ratio'    => [
                'list' => $chart_apply_list
            ],
        );
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }


    // ----------------------- ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑  2018年6月8日14:35:38 新增

    /**
     * 信审考核报表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_handle_data()
    {
        $start_time = strtotime(date('Y-m-d 00:00:00'));
        $end_time   = strtotime(date('Y-m-d 23:59:59'));
        $list       = Db::name('hunuo_order_handle_user')->alias('l')
            ->field('u.real_name,l1.order_count,l2.order_todo_count')
            ->join('system_admin_v2 u', 'u.admin_id = l.admin_id')
            ->join("(SELECT count(*) as order_count, admin_id from hunuo_order_handle_user WHERE order_state in (2,3) and  change_time between {$start_time} and {$end_time}  GROUP BY admin_id) l1", 'l1.admin_id = u.admin_id', 'left')
            ->join('(SELECT count(*) as order_todo_count, admin_id from hunuo_order_handle_user WHERE order_state = 1  GROUP BY admin_id) l2', 'l2.admin_id = u.admin_id', 'left')
            ->where('u.company_code', session('admin_info.company_code'))
            ->fetchSql(false)
            ->group('u.real_name')
            ->select();
        if (!empty($list) && is_array($list)) {
            $data['xAxis']            = [];
            $data['order_count']      = [];
            $data['order_todo_count'] = [];
            foreach ($list as $key => $value) {
                $data['xAxis'][]            = $value['real_name'];
                $data['order_count'][]      = $value['order_count'] ? $value['order_count'] : 0;
                $data['order_todo_count'][] = $value['order_todo_count'] ? $value['order_todo_count'] : 0;
            }
        } else {
            $data['xAxis']            = [];
            $data['order_count']      = [];
            $data['order_todo_count'] = [];
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 初始化表格数据
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    //public function order_count_init()
    //{
    //    // 订单统计
    //    $order_list = Db::name('hunuo_order_info')->field('count(order_id) AS `order_count`,date_format(from_unixtime(`add_time`),"%Y-%m-%d") AS `order_time`')->group('order_time')->select();
    //    // 信审订单
    //    $order_handle_list = Db::name('hunuo_order_handle_user')->field('count(order_id) AS `order_count`,date_format(from_unixtime(`change_time`),"%Y-%m-%d") AS `order_time`')->where('order_state', 'neq', 1)->group('order_time')->fetchSql(false)->select();
    //
    //    static $order_num = 0;
    //    foreach ($order_list as $key => $value) {
    //        $order_num                             += $value['order_count'];
    //        $order_list_data[$value['order_time']] = $order_num;
    //    }
    //    unset($order_num);
    //
    //    static $order_handle_num = 0;
    //    foreach ($order_handle_list as $key1 => $value1) {
    //        $order_handle_num                              += $value1['order_count'];
    //        $order_handle_list_data[$value1['order_time']] = $order_handle_num;
    //    }
    //    unset($order_handle_num);
    //    //$min_time = Db::name('hunuo_order_info')->order('order_id')->value('add_time');
    //    $min_time = strtotime(date('Y') . '-01-01');
    //    $max_time = strtotime(date('Y-m-d 23:59:59'));
    //    for ($start = $min_time; $start < $max_time; $start += 24 * 60 * 60) {
    //        $time = date('Y-m-d', $start);
    //
    //        if (!array_key_exists($time, $order_list_data)) {
    //            $order_list_data[$time] = $order_list_data[date('Y-m-d', $start - 24 * 60 * 60)] ? $order_list_data[date('Y-m-d', $start - 24 * 60 * 60)] : 0;
    //        }
    //    }
    //    ksort($order_list_data);
    //    for ($start = $min_time; $start < $max_time; $start += 24 * 60 * 60) {
    //        $time = date('Y-m-d', $start);
    //        if (!array_key_exists($time, $order_handle_list_data)) {
    //            $order_handle_list_data[$time] = $order_handle_list_data[date('Y-m-d', $start - 24 * 60 * 60)] ? $order_handle_list_data[date('Y-m-d', $start - 24 * 60 * 60)] : 0;
    //        }
    //    }
    //    ksort($order_handle_list_data);
    //
    //    $add_data = [];
    //    for ($start = $min_time; $start < $max_time; $start += 24 * 60 * 60) {
    //        $time       = date('Y-m-d', $start);
    //        $add_data[] = [
    //            'order_time'         => $time,
    //            'order_count'        => array_key_exists($time, $order_list_data) ? $order_list_data[$time] : 0,
    //            'order_handle_count' => array_key_exists($time, $order_handle_list_data) ? $order_handle_list_data[$time] : 0,
    //            'add_time'           => time(),
    //        ];
    //    }
    //    Db::name('hunuo_echart_order_count')->insertAll($add_data);
    //}

    /**
     * 信审订单走势报表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_count_data()
    {
        $request   = request();
        $post_data = $request->param();
        $type      = $post_data['type'] ? $post_data['type'] : 1;
        if (!empty($post_data['date'])) {
            $type      = 4;
            $time_data = getSearchData($post_data['date']);
        }
        // 1最近7天(本周) 最近30天
        switch ($type) {
            case 1:
                $data_list = Db::name('hunuo_report_day')
                    ->field('date_str as order_time,risk_count as order_count,xinshen_all_count as order_handle_count')
                    ->where('company_code', session('admin_info.company_code'))
                    ->limit(7)
                    ->order('id desc')
                    ->fetchSql(false)
                    ->select();
                break;
            case 2:
                $data_list = Db::name('hunuo_report_day')
                    ->field('date_str as order_time,risk_count as order_count,xinshen_all_count as order_handle_count')
                    ->where('company_code', session('admin_info.company_code'))
                    ->limit(30)
                    ->order('id desc')
                    ->fetchSql(false)
                    ->select();
                break;
            case 4:
                $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                $data_list  = Db::name('hunuo_report_day')
                    ->field('date_str as order_time,risk_count as order_count,xinshen_all_count as order_handle_count')
                    ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                    ->where('company_code', session('admin_info.company_code'))
                    ->fetchSql(false)
                    ->select();
                break;
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => array_sort_chat($data_list, 'id')]);
    }

    /**
     * 渠道订单占比
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_from_view()
    {
        $admin_company_id = session('admin_info.company_id');
        if ($admin_company_id === '0') {
            $company_id = request()->post('company_id');
            if (!empty($company_id) || $company_id === '0') {
                $company_code = getCompanyCode($company_id);
                $res          = Db::name('hunuo_order_info')->field('count(0) value,source as name')->where('company_code', $company_code)->group('source')->select();
            } else {
                $res = Db::name('hunuo_order_info')->field('count(0) value,source as name')->group('source')->select();
            }
        } else {
            $res = Db::name('hunuo_order_info')->field('count(0) value,source as name')->where('company_code', session('admin_info.company_code'))->group('source')->select();
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $res]);
    }

    /**
     * 渠道用户量注册占比
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_from_view()
    {
        $company_id = session('admin_info.company_id');
        if ($company_id === '0') {
            $company_id = request()->post('company_id');
            if (!empty($company_id) || $company_id === '0') {
                $company_code = getCompanyCode($company_id);
                $res          = Db::name('hunuo_users')->field('count(0) value,source as name')->where('company_code', $company_code)->group('source')->select();
            } else {
                $res = Db::name('hunuo_users')->field('count(0) value,source as name')->group('source')->select();
            }
        } else {
            $res = Db::name('hunuo_users')->field('count(0) value,source as name')->where('company_code', session('admin_info.company_code'))->group('source')->select();
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $res]);
    }

    /**
     * 渠道风控通过量占比
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function risk_order_view()
    {
        $company_id = session('admin_info.company_id');
        if ($company_id === '0') {
            $company_id = request()->post('company_id');
            if (!empty($company_id) || $company_id === '0') {
                $company_code = getCompanyCode($company_id);
                $res          = Db::name('hunuo_order_info')->field('count(0) value,if( order_status >= 160 ,"order_ok","order_fail") as order_type')->where('company_code', $company_code)->group('order_type')->select();
            } else {
                $res = Db::name('hunuo_order_info')->field('count(0) value,if( order_status >= 160 ,"order_ok","order_fail") as order_type')->group('order_type')->select();
            }
        } else {
            $res = Db::name('hunuo_order_info')->field('count(0) value,if( order_status >= 160 ,"order_ok","order_fail") as order_type')->where('company_code', session('admin_info.company_code'))->group('order_type')->select();
        }
        if (!empty($res) && is_array($res)) {
            foreach ($res as $value) {
                $data[] = array(
                    'value' => $value['value'],
                    'name'  => $value['order_type']
                );
            }
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 渠道坏账量对比
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function brake_order_view()
    {
        $company_id = session('admin_info.company_id');
        if ($company_id === '0') {
            $company_id = request()->post('company_id');
            if (!empty($company_id) || $company_id === '0') {
                $company_code = getCompanyCode($company_id);
                $res          = Db::name('hunuo_order_info')->alias('o')->field('count(0) value,o.source as name')->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')->where('r.due_day', 'gt', '15')->where('company_code', $company_code)->group('source')->fetchSql(false)->select();
            } else {
                $res = Db::name('hunuo_order_info')->alias('o')->field('count(0) value,o.source as name')->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')->where('r.due_day', 'gt', '15')->group('source')->fetchSql(false)->select();
            }
        } else {
            $res = Db::name('hunuo_order_info')->alias('o')->field('count(0) value,o.source as name')->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')->where('r.due_day', 'gt', '15')->where('company_code', session('admin_info.company_code'))->group('source')->fetchSql(false)->select();
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $res]);
    }

    /**
     * 渠道逾期订单占比
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function due_order_view()
    {
        $company_id = session('admin_info.company_id');
        if ($company_id === '0') {
            $company_id = request()->post('company_id');
            if (!empty($company_id) || $company_id === '0') {
                $company_code = getCompanyCode($company_id);
                $res          = Db::name('hunuo_order_info')->alias('o')->field('count(0) value,o.source as name')
                    ->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')
                    ->where('r.due_day', 'gt', '0')->where('company_code', $company_code)
                    ->group('source')
                    ->fetchSql(false)
                    ->select();
            } else {
                $res = Db::name('hunuo_order_info')->alias('o')->field('count(0) value,o.source as name')->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')->where('r.due_day', 'gt', '0')->group('source')->fetchSql(false)->select();
            }
        } else {
            $res = Db::name('hunuo_order_info')->alias('o')->field('count(0) value,o.source as name')->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')->where('r.due_day', 'gt', '0')->where('company_code', session('admin_info.company_code'))->group('source')->fetchSql(false)->select();
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $res]);
    }

    /**
     * 新老用户下单占比
     * @return \think\response\Json
     */
    public function old_new_order_view()
    {
        $company_id = session('admin_info.company_id');
        if ($company_id === '0') {
            $company_id = request()->post('company_id');
            if (!empty($company_id) || $company_id === '0') {
                $company_code = getCompanyCode($company_id);
                $company_code = $company_code ? $company_code : '';
                $sql          = 'select count(*) value,name from (SELECT count(0) as old_sum,if(COUNT(0)>1,"old","new") name FROM `hunuo_order_info` where `company_code` = "' . $company_code . '" GROUP BY user_id)a GROUP BY name';
            } else {
                $sql = 'select count(*) value,name from (SELECT count(0) as old_sum,if(COUNT(0)>1,"old","new") name FROM `hunuo_order_info` GROUP BY user_id)a GROUP BY name';
            }
        } else {
            $sql = 'select count(*) value,name from (SELECT count(0) as old_sum,if(COUNT(0)>1,"old","new") name FROM `hunuo_order_info` where `company_code` = "' . session('admin_info.company_code') . '" GROUP BY user_id)a GROUP BY name';
        }
        $res = Db::query($sql);
        return json(['code' => 200, 'message' => lang('success'), 'data' => $res]);
    }

    /**
     * 统计分析 注册量 订单量 逾期订单量 坏账量
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function count_view()
    {
        $request   = request();
        $post_data = $request->param();
        $type      = $post_data['type'] ? $post_data['type'] : 1;
        if (!empty($post_data['date'])) {
            $type      = 4;
            $time_data = getSearchData($post_data['date']);
        }
        if (!empty($post_data['company_id']) || $post_data['company_id'] === '0') {
            $company_code = getCompanyCode($post_data['company_id']);
        } else {
            $company_code = session('admin_info.company_code');
        }
        if ($post_data['company_id'] === '') {
            // 总公司数据
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(order_count) order_count,sum(reg_user_count) reg_user_count,sum(due_order_count) due_order_count,sum(death_order_count) death_order_count')
                        ->group('date_str')
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(order_count) order_count,sum(reg_user_count) reg_user_count,sum(due_order_count) due_order_count,sum(death_order_count) death_order_count')
                        ->group('date_str')
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 4:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,sum(order_count) order_count,sum(reg_user_count) reg_user_count,sum(due_order_count) due_order_count,sum(death_order_count) death_order_count')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->group('date_str')
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        } else {
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,order_count,reg_user_count,due_order_count,death_order_count')
                        ->where('company_code', $company_code)
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,order_count,reg_user_count,due_order_count,death_order_count')
                        ->where('company_code', $company_code)
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 4:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,order_count,reg_user_count,due_order_count,death_order_count')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->where('company_code', $company_code)
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        }

        return json(['code' => 200, 'message' => lang('success'), 'data' => ['data_list' => array_sort_chat($data_list, 'id'), 'field' => lang('chart_count_view')]]);
    }

    /**
     * 统计分析 放款金额 逾期金额 坏账金额
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function money_count_data()
    {
        $request   = request();
        $post_data = $request->param();
        $type      = $post_data['type'] ? $post_data['type'] : 1;
        if (!empty($post_data['date'])) {
            $type      = 4;
            $time_data = getSearchData($post_data['date']);
        }
        if (!empty($post_data['company_id']) || $post_data['company_id'] === '0') {
            $company_code = getCompanyCode($post_data['company_id']);
        } else {
            $company_code = session('admin_info.company_code');
        }
        if ($post_data['company_id'] === '') {
            // 总公司数据
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(sum_amount) sum_amount,sum(due_amount) due_amount,sum(death_amount) death_amount')
                        ->group('date_str')
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(sum_amount) sum_amount,sum(due_amount) due_amount,sum(death_amount) death_amount')
                        ->group('date_str')
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 4:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,sum(sum_amount) sum_amount,sum(due_amount) due_amount,sum(death_amount) death_amount')
                        ->group('date_str')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        } else {
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum_amount,due_amount,death_amount')
                        ->where('company_code', $company_code)
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum_amount,due_amount,death_amount')
                        ->where('company_code', $company_code)
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 4:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,sum_amount,due_amount,death_amount')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->where('company_code', $company_code)
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => ['data_list' => array_sort_chat($data_list, 'id'), 'field' => lang('chart_money_count_data')]]);
    }

    /**
     * 统计分析 风控通过率 信审通过率 总通过率
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pass_count_data()
    {
        $request   = request();
        $post_data = $request->param();
        $type      = $post_data['type'] ? $post_data['type'] : 1;
        if (!empty($post_data['date'])) {
            $type      = 4;
            $time_data = getSearchData($post_data['date']);
        }
        if (!empty($post_data['company_id']) || $post_data['company_id'] === '0') {
            $company_code = getCompanyCode($post_data['company_id']);
        } else {
            $company_code = session('admin_info.company_code');
        }
        if ($post_data['company_id'] === '') {
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(risk_rate) risk_rate,sum(xinshen_rate) xinshen_rate,sum(all_rate) all_rate ')
                        ->group('date_str')
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(risk_rate) risk_rate,sum(xinshen_rate) xinshen_rate,sum(all_rate) all_rate ')
                        ->group('date_str')
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 4:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,sum(risk_rate) risk_rate,sum(xinshen_rate) xinshen_rate,sum(all_rate) all_rate ')
                        ->group('date_str')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        } else {
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,risk_rate,xinshen_rate,all_rate ')
                        ->where('company_code', $company_code)
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,risk_rate,xinshen_rate,all_rate ')
                        ->where('company_code', $company_code)
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 4:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,risk_rate,xinshen_rate,all_rate ')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->where('company_code', $company_code)
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        }

        foreach ($data_list as &$value) {
            $value['all_rate']  = number_format($value['all_rate'], 2);
            $value['risk_rate'] = number_format($value['risk_rate'], 2);
            $value['risk_rate'] = number_format($value['xinshen_rate'], 2);
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => ['data_list' => array_sort_chat($data_list, 'id'), 'field' => lang('chart_pass_count_data')]]);
    }

    /**
     * 统计分析 当天注册去申请借款的比率
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function one_day_count_data()
    {
        $request   = request();
        $post_data = $request->param();
        $type      = $post_data['type'] ? $post_data['type'] : 1;
        if (!empty($post_data['date'])) {
            $type      = 3;
            $time_data = getSearchData($post_data['date']);
        }
        if (!empty($post_data['company_id']) || $post_data['company_id'] === '0') {
            $company_code = getCompanyCode($post_data['company_id']);
        } else {
            $company_code = session('admin_info.company_code');
        }
        if ($post_data['company_id'] === '') {
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(jiekuan_rate) jiekuan_rate')
                        ->group('date_str')
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,sum(jiekuan_rate) jiekuan_rate')
                        ->group('date_str')
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 3:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,sum(jiekuan_rate) jiekuan_rate')
                        ->group('date_str')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        } else {
            switch ($type) {
                case 1:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,jiekuan_rate')
                        ->where('company_code', $company_code)
                        ->limit(7)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 2:
                    $data_list = Db::name('hunuo_report_day')
                        ->field('date_str,jiekuan_rate')
                        ->where('company_code', $company_code)
                        ->limit(30)
                        ->order('id desc')
                        ->fetchSql(false)
                        ->select();
                    break;
                case 3:
                    $start_data = date('Y-m-d', strtotime($time_data['start_time']));
                    $end_data   = date('Y-m-d', strtotime($time_data['end_time']));
                    $data_list  = Db::name('hunuo_report_day')
                        ->field('date_str,jiekuan_rate')
                        ->where(array('date_str' => array('between', "{$start_data},{$end_data}")))
                        ->where('company_code', $company_code)
                        ->fetchSql(false)
                        ->select();
                    break;
            }
        }

        return json(['code' => 200, 'message' => lang('success'), 'data' => ['data_list' => array_sort_chat($data_list, 'id'), 'field' => lang('chart_one_day_count_data')]]);
    }


}