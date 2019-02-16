<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/6/5
 * Time: 10:53
 */

namespace app\admin\controller;

use think\Db;

class Menu extends Base
{
    public function __construct()
    {
        if(session('admin_info.admin_id') != 1){
            exit(json_encode(['code' => 500, 'message' => lang('error_5001'), 'data' => []]));
        }
        parent::__construct();
    }

    /**
     * 递归菜单
     * @param $data
     * @param $pId
     * @return array|string
     */
    public function getTree($data, $pId)
    {
        $tree = [];
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pId) {
                $v['children'] = $this->getTree($data, $v['id']);
                $tree[]     = $v;
            }
        }
        return $tree;
    }

    /**
     * 获取所有菜单
     * @param $type
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function menu_list()
    {
        $menu_list = Db::name('system_menu')->order('sort asc')->select();
        return json(['code' => 200, 'message' => lang('success'), 'data' => $menu_list]);
//        return json(['code' => 200, 'message' => lang('success'), 'data' => $this->getTree($menu_list, 0)]);
    }


    /**
     * 节点编辑
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function menu_edit()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['id'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if ($post_data['type'] == 1) {
            $menu_info = Db::name('system_menu')->where('id', $post_data['id'])->find();
            return json(['code' => 200, 'message' => lang('success'), 'data' => $menu_info]);
        }
        if ($post_data['type'] == 2) {
            $save_data = array(
                'name'   => $post_data['name'],
                'en_name'   => $post_data['en_name'],
                'group'  => $post_data['group'],
                'pid'    => $post_data['pid'],
                'right'  => $post_data['right'],
                'type'   => $post_data['type'],
                'status' => $post_data['status'],
                'sort'   => $post_data['sort'],
                'level'  => $post_data['level'],
                'url'    => $post_data['url']
            );
            $res       = Db::name('system_menu')->where('id', $post_data['id'])->update($save_data);
            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }
    }

    /**
     * 添加节点
     * @return \think\response\Json
     */
    public function menu_add()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['name']) || empty($post_data['group']) || !isset($post_data['pid']) || empty($post_data['right']) || empty($post_data['level']) || empty($post_data['en_name'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        $add_data = array(
            'name'   => $post_data['name'],
            'en_name'   => $post_data['en_name'],
            'group'  => $post_data['group'],
            'pid'    => $post_data['pid'],
            'right'  => $post_data['right'],
            'type'   => $post_data['level'] == 'Top' ? 0 : 1,
            'status' => $post_data['status'],
            'sort'   => $post_data['sort'],
            'level'  => $post_data['level'],
            'url'    => $post_data['url']
        );
        $res       = Db::name('system_menu')->insert($add_data);
        if (false !== $res) {
            return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
        } else {
            return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
        }
    }

    /**
     * 返回序列化后的菜单
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function menu_all()
    {
        $menu_list = Db::name('system_menu')->select();
        $menu_data = $this->getTree($menu_list,0);
        $html = array();
        foreach ($menu_data as $key => $value){
            $html[] = array('name'=>'│──&nbsp;&nbsp;'.$value['name'],'value'=>$value['id']);
            if(is_array($value['children'])){
                foreach ($value['children'] as $key2 => $value2){
                    $html[] = array('name'=>'│&nbsp;&nbsp;&nbsp;└─&nbsp;&nbsp;'.$value2['name'],'value'=>$value2['id']);
                    if(is_array($value2['children'])){
                        foreach ($value2['children'] as $key3 => $value3){
                            $html[] = array('name'=>'│&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; │&nbsp; └─&nbsp;&nbsp;'.$value3['name'],'value'=>$value3['id']);
                        }
                    }
                }
            }
        }

        return json(['code' => 200, 'message' => lang('success'), 'data' => $html]);
    }
}