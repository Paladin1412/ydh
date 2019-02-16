<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 10:50
 */

namespace app\admin\controller;

use think\Db;

class Personnel extends Base
{
    public function __construct()
    {
        parent::__construct();
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
            $condition['u.user_name|u.real_name|u.email'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['u.add_time'] = array(array('gt', strtotime($time_data['start_time'])), array('lt', strtotime($time_data['end_time'])));
        }
        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }
        if(session('admin_info.role_id')!=='1'){
            $condition['u.company_id'] = session('admin_info.company_id');
        }
        $user_list = Db::table('system_admin_v2')
            ->alias('u')
            ->field('u.admin_id,u.user_name,u.real_name,u.email,c.cp_name,u.add_time,u.status')
            ->where($condition)
            ->join('system_company c', 'c.cp_id = u.company_id', 'left')
            ->limit((($post_data['page'] ? $post_data['page'] : 1) - 1) * $this->limit,$this->limit )
            ->order('u.admin_id')
            ->fetchSql(false)
            ->select();
        if (!empty($user_list) && is_array($user_list)) {
            foreach ($user_list as $key => &$value) {
                $value['cp_name']  = $value['cp_name'] ? $value['cp_name'] : "本平台";
                $value['add_time'] = $value['add_time'] ? date('Y-m-d', $value['add_time']) : '-';
                $value['roles']    = getUserRoles($value['admin_id']);
            }
        }
        $user_list_count = Db::table('system_admin_v2')->alias('u')->where($condition)->join('system_company c', 'c.cp_id = u.company_id', 'left')->count();

        $data['list']  = $user_list;
        $data['page']  = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $user_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($user_list_count / 20),
        );
        $data['field'] = lang('personnel_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 添加员工
     * @return \think\response\Json
     */
    public function user_add()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['user_name']) || empty($post_data['real_name']) || empty($post_data['email']) || empty($post_data['password']) || !isset($post_data['company_id']) || empty($post_data['role_id'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $has_user = Db::table('system_admin_v2')->where('user_name', $post_data['user_name'])->count();
        if (!empty($has_user)) {
            return json(['code' => 403, 'message' => lang('error_4007'), 'data' => []]);
        }
        $company_code = Db::table('system_company')->where('cp_id', $post_data['company_id'])->value('cp_code');
        $add_data     = array(
            'user_name'    => $post_data['user_name'],
            'real_name'    => $post_data['real_name'],
            'email'        => $post_data['email'],
            'password'     => encrypt($post_data['password']),
            'company_id'   => $post_data['company_id'],
            'role_id'      => $post_data['role_id'],
            'status'       => 1,
            'company_code' => $company_code,
            'operator'     => session('admin_info.real_name'),
            'operate_time' => time(),
            'add_time'     => time(),
        );
        $user_id      = Db::name('system_admin_v2')->insertGetId($add_data);
        $role_arr     = explode(",", $add_data["role_id"]);
        foreach ($role_arr as $key => $value) {
            Db::name('system_admin_role_relation_v2')->insert(array('admin_id' => $user_id, 'role_id' => $value, 'type' => 0, 'addtime' => date('Y-m-d H:i:s')));
        }
        if (false !== $user_id) {
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        } else {
            return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
        }

    }

    /**
     * 修改员工状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_change()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['admin_id']) || !isset($post_data['status'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $status = $post_data['status'] == 0 ? 1 : 0;
        $user   = Db::table('system_admin_v2')->where(array('admin_id' => $post_data['admin_id']))->find();
        if ($user['status'] == $status) {
            $res = Db::name('system_admin_v2')->where('admin_id', $post_data['admin_id'])->fetchSql(false)->update(array('status' => $post_data['status']));
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
     * 删除员工
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function user_delete()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['admin_id'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        Db::startTrans();
        $res  = Db::name('system_admin_v2')->delete($post_data['admin_id']);
        $res2 = Db::name('system_admin_role_relation_v2')->where('admin_id', $post_data['admin_id'])->delete();
        if (false !== $res && false !== $res2) {
            Db::commit();
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        } else {
            Db::rollback();
            return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
        }
    }

    /**
     * 编辑用户信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function user_edit()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['admin_id']) || empty($post_data['type'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if ($post_data['type'] == 1) {
            $user_info = Db::table('system_admin_v2')->find($post_data['admin_id']);
            if (empty($user_info)) {
                return json(['code' => 201, 'message' => lang('data_empty'), 'data' => []]);
            }
            $role_arr           = Db::name('system_admin_role_v2')->field('role_id,role_name')->where(array('role_id' => array('in', $user_info['role_id'])))->select();
            $user_info['roles'] = $role_arr;
            return json(['code' => 200, 'message' => lang('success'), 'data' => $user_info]);
        }
        if ($post_data['type'] == 2) {
            if (empty($post_data['user_name']) || empty($post_data['real_name']) || empty($post_data['email']) || empty($post_data['password']) || !isset($post_data['company_id']) || empty($post_data['role_id'])) {
                return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
            }
            $has_user = Db::table('system_admin_v2')->where('user_name', $post_data['user_name'])->where('admin_id', 'neq', $post_data['admin_id'])->where('company_id',session('admin_info.company_id'))->fetchSql(false)->count();
            if (!empty($has_user)) {
                return json(['code' => 403, 'message' => lang('error_4007'), 'data' => []]);
            }
            // 删除原用户-角色中间表
            Db::name('system_admin_role_relation_v2')->where('admin_id',$post_data['admin_id'])->delete();
            $role_arr = explode(',',$post_data['role_id']);
            if(!empty($role_arr) && is_array($role_arr)){
                foreach ($role_arr as $value){
                    Db::name('system_admin_role_relation_v2')->insert(array('role_id'=>$value,'admin_id'=>$post_data['admin_id'],'type'=>0,'addtime'=>date('Y-m-d')));
                }
            }
            $save_data = array(
                'admin_id'   => $post_data['admin_id'],
                'user_name'  => $post_data['user_name'],
                'real_name'  => $post_data['real_name'],
                'email'      => $post_data['email'],
                'company_id' => $post_data['company_id'],
                'role_id'    => $post_data['role_id'],
            );
            if (!empty($post_data['password'])) {
                $save_data['password'] = encrypt($post_data['password']);
            }
            $res = Db::name('system_admin_v2')->fetchSql(false)->update($save_data);
            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }
    }

    /**
     * 员工日志
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_log()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string'] = trimall($post_data['search_string']);
            $condition['u.user_name|u.real_name'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['l.log_time'] = array(array('gt', strtotime($time_data['start_time'])), array('lt', strtotime($time_data['end_time'])));
        }
        if (!empty($post_data['company_id'])) {
            if (session('company_id') != 0) {
                $condition['u.company_id'] = $post_data['company_id'];
            } else {
                return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
            }
        } else {
            if(session('company_id') != 0){
                $condition['u.company_id'] = session('company_id');
            }
        }

        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }
        $log_list = Db::table('system_admin_log')
            ->alias('l')
            ->field('u.user_name,u.real_name,c.cp_name,l.log_info,l.log_ip,l.log_time,l.log_url')
            ->where($condition)
            ->join('system_company c', 'c.cp_id = l.company_id', 'left')
            ->join('system_admin_v2 u', 'u.admin_id = l.admin_id', 'left')
            ->limit((($post_data['page'] ? $post_data['page'] : 1) - 1) * $this->limit,$this->limit )
            ->order('l.log_id desc')
            ->fetchSql(false)
            ->select();

        if (!empty($log_list) && is_array($log_list)) {
            foreach ($log_list as $key => &$value) {
                $value['log_time'] = date('Y-m-d H:i:s', $value['log_time']);
                $value['cp_name']  = $value['cp_name'] ? $value['cp_name'] : "本平台";
                $value['log_info'] = $value['log_info'] . $value['log_url'];
                unset($value['log_url']);
            }
        }
        $log_count = Db::table('system_admin_log')
            ->alias('l')
            ->where($condition)
            ->join('system_company c', 'c.cp_id = l.company_id', 'left')
            ->join('system_admin_v2 u', 'u.admin_id = l.admin_id', 'left')
            ->fetchSql(false)
            ->count();
        $data['list']  = $log_list;
        $data['page']  = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $log_count,
            'limit' => $this->limit,
            'cols'  => ceil($log_count / 20),
        );
        $data['field'] = lang('personnel_log');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }


}