<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 14:32
 */
return array(
    // Error code
    'error_4001'               => 'Request error',
    'error_4002'               => 'Request parameters can not be empty',
    'error_4003'               => 'Verification code error',
    'error_4004'               => 'Please fill in the account number code',
    'error_4005'               => 'Account password is incorrect',
    'error_4006'               => 'The account is disable, please contact the administrator',
    'error_4007'               => 'The user has already existed',
    'error_4008'               => 'Network error, please contact the administrator',
    'error_4009'               => 'The company has a role and can not delete the company',
    'error_4010'               => 'An error occurred while uploading the picture',
    'error_4011'               => 'An error occurred when uploading OSS',
    'error_4012'               => 'The state of the cooperative company is not in conformity',
    'error_4013'               => 'The company that the account belongs to is forbidden. Please contact the administrator',
    'error_4014'               => 'The company administrator account has already existed',
    'error_5001'               => 'Do not overstep the right to operate',
    'error_8001'               => 'The log in period has expired',
    'error_4015'               => 'Please fill in the reason why not',
    'error_4016'               => 'Please fill in the note',

    // 通用部分
    'is_login'                 => 'Already logged in',
    'success'                  => 'Complete',
    'is_status'                => 'There is status change, do not repeat operation',
    'data_empty'               => 'Data is empty',
    'order_status_fail'        => 'Order state is incompatible',
    'auto_mode_no_order'       => 'The orders to be allocated have not been found yet',
    'auto_mode_no_user'        => 'The company has no trust personnel',

    // 合同金额语言包
    'data_lf'                  => '数据太长，没有这么大的钱吧，检查下',
    'lyz'                      => '零元整',
    'ints'                     => '整',
    'm_num'                    => '零壹贰叁肆伍陆柒捌玖',
    'm_dw'                     => '分角元拾佰仟万拾佰仟亿',

    //---------------------------------- ------------ 员工系统表头
    // 员工列表
    'personnel_list'           => array(
        'user_name' => 'User name',
        'real_name' => 'Collector',
        'email'     => 'E-mail address',
        'role_name' => 'Role name',
        'cp_name'   => 'Affiliated company',
        'add_time'  => 'Application date',
        'operate'   => 'Operation',
    ),
    // 员工日志
    'personnel_log'            => array(
        'user_name' => 'User name',
        'real_name' => 'Full name',
        'cp_name'   => 'Affiliated company',
        'log_ip'    => 'IP',
        'log_time'  => 'Operation time',
        'log_info'  => 'Describe',
    ),
    // 角色管理
    'role_index'               => array(
        'role_name' => 'Role name',
        'role_desc' => 'Role description',
        'cp_name'   => 'Affiliated company',
        'role_info' => 'Describe',
        'operate'   => 'Operation',
    ),

    //------- ------------------------------------ 业务系统表头
    // 订单列表
    'order_index'              => array(
        'order_no'           => 'Order No.',
        'name'               => 'User name',
        'repay_time'         => 'Expected repayment date',
        'phone'              => 'Phone No',
        'source'             => 'Source of channel',
        'add_time'           => 'Application date',
        'application_amount' => 'Loan amount',
        'application_term'   => 'Term of loan',
        'order_status'       => 'Order status',
        'end_time'           => 'Time of entry',
        'region_name'        => 'Region',
        'due_day'            => 'Days overdue',
        'handle_state'            => 'Approval status',
        'pay_status'            => 'Lending status',
        'risk_status'            => 'Risk control status',
    ),
    // 客户列表
    'user_list'                => array(
        'name'     => 'User name',
        'idcode'   => 'ID No',
        'phone'    => 'Phone No',
        'source'   => 'Source of channel',
        'reg_time' => 'Registration time',
    ),
    // 订单详情
    'order_info'               => array(
        'order_no'           => 'Order No.',
        'name'               => 'User name',
        'phone'              => 'Phone No',
        'application_amount' => 'Loan amount',
        'paid_amount'        => 'Amount of repayment',
        'bankcard_name'      => 'Bank name',
        'card_num'           => 'Bank card number',
        'add_time'           => 'Loan time',
        'refuse_time'        => 'Approval time',
        'lending_time'       => 'Lending time',
        'end_time'           => 'Time to Pay back ',
        'order_status'       => 'State',
        'not_pass_info'      => 'Not to pass the reason',
    ),
    // 借款扣款记录信息
    'pay_log'                  => array(
        'cmd'       => 'Notification type',
        't_id'      => 'Transaction order number',
        'bt_id'     => 'Account',
        'status'    => 'State',
        'price'     => 'Transaction amount',
        'currency'  => 'Trading currency',
        'productid' => 'BluePlay product ID',
        'add_time'  => 'Notification time',
    ),
    // 还款扣款记录信息
    'repay_log'                => array(
        'cmd'       => 'Notification type',
        't_id'      => 'Transaction order number',
        'status'    => 'State',
        'price'     => 'Transaction amount',
        'currency'  => 'Trading currency',
        'productid' => 'BluePlay product ID',
        'add_time'  => 'Notification time',
    ),
    // 还款扣款记录信息
    'repay_code_log'                => array(
        'code'       => 'Recharge code',
        'add_time'      => 'Generation time',
        'status'    => 'status',
    ),
    // 费用配置
    'system_list'              => array(
        'type_id'      => 'Number',
        'company_name' => 'Corporate name',
        'apply_term'   => 'Term of loan',
        'apply_amount' => 'Loan amount',
        'rate'         => 'Daily interest rate',
        'service_fee'  => 'Platform service fee',
        'approval_fee' => 'Information audit rate',
        'over_fee'     => 'Overdue rate',
        'term_fee'     => 'Repayment interest',
        'max_money'    => 'Daily loan',
        'operate'      => 'Operation'
    ),

    //--------------- ---------------------------- 审批系统表头
    // 所有审批
    'order_all'                => array(
        'order_no'           => 'Order No.',
        'user_name'          => 'User name',
        'user_card'          => 'ID No',
        'user_phone'         => 'Phone No',
        'application_amount' => 'Loan amount',
        'application_term'   => 'Term of loan',
        'create_time'        => 'Application date',
        'handle_state'       => 'Approval status',
        'handle_admin'       => 'Approver',
        'handle_time'        => 'Approval time',
        'operate'            => 'Operation',
    ),
    // 信审订单详细-客户资料
    'order_todo_user'          => array(
        'title'       => 'Customer information',
        'phone'       => 'Phone No',
        'name'        => 'Full name',
        'idcode'      => 'ID No',
        'city'        => 'City',
        'address'     => 'Address',
        'industry'    => 'Industry',
        'profession'  => 'Occupation',
        'gps_address' => 'GPS address',
        'education'   => 'Education ',
        'company'     => 'Company name',
        'company_add' => 'Company address',
        'company_tel' => 'Phone No',
        'credit_img'  => 'Signature picture',
        'other_img'   => 'Other picture information'
    ),
    // 信审订单详细-订单
    'order_todo_info'          => array(
        'title'              => 'Order details',
        'order_no'           => 'Order No.',
        'name'               => 'User name',
        'phone'              => 'Phone No',
        'application_amount' => 'Loan amount',
        'paid_amount'        => 'Amount of repayment',
        'bankcard_name'      => 'Bank name',
        'card_num'           => 'Bank card number',
        'add_time'           => 'Loan time',
        'application_term'   => 'Term of loan',
        'refuse_time'        => 'Approval time',
        'lending_time'       => 'Lending time',
        'End_time'           => 'Pay back the time to settle the time',
        'order_status'       => 'State',
        'not_pass_info'      => 'Not to pass the reason',
        'handle_state'       => 'State of verification',
        'success_time'       => 'Time of trial',
        'relaname'           => 'Relationship',
    ),

    // face++ 活体记录
    'order_todo_face_log'      => array(
        'title'         => 'Face++ living body recording',
        'add_time'      => 'Request time',
        'face_image'    => 'Living photo',
        'image_ref1'    => 'Photo of ID card',
        'image_best'    => 'Best photo',
        'image_env'     => 'Fake face recognition photograph',
        'error_message' => 'False hints',
        'match_score'   => 'Matching fraction'
    ),

    //------------------------- ------------------ 合作公司表头
    // 合作公司
    'company_list'             => array(
        'cp_name'           => 'Corporate name',
        'cp_num'            => 'Company management account',
        'cp_code'           => 'Company code',
        'cp_leg_person'     => 'Name of company legal person',
        'cp_contact_person' => 'Company contacts',
        'cp_mobile'         => 'Company\'s telephone number',
        'cp_address'        => 'Company address',
        'cp_country'        => 'Country',
        'status'            => 'Application status',
        'operator_name'     => 'Auditor',
        'operator_date'     => 'Application date',
        'operate'           => 'Operation',
    ),
    // Statistical analysis Registration volume Order volume Overdue order volume Bad debt amount

    //------------------------------------------- 统计表头
    'chart_count_view'         => array(
        'order_count'       => 'Order amount',
        'reg_user_count'    => 'Registration amount',
        'due_order_count'   => 'Late order amount',
        'death_order_count' => 'Bad debt',
    ),
    // Statistical analysis of loan amount overdue amount Bad debt amount
    'chart_money_count_data'   => array(
        'sum_amount'   => 'Lending amount',
        'due_amount'   => 'Dated amount',
        'death_amount' => 'Bad debt amount',
    ),
    // statistics Analysis of risk control pass rate, pass rate, pass rate
    'chart_pass_count_data'    => array(
        'risk_rate'    => 'Risk control',
        'xinshen_rate' => 'Pass rate',
        'all_rate'     => 'Total throughput',
    ),
    // Statistical analysis of the percentage of registered applications for borrowing that day
    'chart_one_day_count_data' => array(
        'jiekuan_rate' => 'Rate of application for borrowing on the same day'
    ),
    // 统计分析 业务系统 渠道明细
    'get_channel_data_list'    => array(
        "name"                   => "Channel name",
        "click"                  => "Click",
        "download"               => "Download",
        "register"               => "Registration",
        "apply_order"            => "Order",
        "download_click_rate"    => "Download/Click Ratio",
        "register_download_rate" => "Registration/download Ratio",
        "order_register_rate"    => "Order/Registration Ratio",
        "order_click_rate"       => "Order/Click Ratio",
        "order_download_rate"    => "Order/Download Ratio"
    ),
    //新增逾期
    'chart_due_list'           => array(
        'date_str'                   => 'Should be repaid',
        'order_pay_sum'              => 'Repayment amount payable',
        'order_repay_sum'            => 'Actual repayments',
        'order_today_due_sum'        => 'Number of first overdue',
        'order_due_sum'              => 'Current overdue number',
        'first_overdue_rate'         => 'First overdue ratio',
        'current_overdue_rate'       => 'Current overdue ratio',
        '1_3_days'                   => '1-3 days',
        '4_8_days'                   => '4-8 days',
        '9_18_days'                  => '9-18 days',
        '19_30_days'                 => '19-30 days',
        '31_60_days'                 => '31-60 days',
        'over_60_days'               => '61+ days',
        'first_overdue_3_days'       => 'First 3 days',
        'first_overdue_8_days'       => 'First 8 days',
        'first_overdue_18_days'      => 'First 18 days',
        'first_overdue_30_days'      => 'First 30 days',
        'first_overdue_60_days'      => 'First 60 days',
        'first_overdue_over_60_days' => 'First over 61+ days',
    ),

    //审批详情-历史纪录
    'history_order_list' => array(
        'order_no'     => 'Order No.',
        'lending_time' => 'Borrowing time',
        'repay_time'   => 'Expire date',
        'over_day'    => 'Days overdue',
    ),

    //------------------------------------------- 财务表头

    // 放款列表
    //'finance_payment'          => array(
    //    'sn'       => 'number',
    //    'order_no' => 'sn',
    //    'name'     => 'name',
    //    'price'    => 'price',
    //    'add_time' => 'time'
    //),
    // 还款列表
    //'finance_repayment'        => array(
    //    'sn'       => 'number',
    //    'order_no' => 'sn',
    //    'name'     => 'name',
    //    'bt_no'    => 'serial number',
    //    'price'    => 'price',
    //    'add_time' => 'time'
    //),
    // 财务统计表头
    //    'finance_chart'            => array(
    //        'date_str'  => 'Time',
    //        'repay_sum' => 'Repayment amount',
    //        'pay_sum'   => 'Loan amount'
    //    ),
    // 新财务表头
    'finance_all_list'         => array(
        'yinghuan_order_cnt'    => 'Repayment amount payable',
        'yihuan_order_cnt'      => 'Paid repayments',
        'weihuan_order_cnt'     => 'Unpaid repayments',
        'yingshou_benjin_sum'   => 'Principal receivable',
        'yingshou_benxi_sum'    => 'Principal and interest receivable',
        'yingshou_zongjine_sum' => 'Total amount receivable',
        'huankuan_benxi_sum'    => 'Principal and interest of repayment',
        'huankuan_zonge_sum'    => 'Repayment amount',
        'benjin_huishou_rate'   => 'Recovery rate of principal',
        'yingshou_huishou_rate' => 'Recovery rate of receivable',
        'zong_huishou_rate'     => 'Total return rate',
    ),
    // financial lending
    'finance_pay_list'         => array(
        'date_str'          => 'Lending date',
        'order_cnt'         => 'Lending amount (orders)',
        'order_success_cnt' => 'Lent amount (orders)',
        'order_fail_cnt'    => 'Failed lending amount (orders)',
        'order_success_sum' => 'Total amount of lending',
        'order_repayment_sum' => 'Repayment amount',
    ),

    // 财务总计
    'finance_all_sum_name'     => 'New guest/old customer',
    'finance_all_sum_list'     => array(
        'name'               => 'Customer type',
        'order_apply_sum'    => 'Number of applications',
        'order_handle_sum'   => 'Passed frequency',
        'order_handle_rate'  => 'rate of passing',
        'order_ht_amount'    => 'Amount of lending contract',
        'order_bj_amount'    => 'Actual loan amount',
        'order_repay_sum'    => 'Total recovery frequency',
        'order_repay_amount' => 'Total recovery amount',
        'order_profit'       => 'Profit',
        'order_profit_rate'  => 'Profit rate',
    ),

    //------------------------------------------- 2018年7月9日新增表头
    'collection_log'           => array(
        'real_name'        => 'Receiver',
        'order_cnt_sum'    => 'Promise the number of repayments',
        'order_ontime_sum' => 'Repaid number',
        'order_undue_sum'  => 'Number of unrepaid',
        'order_due_sum'    => 'Exceeded commitment period',
        'rate'             => 'Recovery rate'
    ),


    //------- ------------------------------------ June 8, 2018 New header

    'chart_click_amount_tag'                  => 'Total clicks',
    'chart_channel_ratio_tag'                 => 'Channel / Click ratio',
    'chart_download_ratio_tag'                => 'Downloading / Clicking ratio',
    'chart_download_ratio_tag_success'        => 'Downloaded quantity',
    'chart_download_ratio_tag_fail'           => 'Non downloadable volume',
    'chart_channel_download_ratio_tag'        => 'Channel / Download ratio',
    'chart_reg_ratio_tag'                     => 'Registration / Download ratio',
    'chart_reg_ratio_tag_success'             => 'Registered quantity',
    'chart_reg_ratio_tag_fail'                => 'Unregistered quantity',
    'chart_channel_reg_ratio_tag'             => 'Channel / Registration ratio',
    'chart_apply_ratio_tag'                   => 'Application / Registration ratio',
    'chart_apply_ratio_tag_success'           => 'Application amount',
    'chart_apply_ratio_tag_fail'              => 'Non application amount',
    'chart_channel_apply_ratio_tag'           => 'Channel / Applied ratio',
    'chart_new_old_loan_apply_ratio_tag'      => 'The ratio of new applicant and old applicant',
    'chart_new_loan_apply_ratio_tag_success'  => 'New loan customer application',
    'chart_old_loan_apply_ratio_tag_success'  => 'Reapply loan customer application',
    'chart_channel_new_loan_apply_ratio_tag'  => 'New application ratio',
    'chart_channel_old_loan_apply_ratio_tag'  => 'Reapply application ratio',
    'chart_risk_pass_ratio_tag'               => 'Proportion of parts passing risk control',
    'chart_risk_pass_ratio_tag_success'       => 'Quantity of parts passing risk control',
    'chart_risk_pass_ratio_tag_fail'          => 'Quantity of parts not passing risk control',
    'chart_channel_risk_pass_ratio_tag'       => 'Proportion of channel/qualified parts',
    'chart_approval_pass_ratio_tag'           => 'Proportion of parts passing credit audit/risk control',
    'Chart_approval_pass_ratio_success'       => 'Quantity of parts passing credit audit',
    'chart_approval_pass_ratio_fail'          => 'Quantity of parts not passing credit audit',
    'chart_channel_approval_pass_ratio_tag'   => 'Proportion of channel/credit audit',
    'chart_in_collect_ratio_tag'              => 'Entry rate',
    'chart_in_collect_ratio_ok'               => 'Amount of repayment on the due day',
    'chart_in_collect_ratio_due'              => 'Unpaid amount o the due day',
    'chart_channel_in_collect_pass_ratio_tag' => 'Proportion of channel/first overdue proportion',
    'chart_due_order_ratio_tag'               => 'Overdue/order proportion',
    'chart_due_day_1'                         => 'Order quantity with less than 3 overdue days',
    'chart_due_day_2'                         => 'Order quantity with less than 10 overdue days',
    'chart_due_day_3'                         => 'Order quantity with less than 15 overdue days',
    'chart_due_day_4'                         => 'Order quantity with less than 30 overdue days',
    'chart_due_day_5'                         => 'Order quantity with more than 3 overdue days',
    'chart_channel_due_ratio_tag'             => 'Channel overdue ratio',
    'chart_due_ratio_tag'                     => 'Overdue proportion',
    'chart_due_ratio_on_due'                  => 'Overdue',
    'chart_due_ratio_ok'                      => 'Amount repaid',
    'chart_due_ratio_not_over'                => 'Undue ',
    'chart_due_three_days_ratio_tag'          => 'PD3 ratio',
    'chart_due_ten_days_ratio_tag'            => 'PD10 ratio',
    'chart_due_fifteen_days_ratio_tag'        => 'PD15 ratio',
    'chart_due_thirty_days_ratio_tag'         => 'PD30 ratio',
    'chart_due_over_thirty_days_ratio_tag'    => 'PD30+ ratio',
    'chart_finance_yingshou_huikuan_tag'      => 'Recovery of capital and interest',
    'chart_finance_yingshou_huikuan_tag_ok'   => 'Returned capital and interest',
    'chart_finance_yingshou_huikuan_tag_fail' => 'Not yet returned capital and interest',
    'chart_finance_sum_huikuan_tag'           => 'Total return (including fine)',
    'chart_finance_sum_huikuan_tag_ok'        => 'Total amount of repayment',
    'chart_finance_sum_huikuan_tag_fail'      => 'Total amount of to-be-repaid',
    'chart_finance_loan_repayment_tag'        => 'Recovery report',
    'chart_finance_principal_tag'             => 'Recovery of principal',
    'chart_loan_repayment_field'              => array(
        'title'            => 'Recovery report',
        'date'             => 'Time',
        'loan_amount'      => 'Total amount of lending',
        'repayment_amount' => 'Total amount of repayment',
        'weihuan_amount'   => 'Amount of debt library',
    ),

    'chart_principal_field' => array(
        'title'              => 'Recovery of principal',
        'benjinhuishou_rate' => 'Recovery rate of principal',
        'repayment_amount'   => 'Total amount of repayment',
        'yinghuan_benjin'    => 'Principal payable',
    ),

    //---------- --------------------------------- 公用部分

    // 订单状态
    'order_status'          => array(
        '1'   => 'To be audited',
        '80'  => 'Additional information',
        '90'  => 'Auditing', //examination and approval
        '100' => 'Audit approved',
        '110' => 'Do not passed audit',
        '160' => 'In the loan',
        '161' => 'Loan cancellation',
        '169' => 'Loan failure',
        '170' => 'Loan success',
        '180' => 'Overdue',
        '190' => 'In the payment',
        '195' => 'Payment failure',
        '200' => 'Loan settlement',
    ),

    // 订单审核状态
    'order_handle'               => array(
        '0' => 'Not carried out',
        '1' => 'Preliminary review',
        '4' => 'To be finalized',
        '2' => 'Passed',
        '3' => 'Rejected',
        //'5' => 'Lending failure',
    ),

    // 订单风控状态
    'risk_status'        => array(
        '0' => 'Not carried out',
        '1' => 'Passed',
        '2' => 'Rejected',
    ),

    // 订单放款状态
    'pay_status'        => array(
        '0' => 'Not carried out',
        '1' => 'success',
        '2' => 'failure',
    ),

    // blue pay 请求回调状态
    'pay_callback_lang'     => array(
        '200' => 'Bluepay transaction completed',
        '201' => 'The BluePayrequest  is successful, indicating that the order was successfully established on the BluePay side.',
        '600' => 'Failure to request a bank, is usually caused by incorrect bank account information.',
        '400' => 'Parameter error, lack of parameter',
        '401' => 'Signature error, encryption error',
        '501' => 'The bank asked for time out and the transaction failed. Reinitiating transactions',
        '506' => 'IP restriction',
        '404' => 'The information was not found in the transaction',
        '500' => 'Internal service error',
        '646' => 'Failure of the bank to deal with',
        '601' => 'Business balance is insufficient and loan payment is insufficient. Please contact BluePay business recharge. Please contact the technical service for the test environment',
        '649' => 'The bank information is wrong, the account number is wrong'
    ),

    // 行业类型
    'profession_type'            => array(
        '1'  => 'Professional, technical and related staff',
        '2'  => 'Administration and management',
        '3'  => 'Clerical and related staff',
        '4'  => 'Salesman',
        '5'  => 'Service worker',
        '6'  => 'Agroforestry and hunting workers',
        '7'  => 'Production and related, transportation operators and workers',
        '8'  => 'Driver',
        '9'  => 'Others',
        '10' => 'soldier',//军人
        '11' => 'Policemen',//警察
        '12' => 'lawyer',//律师
    ),

    // 职业类型
    'industry_type'         => array(
        '1' => 'Agriculture, forestry, hunting and fishery',
        '2' => 'Mining / quarrying',
        '3' => 'Manufacturing industry',
        '4' => 'Electricity, gas and water',
        '5' => 'Laying channels',
        '6' => 'Wholesale / retail trade, restaurants and hotel',
        '7' => 'Transport, storage and communication',
        '8' => 'Financing, insurance, real estate, business service',
        '9' => 'Community, social worker',
    ),

    // 学历
    'education_type'        => array(
        '1' => 'Master and above',
        '2' => 'Bachelor',
        '3' => 'Diploma',
        '4' => 'Senior high',
        '5' => 'Junior high and below',
    ),

    // 审批跟进记录
    'handle_result_type'         => array(
        '1' => 'pass',
        '2' => 'Refuse',
        '3' => 'Final review',
        '4' => 'Final refusal'
    ),

    // 公司审核状态
    'company_status_lang'   => array(
        '0' => 'Passed',
        '1' => 'Rejected',
        '2' => 'To be approved',
        '3' => 'Auditing',
    ),

    //还款码状态
    'payment_code_status_lang'   => array(
        '0' => 'Expired',
        '1' => 'Recharge',
    ),

    // 通讯录展示时来源
    'phone_from'                 => [
        '1' => 'my self',
        '2' => 'contact',
        '3' => 'communication'
    ],

    // 联系人匹配
    'phone_match'                => [
        'N' => 'Mismatch',
        'Y' => 'match',
        'E' => '-',
    ],

    // 催收标记
    'collection_s'               => [
        '0' => 'all',
        '1' => 'S1(pd 1~10 day)',
        '2' => 'S2(pd 11~30 day)',
        '3' => 'S3(pd 31 day +)'
    ],

    // 专案查询
    'order_quality'              => [
        '0' => 'all',
        '1' => 'Yes',
        '2' => 'No',
    ],

    // 订单信审初审不通过原因
    'order_handle_not_pass_info' => [
        'TL001' => 'Material does not match',
        'TL002' => 'The unit number is invalid',
        'TL003' => 'Unit information is not true',
        'TL004' => 'Unit information could not be verified',
        'TL005' => 'Low qualified customer',
        'TL006' => 'Electric nuclear negative-other',
        'TL007' => 'SMS negative',
        'TL008' => 'Not my application',
        'TL009' => 'Refuse to provide the necessary information',
        'TL010' => 'Contact number is invalid',
        'TL011' => 'Contact information is not true',
        'TL012' => 'Contact information cannot be verified',
        'TL013' => 'Other',
        'TL014' => 'Cancel application',
        'TL015' => 'Applicant\'s nuclear failure',
        'TL016' => 'Applicant information cannot be verified',
        'TL017' => 'Unable to contact the unit',
        'TL018' => 'Unable to contact contact',
        'TL019' => 'Unable to contact applicant',
        'TL020' => 'No abnormality passed',
        'TL021' => 'Suspected agent/black agent',
        'TL022' => 'departed/unemployed',
        'TL023' => 'Occupation does not meet the requirements',
    ],

    // 订单信审审核记录
    'order_handle_review_log'    => [
        'title'       => 'audit record',
        'add_time'    => 'Approval time',
        'admin_name'  => 'Approver',
        'refuse_desc' => 'Reject reason',
        'remark'      => 'Remarks',
        'review_desc' => 'audit result'
    ],

    // 订单电核记录
    'order_handle_flow_log'      => [
        'title'      => 'Electrical record',
        'add_time'   => 'follow time',
        'relation'   => 'follow object',
        'status'     => 'Follow up status',
        'name'       => 'name',
        'phone'      => 'contact phone',
        'remark'     => 'follow the record',
        'admin_name' => 'follow-up staff',
    ],

    // 通话记录状态
    'record_type' => [
        '1' => 'call',
        '2' => 'Outgoing',
        '3' => 'not connected',
    ],


    //jia

    "inser_fail" => "Insert failure",

    "cllection_case_details"        => "Details of collection",
    //催收列表
    'cllection_order_no'            => 'Order No.',
    'cllection_real_name'           => 'User name',
    'cllection_phone'               => 'Phone No',
    'cllection_due_day'             => 'Days overdue',
    'cllection_repay_amount'        => 'Amount payable',
    'cllection_due_time'            => 'Expected repayment date',
    'cllection_collection_status'   => 'Collection status',
    'cllection_followup_feed'       => 'Collection feedback',
    'cllection_case_follow_name'    => 'collector',
    'cllection_success_time'        => "Repayment Date",
    'follow_time'                   => "Follo-up date",
    'paid_amount'                   => "Entry Amount",


    //催收客户用户信息
    "cllection_name"                => "Ful name",
    "cllection_sex"                 => "Sex",
    "cllection_idcode"              => "ID number",
    "cllection_card_type"           => "Account type",
    "cllection_bankcard_name"       => "Bank account",
    "cllection_card_num"            => "Account number",
    "cllection_is_marrey"           => "Marital status",
    "cllection_email"               => "Email",
    "cllection_education"           => "Educational level ",
    "cllection_relation"            => "Relationship",

    //催收状态
    'cllection_case_status_0'       => ' - ',
    'cllection_case_status_170'     => 'On collect',
    'cllection_case_status_180'     => 'On collect',
    'cllection_case_status_200'     => 'Amount repaid',
    //催收反馈
    'cllection_followup_feed_'      => ' - ',
    'cllection_followup_feed_0'     => 'Please choose',
    'cllection_followup_feed_181'   => 'Commit to repay',
    'cllection_followup_feed_182'   => 'Consultation follow up',
    'cllection_followup_feed_183'   => 'Refusing to pay',
    'cllection_followup_feed_184'   => 'Customer prompt has repaid',
    'cllection_followup_feed_185'   => 'Commit to follow uup',
    'cllection_followup_feed_186'   => 'Willing to pass the message',
    'cllection_followup_feed_187'   => 'Refusing to pass the message',
    'cllection_followup_feed_188'   => 'A message from others',
    'cllection_followup_feed_189'   => 'Out of contact',
    'cllection_followup_feed_190'   => 'To be verified',
    'cllection_followup_feed_191'   => 'Others',
    'cllection_followup_feed_200'   => 'Application for remission',
    // 催收类型
    'cllection_follow_type_'        => ' - ',
    'cllection_follow_type_0'       => 'Please choose',
    'cllection_follow_type_81'      => 'Digital collect',
    'cllection_follow_type_82'      => 'Visit',
    'cllection_follow_type_83'      => 'Judicial',
    'cllection_follow_type_84'      => 'Outside committee',
    'cllection_follow_type_85'      => 'Reminder',
    //手机状态
    'cllection_contact_state_'      => '-',
    'cllection_contact_state_0'     => 'Please choose',
    'cllection_contact_state_1'     => 'Normal',
    'cllection_contact_state_2'     => 'Turned off',
    'cllection_contact_state_3'     => 'Vacant number',
    'cllection_contact_state_4'     => 'Phone broke',
    'cllection_contact_state_5'     => 'On call',
//    'cllection_contact_state_6'     => 'Unable to connect',
    'cllection_contact_state_7'     => 'Unmanned response',

    //关系
    'cllection_target_0 '           => 'Please choose',
    'cllection_target_1'            => 'Father',
    'cllection_target_2'            => 'Mother',
    'cllection_target_3'            => 'Brother',
    'cllection_target_4'            => 'Sister',
    'cllection_target_5'            => 'Friends',
    'cllection_target_6'            => 'Children',
    'cllection_target_7'            => 'Workmate',
    'cllection_target_8'            => 'Other',
    'cllection_target_9'            => 'Spouse',
    'cllection_target_10'           => 'Oneself',

    // 性别
    'cllection_sex_0'               => ' - ',
    'cllection_sex_1'               => 'Male',
    'cllection_sex_2'               => 'Female',
    // 婚姻状态
    'cllection_is_marrey_0'         => 'Unmarried',
    'cllection_is_marrey_1'         => 'Married',
    //教育程度
    'cllection_education_1'         => 'Master and above',
    'cllection_education_2'         => 'Bachelor',
    'cllection_education_3'         => 'Diploma',
    'cllection_education_4'         => 'Senior high',
    'cllection_education_5'         => 'Junior high and below',

    // 催收账单详细
    "cllection_principal"           => "Capital",
    "cllection_interest"            => "Interest",
    //"repay_amount" => "payable amount",
    "cllection_over_interest"       => "Fine",
    //"due_day"=>"due days",
    "cllection_repay_data"          => "Enter account information",
    //Cost Flow 
    "cllection_price"               => "Total amount enter account",
    "cllection_add_time"            => "Time of entry",

    // 催收
    "cllection_input"               => "Please enter",
    "cllection_personal_id"         => "Customer ID",
    "cllection_case_id"             => "Order ID",
    "cllection_operator_time"       => "Follow up time",
    "cllection_follow_type"         => "Follow up mode",
    "cllection_target"              => "Collect objects",
    "cllection_target_name"         => "Full name",
    "cllection_contact_phone"       => "Contact number",
    "cllection_contact_state"       => "Telephone state",
    "cllection_collection_feedback" => "Collection feedback",
    "cllection_content"             => "Follow up record",
    "cllection_operator_name"       => "Follow up personnel",

    // 费用减免
    "reduction_title"               => "Application for remission",
    "reduction_order_no"            => "Order number",
    "reduction_user_name"           => "Customer Name",
    "reduction_repay_amount"        => "Total amount",

    "reduction_over_fee"           => "Fine",
    "reduction_fee"                => "Amount of remission",
    "reduction_fee_remark"         => "The amount of remission should not be higher than the fine",
    "reduction_remark"             => "Remarks",
    "reduction_apply_has"          => "There is a remission apply, please wait for the verification approval",
    "reduction_apply_date"         => "Application date",
    "reduction_apply_name"         => "Applicant",
    "reduction_application_amount" => "Capital",
    "reduction_interest"           => "Interest",


    "reduction_status"   => "Approval status",
    "reduction_status_0" => "To be audited",//"examination and approval",
    "reduction_status_1" => "Passed",
    "reduction_status_2" => "Rejected",
    "reduction_record"   => "Application of remission",

    //Distribution collector

    'cllection_list_no'           => "Number",
    //'cllection_real_name' => lang('cllection_real_name'), 
    'cllection_role_name'         => "Role type",
    'cllection_has_case'          => "Current number of cases",
    'cllection_can_case'          => "Number of distributed cases",

    //视图走势
    "order_view_title"            => "Collecting orders report",
    "order_view_order_count"      => "Total number of orders",
    "order_view_collection_count" => "Actual collection number",

    "collector_view_title"            => "Total number of orders",
    "collector_view_order_count"      => "All orders",
    "collector_view_collection_count" => "Completed orders",

    "reduction_view_title"   => "Cost reduction",
    'reduction_view_all_fee' => 'Total amount of order',
    'reduction_view_all_num' => 'Order quantity',
    "close_status"           => "The case has been closed",
    "has_reduction"          => "Can only apply for one remission",
    "my_relation"            => "Oneself",
    "no_case"                => "No case to be allocated",
    "no_can"                 => "Can not do this operation",
    "cur_yinghuankuan_count" => "Repayments due on the day",
    "cur_yihuankuan_count"   => "The number of payments to be due on the day",
    "all_huan_count"         => "The number of payments due on the day",
    "all_yinghuan_count"     => "All repayments",
//测试环境的菜单ID
    'menu_name' => array(
        'approval_name'   => 'Approval system',//审批系统
        'business_name'   => 'Business Management',//业务管理
        'company_name'    => 'Cooperation Company',//合作公司
        'collection_name' => 'Collection System',//催收系统
        'personnel_name'  => 'Employee System',//员工系统
        'analysis_name'   => 'Business Analysis',//经营分析
        'finance_name'    => 'Financial System',//财务系统
        'channel_name'    => 'Promotion Channels',//推广渠道
        'adv_name'        => 'Promotion Channels',//渠道备用名称
    ),
    //测试环境的菜单ID
    'menu_child_name'        => array(
        //审批系统
        8   => 'All approvals',//所有审批
        9   => 'Preliminary review',//未审批（Unapproved）修改为 待初审
        219   => 'Pending final review',//待终审
        //业务系统
        10  => 'User List',//用户列表
        11  => 'Order List',//订单列表
        12  => 'Business Switch',//业务开关
        13  => 'Cost Allocation',//费用配置
        //合作公司
        14  => 'Company List',//公司列表
        15  => 'Company Application',//公司申请
        16  => 'Company Audit',//公司审核
        //催收系统
        17  => 'All Collections',//所有催收
        18  => 'In The Collection',//催收中
        19  => 'Amount Repaid',//已还款
        20  => 'Early Dispatch',//提早派单
        22  => 'Cost Reduction',//费用减免
        //员工系统
        24  => 'Employee List',//员工列表
        25  => 'Role Management',//角色管理
        26  => 'Employee Log',//员工日志
        200 => 'Menu List',//菜单列表
//经营分析
        203 => 'Business Statistics',//业务统计
        204 => 'Risk Control Statistics',//风控统计
        209 => 'Overdue Statistics',//逾期统计
        215 => 'Overdue Report',//逾期报表
//推广渠道
        202 => 'Channel List',//渠道列表
//财务系统
        206 => 'Repayment Statistics',//还款统计
        211 => 'Loan Statistics',//放款统计




    ),

    // 线上环境的菜单ID
    'menu_name_online' => array(
        'approval_name'   => 'Sistem persetujuan',//审批系统
        'business_name'   => 'Business Management',//业务管理
        'company_name'    => 'Cooperation company',//合作公司
        'collection_name' => 'Collection system',//催收系统
        'personnel_name'  => 'Employee system',//员工系统
        'analysis_name'   => 'Business analysis',//经营分析
        'finance_name'    => 'Financial System',//财务系统
        'channel_name'    => 'Promotion channels',//推广渠道
        'adv_name'        => 'Promotion channels',//渠道备用名称
    ),
    //线上环境的菜单ID
    'menu_child_name_online' => array(
        //审批系统
        8   => 'All Approvals',//所有审批
        9   => 'Not Approved',//未审批
        220   => 'Pending final review',//待终审
        //业务管理
        10  => 'User list',//用户列表
        11  => 'Order List',//订单列表
        12  => 'Business Switch',//业务开关
        13  => 'Cost Configuration',//费用配置
        //合作公司
        14  => 'Company List',//公司列表
        15  => 'Company Application',//公司申请
        16  => 'Company Audit',//公司审核
        //催收系统
        17  => 'All Collections',//所有催收
        18  => 'In the Collection',//催收中
        19  => 'Repaid',//已还款
        20  => 'Early Dispatch',//提早派单
        22  => 'Cost Reduction',//费用减免
        //员工系统
        24  => 'Employee List',//员工列表
        25  => 'Role Management',//角色管理
        26  => 'Employee Log',//员工日志
        200 => 'Menu List',//菜单列表
        //经营分析
        203 => 'Business Statistics',//业务统计
        204 => 'Risk control Statistics',//风控统计
        209 => 'Overdue Statistics',//逾期统计
        215 => 'Overdue Report',//逾期报表
        //财务系统
        213 => 'Repayment Statistics',//还款统计
        211 => 'Loan Statistics',//放款统计
        //推广渠道
        202 => 'Channels Statistics',//渠道列表

    ),


);