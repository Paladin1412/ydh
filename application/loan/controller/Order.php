<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 14:00
 */

namespace app\loan\controller;

use think\Db;
use TupulianRisk\Risk;
use SenseTime\SenseTime;
use BaiRong\VerifyBlacklist;
use Redis\redisServer;
use XinYan\NewLook;

class Order extends Common
{
    // 订单类接口
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 验证资料是否完整
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check_data_intact()
    {
        $act     = request()->post('act', 'all', 'trim');
        $user_id = request()->post('user_id', '0', 'intval');
        $company_code = request()->header('COMPANYCODE');
        //用户补充信息是否有记录
        //$aut_id = Db::name('user_aut_info')->where('user_id', $user_id)->value('aut_id');
        //if (empty($aut_id)) {
        //    Db::name('user_aut_info')->insert(array('user_id' => $user_id, 'add_time' => time()));
        //}
        //检查资料完整性
        $result = $this->check_data($user_id, $act,$company_code);
        unset($result['bankcard_data']);
        unset($result['bankcard_id']);
        if ($result) {
            return json(['status' => '200', 'message' => lang('success'), 'data' => $result]);
        }
        return json(['status' => '500', 'message' => lang('request_cs_f'), 'data' => []]);//请求的参数有误
    }

    /**
     * 检查用户资料完整性
     * @param $user_id
     * @param string $act
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function check_data($user_id, $act = 'all',$company_code)
    {

        $user_info = Db::name('users')
            ->field('name ,phone, idcode ,city ,address,company ,company_add ,company_tel,photo_assay,pair_verify_result,account_state,face_card ,back_card,credit_img,birthday,email,zipcode,education,is_bank')
            ->where('user_id', $user_id)
            ->find();
        $loan_type = Db::name('loan_type')
            ->where(array("status" => 1,'company_code'=>$company_code))
            ->field('apply_amount,apply_term')
            ->find();

        $result = array(
            'basic_data'    => '0',  //基本资料(包括基本信息、职业信息、联系人信息)
            'living_data'   => '0',  //活体资料(包括身份证ocr信息、和依图接口比对结果)
            'credit_data'   => '0',  // 签名授权
            'account_state' => '0',  //账号绑定
            'bankcard_data' => '0',  //是否已绑定银行卡
            'zhima_data'    => '0',
            'mobile_data'   => '0',
            'bankcard_msg'  => '0',
            'is_bank'       => '0',
            'apply_amount'  => $loan_type["apply_amount"],   //借款金额
            'apply_term'    => $loan_type["apply_term"],   //借款天数
        );

        /**************************************** 验证基本资料是否完整_Start ***********************************/
        $basic_result = array(
            'basic_info'   => '0',
            'job_info'     => '0',
            'contact_info' => '0',
        );
        //基本信息：真实姓名、身份证号、现居城市、详细地址、生日、邮箱、邮编、学历
        if (!empty($user_info['name']) && !empty($user_info['idcode']) && !empty($user_info['city']) && !empty($user_info['address']) && !empty($user_info['birthday']) && !empty($user_info['email']) && !empty($user_info['zipcode'])) {
            $basic_result['basic_info'] = '1';
        }

        //职业信息：单位名称、单位地址、单位电话
        if (!empty($user_info['company']) && !empty($user_info['company_add']) && !empty($user_info['company_tel'])) {
            $basic_result['job_info'] = '1';
        }

        //联系人信息(2个)：关系、姓名、电话
        $contact_count = Db::name('user_contact')->where('user_id', $user_id)->count();    //联系人数量
        if ($contact_count >= 2) {
            $basic_result['contact_info'] = '1';
        }

        if ($act == 'basic') {
            return $basic_result;
        }

        //所有基本信息通过，则基本资料项为通过
        if ($basic_result['basic_info'] == 1 && $basic_result['job_info'] == 1 && $basic_result['contact_info'] == 1) {
            $result['basic_data'] = '1';
        }
        /**************************************** 验证基本资料是否完整_End ***********************************/

        /**************************************** 验证活体识别 ***********************************/
        if (!empty($user_info['face_card']) && !empty($user_info['photo_assay']) && $user_info['pair_verify_result'] == 1) {
            $result['living_data'] = '1';
        }

        /**************************************** 互联网账户是否授权 ***********************************/

        if (!empty($user_info['account_state'])) {
            $result['account_state'] = '1';
        }

        /**************************************** 签名授权 ***********************************/
        if (!empty($user_info['credit_img'])) {
            $result['credit_data'] = '1';
        }

        /****************************** 4个验证通过后，查询是否有绑定银行卡 *********************************/
        if ($result['basic_data'] == '1' && $result['living_data'] == '1') {
            $bankcard_id = Db::name('bankcard')
                ->where('user_id', $user_id)
                ->order('bankcard_id desc')
                ->limit(1)
                ->value('bankcard_id');
            if (!empty($bankcard_id) && $user_info['is_bank']) {
                $result['is_bank']       = '1';
                $result['bankcard_data'] = '1';
                $result['bankcard_id']   = $bankcard_id;
            }
        }

        if ($act == 'all') {
            $result['user_info'] = array(
                'name'   => $user_info['name'],
                'phone'  => $user_info['phone'],
                'idcode' => $user_info['idcode'],
            );
            return $result;
        }
    }


    //活体识别
    public function assay_ocr(){
        $this->check_login();
        $user_id  = request()->post('user_id', 0, 'intval');
        $order_count = Db::name('order_info')
           ->where(array('user_id' => $user_id, 'order_status' => array('neq', 200)))
           ->count();
        if ($order_count > 0) {
           return json(['status' => '500', 'message' => '您已存在进行中的账单！', 'data' => []]);
        }
        //查询用户已做活体次数
        $assay_num = Db::name('sensetime_log')->where('user_id',$user_id)->count();
        if($assay_num>=3){
            return json(['status' => '500', 'message' => '活体验证次数达到上限！', 'data' => []]);
        }
        //获取用户身份证图
        $face_card  = Db::name('users')->where('user_id', $user_id)->value('face_card');
        $oss_config = config('auth_' . $this->env . '.OSS');
        $face_card  = get_oss_image($oss_config, $face_card);
        $image_content = file_get_contents($face_card);
        $local_idcard_temp_image = ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'user'. DS . $user_id . '_photo_idcard.jpg';
        //保存到本地
        file_put_contents($local_idcard_temp_image, $image_content);

        // 获取活体4张对比图片
        for ($i = 0; $i < 4; $i++) {
           $file = request()->file('image_action' . ($i + 1));
           if ($file) {
               $info = $file->move(ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'user', $user_id . '_photo_assay' . ($i + 1) . '.jpg');
               if ($info) {
                   $image_url = ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'user' . DS . $info->getSaveName();
                   $imgCode   = upload_oss_image($oss_config, $image_url);
                   if ($imgCode) {
                       $code_arr[] = $imgCode;
                       $image_url_arr[] = $image_url;
                   }
               }
               unset($file);
           } else {
               return json(['status' => '500', 'message' => '请完成活体识别！', 'data' => []]);
           }
        }
        //进行商汤图片对比
        $st = new SenseTime($this->env);
        $live_image = $image_url_arr[0];
        $result = $st->compare($live_image,$local_idcard_temp_image);
        $result = json_decode($result,true);

        //删除本地临时图片
        @unlink($local_idcard_temp_image);
        foreach($image_url_arr as $key=>$val){
            @unlink($val);
        }

        //修改开始 赵光帅
        if($this->is_open_mongo == true){
            //记录mongodb日志
            $mongo_data = array(
                'user_id'      => $user_id,
                'live_code'    => $code_arr[0],
                'return_data'  => json_encode($result),
                'add_date'     => date('Y-m-d H:i:s'),
                'add_time'     => time(),
            );
            mongo_log('sensetime_log', $mongo_data);
        }
        //修改结束


        //记录mysql日志
        $user_face_log_data = array(
            'user_id'     => $user_id,
            'live_code'    => $code_arr[0],
            'return_data' => json_encode($result),
            'add_date'     => date('Y-m-d H:i:s'),
            'add_time'    => time(),
        );
        Db::name('sensetime_log')->insert($user_face_log_data);

        if($result['code']===1000){
            $verification_score = $result['verification_score']*100;
            $verification_score = round($verification_score, 2);

            if ($verification_score > 60) {
                $pair_verify_result = 1;//1认为是同一个人
            } else {
                $pair_verify_result = 0;//0认为不是同一个人
            }
            $user_data = array(
                //阈值  0.4 0.5 0.6 0.7 0.8 0.9
                //错误率 十分之一    百分之一    千分之一    万分之一    十万分之一   百万分之一
                'scores_assay'       => $verification_score,//取值范围
                'pair_verify_result' => $pair_verify_result,//活体同人验证结果
                'photo_assay'        => implode(',', $code_arr),// 活体图片
                'assay_time'         => time(),//活体通过时间
            );  
            //保存用户活体验证结果信息
            Db::name('users')->where('user_id', $user_id)->update($user_data);
            if($pair_verify_result){
                return json(['status' => '200', 'message' => '通过', 'data' => []]);
            }else{
                return json(['status' => '500', 'message' => '请确认是否上传本人真实有效的身份证照片!', 'data' => []]);
            } 
        }else{
            return json(['status' => '500', 'message' => '请确认是否上传本人真实有效的身份证照片!', 'data' => []]);
        }
    }


    /**
     * 保存用户通讯录
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function phone_list()
    {
        $user_id    = request()->post('user_id', 0, 'intval');           //用户ID
        $phone_list = request()->post('phone_list', '', 'trim');
        $phone_list = json_decode($phone_list, true);
        if (!empty($phone_list)) {
            Db::name('phone_list')->where('user_id', $user_id)->delete();  //删除旧的用户通讯录
            //用户通讯录入库
            foreach ($phone_list as $k => $v) {
                $phone = explode('_', $v['phone']);
                foreach ($phone as $key => $val) {
                    $data[] = array(
                        'user_id' => $user_id,
                        'name'    => filter_emoji($v['name']),
                        'phone'   => trim(preg_replace('/\s+/', '', $val))//trim($val),
                    );
                }
            }
            if (!empty($data)) {
                $data = array_unique($data, SORT_REGULAR);
                Db::name('phone_list')->insertAll($data);
            }
        }
        return json(['status' => '200', 'message' => lang('success'), 'data' => []]);
    }

    /**
     * v2.0 下单
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function make_order()
    {
        $this->check_login();
        $user_id      = request()->post('user_id');            //用户ID *
        $apply_amount = request()->post('apply_amount', 1000, '');      //申请的借款额度 *
        $apply_term   = request()->post('apply_term', 14, 'intval');       //申请的期限范围 *
        $gps_location = request()->post('gps_location', '', 'trim');        //gps经纬度 *
        $gps_address  = request()->post('gps_address', '', 'trim');         //gps地址 *
        //$minorNum     = request()->post('minorNum', '', 'trim');            //设备信息 *
        $app_nums     = request()->post('app_nums', '', 'intval');          //应用数量 *
        $app          = request()->post('app');//应用数量 *
        $source       = request()->post('source');
        $xinyan_token = request()->post('xy_token', '', 'trim'); //新颜黑镜token
        $app_data     = json_decode(stripslashes($app), true);  //去掉斜杠并转成数组格式
        $company_code = request()->header('COMPANYCODE');
        $minorNum = request()->header('IMEI');
        //$company_code = !empty($company_code) ? $company_code : '5aab2f49c3ec9';
        //$xinyan_token = '1811291042374623456104';
        if(empty($xinyan_token)) return json(['status' => '500', 'message' => '新颜黑镜token不能为空！', 'data' => []]);
        $loan_type    = Db::name('loan_type')->where(array("status" => 1, 'company_code' => $company_code))->find();
        if (!$loan_type['open']) {
            $message = '今天的贷款限额已达到极限。 请明天再试一遍，谢谢你的支持。';
            return json(['status' => '500', 'message' => $message, 'data' => []]);
        }
        if (empty($apply_amount))
            return json(['status' => '500', 'message' => '借款金额不能为空', 'data' => []]);
        if (empty($apply_term))
            return json(['status' => '500', 'message' => '借款期限不能为空', 'data' => []]);
        if (empty($company_code))
            return json(['status' => '500', 'message' => '公司编号不能为空', 'data' => []]);
        $user_info      = Db::name('users')
            ->field('id_number_ocr,name_ocr,credit_img,pair_verify_result,name,phone,idcode,face_card,back_card,photo_assay,sex,birthday,education,is_marrey,city,address,live_start_time,company,company_add,company_tel,profession,industry,account_state,code')
            ->where('user_id', $user_id)
            ->find();
        $user_bank_data = Db::name('bankcard')->where(array('user_id' => $user_id))->find();
        // 兼容老用户处理
        $is_order = Db::name('order_info')->where(array('user_id' => $user_id, 'order_status' => 200))->count();
        if ($is_order > 0) {
            if ($user_info['name'] == NULL || $user_info['idcode'] == NULL || $user_info['face_card'] == '' || $user_info['birthday'] == NULL || $user_info['education'] == 0 || $user_info['is_marrey'] === NULL || $user_info['city'] == NULL || $user_info['address'] == '') {
                return json(['status' => '500', 'message' => '请完善基本资料！', 'data' => []]);
            }
        } else {
            if ($user_info['name'] == NULL || $user_info['idcode'] == NULL || $user_info['face_card'] == '' || $user_info['back_card'] == '' || $user_info['sex'] == 0 || $user_info['birthday'] == NULL || $user_info['education'] == 0 || $user_info['is_marrey'] === NULL || $user_info['city'] == NULL || $user_info['address'] == '' || $user_info['live_start_time'] == NULL) {
                return json(['status' => '500', 'message' => '请完善基本资料！', 'data' => []]);
            }
        }
        if ($user_info['photo_assay'] == '' || (int)$user_info['pair_verify_result'] !== 1) {
            return json(['status' => '500', 'message' => '请完成活体识别！', 'data' => []]);
        }
        if ($user_info['credit_img'] == '') {
            return json(['status' => '500', 'message' => '请完成征信授权！', 'data' => []]);
        }
        if (count($user_bank_data) < 1) {
            return json(['status' => '500', 'message' => '请先绑定银行卡！', 'data' => []]);
        }
        $order_count = Db::name('order_info')
            ->where(array('user_id' => $user_id, 'order_status' => array('in', array(1, 80, 90, 100, 160, 170, 180))))
            ->count();
        if ($order_count) {
            return json(['status' => '500', 'message' => '您已存在进行中的账单！', 'data' => []]);
        }
        /*用户是否存在审批拒绝过*/
        $day          = 30;  //  30天内审批拒绝过，不能申请
        $refuse_time  = time() - $day * 24 * 3600;
        $refuse_count = Db::name('order_info')
            ->where(array('user_id' => $user_id, 'order_status' => 110, 'refuse_time' => array('GT', $refuse_time)))
            ->count();
        if ($refuse_count) {
            return json(['status' => '500', 'message' => '您最近被审批拒绝过，请往后再来！', 'data' => []]);
        }
        /*用户是否存在放款失败*/
        $fail_refuse_time  = strtotime("-1 day");
        $fail_refuse_count = Db::name('order_info')
            ->where(array('user_id' => $user_id, 'order_status' => 169, 'add_time' => array('GT', $fail_refuse_time)))
            ->count();
        if ($fail_refuse_count) {
            return json(['status' => '500', 'message' => '您最近存在放款失败，请往后再来！', 'data' => []]);
        }
        $user_data = array(
            'gps_location' => empty($gps_location) ? '' : $gps_location,
            'gps_address'  => empty($gps_address) ? '' : $gps_address,
        );
        Db::startTrans();
        if (!empty($user_data)) {
            Db::name('users')->where('user_id', $user_id)->update($user_data);
        }
        //生成订单号
        $order_no = make_sn();

        //计算应还款金额
        $loan_amount     = $loan_type['apply_amount'];//借款金额
        $loan_term       = $loan_type['apply_term'];
        //计算应到帐金额
        $approval_amount = $loan_amount - $loan_amount * ($loan_type['service_fee'] + $loan_type['approval_fee'] + $loan_type['manage_fee']);
        //计算到期应还款金额
        $term_fee = empty($loan_type['term_fee']) ? $loan_amount * $loan_term * $loan_type['rate'] : $loan_type['term_fee'];//利息 = 借款金额 * 借款期限 * 日费率2998.2
        $amount          = $loan_amount + round($term_fee);

        //构建贷款订单
        $order         = array(
            'order_no'           => $order_no,//订单号
            'user_id'            => $user_id,//用户id
            'name'               => $user_info['name'],//姓名
            'phone'              => $user_info['phone'],//用户手机
            'order_status'       => 1,//订单状态
            'application_amount' => $loan_type["apply_amount"],//申请金额
            'loan_amount'        => $loan_type["apply_amount"],//审批金额
            'approval_amount'    => $approval_amount,
            'application_term'   => $loan_type["apply_term"],//申请期限
            'loan_term'          => $loan_type["apply_term"],//审批期限
            'bankcard_id'        => $user_bank_data['bankcard_id'],//使用的银行卡id
            'type'               => $loan_type["type_id"],//贷款类型
            //'repay_time'         => $repay_time,//应还款时间  还款回调成功时加入
            'repay_amount'         => $amount,//到期还款金额
            'add_time'           => time(),                     //订单创建时间
            'gps_location'       => $gps_location,              //GPS经纬度
            'gps_address'        => $gps_address,               //GPS地址
            'minorNum'           => $minorNum,                  //设备信息
            'app_nums'           => $app_nums,                 //app数量
            'over_fee'           => $loan_type["over_fee"],    //逾期费率
            'remark'             => '',
            'company_code'       => $company_code,
            'risk_status'        => 0, // 风控 未进行0 ，通过1，未通过2
        );

        $handle_status = Db::table('system_config')->where(array('name' => 'handle_status'))->value('value');
        if ($handle_status == 1) {
            $order['audit_method'] = 1;
            $order['handle_state'] = 0;
        } else {
            $order['audit_method'] = 2;
            $order['handle_state'] = 1;
        }
        $order_id          = Db::name('order_info')->insertGetId($order);

        //添加渠道订单统计
        if(!empty($user_info['code'])){
            $statistic_order = [
                'user_id' => $user_id,
                'order_no' => $order_no,
                'addtime' => date("Y-m-d H:i:s"),
                'code' => $user_info['code'],
            ];
            Db::table('statistical_order')->insertGetId($statistic_order);
        }

        $order['order_id'] = $order_id;
        if (!empty($app_data) && is_array($app_data)) {
            foreach ($app_data['app'] as $k => $app) {
                $param = array(
                    'user_id'  => $user_id,                 //此次App的下单的用户
                    'order_id' => $order_id,                //此次下单使用的App
                    'app_name' => $app['app_name'],         //APP名称
                    'app_bm'   => $app['app_bm'],           //APP包名
                    'add_time' => time(),                  //APP录入时间
                );
                Db::name('order_app')->insert($param);
            }
        }
        if ($order_id) {
            Db::commit();
            // 处理重复下单逻辑
            $order_ids = Db::name('order_info')
                ->where(array('user_id' => $user_id, 'order_status' => array('in', array(1, 80, 90, 100, 160, 170, 180))))
                ->column('order_id');
            if (count($order_ids) >= 2) {
                Db::name('order_info')
                    ->where('order_id', '<>', min($order_ids))
                    ->where('order_id', 'in', $order_ids)
                    ->delete();
            }

            /*$obj = new Risk($this->env, $this->is_open_mongo);
            $obj->verifyData($order_no, $xinyan_token);
            exit;*/
            //整个风控异步
            $url = get_request_url().'/loan/Order/asynchronous_execute_risk';
            $param = [
                'env' => $this->env,
                'is_open_mongo' => $this->is_open_mongo,
                'order_no' => $order_no,
                'xinyan_token' => $xinyan_token,
                'scene' => 'gdfdfg',
                'user_id' => $user_id,
            ];
            yibu_request($url, $param);

            return json(['status' => '200', 'message' => '您的贷款申请已经提交，审核通过后将为你放款。', 'data' => $order]);
        } else {
            Db::rollback();
            return json(['status' => '500', 'message' => '您的贷款申请提交失败', 'data' => $order]);
        }
    }

    //异步执行风控
    public function asynchronous_execute_risk(){
        $env = request()->param('env');
        $is_open_mongo = request()->param('is_open_mongo');
        $order_no = request()->param('order_no');
        $xinyan_token = request()->param('xinyan_token');
        $user_id = request()->param('user_id');
        trace($order_no.'异步已经走到'.json_encode(request()->param()));
        $obj = new Risk($env, $is_open_mongo);
        $obj->verifyData($order_no, $xinyan_token);
        //订单提交推送
        $this->message_send($user_id,3);
    }

    //测试风控
    public function testRisk(){
        $obj = new Risk($this->env);
        $order_no = '38045645999464413';
        $obj->verifyData($order_no,'113456');
    }


}