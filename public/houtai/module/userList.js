layui.define(['jquery','laydate','table','laypage','language'],function (exports) {
	
	var laydate = layui.laydate;
	var $jq = layui.jquery;
	var table = layui.table;
	var laypage = layui.laypage;
	var language = layui.language;
	
    var obj = {
        initView: function () {
        	layui.use('verify_common',function(){
        		layui.verify_common.initRole();
        	});
        	language.render('page_user_list');
        	//初始化搜索框中的时间
        	this.initDate();
        	//初始化表格
        	this.getData(1,50);
        },
        /*
         * 初始化搜索框中的时间
         */
        initDate : function(){
        	laydate.render({
        		elem : '#date',
        		range : true,
        		min: '2018-01-01',
        	   	max: '2100-12-31',
        		done : function(value){
        			var search_string = $jq('#search_string').val();
        			var company_id = $jq('#company_id').val();
        			$jq.post(basePath + 'admin/User/user_list',{'date':value,'search_string':search_string,'company_id':company_id},function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
        				layui.userList.initViewTable(data);
        			})
        		}
        	})
        },
        /*
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit){
        	var date = $jq('#date').val();
        	var search_string = $jq('#search_string').val();
        	var company_id = $jq('#company_id').val();
        	$jq.post(basePath + 'admin/User/user_list',{'page':curr,'limit':limit,'date':date,'search_string':search_string,'company_id':company_id},function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		layui.userList.initViewTable(data);
        	})
        },
        /*
         * 初始化表格
         */
        initViewTable : function(data){
        	var field = data.data.field;
        	var initIndex = 0;
        	table.render({
        		elem : '#userListTable',
        		data: data.data.list,
        		limit: data.data.page.limit,
        		cols : [[
        			{field: 'num',title: 'ID',width:80,templet:function(d){
        				var size = data.data.page.limit;
        				var cur = data.data.page.page;
        				++initIndex;
        				return (cur-1)*size+initIndex;
        			}},
        			{field: 'name', title: field.name},
        			{field: 'idcode', title: field.idcode},
                    {field: 'phone', title: field.phone},
//                    {field: 'source', title: field.source},
                    {field: 'reg_time', title: field.reg_time}
        		]],
        		id: 'userListTable',
        		page: false
        	});
         	  
      	  //执行重载
      	  //完整功能
        	
      	  var canFlush = false;
      	  laypage.render({
      	    elem: 'userListPage'
      	    ,count: data.data.page.count
      	    ,curr: data.data.page.page
      	    ,prev: '<em><</em>'
            ,next: '<em>></em>'
      	    ,limit: data.data.page.limit
      	    ,limits:[20, 50, 100]
      	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
      	    ,jump: function(obj){
      	    	
      	    	if(canFlush) {
      	    		layui.userList.getData(obj.curr,obj.limit);
      	    	}else {
      	    		canFlush=true;
      	    	}
      	        
      	    }
      	  });
        }
        
    }

    //输出test接口
    exports('userList', obj);
});  


