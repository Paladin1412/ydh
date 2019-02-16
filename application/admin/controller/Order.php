<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/4/13
 * Time: 14:43
 */

namespace app\admin\controller;

use think\Config;
use think\Db;

class Order extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_list()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string']                      = trimall($post_data['search_string']);
            $condition['o.name|o.phone|u.idcode|o.order_no'] = array('like', "%{$post_data['search_string']}%");
            $is_search                                       = 1;
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['o.add_time'] = array(array('gt', strtotime($time_data['start_time'])), array('lt', strtotime($time_data['end_time'])));
            $is_search               = 1;
        }
        // 新增查询应还款时间
        if (!empty($post_data['date2'])) {
            $time_data2              = getSearchData($post_data['date2']);
            $condition['r.due_time'] = array(array('egt', strtotime($time_data2['start_time'])), array('elt', strtotime($time_data2['end_time'])));
            $is_search               = 1;
        }
        // 新增查询入账时间
        if (!empty($post_data['date3'])) {
            $time_data3              = getSearchData($post_data['date3']);
            $condition['o.end_time'] = array(array('egt', strtotime($time_data3['start_time'])), array('elt', strtotime($time_data3['end_time'])));
            $is_search               = 1;
        }
        // 新增查询渠道
        if (!empty($post_data['statistical_code'])) {
            $condition['sa.code'] = $post_data['statistical_code'];
            $is_search            = 1;
        }
        // 新增查询所处城市
        if (!empty($post_data['city_id'])) {
            $condition['u.city'] = $post_data['city_id'];
            $is_search           = 1;
        }
        if (!empty($post_data['company_id']) || $post_data['company_id'] === '0') {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['o.company_code'] = $company_code;
        } else {
            if (session('admin_info.company_id') == 0) {
                //$condition['o.company_code'] = array('exp', 'is not null');
            } else {
                $condition['o.company_code'] = session('admin_info.company_code');
            }
        }
        // 新增查询订单状态
        if (!empty($post_data['order_status'])) {
            $condition['o.order_status'] = $post_data['order_status'];
        }
        // 风控 未进行0 ，通过1，未通过2
        if (!empty($post_data['risk_status']) || $post_data['risk_status'] === '0') {
            $condition['o.risk_status'] = $post_data['risk_status'];
        }
        // 放款 未进行0 ，成功1，失败2
        if (!empty($post_data['pay_status']) || $post_data['pay_status'] === '0') {
            $condition['o.pay_status'] = $post_data['pay_status'];
        }
        // 信审 1未进行 2通过 3未通过
        if (!empty($post_data['handle_status']) || $post_data['handle_status'] === '0') {
            $condition['o.handle_state'] = $post_data['handle_status'];
        }

        
        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }
        $order_list = Db::table('hunuo_order_info')
            ->alias('o')
            ->field('o.order_no,o.name,o.phone,r.due_time,o.source,o.handle_state,o.add_time,o.application_amount,o.application_term,o.order_status,r.due_day,o.end_time,hr.region_name,sa.name as statistical,o.handle_state,o.pay_status,o.risk_status')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')
            ->join('statistical_order so', 'so.order_no = o.order_no', 'left')
            ->join('statistical_click sc', 'sc.code = so.code', 'left')
            ->join('statistical_adv sa', 'sa.code = sc.code', 'left')
            ->join('hunuo_region hr', 'hr.region_id = u.city', 'left')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->group('o.order_no')
            ->order('o.order_id desc')
            ->fetchSql(false)
            ->select();
        if (!empty($order_list) && is_array($order_list)) {
            $order_status_lang = lang('order_status');
            $order_handle_lang = lang('order_handle');
            $risk_status_lang  = lang('risk_status');
            $pay_status_lang   = lang('pay_status');
            foreach ($order_list as $key => &$value) {
                $value['add_time']     = date('Y-m-d', $value['add_time']);
                $value['order_status'] = $order_status_lang[$value['order_status']];
                $value['handle_state'] = ($value['handle_state'] != 0 && $value['handle_state'] != 1) ? $order_handle_lang[$value['handle_state']] : '-';
                $value['risk_status']  = $value['risk_status'] != 0 ? $risk_status_lang[$value['risk_status']] : '-';
                $value['pay_status']   = $value['pay_status'] != 0 ? $pay_status_lang[$value['pay_status']] : '-';
                $value['repay_time']   = ($value['due_time'] > 0) ? date('Y-m-d', $value['due_time']) : '-';
                $value['end_time']     = ($value['end_time'] > 0) ? date('Y-m-d', $value['end_time']) : '-';
                $value['due_day']      = (int)$value['due_day'];
                $value['source']       = empty($value['statistical']) ? '-' : $value['statistical'];

            }
        }
        $order_list_count = Db::table('hunuo_order_info')
            ->alias('o')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('statistical_order so', 'so.order_no = o.order_no', 'left')
            ->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')
            ->join('statistical_click sc', 'sc.id = so.click_id', 'left')
            ->join('statistical_adv sa', 'sa.code = sc.code', 'left')
            ->join('hunuo_region hr', 'hr.region_id = u.city', 'left')
            ->count();

        $data['list']  = $order_list;
        $data['page']  = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / 20),
        );
        $data['field'] = lang('order_index');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 订单详细
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function order_info()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['order_no'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        // 订单详细
        // 订单号 姓名 手机号 贷款金额 还款金额 银行名称 银行卡号 贷款时间 审批时间 放款时间 还款结清时间 状态 不通过原因 信审审核
        $order_info = Db::table("hunuo_order_info")->alias('o')
            ->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')
            ->join('hunuo_bankcard b', 'b.bankcard_id=o.bankcard_id', 'left')
            //->join('hunuo_order_handle_user t', 't.order_id = o.order_id', 'left')
            ->join('hunuo_users u', 'u.user_id = o.user_id ')
            ->field('o.order_id,o.order_no,o.name,o.phone,o.application_amount,o.confirm_time,o.approval_amount,r.paid_amount,b.bankcard_name,b.card_num,o.add_time,o.application_term,o.refuse_time,o.lending_time,o.end_time,o.order_status,o.handle_state,r.success_time,u.whats_app_user')
            ->where('o.order_no', $post_data['order_no'])
            ->fetchSql(false)
            ->find();

        //如果订单状态为审核不通过,且最后一次为初审,则获取不通过原因
        if((int)$order_info['order_status']===110){
            $review_log = Db::table("hunuo_order_review_log")->where(['order_id'=>$order_info['order_id']])->order('order_log_id desc')->find();
            if((int)$review_log['review_type']===1){
                $order_no_pass_lang = lang('order_handle_not_pass_info');
                $order_info['not_pass_info'] = $order_no_pass_lang[$review_log['refuse_type']];
            }
        }

        //获取最后一次初审时间
        $check_time1 = Db::table("hunuo_order_review_log")->where(['order_id'=>$order_info['order_id'],'review_type'=>1])->order('order_log_id desc')->value('add_time');
        //获取最后一次终审时间
        $check_time2 = Db::table("hunuo_order_review_log")->where(['order_id'=>$order_info['order_id'],'review_type'=>2])->order('order_log_id desc')->value('add_time');

        $order_status_lang              = lang('order_status');
        $order_info['order_status']     = $order_status_lang[$order_info['order_status']];
        $order_info['lending_time']     = $order_info['lending_time'] == 0 ? '-' : date('Y-m-d H:i:s', $order_info['lending_time']);//放款时间
        $order_info['end_time']         = $order_info['end_time'] == 0 ? '-' : date('Y-m-d H:i:s', $order_info['end_time']);//还款结清时间
        $order_info['refuse_time_risk'] = $order_info['refuse_time'] == 0 ? '-' : date('Y-m-d H:i:s', $order_info['refuse_time']);//审批时间
        $order_info['refuse_time']      = empty($order_info['confirm_time']) ? '-' : date('Y-m-d H:i:s', $order_info['confirm_time']);//审批时间
        $order_info['add_time']         = $order_info['add_time'] == 0 ? '-' : date('Y-m-d H:i:s', $order_info['add_time']);//下单时间
        $order_info['paid_amount']      = empty($order_info['paid_amount']) ? '-' : $order_info['paid_amount'];//还款金额
        $order_info['check_time1']      = empty($check_time1)? '-' : date('Y-m-d H:i:s', $check_time1);//初审时间
        $order_info['check_time2']      = empty($check_time2)? '-' : date('Y-m-d H:i:s', $check_time2);//终审时间

        $agency_back = Db::table("hunuo_agency_callback_log")->where("order_no", $post_data['order_no'])->select();
        $agency_back_log = [];
        foreach($agency_back as $key=>$val){
            $agency_back_log[$key]['t_id'] = $val['serial_number'];
            $agency_back_log[$key]['add_time'] = $val['add_date'];
            $agency_back_log[$key]['status'] = $val['msg'];
            $agency_back_log[$key]['price'] = $order_info['approval_amount'];
            $agency_back_log[$key]['currency'] = '人民币';
        }

        $quick_back = Db::table("hunuo_quick_callback_log")->where("order_no", $post_data['order_no'])->select();
        $quick_back_log = [];
        foreach($quick_back as $key=>$val){
            $quick_back_log[$key]['t_id'] = $val['serial_number'];
            $quick_back_log[$key]['add_time'] = $val['add_date'];
            $quick_back_log[$key]['status'] = $val['msg'];
            $quick_back_log[$key]['price'] = $val['order_amount'];
            $quick_back_log[$key]['currency'] = '人民币';
        } 
        $lang_arr = array(lang('order_info'), lang('pay_log'), lang('repay_log'),lang('repay_code_log'));
        return json(['code' => 200, 'message' => lang('success'), 'data' => array('order_info' => $order_info, 'pay_log' => $agency_back_log, 'repay_log' => $quick_back_log,'field' => $lang_arr)]);
    }

    /**
     * 所有审批
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_all()
    {
        $request   = request();
        $post_data = $request->param();
        $is_search = 0;
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string']                      = trimall($post_data['search_string']);
            $condition['o.name|o.phone|u.idcode|o.order_no'] = array('like', "%{$post_data['search_string']}%");
            $is_search                                       = 1;
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['o.add_time'] = array(array('gt', strtotime($time_data['start_time'])), array('lt', strtotime($time_data['end_time'])));
            $is_search               = 1;
        }
        if (!empty($post_data['approval_time'])) {
            $time_data2 = getSearchData($post_data['approval_time']);

            $condition['o.confirm_time'] = array(array('gt', strtotime($time_data2['start_time'])), array('lt', strtotime($time_data2['end_time'])));
            $is_search                   = 1;
        }
        if (isset($post_data['company_id']) && !empty($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['o.company_code'] = $company_code;
            $is_search                   = 1;
        } else {
            if (session('admin_info.company_id') == 0) {
            } else {
                $condition['o.company_code'] = session('admin_info.company_code');
            }
        }
        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'add_time') {
                $order_condition = 'o.add_time ';
            }
            if ($post_data['order_field'] == 'confirm_time') {
                $order_condition = 'o.confirm_time ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'o.order_id desc';
        }
        if (isset($post_data['handle_state']) && !empty($post_data['handle_state'])) {
            $condition['o.handle_state'] = $post_data['handle_state'];
        }
        $condition['o.risk_status'] = 1;
        if (!empty($post_data['admin_id'])) {
            $condition['hu.admin_id'] = $post_data['admin_id'];
        } else {
            $role_type = getAdminRoleType();
            if (in_array($role_type, ['3', '5', '6'])) {
                // 信审主管
            } elseif ($role_type == 1) {
                // 信审人
                $condition['hu.admin_id'] = session('admin_id');
            }
        }
        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }
        $order_list = Db::table('hunuo_order_info')
            ->alias('o')
            ->field('o.order_no,o.name,u.idcode,o.phone,o.application_amount,o.application_term,o.add_time,o.handle_state,o.confirm_time,o.order_status,o.company_code,hu.admin as first_admin,hus.admin as second_admin')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_order_handle_user hu', 'hu.order_id = o.order_id','left')
            ->join('hunuo_order_handle_user_second hus', 'hus.order_id = o.order_id','left')
            ->join('system_admin_v2 ua', 'ua.admin_id = o.user_id', 'left')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();
        // echo Db::table('hunuo_order_info')->alias('o')->getlastsql();
        // exit;
        if (!empty($order_list) && is_array($order_list)) {
            $order_handle = lang('order_handle');
            foreach ($order_list as $key => &$value) {
                $value['add_time']     = date('Y-m-d', $value['add_time']);
                $value['confirm_time'] = $value['confirm_time'] ? date('Y-m-d', $value['confirm_time']) : '-';
                $value['handle_state'] = $order_handle[$value['handle_state']];
            }
        }
        $order_list_count = Db::table('hunuo_order_info')
            ->alias('o')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            //->join('hunuo_order_handle_user t', 't.order_id = o.order_id', 'left')
            //->join('system_admin_v2 ua', 'ua.admin_id = t.admin_id', 'left')
            ->count();

        $data['list']  = $order_list;
        $data['page']  = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / $this->limit),
        );
        $data['field'] = lang('order_all');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 待初批
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_todo()
    {
        $request   = request();
        $post_data = $request->param();
        $is_search = 0;
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string']                      = trimall($post_data['search_string']);
            $condition['o.name|o.phone|u.idcode|o.order_no'] = array('like', "%{$post_data['search_string']}%");
            $is_search                                       = 1;
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['o.add_time'] = array(array('gt', strtotime($time_data['start_time'])), array('lt', strtotime($time_data['end_time'])));
            $is_search               = 1;
        }
        if (isset($post_data['company_id']) && !empty($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['o.company_code'] = $company_code;
            $is_search                   = 1;
        } else {
            if (session('admin_info.company_id') == 0) {
                //$condition['o.company_code'] = array('exp', 'is not null');
            } else {
                $condition['o.company_code'] = session('admin_info.company_code');
            }
        }
        if (!empty($post_data['admin_id'])) {
            //if (session('admin_info.company_id') == 0 || powerShowV2('Base@handle_user_list')) {
            //    $condition['t.user_id'] = $post_data['admin_id'];
            //} else {
            //    return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
            //}
            $condition['hu.admin_id'] = $post_data['admin_id'];
            $is_search               = 1;
        } else {
            $role_type = getAdminRoleType();
            if (in_array($role_type, ['3', '5', '6'])) {
                // 信审主管
            } elseif ($role_type == 1) {
                // 信审人
                $condition['hu.admin_id'] = session('admin_id');
            }
        }
        $condition['o.handle_state'] = 1;
        $condition['o.risk_status']  = 1;

        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'add_time') {
                $order_condition = 'o.add_time ';
            }
            if ($post_data['order_field'] == 'confirm_time') {
                $order_condition = 'o.confirm_time ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'o.order_id desc';
        }

        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }

        $order_list = Db::table('hunuo_order_info')
            ->alias('o')
            ->field('o.order_id,o.order_no,o.name,u.idcode,o.phone,o.application_amount,o.application_term,o.add_time,o.handle_state,o.confirm_time,
            o.company_code,hu.admin as first_admin,hus.admin as second_admin')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_order_handle_user hu', 'hu.order_id = o.order_id', 'left')
            ->join('hunuo_order_handle_user_second hus', 'hus.order_id = o.order_id', 'left')
            //->join('system_admin_v2 ua', 'ua.admin_id = t.admin_id', 'left')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();
        if (!empty($order_list) && is_array($order_list)) {
            $order_handle_lang = lang('order_handle');
            foreach ($order_list as $key => &$value) {
                $value['add_time']     = date('Y-m-d', $value['add_time']);
                $value['confirm_time'] = $value['confirm_time'] ? date('Y-m-d', $value['confirm_time']) : '-';
                $value['handle_state'] = $order_handle_lang[$value['handle_state']];
            }
        }
        $order_list_count = Db::table('hunuo_order_info')
            ->alias('o')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_order_handle_user t', 't.order_id = o.order_id', 'left')
            //->join('system_admin_v2 ua', 'ua.admin_id = t.admin_id', 'left')
            ->count();

        $data['list']  = $order_list;
        $data['page']  = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / $this->limit),
        );
        $data['field'] = lang('order_all');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }
    /**
     * 待终批
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_todo_end()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string']                      = trimall($post_data['search_string']);
            $condition['o.name|o.phone|u.idcode|o.order_no'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['o.add_time'] = array(array('gt', strtotime($time_data['start_time'])), array('lt', strtotime($time_data['end_time'])));
        }
        if (isset($post_data['company_id']) && !empty($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['o.company_code'] = $company_code;
        } else {
            if (session('admin_info.company_id') == 0) {
            } else {
                $condition['o.company_code'] = session('admin_info.company_code');
            }
        }
        // // 新增查询专案订单
        // if (!empty($post_data['quality'])) {
        //     $condition['o.quality'] = ($post_data['quality'] == 2) ? 0 : 1;
        // }
        if (!empty($post_data['admin_id'])) {
            $condition['hus.admin_id'] = $post_data['admin_id'];
        } else {
            $role_type = getAdminRoleType();
            if (in_array($role_type, ['3', '5', '6'])) {
                // 信审主管
            } elseif ($role_type == 1) {
                // 信审人
                $condition['hus.admin_id'] = session('admin_id');
            }
        }
        $condition['o.handle_state'] = 4;
        $condition['o.risk_status']  = 1;

        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'add_time') {
                $order_condition = 'o.add_time ';
            }
            if ($post_data['order_field'] == 'confirm_time') {
                $order_condition = 'o.confirm_time ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'o.order_id desc';
        }

        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }

        $order_list = Db::table('hunuo_order_info')
            ->alias('o')
            ->field('o.order_id,o.order_no,o.name,u.idcode,o.phone,o.application_amount,o.application_term,o.add_time,o.handle_state,o.confirm_time,o.company_code,hu.admin as first_admin,hus.admin as second_admin')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_order_handle_user hu', 'hu.order_id = o.order_id', 'left')
            ->join('hunuo_order_handle_user_second hus', 'hus.order_id = o.order_id', 'left')
            //->join('system_admin_v2 ua', 'ua.admin_id = t.admin_id', 'left')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();
        if (!empty($order_list) && is_array($order_list)) {
            $order_handle_lang = lang('order_handle');
            foreach ($order_list as $key => &$value) {
                $value['add_time']     = date('Y-m-d', $value['add_time']);
                $value['confirm_time'] = $value['confirm_time'] ? date('Y-m-d', $value['confirm_time']) : '-';
                $value['handle_state'] = $order_handle_lang[$value['handle_state']];
            }
        }
        $order_list_count = Db::table('hunuo_order_info')
            ->alias('o')
            ->where($condition)
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_order_handle_user t', 't.order_id = o.order_id', 'left')
            //->join('system_admin_v2 ua', 'ua.admin_id = t.admin_id', 'left')
            ->count();

        $data['list']  = $order_list;
        $data['page']  = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / $this->limit),
        );
        $data['field'] = lang('order_all');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 信审订单详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_handle_info()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['order_no'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        // 订单详细
        // 订单号 姓名 手机号 贷款金额 还款金额 银行名称 银行卡号 贷款时间 审批时间 放款时间 还款结清时间 状态 信审审核
        // 手机号码，姓名，身份证，现居住地址，现单位地址，职业类别，GPS地址，教育程度，公司名称，公司地址，公司电话，征信图片，其他图片资料
        $order_info        = Db::table("hunuo_order_info")->alias('o')
            ->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')
            ->join('hunuo_bankcard b', 'b.bankcard_id=o.bankcard_id', 'left')
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_region c', 'c.region_id = u.city', 'left')
            ->join('hunuo_order_handle_user t', 't.order_id = o.order_id', 'left')
            ->field('o.order_id,o.user_id,o.order_no,o.name,o.phone,o.application_amount,r.paid_amount,b.bankcard_name,
            b.card_num,o.add_time,o.application_term,o.refuse_time,o.lending_time,o.end_time,o.order_status,o.handle_state,
            r.success_time,u.phone,u.name,u.idcode,c.region_name as city,u.address,u.profession,u.industry,o.gps_address,
            u.education,u.company,u.company_add,u.company_tel,u.credit_img,u.tax_card,u.security_card,u.family_card,u.staff_card,
            u.salary_card,u.work_prove,u.photo_assay,t.change_time,u.face_card,u.scores_assay,u.assay_time,u.is_marrey,u.birthday,u.whats_app_user')
            ->where('o.order_no', $post_data['order_no'])
            ->find();
        $addtime           = $order_info['add_time'];
        $order_status_lang = lang('order_status');
        $handle_order_lang = lang('order_handle');
        $profession_type   = lang('profession_type');
        $industry_type     = lang('industry_type');
        $education_type    = lang('education_type');
        $handle_result_type = lang('handle_result_type');

        $order_info['order_status']     = $order_status_lang[$order_info['order_status']];
        $order_info['profession']       = $profession_type[$order_info['profession']];
        $order_info['industry']         = $industry_type[$order_info['industry']];
        $order_info['education']        = $education_type[$order_info['education']];
        $order_info['now_state']        = $order_info['handle_state'];
        $order_info['handle_state']     = $handle_order_lang[$order_info['handle_state']];
        $order_info['credit_img']       = $order_info['credit_img'] ? getOssImageurl($order_info['credit_img']) : '-';
        $order_info['tax_card']         = $order_info['tax_card'] ? getOssImageurl($order_info['tax_card']) : "";
        $order_info['security_card']    = $order_info['security_card'] ? getOssImageurl($order_info['security_card']) : "";
        $order_info['family_card']      = $order_info['family_card'] ? getOssImageurl($order_info['family_card']) : "";
        $order_info['staff_card']       = $order_info['staff_card'] ? getOssImageurl($order_info['staff_card']) : "";
        $order_info['salary_card']      = $order_info['salary_card'] ? getOssImageurl($order_info['salary_card']) : "";
        $order_info['work_prove']       = $order_info['work_prove'] ? getOssImageurl($order_info['work_prove']) : "";
        $order_info['add_time']         = $order_info['add_time'] ? date('Y-m-d', $order_info['add_time']) : '-';
        $order_info['refuse_time_risk'] = $order_info['refuse_time'] ? date('Y-m-d', $order_info['refuse_time']) : '-';
        $order_info['refuse_time']      = $order_info['change_time'] ? date('Y-m-d', $order_info['change_time']) : '-';
        $order_info['lending_time']     = $order_info['lending_time'] ? date('Y-m-d', $order_info['lending_time']) : '-';
        $order_info['end_time']         = $order_info['end_time'] ? date('Y-m-d', $order_info['end_time']) : '-';
        $order_info['success_time']     = $order_info['success_time'] ? date('Y-m-d', $order_info['success_time']) : '-';
        $order_info['age']              = floor((time() - $order_info['birthday']) / (60 * 60 * 24 * 365));

        //获取最后的初审与终审时间
        $review_time1 = Db::name('hunuo_order_review_log')->where(['order_id'=>$order_info['order_id'],'review_type'=>1])->order('order_log_id desc')->value('add_time');
        $review_time2 = Db::name('hunuo_order_review_log')->where(['order_id'=>$order_info['order_id'],'review_type'=>2])->order('order_log_id desc')->value('add_time');
        $order_info['review_time1'] = $review_time1?date('Y-m-d H:i:s',$review_time1):'-';
        $order_info['review_time2'] = $review_time2?date('Y-m-d H:i:s',$review_time2):'-';
        // 活体识别图片
        $face_images = explode(',', $order_info['photo_assay']);
        if (!empty($face_images) && is_array($face_images)) {
            foreach ($face_images as $key2 => &$value2) {
                if (check_oss_image_url($value2)) {
                    $value2 = getOssImageurl($value2);
                } else {
                    $value2 = Config::get('database.app_site') . '/Uploads/user/' . $order_info['user_id'] . '_photo_assay' . ($key2 + 1) . '.jpg';
                }
            }
        }
        unset($order_info['photo_assay']);

        // 联系人
        $contact_list = Db::table("hunuo_user_contact")->alias('c')
            //->join('hunuo_relation r', 'r.id=c.relation', 'LEFT')
            ->field('c.name,c.phone,c.relation')
            ->where('user_id', $order_info['user_id'])
            ->limit(4)
            ->select();
        $match_lang   = lang('phone_match');

        if (!empty($contact_list) && is_array($contact_list)) {
            foreach ($contact_list as $key4 => &$value4) {
                // 用联系人的手机号查询订单
                $temp_order = Db::name('hunuo_order_info o')
                    ->field('u.phone,u.user_id')
                    ->join('hunuo_users u', 'u.user_id = o.user_id')
                    ->where('u.phone', $value4['phone'])
                    ->find();

                if (!empty($temp_order)) {
                    // 存在  则查询联系人的订单 关联的联系人
                    $temp_contact = Db::name('hunuo_user_contact')
                        ->field('relation,phone')
                        ->where('user_id', $temp_order['user_id'])
                        ->select();

                    if (!empty($temp_contact) && is_array($temp_contact)) {
                        foreach ($temp_contact as $contact_key => $contact_value) {
                            // 关联的联系人中 有本人 则检测匹配关系
                            if ($contact_value['phone'] == $order_info['phone']) {
                                // 父 - 子女 || 母 - 子女
                                if ($value4['relation'] == 1 || $value4['relation'] == 2) {
                                    if ($contact_value['relation'] == 6) {
                                        $match_result = $match_lang['Y'];
                                    } else {
                                        $match_result = $match_lang['N'];
                                    }
                                } elseif ($value4['relation'] == $contact_value['relation']) {
                                    $match_result = $match_lang['Y'];
                                } else {
                                    $match_result = $match_lang['N'];
                                }
                            }
                        }
                    } else {
                        $match_result = $match_lang['E'];
                    }
                } else {
                    $match_result = $match_lang['E'];
                }

                $value4['relaname'] = lang('cllection_target_' . $value4['relation']);
            }
        }

        // 新增审核记录
        $review_log      = Db::table('hunuo_order_review_log orl')
            ->field('orl.*,u.real_name')
            ->join('system_admin_v2 u', 'u.admin_id = orl.admin_id')
            ->where('order_id', $order_info['order_id'])
            //->order('order_log_id', 'desc')
            ->select();
        $review_log_type = lang('order_handle_not_pass_info');
        if (!empty($review_log) && is_array($review_log)) {
            foreach ($review_log as $review_log_key => &$review_log_value) {
                if($review_log_value['refuse_type']==='-'){
                    $review_log_value['refuse_type'] = '-';
                }else{
                    $review_log_value['refuse_type'] = !empty($review_log_value['refuse_type']) ? $review_log_type[$review_log_value['refuse_type']] : '-';
                }
                $review_log_value['result_type'] = !empty($review_log_value['result_type']) ? $handle_result_type[$review_log_value['result_type']] : '-';
                $review_log_value['add_time']    = date('Y-m-d H:i:s', $review_log_value['add_time']);
            }
        }

        // 跟进时间 跟进人员 跟进结果 跟进记录
        $handle_log = Db::table('hunuo_order_handle_log')
            ->alias('ol')
            ->field('ol.*,u.real_name')
            ->where('ol.order_id', $order_info['order_id'])
            ->join('system_admin_v2 u', 'u.admin_id = ol.admin_id')
            ->order('ol.id desc')
            ->select();
        $phone_status = lang('phone_state');
        if (!empty($handle_log) && is_array($handle_log)) {
            foreach ($handle_log as $handle_log_key => &$handle_log_value) {
                $handle_log_value['flow_relation'] = lang('cllection_target_' . $handle_log_value['flow_relation']);
                $handle_log_value['add_time']      = date('Y-m-d', $handle_log_value['add_time']);
                $handle_log_value['phone_status']  = !empty($handle_log_value['phone_status']) ? $phone_status[$handle_log_value['phone_status']] : '-';
            }
        }
        // face++记录
        // 身份证照片 活体识别照片 最佳照片 假脸判断 请求时间 错误提示 匹配分数
        $face_log['add_time']      = $order_info['assay_time'] ? date('Y-m-d', $order_info['assay_time']) : '-';
        $face_log['face_image']    = $face_images;
        $face_log['image_ref1']    = getOssImageurl($order_info['face_card']);
        $face_log['match_score']   = $order_info['scores_assay'] ? $order_info['scores_assay'] : '-';

        //历史订单纪录,排除当前订单
        $history_order_list = Db::table("hunuo_order_info")->alias('o')
            ->field('o.order_no,o.lending_time,r.due_time,r.due_day')
            ->join('hunuo_order_repayment r', 'o.order_id = r.order_id', 'left')
            ->where(array('o.user_id' => $order_info['user_id'], 'o.add_time' => array('lt', $addtime)))
            ->order('o.order_id asc')
            ->select();
        foreach ($history_order_list as $k => $v) {
            $history_order_list[$k]['lending_time'] = $v['lending_time'] ? date('Y-m-d', $v['lending_time']) : '-';
            $history_order_list[$k]['repay_time']   = $v['due_time'] ? date('Y-m-d', $v['due_time']) : '-';
            unset($history_order_list[$k]['due_time']);
        }
        $lang_arr = [
            'order_info'       => lang('order_todo_info'),
            'order_review_log' => lang('order_handle_review_log'),
            'order_flow_log'   => lang('order_handle_flow_log'),
            'face_log'         => lang('order_todo_face_log'),
            'history_order'    => lang('history_order_list')
        ];
        $data     = [
            'order_info'         => $order_info,
            'handle_log'         => $handle_log,
            'contact_list'       => $contact_list,
            'face_log'           => $face_log,
            'history_order_list' => $history_order_list,
            'order_review_log'   => $review_log,
            'field'              => $lang_arr
        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 信审订单终审核
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function handle_change()
    {
        $post_data = request()->param();
        if (empty($post_data['order_no']) || empty($post_data['handle_state'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $order_info = Db::table('hunuo_order_info')
            ->field('order_id,order_no,order_status,audit_method,handle_state,user_id')
            ->where(array('order_no' => $post_data['order_no']))
            ->find();
        if ($order_info['order_status'] != 90 || $order_info['audit_method'] != 2) { // 4为初审通过
            return json(['code' => 201, 'message' => lang('order_status_fail'), 'data' => []]);
        }
        $handle_state = $post_data['handle_state'];
        if (empty($post_data['remark']) && $handle_state != 3) {
            return json(['code' => 400, 'message' => lang('error_4016'), 'data' => []]);
        }
        if ($handle_state == 1) {// 初审通过
            // 修改订单数据为初审通过
            Db::name('hunuo_order_info')
                ->where('order_id', $order_info['order_id'])
                ->update(['handle_state' => 4,'confirm_time'=>time()]);

            // $order_handle_user  = Db::table('hunuo_order_handle_user')->where('order_id', $order_info['order_id'])->find();
            // $order_no_pass_lang = lang('order_handle_not_pass_info');
            // if (!empty($order_handle_user)) {
            //     $save_data['order_state']   = 2;
            //     $save_data['change_time']   = time();
            //     $save_data['not_pass_info'] = $order_no_pass_lang[$post_data['flow_type']];
            //     Db::name('hunuo_order_handle_user')->where('order_id', $order_info['order_id'])->update($save_data);
            // } else {
            //     $add_data['admin_id']      = session('admin_id');
            //     $add_data['order_id']      = $order_info['order_id'];
            //     $add_data['order_state']   = 3;
            //     $add_data['add_time']      = time();
            //     $add_data['change_time']   = time();
            //     $add_data['not_pass_info'] = $order_no_pass_lang[$post_data['flow_type']];
            //     Db::name('hunuo_order_handle_user')->insert($add_data);
            // }  

            // 添加审核表记录
            $log_data = [
                'order_id'    => $order_info['order_id'],
                'admin_id'    => session('admin_id'),
                'add_time'    => time(),
                'refuse_type' => '-',
                'remark'      => $post_data['remark'],
                'result_type' => 1,//通过
                'review_type' => 1,//初审
            ];
            Db::name('hunuo_order_review_log')->insert($log_data);
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        }
        if ($handle_state == 2) {
            // 初审不通过
            if (empty($post_data['remark'])) {
                return json(['code' => 400, 'message' => lang('error_4015'), 'data' => []]);
            }

            //修改订单状态为不通过
            Db::name('hunuo_order_info')->where('order_id', $order_info['order_id'])->update(['order_status'=>110,'handle_state'=>3,'confirm_time'=>time()]);

            // $order_handle_user  = Db::table('hunuo_order_handle_user')->where('order_id', $order_info['order_id'])->find();
            // $order_no_pass_lang = lang('order_handle_not_pass_info');
            // if (!empty($order_handle_user)) {
            //     $save_data['order_state']   = 3;
            //     $save_data['change_time']   = time();
            //     $save_data['not_pass_info'] = $order_no_pass_lang[$post_data['flow_type']];

            //     Db::name('hunuo_order_handle_user')->where('order_id', $order_info['order_id'])->update($save_data);
            // } else {
            //     $add_data['admin_id']      = session('admin_id');
            //     $add_data['order_id']      = $order_info['order_id'];
            //     $add_data['order_state']   = 3;
            //     $add_data['add_time']      = time();
            //     $add_data['change_time']   = time();
            //     $add_data['not_pass_info'] = $order_no_pass_lang[$post_data['flow_type']];
            //     Db::name('hunuo_order_handle_user')->insert($add_data);
            // }

            // 添加审核表记录
            $log_data = [
                'order_id'    => $order_info['order_id'],
                'admin_id'    => session('admin_id'),
                'add_time'    => time(),
                'refuse_type' => $post_data['flow_type'],
                'remark'      => $post_data['remark'],
                'result_type' => 2,
                'review_type' => 1,//初审
            ];
            Db::name('hunuo_order_review_log')->insert($log_data);

            //审核失败调用api推送
            $key        = 'tupulian2018@andy';
            $rand_str   = substr(md5(microtime(true)), 0, 6);
            $time_stamp = time();
            $sign       = md5($key . $rand_str . $time_stamp . $order_info['user_id']);
            $data       = array(
                "user_id"   => $order_info['user_id'],
                "rand_str"   => $rand_str,
                "time_stamp" => $time_stamp,
                "sign"       => $sign,
            );
            $site_web   = Config::get('database.app_site_'.$this->env);
            $postUrl    = $site_web . "/index.php/loan/Pay/audit_failure";
            $response   = httpPost($postUrl, $data);
            $res        = json_decode($response, true);
            // 修改订单表在API端进行操作
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        }
        if ($handle_state == 3) {
            // 终审通过
            Db::name('hunuo_order_info')->where(['order_no' => $post_data['order_no']])->update(['order_status'=>100,'handle_state'=>2,'confirm_time'=>time()]);
            // echo Db::table('hunuo_order_info')->getlastsql();
            // exit;
            $company_code = Db::table('hunuo_users')->where(['user_id' => $order_info['user_id']])->value('company_code');
            $key        = 'tupulian2018@andy';
            $rand_str   = substr(md5(microtime(true)), 0, 6);
            $time_stamp = time();
            $sign       = md5($key . $rand_str . $time_stamp . $post_data['order_no']);
            $data       = array(
                "order_no"   => $post_data['order_no'],
                "rand_str"   => $rand_str,
                "time_stamp" => $time_stamp,
                "sign"       => $sign,
                'company_code'   => $company_code,
            );
            $site_web   = Config::get('database.app_site_'.$this->env);
            $postUrl    = $site_web . "/index.php/loan/Pay/agency_pay";
            $response   = httpPost($postUrl, $data);
            $res        = json_decode($response, true);
            // 信审通过后在API接口完成对数据的修改
            if ($res['status'] == 200) {
                // $order_handle_user  = Db::table('hunuo_order_handle_user_second')->where('order_id', $order_info['order_id'])->find();
                // if (!empty($order_handle_user)) {
                //     $save_data['order_state']   = 2;
                //     $save_data['change_time']   = time();
                //     $save_data['not_pass_info'] = '-';
                //     Db::name('hunuo_order_handle_user_second')->where('order_id', $order_info['order_id'])->update($save_data);
                // } else {
                //     $add_data['admin_id']      = session('admin_id');
                //     $add_data['order_id']      = $order_info['order_id'];
                //     $add_data['order_state']   = 2;
                //     $add_data['add_time']      = time();
                //     $add_data['change_time']   = time();
                //     $add_data['not_pass_info'] = '-';
                //     Db::name('hunuo_order_handle_user_second')->insert($add_data);
                // }
                // 添加审核表记录
                $log_data = [
                    'order_id'    => $order_info['order_id'],
                    'admin_id'    => session('admin_id'),
                    'add_time'    => time(),
                    'refuse_type' => '-',
                    'remark'      => $post_data['remark'],
                    'result_type' => 1,
                    'review_type' => 2,//终审
                ];
                Db::name('hunuo_order_review_log')->insert($log_data);
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => $res['message'], 'data' => []]);
            }
        }
        if ($handle_state == 4) { 
            // 终审不通过 退回处理
            // 修改订单数据为初审状态
            Db::name('hunuo_order_info')->where('order_id', $order_info['order_id'])->update(['handle_state' => 1,'order_status'=>90,'confirm_time'=>time()]);


            // $order_handle_user  = Db::table('hunuo_order_handle_user_second')->where('order_id', $order_info['order_id'])->find();
            // if (!empty($order_handle_user)) {
            //     $save_data['order_state']   = 3;
            //     $save_data['change_time']   = time();
            //     $save_data['not_pass_info'] = '-';
            //     Db::name('hunuo_order_handle_user_second')->where('order_id', $order_info['order_id'])->update($save_data);
            // } else {
            //     $add_data['admin_id']      = session('admin_id');
            //     $add_data['order_id']      = $order_info['order_id'];
            //     $add_data['order_state']   = 3;
            //     $add_data['add_time']      = time();
            //     $add_data['change_time']   = time();
            //     $add_data['not_pass_info'] = '-';
            //     Db::name('hunuo_order_handle_user_second')->insert($add_data);
            // }

            // 添加审核记录
            $log_data = [
                'order_id'    => $order_info['order_id'],
                'admin_id'    => session('admin_id'),
                'add_time'    => time(),
                'refuse_type' => '-',
                'remark'      => $post_data['remark'],
                'result_type' => 3,
                'review_type' => 2,//终审
            ];
            Db::name('hunuo_order_review_log')->insert($log_data);

            //审核失败调用api推送
            // $key        = 'tupulian2018@andy';
            // $rand_str   = substr(md5(microtime(true)), 0, 6);
            // $time_stamp = time();
            // $sign       = md5($key . $rand_str . $time_stamp . $order_info['user_id']);
            // $data       = array(
            //     "user_id"   => $order_info['user_id'],
            //     "rand_str"   => $rand_str,
            //     "time_stamp" => $time_stamp,
            //     "sign"       => $sign,
            // );
            // $site_web   = Config::get('database.app_site_'.$this->env);
            // $postUrl    = $site_web . "/index.php/loan/Pay/audit_failure";
            // $response   = httpPost($postUrl, $data);
            // $res        = json_decode($response, true);
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        }
    }

    /**
     * 添加信审跟进记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order_flow_log()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['order_no'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $order_info = Db::table('hunuo_order_info')
            ->field('order_id,order_no,order_status,audit_method,handle_state')
            ->where(array('order_no' => $post_data['order_no']))
            ->find();
        if ($order_info['order_status'] != 90 || $order_info['audit_method'] != 2) {
            return json(['code' => 201, 'message' => lang('order_status_fail'), 'data' => []]);
        }
        $add_data = [
            'admin_id'      => session('admin_id'),
            'order_id'      => $order_info['order_id'],
            'add_time'      => time(),
            'flow_desc'     => $post_data['flow_desc'],
            'flow_name'     => $post_data['flow_name'],
            'flow_phone'    => $post_data['flow_phone'],
            'flow_relation' => $post_data['flow_relation'],
            'phone_status'  => $post_data['phone_status'],
        ];
        $res      = Db::name('hunuo_order_handle_log')->insert($add_data);
        if (false !== $res) {
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        } else {
            return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
        }
    }

    /**
     * 信审订单批量分配
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function order_distribution_user()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['order_arr']) || empty($post_data['admin_arr'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }

        //必须要有信审主管或公司管理员权限
        $admin_class = get_admin_class();
        if (strpos('a'.$admin_class,'3') >= 1 || strpos('a'.$admin_class,'5') >= 1) {
            if((int)$post_data['type']===1){
                $table = 'hunuo_order_handle_user';//初审订单分配
            }else{
                $table = 'hunuo_order_handle_user_second';//终审订单分配
            }
            $order_arr_data = explode('-', $post_data['order_arr']);
            $admin_arr_data = explode('-', $post_data['admin_arr']);
            $temp_admin     = 0;
            if (!empty($order_arr_data) && is_array($order_arr_data)) {

                for ($i = 0; $i < count($order_arr_data); $i++) {
                    if (!isset($admin_arr_data[$temp_admin])) {
                        $temp_admin = 0;
                    }
                    $admin_info = $this->userinfo_by_userid($admin_arr_data[$temp_admin]);
                    $save_data[] = [
                        'admin_id' => $admin_arr_data[$temp_admin],
                        'order_id' => $order_arr_data[$i],
                        'add_time' => time(),
                        'admin'    => $admin_info['real_name'],
                    ];
                    $temp_admin++;
                }
            }
            //删除原有分配
            Db::table($table)->where('order_id', 'in', $order_arr_data)->delete();
            $res = Db::table($table)->insertAll($save_data);
            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }else{
            return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
        }

    }

    /**
     * 自动分配
     * @return \think\response\Json
     */
    public function auto_mode()
    {
        $type = request()->param('type');
        //必须要有信审主管或公司管理员权限
        $admin_class = get_admin_class();
        if (strpos('a'.$admin_class,'3') >= 1 || strpos('a'.$admin_class,'5') >= 1) {
            $map['r.company_id'] = session('admin_info.company_id');
            $map['u.status'] = 0;
            $map['r.status'] = 0;
            if((int)$type===1){
                $table = 'hunuo_order_handle_user';//初审订单分配
                $condition['handle_state'] = 1;
                $map['admin_class'] = ['in','1,3'];
            }else{
                $table = 'hunuo_order_handle_user_second';//终审订单分配
                $condition['handle_state'] = 4;
                $map['admin_class'] = ['in','7,3'];
            }

            $condition['company_code'] = session('admin_info.company_code');
            $condition['order_status'] = 90;
            $condition['audit_method'] = 2;

            // 获取已经分配的订单ID
            $order_ids = Db::table($table)->column('order_id');

            if (!empty($order_ids)) {
                $condition['order_id'] = ['not in', implode(',', $order_ids)];
            }
            $order_list = Db::name('hunuo_order_info')->where($condition)->fetchSql(false)->column('order_id');
            if (empty($order_list)) {
                return json(['code' => 201, 'message' => lang('auto_mode_no_order'), 'data' => []]);
            }
            $handle_user_list = Db::table('system_admin_v2')->alias('u')
                ->join('system_admin_role_v2 r', 'r.role_id = u.role_id')
                ->where($map)
                ->fetchSql(false)
                ->column('admin_id');
            if (empty($handle_user_list)) {
                return json(['code' => 201, 'message' => lang('auto_mode_no_user'), 'data' => []]);
            }
            $temp_admin = 0;
            if (!empty($order_list) && is_array($order_list)) {

                for ($i = 0; $i < count($order_list); $i++) {
                    if (!isset($handle_user_list[$temp_admin])) {
                        $temp_admin = 0;
                    }
                    //zhaoguangshuai修改开始
                    $countNum = count($handle_user_list);
                    $temp_admin = mt_rand(0, $countNum - 1);
                    //修改结束
                    $admin_info = $this->userinfo_by_userid($handle_user_list[$temp_admin]);
                    $save_data[] = [
                        'admin_id' => $handle_user_list[$temp_admin],
                        'order_id' => $order_list[$i],
                        'add_time' => time(),
                        'admin'    => $admin_info['real_name'],
                    ];
                    $temp_admin++;
                }
            }

            if (!empty($save_data)) {
                $res = Db::table($table)->insertAll($save_data);
                if (false !== $res) {
                    return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
                } else {
                    return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
                }
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }else{
            return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
        }

    }

    /**
     * 用户短信记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sms_list()
    {
        $order_id = request()->post('order_id');

        $list = Db::name('hunuo_user_sms')
            ->where('order_id', $order_id)
            ->order('send_time', 'desc')
            ->select();

        $data['sms_list']  = $list;
        $data['sms_count'] = count($list);
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 用户通话记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function record_list()
    {
        $order_id = request()->post('order_id');

        $list = Db::name('hunuo_user_record')
            ->where('order_id', $order_id)
            ->order('record_time', 'desc')
            ->select();

        $record_lang = lang('record_type');
        if(!empty($list) && is_array($list)){
            foreach ($list as $key => &$value)
            {
                $value['record_type'] = $record_lang[$value['record_type']];
            }
        }

        $data['record_list']  = $list;



        $data['record_count'] = count($list);
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }


}
