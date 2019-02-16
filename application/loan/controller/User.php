<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 14:19
 */


namespace app\loan\controller;

use Redis\redisServer;
use think\Db;

class User extends Common
{
    // 用户注册类接口
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 忘记密码
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function forget_pass()
    {
        $phone    = request()->post('phone');
        $code     = request()->post('authCode');
        $userInfo = Db::name('users')->where(array("phone" => $phone))->find();
        if (!$userInfo) {
            return json(['status' => '500', 'message' => lang('no_uname'), 'data' => []]);//此用户不存在,请重新输入
        }
        if (!check_code($phone, $code, 2)) {
            return json(array('status' => '500', 'message' => lang('tellcode_f'), 'data' => array()));//手机验证码不正确
        }
        $session_id         = $this->get_session_id();
        $data['session_id'] = $session_id;
        $data['IMEI']       = request()->header('IMEI');
        $data['minorNum']   = request()->post('minorNum', '', 'trim');
        Db::name('users')->where(array('user_id' => $userInfo['user_id']))->update($data);
        $this->update_userinfo($userInfo['user_id']);
        $arr['user_id']    = $userInfo['user_id'];
        $arr['session_id'] = $session_id;
        if ($userInfo) {
            return json(['status' => '200', 'message' => lang('success'), 'data' => $arr]);
        } else {
            return json(['status' => '500', 'message' => lang('common_infos'), 'data' => []]);//网络繁忙，请稍候再试
        }
    }


    /**
     * 登录处理
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function do_login()
    {
        $phone        = request()->param('phone', '', 'trim');
        $password     = request()->param('password', '', 'md5');
        $code         = request()->param('code', '', 'trim');
        $minorNum     = request()->param('minorNum', '', 'trim');
        $gps_location = request()->param('gps_location', '', 'trim');        //gps经纬度 *
        $gps_address  = request()->param('gps_address', '', 'trim');         //gps地址 *
        $session_id   = $this->get_session_id();
        $login_type   = request()->post('login_type');

        //记录最后登录时间
        $data['last_login_time'] = time();
        $data['session_id']      = $session_id;
        $data['IMEI']            = request()->header('IMEI');
        if(empty($data['IMEI'])){
            return json(['status' => '500', 'message' => '请填写设备号', 'data' => []]);
        }
        $userInfo = Db::name('users')
            ->field('is_black,password,user_id,minorNum')
            ->where(array("phone" => $phone))
            ->find();
        if ($userInfo['is_black'] == 1) {
            return json(['status' => '500', 'message' => lang('isblack_1'), 'data' => []]);//黑名单用户，请联系管理员
        }
        if (!$userInfo) {
            return json(['status' => '500', 'message' => lang('no_uname'), 'data' => []]);//此用户不存在,请重新输入
        }
        /*login_type 等于 1(密码登录) 2(短信登录)*/
        if ($login_type == 1) {
            if ($userInfo['password'] != $password) {
                return json(['status' => '500', 'message' => lang('pwd_f'), 'data' => []]);//密码错误,请重新输入
            }
        } else {
            if (!check_code($phone, $code, 3)) {
                return json(array('status' => '500', 'message' => lang('tellcode_f'), 'data' => array()));//手机验证码不正确
            }
        }
        $affected_rows     = Db::name('users')->where(array('user_id' => $userInfo['user_id']))->update($data);
        $arr['session_id'] = $session_id;
        $arr['user_id']    = $userInfo['user_id'];
        $arr['is_same']    = $userInfo['minorNum'] == $minorNum ? 1 : 0;
        if ($affected_rows !== false) {
            //推送下线
            if ($userInfo['minorNum'] != $minorNum) {
                $this->message_send($userInfo['user_id'], 1, ['minorNum' => $userInfo['minorNum']]);
                Db::name('users')
                    ->where('user_id', $userInfo['user_id'])
                    ->update(array('minorNum' => $minorNum, 'gps_location' => $gps_location, 'gps_address' => $gps_address, 'ip_address' => request()->ip()));
            }
            //更新个人信息
            $this->update_userinfo($userInfo['user_id']);
            //添加用户的登录流水
            $loginListInfo['user_id'] = $userInfo['user_id'];
            $loginListInfo['phone'] = $phone;
            $loginListInfo['deviceId'] = $data['IMEI'];
            $loginListInfo['captureTime'] = date('Y-m-d H:i:s');
            Db::name('user_login_list')->insert($loginListInfo);
            return json(['status' => '200', 'message' => lang('login_success'), 'data' => $arr]);
        } else {
            return json(['status' => '500', 'message' => lang('common_infos'), 'data' => []]);//网络繁忙，请稍候再试
        }
    }


    /**
     * 处理注册
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function do_reg()
    {
        $phone     = request()->post('phone');
        $password  = request()->post('password');
        $auth_code = request()->post('authCode');
        $minorNum   = request()->header('IMEI');
        $company_code = request()->header('COMPANYCODE');
        if (empty($phone))
            return json(['status' => '500', 'message' => lang('input_mobile'), 'data' => []]);//请填写手机号码
        if (!check_phone($phone))
            return json(['status' => '500', 'message' => lang('reg_mobile'), 'data' => []]);//手机号码格式错误
        $has_user = Db::name('users')->where('phone', $phone)->count();
        if ($has_user)
            return json(['status' => '500', 'message' => lang('reg_mobile_y'), 'data' => []]);//此号码已经被注册了
        if (empty($password))
            return json(['status' => '500', 'message' => lang('reg_pwd'), 'data' => []]);//请填写密码
        if (!empty($password)) {
            $code = $this->valid_pass($password);
            switch ($code) {
                case 1:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_1')));//密码必须包含至少一个字母
                    break;
                case 2:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_2')));//密码必须包含至少一个数字
                    break;
                case 3:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_3')));//密码必须包含至少含有8个字符
                    break;
                default:
                    break;
            }
        }

        if($this->is_open_redis == true){
            if (redisServer::getInstance()->exists('user_reg:user_' . $phone)) {
                return json(['status' => '500', 'message' => lang('reg_mobile_y'), 'data' => ['message' => 'redis not pass']]);//此号码已经被注册了
            }
        }else{
            if (\think\Cache::has('user_reg:user_' . $phone)) {
                return json(['status' => '500', 'message' => lang('reg_mobile_y'), 'data' => ['message' => 'redis not pass']]);//此号码已经被注册了
            }
        }

        if (empty($company_code)){
            return json(['status' => '500', 'message' => '请填写公司编码', 'data' => []]);
        }
        if (empty($minorNum)){
            return json(['status' => '500', 'message' => '请填写设备号', 'data' => []]);
        }
        if($this->is_open_redis == true){
            redisServer::getInstance()->set('user_reg:user_' . $phone, true);
        }else{
            \think\Cache::set('user_reg:user_' . $phone, true, 0);
        }

        $session_id = $this->get_session_id();
        $is_agree   = request()->post('is_agree');
        if (!$is_agree) {
            return json(['status' => '500', 'message' => lang('register_agreef'), 'data' => []]);//未同意注册协议
        }
        if (!check_code($phone, $auth_code, 1)) {
            if($this->is_open_redis == true){
                if (redisServer::getInstance()->exists('user_reg:user_' . $phone)) {
                    redisServer::getInstance()->delete('user_reg:user_' . $phone);
                }
            }else{
                if (\think\Cache::has('user_reg:user_' . $phone)) {
                    \think\Cache::rm('user_reg:user_' . $phone);
                }
            }

            return json(array('status' => '500', 'message' => lang('tellcode_f'), 'data' => array()));//手机验证码不正确
        }
        $source                  = request()->post('source');
        $data['IMEI']            = $minorNum;
        $data['session_id']      = $session_id;
        $data['phone']           = $phone;
        $data['password']        = md5(trim($password));
        $data['reg_time']        = time();
        $data['last_login_time'] = time();
        $data['minorNum']        = $minorNum;
        //默认用户名为手机号
        $data['user_name']        = $phone;
        $data['source']           = empty($source) ? "natural" : $source;
        $data['name']             = $data['head_img'] = $data['idcode'] = $data['company_add'] = $data['company_tel'] = $data['server_name'] = $data['thresholds'] = '';
        $data['face_genuineness'] = $data['pair_verify_similarity'] = $data['credit_img'] = $data['phone_code'] = '';
        $data['is_marrey']        = $data['city'] = 0;
        $data['company_code']     = $company_code;
        $user_id           = Db::name('users')->insertGetId($data);

        $arr['user_id']    = $user_id;
        $arr['session_id'] = $session_id;
        if ($user_id !== false) {
            $this->update_userinfo($user_id);
            return json(['status' => '200', 'message' => lang('register_success'), 'data' => $arr]);//注册成功
        } else {
            if($this->is_open_redis == true){
                if (redisServer::getInstance()->exists('user_reg:user_' . $phone)) {
                    redisServer::getInstance()->delete('user_reg:user_' . $phone);
                }
            }else{
                if (\think\Cache::has('user_reg:user_' . $phone)) {
                    \think\Cache::rm('user_reg:user_' . $phone);
                }
            }
            return json(['status' => '500', 'message' => lang('common_infos'), 'data' => []]);//网络繁忙，请稍候再试
        }
    }

    /**
     * 密码正则
     * @param $candidate
     * @return bool
     */
    public function valid_pass($candidate)
    {
        $r1 = '/[a-zA-Z]/';  //uppercase
        $r3 = '/[0-9]/';  //numbers
        if (preg_match_all($r1, $candidate, $o) < 1) {
            return 1;//json(array('status' => '500', 'message' => lang('reg_pwd_1')));//密码必须包含至少一个字母
        }
        if (preg_match_all($r3, $candidate, $o) < 1) {
            return 2;//json(array('status' => '500', 'message' => lang('reg_pwd_2')));//密码必须包含至少一个数字
        }
        if (strlen($candidate) < 8) {
            return 3;//json(array('status' => '500', 'message' => lang('reg_pwd_3')));//密码必须包含至少含有8个字符
        }
    }

    /**
     * 重置密码
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function reset_pass()
    {
        //$this->check_login();
        $password     = request()->post('password', '', 'trim');
        $user_id      = request()->post('user_id', '', 'intval');
        $cfm_password = request()->post('cfm_password', '', 'trim');
        if (empty($password)) {
            return json(['status' => '500', 'message' => lang('reg_czpwd_1'), 'data' => []]);//登录密码不能为空
        }
        if (empty($cfm_password)) {
            return json(['status' => '500', 'message' => lang('reg_czpwd_2'), 'data' => []]);//确认登录密码不能为空
        }
        if ($password !== $cfm_password) {
            return json(['status' => '500', 'message' => lang('reg_czpwd_3'), 'data' => []]);//两次输入的密码不一致
        }
        if (!empty($password)) {
            $code = $this->valid_pass($password);
            switch ($code) {
                case 1:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_1')));//密码必须包含至少一个字母
                    break;
                case 2:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_2')));//密码必须包含至少一个数字
                    break;
                case 3:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_3')));//密码必须包含至少含有8个字符
                    break;
                default:
                    break;
            }
        }
        $user = Db::name('users')->where(array('user_id' => $user_id))->find();
        if ($user) {
            if ($user['password'] == md5($password)) {
                return json(['status' => '500', 'message' => lang('reg_czpwd_same'), 'data' => []]);//新密码不能与原密码相同
            }
            $info = Db::name('users')->where(array('user_id' => $user_id))->update(array('password' => md5($password)));
            if ($info) {
                return json(['status' => '200', 'message' => lang('reg_czpwd_s'), 'data' => []]);//密码重置成功
            } else {
                return json(['status' => '500', 'message' => lang('reg_czpwd_f'), 'data' => []]);//密码重置失败
            }
        } else {
            return json(['status' => '500', 'message' => lang('reg_register'), 'data' => []]);//您还没注册，请先注册
        }
    }

    /**
     * 安全退出
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function logout()
    {
        $user_id    = request()->post('user_id', 'intval', '');//退出清空绑定设备号
        $order_data = array(
            'minorNum'   => '',
            'session_id' => '',
            'IMEI'       => '',
        );
        Db::name('users')->where(array('user_id' => $user_id))->update($order_data);
        if($this->is_open_redis == true){
            $userinfo_exists = redisServer::getInstance()->exists('user_info:user_' . $user_id);
            if ($userinfo_exists) {
                redisServer::getinstance()->delete('user_info:user_' . $user_id);
            }
        }else{
            $userinfo_exists = \think\Cache::has('user_info:user_' . $user_id);
            if ($userinfo_exists) {
                \think\Cache::rm('user_info:user_' . $user_id);
            }
        }

        return json(['status' => '200', 'message' => lang('safe_quit'), 'data' => []]);
    }


    public function user_img_trail()
    {
        return json(array('status' => '200', 'message' => lang('success')));
    }

    /**
     * 找回密码
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function find_password()
    {
        $post_data = request()->post();
        if ($post_data['password'] !== $post_data['cfm_password']) {
            return json(['status' => '500', 'message' => lang('reg_czpwd_3'), 'data' => []]);//两次输入的密码不一致
        }
        if (!empty($post_data['password'])) {
            $code = $this->valid_pass($post_data['password']);
            switch ($code) {
                case 1:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_1')));//密码必须包含至少一个字母
                    break;
                case 2:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_2')));//密码必须包含至少一个数字
                    break;
                case 3:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_3')));//密码必须包含至少含有8个字符
                    break;
                default:
                    break;
            }
            $check = check_code($post_data['phone'], $post_data['code'], 5);
            if ($check) {
                Db::name('users')
                    ->where('phone', $post_data['phone'])
                    ->update(['password' => md5($post_data['password'])]);
                return json(['status' => '200', 'message' => lang('success'), 'data' => []]);
            } else {
                return json(array('status' => '500', 'message' => lang('tellcode_f'), 'data' => []));
            }

        }
    }

    /**
     * 修改密码
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function change_password()
    {
        $post_data = request()->post();
        if ($post_data['password'] !== $post_data['cfm_password']) {
            return json(['status' => '500', 'message' => lang('reg_czpwd_3'), 'data' => []]);//两次输入的密码不一致
        }
        if (!empty($post_data['password'])) {
            $code = $this->valid_pass($post_data['password']);
            switch ($code) {
                case 1:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_1')));//密码必须包含至少一个字母
                    break;
                case 2:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_2')));//密码必须包含至少一个数字
                    break;
                case 3:
                    return json(array('status' => '500', 'message' => lang('reg_pwd_3')));//密码必须包含至少含有8个字符
                    break;
                default:
                    break;
            }
        }
        $user_info = Db::name('users')
            ->where('user_id', $post_data['user_id'])
            ->find();
        if ($user_info['password'] == md5($post_data['old_password'])) {
            Db::name('users')
                ->where('user_id', $post_data['user_id'])
                ->update(['password' => md5($post_data['password'])]);
            return json(['status' => '200', 'message' => lang('success'), 'data' => []]);
        } else {
            return json(array('status' => '500', 'message' => lang('pwd_f'), 'data' => []));
        }
    }

    /**
     * 修改手机号
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function change_phone()
    {
        $post_data = request()->post();
        $user_info = Db::name('users')
            ->where('user_id', $post_data['user_id'])
            ->find();
        if (md5($post_data['password']) == $user_info['password']) {
            $has_phone = Db::name('users')->where('phone', $post_data['phone'])->count();
            if ($has_phone) {
                return json(array('status' => '500', 'message' => lang('reg_mobile_y'), 'data' => []));
            }
            $has_order = Db::name('order_info')
                ->where('user_id', $post_data['user_id'])
                ->where('order_status', 'neq', '200')
                ->find();
            if ($has_order) {
                return json(array('status' => '500', 'message' => lang('exist_order'), 'data' => []));
            }
            $user = Db::name('users')
                ->where('phone', $post_data['old_phone'])
                ->where('password', md5($post_data['password']))
                ->find();
            if (empty($user)) {
                return json(['status' => '400', 'message' => lang('not_user'), 'data' => []]);
            }

            $check = check_code($post_data['phone'], $post_data['code'], 6);
            if (!$check) {
                return json(array('status' => '500', 'message' => lang('tellcode_f'), 'data' => []));
            }
            $update_data = [
                'phone'     => $post_data['phone'],
                'user_name' => $post_data['phone'],
            ];
            Db::name('users')
                ->where('user_id', $post_data['user_id'])
                ->update($update_data);
            return json(['status' => '200', 'message' => lang('success'), 'data' => []]);
        } else {
            return json(array('status' => '500', 'message' => lang('pwd_f'), 'data' => []));
        }

    }
}