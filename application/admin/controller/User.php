<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/4/13
 * Time: 15:16
 */
namespace app\admin\controller;

use think\Db;
use think\Config;
class User extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


    //用户管理过虑参数
    public function filtr(){
        //申请时间
        $filtr['apply_auth_time'] = ['title'=>lang('apply_auth_time_title')];
        //审核状态
        $filtr['check_auth_status'] = ['title'=>lang('check_auth_status_title'),'value'=>lang('check_auth_status')];
        //用户姓名/手机号
        $filtr['auth_searchstr'] = ['title'=>lang('auth_searchstr_title')];
        //dump($filtr);
        return json(['code' => 200, 'message' => lang('success'), 'data' => $filtr]);
    }

    /**
     * 员工列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_list()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string'] = trimall($post_data['search_string']);
            $condition['name|phone|idcode'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['reg_time'] = array(array('gt', strtotime($time_data['start_time'])), array('lt', strtotime($time_data['end_time'])));
        }
        if (!empty($post_data['source'])) {
            if (session('admin_info.company_id') != 0) {
                $condition['source'] = $post_data['source'];
            } else {
                return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
            }
        }
        if(!empty($post_data['company_id']) || $post_data['company_id'] === '0' ){
            $company_code = getCompanyCode($post_data['company_id']);
            $condition['company_code'] = $company_code;
        }else{
            if (session('admin_info.company_id') == 0){
                //$condition['company_code'] =  array('exp', 'is not null');
            }else{
                $condition['company_code'] = session('admin_info.company_code');
            }
        }

        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }
        $user_list = Db::table('hunuo_users')
            ->field('name,idcode,phone,source,reg_time')
            ->where($condition)
            ->limit((($post_data['page'] ? $post_data['page'] : 1) - 1) * $this->limit,$this->limit )
            ->order('user_id','desc')
            ->fetchSql(false)
            ->select();
        if (!empty($user_list) && is_array($user_list)) {
            foreach ($user_list as $key => &$value) {
                $value['reg_time']     = date('Y-m-d', $value['reg_time']);
            }
        }
        $user_list_count = Db::table('hunuo_users')->where($condition)->count();
        //echo Db::table('hunuo_users')->getlastsql();
        // exit;
        $data['list']     = $user_list;
        $data['page']     = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $user_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($user_list_count / 20),
        );
        $data['field']    = lang('user_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }


    //资质审核列表
    public function auth_list(){
        $post_data = request()->param();

        $where['ui.is_auth'] = ['<>',0];
        if (!empty($post_data['date'])) {
            $time_data = getSearchData($post_data['date']);
            $where['ui.apply_auth_time'] = array(array('egt', strtotime($time_data['start_time'])), array('elt', strtotime($time_data['end_time'])));
        }
        if (!empty($post_data['status'])) {
            $where['ui.is_auth'] = $post_data['status'];
        }
        if (!empty($post_data['searchstr'])) {
            $where['ui.id_card_name|ui.phone'] = array('like', "%{$post_data['searchstr']}%");
        }

        $page = $post_data['page'] ? $post_data['page']:1;
        $page_size = $post_data['page_size'] ? $post_data['page_size']:20;
        $list = Db::connect("db_config_invest")
            ->table('invest_user_info')
            ->alias('ui')
            ->field('ui.id,ui.user_id,ui.id_card_name,ui.phone,bl.bank_name,bc.card_num,ui.id_card_num,ui.is_auth,ui.apply_auth_time')
            ->join('invest_bank_card bc','bc.user_id =ui.user_id','left')
            ->join('invest_bank_list bl','bl.bank_id =bc.bank_id','left')
            ->where($where)
            ->limit(($page - 1) * $page_size, $page_size)
            ->order('ui.id desc')
            ->fetchSql(false)
            ->select();        
        foreach($list as $key=>$val){
            $check_auth_status_lang = lang('check_auth_status');
            $list[$key]['is_auth_name'] = $check_auth_status_lang[$val['is_auth']];
            $list[$key]['apply_auth_time'] = date('Y-m-d H:i:s',$val['apply_auth_time']);
        }
        $count = Db::connect("db_config_invest")->table('invest_user_info')->alias('ui')->where($where)->fetchSql(false)->count();

        $return_page  = [
            'page'  => $page,
            'count' => $count,
            'page_size' => $page_size,
            'cols'  => ceil($page_size/$page_size),
        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' =>['list'=>$list,'field'=>lang('auth_list_header'),'page'=>$return_page]]);

    }

    //资质审核
    public function auth_check(){
        $post_data = request()->param();
        if(empty($post_data['user_id']) || empty($post_data['type'])){
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if((int)$post_data['type']===1){
            $info = Db::connect("db_config_invest")
                ->table('invest_user_info')
                ->alias('ui')
                ->field('ui.user_id,ui.id_card_name,ui.sex,ui.birthday,ui.address,ui.is_auth,ui.birth_place,ui.phone,ui.id_card_num,bc.card_num,bl.bank_name,ui.security_card,ui.tax_card,ui.family_card,ui.id_card_front,ui.id_card_back,ui.living_best')
                ->join('invest_bank_card bc','bc.user_id =ui.user_id','left')
                ->join('invest_bank_list bl','bl.bank_id =bc.bank_id','left')
                ->where(['ui.user_id' =>$post_data['user_id']])
                ->fetchSql(false)
                ->find();

            //格式化数据
            $info['security_card'] = $info['security_card']?getOssImageurl($info['security_card']):'';
            $info['tax_card'] = $info['tax_card']?getOssImageurl($info['tax_card']):'';
            $info['family_card'] = $info['family_card']?getOssImageurl($info['family_card']):'';
            $info['id_card_front'] = $info['id_card_front']?getOssImageurl($info['id_card_front']):'';
            $info['id_card_back'] = $info['id_card_back']?getOssImageurl($info['id_card_back']):'';
            $info['living_best'] = $info['living_best']?getOssImageurl($info['living_best']):'';
            $info['birthday'] = date('Y-m-d',$info['birthday']);
            $info['country_name'] = 'indonesia';
            if($info['sex']===1){
                $info['sex'] = lang('cllection_sex_1');
            }else{
                $info['sex'] = lang('cllection_sex_2');
            }

            //语言包
            $field_arr['base_info'] = lang('base_info');
            $field_arr['other_picture'] = lang('other_picture');
            $field_arr['identity_picture'] = lang('identity_picture');

            return json(['code' => 200, 'message' => lang('success'), 'data' => ['info'=>$info,'field'=>$field_arr]]);
        }else{
            if(empty($post_data['is_auth'])){
                return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
            }
            $remark = $post_data['remark']?$post_data['remark']:'';
            $update_data = [
                'is_auth' => $post_data['is_auth'],
                'auth_remark' => $remark,
                'last_auth_time' => time(),
                'check_auth_admin' => session('admin_info.user_name'),
            ];
            $result = Db::connect("db_config_invest")->name('invest_user_info')->where(['user_id'=>$post_data['user_id']])->update($update_data);

            //审核结果通知用户
            if($result){
                $key = '8QXp1kK6yI3rwBgj';
                $sign = md5($post_data['user_id'].$key);
                (int)$post_data['is_auth']=== 2?$type=1:$type=2;
                $data = [
                    'user_id' => $post_data['user_id'],
                    'sign' => $sign,
                    'type' => $type,
                ];
                //获取app域
                $site_web   = Config::get('database.app_site');  
                $postUrl    = $site_web . '/index.php/index/invest_user/admin_auth';
                $response   = httpPost($postUrl, $data);
                $res     = json_decode($response, true);
                //加入审核日志
                $audit_log = [
                    'audit_type' => 1,
                    'unique_id' => $post_data['user_id'],
                    'desc' => '用户资质审核',
                    'return_code' => $res['status']?$res['status']:0,
                    'add_time' => time(),
                    'audit_result' => $post_data['is_auth'],
                ];
                Db::connect("db_config_invest")->name('invest_audit_log')->insertGetId($audit_log);
            }
            if($result){
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            }else{
                return json(['code' => 401, 'message' => lang('error_4001'), 'data' => []]);
            }
        }
    }

    //审核纪录
    public function check_record(){
        $post_data = request()->param();
        if(empty($post_data['user_id']) || empty($post_data['type'])){
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if((int)$post_data['type']===1){
            $info = Db::connect("db_config_invest")
                ->table('invest_user_info')
                ->field('id,user_id,check_auth_admin,is_auth,last_auth_time,auth_remark')
                ->where(['user_id'=>$post_data['user_id']])
                ->find();
            $info['last_auth_time'] = date('Y-m-d H:i:s',$info['last_auth_time']);
            $check_auth_status = lang('check_auth_status');
            $info['is_auth_name'] = $check_auth_status[$info['is_auth']];
            return json(['code' => 200, 'message' => lang('success'), 'data' => $info]);
        }else{
            //重新审核，状态改为待审核
            $result = Db::connect("db_config_invest")->name('invest_user_info')->where(['user_id'=>$post_data['user_id']])->update(['is_auth'=>1]);
            if($result){
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            }else{
                return json(['code' => 401, 'message' => lang('error_4001'), 'data' => []]);
            }
        }
    }

   




}