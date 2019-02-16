<?php

/**Desccription 定时任务类
 * User: andy.deng
 * Date: 2018/8/24
 * Time: 14:43
 */
namespace app\admin\controller;

use think\Controller;
use think\Db;

class Crontab extends Controller
{
	//异步测试
	public function test(){
		for($i=1;$i<=1000;$i++){
			yibu_request(get_request_url() . '/index.php/api/Crontab/test_log',['i'=>$i]);
		}
	}

	public function test_log(){
		$i = request()->param('i');
		$add_data = [
			'i' => $i,
			'addtime' =>time(),
		];
		Db::connect("db_config_invest")->name('invest_yibu_log')->add($add_data);
	}


	//开始统计
    public function begin()
    {
    	if(!empty(request()->param('is_today'))){
			$is_today = 'yes';
    	}else{
    		$is_today = 'no';
    	}
    	//dump($is_today);
        //充值统计
        // echo get_request_url() . '/index.php/api/Crontab/recharge_statis';
        // exit;
        yibu_request(get_request_url() . '/index.php/api/Crontab/recharge_statis',['is_today'=>$is_today]);
        //提现统计
        yibu_request(get_request_url() . '/index.php/api/Crontab/withdraw_statis',['is_today'=>$is_today]);
        //标的统计
        yibu_request(get_request_url() . '/index.php/api/Crontab/bid_statis',['is_today'=>$is_today]);
        //用户统计
        yibu_request(get_request_url() . '/index.php/api/Crontab/user_statis',['is_today'=>$is_today]);
    }

    //充值统计
    public function recharge_statis(){
    	//file_put_contents('./test.txt', 'abc');
    	if(request()->param('is_today')==='yes'){
			//今日开始时间
	    	$today_start_time = strtotime(date('Y-m-d').' 00:00:00');
	    	//今日结束时间
	    	$today_end_time = strtotime(date('Y-m-d').' 23:59:59');
	    	$date_str = date('Y-m-d');
    	}else{
    		//昨日开始时间
	    	$today_start_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 00:00:00');
	    	//昨日结束时间
	    	$today_end_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 23:59:59');
	    	$date_str = date('Y-m-d',strtotime('-1 day'));
    	}

    	$insert_data = [];
    	$insert_data['date_str'] = $date_str;
    	//当日充值数量
    	$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['recharge_num'] = Db::connect("db_config_invest")
								    	->name('invest_recharge_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
		//当日充值成功数量
		unset($where);						    	
		$where['success_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
		$where['order_status'] = 2;		    	
    	$insert_data['success_num'] = Db::connect("db_config_invest")
								    	->name('invest_recharge_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();		
		//当日充值失败数量
		unset($where);						    	
		$where['success_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
		$where['order_status'] = 3;		   	
    	$insert_data['failed_num'] = Db::connect("db_config_invest")
								    	->name('invest_recharge_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
		//当日充值成功总额
		unset($where);						    	
		$where['success_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
		$where['order_status'] = 2;
    	$insert_data['today_total_sum'] = Db::connect("db_config_invest")
								    	->name('invest_recharge_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->sum('money');								    	
		//累计充值成功总额
		unset($where);						    	
		$where['order_status'] = 2;
		$where['success_time'] = ['<=',$today_end_time];
    	$insert_data['all_total_sum'] = Db::connect("db_config_invest")
								    	->name('invest_recharge_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->sum('money');	
		//当日投标金额
		unset($where);						    	
		$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['today_bid_sum'] = Db::connect("db_config_invest")
								    	->name('invest_order_info')
								    	->where($where)
								    	->fetchSql(false)
								    	->sum('use_money');
		//累计投标金额
		unset($where);
		$where['add_time'] = ['<=',$today_end_time];			
    	$insert_data['all_bid_sum'] = Db::connect("db_config_invest")
								    	->name('invest_order_info')
								    	->where($where)
								    	->fetchSql(false)
								    	->sum('use_money');							    	
    	//dump($insert_data);
    	//统计数据为空时默认为0
        foreach($insert_data as $key=>$val){
            $insert_data[$key] = $val?$val:0;
        }
        //dump($insert_data);
        $info = Db::connect("db_config_invest")->name('invest_statistics_recharge')->where(['date_str'=>$date_str])->find();
        //存在就更新,不存在就添加
        if($info){
        	Db::connect("db_config_invest")->name('invest_statistics_recharge')->where(['date_str'=>$date_str])->update($insert_data);
        }else{
        	Db::connect("db_config_invest")->name('invest_statistics_recharge')->insertGetId($insert_data);
        }

    }

    //提现统计
    public function withdraw_statis(){
    	if(request()->param('is_today')==='yes'){
			//今日开始时间
	    	$today_start_time = strtotime(date('Y-m-d').' 00:00:00');
	    	//今日结束时间
	    	$today_end_time = strtotime(date('Y-m-d').' 23:59:59');
	    	$date_str = date('Y-m-d');
    	}else{
    		//昨日开始时间
	    	$today_start_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 00:00:00');
	    	//昨日结束时间
	    	$today_end_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 23:59:59');
	    	$date_str = date('Y-m-d',strtotime('-1 day'));
    	}

    	$insert_data = [];
    	$insert_data['date_str'] = $date_str;
    	//当日提现数量
    	$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['withdraw_num'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//未审核数量
		unset($where);						    	
		$where['status'] =	0;	
		$where['add_time'] = ['<=',$today_end_time];			    	
    	$insert_data['no_audit_num'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//当日审核通过数量
		unset($where);						    	
		$where['status'] =	1;						    	
    	$where['last_check_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['audit_pass_num'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//当日审核不通过数量
		unset($where);						    	
		$where['status'] =	2;						    	
    	$where['last_check_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['audit_failed_num'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
		//当日提现成功数量						    	
		unset($where);						    	
		$where['status'] =	3;						    	
    	$where['success_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['withdraw_success_num'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
		//当日提现失败数量						    	
		unset($where);						    	
		$where['status'] =	4;						    	
    	$where['success_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['withdraw_failed_num'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
		//当日提现成功金额						    	
		unset($where);						    	
		$where['status'] =	3;						    	
    	$where['success_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['today_withdraw_sum'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->sum('withdraw_money');

		//累计提现成功金额					    	
		unset($where);						    	
		$where['status'] =	3;
		$where['success_time'] = ['<=',$today_end_time];						    	
    	$insert_data['all_withdraw_sum'] = Db::connect("db_config_invest")
								    	->name('invest_withdraw_order')
								    	->where($where)
								    	->fetchSql(false)
								    	->sum('withdraw_money');
		//dump($insert_data);
    	//统计数据为空时默认为0
        foreach($insert_data as $key=>$val){
            $insert_data[$key] = $val?$val:0;
        }
        $info = Db::connect("db_config_invest")->name('invest_statistics_withdraw')->where(['date_str'=>$date_str])->find();
        //存在就更新,不存在就添加
        if($info){
        	Db::connect("db_config_invest")->name('invest_statistics_withdraw')->where(['date_str'=>$date_str])->update($insert_data);
        }else{
        	Db::connect("db_config_invest")->name('invest_statistics_withdraw')->insertGetId($insert_data);
        }
    }

    //投标统计
    public function bid_statis(){
    	if(request()->param('is_today')==='yes'){
			//今日开始时间
	    	$today_start_time = strtotime(date('Y-m-d').' 00:00:00');
	    	//今日结束时间
	    	$today_end_time = strtotime(date('Y-m-d').' 23:59:59');
	    	$date_str = date('Y-m-d');
    	}else{
    		//昨日开始时间
	    	$today_start_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 00:00:00');
	    	//昨日结束时间
	    	$today_end_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 23:59:59');
	    	$date_str = date('Y-m-d',strtotime('-1 day'));
    	}

    	$insert_data = [];
    	$insert_data['date_str'] = $date_str;

    	//投标中数量
    	$where['project_status'] = 1;
    	$where['is_delete'] = 0;
    	$where['add_time'] = ['<=',$today_end_time];
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['bid_way_num'] = Db::connect("db_config_invest")
								    	->name('invest_project')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//还款中数量
    	$where['project_status'] = 3;
    	$where['is_delete'] = 0;
    	$where['add_time'] = ['<=',$today_end_time];
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['repayment_way_num'] = Db::connect("db_config_invest")
								    	->name('invest_project')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//已结清数量
    	$where['project_status'] = 4;
    	$where['is_delete'] = 0;
    	$where['add_time'] = ['<=',$today_end_time];
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['complete_num'] = Db::connect("db_config_invest")
								    	->name('invest_project')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();								    	
    	//满标待审核数量
    	$where['project_status'] = 2;
    	$where['is_delete'] = 0;
    	$where['add_time'] = ['<=',$today_end_time];
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['full_bid_num'] = Db::connect("db_config_invest")
								    	->name('invest_project')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();	
    	//总标的数量
		unset($where);						    	
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$where['is_delete'] = 0;
    	$where['add_time'] = ['<=',$today_end_time];
    	$insert_data['total_bid_num'] = Db::connect("db_config_invest")
								    	->name('invest_project')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//总标的金额
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
		$where['is_delete'] = 0;
    	$where['add_time'] = ['<=',$today_end_time];						    	
    	$insert_data['total_bid_sum'] = Db::connect("db_config_invest")
								    	->name('invest_project')
								    	->where($where)
								    	->fetchSql(false)
								    	->sum('project_money');

    	//统计数据为空时默认为0
        foreach($insert_data as $key=>$val){
            $insert_data[$key] = $val?$val:0;
        }

        $info = Db::connect("db_config_invest")->name('invest_statistics_bid')->where(['date_str'=>$date_str])->find();
        //存在就更新,不存在就添加
        if($info){
        	Db::connect("db_config_invest")->name('invest_statistics_bid')->where(['date_str'=>$date_str])->update($insert_data);
        }else{
        	Db::connect("db_config_invest")->name('invest_statistics_bid')->insertGetId($insert_data);
        }
    }

    //用户统计
    public function user_statis(){
    	if(request()->param('is_today')==='yes'){
			//今日开始时间
	    	$today_start_time = strtotime(date('Y-m-d').' 00:00:00');
	    	//今日结束时间
	    	$today_end_time = strtotime(date('Y-m-d').' 23:59:59');
	    	$date_str = date('Y-m-d');
    	}else{
    		//昨日开始时间
	    	$today_start_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 00:00:00');
	    	//昨日结束时间
	    	$today_end_time = strtotime(date('Y-m-d',strtotime('-1 day')).' 23:59:59');
	    	$date_str = date('Y-m-d',strtotime('-1 day'));
    	}

    	$insert_data = [];
    	$insert_data['date_str'] = $date_str;
    	//新增用户数
    	$where['reg_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['register_user_num'] = Db::name("hunuo_users")
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//新增借款人数
		unset($where);						    	
    	$where['reg_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$where['photo_assay'] = ['<>',''];
    	$insert_data['borrow_user_num'] = Db::name("hunuo_users")
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//新增投资人数
		unset($where);							    	
		$where['is_auth'] = 2;
    	$where['last_auth_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$insert_data['invest_user_num'] = Db::connect("db_config_invest")
								    	->name('invest_user_info')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//总借款人数
		unset($where);	
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$where['photo_assay'] = ['<>',''];
    	$where['reg_time'] = ['<=',$today_end_time];
    	$insert_data['total_borow_num'] = Db::name("hunuo_users")
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
    	//总投资人数
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
		unset($where);						    	
		$where['is_auth'] = 2;
		$where['last_auth_time'] = ['<=',$today_end_time];
    	$insert_data['total_invest_num'] = Db::connect("db_config_invest")
								    	->name('invest_user_info')
								    	->where($where)
								    	->fetchSql(false)
								    	->count();
		unset($where);							    	
    	//总用户数
    	//$where['add_time'] = [['egt',$today_start_time],['elt',$today_end_time]];
    	$where['reg_time'] = ['<=',$today_end_time];
    	$insert_data['total_user_num'] = Db::name("hunuo_users")
								    	->where($where)
								    	->fetchSql(false)
								    	->count();							    				    	
    	//统计数据为空时默认为0
        foreach($insert_data as $key=>$val){
            $insert_data[$key] = $val?$val:0;
        }

        $info = Db::connect("db_config_invest")->name('invest_statistics_user')->where(['date_str'=>$date_str])->find();
        //存在就更新,不存在就添加
        if($info){
        	Db::connect("db_config_invest")->name('invest_statistics_user')->where(['date_str'=>$date_str])->update($insert_data);
        }else{
        	Db::connect("db_config_invest")->name('invest_statistics_user')->insertGetId($insert_data);
        }
    }
}