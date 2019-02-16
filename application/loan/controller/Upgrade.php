<?php
/**
 * Created by PhpStorm.
 * User: andy.deng
 * Date: 2018/5/11
 * Time: 14:00
 */

namespace app\loan\controller;
use think\Db;
class Upgrade extends Common
{
    // 订单类接口
    public function __construct()
    {
        parent::__construct();

    }

    //版本更新
    public function update(){
    	$app_type     = request()->post('app_type', '', 'trim');
    	if(empty($app_type)){
    		return json(['status' => '500', 'message' => '缺少必要参数', 'data' => '']);
    	}
    	$version_info = Db::name('app_version')->field('version,update_url,content')->where(['app_type'=>$app_type,'status'=>1])->order('version desc')->find();
    	return json(['status' => '200', 'message' => '成功', 'data' => $version_info]);
    }









}