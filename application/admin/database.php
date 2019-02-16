<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

$server_env = $_SERVER['SERVER_NAME'];
$service_port = $_SERVER['SERVER_PORT'];
if ($server_env == 'ydh.tupulian.com') {
    $db_host = 'rm-wz9s725zi075me3ru.mysql.rds.aliyuncs.com';
    $db_name = 'ydh_tupulian';
    $db_user = 'yidaihuan_123';
    $db_pwd  = 'yidaihuan_123';
} else {
    $db_host = '120.77.81.91';
    $db_name = 'ydh_tupulian';
    $db_user = 'root';
    $db_pwd  = 'mysql123';
}
 return [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => $db_host,
        // 数据库名
        'database' => $db_name,
        // 用户名
        'username' => $db_user,
        // 密码
        'password' => $db_pwd,
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        // 数据库调试模式
        'debug' => true,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy' => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate' => false,
        // 读写分离后 主服务器数量
        'master_num' => 1,
        // 指定从服务器序号
        'slave_no' => '',
        // 是否严格检查字段是否存在
        'fields_strict' => true,
        // 数据集返回类型 array 数组 collection Collection对象
        'resultset_type' => 'array',
        // 是否自动写入时间戳字段
        'auto_timestamp' => false,
        // 是否需要进行SQL性能分析
        'sql_explain' => false,

        // App域名地址
        'app_site_dev' => 'http://developing.api.ydh.china.tupulian.com',
        'app_site_online' => 'http://39.108.26.98',
    ];

