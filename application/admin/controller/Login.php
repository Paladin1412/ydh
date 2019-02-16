<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 14:21
 */

namespace app\admin\controller;

use think\Controller;
//use think\Verify;
use think\Db;
use think\Session;
use think\captcha\Captcha;
class Login extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $request = request();
        if (var_export($request->isAjax(), true)) {
            return json(['code' => 401, 'message' => lang('error_4001'), 'data' => []]);
        }
    }

    /**
     * 语言切换
     */
    public function set_lang()
    {
        $lang = request()->param('lang');
        switch ($lang) {
            case 'cn':
                cookie('think_var', 'zh-cn');
                break;
            case 'en':
                cookie('think_var', 'en-us');
                break;
            case 'id':
                cookie('think_var', 'id-id');
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
    }

    public function verify_open()
    {
        $is_open = Db::table('system_config')->where('name','is_verify_open')->value('value');
        //是否开启验证码 1开启 2关闭
        return json(['code' => 200, 'message' => lang('success'), 'data' => ['is_verify_open'=>$is_open]]);
    }

    /**
     * 生成验证码
     */
    public function verify_img()
    {
        $config =    [
            // 验证码字体大小
            'fontSize'    =>    30,    
            // 验证码位数
            'length'      =>    4,   
            // 关闭验证码杂点
            'useNoise'    =>    false, 
            'codeSet'     => '1234567890',
        ];
        $captcha = new Captcha($config);
        //$Verify = new Verify(array('fontSize' => 30, 'length' => 4, 'useCurve' => false, 'useNoise' => false, 'reset' => false ,'codeSet' => '1234567890'));
        return $captcha->entry("admin_login");
    }

    /**
     * 登陆
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data)) {
            return json(['code' => 402, 'message' => lang('error_4004'), 'data' => []]);
        }
        if (session('?admin_id') && session('admin_id') > 0) {
            return json(['code' => 201, 'message' => lang('is_login'), 'data' => []]);
        }
        // 验证码开启
        $is_open = Db::table('system_config')->where('name','is_verify_open')->value('value');
        if($is_open == 1){
            $captcha = new Captcha();
            if (!$captcha->check($post_data['verify'], "admin_login")) {
                return json(['code' => 402, 'message' => lang('error_4003'), 'data' => []]);
            }
        }

        $condition['user_name'] = $post_data['user_name'];
        $condition['password']  = encrypt($post_data['password']);

        $admin_info = Db::table('system_admin_v2')->where($condition)->find();
        if (!empty($admin_info)) {
            if ($admin_info['company_id'] != 0) {
                $company_status = Db::table("system_company")->where(array("cp_id" => $admin_info['company_id']))->value('status');
                if ( $company_status == 1) {
                    return json(['code' => 402, 'message' => lang('error_4013'), 'data' => []]);
                }
            }
            if ($admin_info['status'] != 0) {
                return json(['code' => 403, 'message' => lang('error_4006'), 'data' => []]);
            }
            session('admin_id', $admin_info['admin_id']);
            $ip = $request->ip();
            Db::name('system_admin_v2')->where("admin_id", $admin_info['admin_id'])->fetchSql(false)->update(array('last_login' => time(), 'last_ip' => $ip));
            session("company_id", (int)$admin_info["company_id"]);
            unset($admin_info['password']);
            session("admin_info", $admin_info);
            Db::table('system_admin_log')->insert(array('log_time' => time(), 'admin_id' => $admin_info['admin_id'], 'log_info' => '后台登录', 'company_id' => $admin_info["company_id"], 'log_ip' => $ip, 'log_url' => request()->baseUrl()));
            $role_type = Db::name('system_admin_role_v2')->where('role_id', 'in', session('admin_info.role_id'))->fetchSql(false)->column('admin_class');
            if(!empty($role_type)){
                if(in_array('6',$role_type) || in_array('5',$role_type)){
                    // 管理员 和 最高管理员 到订单统计  $url = 'analysis/businessStatistics.html';
                    //最高管理员、平台管理员、财务人员 至 首页
                    $url = 'home/homeIndex.html';
                }elseif(in_array('3',$role_type)) {
                    $url = 'verify/all-verify.html';
                }elseif (in_array('4',$role_type)){
                    $url = 'postloan/allCollection.html';
                }elseif (in_array('1',$role_type)){
                    $url = 'verify/not-verify.html';
                }elseif(in_array('2',$role_type)){
                    $url = 'postloan/inCollection.html';
                }elseif (strstr('176',$admin_info['role_id'])){
                    $url = 'company/company_list.html';//运营
                }elseif (strstr('177',$admin_info['role_id'])){
                    $url = 'analysis/summaryStatistics.html';//商务
                }elseif (strstr('193',$admin_info['role_id']) || strstr('194',$admin_info['role_id']) ){
                    $url = 'home/home/homeIndex.html';//财务 首页（财务统计报表）
                } else{
                    $url = 'index.html';
                }
            }else{
                $url = 'index.html';
            }
            return json(['code' => 200, 'message' => lang('success'), 'data' => ['url' => $url]]);
        } else {
            return json(['code' => 403, 'message' => lang('error_4005'), 'data' => []]);
        }
    }

    /**
     * 退出系统
     * @return \think\response\Json
     */
    public function logout()
    {
        Session::clear();
        return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
    }

    /**
     * 平台服务协议-借款协议
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function agreement()
    {
        $company_code = request()->param('company_code');
        $order_no = request()->param('order_no');
        $user_id = Db::name('hunuo_order_info')->where(['order_no'=>$order_no])->value('user_id');
        $user = Db::name('hunuo_users')->where(['user_id'=>$user_id])->find();
        // 修改传递用订单ID 还原原本的订单号
        $bank = Db::name('hunuo_bankcard')->alias('bc')
            ->field('bc.bank_id,bc.name,bc.card_num,bc.bankcard_name')
            ->where('user_id', $user_id)
            ->order('bankcard_id', 'desc')
            ->find();

       $loan_type =  Db::name('hunuo_loan_type')->where(['company_code'=>$company_code])->find();

        //生成保存合同编号
        $contract_number             = $contract_number2 = date('Ymd') . $user_id;
        $fee['amount']               = $loan_type['apply_amount'];
        $fee['b_amount']             = get_amount($fee['amount']);
        $fee['platform_service_fee'] = $fee['amount'] * $loan_type['manage_fee'];
        $fee['info_fee']             = $fee['amount'] * $loan_type['approval_fee'];
        $fee['service_fee']          = $fee['amount'] * $loan_type['service_fee'];
        $fee['over_fee']             = $loan_type['over_fee']*100;
        $day                         = $loan_type['apply_term'];
        $ymd                         = date('Y-m-d');
        $due_ymd                     = date('Y-m-d', strtotime("+$day day"));
        $this->assign([
            'user'             => $user,
            'bank'             => $bank,
            'fee'              => $fee,
            'ymd'              => $ymd,
            'due_ymd'          => $due_ymd,
            'contract_number'  => $contract_number,
            'contract_number2' => $contract_number2,

        ]);
        return $this->fetch();
    }

    /**
     * 查询出推广的链接信息
     * @return \think\response\Json
     */
    public function showLoadInfo()
    {
        $companyUrl = request()->post('company_url', '', 'trim');
        $companyUrl = decode($companyUrl, config('conf.cook_key'));
        if(empty($companyUrl)) return json_encode(['code' => 201, 'message' => '参数不能为空', 'data' => '']);
        $downloadInfo = Db::name('promotion_download')->where(['status' => 1, 'company_url' => $companyUrl])->field('image_url,android_url,app_url')->find();
        if(empty($downloadInfo)) return json_encode(['code' => 202, 'message' => '不存在该推广链接', 'data' => '']);

        return json_encode(['code' => 200, 'message' => '请求成功', 'data' => $downloadInfo]);
    }

}