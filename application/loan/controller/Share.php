<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2018/5/22
 * Time: 14:04
 */

namespace app\loan\controller;

use Endroid\QrCode;
use think\Config;
use think\Db;

class Share extends Common
{
    private $_url = '';

    private $_postdata = array();

    // 分享类接口 处理用户分享二维码
    public function __construct()
    {
        parent::__construct();
        $this->_postdata = request()->post();
        $this->_url      = Config::get("auth.share_url");
    }

    /**
     * 分享接口
     * @throws QrCode\Exception\InvalidPathException
     */
    public function get_share()
    {
        if (!isset($this->_postdata['user_id']) || empty($this->_postdata['user_id'])) {
            return json(array("status" => '505', "message" => "请传入用户ID"));
        }
        $share_url = $this->_url . "index?user_id=" . $this->_postdata['user_id'];
        $qcode_url = $this->getOssImg($share_url, $this->_postdata['user_id'], 'share', 0);
        return json(array("status" => 200, "message" => lang('success'), "data" => array("url" => $share_url, "qcode_url" => $qcode_url)));
    }

    /**
     * 活动接口
     * @throws QrCode\Exception\InvalidPathException
     */
    public function get_activity()
    {
        if (!isset($this->_postdata['user_id']) || empty($this->_postdata['user_id'])) {
            return json(array("status" => '505', "message" => "请传入用户ID"));
        }
        if (!isset($this->_postdata['activity_id']) || empty($this->_postdata['activity_id'])) {
            return json(array("status" => '505', "message" => "请传入活动ID"));
        }
        $activity_id = isset($this->_postdata["activity_id"]) ? $this->_postdata["activity_id"] : 1;
        $share_url   = $this->_url . "index?activity_id=" . $activity_id . '&user_id=' . $this->_postdata['user_id'];
        $qcode_url   = $this->getOssImg($share_url, $this->_postdata['user_id'], "activity", $activity_id);
        return json(array("status" => 200, "message" => lang('success'), "data" => array("url" => $share_url, "qcode_url" => $qcode_url)));
    }

    /**
     * 获取图片
     * @param $share_url
     * @param $uid
     * @param string $type
     * @param int $activity_id
     * @return bool|null
     * @throws QrCode\Exception\InvalidPathException
     */
    private function getOssImg($share_url, $uid, $type = "share", $activity_id = 0)
    {
        $oss_code = Db::name('user_qrcode_image')->where(array('user_id' => $uid, 'type' => $type, 'activity_id' => $activity_id))->value('code');
        if (empty($oss_code)) {
            $oss_code = $this->make_image($share_url, $uid, $type);
            $add_data = array(
                'user_id'     => $uid,
                'type'        => $type,
                'code'        => $oss_code,
                'activity_id' => $activity_id,
                'add_time'    => time()
            );
            Db::name('user_qrcode_image')->insert($add_data);
        }
        $imageUrl = get_oss_image(config('auth_' . $this->env . '.OSS'), $oss_code);
        return $imageUrl;
    }


    /**
     * 生成图片
     * @param $url
     * @param string $uid
     * @param string $type
     * @return bool|null|string
     * @throws QrCode\Exception\InvalidPathException
     */
    private function make_image($url, $uid = '', $type = 'share')
    {
        $logo_path = '20180816_logo.png';
        $bg_path   = '20180816_bg.png';
        $config_arr = array(
            'share'    => array(
                'logo_width' => 150,//logo大小宽高
                'logo_path'  => $logo_path,//logo地址
                'bg_path'    => $bg_path,//活动背景图地址
                'position_x' => 125,//X轴定位
                'position_y' => 423,//Y轴定位
            ),
            'activity' => array(
                'logo_width' => 150,
                'logo_path'  => $logo_path,
                'bg_path'    => $bg_path,
                'position_x' => 519,
                'position_y' => 1152,
            ),
        );

        $config    = isset($config_arr[$type]) ? $config_arr[$type] : array();
        $base_path = ROOT_PATH . 'public' . DS . 'channel' . DS . 'qrcode' . DS;

        // 1 根据活动要求生成二维码
        $code_path = $base_path . $uid . '_qrcode_' . time() . '.png';  //临时二维码路径
        $save_path = $base_path . $uid . '_success_' . time() . '.png'; //临时最终图片路径
        $qrCode    = new QrCode\QrCode();//创建生成二维码对象
        $qrCode->setText($url)
            ->setSize(450)
            ->setMargin(10)
            ->setLogoPath($base_path . $config['logo_path'])
            ->setLogoWidth($config['logo_width'])
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->writeFile($code_path);

        // 2 根据活动合成对应的效果图
        $background_img = imagecreatefromstring(file_get_contents($base_path . $config['bg_path']));
        $qrcode_img     = imagecreatefromstring(file_get_contents($code_path));
        list($qrcode_img_width, $qrcode_img_hight) = getimagesize($code_path);
        imagecopymerge($background_img, $qrcode_img, $config['position_x'], $config['position_y'], 0, 0, $qrcode_img_width, $qrcode_img_hight, 100);
        imagejpeg($background_img, $save_path);
        imagedestroy($background_img);
        unlink($code_path);//删除二维码

        // 3 上传到oss
        $oss_code = upload_oss_image(config('auth_'.$this->env.'.OSS'),$save_path);
        unlink($save_path);
        return $oss_code;
    }

    /**
     * 测试环境低版本
     */
    public function Statistics()
    {
        exit(json_encode(array('status' => '500', 'message' => lang('please_update_app'), 'data' => array())));
    }


}