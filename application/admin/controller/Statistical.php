<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/10
 * Time: 14:21
 */

namespace app\admin\controller;

use think\AuthonV2;
use think\Controller;
use think\Db;
use think\Config;

class Statistical extends Controller
{

    public function adv()
    {
        $condition = array();
        $data      = Db::table('statistical_adv')
            ->field('id,name,code,callback,status,show_data_url')
            ->where($condition)
            ->fetchSql(false)
            ->select();
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    //添加渠道
    public function adv_add(){
        $request   = request();
        $post_data = $request->param();
        if(empty($post_data['name'])){
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $data = Db::table('statistical_adv')->field('code')->select();

        //把当前表中所有渠道code放入数组
        $code_array = [];
        foreach($data as $key=>$val){
            $code_array[] = $val['code'];
        }
        for($i=1;$i<=100;$i++){
            $rand_code = '62-'.mt_rand(10000000,99999999);
            //生成不重复的code就立即退出循环
            if(!in_array($rand_code,$code_array)){
                break;
            }
        }
        $callback = config('config_' . check_env() . '.EXTEN_LINK');
        $show_data_url = config('config_' . check_env() . '.SHOW_DATA_LINK');
        $add_data = [
            'name' => $post_data['name'],
            'code' => $rand_code,
            'callback' => $callback.'?code='.$rand_code,
            'addtime' => date('Y-m-d H:i:s'),
            'company_code' =>session('admin_info.company_code'),
            'show_data_url' => $show_data_url.'/admin/Statistics/detail?code='.$rand_code,
            'status' => 1,
        ];
        $lastId = Db::name('statistical_adv')->insertGetId($add_data);
        if((int)$lastId > 0){
            return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
        }else{
            return json(['code' => 401, 'message' => lang('error_4001'), 'data' => $data]);
        }
    }

    //改变渠道显示状态
    public function change_status(){
        $request   = request();
        $post_data = $request->param();
        if(empty($post_data['id']) || !isset($post_data['status'])){
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => '']);
        }
        $result = Db::name('statistical_adv')->where('id',$post_data['id'])->fetchSql(false)->update(['status'=>$post_data['status']]);
        if($result){
            return json(['code' => 200, 'message' => lang('success'), 'data' => '']);
        }else{
            return json(['code' => 401, 'message' => lang('error_4001'), 'data' => '']);
        }
    }



    public function all()
    {
        $request             = request();
        $post_data           = $request->param();
        $condition['c.code'] = empty($post_data['code']) ? '62-6221779' : $post_data['code'];
        if (!empty($post_data['date'])) {
            $time_data              = getSearchData($post_data['date']);
            $condition['c.addtime'] = array(array('egt', $time_data['start_time']), array('elt', $time_data['end_time']));
        }
        $click = Db::table('statistical_click')
            ->alias('c')
            ->where($condition)
            ->fetchSql(false)
            ->count();

        $download = Db::table('statistical_download')
            ->alias('d')
            ->join('statistical_click c', 'c.id=d.click_id', 'left')
            ->where($condition)
            ->fetchSql(false)
            ->count();

        $register = Db::table('statistical_register')
            ->alias('r')
            ->join('statistical_click c', 'c.id=r.click_id', 'left')
            ->where($condition)
            ->fetchSql(false)
            ->count();
        if (!empty($post_data['date'])) {
            $time_data              = getSearchData($post_data['date']);
            $map['o.addtime'] = array(array('egt', $time_data['start_time']), array('elt', $time_data['end_time']));
        }
        $map['o.code'] = empty($post_data['code']) ? '62-6221779' : $post_data['code'];
        $order = Db::table('statistical_order')
            ->alias('o')
            ->where($map)
            ->fetchSql(false)
            ->count();


        $data['list'][] = array(
            'click'    => $click,
            'download' => $download,
            'register' => $register,
            'order'    => $order,
        );
        $data['field']  = [
            'click'    => '点击',
            'download' => '下载',
            'register' => '注册',
            'order'    => '下单',
        ];
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);

    }

    public function download()
    {
        $request             = request();
        $post_data           = $request->param();
        $condition['c.code'] = empty($post_data['code']) ? '62-6221779' : $post_data['code'];
        if (!empty($post_data['date'])) {
            $time_data              = getSearchData($post_data['date']);
            $condition['c.addtime'] = array(array('egt', $time_data['start_time']), array('elt', $time_data['end_time']));
        }

        $data = Db::table('statistical_download')
            ->alias('d')
            ->join('statistical_click c', 'c.id=d.click_id', 'left')
            ->where($condition)
            ->fetchSql(true)
            ->count();

        echo $data;

    }

    public function reg()
    {
        $condition['c.code']    = '62-6221779';
        $condition['c.addtime'] = array(array('egt', '2018-06-07 00:00:00'), array('elt', '2018-06-07 23:59:59'));

        $data = Db::table('statistical_register')
            ->alias('r')
            ->join('statistical_download d', 'd.minorNum=r.minorNum', 'left')
            ->join('statistical_click c', 'c.id=d.click_id', 'left')
            ->where($condition)
            ->fetchSql(true)
            ->count();

        echo $data;

    }

    public function order()
    {
        $condition['c.code']    = '62-6221779';
        $condition['c.addtime'] = array(array('egt', '2018-06-07 00:00:00'), array('elt', '2018-06-07 23:59:59'));

        $data = Db::table('statistical_order')
            ->alias('o')
            ->join('statistical_register r', 'r.minorNum=o.minorNum', 'left')
            ->join('statistical_download d', 'd.minorNum=r.minorNum', 'left')
            ->join('statistical_click c', 'c.id=d.click_id', 'left')
            ->where($condition)
            ->fetchSql(true)
            ->count();

        echo $data;

    }

    // 定时任务统一分发请求 v1.1版本
    public function finance_statistical()
    {
        //标记开始时间
        $begin     = "2018-02-09";
        $begintime = strtotime($begin);
        //异步处理2个请求
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_due_data', array('date' => date('Y-m-d', $begintime)));
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_pay_data', array('date' => date('Y-m-d', $begintime)));
    }

    // 修改为写完当天数据后再次回调自己
    public function get_due_data()
    {
        //获取当天时间
        $date = request()->param('date');
        //时间超出则直接结束
        if ($date > date('Y-m-d')) {
            return;
        }
        // 当前正常公司
        $company_list = Db::name('system_company')->field('cp_name,cp_code')->where('status', 0)->where('apply_status', 0)->select();
        if (!empty($company_list) && is_array($company_list)) {
            foreach ($company_list as $key => $value) {
                $end_time = strtotime($date . ' 23:59:59');
                //当日应收
                $sum_data = Db::table('hunuo_order_repayment r')
                    ->field('sum(r.pay_amount) as pay_amount,sum(r.amount) as amount,sum(overdue_fee) as overdue_fee,sum(r.repay_amount) as repay_amount')
                    ->join('hunuo_order_info o', 'o.order_id = r.order_id')
                    ->where('o.company_code', $value['cp_code'])
                    ->where('due_time', $end_time)//还款日为当天
                    ->fetchSql(false)
                    ->find();


                $success_data = Db::table('hunuo_order_repayment r')
                    ->field('sum(r.paid_amount) as paid_amount,sum(overdue_fee) as overdue_fee')
                    ->join('hunuo_order_info o', 'o.order_id = r.order_id')
                    ->where('o.company_code', $value['cp_code'])
                    ->where('due_time', $end_time)
                    ->where('bill_status', 'in', '1,2')
                    ->fetchSql(false)
                    ->find();

                //当日还款成功
                $success_pay_data = Db::table('hunuo_order_repayment r')
                    ->field('r.repay_amount,count(0) as order_count,sum(paid_amount) as paid_amount')
                    ->join('hunuo_order_info o', 'o.order_id = r.order_id')
                    ->where('o.company_code', $value['cp_code'])
                    ->where('due_time', $end_time)
                    ->where('bill_status', 2)
                    ->fetchSql(false)
                    ->find();

                //应还款数
                $yinghuan_order_cnt = Db::table('hunuo_order_repayment r')
                    ->join('hunuo_order_info o', 'o.order_id = r.order_id')
                    ->where('o.company_code', $value['cp_code'])
                    ->where('due_time', $end_time)
                    ->count();
                //已还款数    
                $yihuan_order_cnt   = Db::table('hunuo_order_repayment r')
                    ->join('hunuo_order_info o', 'o.order_id = r.order_id')
                    ->where('o.company_code', $value['cp_code'])
                    ->where('bill_status', 2)
                    ->where('due_time', $end_time)
                    ->count();

                //本金回收率    
                if (empty($sum_data['pay_amount'])) {
                    $benjin_huishou_rate = 0.00;
                } else {
                    $benjin_huishou_rate = (sprintf('%.2f', $success_data['paid_amount'] / $sum_data['pay_amount'])) * 100;
                }
                //应收回款率
                if (empty($sum_data['amount'])) {
                    $yingshou_huishou_rate = 0.00;
                } else {
                    $yingshou_huishou_rate = (sprintf('%.2f', ($success_pay_data['paid_amount']) / $sum_data['repay_amount'])) * 100;
                }
                //总回款率
                $huikuan_amount = $sum_data['repay_amount'] + $sum_data['overdue_fee'];
                if (empty($huikuan_amount)) {
                    $zong_huishou_rate = 0;
                } else {
                    $zong_huishou_rate = (sprintf('%.2f', ($success_data['paid_amount'] /$huikuan_amount ))) * 100;
                }

                $yingshou_benjin_sum   = $sum_data['pay_amount'];
                $yingshou_benxi_sum    = $sum_data['repay_amount'];
                $yingshou_zongjine_sum = ($sum_data['repay_amount'] + $sum_data['overdue_fee']);
                $huankuan_benxi_sum    = ($success_pay_data['repay_amount'] * $success_pay_data['order_count']);
                $huankuan_zonge_sum    = $success_pay_data['paid_amount'];

                // 删除原本记录
                Db::name('report_finance_day')->where('company_code', $value['cp_code'])->where('date_str', $date)->delete();
                $add_data = array(
                    'date_str'              => $date,
                    'company_code'          => $value['cp_code'],
                    'yinghuan_order_cnt'    => $yinghuan_order_cnt,
                    'yihuan_order_cnt'      => $yihuan_order_cnt,
                    'weihuan_order_cnt'     => $yinghuan_order_cnt - $yihuan_order_cnt,
                    'due_order_cnt'         => 0,
                    'yingshou_benjin_sum'   => $yingshou_benjin_sum ? $yingshou_benjin_sum : 0,
                    'yingshou_benxi_sum'    => $yingshou_benxi_sum ? $yingshou_benxi_sum : 0,
                    'yingshou_zongjine_sum' => $yingshou_zongjine_sum ? $yingshou_zongjine_sum : 0,
                    'huankuan_benxi_sum'    => $huankuan_benxi_sum ? $huankuan_benxi_sum : 0,
                    'huankuan_zonge_sum'    => $huankuan_zonge_sum ? $huankuan_zonge_sum : 0,
                    'benjin_huishou_rate'   => $benjin_huishou_rate,
                    'yingshou_huishou_rate' => $yingshou_huishou_rate,
                    'zong_huishou_rate'     => $zong_huishou_rate,
                );
                // 新增记录
                Db::name('report_finance_day')->insert($add_data);
            }
        }
        // 执行完成后回调本身时间+1天继续执行
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_due_data', array('date' => date('Y-m-d', strtotime($date) + 24 * 60 * 60)));
    }

    // 修改为写完当天数据后再次回调自己
    public function get_pay_data()
    {
        //获取当天时间
        $date = request()->param('date');
        if ($date > date('Y-m-d')) {
            return false;
        }
        $start_time = strtotime($date . ' 00:00:00');
        $end_time   = strtotime($date . ' 23:59:59');

        // 当前正常公司
        $company_list = Db::name('system_company')->field('cp_name,cp_code')->where('status', 0)->where('apply_status', 0)->select();

        $file = date('Y-m-d') . '_pay_data.php';

        if (!empty($company_list) && is_array($company_list)) {
            foreach ($company_list as $key => $value) {
                // 应放款总数
                //$value['cp_code'] = '5aab2f49c3ec9';
                $order_count = Db::table('hunuo_order_info o')
                    ->join('( select order_no,min(add_time) as add_time from hunuo_agency_callback_log group by order_no)acl', 'acl.order_no = o.order_no')
                     ->where('o.order_status', 'in', '100,160,169,170,180,190,195,200')
                     ->where('acl.add_time', 'between', $start_time . ',' . $end_time)
                     ->where('o.company_code', $value['cp_code'])
                    ->count();

                //已放款数
                $order_success_data = Db::table('hunuo_order_info o')
                    ->field('count(0) as order_count_success,sum(loan_amount) as amount_sum')
                    ->join('( select order_no,min(add_time) as add_time,order_status from hunuo_agency_callback_log  group by order_no)acl', 'acl.order_no = o.order_no')
                    ->where('o.lending_time', 'neq', 0)
                    ->where('o.order_status', 'egt', 170)
                    ->where('acl.order_status','SUCCESS')
                    ->where('acl.add_time', 'between',$start_time . ',' . $end_time)
                    ->where('o.company_code', $value['cp_code'])
                    ->find();

                // 放款失败总数
                $order_fail_count = Db::table('hunuo_order_info o')
                    ->join('( select order_no,min(add_time) as add_time,order_status from hunuo_agency_callback_log group by order_no  )acl', 'acl.order_no = o.order_no')
                    ->where('o.order_status',169)
                    ->where('acl.order_status','FALL')
                    ->where('acl.add_time', 'between',$start_time . ',' . $end_time)
                    ->where('company_code', $value['cp_code'])
                    ->fetchSql(false)
                    ->count();

                //入帐总额                
                $order_repayment_data = Db::table('hunuo_order_repayment r')
                    ->field('sum(r.paid_amount) as amount_sum')
                    ->join('hunuo_order_info o', 'r.order_id = o.order_id')
                    ->where('r.bill_status', 2)
                    ->where('r.success_time', 'between', '' . $start_time . ',' . $end_time)
                    ->where('o.company_code', $value['cp_code'])
                    ->find();

                // 删除原本记录
                Db::name('report_finance_order_day')->where('company_code', $value['cp_code'])->where('date_str', $date)->delete();
                $add_data = array(
                    'date_str'          => $date,
                    'order_cnt'         => $order_count ? $order_count : 0,
                    'order_success_cnt' => $order_success_data['order_count_success'] ? $order_success_data['order_count_success'] : 0,
                    'order_success_sum' => $order_success_data['amount_sum'] ? $order_success_data['amount_sum'] : 0,
                    'order_fail_cnt'    => $order_fail_count ? $order_fail_count : 0,
                    'order_repayment_sum'    => (int)$order_repayment_data['amount_sum'] ? (int)$order_repayment_data['amount_sum']:0,
                    'add_time'          => time(),
                    'company_code'      => $value['cp_code'],
                );
                Db::name('report_finance_order_day')->insert($add_data);
                //$this->write_log($file, $log_str);
            }
        }

        // 执行完成后回调本身时间+1天继续执行
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_pay_data', array('date' => date('Y-m-d', strtotime($date) + 24 * 60 * 60)));
    }

    // 承诺还款修改为写完当天数据后再次回调自己
    public function get_collection_log_data()
    {
        //获取当天时间
        $date = request()->param('date');
        if ($date > date('Y-m-d')) {
            return false;
        }
        $log_str = '当前时间：' . $date . "\n";
        $start_time = strtotime($date . ' 00:00:00');
        $end_time   = strtotime($date . ' 23:59:59');

        $list = Db::table('hunuo_order_collection_log t1')
            ->field('count(*) as order_cnt_sum,t2.order_undue_sum,t3.order_ontime_sum,t4.order_due_sum,t1.admin_id')
            ->join('(select count(*) as order_undue_sum,admin_id from hunuo_order_collection_log where status in (1,2) and add_time between ' . $start_time . ' and ' . $end_time . ' group by admin_id) t2', 't2.admin_id = t1.admin_id','left')
            ->join('(select count(*) as order_ontime_sum,admin_id from hunuo_order_collection_log where status = 3 and add_time between ' . $start_time . ' and ' . $end_time . '  group by admin_id) t3', 't3.admin_id = t1.admin_id','left')
            ->join('(select count(*) as order_due_sum,admin_id from hunuo_order_collection_log where status = 4 and add_time between ' . $start_time . ' and ' . $end_time . '  group by admin_id) t4', 't4.admin_id = t1.admin_id','left')
            ->where('t1.add_time', 'between', [$start_time,$end_time])
            ->group('admin_id')
            ->fetchSql(false)
            ->select();
        $file = date('Y-m-d') . '_collection_log_data.php';

        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => $value) {
                $log_str .= "催收员ID：{$value['admin_id']} \n";
                $log_str .= "承诺还款数：{$value['order_cnt_sum']} \n";
                $log_str .= "承诺期内已经还的：{$value['order_ontime_sum']} \n";
                $log_str .= "承诺期内的未还数量：{$value['order_undue_sum']} \n";
                $log_str .= "超出承诺期的数量：{$value['order_due_sum']} \n";
                // 删除原本记录
                Db::name('report_daihou_commitment')->where('date_str', $date)->where('admin_id', $value['admin_id'])->delete();
                $add_data = array(
                    'date_str'     => $date,
                    'admin_id'     => $value['admin_id'],
                    'order_cnt'    => $value['order_cnt_sum'] ? $value['order_cnt_sum'] : 0,
                    'order_undue'  => $value['order_undue_sum'] ? $value['order_undue_sum'] : 0,
                    'order_ontime' => $value['order_ontime_sum'] ? $value['order_ontime_sum'] : 0,
                    'order_due'    => $value['order_due_sum'] ? $value['order_due_sum'] : 0,
                );
                Db::name('report_daihou_commitment')->insert($add_data);
            }
        }
        $this->write_log($file, $log_str);

        // 执行完成后回调本身时间+1天继续执行
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_collection_log_data', array('date' => date('Y-m-d', strtotime($date) + 24 * 60 * 60)));
    }


    /**
     * 获取逾期统计每日的具体数值
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function get_due_order_data()
    {

        // 传递日期查询当天数据
        $post_data = request()->param();
        if ($post_data['date'] > date('Y-m-d')) {
            return;
        }
        $company_list = Db::name('system_company')->field('cp_name,cp_code')->where('status', 0)->where('apply_status', 0)->select();
        if (!empty($company_list) && is_array($company_list)) {
            foreach ($company_list as $key => $value) {
                $company_code = $value['cp_code'];
                $due_time     = strtotime($post_data['date'] . ' 23:59:59');
                // 逾期后还款
                $due_repay_time = [
                    'pd1_3'   => ['start_time' => $due_time + 1 , 'end_time' => $due_time + 60 * 60 * 24 * 3],
                    'pd4_8'   => ['start_time' => $due_time + 60 * 60 * 24 * 3 + 1 , 'end_time' => $due_time + 60 * 60 * 24 * 8],
                    'pd9_18'  => ['start_time' => $due_time + 60 * 60 * 24 * 8 + 1 , 'end_time' => $due_time + 60 * 60 * 24  * 18],
                    'pd19_30' => ['start_time' => $due_time + 60 * 60 * 24 * 18 + 1, 'end_time' => $due_time + 60 * 60 * 24 * 30],
                    'pd31_60' => ['start_time' => $due_time + 60 * 60 * 24 * 30 + 1, 'end_time' => $due_time + 60 * 60 * 24 * 60],
                    'pd61'    => $due_time + 60 * 60 * 24 * 60 + 1
                ];
                // 1 应还款数
                // 2 已还款数
                // 3 当天即将逾期
                // 4 当前逾期订单总量
                // 5 逾期后还款订单量 pd1-3
                // 6 逾期后还款订单量 pd4-8
                // 7 逾期后还款订单量 pd9-18
                // 8 逾期后还款订单量 pd19-30
                // 9 逾期后还款订单量 pd31-60
                // 10 逾期后还款订单量 pd61 +
                // 11 逾期总量 pd1-3
                // 12 逾期总量 pd4-8
                // 13 逾期总量 pd9-18
                // 14 逾期总量 pd19-30
                // 15 逾期总量 pd31-60
                // 16 逾期总量 pd61 +

                // 应还款数
                $data = Db::table("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r`
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}'  AND `due_time` = {$due_time} LIMIT 1) t")
                    // 已还款数
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}'  AND `due_time` = {$due_time}  AND `bill_status` = 2 LIMIT 1)", true)
                    // 当天即将逾期
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}'  AND `due_time` = {$due_time} AND ((`success_time` > `due_time` and bill_status = 2 ) OR (bill_status != 2)) LIMIT 1)", true)
                    // 当前逾期订单总量
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}'  AND `due_time` = {$due_time}  AND `bill_status` = 3 LIMIT 1)", true)
                    // 逾期后还款订单量 分段催回率
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `success_time`  between {$due_repay_time['pd1_3']['start_time']} and {$due_repay_time['pd1_3']['end_time']} AND `bill_status` = 2 AND `due_time` = {$due_time}   LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `success_time`  between {$due_repay_time['pd4_8']['start_time']} and {$due_repay_time['pd4_8']['end_time']} AND `bill_status` = 2 AND `due_time` = {$due_time}   LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `success_time`  between {$due_repay_time['pd9_18']['start_time']} and {$due_repay_time['pd9_18']['end_time']} AND `bill_status` = 2 AND `due_time` = {$due_time}   LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `success_time`  between {$due_repay_time['pd19_30']['start_time']} and {$due_repay_time['pd19_30']['end_time']} AND `bill_status` = 2 AND `due_time` = {$due_time}   LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `success_time`  between {$due_repay_time['pd31_60']['start_time']} and {$due_repay_time['pd31_60']['end_time']} AND `bill_status` = 2 AND `due_time` = {$due_time}   LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `success_time`  >= {$due_repay_time['pd61']} AND `bill_status` = 2 AND `due_time` = {$due_time}   LIMIT 1)", true)
                    // 逾期总量 用于计算 逾期率
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `due_time` = {$due_time} AND `due_day` between 1 and 3  AND `bill_status` = 3 LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `due_time` = {$due_time} AND `due_day`  between 4 and 8  AND `bill_status` = 3 LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `due_time` = {$due_time} AND `due_day`  between 9 and 18  AND `bill_status` = 3 LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `due_time` = {$due_time} AND `due_day`  between 19 and 30  AND `bill_status` = 3 LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `due_time` = {$due_time} AND `due_day`  between 31 and 60  AND `bill_status` = 3 LIMIT 1)", true)
                    ->union("(SELECT COUNT(*) AS tp_count FROM `hunuo_order_repayment` `r` 
                    INNER JOIN `hunuo_order_info` `o` ON `o`.`order_id`=`r`.`order_id` 
                    WHERE  `o`.`company_code` = '{$company_code}' AND `due_time` = {$due_time} AND `due_day`  >= 61 AND `bill_status` = 3 LIMIT 1)", true)
                    ->fetchSql(false)
                    ->select();
                //$log_str .= $data ."\n";
                //$log_str .= date('Y-m-d H:i:s',$due_time) ."\n";
                //$this->write_log($file,$log_str);
                $add_date = array(
                    'date_str'                   => $post_data['date'],
                    'order_pay_sum'              => $data[0]['tp_count'],
                    'order_repay_sum'            => $data[1]['tp_count'],
                    'order_today_due_sum'        => $data[2]['tp_count'],
                    'order_due_sum'              => $data[3]['tp_count'],
                    'order_due_repay_pd_1_3'     => $data[4]['tp_count'],
                    'order_due_repay_pd_4_8'     => $data[5]['tp_count'],
                    'order_due_repay_pd_9_18'    => $data[6]['tp_count'],
                    'order_due_repay_pd_19_30'   => $data[7]['tp_count'],
                    'order_due_repay_pd_31_60'   => $data[8]['tp_count'],
                    'order_due_repay_pd_61'      => $data[9]['tp_count'],
                    'order_due_pd_1_3'           => $data[10]['tp_count'],
                    'order_due_pd_4_8'           => $data[11]['tp_count'],
                    'order_due_pd_9_18'          => $data[12]['tp_count'],
                    'order_due_pd_19_30'         => $data[13]['tp_count'],
                    'order_due_pd_31_60'         => $data[14]['tp_count'],
                    'order_due_pd_61'            => $data[15]['tp_count'],
                    'company_code'               => $company_code,
                );
                Db::name('report_due_day')->where('company_code', $company_code)->where('date_str', $post_data['date'])->delete();
                Db::name('report_due_day')->insert($add_date);
            }
        }
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_due_order_data', array('date' => date('Y-m-d', strtotime($post_data['date']) + 60 * 60 * 24)));
    }

    /**
     * 定时写入 hunuo_report_day 数据
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    // public function get_hunuo_report_day_data()
    // {
    //     $post_data = request()->param();
    //     if ($post_data['date'] > date('Y-m-d')) {
    //         return;
    //     }
    //     $company_list = Db::name('system_company')->field('cp_name,cp_code')->where('status', 0)->where('apply_status', 0)->select();
    //     if (!empty($company_list) && is_array($company_list)) {
    //         foreach ($company_list as $key => $value) {
    //             if($value['cp_code'] != '5aab9fb19ecea')
    //             {
    //                 Db::query('call proc_hunuo_report_day(:date,:code)', array('date' => $post_data['date'], 'code' => $value['cp_code']));
    //             }
    //         }
    //     }
    //     $this->doRequest($this->GetHttpsUrl() . '/index.php/api/Statistical/get_hunuo_report_day_data', array('date' => date('Y-m-d', strtotime($post_data['date']) + 60 * 60 * 24)));
    // }

    public function get_hunuo_report_day_data()
    {
        $post_data = request()->param();
        if ($post_data['date'] > date('Y-m-d')) {
            return;
        }

        $dsn = 'mysql:host='.Config::get('database.hostname').';dbname='.Config::get('database.database');
        $company_list = Db::name('system_company')->field('cp_name,cp_code')->where('status', 0)->where('apply_status', 0)->select();
        if (!empty($company_list) && is_array($company_list)) {
            foreach ($company_list as $key => $value) {
                if($value['cp_code'] != '5aab9fb19ecea')
                {
                    //Db::query('call proc_hunuo_report_day(:date,:code)', array('date' => $post_data['date'], 'code' => $value['cp_code']));
                    $dbh = new \PDO($dsn, Config::get('database.username'),Config::get('database.password'));
                    $stmt = $dbh->prepare("CALL proc_hunuo_report_day(:date,:code)");
                    $stmt->bindParam(':date', $post_data['date']); 
                    $stmt->bindParam(':code', $value['cp_code']); 
                    $stmt->execute();
                }
            }
        }
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_hunuo_report_day_data', array('date' => date('Y-m-d', strtotime($post_data['date']) + 60 * 60 * 24)));
    }

    /**
     * 定时写入 report_business_channel_day
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    // public function get_proc_report_business_channel_day_data()
    // {
    //     $post_data = request()->param();
    //     if ($post_data['date'] > date('Y-m-d')) {
    //         return;
    //     }
    //     $company_list = Db::name('system_company')->field('cp_name,cp_code')->where('status', 0)->where('apply_status', 0)->select();
    //     if (!empty($company_list) && is_array($company_list)) {
    //         Db::query('call proc_report_user_channel');
    //         foreach ($company_list as $key => $value) {
    //             $channel_list = Db::name('statistical_adv')->where('company_code',$value['cp_code'])->where('status',1)->select();
    //             if (!empty($channel_list) && is_array($channel_list)) {
    //                 foreach ($channel_list as $key2 => $value2) {
    //                     Db::query('call proc_report_business_channel_day(:date,:cp_code,:channel_code)', array('date' => $post_data['date'], 'cp_code' => $value['cp_code'],'channel_code'=>$value2['code']));
    //                 }
    //             }
    //         }
    //     }
    //     $this->doRequest($this->GetHttpsUrl() . '/index.php/api/Statistical/get_proc_report_business_channel_day_data', array('date' => date('Y-m-d', strtotime($post_data['date']) + 60 * 60 * 24)));
    // }


    public function get_proc_report_business_channel_day_data()
    {
        $post_data = request()->param();
        if ($post_data['date'] > date('Y-m-d')) {
            return;
        }
        $company_list = Db::name('system_company')->field('cp_name,cp_code')->where('status', 0)->where('apply_status', 0)->select();
        if (!empty($company_list) && is_array($company_list)) {
            //Db::query('call proc_report_user_channel');
            $dsn = 'mysql:host='.Config::get('database.hostname').';dbname='.Config::get('database.database');
            $dbh = new \PDO($dsn, Config::get('database.username'),Config::get('database.password'));
            $stmt = $dbh->prepare("CALL proc_report_user_channel");
            $stmt->execute();
            foreach ($company_list as $key => $value) {
                $channel_list = Db::name('statistical_adv')->where('company_code',$value['cp_code'])->where('status',1)->select();
                if (!empty($channel_list) && is_array($channel_list)) {
                    foreach ($channel_list as $key2 => $value2) {
                        // Db::query('call proc_report_business_channel_day(:date,:cp_code,:channel_code)', array('date' => $post_data['date'], 'cp_code' => $value['cp_code'],'channel_code'=>$value2['code']));
                        $dbh = new \PDO($dsn, Config::get('database.username'),Config::get('database.password'));
                        $stmt = $dbh->prepare("CALL proc_report_business_channel_day(:date,:cp_code,:channel_code)");
                        $stmt->bindParam(':date', $post_data['date']); 
                        $stmt->bindParam(':cp_code', $value['cp_code']); 
                        $stmt->bindParam(':channel_code', $value2['code']); 
                        $stmt->execute();
                    }
                }
            }
        }
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_proc_report_business_channel_day_data', array('date' => date('Y-m-d', strtotime($post_data['date']) + 60 * 60 * 24)));
    }

    /**
     * 定时写入 report_business_channel_day
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    // public function get_proc_report_business_company_day_data()
    // {
    //     $post_data = request()->param();
    //     if ($post_data['date'] > date('Y-m-d')) {
    //         return;
    //     }
    //     //Db::query('call proc_report_user_channel');
    //     $result = Db::query('call proc_report_business_company_day(:date)', array('date' => $post_data['date']));
    //     $this->doRequest($this->GetHttpsUrl() . '/index.php/api/Statistical/get_proc_report_business_company_day_data', array('date' => date('Y-m-d', strtotime($post_data['date']) + 60 * 60 * 24)));
    // }

    //insert into hunuo_proc_log(date_str) VALUES(in_date);
    public function get_proc_report_business_company_day_data()
    {
        $post_data = request()->param();
        if ($post_data['date'] > date('Y-m-d')) {
            return;
        }
        $dsn = 'mysql:host='.Config::get('database.hostname').';dbname='.Config::get('database.database');
        $dbh = new \PDO($dsn, Config::get('database.username'),Config::get('database.password'));
        $stmt = $dbh->prepare("CALL proc_report_business_company_day(:date)");
        $stmt->bindParam(':date', $post_data['date']); 
        $stmt->execute();
        
        $this->doRequest($this->GetHttpsUrl() . '/index.php/admin/Statistical/get_proc_report_business_company_day_data', array('date' => date('Y-m-d', strtotime($post_data['date']) + 60 * 60 * 24)));
    }

    public function GetHttpsUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url      = "$protocol$_SERVER[HTTP_HOST]";
        return $url;
    }

    // 调试写入日志
    public function write_log($file, $str)
    {
       // $file = fopen(RUNTIME_PATH . '/log/' . $file, "aw");
       /// fwrite($file, ($str ? $str : '---- ') . "\n");
       // fclose($file);
    }

    /**
     * 异步请求
     * @param $url
     * @param array $param
     */
    public function doRequest($url, $param = array())
    {
        $urlinfo = parse_url($url);
        $host    = $urlinfo['host'];
        $path    = $urlinfo['path'];
        $query   = isset($param) ? http_build_query($param) : '';
        $port    = 80;
        $errno   = 0;
        $errstr  = '';
        $timeout = 10;
        $fp      = fsockopen($host, $port, $errno, $errstr, $timeout);
        $out     = "POST " . $path . " HTTP/1.1\r\n";
        $out     .= "host:" . $host . "\r\n";
        $out     .= "content-length:" . strlen($query) . "\r\n";
        $out     .= "content-type:application/x-www-form-urlencoded\r\n";
        $out     .= "connection:close\r\n\r\n";
        $out     .= $query;
        fputs($fp, $out);
        usleep(300000); //等待300ms
        fclose($fp);
    }


}