layui.define(function (exports) {
	var form = layui.form;
	var layer = layui.layer;
	var $jq = layui.jquery;
	var laytpl = layui.laytpl;
	var isVerifyFlag = true;
	var isStoreRole = false;
	var isInit = false;
	
    var obj = {
        initView: function () {
        	layui.use(['language'],function(language){
        		language.render('header');
        	});
        	//显示当前语言
        	obj.initCurrLan();
            obj.loadMemu();
            obj.display();
            obj.loadRole();
            obj.loadRealName();
        }
    	,loadRole:function() {
            var sendData1={};
            sendData1['type']=1;
    		 $jq.post(basePath+'admin/Base/get_role_type', sendData1, function (data) {
    			 if(!ajaxCall(data)) {
         			return;
         		}
    			 dataStore.set('global_role_type',data.data.role_type);
    			 isStoreRole = true;
                  layui.index.roleIndex();
             });

            var sendData2={};
            sendData2['type']=2;
            $jq.post(basePath+'admin/Base/get_role_type', sendData2, function (data) {
                if(!ajaxCall(data)) {
                    return;
                }
                dataStore.set('collection_role_type',data.data.role_type);
            });
    	}

        // 缓存当前登录的用户名称
    	,loadRealName :function(){
            $jq.post(basePath+'admin/Base/get_user_info', null, function (data) {
                if (!ajaxCall(data)) {
                    return;
                }
                dataStore.set('now_login_user_realname', data.data.user_name);
                $jq('#login_realname').attr('value',data.data.user_name);
              var  html = "<a href=\"javascript:;\">"+ data.data.user_name  +"</a>";
                $jq("#realname_div").html(html)
                $jq("#index_host_title").html(layui.language.get('index_host_title'))

            });
        }

        // 加载视图函数
        , loadHtml: function (url, id) {
            var $jq = layui.jquery;
            url = url.replace('#', '');
            $jq.get(url, 'random=' + Math.random(), function (data) {
                $jq(id).html(data);
            });
        }
        ,doClick:function(url) {
        	 var $jq = layui.jquery;
				$jq('#left a[data-href="#'+url+'"]').parent().parent().prev('a').attr('id',"now_tip");
				$jq('#now_tip img').click();
				// $jq('#left a[data-href="#'+url+'"]').parent("dd").parents("li").addClass("layui-nav-itemed");
				$jq('#left a[data-href="#'+url+'"]').parent("dd").addClass("layui-this");
				$jq('#left a[data-href="#'+url+'"]').click();

        }
        ,noFlush:function() {
        	 var curUrlArr = location.href.split('#');
             if(curUrlArr.length == 2) {
             	var curUrl = curUrlArr[1];
             	layui.index.doClick(curUrl);
             	return;
             }
        }
        ,roleIndex:function() {
        	if(!isStoreRole ){
    			return;
    		}
        	if(!isInit ){
    			return;
    		}

        	var url = location.href;

        	if(url.indexOf('#')>-1){
        		layui.index.noFlush();
        	}else{
        		var load_url = dataStore.get('load_url');
                if(load_url){
                    layui.index.doClick(load_url);
				}else{
                    layui.index.noFlush();
				}
        		
        	}
        }
        // 菜单点击事件
        , memuClick: function () {
            var $jq = layui.jquery;
            $jq('#left a[data-href]').on('click', function () {
                var href = $jq(this).attr('data-href');
                if(href == '#home/homeIndex.html'){
                	$jq('.home_icon_memu').prop('src','image/home-active.png');
                	layui.index.loadHtml(href, '#main');
                }else{
                	$jq('.home_icon_memu').prop('src','image/home.png');
                	layui.index.loadHtml(href, '#main');
                }
            });
            isInit = true;
            layui.index.roleIndex();
        }

        // 加载菜单
        , loadMemu: function () {
        	var $jq = layui.jquery;
       		$jq.post(basePath+'admin/Base/menu_list','',function(data){
        		if(!ajaxCall(data)) {
        			return;
        		}
         		var d = data.data;
        		var memu = {};
        		
        		memu.html = '<ul lay-filter="memu" class="layui-nav layui-nav-tree"  lay-filter="test" style="width:100%;background-color: #FFF;">';
        		layui.index.class1Memu(memu,d);
        		memu.html = memu.html+'</ul>';
        		$jq('#left').html(memu.html);
        		layui.element.render('nav'); 
        		layui.element.on('nav(memu)',function(elm){
        			var img = $jq(elm).find('.icon_memu');
        			var src = img.attr('src');
        			if(!src) return;
        			var active = src.indexOf('active')>-1;
        			if(active){
        				src = src.replace('-active.png','.png');
        			}else{
        				src = src.replace('.png','-active.png');
        			}
        			img.prop('src',src);
        			var icon = $jq(elm).find('.layui-icon');
        			if(!icon) return;
        			if(active) {
        				icon.attr('data-open','close');
        				icon.html('&#xe603;');
        				icon.css('color','#CCC');
                	}else {
                		icon.html('&#xe61a;');
                		icon.attr('data-open','open');
                		icon.css('color','#f03232');
                	}
        		});
        		obj.memuClick();
        	});
        	
        }
        ,class1Memu: function(memu,d) {
        	for(var item  in d) {
        		if(item == "home"){
        			memu.html += '<li class="layui-nav-item page-home-item"  id="'+item+'">'
        			 				+ '<a href="#'+d[item].url+'" data-href="#'+d[item].url+'">'
        			 					+ '<img class="home_icon_memu" src="image/'+item+'.png" style="padding-right:10px;height:20px;"/>' + d[item].name
//        			 						+ '<i class="layui-icon" data-open="close" style="color:#CCC;float:right;"></i>'
        			 						+ '</a>'
        			 							+ '</li>';
        		}else{
        			memu.html = memu.html+'<li class="layui-nav-item"  id="'+item+'">';
            		memu.html = memu.html+'<a href="javascript:layui.index.changeMemu(\''+item+'\');">'
            		            +'<img class="icon_memu" src="image/'+item+'.png"/>'
            		            +d[item].name
            		            +'<i class="layui-icon" data-open="close" style="color:#CCC;float:right;">&#xe603;</i> '
            		            +'</a>';
        			layui.index.class2Memu(memu,d[item].child);
        			memu.html = memu.html+'</li>';
        		}
    		}
        }
        ,class2Memu: function(memu,d) {
        	if(!d) return;
        	memu.html = memu.html+'<dl class="layui-nav-child">';
        	for(var item  in d) {
        		memu.html = memu.html+'<dd><a  href="#'+d[item].url+'" data-href="#'+d[item].url+'"><span class="y-memu-span">'+d[item].name+'</span></a></dd>';
    		}
        	memu.html = memu.html+'</dl>';
        }
        ,changeMemu:function(_id) {
        	var $jq = layui.jquery;
        	var dis = $jq('#memu_close').css('display');
        	var command = dis=='none'?'open':'close';
        	if(command != 'open') return true;
        	layui.index.memuOpen(command);
        	return;
        	layui.index.memuOpen();
        	return;
        	
        	var id = '#'+_id;
        	// var styleClass = $jq(id).attr('class')+'';
        	// var active = styleClass.indexOf('layui-nav-itemed')>-1;
        	
        	var src = $jq(id).prop('open')+'';
        	
        	var isOpen = src=='open'; 
        	if(isOpen){
        		$jq(id).prop('open','close');
        	}else{
        		$jq(id).prop('open','open');
        	}
        	var close = isOpen;
        	obj.changeMemuImg(_id,close);
        	obj.changeMumuIcon(id,close);
        }
        ,changeMemuImg: function(id,close) {
        	var src = '';// $jq('#'+id+' .icon_memu').attr('src');
        	// if(!src) return;
        	if(close) {
        		 src = 'image/'+id+'.png?ran='+Math.random();
        		// src = src.replace('-active.png','.png');
        	}else {
        		 src = 'image/'+id+'-active.png?ran='+Math.random();
        		// src = src.replace('.png','-active.png');
        	}
        	$jq('#'+id+' .icon_memu').prop('src',src);
        }
        ,changeMumuIcon:function(id,close){

        	var open = $jq(id+' .layui-icon').attr('data-open');
        	if(close) {
        		$jq(id+' .layui-icon').attr('data-open','close');
        		$jq(id+' .layui-icon').html('&#xe603;');
        		$jq(id+' .layui-icon').css('color','#CCC');
        	}else {
        		$jq(id+' .layui-icon').html('&#xe61a;');
        		$jq(id+' .layui-icon').attr('data-open','open');
        		$jq(id+' .layui-icon').css('color','#f03232');
        	}
        	
        }

        // 请求用户信息
        , display: function () {

        }
        //安全退出
        , logout: function(){

            layer.confirm(layui.language.get('confirm_logout'),{
            	title: [layui.language.get('kindly_reminder'),'text-align:center;'],
                success: function (index, layero) {
                $jq(':focus').blur();
           		 },
                btn : [ layui.language.get('certainly'), layui.language.get('cancel') ]//按钮
			},function(){
                $jq.post(basePath+'admin/Login/logout',function(data) {
                                if (data.code == '200') {
                                    dataStore.set('now_login_user_realname', null);   //销毁缓存的用户名
                                    layer.msg(layui.language.get('logout_suc'), {
                                        icon: 1,
                                        time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                                    }, function(){
                                        window.location.href='login.html';
                                    });
                                }
                            });


                    });
	        }
        //切换语言
        ,switchLanguage : function(lan){
        	layui.use(['language'],function(language){
        		language.changeLan(lan);
        	});
        }
        ,switchLanguage2 : function(lan,index){
            layui.use(['language'],function(language){
                language.changeLan(lan);


            });
        }
        ,memuOpen:function(command){
        	$jq = layui.jquery;
        	if(command=='open'){
        		$jq('#memu_open').hide();
        		$jq('#memu_close').show();
        		$jq('#main').css('margin-left','262px');
        		$jq('#left').css('width','260px');
        		$jq('.main-boder').css('margin-left','260px');
        		$jq('#left .layui-nav-itemed .layui-nav-child').show();
        		$jq('#left .layui-nav-itemed .layui-nav-child').css('display','');
        	}else{
        		$jq('#memu_open').show();
        		$jq('#memu_close').hide();
        		$jq('#left').css('width','48px');
        		$jq('#main').css('margin-left','50px');
        		$jq('.main-boder').css('margin-left','48px');
        		$jq('#left .layui-nav-itemed .layui-nav-child').hide();
        	}

        	var curUrlArr = location.href.split('#');
        	$jq('#left a[data-href="#'+curUrlArr[1]+'"]').click();
        	
        }
        
        //显示当前语言
        ,initCurrLan : function(){
        	var currLan = dataStore.get('current_lan');
        	layui.use(['language'],function(language){
            	if(currLan == 'cn'){
            		$jq('#curr_lan').text(language.get('index_cn'));
            	}else if(currLan == 'en'){
            		$jq('#curr_lan').text(language.get('index_en'));
            	}
            	$jq('.lanBar li').each(function(){
            		if($jq(this).text() == $jq('#curr_lan').text()){
            			$jq(this).children('span').css('color','#f03232');
            		}
            	})
            	
            	
        	});
        }
        
    }


    //输出test接口
    exports('index', obj);
});  


