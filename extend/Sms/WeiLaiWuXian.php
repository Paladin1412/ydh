<?php
/**
 * Created by PhpStorm.
 * User: zgs
 * Date: 2018/11/27
 * Time: 下午9:06
 */
namespace Sms;

use think\Config;
class WeiLaiWuXian{
    protected $CUST_CODE = null;
    protected $CUST_PWD = null;
    protected $SMS_HOST = null;
    protected $type = null;
    protected $company_code = null;

    public function __construct($type,$company_code=null,$env){
        $wlwx_config = Config::get('auth_'.$env.'.WLWX');
        $this->CUST_CODE = $wlwx_config['CUST_CODE'];
        $this->CUST_PWD = $wlwx_config['CUST_PWD'];
        $this->SMS_HOST = $wlwx_config['SMS_HOST'];
        $this->type = (int)$type;
        $this->company_code = $company_code;
    }

    //发送验证码
    //成功返回0,失败返回错误代码
    public function send($number,$content,$template=1){
      //if($this->company_code === '5aab9fb19ecea'){//易贷还 1注册 2找回密码  3登录
            if($template===1){
                $data1['content'] = '【易贷还】您的注册验证码为：'.$content.',请于30分钟内正确输入，如非本人操作，请忽略此短信。';
            }else if($template===2){
                $data1['content'] = '【易贷还】您的验证码为：'.$content.',请于30分钟内正确输入，如非本人操作，请忽略此短信。';
            }else if($template===3){
                $data1['content'] = '【易贷还】您的登录验证码为：'.$content.',请于30分钟内正确输入，如非本人操作，请忽略此短信。';
            }else{
                $data1['content'] = '【易贷还】'.$content;
            }

        //}
        import('wlwx.autoload', EXTEND_PATH, '.php');
        //开发者亦可在构造函数中填入配置项
        $smsOperator = new \SmsOperator($this->CUST_CODE, $this->CUST_PWD, '', 'yes', '');

        // 发送普通短信
        $data1['destMobiles'] = $number;
        $result = $smsOperator->send_comSms($data1);
        return $result;
    }

}
