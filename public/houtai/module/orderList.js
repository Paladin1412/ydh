layui.define(['jquery','laydate','table','laypage','laytpl','language','form','col_common'],function (exports) {
	
	var laydate = layui.laydate;
	var $jq = layui.jquery;
	var table = layui.table;
	var laypage = layui.laypage;
	var laytpl = layui.laytpl;
	var language = layui.language;
	var form = layui.form;
	
    var obj = {
        initView: function () {
            language.render('paramPage');
            language.render('itemPage');
            layui.form.render();
            //初始化公司和催收员 下拉框
            // layui.use('col_common',function(){
            //     layui.col_common.initMain();
            // });
			this.initCompany();
            //初始化渠道列表
            // this.initChannel();
        	//初始化搜索框中的时间
        	this.initDate();
        	//初始化表格
        	this.getData(1,50);
        	//初始化下拉框
        	this.initSelectOption();

        	
        },


		/**
		 * 获取公司列表
		 */
		initCompany  : function(){
            var roleType = dataStore.get('collection_role_type');
            this.initChannel("null");
            if( roleType == "5"  || roleType == "6"){
                $jq('#statistical_div').show();
			}
            if(roleType == '6'){
                $jq('#hid_company').show();
                $jq.post(basePath+'admin/Base/company_list', '',function (data) {
                    if (data.code == 200) {
                        var cp_nameHtml = '<option value="">'+ layui.language.get('sel_company') +'</option>';
                        $jq.each(data.data, function (i, item) {
                            cp_nameHtml += '<option value="' + item.cp_id + '">' + item.cp_name + '</option>';
                        })
                        $jq('select[name="company_id"]').html(cp_nameHtml);
                        layui.form.render('select');
                        layui.form.on('select(company_id)', function(data){
                            layui.orderList.initChannel(data.value);
                        });
                    }
                });
            }

		},



        /**
		 * 获取渠道列表
         */
        initChannel : function(value){
        	var sendDatas ={};
        	sendDatas.company_id = value;
            $jq.post(basePath + 'admin/Base/get_company_statistical',sendDatas,function(data){
                if (data.code == 200) {
                    var channelHtml = '<option value="">'+ layui.language.get('sel_channel') +'</option>';
                    $jq.each(data.data, function (i, item) {
                        channelHtml += '<option value="' + item.code + '">' + item.name + '</option>';
                    })
                    $jq('select[name="statistical_code"]').html(channelHtml);
                    layui.form.render('select');
                }

			})

		},


        /**
         * 初始化搜索框中的时间
         */
        initDate : function(){
        	laydate.render({
        		elem : '#date',
        		range : true,
        		min: '2018-01-01',
        	   	max: '2100-12-31',
        		done : function(value){
        		    var sendDatas ={};
						sendDatas.date  = value;
						sendDatas.date2 = $jq('#date2').val();
						sendDatas.date3 = $jq('#date3').val();
						sendDatas.company_id = $jq('#company_id').val();
						sendDatas.order_status = $jq('#order_status').val();
						sendDatas.statistical_code = $jq('#statistical_code').val();
						sendDatas.search_string = $jq('#search_string').val();
						sendDatas.risk_status = $jq('#risk_status').val();
						sendDatas.handle_status = $jq('#handle_status').val();
						sendDatas.pay_status = $jq('#pay_status').val();
        			$jq.post(basePath + 'admin/Order/order_list',sendDatas,function(data){
        				layui.orderList.initViewTable(data.data);
        			})
        		}
        	});
        	laydate.render({
        		elem : '#date2',
        		range : true,
        		done : function(value){
                    var sendDatas ={};
						sendDatas.date   = $jq('#date').val();
						sendDatas.date2  = value;
						sendDatas.date3  = $jq('#date3').val();
						sendDatas.company_id = $jq('#company_id').val();
						sendDatas.order_status = $jq('#order_status').val();
						sendDatas.statistical_code = $jq('#statistical_code').val();
                    	sendDatas.search_string = $jq('#search_string').val();
						sendDatas.risk_status = $jq('#risk_status').val();
						sendDatas.handle_status = $jq('#handle_status').val();
						sendDatas.pay_status = $jq('#pay_status').val();
                    $jq.post(basePath + 'admin/Order/order_list',sendDatas,function(data){
        				layui.orderList.initViewTable(data.data);
        			})
        		}
        	});
            laydate.render({
                elem : '#date3',
                range : true,
                min: '2018-01-01',
                max: '2100-12-31',
                done : function(value){
                    var sendDatas ={};
						sendDatas.date  = $jq('#date').val();
						sendDatas.date2 = $jq('#date2').val();
						sendDatas.date3  = value;
						sendDatas.company_id = $jq('#company_id').val();
						sendDatas.order_status = $jq('#order_status').val();
						sendDatas.statistical_code = $jq('#statistical_code').val();
						sendDatas.search_string = $jq('#search_string').val();
						sendDatas.risk_status = $jq('#risk_status').val();
						sendDatas.handle_status = $jq('#handle_status').val();
						sendDatas.pay_status = $jq('#pay_status').val();
                    $jq.post(basePath + 'admin/Order/order_list',sendDatas,function(data){
                        layui.orderList.initViewTable(data.data);
                    })
                }
            });
        },
        /*
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit){
			var sendDatas ={};
				sendDatas.date = $jq('#date').val();
				sendDatas.date2 = $jq('#date2').val();
				sendDatas.date3 = $jq('#date3').val();
				sendDatas.company_id = $jq('#company_id').val();
				sendDatas.order_status = $jq('#order_status').val();
				sendDatas.statistical_code = $jq('#statistical_code').val();
				sendDatas.search_string = $jq('#search_string').val();
				sendDatas.risk_status = $jq('#risk_status').val();
				sendDatas.handle_status = $jq('#handle_status').val();
				sendDatas.pay_status = $jq('#pay_status').val();
				// var sea = $jq("#orderListForm").serialize();
         	$jq.post(basePath + 'admin/Order/order_list?page='+curr+'&limit='+limit,sendDatas,function(data){
        		layui.orderList.initViewTable(data.data);
        	})
        },


        /*
         * 初始化表格
         */
        initViewTable : function(data){
        	var field = data.field;
        	var initIndex = 0;
        	table.render({
        		elem : '#orderListTable',
        		data: data.list,
        		limit: data.page.limit,
        		cols : [[
        			{field: 'num',title: 'ID',width:'6%',templet:function(d){
        				var size = data.page.limit;
        				var cur = data.page.page;
        				++initIndex;
        				return (cur-1)*size+initIndex;
        			}},
        			{field: 'order_no', title: field.order_no,width:'15%',templet:'#orderNo'},
        			{field: 'name', title: field.name,width:'5.9%'},
                    {field: 'phone', title: field.phone,width:'10.2%'},
                    {field: 'application_amount', title: field.application_amount,width:'6.8%'},
                    {field: 'application_term', title: field.application_term,width:'6.7%'},
                    {field: 'add_time', title: field.add_time,width:'7.4%'},
                    {field: 'repay_time', title: field.repay_time,width:'7.4%'},
                    {field: 'risk_status', title: field.risk_status,width:'6.9%'},
                    {field: 'handle_state', title: field.handle_state,width:'6.9%'},
                    {field: 'pay_status', title: field.pay_status,width:'6.9%'},
                    {field: 'order_status', title: field.order_status,width:'6.9%'},
                    {field: 'end_time', title: field.end_time,width:'7.2%'},
                    {field: 'due_day', title: field.due_day,width:'7%'},
                    {field: 'region_name', title: field.region_name,width:'5.9%'},
                    {field: 'source', title: field.source,width:'7.6%'}
        		]],
        		id: 'orderListTable',
        		page: false,
                done: function(res, curr, count){
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
      	    elem: 'orderListPage'
      	    ,count: data.page.count
      	    ,curr: data.page.page
      	    ,prev: '<em><</em>'
      	    ,next: '<em>></em>'
      	    ,limit: data.page.limit
      	    ,limits:[20, 50, 100]
      	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
      	    ,jump: function(obj){
      	    	if(canFlush) {
      	    		layui.orderList.getData(obj.curr,obj.limit);
      	    	}else {
      	    		canFlush=true;
      	    	}
      	        
      	    }
      	  });
        },
        
        /*
         * 订单详情
         */
        orderDetails : function(order_no){
        	$jq('#paramPage').hide();
        	$jq('#itemPage').show();
        	$jq.post( basePath + 'admin/Order/order_info',{'order_no':order_no},function(data){
        		layui.orderList.viewOrderDetails(data.data);
        	})
        },
        /*
         * 详情页
         */
        viewOrderDetails : function(data){
        	var order_info = data.order_info;//订单详细
        	var orderHtml = orderInfo.innerHTML;
        	var orderHtmlStr = laytpl(orderHtml).render(order_info,function(html){
        		$jq('#order_info').html(html);
        		language.render('order_info');
        	});
        	
        	var pay_log = data.pay_log;//放款扣款信息
			if(pay_log == null){
                pay_log =[];
			}
        	var payLogField = data.field[1];
        	table.render({
        		elem : '#loan_debit_record_info',
        		autoHeight:true,
        		data: pay_log,
        		cols : [[
        			  {field: 't_id', title:payLogField.t_id, width: '15%'},
					  {field: 'status', title:payLogField.status, width: '20%'},
					  {field: 'price', title:payLogField.price },
					  {field: 'currency', title:payLogField.currency},
					  {field: 'add_time', title:payLogField.add_time, width: '10%'}
        		]],
        		page: false
        	});
        	
        	var repay_log = data.repay_log;//还款扣款信息
            if(repay_log == null){
                repay_log =[];
            }
        	var repayLogField = data.field[2];
        	table.render({
        		elem : '#repayment_debit_record_info',
        		data: repay_log,
        		autoHeight:true,
        		cols : [[
        			{field: 't_id', title:repayLogField.t_id,width: '20%'},
        			{field: 'status', title:repayLogField.status,width: '20%'},
                    {field: 'price', title:repayLogField.price },
                    {field: 'currency', title:repayLogField.currency },
                    {field: 'add_time', title:repayLogField.add_time,width: '15%'}
        		]],
        		page: false
        	});


        },
        /*
         * 返回订单列表
         */
        orderInfo : function(){
        	$jq('#paramPage').show();
        	$jq('#itemPage').hide();
        },
        /*
         * 初始化下拉框
         */
        initSelectOption : function(){
        	$jq.post(basePath + 'admin/Base/get_order_status','',function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		var orderStatusHtml = '<option value="">' + language.get('order_status') + '</option>';
        		for(var item in data.data){
        			orderStatusHtml += '<option value="' + item + '">' + data.data[item] + '</option>';
        		}
        		$jq('#order_status').html(orderStatusHtml);
        		form.render();
        	})
        }
    }

    //输出test接口
    exports('orderList', obj);
});  


