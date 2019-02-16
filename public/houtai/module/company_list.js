layui.define(['laytpl','language','jquery'],function (exports) {
	
	var laytpl = layui.laytpl;
	var language = layui.language;
	var $jq = layui.jquery;
	
	
    var obj = {
        initView: function () {
        	language.render('companyListInfo');
        	language.render('company_list_parent');
            obj.initForm();     
            obj.getData(1,50);
//            obj.initSelectOption();
            
        }
//    ,//初始化下拉框
//    initSelectOption : function(){
//    	 $jq.post(basePath+'admin/Base/company_list','',function(_data){
//	     		if(!ajaxCall(_data)) {
//	     			return;
//	     		}
//	     		var data = _data.data;
//	     		if(!data) return;
//	     		var html='<option value=""  placeholder="'+layui.language.get('company_name')+'">'+layui.language.get('company_name')+'</option>';//+
//	     		for(var index in data) {
//	     			var item = data[index];
//	     			html = html+'<option value="'+item.cp_name+'">'+item.cp_name+'</option>';
//	     		}
//	     		$jq('select[name="company_id"]').html(html);
//	     		layui.form.render('select');
//	     	}); 
//    }
    
    // 初始化页面 start
    ,initForm:function() {
    	var $jq = layui.jquery;
    	
   	 //日期
   	var laydate = layui.laydate;
   	  laydate.render({
   	    elem: '#company_list_date'
   	    ,range: true
   	    ,min: '2018-01-01'
   	    ,max: '2100-12-31'
   	  });
   	  // state
   	$jq.post(basePath+'admin/Base/order_handle_lang','',function(_data){
   		
		if(!ajaxCall(_data)) {
			return;
		}
		var $jq_ = layui.jquery;
		var data = _data.data;
		if(!data) return;
		var html='<option>' + language.get('layui_select') + '</option>';
		for(var index in data) {
			html = html+'<option value="'+index+'">'+data[index]+'</option>';
		}
		$jq_('select[name="handle_state"]').html(html);
		layui.form.render('select');
		
	}); 
   	layui.form.render();
    } // 初始化页面 end
    
    // 请求数据 start
    ,getData: function(curr,limit) {
    	var $jq = layui.jquery;
    	var sendData = $jq('#company_list_form').serialize();
    	$jq.get(basePath+'admin/Company/company_index?page='+curr+'&limit='+limit,sendData,function(data){
    		if(!ajaxCall(data)) {
    			return;
    		}
    		layui.company_list.loadData(data.data);
    	}); 
    }
    ,loadData: function(data) {
    	
    	var fieldArr = data.field;
    	
    	var table = layui.table;
    	var laypage = layui.laypage;
    	var initIndex = 0;
    	  //方法级渲染
    	  table.render({
    	    elem: '#company_list_table'
    	    ,data:data.list
    	    ,limit:1000
    	    ,cols: [[
    			{field: 'num',title: 'ID',width:50,templet:function(d){
    				var size = data.page.limit;
    				var cur = data.page.page;
    				++initIndex;
    				return (cur-1)*size+initIndex;
    			}}
    	      ,{field:'cp_name', title: fieldArr['cp_name']}
    	      ,{field:'cp_num', title: fieldArr['cp_num']}
    	      ,{field:'cp_code', title: fieldArr['cp_code']}
    	     ,{field:'cp_leg_person', title: fieldArr['cp_leg_person']}
    	     ,{field:'cp_contact_person', title: fieldArr['cp_contact_person']}
    	      ,{field:'cp_mobile', title: fieldArr['cp_mobile']}
    	      ,{field:'cp_address', title: fieldArr['cp_address']}
    	      ,{field:'country_name', title: fieldArr['cp_country']}
    	      ,{field:'apply_status', title: fieldArr['status']}
    	      ,{field:'operator_name', title: fieldArr['operator_name']}
    	       ,{field:'operator_date', title: fieldArr['operator_date']}
    	       ,{ title: fieldArr['operate'],width:180,templet:function(d){
    	    	   if(d.status=='0') {
    	    		   return '<button onclick="layui.company_list.goDetail(\''+d.cp_id+'\')" class="layui-btn layui-btn-xs y-btn-red">' + language.get('look') + '</button>'
        	    	   + '<button onclick="layui.company_list.showEdit(\''+d.cp_id+'\',1)" class="layui-btn layui-btn-xs y-btn-red">' + language.get('forbidden') + '</button>';
    	       			
    	    	   }else {
    	    		   return '<button onclick="layui.company_list.goDetail(\''+d.cp_id+'\')" class="layui-btn layui-btn-xs y-btn-red">' + language.get('look') + '</button>'
        	    	   + '<button onclick="layui.company_list.showEdit(\''+d.cp_id+'\',0)" class="layui-btn layui-btn-xs y-btn-red">' + language.get('active') + '</button>';
    	       			
    	    	   }
	       			
    	       }}
    	    ]]
    	    ,id: 'company_list_table'
    	    ,page: false
            ,done: function(res, curr, count){
                var allTableHead = $jq('.layui-table-cell span');//所有表头
                allTableHead.each(function(index,item){
                	item.parentElement.title = item.textContent;
                })
            }
    	  });
    	  
    	  //执行重载
    	  //完整功能
    	  var canFlush = false;
    	  laypage.render({
    	    elem: 'company_list_page'
    	    ,count: data.page.count
    	    ,curr: data.page.page
      	    ,prev: '<em><</em>'
            ,next: '<em>></em>'
    	    ,limit: data.page.limit
    	    ,limits:[20, 50, 100]
    	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
    	    ,jump: function(obj){
    	    	
    	    	if(canFlush) {
    	    		layui.company_list.getData(obj.curr,obj.limit);
    	    	}else {
    	    		canFlush=true;
    	    	}
    	        
    	    }
    	  });
    	
    }
    ,goDetail:function(cpId) {
    	$jq = layui.jquery;
    	$jq('#cp_id_hide').val(cpId);
    	
    	$jq.post(basePath + 'admin/Company/company_edit',{'cp_id':cpId,'type':'1'},function(data){
    		if(!ajaxCall(data)){
    			return;
    		}
    		layui.company_list.lookCompanyListInfo(data.data);
    	});
    }
    ,lookCompanyListInfo : function(data){
    	var html = companyListInfo.innerHTML;
    	var htmlStr = laytpl(html).render(data);
    	layer.open({
    		title: language.get('message'),
    		type: 1,
    		area: ['1000px'],
            success: function (index, layero) {
                $jq(':focus').blur();
            },
    		content: htmlStr
    	})
    }
    ,showDetail:function(id) {
    	$jq = layui.jquery;
    	if(id=='parent'){
    		
        	$jq('#company_list_parent').show();
        	$jq('#company_list_detail').hide();
    	}else if(id=='detail'){
    		$jq('#company_list_parent').hide();
        	$jq('#company_list_detail').show();
    	}
    	
    }
    ,showEdit:function(cpId,status) {
    	$jq = layui.jquery;
    	var content = '';
    	if(status == '1'){
    		content = language.get('edit_to_fobidden_status');
    	}else{
    		content = language.get('edit_to_active_status');
    	}
		layer.open({
			title: [language.get('message')],
            success: function (index, layero) {
                $jq(':focus').blur();
            },
			content: content,
			btn: [language.get('certainly'),language.get('cancel')],
			yes: function(index){
				$jq.post(basePath+'admin/Company/company_change_status',{'cp_id':cpId,'status':status},function(data){
	        		if(!ajaxCall(data)){
	        			return;
	        		}
    				layui.company_list.getData(1,50);
    				layer.close(index);
    			})
			}
		})
    	
    	
    }

    }


    //输出test接口
    exports('company_list', obj);
});  


