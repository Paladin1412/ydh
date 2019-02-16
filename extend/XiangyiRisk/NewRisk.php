<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/21
 * Time: 17:23
 */

namespace XiangyiRisk;

use Redis\redisServer;
use think\Db;
use XiangyiRisk\Xiangyi;
class NewRisk
{

    protected $order_info = null;
    protected $user_id = null;
    protected $env = null;
    protected $user_info = null;

    public function __construct($env)
    {
        $this->env = $env;
    }

    /**
     * 过滤订单信息
     * @param $order_no
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function verifyData($order_no)
    {
        if (empty($order_no)) {
            return json(['status' => '400', 'message' => '订单号不能为空']);
        }
        $order_info = Db::name('order_info')
            ->where('order_no', $order_no)
            ->find();
        if (empty($order_info)) {
            return json(['status' => '400', 'message' => '订单不存在']);
        }
        $this->order_info = $order_info;
        $user_id = $order_info['user_id'];
        $this->user_id  = $user_id;
        return $this->risk_check($order_no);
    }


    /**
     * 风控流程
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function risk_check($order_no)
    {
        $this->user_info = Db::name('users')->where('user_id',$this->user_id)->find();
        $xy = new Xiangyi($this->env);
        //配置信息
        $post_parms = $this->get_info();
        $result = $xy->decision($post_parms);
        //记录mongodb
        $mongo_data = array(
            'order_no' => $order_no,
            'add_date' => date('Y-m-d H:i:s'),
            'res' => $result,
        );
        mongo_log('risk_check_log', $mongo_data);

    }

    //配置数据
    public function get_info(){
            $limitApplInfo = [  //授信申请信息
                'applTime' => date('Y-m-d H:i:s'),//申请时间y
                'addChannel' => 'app',//授信渠道y
            ];
                    $identityInfo = [   //客户身份信息
                        'regTime' => date('Y-m-d H:i:s', $this->user_info['reg_time']),//注册时间y
                        'certNo' => $this->user_info['idcode'],//身份证号码y
                        'name' => $this->user_info['name'],//姓名y
                        'realNameStatus' => 0,//实名认证状态y
                        'careerType' => 15,//职业身份信息y
                        'maritalStatus' => $this->user_info['is_marrey'],//婚姻情况y
                        'downLoadChannel' => 'app',//下载渠道n
                    ];
                    //查询出用户的银行卡信息
                    $cardInfo = Db::name('bankcard')->where('user_id',$this->user_id)->field('bankcard_id,card_num bankCardNo,bankcard_name bankName,card_type cardType,phone mobileNo')->select();
                    foreach ($cardInfo as $k=>$v){
                        $cardInfo[$k]['cardVerifyStatus'] = 0;
                    }
                    if(!empty($cardInfo)){
                        foreach ($cardInfo as $k=>$v){
                            $cardInfo[$k]['cardVerifyStatus'] = 0;
                            if($v['bankcard_id'] == $this->order_info['bankcard_id']){
                                $cardInfo[$k]['isDefaultLoanNo'] = 1;
                            }else{
                                $cardInfo[$k]['isDefaultLoanNo'] = 0;
                            }
                            unset($cardInfo[$k]['bankcard_id']);
                            if($v['cardType'] == 'cc'){
                                $cardInfo[$k]['cardType'] = 'credit';
                            }else{
                                $cardInfo[$k]['cardType'] = 'debit';
                            }
                        }
                        $bankCardInfo = $cardInfo;
                        /*$bankCardInfo = [   //银行卡列表
                            'bankCardNo' => $cardInfo['card_num'],//银行卡卡号y
                            'bankName' => $cardInfo['bankcard_name'],//银行名称y
                            'cardType' => $cardInfo['card_type'] == 'cc'?'credit':'debit',//卡类型y
                            'mobileNo' => $cardInfo['phone'],//预留手机号y
                            'cardVerifyStatus' => 0,//卡验证状态y
                            'isDefaultLoanNo' => 1,//是否当前放款卡号y
                        ];*/
                    }else{
                        $bankCardInfo = [];
                    }

                    $jobInfo = [   //工作信息
                        'jobUnitName' => $this->user_info['company'],//单位名称y
                        'jobAddr' => $this->user_info['company_add'],//单位详细地址y
                        'jobProvinceName' => '',//单位所属省名称y
                        'jobCityName' => '',//单位所属市名称y
                        'jobTelZone' => '',//工作电话区号n
                        'jobTel' => $this->user_info['company_tel'],//工作电话y
                        'jobTelExt' => '',//工作电话区分机号n
                        'jobIncome' => '',//收入y
                    ];
                    $resideInfo = [   //居住信息
                        'resideAddr' => $this->user_info['address'],//居住地地址n
                        'resideProvinceName' => '',//居住地所属省y
                        'resideCityName' => $this->user_info['city'],//居住地所属市y
                        'resideTel' => '',//居住地电话n
                    ];
                    $manInfo = Db::name('user_contact')->distinct(true)->where('user_id',$this->user_id)->field('phone linkManNo,relation,name linkManName')->select();
                    if(!empty($manInfo)){
                        foreach ($manInfo as $k => $v){
                            if($v['relation'] == 1 || $v['relation'] == 2){
                                $manInfo[$k]['linkManRelship'] = "parent";
                            }elseif ($v['relation'] == 3){
                                $manInfo[$k]['linkManRelship'] = "brother";
                            }elseif ($v['relation'] == 4){
                                $manInfo[$k]['linkManRelship'] = "sister";
                            }elseif ($v['relation'] == 5){
                                $manInfo[$k]['linkManRelship'] = "friends";
                            }elseif ($v['relation'] == 6){
                                $manInfo[$k]['linkManRelship'] = "relatives";
                            }elseif ($v['relation'] == 7){
                                $manInfo[$k]['linkManRelship'] = "workmate";
                            }elseif ($v['relation'] == 9){
                                $manInfo[$k]['linkManRelship'] = "mate";
                            }else{
                                $manInfo[$k]['linkManRelship'] = "other";
                            }
                            $manInfo[$k]['linkManType'] = 'main';
                            unset($manInfo[$k]['relation']);
                        }
                        $linkManInfo = $manInfo;
                       /* $linkManInfo = [   //联系人信息
                            'linkManName' => $manInfo['name'],//联系人姓名y
                            'linkManNo' => $manInfo['phone'],//联系人号码y
                            'linkManRelship' => $linkManRelship,//联系人关系y
                            'linkManType' => 'main',//联系人类型y
                        ];*/
                    }else{
                        $linkManInfo = [];
                    }
                    if($this->user_info['user_education'] == 1){
                        $educationDegree = '硕士及以上';
                    }elseif ($this->user_info['user_education'] == 2){
                        $educationDegree = '本科';
                    }elseif ($this->user_info['user_education'] == 3){
                        $educationDegree = '大专';
                    }elseif ($this->user_info['user_education'] == 4){
                        $educationDegree = '中专高专及以下';
                    }else{
                        $educationDegree = '本科';
                    }
                    $fillEduInfo = [   //学历信息y
                        'graduateName' => '',//毕业学校n
                        'graduateDate' => '',//毕业年份n
                        'educationDegree' => $educationDegree,//文化程度y
                    ];
            $custInfo = [   //客户基本信息
                'identityInfo' => $identityInfo,//客户身份信息y
                'bankCardInfo' => $bankCardInfo,//银行卡列表y
                'jobInfo' => $jobInfo,//工作信息y
                'resideInfo' => $resideInfo,//居住信息y
                'linkManInfo' => $linkManInfo,//联系人信息y
                'fillEduInfo' => $fillEduInfo,//学历信息y
                'consigneeInfo' => [],//收货人列表n
            ];
            /*        $callRdsInfo = [   //通话记录列表y
                        'phoneNo' => '18529113945',//原始的通话号码y
                        'callTime' => date('Y-m-d H:i:s'),//通话时间y
                        'callType' => 1,//通话类型y
                        'callDuration' => 500,//通话时长y
                        'isThrough' => 1,//是否接通y
                    ];
                    $BookInfo = Db::name('phone_list')->where('user_id',$this->user_id)->field('phone phoneNo,name bookName,addTime')->select();
                    $contactsBookInfo = $BookInfo;
                    $smsInfo = [   //短信息y
                        'phoneNo' => '18529113945',//短信号码y
                        'smsTime' => date('Y-m-d H:i:s'),//短信时间y
                        'smsType' => 1,//短信类型y
                        'smsContent' => 'sdfssdsd',//短信内容y
                    ];
                    $appNames = [   //安装app列表y
                        'appName' => '易贷还',//app名称y
                        'captureTime' => date('Y-m-d H:i:s'),//抓取时间y
                    ];
            $grabInfo = [   //抓取信息
                'callRdsInfo' => $callRdsInfo,//通话记录列表y
                'contactsBookInfo' => $contactsBookInfo,//通讯录列表y
                'smsInfo' => $smsInfo,//短信息列表y
                'appNames' => $appNames,//安装app列表y
                'contactsStatisticInfo' => [],//通讯录统计信息n
            ];*/
            $equipInfo = [   //设备信息
                'equipFeatureInfo' => [],//设备特征信息n
                'lbsInfo' => [],//设备位置信息n
            ];
                    /*$telecomInfo = [   //运营商信息
                        'telecomType' => 'DHB',//爬虫供应商y
                        'resultData' => [],//运营商爬虫整报文n
                    ];*/
            $creditInfo = [   //前端征信信息
                'zhimaInfo' => [],//芝麻信息n
                'faceResultInfo' => [],//人脸识别信息n
                'ocrResultInfo' => [],//Ocr扫描信息n
                //'telecomInfo' => $telecomInfo,//运营商信息y
            ];
            $withDrawReq = [   //提现申请信息
                'applTime' => date('Y-m-d H:i:s'),//申请时间y
                'orderNo' => $this->order_info['order_no'],//订单号y
                'orderType' => 'cash',//订单类型n
                'orderAmount' => $this->order_info['application_amount'],//订单金额y
                'terms' => 1,//贷款期数y
                'loanDay' => 7,//借款天数y
                'addChannel' => 'app',//进件渠道y
            ];
        $ifFirst = Db::name('order_info')->where(['phone' => $this->user_info['user_name'] , 'order_status' => 200])->value('order_id');
        empty($ifFirst)? $ifFirstReq = 1 : $ifFirstReq = 0;
        $content = [
            'phone' => $this->user_info['user_name'],//手机号
            'limitApplInfo' => $limitApplInfo,//授信申请信息y
            'custInfo' => $custInfo,//客户基本信息y
            'grabInfo' => json_decode(json_encode(['ok' => 12])),//抓取信息y
            'equipInfo' => json_decode(json_encode(['ok' => 12])),//设备信息y
            'creditInfo' => json_decode(json_encode(['ok' => 12])),//前端征信信息y
            'behaviorInfo' => json_decode(json_encode(['ok' => 12])),//行为信息n
            'ifFirstReq' => $ifFirstReq,  //是否首次申请
            'withDrawReq' => $withDrawReq,  //提现申请信息
        ];
        /*halt(json_encode([
            'phone' => $this->user_info['user_name'],//手机号y
            'serviceId' => 'creditAndWithDrawReq',//服务识别号y
            'content' => $content,//业务数据y
            'sign' => 'test',//签名y
            'requestTime' => date('Y-m-d H:i:s'),//请求时间y
        ]));*/
        return  [
            'phone' => $this->user_info['user_name'],//手机号y
            'serviceId' => 'creditAndWithDrawReq',//服务识别号y
            'content' => $content,//业务数据y
            'sign' => 'test',//签名y
            'requestTime' => date('Y-m-d H:i:s'),//请求时间y
        ];
    }


}