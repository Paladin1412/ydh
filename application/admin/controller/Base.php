<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 14:19
 */

namespace app\admin\controller;

use Admin\AuthonV2;
use think\Controller;
use think\Db;

class Base extends Controller
{
    protected $limit = 20;
    protected $env = null;

    public function __construct()
    {
        parent::__construct();

        if (session('?admin_id') === false) {
            exit(json_encode(['code' => 800, 'message' => lang('error_8001'), 'data' => []]));
        }
        $Auth = new AuthonV2();
        if ($Auth->Auth_Check(session('admin_id'), request()->controller() . '@' . request()->action(), session('admin_info.company_id')) === false) {
            exit(json_encode(['code' => 500, 'message' => lang('error_5001'), 'data' => []]));
        }
        $limit       = config('system.system_limit');
        $this->limit = isset($limit) ? $limit : 20;
        $this->env = check_env();
        adminLog('发起请求');
    }

    /**
     * 获取菜单列表
     * @return \think\response\Json
     */
    public function menu_list()
    {
        $obj       = new AuthonV2();
        $menu_data = $obj->getTopmenu(session('admin_id'), session("company_id"),cookie('think_var'));

        // 设置非中文时读取语言包
        // if (!empty($menu_data) && is_array($menu_data) && cookie('think_var') != 'zh-cn') {
        //     if ($_SERVER['SERVER_NAME'] == 'indonesia.tupulian.com' || $_SERVER['SERVER_NAME'] == 'test.indonesia.tupulian.com') {
        //         $id_lang       = lang('menu_name_online');
        //         $id_child_lang = lang('menu_child_name_online');
        //     } else {
        //         $id_lang       = lang('menu_name');
        //         $id_child_lang = lang('menu_child_name');
        //     }
        //     foreach ($menu_data as $key => &$value) {
        //         $value['name'] = $id_lang[$key . '_name'];
        //         if (!empty($value['child']) && is_array($value['child'])) {
        //             foreach ($value['child'] as &$value2) {
        //                 $value2['name'] = $id_child_lang[$value2['id']];
        //             }
        //         }
        //     }
        // }
        // 新增首页节点 直接在数据中操作
        $role_type = getAdminRoleType(1);
        if ($role_type >= 5) {
            $menu_data = array_reverse($menu_data);
            $menu_data['home'] = [
                'name'  => 'Home',
                'url'   => 'home/homeIndex.html',
                'child' => [],
            ];
            $menu_data = array_reverse($menu_data);
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $menu_data]);
    }

    /**
     * 获取公司列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function company_list()
    {
        $where['apply_status'] = 0;
        if (session('admin_info.company_id') != 0) {
            $company_list = Db::table('system_company')->field('cp_id,cp_name')->where('cp_id', session('admin_info.company_id'))->where($where)->select();
        } else {
            $company_list = Db::table('system_company')->field('cp_id,cp_name')->where($where)->select();
            if (!empty($company_list) && is_array($company_list)) {
                array_unshift($company_list, array('cp_id' => 0, 'cp_name' => '本平台'));
            }
        }
        return json(['code' => 200, 'message' => lang('success'), 'data' => $company_list]);
    }

    /**
     * 获取角色列表
     * @param $company_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function role_list($company_id = 0)
    {
        $company_id = session('company_id') == 0 ? request()->param('cp_id') : session('company_id');
        $role_list  = Db::table('system_admin_role_v2')->field('role_id,role_name')->where('company_id', $company_id)->where('status', 0)->fetchSql(false)->select();
        return json(['code' => 200, 'message' => lang('success'), 'data' => $role_list]);
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

    /**
     * 获取订单审核状态语言
     * @return \think\response\Json
     */
    public function order_handle_lang()
    {
        $order_handle_lang = lang('order_handle');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $order_handle_lang]);
    }

    /**
     * 获取信审人员
     * @param int $company_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handle_user_list($company_id = 0)
    {
        $admin_class = request()->param('admin_class');
        if((int)$admin_class===1){
            $class_id = '1,3';
        }else{
            $class_id = '7,3';
        }
        if ($company_id != session('company_id')) {
            if (session('admin_id') != 1) {
                $company_id = session('company_id');
            }
        } else {
            $company_id = session('company_id');
        }
        $handle_user_list = Db::table('system_admin_role_relation_v2 rr')
            ->field('u.admin_id,u.real_name,r.role_id,r.role_name')
            ->join('system_admin_role_v2 r','r.role_id = rr.role_id')
            ->join('system_admin_v2 u','u.admin_id = rr.admin_id')
            ->where('r.company_id',$company_id)
            ->where('r.admin_class','in',$class_id)
            ->where('u.status',0)
            ->where('r.status',0)
            ->select();  
        return json(['code' => 200, 'message' => lang('success'), 'data' => $handle_user_list]);
    }

    //获取角色属性列表
    public function role_attribute_list(){
         $list = Db::table('system_role_attribute')->field('id,name')->where('valid',1)->select();
         return json(['code' => 200, 'message' => lang('success'), 'data' => $list]);
    }

    //根据用户id获取用户信息
    public function userinfo_by_userid($userid=0){
        $user_info = Db::table('system_admin_v2')->field('admin_id,user_name,real_name')->where('admin_id',$userid)->find();
        return $user_info;
    }

    /**
     * 催收人员
     * @param int $company_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function collect_user_list($company_id = 0)
    {
        if ($company_id != session('company_id')) {
            if (session('admin_id') != 1) {
                $company_id = session('company_id');
            }
        } else {
            $company_id = session('company_id');
        }
        $handle_user_list = Db::table('system_admin_v2')->alias('u')
            ->field('u.admin_id,u.real_name')
            ->join('system_admin_role_v2 r', 'r.role_id = u.role_id')
            ->where(array('r.company_id' => $company_id, 'u.status' => 0, 'r.status' => 0, 'admin_class' => array('in', "2,4")))
            ->fetchSql(false)
            ->select();
        return json(['code' => 200, 'message' => lang('success'), 'data' => $handle_user_list]);
    }

    /**
     * 获取用户角色类型
     * @return int|mixed
     */
    public function get_role_type($type = 1)
    {
        $role_info = Db::table('system_admin_role_v2')->where('role_id', 'in', session('admin_info.role_id'))->fetchSql(false)->column('admin_class');
        $role_info = ($type == 1) ? array_diff($role_info, array(2, 4)) : array_diff($role_info, array(1, 3));
        return json(['code' => 200, 'message' => lang('success'), 'data' => ['role_type' => session('admin_id') == 1 ? 6 : (count($role_info) > 0 ? max($role_info) : 0)]]);
    }

    /**
     * 获取信审订单跟进
     * @return \think\response\Json
     */
    public function get_order_flow_lang()
    {
        $get_order_flow_lang = lang('handle_flow_type');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $get_order_flow_lang]);
    }

    /**
     * 获取国家信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_country_list()
    {
        $data = Db::table('daihou_country')->select();
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 获取当前登录用户名
     * @return \think\response\Json
     */
    public function get_user_info()
    {
        $data = array(
            'user_name' => session('admin_info.user_name'),
        );
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 获取订单状态
     * @return \think\response\Json
     */
    public function get_order_status()
    {
        return json(['code' => 200, 'message' => lang('success'), 'data' => lang('order_status')]);
    }

    /**
     * 获取渠道
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_company_statistical()
    {
        $company_id = request()->post('company_id');
        // 公司返回所有
        if (empty($company_id) && session('admin_info.company_id') == '0' && $company_id != '0') {
            $list = Db::table('statistical_adv')->field('code,name')->select();
            return json(['code' => 200, 'message' => lang('success'), 'data' => $list]);
        }
        if (isset($company_id) && session('admin_info.company_id') == '0') {
            $company_code = getCompanyCode($company_id);
        } else {
            $company_code = session('admin_info.company_code');
        }
        //$company_code = '5aab24666cfbf';
        $list = Db::table('statistical_adv')->field('code,name')->where(['company_code' => $company_code, 'status' => 1])->select();
        return json(['code' => 200, 'message' => lang('success'), 'data' => $list]);
    }

    /**
     * 获取城市
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_city()
    {
        $list = Db::name('hunuo_region')->field('region_id,region_name')->where('region_type', 2)->order('convert(`region_name` using gb2312) ASC')->select();
        return json(['code' => 200, 'message' => lang('success'), 'data' => $list]);
    }

    /**
     * 获取订单专案状态
     * @return \think\response\Json
     */
    public function get_order_quality()
    {
        return json(['code' => 200, 'message' => lang('success'), 'data' => lang('order_quality')]);
    }

    /**
     * 获取催收订单逾期属性
     * @return \think\response\Json
     */
    public function get_collection_s()
    {
        return json(['code' => 200, 'message' => lang('success'), 'data' => lang('collection_s')]);
    }

    /**
     * 获取信审不通过原因
     * @return \think\response\Json
     */
    public function get_handle_not_pass_list()
    {
        return json(['code' => 200, 'message' => lang('success'), 'data' => lang('order_handle_not_pass_info')]);
    }

    /**
     * 获取电话状态
     * @return \think\response\Json
     */
    public function get_phone_state()
    {
        return json(['code' => 200, 'message' => lang('success'), 'data' => lang('phone_state')]);
    }

    /*
     * 成功返回
     * */
    public function buildSuccess($data = [], $msg = '操作成功', $code = 200)
    {
        $return = json([
            'code' => $code,
            'message' => $msg,
            'data' => $data
        ]);
        return $return;
    }

    /*
     * 失败返回
     * */
    public function buildFailed($code, $msg, $data = [])
    {
        $return = json([
            'code' => $code,
            'message' => $msg,
            'data' => $data
        ]);
        return $return;
    }
}