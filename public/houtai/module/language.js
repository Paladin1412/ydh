layui.define(['jquery'],function (exports) {
	
	I18nfun.initLanObj(dataStore.get('current_lan'));
    var obj = {
			changeLan:function(lan) {
				var $jq = layui.jquery;
				I18nfun.changeLan(lan);
				//将当前语言发送给后台
				$jq.post(basePath + 'admin/Base/set_lang',{'lang':lan},function(data){
					if(!ajaxCall(data)){
						return;
					}
					dataStore.set('current_lan',lan);

					location.reload();
				})
				
//				location.href = location.href;
			}
			,get:function(key) {
				return I18nfun.lanObj[key];
			}
			,render:function(id) {
				var app = new Vue({
					el: '#'+id,
					delimiters: ['<%', '%>'],
					data: I18nfun.lanObj
				});
			} 

    }


    //输出test接口
    exports('language', obj);
});  


