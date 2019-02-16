<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>网银下单-DEMO</title>
<script type="text/javascript" src="./jquery-3.1.1.min.js"></script> 
</head>
<body>
<?php
	error_reporting(E_ALL ^ E_NOTICE); 

    include 'HttpClient.class.php';



	$orderId="p_".date('Ymdhis',time());//订单号
    $timestamp=date('Ymdhis',time());//时间戳
    $cusNum = "C1800001107";//注意商户号这里是写死的关联到下面的发送验证码签名
    $phone = '18610097084';//注意手机这里是写死的关联到下面的发送验证码签名
    $signkey='';
    $code = $_POST["P17_validateCode"];
    
    //发送验证码的签名串
    $orinMessage = "&QuickPaySendValidateCode&$cusNum&$orderId&$timestamp&$phone&$signkey";
    $sign = md5($orinMessage);
	
	
	
    if ($code <> "") {//校验必要参数
        
        //获取form表单数据
        $P1_bizType =  $_POST["P1_bizType"];
        $P2_customerNumber =  $_POST["P2_customerNumber"];
        $P3_bindId =  $_POST["P3_bindId"];
        $P4_userId =  $_POST["P4_userId"];
        $P5_orderId =  $_POST["P5_orderId"];
        $P6_timestamp =  $_POST["P6_timestamp"];
        $P7_currency =  $_POST["P7_currency"];
        $P8_orderAmount =  $_POST["P8_orderAmount"];
        $P9_goodsName =  $_POST["P9_goodsName"];
        $P10_goodsDesc =  $_POST["P10_goodsDesc"];
        $P11_terminalType =  $_POST["P11_terminalType"];
        $P12_terminalId =  $_POST["P12_terminalId"];
        $P13_orderIp =  $_POST["P13_orderIp"];
        $P14_period =  $_POST["P14_period"];
        $P15_periodUnit =  $_POST["P15_periodUnit"];
        $P16_serverCallbackUrl =  $_POST["P16_serverCallbackUrl"];
        $P17_validateCode =  $_POST["P17_validateCode"];

        //构造form表单值的签名串
        $signFormString = "&".$P1_bizType."&".$P2_customerNumber."&".$P3_bindId."&".$P4_userId."&".$P5_orderId."&".$P6_timestamp."&".$P7_currency."&".$P8_orderAmount."&".$P9_goodsName."&".$P10_goodsDesc."&".$P11_terminalType."&".$P12_terminalId."&".$P13_orderIp."&".$P14_period."&".$P15_periodUnit."&".$P16_serverCallbackUrl."&".$signkey;

       

        //bingPay的支付sign
        $paySign = md5($signFormString);

        
        //构造请求参数
        $params = array('P1_bizType'=>$P1_bizType,'P2_customerNumber'=>$P2_customerNumber,'P3_bindId'=>$P3_bindId,'P4_userId'=>$P4_userId,'P5_orderId'=>$P5_orderId,'P6_timestamp'=>$P6_timestamp,'P7_currency'=>$P7_currency,'P8_orderAmount'=>$P8_orderAmount,'P9_goodsName'=>$P9_goodsName,'P10_goodsDesc'=>$P10_goodsDesc,'P11_terminalType'=>$P11_terminalType,'P12_terminalId'=>$P12_terminalId,'P13_orderIp'=>$P13_orderIp,'P14_period'=>$P14_period,'P15_periodUnit'=>$P15_periodUnit,'P16_serverCallbackUrl'=>$P16_serverCallbackUrl,'sign'=>$paySign);


        //调用支付接口
        $url = "http://test.trx.helipay.com/trx/quickPayApi/interface.action";
        $pageContents = HttpClient::quickPost($url, $params);  //发送请求 send request
        echo "back msg:".$pageContents."<br/>";  //返回的结果   The returned result

    }

	
	
?>
<div class="Container">
    <div class="information">
        <div class="search_tit">
            <h2>快捷支付绑卡支付接口</h2>
        </div>
            <table class="request_table">
                <tr>
                    <td>交易类型：<font color="red">*</font></td>
                    <td><input type="text" name="P1_bizType" id="P1_bizType" class="input_text" value="QuickPayBindPay"/></td>
                </tr>
                <tr>
                    <td>商户编号：<font color="red">*</font></td>
                    <td><input type="text" name="P2_customerNumber" id="P2_customerNumber" class="input_text" value="<?php echo $cusNum;?>"/></td>
                </tr>
                <tr>
                    <td>商户订单号：<font color="red">*</font></td>
                    <td><input type="text" name="P3_orderId" id="P3_orderId" class="input_text" value="<?php echo $orderId;?>"/></td>
                </tr>
                <tr>
                    <td>时间戳：<font color="red">*</font></td>
                    <td><input type="text" name="P4_timestamp" id="P4_timestamp" class="input_text" value="<?php echo $timestamp;?>"/></td>
                </tr>
                <tr>
                    <td>手机号码：<font color="red">*</font></td>
                    <td><input type="text" name="P5_phone" id="P5_phone" class="input_text" value="<?php echo $phone;?>"/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="button" class="smsButton" value="发送验证码" onclick="return confirmPaySms(this);"/></td>
                </tr>
            </table>
        <div id="sendSmsStatus"></div>
        <div>------------------------------------------------------------------------------------------</div>
        <form action="" id="payForm" name="payForm" method="post">
            <div class="input_cont">
                <table class="request_table">
                    <tr>
                        <td>交易类型：<font color="red">*</font></td>
                        <td><input type="text" name="P1_bizType" class="input_text" value="QuickPayBindPay"/></td>
                    </tr>
                    <tr>
                        <td>商户编号：<font color="red">*</font></td>
                        <td><input type="text" name="P2_customerNumber" class="input_text" value="C1800001107"/></td>
                    </tr>
                    <tr>
                        <td>绑卡ID：<font color="red">*</font></td>
                        <td><input type="text" name="P3_bindId" class="input_text" value="107d7b7f2487414991112c6081967fa1"/></td>
                    </tr>
                    <tr>
                        <td>用户ID：<font color="red">*</font></td>
                        <td><input type="text" name="P4_userId" class="input_text" value="170000000003"/></td>
                    </tr>
                    <tr>
                        <td>商户订单号：<font color="red">*</font></td>
                        <td><input type="text" name="P5_orderId" class="input_text" value="<?php echo $orderId;?>"/></td>
                    </tr>
                    <tr>
                        <td>时间戳：<font color="red">*</font></td>
                        <td><input type="text" name="P6_timestamp" class="input_text" value="<?php echo $timestamp;?>"/></td>
                    </tr>
                    <tr>
                        <td>交易币种：<font color="red">*</font></td>
                        <td><input type="text" name="P7_currency" class="input_text" value="CNY"/></td>
                    </tr>
                    <tr>
                        <td>交易金额：<font color="red">*</font></td>
                        <td><input type="text" name="P8_orderAmount" class="input_text" value="0.11"/></td>
                    </tr>
                    <tr>
                        <td>商品名称：<font color="red">*</font></td>
                        <td><input type="text" name="P9_goodsName" class="input_text" value="大西瓜"/></td>
                    </tr>
                    <tr>
                        <td>商品描述：</td>
                        <td><input type="text" name="P10_goodsDesc" class="input_text" value="大西瓜"/></td>
                    </tr>
                    <tr>
                        <td>终端类型：</td>
                        <td><input type="text" name="P11_terminalType" class="input_text" value="IMEI"/></td>
                    </tr>
                    <tr>
                        <td>终端标识：</td>
                        <td><input type="text" name="P12_terminalId" class="input_text" value="122121212121"/></td>
                    </tr>
                    <tr>
                        <td>下单IP：<font color="red">*</font></td>
                        <td><input type="text" name="P13_orderIp" class="input_text" value="127.0.0.1"/></td>
                    </tr>
                    <tr>
                        <td>订单有效时间：<font color="red">*</font></td>
                        <td><input type="text" name="P14_period" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>订单有效时间单位：<font color="red">*</font></td>
                        <td><input type="text" name="P15_periodUnit" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>回调地址：<font color="red">*</font></td>
                        <td><input type="text" name="P16_serverCallbackUrl" class="input_text" value="127.0.0.1"/></td>
                    </tr>
                    <tr>
                        <td>验证码：</td>
                        <td><input type="text" name="P17_validateCode" class="input_text" value=""/></td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" id="submitButton" class="btn_sure fw" value="提交" /></td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
    
    <div class="clearer"></div>
</div>
<script>
    $(function(){
        $("#submitButton").click(function(){
            $("#message").text("处理中。。。。。");
        })
    });

     function confirmPaySms(smsObj) {
        $(smsObj).css({
            background: "#aaa"
        }).attr("disabled", "disabled");
        timing(smsObj);
        var mobile = $("#mobile").val();
        $.ajax({
            type: 'POST',
            url: "http://test.trx.helipay.com/trx/quickPayApi/interface.action",
            data: {
                "P1_bizType": "QuickPaySendValidateCode",
                "P2_customerNumber": $("#P2_customerNumber").val(),
                "P3_orderId": $("#P3_orderId").val(),
                "P4_timestamp": $("#P4_timestamp").val(),
                "P5_phone": $("#P5_phone").val(),
                "sign":"<?php echo $sign;?>"
            },
            success: function (data) {
                console.info(data);
                var retJson = eval(data);
                if(retJson.code!="00"){
                    alert(data.message);
                }
            }
        });
        return false;
    }
    var times = 5;
    function timing(smsObj) {
        times--;
        if (times == 0) {
            times = 5;
            $(smsObj).removeAttr("disabled").css({
                background: "#0C9DF3"
            });
            $(smsObj).val("发送验证码");
            return;
        }
        $(smsObj).val(times);
        setTimeout(function () {
            timing(smsObj)
        }, 1000);

    }
</script>
</body>

</html>

