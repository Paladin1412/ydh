<?php

namespace Admin;
use think\Db;
class AuthonV2 {


    // 权限白名单
	private $_WhiteList = array(
	    'Base@menu_list',
        'Base@company_list',
        'Base@role_list',
        'Base@get_role_type',
        'Base@order_handle_lang',
        'Base@handle_user_list',
        'Base@collect_user_list',
        'Base@get_order_flow_lang',
        'Base@get_country_list',
        'Base@get_user_info',
        'Base@set_lang',
        'Base@get_order_status',
        'Base@get_handle_not_pass_list',
        'Base@get_collection_s',
        'Base@get_order_quality',
        'Base@role_attribute_list',

        //标的管理过虑参数
        'Bid@list_filtr',
        //标的获取信息
        'Bid@info',
        //判断标的名是否存在
        'Bid@check_project_name',
        //资金管理过虑参数
        'Fund@filtr',
        //用户管理过虑参数
        'User@filtr'






        
    );
    
    // 权限黑名单
	private $_BackeList = array();
   
    // 是否开启权限验证
	private $_OpenCheck = TRUE;
    
    // 权限管理配置
	protected $_AuthoAccess = array(

		"authon_on"  => TRUE, //是否开启权限管理，默认是开启
		"authon_user"  => "system_admin_v2", //用户信息表
		"authon_role"  => "system_admin_role_v2", //用户角色表
		"authon_role_user" => "system_admin_role_relation_v2", //角色和用户信息对应表
		"authon_module" => "system_menu", //模块表
		"authon_role_module" => "system_role_menu_relation_v2", //规则和角色对应关系表
	);

	public function __construct() {
		//if(is_array($_Whitelist) && !empty($_Whitelist)) {
		//	$this->_Whitelist  = array_merge($this->_WhiteList,$_Whitelist);
		//}
		//if(is_array($_BackeList) && !empty($_BackeList)) {
		//	$this->_BackeList = array_merge($this->_BackeList,$_BackeList);
		//}
 	}

    // 验证权限的白名单
    private function verifyWhite($controller = NULL) {
        $whiteList = $this->_WhiteList;
        if(!empty($whiteList) && in_array($controller,$whiteList)) {
            return TRUE;
        }
        return FALSE;
    }

    // 获取用户的用户组
    public function getGroup($uid,$company) {
        $where = array(
            $this->_AuthoAccess["authon_role_user"].".admin_id" => $uid,
            $this->_AuthoAccess["authon_role_user"].'.type'  => 0, // 0表示正常 1表示禁用
            $this->_AuthoAccess["authon_role"].".company_id" => $company
        );
    	$Return = Db::table('system_admin_role_relation_v2')
            ->alias("roleuser")
            ->join($this->_AuthoAccess["authon_role"],$this->_AuthoAccess["authon_role"].".role_id=roleuser.role_id")
            ->field(array('roleuser.role_id','role_name'))
            ->fetchSql(false)
            ->where($where)
            ->select();  
        if(!empty($Return)) {
            return $Return;
        }
        return NULL;
    }

    // 获取角色对应的规则权限ID
    public function getRoleAuthon($rolearr = array()) {
        if(empty($rolearr) || !is_array($rolearr)) {
            return NULL;
        }
        $rolelist = array();
        foreach($rolearr as $key=>$value) {
            if(isset($value['role_id']) && !empty($value['role_id'])) {
                $rolelist[] = $value['role_id'];
            }
        }
        if(!empty($rolelist)) {
            return $rolelist;
        }
        return NULL;
    }

    // 获取权限规则数据
    public function getAuthon($rolearr = array(),$uid) {
        if(empty($rolearr) || !is_array($rolearr)) {
            return NULL;
        }
        if($uid == 1){

            $resultData = Db::table($this->_AuthoAccess["authon_module"])
                ->field(array($this->_AuthoAccess["authon_module"].".right",$this->_AuthoAccess["authon_module"].".id"))
                ->fetchSql(false)
                ->select();
        }else{
            $where = array(
                $this->_AuthoAccess["authon_role_module"].".role_id" => array("in",$rolearr)
            );
            $resultData = Db::table($this->_AuthoAccess["authon_role_module"])
                ->alias("rolemodule")
                ->join($this->_AuthoAccess["authon_module"],$this->_AuthoAccess["authon_module"].".id=rolemodule.menu_id")
                ->field(array($this->_AuthoAccess["authon_module"].".right",$this->_AuthoAccess["authon_module"].".id"))
                ->where($where)
                ->fetchSql(false)
                ->select();
        }
        if(!empty($resultData)) {
            return $resultData;
        }
        return NULL;
        
    }

    /**
        function: 判断是否为合作公司超级管理员
        param     $uid    int    管理员ID
                  $commpanyId    int     公司ID
        return   TRUE 表示是合作公司超级管理员 FALSE  表示不是合作公司的超级管理员
    **/
    private function getCommpanyAuthon($uid,$commpanyId) {
        if(empty($uid) || empty($commpanyId)) {
            return FALSE;
        }
        $where = array(
            $this->_AuthoAccess["authon_role_user"].".admin_id" => $uid,
            $this->_AuthoAccess["authon_role_user"].".type" =>0,
            $this->_AuthoAccess["authon_role"].".company_id" => $commpanyId,
            $this->_AuthoAccess["authon_role"].".type" => 0
        );
        $result = Db::table($this->_AuthoAccess["authon_role_user"])->alias("roleuser")->join($this->_AuthoAccess["authon_role"],$this->_AuthoAccess["authon_role"].".role_id=roleuser.role_id")->where($where)->find();
        if(!empty($result)) {
            return TRUE;
        }
        return FALSE;
    }

 	// 权限验证
 	public function Auth_Check($uid,$name,$commpanyId,$relation='or') {
 		if(FALSE === $this->_OpenCheck) {
 			return TRUE;
 		}
        // 如果是平台超级管理员的话就不用验证的权限，直接通过
        if($uid == 1) {
            return TRUE;
        }
        // 判断模块是否在白名单内
        if(TRUE == $this->verifyWhite($name)) {
            return TRUE;
        }
        // 获取管理人员的角色信息 如果为空就返回FALSE
        $userGroups = $this->getGroup($uid,$commpanyId);
        if(NULL === $userGroups) {
            return FALSE;
        }
        // 获取角色对应的权限，如果数据为NULL 就返回FALSE
        $roleAuthonList = $this->getRoleAuthon($userGroups);
        if(NULL == $roleAuthonList) {
            return FALSE;
        }
        $authonData = $this->getAuthon($roleAuthonList,$uid);
        if(NULL == $authonData) {
            return FALSE;
        }
        $list = array();
        foreach($authonData as $key=>$value) {
            $list[] = $value['right'];
        }
        if(in_array($name,$list)) {
            return TRUE;
        }
        return FALSE;
 	}

    // 获取后台头部信息
 	public function getTopmenu($uid,$commpanyId,$language) {
        //判断语言
        switch ($language) {
            case 'zh-cn':
                    $name = 'name';
                break;
            case 'en-us':
                    $name = 'en_name';
                break;
            case 'id-id':
                    $name = 'id_name';
                break;
            default:
                    $name = 'name';
                break;
        }
        $topmenu = Db::table($this->_AuthoAccess["authon_module"])->where(array("pid"=>0,"status"=>0))->field(array("id",$name,"group",'sort'))->order('sort asc')->select();
        // 获取角色
        $userGroups = $this->getGroup($uid,$commpanyId);
        $roleAuthonList = $this->getRoleAuthon($userGroups);
        // 获取菜单
        $authonData = $this->getAuthon($roleAuthonList,$uid);
        $authonList = array();
        if(!empty($authonData)) {
            foreach($authonData as $key=>$value) {
                $authonList[] = $value['id'];
            }
        }

        if(!empty($topmenu)) {
            $menuList = array();
            foreach($topmenu as $key=>$value) {
                $where = array(
                    "pid" => $value["id"],
                    "status" => 0,
                    "level" => "left",
                    "id" => array("in",$authonList)
                );
                $leftmenu = Db::table($this->_AuthoAccess["authon_module"])->where($where)->field(array("id",$name,"right",'url'))->select();
                if(!empty($leftmenu)) {

                    foreach($leftmenu as $mkey=>$mvalue) {
                        //if(strpos($mvalue["right"],"@") && $controller = explode("@",$mvalue["right"])) {
                            $leftmenu[$mkey]["name"] = $mvalue[$name];
                            if($language !== 'zh-cn'){
                                unset($leftmenu[$mkey][$name]);
                            }
                            //unset($leftmenu[$mkey]["right"]);
                        //}
                    }

                    $groupData = strtolower($value["group"]);
                    $menuList[$groupData]["name"] = $value[$name];
                    $menuList[$groupData]["child"] = $leftmenu;
                }

            }
            return $menuList;
        }
    }
}
