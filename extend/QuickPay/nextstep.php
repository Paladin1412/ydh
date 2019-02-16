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
	$P1_bizType='QuickPaySendValidateCode';
	$cusNum = $_GET['P2_customerNumber'];
	$orderId = $_GET['P4_orderId'];
	$timestamp = $_GET['P5_timestamp'];
	$phone = $_GET['P13_phone'];
	$code = $_POST['P5_validateCode'];
	$ip = $_POST['P6_orderIp'];
	$signkey='';
	
	//发送验证码的签名串
	$orinMessage = "&QuickPaySendValidateCode&$cusNum&$orderId&$timestamp&$phone&$signkey";
	$sign = md5($orinMessage);
	
	
	$sign_parmas = [
        'P1_bizType'=>'QuickPayConfirmPay',
        'P2_customerNumber'=>'C1800000002',
        'P3_orderId'=>'p_20180927061321',
        'P4_timestamp'=>'20180927061321',
        'P5_validateCode'=>'18818241811',
        'P6_orderIp'=>'127.0.0.1',
    ];

	if($code <> ""){//校验必要的参数
		$url = "http://test.trx.helipay.com/trx/quickPayApi/interface.action";//网银请求的页面地址 
		//支付MD5的签名串
		$orinSendMessage = "&QuickPayConfirmPay&$cusNum&$orderId&$timestamp&$code&$ip&$signkey";
		$sendSign = md5($orinSendMessage);
        //构造支付请求参数
		$params=array('P1_bizType'=>'QuickPayConfirmPay','P2_customerNumber'=>$cusNum,
			'P3_orderId'=>$orderId,'P4_timestamp'=>$timestamp,'P5_validateCode'=>$code,'P6_orderIp'=>$ip,'sign'=>$sendSign);

		//调用支付请求	
		$pageContents = HttpClient::quickPost($url, $params);  //发送请求 send request
    	echo "back msg:".$pageContents."<br/>";  //返回的结果   The returned result
	}
	
?>
<form action="" id="payForm" name="payForm" method="post">
            <input type="hidden" id="mobile" value="<?php echo $phone;?>"/>
            <div class="input_cont">
                <div class="search_tit">
                    <h2>确认支付</h2>
                </div>
                <table class="request_table">
                    <tr>
                        <td class="text_right">交易类型：<font color="red">*</font></td>
                        <td class="text_left"><input type="text" name="P1_bizType" class="input_text" value="QuickPayConfirmPay"/></td>
                    </tr>
                    <tr>
                        <td class="text_right">商户编号：<font color="red">*</font></td>
                        <td class="text_left"><input type="text" id="P2_customerNumber" name="P2_customerNumber" class="input_text" value="<?php echo $cusNum;?>"/></td>
                    </tr>
                    <tr>
                        <td class="text_right">商户订单号：<font color="red">*</font></td>
                        <td class="text_left"><input type="text" id="P3_orderId" name="P3_orderId" class="input_text" value="<?php echo $orderId;?>"/></td>
                    </tr>
                    <tr>
                        <td class="text_right">时间戳：<font color="red">*</font></td>
                        <td class="text_left"><input type="text" id="P4_timestamp" name="P4_timestamp" class="input_text" value="<?php echo $timestamp;?>"/></td>
                    </tr>
                    <tr>
                        <td class="text_right">短信验证码：<font color="red">*</font></td>
                        <td class="text_left"><input type="text" name="P5_validateCode" class="input_text" value=""/><input type="button" class="smsButton" value="发送验证码" onclick="return confirmPaySms(this);"/></td>
                    </tr>
                    <tr>
                        <td class="text_right">支付IP：<font color="red">*</font></td>
                        <td class="text_left"><input type="text" name="P6_orderIp" class="input_text" value="127.0.0.1"/></td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td></td>
                        <td class="text_left"><input type="submit" class="btn_sure fw" value="提交"/></td>
                    </tr>
                </table>
            </div>
        </form>
</body>
	 

<script>
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
                "P5_phone": mobile,
                "sign":"<?php echo $sign;?>"
            },
            success: function (data) {
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

</html>

