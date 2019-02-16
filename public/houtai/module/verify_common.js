layui.define(function (exports) {
	
    var obj = {
    		initRole:function() {
    	    	 var roleType = dataStore.get('global_role_type');
    	    	 var $jq = layui.jquery;
    	    	 if(roleType=='6') {
    	    		 $jq('#hid_company').show();
    	    		 $jq('#hid_search_btn').show();
    	    	 }else {
    	    		 $jq('#hid_company').hide();
    	    		 $jq('#hid_search_btn').hide();
    	    	 }
    	    	 
    	    	 if(roleType=='3' || roleType=='5'){
    		    		$jq('#echart_hidden').hide();
    		    		$jq('#echart_show').show();
    	    	 }else{
    	    		 $jq('#echart_hidden').hide();
 		    		$jq('#echart_show').hide();
    	    	 }
    	    	 
    	    	 if(roleType=='3' || roleType=='5' || roleType=='6' ) {
    	    		 $jq('#hid_user').show();
    	    		 $jq('#hid_fenpei').show();
    	    	 }else {
    	    		 $jq('#hid_user').hide();
    	    		 $jq('#hid_fenpei').hide();
    	    	 }
    	    	 if(roleType=='6') 
    	    	 $jq.post(basePath+'admin/Base/company_list','',function(_data){
    	     		if(!ajaxCall(_data)) {
    	     			return;
    	     		}
    	     		var $jq_ = layui.jquery;
    	     		var data = _data.data;
    	     		if(!data) return;
    	     		var html='<option value=""  placeholder="'+layui.language.get('company_name')+'">'+layui.language.get('company_name')+'</option>';//+
    	     		for(var index in data) {
    	     			var item = data[index];
    	     			html = html+'<option value="'+item.cp_id+'">'+item.cp_name+'</option>';
    	     		}
    	     		$jq_('select[name="company_id"]').html(html);
    	     		layui.form.render('select');
    	     		layui.form.on('select(company_id)', function(data){
    	     			  layui.verify_common.initUserSelect(data.value);
    	     			});    
    	     	}); 
    	     	
    	     	layui.verify_common.initUserSelect();
    	     	
    	    }
    ,initUserSelect:function(company) {
    	var $jq = layui.jquery;
    	var sendData = {};
    	if(company) {
    		sendData.company_id =company;
    	}
    	 var roleType = dataStore.get('global_role_type');
    	 if(roleType=='6' && !company) {
    		 sendData.company_id = 0;
    	 }
    	if(roleType=='3' || roleType=='5' || roleType=='6')
        	$jq.post(basePath+'admin/Base/handle_user_list',sendData,function(_data){
        		if(!ajaxCall(_data)) {
        			return;
        		}
        		var $jq_ = layui.jquery;
        		var data = _data.data;
        		if(!data) return;
        		var html='<option placeholder="'+layui.language.get('verify_person')+'" value="">'+'</option>';// +
        		for(var index in data) {
        			var item = data[index];
        			html = html+'<option value="'+item.admin_id+'">'+item.real_name+'</option>';
        		}
        		$jq_('select[name="admin_id"]').html(html);
        		layui.form.render('select');

        	});
    }
    ,showOrder:function(orderNo,company_code) {
    	var sendData={};
    	sendData.order_no = orderNo;
    	sendData.company_code = company_code;
    	$jq = layui.jquery;
    	$jq.post(basePath+'admin/Login/agreement',sendData,function(data){
    		if(!ajaxCall(data)) {
    			return;
    		}
    		layer.open({
    			title:layui.language.get('contract'),
    			type:2,
    			area:['800px','600px'],
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
    			content:basePath+'admin/Login/agreement?order_no='+orderNo+'&company_code='+company_code
    		});
    	});
    }
    ,goDetail:function(orderNo,view_type){
    	layui.use(['verify_detail'], function(){
    		  layui.verify_detail.initView(orderNo,view_type);
    	});
    }
    ,goColDetail:function(orderNo){
       layui.use(['colDetail'], function(){
            layui.colDetail.initView(orderNo);
        });
    }
    ,showDetailPage:function(id) {
    	$jq = layui.jquery;
    	if(id=='detail') {
    		$jq('#all_verify_parent').hide();
        	$jq('#all_verify_detail').show();
        	
    	}else if(id=='parent') {
    		$jq('#all_verify_detail').hide();
        	$jq('#all_verify_parent').show();
    	}
    	
    }

    ,showColDetailPage:function(id) {

            if(id=='detail') {
                $jq('#paramPage').hide();
                $jq('#all_col_detail').show();

            }else if(id=='parent') {
                $jq('#all_col_detail').hide();
                $jq('#paramPage').show();
            }

        }




    ,getDay :function(num, str) { //如果要获取昨天的日期，num就是-1， 前天的就是-2，依次类推。str表示年月日间的分割方式。
        var today = new Date();
        var nowTime = today.getTime();
        var ms = 24*3600*1000*num;
        today.setTime(parseInt(nowTime + ms));
        var oYear = today.getFullYear();
        var oMoth = (today.getMonth() + 1).toString();
        if (oMoth.length <= 1) oMoth = '0' + oMoth;
        var oDay = today.getDate().toString();
        if (oDay.length <= 1) oDay = '0' + oDay;
        return oYear + str + oMoth + str + oDay;
    }


    }

    //输出test接口
    exports('verify_common', obj);
});  


