<?php
/**
 * Created by PhpStorm.
 */

namespace app\admin\controller;

use think\Config;
use think\Db;

class Assignment extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->company_code=session("admin_info.company_code");
        //$this->company_code= "5aab2f49c3ec9";
        //判断级别
        $role_type           = Db::table('system_admin_role_v2')->where('role_id', 'in', session('admin_info.role_id'))->column('admin_class');
        $this->is_collector_leader = in_array(4, $role_type)||in_array(5, $role_type);
    }


    //催收任务分配
    public function collection_admin_list(){
        $company_code = $this->company_code;
        $is_collector_leader=$this->is_collector_leader;
        if($is_collector_leader&&!empty($company_code)){
            $list = Db::table('system_admin_role_relation_v2 rr')
                ->field('u.admin_id,u.real_name')
                ->join('system_admin_role_v2 r','r.role_id = rr.role_id')
                ->join('system_company sc','sc.cp_id = r.company_id')
                ->join('system_admin_v2 u','u.admin_id = rr.admin_id')
                ->where('sc.cp_code',$company_code)
                ->where('r.admin_class','in','2,4')
                ->where('u.status',0)
                ->where('r.status',0)
                ->select();

            foreach ($list as $key =>$value){
                $has_case_conunt = Db::table('daihou_case_info c')
                    ->join('hunuo_order_repayment r','r.order_no = c.order_no')
                    ->where(array('current_collector'=>$value["admin_id"]))
                    ->where('c.company_code',session('admin_info.company_code'))
                    ->where('bill_status',3)
                    ->fetchSql(false)
                    ->count();
                $list[$key]["has_case"]=$has_case_conunt;
                $list[$key]["can_case"]=20-$has_case_conunt;

            }
            $data['list']=$list;
            $data['field'] = [
                'list_no'   =>lang('cllection_list_no'),
                'real_name' => lang('cllection_real_name'),
                'role_name' => lang('cllection_role_name'),
                'has_case'  => lang('cllection_has_case'),
                'can_case'  => lang('cllection_can_case'),
            ];
            return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

        }else{
            return json(['code' => 500, 'message' => lang('error_5001')]);
        }

    }
    //批量分配
    public function collection_manual()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['order_arr']) || empty($post_data['admin_arr'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }

        $admin_class = get_admin_class();
        //必须要有信审主管或公司管理员权限
        if (strpos('a'.$admin_class,'4') >= 1 || strpos('a'.$admin_class,'5') >= 1) {
            $order_arr_data = explode('-', $post_data['order_arr']);
            $admin_arr_data = explode('-', $post_data['admin_arr']);
            //人数
            $admim_num = count($admin_arr_data);
            //订单分配组数
            $divide_arr = array();
            foreach($order_arr_data as $key=>$val){
                //订单数/人数的余数
                $remainder = $key%$admim_num;
                $divide_arr[$val] = $admin_arr_data[$remainder];
            }
            if (!empty($divide_arr) && is_array($divide_arr)) {
                foreach ($divide_arr as $key => $value) {
                    if (!empty($value)) {
                        Db::name('daihou_case_info')
                            ->where(array('order_no' => $key))
                            ->fetchSql(false)
                            ->update(["current_collector"=>$value,"distribution_time"=>time()]);
                    }
                }
            }
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        }else{
            return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
        }
    }


    //催收自动分配
    public function collection_automatic()
    {
        $request                 = request();
        $post_data               = $request->param();
        $bill_status=$post_data["type"];

        $company_code = $this->company_code;
        $is_collector_leader=$this->is_collector_leader;

        $admin_class = get_admin_class();

        //必须要有信审主管或公司管理员权限
        if (strpos('a'.$admin_class,'4') >= 1 || strpos('a'.$admin_class,'5') >= 1) {  
            if ($is_collector_leader && !empty($company_code)&&!empty($bill_status)) {

                //查找催收员数组
                $user_list = Db::table('system_admin_v2')
                    ->alias('a')
                    ->field('a.admin_id,a.real_name,r.role_name')
                    ->join('system_admin_role_v2 r', 'r.role_id = a.role_id',"left")
                    ->where(['a.company_code' => $company_code, 'a.status' => 0, 'r.status' => 0, 'r.admin_class' => ['in', "2"]])
                    ->fetchSql(false)
                    ->select();

                //查找未分配案件
                $order_list =  Db::table("daihou_case_info")
                    ->alias("c")
                    ->join("hunuo_order_repayment r","r.order_no=c.order_no","left")
                    ->join("hunuo_order_info i","i.order_no=c.order_no","left")
                    ->field('c.id')
                    ->where(['c.current_collector'=>0,"bill_status"=>$bill_status,"i.company_code"=>$company_code])
                    ->select();
                if (empty($order_list)) {
                    return json(['code' => 201, 'message' => lang('no_case'), 'data' => []]);
                }
                $order_count = count($order_list);
                $user_count  = count($user_list);
                $array_num   = round($order_count / $user_count);
                $array_num   = $array_num == 0 ? 1 : $array_num;
                $than        = $order_count % $user_count;
                if ($than != 0) {
                    for ($i = 0; $i < $user_count - $than; $i++) {
                        $order_list[] = 'temp';
                    }
                }
                $save_data = array();
                $new_array = array_chunk($order_list, $array_num, true);
                foreach ($new_array as $key => $value) {
                    if (!empty($value) && is_array($value)) {
                        foreach ($value as $value2) {
                            if ($value2 != 'temp') {
                                $save_data[] = array('current_collector' => $user_list[$key]["admin_id"],"distribution_time"=>time(), 'id' => $value2["id"]);
                            }
                        }
                    }
                }

                if (!empty($save_data)) {
                    //dump($save_data);
                    //$res = Db::table('hunuo_order_handle_user')->saveAll($save_data);
                   foreach ($save_data as $s_k=>$s_v){
                       if(!empty($s_v["current_collector"])) {
                           Db::name('daihou_case_info')->where(["id"=>$s_v["id"]])->update(["current_collector"=>$s_v["current_collector"]]);
                       }
                   }
                    return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
                } else {
                    return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
                }

            }else{

                return json(['code' => 500, 'message' => lang('error_5001')]);
            }
        }else{
            return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
        }
    }

    //催收业绩考核
    public function collector_view(){
        $request   = request();
        $post_data = $request->param();
        $company_code = $this->company_code;
        //筛选时间数组 组装
        if (!empty($post_data['date'])) {
            $time_data = $post_data['date'];
        }else{
            $time_data = date("Y-m-d");
           // $time_data = '2018-04-10';
        }
        //$is_collector_leader=$this->is_collector_leader;
        if (!empty($company_code)) {

            //查找催收员数组
            $list = Db::table('system_admin_v2')->alias('a')
                ->field('a.admin_id,a.real_name')
                ->join('system_admin_role_v2 r', 'r.role_id = a.role_id')
                ->where(['a.company_code' => $company_code, 'a.status' => 0, 'r.status' => 0, 'r.admin_class' => ['in', "2,4"]])
                ->fetchSql(false)
                ->select();

            foreach ($list as $k => $v) {
                //含有催收总数
                $has_condition['c.current_collector'] = array('eq',$v["admin_id"]);
                $has_condition['r.due_day'] = array('neq', 0);
                $has_condition['r.bill_status'] = array('eq', 3);
                $pre_time=$time_data." 00:00:00";
                $next_time=$time_data." 23:59:59";

                $all_case =  Db::name("daihou_case_info")
                    ->alias("c")
                    ->join("hunuo_order_repayment r","r.order_no=c.order_no","left")
                    ->where($has_condition)
                    ->fetchSql(false)
                    ->count();

                $has_condition['f.operator_time'] = ['between',[$pre_time,$next_time]];

                $has_case =  Db::name("daihou_case_info")
                    ->alias("c")
                    ->join("hunuo_order_repayment r","r.order_no=c.order_no","left")
                    ->join("(select * from ( SELECT * from daihou_case_followup_record ORDER BY id desc ) temp  group by temp.case_id  order by temp.id desc ) f", 'f.case_id = c.id', 'left')
                    ->where($has_condition)
                    //->field('f.operator_time,c.id')
                    ->fetchSql(false)
                    //->select();
                    ->count();


                //已完成催收总数
                $list[$k]["has_case"]=$has_case;
                $list[$k]['all_case'] = $all_case;
               // $list[$k]["over_case"]=$v["admin_id"];
                unset($has_condition);

            }

            $field=[
                "title"=>lang("collector_view_title"),
                "order_count"=>lang("collector_view_order_count"),
                "collection_count"=>lang("collector_view_collection_count"),
            ];
            $data["list"]=$list;
            $data["field"]=$field;
            return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

        }else{

            return json(['code' => 500, 'message' => lang('error_5001')]);
        }

    }

    //本月第一天：参数：$day格式为yyyy-mm-dd
    public function monFirstDay($day)
    {
        $_day = getdate(strtotime($day));
        return $_day[year].'-'.$_day[mon].'-1';
    }

    //本周第一天：参数：$day格式为yyyy-mm-dd
    public function weekFirstDay($day)
    {
        $__day = strtotime($day);
        $_day = getdate($__day);
        $_thisw = (7-$_day[wday])*3600*24;
        return date("Y-m-d",$__day - $_thisw);

    }




    //催收订单走势
    public function order_view(){

        $request   = request();
        $post_data = $request->param();
        $company_code = $this->company_code;
        if(empty($post_data["type"])){
            $type=1;
        }else{
            $type=$post_data["type"];
        }
        //筛选时间数组 组装
        if (!empty($post_data['date'])) {

            $time_data = getSearchData($post_data['date']);
            $end_time = strtotime($time_data['end_time']);
            $start_time=strtotime($time_data['start_time']);
            $one= 24 * 60 * 60;
            $day=($end_time-$start_time)/$one;


        } else {
            //本周 type=1
            if($type==1) {
                $now      = date("Y-m-d");
                $start_time = strtotime($this->weekFirstDay($now));
                $day=7;
            }
            if($type==2) {
                $now      = date("Y-m-d");
                $start_time = strtotime($this->monFirstDay($now));
                $day=date('t', strtotime(date("Y-m")));
            }

        }
        for ($i = 0; $i < $day; $i++) {
            $date[$i]["start_time"] = date('Y-m-d H:i:s', $start_time+ $i * 24 * 60 * 60);
            $date[$i]["end_time"]   = date('Y-m-d H:i:s', $start_time + ($i+1) * 24 * 60 * 60);

        }

        foreach ($date as $k=>$v){

            //$distribution_time = array(array('gt', strtotime($v['start_time'])),array('lt', strtotime($v['end_time'])),"and");
            $due_time = array('lt', strtotime($v['end_time']));
            //总订单数
            $order_count= Db::table('daihou_case_info')->alias('c')
                ->join('hunuo_order_info o', 'o.order_no = c.order_no')
                ->join('hunuo_order_repayment r', 'r.order_no = c.order_no')
                ->where(["o.company_code"=>$company_code,"r.due_time"=>$due_time,"r.bill_status"=>array("eq",3)])
                ->fetchSql(false)
                ->count();

            //总催收订单
            //$collection_count= Db::table('daihou_case_info')
               // ->alias('c')
                //->join('hunuo_order_info o', 'o.order_no = c.order_no')
                //->join('hunuo_order_repayment r', 'r.order_no = c.order_no')
                //->where(["o.company_code"=>$company_code,"r.success_time"=>$distribution_time,"r.due_day"=>["neq",0]])
                //->fetchSql(false)
                //->count();
            //$has_condition['c.current_collector'] = array('eq',$v["admin_id"]);
            $has_condition['o.company_code'] =$company_code;
            $has_condition['r.due_day'] = array('neq', 0);
            $has_condition['r.bill_status'] = array('eq', 3);
            $has_condition['f.operator_time'] = ['between',[$v['start_time'],$v['end_time']]];

            $collection_count =  Db::name("daihou_case_info")
                ->alias("c")
                ->join('hunuo_order_info o', 'o.order_no = c.order_no')
                ->join("hunuo_order_repayment r","r.order_no=c.order_no","left")
                ->join("(select * from ( SELECT * from daihou_case_followup_record ORDER BY id desc ) temp  group by temp.case_id  order by temp.id desc ) f", 'f.case_id = c.id', 'left')
                ->where($has_condition)
                //->field('f.operator_time,c.id')
                ->fetchSql(false)
                //->select();
                ->count();
            $list[$k]["date"]=date("m-d",strtotime($v['start_time']));;
            $list[$k]["order_count"]=$order_count;
            $list[$k]["collection_count"]=$collection_count;

        }
        $field=[
            "title"=>lang("order_view_title"),
            "order_count"=>lang("order_view_order_count"),
            "collection_count"=>lang("order_view_collection_count"),
        ];
        $data["list"]=$list;
        $data["field"]=$field;
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }


    //订单还款走势
    public function repay_view(){

        $request   = request();
        $post_data = $request->param();
        $company_code = $this->company_code;
        $is_collector_leader=$this->is_collector_leader;
        if (empty($company_code)&&!$is_collector_leader) {
            return json(['code' => 500, 'message' => lang('error_5001')]);
        }
        if(empty($post_data["type"])){
            $type=1;
        }else{
            $type=$post_data["type"];
        }
        //筛选时间数组 组装
        if (!empty($post_data['date'])) {

            $time_data = getSearchData($post_data['date']);
            $end_time = strtotime($time_data['end_time']);
            $start_time=strtotime($time_data['start_time']);
            $one= 24 * 60 * 60;
            $day=($end_time-$start_time)/$one;


        } else {
            //本周 type=1
            if($type==1) {
                $now      = date("Y-m-d");
                $start_time = strtotime($this->weekFirstDay($now));
                $day=7;
            }
            if($type==2) {
                $now      = date("Y-m-d");
                $start_time = strtotime($this->monFirstDay($now));
                $day=date('t', strtotime(date("Y-m")));
            }

        }
        for ($i = 0; $i < $day; $i++) {
            $date[$i]["start_time"] = date('Y-m-d H:i:s', $start_time+ $i * 24 * 60 * 60);
            $date[$i]["end_time"]   = date('Y-m-d H:i:s', $start_time + ($i+1) * 24 * 60 * 60);
            $date[$i]["date"]   = date('Y-m-d', $start_time+ $i * 24 * 60 * 60);

        }

        foreach ($date as $k=>$v){

           // $distribution_time = array(array('gt', strtotime($v['start_time'])),array('lt', strtotime($v['end_time'])),"and");

            //总订单数
            $count= Db::table('hunuo_report_day')
                ->where(["date_str"=>$v["date"],"company_code"=>$company_code])
                ->field("cur_yinghuankuan_count,cur_yihuankuan_count,all_huan_count,all_yinghuan_count")
                ->fetchSql(false)
                ->find();

            $list[$k]["date"]=date("m-d",strtotime($v['start_time']));;
            $list[$k]["cur_yinghuankuan_count"]=$count["cur_yinghuankuan_count"]?$count["cur_yinghuankuan_count"]:0;
            $list[$k]["cur_yihuankuan_count"]=$count["cur_yihuankuan_count"]?$count["cur_yihuankuan_count"]:0;
            $list[$k]["all_huan_count"]=$count["all_huan_count"]?$count["all_huan_count"]:0;
            $list[$k]["all_yinghuan_count"]=$count["all_yinghuan_count"]?$count["all_yinghuan_count"]:0;

        }
        $field=[
            "title"=>lang("order_view_title"),
            "cur_yinghuankuan_count"=>lang("cur_yinghuankuan_count"),
            "cur_yihuankuan_count"=>lang("cur_yihuankuan_count"),
            "all_huan_count"=>lang("all_huan_count"),
            "all_yinghuan_count"=>lang("all_yinghuan_count"),
        ];
        $data["list"]=$list;
        $data["field"]=$field;
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }
    //减免订单走势
    public function reduction_view(){

        $request   = request();
        $post_data = $request->param();
        $company_code = $this->company_code;

        $condition               = array();
        $condition['i.company_code'] = array('eq', $company_code);
        $condition['r.reduction_status'] = array('eq', 1);

        //筛选时间数组 组装
        if(empty($post_data["type"])){
            $type=1;
        }else{
            $type=$post_data["type"];
        }
        //筛选时间数组 组装
        if (!empty($post_data['date'])) {

            $time_data = getSearchData($post_data['date']);
            $end_time = strtotime($time_data['end_time']);
            $start_time=strtotime($time_data['start_time']);
            $one= 24 * 60 * 60;
            $day=($end_time-$start_time)/$one;


        } else {
            //本周 type=1
            if($type==1) {
                $now      = date("Y-m-d");
                $start_time = strtotime($this->weekFirstDay($now));
                $day=7;
            }
            if($type==2) {
                $now      = date("Y-m-d");
                $start_time = strtotime($this->monFirstDay($now));
                $day=date('t', strtotime(date("Y-m")));
            }

        }

        for ($i = 0; $i < $day; $i++) {
            $date[$i]["start_time"] = date('Y-m-d H:i:s', $start_time+ $i * 24 * 60 * 60);
            $date[$i]["end_time"]   = date('Y-m-d H:i:s', $start_time + ($i+1) * 24 * 60 * 60);

        }

        foreach ($date as $k=>$v){
            $condition['r.confirm_date'] = array(array('egt', strtotime($v['start_time'])),array('elt', strtotime($v['end_time'])),"and");
            $_data = Db::table('daihou_case_reduction')->alias('r')
                ->field("sum(r.reduction_fee) as all_fee,count(*) as all_num")
                ->join('hunuo_order_info i', 'i.order_no = r.order_no')
                ->where($condition)
                ->fetchSql(false)
                ->find();
            $list[$k]["all_fee"]= empty($_data['all_fee'])?0:$_data['all_fee'];
            $list[$k]['all_num'] = empty($_data['all_num'])?0:$_data['all_num'];
            $list[$k]["date"]=date("m-d",strtotime($v['start_time']));
            unset($_data);
        }
        $field=[
            "title"=>lang("reduction_view_title"),
            'all_fee'=>lang('reduction_view_all_fee'),
            'all_num'=>lang('reduction_view_all_num'),

        ];
        $data["list"]=$list;
        $data["field"]=$field;

        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }


}