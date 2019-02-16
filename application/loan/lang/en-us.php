<?php

/**
 *ThinkPHP api 反馈内容-英文语言包
 * @category   Think
 * @package    Lang
 * @author     zhongkai
 */

return array(
    /* UserAction.Class*/

    /* 变量名*/        /*页面返回显示的值*/
    /*标题*/

    'S_title'          => 'Qianhai Future Hao Financial Services',//前海未来豪金融服务
    'Hunuo'            => 'Mutual Technology',//互诺科技
    'LA'               => 'Switch language:',//切换语言：
    'CN'               => 'Simplified Chinese',//简体中文
    'EN'               => 'English',//英文

    //是否登录
    'login_status1'    => 'Has logged',//已登录
    'login_status2'    => 'Not logged in',//未登录
    //获取用户信息
    'common_infos'     => 'The network is busy, please try again later',//网络繁忙，请稍候再试
    //忘记密码
    'forget_pwd'       => 'forget password',//忘记密码
    'no_uname'         => 'This user does not exist, please re-enter!',//此用户不存在,请重新输入！
    //验证码
    'success'          => 'success',//成功
    'false'            => 'failure',//失败
    //登录处理
    'isblack_1'        => 'Blacklist users, please contact the administrator!',//黑名单用户，请联系管理员！
    'pwd_f'            => 'Wrong password, please re-enter!',//密码错误,请重新输入！
    'login_success'    => 'login successful!',//登录成功！
    /*推送下线*/
    'ts_title'         => 'Offline notice',//下线通知
    'ts_message'       => 'Your account is logged in at another location and you have been forced to go offline',//您的账号在另一地点登录,您已被迫下线
    //处理注册(正则)
    'input_mobile'     => 'please fill in cell phone number!',//请填写手机号码！
    'reg_mobile'       => 'Wrong format of phone number!',//手机号码格式错误！
    'reg_mobile_y'     => 'This number has already been registered!',//此号码已经被注册了！
    'reg_pwd'          => 'Please fill in the password!',//请填写密码！
    'register_agreef'  => 'Did not agree to the registration agreement!',//未同意注册协议！
    'register_success' => 'registration success!',//注册成功！
    /*密码正则*/
    'reg_pwd_1'        => 'The password must contain at least one letter!',//密码必须包含至少一个字母！
    'reg_pwd_2'        => 'The password must contain at least one number!',//密码必须包含至少一个数字！
    'reg_pwd_3'        => 'The password must contain at least 8 characters!',//密码必须包含至少含有8个字符！
    /* 重置密码*/
    'reg_czpwd_1'      => 'password can not be blank!',//密码不能为空！
    'reg_czpwd_2'      => 'Confirm that the login password cannot be empty!',//确认登录密码不能为空！
    'reg_czpwd_3'      => 'The passwords entered twice are inconsistent!',//两次输入的密码不一致！
    'oldpwd_f'         => 'The old password you entered is incorrect!',//输入的旧密码不正确！
    'old_new_pwd'      => 'The new password entered is the same as the old password!',//输入的新密码和旧密码相同！
    'revise_pwd_s'     => 'Change the password successfully, please log in again!',//修改密码成功，请重新登录！
    /*1登录密码，2支付密码*/
    'reg_czpwd_s'      => 'Password reset is successful!',//密码重置成功！
    'reg_czpwd_f'      => 'Password reset failed!',//密码重置失败！
    'reg_register'     => 'You have not registered yet, please register first!',//您还没注册，请先注册！
    /*安全退出*/
    'safe_quit'        => 'Successfully quit!',//成功退出！
    /*我的消息详细*/
    'caozuo_f'         => 'Operation error',//操作失误
    /*删除我的消息*/
    'del_msgs'         => 'Successfully deleted this message',//成功删除此消息
    'del_msgf'         => 'The network is busy and the message deletion fails.',//网络繁忙，消息删除失败


    /* OrderAction.Class*/
    'request_cs_f'     => 'The requested parameters are incorrect!',//请求的参数有误！
    //保存用户信息，完善资料
    'reg_name'         => 'Please fill in your real name!',//请填写真实姓名！
    'reg_idcode'       => 'Please fill in the ID number!',//请填写身份证号！
    'reg_idcode_f'     => 'Please fill in the correct ID number!',//请填写正确的身份证号！
    'reg_phone'        => 'please fill in cell phone number!',//请填写手机号码！
    'reg_city'         => 'Please choose your current city!',//请选择您的现居城市！
    'reg_address'      => 'Please fill in your full address!',//请填写您的详细地址！
    'save_s'           => 'Saved successfully',//保存成功
    'save_f'           => 'Save failed',//保存失败
    'company'          => 'Please fill in the name of the unit!',//请填写单位名称！
    'company_add'      => 'Please fill in your address!',//请填写您的单位地址！
    'company_tel'      => 'Please fill in your unit phone number!',//请填写您的单位电话！
    'contact_relation' => 'Please select a contact relationship!',//请选择联系人关系！
    'contact_name'     => 'Please fill in the name!',//请填写姓名！
    'contact_phone'    => 'Please fill in the phone!',//请填写电话！
    'contact_phone_t'  => 'Please fill in the correct mobile number!',//请填写正确的手机号码！
    'equal_relation'   => 'Contact relationship cannot be the same',//联系人关系不能相同
    'equal_name'       => 'Contact name cannot be the same',//联系人名称不能相同
    'equal_phone'      => 'Contact phone number cannot be the same',//联系人电话不能相同


    // 上传身份证图片
    'upload_s'         => 'Uploaded successfully!',//上传成功！
    'upload_f'         => 'upload failed!',//上传失败！
    'reg_upload_photo' => 'Please upload photos!',//请上传照片！
    'reg_sfz_name'     => 'The real name does not match the ID card name!',//真实姓名与身份证姓名不符！
    'reg_sfz_photo'    => 'The ID number does not match the photo!',//身份证号与照片不符！
    'reg_cx_upload'    => 'Unrecognized, please re-upload!',//无法识别，请重新上传！
    //活体数据
    //'reg_upload_photo'  => '请上传数据！',
    'reg_sfz_photo_f'  => 'The front photo of the ID card is empty!',//身份证正面照片为空！
    /*请求天机依图身份验证综合接口*/
    'reg_sfz_y'        => 'Is it a true and valid ID card photo, please re-upload',//是否是真实有效的身份证照片,请重新上传
    'verify_sfz_f'     => 'If the verification does not pass, please check the photo of the ID you uploaded!',//验证不通过，请检查你所上传的身份证照片！
    'verify_s'         => 'Verification passed!',//验证通过！
    'request_s'        => 'Request is successful!',//请求成功！
    'request_f'        => 'Request error!',//请求错误！
    //下单，生成账单
    'loan_amount'      => 'The amount of the loan cannot be empty!',//借款金额不能为空！
    'loan_term'        => 'The loan period cannot be empty!',//借款期限不能为空！
    'basic_data'       => 'Please improve the basic information!',//请完善基本资料！
    'living_data'      => 'Please complete the living recognition!',//请完成活体识别！
    'zhima_data'       => 'Please complete the sesame sub-authorization!',//请完成芝麻分授权！
    'mobile_data'      => 'Please complete the operator authorization!',//请完成运营商授权！
    'credit_data'      => 'Please complete the credit authorization!',//请完成征信授权！
    'bankcard_data'    => 'Please bind the bank card first!',//请先绑定银行卡！
    /*用户是否存在进行中的订单*/
    'exist_order'      => 'You already have an in-progress bill!',//您已存在进行中的账单！
    'no_order'         => 'The order does not exist',//该订单不存在
    'sh_refuse'        => 'You have recently been rejected by the approval, please come back later!',//您最近被审批拒绝过，请往后再来！
    'loan_f'           => 'You have recently failed to release money, please come back later!',//您最近存在放款失败，请往后再来！
    'authorize_f'      => 'Your sesame seeds are not authorized, please come back after authorization!',//您的芝麻分未进行授权，请授权后再来！

    /*信息推送*/
    'system_msg'       => 'system message',//系统信息
    'apply_content'    => 'Your loan application has been submitted and will be released for you after the approval.',//您的贷款申请已经提交，审核通过后将为你放款。
    'apply_contentf'   => 'Your loan application submission failed!',//您的贷款申请提交失败！
    'jdqb_content2'    => 'Your loan application has been submitted and will be released for you after the approval!',//您的贷款申请已经提交，审核通过后将为你放款！

    /*CommonAction.class*/
    /*发送信息*/
    'msg_send_s'       => 'SMS has been sent!',//短信已发送！
    'internet_f'       => 'network anomaly',//网络异常
    'no_power'         => 'You don\'t have permission to do this',//您没有权限进行此操作
    /*检查是否登录*/
    'blacklist_y'      => 'Hit blacklist',//命中黑名单
    'p_login'          => 'Please log in before doing this',//请登录后再执行此操作
    'tellcode_f'       => 'The verification code is incorrect. Please re-enter',//验证码不正确，请重新输入
    //1,注册 2,忘记密码，3修改手机
    'jdqb_reg_code'    => 'Hello! , your registration verification code is',//您好！,您的注册验证码为 
    'jdqb_save_code'   => 'Hello! , your edit verification code is',//您好！,您的修改验证码为 
    'jdqb_login_code'  => 'Hello! , your login verification code is',//您好！,您的登录验证码为 


    'code_time'            => 'Valid for 30 minutes, please verify as soon as possible.',//有效期30分钟，请您尽快验证。

    /* BankAction.class*/
    //银行卡绑定
    'reg_yl_phone'         => 'Please fill in the reserved mobile number!',//请填写预留手机号！
    'reg_bankcard'         => 'Please fill in the bank card number!',//请填写银行卡号！
    'reg_bankcard_t'       => 'Please fill in the correct bank card number!',//请填写正确的银行卡号！
    'reg_bankcard_cf'      => 'Do not double bond your bank card!',//请勿重复绑定银行卡！
    'no_bankcard'          => 'Bank card does not exist!',//银行卡不存在！
    'set_bankcard_s'       => 'Edit bank card successfully',//编辑银行卡成功
    'set_bankcard_f'       => 'Edit bank card failed',//编辑银行卡失败
    'bd_bankcard_s'        => 'Bind bank card successfully',//绑定银行卡成功
    'bd_bankcard_f'        => 'Binding bank card failed',//绑定银行卡失败
    'reg_bill'             => 'Sorry, you have an ongoing bill!',//抱歉，你存在进行中的账单！
    'hs_bankmain'          => 'Please check the bank card information filled in!',//请核对所填写的银行卡信息！
    'no_bankUnion'         => 'Bank card has not opened UnionPay!',//银行卡未开通银联！
    'del_s'                => 'successfully deleted',//删除成功
    'del_f'                => 'failed to delete',//删除失败

    /* MemberAction.class*/
    'revise_s'             => 'Successfully modified',//修改成功
    'revise_f'             => 'fail to edit',//修改失败
    'verfity_s'            => 'Successful verification',//验证成功
    'verfity_f'            => 'verification failed',//验证失败
    'exist_name'           => 'This username already exists!',//该用户名已存在！
    'revise_f_exist_name'  => 'The modification failed and the username already exists!',//修改失败,该用户名已存在！
    'revise_f_exist_phone' => 'The modification failed, the phone number already exists!',//修改失败,该手机号已存在！
    'sfzgs_f'              => 'The ID card format is wrong!',//身份证格式错误！

    /* PayAction.class*/
    'repeat_pay'           => 'Do not pay again!',//请勿重复支付！
    'repay_f'              => 'Payment failed!',//支付失败！
    'ffcz'                 => 'Illegal operation!',//非法操作！
    'no_bill'              => 'The bill does not exist!',//账单不存在！
    'request_isydk'        => 'The withholding request was accepted successfully!',//代扣请求受理成功！
    'request_sl'           => 'Request has been accepted, please do not submit again!',//请求已受理，请勿重复提交！

    /*信息推送*/
    'hk_content'           => 'The repayment was successful and the repayment amount was',//还款成功,还款金额为 
    'jdqb_hk_success'      => 'The repayment was successful and the repayment amount was',//还款成功,还款金额为  
    'jdqb_yuqi_ts'         => 'Dear users, your bill has been overdue, please please do so as soon as possible to avoid a higher overdue fee.',//尊敬的用户，您的账单已逾期，请尽快还请，以免产生更高的逾期费用。


    /*new 2017-12-18*/
    'zmf_f'                => 'Your sesame is not enough, please come back later!',//您的芝麻分不足，请以后再来！
    'jdqb_content3'        => 'Dear users, the loan you applied for has successfully arrived at your account.',//尊敬的用户，您申请的贷款已经成功到达您的账户。
    'jdqb_fk_ts'           => 'Dear users, the loan you applied for has successfully arrived at your account.',//尊敬的用户，您申请的贷款已经成功到达您的账户。
    'data_lf'              => 'The data is too long, there is no such big money, check it.',//数据太长，没有这么大的钱吧，检查下
    'lyz'                  => 'Zero yuan',//零元整
    'ints'                 => 'whole',//整
    'm_num'                => 'Zero 壹贰叁肆伍柒捌玖',//零壹贰叁肆伍陆柒捌玖
    'm_dw'                 => 'Dividing corners',//分角元拾佰仟万拾佰仟亿
    'me_msg'               => 'The quota has been issued. Please apply again tomorrow.',//名额已发放完，请明日再来申请
    'status_1'             => 'Pending',//待审批
    'status_80'            => 'Additional information',//待补充资料
    'status_85'            => 'Additional information',//待补充资料
    'status_90'            => 'Additional information',//待补充资料
    //'status_90'			=> '审批中',
    'status_100'           => 'Approved',//审批通过
    'status_110'           => 'Approval not passed',//审批不通过
    'status_161'           => 'Loan cancellation',//贷款取消
    'status_169'           => 'Lending failure',//放款失败
    'status_170'           => 'Successful loan',//放款成功
    'status_180'           => 'Overdue',//逾期
    'status_200'           => 'Loan settlement',//贷款结清
    'day'                  => 'day',//天
    'month'                => 'month',//月
    'one_stage'            => 'Phase 1',//1期
    'bill_sta1'            => 'Repayment',//待还款
    'bill_sta2'            => 'Successful repayment',//还款成功
    'bill_sta3'            => 'Overdue',//已逾期

    //前台页面
    /*pay*/
    'repay_info'           => 'Payment Information',//支付信息
    'repay_ing'            => 'Going to pay',//正在前往支付
    'repay_finish'         => 'Payment completed',//支付完成
    'alipay'               => 'Pay with Ali-Pay',//支付宝支付

    /*order*/
    'ver_msg'              => 'verify message',//验证信息
    'ver_tjmsg'            => 'Celestial information authentication',//天机信息认证
    'tjmsg'                => 'Bank-level security, all information is encrypted; the secret system does not store your account and password information.',//银行级别的安全保障，所有信息经过加密处理 ;天机系统不会储存您的账号和密码信息。
    'mobile_res'           => 'Carrier report',//运营商报告
    'ver_ing'              => 'Certifying',//正在认证
    'ver_finish'           => 'Certification completed',//认证完成
    /*single*/
    'banquan'              => 'Copyright © 2017 Qianhai Future Hao Financial Services (Shenzhen) Co., Ltd. 粤ICP备05068754号',//版权所有 © 2017 前海未来豪金融服务（深圳）有限公司 粤ICP备05068754号
    'wzjs'                 => 'Website construction: Mutual Technology',//网站建设：互诺科技
    'contact_me'           => 'contact us',//联系我们
    'law'                  => 'Legal Notice',//法律申明

    /*index*/
    'login'                => 'log in',//登录
    'mobile'               => 'cellphone number',//手机号码
    'passward'             => 'password',//密码
    'get_order'            => 'Order',//下单
    'agree_xy'             => 'Agree to the agreement',//同意协议
    'yes'                  => 'Yes',//是
    'no'                   => 'No',//否
    'submit'               => 'submit',//提交

    /*2017-12-20*/
    /*relation*/
    'father'               => 'father',//父亲
    'mother'               => 'mother',//母亲
    'spouse'               => 'spouse',//配偶
    'brother'              => 'brothers',//兄弟
    'sister'               => 'sisters',//姐妹
    'children'             => 'child',//子女
    'friend'               => 'friend',//朋友
    'colleague'            => 'colleague',//同事
    'other'                => 'other',//其他

    /*2017-12-26*/
    'rep_tell'             => 'Please enter the area code - fixed number',//请输入区号-固定号码
    'qdqb_contact'         => 'To access your address book, the app needs your consent to access the address book!',//想访问您的通讯录，App需要您的同意，才能访问通讯录！

    /*2018-01-03*/
    /*education*/
    'master'               => 'Master degree and above',//硕士及以上
    'undergraduate'        => 'Bachelor',//本科
    'dazhuan'              => 'College',//大专
    'senior_school'        => 'High school',//高中
    'junior'               => 'Junior high school or below',//初中以下
    'no_zipcode'           => 'Please fill in your zip code number!',//请填写您的邮编编号！
    'no_mail'              => 'Please enter your email account!',//请输入您的邮箱账号！

    'no_brithday'                 => 'Please choose your birthday!',//请选择生日！
    'no_edu'                      => 'Please choose a degree!',//请选择学历！
    'rep_zipcode'                 => 'Please enter the correct zip code!',//请输入正确的邮编！
    'rep_mail'                    => 'Please enter the correct email format!',//请输入正确的邮箱格式！


    /*2018-01-10*/
    /*industry*/
    'industry1'                   => 'Agriculture, forestry, hunting and fishing',//农业，林业，狩猎和渔业
    'industry2'                   => 'Mining / quarrying',//采矿/采石业
    'industry3'                   => 'manufacturing',//制造业
    'industry4'                   => 'Electricity, gas and water',//电力，煤气和水
    'industry5'                   => 'Laying channels',
    'industry6'                   => 'Wholesale/retail trade, restaurants/hotels',//批发/零售贸易，餐馆/旅馆
    'industry7'                   => 'Transportation, warehousing and communication',//运输，仓储和通讯
    'industry8'                   => 'Financing, insurance, real estate, business services',//融资，保险，房地产，商务服务
    'industry9'                   => 'Community, social personal service',//社区，社会个人服务
    'rep_indus'                   => 'Please choose the industry!',//请选择行业！

    /*2018-01-10*/
    /*profession*/
    'p1'                          => 'Professional, technical and related staff',//专业，技术和相关的工作人员
    'p2'                          => 'Administration and management',//行政和管理
    'p3'                          => 'Instruments and related staff',//文书和相关工作人员
    'p4'                          => 'salesperson',//销售人员
    'p5'                          => 'Service worker',//服务工作者
    'p6'                          => 'Agriculture, forestry, fishing and hunting',//农林渔猎工
    'p7'                          => 'Production and related workers, transport equipment operators and laborers',//生产及相关工人,运输设备经营者和劳动者	
    'p8'                          => 'driver',//司机
    'p9'                          => 'other',//其他
    'rep_profess'                 => 'Please choose a career!',//请选择职业！


    /*2018-01-16*/
    /*huoticeshi*/
    'jc_sfz'                      => 'Please check the photo of the ID you uploaded!',//请检查你所上传的身份证照片！
    'verify_f'                    => 'Verification failed',//验证不通过
    'face_mj'                     => 'The face is a mask!',//人脸是面具！
    'face_hc'                     => 'The face is a software composite photo!',//人脸为软件合成相片！
    'face_fp'                     => 'The face is a screen remake photo!',//人脸为屏幕翻拍相片！
    'not_person'                  => 'Think that the face is not the same person!',//认为人脸不是同一人！

    /*2018-01-18*/
    /*中国国际区号*/
    'countrycode'                 => '86',
    'no_briyhday'                 => 'Please choose your date of birth!',//请选择您的出生日期！
    'reg_czpwd_same'              => 'The new password cannot be the same as the original password!',//新密码不能与原密码相同！
    'session_status'              => 'Your account is logged in on another device and you have been forced to go offline',//您的账号在另一设备登录,您已被迫下线
    'lan'                         => 'en',           //英文en，印尼文：id
    'Risk_prompt'                 => 'Sorry, the order you applied for has not passed the platform review. Please come back later.',//抱歉，您申请的订单未通过平台审核，请往后再来
    'Risk_prompts'                => 'Sorry, the order you applied for has not passed the platform review. Please come back later.',//抱歉，您申请的订单未通过平台审核，请往后再来
    "firstPass"                   => "Congratulations, your order has passed the first instance. Please continue to improve the information.",

    // 2018年3月13日
    // Ai message
    'id_check_success'            => 'Contrast completion, similarity',//对比完成，相似度
    'ocr_check_miss_match'        => 'The uploaded ID photo information does not match the original information.',//上传的身份证照片信息和原信息不符
    'ocr_error'                   => 'OCR check failed',//OCR检查失败
    'ocr_success'                 => 'Scanning inbound',//扫描入库完成
    'identity_check_error'        => 'Incomplete user information',//用户信息不完善
    'identity_check_success'      => 'Get and update completed',//获取并更新完成
    'identity_check_invalid_card' => 'Get invalid ID number',//无效的ID号码
    'identity_check_not_found'    => 'Not found in the system',//系统中未找到
    // Ai common message
    'ai_error_image_type'         => 'Invalid image format',//无效的图像格式
    'ai_error_image_size'         => 'Invalid image size',//无效的图像尺寸
    'ai_error_one_face'           => 'Only one face can be found in the uploaded image',//上传的图片中只能找到一张脸
    'ai_error_none_face'          => 'Unable to find any face in the uploaded image',//无法在上传的图像中找到任何脸部
    'ai_check_html_error'         => 'Network Error',//网络错误
    'adv_verify_not_pass'         => 'Identity verification does not pass',//身份校验不通过
    'name_length_not'             => 'The name is not the correct length',//姓名长度不对

    'version_too_low' => 'The version is too low, please update to the latest version',//版本过低，请更新到最新版本

    'get_user_image_succ' => 'Get user image data completed',//获取用户图片资料完成

    'feed_back_succ'             => 'Feedback submission completed',//意见反馈提交完成
    'feed_back_err'              => 'Feedback submission failed',//意见反馈提交失败
    'no_live_time'               => 'Please fill in the time of residence',//请填写居住时间
    'edit_order_bank_not_params' => 'Bank account cannot be empty',//银行账号不能为空
    'edit_order_bank_fail_limit' => 'Change the number of bank cards to the maximum',//修改银行卡次数达到上限
    'edit_order_bank_not_order'  => 'No order found',//没有找到订单
    'edit_order_bank_save_succ'  => 'Modify bank card successfully',//修改银行卡成功
    'edit_order_bank_save_err'   => 'Modify bank card failed',//修改银行卡失败
    'app_version_allow_update'   => 'New version update',//有新版本更新
    'app_version_not_update'     => 'Already the latest version',//已是最新版本

    'cron_tab_sms1' => 'Tomorrow is your last repayment date, please repay in time and maintain good credit.',//明天是你最后的还款日，请及时还款，保持良好信用。
    'cron_tab_sms2' => 'Today is your last repayment date, please repay in time and maintain good credit.',//今天是你最后的还款日，请及时还款，保持良好信用。

    'can_not_make_order' => 'Currently unlocked, unable to place an order',//当前尚未开闸，无法下单
    'over_msg'           => 'Hello, your bill has expired. Please repay immediately to avoid higher billing costs',//你好，你的账单到期了。 请立即偿还，以避免更高的结算费用

    'before_user_basic' => 'Please complete the basic information first.',//请先完善基本信息

    'live_time_bigger' => 'Living time cannot be greater than birth time',//居住时间不能大于出生时间

    'please_update_app' => 'Please update the app version and try again',//请更新APP版本后再试

    'app_header_old' => '【Dompet kamu】',
    'app_header_new' => '【saku kamu】',

    'month_params_error' => 'Please fill in the monthly income',
    'faith_error'        => 'Please choose religious beliefs',
    'live_error'         => 'Please select the property property',


    'faith_type' => [
        0 => 'please choose',
        1 => 'Catholic',
        2 => 'Christian',
        3 => 'Islam',
        4 => 'Buddhism',
        5 => 'other',
    ],

    'marry_type' => [
        0 => 'unmarried',
        1 => 'married',
        2 => 'divorced',
        3 => 'other',
    ],

    'live_type' => [
        0 => 'please choose',
        1 => 'Own',
        2 => 'lease',
        3 => 'dorm room',
        4 => 'live with family',
        5 => 'other'
    ],

);

?>
