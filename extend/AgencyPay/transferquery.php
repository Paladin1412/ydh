<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>演示代付查询-DEMO</title>
</head>
<?php


	include 'Crypt_RSA.php';
  	include 'HttpClient.class.php';


	$P1_bizType =  "TransferQuery"; 
	$P3_customerNumber =  "C1800000002"; 
	$P2_orderId =  @$_POST["P2_orderId"]; 
	$privatekey = "";
	


if($P2_orderId<>"" ){

 
	//您的商户编号 merchant number 
	$signKey="rV8u3c2n2hlTCIDWyzei7iz66DiQlYTh"; //32位字符串,在后台可获取,用于签名   32 bit string ,take it on website 'mservice' ,this is for signature

	

	$source = "&".$P1_bizType."&".$P2_orderId."&".$P3_customerNumber;
	echo "source:".$source."<br/>";


	$rsa = new Crypt_RSA();
  	$rsa->setHash('md5');
  	$rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
  	$rsa->loadKey($privatekey);

	$sign= base64_encode($rsa->sign($source));
  	echo "sign:".$sign."<br/>";

 
        $Client = new HttpClient("127.0.0.1"); 
        $url = "http://test.trx.helipay.com/trx/transfer/interface.action";
        //post的参数 
       $params = array('P1_bizType'=>$P1_bizType,'P3_customerNumber'=>$P3_customerNumber,'P2_orderId'=>$P2_orderId,'sign'=>$sign);

        $pageContents = HttpClient::quickPost($url, $params); 
     
        echo "back msg:".$pageContents."<br/>"; 


	$obj = json_decode($pageContents);
	$rt1_bizType = $obj->{'rt1_bizType'};
	$rt2_retCode = $obj->{'rt2_retCode'};
	$rt3_retMsg = $obj->{'rt3_retMsg'};
	$rt4_customerNumber = $obj->{'rt4_customerNumber'};
	$rt5_orderId = $obj->{'rt5_orderId'};
	$rt6_serialNumber = $obj->{'rt6_serialNumber'};
	$rt7_orderStatus = $obj->{'rt7_orderStatus'};
	$json_sign = $obj->{'sign'};

	echo "rt1_bizType:".$rt1_bizType."<br/>";
	echo "rt2_retCode:".$rt2_retCode."<br/>";
	echo "rt3_retMsg:".$rt3_retMsg."<br/>";
	echo "rt4_customerNumber:".$rt4_customerNumber."<br/>";
	echo "rt5_orderId:".$rt5_orderId."<br/>";
	echo "rt6_serialNumber:".$rt6_serialNumber."<br/>";
	echo "rt7_orderStatus:".$rt7_orderStatus."<br/>";
	echo "json_sign:".$json_sign."<br/>";
	
	//当retCode为0000证明查询请求受理成功，订单是否支付成功根据r4_orderStatus判断，INIT:已接收;DOING: 处理中;SUCCESS:成功; FAIL:失败;CLOSE:关闭
	
	
	
	 
}


?>

<body>
<form id="form1" name="form1" method="post" action="">
<table width="504" height="139" border="1" align="center" cellpadding="1">
  
  
  
    <tr>
    <td><div align="right">商户订单号：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P2_orderId"  value="" />
          </label>
      </div></td>
    </tr>
  
  <tr>
    <td height="29" colspan="4"><label>
      <div align="center">
        <input type="submit" name="Submit" value="提交" />
        </div>
    </label></td>
    </tr>
</table>
</form>
</body>
</html>
