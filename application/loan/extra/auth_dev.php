<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/8/15
 * Time: 14:41
 */
return [
    'REDIS' => [
        'host'       => '127.0.0.1',//127.0.0.1
        'port'       => 6379,
        'password'   => 'redis123',//redis123
        'select'     => 0,//指定库
        'timeout'    => 0,//关闭时间 0:代表不关闭
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
    ],
    'OSS' => [
        'endpoint' => 'oss-cn-shenzhen.aliyuncs.com',
        'bucket'   => 'tupulian',
        'accesskeyid' => 'LTAIz0M71XTNCfNg',
        'accesskeysecret' => 'SatZDyzuSrdmMtuAqJMdxsClK1iaWi',
    ],
    'FACE' => [
        'app_key' => 'sVB1ii_WwUbNv3wJwdPJnvdhmMKiihJ7',
        'app_secret' => 'eGZxuX9i0_NyJhnpS14S56FufjueiYDz',
    ],
    'MONGO' => [
        'type'          => '\think\mongo\Connection',
        'hostname'      => '120.77.81.91',
        'database'      => 'app_ydh_china',
        'username'      => 'app_ydh_china',
        'password'      => 'app_ydh_china',
        'hostport'      => '27017',
        'pk_convert_id' => true,
    ],

    'JPush' => [
        'key'    => 'a821830d90113432a2e3197c',
        'secret' => '5bf6c14e78906a41b61c27ad',
    ],

    //秒嘀短信
    'MIAODI' =>[
        'account_sid' => 'b0a1918df72741af883d019a0984f7f2',
        'auth_token'  => '8428992cb5f74c1e88201bcefb9052ba',
    ],
    //未来无线
    'WLWX' =>[
        'CUST_CODE' => 860463,
        'CUST_PWD'  => '2JGUM5T2XP',
        'SMS_HOST'  => '123.58.255.70',
    ],
    //商汤验证
    'SENSE_TIME' =>[
        'api_key' => 'e8768151cc3146f5a0895ff80c34abec',
        'api_secret' => 'f4aab2bed8ba47cea983d4ed25b856ea',
    ],
    //百融黑名单
    'VERIFY_BLACK' =>[
        'user_name' => 'hesongStr',
        'password' => 'hesongStr',
        'api_code' => '3003034',
        'login_url' => 'https://api.100credit.cn/bankServer2/user/login.action',
    ],
    //新颜
    'XIN_YAN' =>[
        'member_id' => '8150722935',
        'terminal_id' => '1811270047',
        'aes_key' => 'vypTwTAfbZtpvQ3A',
        'xwld_url' => 'https://test.xinyan.com/product/radar/v3/behavior',
        'fmlh_url' => 'https://test.xinyan.com/product/negative/v3/black',
        'fmxb_url' => 'https://test.xinyan.com/product/negative/v3/white',
        'pfx_pwd' => '217526',
        'rsa_member_id' => '8000013189',
        'rsa_terminal_id' => '8000013189',
        'pri_key_name' => '8000013189_pri.pfx',//测试私钥文件名称
    ],
    //白骑士
    'BAIQISHI' =>[
        'partnerid' => 'yidaihuan',
        'verifykey' => '5843f5a08b584cb69b8966603e3c2519',
        'appid' => 'ydh001',
        'decision_request_url' => 'https://api.baiqishi.com/services/decision',
        'upload_request_url' => 'https://api.baiqishi.com/services/decision/result',
    ],
    //翔一
    'XIANGYI' =>[
        'merchantId' => 'fb28de5f9160a461',
        'productType' => '73',
        'gateway_url' => 'http://rcatestapi.lingyizhengxin.com/gateway', //统一网关
    ],
    //合利宝
    'HELIBAO' =>[
        'agencypay_request_url'=>'http://test.trx.helipay.com/trx/transfer/interface.action',//代付测试地址
        'quickpay_request_url'=>'http://test.trx.helipay.com/trx/quickPayApi/interface.action',//快捷支付测试地址
        'customer_number' => 'C1800363646',//商户编号
        'agencypay_notify_url' => 'http://developing.api.china.tupulian.com/loan/pay/agency_pay_callback',//代付回调地址
        'quickpay_notify_url' => 'http://developing.api.china.tupulian.com/loan/pay/quick_pay_callback',//快捷支付回调地址
        'agencypay_sign_key' => '7QeJphU54N0YQpESmCawRcSTx13nzs6C',//用于代付验签
        'quickpay_sign_key' => 'Lw4LUxef0Rc92vSPtZ4WHtdBzrARYSse',//用于快捷支付验签
    ],
    //汇聚支付
    'HUIJU' => [
        'merchant_no' => '888105200007262',//商户号
        'merchant_name' => '长沙和颂网络科技有限公司',//商户名
        'md5_sign_key' => '1ddac990df5b4367a2066f6eca117ab1',//md5加密密钥
        'agencypay_request_url'=>'https://www.joinpay.com/payment/pay/singlePay',//代付地址
        'quickpay_sms_url'=>'https://www.joinpay.com/trade/fastpaySmsApi.action',//快捷支付获取短信地址
        'quickpay_request_url'=>'https://www.joinpay.com/trade/fastpayPayApi.action',//快捷支付地址
        'agencypay_notify_url' => 'http://developing.api.ydh.china.tupulian.com/loan/pay/agency_pay_callback',//代付回调地址
        'quickpay_notify_url' => 'http://developing.api.ydh.china.tupulian.com/loan/pay/quick_pay_callback',//快捷支付回调地址
        'quickpay_sms_notify_url' => 'http://developing.api.ydh.china.tupulian.com/loan/pay/quick_pay_sms_callback',//快捷支付回调地址
    ],
    'APP_SITE' => 'http://developing.api.ydh.china.tupulian.com',
    'api_md5_key' => 'tupulian2018@andy',
    'tupulian2018Android' => 'ed63a7fn138ba15eaf2h388e857fn15c',
    'tupulian2018IOS'   => 'dff44287ae5573eea198574126a73ed6',
    //是否开启mongodb和redis
    'IS_OPEN_MONGO' => true,
    'IS_OPEN_REDIS'   => true,


];