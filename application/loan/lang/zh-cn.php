<?php

/**
 *ThinkPHP api 反馈内容-简体中文语言包
 * @category   Think
 * @package    Lang
 * @author     zhongkai
 */

return array(
    /* UserAction.Class*/

    /* 变量名*/        /*页面返回显示的值*/
    /*标题*/

    'S_title'          => '前海未来豪金融服务',
    'Hunuo'            => '互诺科技',
    'LA'               => '切换语言：',
    'CN'               => '简体中文',
    'EN'               => '英文',

    //是否登录
    'login_status1'    => '已登录',
    'login_status2'    => '未登录',
    //获取用户信息
    'common_infos'     => '网络繁忙，请稍候再试',
    //忘记密码
    'forget_pwd'       => '忘记密码',
    'no_uname'         => '此用户不存在,请重新输入！',
    //验证码
    'success'          => '成功',
    'false'            => '失败',
    //登录处理
    'isblack_1'        => '黑名单用户，请联系管理员！',
    'pwd_f'            => '密码错误,请重新输入！',
    'login_success'    => '登录成功！',
    /*推送下线*/
    'ts_title'         => '下线通知',
    'ts_message'       => '您的账号在另一地点登录,您已被迫下线',
    //处理注册(正则)
    'input_mobile'     => '请填写手机号码！',
    'reg_mobile'       => '手机号码格式错误！',
    'reg_mobile_y'     => '此号码已经被注册了！',
    'reg_pwd'          => '请填写密码！',
    'register_agreef'  => '未同意注册协议！',
    'register_success' => '注册成功！',
    /*密码正则*/
    'reg_pwd_1'        => '密码必须包含至少一个字母！',
    'reg_pwd_2'        => '密码必须包含至少一个数字！',
    'reg_pwd_3'        => '密码必须包含至少含有8个字符！',
    /* 重置密码*/
    'reg_czpwd_1'      => '密码不能为空！',
    'reg_czpwd_2'      => '确认登录密码不能为空！',
    'reg_czpwd_3'      => '两次输入的密码不一致！',
    'oldpwd_f'         => '输入的旧密码不正确！',
    'old_new_pwd'      => '输入的新密码和旧密码相同！',
    'revise_pwd_s'     => '修改密码成功，请重新登录！',
    /*1登录密码，2支付密码*/
    'reg_czpwd_s'      => '密码重置成功！',
    'reg_czpwd_f'      => '密码重置失败！',
    'reg_register'     => '您还没注册，请先注册！',
    /*安全退出*/
    'safe_quit'        => '成功退出！',
    /*我的消息详细*/
    'caozuo_f'         => '操作失误',
    /*删除我的消息*/
    'del_msgs'         => '成功删除此消息',
    'del_msgf'         => '网络繁忙，消息删除失败',


    /* OrderAction.Class*/
    'request_cs_f'     => '请求的参数有误！',
    //保存用户信息，完善资料
    'reg_name'         => '请填写真实姓名！',
    'reg_idcode'       => '请填写身份证号！',
    'reg_idcode_f'     => '请填写正确的身份证号！',
    'reg_phone'        => '请填写手机号码！',
    'reg_city'         => '请选择您的现居城市！',
    'reg_address'      => '请填写您的详细地址！',
    'save_s'           => '保存成功',
    'save_f'           => '保存失败',
    'company'          => '请填写单位名称！',
    'company_add'      => '请填写您的单位地址！',
    'company_tel'      => '请填写您的单位电话！',
    'contact_relation' => '请选择联系人关系！',
    'contact_name'     => '请填写姓名！',
    'contact_phone'    => '请填写电话！',
    'contact_phone_t'  => '请填写正确的手机号码！',
    'equal_relation'   => '联系人关系不能相同',
    'equal_name'       => '联系人名称不能相同',
    'equal_phone'      => '联系人电话不能相同',


    // 上传身份证图片
    'upload_s'         => '上传成功！',
    'upload_f'         => '上传失败！',
    'reg_upload_photo' => '请上传照片！',
    'reg_sfz_name'     => '真实姓名与身份证姓名不符！',
    'reg_sfz_photo'    => '身份证号与照片不符！',
    'reg_cx_upload'    => '无法识别，请重新上传！',
    //活体数据
    //'reg_upload_photo'  => '请上传数据！',
    'reg_sfz_photo_f'  => '身份证正面照片为空！',
    /*请求天机依图身份验证综合接口*/
    'reg_sfz_y'        => '是否是真实有效的身份证照片,请重新上传',
    'verify_sfz_f'     => '验证不通过，请检查你所上传的身份证照片！',
    'verify_s'         => '验证通过！',
    'request_s'        => '请求成功！',
    'request_f'        => '请求错误！',
    //下单，生成账单
    'loan_amount'      => '借款金额不能为空！',
    'loan_term'        => '借款期限不能为空！',
    'basic_data'       => '请完善基本资料！',
    'living_data'      => '请完成活体识别！',
    'zhima_data'       => '请完成芝麻分授权！',
    'mobile_data'      => '请完成运营商授权！',
    'credit_data'      => '请完成征信授权！',
    'bankcard_data'    => '请先绑定银行卡！',
    /*用户是否存在进行中的订单*/
    'exist_order'      => '您已存在进行中的账单！',
    'no_order'         => '该订单不存在',
    'sh_refuse'        => '您最近被审批拒绝过，请往后再来！',
    'loan_f'           => '您最近存在放款失败，请往后再来！',
    'authorize_f'      => '您的芝麻分未进行授权，请授权后再来！',

    /*信息推送*/
    'system_msg'       => '系统信息',
    'apply_content'    => '您的贷款申请已经提交，审核通过后将为你放款。',
    'apply_contentf'   => '您的贷款申请提交失败！',
    'jdqb_content2'    => '您的贷款申请已经提交，审核通过后将为你放款！',

    /*CommonAction.class*/
    /*发送信息*/
    'msg_send_s'       => '短信已发送！',
    'internet_f'       => '网络异常',
    'no_power'         => '您没有权限进行此操作',
    /*检查是否登录*/
    'blacklist_y'      => '命中黑名单',
    'p_login'          => '请登录后再执行此操作',
    'tellcode_f'       => '验证码不正确，请重新输入',
    //1,注册 2,忘记密码，3修改手机
    'jdqb_reg_code'    => '您好！,您的注册验证码为 ',
    'jdqb_save_code'   => '您好！,您的修改验证码为 ',
    'jdqb_login_code'  => '您好！,您的登录验证码为 ',


    'code_time'            => '有效期30分钟，请您尽快验证。',

    /* BankAction.class*/
    //银行卡绑定
    'reg_yl_phone'         => '请填写预留手机号！',
    'reg_bankcard'         => '请填写银行卡号！',
    'reg_bankcard_t'       => '请填写正确的银行卡号！',
    'reg_bankcard_cf'      => '请勿重复绑定银行卡！',
    'no_bankcard'          => '银行卡不存在！',
    'set_bankcard_s'       => '编辑银行卡成功',
    'set_bankcard_f'       => '编辑银行卡失败',
    'bd_bankcard_s'        => '绑定银行卡成功',
    'bd_bankcard_f'        => '绑定银行卡失败',
    'reg_bill'             => '抱歉，你存在进行中的账单！',
    'hs_bankmain'          => '请核对所填写的银行卡信息！',
    'no_bankUnion'         => '银行卡未开通银联！',
    'del_s'                => '删除成功',
    'del_f'                => '删除失败',

    /* MemberAction.class*/
    'revise_s'             => '修改成功',
    'revise_f'             => '修改失败',
    'verfity_s'            => '验证成功',
    'verfity_f'            => '验证失败',
    'exist_name'           => '该用户名已存在！',
    'revise_f_exist_name'  => '修改失败,该用户名已存在！',
    'revise_f_exist_phone' => '修改失败,该手机号已存在！',
    'sfzgs_f'              => '身份证格式错误！',

    /* PayAction.class*/
    'repeat_pay'           => '请勿重复支付！',
    'repay_f'              => '支付失败！',
    'ffcz'                 => '非法操作！',
    'no_bill'              => '账单不存在！',
    'request_isydk'        => '代扣请求受理成功！',
    'request_sl'           => '请求已受理，请勿重复提交！',

    /*信息推送*/
    'hk_content'           => '还款成功,还款金额为 ',
    'jdqb_hk_success'      => '还款成功,还款金额为  ',
    'jdqb_yuqi_ts'         => '尊敬的用户，您的账单已逾期，请尽快还请，以免产生更高的逾期费用。',


    /*new 2017-12-18*/
    'zmf_f'                => '您的芝麻分不足，请以后再来！',
    'jdqb_content3'        => '尊敬的用户，您申请的贷款已经成功到达您的账户。',
    'jdqb_fk_ts'           => '尊敬的用户，您申请的贷款已经成功到达您的账户。',
    'data_lf'              => '数据太长，没有这么大的钱吧，检查下',
    'lyz'                  => '零元整',
    'ints'                 => '整',
    'm_num'                => '零壹贰叁肆伍陆柒捌玖',
    'm_dw'                 => '分角元拾佰仟万拾佰仟亿',
    'me_msg'               => '名额已发放完，请明日再来申请',
    'status_1'             => '待审批',
    'status_80'            => '待补充资料',
    'status_85'            => '待补充资料',
    'status_90'            => '待补充资料',
    //'status_90'			=> '审批中',
    'status_100'           => '审批通过',
    'status_110'           => '审批不通过',
    'status_161'           => '贷款取消',
    'status_169'           => '放款失败',
    'status_170'           => '放款成功',
    'status_180'           => '逾期',
    'status_200'           => '贷款结清',
    'day'                  => '天',
    'month'                => '月',
    'one_stage'            => '1期',
    'bill_sta1'            => '待还款',
    'bill_sta2'            => '还款成功',
    'bill_sta3'            => '已逾期',

    //前台页面
    /*pay*/
    'repay_info'           => '支付信息',
    'repay_ing'            => '正在前往支付',
    'repay_finish'         => '支付完成',
    'alipay'               => '支付宝支付',

    /*order*/
    'ver_msg'              => '验证信息',
    'ver_tjmsg'            => '天机信息认证',
    'tjmsg'                => '银行级别的安全保障，所有信息经过加密处理 ;天机系统不会储存您的账号和密码信息。',
    'mobile_res'           => '运营商报告',
    'ver_ing'              => '正在认证',
    'ver_finish'           => '认证完成',
    /*single*/
    'banquan'              => '版权所有 © 2017 前海未来豪金融服务（深圳）有限公司 粤ICP备05068754号',
    'wzjs'                 => '网站建设：互诺科技',
    'contact_me'           => '联系我们',
    'law'                  => '法律申明',

    /*index*/
    'login'                => '登录',
    'mobile'               => '手机号码',
    'passward'             => '密码',
    'get_order'            => '下单',
    'agree_xy'             => '同意协议',
    'yes'                  => '是',
    'no'                   => '否',
    'submit'               => '提交',

    /*2017-12-20*/
    /*relation*/
    'father'               => '父亲',
    'mother'               => '母亲',
    'spouse'               => '配偶',
    'brother'              => '兄弟',
    'sister'               => '姐妹',
    'children'             => '子女',
    'friend'               => '朋友',
    'colleague'            => '同事',
    'other'                => '其他',

    /*2017-12-26*/
    'rep_tell'             => '请输入区号-固定号码',
    'qdqb_contact'         => '想访问您的通讯录，App需要您的同意，才能访问通讯录！',

    /*2018-01-03*/
    /*education*/
    'master'               => '硕士及以上',
    'undergraduate'        => '本科',
    'dazhuan'              => '大专',
    'senior_school'        => '高中',
    'junior'               => '初中以下',
    'no_zipcode'           => '请填写您的邮编编号！',
    'no_mail'              => '请输入您的邮箱账号！',

    'no_brithday'                 => '请选择生日！',
    'no_edu'                      => '请选择学历！',
    'rep_zipcode'                 => '请输入正确的邮编！',
    'rep_mail'                    => '请输入正确的邮箱格式！',


    /*2018-01-10*/
    /*industry*/
    'industry1'                   => '农业，林业，狩猎和渔业',
    'industry2'                   => '采矿/采石业',
    'industry3'                   => '制造业',
    'industry4'                   => '电力，煤气和水',
    'industry5'                   => '敷设渠道',
    'industry6'                   => '批发/零售贸易，餐馆/旅馆',
    'industry7'                   => '运输，仓储和通讯',
    'industry8'                   => '融资，保险，房地产，商务服务',
    'industry9'                   => '社区，社会个人服务',
    'rep_indus'                   => '请选择行业！',

    /*2018-01-10*/
    /*profession*/
    'p1'                          => '专业，技术和相关的工作人员',
    'p2'                          => '行政和管理',
    'p3'                          => '文书和相关工作人员',
    'p4'                          => '销售人员',
    'p5'                          => '服务工作者',
    'p6'                          => '农林渔猎工',
    'p7'                          => '生产及相关工人,运输设备经营者和劳动者',
    'p8'                          => '司机',
    'p9'                          => '其他',
    'rep_profess'                 => '请选择职业！',


    /*2018-01-16*/
    /*huoticeshi*/
    'jc_sfz'                      => '请检查你所上传的身份证照片！',
    'verify_f'                    => '验证不通过',
    'face_mj'                     => '人脸是面具！',
    'face_hc'                     => '人脸为软件合成相片！',
    'face_fp'                     => '人脸为屏幕翻拍相片！',
    'not_person'                  => '认为人脸不是同一人！',

    /*2018-01-18*/
    /*中国国际区号*/
    'countrycode'                 => '86',
    'no_briyhday'                 => '请选择您的出生日期！',
    'reg_czpwd_same'              => '新密码不能与原密码相同！',
    'session_status'              => '您的账号在另一设备登录,您已被迫下线',
    'lan'                         => 'en',           //英文en，印尼文：id
    'Risk_prompt'                 => '抱歉，您申请的订单未通过平台审核，请往后再来',
    'Risk_prompts'                => '抱歉，您申请的订单未通过平台审核，请往后再来',


    // 2018年3月13日
    // Ai message
    'id_check_success'            => '对比完成，相似度',
    'ocr_check_miss_match'        => '上传的身份证照片信息和原信息不符',
    'ocr_error'                   => 'OCR检查失败',
    'ocr_success'                 => '扫描入库完成',
    'identity_check_error'        => '用户信息不完善',
    'identity_check_success'      => '获取并更新完成',
    'identity_check_invalid_card' => '无效的ID号码',
    'identity_check_not_found'    => '系统中未找到',
    // Ai common message
    'ai_error_image_type'         => '无效的图像格式',
    'ai_error_image_size'         => '无效的图像尺寸',
    'ai_error_one_face'           => '上传的图片中只能找到一张脸',
    'ai_error_none_face'          => '无法在上传的图像中找到任何脸部',
    'ai_check_html_error'         => '网络错误',
    'adv_verify_not_pass'         => '身份校验不通过',
    'name_length_not'             => '姓名长度不对',

    'version_too_low' => '版本过低，请更新到最新版本',

    'get_user_image_succ' => '获取用户图片资料完成',

    'feed_back_succ'             => '意见反馈提交完成',
    'feed_back_err'              => '意见反馈提交失败',
    'no_live_time'               => '请填写居住时间',
    'edit_order_bank_not_params' => '银行账号不能为空',
    'edit_order_bank_fail_limit' => '修改银行卡次数达到上限',
    'edit_order_bank_not_order'  => '没有找到订单',
    'edit_order_bank_save_succ'  => '修改银行卡成功',
    'edit_order_bank_save_err'   => '修改银行卡失败',
    'app_version_allow_update'   => '有新版本更新',
    'app_version_not_update'     => '已是最新版本',

    'cron_tab_sms1' => '明天是你最后的还款日，请及时还款，保持良好信用。',
    'cron_tab_sms2' => '今天是你最后的还款日，请及时还款，保持良好信用。',

    'can_not_make_order' => '当前尚未开闸，无法下单',
    'over_msg'           => '你好，你的账单到期了。 请立即偿还，以避免更高的结算费用',

    'before_user_basic' => '请先完善基本信息',

    'live_time_bigger' => '居住时间不能大于出生时间',

    'please_update_app' => '请更新APP版本后再试',

    'app_header_old' => '【Dompet kamu】',
    'app_header_new' => '【saku kamu】',

    'repay_title' => 'Pesan pembayaran kembali',


    "firstPass" => "恭喜你，你的订单已通过初审，请继续完善资料",

    'month_params_error' => '请填写月收入',

    'faith_error' => '请选择宗教信仰',
    'live_error'  => '请选择房屋属性',

    'faith_type' => [
        '0' => '请选择',
        '1' => '天主教',
        '2' => '基督教',
        '3' => '伊斯兰教',
        '4' => '佛教',
        '5' => '其他',
    ],

    'marry_type' => [
        '0' => '未婚',
        '1' => '已婚',
        '2' => '离异',
        '3' => '其他',
    ],

    'live_type' => [
        '0' => '请选择',
        '1' => '自有',
        '2' => '租赁',
        '3' => '宿舍',
        '4' => '与家人同住',
        '5' => '其他'
    ],

);

?>
