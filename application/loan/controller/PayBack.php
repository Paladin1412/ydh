<?php
namespace app\loan\controller;

use think\Config;
use think\Db;
use think\Lang;
class PayBack extends Common
{

	//合利宝代付回调方法
	public function call_back(){
		$post_data = request()->post();
		file_put_contents('../pay_back.txt', $post_data);
		dump($post_data);
	}




}