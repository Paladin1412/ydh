layui.define(['jquery','table','laypage','layer','laytpl','form','language'],function (exports) {
	
	var $jq = layui.jquery;
	var table = layui.table;
	var laypage = layui.laypage;
	var layer = layui.layer;
	var laytpl = layui.laytpl;
	var form = layui.form;
	var language = layui.language;
	
    var obj = {
        initView: function () {
        	language.render('page_fee_config');
        	language.render('feeConfigTool');
        	language.render('addFeeConfig');
        	//初始化表格
        	this.getData(1,50);
        },
        /*
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit){
        	$jq.post(basePath + 'admin/System/loan_list',{'page':curr,'limit':limit},function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		layui.feeConfig.initViewTable(data);
        	})
        },
        /*
         * 初始化表格
         */
        initViewTable : function(data){
        	var field = data.data.field;
        	var initIndex = 0;
        	table.render({
        		elem : '#feeConfigTable',
        		data: data.data.list,
        		limit: data.data.page.limit,
        		cols : [[
        			{field: 'num',title: 'ID',width:75,templet:function(d){
        				var size = data.data.page.limit;
        				var cur = data.data.page.page;
        				++initIndex;
        				return (cur-1)*size+initIndex;
        			}},
        			{field: 'apply_term', title: field.apply_term,width:80},
        			{field: 'apply_amount', title: field.apply_amount},
                    {field: 'rate', title: field.rate,minWidth:90},
                    {field: 'approval_fee', title: field.approval_fee},
                    {field: 'service_fee', title: field.service_fee},
                    {field: 'over_fee', title: field.over_fee,minWidth:90},
                    {field: 'term_fee', title: field.term_fee},
                    {field: 'max_money', title: field.max_money},
                    {field: 'cp_name', title: field.company_name},
                    {field: 'right',title: field.operate,width:80,toolbar: '#feeConfigTool'}
        		]],
        		page: false
        	});
        	
        	//执行重载
        	  //完整功能
          	
    	  var canFlush = false;
    	  laypage.render({
    	    elem: 'feeConfigPage'
    	    ,count: data.data.page.count
    	    ,curr: data.data.page.page
      	    ,prev: '<em><</em>'
            ,next: '<em>></em>'
    	    ,limit: data.data.page.limit
        	,limits:[20, 50, 100]
    	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
    	    ,jump: function(obj){
    	    	
    	    	if(canFlush) {
    	    		layui.feeConfig.getData(obj.curr,obj.limit);
    	    	}else {
    	    		canFlush=true;
    	    	}
    	        
    	    }
    	  });
        },
        /*
         * 处理监听事件
         */
        tool : function(){
        	//表格监听
        	table.on('tool(feeConfigEvent)',function(obj){
        		var data = obj.data;//获取一行数据的值
        		//编辑操作
        		if(obj.event === 'edit'){
        			$jq.post(basePath + 'admin/System/loan_edit',{'type':'1','type_id':data.type_id},function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
        				layui.feeConfig.viewEdit(data.data);
        			})
        		}
        	});
        	//监听表单验证
        	form.verify({
//        		applyTerm : [/[0-9]*/, language.get('apply_term_must_number')]
        		applyTerm : [/^\d+(\.\d+)?$/, language.get('apply_term_must_number')],
        		applyAmount : [/^\d+(\.\d+)?$/, language.get('apply_amount_must_number')],
        		rate : [/^\d+(\.\d+)?$/, language.get('rate_must_number')],
        		serviceFee : [/^\d+(\.\d+)?$/, language.get('service_fee_must_number')],
        		approvalFee : [/^\d+(\.\d+)?$/, language.get('approval_fee_must_number')],
        		overFee : [/^\d+(\.\d+)?$/, language.get('over_fee_must_number')],
        		termFee : [/^\d+(\.\d+)?$/, language.get('term_fee_must_number')],
        		maxMoney : [/^\d+(\.\d+)?$/, language.get('max_money_must_number')]
        	})
        	
        	//监听表单提交
        	form.on('submit(addFeeConfigForm)',function(data){
        		data.field['type'] = '2';
        		$jq.post(basePath + 'admin/System/loan_edit',data.field,function(data){
            		if(!ajaxCall(data)){
            			return;
            		}
        			layer.closeAll();
    				layer.msg(data.message);
    				layui.feeConfig.initView();
        		})
        		return false;
        	})
        },
        /*
         * 编辑弹出框
         */
        viewEdit : function(data){
        	var html = addFeeConfig.innerHTML;
        	var htmlStr = laytpl(html).render(data);
        	layer.open({
        		type: 1,
        		area: '600px',
        		title: [language.get('edit_fee'),'text-align:center;font-size:18px;'],
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
        		content: htmlStr
        	});
        	form.render();
        	layui.feeConfig.initCss(dataStore.get('current_lan'));
        },
        /*
         * 初始化css
         */
        initCss : function(lan){
        	if(lan == 'id'){
        		$jq('.layui-form-label-id').css('width','190px');
        		$jq('.layui-input-inline-id').css('width','300px');
        	}else if(lan == 'cn'){
        		$jq('.layui-form-label-id').css('width','100px');
        		$jq('.layui-input-inline-id').css('width','400px');
        	}else if(lan == 'en'){
        		$jq('.layui-form-label-id').css('width','160px');
        		$jq('.layui-input-inline-id').css('width','330px');
        	}
        }
    }

    //输出test接口
    exports('feeConfig', obj);
});  


