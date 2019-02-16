<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 14:21
 */

namespace app\admin\controller;

use think\Config;
use think\Db;

class Collection extends Base
{
    private $company_code;
    private $is_collector;

    public function __construct()
    {
        parent::__construct();

        $company            = Db::table("system_company")->where(array("cp_id" => session("company_id")))->find();
        $this->company_code = $company["cp_code"];

        $role_type = getAdminRoleType(2);
        if ($role_type == 2) {
            $this->is_collector = 1;
        } else {
            $this->is_collector = 0;
        }
        //判断级别
        // dump([session('admin_info.role_id')]);
        // exit;
        $role_type1                = Db::table('system_admin_role_v2')->where('role_id', 'in', [session('admin_info.role_id')])->column('admin_class');
        $this->is_collector_leader = in_array(4, $role_type1);
    }

    /**
     *
     * 所有催收员
     */
    public function collection_user()
    {
        $request   = request();
        $post_data = $request->param();
        if (isset($post_data["company_id"])) {
            $company_id = $post_data["company_id"];
        } else {
            $company_id = session("company_id");
        }
        $list = Db::table('system_admin_role_relation_v2 rr')
            ->join('system_admin_role_v2 r', 'r.role_id = rr.role_id')
            ->join('system_admin_v2 u', 'u.admin_id = rr.admin_id')
            ->where('r.company_id', $company_id)
            ->where('r.admin_class', 'in', '2,4')
            ->where('u.status', 0)
            ->where('r.status', 0)
            ->select();

        $data['list']  = $list;
        $data['field'] = [
            'list_no'   => lang('cllection_list_no'),
            'real_name' => lang('cllection_real_name'),
            'role_name' => lang('cllection_role_name'),

        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     *
     * 催收反馈
     */
    public function collection_feed()
    {
        //催收反馈
        $data["follow_feed"] = [
            "title" => lang("cllection_collection_feedback"),
            "name"  => "follow_feed",
            "value" => [
                //'0'   => lang("cllection_followup_feed_0"),
                '181' => lang("cllection_followup_feed_181"),
                '182' => lang("cllection_followup_feed_182"),
                '183' => lang("cllection_followup_feed_183"),
                '184' => lang("cllection_followup_feed_184"),
                '185' => lang("cllection_followup_feed_185"),
                '186' => lang("cllection_followup_feed_186"),
                '187' => lang("cllection_followup_feed_187"),
                '188' => lang("cllection_followup_feed_188"),
                '189' => lang("cllection_followup_feed_189"),
                '190' => lang("cllection_followup_feed_190"),
                '191' => lang("cllection_followup_feed_191"),
            ],

        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     *
     * 所有催收
     */
    public function all()
    {
        $request                 = request();
        $post_data               = $request->param();
        $condition               = array();
        $condition['i.order_no'] = array('neq', "");
        $condition['d.order_no'] = array('neq', "");
        $condition['r.due_day']  = array('neq', 0);

        //判断是否子公司
        if (!empty($this->company_code)) {
            $condition['i.company_code'] = array('eq', $this->company_code);
        }
        //判断级别

        $is_collector = $this->is_collector;

        if ($is_collector) {
            $condition['d.current_collector'] = array('eq', session('admin_id'));
        }

        if (isset($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['i.company_code'] = $company_code;
        } else {
            if (session('admin_info.company_id') == 0) {
                //$condition['i.company_code'] = array('exp', 'is not null');
            } else {
                $condition['i.company_code'] = session('admin_info.company_code');
            }
        }

        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'due_day') {
                $order_condition = 'r.due_day ';
            }
            if ($post_data['order_field'] == 'due_time') {
                $order_condition = 'r.due_time ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'r.due_time desc,r.repay_id desc';
        }


        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $start_time              = strtotime($time_data['start_time']);
            $end_time                = strtotime($time_data['end_time']);
            $condition['r.due_time'] = array(array('egt', $start_time), array('elt', $end_time));
        }

        //催收时间
        if (!empty($post_data['follow_date'])) {
            //$time_data               = getSearchData($post_data['follow_date']);
            $follow_data                  = explode(' ', $post_data['follow_date']);
            $start_time                   = $follow_data[0] . ' 00:00:00';
            $end_time                     = $follow_data[2] . ' 23:59:59';
            $condition['f.operator_time'] = array(array('egt', $start_time), array('elt', $end_time));
        }
        if (!empty($post_data['admin_id'])) {
            $condition['d.current_collector'] = $post_data['admin_id'];
        }

        if (!empty($post_data['collection_feedback'])) {
            $_sql      = 'SELECT case_id,collection_feedback FROM ( SELECT * FROM daihou_case_followup_record ORDER BY id DESC ) t GROUP BY t.case_id having  t.collection_feedback = ' . $post_data['collection_feedback'];
            $case_list = Db::query($_sql);
            if (!empty($case_list) && is_array($case_list)) {
                foreach ($case_list as $key3 => $value3) {
                    $case_ids[] = $value3['case_id'];
                }
                $case_ids = implode(',', $case_ids);
            }
            $condition['d.id'] = array('in', isset($case_ids) ? $case_ids : []);
        }

        ////订单 姓名 订单号查询
        $condition1 = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string'] = trimall($post_data['search_string']);
            if (!$is_collector) {
                //主管级别模糊查询
                $condition['i.name|i.phone|i.order_no'] = array('like', "%{$post_data['search_string']}%");
            } else {
                //催收专员首先模糊查询自己逾期订单
                //unset($condition['d.current_collector']);
                $condition['i.name|i.phone|i.order_no'] = array('like', "%{$post_data['search_string']}%");

                //催收专员然后精准查询别人逾期订单
                $condition1                              = $condition;
                $condition1['d.current_collector']       = array('neq', session('admin_id'));
                $condition1['i.name|i.phone|i.order_no'] = array('eq', $post_data['search_string']);
            }

        }

        //提早派单查询
        $time = (time() + 2 * 24 * 60 * 60);

        //催收专员模糊查询自己的未逾期订单
        $where = $condition;
        unset($where['r.due_day']);
        $where['r.bill_status'] = array('eq', 1);
        if (!$is_collector && empty($post_data['admin_id'])) {
            $where['d.current_collector'] = ['neq', 0];
        }

        if (!empty($end_time)) {
            if ($start_time < time() && $end_time > time() && $end_time < $time) {
                $where['r.due_time'] = ['between', [time(), $end_time]];
            } else if ($start_time > time() && $end_time > time() && $end_time < $time) {
                $where['r.due_time'] = ['between', [$start_time, $end_time]];
            } else if ($start_time > time() && $end_time > time() && $end_time > $time) {
                $where['r.due_time'] = ['between', [$start_time, $time]];
            } else {
                $where = array();
            }

        } else {
            $where['r.due_time'] = ['elt', $time];
        }

        //催收专员精准查询自己的未逾期订单
        $where1 = $condition1;
        if (!empty($where1)) {
            unset($where['r.due_day']);
            $where1['r.bill_status'] = ['eq', 1];
            if (!empty($post_data['search_string'])) {
                $where1['d.current_collector'] = ['neq', 0];
            }
            if (!empty($end_time)) {
                if ($start_time < time() && $end_time > time() && $end_time < $time) {
                    $where1['r.due_time'] = ['between', [time(), $end_time]];
                } else if ($start_time > time() && $end_time > time() && $end_time < $time) {
                    $where1['r.due_time'] = ['between', [$start_time, $end_time]];
                } else if ($start_time > time() && $end_time > time() && $end_time > $time) {
                    $where1['r.due_time'] = ['between', [$start_time, $time]];
                } else {
                    $where1 = [];
                }

            } else {
                $where1['r.due_time'] = ['elt', $time];
            }

        }
        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }
        $case_list = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where(
                function ($query) use ($condition, $condition1) {
                    $query->where($condition)->whereOr(
                        function ($query) use ($condition1) {
                            $query->where($condition1);
                        }
                    );
                }
            )
            ->whereOr(
                function ($query) use ($where, $where1) {
                    $query->where($where)->whereOr(
                        function ($query) use ($where1) {
                            $query->where($where1);
                        }
                    );
                }
            )
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            //->join('daihou_case_followup_record f', 'f.case_id = d.id', 'left')

            ->join("(SELECT max(operator_time) as operator_time,case_id from daihou_case_followup_record group by case_id ) f", 'f.case_id = d.id', 'left')
            ->field('i.order_status,r.order_no,i.name,i.order_status,i.phone,r.due_day,r.repay_amount,r.amount,r.due_time,r.over_fee,a.real_name,d.id,d.current_collector,d.id as case_id,f.operator_time')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();

        if (!empty($case_list) && is_array($case_list)) {
            foreach ($case_list as $key => $value) {
                $value['collection_feedback'] = Db::name('daihou_case_followup_record')->where('case_id', $value['case_id'])->order('id desc')->value('collection_feedback');
                if ($value['order_status'] == "180") {
                    if (empty($value['current_collector'])) {
                        $case_status = "cllection_case_status_0";
                    } else {
                        $case_status = "cllection_case_status_180";
                    }
                } else if ($value['order_status'] == "200") {
                    $case_status = "cllection_case_status_200";
                } else {

                    $case_status = "cllection_case_status_" . $value['order_status'];
                }
                $data['list'] [$key] = [
                    'order_status'     => $value['order_status'],
                    'case_id'          => $value['id'],
                    'order_no'         => $value['order_no'],
                    'real_name'        => $value['name'],
                    'phone'            => $value['phone'],
                    'due_day'          => $value['due_day'],
                    'repay_amount'     => $value['repay_amount'] + $value['due_day'] * $value['over_fee'] * $value['amount'],
                    'due_time'         => date('Y-m-d', $value['due_time']),
                    'case_status'      => lang($case_status),
                    'followup_feed'    => lang('cllection_followup_feed_' . $value['collection_feedback']),
                    'case_follow_name' => empty($value['real_name']) ? "-" : $value['real_name'],
                    'follow_time'      => $value['operator_time'] ? date('Y-m-d', strtotime($value['operator_time'])) : '-',
                ];
            }
        }

        //$data['post_data'] = $post_data;
        //$data['condition'] = $condition;
        // $data['cllection_user'] = $this->collection_user();

        $case_list_count = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where(
                function ($query) use ($condition, $condition1) {
                    $query->where($condition)->whereOr(
                        function ($query) use ($condition1) {
                            $query->where($condition1);
                        }
                    );
                }
            )
            ->whereOr(
                function ($query) use ($where, $where1) {
                    $query->where($where)->whereOr(
                        function ($query) use ($where1) {
                            $query->where($where1);
                        }
                    );
                }
            )
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            ->join("(SELECT max(operator_time) as operator_time,case_id from daihou_case_followup_record group by case_id ) f", 'f.case_id = d.id', 'left')
            ->fetchSql(false)
            ->count();

        $data['page']  = [
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $case_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($case_list_count / $this->limit),
        ];
        $data['field'] = [
            //'is_collector'          => $is_collector,
            'order_no'          => lang('cllection_order_no'),
            'real_name'         => lang('cllection_real_name'),
            'phone'             => lang('cllection_phone'),
            'due_day'           => lang('cllection_due_day'),
            'repay_amount'      => lang('cllection_repay_amount'),
            'due_time'          => lang('cllection_due_time'),
            'collection_status' => lang('cllection_collection_status'),
            'followup_feed'     => lang('cllection_followup_feed'),
            'case_follow_name'  => lang('cllection_case_follow_name'),
            'follow_time'       => lang('follow_time'),
        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 催收中
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cllection_going()
    {
        $request                    = request();
        $post_data                  = $request->param();
        $condition                  = array();
        $condition['i.order_no']    = array('neq', "");
        $condition['d.order_no']    = array('neq', "");
        $condition['r.due_day']     = array('neq', 0);
        $condition['r.bill_status'] = array('neq', 2);

        //判断是否子公司
        if (!empty($this->company_code)) {
            $condition['i.company_code'] = array('eq', $this->company_code);
        }

        //判断级别
        $is_collector = $this->is_collector;

        if ($is_collector) {
            $condition['d.current_collector'] = array('eq', session('admin_id'));
        }

        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'due_day') {
                $order_condition = 'r.due_day ';
            }
            if ($post_data['order_field'] == 'due_time') {
                $order_condition = 'r.due_time ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'r.due_time desc,r.repay_id desc';
        }

        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $start_time              = strtotime($time_data['start_time']);
            $end_time                = strtotime($time_data['end_time']);
            $condition['r.due_time'] = array(array('egt', strtotime($time_data['start_time'])), array('elt', strtotime($time_data['end_time'])));
        }
        //催收时间
        if (!empty($post_data['follow_date'])) {
            //$time_data               = getSearchData($post_data['follow_date']);
            $follow_data                  = explode(' ', $post_data['follow_date']);
            $start_time                   = $follow_data[0] . ' 00:00:00';
            $end_time                     = $follow_data[2] . ' 23:59:59';
            $condition['f.operator_time'] = array(array('egt', $start_time), array('elt', $end_time));
        }
        if (!empty($post_data['admin_id'])) {

            $condition['d.current_collector'] = $post_data['admin_id'];

        }
        if (!empty($post_data['collection_feedback'])) {
            $_sql      = 'SELECT case_id,collection_feedback FROM ( SELECT * FROM daihou_case_followup_record ORDER BY id DESC ) t GROUP BY t.case_id having  t.collection_feedback = ' . $post_data['collection_feedback'];
            $case_list = Db::query($_sql);
            if (!empty($case_list) && is_array($case_list)) {
                foreach ($case_list as $key3 => $value3) {
                    $case_ids[] = $value3['case_id'];
                }
                $case_ids = implode(',', $case_ids);
            }
            $condition['d.id'] = array('in', isset($case_ids) ? $case_ids : []);

        }
        if (isset($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['i.company_code'] = $company_code;
        } else {
            if (session('admin_info.company_id') == 0) {
                //$condition['i.company_code'] = array('exp', 'is not null');
            } else {
                $condition['i.company_code'] = session('admin_info.company_code');
            }
        }
        //订单 姓名 订单号查询
        $condition1 = array();
        if (!empty($post_data['search_string'])) {
            $post_data['search_string'] = trimall($post_data['search_string']);
            if (!$is_collector) {
                //主管级别模糊查询
                $condition['i.name|i.phone|i.order_no'] = array('like', "%{$post_data['search_string']}%");
            } else {
                //催收专员首先模糊查询自己逾期订单
                //unset($condition['d.current_collector']);
                $condition['i.name|i.phone|i.order_no'] = array('like', "%{$post_data['search_string']}%");

                //催收专员然后精准查询别人逾期订单
                $condition1                              = $condition;
                $condition1['d.current_collector']       = array('neq', session('admin_id'));
                $condition1['i.name|i.phone|i.order_no'] = array('eq', $post_data['search_string']);
            }

        }

        //提早派单查询
        $time = (time() + 2 * 24 * 60 * 60);

        //催收专员模糊查询自己的未逾期订单
        $where = $condition;
        unset($where['r.due_day']);
        $where['r.bill_status'] = array('eq', 1);
        if (!$is_collector && empty($post_data['admin_id'])) {
            $where['d.current_collector'] = ['neq', 0];
        }

        if (!empty($end_time)) {
            if ($start_time < time() && $end_time > time() && $end_time < $time) {
                $where['r.due_time'] = ['between', [time(), $end_time]];
            } else if ($start_time > time() && $end_time > time() && $end_time < $time) {
                $where['r.due_time'] = ['between', [$start_time, $end_time]];
            } else if ($start_time > time() && $end_time > time() && $end_time > $time) {
                $where['r.due_time'] = ['between', [$start_time, $time]];
            } else {
                $where = array();
            }

        } else {
            $where['r.due_time'] = ['elt', $time];
        }

        //催收专员精准查询自己的未逾期订单
        $where1 = $condition1;
        if (!empty($where1)) {
            unset($where['r.due_day']);
            $where1['r.bill_status'] = ['eq', 1];
            if (!empty($post_data['search_string'])) {
                $where1['d.current_collector'] = ['neq', 0];
            }
            if (!empty($end_time)) {
                if ($start_time < time() && $end_time > time() && $end_time < $time) {
                    $where1['r.due_time'] = ['between', [time(), $end_time]];
                } else if ($start_time > time() && $end_time > time() && $end_time < $time) {
                    $where1['r.due_time'] = ['between', [$start_time, $end_time]];
                } else if ($start_time > time() && $end_time > time() && $end_time > $time) {
                    $where1['r.due_time'] = ['between', [$start_time, $time]];
                } else {
                    $where1 = [];
                }

            } else {
                $where1['r.due_time'] = ['elt', $time];
            }
        }
        $due_day_where = [];
        if (!empty($post_data['s'])) {
            switch ($post_data['s']) {
                case 1:
                    $due_day_where['r.due_day'] = ['between', [1, 10]];
                    break;
                case 2:
                    $due_day_where['r.due_day'] = ['between', [11, 30]];
                    break;
                case 3:
                    $due_day_where['r.due_day'] = ['>', 30];
            }
        }
        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }
        $case_list = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where(
                function ($query) use ($condition, $condition1) {
                    $query->where($condition)->whereOr(
                        function ($query) use ($condition1) {
                            $query->where($condition1);
                        }
                    );
                }
            )
            ->whereOr(
                function ($query) use ($where, $where1) {
                    $query->where($where)->whereOr(
                        function ($query) use ($where1) {
                            $query->where($where1);
                        }
                    );
                }
            )
            ->where($due_day_where)
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            ->join("(SELECT max(operator_time) as operator_time,case_id from daihou_case_followup_record group by case_id ) f", 'f.case_id = d.id', 'left')
            //->join("(select * from ( SELECT * from daihou_case_followup_record ORDER BY id desc ) temp group by temp.case_id order by temp.id desc ) f", 'f.case_id = d.id', 'left')
            ->field('i.order_status,r.order_no,i.name,i.order_status,i.phone,r.due_day,r.repay_amount,r.amount,r.due_time,r.over_fee,a.real_name,d.id as case_id,d.current_collector,f.operator_time')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();
        // dump($case_list);
        // exit;    
        if (!empty($case_list) && is_array($case_list)) {
            foreach ($case_list as $key => $value) {
                //f.collection_feedback,f.operator_time,a
                $followup_record              = Db::table('daihou_case_followup_record')->field('operator_time,collection_feedback')->where('case_id', $value['case_id'])->order('id desc')->find();
                $value['operator_time']       = $followup_record['operator_time'];
                $value['collection_feedback'] = $followup_record['collection_feedback'];
                $data['list'] [$key]          = [
                    'order_status'     => $value['order_status'],
                    'case_id'          => $value['case_id'],
                    'order_no'         => $value['order_no'],
                    'real_name'        => $value['name'],
                    'phone'            => $value['phone'],
                    'due_day'          => $value['due_day'],
                    'repay_amount'     => $value['repay_amount'] + $value['due_day'] * $value['over_fee'] * $value['amount'],
                    'due_time'         => date('Y-m-d', $value['due_time']),
                    'followup_feed'    => lang('cllection_followup_feed_' . $value['collection_feedback']),   //催收状态
                    "operator_time"    => $value['operator_time'] ? explode(' ', $value['operator_time'])[0] : '-',                                                  //跟进时间
                    'case_follow_name' => empty($value['real_name']) ? "-" : $value['real_name'],
                ];


            }
        }

        //$data['post_data'] = $post_data;
        //$data['condition'] = $condition;
        // $data['cllection_user'] = $this->collection_user();
        $case_list_count = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where($due_day_where)
            ->where(
                function ($query) use ($condition, $condition1) {
                    $query->where($condition)->whereOr(
                        function ($query) use ($condition1) {
                            $query->where($condition1);
                        }
                    );
                }
            )
            ->whereOr(
                function ($query) use ($where, $where1) {
                    $query->where($where)->whereOr(
                        function ($query) use ($where1) {
                            $query->where($where1);
                        }
                    );
                }
            )
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            ->join("(SELECT max(operator_time) as operator_time,case_id from daihou_case_followup_record group by case_id ) f", 'f.case_id = d.id', 'left')
            ->count();
        $data['page']    = [
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $case_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($case_list_count / $this->limit),
        ];
        $data['field']   = [
            'order_no'         => lang('cllection_order_no'),
            'real_name'        => lang('cllection_real_name'),
            'phone'            => lang('cllection_phone'),
            'due_day'          => lang('cllection_due_day'),
            'repay_amount'     => lang('cllection_repay_amount'),
            'due_time'         => lang('cllection_due_time'),
            'followup_feed'    => lang('cllection_followup_feed'),
            'operator_time'    => lang('cllection_operator_time'),
            'case_follow_name' => lang('cllection_case_follow_name'),
        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     *
     * 催收详情
     */
    public function details()
    {
        $request                 = request();
        $post_data               = $request->param();
        $order_no                = $post_data["order_no"];
        $condition['r.order_no'] = $order_no;
        //$condition['re.reduction_status'] = 1;
        //订单信息
        $order_data = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where($condition)
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info c', 'c.order_no = r.order_no', 'left')
            //->join('daihou_case_reduction re', 're.order_no = r.order_no', 'left')
            ->field('i.order_no,i.name,i.order_status,i.application_amount,i.approval_amount,i.phone,i.phone,i.user_id,r.due_day,r.repay_amount,r.amount,r.due_time,r.over_fee,c.id')
            ->fetchSql(false)
            ->find();
        if (empty($order_data["order_no"])) {
            return json(['code' => 403, 'message' => 'order_no is null']);
        }

        $user_data = Db::table("hunuo_order_info")->alias('o')
            ->join('hunuo_order_repayment r', 'r.order_id = o.order_id', 'left')
            ->join('hunuo_bankcard b', 'b.bankcard_id=o.bankcard_id', 'left')
            ->join('hunuo_users u', 'u.user_id = o.user_id', 'left')
            ->join('hunuo_region c', 'c.region_id = u.city', 'left')
            ->join('hunuo_order_handle_user t', 't.order_id = o.order_id', 'left')
            ->field('o.order_id,u.birthday,o.user_id,o.user_id,o.order_no,o.name,o.phone,o.application_amount,o.bankcard_id,b.bankcard_name,r.paid_amount,b.card_type,b.card_num,u.is_marrey,b.bankcard_name,b.card_num,o.add_time,o.application_term,o.refuse_time,o.lending_time,o.end_time,o.order_status,o.handle_state,r.success_time,u.phone,u.sex,u.name,u.idcode,c.region_name as city,u.address,u.profession,u.industry,o.gps_address,u.education,u.company,u.company_add,u.company_tel,u.credit_img,u.tax_card,u.security_card,u.family_card,u.staff_card,u.salary_card,u.work_prove,u.photo_assay,t.change_time,u.face_card,u.scores_assay')
            ->where('o.order_no', $post_data['order_no'])
            ->find();

        //联系人信息
        $contact_data = Db::table('hunuo_user_contact')
            ->where(array("user_id" => $order_data["user_id"]))
            ->field('relation,name,phone,phone_status')
            ->fetchSql(false)
            ->select();

        // //费用入账信息
        $repay_data = Db::table('hunuo_order_repayment')->where(["order_no" => $order_no])->field('paid_amount as price,success_time')->fetchSql(false)->find();

        $repay_data["success_time"] = empty($repay_data["success_time"]) ? "" : date("Y-m-d H:i:s",$repay_data["success_time"]);

        //案件跟进信息
        $follow_data = Db::table('daihou_case_followup_record')
            ->alias('f')
            ->where(array("case_id" => $order_data["id"]))
            ->field('f.operator_time,f.type,f.target,f.target_name,f.contact_phone,f.contact_state,f.collection_feedback,f.content,f.operator_name')
            ->fetchSql(false)
            ->order('operator_time desc')
            ->select();

        $over_fee        = $order_data["due_day"] * $order_data["over_fee"] * $order_data["application_amount"];
        $reduction_info = Db::table('daihou_case_reduction')->where(['order_no'=>$order_no,'reduction_status'=>1])->find();
        $data['order']   = [
            'order_id'      => $user_data['order_id'],
            "order_no"      => $order_no,                                                              //订单号
            "principal"     => $order_data["application_amount"],                                    //本金
            "interest"      => $order_data["repay_amount"] - $order_data["application_amount"],      //利息
            "repay_amount"  => $order_data["repay_amount"] + $over_fee,                                         //应还金额
            "over_interest" => $over_fee,//罚息
            "due_day"       => $order_data["due_day"],                                              //逾期天数
            "reduction_fee" => isset($reduction_info["reduction_fee"])?$reduction_info["reduction_fee"]:0,//减免金额
            'price'         => $repay_data['price'],
            'success_time'  => $repay_data['success_time'],
        ];
        $profession_type = lang('profession_type');
        $industry_type   = lang('industry_type');
        //根据身份证号码计算年龄
        $birthday = $user_data['birthday'];                                                                    //贷款用户生日
        $today    = strtotime('today');                                                                          //获得今日的时间戳
        $diff     = floor(($today - $birthday) / 86400 / 365);                                                          //得到两个日期相差的大体年数
        $age      = strtotime($birthday . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;                                //strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        //$data['user'] =$user_data;
        if($user_data["is_marrey"] == 1){
            $is_marrey_str = '已婚';
        }elseif ($user_data["is_marrey"] == 2){
            $is_marrey_str = '离异';
        }else{
            $is_marrey_str = '未婚';
        }
        $data['user'] = [
            "user_id"       => empty($order_data["user_id"]) ? "" : $order_data["user_id"],                            //姓名
            "name"          => empty($user_data["name"]) ? "" : $user_data["name"],                            //姓名
            "sex"           => lang("cllection_sex_" . $user_data["sex"]),              //性别
            "idcode"        => empty($user_data["idcode"]) ? "" : $user_data["idcode"],                         //身份证
            "card_type"     => empty($user_data["card_type"]) ? "" : $user_data["card_type"],                      //账号类型
            "bankcard_name" => empty($user_data["bankcard_name"]) ? "" : $user_data["bankcard_name"],                  //开户银行
            "card_num"      => empty($user_data["card_num"]) ? "" : $user_data["card_num"],                       //银行卡号
            "is_marrey"     => $is_marrey_str, //婚姻状况
            "age"           => empty($age) ? "" : $age,                         //邮件
            "education"     => lang("cllection_education_" . $user_data["education"]),//教育程度
            "phone"         => empty($user_data["phone"]) ? "" : $user_data["phone"],//手机号码
            "address"       => empty($user_data["address"]) ? "" : $user_data["address"],//现居住地
            "industry"      => empty($user_data["industry"]) ? "" : $industry_type[$user_data["industry"]],//行业类别
            "profession"    => empty($user_data['profession']) ? "" : $profession_type[$user_data['profession']],//职业类别
            "gps_address"   => empty($user_data["gps_address"]) ? "" : $user_data["gps_address"],//GPS地址
            "company"       => empty($user_data["company"]) ? "" : $user_data["company"],//公司名称
            "company_add"   => empty($user_data["company_add"]) ? "" : $user_data["company_add"],//公司地址
            "company_tel"   => empty($user_data["company_tel"]) ? "" : $user_data["company_tel"],//公司电话
            "credit_img"    => empty($user_data["credit_img"]) ? "" : getOssImageurl($user_data["credit_img"]),//征信照片
            "tax_card"      => empty($user_data["tax_card"]) ? "" : getOssImageurl($user_data["tax_card"]),//税卡
            "security_card" => empty($user_data["security_card"]) ? "" : getOssImageurl($user_data["security_card"]),//社保卡
            "family_card"   => empty($user_data["family_card"]) ? "" : getOssImageurl($user_data["family_card"]),//家庭卡
            "staff_card"    => empty($user_data["staff_card"]) ? "" : getOssImageurl($user_data["staff_card"]),//员工卡
            "salary_card"   => empty($user_data["salary_card"]) ? "" : getOssImageurl($user_data["salary_card"]),//工资卡
            "work_prove"    => empty($user_data["work_prove"]) ? "" : getOssImageurl($user_data["work_prove"]),//在职证明
            "face_card"     => empty($user_data["face_card"]) ? "" : getOssImageurl($user_data["face_card"]),//身份证照
            "city"          => empty($user_data['city'])?"":$user_data['city'],
            //""=>empty($user_data["photo_assay"])?"":$user_data["photo_assay"],//最佳图


        ];

        $face_log['image_ref1']    = $user_data['face_card']?getOssImageurl($user_data['face_card']):'-';
        //exit;
        $face_images = explode(',', $user_data['photo_assay']);
        $face_log['image_best']    = $face_images[0]? getOssImageurl($face_images[0]) :'-';
        $data['face_log'] = $face_log;

        $data['contact'] = $contact_data;
        foreach ($contact_data as $contact_key => $contact_value) {

            $data['contact'][$contact_key + 1]["relation"]     = lang("cllection_target_" . $contact_value["relation"]);           //跟进方式
            $data['contact'][$contact_key + 1]["phone"]        = $contact_value["phone"];           //跟进方式
            $data['contact'][$contact_key + 1]["name"]         = $contact_value["name"];           //跟进方式
            $data['contact'][$contact_key + 1]["phone_status"] = lang("cllection_contact_state_" . $contact_value["phone_status"]);        //跟进方式

        }
        $data['contact'][0]["relation"]     = lang("my_relation");
        $data['contact'][0]["phone"]        = empty($user_data["phone"]) ? "" : $user_data["phone"];
        $data['contact'][0]["name"]         = empty($user_data["name"]) ? "" : $user_data["name"];
        $data['contact'][0]["phone_status"] = lang("cllection_contact_state_1");

        foreach ($follow_data as $key => $value) {
            $data['follow'][$key] = [
                "operator_time"       => $value["operator_time"],                            //跟进时间
                "follow_type"         => lang("cllection_follow_type_" . $value["type"]),              //跟进方式
                "target"              => lang("cllection_target_" . $value["target"]),               //催收对象
                //"target"              => lang("target"),               //催收对象
                "target_name"         => $value["target_name"],                           //姓名
                "contact_phone"       => $value["contact_phone"],                         //联系电话
                "contact_state"       => lang("cllection_contact_state_" . $value["contact_state"]),//电话状态
                "collection_feedback" => lang("cllection_followup_feed_" . $value["collection_feedback"]), //催收反馈
                "content"             => $value["content"],                             //跟进记录
                "operator_name"       => $value["operator_name"],                       //跟进人员

            ];

        }

        $data['field'] = [
            'title'   => lang('cllection_case_details'),
            'user'    => [
                "name"          => lang('cllection_name'),        //姓名
                "sex"           => lang("cllection_sex"),        //性别
                "idcode"        => lang('cllection_idcode'),          //身份证
                "card_type"     => lang('cllection_card_type'),//账号类型
                "bankcard_name" => lang('cllection_bankcard_name'),//开户银行
                "card_num"      => lang('cllection_card_num'),//银行卡号
                "is_marrey"     => lang('cllection_is_marrey'), //婚姻状况
                "email"         => lang('cllection_email'),//邮件
                "education"     => lang('cllection_education'),//教育程度
            ],
            'contact' => [
                "relation"      => lang('cllection_relation'),
                "name"          => lang('cllection_name'),
                "phone"         => lang('cllection_phone'),
                "contact_state" => lang('cllection_contact_state'),

            ],
            'order'   => [

                "principal"     => lang('cllection_principal'),              //本金
                "interest"      => lang('cllection_interest'),            //利息
                "repay_amount"  => lang('cllection_repay_amount'),        //应还金额
                "over_interest" => lang('cllection_over_interest'),      //罚息
                "due_day"       => lang('cllection_due_day'),          //逾期天数
                "repay_data"    => lang('cllection_repay_data')        //入账信息
            ],
            'fee'     => [

                "price"    => lang('cllection_price'),
                "add_time" => lang('cllection_add_time'),


            ],
            'follow'  => [
                "operator_time"       => lang('cllection_operator_time'),          //跟进时间
                "follow_type"         => lang('cllection_follow_type'),           //跟进方式
                "target"              => lang('cllection_target'),               //催收对象
                "target_name"         => lang('cllection_target_name'),          //姓名
                "contact_phone"       => lang('cllection_contact_phone'),        //联系电话
                "contact_state"       => lang('cllection_contact_state'),        //电话状态
                "collection_feedback" => lang('cllection_collection_feedback'),  //催收反馈
                "content"             => lang('cllection_content'),              //跟进记录
                "operator_name"       => lang('cllection_operator_name'),        //跟进人员
            ],

        ];

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }


    /**
     * 通讯录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function phone_list()
    {
        $order_id = request()->param('order_id');
        $user_id  = request()->param('user_id');
        // 搜索条件
        $search_string = request()->param('search_string');
        if (!empty($search_string)) {
            $condition['name|phone'] = ['like', '%' . trimall($search_string) . '%'];
        }
        // 兼容老订单
        $condition['order_id'] = $order_id;
        $condition['user_id'] = $user_id;

        $list = $data = Db::table('hunuo_phone_list')
            ->field('name,phone')
            ->where($condition)
            ->select();

        if(empty($list)){
            unset($condition['order_id']);
            $list = $data = Db::table('hunuo_phone_list')
                ->field('name,phone')
                ->where($condition)
                ->select();
        }

        $phone_from = lang('phone_from');

        $user_info = Db::name('hunuo_users')
            ->field('phone,name')
            ->where('user_id', $user_id)
            ->find();

        $tab1 = Db::name('hunuo_phone_list')
            ->field('name,phone')
            ->where($condition)
            ->where('phone', $user_info['phone'])
            ->find();


        $contact_list = Db::name('hunuo_user_contact')
            ->field('phone,name')
            ->where('user_id', $user_id)
            ->select();

        $user_name = $user_phone = [];
        if (!empty($contact_list) && is_array($contact_list)) {
            foreach ($contact_list as $key => $value) {
                $user_name[$key]  = $value['name'];
                $user_phone[$key] = $value['phone'];
            }
        }

        if(!empty($user_phone[0])){
            $tab2 = Db::name('hunuo_phone_list')
                ->field('name,phone')
                ->where($condition)
                ->where('phone', $user_phone[0])
                ->find();
        }

        if(!empty($user_phone[1])){
            $tab3 = Db::name('hunuo_phone_list')
                ->field('name,phone')
                ->where($condition)
                ->where('phone', $user_phone[1])
                ->find();
        }

        if(!empty($user_phone[2])){
            $tab4 = Db::name('hunuo_phone_list')
                ->field('name,phone')
                ->where($condition)
                ->where('phone', $user_phone[2])
                ->find();
        }

        if(!empty($user_phone[3])){
            $tab5 = Db::name('hunuo_phone_list')
                ->field('name,phone')
                ->where($condition)
                ->where('phone', $user_phone[3])
                ->find();
        }

        $data = [
            'tab6' => [
                'list' => $list
            ],
        ];

        if(!empty($tab1)){
            $data = [
                'tab1' => [ //本人
                    'list' => [
                        [
                            'from'  => $phone_from[1],
                            'name'  => $user_info['name'],
                            'phone' => $user_info['phone'],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab1['name']) ? $tab1['name'] : '',
                            'phone' => !empty($tab1['phone']) ? $tab1['phone'] : '',
                        ]
                    ]
                ],
                'tab6' => [
                    'list' => $list
                ],
            ];
        }

        if(!empty($tab2)){
            $data = [
                'tab1' => [ //本人
                    'list' => [
                        [
                            'from'  => $phone_from[1],
                            'name'  => $user_info['name'],
                            'phone' => $user_info['phone'],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab1['name']) ? $tab1['name'] : '',
                            'phone' => !empty($tab1['phone']) ? $tab1['phone'] : '',
                        ]
                    ]
                ],
                'tab2' => [ // 联系人1
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[0],
                            'phone' => $user_phone[0],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab2['name']) ? $tab2['name'] : '',
                            'phone' => !empty($tab2['phone']) ? $tab2['phone'] : '',
                        ]
                    ]
                ],
                'tab6' => [
                    'list' => $list
                ],
            ];
        }

        if(!empty($tab3)){
            $data = [
                'tab1' => [ //本人
                    'list' => [
                        [
                            'from'  => $phone_from[1],
                            'name'  => $user_info['name'],
                            'phone' => $user_info['phone'],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab1['name']) ? $tab1['name'] : '',
                            'phone' => !empty($tab1['phone']) ? $tab1['phone'] : '',
                        ]
                    ]
                ],
                'tab2' => [ // 联系人1
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[0],
                            'phone' => $user_phone[0],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab2['name']) ? $tab2['name'] : '',
                            'phone' => !empty($tab2['phone']) ? $tab2['phone'] : '',
                        ]
                    ]
                ],
                'tab3' => [
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[1],
                            'phone' => $user_phone[1],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab3['name']) ? $tab3['name'] : '',
                            'phone' => !empty($tab3['phone']) ? $tab3['phone'] : '',
                        ]
                    ]
                ],
                'tab6' => [
                    'list' => $list
                ],
            ];
        }

        if(!empty($tab4)){
            $data = [
                'tab1' => [ //本人
                    'list' => [
                        [
                            'from'  => $phone_from[1],
                            'name'  => $user_info['name'],
                            'phone' => $user_info['phone'],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab1['name']) ? $tab1['name'] : '',
                            'phone' => !empty($tab1['phone']) ? $tab1['phone'] : '',
                        ]
                    ]
                ],
                'tab2' => [ // 联系人1
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[0],
                            'phone' => $user_phone[0],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab2['name']) ? $tab2['name'] : '',
                            'phone' => !empty($tab2['phone']) ? $tab2['phone'] : '',
                        ]
                    ]
                ],
                'tab3' => [
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[1],
                            'phone' => $user_phone[1],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab3['name']) ? $tab3['name'] : '',
                            'phone' => !empty($tab3['phone']) ? $tab3['phone'] : '',
                        ]
                    ]
                ],
                'tab4' => [
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[2],
                            'phone' => $user_phone[2],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab4['name']) ? $tab4['name'] : '',
                            'phone' => !empty($tab4['phone']) ? $tab4['phone'] : '',
                        ]
                    ]
                ],
                'tab6' => [
                    'list' => $list
                ],
            ];
        }

        if(!empty($tab5)){
            $data = [
                'tab1' => [ //本人
                    'list' => [
                        [
                            'from'  => $phone_from[1],
                            'name'  => $user_info['name'],
                            'phone' => $user_info['phone'],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab1['name']) ? $tab1['name'] : '',
                            'phone' => !empty($tab1['phone']) ? $tab1['phone'] : '',
                        ]
                    ]
                ],
                'tab2' => [ // 联系人1
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[0],
                            'phone' => $user_phone[0],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab2['name']) ? $tab2['name'] : '',
                            'phone' => !empty($tab2['phone']) ? $tab2['phone'] : '',
                        ]
                    ]
                ],
                'tab3' => [
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[1],
                            'phone' => $user_phone[1],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab3['name']) ? $tab3['name'] : '',
                            'phone' => !empty($tab3['phone']) ? $tab3['phone'] : '',
                        ]
                    ]
                ],
                'tab4' => [
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[2],
                            'phone' => $user_phone[2],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab4['name']) ? $tab4['name'] : '',
                            'phone' => !empty($tab4['phone']) ? $tab4['phone'] : '',
                        ]
                    ]
                ],
                'tab5' => [
                    'list' => [
                        [
                            'from'  => $phone_from[2],
                            'name'  => $user_name[3],
                            'phone' => $user_phone[3],
                        ],
                        [
                            'from'  => $phone_from[3],
                            'name'  => !empty($tab5['name']) ? $tab5['name'] : '',
                            'phone' => !empty($tab5['phone']) ? $tab5['phone'] : '',
                        ]
                    ]
                ],
                'tab6' => [
                    'list' => $list
                ],
            ];
        }

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }

    /**
     *
     * 催收记录添加
     */
    public function record_param()
    {
        $data["name"] = [
            "title" => lang("cllection_target_name"),
            "name"  => "target_name",
        ];
        //催收对象
        $data["target"] = [
            "title" => lang("cllection_target"),
            "name"  => "target",
            "value" => [
                //"0"  => lang("cllection_target_0"),
                "1"  => lang("cllection_target_1"),
                "2"  => lang("cllection_target_2"),
                "3"  => lang("cllection_target_3"),
                "4"  => lang("cllection_target_4"),
                "5"  => lang("cllection_target_5"),
                "6"  => lang("cllection_target_6"),
                "7"  => lang("cllection_target_7"),
                "8"  => lang("cllection_target_8"),
                "9"  => lang("cllection_target_9"),
                "10" => lang("cllection_target_10"),
            ],

        ];
        //联系电话
        $data["contact_phone"] = [
            "title" => lang("cllection_contact_phone"),
            "name"  => "target_name",
        ];

        //电话状态
        $data["contact_state"] = [
            "title" => lang("cllection_contact_state"),
            "name"  => "contact_state",
            "value" => [
                //'0' => lang("cllection_contact_state_0"),
                '1' => lang("cllection_contact_state_1"),
                '2' => lang("cllection_contact_state_2"),
                '3' => lang("cllection_contact_state_3"),
                '4' => lang("cllection_contact_state_4"),
                '5' => lang("cllection_contact_state_5"),
                //'6' => lang("cllection_contact_state_6"),
                //'7' => lang("cllection_contact_state_7"),
            ],

        ];

        //跟进方式
        $data["follow_type"] = [
            "title" => lang("cllection_follow_type"),
            "name"  => "follow_type",
            "value" => [
                //'0'  => lang("cllection_follow_type_0"),
                '81' => lang("cllection_follow_type_81"),
                '82' => lang("cllection_follow_type_82"),
                '83' => lang("cllection_follow_type_83"),
                '84' => lang("cllection_follow_type_84"),
                '85' => lang("cllection_follow_type_85"),
            ],

        ];

        //催收反馈
        $data["follow_feed"] = [
            "title" => lang("cllection_collection_feedback"),
            "name"  => "follow_feed",
            "value" => [
                //'0'   => lang("cllection_followup_feed_0"),
                '181' => lang("cllection_followup_feed_181"),
                '182' => lang("cllection_followup_feed_182"),
                '183' => lang("cllection_followup_feed_183"),
                '184' => lang("cllection_followup_feed_184"),
                '185' => lang("cllection_followup_feed_185"),
                '186' => lang("cllection_followup_feed_186"),
                '187' => lang("cllection_followup_feed_187"),
                '188' => lang("cllection_followup_feed_188"),
                '189' => lang("cllection_followup_feed_189"),
                '190' => lang("cllection_followup_feed_190"),
                '191' => lang("cllection_followup_feed_191"),
            ],

        ];
        //跟进记录
        $data["content"] = [
            "title" => lang("cllection_content"),
            "name"  => "target_content",
        ];

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }

    public function record_add()
    {
        $request   = request();
        $post_data = $request->param();

        $order_no = $post_data["order_no"];

        $f_data = Db::table("daihou_case_info")
            ->alias("c")
            ->join("hunuo_order_info i", "i.order_no=c.order_no", "left")
            ->where(array("c.order_no" => $order_no))
            ->field("c.id,i.order_status")
            ->find();


        if ($f_data["order_status"] == 200) {
            return json(['code' => 402, 'message' => lang('close_status')]);
        }
        $f_name = Db::table('system_admin_v2')->where(array('admin_id' => session('admin_id')))->field('real_name')->find();

        $data = [
            'case_id'             => $f_data["id"],                                       //案件ID
            'target'              => $post_data["target"],                               //跟进对象关系
            // 'type'                => $post_data["follow_type"],                         //跟进方式
            'contact_state'       => $post_data["contact_state"],                      //催收反馈状态
            'collection_feedback' => $post_data["follow_feed"],                       //催收反馈类型
            'content'             => $post_data["content"],                          //跟进记录
            'target_name'         => $post_data["target_name"],                     //跟进对象名字
            'contact_phone'       => $post_data["contact_phone"],                  //跟进对象电话
            'personal_id'         => $post_data["user_id"],                       //用户ID
            'operator_name'       => $f_name["real_name"],                       //当前操作人
            'operator_time'       => date("Y-m-d H:i:s"),                       //操作时间
        ];

        $code    = 200;
        $message = lang('success');
        foreach ($data as $k => $v) {
            if (empty($v)) {
                $code    = 402;
                $message = lang("cllection_input") . lang("cllection_" . $k);
            }

        }
        if ($code == 200) {

            // 2018年7月9日10:21:50 新增承诺还款
            if ($post_data['follow_feed'] == '181') {
                // 查询最新2天内承诺还款信息
                $has_log = Db::table('hunuo_order_collection_log')
                    ->where('order_no', $post_data['order_no'])
                    ->where('add_time', 'egt', time() - 3600 * 24 * 2)
                    ->order('id desc')
                    ->find();
                if (empty($has_log)) {
                    $add_data = array(
                        'admin_id' => session('admin_id'),
                        'order_no' => $post_data['order_no'],
                        'add_time' => time(),
                        'status'   => 1,
                    );
                    Db::name('hunuo_order_collection_log')->insert($add_data);

                } else {
                    // 2天内该订单已存在承诺还款 不做处理
                }
            }

            Db::name("daihou_case_followup_record")->insert($data);
            // 2018年7月17日17:20:41 调整这里写入数据错误
            Db::name("daihou_case_info")->where(["id" => $f_data["id"]])->update(["followup_back" => $post_data["follow_feed"], 'followup_time' => date('Y-m-d H:i:s')]);
        }
        return json(['code' => $code, 'message' => $message, 'data' => $data]);

    }

    public function reduction_param()
    {
        $request             = request();
        $post_data           = $request->param();
        $company_code        = $this->company_code;
        $is_collector_leader = $this->is_collector_leader;

        $order_no = $post_data["order_no"];

        //数据查找
        $order_data = Db::table('hunuo_order_info')
            ->alias("i")
            ->where(["i.order_no" => $order_no])
            ->join("hunuo_order_repayment r", "r.order_no=i.order_no", "left")
            ->field("i.user_id,i.name,i.phone,i.order_status,r.due_day,r.over_fee,i.application_amount,r.repay_amount,r.overdue_fee")
            ->find();


        if ($order_data["order_status"] == 200) {
            return json(['code' => 402, 'message' => lang('close_status')]);
        }

        if (!empty($company_code)) {
            $data = [
                "title"                  => lang("reduction_title"),
                "reduction_order_no"     => lang("reduction_order_no"),
                "reduction_user_name"    => lang("reduction_user_name"),
                "reduction_repay_amount" => lang("reduction_repay_amount"),
                "reduction_over_fee"     => lang("reduction_over_fee"),
                "reduction_fee"          => lang("reduction_fee"),
                "reduction_fee_remark"   => lang("reduction_fee_remark"),
                "reduction_remark"       => lang("reduction_remark"),

            ];

            return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
        } else {
            return json(['code' => 500, 'message' => lang('no_can')]);
        }
    }

    //添加减免
    public function reduction_add()
    {
        $request   = request();
        $post_data = $request->param();
        $order_no  = $post_data["order_no"];

        //数据查找
        $order_data = Db::table('hunuo_order_info')
            ->alias("i")
            ->where(["i.order_no" => $order_no])
            ->join("hunuo_order_repayment r", "r.order_no=i.order_no", "left")
            ->field("i.user_id,i.order_status,i.name,i.phone,r.due_day,r.over_fee,i.application_amount,r.repay_amount,r.pay_amount,r.overdue_fee")
            ->find();

        if ($order_data["order_status"] == 200) {
            return json(['code' => 402, 'message' => lang('close_status')]);
        }

        $f_data = Db::table('daihou_case_reduction')
            ->where(["order_no" => $order_no, "reduction_status" => 1])
            ->find();
        //确保通过的减免一个订单只有一个
        if (!empty($f_data)) {
            return json(['code' => 402, 'message' => lang('has_reduction')]);
        }
        $over_fee = $order_data["due_day"] * $order_data["over_fee"] * $order_data["application_amount"];
        //$over_fee=$order_data["overdue_fee"];
        //减免数据组装
        $data = [
            "order_no"         => $order_no,
            "reduction_fee"    => $post_data["reduction_fee"],    //减免金额
            "reduction_remark" => $post_data["reduction_remark"],  //减免备注
            "apply_date"       => time(),                         //申请时间
            "user_id"          => $order_data["user_id"],         //用户ID
            "user_name"        => $order_data["name"],             //用户名
            "repay_amount"     => $order_data["repay_amount"] + $over_fee,   //应还总额
            "over_fee"         => $over_fee,                        //罚息
            "admin_id"         => session('admin_id'),
        ];

        $code    = 200;
        $message = lang('success');

        //判断数据是否为空
        foreach ($data as $k => $v) {
            if (empty($v)) {
                $code    = 402;
                $message = lang("cllection_input") . lang("cllection_" . $k);
            }

        }

        //记录数据组装
        $f_data = Db::table("daihou_case_info")
            ->where(array("order_no" => $order_no))
            ->field("id")
            ->find();
        $f_name = Db::table('system_admin_v2')->where(array('admin_id' => session('admin_id')))->field('real_name')->find();

        $record_data = [
            'case_id'             => $f_data["id"],                          //案件ID
            'target'              => 10,                                     //跟进对象关系
            'type'                => 81,                                     //跟进方式
            'contact_state'       => 1,                                      //电话状态
            'collection_feedback' => 181,                                    //催收反馈类型
            'content'             => lang("reduction_record") . $post_data["reduction_fee"],      //跟进记录
            'target_name'         => $order_data["name"],                     //跟进对象名字
            'contact_phone'       => $order_data["phone"],                    //跟进对象电话
            'personal_id'         => $order_data["user_id"],                   //用户ID
            'operator_name'       => $f_name["real_name"],                     //当前操作人
            'operator_time'       => date("Y-m-d H:i:s"),                     //操作时间
        ];


        //异常判断
        if ($code == 200) {
            $f_order = Db::table('daihou_case_reduction')->where(array("order_no" => $order_no, "reduction_status" => array("in", ["0", "1"])))->find();
            if (empty($f_order)) {
                $result = Db::name('daihou_case_reduction')->insert($data);
                Db::name("daihou_case_followup_record")->insert($record_data);
                if ($result) {
                    return json(['code' => 200, 'message' => $message]);
                } else {
                    $message = lang("inser_fail");

                    return json(['code' => 402, 'message' => $message]);
                }

            } else {
                $message = lang("reduction_apply_has");
                return json(['code' => 402, 'message' => $message]);
            }

        } else {
            return json(['code' => 402, 'message' => $message, 'data' => $data]);
        }
    }


    public function reduction_list()
    {
        $request   = request();
        $post_data = $request->param();
        $condition = array();

        if (!empty($this->company_code)) {
            $condition['i.company_code'] = array('eq', $this->company_code);
        }

        $is_collector = $this->is_collector;

        if ($is_collector) {
            $condition['d.current_collector'] = array('eq', session('admin_id'));
        }

        //总账号筛选公司
        if (!empty($post_data['company_id'])) {
            $f_code                      = Db::table('system_company')
                ->where(array("cp_id" => $post_data['company_id']))
                ->field("cp_code")
                ->find();
            $condition['i.company_code'] = array('eq', $f_code['cp_code']);
        }
        //条件筛选
        if (!empty($post_data['search_string'])) {
            $post_data['search_string']          = trimall($post_data['search_string']);
            $condition['r.user_name|r.order_no'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data                 = getSearchData($post_data['date']);
            $condition['r.apply_date'] = array(array('egt', strtotime($time_data['start_time'])), array('elt', strtotime($time_data['end_time'])));
        }
        if (!empty($post_data['admin_id'])) {

            $condition['r.admin_id'] = $post_data['admin_id'];

        }

        if (isset($post_data['company_id']) && !empty($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['i.company_code'] = $company_code;
        } else {
            if (session('admin_info.company_id') == 0) {
                //$condition['i.company_code'] = array('exp', 'is not null');
            } else {
                $condition['i.company_code'] = session('admin_info.company_code');
            }
        }

        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'apply_date') {
                $order_condition = 'r.apply_date ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'r.apply_date desc';
        }

        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }

        //数据查找
        $reduction_list = Db::table('daihou_case_reduction')
            ->alias("r")
            ->where($condition)
            ->join("system_admin_v2 a", "a.admin_id=r.admin_id", "left")
            ->join("hunuo_order_info i", "i.order_no=r.order_no", "left")
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->field("r.reduction_id,r.order_no,r.user_name,r.repay_amount,r.reduction_fee,d.current_collector,r.apply_date,a.real_name,r.reduction_status,r.reduction_id")
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();

        foreach ($reduction_list as $key => $value) {
            $reduction_list[$key]["reduction_status"] = lang("reduction_status_" . $value["reduction_status"]);
            $reduction_list[$key]["apply_date"]       = date("Y-m-d", $value["apply_date"]);
            $reduction_list[$key]["apply_name"]       = $reduction_list[$key]["real_name"];
            unset($reduction_list[$key]["real_name"]);

        }
        //$data["condition"]=$condition;

        //数据拼装
        $data["list"] = $reduction_list;

        //页码
        $reduction_list_count = Db::table('daihou_case_reduction')
            ->alias("r")
            ->where($condition)
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join("hunuo_order_info i", "i.order_no=r.order_no", "left")
            ->count();
        $data['page']         = array(
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $reduction_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($reduction_list_count / $this->limit),
        );

        $data['field'] = [
            'user_name'        => lang('reduction_user_name'),
            'repay_amount'     => lang('reduction_repay_amount'),
            'reduction_fee'    => lang('reduction_fee'),
            'apply_date'       => lang('reduction_apply_date'),
            'apply_name'       => lang('reduction_apply_name'),
            'reduction_status' => lang('reduction_status'),
        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }


    public function reduction_details()
    {
        $request   = request();
        $post_data = $request->param();

        $reduction_id                = $post_data["reduction_id"];
        $condition['r.reduction_id'] = array('eq', $reduction_id);

        $data_list = Db::table('daihou_case_reduction')
            ->alias("r")
            ->where($condition)
            ->join("hunuo_order_info i", "i.order_no=r.order_no", "left")
            ->join("system_admin_v2 a", "a.admin_id=r.admin_id", "left")
            ->field("r.reduction_id,r.order_no,r.user_name,i.repay_amount,i.application_amount,i.approval_amount,r.over_fee,r.reduction_fee,r.apply_date,a.real_name,r.reduction_remark,r.reduction_status")
            ->find();

        $data['list'] = [
            'reduction_id'       => $data_list["reduction_id"],                                   //订单
            'order_no'           => $data_list["order_no"],                                   //订单
            'user_name'          => $data_list["user_name"],                                   //用户名
            'repay_amount'       => $data_list["repay_amount"] + $data_list["over_fee"],         //应还总额
            'application_amount' => $data_list["application_amount"],                             //本金
            'interest'           => $data_list["repay_amount"] - $data_list["application_amount"],             //利息
            'over_fee'           => $data_list["over_fee"],             //罚息
            'reduction_fee'      => $data_list["reduction_fee"],         //减免金额
            'apply_date'         => date("Y-m-d H:i:s", $data_list["apply_date"]),//申请日期
            'apply_name'         => $data_list["real_name"],//申请人
            'reduction_remark'   => $data_list["reduction_remark"],  //减免备注
            'reduction_status'   => $data_list["reduction_status"],  //审批状态
        ];

        $data["reduction_status"] = [
            lang("reduction_status_0") => 0,
            lang("reduction_status_1") => 1,
            lang("reduction_status_2") => 2,
        ];

        $data['field'] = [
            'order_no'           => lang('reduction_order_no'),
            'user_name'          => lang('reduction_user_name'),
            'repay_amount'       => lang('reduction_repay_amount'),
            'application_amount' => lang('reduction_application_amount'),
            'interest'           => lang('reduction_interest'),
            'over_fee'           => lang('reduction_over_fee'),
            'reduction_fee'      => lang('reduction_fee'),
            'apply_date'         => lang('reduction_apply_date'),
            'apply_name'         => lang('reduction_apply_name'),
            'reduction_remark'   => lang('reduction_remark'),
            'reduction_status'   => lang('reduction_status'),
        ];
        if ($data_list) {
            return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
        } else {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => $data]);
        }
    }


    public function reduction_save()
    {
        $request                   = request();
        $post_data                 = $request->param();
        $reduction_id              = $post_data["reduction_id"];
        $post_data["confirm_date"] = time();
        if ($reduction_id) {
            $r = Db::name('daihou_case_reduction')->where(["reduction_id" => $reduction_id])->update($post_data);
        }
        if (false !== $r) {
            return json(['code' => 200, 'message' => lang('success')]);
        } else {
            return json(['code' => 403, 'message' => lang('error_4008')]);
        }

    }

    //已经还款案件跟进记录
    public function closed_record()
    {

        $request                 = request();
        $post_data               = $request->param();
        $condition               = array();
        $condition['c.order_no'] = array('eq', $post_data["order_no"]);
        if (empty($post_data["order_no"])) {

            return json(['code' => 402, 'message' => lang('error_4002')]);

        }

        //案件跟进信息
        $follow_data = Db::table('daihou_case_followup_record')
            ->alias('f')
            ->join("daihou_case_info c", "c.id=f.case_id", "left")
            ->where($condition)
            ->field('f.case_id,f.operator_time,f.type,f.target,f.target_name,f.contact_phone,f.contact_state,f.collection_feedback,f.content,f.operator_name')
            ->fetchSql(false)
            ->select();


        foreach ($follow_data as $key => $value) {
            $data['follow'][$key] = [
                "case_id"             => $value["case_id"],                            //id
                "operator_time"       => $value["operator_time"],                            //跟进时间
                "follow_type"         => lang("cllection_follow_type_" . $value["type"]),              //跟进方式
                "target"              => lang("cllection_target_" . $value["target"]),               //催收对象
                //"target"              => lang("target"),               //催收对象
                "target_name"         => $value["target_name"],                           //姓名
                "contact_phone"       => $value["contact_phone"],                         //联系电话
                "contact_state"       => lang("cllection_contact_state_" . $value["contact_state"]),//电话状态
                "collection_feedback" => lang("cllection_followup_feed_" . $value["collection_feedback"]), //催收反馈
                "content"             => $value["content"],                             //跟进记录
                "operator_name"       => $value["operator_name"],                       //跟进人员

            ];

        }


        $data['field'] = [
            'title'  => lang('cllection_case_details'),
            'follow' => [
                "operator_time"       => lang('cllection_operator_time'),          //跟进时间
                "follow_type"         => lang('cllection_follow_type'),           //跟进方式
                "target"              => lang('cllection_target'),               //催收对象
                "target_name"         => lang('cllection_target_name'),          //姓名
                "contact_phone"       => lang('cllection_contact_phone'),        //联系电话
                "contact_state"       => lang('cllection_contact_state'),        //电话状态
                "collection_feedback" => lang('cllection_collection_feedback'),  //催收反馈
                "content"             => lang('cllection_content'),              //跟进记录
                "operator_name"       => lang('cllection_operator_name'),        //跟进人员
            ]

        ];

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }

    //已经还款案件列表
    public function closed_list()
    {

        $request                    = request();
        $post_data                  = $request->param();
        $condition                  = array();
        $condition['i.order_no']    = array('neq', "");
        $condition['d.order_no']    = array('neq', "");
        $condition['r.bill_status'] = array('eq', 2);
        $condition['r.due_day']     = array('neq', 0);

        //判断是否子公司
        if (!empty($this->company_code)) {
            $condition['i.company_code'] = array('eq', $this->company_code);
        }
        //判断级别
        $is_collector = $this->is_collector;

        if ($is_collector) {
            $condition['d.current_collector'] = array('eq', session('admin_id'));
        }

        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'due_day') {
                $order_condition = 'r.due_day ';
            }
            if ($post_data['order_field'] == 'due_time') {
                $order_condition = 'r.due_time ';
            }
            if ($post_data['order_field'] == 'success_time') {
                $order_condition = 'r.success_time ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'r.success_time desc';
        }

        if (!empty($post_data['search_string'])) {
            $post_data['search_string']             = trimall($post_data['search_string']);
            $condition['i.name|i.phone|i.order_no'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data                   = getSearchData($post_data['date']);
            $condition['r.success_time'] = array(array('egt', strtotime($time_data['start_time'])), array('elt', strtotime($time_data['end_time'])));
        }
        if (!empty($post_data['due_date'])) {
            $time_data2              = getSearchData($post_data['due_date']);
            $condition['r.due_time'] = array(array('egt', strtotime($time_data2['start_time'])), array('elt', strtotime($time_data2['end_time'])));
        }
        if (!empty($post_data['admin_id'])) {

            $condition['d.current_collector'] = $post_data['admin_id'];

        }

        if (isset($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['i.company_code'] = $company_code;
        } else {
            if (session('admin_info.company_id') == 0) {
                //$condition['i.company_code'] = array('exp', 'is not null');
            } else {
                $condition['i.company_code'] = session('admin_info.company_code');
            }
        }
        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }

        $case_list = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where($condition)
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            //->join('(select * from daihou_case_followup_record group by case_id order by id desc) f', 'f.case_id = d.id', 'left')
            ->field('r.order_no,i.name,i.phone,r.due_day,r.repay_amount,r.amount,r.paid_amount,r.due_time,r.over_fee,a.real_name,r.success_time,r.due_time')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();

        if (!empty($case_list) && is_array($case_list)) {
            foreach ($case_list as $key => $value) {

                $data['list'] [$key] = [
                    'order_no'         => $value['order_no'],
                    'real_name'        => $value['name'],
                    'phone'            => $value['phone'],
                    'due_day'          => $value['due_day'],
                    'repay_amount'     => $value['repay_amount'] + $value['due_day'] * $value['over_fee'] * $value['amount'],
                    'paid_amount'      => $value['paid_amount'],
                    'success_time'     => date('Y-m-d', $value['success_time']),
                    'case_follow_name' => empty($value['real_name']) ? "-" : $value['real_name'],
                    'due_time'         => date('Y-m-d', $value['due_time'])
                ];


            }
        }

        $case_list_count = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where($condition)
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            ->join('daihou_case_followup_record f', 'f.id = d.id', 'left')
            ->count();

        $data['page']  = [
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $case_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($case_list_count / $this->limit),
        ];
        $data['field'] = array(
            'order_no'         => lang('cllection_order_no'),
            'real_name'        => lang('cllection_real_name'),
            'phone'            => lang('cllection_phone'),
            'due_day'          => lang('cllection_due_day'),
            'repay_amount'     => lang('cllection_repay_amount'),
            'success_time'     => lang('cllection_success_time'),
            'case_follow_name' => lang('cllection_case_follow_name'),
            'paid_amount'      => lang('paid_amount'),
        );

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }


    //提早派单
    public function advance_case_list()
    {

        $request                    = request();
        $post_data                  = $request->param();
        $condition                  = array();
        $condition['r.bill_status'] = array('eq', 1);
        $condition['r.due_time']    = array('lt', (time() + 2 * 24 * 60 * 60));
        $condition['d.current_collector'] = array('eq', 0);//还没有分配催收员
        //判断是否子公司
        if (!empty($this->company_code)) {
            $condition['i.company_code'] = array('eq', $this->company_code);
        }
        //判断级别
        $is_collector = $this->is_collector;

        if ($is_collector) {
            $condition['d.current_collector'] = array('eq', session('admin_id'));
        }

        // 新增 排序
        if (!empty($post_data['order_field'])) {
            if ($post_data['order_field'] == 'due_time') {
                $order_condition = 'r.due_time ';
            }
            $order_condition .= (isset($post_data['order_sort'])) ? $post_data['order_sort'] : ' ';
        }
        if (empty($order_condition)) {
            $order_condition = 'r.due_time desc';
        }

        if (!empty($post_data['search_string'])) {
            $post_data['search_string']             = trimall($post_data['search_string']);
            $condition['i.name|i.phone|i.order_no'] = array('like', "%{$post_data['search_string']}%");
        }
        if (!empty($post_data['date'])) {
            $time_data               = getSearchData($post_data['date']);
            $condition['r.due_time'] = array(array('egt', strtotime($time_data['start_time'])), array('elt', strtotime($time_data['end_time'])));
        }
        if (!empty($post_data['admin_id'])) {

            $condition['d.current_collector'] = $post_data['admin_id'];

        }

        if (isset($post_data['company_id'])) {
            $company_code                = getCompanyCode($post_data['company_id']);
            $condition['i.company_code'] = $company_code;
        } else {
            if (session('admin_info.company_id') == 0) {
                //$condition['i.company_code'] = array('exp', 'is not null');
            } else {
                $condition['i.company_code'] = session('admin_info.company_code');
            }
        }

        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }
        $case_list = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where($condition)
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            //->join('(select * from daihou_case_followup_record group by case_id order by id desc) f', 'f.case_id = d.id', 'left')
            ->field('r.order_no,i.name,i.phone,r.repay_amount,r.due_time,r.due_day,r.over_fee,r.amount,a.real_name')
            ->limit(((isset($post_data['page']) ? $post_data['page'] : 1) - 1) * $this->limit, $this->limit)
            ->order($order_condition)
            ->fetchSql(false)
            ->select();

        if (!empty($case_list) && is_array($case_list)) {
            foreach ($case_list as $key => $value) {

                $data['list'] [$key] = [
                    'order_no'         => $value['order_no'],
                    'real_name'        => $value['name'],
                    'phone'            => $value['phone'],
                    'repay_amount'     => $value['repay_amount'] + $value['due_day'] * $value['over_fee'] * $value['amount'],
                    'due_time'         => empty($value['due_time']) ? "-" : date('Y-m-d', $value['due_time']),
                    'case_follow_name' => empty($value['real_name']) ? "-" : $value['real_name'],
                ];
            }
        }
        $case_list_count = Db::table('hunuo_order_repayment')
            ->alias('r')
            ->where($condition)
            ->join('hunuo_order_info i', 'i.order_no = r.order_no', 'left')
            ->join('daihou_case_info d', 'd.order_no = r.order_no', 'left')
            ->join('system_admin_v2 a', 'a.admin_id = d.current_collector', 'left')
            ->join('daihou_case_followup_record f', 'f.id = d.id', 'left')
            ->count();

        $data['page']  = [
            'page'  => isset($post_data['page']) ? $post_data['page'] : 1,
            'count' => $case_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($case_list_count / $this->limit),
        ];
        $data['field'] = array(
            'order_no'         => lang('cllection_order_no'),
            'real_name'        => lang('cllection_real_name'),
            'phone'            => lang('cllection_phone'),
            'repay_amount'     => lang('cllection_repay_amount'),
            'due_time'         => lang('cllection_due_time'),
            'case_follow_name' => lang('cllection_case_follow_name')
        );

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }

    /**
     * 承诺还款信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function collection_log()
    {
        $post_data['date'] = request()->post('promise_time');
        if (empty($post_data['date'])) {
            $condition['date_str'] = array('elt', date('Y-m-d'));
        } else {
            $time_data             = getSearchData($post_data['date']);
            $condition['date_str'] = array('between', array(date('Y-m-d', strtotime($time_data['start_time'])), date('Y-m-d', strtotime($time_data['end_time']))));
        }
        if (session('admin_info.company_id') == '0') {
            // 总公司
            if ($post_data['company_id'] == '0' || !empty($post_data['company_id'])) {
                $company_code = getCompanyCode($post_data['company_id']);
            } else {
                $company_code = '5aab2f49c3ec9';
            }
        } else {
            // 分公司
            $company_code = session('admin_info.company_code');
        }
        $condition['u.company_code'] = $company_code;
        $list                        = Db::table('report_daihou_commitment c')
            ->field('u.real_name,sum(order_cnt) as order_cnt_sum,sum(order_ontime) as order_ontime_sum,sum(order_undue) as order_undue_sum,sum(order_due) as order_due_sum')
            ->join('system_admin_v2 u', 'u.admin_id = c.admin_id')
            ->where($condition)
            ->group('c.admin_id')
            ->select();

        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => &$value) {
                $value['rate'] = !empty($value['order_cnt_sum']) ? sprintf('%.4f', $value['order_ontime_sum'] / $value['order_cnt_sum']) * 100 : 0;
            }
        }

        $data['list']  = $list;
        $data['field'] = lang('collection_log');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 获取百融信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function hundred_funsion_info()
    {
        $order_no = request()->post('order_no');
        $type = request()->post('type', '', 'trim');
        if($type == 1){ //查询百融的数据
            $data = Db::table('hunuo_order_risk_beirong')->field('order_no,flag_rulespeciallist_c,Rule_final_decision,Rule_final_weight,rule')->where(['order_no' => $order_no])->find();
            if(empty($data)) return json(['code' => 400, 'message' => lang('success'), 'data' => []]);
            $data['rule'] = json_decode($data['rule'], true);
        }elseif ($type == 2){ //查询新颜的数据
            $data['hunuo_order_risk_heijing'] = Db::table('hunuo_order_risk_heijing')->field('Id,order_no,trans_id,trade_no,fee,add_time',true)->where(['order_no' => $order_no])->find();
            $data['hunuo_order_risk_xwld'] = Db::table('hunuo_order_risk_xwld')->field('Id,order_no,trans_id,trade_no,fee,add_time',true)->where(['order_no' => $order_no])->find();
            $data['hunuo_order_risk_fmlh'] = Db::table('hunuo_order_risk_fmlh')->field('Id,order_no,trans_id,trade_no,fee,add_time',true)->where(['order_no' => $order_no])->find();
            $data['hunuo_order_risk_fmxb'] = Db::table('hunuo_order_risk_fmxb')->field('Id,order_no,trans_id,trade_no,fee,add_time',true)->where(['order_no' => $order_no])->find();
            if(empty($data['hunuo_order_risk_heijing']) && empty($data['hunuo_order_risk_xwld']) && empty($data['hunuo_order_risk_fmlh']) && empty($data['hunuo_order_risk_fmxb'])) return json(['code' => 400, 'message' => lang('success'), 'data' => []]);
        }else{
            $data = [];
        }

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

}