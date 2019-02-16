<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/11
 * Time: 9:52
 */

namespace app\loan\controller;

use think\Db;
use app\loan\model\QuickPayModel;
class Bank extends Common
{
    // 银行卡类接口
    public function __construct()
    {
        parent::__construct();

    }


    /**
     * 绑定银行卡
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function binding_card()
    {
        $this->check_login();
        $user_id     = request()->post('user_id', 0);
        $card_num    = request()->post('card_num', '', 'trim');
        $name        = request()->post('name', '', 'trim');
        $phone       = request()->post('phone', '', 'trim');
        $idCard      = request()->post('idCard', '', 'trim');
        $bank_id     = request()->post('bank_id', '', 'trim');
        $bankcard_id = request()->post('bankcard_id', 'trim');

        if (empty($card_num)) {
            return json(['status' => '500', 'message' => lang('reg_bankcard'), 'data' => []]);//请填写银行卡号
        }
        if (empty($name)) {
            return json(['status' => '500', 'message' => lang('reg_name'), 'data' => []]);//请填写姓名
        }
        if (empty($phone)) {
            return json(['status' => '500', 'message' => lang('reg_yl_phone'), 'data' => []]);//请填写预留手机号
        }
        if (!check_phone($phone)) {
            return json(['status' => '500', 'message' => lang('contact_phone_t'), 'data' => []]);//请输入正确的手机号
        }
        if (empty($idCard)) {
            return json(['status' => '500', 'message' => lang('reg_idcode'), 'data' => []]);//请填写身份证号
        }

        //查询银行名称
        $bank_lists = config('loan.bank_list');
        foreach ($bank_lists as $ks => $vs) {
            if ($vs['id'] == $bank_id) {
                $bank_name = $vs['bank_name'];
            }
        }
        if (empty($bankcard_id)) {
            //是否已存在银行卡
            $bankcard_count = Db::name('bankcard')->where('user_id', $user_id)->count();
            if ($bankcard_count) {
                return json(['status' => '500', 'message' => lang('reg_bankcard_cf'), 'data' => []]);//请勿重复绑定银行卡
            }
        } else {
            //要编辑的银行卡，是否存在
            $bankcard_info = Db::name('bankcard')->field('card_num,name,phone,idCard')->find($bankcard_id);
            if (empty($bankcard_info)) {
                return json(['status' => '500', 'message' => lang('no_bankcard'), 'data' => []]);//银行卡不存在
            }
            /*用户是否存在进行中的订单*/
            $order_where = array(
                'user_id'      => $user_id,
                'order_status' => array('IN', array(80, 90, 100, 170, 180)),
            );
            $order_count = Db::name('order_info')->where($order_where)->count();
            if ($order_count) {
                return json(['status' => '500', 'message' => lang('reg_bill'), 'data' => []]);//抱歉，你存在进行中的账单
            }
        }
        $bank_data = array(
            'user_id'       => $user_id,
            'card_num'      => $card_num,                                   //银行卡号
            'card_type'     => empty($card_type) ? 'DC' : $card_type,      //银行卡类型
            'name'          => $name,                                       //姓名
            'phone'         => $phone,                                      //预留手机号
            'idCard'        => $idCard,                                     //身份证号
            'bank_id'       => $bank_id,                                    //银行ID
            'bankcard_name' => $bank_name,                                   //银行ID
        );
        if (empty($bankcard_id)) {
            $bank_data['add_time'] = time();
            Db::startTrans();
            $res1 = Db::name('bankcard')->insert($bank_data);
            $res2 = Db::name('users')->where(array('user_id' => $user_id))->update(array('is_bank' => '1'));
            if ($res1 !== false && $res2 !== false) {
                Db::commit();
                return json(['status' => '200', 'message' => lang('bd_bankcard_s'), 'data' => []]);//绑定银行卡成功
            } else {
                Db::rollback();
                return json(['status' => '500', 'message' => lang('bd_bankcard_f'), 'data' => []]);//绑定银行卡失败
            }
        } else {
            $res1 = Db::name('bankcard')->where('bankcard_id', $bankcard_id)->update($bank_data);
            $res2 = Db::name('users')->where(array('user_id' => $user_id))->update(array('is_bank' => '1'));
            if ($res1 !== false && $res2 !== false) {
                Db::commit();
                return json(['status' => '200', 'message' => lang('set_bankcard_s'), 'data' => []]);//编辑银行卡成功
            } else {
                Db::rollback();
                return json(['status' => '500', 'message' => lang('set_bankcard_f'), 'data' => []]);//编辑银行卡失败
            }
        }
    }

    /**
     * 银行卡列表
     * @return \think\response\Json
     */
    public function banks_list()
    {
        $list = Db::name('bank')->select();
        return json(['status' => 200, 'message' => '', 'data' => $list]);
    }

    //合利宝绑卡短信(单个手机号短信会有上限)
    public function bind_card_code(){
        $this->check_login();
        $user_id     = request()->post('user_id',0);
        $card_no    = request()->post('card_no', '', 'trim');
        $phone       = request()->post('phone', '', 'trim');
        $user_info = Db::name('users')->where('user_id',$user_id)->find();
        if(!empty($user_info['idcode']) && !empty($user_info['name'])){
            if(isset($user_id) && isset($card_no) && isset($phone)){
                if($user_info){
                    $bindbank_info = Db::name('quick_bindbank_log')->where(['card_no'=>$card_no,'phone'=>$phone])->order('id desc')->find();
                    if(is_array($bindbank_info)){
                        if($bindbank_info['result']){//如果最近一次认证通过,重新认证需要更换订单号
                            $order_no = make_sn();
                        }else{
                            $order_no = $bindbank_info['order_no'];
                        }
                    }else{
                        $order_no = make_sn();
                    }
                    $bank_info = [
                        'user_id'=>$user_id,
                        'order_no'=>$order_no,
                        'card_no'=>$card_no,
                        'phone'=>$phone,
                        'idcard'=>$user_info['idcode'],
                        'name'=>$user_info['name'],
                    ];
                    $pay = new QuickPayModel($this->env);
                    $result = $pay->agreementPayBindCardValidateCode($bank_info);
                    if($result['rt2_retCode']==='0000'){
                        return json(['status' => '200', 'message' => $result['rt3_retMsg'], 'data' => ['order_no'=>$order_no]]);
                    }else{
                        return json(['status' => '500', 'message' => $result['rt3_retMsg'], 'data' => []]);
                    }
                }else{
                    return json(['status' => '500', 'message' => '用户不存在', 'data' => []]);
                }
            }else{
                return json(['status' => '500', 'message' => '缺少重要参数', 'data' => []]);
            }
        }else{
            return json(['status' => '500', 'message' => '请完善用户基本信息', 'data' => []]);
        }

    }

}