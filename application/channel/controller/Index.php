<?php
namespace app\channel\controller;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/19
 * Time: 16:42
 */
use app\util\ReturnCode;
use think\Controller;
use think\Db;
use Redis\redisServer;

class Index extends Controller
{
    /*
     * 查询出推广APP下载链接的信息
     * */
    public function index()
    {
        $url='http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        return redirect($url.'channel/','',302);
    }

    /*
     * 查询出推广APP下载链接的信息
     * */
    public function show_link_info()
    {
        $returnDataInfo['android_url'] = Db::name('app_version')->where(['app_type' => 1, 'status' => 2])->order('id desc')->value('update_url');
        $returnDataInfo['app_url'] = Db::name('app_version')->where(['app_type' => 2, 'status' => 2])->order('id desc')->value('update_url');
        $returnDataInfo['type'] = get_device_type();
        return json(['status' => '200', 'message' => '查询成功', 'data' => $returnDataInfo]);
    }

    /**
    function:用户下载并且第一次打开APP 页面
     **/
    public function download()
    {
        $code = redisServer::getInstance()->get(session_id().'code');
        $user_id = redisServer::getInstance()->get(session_id().'user_id');
        $click_id = redisServer::getInstance()->get(session_id().'click_id');
        $dataInfo = Db::table("statistical_download")->where(['code' => $code, 'user_id' => $user_id])->find();
        if(empty($dataInfo)){
            $data = [
                "click_id" => $click_id,
                //"minorNum"    => $_ClientData["minorNum"],
                "code"    => $code,
                "user_id"    => $user_id,
                "addtime"   => date("Y-m-d H:i:s", time())
            ];
            $res = Db::table("statistical_download")->insert($data);
            if(!empty($res)){
                return json(['status' => '200', 'message' => '下载量增加成功', 'data' => []]);
            }else{
                return json(['status' => '500', 'message' => '下载量增加失败', 'data' => []]);
            }
        }
        return json(['status' => '200', 'message' => '下载量增加成功', 'data' => []]);
    }


}
