<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>快捷支付首次下单-DEMO</title>
</head>
<body>
<?php
    error_reporting(E_ALL ^ E_NOTICE); 

	include 'HttpClient.class.php';
    $P4_orderId="p_".date('Ymdhis',time());//订单号
    $P5_timestamp=date('Ymdhis',time());//时间戳
    $phone =  $_POST["P13_phone"];

	
   

    if($phone <> ""){//判断非空
        //获取form提交参数
        $P1_bizType =  $_POST["P1_bizType"];
        $P2_customerNumber =  $_POST["P2_customerNumber"];
        $P3_userId =  $_POST["P3_userId"];
        $P4_orderId =  $_POST["P4_orderId"];
        $P5_timestamp =  $_POST["P5_timestamp"];
        $P6_payerName =  $_POST["P6_payerName"];
        $P7_idCardType =  $_POST["P7_idCardType"];
        $P8_idCardNo =  $_POST["P8_idCardNo"];
        $P9_cardNo =  $_POST["P9_cardNo"];
        $P10_year =  $_POST["P10_year"];
        $P11_month =  $_POST["P11_month"];
        $P12_cvv2 =  $_POST["P12_cvv2"];
        $P13_phone =  $_POST["P13_phone"];
        $P14_currency =  $_POST["P14_currency"];
        $P15_orderAmount =  $_POST["P15_orderAmount"];
        $P16_goodsName =  $_POST["P16_goodsName"];
        $P17_goodsDesc =  $_POST["P17_goodsDesc"];
        $P18_terminalType =  $_POST["P18_terminalType"];
        $P19_terminalId =  $_POST["P19_terminalId"];
        $P20_orderIp =  $_POST["P20_orderIp"];
        $P21_period =  $_POST["P21_period"];
        $P22_periodUnit =  $_POST["P22_periodUnit"];
        $P23_serverCallbackUrl =  $_POST["P23_serverCallbackUrl"];

    	$signkey_quickpay = "";//密钥key

        //构造支付签名串
        $signFormString = "&$P1_bizType&$P2_customerNumber&$P3_userId&$P4_orderId&$P5_timestamp&$P6_payerName&$P7_idCardType&$P8_idCardNo&$P9_cardNo&$P10_year&$P11_month&$P12_cvv2&$P13_phone&$P14_currency&$P15_orderAmount&$P16_goodsName&$P17_goodsDesc&$P18_terminalType&$P19_terminalId&$P20_orderIp&$P21_period&$P22_periodUnit&$P23_serverCallbackUrl&$signkey_quickpay";

    	
    	$sign= md5($signFormString);//MD5签名
        

    	$Client = new HttpClient("127.0.0.1"); 
        $url = "http://test.trx.helipay.com/trx/quickPayApi/interface.action";//网银请求的页面地址  request url

        //post的参数
        $params = array('P1_bizType'=>$P1_bizType,'P2_customerNumber'=>$P2_customerNumber,'P3_userId'=>$P3_userId,'P4_orderId'=>$P4_orderId,'P5_timestamp'=>$P5_timestamp,'P6_payerName'=>$P6_payerName,'P7_idCardType'=>$P7_idCardType,'P8_idCardNo'=>$P8_idCardNo,'P9_cardNo'=>$P9_cardNo,'P10_year'=>$P10_year,'P11_month'=>$P11_month,'P12_cvv2'=>$P12_cvv2,'P13_phone'=>$P13_phone,'P14_currency'=>$P14_currency,'P15_orderAmount'=>$P15_orderAmount,'P16_goodsName'=>$P16_goodsName,'P17_goodsDesc'=>$P17_goodsDesc,'P18_terminalType'=>$P18_terminalType,'P19_terminalId'=>$P19_terminalId,'P20_orderIp'=>$P20_orderIp,'P21_period'=>$P21_period,'P22_periodUnit'=>$P22_periodUnit,'P23_serverCallbackUrl'=>$P23_serverCallbackUrl,'sign'=>$sign);
      

        $pageContents = HttpClient::quickPost($url, $params);  //发送请求 send request
        echo "back msg:".$pageContents."<br/>";  //返回的结果   The returned result
    	
        

	

    //跳转到验证码支付页面
    echo "<script language='javascript'>"; 
    echo "location='nextstep.php?P2_customerNumber=$P2_customerNumber&P4_orderId=$P4_orderId&P5_timestamp=$P5_timestamp&P13_phone=$P13_phone'";
    echo "</script>";

   
	 
	
}



?>
<div class="Container">
    <div class="information">
        <form action="" id="payForm" name="payForm" method="post">
            <div class="input_cont">
                <div class="search_tit">
                    <h2>快捷支付绑卡支付接口</h2>
                </div>
                <table class="request_table">
                    <tr>
                        <td>交易类型：<font color="red">*</font></td>
                        <td><input type="text" name="P1_bizType" class="input_text" value="QuickPayCreateOrder"/></td>
                    </tr>
                    <tr>
                        <td>商户编号：<font color="red">*</font></td>
                        <td><input type="text" name="P2_customerNumber" class="input_text" value="C1800000002"/></td>
                    </tr>
                    <tr>
                        <td>用户ID：<font color="red">*</font></td>
                        <td><input type="text" name="P3_userId" class="input_text" value="1000000002"/></td>
                    </tr>
                    <tr>
                        <td>商户订单号：<font color="red">*</font></td>
                        <td><input type="text" name="P4_orderId" class="input_text" value="<?php echo $P4_orderId;?>"/></td>
                    </tr>
                    <tr>
                        <td>时间戳：<font color="red">*</font></td>
                        <td><input type="text" name="P5_timestamp" class="input_text" value="<?php echo $P5_timestamp;?>"/></td>
                    </tr>
                    <tr>
                        <td>姓名：<font color="red">*</font></td>
                        <td><input type="text" name="P6_payerName" class="input_text" value="肖勇进"/></td>
                    </tr>
                    <tr>
                        <td>证件类型：<font color="red">*</font></td>
                        <td><input type="text" name="P7_idCardType" class="input_text" value="IDCARD"/></td>
                    </tr>
                    <tr>
                        <td>证件号：<font color="red">*</font></td>
                        <td><input type="text" name="P8_idCardNo" class="input_text" value="441622199203210775"/></td>
                    </tr>
                    <tr>
                        <td>银行卡号：<font color="red">*</font></td>
                        <td><input type="text" name="P9_cardNo" class="input_text" value="6217003320021589471"/></td>
                    </tr>
                    <tr>
                        <td>信用卡有效年份：</td>
                        <td><input type="text" name="P10_year" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>信用卡有效期月份：</td>
                        <td><input type="text" name="P11_month" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>cvv2：</td>
                        <td><input type="text" name="P12_cvv2" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>手机号码：<font color="red">*</font></td>
                        <td><input type="text" name="P13_phone" class="input_text" value="18610097084"/></td>
                    </tr>
                    <tr>
                        <td>交易币种：<font color="red">*</font></td>
                        <td><input type="text" name="P14_currency" class="input_text" value="CNY"/></td>
                    </tr>
                    <tr>
                        <td>交易金额：<font color="red">*</font></td>
                        <td><input type="text" name="P15_orderAmount" class="input_text" value="1.01"/></td>
                    </tr>
                    <tr>
                        <td>商品名称：<font color="red">*</font></td>
                        <td><input type="text" name="P16_goodsName" class="input_text" value="大西瓜"/></td>
                    </tr>
                    <tr>
                        <td>商品描述：</td>
                        <td><input type="text" name="P17_goodsDesc" class="input_text" value="大西瓜"/></td>
                    </tr>
                    <tr>
                        <td>终端类型：<font color="red">*</font></td>
                        <td><input type="text" name="P18_terminalType" class="input_text" value="IMEI"/></td>
                    </tr>
                    <tr>
                        <td>终端标识：<font color="red">*</font></td>
                        <td><input type="text" name="P19_terminalId" class="input_text" value="122121212121"/></td>
                    </tr>
                    <tr>
                        <td>下单IP：<font color="red">*</font></td>
                        <td><input type="text" name="P20_orderIp" class="input_text" value="127.0.0.1"/></td>
                    </tr>
                    <tr>
                        <td>订单有效时间：</td>
                        <td><input type="text" name="P21_period" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>有效时间单位：</td>
                        <td><input type="text" name="P22_periodUnit" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>回调地址：<font color="red">*</font></td>
                        <td><input type="text" name="P23_serverCallbackUrl" class="input_text" value="http://localhost/server"/></td>
                    </tr>
                    <tr></tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" class="btn_sure fw" value="提交" /></td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
    <div class="clearer"></div>
</div>
</body>


</html>