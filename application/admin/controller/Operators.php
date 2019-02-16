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

class Operators extends Base
{

	/*
     * 运营商文件是否存在
     * */
    public function file_is_exist(){
		$file_url = request()->post('file_url');
        $path = ROOT_PATH.'public/'.$file_url;
		if(file_exists($path)){
            return json(['code' => 200, 'message' => '运营商报告存在', 'data' => [],'url'=>$file_url]);
        }else{
            return json(['code' => 404, 'message' => '运营商报告不存在', 'data' => [],'url'=>""]);
        }
	}








}