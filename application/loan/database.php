<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

$server_name = $_SERVER['SERVER_NAME'];
if($server_name==='39.108.26.98'){
	$database = [
		'type'        => 'mysql',
	    'hostname'    => 'rm-wz9s725zi075me3ru.mysql.rds.aliyuncs.com',
	    'database'    => 'ydh_tupulian',
	    'username'    => 'yidaihuan_123',
	    'password'    => 'yidaihuan_123',
	    'hostport'    => '3306',
	    'charset'     => 'utf8',
	    'prefix'      => 'hunuo_',
	    'sql_explain' => false,
	];
}else{
	$database = [
		'type'        => 'mysql',
	    'hostname'    => '120.77.81.91',
	    'database'    => 'ydh_tupulian',
	    'username'    => 'root',
	    'password'    => 'mysql123',
	    'hostport'    => '3306',
	    'charset'     => 'utf8',
	    'prefix'      => 'hunuo_',
	    'sql_explain' => false,
	];
}
return $database;