layui.define(['verify_common','language','jquery','table'],function (exports) {
	
	var language = layui.language;
	var $jq = layui.jquery;
	var table = layui.table;
	
    var obj = {
        initView: function () {
        	obj.initForm(); 
        	//初始化报表按钮css
        	this.initBtnCss(dataStore.get('current_lan'));
        	layui.use('verify_common',function(){
        		layui.verify_common.initRole();
        	});
//       	 var roleType = dataStore.get('global_role_type');
//   		 if(roleType=='3' || roleType=='5' || roleType=='6'){
//         	//初始化报表
//         	obj.initVerifyReport();
//   		 }

        	
          
            obj.initBtn();
            obj.getData(1,50);
        }
    
    // 初始化页面 start
    ,initForm:function() {
    	layui.language.render('page_all_verify');
    	layui.form.render();
    	
   	 //日期
   	var laydate = layui.laydate;
   	  laydate.render({
   	    elem: '#all_verify_date'
   	    ,range: true
   	    ,min: '2018-01-01'
   	    ,max: '2100-12-31'
   	    ,done: function(value){
   	    	var sendData = {};
   	    	sendData.date = value;
   	    	sendData.approval_time = $jq('#approval_time').val();
   	    	sendData.company_id = $jq('#company_id').val();
   	    	sendData.admin_id = $jq('#admin_id').val();
   	    	sendData.handle_state = $jq('#handle_state').val();
   	    	sendData.search_string = $jq('#search_string').val();
   	    	
   	    	$jq.post(basePath+'admin/Order/order_all',sendData,function(data){
   	    		if(!ajaxCall(data)) {
   	    			return;
   	    		}
   	    		layui.all_verify.loadData(data.data);
   	    	}); 
   	    }
   	  });
   	  //审批时间
   	laydate.render({
   	    elem: '#approval_time'
   	    ,range: true
   	    ,min: '2018-01-01'
   	    ,max: '2100-12-31'
   	    ,done: function(value){
   	    	var sendData = {};
   	    	sendData.approval_time = value
   	    	sendData.date = $jq('#all_verify_date').val();
   	    	sendData.company_id = $jq('#company_id').val();
   	    	sendData.admin_id = $jq('#admin_id').val();
   	    	sendData.handle_state = $jq('#handle_state').val();
   	    	sendData.search_string = $jq('#search_string').val();
   	    	
   	    	$jq.post(basePath+'admin/Order/order_all',sendData,function(data){
   	    		if(!ajaxCall(data)) {
   	    			return;
   	    		}
   	    		layui.all_verify.loadData(data.data);
   	    	}); 
   	    }
   	  });
   	  
   	  laydate.render({
     	    elem: '#orderTime'
     	    ,range: true
     	    ,min: '2018-01-01'
     	    ,max: '2100-12-31'
    		,done : function(value){
    			 var roleType = dataStore.get('global_role_type');
    			 if(roleType=='3' || roleType=='5' || roleType=='6') 
    			$jq.post(basePath + 'admin/Echart/order_count_data',{'date':value},function(data){
            		if(!ajaxCall(data)){
            			return;
            		}
            		var approvalOrderReport = echarts.init(document.getElementById('approvalOrderReport'));
    				layui.all_verify.initApprovalOrderReport(data.data,approvalOrderReport);
    			})
    		}
     	  });
    } // 初始化页面 end
    
    ,initVerifyReport : function(){
    	var chartIds = {};
//    	document.getElementById('approvalReport').style.width=(document.getElementById('page_all_verify').offsetWidth/2-60) +'px';//动态计算图表的宽度；
    	var approvalReport = echarts.init(document.getElementById('approvalReport'));
    	chartIds['approvalReport'] = approvalReport;
//    	document.getElementById('approvalOrderReport').style.width=(document.getElementById('page_all_verify').offsetWidth/2-60) +'px';//动态计算图表的宽度；
    	var approvalOrderReport = echarts.init(document.getElementById('approvalOrderReport'));
    	chartIds['approvalOrderReport'] = approvalOrderReport;
    	layui.all_verify.initReport(chartIds);
    	//图表自适应
		window.addEventListener("resize", function () {
			approvalReport.resize();
			approvalOrderReport.resize();
		});
    }
    
    ,initReport : function(chartIds){
		$jq.post(basePath + 'admin/Echart/order_handle_data','',function(data){
			if(!ajaxCall(data)){
				return;
			}
			//初始化信审考核报表
			layui.all_verify.initApprovalReportEcharts(data.data,chartIds.approvalReport);
		});
	    	//初始化信审订单走势报表
	    	layui.all_verify.initApprovalOrderReportView(chartIds.approvalOrderReport);
    }
    //初始化信审订单走势报表
    ,initApprovalOrderReportView : function(chartId){
    	$jq.post(basePath + 'admin/Echart/order_count_data',{'type':'1'},function(data){
    		if(!ajaxCall(data)){
    			return;
    		}
    		//初始化信审订单走势报表
    		layui.all_verify.initApprovalOrderReport(data.data,chartId);
    	})
    }
    //按周、月、年展示信审订单走势报表
    ,getDates : function(type){
		if(type == '1'){
			selectThisWeek('.verify-btn-left','.verify-btn-left span','.verify-btn-right','.verify-btn-right span');
		}else{
			selectThisMonth('.verify-btn-left','.verify-btn-left span','.verify-btn-right','.verify-btn-right span');
		}
    	var roleType = dataStore.get('global_role_type');
		 if(roleType=='3' || roleType=='5' || roleType=='6') 
    	$jq.post(basePath + 'admin/Echart/order_count_data',{'type':type},function(data){
    		if(!ajaxCall(data)){
    			return;
    		}
    		//初始化信审订单走势报表
    		var approvalOrderReport = echarts.init(document.getElementById('approvalOrderReport'));
    		layui.all_verify.initApprovalOrderReport(data.data,approvalOrderReport);
    	})
    }
    //初始化信审订单走势报表
    ,initApprovalOrderReport : function(data,chartId){
    	
    	var dates = [];//横坐标
    	var orderCount = [];//订单总数
    	var orderHandleCount = [];//已完成订总数
    	var lengend = [language.get('order_count'),language.get('order_handle_count')];
    	
    	$jq.each(data,function(i,item){
    		dates.push(item.order_time);
    		orderCount.push(item.order_count);
    		orderHandleCount.push(item.order_handle_count);
    	});
		
//		document.getElementById('approvalOrderReport').style.width=(document.getElementById('page_all_verify').offsetWidth/2-60) +'px';//动态计算图表的宽度；
//    	var approvalOrderReport = echarts.init(document.getElementById('approvalOrderReport'));
    	option = {
    			color: ['#a1e9d9','#59c8e2'],
    			tooltip : {
		            trigger: 'axis',
		        },
		        legend : {
					orient : 'horizontal',
					data : lengend,
					bottom : 18,
					x : 'center',
					itemWidth: 10,
					itemHeight: 10,
					itemGap: 10
				},
				grid: {
					top:'7%',
		            left: '4%',
		            right: '5%',
		            bottom: '25%',
		            containLabel: true
		        },
		        xAxis : [
		            {
		                type : 'category',
		                data : dates,
		                boundaryGap : false,
		                axisTick: {
		                    alignWithLabel: true
		                },
                        axisLabel: {
                            rotate: 40,
                            interval: 0
                        }
		            }
		        ],
		        yAxis : [
		            {
		                type : 'value',
		                splitLine:{
		                	show:true,
		                	lineStyle:{
		                		color:'#e9ebf3',
		                		type:'dashed'
		                	}
		                }
		            }
		        ],
				dataZoom:[
					{
						type: 'slider',
						show: true,
						xAxisIndex: [0],
						handleSize: 20,//滑动条的 左右2个滑动条的大小
						height: 20,//组件高度
						left: 70,
						right: 75,
						bottom: -3,
						// start: 60,                                //数据窗口范围的起始百分比,表示30%
						// end: 100                                  //数据窗口范围的结束百分比,表示70%
					}
				],
		        series : [
		            {
		                name:lengend[0],
		                type:'line',
		                data:orderCount,
		                symbol:'circle',
		                symbolSize:8
		            },
		            {
		                name:lengend[1],
		                type:'line',
		                data:orderHandleCount,
		                symbol:'circle',
		                symbolSize:8
		            }
		        ],
    	};
    	// 使用刚指定的配置项和数据显示图表。
    	chartId.setOption(option, true);
    }
    //初始化信审考核报表
    ,initApprovalReportEcharts : function(data,chartId){
    	
    	var xData = data.xAxis;//横坐标
    	var orderCount = data.order_count;//当天已完成订单总数
    	var orderTodoCount = data.order_todo_count;//未审批订单总数
		var lengend = [language.get('no_approval_order_count'),language.get('now_order_handle_count')];
 	option = {
    			color: ['#a1e9d9','#59c8e2'],
		    	tooltip : {
		            trigger: 'axis',
		            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
		                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
		            }
		        },
		        legend : {
					orient : 'horizontal',
					data : lengend,
					x : 'center',
					bottom : 18,
					itemWidth: 10,
					itemHeight: 10,
					itemGap: 10
				},
		        grid: {
		        	top:'7%',
		            left: '4%',
		            right: '5%',
		            bottom: '25%',
		            containLabel: true
		        },
		        xAxis : [
		            {
		                type : 'category',
		                data : xData,
		                axisTick: {
		                    alignWithLabel: true
		                },
		            }
		        ],
		        yAxis : [
		            {
		                type : 'value',
		                splitLine:{
		                	show:true,
		                	lineStyle:{
		                		color:'#e9ebf3',
		                		type:'dashed'
		                	}
		                }
		            }
		        ],
		        series : [
		            {
		                name:lengend[0],
		                type:'bar',
		                barMaxWidth :50,
		                data:orderTodoCount
		            },
		            {
		                name:lengend[1],
		                type:'bar',
		                barMaxWidth :50,	
		                data:orderCount
		            }
		        ],
    	};
    	// 使用刚指定的配置项和数据显示图表。
    	chartId.setOption(option, true);
    }
    
    ,initBtn:function() {
    	
    	var $jq = layui.jquery;
    	 var roleType = dataStore.get('global_role_type');
    	$jq.post(basePath+'admin/Base/order_handle_lang','',function(_data){
    		if(!ajaxCall(_data)) {
    			return;
    		}
    		var $jq_ = layui.jquery;
    		var data = _data.data;
    		if(!data) return;
    		var html='<option value="" placeholder="'+layui.language.get('verify_status')+'">'+'</option>';
    		for(var index in data) {
    			html = html+'<option value="'+index+'">'+data[index]+'</option>';
    		}
    		$jq_('select[name="handle_state"]').html(html);
    		layui.form.render('select');
    		
    	}); 
    }
    // 请求数据 start
    ,getData: function(curr,limit,obj) {
    	var $jq = layui.jquery;
    	if(obj){
    		$jq('#order_field').val(obj.field);
    		$jq('#order_sort').val(obj.type);
    	}
    	var sendData = $jq('#all_verify_form').serialize();
    	if(sendData&&sendData.indexOf('admin_id=-100')>-1){
    		sendData=sendData.replace('admin_id=-100','admin_id=');
    	}
    	if(sendData&&sendData.indexOf('company_id=-100')>-1){
    		sendData=sendData.replace('company_id=-100','company_id=');
    	}
    	if(sendData&&sendData.indexOf('handle_state=-100')>-1){
    		sendData=sendData.replace('handle_state=-100','handle_state=');
    	}
    	$jq.post(basePath+'admin/Order/order_all?page='+curr+'&limit='+limit,sendData,function(data){
    		if(!ajaxCall(data)) {
    			return;
    		}
    		layui.all_verify.loadData(data.data,obj);
    	}); 
    }
    ,loadData: function(data,objData) {
    	var fieldArr = data.field;
    	var table = layui.table;
    	var laypage = layui.laypage;
    	var initIndex = 0;
    	  //方法级渲染
    	  table.render({
    	    elem: '#all_verify_table'
    	    ,data:data.list	
    	    ,limit:data.page.limit
    	    ,cols: [[
    			{field: 'num',title: 'ID',width:80,templet:function(d){
    				var size = data.page.limit;
    				var cur = data.page.page;
    				++initIndex;
    					return (cur-1)*size+initIndex;
    			}}
    	     	,{field:'order_no', title: fieldArr['order_no'],width:165,  templet:function(d){
    	       			return '<a  class="td_a"  href="javascript:layui.verify_common.goDetail(\''+d.order_no+'\',0)">'+d.order_no+'</a>';
      			}
		       }
		       ,{field:'name', title: fieldArr['user_name']}//,minWidth:84
		       ,{field:'idcode', title: fieldArr['user_card']}//,width:80
		       ,{field:'phone', title: fieldArr['user_phone'],width:124}
		       ,{field:'application_amount', title: fieldArr['application_amount']}//,minWidth:86
		       ,{field:'application_term', title: fieldArr['application_term']}//,width:83
		       ,{field:'add_time', title: fieldArr['create_time'],sort:true}//,minWidth:83
		       ,{field:'handle_state', title: fieldArr['handle_state']}//,width:95
		       ,{field:'first_admin', title: "初审人"}//,width:85
		       ,{field:'second_admin', title: "终审人"}//,width:85
		       ,{field:'confirm_time', title: fieldArr['handle_time'],sort:true}//,width:94
		       ,{field:'',width:100, title: fieldArr['operate'],templet:function(d){
		    	     	return '<button onclick="layui.verify_common.showOrder(\''+d.order_no+'\',\''+d.company_code+'\')" class="layui-btn layui-btn-xs y-btn-red">'+layui.language.get('contract')+'</button>';
		       	}
		       }
    	    ]]
    	    ,id: 'all_verify_table'
    	    ,page: false
            ,done: function(res, curr, count){
            	if(objData){
            		$jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
            	}
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
    	    elem: 'all_verify_page'
    	    ,count: data.page.count
    	    ,curr: data.page.page
      	    ,prev: '<em><</em>'
            ,next: '<em>></em>'
    	    ,limit: data.page.limit
    	    ,limits:[20, 50, 100]
    	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
    	    ,jump: function(obj){
    	    	if(canFlush) {
    	    		layui.all_verify.getData(obj.curr,obj.limit,objData);
    	    	}else {
    	    		canFlush=true;
    	    	}
    	        
    	    }
    	  });
    	
    	}
    	//初始化报表按钮css
    	,initBtnCss : function(lan){
    		$jq('.verify-btn-left').css('background-color','#1c3368');
    		$jq('.verify-btn-left span').css('color','#FFFFFF');
    		if(lan == 'cn'){
    			$jq('.echart-btn-show').css('margin-left','38%');
    		}else if(lan == 'en'){
    			$jq('.echart-btn-show').css('margin-left','35%');
    		}
    	}
    	//展示或者隐藏信审报表
	    ,echartSwitch:function(flag){
	    	if(flag){//点击隐藏
	    		$jq('#echart_hidden').hide();
	    		$jq('#echart_show').show();
	    		$jq('#all-approval-report').hide();
	    	}else{
	    		$jq('#echart_hidden').show();
	    		$jq('#echart_show').hide();
	    		$jq('#all-approval-report').show();
		       	var roleType = dataStore.get('global_role_type');
		   		if(roleType=='3' || roleType=='5' || roleType=='6'){
			        //初始化报表
			        layui.all_verify.initVerifyReport();
		   		 }
	    	}
	    }
	    
	    
	    /**
	     * 处理监听事件
	     */
	    ,tool : function(){
	    	table.on('sort(allVerifyTableEvent)',function(obj){
	    		layui.all_verify.getData(1,50,obj);
	    	})
	    }
   
    } // end

    //输出test接口
    exports('all_verify', obj);
});  


