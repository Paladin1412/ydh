var domain = window.location.host;
if(domain=="download.hsydh.com"){
    var url="http://39.108.26.98";
    var android_download_url="./android/App_ydh_v1.1.0_2018.12.08_release.apk";//安卓下载地址
}else{
    var url="http://developing.api.ydh.china.tupulian.com";
    var android_download_url="./android/App_ydh_v1.1.0_2018.12.08_release.apk";//安卓下载地址
}

var ios_download_url="itms-services:///?action=download-manifest&url=https://a0.hsydh.com/ios/ydh_v1.1.0.plist";//苹果下载地址
var companycode="5aab9fb19ecea";//公司code
var test_url="http://localhost/ydh/public/index.php/channel/Test/index";//本地测试链接
var get_code_url=url+"/channel/Register/new_send_msg";//发送验证码
var verification_code_url=url+"/channel/Register/check_code";//验证验证码
var get_click_url=url+"/channel/Register/index";//记录点击量
var user_reg_url=url+"/channel/Register/do_reg";//用户注册接口
var record_download=url+"/channel/Index/download";//记录下载量
var get_download_url=url+"/channel/Index/show_link_info";//查询出下载地址
var channel_code=0;


(function () {
    //获取url参数方法
    function GetUrlParam(paraName) {
        var url = document.location.toString();
        var arrObj = url.split("?");

        if (arrObj.length > 1) {
            var arrPara = arrObj[1].split("&");
            var arr;

            for (var i = 0; i < arrPara.length; i++) {
                arr = arrPara[i].split("=");

                if (arr != null && arr[0] == paraName) {
                    return arr[1];
                }
            }
            return "";
        }
        else {
            return "";
        }
    }
    channel_code=GetUrlParam("code");

}());

