<?php
namespace app\admin\controller;

use think\Db;

class Pormotion extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * 查询出公司推广APP的链接的信息
     * */
    public function showinfo()
    {
        $post_data = request()->param();
        if (!empty($post_data['limit'])) {
            $this->limit = $post_data['limit'];
        }
        $roleId = Db::name('system_admin_role_v2')->where(['role_id' => session('admin_info')['role_id']])->value('admin_class');
        if($roleId == 5){  //查询自己公司的推广信息
            $map['status'] = 1;
            $map['company_id'] = session('company_id');
        }elseif ($roleId == 6){ //查询平台的推广信息
            $map['status'] = 1;
        }else{
            return $this->buildFailed(401, '您没有权限查看该信息');
        }
        $downloadInfo = Db::name('hunuo_promotion_download')->where($map)->field('id,company_url,image_url,image_name,android_url,android_name,app_url')->select();
        $order_list_count = Db::name('hunuo_promotion_download')->where($map)->count();
        $data['list'] = $downloadInfo;
        $data['page']  = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / 20),
        );
        return $this->buildSuccess($data, '查询成功');
    }

    /*
     * 添加和编辑公司推广APP的链接
     * */
    public function addextensionlink()
    {
        $roleId = Db::name('system_admin_role_v2')->where(['role_id' => session('admin_info')['role_id']])->value('admin_class');
        if($roleId != 5)  return $this->buildFailed(401, '您没有权限操作该推广链接');

        $type= request()->post('type');
        $id = request()->post('id');
        $imageCode= request()->post('image_code');
        $androidCode = request()->post('android_code');
        $appleUrl = request()->post('appurl', '', 'trim');
        $companyUrl = request()->post('company_url', '', 'trim');
        if ($type == 2 && empty($id)) return $this->buildFailed(402, 'id不能为空');
        if(empty($imageCode) || empty($androidCode) || empty($appleUrl) || empty($companyUrl)) return $this->buildFailed(402, lang('error_4002'));

        if($type == 1){
            $downloadId = Db::name('hunuo_promotion_download')->where(['status' => 1, 'company_url' => $companyUrl])->field('id')->find();
            if(isset($downloadId) || !empty($downloadId)) return $this->buildFailed(402, lang('error_4017'));
        }

        $imageInfo = explode('/', $imageCode);
        $androidInfo = explode('/', $androidCode);
        $addData['create_uid'] = session('admin_id');
        $addData['company_id'] = session('company_id');
        $addData['company_url'] = $companyUrl;
        $addData['image_code'] = $imageCode;
        $addData['image_url'] = get_oss_image(getOssConfig(), $imageCode);
        $addData['image_name'] = $imageInfo[3];
        $addData['android_code'] = $androidCode;
        $addData['android_url'] = get_oss_image(getOssConfig(), $androidCode);
        $addData['android_name'] = $androidInfo[3];
        $addData['app_url'] = $appleUrl;
        $addData['create_time'] = (int)$_SERVER['REQUEST_TIME_FLOAT'];
        try {
            if($type == 1){
                if(empty(Db::name('hunuo_promotion_download')->insert($addData))) return $this->buildFailed(401, '添加推广链接信息失败');
                return $this->buildSuccess($addData, '添加成功');
            }else{
                if(empty(Db::name('hunuo_promotion_download')->where(['id' => $id])->update($addData))) return $this->buildFailed(401, '添加推广链接信息失败');
                return $this->buildSuccess($addData, '编辑成功');
            }


        } catch (OssException $e) {
            trace('推广APP链接错误信息', $e->getMessage());
            return $this->buildFailed(401, $e->getMessage().'操作失败');
        }
    }

    /*
     * 查询出单条推广链接的信息
     * */
    public function showonelink()
    {
        $downloadId = request()->post('id');
        if(empty($downloadId)) return $this->buildFailed(402, lang('error_4002'));

        $downloadInfo = Db::name('hunuo_promotion_download')->where(['status' => 1, 'id' => $downloadId])->field('id,image_code,company_url,image_url,android_url,app_url')->find();
        return $this->buildSuccess($downloadInfo, '查询成功');

    }
}
