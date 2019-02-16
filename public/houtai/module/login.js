layui.define(['jquery','form','language'],function(exports){
    var $jq = layui.jquery
       ,form = layui.form
       ,language = layui.language;
    var domain = document.domain;  //获取当前域名
    var basePath = '../../';
    var localhost_ary = new Array('tpl_yidaihuan.com');
    // if( localhost_ary.indexOf(domain) > -1 ){
        // basePath = 'http://developing.china.tupulian.com/';
    // }
    var obj = {
        initView:function() { // initView start
            //获取当前语言并存入缓存
        	this.initLan();
        	
        	$jq.post(basePath + 'admin/Login/set_lang',{'lang':dataStore.get('current_lan')},function(data){
				layui.login.doInit();
        	})
        }, // initView end
        doInit: function() {
        	layui.use(['language'],function(lan){
                language = layui.language;
               $jq('#LAY-user-login-username').attr('placeholder',language.get('user_name'));
               $jq('#LAY-user-login-password').attr('placeholder',language.get('pwd'));
               $jq('#LAY-user-login-vercode').attr('placeholder',language.get('verify'));
               var  html_remb_pwd  = "<a href=\"javascript:;\">"+ language.get('remember_pwd') +"</a>";
               var  html_login_btn = language.get('login_sub');
               var  host_title     = language.get('host_title');
               $jq("#remb_pwd").html(html_remb_pwd);
               $jq("#login_btn").html(html_login_btn);
               $jq("#host_title").html(host_title);
           });

           // 初始化验证码开关
           this.initCodeImg();
        },
        changeImg: function () {
            document.getElementById('verify').src= basePath + 'admin/Login/verify_img?'+Math.random();
        },
        	
        /**
         * 初始化验证码开关
         */
        initCodeImg : function(){
        	$jq.post(basePath + 'admin/Login/verify_open?'+Math.random(),'',function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		if(data.data.is_verify_open == '1'){
        			$jq('#hid_codeimg').show();
        			dataStore.set('open_close_verification_code','1');
        			//初始换验证码
                    layui.login.changeImg();
        		}else{
        			$jq('#hid_codeimg').hide();
        			dataStore.set('open_close_verification_code','2');
        		}
        	})
        },


        /**
         * 登录页
         */
        login :function(){
            var user_name = $jq('input[name="user_name"]').val();
            var password  = $jq('input[name="password"]').val();
            var verify    = $jq('input[name="verify"]').val();
            var remember  = $jq('input:checkbox[name="remember"]:checked').val();
            if(user_name.length <= 0){
                // layer.msg('用户名不能为空', {
                layer.msg(layui.language.get('not_user_name'), {
                    icon: 2,
                    time: 1000    //1秒关闭（如果不配置，默认是3秒）
                });
                return false;
            }
            if(password.length <= 0){
                // layer.msg('密码不能为空', {
                 layer.msg(layui.language.get('not_pwd'), {
                    icon: 2,
                    time: 1000    //1秒关闭（如果不配置，默认是3秒）
                });
                return false;
            }
            if(dataStore.get('open_close_verification_code') == '1'){
                if(verify.length <= 0){
                    // layer.msg('验证码不能为空', {
                  layer.msg(layui.language.get('not_verify'), {
                    icon: 2,
                    time: 1000    //1秒关闭（如果不配置，默认是3秒）
                });
                return false;
                }
            }

            var sendDatas ={
                'user_name': user_name.replace(/\s/g, ""),  //去除所有空格
                'password' : password.replace(/\s/g, ""),   //去除所有空格
                'verify'   : verify,
                'remember' : remember == 'on' ?remember: null,
            };

            $jq.post(basePath+'admin/Login/login',sendDatas,function(data){
                var url =data.data.url;
                if(url || url !==undefined){
                    dataStore.set('load_url',url);
                }
                if(data.code == '200'){
                    if(sendDatas.remember == 'on'){
                        //加密
                        // var password = Base64.encode(sendDatas.password);
                        //cookie 记录
                        layui.login.addCookie("user_name",sendDatas.user_name,24,"/");
                        layui.login.addCookie("password",password,24,"/");
                        layui.login.addCookie("password1",password,24,"/");
                    }else{
                        layui.login.addCookie("user_name","",24,"/");
                        layui.login.addCookie("password","",24,"/");
                        layui.login.addCookie("password1","",24,"/");
                    }


                    layer.msg(layui.language.get('login_suc'), {
                        icon: 1,
                        time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                    }, function(){
                        window.location.href='index.html';
                    });

                }else if(data.code == '201'){ //已登录
                    layer.msg(data.message, {
                        icon: 1,
                        time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                    }, function(){
                            window.location.href='index.html';
                    });
                }else{ //错误信息
                    layer.msg(data.message, {icon: 2});
                    layui.login.changeImg();
                }
            });
            return false;

        },

        /**
         *  cookie 记住密码
         */
         addCookie :function(name,value,days,path){   /**添加设置cookie**/
             var name = escape(name);
             var value = escape(value);
             var expires = new Date();
            expires.setTime(expires.getTime() + days * 3600000 * 24);
            //path=/，表示cookie能在整个网站下使用，path=/temp，表示cookie只能在temp目录下使用
            path = path == "" ? "" : ";path=" + path;
            //GMT(Greenwich Mean Time)是格林尼治平时，现在的标准时间，协调世界时是UTC
            //参数days只能是数字型
            var _expires = (typeof days) == "string" ? "" : ";expires=" + expires.toUTCString();
            document.cookie = name + "=" + value + _expires + path;
         },

        /**
         *  form 表单提交登录页
         */

        tool : function(){
            //验证表单数据是否为空
            form.verify({
                user_name: function(value){
                    if(value.length <= 0) {
                        // return '用户名不能为空';
                        return layui.language.get('not_user_name');
                    }
                }, password: function(value){
                    if(value.length <= 0){
                        // return '密码不能为空';
                        return layui.language.get('not_pwd');
                    }
                }
                ,verify: function(value){
                	if(dataStore.get('open_close_verification_code') == '1'){
                        if(value.length <= 0){
                            // return '验证码不能为空';
                            return layui.language.get('not_verify');
                        }
                	}
                }
            });
            form.on('submit(LAY-user-login-submit)',function(data){
                $jq.post(basePath+'admin/Login/login',data.field,function(data){

                    if(data.code == '200'){
                        layer.msg(layui.language.get('login_suc'), {
                            icon: 1,
                            time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                        }, function(){

                            window.location.href='index.html';
                        });
                    }else if(data.code == '201'){ //已登录
                        layer.msg(data.message, {
                            icon: 1,
                            time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                        }, function(){
                            window.location.href='index.html';
                        });
                    }else{ //错误信息
                        layer.msg(data.message, {icon: 2});
                        layui.login.changeImg();
                    }
                });
                return false;
            });
        },
        //获取当前语言并存入缓存
        initLan : function(){
        	if(!dataStore.get('current_lan')){//缓存语言为空时，则默认为当前浏览器的语言
        		var lan = navigator.language.toLowerCase();
            	if(lan.indexOf('cn') != -1){
            		dataStore.set('current_lan','cn');
            	}else if(lan.indexOf('id') != -1){
            		dataStore.set('current_lan','id');
            	}else if(lan.indexOf('en') != -1){
            		dataStore.set('current_lan','en');
            	}
        	}
        }

    };

    //输出接口
    exports('login',obj);
});




