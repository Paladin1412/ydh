<?php

/**

功能: 图片上传到阿里云SDK 封装
 **/
namespace AliOss;

use OSS\Core\OssException;
use OSS\OssClient;

class  ImageUpload {

    protected $_AccessKeyID = null;  //阿里云Accesskey

    protected $_AccessKeySecret= null; //阿里云secret

    protected $_EndPoint = null; //外网访问

    private $_Image = "";

    private $_Image_name = '';

    private $_OssClient = NULL;  // OSS 文件客户端对象


    private $_Bucket = null;

    private $_Extension = "jpg"; //文件后缀

    /**
    function:构造函数
    param $image : string 图片地址
    endpoint:  string Endpoint  默认为测试
     **/
    public function __construct($image , $oss_config) {

        if(is_array($oss_config)) {
            $this->_AccessKeyID = $oss_config['accesskeyid'];
            $this->_AccessKeySecret = $oss_config['accesskeysecret'];
            $this->_EndPoint = $oss_config['endpoint'];
            $this->_Bucket = $oss_config['bucket'];
        }
        try {

            if(FALSE == $this->verifyImage($image)) {
                throw new \think\Exception("image type error...");
            }


            $this->getOssClient();
            $this->_Image  = $image;
            $this->_Image_name = substr(basename($image),0,-4);

        }catch(\think\Exception $e) {

            echo $e->getmessage();
            die();

        }catch(OssException $e) {
            echo $e->getmessage();
            die();
        }
    }

    /**
    function:验证上传文件的格式类型

    param   $image    string  文件路径

    return  成功返回TRUE,失败返回FALSE
     **/

    public function verifyImage($image) {

        if(empty($image) || !is_string($image)) {
            return FALSE;
        }

        $extends = pathinfo($image);

        if(!empty($extends["extension"]) && in_array($extends["extension"],array("jpg","png","jpeg",'JPEG','PNG','JPG'))) {

            $this->_Extension = $extends["extension"];
            return TRUE;
        }

        return FALSE;
    }


    public function getOssClient() {
        $this->_OssClient = new \OSS\OssClient($this->_AccessKeyID , $this->_AccessKeySecret, $this->_EndPoint);
    }

    /**
    function: 上传 到阿里云的文件
     **/
    public function AliyunUpload() {

        if(NULL != $this->_OssClient) {


            if(!$this->_OssClient->doesBucketExist($this->_Bucket)) {

                $this->_OssClient->createBucket($this->_Bucket);
            }

            $aliyunImage = $this->_Bucket."/app/".date("Ymd")."/".$this->_Image_name.".".$this->_Extension;

            try {


                $flag = $this->_OssClient->uploadFile($this->_Bucket,$aliyunImage,$this->_Image);


            }catch(OssException $e) {

                echo $e->getMessage();
                return FALSE;
            }

            return $aliyunImage;
        }

        return FALSE;

    }

    /**
    function:获取图片信息
    param   obj    string  图片标识
     **/
    public function getSignedUrl($obj) {

        if(empty($obj) || !is_string($obj)) {
            return FALSE;
        }
        $timeout = 3600;

        try{

            $singedUrl = $this->_OssClient->signUrl($this->_Bucket,$obj,$timeout);

        }catch(OssException $e) {
            echo $e->getMessage();
            return NULL;
        }

        //$request = new RequestCore($signedUrl);
        return $singedUrl;
    }
}