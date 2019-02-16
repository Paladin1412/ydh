<?php
namespace app\channel\controller;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 18:09
 */

use think\cache\driver\Redis;
use think\Controller;
use think\Db;

class Test extends Controller
{
   
	public function mongo(){
	    /*var_dump(request()->ip());
	    halt($_SERVER);*/
		$mongo_data['name'] = 'te3';
		$mongo_data['age'] = 'te23';
        $mongo_data['date'] = date('Y-m-d H:i:s');
		//mongo_log('channel_log',$mongo_data);
        //$config = config('auth_' . check_env() . '.MONGO');
        //halt($config);
        $config = [
            'type'          => '\think\mongo\Connection',
            'hostname'      => '127.0.0.1',
            'database'      => 'app_ydh_china',
            'username'      => 'app_ydh_china',
            'password'      => 'app_ydh_china',
            'hostport'      => '27017',
            'pk_convert_id' => true,
        ];
        //var_dump(\think\Db::connect($config));
        $result = \think\Db::connect($config)->name('cheshi_log')->insert($mongo_data);
        var_dump($result);
        /*var_dump(new Redis());
        echo phpinfo();*/

	}

    public function index()
    {
        //$phone     = request()->post('phone');
        $mobile = request()->post('phone');
        $company_code = request()->post('companycode');
        $type   = request()->post('type'); //1,注册 2,忘记密码，3.登入

        return json(['status' => 200, 'msg' => $type]);
    }




}