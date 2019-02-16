<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>网银下单-DEMO</title>
</head>
<body>
<?php
	error_reporting(E_ALL ^ E_NOTICE); 

    include 'HttpClient.class.php';



	
    $P2_orderId = $_POST["P2_orderId"];
    $signkey=trim("t7rjXvj5yW3qyRa0Y2HOqcz830Bp3bM3");
   
	
	
    if ($P2_orderId <> "") {//检查必要参数
        //form提交的表单值
       $P1_bizType =  trim($_POST["P1_bizType"]);
       $P2_orderId =  trim($_POST["P2_orderId"]);
       $P3_customerNumber =  trim($_POST["P3_customerNumber"]);

       //构造签名
       $signFormString = trim("&$P1_bizType&$P2_orderId&$P3_customerNumber&$signkey");
       $querySign=md5($signFormString);



       //构造请求参数 
       $params = array('P1_bizType'=>$P1_bizType,'P2_orderId'=>$P2_orderId,'P3_customerNumber'=>$P3_customerNumber,'sign'=>$querySign);

        //调用支付接口
        $url = "http://test.trx.helipay.com/trx/quickPayApi/interface.action";
        $pageContents = HttpClient::quickPost($url, $params);  //发送请求 send request
        echo "back msg:".$pageContents."<br/>";  //返回的结果   The returned result
    }

	
	
?>
<div class="Container">
    <div class="information">
        <form action="" id="payForm" name="payForm" method="post">
            <div class="input_cont">
                <div class="search_tit">
                    <h2>快捷支付订单查询接口</h2>
                </div>
                <table class="request_table">
                    <tr>
                        <td>交易类型：<font color="red">*</font></td>
                        <td><input type="text" name="P1_bizType" class="input_text" value="QuickPayQuery"/></td>
                    </tr>
                    <tr>
                        <td>商户订单号：<font color="red">*</font></td>
                        <td><input type="text" name="P2_orderId" class="input_text" value=""/></td>
                    </tr>
                    <tr>
                        <td>商户编号：<font color="red">*</font></td>
                        <td><input type="text" name="P3_customerNumber" class="input_text" value="C1800001107"/></td>
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

