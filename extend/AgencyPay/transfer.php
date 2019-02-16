<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>演示代付提交-DEMO</title>

</head>
<?php


	include 'Crypt_RSA.php';
  include 'HttpClient.class.php';


	$P1_bizType =  "Transfer";
  $P2_orderId = date('ymdhis',time());
	$P3_customerNumber =  "C1800000002";
	$P4_amount =  @$_POST["P4_amount"];
  $P5_bankCode =  @$_POST["P5_bankCode"];
  $P6_bankAccountNo = @$_POST["P6_bankAccountNo"];
	$P7_bankAccountName = @$_POST["P7_bankAccountName"];
	$P8_biz = @$_POST["P8_biz"];
	$P9_bankUnionCode = @$_POST["P9_bankUnionCode"];
  $P10_feeType = @$_POST["P10_feeType"];
  $P11_urgency = @$_POST["P11_urgency"];
  $P12_summary = @$_POST["P12_summary"];
  $privatekey = "-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAJUwOEPoHG4AK+X3
cfhTFQJxACCwnrsphXxGD5hxjtRoVF2VlJnT9nHz4MNlLqfq12bPfRSpO0Ab9Pd3
D7xAQOSvGugZ/3LaOA3zqADyCLEuZ1vZXgOn9S86ko8cGY7qzaf1G+7YePOMvRTk
D9Dw56JPtzMmMY4+SXkuA0YGQce7AgMBAAECgYEAhX2FCvYY4jCTFw2HVCx7Ixkb
hSSkk1H+2AkUkVIi/FpyCt75/X7VCHewyQWzbprKrHrCMAeRNbcORZOqZ8aSiO+G
FJc6PgFWKMAwgnkxTSKByAtv6oh5PjYNsDbluGhCWl5Luuo+zGkkwiMGzxBxIUI0
/3SYcsHo7TUtwZT/KRkCQQDF/D7oVAszXCPFnOh6XjuzrtcqPCI6ZIgN1Mtju5V5
uPOqB2TUxsXTkWLSi2dTR7z50YTHMKTCp+icmvEGznvPAkEAwOd/U7wRMakur9WS
MphcvrpFVa/UsAgOdB0wmUWGp3WL+72IaZJXmQ+081w7TyzE2rn/IHM3zJ0GJ02Y
FrGUVQJAdF1gv/59os85+zDQ9vMh1ecicaFsYLOrv61daZ3cKfHJzRqRafn2VfYy
hhsHPMyCRradetGdVQeJUVpD5cNAwwJBAL3OCjZ1weU/NjTEy8kEqZzw4u/gxkE+
bBpL7lmhU1DKFCZq4yIdYBu2nLEneyP4ROmCQUnIlIq7piZd7tvFdtECQBq+T1BY
O1QmQtvrH7HQAp864edExPWdP5edYwXR14qaDeb+XdX5lh/mnmJxkRQ73z/b9Z6G
ZDLJfuPRKcUCAII=
-----END PRIVATE KEY-----
";


if($P5_bankCode<>"" && $P4_amount<>"" && $P6_bankAccountNo<>"" && $P7_bankAccountName<>""){

 
	
	$md5Key="rV8u3c2n2hlTCIDWyzei7iz66DiQlYTh"; 

	

	$source = "&".$P1_bizType."&".$P2_orderId."&".$P3_customerNumber."&".$P4_amount."&".$P5_bankCode."&".$P6_bankAccountNo."&".$P7_bankAccountName."&".$P8_biz."&".$P9_bankUnionCode."&".$P10_feeType."&".$P11_urgency."&".$P12_summary;
	echo "source:".$source."<br/>";

  $rsa = new Crypt_RSA();
  // var_dump($rsa);
  // exit;
  $rsa->setHash('md5');
  $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
  $rsa->loadKey($privatekey);

	$sign= base64_encode($rsa->sign($source));
  echo "sign:".$sign."<br/>";

 
        $Client = new HttpClient("127.0.0.1"); 
        $url = "http://test.trx.helipay.com/trx/transfer/interface.action";//请求的页面地址  request url
        //post的参数 
       $params = array('P1_bizType'=>$P1_bizType,'P2_orderId'=>$P2_orderId,'P3_customerNumber'=>$P3_customerNumber,'P4_amount'=>$P4_amount,'P5_bankCode'=>$P5_bankCode,'P6_bankAccountNo'=>$P6_bankAccountNo,'P7_bankAccountName'=>$P7_bankAccountName,'P8_biz'=>$P8_biz,'P9_bankUnionCode'=>$P9_bankUnionCode,'P10_feeType'=>$P10_feeType,'P11_urgency'=>$P11_urgency,'P12_summary'=>$P12_summary,'sign'=>$sign);

        $pageContents = HttpClient::quickPost($url, $params);  //发送请求 send request
     
        echo "back msg:".$pageContents."<br/>";  //返回的结果   The returned result


	$obj = json_decode($pageContents);
  $rt1_bizType = $obj->{'rt1_bizType'};
	$rt2_retCode = $obj->{'rt2_retCode'};
	$rt3_retMsg = $obj->{'rt3_retMsg'};
	$rt4_customerNumber = $obj->{'rt4_customerNumber'};
	$rt5_orderId = $obj->{'rt5_orderId'};
	$rt6_serialNumber = $obj->{'rt6_serialNumber'};	
  $sign = $obj->{'sign'};	 
}


?>

<body>
<form id="form1" name="form1" method="post" action="">
<table width="504" height="139" border="1" align="center" cellpadding="1">
    <tr>
    <td><div align="right">交易金额：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P4_amount"  value="10" />
          </label>
      </div></td>
    </tr>
  <tr>
    <td><div align="right">银行编码：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P5_bankCode"  value="ICBC" />
          </label>
      </div></td>
    </tr>
    <tr>
    <td><div align="right">银行卡号：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P6_bankAccountNo"  value="6225888833332323" />
          </label>
      </div></td>
    </tr>
    <tr>
    <td><div align="right">账户名：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P7_bankAccountName"  value="张三" />
          </label>
      </div></td>
    </tr>
    <tr>
    <td><div align="right">对私或对公(B2C或者B2B)：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P8_biz"  value="B2C" />
          </label>
      </div></td>
    </tr>
    <tr>
    <td><div align="right">联行号(对公B2B必填)：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P9_bankUnionCode"  value="" />
          </label>
      </div></td>
    </tr>
    <tr>
    <td><div align="right">计费方向（RECEIVER-收款方，PAYER-付款方）：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P10_feeType"  value="PAYER" />
          </label>
      </div></td>
    </tr>
    <tr>
    <td><div align="right">是否即时到账（true，false）：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P11_urgency"  value="true" />
          </label>
      </div></td>
    </tr>
    <tr>
    <td><div align="right">打款备注：</div></td>
    <td><label>
        
        <div align="left">
          <input type="text" name="P12_summary"  value="summary" />
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
