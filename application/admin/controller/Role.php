<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 13:32
 */

namespace app\admin\controller;

use think\Db;
use think\Session;
class Role extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 角色列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function role_index()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['company_id'])) {
            if (session('company_id') != 0) {
                $condition['r.company_id'] = $post_data['company_id'];
            } else {
                return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
            }
        } else {
            if(session('company_id') != 0){
                $condition['r.company_id'] = session('company_id');
            }
        }

        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }
        $role_list     = Db::table('system_admin_role_v2')
            ->alias('r')
            ->field('role_id,role_name,role_desc,r.status,c.cp_name')
            ->join('system_company c', 'c.cp_id = r.company_id', 'left')
            ->where($condition)
            ->order('company_id asc')
            ->limit((($post_data['page'] ? $post_data['page'] : 1) - 1) * $this->limit,$this->limit )
            ->select();
        $role_list_count = Db::table('system_admin_role_v2')->alias('r')->where($condition)->join('system_company c', 'c.cp_id = r.company_id', 'left')->count();
        $data['list']  = $role_list;
        $data['page']     = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $role_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($role_list_count / 20),
        );
        $data['field'] = lang('role_index');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 修改角色状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function role_change()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['role_id']) || !isset($post_data['status'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $status = $post_data['status'] == 0 ? 1 : 0;
        $user   = Db::table('system_admin_role_v2')->where(array('role_id' => $post_data['role_id']))->find();
        if ($user['status'] == $status) {
            $res = Db::table('system_admin_role_v2')->where('role_id', $post_data['role_id'])->fetchSql(false)->update(array('status' => $post_data['status']));

            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        } else {
            return json(['code' => 201, 'message' => lang('is_status'), 'data' => []]);
        }
    }

    /**
     * 编辑角色权限
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function role_edit()
    {
        $request   = request();
        $post_data = $request->param();

        if (empty($post_data['role_id']) || empty($post_data['type'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if ($post_data['type'] == 1) {
            $role_info = Db::table('system_admin_role_v2')->where("role_id", $post_data['role_id'])->find();
            // 获取该角色已配置的节点id
            $auth_menu_list = Db::table('system_role_menu_relation_v2')->where('role_id', $post_data['role_id'])->column('menu_id');
            //获取 menu_list
            $role_id = session('admin_info.role_id');
            $all_menu_list = get_auth_menu_list($auth_menu_list,$role_id);
            // dump($all_menu_list);
            // exit;

            //foreach($menu_list as $key => $value){
            //    $menu_list_arr[] = $value['menu_id'];
            //}
//            $right_menu = M('system_menu')->where('level', 'neq', 'Top')->where('status',0)->order('id')->select();
//            $home_sub = [
//                  'id' => 213,
//                  'name' => 'Home',
//                  'group' => 'Home',
//                  'pid' => 0,
//                  'right' => 'Home@get_home_data',
//                  'is_del' => 0,
//                  'type' => null,
//                  'status' => 0,
//                  'sort' => 1,
//                  'level' => 'Left',
//                  'url' => 'home/homeIndex.html',
//                  'en_name' => 'Home',
//                  'id_name' => 'Home',
//            ];
//            array_unshift($right_menu, $home_sub);
//            foreach ($right_menu as $key1 => $value1) {
//                //    if(!empty($role_info)){
//                //        if(!empty($menu_list_arr)){
//                //            $value1['enable'] = in_array($value1['id'], $menu_list_arr);
//                //        }else{
//                //            $value1['enable'] =0;
//                //        }
//                //    }
//                $modules[$value1['group']][] = $value1;
//            }
            $data['role_info'] = $role_info;
            $data['user_menu'] = $auth_menu_list;
            $data['all_menu']  = $all_menu_list;
            return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
        }
        if ($post_data['type'] == 2) {
            if (empty($post_data['role_name']) || empty($post_data['role_desc']) || empty($post_data['menu_id']) || !isset($post_data['company_id'])) {
                return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
            }
            Db::startTrans();
            $res1      = Db::name('system_role_menu_relation_v2')->where('role_id', $post_data['role_id'])->delete();
            $save_data = array(
                'role_id'    => $post_data['role_id'],
                'role_name'  => $post_data['role_name'],
                'role_desc'  => $post_data['role_desc'],
                'status'     => $post_data['status'],
                'company_id' => $post_data['company_id']
            );
            $res2      = Db::name('system_admin_role_v2')->fetchSql(false)->update($save_data);
            $menu_list = explode(',', $post_data['menu_id']);
            foreach ($menu_list as $key => $value) {
                Db::name('system_role_menu_relation_v2')->fetchSql(false)->insert(array('role_id' => $post_data['role_id'], 'menu_id' => $value,'type'=>0,'addtime'=>date('Y-m-d H:i:s',time())));
            }
            if (false !== $res1 && false !== $res2) {
                Db::commit();
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                Db::rollback();
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }
    }

    /**
     * 添加角色
     * @return \think\response\Json
     */
    public function role_add()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['role_name']) || empty($post_data['role_desc']) || !isset($post_data['company_id'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if(in_array($post_data['admin_class'],[5,6])){
            $type = 0;
        }else{
            $type = 1;
        }
        $add_data = array(
            'role_name'  => $post_data['role_name'],
            'role_desc'  => $post_data['role_desc'],
            'status'     => $post_data['status'],
            'company_id' => $post_data['company_id'],
            'admin_class'=> $post_data['admin_class'],
            'type'       => $type,
        );
        $res      = Db::name('system_admin_role_v2')->fetchSql(false)->insert($add_data);
        if (false !== $res) {
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        } else {
            return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
        }
    }
}