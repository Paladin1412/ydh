<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/9/13
 * Time: 17:19
 */
return [
    'channel' => [
        'package_name' => 'com.tupulian.p2p.android.indonesia',
    ],
    //白名单
    'white_lib' => [
        'Pay/agency_pay',//放款
        'Pay/agency_pay_callback',//代付回调
        'Pay/quick_pay_callback',//快捷支付回调
        'Pay/audit_failure',//审核失败推送

        'Xynotice/notice_credit_request',//授信请求翔一异步通知
        'Xynotice/expire_remind',//用户额度变更翔一异步通知
        'Xynotice/pay_callback',//易贷还第三方支付回调
        'Xynotice/credit_enquiry',//获取征信

        //之前的白名单
        'Activity/home',
        'Common/education_list',
        'Index/ad_banner',
        'Common/city_list',
        'Bank/banks_list',
        'Common/industry_list',
        'Common/profession_list',
        'Common/relation_list',
        'Common/tel',
        'Bill/scroll_list',
        'Activity/activity_list',
        'Article/agreement',
        'Operators/h5_backurl',
        'Operators/h5_failurl',

        //定时任务
        'Help/update_overdue_fee',//修改逾期费用
        'Help/expire_remind',//到期提示还款
        'Help/get_operators_h5_data',//获取h5运营商数据
        'Help/get_operators_json_data',//获取json运营商数据
    ],
];