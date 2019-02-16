<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 14:32
 */
return array(
    // 错误码
    'error_4001'               => '请求方式错误',
    'error_4002'               => '请求参数不能为空',
    'error_4003'               => '验证码错误',
    'error_4004'               => '请填写账号密码',
    'error_4005'               => '账号密码不正确',
    'error_4006'               => '账号被禁用，请联系管理员',
    'error_4007'               => '已存在该用户',
    'error_4008'               => '网络错误，请联系管理员',
    'error_4009'               => '公司下存在角色，不能删除公司',
    'error_4010'               => '上传图片时发生错误',
    'error_4011'               => '上传OSS时发生错误',
    'error_4012'               => '合作公司状态不符',
    'error_4013'               => '账号所属公司被禁用, 请联系管理员',
    'error_4014'               => '公司管理员账户已存在',
    'error_5001'               => '请勿越权操作',
    'error_8001'               => '登陆已过期',
    'error_4015'               => '请填写不通过原因',
    'error_4016'               => '请填写备注',
    'error_4017'               => '该推广下载链接已经添加过,不能重复添加',

    // 通用部分
    'is_login'                 => '已登录',
    'success'                  => '完成',
    'is_status'                => '已改变状态，请勿重复操作',
    'data_empty'               => '数据为空',
    'order_status_fail'        => '订单状态不符',
    'auto_mode_no_order'       => '尚未找到需要分配的订单',
    'auto_mode_no_user'        => '该公司无信审人员',

    // 合同金额语言包
    'data_lf'                  => '数据太长，没有这么大的钱吧，检查下',
    'lyz'                      => '零元整',
    'ints'                     => '整',
    'm_num'                    => '零壹贰叁肆伍陆柒捌玖',
    'm_dw'                     => '分角元拾佰仟万拾佰仟亿',

    //------------------------------------------- 员工系统表头
    // 员工列表
    'personnel_list'           => array(
        'user_name' => '用户名',
        'real_name' => '姓名',
        'email'     => 'Email地址',
        'role_name' => '角色名称',
        'cp_name'   => '所属公司',
        'add_time'  => '申请日期',
        'operate'   => '操作',
    ),
    // 员工日志
    'personnel_log'            => array(
        'user_name' => '用户名',
        'real_name' => '姓名',
        'cp_name'   => '所属公司',
        'log_ip'    => 'IP',
        'log_time'  => '操作时间',
        'log_info'  => '描述',
    ),
    // 角色管理
    'role_index'               => array(
        'role_name' => '角色名称',
        'role_desc' => '角色描述',
        'cp_name'   => '所属公司',
        'role_info' => '描述',
        'operate'   => '操作',
    ),

    //------------------------------------------- 业务系统表头
    // 订单列表
    'order_index'              => array(
        'order_no'           => '订单号',
        'name'               => '用户名',
        'repay_time'         => '应还日期',
        'phone'              => '手机号',
        'source'             => '渠道来源',
        'add_time'           => '申请日期',
        'application_amount' => '借款金额',
        'application_term'   => '借款期限',
        'order_status'       => '订单状态',
        'end_time'           => '入账时间',
        'region_name'        => '区域',
        'handle_state'            => '信审状态',
        'pay_status'            => '放款状态',
        'risk_status'            => '风控状态',
        'due_day'            => '逾期天数',
    ),
    // 客户列表
    'user_list'                => array(
        'name'     => '用户名',
        'idcode'   => '身份证号',
        'phone'    => '手机号',
        'source'   => '渠道来源',
        'reg_time' => '注册时间',
    ),
    // 订单详情
    'order_info'               => array(
        'order_no'           => '订单号',
        'name'               => '用户名',
        'phone'              => '手机号',
        'application_amount' => '借款金额',
        'paid_amount'        => '还款金额',
        'bankcard_name'      => '银行名称',
        'card_num'           => '银行卡号',
        'add_time'           => '贷款时间',
        'refuse_time'        => '审批时间',
        'lending_time'       => '放款时间',
        'end_time'           => '还款结清时间',
        'order_status'       => '状态',
        'not_pass_info'      => '不通过原因',
    ),
    // 借款扣款记录信息
    'pay_log'                  => array(
        'cmd'       => '通知类型',
        't_id'      => '交易订单号',
        'bt_id'     => '对账账号',
        'status'    => '状态',
        'price'     => '交易金额',
        'currency'  => '交易货币',
        'productid' => 'BluePlay产品ID',
        'add_time'  => '通知时间',
    ),
    // 还款扣款记录信息
    'repay_log'                => array(
        'cmd'       => '通知类型',
        't_id'      => '交易订单号',
        'status'    => '状态',
        'price'     => '交易金额',
        'currency'  => '交易货币',
        'productid' => 'BluePlay产品ID',
        'add_time'  => '通知时间',
    ),

    // 还款扣款记录信息
    'repay_code_log'                => array(
        'code'       => '充值码',
        'add_time'      => '生成时间',
        'status'    => '状态',
    ),
    // 费用配置
    'system_list'              => array(
        'type_id'      => '编号',
        'company_name' => '公司名称',
        'apply_term'   => '借款期限',
        'apply_amount' => '借款金额',
        'rate'         => '日利率',
        'service_fee'  => '平台服务费',
        'approval_fee' => '信息审核费率',
        'over_fee'     => '逾期费率',
        'term_fee'     => '还款利息',
        'max_money'    => '每日放款上限',
        'operate'      => '操作'
    ),

    //------------------------------------------- 审批系统表头
    // 所有审批
    'order_all'                => array(
        'order_no'           => '订单号',
        'user_name'          => '用户名',
        'user_card'          => '身份证号',
        'user_phone'         => '手机号',
        'application_amount' => '借款金额',
        'application_term'   => '借款期限',
        'create_time'        => '申请日期',
        'handle_state'       => '审批状态',
        'handle_admin'       => '审批人',
        'handle_time'        => '审批时间',
        'operate'            => '操作',
    ),
    // 信审订单详细-客户资料
    'order_todo_user'          => array(
        'title'       => '客户信息',
        'phone'       => '电话号',
        'name'        => '姓名',
        'idcode'      => '身份证号',
        'city'        => '城市',
        'address'     => '地址',
        'industry'    => '行业',
        'profession'  => '职业',
        'gps_address' => 'GPS地址',
        'education'   => '学历',
        'company'     => '公司名',
        'company_add' => '公司地址',
        'company_tel' => '公司电话',
        'credit_img'  => '签名图片',
        'other_img'   => '其他图片资料'
    ),
    // 信审订单详细-订单
    'order_todo_info'          => array(
        'title'              => '订单详细',
        'order_no'           => '订单号',
        'name'               => '用户名',
        'phone'              => '手机号',
        'application_amount' => '借款金额',
        'paid_amount'        => '还款金额',
        'bankcard_name'      => '银行名称',
        'card_num'           => '银行卡号',
        'add_time'           => '贷款时间',
        'application_term'   => '借款期限',
        'refuse_time'        => '审批时间',
        'lending_time'       => '放款时间',
        'end_time'           => '还款结清时间',
        'order_status'       => '状态',
        'not_pass_info'      => '不通过原因',
        'handle_state'       => '信审状态',
        'success_time'       => '信审时间',
        'relaname'           => '关系',
    ),

    // face++活体记录
    'order_todo_face_log'      => array(
        'title'         => 'face++活体记录',
        'add_time'      => '请求时间',
        'face_image'    => '活体照片',
        'image_ref1'    => '身份证照片',
        'image_best'    => '最佳照片',
        'image_env'     => '假脸识别照片',
        'error_message' => '错误提示',
        'match_score'   => '匹配分数'
    ),

    //------------------------------------------- 合作公司表头
    // 合作公司
    'company_list'             => array(
        'cp_name'           => '公司名称',
        'cp_num'            => '公司管理账号',
        'cp_code'           => '公司code',
        'cp_leg_person'     => '公司法人姓名',
        'cp_contact_person' => '公司联系人',
        'cp_mobile'         => '公司电话',
        'cp_address'        => '公司地址',
        'cp_country'        => '所属国家',
        'status'            => '申请状态',
        'operator_name'     => '审核人',
        'operator_date'     => '申请日期',
        'operate'           => '操作',
    ),

    //------------------------------------------- 统计表头
    // 统计分析 注册量 订单量 逾期订单量 坏账量
    'chart_count_view'         => array(
        'order_count'       => '订单量',
        'reg_user_count'    => '注册量',
        'due_order_count'   => '逾期订单量',
        'death_order_count' => '坏账量',
    ),
    // 统计分析 放款金额 逾期金额 坏账金额
    'chart_money_count_data'   => array(
        'sum_amount'   => '放款金额',
        'due_amount'   => '逾期金额',
        'death_amount' => '坏账金额',
    ),
    // 统计分析 风控通过率 信审通过率 总通过率
    'chart_pass_count_data'    => array(
        'risk_rate'    => '风控通过率',
        'xinshen_rate' => '信审通过率',
        'all_rate'     => '总通过率',
    ),
    // 统计分析 当天注册去申请借款的比率
    'chart_one_day_count_data' => array(
        'jiekuan_rate' => '当天注册去申请借款的比率'
    ),
    // 统计分析 业务系统 渠道明细
    'get_channel_data_list'    => array(
        "name"                   => "渠道名称",
        "click"                  => "点击",
        "download"               => "下载",
        "register"               => "注册",
        "apply_order"            => "下单",
        "download_click_rate"    => "下载/点击 占比",
        "register_download_rate" => "注册/下载 占比",
        "order_register_rate"    => "下单/注册 占比",
        "order_click_rate"       => "下单/点击 占比",
        "order_download_rate"    => "下单/下载 占比"
    ),
    //新增逾期
    'chart_due_list'           => array(
        'date_str'                   => '应还款日',
        'order_pay_sum'              => '应还款数',
        'order_repay_sum'            => '实际还款数',
        'order_today_due_sum'        => '首次逾期笔数',
        'order_due_sum'              => '当前逾期笔数',
        'first_overdue_rate'         => '首次逾期比',
        'current_overdue_rate'       => '当前逾期比',
        '1_3_days'                   => '1-3天',
        '4_8_days'                   => '4-8天',
        '9_18_days'                  => '9-18天',
        '19_30_days'                 => '19-30天',
        '31_60_days'                 => '31-60天',
        'over_60_days'               => '61+天',
        'first_overdue_3_days'       => '首逾3日',
        'first_overdue_8_days'       => '首逾8日',
        'first_overdue_18_days'      => '首逾18日',
        'first_overdue_30_days'      => '首逾30日',
        'first_overdue_60_days'      => '首逾60日',
        'first_overdue_over_60_days' => '首逾61+天',
    ),

    //------------------------------------------- 财务表头

    // 放款列表
    //'finance_payment'          => array(
    //    'sn'       => '编号',
    //    'order_no' => '订单编号',
    //    'name'     => '用户名',
    //    'price'    => '放款金额',
    //    'add_time' => '放款时间'
    //),
    // 还款列表
    //'finance_repayment'        => array(
    //    'sn'       => '编号',
    //    'order_no' => '订单编号',
    //    'name'     => '用户名',
    //    'bt_no'    => '流水号',
    //    'price'    => '回款金额',
    //    'add_time' => '回款时间'
    //),
    // 财务统计表头
    //'finance_chart'            => array(
    //    'date_str'  => '时间',
    //    'repay_sum' => '回款金额',
    //    'pay_sum'   => '放款金额'
    //),
    // 新财务表头
    'finance_all_list'         => array(
        'yinghuan_order_cnt'    => '应还款数',
        'yihuan_order_cnt'      => '已还款数',
        'weihuan_order_cnt'     => '未还款数',
        'yingshou_benjin_sum'   => '应收本金',
        'yingshou_benxi_sum'    => '应收本息',
        'yingshou_zongjine_sum' => '应收总金额',
        'huankuan_benxi_sum'    => '还款本息',
        'huankuan_zonge_sum'    => '还款总额',
        'benjin_huishou_rate'   => '本金回收率',
        'yingshou_huishou_rate' => '应收回款率',
        'zong_huishou_rate'     => '总回款率',
    ),
    // 财务放款
    'finance_pay_list'         => array(
        'date_str'          => '放款日期',
        'order_cnt'         => '应放款数',
        'order_success_cnt' => '已放款数',
        'order_fail_cnt'    => '放款失败数',
        'order_success_sum' => '放款总额',
        'order_repayment_sum' => '还款总额',
    ),

    // 财务总计
    'finance_all_sum_name'     => '新客/旧客',
    'finance_all_sum_list'     => array(
        'name'               => '客户类型',
        'order_apply_sum'    => '申请笔数',
        'order_handle_sum'   => '通过笔数',
        'order_handle_rate'  => '过件率',
        'order_ht_amount'    => '放款合同金额',
        'order_bj_amount'    => '实际放款金额',
        'order_repay_sum'    => '总回收笔数',
        'order_repay_amount' => '总回收金额',
        'order_profit'       => '利润',
        'order_profit_rate'  => '利润率',
    ),

    //------------------------------------------- 2018年7月9日新增表头
    'collection_log'           => array(
        'real_name'        => '催收员',
        'order_cnt_sum'    => '承诺还款笔数',
        'order_ontime_sum' => '已还款笔数',
        'order_undue_sum'  => '未还款笔数',
        'order_due_sum'    => '超出承诺期限',
        'rate'             => '回收率'
    ),


    //------------------------------------------- 2018年6月8日新增表头

    'chart_click_amount_tag'                  => '总点击量',
    'chart_channel_ratio_tag'                 => '渠道/点击占比',
    'chart_download_ratio_tag'                => '下载/点击占比',
    'chart_download_ratio_tag_success'        => '已下载量',
    'chart_download_ratio_tag_fail'           => '未下载量',
    'chart_channel_download_ratio_tag'        => '渠道/下载占比',
    'chart_reg_ratio_tag'                     => '注册/下载占比',
    'chart_reg_ratio_tag_success'             => '已注册量',
    'chart_reg_ratio_tag_fail'                => '未注册量',
    'chart_channel_reg_ratio_tag'             => '渠道/注册占比',
    'chart_apply_ratio_tag'                   => '申请/注册占比',
    'chart_apply_ratio_tag_success'           => '已申请量',
    'chart_apply_ratio_tag_fail'              => '未申请量',
    'chart_channel_apply_ratio_tag'           => '渠道/申请占比',
    'chart_new_old_loan_apply_ratio_tag'      => '新贷客与复贷客申请占比',
    'chart_new_loan_apply_ratio_tag_success'  => '新贷客申请量',
    'chart_old_loan_apply_ratio_tag_success'  => '复贷客申请量',
    'chart_channel_new_loan_apply_ratio_tag'  => '新贷客申请占比',
    'chart_channel_old_loan_apply_ratio_tag'  => '复贷客申请占比',
    'chart_risk_pass_ratio_tag'               => '风控过件占比',
    'chart_risk_pass_ratio_tag_success'       => '风控过件量',
    'chart_risk_pass_ratio_tag_fail'          => '风控未过件量',
    'chart_channel_risk_pass_ratio_tag'       => '渠道/过件占比',
    'chart_approval_pass_ratio_tag'           => '信审/风控过件占比',
    'chart_approval_pass_ratio_success'       => '信审通过量',
    'chart_approval_pass_ratio_fail'          => '信审未通过量',
    'chart_channel_approval_pass_ratio_tag'   => '渠道/信审占比',
    'chart_in_collect_ratio_tag'              => '入催率',
    'chart_in_collect_ratio_ok'               => '到期当天已还款量',
    'chart_in_collect_ratio_due'              => '到期当天未还款量',
    'chart_channel_in_collect_pass_ratio_tag' => '渠道/首逾占比',
    'chart_due_order_ratio_tag'               => '逾期/订单占比',
    'chart_due_day_1'                         => '逾期天数小于3订单量',
    'chart_due_day_2'                         => '逾期天数小于10订单量',
    'chart_due_day_3'                         => '逾期天数小于15订单量',
    'chart_due_day_4'                         => '逾期天数小于30订单量',
    'chart_due_day_5'                         => '逾期天数大于30订单量',
    'chart_channel_due_ratio_tag'             => '渠道逾期占比',
    'chart_due_ratio_tag'                     => '逾期占比',
    'chart_due_ratio_on_due'                  => '逾期中',
    'chart_due_ratio_ok'                      => '已还款',
    'chart_due_ratio_not_over'                => '未到期',
    'chart_due_three_days_ratio_tag'          => 'PD3占比',
    'chart_due_ten_days_ratio_tag'            => 'PD10占比',
    'chart_due_fifteen_days_ratio_tag'        => 'PD15占比',
    'chart_due_thirty_days_ratio_tag'         => 'PD30占比',
    'chart_due_over_thirty_days_ratio_tag'    => 'PD30+占比',
    'chart_finance_yingshou_huikuan_tag'      => '本息回收',
    'chart_finance_yingshou_huikuan_tag_ok'   => '已还本息',
    'chart_finance_yingshou_huikuan_tag_fail' => '未还本息',
    'chart_finance_sum_huikuan_tag'           => '总回款(含罚息)',
    'chart_finance_sum_huikuan_tag_ok'        => '已还款总额',
    'chart_finance_sum_huikuan_tag_fail'      => '未还款总额',
    'chart_finance_loan_repayment_tag'        => '回收报告',
    'chart_finance_principal_tag'             => '本金回收',

    'chart_loan_repayment_field' => array(
        'title'            => '回收报告',
        'date'             => '时间',
        'loan_amount'      => '放款总金额',
        'repayment_amount' => '已还款总额',
        'weihuan_amount'   => '债库金额',
    ),

    'chart_principal_field' => array(
        'title'              => '本金回收',
        'benjinhuishou_rate' => '本金回收率',
        'repayment_amount'   => '已还款总额',
        'yinghuan_benjin'    => '应还本金',
    ),

    //审批详情-历史纪录
    'history_order_list' => array(
        'order_no'     => '订单号',
        'lending_time' => '借款时间',
        'repay_time'   => '到期时间',
        'over_day'    => '逾期天数',
    ),
    

    //------------------------------------------- 公用部分


    // 订单状态

    'order_status'        => array(
        '1'   => '风控待审核',
        '80'  => '待补充资料',
        '90'  => '信审待审核',
        '100' => '审批通过',
        '110' => '审批不通过',
        '160' => '放款中',
        '161' => '贷款取消',
        '169' => '放款失败',
        '170' => '放款成功',
        '180' => '逾期',
        '190' => '还款中',
        '195' => '还款失败',
        '200' => '贷款结清',
    ),

    // 订单信审状态
    'order_handle'               => array(
        '0' => '未进行',
        '1' => '待初审',
        '4' => '待终审',
        '2' => '通过',
        '3' => '不通过',
        //'5' => '放款失败',
    ),

    // 订单风控状态
    'risk_status'        => array(
        '0' => '未进行',
        '1' => '通过',
        '2' => '不通过',
    ),

     // 订单放款状态
    'pay_status'        => array(
        '0' => '未进行',
        '1' => '成功',
        '2' => '失败',
    ),

    // blue pay 请求回调状态
    'pay_callback_lang'   => array(
        '200' => 'BluePay 侧交易完成',
        '201' => '请求BluePay 成功，表示订单在 BluePay 侧成功建立',
        '600' => '请求银行失败，一般由于银行卡账号信息不正确导致的',
        '400' => '参数错误，缺少参数',
        '401' => '签名错误/加密错误',
        '501' => '银行请求超时，交易失败。可重新发起交易',
        '506' => 'IP 限制',
        '404' => '信息未找到，交易信息未找到',
        '500' => '服务内部错误',
        '646' => '银行处理失败',
        '601' => '商户余额不足，放款备付金不足，请联系 BluePay 商务充值。测试环境请联系技术服务',
        '649' => '银行信息错误，账号不对'
    ),

    //职业类型 
    'profession_type' => array(
        '1'  => '销售|客服|市场',
        '2'  => '财务|人力资源|行政',
        '3'  => '项目|质量|高级管理',
        '4'  => 'IT|互联网|通信技术',
        '5'  => '房产|建筑|物业管理',
        '6'  => '金融从业者',
        '7'  => '采购|贸易|交通|物流',
        '8'  => '生产|制造',
        '9'  => '传媒|印刷|艺术|设计',
        '10' => '咨询|法律|教育|翻译',
        '11' => '服务业从业者',
        '12' => '能源|环保|农业|科研',
        '13' => '兼职|实习|社工|其他',
    ),

    //行业类型 
    'industry_type'       => array(
        '1' => '互联网/IT',
        '2' => '金融',
        '3' => '房地产/建筑',
        '4' => '商业服务',
        '5' => '贸易/批发/零售',
        '6' => '教育/艺术',
        '7' => '服务业',
        '8' => '文化/传媒/娱乐',
        '9' => '制造业',
        '10' => '物流运输',
        '11' => '能源/环保',
        '12' => '政府/非盈利',
        '13' => '农林牧渔',
    ),

    // 学历
    'education_type'      => array(
        '1' => '硕士及以上',
        '2' => '本科',
        '3' => '大专',
        '4' => '高中',
        '5' => '初中以下',
    ),

    // 审批跟进记录
    'handle_result_type'         => array(
        '1' => '通过',
        '2' => '拒绝',
        '3' => '终审退回',
    ),

    // 公司审核状态
    'company_status_lang' => array(
        '0' => '通过',
        '1' => '拒绝',
        '2' => '待审核',
        '3' => '审核中',
    ),

    //还款码状态
    'payment_code_status_lang' => array(
        '0' => '过期',
        '1' => '待充值',
    ),

    // 通讯录展示时来源
    'phone_from'                 => [
        '1' => '本人',
        '2' => '联系人',
        '3' => '通讯录'
    ],

    // 联系人匹配
    'phone_match'                => [
        'N' => '不匹配',
        'Y' => '匹配',
        'E' => '-',
    ],

    // 催收标记
    'collection_s'               => [
        '0' => '全部',
        '1' => 'S1(pd 1~10天)',
        '2' => 'S2(pd 11~30天)',
        '3' => 'S3(pd 31天以上)'
    ],

    // 专案查询
    'order_quality'              => [
        '0' => '全部',
        '1' => '是',
        '2' => '否',
    ],

    // 订单信审初审不通过原因
    'order_handle_not_pass_info' => [
        'TL001' => '材料不符',
        'TL002' => '单位号码无效',
        'TL003' => '单位信息不真实',
        'TL004' => '单位信息无法验证',
        'TL005' => '低资质客户',
        'TL006' => '电核负面-其他',
        'TL007' => '短信负面',
        'TL008' => '非本人申请',
        'TL009' => '拒绝提供必要资料',
        'TL010' => '联系人号码无效',
        'TL011' => '联系人信息不真实',
        'TL012' => '联系人信息无法核实',
        'TL013' => '其他',
        'TL014' => '取消申请',
        'TL015' => '申请人核身失败',
        'TL016' => '申请人信息无法核实',
        'TL017' => '无法联络单位',
        'TL018' => '无法联络联系人',
        'TL019' => '无法联络申请人',
        'TL020' => '无异常通过',
        'TL021' => '疑似代办/黑中介',
        'TL022' => '已离职/无业',
        'TL023' => '职业不符合要求',
    ],

    // 订单信审审核记录
    'order_handle_review_log'    => [
        'title'       => '审核记录',
        'add_time'    => '审批时间',
        'admin_name'  => '审批人',
        'refuse_desc' => '拒绝原因',
        'remark'      => '备注',
        'review_desc' => '审核结果'
    ],

    // 订单电核记录
    'order_handle_flow_log'      => [
        'title'      => '电核记录',
        'add_time'   => '跟进时间',
        'relation'   => '跟进对象',
        'status'     => '跟进状态',
        'name'       => '姓名',
        'phone'      => '联系电话',
        'remark'     => '跟进记录',
        'admin_name' => '跟进人员',
    ],

    // 电核状态
    'phone_state'                => [
        '0' => '请选择',
        '1' => '正常',
        '2' => '关机',
        '3' => '空号',
        '4' => '停机',
        '5' => '通话中',
        //'6' => '无法联通',
        '7' => '无人应答',
    ],

    // 通话记录状态
    'record_type' => [
        '1' => '来电',
        '2' => '去电',
        '3' => '未接通',
    ],



    //jia

    "inser_fail" => "插入失败",

    "cllection_case_details"      => "催收详情",
    //催收列表
    'cllection_order_no'          => '订单号',
    'cllection_real_name'         => '用户姓名',
    'cllection_phone'             => '手机号',
    'cllection_due_day'           => '逾期天数',
    'cllection_repay_amount'      => '应还金额',
    'cllection_due_time'          => '应还日期',
    'cllection_collection_status' => '催收状态',
    'cllection_followup_feed'     => '催收反馈',
    'cllection_case_follow_name'  => '催收员',
    'cllection_success_time'      => "还款日期",
    'follow_time'                 => "跟进日期", //催收日期
    'paid_amount'                 => "还款金额", //入帐金额


    //催收客户用户信息
    "cllection_name"              => "姓名",
    "cllection_sex"               => "性别",
    "cllection_idcode"            => "身份证",
    "cllection_card_type"         => "账号类型",
    "cllection_bankcard_name"     => "开户银行",
    "cllection_card_num"          => "银行卡号",
    "cllection_is_marrey"         => "婚姻状况",
    "cllection_email"             => "邮件",
    "cllection_education"         => "教育程度",
    "cllection_relation"          => "关系",

    //催收状态
    'cllection_case_status_0'     => '-',
    'cllection_case_status_170'   => '催收中',
    'cllection_case_status_180'   => '催收中',
    'cllection_case_status_200'   => '已还款',
    //催收反馈
    'cllection_followup_feed_'    => '-',
    'cllection_followup_feed_0'   => '请选择',
    'cllection_followup_feed_181' => '承诺还款',
    'cllection_followup_feed_182' => '协商跟进',
    'cllection_followup_feed_183' => '拒绝还款',
    'cllection_followup_feed_184' => '客户提示已还款',
    'cllection_followup_feed_185' => '承诺跟进',
    'cllection_followup_feed_186' => '配合转告',
    'cllection_followup_feed_187' => '拒绝转告',
    'cllection_followup_feed_188' => '他人转告',
    'cllection_followup_feed_189' => '失联',
    'cllection_followup_feed_190' => '待核实',
    'cllection_followup_feed_191' => '其他',
    'cllection_followup_feed_200' => '申请减免',
    //催收类型
    'cllection_follow_type_'      => '-',
    'cllection_follow_type_0'     => '请选择',
    'cllection_follow_type_81'    => '电催',
    'cllection_follow_type_82'    => '外访',
    'cllection_follow_type_83'    => '司法',
    'cllection_follow_type_84'    => '委外',
    'cllection_follow_type_85'    => '提醒',
    //手机状态
    'cllection_contact_state_'    => '-',
    'cllection_contact_state_0'   => '请选择',
    'cllection_contact_state_1'   => '正常',
    'cllection_contact_state_2'   => '关机',
    'cllection_contact_state_3'   => '空号',
    'cllection_contact_state_4'   => '停机',
    'cllection_contact_state_5'   => '通话中',
//    'cllection_contact_state_6'   => '无法联通',
    'cllection_contact_state_7'   => '无人应答',

    //关系
    'cllection_target_0'          => '请选择',
    'cllection_target_1'          => '父亲',
    'cllection_target_2'          => '母亲',
    'cllection_target_3'          => '兄弟',
    'cllection_target_4'          => '姐妹',
    'cllection_target_5'          => '朋友',
    'cllection_target_6'          => '子女',
    'cllection_target_7'          => '同事',
    'cllection_target_8'          => '其他',
    'cllection_target_9'          => '配偶',
    'cllection_target_10'         => '本人',

    //性别

    'cllection_sex_0'               => '-',
    'cllection_sex_1'               => '男',
    'cllection_sex_2'               => '女',
    //婚姻状态
    'cllection_is_marrey_0'         => '未婚',
    'cllection_is_marrey_1'         => '已婚',
    //教育程度
    'cllection_education_1'         => '硕士及以上',
    'cllection_education_2'         => '本科',
    'cllection_education_3'         => '大专',
    'cllection_education_4'         => '高中',
    'cllection_education_5'         => '初中及以下',


    //催收账单详细
    "cllection_principal"           => "本金",
    "cllection_interest"            => "利息",
    //"repay_amount"		    => "应还金额",
    "cllection_over_interest"       => "罚息",
    //"due_day"			    => "逾期天数",
    "cllection_repay_data"          => "入账信息",
    //费用流水
    "cllection_price"               => "入账金额",
    "cllection_add_time"            => "入账时间",

    //催收
    "cllection_input"               => "请传入",
    "cllection_personal_id"         => "客户id",
    "cllection_case_id"             => "订单id",
    "cllection_operator_time"       => "跟进时间",
    "cllection_follow_type"         => "跟进方式",
    "cllection_target"              => "催收对象",
    "cllection_target_name"         => "姓名",
    "cllection_contact_phone"       => "联系电话",
    "cllection_contact_state"       => "电话状态",
    "cllection_collection_feedback" => "催收反馈",
    "cllection_content"             => "跟进记录",
    "cllection_operator_name"       => "跟进人员",


    //费用减免
    "reduction_title"               => "申请减免",
    "reduction_order_no"            => "订单号",
    "reduction_user_name"           => "客户姓名",
    "reduction_repay_amount"        => "应还总额",
    "reduction_over_fee"            => "罚息",
    "reduction_fee"                 => "减免金额",
    "reduction_fee_remark"          => "减免金额不能大于罚息金额",
    "reduction_remark"              => "备注",
    "reduction_apply_has"           => "已存在申请中的减免，等待审批",
    "reduction_apply_date"          => "申请日期",
    "reduction_apply_name"          => "申请人",
    "reduction_application_amount"  => "本金",
    "reduction_interest"            => "利息",


    "reduction_status"   => "审批状态",
    "reduction_status_0" => "审批中",
    "reduction_status_1" => "通过",
    "reduction_status_2" => "拒绝",
    "reduction_record"   => "申请减免费用",

    //分配催收员

    'cllection_list_no'           => "编号",
    //'cllection_real_name' => lang('cllection_real_name'),
    'cllection_role_name'         => "角色类型",
    'cllection_has_case'          => "当前持有案件数",
    'cllection_can_case'          => "可分配案件数",


    //视图走势
    "order_view_title"            => "催收订单走势报表",
    "order_view_order_count"      => "订单总数",
    "order_view_collection_count" => "实际催收数",

    "collector_view_title"            => "催收员考核表",
    "collector_view_order_count"      => "全部订单",
    "collector_view_collection_count" => "已完成订单",
    "reduction_view_title"            => "费用减免金额",
    'reduction_view_all_fee'          => '订单总金额',
    'reduction_view_all_num'          => '订单数量',
    "close_status"                    => "案件已关闭",
    "has_reduction"                   => "只能申请一次减免",
    "my_relation"                     => "本人",
    "no_case"                         => "没有案件可分配",
    "no_can"                          => "不能进行此操作",
    "cur_yinghuankuan_count"          => "当天到期应还款数",
    "cur_yihuankuan_count"            => "当天到期还款数",
    "all_huan_count"                  => "所有还款数",
    "all_yinghuan_count"              => "所有应还款数",


);