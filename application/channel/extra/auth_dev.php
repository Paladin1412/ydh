<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/8/15
 * Time: 14:41
 */
return [
    'REDIS' => [
        'host'       => '127.0.0.1',//127.0.0.1
        'port'       => 6379,
        'password'   => 'redis123',//redis123
        'select'     => 0,//指定库
        'timeout'    => 0,//关闭时间 0:代表不关闭
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
    ],
    'MONGO' => [
        'type'          => '\think\mongo\Connection',
        'hostname'      => '120.77.81.91',
        'database'      => 'app_ydh_china',
        'username'      => 'app_ydh_china',
        'password'      => 'app_ydh_china',
        'hostport'      => '27017',
        'pk_convert_id' => true,
    ],



];