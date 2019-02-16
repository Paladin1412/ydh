<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/4/17
 * Time: 18:34
 */

namespace app\admin\controller;

use think\Config;
use think\Db;
use think\ImageUpload;

class Company extends Base
{
    public function __construct()
    {
        //parent::__construct();
    }

    /**
     * 公司列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function company_index()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string'] = trimall($post_data['search_string']);
            $condition['cp_china_name|cp_name'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data                  = getSearchData($post_data['date']);
            $condition['operator_date'] = array(array('gt', $time_data['start_time']), array('lt', $time_data['end_time']));
        }
        $condition['apply_status'] = 0;


        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }
        $company_list = Db::table('system_company')
            ->alias('c')
            ->field('cp_id,cp_name,cp_code,status,cp_num,cp_leg_person,cp_contact_person,cp_mobile,cp_address,d.country_name,operator_name,operator_date,apply_status')
            ->join('daihou_country d', 'd.c_id = c.cp_country', 'left')
            ->where($condition)
            ->limit((($post_data['page'] ? $post_data['page'] : 1) - 1) * $this->limit,$this->limit )
            ->order('cp_id desc')
            ->fetchSql(false)
            ->select();
        if (!empty($company_list) && is_array($company_list)) {
            $company_status_lang = lang('company_status_lang');
            foreach ($company_list as $key => &$value) {
                $value['apply_status'] = $company_status_lang[$value['apply_status']];
                $value['operator_date'] = date('Y-m-d',strtotime($value['operator_date']));
            }
        }
        $company_list_count = Db::table('system_company')->where($condition)->count();
        $data['list']       = $company_list;
        $data['page']       = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $company_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($company_list_count / 20),
        );
        $data['field']      = lang('company_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 添加合作公司
     * @return \think\response\Json
     */
    public function company_add()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['business_card']) || empty($post_data['cp_contract']) || empty($post_data['cp_country']) || empty($post_data['cp_address']) || empty($post_data['cp_num']) || empty($post_data['cp_name']) || empty($post_data['cp_mobile']) || empty($post_data['cp_contact_person']) || empty($post_data['cp_leg_person']) || empty($post_data['cp_email'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $has_user = Db::name('system_admin_v2')->where('user_name', $post_data['cp_num'])->count();
        if ($has_user > 0) {
            return json(['code' => 402, 'message' => lang('error_4014'), 'data' => []]);
        }
        $add_data = array(
            'cp_num'            => $post_data['cp_num'],
            'cp_name'           => $post_data['cp_name'],
            'cp_mobile'         => $post_data['cp_mobile'],
            'cp_contact_person' => $post_data['cp_contact_person'],
            'cp_leg_person'     => $post_data['cp_leg_person'],
            'cp_email'          => $post_data['cp_email'],
            'cp_address'        => $post_data['cp_address'],
            'cp_country'        => $post_data['cp_country'],
            'cp_contract'       => filter_arr($post_data['cp_contract']),
            'business_card'     => filter_arr($post_data['business_card']),
            'apply_admin_id'    => session('admin_id'),
            'apply_admin_name'  => session('admin_info.real_name'),
            'apply_date'        => date('Y-m-d H:i:s'),
        );
        $res      = Db::name('system_company')->insert($add_data);
        if (false !== $res) {
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        } else {
            return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
        }
    }

    /**
     * 公司审核
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function company_todo()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $condition['cp_china_name|cp_name'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data                  = getSearchData($post_data['date']);
            $condition['operator_date'] = array(array('gt', $time_data['start_time']), array('lt', $time_data['end_time']));
        }
        $condition['apply_status'] = array('neq', 0);

        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }
        $company_list = Db::table('system_company')
            ->alias('c')
            ->field('cp_id,cp_name,cp_code,status,cp_num,cp_leg_person,cp_contact_person,cp_mobile,cp_address,d.country_name,operator_name,operator_date,apply_status,apply_date')
            ->join('daihou_country d', 'd.c_id = c.cp_country', 'left')
            ->where($condition)
            ->limit((($post_data['page'] ? $post_data['page'] : 1) - 1) * $this->limit,$this->limit )
            ->order('cp_id desc')
            ->fetchSql(false)
            ->select();
        if (!empty($company_list) && is_array($company_list)) {
            $company_status_lang = lang('company_status_lang');
            foreach ($company_list as $key => &$value) {
                $value['apply_status'] = $company_status_lang[$value['apply_status']];
                $value['operator_date'] = date('Y-m-d',strtotime($value['operator_date']));
            }
        }
        $company_list_count = Db::table('system_company')->where($condition)->count();
        $data['list']       = $company_list;
        $data['page']       = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $company_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($company_list_count / 20),
        );
        $data['field']      = lang('company_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 修改合作公司
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function company_edit()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['cp_id']) || empty($post_data['type'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if ($post_data['type'] == 1) {
            $company_info = Db::table('system_company s')->field('s.*,c.country_name')->where('s.cp_id', $post_data['cp_id'])->join('daihou_country c','c.c_id = s.cp_country')->find();
            if (empty($company_info)) {
                return json(['code' => 201, 'message' => lang('data_empty'), 'data' => []]);
            }
            $company_contract_arr = explode(',', $company_info['cp_contract']);
            if (!empty($company_contract_arr) && is_array($company_contract_arr)) {
                foreach ($company_contract_arr as $key => $value) {
                    if (!empty($value)) {
                        $contract_arr[] = array(
                            'image_code' => $value,
                            'image_url'  => getOssImageurl($value)
                        );
                    }
                }
            }
            $company_business_arr = explode(',', $company_info['business_card']);
            if (!empty($company_business_arr) && is_array($company_business_arr)) {
                foreach ($company_business_arr as $key1 => $value1) {
                    if (!empty($value1)) {
                        $business_arr[] = array(
                            'image_code' => $value1,
                            'image_url'  => getOssImageurl($value1),
                        );
                    }
                }
            }
            $company_info['cp_contract']   = $contract_arr;
            $company_info['business_card'] = $business_arr;
            return json(['code' => 200, 'message' => lang('success'), 'data' => $company_info]);
        }
        if ($post_data['type'] == 2) {
            if (empty($post_data['business_card']) || empty($post_data['cp_contract']) || empty($post_data['cp_country']) || empty($post_data['cp_address']) || empty($post_data['cp_num']) || empty($post_data['cp_name']) || empty($post_data['cp_mobile']) || empty($post_data['cp_contact_person']) || empty($post_data['cp_leg_person']) || empty($post_data['cp_email'])) {
                return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
            }
            $save_data = array(
                'cp_num'            => $post_data['cp_num'],
                'cp_name'           => $post_data['cp_name'],
                'cp_mobile'         => $post_data['cp_mobile'],
                'cp_contact_person' => $post_data['cp_contact_person'],
                'cp_leg_person'     => $post_data['cp_leg_person'],
                'cp_email'          => $post_data['cp_email'],
                'cp_address'        => $post_data['cp_address'],
                'cp_country'        => $post_data['cp_country'],
                'cp_contract'       => filter_arr($post_data['cp_contract']),
                'business_card'     => filter_arr($post_data['business_card']),
            );
            $res       = Db::name('system_company')->where('cp_id', $post_data['cp_id'])->update($save_data);
            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }
    }

    /**
     * 删除公司
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function company_delete()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['cp_id'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $role_count = Db::table('system_admin_role_v2')->where('company_id', $post_data['cp_id'])->count();
        if (!empty($role_count)) {
            return json(['code' => 403, 'message' => lang('error_4009'), 'data' => []]);
        }
        $res = Db::table('system_company')->where('cp_id', $post_data['cp_id'])->delete();
        if (false !== $res) {
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        } else {
            return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
        }
    }

    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function company_upload_image()
    {
        $file = request()->file('image');
        if (!empty($file)) {
            $info = $file->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                $image_path = ROOT_PATH . 'public' . DS . 'uploads' . DS . $info->getFilename();
                $image_code = uploadOssImageurl($image_path);
                unset($info);
                if (!empty($image_code)) {
                    unlink($image_path);
                    return json(['code' => 200, 'message' => lang('success'), 'data' => ['image_code' => $image_code, 'image_url' => getOssImageurl($image_code)]]);
                } else {
                    return json(['code' => 403, 'message' => lang('error_4011'), 'data' => []]);
                }
            } else {
                return json(['code' => 403, 'message' => lang('error_4010'), 'data' => []]);
            }
        } else {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
    }

    /**
     * 修改公司状态
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function company_change_status()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['cp_id']) || !isset($post_data['status'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $company_info = Db::name('system_company')->where('cp_id', $post_data['cp_id'])->find();
        if ($company_info['status'] != $post_data['status']) {
            $res = Db::name('system_company')->where('cp_id', $post_data['cp_id'])->fetchSql(false)->update(array('status' => $post_data['status']));
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
     * 合作公司审核
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function company_change()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['cp_id']) || empty($post_data['type'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $company_info = Db::table('system_company')->where('cp_id', $post_data['cp_id'])->find();
        if (empty($company_info) || $company_info['apply_status'] != 2) {
            return json(['code' => 403, 'message' => lang('error_4012'), 'data' => []]);
        }
        Config::load(APP_PATH . 'admin/config/company_default_role.php');
        if ($post_data['type'] == 1) {
            //  更新公司表状态
            $save_data = [
                'apply_status'  => 0,
                'operator_id'   => session('admin_id'),
                'operator_name' => session('admin_info.real_name'),
                'operator_date' => date("Y-m-d H:i:s"),
                'cp_code'       => uniqid(),
            ];
            Db::startTrans();
            $loan_info = [
                'status'    =>  1,
                'apply_term'    =>  0,
                'apply_amount'  =>  0,
                'rate'          =>  0,
                'service_fee'   =>  0,
                'approval_fee'  =>  0,
                'manage_fee'    =>  0,
                'over_fee'      =>  0,
                'term_fee'      =>  0,
                'open'          =>  0,
                'company_code'  =>  $save_data['cp_code'],
                'max_money'     =>  0
            ];
            Db::name('hunuo_loan_type')->insert($loan_info);
            Db::name('system_company')->where('cp_id', $post_data['cp_id'])->update($save_data);
            // 添加管理员角色
            $role_admin_data = [
                'role_name'   => $company_info["cp_name"] . '管理员组',
                'company_id'  => $company_info['cp_id'],
                'role_desc'   => $company_info["cp_name"] . '管理全平台 ',
                'type'        => 0,
                'admin_class' => '5',
            ];
            $role_admin_id   = Db::name('system_admin_role_v2')->insert($role_admin_data);
            // 添加管理员节点权限
            $admin_menu_data = Config::get('company_default_admin_menu');
            $admin_menu_data = explode(",", $admin_menu_data);
            if (!empty($admin_menu_data) && is_array($admin_menu_data)) {
                foreach ($admin_menu_data as $key2 => $value2) {
                    $role_menu_data = array(
                        'role_id' => $role_admin_id,
                        'menu_id' => $value2,
                        'type'    => 0,
                        'addtime' => date("Y-m-d H:i:s", time())
                    );
                    Db::name('system_role_menu_relation_v2')->insert($role_menu_data);
                }
            }
            // 新增管理员账号
            $admin_company_data = [
                'user_name'    => $company_info["cp_num"],
                'real_name'    => $company_info["cp_name"],
                'company_id'   => $company_info['cp_id'],
                'email'        => $company_info['cp_email'],
                'role_id'      => $role_admin_id,
                'operator'     => session('admin_info.real_name'),
                'add_time'     => time(),
                'company_code' => $save_data['cp_code'],
                'password'     => encrypt(111111),
            ];

            $admin_id = Db::name('system_admin_v2')->insert($admin_company_data);
            // 管理员用户和角色中间表关系
            $admin_role_relation = [
                'admin_id' => $admin_id,
                'role_id'  => $role_admin_id,
                'type'     => 0,
                'addtime'  => date("Y-m-d H:i:s"),
            ];
            Db::name('system_admin_role_relation_v2')->insert($admin_role_relation);

            //  新增其他默认角色
            $default_role_list = Config::get('company_default_role');
            if (!empty($default_role_list) && is_array($default_role_list)) {
                foreach ($default_role_list as $key => $value) {
                    $role_data = array(
                        'role_name'   => (isset($company_info['cp_name']) && !empty($company_info['cp_name'])) ? ($company_info['cp_name'] . $value['name']) : $value['name'],
                        'role_desc'   => $value['desc'],
                        'company_id'  => $company_info['cp_id'],
                        'status'      => 0,
                        'type'        => 1,
                        'admin_class' => $value['admin_class'],
                    );
                    $role_res  = Db::name('system_admin_role_v2')->insert($role_data);
                    if (false !== $role_res) {
                        $menu_data = explode(",", $value["menu_id"]);
                        if (isset($value["menu_id"]) && !empty($value["menu_id"]) && is_array($menu_data)) {
                            foreach ($menu_data as $key1 => $value1) {
                                $role_menu_data = array(
                                    'role_id' => $role_res,
                                    'menu_id' => $value1,
                                    'type'    => 0,
                                    'addtime' => date("Y-m-d H:i:s", time())
                                );
                                Db::name('system_role_menu_relation_v2')->insert($role_menu_data);
                            }
                        }
                    }
                }
            }


            Db::commit();
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        }
        if ($post_data['type'] == 2) {
            $save_data = array(
                'apply_status'  => 1,
                'operator_date' => date("Y-m-d H:i:s")
            );

            $res = Db::name('system_company')->where('cp_id', $post_data['cp_id'])->update($save_data);
            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }
    }


}