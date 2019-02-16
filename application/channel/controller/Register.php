<?php
namespace app\channel\controller;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12
 * Time: 18:09
 */
use think\Controller;
use think\Db;
use Sms\WeiLaiWuXian;
use think\exception\Handle;
use Redis\redisServer;

class Register extends Controller
{
    /*
     * 查询出推广APP下载链接的信息
     * */
    public function index()
    {
        $request = request();
        $data = $request->param();

        if(isset($data['code']) && !empty($data['code'])){
            redisServer::getInstance()->set(session_id().'code', $data['code'], 3600);
            //cookie('code', $data['code'], 3600);
        }else{
            $code =redisServer::getInstance()->get(session_id().'code');
            if(empty($code)){
                redisServer::getInstance()->set(session_id().'code', '62-81166813', 3600);
                //cookie('code', '62-81166813', 3600);
                $data['code'] = '62-81166813';
            }else{
                $data['code'] = $code;
            }
        }

        $request_data = [
            'url' => 'code='.$data['code'],
            'addtime' => date("Y-m-d H:i:s", time()),
            'status' => 0,
            'code' => $data['code'],
            'ip' => request()->ip(),
        ];
        $click_id = Db::table('statistical_click')->insertGetId($request_data);
        if (empty($click_id)) return json(['status' => '500', 'message' => '入库失败，请联系管理员', 'data' => []]);

        redisServer::getInstance()->set(session_id().'click_id', $click_id, 3600);
        //cookie('click_id', $click_id, 3600);
        $company_code = Db::table('statistical_adv')->where('code', $data['code'])->value('company_code');
        $arr = [];
        $arr['company_code'] = $company_code;
        $arr['code'] = $data['code'];
        return json(['status' => '200', 'message' => '成功', 'data' => $arr]);
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
        $auth_code = request()->post('authcode');//验证码
        $qudao_code = redisServer::getInstance()->get(session_id().'code');//渠道code
        $company_code = Db::table('statistical_adv')->where('code', $qudao_code)->value('company_code');
        if(empty($company_code)){
            $company_code = '5aab9fb19ecea';
            $qudao_code = '62-81166813';
        }
        if (empty($phone))
            return json(['status' => '500', 'message' => "手机号不能为空", 'data' => []]);//请填写手机号码
        if (!check_phone($phone))
            return json(['status' => '500', 'message' => "手机号码格式错误", 'data' => []]);//手机号码格式错误
        $has_user = Db::name('users')->where('phone', $phone)->count();
        if ($has_user)
            return json(['status' => '1', 'message' => "此号码已经被注册了", 'data' => []]);//此号码已经被注册了
        if (empty($password))
            return json(['status' => '500', 'message' => "请填写密码", 'data' => []]);//请填写密码
        if (!empty($password)) {
            $code = valid_pass($password);
            switch ($code) {
                case 1:
                    return json(array('status' => '500', 'message' => "密码必须包含至少一个字母"));//密码必须包含至少一个字母
                    break;
                case 2:
                    return json(array('status' => '500', 'message' => "密码必须包含至少一个数字"));
                    break;
                case 3:
                    return json(array('status' => '500', 'message' => "密码长度必需在8至14个字符"));
                    break;
                default:
                    break;
            }
        }

        $session_id = session_id();
        $is_agree   = request()->post('is_agree');
        if (!$is_agree) {
            return json(['status' => '500', 'message' => '未同意注册协议', 'data' => []]);
        }

        if (!check_code($phone, $auth_code, 1)) {
            return json(['status' => '500', 'message' => '手机验证码不正确', 'data' => []]);
        }

        $source                  = request()->post('source');
        $data['session_id']      = $session_id;
        $data['phone']           = $phone;
        $data['password']        = md5(trim($password));
        $data['reg_time']        = time();
        $data['last_login_time'] = time();
        //默认用户名为手机号
        $data['user_name']        = $phone;
        $data['source']           = empty($source) ? "natural" : $source;
        $data['name']             = $data['head_img'] = $data['idcode'] = $data['company_add'] = $data['company_tel'] = $data['server_name'] = $data['thresholds'] = '';
        $data['face_genuineness'] = $data['pair_verify_similarity'] = $data['credit_img'] = $data['phone_code'] = '';
        $data['is_marrey']        = $data['city'] = 0;
        $data['company_code']     = $company_code;
        $data['code']     = $qudao_code;
        $user_id           = Db::name('users')->insertGetId($data);
        redisServer::getInstance()->set(session_id().'user_id', $user_id, 3600);
        //cookie('user_id', $user_id, 3600);

        $arr['user_id']    = $user_id;
        $arr['session_id'] = $session_id;
        if ($user_id !== false) {
            $data = [
                "user_id" => $user_id,
                "click_id" => redisServer::getInstance()->get(session_id().'click_id'),
                "code"    => redisServer::getInstance()->get(session_id().'code'),
                "addtime"   => date("Y-m-d H:i:s", time())
            ];
            Db::table("statistical_register")->insert($data);
            return json(['status' => '200', 'message' => '注册成功', 'data' => []]);
        } else {
            return json(['status' => '500', 'message' => '网络繁忙，请稍候再试', 'data' => []]);
        }
    }

    /*
     * 判断验证码是否正确
     * */
    public function check_code()
    {
        $phone     = request()->post('phone');
        $auth_code = request()->post('authcode');
        if(empty($phone) || empty($auth_code)) return json(['status' => '500', 'message' => '参数不能为空', 'data' => []]);
        if (!check_code_new($phone, $auth_code, 1)) {
            return json(['status' => '500', 'message' => '手机验证码不正确', 'data' => []]);
        }
        return json(['status' => '200', 'message' => '验证通过', 'data' => []]);
    }

    /**
     * 发送短信验证码
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function new_send_msg()
    {
        $mobile = request()->post('phone');
        $company_code = request()->post('companycode');
        $type   = request()->post('type'); //1,注册 2,忘记密码，3.登入
        $type = (int)$type;

        if (empty($mobile)) return json(['status' => '500', 'message' => '手机号不能为空', 'data' => []]);
        if (empty($type)) return json(['status' => '500', 'message' => '验证码类型不能为空', 'data' => []]);
        $code = mt_rand(100000, 999999);
        if (!check_phone($mobile)) {
            return json(['status' => '500', 'message' => '手机号码格式错误', 'data' => []]);
        }
        if(empty($company_code)){
            return json(['status' => '500', 'message' => '缺少重要参数', 'data' => []]);
        }
        $res = $this->send_to_sms(1,$mobile,$code,$company_code,$type);
        if ($res) {
            $arrayName = array(
                'code'     => $code,
                'phone'    => $mobile,
                'type'     => $type,
                'add_time' => time(),
                'company_code' => $company_code,
            );
            //先删除过期的验证码
            Db::name('session_code')->where(['phone'=>$mobile,'type'=>$type])->delete();
            Db::name('session_code')->insert($arrayName);
            return json(['status' => '200', 'message' => '验证码发送成功', 'data' => []]);//短信发送成功
        } else {
            return json(['status' => '500', 'message' => '验证码发送失败', 'data' => []]);//短信发送失败
        }
    }

    /**
     * 发送短信类
     * @param $mobile
     * @param $content
     * @param string $type
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function send_to_sms($type,$mobile, $content,$company_code='',$template=0)
    {
        $wlwx = new WeiLaiWuXian($type,$company_code,check_env());

        $result = $wlwx->send($mobile,$content,$template);
        //写入日志
        $sms_log = [
            'phone' => $mobile,
            'content' => $content,
            'type' => $type,
            'sms_name' => '未来无线',
            'return_data' => json_encode($result),
            'company_code' => $company_code,
            'template' => $template,
            'sent_date' => date('Y-m-d H:i:s'),
            'sent_time' => time(),
        ];
        Db::name('sms_log')->insert($sms_log);

        return $result;
    }





}