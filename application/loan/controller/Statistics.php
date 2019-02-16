<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 14:19
 */

namespace app\loan\controller;

use think\Db;
class Statistics extends Common
{
    // 统计类接口
    public function __construct() {
        parent::__construct();
    }

    /**
    function:用户下载并且第一次打开APP 页面
     **/
    public function download()
    {
        $request     = request();
        $_ClientData = $request->param();
        if (empty($_ClientData) || !is_array($_ClientData)) {
            return json(["code" => 505, "message" => "请传入信息"]);
        }
        //解码referrer参数
        $referrer_url=urldecode($_ClientData['referrer']);
        parse_str($referrer_url,$referrer);

        $data = [
            "click_id" => empty($referrer['click_id'])?0:$referrer['click_id'],
            "minorNum"    => $_ClientData["minorNum"],
            "addtime"   => date("Y-m-d H:i:s", time())
        ];
        Db::table("statistical_download")->insert($data);
        //Db::connect(Config::get('channel'))->name("download")->insert($data);
    }

    public function register() {
        $request   = request();
        $_ClientData = $request->param();
        if(!isset($_ClientData["user_id"]) || empty($_ClientData["user_id"])) {
            return json(["code"=>505,"message"=>"请传入用户UID"]);
        }
        if(NULL != $_ClientData["user_id"]&& !empty($_ClientData["user_id"])) {
            $has_click = Db::table('statistical_register')
                ->where('click_id',empty($_ClientData['click_id'])?0:$_ClientData['click_id'])
                ->where('minorNum',$_ClientData["minorNum"])
                ->find();
            if(empty($has_click)){
                $data = array(
                    "user_id" => $_ClientData["user_id"],
                    "minorNum" =>$_ClientData["minorNum"],
                    "addtime"   => date("Y-m-d H:i:s", time()),
                    "click_id" => empty($_ClientData['click_id'])?0:$_ClientData['click_id'],
                );
                Db::table("statistical_register")->insert($data);
            }

            //Db::connect(Config::get('channel'))->name("register")->insert($data);
        }
    }

    public function order() {
        $request   = request();
        $_ClientData = $request->param();
        if(!isset($_ClientData["user_id"]) || empty($_ClientData["user_id"])) {
            return json(["code"=>505,"message"=>"请传入用户UID"]);
        }

        if(!isset($_ClientData["order_no"]) || empty($_ClientData["order_no"])) {
            return json(["code"=>505,"message"=>"请传入订单号"]);
        }

        if(NULL !=$_ClientData["user_id"]&& !empty($_ClientData["user_id"])) {
            $has_click = Db::table('statistical_order')
                ->where('click_id',empty($_ClientData['click_id'])?0:$_ClientData['click_id'])
                ->where('minorNum',$_ClientData["minorNum"])
                ->find();
            if(empty($has_click)) {
                $data = array(
                    "user_id"  => $_ClientData["user_id"],
                    "minorNum" => $_ClientData["minorNum"],
                    "addtime"  => date("Y-m-d H:i:s", time()),
                    "order_no" => $_ClientData["order_no"],
                    "click_id" => empty($_ClientData['click_id']) ? 0 : $_ClientData['click_id'],
                );
                Db::table("statistical_order")->insert($data);
                //Db::connect(Config::get('channel'))->name("order")->insert($data);
            }
        }
    }

}