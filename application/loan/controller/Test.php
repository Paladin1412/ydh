<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 14:19
 */

namespace app\loan\controller;
use Sms\TianYiHong;
use Sms\MiaoDi;
use SenseTime\SenseTime;
use app\loan\model\AgencyPayModel;
use app\loan\model\QuickPayModel;
use Baiqishi\Baiqishi;
use Baiqishi\Operator;
use think\Db;
use Redis\redisServer;
use think\validate\ValidateRule;
use XiangyiRisk\NewRisk;
use BaiRong\VerifyBlacklist;
use XinYan\NewLook;
use XinYan\EncryptUtil;
use TupulianRisk\Risk;

class Test
{
    //异步
    public function yibu(){
        $host = get_request_url();
        $path = '/loan/Order/asynchronous_execute_risk';
        $url = $host.$path;
        $param = [
            'env' => 'dev',
            'is_open_mongo' => '',
            'order_no' => '35034614112176900',
            'xinyan_token' => '1811291042374623456104',
            'scene' => 'gdfdfg'
        ];
        yibu_request($url, $param);
        echo 33;
    }

    //新颜行为雷达
    public function baiqishi(){
        $order_no = '33025142649150754';
        $xinyan_token = '1811291042374623456104';
        $obj = new Risk('dev', '');
        return $obj->verifyData($order_no, $xinyan_token);
    }

    //新颜行为雷达
    public function xwld(){
        $user_id = 1;
        $user_info      = Db::name('users')
            ->field('name,phone,idcode')
            ->where('user_id', $user_id)
            ->find();
        $idcode = '522627199205170415';
        $phone = 18529113912;
        $name = '陆曙';
        $order_no = '33095209216820640';
        $obj = new NewLook('dev');
        $xwldRes = $obj->get_behavioral_radar_info($idcode,$phone,$name,$order_no,'');
        var_dump($xwldRes);
        $xwldRes = $obj->get_negative_pull_black_info($idcode,$phone,$name,$order_no,'');
        var_dump($xwldRes);
        $xwldRes = $obj->get_negative_wash_info($idcode,$phone,$name,$order_no,'');
        halt($xwldRes);
    }

    //新颜黑镜
    public function heijing(){
        $user_id = 1;
        $user_info      = Db::name('users')
            ->field('name,phone,idcode')
            ->where('user_id', $user_id)
            ->find();
        $order_no = '44020314055424804';
        $xinyan_token = '1811291042374623456104';
        $obj = new NewLook('dev');
        $res = $obj->get_black_mirror($xinyan_token,$user_info['idcode'],$user_info['phone'],$user_info['name'],$order_no,'');
    }
    //百融黑名单
    public function bairong(){
        //获取tokenid
        /*$baiRong_tokenid = redisServer::getInstance()->get('bairong_tokenid');
        $obj = new VerifyBlacklist('dev');
        if(empty($baiRong_tokenid)){
            $resInfo = $obj->get_tokenid();
            if($resInfo['code'] == 00){
                redisServer::getInstance()->set('bairong_tokenid', $resInfo['tokenid'], 50 * 60);
                $baiRong_tokenid = $resInfo['tokenid'];
            }else{
                trace('获取tokenid失败'.json_encode($resInfo));
                return json(['status' => '500', 'message' => '百融tokenid获取失败', 'data' => []]);
            }
        }
        //根据tokenid对百融黑名单进行验证
        $id_card = '140502198811102244';
        $phone = 18529113912;
        $user_name = '王亮';
        $resInfo = $obj->get_check_bairong($baiRong_tokenid,$id_card,$phone,$user_name);
        halt($resInfo);*/
        $user_id = 1;
        $user_info      = Db::name('users')
            ->field('name,phone,idcode')
            ->where('user_id', $user_id)
            ->find();
        $order_no = '44020314055424804';
        //获取tokenid
        $baiRong_tokenid = redisServer::getInstance()->get('bairong_tokenid');
        $obj = new VerifyBlacklist('dev');
        if(empty($baiRong_tokenid)){
            $resInfo = $obj->get_tokenid($order_no);
            if(empty($resInfo['status'])) return json(['status' => '200', 'message' => '您的贷款申请已经提交，审核通过后将为你放款。']);
            $baiRong_tokenid = $resInfo['tokenid'];
        }

        //根据tokenid对百融黑名单进行验证
        $checkRes = $obj->get_check_bairong($baiRong_tokenid,$user_info['idcode'],$user_info['phone'],$user_info['name'],$order_no,'');
        if(empty($checkRes)) return json(['status' => '200', 'message' => '您的贷款申请已经提交，审核通过后将为你放款。']);

        halt(345);

    }
    //未来无线短信
    public function weilaiwx(){

        $res = import('wlwx.autoload', EXTEND_PATH, '.php');
        //halt($res);
        $smsOperator = new \SmsOperator();
        //开发者亦可在构造函数中填入配置项
        //$smsOperator = new \SmsOperator($cust_code, $cust_pwd, $sp_code, $need_report, $uid);

        // 发送普通短信
        $data1['destMobiles'] = '18529113912';
        $data1['content'] = '【未来无线】您的验证码为：170314。如非本人操作，请忽略。';
        $result = $smsOperator->send_comSms($data1);
        print_r($result);

    }
    //翔一风控
    public function xiangyi(){
        $obj = new NewRisk('dev');
        $res = $obj->verifyData('55013735022070335');
        return json($res);
    }
	public function redis(){
        $host = "r-wz9b0a718ef9cb44.redis.rds.aliyuncs.com";
        $port = 6379;
        /* 这里替换为实例id和实例password */
        $user = "test_username";
        $pwd = "yidaihuan@2018";
        $redis = new \Redis;
        if ($redis->connect($host, $port) == false) {
            die($redis->getLastError());
        }
        if ($redis->auth($pwd) == false) {
            die($redis->getLastError());
        }
        /* 认证后就可以进行数据库操作，详情文档参考https://github.com/phpredis/phpredis */
        if ($redis->set("qwer1234567", "123456788") == false) {
            die($redis->getLastError());
        }
        $value = $redis->get("qwer1234567");
        echo $value;
        exit;
		$user_id = 2086;
		$user_info = [
			'name'=>'andy',
			'age'=>26
		];
        //\think\Cache::set('name','zgs',3600);
        $user_id = 2144;
        /*$userInfo = Db::name('users')->where('user_id',$user_id)->find();
        \think\Cache::set('user_info:user_' . $user_id, json_encode($userInfo), 5);
        sleep(3);
        $user_info_json = \think\Cache::get('user_info:user_' . $user_id);
        $user_info      = json_decode($user_info_json, true);
        halt($user_info);*/
        $user_id = 2144;
        $userInfo = Db::name('users')->where('user_id',$user_id)->find();
		$result = redisServer::getInstance()->set('user_info:user_' . $user_id, json_encode($user_info), 60 * 60 * 24 * 18);
		$user_exists = redisServer::getInstance()->exists('user_info:user_' . $user_id);
		$user_info = redisServer::getInstance()->get('user_info:user_' . $user_id);
		dump($user_info);
		exit;
	}

	//删除注册redis缓存
	public function clear_reg_redis(){
		$phone = request()->param('phone');
		$result = redisServer::getInstance()->delete('user_reg:user_' . $phone);
		dump($result);
	}

	public function del_order(){
		$phone = request()->param('phone');
		Db::name('order_info')->where(['phone'=>$phone])->delete();
		exit('订单删除成功');
	}

	//天一弘短信测试
	public function index(){
		// $result = $this->send_to_sms('6281315015170','halo . Nomor verifikasi anda adalah:123456');
		// exit;
		$type = request()->post('type');
		$clapi       = new TianYiHong;//081398773791
        $result      = $clapi->send_sms('6281398773791', 'halo . Nomor verifikasi anda adalah:123456',$type);
		dump($result);
		exit;
		$numbers = array('6281398773791');//公司 6285931284765,6281398773791 印尼 6281908300029,6289695104282
		$content = '尊敬的用户，你本此支付确认码是：123456';
		$clapi       = new TianYiHong;
        $result      = $clapi->send_sms($numbers, $content,1);
        $array         = json_decode($result, true); //转化json
        dump($array);
        exit;
	}

	//余额查询
	public function balance(){
		$clapi       = new TianYiHong;
        $result      = $clapi->query_balance(2);
        $array         = json_decode($result, true); //转化json
        dump($array);
        exit;
	}

	//发送结果查询
	public function report(){
		$id = 986488;
		$clapi       = new TianYiHong;
        $result      = $clapi->get_report(1,$id);//381492,381521
        $array         = json_decode($result, true); //转化json
        dump($array);
        exit;
	}
	
	//接收短信
	public function get(){
		$clapi       = new TianYiHong;
        $result      = $clapi->get_sms(2);
        $array         = json_decode($result, true); //转化json
        dump($array);
        exit;
	}


	#####################################################秒嘀短信#########################################################

	public function sent_code(){
		$miaodi = new MiaoDi(1,'5aab2f49c3ec9');
		$miaodi->send('18818241811',258369);
	}

	public function sent_content(){
		$miaodi = new MiaoDi(2);
		$result = $miaodi->send('18818241811','【大众易贷】您的贷款申请已经提交,审核通过后将为你放款.');
		dump($result);
		exit;
	}

	#####################################################商汤验证#########################################################

	public function compare(){
		$st = new SenseTime('dev');
		$livenessPath = 'C:/Users/Administrator/Desktop/images/20180925151558.jpg';//活体数据路径
	    $photoPath = 'C:/Users/Administrator/Desktop/images/20180925151553.jpg';//照片路径
		$st->compare($livenessPath,$photoPath);
	}


	public function idcard_ocr(){
		$st = new SenseTime('dev');
		$imagePath = 'C:\Users\Administrator\Desktop\images\IMG_20181019_100819.jpg';//正面图
		//$imagePath = 'C:\Users\Administrator\Desktop\images\IMG_20181019_100827.jpg';//反面图
		$side = 'auto';//front表示正面，back表示背面,auto表示自动
		$result = $st->idcard_ocr($imagePath,$side);
		return ($result);
	}

	#####################################################合利宝代付#########################################################

	public function pay(){
		$pay = new AgencyPayModel('dev');
		$order_info = [
			'order_no' => '20180925151569',//make_sn()
			'amount' => 1.00,
			'bankCode' => 'ICBC',
			'bankAccountNo' => '6222034000015132024',
			'bankAccountName' => '邓玉山',
			'company_code' => '5aab2f49c3ec9',
		];
		$response = $pay->pay($order_info);
		dump($response);
		exit;
	}

	public function query(){
		$pay = new AgencyPayModel('dev');
		$orderId = '45103259379567021';
		$response = $pay->query($orderId);
		dump($response);
		exit;
	}

	#####################################################合利宝快键支付#########################################################

	//首次支付
	//中国 6217582000010377646
	//招商 6214836558912891
	//交通 6222621310014593004
	//工商 6222034000015132024
	public function quickPayCreateOrder(){
		$order_info = [
			'user_id' => '123456',
			'order_no' => make_sn(),//
			'name' => '邓玉山',
			'idCardNo' => '360782199104126616',
			'cardNo' => '6222621310014593004',//
			'phone' => '18818241811',
			'application_amount' =>'1.01',
			'company_code' => 'fewfwefffqff',
		];
		$pay = new QuickPayModel('dev');
		$result = $pay->quickPayCreateOrder($order_info);
		dump($result);
		exit;
	}

	//首次支付短信
	public function quickPaySendValidateCode(){
		$order_info = [
	        'order_no'=>'48030525618350407',
	        'phone'=>'18818241811',
	        'company_code'=>'fewfwefffqff',
	    ];
	    $pay = new QuickPayModel('dev');
		$result = $pay->quickPaySendValidateCode($order_info);
		dump($result);
		exit;
	}

	//确认支付
	public function quickPayConfirmPay(){
		$order_info = [
	        'order_no'=>'48030525618350407',
	        'code'=>'585553',
	        'orderIp'=>'127.0.0.1',
	        'company_code'=>'fewfwefffqff',
	    ];
	    $pay = new QuickPayModel('dev');
		$result = $pay->quickPayConfirmPay($order_info);
		dump($result);
		exit;
	}

	//订单查询
	public function quickPayQuery(){
		$order_no = '44053604767998912';
	    $pay = new QuickPayModel('dev');
		$result = $pay->quickPayQuery($order_no);
		dump($result);
		exit;
	}

	//鉴权绑卡短信
	//中国 6217582000010377646
	//招商 6214836558912891
	//交通 6222621310014593004
	public function agreementPayBindCardValidateCode(){
		$bank_info = [
	        'user_id'=>'456789',
	        'order_no'=>'53112955388675874',
	        'card_no'=>'6222034000015132024',
	        'phone'=>'18818241811',
	        'idcard'=>'360782199104126616',
	        'name'=>'邓玉山',
	    ];
	    $pay = new QuickPayModel('dev');
		$result = $pay->agreementPayBindCardValidateCode($bank_info);
		dump($result);
		exit;
	}

	//鉴权绑卡
	public function quickPayBindCard(){
		$bank_info = [
	        'user_id'=>'456789',
	        'order_no'=>'53112955388675874',
	        'name'=>'邓玉山',
	        'idcard'=>'360782199104126616',
	        'card_no'=>'6222034000015132024',
	        'phone'=>'18818241811',
	        'code'=>'066337',
	    ];
	    $pay = new QuickPayModel('dev');
		$result = $pay->quickPayBindCard($bank_info);
		dump($result);
		exit;
	}








	//绑卡支付短信
	public function quickPayBindPayValidateCode(){
		$sign_params = [
	        'P1_bizType'=>'QuickPayBindPayValidateCode',
	        'P2_customerNumber'=>'C1800000002',
	        'P3_bindId'=>'5fa1c48c7436',//合利宝生成的
	        'P4_userId'=>'123',
	        'P5_orderId'=>'20170410155310',
	        'P6_timestamp'=>'20170410155310',
	        'P7_currency'=>'CNY',
	        'P8_orderAmount'=>'1.01',
	        'P9_phone'=>'18818241811',
	    ];
	    $pay = new QuickPayModel('dev');
		$pay->quickPayBindPayValidateCode($sign_params);
	}

	//绑卡支付
	public function quickPayBindPay(){
		$sign_params = [
	        'P1_bizType'=>'QuickPayBindPay',
	        'P2_customerNumber'=>'C1800000002',
	        'P3_bindId'=>'5fa1c48c7436',//合利宝生成的
	        'P4_userId'=>'123',
	        'P5_orderId'=>'20170410155310',
	        'P6_timestamp'=>'20170410155310',
	        'P7_currency'=>'CNY',
	        'P8_orderAmount'=>'1.01',
	        'P9_goodsName'=>'苹果',
	        'P10_goodsDesc'=>'',
	        'P11_terminalType'=>'IMEI',
	        'P12_terminalId'=>'122121212121',
	        'P13_orderIp'=>'127.0.0.1',
	        'P14_period'=>'',
	        'P15_periodUnit'=>'',
	        'P16_serverCallbackUrl'=>'http://www.helipay/server.html',
	        'P17_validateCode'=>'',
	    ];
	    $pay = new QuickPayModel('dev');
		$pay->quickPayBindPay($sign_params);
	}

	//银行卡解绑
	public function bankCardUnbind(){
		$sign_params = [
	        'P1_bizType'=>'BankCardUnbind',
	        'P2_customerNumber'=>'C1800000002',
	        'P3_userId'=>'5fa1c48c7436',//合利宝生成的
	        'P4_bindId'=>'123',
	        'P5_orderId'=>'20170410155310',
	        'P6_timestamp'=>'20170410155310',
	    ];
	    $pay = new QuickPayModel('dev');
		$pay->bankCardUnbind($sign_params);
	}

	//用户绑定银行卡信息查询（仅限于交易卡）
	public function bankCardbindList(){
		$sign_params = [
	        'P1_bizType'=>'BankCardbindList',
	        'P2_customerNumber'=>'C1800000002',
	        'P3_userId'=>'5fa1c48c7436',//合利宝生成的
	        'P4_bindId'=>'123',
	        'P5_timestamp'=>'20170410155310',
	    ];
	    $pay = new QuickPayModel('dev');
		$pay->bankCardbindList($sign_params);
	}


	#####################################################白骑士--风控策略#########################################################

	public function decision(){
		$bqs = new Baiqishi('dev');
		$mobile = request()->param('mobile');
		$certNo = request()->param('certNo');
		$post_parms = [
			//'eventType' => 'loan',//事件类型 //loan 代款
			// 'account' => 'andy.deng',//用户帐号
			//'name' => '张三',
			//'email' => '957651598@qq.com',
			'mobile' => $mobile,
			'certNo' => $certNo,//身份证号
			//'address' => '江西省南康市隆木乡',//用户所在地址
			//'addressCity' => 'dffd深圳市',//用户所在城市
			// 'contactsName' => '王五',//用户第一联系人姓名
			// 'contactsMobile' => '18818241813',//用户第一联系人手机号
			// 'contactsNameSec' => '李四',//用户第二联系人姓名
			// 'contactsMobileSec' => '18818241814',//用户第二联系人手机号
			// 'education' => '大专',//用户学历（文盲或半文盲/初中/高中/中专或技校/大专/大学本科/研究生/博士） 
			// 'marriage' => '已婚',//是否已婚（未婚/已婚/离异/丧偶）
			// 'bankCardNo' => '345955454654654654544445',//银行卡卡号 
			// 'bankCardName' => '张三',//银行卡持卡人姓名 
			// 'bankCardMobile' => '18818241819',//银行卡预留手机号
		];
		$result = $bqs->decision($post_parms);
		echo '<pre>';
		print_r($result);
		exit;
	}

	public function upload_result(){
		$bqs = new Baiqishi('dev');
		$bqs->upload_result();
	}

	#####################################################白骑士--运营商#########################################################

	public function login(){
		$bqs = new Operator('dev');
		$post_parms = [
			//'reqId' => '',//第一次请求 login 接口不需要填写
			'name' => '邓玉山',//姓名
			'certNo' => '360782199104126616',//身份证号
			'mobile' => '18166816137',//手机号
			'pwd' => '123456',//服务密码
			//'smsCode' => 'success',//第一次请求 login 接口不需要填写该参数
		];
		$result = $bqs->login($post_parms);
		dump($result);
		exit;
	}


	public function login2(){
		$bqs = new Operator('dev');
		$post_parms = [
			'reqId' => 'a05c94e3520243d4a95924cd3300e174',//第二次请求 login 接口需填写 reqId 该参数；reqId 来源于上一次请求结果中的 data 字段
			'name' => '邓玉山',//姓名
			'certNo' => '360782199104126616',//身份证号
			'mobile' => '18166816137',//手机号
			'pwd' => '123456',//服务密码
			'smsCode' => '111222',//第二次请求 login 接口需填写该参数；smsCode 来源于用户收到的短信验证码
		];
		$result = $bqs->login2($post_parms);
		dump($result);
		exit;
	}

	public function verifyauthsms(){
		$bqs = new Operator('dev');
		$post_parms = [
			'reqId' => 'a05c94e3520243d4a95924cd3300e174',//第二次请求 login 接口需填写 reqId 该参数；reqId 来源于上一次请求结果中的 data 字段
			'smsCode' => '123456',
		];
		$result = $bqs->verifyauthsms($post_parms);
		dump($result);
		exit;
	}

	public function sendloginsms(){
		$bqs = new Operator('dev');
		$post_parms = [
			'reqId' => 'a05c94e3520243d4a95924cd3300e174',//第二次请求 login 接口需填写 reqId 该参数；reqId 来源于上一次请求结果中的 data 字段
		];
		$result = $bqs->sendloginsms($post_parms);
		dump($result);
		exit;
	}

	public function sendauthsms(){
		$bqs = new Operator('dev');
		$post_parms = [
			'reqId' => 'a05c94e3520243d4a95924cd3300e174',//第二次请求 login 接口需填写 reqId 该参数；reqId 来源于上一次请求结果中的 data 字段
		];
		$result = $bqs->sendauthsms($post_parms);
		dump($result);
		exit;
	}


	//查看用户原始数据
	public function getoriginal(){
		$bqs = new Operator('online');
		$post_parms = [
			'name' => '邓玉山',//姓名
			'certNo' => '360782199104126616',//身份证号
			'mobile' => '15007096879',//手机号
		];
		$result = $bqs->getoriginal($post_parms);
		echo '<pre>';
		print_r($result);
		exit;

	}

	public function h5_no_userinfo(){
		$bqs = new Operator('dev');
		$post_parms = [
			'name' => '邓玉山',//姓名
			'idcode' => '360782199104126616',//身份证号
			'phone' => '15007096879',//手机号
		];
		$result = $bqs->h5_no_userinfo($post_parms);
		dump($result);
		exit;
	}

	#####################################################测试汇聚支付#########################################################
	

	//代付
	public function agencyPay(){
		$order_info = [
			'order_no' => make_sn(),
			'amount' => '0.01',
			'name' => '邓玉山',
			'bankcard_no' => '6222034000015132024',
			'company_code' => 'abgdfdfdsfdf',
		];
		$pay = new AgencyPayModel('dev',true);
		$result = $pay->pay($order_info);
		dump($result);
		exit;
	}

	//发送快捷支付短信
	public function quickPaySentSms(){
		$order_info = [
			'order_no' => '38013639606649514',//make_sn()
			'amount' => '1.00',
			'name' => '陈德楷',
			'idcard_no' => '445381199107134036',
			'bankcard_no' => '6222034000018013932',
			'mobile' => '13417977171',
			'user_id' => 123,
			'company_code' => 'abgdfdfdsfdf',
		];
		$pay = new QuickPayModel('dev');
		$result = $pay->quickPaySentSms($order_info);
		dump($result);
		exit;
	}

	//确认支付
	public function quickPayConfirmPay2(){
		$order_info = [
			'order_no' => '38013639606649514',
			'code' => '123456',
			'amount' => '1.00',
			'name' => '陈德楷',
			'idcard_no' => '445381199107134036',
			'bankcard_no' => '6222034000018013932',
			'mobile' => '13417977171',
			'user_id' => 123,
			'company_code' => 'abgdfdfdsfdf',
		];
		$pay = new QuickPayModel('dev');
		$result = $pay->quickPayConfirmPay($order_info);
		dump($result);
		exit;
	}

}