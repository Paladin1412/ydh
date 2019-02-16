layui.define(['language'],function (exports) {
	
    var obj = {
        initView: function () {
        	var $jq = layui.jquery;
        	var form = layui.form;
        	form.render();
        	layui.laydate.render({ elem:'#date',range:true});
        	layui.language.render('need_language');
        	
        }
       , onClick:function(id) {
    	   
       }
       

    }


    //输出test接口
    exports('style', obj);
});  


