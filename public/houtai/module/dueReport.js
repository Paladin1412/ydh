layui.define(['jquery','table','laypage','element','language','laydate','form'],function(exports){
	var $jq = layui.jquery;
	var table = layui.table;
	var laypage = layui.laypage;
	var element = layui.element;
	var language = layui.language;
	var laydate = layui.laydate;
	var form = layui.form;
	
	var obj = {
			initView : function(){
				language.render('due_report');
				
				//初始化表格
				layui.dueReport.getData(1,50);
				
				//初始化时间
				layui.dueReport.initDate();
				
				//初始化公司下拉框
				layui.dueReport.initCompanySelect();
				
			}
			/**
			 * 初始化公司下拉框
			 */
			,initCompanySelect : function(){
				var roleType = dataStore.get('collection_role_type');
				if(roleType == '6'){
					$jq('#hid_company').show();
					$jq.post(basePath+'admin/Base/company_list', '',function (data) {
						if(!ajaxCall(data)){
							return;
						}
						var cp_nameHtml = '<option value="">'+ layui.language.get('cp_real_name') +'</option>';
                        $jq.each(data.data, function (i, item) {
                            cp_nameHtml += '<option value="' + item.cp_id + '">' + item.cp_name + '</option>';
                        })
                        $jq('#company_id').html(cp_nameHtml);
                        //渲染下拉框
                        form.render('select');
					});
				}
			}
			/**
			 * 初始化时间
			 */
			,initDate : function(){
		   	  	laydate.render({
		   	  		elem: '#date'
		   	  		,range: true
		   	  		,min: '2018-01-01'
		   	  		,max: '2100-12-31'
		   	  		,done: function(value){
						var sendData = {};
						sendData.date = value
						sendData.company_id = $jq('#company_id').val();
		   	  			$jq.post(basePath+'admin/Echart/get_due_list_data',sendData,function(data){
		   	  				if(!ajaxCall(data)) {
		   	  					return;
		   	  				}
		   	  				layui.dueReport.initViewTable(data.data);
		   	  			}); 
		   	  		}
		   	  });
			}
			/**
			 * 获取表格数据
			 */
			,getData : function(page,limit){
				var sendData = {};
				sendData.date = $jq('#date').val();
				sendData.company_id = $jq('#company_id').val();
		    	$jq.post(basePath+'admin/Echart/get_due_list_data?page='+page+'&limit='+limit,sendData,function(data){
		    		if(!ajaxCall(data)) {
		    			return;
		    		}
		    		layui.dueReport.initViewTable(data.data);
		    	}); 
			}
			/**
			 * 初始化表格
			 */
			,initViewTable : function(data){
				var fieldArr = data.field;
				//表格
				table.render({
					elem: '#dueReportTable'
					,data:data.list	
					,limit:data.page.limit
					,cols: [[
						{field:'daily_repay_schedule', title: language.get('daily_repay_schedule'),colspan:7}//每日还款情况表（笔数）
						,{field:'due_repay_amount', title: language.get('due_repay_amount'),colspan:6}//逾期后还款笔数
						,{field:'due_rate', title: language.get('due_rate'),colspan:6}//逾期率
						,{field:'section_collect_rate', title: language.get('section_collect_rate'),colspan:6}//分段催回率
						,{field:'add_up_collect_rate', title: language.get('add_up_collect_rate'),colspan:6}//累计催回率
					],[
						{field:'date_str', title: fieldArr['date_str'],minWidth:88}//应还款日
						,{field:'order_pay_sum', title: fieldArr['order_pay_sum']}//应还款数
						,{field:'order_repay_sum', title: fieldArr['order_repay_sum']}//实际还款数
						,{field:'order_today_due_sum', title: fieldArr['order_today_due_sum'],minWidth:75}//首次逾期笔数
						,{field:'order_due_sum', title: fieldArr['order_due_sum'],minWidth:75}//当前逾期笔数
						,{field:'first_overdue_rate', title: fieldArr['first_overdue_rate'],templet:function(d){//首次逾期比
							return '<div class="layui-progress" lay-filter="for' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
									+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.first_overdue_rate + '%</span>'
										+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>' //d8ae61
											+ '</div>';
						}}
						,{field:'current_overdue_rate', title: fieldArr['current_overdue_rate'],templet:function(d){//当前逾期比
							return '<div class="layui-progress" lay-filter="cor' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.current_overdue_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//d8ae61
									+ '</div>';
						}}
						,{field:'order_due_repay_pd_1_3', title: fieldArr['1_3_days'],templet:function(d){//逾期还款笔数(1-3天)
							var repayAmount = d.order_due_repay_pd_1_3;
							if(repayAmount == 0){
								return '<div style="background-color:RGB(11, 192, 125,0)">' + repayAmount + '</div>';
							}else if(repayAmount > 0 && repayAmount <= 10){
								return '<div style="background-color:RGB(11, 192, 125,0.2)">' + repayAmount + '</div>';
							}else if(repayAmount > 10 && repayAmount <= 30){
								return '<div style="background-color:RGB(11, 192, 125,0.4)">' + repayAmount + '</div>';
							}else if(repayAmount > 30 && repayAmount <= 50){
								return '<div style="background-color:RGB(11, 192, 125,0.7)">' + repayAmount + '</div>';
							}else if(repayAmount > 50){
								return '<div style="background-color:RGB(11, 192, 125,1)">' + repayAmount + '</div>';
							}
						}}
						,{field:'order_due_repay_pd_4_8', title: fieldArr['4_8_days'],templet:function(d){//逾期还款笔数(4-8天)
							var repayAmount = d.order_due_repay_pd_4_8;
							if(repayAmount == 0){
								return '<div style="background-color:RGB(11, 192, 125,0)">' + repayAmount + '</div>';
							}else if(repayAmount > 0 && repayAmount <= 10){
								return '<div style="background-color:RGB(11, 192, 125,0.2)">' + repayAmount + '</div>';
							}else if(repayAmount > 10 && repayAmount <= 30){
								return '<div style="background-color:RGB(11, 192, 125,0.4)">' + repayAmount + '</div>';
							}else if(repayAmount > 30 && repayAmount <= 50){
								return '<div style="background-color:RGB(11, 192, 125,0.7)">' + repayAmount + '</div>';
							}else if(repayAmount > 50){
								return '<div style="background-color:RGB(11, 192, 125,1)">' + repayAmount + '</div>';
							}
						}}
						,{field:'order_due_repay_pd_9_18', title: fieldArr['9_18_days'],templet:function(d){//逾期还款笔数(9-18天)
							var repayAmount = d.order_due_repay_pd_9_18;
							if(repayAmount == 0){
								return '<div style="background-color:RGB(11, 192, 125,0)">' + repayAmount + '</div>';
							}else if(repayAmount > 0 && repayAmount <= 10){
								return '<div style="background-color:RGB(11, 192, 125,0.2)">' + repayAmount + '</div>';
							}else if(repayAmount > 10 && repayAmount <= 30){
								return '<div style="background-color:RGB(11, 192, 125,0.4)">' + repayAmount + '</div>';
							}else if(repayAmount > 30 && repayAmount <= 50){
								return '<div style="background-color:RGB(11, 192, 125,0.7)">' + repayAmount + '</div>';
							}else if(repayAmount > 50){
								return '<div style="background-color:RGB(11, 192, 125,1)">' + repayAmount + '</div>';
							}
						}}
						,{field:'order_due_repay_pd_19_30', title: fieldArr['19_30_days'],templet:function(d){//逾期还款笔数(19-30天)
							var repayAmount = d.order_due_repay_pd_19_30;
							if(repayAmount == 0){
								return '<div style="background-color:RGB(11, 192, 125,0)">' + repayAmount + '</div>';
							}else if(repayAmount > 0 && repayAmount <= 10){
								return '<div style="background-color:RGB(11, 192, 125,0.2)">' + repayAmount + '</div>';
							}else if(repayAmount > 10 && repayAmount <= 30){
								return '<div style="background-color:RGB(11, 192, 125,0.4)">' + repayAmount + '</div>';
							}else if(repayAmount > 30 && repayAmount <= 50){
								return '<div style="background-color:RGB(11, 192, 125,0.7)">' + repayAmount + '</div>';
							}else if(repayAmount > 50){
								return '<div style="background-color:RGB(11, 192, 125,1)">' + repayAmount + '</div>';
							}
						}}
						,{field:'order_due_repay_pd_31_60', title: fieldArr['31_60_days'],templet:function(d){//逾期还款笔数(31-60天)
							var repayAmount = d.order_due_repay_pd_31_60;
							if(repayAmount == 0){
								return '<div style="background-color:RGB(11, 192, 125,0)">' + repayAmount + '</div>';
							}else if(repayAmount > 0 && repayAmount <= 10){
								return '<div style="background-color:RGB(11, 192, 125,0.2)">' + repayAmount + '</div>';
							}else if(repayAmount > 10 && repayAmount <= 30){
								return '<div style="background-color:RGB(11, 192, 125,0.4)">' + repayAmount + '</div>';
							}else if(repayAmount > 30 && repayAmount <= 50){
								return '<div style="background-color:RGB(11, 192, 125,0.7)">' + repayAmount + '</div>';
							}else if(repayAmount > 50){
								return '<div style="background-color:RGB(11, 192, 125,1)">' + repayAmount + '</div>';
							}
						}}
						,{field:'order_due_repay_pd_61', title: fieldArr['over_60_days'],templet:function(d){//逾期还款笔数(60+天)
							var repayAmount = d.order_due_repay_pd_61;
							if(repayAmount == 0){
								return '<div style="background-color:RGB(11, 192, 125,0)">' + repayAmount + '</div>';
							}else if(repayAmount > 0 && repayAmount <= 10){
								return '<div style="background-color:RGB(11, 192, 125,0.2)">' + repayAmount + '</div>';
							}else if(repayAmount > 10 && repayAmount <= 30){
								return '<div style="background-color:RGB(11, 192, 125,0.4)">' + repayAmount + '</div>';
							}else if(repayAmount > 30 && repayAmount <= 50){
								return '<div style="background-color:RGB(11, 192, 125,0.7)">' + repayAmount + '</div>';
							}else if(repayAmount > 50){
								return '<div style="background-color:RGB(11, 192, 125,1)">' + repayAmount + '</div>';
							}
						}}
						,{field:'order_due_pd_1_3_rate', title: fieldArr['1_3_days'],templet:function(d){//逾期率(1-3天)
							return '<div class="layui-progress" lay-filter="pd_1_3dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_due_pd_1_3_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//d8ae61
									+ '</div>';
						}}
						,{field:'order_due_pd_4_8_rate', title: fieldArr['4_8_days'],templet:function(d){//逾期率(4-8天)
							return '<div class="layui-progress" lay-filter="pd_4_8dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_due_pd_4_8_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//d8ae61
									+ '</div>';
						}}
						,{field:'order_due_pd_9_18_rate', title: fieldArr['9_18_days'],templet:function(d){//逾期率(9-18天)
							return '<div class="layui-progress" lay-filter="pd_9_18dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_due_pd_9_18_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//d8ae61
									+ '</div>';
						}}
						,{field:'order_due_pd_19_30_rate', title: fieldArr['19_30_days'],templet:function(d){//逾期率(19-30天)
							return '<div class="layui-progress" lay-filter="pd_19_30dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_due_pd_19_30_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//d8ae61
									+ '</div>';
						}}
						,{field:'order_due_pd_31_60_rate', title: fieldArr['31_60_days'],templet:function(d){//逾期率(31-60天)
							return '<div class="layui-progress" lay-filter="pd_31_60dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_due_pd_31_60_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//d8ae61
									+ '</div>';
						}}
						,{field:'order_due_pd_61_rate', title: fieldArr['over_60_days'],templet:function(d){//逾期率(60+天)
							return '<div class="layui-progress" lay-filter="pd_over_60dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_due_pd_61_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//d8ae61
									+ '</div>';
						}}
						,{field:'order_today_repay_pd_1_3_rate', title: fieldArr['1_3_days'],templet:function(d){//催回率(1-3天)
							return '<div class="layui-progress" lay-filter="c_1_3dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_pd_1_3_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//648FC6
									+ '</div>';
						}}
						,{field:'order_today_repay_pd_4_8_rate', title: fieldArr['4_8_days'],templet:function(d){//催回率(4-8天)
							return '<div class="layui-progress" lay-filter="c_4_8dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_pd_4_8_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//648FC6
									+ '</div>';
						}}
						,{field:'order_today_repay_pd_9_18_rate', title: fieldArr['9_18_days'],templet:function(d){//催回率(9-18天)
							return '<div class="layui-progress" lay-filter="c_9_18dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_pd_9_18_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//648FC6
									+ '</div>';
						}}
						,{field:'order_today_repay_pd_19_30_rate', title: fieldArr['19_30_days'],templet:function(d){//催回率(19-30天)
							return '<div class="layui-progress" lay-filter="c_19_30dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_pd_19_30_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//648FC6
									+ '</div>';
						}}
						,{field:'order_today_repay_pd_31_60_rate', title: fieldArr['31_60_days'],templet:function(d){//催回率(31-60天)
							return '<div class="layui-progress" lay-filter="c_31_60dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_pd_31_60_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'//648FC6
									+ '</div>';
						}}
						,{field:'order_today_repay_pd_61_rate', title: fieldArr['over_60_days'],templet:function(d){//催回率(60+天)
							return '<div class="layui-progress" lay-filter="c_over_60dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_pd_61_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>' //648FC6
									+ '</div>';
						}}
						,{field:'order_today_repay_all_pd_1_3_rate', title: fieldArr['first_overdue_3_days'],templet:function(d){//首逾催回率(3日)
							return '<div class="layui-progress" lay-filter="fo_3dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_all_pd_1_3_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'
									+ '</div>';
						}}
						,{field:'order_today_repay_all_pd_1_8_rate', title: fieldArr['first_overdue_8_days'],templet:function(d){//首逾催回率(8日)
							return '<div class="layui-progress" lay-filter="fo_8dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_all_pd_1_8_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'
									+ '</div>';
						}}
						,{field:'order_today_repay_all_pd_1_18_rate', title: fieldArr['first_overdue_18_days'],templet:function(d){//首逾催回率(18日)
							return '<div class="layui-progress" lay-filter="fo_18dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_all_pd_1_18_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'
									+ '</div>';
						}}
						,{field:'order_today_repay_all_pd_1_30_rate', title: fieldArr['first_overdue_30_days'],templet:function(d){//首逾催回率(30日)
							return '<div class="layui-progress" lay-filter="fo_30dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_all_pd_1_30_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'
									+ '</div>';
						}}
						,{field:'order_today_repay_all_pd_1_60_rate', title: fieldArr['first_overdue_60_days'],templet:function(d){//首逾催回率(60日)
							return '<div class="layui-progress" lay-filter="fo_60dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_all_pd_1_60_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'
									+ '</div>';
						}}
						,{field:'order_today_repay_all_pd_61_rate', title: fieldArr['first_overdue_over_60_days'],templet:function(d){//首逾催回率(61+天)
							return '<div class="layui-progress" lay-filter="fo_over_60dr' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF">'
							+  '<span style="position:absolute;z-index:1;width:100%;right:0;">' + d.order_today_repay_all_pd_61_rate + '%</span>'
								+ '<div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #64C384 , #FFF);"></div>'
									+ '</div>';
						}}
						
					]]
					,page: false
					,done : function(res,curr,count){
						//给表头指定字体改变颜色
						$jq('.layui-table-header th[data-field="date_str"]').css('color','#FF0021');
						$jq('.layui-table-header th[data-field="order_due_repay_pd_61"]').css('color','#FF0021');
						$jq('.layui-table-header th[data-field="order_due_pd_61_rate"]').css('color','#FF0021');
						$jq('.layui-table-header th[data-field="order_today_repay_pd_61_rate"]').css('color','#FF0021');
						$jq('.layui-table-header th[data-field="order_today_repay_all_pd_61_rate"]').css('color','#FF0021');
						
						var repayAmountTotal = 0;//应还款总数
						var actualRepayAmountTotal = 0;//实际还款总数
						var firstOverdueAmountTotal = 0;//首次逾期总笔数
						var currentOverdueAmountTotal = 0;//当前逾期总笔数
						var pd1_3Total = 0;//逾期1-3天后还款总笔数
						var pd4_8Total = 0;//逾期4-8天后还款总笔数
						var pd9_18Total = 0;//逾期9-18天后还款总笔数
						var pd19_30Total = 0;//逾期19-30天后还款总笔数
						var pd31_60Total = 0;//逾期31-60天后还款总笔数
						var pd_over_60Total = 0;//逾期60+天后还款总笔数
						
						$jq.each(res.data,function(index,item){
                    		element.progress('for' + index,item.first_overdue_rate + '%');
                    		element.progress('cor' + index,item.current_overdue_rate + '%');
                    		element.progress('pd_1_3dr' + index,item.order_due_pd_1_3_rate + '%');
                    		element.progress('pd_4_8dr' + index,item.order_due_pd_4_8_rate + '%');
                    		element.progress('pd_9_18dr' + index,item.order_due_pd_9_18_rate + '%');
                    		element.progress('pd_19_30dr' + index,item.order_due_pd_19_30_rate + '%');
                    		element.progress('pd_31_60dr' + index,item.order_due_pd_31_60_rate + '%');
                    		element.progress('pd_over_60dr' + index,item.order_due_pd_61_rate + '%');
                    		element.progress('c_1_3dr' + index,item.order_today_repay_pd_1_3_rate + '%');
                    		element.progress('c_4_8dr' + index,item.order_today_repay_pd_4_8_rate + '%');
                    		element.progress('c_9_18dr' + index,item.order_today_repay_pd_9_18_rate + '%');
                    		element.progress('c_19_30dr' + index,item.order_today_repay_pd_19_30_rate + '%');
                    		element.progress('c_31_60dr' + index,item.order_today_repay_pd_31_60_rate + '%');
                    		element.progress('c_over_60dr' + index,item.order_today_repay_pd_61_rate + '%');
                    		element.progress('fo_3dr' + index,item.order_today_repay_all_pd_1_3_rate + '%');
                    		element.progress('fo_8dr' + index,item.order_today_repay_all_pd_1_8_rate + '%');
                    		element.progress('fo_18dr' + index,item.order_today_repay_all_pd_1_18_rate + '%');
                    		element.progress('fo_30dr' + index,item.order_today_repay_all_pd_1_30_rate + '%');
                    		element.progress('fo_60dr' + index,item.order_today_repay_all_pd_1_60_rate + '%');
                    		element.progress('fo_over_60dr' + index,item.order_today_repay_all_pd_61_rate + '%');
                    		
                    		repayAmountTotal += Number(item.order_pay_sum);
                    		actualRepayAmountTotal += Number(item.order_repay_sum);
                    		firstOverdueAmountTotal += Number(item.order_today_due_sum);
                    		currentOverdueAmountTotal += Number(item.order_due_sum);
                    		pd1_3Total += Number(item.order_due_repay_pd_1_3);
                    		pd4_8Total += Number(item.order_due_repay_pd_4_8);
                    		pd9_18Total += Number(item.order_due_repay_pd_9_18);
                    		pd19_30Total += Number(item.order_due_repay_pd_19_30);
                    		pd31_60Total += Number(item.order_due_repay_pd_31_60);
                    		pd_over_60Total += Number(item.order_due_repay_pd_61);
                    	});
						
						if(count > 0){
							//增加总和
							var bodyHtml = '<tr style="height:28px;background-color:#EDFBF6;font-weight: bold;">'
											+ '<td style="text-align: center;">' + language.get('sum_or_total') + '</td>'//求和(总计)
											+ '<td>' + repayAmountTotal + '</td>'
											+ '<td>' + actualRepayAmountTotal + '</td>'
											+ '<td>' + firstOverdueAmountTotal + '</td>'
											+ '<td>' + currentOverdueAmountTotal + '</td>'
											+ '<td>-</td>'
											+ '<td>-</td>'
											+ '<td>' + pd1_3Total + '</td>'
											+ '<td>' + pd4_8Total + '</td>'
											+ '<td>' + pd9_18Total + '</td>'
											+ '<td>' + pd19_30Total + '</td>'
											+ '<td>' + pd31_60Total + '</td>'
											+ '<td>' + pd_over_60Total + '</td>'
											+ '<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>'
											+ '</tr>';
							$jq('.layui-table-body tbody').append(bodyHtml);
						}
						
		                var allTableHead = $jq('.layui-table-cell span');//所有表头
		                allTableHead.each(function(index,item){
		                	item.parentElement.title = item.textContent;
		                })
					}
				});
				
				//分页
				var canFlush = false;
				laypage.render({
					elem: 'dueReportPage'
					,count: data.page.count
					,curr: data.page.page
					,prev: '<em><</em>'
					,next: '<em>></em>'
					,limit: data.page.limit
					,limits:[20, 50, 100]
					,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
					,jump: function(obj){
						if(canFlush) {
							layui.dueReport.getData(obj.curr,obj.limit);
						}else{
							canFlush = true;
						}
					}
				});
			}
			/**
			 * 处理监听事件
			 */
			,tool : function(){
				//表单下拉框监听事件（公司名称）
                layui.form.on('select(company_id)', function(data){
                    layui.dueReport.getData(1,50);
                });
			}
	
	}
	
	exports('dueReport', obj);
	
})