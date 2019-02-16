<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 13:44
 */


namespace app\loan\controller;

use Adv\Ai;
use think\Db;
use Baiqishi\Operator;
use Redis\redisServer;
use SenseTime\SenseTime;
use app\loan\model\QuickPayModel;
class Member extends Common
{
    protected $oss_config;

    // 会员类接口
    public function __construct()
    {
        parent::__construct();
        $this->oss_config = config('auth_' . $this->env . '.OSS');
    }

    /**
     * 会员中心首页
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function home()
    {
        $this->check_login();
        $user_id = request()->post('user_id', 0, 'intval');
        $user    = Db::name('users')->field('name,head_img,user_id,phone')->where('user_id', $user_id)->find();
        //未还账单
        $BillList = Db::name('order_info')
            ->alias('o')
            ->field('o.order_id,o.order_no,r.amount,r.repay_amount,o.add_time,r.overdue_fee,r.loan_term')
            ->join('hunuo_order_repayment r', 'r.order_id=o.order_id', 'left')
            ->order('r.due_time asc')
            ->where(array('order_status' => array('in', '170,180'), 'user_id' => $user_id))
            ->select();

        $all_repay_amount = 0;
        foreach ($BillList as $k => $v) {
            $repay_amount     = $v['repay_amount'];                                //借款金额
            $over_fee         = $v['overdue_fee'];                                //逾期费
            $amount           = $repay_amount + $over_fee;                        //还款金额 = 本金 + 利息 + 逾期费
            $all_repay_amount += $amount;
        }
        $loan_type       = Db::name('loan_type')->where(array("status" => 1))->find();
        $all_amount1     = Db::name('order_info')->alias('o')
            ->field('r.repay_amount,r.overdue_fee')
            ->join('hunuo_order_repayment r', 'r.order_id=o.order_id', 'left')
            ->where('order_status in (170,180) and user_id = ' . $user_id)
            ->sum('r.repay_amount');
        $all_amount2     = Db::name('order_info')->alias('o')
            ->field('r.repay_amount,r.overdue_fee')
            ->join('hunuo_order_repayment r', 'r.order_id=o.order_id', 'left')
            ->where('order_status in (170,180) and user_id = ' . $user_id)
            ->sum('r.overdue_fee');
        $all_amount      = $all_amount1 + $all_amount2;
        $user['nearPay'] = $all_repay_amount;
        $user['myOrder'] = $all_amount;
        $user['canUse']  = $all_amount > 0 ? '0' : $loan_type["apply_amount"];
        if (!empty($user['head_img'])) {
            $user['head_img'] = get_oss_image($this->oss_config, $user['head_img']);
        }
        $user['suc_count'] = Db::name('order_info')
            ->where('order_status', 'lt', '169')
            ->where('user_id', $user_id)
            ->where('order_status>169 and user_id =' . $user_id)
            ->count();
        return json(['status' => '200', 'message' => lang('success'), 'data' => $user]);
    }

    /**
     * 更改头像
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function update_head()
    {
        $this->check_login();
        $file    = request()->file('head_img');
        $user_id = request()->post('user_id');
        //图片
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'head', $user_id . '_' . time() . '.jpg');
            if ($info) {
                $image_url = ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'head' . DS . $info->getSaveName();//本地路径
                $imgCode   = upload_oss_image($this->oss_config, $image_url);                               //上传OSS获取的图片标识
                if ($imgCode) {
                    $code = $imgCode;
                    @unlink($image_url);                                         //删除本地文件
                }
            } else {
                return json(['status' => '500', 'message' => lang('upload_f'), 'data' => []]);//上传失败
            }
        } else {
            return json(['status' => '500', 'message' => lang('upload_f'), 'data' => []]);
        }
        $data['head_img'] = $code ? $code : '';
        Db::name('users')->where('user_id', $user_id)->update($data);
        $user_info['head_img'] = get_oss_image($this->oss_config, $code);
        $this->update_userinfo($user_id);
        return json(['status' => '200', 'message' => lang('success'), 'data' => array('head_img' => $user_info['head_img'])]);
    }

    /**
     * 消息详情
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function msg_detail()
    {
        $user_id         = request()->post('user_id');
        $id              = request()->post('id');
        $msg_info       = Db::name('message')->where(array('user_id' => $user_id, 'id' => $id))->find();
        $arr['title']    = $msg_info['title'];
        $arr['content']  = $msg_info['content'];
        $arr['add_time'] = $msg_info['add_time'];
        Db::name('message')->update(array('id' => $id, 'is_read' => 1));
        return json(['status' => '200', 'message' => lang('success'), 'data' => $arr]);
    }


    /**
     * 获取用户信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit_profile()
    {
        $this->check_login();
        $user_id = request()->post('user_id');
        $act     = request()->post('act', 'basic');
        //类型 basic基本信息|job职业信息|contact联系人信息|bankcard银行卡信息|bank_list银行卡列表
        if ($act == 'basic') {
            $result    = Db::name('users')
                ->field('name,idcode,city,address,is_marrey,education,sex,birthday,face_card,back_card,photo_assay,tax_card,security_card,family_card,live_start_time,pair_verify_result,name_ocr')
                ->fetchSql(false)
                ->where('user_id', $user_id)
                ->find();

            $city_name = Db::name('region')->where('region_id', $result['city'])->value('region_name');

            $result['city'] = array(
                'city_id'   => empty($result['city']) ? '' : $result['city'],
                'city_name' => empty($city_name) ? '' : $city_name,
            );
            // 学历
            $edu = array(
                array('id' => 1, 'edu_name' => '硕士及以上'),
                array('id' => 2, 'edu_name' => '本科'),       
                array('id' => 3, 'edu_name' => '大专'),             
                array('id' => 4, 'edu_name' => '高中'),                
                array('id' => 5, 'edu_name' => '初中以下'),      
            );
            $result['edu'] = array();

            $result['edu']['edu_id'] = $result['education'];
            foreach ($edu as $r => $res) {
                if ($res['id'] == $result['education']) {
                    $result['edu']['edu_name'] = $res['edu_name'];
                }
            }
            // 婚姻状况
            $faith_name      = lang('marry_type');
            $result['marry'] = [
                'marry_name' => isset($faith_name[$result['is_marrey']]) ? $faith_name[$result['is_marrey']] : '',
                'is_marrey'  => $result['is_marrey'],
            ];
            $result['live_start_time']   = (string)(!empty($result['live_start_time']) ? $result['live_start_time'] : 0);
            $result['has_face_card']     = !empty($result['face_card']) ? 1 : 0;
            $result['has_back_card']     = !empty($result['back_card']) ? 1 : 0;
            $result['has_tax_card']      = !empty($result['tax_card']) ? 1 : 0;
            $result['has_security_card'] = !empty($result['security_card']) ? 1 : 0;
            $result['has_family_card']   = !empty($result['family_card']) ? 1 : 0;
            $result['has_verify_assay']   = $result['pair_verify_result'];//活体验证是否通过 1为通过
            //判断活体与ocr是否都通过
            if($result['pair_verify_result']=='1' && !empty($result['name_ocr'])){
                $result['has_assay_ocr_pass'] = 1;
            }else{
                $result['has_assay_ocr_pass'] = 0;
            }

            unset($result['face_card']);
            unset($result['back_card']);
            unset($result['photo_assay']);
            unset($result['tax_card']);
            unset($result['security_card']);
            unset($result['family_card']);
        }
        if ($act == 'job') {
            $result = Db::name('users')
                ->field('company, company_add, company_tel,industry,profession,staff_card,salary_card,work_prove')
                ->where('user_id', $user_id)
                ->find();
            //行业
            $industry_list = array(
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

            $result['indus']                = array();
            $result['indus']['industry_id'] = $result['industry'];
            $result['has_staff_card']       = !empty($result['staff_card']) ? 1 : 0;
            $result['has_salary_card']      = !empty($result['salary_card']) ? 1 : 0;
            $result['has_work_prove']       = !empty($result['work_prove']) ? 1 : 0;
            unset($result['staff_card']);
            unset($result['salary_card']);
            unset($result['work_prove']);
            foreach ($industry_list as $r => $res) {
                if ($res['id'] == $result['industry']) {
                    $result['indus']['industry_name'] = $res['indus_name'];
                }
            }
            //职业关系表
            $profession_list = array(
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

            $result['profess']                  = array();
            $result['profess']['profession_id'] = $result['profession'];
            foreach ($profession_list as $r => $res) {
                if ($res['id'] == $result['profession']) {
                    $result['profess']['profession_name'] = $res['profess_name'];
                }
            }
        }
        if ($act == 'contact') {
            $list = Db::name('user_contact')
                ->field('id, name, phone, relation')
                ->where('user_id', $user_id)
                ->order('id', 'asc')
                ->limit(2)
                ->select();
            //联系人关系表
            $relations = array(
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
            if (!empty($list)) {
                foreach ($list as $k => $v) {
                    $list[$k]['relation']            = array();
                    $list[$k]['relation']['rela_id'] = $v['relation'];
                    foreach ($relations as $r => $res) {
                        if ($res['id'] == $v['relation']) {
                            $list[$k]['relation']['rela_name'] = $res['relaname'];
                        }
                    }
                }
            }
            $result = array(
                'contact_list' => empty($list) ? array() : $list,
            );
        }
        if ($act == 'bankcard') {
            $result = Db::name('bankcard')
                ->field('user_id, card_num, card_type, phone,bank_id,bankcard_name,bankcard_id')
                ->where('user_id', $user_id)
                ->find(); 
            if (empty($result)) {
                $user_info               = Db::name('users')->where('user_id',$user_id)->field('name,idcode,user_name')->find();
                $result['card_num']      = "";
                $result['card_type']     = "";
                $result['phone']         = $user_info['user_name'];
                $result['bank_id']       = "";
                $result['bankcard_name'] = "";
                $result['bankcard_id']   = "";
                $result['user_id']       = $user_id;
            }
        }
        
        if ($act == 'bank_list') {
            $bank_list = Db::name('bankcard')
                ->field('bankcard_id,name,card_num,phone,phone,bankcard_name as bankName,card_type as cardType,idCard')
                ->where('user_id', $user_id)
                ->order('add_time', 'desc')
                ->fetchSql(false)
                ->select();
            if (!empty($bank_list) && is_array($bank_list)) {
                foreach ($bank_list as $key => &$value) {
                    if ($value['bankName'] == 'BNI') {
                        $value['bank_img'] = GetHttpsUrl() . "/static/bank/bni.png";
                    } else if ($value['bankName'] == 'MANDIRI') {
                        $value['bank_img'] = GetHttpsUrl() . "/static/bank/mandiri.png";
                    } else if ($value['bankName'] == 'PERMATA') {
                        $value['bank_img'] = GetHttpsUrl() . "/static/bank/permata.png";
                    } else {
                        $value['bank_img'] = GetHttpsUrl() . "/static/bank/other.png";
                    }
                }
            }
            $result = array(
                'bankcard_list' => empty($bank_list) ? array() : $bank_list,
            );
        }
        $order_count = Db::name('order_info')->where(['user_id'=>$user_id,'order_status'=>['in',[90,100,160,170,180,190,195]]])->count();
        // echo Db::name('order_info')->getlastsql();
        // exit;
        $result['is_order'] = $order_count ? 1 : 0;
        return json(['status' => '200', 'message' => lang('success'), 'data' => $result]);
    }


    public function test_ocr(){
        $user_id = 78;
        $user_info = Db::name('users')->field('user_id,name,idcode,face_card')->where('user_id',$user_id)->find();
        $ocr_result = action('loan/Face/face_ocr',[$user_info]);
        if($ocr_result['result']){
            //ocr通过才纪录到users表
            $ocr_data = array(
                'name_ocr' => $ocr_result['name'],
                'id_number_ocr' => $ocr_result['idcard_number'],
                'id_address_ocr' => $ocr_result['address'],
            );
            Db::name('users')->where('user_id', $user_id)->update($ocr_data);
        }else{
            return json(['status' => '500', 'message' => '请确认输入的姓名与身份证号信息是否正确!', 'data' => []]);
        }
    }

    /**
     * 保存用户信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function update_profile()
    {
        $this->check_login();
        $act         = request()->post('act', 'basic', 'trim');
        $user_id     = request()->post('user_id');
        $is_verify   = request()->post('is_verify', 1);
        $order_count = Db::name('order_info')
            ->where(array('user_id' => $user_id, 'order_status' => array('neq', 200)))
            ->count();
        if ($order_count) {
            return json(['status' => '200', 'message' => lang('reg_bill'), 'data' => []]);//抱歉，你存在进行中的账单
        }
        // 类型 basic基本信息|job职业信息|contact联系人信息|bankcard
        // is_verify校验 1校验数据 0不校验
        if ($act == 'basic') {
            $name            = request()->post('name', '', 'trim');                              //姓名
            $idcode          = request()->post('idcode', '', 'trim');                            //身份证
            $city            = request()->post('city', 0, 'intval');                             //城市
            $address         = request()->post('address', '', 'trim');                           //详细地址
            $is_marrey       = request()->post('is_marrey', 0, 'intval');                        //婚姻状况
            $education       = request()->post('education', 0, 'intval');                        //学历   1=硕士及以上,2=本科,3=大专,4=高中,5=初中以下
            $sex             = request()->post('sex', 0, 'intval');                              //性别    0：未设置  1：男  2：女
            $birthday        = request()->post('birthday', '', 'intval');                          //生日
            $live_start_time = request()->post('live_start_time', '', 'intval');                   //居住时间

            if ($is_verify == 1) {
                $check_name = check_china_name($name);
                $check_idcode = check_idcode($idcode);
                if (empty($name) || !$check_name)
                    return json(['status' => '500', 'message' => '请填写真实姓名', 'data' => []]);
                if (empty($idcode) || !$check_idcode)
                    return json(['status' => '500', 'message' => '请填写真实的身份证号', 'data' => []]);
                if (empty($city))
                    return json(['status' => '500', 'message' => lang('reg_city'), 'data' => []]);//请选择现居城市
                if (empty($address))
                    return json(['status' => '500', 'message' => lang('reg_address'), 'data' => []]);//请填写详细地址
                if (empty($education))
                    return json(['status' => '500', 'message' => lang('no_edu'), 'data' => []]);//请选择学历
                if (!check_idcode($idcode))
                    return json(['status' => '500', 'message' => lang('reg_idcode_f'), 'data' => []]);//请填写正确的身份证号
                if (empty($birthday) && $birthday != '0')
                    return json(['status' => '500', 'message' => lang('no_briyhday'), 'data' => []]); //请选择生日！
                if (empty($live_start_time) || $live_start_time == 0)
                    return json(['status' => '500', 'message' => lang('no_live_time'), 'data' => []]);
                if (strlen($name) < 3)
                    return json(['status' => '500', 'message' => lang('name_length_not'), 'data' => []]);//姓名长度不够
                if ($live_start_time < $birthday) {
                    return json(['status' => '500', 'message' => lang('live_time_bigger'), 'data' => []]);//居住时间不能大于出生时间
                }
            }

            $user_info = Db::name('users')->field('user_id,face_card')->where('user_id',$user_id)->find();
            $user_info['name'] = $name;
            $user_info['idcode'] = $idcode;
            //调用ocr验证
            $ocr_result = action('loan/Face/face_ocr',[$user_info]);
            if($ocr_result['result']){
                //ocr通过才纪录到users表
                $ocr_data = array(
                    'name_ocr' => $ocr_result['name'],
                    'id_number_ocr' => $ocr_result['idcard_number'],
                    'id_address_ocr' => $ocr_result['address'],
                );
                Db::name('users')->where('user_id', $user_id)->update($ocr_data);
            }else{
                return json(['status' => '500', 'message' => '请确认输入的姓名与身份证号信息是否正确!', 'data' => []]);
            }

            $data = array(
                'name'            => $name,
                'idcode'          => $idcode,
                'city'            => $city,
                'address'         => $address,
                'is_marrey'       => $is_marrey,
                'education'       => $education,
                'sex'             => $sex,
                'birthday'        => $birthday,
                'live_start_time' => $live_start_time,
            );
            Db::name('users')->where('user_id', $user_id)->update($data);
            //用户更新完信息之后处理银行卡信息
            $has_bank = Db::name('bankcard')->where('user_id', $user_id)->count();
            if ($has_bank > 0) {
                $bank_data = array(
                    'name'   => $name,
                    'idCard' => $idcode
                );
                Db::name('bankcard')->where('user_id', $user_id)->update($bank_data);
            }

            $this->update_userinfo($user_id);
            return json(['status' => '200', 'message' => lang('save_s'), 'data' => []]);
        }
        if ($act == 'job') {
            $company     = request()->post('company', '', 'trim');
            $company_add = request()->post('company_add', '', 'trim');
            $company_tel = request()->post('company_tel', '', 'trim');
            $industry    = request()->post('industry', '', 'intval');
            $profession  = request()->post('profession', '', 'intval');
            //$month_money = request()->post('month_money', '0');
            if ($is_verify == 1) {
                if (empty($company))
                    return json(['status' => '500', 'message' => lang('company'), 'data' => []]);//请填写单位名称
                if (empty($company_add))
                    return json(['status' => '500', 'message' => lang('company_add'), 'data' => []]);//请填写单位地址
                if (empty($company_tel))
                    return json(['status' => '500', 'message' => lang('company_tel'), 'data' => []]);//请填写单位电话
                if (empty($industry))
                    return json(['status' => '500', 'message' => lang('rep_indus'), 'data' => []]);//请选择行业信息
                if (empty($profession))
                    return json(['status' => '500', 'message' => lang('rep_profess'), 'data' => []]);//请选择职业信息
            }
            $data = array(
                'company'     => $company,
                'company_add' => $company_add,
                'company_tel' => $company_tel,
                'industry'    => $industry,
                'profession'  => $profession,
            );
            Db::name('users')->where('user_id', $user_id)->update($data);

            $this->update_userinfo($user_id);
            return json(['status' => '200', 'message' => lang('save_s'), 'data' => []]);

        }
        if ($act == 'contact') {
            $contact1_id       = request()->post('contact1_id', 0, 'intval');
            $contact1_relation = request()->post('contact1_relation', 0, 'intval');
            $contact1_name     = request()->post('contact1_name', '', 'trim');
            $contact1_phone    = request()->post('contact1_phone', '', 'trim');
            $contact2_id       = request()->post('contact2_id', 0, 'intval');
            $contact2_relation = request()->post('contact2_relation', 0, 'intval');
            $contact2_name     = request()->post('contact2_name', '', 'trim');
            $contact2_phone    = request()->post('contact2_phone', '', 'trim');
            $phone_count       = request()->post('phone_count', 0, 'inval');
            //if ($is_verify == 1) {
            if (empty($contact1_relation) || empty($contact2_relation))
                return json(['status' => '500', 'message' => lang('contact_relation'), 'data' => []]);//请选择联系人关系
            if (empty($contact1_name) || empty($contact2_name))
                return json(['status' => '500', 'message' => lang('contact_name'), 'data' => []]);//请填写姓名
            if (empty($contact1_phone) || empty($contact2_phone))
                return json(['status' => '500', 'message' => lang('contact_phone'), 'data' => []]);//请填写电话
            if (!preg_match("/^\d{11,14}$/", $contact1_phone) || !preg_match("/^\d{11,14}$/", $contact2_phone))
                return json(['status' => '500', 'message' => lang('contact_phone_t'), 'data' => []]);//请填写正确的手机号码
            if ($contact1_relation == $contact2_relation)
                return json(['status' => '500', 'message' => lang('equal_relation'), 'data' => []]);//联系人关系不能相同
            if ($contact1_phone == $contact2_phone)
                return json(['status' => '500', 'message' => lang('equal_phone'), 'data' => []]);//联系人电话不能相同
            if ($contact1_name == $contact2_name)
                return json(['status' => '500', 'message' => lang('equal_name'), 'data' => []]);//联系人名称不能相同
            //}
            $contact1_data = array(
                'relation' => $contact1_relation,
                'name'     => filter_emoji($contact1_name),
                'phone'    => $contact1_phone,
                'user_id'  => $user_id,
            );
            if (!empty($contact1_id)) {
                unset($contact1_data['add_time']);
                Db::name('user_contact')->where('id', $contact1_id)->update($contact1_data);
            } else {
                $count = Db::name('user_contact')->where($contact1_data)->count();
                if (empty($count)) {
                    $contact1_data['add_time'] = time();
                    Db::name('user_contact')->insert($contact1_data);
                }
            }
            $contact2_data = array(
                'relation' => $contact2_relation,
                'name'     => filter_emoji($contact2_name),
                'phone'    => $contact2_phone,
                'user_id'  => $user_id,
            );
            if (!empty($contact2_id)) {
                unset($contact2_data['add_time']);
                Db::name('user_contact')->where('id', $contact2_id)->update($contact2_data);
            } else {
                $count = Db::name('user_contact')->where($contact2_data)->count();
                if (empty($count)) {
                    $contact2_data['add_time'] = time();
                    Db::name('user_contact')->insert($contact2_data);
                }
            }
            Db::name('users')->where(array('user_id' => $user_id))->update(array('phone_count' => $phone_count));

            $this->update_userinfo($user_id);
            return json(['status' => '200', 'message' => lang('save_s'), 'data' => []]);//保存成功
        }
        if ($act == 'bankcard') {
            $card_num    = request()->post('card_num', '', 'trim');
            $phone       = request()->post('phone', '', 'trim');
            $bank_id     = request()->post('bank_id', '', 'intval');
            //$code     = request()->post('code', '', 'trim');
            //$order_no     = request()->post('order_no', '', 'trim');
            $bankcard_id = request()->post('bankcard_id');
            $check_card = check_bankcode($card_num);
            $check_phone = check_phone($phone);
            if (empty($card_num) || !$check_card)
                return json(['status' => '500', 'message' => '请填写真实的银行卡号', 'data' => []]);
            // if (empty($code))
            //     return json(['status' => '500', 'message' => '请输入鉴权验证码', 'data' => []]);
            if (empty($phone) || !$check_phone)
                return json(['status' => '500', 'message' => '请输入真实的预留手机号', 'data' => []]);//请填写预留手机号
            //$bank_lists = config('loan.bank_list');
            $bank_lists = Db::name('bank')->select();
            foreach ($bank_lists as $ks => $vs) {
                if ($vs['id'] == $bank_id) {
                    $bank_name = $vs['bank_name'];
                }
            }
            $user_info = Db::name('users')->field('name,idcode')->where('user_id',$user_id)->find();
            $bank_data = array(
                'user_id'       => $user_id,
                'branch'        => '',
                'extra_info'    => '',
                'subContractId' => '',
                'card_num'      => $card_num,
                'card_type'     => empty($card_type) ? 'DC' : $card_type,
                'name'          => $user_info['name'] ? $user_info['name'] : "",
                'phone'         => $phone,
                'idCard'        => $user_info['idcode'] ? $user_info['idcode'] : "",
                'bank_id'       => $bank_id,
                'bankcard_name' => $bank_name,
            );
            // dump($code);
            // exit;
            //根据code判断是否为编辑页面,编辑页面需要绑卡鉴权
            /*if($code){
                //绑卡鉴权
                $bank_info = [
                    'user_id'=>$user_id,
                    'order_no'=>$order_no,
                    'name'=>$user_info['name'],
                    'idcard'=>$user_info['idcode'],
                    'card_no'=>$card_num,
                    'phone'=>$phone,
                    'code'=>$code,
                ];
                $pay = new QuickPayModel($this->env);
                $result = $pay->quickPayBindCard($bank_info);
                if($result['rt2_retCode']!=='0000' && $result['rt7_bindStatus']!=='SUCCESS'){
                    if($result['rt3_retMsg']==='订单号不唯一'){
                        //把失效订单删除
                        Db::name('quick_bindbank_log')->where(['card_no'=>$card_num,'phone'=>$phone,'result'=>0])->delete();
                        return json(['status' => '500', 'message' => '请重新获取验证码', 'data' => []]);
                    }else{
                        return json(['status' => '500', 'message' => $result['rt3_retMsg'], 'data' => []]);
                    }
                }
            }*/


            $bank = Db::name('bankcard')->where(['card_num'=>$card_num,'user_id'=>$user_id])->find();
            if (empty($bankcard_id) && empty($bank)) {
                $bank_data['add_time'] = time();
                $res  = Db::name('bankcard')->insert($bank_data);
                $res1 = Db::name('users')->where(array('user_id' => $user_id))->update(array('is_bank' => '1'));
                if (false !== $res && false !== $res1) {
                    return json(['status' => '200', 'message' => lang('bd_bankcard_s'), 'data' => []]);//绑定银行卡成功
                } else {
                    return json(['status' => '500', 'message' => lang('bd_bankcard_f'), 'data' => []]);//绑定银行卡失败
                }
            } else {
                $res  = Db::name('bankcard')->where('bankcard_id=' . $bankcard_id)->update($bank_data);
                $res1 = Db::name('users')->where(array('user_id' => $user_id))->update(array('is_bank' => '1'));
                $this->update_userinfo($user_id);
                if (false !== $res && false !== $res1) {
                    return json(['status' => '200', 'message' => lang('set_bankcard_s'), 'data' => []]);//编辑银行卡成功
                } else {
                    return json(['status' => '500', 'message' => lang('set_bankcard_f'), 'data' => []]);//编辑银行卡失败
                }
            }
        }
    }

    /**
     * 用户上传图片信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function upload_user_image()
    {
        $this->check_login();
        $act         = request()->post('act');
        $user_id     = request()->post('user_id', 0, 'intval');
        $order_count = Db::name('order_info')->where(array('user_id' => $user_id, 'order_status' => array('neq', 200)))->count();
        if ($order_count) {
            return json(['status' => '500', 'message' => lang('exist_order'), 'data' => []]);//您存在进行中的生成订单
        }
        //图片类型 card_face身份证正面|card_back身份证背面|tax_card税卡|security_card社保卡|family_card家庭卡|staff_card员工卡|salary_card工资卡|work_prove在职证明|credit_img征信签名
        if ($act == 'card_face') {
            $file = request()->file('card_face');
            $info = $file->move(ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'user', $user_id . '_user_face_' . time() . '.jpg');
            if ($info) {
                $image_url = ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'user' . DS . $info->getSaveName();//本地路径
                $imgCode   = upload_oss_image($this->oss_config, $image_url);                               //上传OSS获取的图片标识
                if ($imgCode) {
                    $code = $imgCode;
                    @unlink($image_url);                                         //删除本地文件
                }
            } else {
                return json(['status' => '500', 'message' => lang('upload_f'), 'data' => []]);//上传失败
            }
            $user_data['face_card']          = $code;
            $user_data['pair_verify_result'] = 0;
            //$this->do_request(GetHttpsUrl() . ('/index.php/Help/get_ocr_data'), array('user_id' => $user_id));
        }
        if (in_array($act, array('card_back', 'tax_card', 'security_card', 'family_card', 'staff_card', 'salary_card', 'work_prove', 'credit_img'))) {
            $file = request()->file($act);
            if ($file) {
                if ($act == 'credit_img') {
                    $path = ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'credit';
                } else {
                    $path = ROOT_PATH . 'public' . DS . 'Uploads' . DS . 'user';
                }
                $info = $file->move($path, $user_id . '_' . $act . '_' . time() . '.jpg');
                if ($info) {
                    $image_url = $path . DS . $info->getSaveName();//本地路径
                    $imgCode   = upload_oss_image($this->oss_config, $image_url);                               //上传OSS获取的图片标识
                    if ($imgCode) {
                        $code = $imgCode;
                        @unlink($image_url);                                         //删除本地文件
                    }
                } else {
                    return json(['status' => '500', 'message' => lang('upload_f'), 'data' => []]);//上传失败
                }
                if ($act == 'card_back') {
                    $user_data['back_card'] = $code;
                } else {
                    $user_data[$act] = $code;
                }
            } else {
                return json(['status' => '500', 'message' => lang('reg_upload_photo'), 'data' => []]);//上传文件不存在
            }
        }
        $res = Db::name('users')->where('user_id',$user_id)->update($user_data);
        $this->update_userinfo($user_id);
        if ($res !== false) {
            return json(['status' => '200', 'message' => lang('ocr_success'), 'data' => []]);
        } else {
            return json(['status' => '500', 'message' => lang('upload_f'), 'data' => []]);//上传失败
        }
    }

    /**
     * 获取用户图片信息
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function get_user_image()
    {
        $this->check_login();
        $act     = request()->post('act', 'card_face', 'trim');
        $user_id = request()->post('user_id', 0, 'intval');
        //card_face身份证正面|card_back身份证背面|tax_card税卡|security_card社保卡|family_card家庭卡|staff_card员工卡|salary_card工资卡|work_prove在职证明
        if ($act == 'card_face') {
            $field = 'face_card';
        } else if ($act == 'card_back') {
            $field = 'back_card';
        } else {
            $field = $act;
        }
        $image_url           = Db::name('users')->where(array('user_id' => $user_id))->value($field);
        $result['image_url'] = $image_url ? get_oss_image($this->oss_config, $image_url) : '';
        return json(['status' => '200', 'message' => lang('get_user_image_succ'), 'data' => $result]);
    }

    /**
     * 未读消息条数
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function msg_count()
    {
        $user_id = request()->post('user_id', 0, 'intval');
        $result['msg_count'] = (string)Db::name('message')->where(array('user_id' => $user_id, 'is_read' => 0))->count();
        return json(['status' => '200', 'message' => lang('request_s'), 'data' => $result]);
    }

    /**
     * 消息列表
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function msg_list()
    {
        $user_id = request()->post('user_id', 0, 'intval');
        $p       = request()->post('p', 1, 'intval');
        $type       = request()->post('type', '', 'trim');
        if($type==='system'){
            $where = ['type'=>1,'user_id'=>$user_id];
        }else{
            $where = ['type'=>2,'user_id'=>$user_id];
        }
        $msg_list = Db::name('message')->where($where)->order('id desc')->select();
        $count = Db::name('message')->where($where)->count();

        $result['msg_list'] = $msg_list ? $msg_list : array();
        $result['page']     = array(
            'p'         => $p,
            'count'     => $count,
            'totalPage' => ceil($count / 10),
        );
        return json(['status' => '200', 'message' => lang('success'), 'data' => $result]);
    }

    /**
     * 获取用户认证信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check_info()
    {
        $user_id   = request()->post('user_id');
        $user_info = Db::name('users')
            ->field('name,idcode,face_card,back_card,photo_assay,sex,birthday,education,is_marrey,city,address,live_start_time,company,company_add,company_tel,profession,industry,account_state')
            ->where('user_id', $user_id)
            ->find();
        // 联系人信息已认证
        $user_contact_count = Db::name('user_contact')->where(array('user_id' => $user_id))->count();
        // 我的银行卡已认证
        $user_bank_count = Db::name('bankcard')->where(array('user_id' => $user_id))->count();
        // 账号授权已认证
        //$user_auth_count = Db::name('users')->where(array('user_id'=>$user_id,'account_state'=>1))->count();
        if ($user_info['name'] == NULL || $user_info['idcode'] == NULL || $user_info['face_card'] == '' || $user_info['back_card'] == '' || $user_info['photo_assay'] == '' || $user_info['sex'] == 0 || $user_info['birthday'] == NULL || $user_info['education'] == 0 || $user_info['is_marrey'] === NULL || $user_info['city'] == NULL || $user_info['address'] == '' || $user_info['live_start_time'] == NULL) {
            $user_base_verify = 0;
        }
        if ($user_info['company'] == NULL || $user_info['company_add'] == NULL || $user_info['company_tel'] == NULL || $user_info['profession'] == 0 || $user_info['industry'] == 0) {
            $user_job_verify = 0;
        }
        if ($user_info['account_state'] != 1) {
            $user_auth_verify = 0;
        }
        if ($user_contact_count < 2) {
            $user_contact_verify = 0;
        }
        if ($user_bank_count < 1) {
            $user_bank_verify = 0;
        }
        $data = array(
            'base_verify'    => (string)(isset($user_base_verify) ? 0 : 1),
            'job_verify'     => (string)(isset($user_job_verify) ? 0 : 1),
            'contact_verify' => (string)(isset($user_contact_verify) ? 0 : 1),
            'bank_verify'    => (string)(isset($user_bank_verify) ? 0 : 1),
            'auth_verify'    => (string)(isset($user_auth_verify) ? 0 : 1)
        );
        return json(['status' => '200', 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * Java聚信立-账号授权接口
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function yz_account()
    {
        $this->check_login();
        $user_id    = request()->post('user_id', '', 'string');
        $userInfo   = Db::name('users')->find($user_id);
        $finish_url = GetHttpsUrl() . ('/index.php/loan/member/yz_accounts');
        if (empty($userInfo['idcode'])) {
            return json(['status' => '500', 'message' => lang('before_user_basic'), 'data' => []]);//身份证未验证
        }
        $param  = array(
            'client_url' => $finish_url . '?user_id=' . $userInfo['user_id'], //通知风控系统获取报告接口
            'user_id'    => $userInfo['user_id'],                                   //用户ID
            'id_type'    => '1',                                                    //证件类型: 1:身份证; 2:驾驶证; 3:社保号; 4: 税务号; 5:护照 99 别的
            'id_num'     => $userInfo['idcode'],                                    //证件号码
            'name'       => $userInfo['name'],                                      //真实姓名
            'email'      => $userInfo['email'],                                     //用户邮箱
            'mobile'     => $userInfo['phone'],                                     //用户手机号码
            'lan'        => lang('lan'),                                               //语言
            'show_title' => 'false',                                                //false显示标题，true不显示
            'page_type'  => '2',                                                    //gojek，golife和bpjs这三个支持2，其他1
        );
        $url    = config('loan.auth.list_url'); //Java聚信立-账号授权接口
        $result = ajaxRequest($url, $param);
        $data   = json_decode($result, true);
        if ($data['code'] != 200) {
            return json(['status' => '500', 'message' => lang('common_infos'), 'data' => $data]);
        }
        return json(['status' => '200', 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * Java聚信立-通知风控系统获取报告接口
     */
    public function yz_accounts()
    {
        $datas                      = $_REQUEST;
        $url                        = config('loan.auth.finish_url');                                                                              //Java聚信立-通知风控系统获取报告接口
        $datas['report_status_url'] = GetHttpsUrl() . ('/index.php/loan/member/accredit_status');                             //Java给后台传递授权结果URL
        $result                     = ajaxRequest($url, $datas);
        $data                       = json_decode($result, true);
        $url                        = 'yzAccountss';
        if ($data['code'] != 200) {
            $status = '500';
        } else {
            $status = '200';
        }
        header("location:$url" . '?status=' . $status);
    }

    /**
     * Java给后台传递授权结果接口
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function accredit_status()
    {
        $result  = file_get_contents('php://input');                                                        //获取AJAX返回的授权结果(user_id用户ID status授权状态)
        $data    = json_decode($result, true);
        $user_id = $data['user_id'];
        $status  = $data['status'];
        $updata  = array(
            'account_state' => $status,
        );
        Db::name('users')->where(array('user_id' => $user_id))->update($updata);
    }

}