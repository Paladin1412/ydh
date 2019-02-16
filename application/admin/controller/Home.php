<?php
/**
 * User: andy.deng
 * Date: 2018/8/21
 * Time: 14:43
 */

namespace app\admin\controller;

use think\Db;

class Home extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取首页数据
    public function get_home_data(){

    	//今日开始时间戳
    	$today_start_time = strtotime(date('Y-m-d'));
    	//今日结束时间戳
    	$today_end_time = strtotime(date('Y-m-d',strtotime('+1 day')));
    	$return_data = [];


    	//用户-总用户数
    	$return_data['user_report']['all_user_num'] = $all_user_num = Db::table('hunuo_users')->count();

    	//用户-今日新增用户
    	$return_data['user_report']['today_new_user']  = $today_new_user = Db::table('hunuo_users')->where(['reg_time'=>['between',[$today_start_time,$today_end_time]]])->count();

    	//今日新增借款人
    	$borrower_data = Db::table('hunuo_users')->field('user_id')->where(['reg_time'=>['between',[$today_start_time,$today_end_time]],'photo_assay'=>['<>','']])->select();
    	$return_data['user_report']['today_new_borrower'] = $today_new_borrower = count($borrower_data);

    	//今日新增投资人
    	$invest_data = Db::connect("db_config_invest")->table('invest_user_info')->field('user_id')->where(['last_auth_time'=>['between',[$today_start_time,$today_end_time]],'is_auth'=>2])->select();
    	$return_data['user_report']['today_new_invester'] = $today_new_invester = count($invest_data);



    	//产品-总标的数
    	$return_data['product_report']['all_project_num'] = $all_project_num = Db::connect("db_config_invest")->table('invest_project')->where(['is_delete'=>0])->count();

    	//产品-总金额
    	$return_data['product_report']['all_project_sum'] = $all_project_sum = Db::connect("db_config_invest")->table('invest_project')->where(['is_delete'=>0])->sum('project_money');

    	//投标中
    	$return_data['product_report']['bid_project_num'] = $bid_project_num = Db::connect("db_config_invest")->table('invest_project')->where(['project_status'=>1])->count();

    	//还款中
    	$return_data['product_report']['repayment_project_num'] = $repayment_project_num = Db::connect("db_config_invest")->table('invest_project')->where(['project_status'=>3])->count();
    	
    	//已结清
    	$return_data['product_report']['close_project_num'] = $close_project_num = Db::connect("db_config_invest")->table('invest_project')->where(['project_status'=>4,'is_delete'=>0])->count();



    	//累计充值
    	$total_recharge_sum = Db::connect("db_config_invest")->table('invest_recharge_order')->where(['order_status'=>2])->sum('money');
    	$return_data['capital_report']['total_recharge_sum'] = $total_recharge_sum?$total_recharge_sum:0;

    	//累计提现
    	$total_withdraw_sum = Db::connect("db_config_invest")->table('invest_withdraw_order')->where(['status'=>3])->sum('withdraw_money');
    	$return_data['capital_report']['total_withdraw_sum'] = $total_withdraw_sum?$total_withdraw_sum:0;

		//累计投资
    	$total_invest_sum = Db::connect("db_config_invest")->table('invest_order_info')->sum('use_money');
    	$return_data['capital_report']['total_invest_sum'] = $total_invest_sum?$total_invest_sum:0;

    	//累计放款金额
    	$total_loan_sum = Db::table('hunuo_order_info')->where(['order_status'=>['egt',170]])->sum('approval_amount');
    	$return_data['capital_report']['total_loan_sum'] = $total_loan_sum?$total_loan_sum:0;

    	//累计还款
        $total_repayment_sum = Db::table('hunuo_order_repayment')->where(['bill_status'=>2])->sum('paid_amount');
        $return_data['capital_report']['total_repayment_sum'] = $total_repayment_sum?$total_repayment_sum:0;

        //今日充值
        $today_recharge_sum = Db::connect("db_config_invest")->table('invest_recharge_order')->where(['order_status'=>2,'success_time'=>['between',[$today_start_time,$today_end_time]]])->sum('money');
        $return_data['capital_report']['today_recharge_sum'] = $today_recharge_sum?$today_recharge_sum:0;

        //今日提现
        $today_withdraw_sum = Db::connect("db_config_invest")->table('invest_withdraw_order')->where(['status'=>3,'success_time'=>['between',[$today_start_time,$today_end_time]]])->sum('withdraw_money');
        $return_data['capital_report']['today_withdraw_sum'] = $today_withdraw_sum?$today_withdraw_sum:0;

        //今日投资
        $today_invest_sum = Db::connect("db_config_invest")->table('invest_order_info')->where(['add_time'=>['between',[$today_start_time,$today_end_time]]])->sum('use_money');
        $return_data['capital_report']['today_invest_sum'] = $today_invest_sum?$today_invest_sum:0;

        //今日放款
        $today_loan_sum = Db::table('hunuo_order_info')->where(['order_status'=>['egt',170],'lending_time'=>['between',[$today_start_time,$today_end_time]]])->sum('approval_amount');
        $return_data['capital_report']['today_loan_sum'] = $today_loan_sum?$today_loan_sum:0;

        //今日还款
        $today_repayment_sum = Db::table('hunuo_order_repayment')->where(['bill_status'=>2,'success_time'=>['between',[$today_start_time,$today_end_time]]])->sum('paid_amount');
        $return_data['capital_report']['today_repayment_sum'] = $today_repayment_sum?$today_repayment_sum:0;


        $lang_arr = [lang('user_report'), lang('product_report'), lang('capital_report')];
        return json(['code' => 200, 'message' => lang('success'), 'data' => ['report_data' => $return_data,'field' => $lang_arr]]);
    }


}