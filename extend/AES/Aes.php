<?php
/*
 *白骑士-运营商接口类
 *By:somnus  
 *Time:2018-09-28 08:33 
 * */
namespace AES;

use think\Config;
use think\Db;
class Aes{

		protected  $rsa_public_key  =  '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC7mkRT3ugaCw/+BBwnY5g+HrOh
rMyVmgoUb9uHt45bRov7t0ieJPkgdQO9iVog4lCWwqLD27JH0elrn58CoLdnp9cm
02/gEOjXiYrLDUXswS4vRk8T95HMNHJUx3DG3M68p2gl2VlLJc1Vx91J0gpkr6FD
SMLvEGyGE7dFEAtI1wIDAQAB
-----END PUBLIC KEY-----';  //  RSA  公钥
        protected  $rsa_private_key  =  '-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALuaRFPe6BoLD/4E
HCdjmD4es6GszJWaChRv24e3jltGi/u3SJ4k+SB1A72JWiDiUJbCosPbskfR6Wuf
nwKgt2en1ybTb+AQ6NeJissNRezBLi9GTxP3kcw0clTHcMbczrynaCXZWUslzVXH
3UnSCmSvoUNIwu8QbIYTt0UQC0jXAgMBAAECgYBvfkZfZhmg6LQvbffhfPJM8Ogn
DNBllK+q57Obm/5xxqKJDauNuUGKBaEcctXqtzXX84LSTe5NerZz7OCOqj90yJBo
5k006B6ejyDa2nJlsmh2AeakE18glpWjyGC5ShksTERCewwTS8piQEzflIbbhGUK
IuFstdBEc7Uphd+8GQJBAPCH1JZP/wtDrh1jTcVZa7788amV+XOxww5SPwpC14Sd
ayMool78aEpUupihP7HhIKUp4yEJEnVZLJOUeOZGY4MCQQDHqwOPFCvxLKCRQs/s
j8uLUvZjWIE6AoQxFJz3Lh8Ed3JODXN1UxIkZbWb3FXVerUx3bB1/yClD0XVzpfZ
z4EdAkEAku83I5e8wsHWhGdcY/lrQ6G7cxCF8XIfKQl/LyKEC6QqhbDV99aTUw0N
bB3dDinrylmbQVwMZZlTDNh/PpSzRwJAJfxO7QV1ZqiK05fWXrbsjlD2Nug7jYG1
DHFTe2L2266tvFOqx4NKTEpdRo2gdsClgBsg2xHyb/R74LUxBmsHsQJAY6hr2PnI
VT7khY7IynvN9CYdyh7pQi+RbGQzIPiZNHgEz1n+wPJqwzstSVaMVx3Lra6tEk5g
uRjkAXOXglpAEw==
-----END PRIVATE KEY-----';  //  RSA  私钥


        /**
          *  @param  $encryptData
          *  @return  bool|string
          *  rsa加密字符串
          */
        public  function  encrypt_rsa($encryptData){
            $rsa_public_key=$this->rsa_public_key;
            $pu_key  =  openssl_pkey_get_public($rsa_public_key);
            $encrypted  =  '';
            if  (openssl_public_encrypt($encryptData,  $encrypted,  $pu_key))  {
                return  $encrypted  =  base64_encode($encrypted);
            }else{
                return  false;
            }
        }

        /**
          *  @param  $encryptData
          *  @return  bool|string
          *  rsa解密字符串
          */
        public  function  decrypt_rsa($encryptData)
        {
            $rsa_private_key=$this->rsa_private_key;
            $pi_key  =    openssl_pkey_get_private($rsa_private_key);
            $encryptData  =  str_replace('  ','+',$encryptData);
            $encrypted='';
            if($decryptData = openssl_private_decrypt(base64_decode($encryptData),  $encrypted,  $pi_key)){
                return  $encrypted;
            }else{
                return  false;
            }
        }

}