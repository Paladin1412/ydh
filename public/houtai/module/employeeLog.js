layui.define(['jquery','laydate','table','laypage','language'],function (exports) {
	
	var laydate = layui.laydate;
	var $jq = layui.jquery;
	var table = layui.table;
	var laypage = layui.laypage;
	var language = layui.language;
	
    var obj = {
        initView: function () {
        	language.render('page_employee_log');
        	
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
        			$jq.post(basePath + 'admin/Personnel/user_log',{'date':value,'search_string':search_string},function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
        				layui.employeeLog.initViewTable(data);
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
        	$jq.post(basePath + 'admin/Personnel/user_log',{'page':curr,'limit':limit,'date':date,'search_string':search_string},function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		layui.employeeLog.initViewTable(data);
        	})
        },
        /*
         * 初始化表格
         */
        initViewTable : function(data){
        	var field = data.data.field;
        	var initIndex = 0;
        	table.render({
        		elem : '#employeeLogTable',
        		data: data.data.list,
        		limit: data.data.page.limit,
        		cols : [[
        			{field: 'num',title: 'ID',width:'5%',templet:function(d){
        				var size = data.data.page.limit;
        				var cur = data.data.page.page;
        				++initIndex;
        				return (cur-1)*size+initIndex;
        			}},
        			{field: 'user_name', title: field.user_name,width:'13%'},
        			{field: 'real_name', title: field.real_name,width:'15%'},
                    {field: 'cp_name', title: field.cp_name,width:'14%'},
                    {field: 'log_info', title: field.log_info,width:'23%'},
                    {field: 'log_ip', title: field.log_ip,width:'11%'},
                    {field: 'log_time', title: field.log_time,width:'18%',sort:true}
        		]],
        		id: 'employeeLogTable',
        		page: false
        	});
         	  
      	  //执行重载
      	  //完整功能
        	
      	  var canFlush = false;
      	  laypage.render({
      	    elem: 'employeeLogPage'
      	    ,count: data.data.page.count
      	    ,curr: data.data.page.page
      	    ,prev: '<em><</em>'
            ,next: '<em>></em>'
      	    ,limit: data.data.page.limit
      	    ,limits:[20, 50, 100]
      	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
      	    ,jump: function(obj){
      	    	
      	    	if(canFlush) {
      	    		layui.employeeLog.getData(obj.curr,obj.limit);
      	    	}else {
      	    		canFlush=true;
      	    	}
      	        
      	    }
      	  });
        }
        
    }

    //输出test接口
    exports('employeeLog', obj);
});  


