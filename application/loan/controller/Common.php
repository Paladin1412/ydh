<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/10
 * Time: 15:00
 */

namespace app\loan\controller;

use JPush\JPush;
use Sms\ChuangLan;
use Sms\TianYiHong;
use Redis\redisServer;
use think\Controller;
use think\Db;
use Sms\WeiLaiWuXian;
use think\Config;
class Common extends Controller
{
    protected $env;
    protected $is_open_mongo;
    protected $is_open_redis;

    // 全局函数继承类
    public function __construct()
    {
        parent::__construct();
        $this->is_open_mongo = config('auth_' . check_env() . '.IS_OPEN_MONGO');
        $this->is_open_redis = config('auth_' . check_env() . '.IS_OPEN_REDIS');
        $this->env = check_env();
        $result = $this->verify_action();
    }

    /**
     * 验证签名
     * @return bool
     */
    private function verify_action()
    {
        $_white_array = config('loan.white_lib');
        $action       = request()->controller() . '/' . request()->action();
        if (!in_array($action, $_white_array)) {
            $post_data = request()->param();
            $config = Config::get('auth_'.$this->env);
            if (!isset($post_data['scene'])) {
                $secret   = $config[$post_data['api_key']];
                $api_sign = $post_data['api_sign'];
                unset($post_data['api_sign']);
                ksort($post_data);
                $info = $secret;
                foreach ($post_data as $k => $v) {
                    if (!is_array($v)) {
                        $info .= $k . $v;
                    }
                }
                $info .= $secret;
                if (strtoupper(md5($info)) !== $api_sign) {
                    exit(json_encode(['status' => 500, 'message' => '您没有权限进行此操作', 'data' => []]));
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * 检测用户账户是否登录
     * @return \think\response\Json
     */
    public function check_login()
    {
        $user_id             = request()->param('user_id');
        $IMEI                = request()->header('IMEI');
        $session_id          = request()->header('SESSION');
        //修改开始
        if($this->is_open_redis == true){
            $user_info_keyexists = redisServer::getInstance()->exists('user_info:user_' . $user_id);
            if (!$user_info_keyexists) {
                exit(json_encode(['status' => '300', 'message' => lang('p_login'), 'data' => []]));
            }
            $user_info_json = redisServer::getInstance()->get('user_info:user_' . $user_id);
        }else{
            $user_info_keyexists = \think\Cache::has('user_info:user_' . $user_id);
            if (!$user_info_keyexists) {
                exit(json_encode(['status' => '300', 'message' => lang('p_login'), 'data' => []]));
            }
            $user_info_json = \think\Cache::get('user_info:user_' . $user_id);
        }
        //修改结束
        $user_info      = json_decode($user_info_json, true);
        if ($user_info) {
            if ($user_info['is_black']) {
                exit(json_encode(['status' => '600', 'message' => lang('blacklist_y'), 'data' => []]));//命中黑名单
            } else if ($user_info['session_id'] != $session_id || $user_info['IMEI'] != $IMEI) {
                exit(json_encode(['status' => '800', 'message' => lang('session_status'), 'data' => []]));//您的账号在另一设备登录,您已被迫下线
            }
            $rand_num = rand(1, 3);
            if ($rand_num == 2) {
                //修改开始
                if($this->is_open_redis == true){
                    redisServer::getInstance()->set('user_info:user_' . $user_id, json_encode($user_info), 60 * 60 * 24 * 18);
                }else{
                    \think\Cache::set('user_info:user_' . $user_id, json_encode($user_info), 60 * 60 * 24 * 18);
                }
                //修改结束
            }
        } else {
            exit(json_encode(['status' => '300', 'message' => lang('p_login'), 'data' => []]));//请登录后再执行此操作
        }
    }

    //获取用户信息
    public function get_userinfo($user_id){
        //修改开始
        if($this->is_open_redis == true){
            $user_info_keyexists = redisServer::getInstance()->exists('user_info:user_' . $user_id);
        }else{
            $user_info_keyexists = \think\Cache::has('user_info:user_' . $user_id);
        }

        if (!$user_info_keyexists) {
            $user_info = Db::name('users')->where('user_id',$user_id)->find();
        }else{
            if($this->is_open_redis == true){
                $user_info_json = redisServer::getInstance()->get('user_info:user_' . $user_id);
            }else{
                $user_info_json = \think\Cache::get('user_info:user_' . $user_id);
            }
            $user_info = json_decode($user_info_json, true);
        }

        return $user_info;
    }

    /**
     * 获取sessin_id
     * @return string
     */
    public function get_session_id()
    {
        session_start();
        $session_id = session_id();
        return $session_id;
    }

    /**
     * 更新用户信息
     * @param int $user_id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update_userinfo($user_id = 0)
    {
        $userInfo = Db::name('users')->where('user_id',$user_id)->find();
        //修改开始
        if($this->is_open_redis == true){
            redisServer::getInstance()->set('user_info:user_' . $user_id, json_encode($userInfo), 60 * 60 * 24 * 18);
        }else{
            \think\Cache::set('user_info:user_' . $user_id, json_encode($userInfo), 60 * 60 * 24 * 18);
        }
        //修改结束
    }

    /**
     * 城市列表
     * @return \think\response\Json
     */
    public function city_list()
    {
        $list = getRegionList('city');
        return json(['status' => '200', 'message' => lang('success'), 'data' => $list]);
    }


    /**
     * 联系人关系列表
     * @return \think\response\Json
     */
    public function relation_list()
    {
        $list = array(
            array('id' => 1, 'relaname' => lang('father'), 'rela_en_name' => 'father'),
            array('id' => 2, 'relaname' => lang('mother'), 'rela_en_name' => 'mother'),
            array('id' => 3, 'relaname' => lang('brother'), 'rela_en_name' => 'brother'),
            array('id' => 4, 'relaname' => lang('sister'), 'rela_en_name' => 'sister'),
            array('id' => 5, 'relaname' => lang('friend'), 'rela_en_name' => 'friend'),
            array('id' => 6, 'relaname' => lang('children'), 'rela_en_name' => 'children'),
            array('id' => 7, 'relaname' => lang('colleague'), 'rela_en_name' => 'colleague'),
            array('id' => 8, 'relaname' => lang('other'), 'rela_en_name' => 'other'),
            array('id' => 9, 'relaname' => lang('spouse'), 'rela_en_name' => 'spouse'),
        );
        return json(['status' => '200', 'message' => lang('success'), 'data' => $list]);
    }

    /**
     * 学历关系列表
     * @return \think\response\Json
     */
    public function education_list()
    {
        $list = array(
            array('id' => 1, 'edu_name' => '硕士及以上'),        //硕士及以上
            array('id' => 2, 'edu_name' => '本科'),                //本科
            array('id' => 3, 'edu_name' => '大专'),                    //大专 junior_college_education
            array('id' => 4, 'edu_name' => '高中'),                //高中 senior_school
            array('id' => 5, 'edu_name' => '初中以下'),                    //初中以下 Junior_school
        );
        return json(['status' => '200', 'message' => lang('success'), 'data' => $list]);
    }

    /**
     * 行业关系列表
     * @return \think\response\Json
     */
    public function industry_list()
    {
        $list = array(
            array('id' => 1, 'indus_name' => '互联网/IT'),     
            array('id' => 2, 'indus_name' => '金融'),
            array('id' => 3, 'indus_name' => '房地产/建筑'),
            array('id' => 4, 'indus_name' => '商业服务'),
            array('id' => 5, 'indus_name' => '贸易/批发/零售'),
            array('id' => 6, 'indus_name' => '教育/艺术'),
            array('id' => 7, 'indus_name' => '服务业'),
            array('id' => 8, 'indus_name' => '文化/传媒/娱乐'),
            array('id' => 9, 'indus_name' => '制造业'),
            array('id' => 10, 'indus_name' => '物流运输'),
            array('id' => 11, 'indus_name' => '能源/环保'),
            array('id' => 12, 'indus_name' => '政府/非盈利'),
            array('id' => 13, 'indus_name' => '农林牧渔'),
        );
        return json(['status' => '200', 'message' => lang('success'), 'data' => $list]);
    }

    /**
     * /职业关系列表
     * @return \think\response\Json
     */
    public function profession_list()
    {
        $list = array(
            array('id' => 1, 'profess_name' => '销售|客服|市场'),
            array('id' => 2, 'profess_name' => '财务|人力资源|行政'),
            array('id' => 3, 'profess_name' => '项目|质量|高级管理'),
            array('id' => 4, 'profess_name' => 'IT|互联网|通信技术'),
            array('id' => 5, 'profess_name' => '房产|建筑|物业管理'),
            array('id' => 6, 'profess_name' => '金融从业者'),
            array('id' => 7, 'profess_name' => '采购|贸易|交通|物流'),
            array('id' => 8, 'profess_name' => '生产|制造'),
            array('id' => 9, 'profess_name' => '传媒|印刷|艺术|设计'),
            array('id' => 10, 'profess_name' => '咨询|法律|教育|翻译'),
            array('id' => 11, 'profess_name' => '服务业从业者'),
            array('id' => 12, 'profess_name' => '能源|环保|农业|科研'),
            array('id' => 13, 'profess_name' => '兼职|实习|社工|其他'),
        );
        return json(['status' => '200', 'message' => lang('success'), 'data' => $list]);
    }
    
    public function common_list()
    {
        $type = request()->param('type');
        if ($type == 'marry_type') {
            $list = lang('marry_type');
        }
        if ($type == 'live_list') {
            $list = lang('live_type');
        }
        if ($type == 'faith_list') {
            $list = lang('faith_type');
        }
        $data = [];
        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => $value) {
                $data[] = [
                    'id'   => $key,
                    'name' => $value
                ];
            }
        }
        return json(['status' => '200', 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 客服电话
     * @return \think\response\Json
     */
    public function tel()
    {
        $arr['tel'] = '02124521805';
        return json(['status' => '200', 'message' => lang('success'), 'data' => $arr]);
    }

    /**
     * 发送短息
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function send_msg()
    {
        $mobile = request()->post('phone');
        $company_code = request()->header('COMPANYCODE');
        if(empty($company_code)) $company_code = request()->post('COMPANYCODE');
        $type   = request()->post('type'); //1,注册 2,忘记密码，3.登入        
        $type = (int)$type;

        if (empty($mobile) || empty($type)) {
            return json(['status' => '500', 'message' => '手机号码格式错误', 'data' => []]);
        }
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
            return json(['status' => '200', 'message' => lang('msg_send_s'), 'data' => []]);//短信发送成功
        } else {
            return json(['status' => '400', 'message' => lang('internet_f'), 'data' => []]);//短信发送失败
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
        $wlwx = new WeiLaiWuXian($type,$company_code,$this->env);

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
        //修改开始 赵光帅
        if($this->is_open_mongo == true) mongo_log('sms_log', $sms_log);
        //修改结束

        return $result;
    }

    public function message_test(){
        $this->message_send(2086, 2, $params = []);
    }


    /**
     * @param $user_id
     * @param $type
     * @param $params
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function message_send($user_id, $type, $params = [])
    {
        $JPush_config = config('auth_' . $this->env . '.JPush');
        $push_obj     = new JPush($JPush_config);
        $user_info    = Db::name('users')->field('minorNum,phone,company_code')->where('user_id', $user_id)->find();
        $message_header = '【易贷还】';
        switch ($type) {
            case 1:// 下线推送 极光 Y
                $content = '您的账号在另一地点登录,您已被迫下线,退订回T.';
                $params  = [
                    'type' => 2,
                    'txt'  => json_encode([]),
                ];
                $result = $push_obj->push($user_info['minorNum'], $content, $params);
                break;
            case 2:// 风控审核不通过 极光 Y 短信 Y 消息 Y
                $content  = '抱歉,您申请的订单未通过平台审核，请往后再来,退订回T.';
                $this->send_notice($content, $user_id);//站内消息
                $params = [
                    'type' => 2,
                    'txt'  => json_encode([]),
                ];
                $push_obj->push($user_info['minorNum'], $content, $params);//极光推送
                $this->send_to_sms(2,$user_info["phone"], $content);
                break;
            case 3:// 订单提交 极光 Y 短信 Y 消息 Y
                $content  = '您申请的订单已经提交,审核通过后将为你放款,退订回T.';
                $this->send_notice($content, $user_id);
                $params = [
                    'type' => 2,
                    'txt'  => json_encode([]),
                ];
                $push_obj->push($user_info['minorNum'], $content, $params);
                $this->send_to_sms(2,$user_info["phone"], $content);
                break;
            case 4:// 放款完成 极光Y 短信 Y 消息 Y
                $content  = '尊敬的用户,您申请的贷款已经成功到达您的账户,退订回T.';
                $this->send_notice($content, $user_id);
                $params = [
                    'type' => 2,
                    'txt'  => json_encode([]),
                ];
                $push_obj->push($user_info['minorNum'], $content, $params);
                //短信
                $this->send_to_sms(2,$user_info["phone"], $content);
                break;
            case 5:// 还款完成  极光Y 短信 Y 消息 Y
                //信息推送
                $content  = '您已还款成功,还款金额为 ' . $params['price'].',退订回T';
                $this->send_notice($content, $user_id, 2);
                $params = [
                    'type' => 2,
                    'txt'  => json_encode([]),
                ];
                $push_obj->push($user_info['minorNum'], $content, $params);
                $this->send_to_sms(2,$user_info["phone"], $content);
                break;
            case 6:// 逾期提醒  极光Y 短信 Y 消息 Y 放入队列
                $content  = '明天是你最后的还款日,请及时还款，保持良好信用,退订回T.';
                $this->send_notice($content, $user_id, 2);
                $params = [
                    'type' => 2,
                    'txt'  => json_encode([]),
                ];
                $push_obj->push($user_info['minorNum'], $content, $params);
                $this->send_to_sms(2,$user_info["phone"], $content);
                break;
            case 7:// 逾期提醒  极光Y 短信 Y 消息 Y 放入队列
                $content  = '今天是您最后的还款日,请及时还款，保持良好信用,退订回T.';
                $this->send_notice($content, $user_id, 2);
                $params = [
                    'type' => 2,
                    'txt'  => json_encode([]),
                ];
                $push_obj->push($user_info['minorNum'], $content, $params);
                $this->send_to_sms(2,$user_info["phone"], $content);
                break;

        }
    }

    /**
     * APP内部消息推送
     * @param $config
     * @param $content
     * @param $user_id
     * @throws \think\Exception
     */
    private function send_notice($content, $user_id, $type = 1)
    {
        if((int)$type===1){
            $title = '系统消息';
        }else{
            $title = '还款消息';
        }
        $message_data = [
            'title'    => $title,
            'is_read'  => 0,
            'content'  => $content,
            'add_date' => date('Y-m-d H:i:s'),
            'add_time' => time(),
            'type' => $type,
            'user_id' => $user_id,
        ];
        $message_id   = Db::name('message')->insertGetId($message_data);
        return $message_id;
    }

}