<?php
namespace app\admin\validate;

use think\Validate;

class Bid extends Validate
{
    protected $rule =   [
        'project_id' => 'require',
        'project_name' => 'require',
        'project_money' => 'require',
        'year_rate' => 'require',
        'min_money' => 'require',
        'project_time_num' => 'require',
        'project_pay_type' => 'require',
        'project_end_time' => 'require',
        'project_desc' => 'require',
    ];
    
    protected $message  =   [
        'project_id.require'     => '标的id不能为空',
        'project_name.require'     => '标的名称不能为空',
        'project_money.require' => '标的总额不能为空', 
        'year_rate.require' => '年化率不能为空', 
        'min_money.require' => '起投金额不能为空', 
        'project_time_num.require' => '标的期限不能为空', 
        'project_pay_type.require' => '标的还款方式不能为空', 
        'project_end_time.require' => '投标结束时间不能为空', 
        'project_desc.require' => '标的描述不能为空', 
    ];
    
    //设置场景
    protected $scene = [
        'add'  =>  ['project_desc','project_money','year_rate','min_money','project_money','project_time_num','project_pay_type','project_end_time','project_desc'],
        'edit'  =>  ['project_id','min_money','project_end_time','project_desc'],
    ];



}