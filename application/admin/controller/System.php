<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/4/13
 * Time: 13:49
 */

namespace app\admin\controller;

use think\Db;

class System extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 费用配置列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function loan_list()
    {
        $request          = request();
        $post_data        = $request->param();

        if(!empty($post_data['limit'])){
            $this->limit = $post_data['limit'];
        }
        $condition = ['c.apply_status'=>0];
        //"hunuo_loan_type.company_code", session('admin_info.company_id') == 0 ? : 
        if(session('admin_info.company_id') == 0){

        }else{
            $condition= ['hunuo_loan_type.company_code'=>session('admin_info.company_code')];
        }

        $order_list       = Db::table('hunuo_loan_type')
            ->field('type_id,apply_term,apply_amount,rate,approval_fee,service_fee,over_fee,term_fee,max_money,c.cp_name')
            ->join('system_company c','c.cp_code = hunuo_loan_type.company_code')
            ->where($condition)
            ->limit((($post_data['page'] ? $post_data['page'] : 1) - 1) * $this->limit,$this->limit )
            ->order('type_id desc')
            ->fetchSql(false)
            ->select();
        $order_list_count = Db::table('hunuo_loan_type')
            ->join('system_company c','c.cp_code = hunuo_loan_type.company_code')
            ->where($condition)
            ->count();
        $data['list']     = $order_list;
        $data['page']     = array(
            'page'  => $post_data['page'] ? $post_data['page'] : 1,
            'count' => $order_list_count,
            'limit' => $this->limit,
            'cols'  => ceil($order_list_count / 20),
        );
        $data['field']    = lang('system_list');
        return json(['code' => 200, 'message' => lang('success'), 'data' => $data]);
    }

    /**
     * 费用配置
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function loan_edit()
    {
        //  apply_term      借款天数
        //  apply_amount    借款金额
        //  rate            日费率%
        //  service_fee     平台服务费率
        //  approval_fee    信息审核费率
        //  manage_fee
        //  over_fee        逾期费率%
        //  term_fee        还款利息
        //  max_money       每日放款限制
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['type'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if ($post_data['type'] == 1) {
            $loan_type = Db::table('hunuo_loan_type')
                ->field('type_id,apply_term,apply_amount,rate,service_fee,approval_fee,over_fee,term_fee,max_money,company_code')
                ->fetchSql(false)
                ->where('type_id', $post_data['type_id'])
                ->where('company_code', session('admin_info.company_code'))
                ->find();
            if(empty($loan_type)){
                return json(['code' => 500, 'message' => lang('error_5001'), 'data' => []]);
            }
            return json(['code' => 200, 'message' => lang('success'), 'data' => $loan_type]);
        }
        if ($post_data['type'] == 2) {
            if (empty($post_data['apply_term']) || empty($post_data['apply_amount']) || empty($post_data['rate']) || empty($post_data['service_fee']) || empty($post_data['approval_fee']) || empty($post_data['max_money']) || empty($post_data['over_fee']) ) {
                return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
            }
            $save_data = array(
                'type_id'      => $post_data['type_id'],
                'apply_term'   => $post_data['apply_term'],
                'rate'         => $post_data['rate'],
                'service_fee'  => $post_data['service_fee'],
                'approval_fee' => $post_data['approval_fee'],
                'over_fee'     => $post_data['over_fee'],
                'term_fee'     => $post_data['term_fee'],
                'apply_amount'     => $post_data['apply_amount'],
                'max_money'    => $post_data['max_money'],
            );
            $res       = Db::name('hunuo_loan_type')->update($save_data);
            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }
    }

    /**
     * 业务开关
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function loan_change()
    {
        $request   = request();
        $post_data = $request->param();
        if (empty($post_data['type'])) {
            return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
        }
        if ($post_data['type'] == 1) {
            $loan_type = Db::table('hunuo_loan_type')
                ->field('type_id,open')->fetchSql(false)
                ->where('company_code', session('admin_info.company_code'))->where('status', 1)
                ->find();
            $sms_choice = Db::table('system_config')->where('name','sms_choice')->value('value');
            $loan_type['sms_choice'] = $sms_choice;
            if(session('admin_info.admin_id') == 1){
                $is_open = Db::table('system_config')->where('name','is_verify_open')->value('value');
                $loan_type['is_verify_view'] = $loan_type['open'];
                $loan_type['is_verify_open'] = $is_open;
                unset($loan_type['type_id']);
                unset($loan_type['open']);
            }
            return json(['code' => 200, 'message' => lang('success'), 'data' => $loan_type]);
        }
        if ($post_data['type'] == 2) {
            // 新增验证码处理
            if($post_data['is_verify_state']){
                $res = Db::name('system_config')->where('name','is_verify_open')->update(array('value' => $post_data['is_verify_state']));
                if (false !== $res) {
                    return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
                } else {
                    return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
                }
            }

            /*if($post_data['sms_choice']){
                $res = Db::name('system_config')->where('name','sms_choice')->update(array('value' => $post_data['sms_choice']));
                if (false !== $res) {
                    return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
                } else {
                    return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
                }
            }*/

            if (!isset($post_data['status'])) {
                return json(['code' => 402, 'message' => lang('error_4002'), 'data' => []]);
            }
            $res = Db::name('hunuo_loan_type')->update(array('type_id' => $post_data['type_id'], 'open' => $post_data['status']));
            if (false !== $res) {
                return json(['code' => 200, 'message' => lang('success'), 'data' => []]);
            } else {
                return json(['code' => 401, 'message' => lang('error_4008'), 'data' => []]);
            }
        }
    }

}