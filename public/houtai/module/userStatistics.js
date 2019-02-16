layui.define(['jquery','language','laydate','form'],function (exports) {
	
	var $jq = layui.jquery;
	var language = layui.language;
	var laydate = layui.laydate;
	var form = layui.form;
	
    var obj = {
        initView: function () {
        	//初始化角色
        	this.initRole();
        	language.render('y-page-user');
        	//初始化报表
        	this.initEcharts();
        	//初始化按钮的css
    		layui.userStatistics.initBtnCss();
        }
    	,initRole : function(){
        	layui.use('verify_common',function(){
        		layui.verify_common.initRole();
        	});
    	}
    	,initEcharts : function(){
        	
        	var company_id = $jq('#company_id').val();
        	//用户统计
        	var userCountChart = echarts.init(document.getElementById('userCount'));
        	layui.userStatistics.initUserCountChart(userCountChart,'1','#userCountDiv','',company_id);
        	//金额统计
        	var moneyCountChart = echarts.init(document.getElementById('moneyCount'));
        	layui.userStatistics.initMoneyCountChart(moneyCountChart,'1','#moneyCountDiv','',company_id);
        	//通过率统计
        	var passCountChart = echarts.init(document.getElementById('passCount'));
        	layui.userStatistics.initPassCountChart(passCountChart,'1','#passCountDiv','',company_id);
        	//转化率统计
        	var oneDayCountChart = echarts.init(document.getElementById('oneDayCount'));
        	layui.userStatistics.initOneDayCountChart(oneDayCountChart,'1','#oneDayCountDiv','',company_id);
        	
        	//初始化时间
        	layui.userStatistics.initDate();
    	}
    
    	,initDate : function(){
    		laydate.render({
    			elem: '#userCountTime'
	     	    ,range: true
	     	    ,min: '2018-01-01'
	     	    ,max: '2100-12-31'
	    		,done : function(value){
	    			layui.userStatistics.initUserCount('4',value);
	    		}
    		});
    		laydate.render({
    			elem: '#moneyCountTime'
	     	    ,range: true
	     	    ,min: '2018-01-01'
	     	    ,max: '2100-12-31'
	    		,done : function(value){
	    			layui.userStatistics.initMoneyCount('4',value);
	    		}
    		});
    		laydate.render({
    			elem: '#passCountTime'
	     	    ,range: true
	     	    ,min: '2018-01-01'
	     	    ,max: '2100-12-31'
	    		,done : function(value){
	    			layui.userStatistics.initPassCount('4',value);
	    		}
    		});
    		laydate.render({
    			elem: '#oneDayCountTime'
	     	    ,range: true
	     	    ,min: '2018-01-01'
	     	    ,max: '2100-12-31'
	    		,done : function(value){
	    			layui.userStatistics.initOneDayCount('4',value);
	    		}
    		})
    	}
    	,initOneDayCount : function(type,date){
    		if(type == '1'){
    			selectThisWeek('.oneday-count-btn-left','.oneday-count-btn-left span','.oneday-count-btn-right','.oneday-count-btn-right span');
    		}else{
    			selectThisMonth('.oneday-count-btn-left','.oneday-count-btn-left span','.oneday-count-btn-right','.oneday-count-btn-right span');
    		}
    		var company_id = $jq('#company_id').val();
        	var oneDayCountChart = echarts.init(document.getElementById('oneDayCount'));
        	this.initOneDayCountChart(oneDayCountChart,type,'',date,company_id);
    	}
    	//转化率统计
    	,initOneDayCountChart : function(dataCharts,type,chartId,date,company_id){
    		$jq.post(basePath + 'admin/Echart/one_day_count_data',{'type':type,'date':date,'company_id':company_id},function(data){
    			var d = null;
        		if( data.code) {
        			d = data;
        		}else {
        			try {
        				d = JSON.parse(data);
        			}catch(e){
        				d = {};
        			}
        		}
        		if(d.code == '500'){
        			$jq(chartId).hide();
        			return;
        		}
    			if(!ajaxCall(d)){
    				return;
    			}
    			var field = d.data.field;
    			var jiekuan_rate = [];
    			var dates = [];
    			var toSeries = [];
    			var toData = {};
    			toData['jiekuan_rate'] = jiekuan_rate;
    			var legend = [field.jiekuan_rate];
    			var formData = ['jiekuan_rate'];
    			$jq.each(d.data.data_list,function(i,item){
    				jiekuan_rate.push(item.jiekuan_rate);
    				dates.push(item.date_str);
    			});
    			for(var i = 0;i < formData.length;i++){
    				var cData = {
    						name:legend[i],
    		                type:'line',
    		                data:toData[formData[i]],
    		                symbol:'circle',
    		                symbolSize:8,
    		                areaStyle: {
    		                	normal: {
        		                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
        		                        offset: 0,
        		                        color: '#76d8e7'
        		                    }, {
        		                        offset: 1,
        		                        color: '#FFFFFF'
        		                    }])
        		                }
    		                }
    				}
    				toSeries.push(cData);
    			}
    			var option = {
    					color:['#56acba'],
//    	    			title: {
//    	    				text: title,
//    	    				top:10,
//    	    				textStyle: {
//    	    					color: '#464646',
//    	    					fontWeight: 'lighter',
//    	    					fontFamily: 'MicrosoftJhengHei',
//    	    					fontSize: 20
//    	    				}
//    	    			},
    	    			tooltip : {
    			            trigger: 'axis',
    			        },
    			        legend : {
    						orient : 'horizontal',
    						data : legend,
    						bottom : 10,
    						x : 'center',
							 itemWidth: 10,
							 itemHeight: 10,
							 itemGap: 10
    					},
    					grid: {
    						top:'10%',
    			            left: '3%',
    			            right: '7%',
    			            bottom: '15%',
    			            containLabel: true
    			        },
    			        xAxis : [
    			            {
    			                type : 'category',
    			                data : dates,
    			                boundaryGap : false,
    			                axisTick: {
    			                    alignWithLabel: true
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
    			        series : toSeries
    			};
    			dataCharts.setOption(option,true);

    		})
    	}
    	,initPassCount : function(type,date){
    		if(type == '1'){
    			selectThisWeek('.pass-count-btn-left','.pass-count-btn-left span','.pass-count-btn-right','.pass-count-btn-right span');
    		}else{
    			selectThisMonth('.pass-count-btn-left','.pass-count-btn-left span','.pass-count-btn-right','.pass-count-btn-right span');
    		}
    		var company_id = $jq('#company_id').val();
        	var passCountChart = echarts.init(document.getElementById('passCount'));
        	this.initPassCountChart(passCountChart,type,'',date,company_id);
    	}
    	//通过率统计
    	,initPassCountChart : function(dataCharts,type,chartId,date,company_id){
    		$jq.post(basePath + 'admin/Echart/pass_count_data',{'type':type,'date':date,'company_id':company_id},function(data){
    			var d = null;
        		if( data.code) {
        			d = data;
        		}else {
        			try {
        				d = JSON.parse(data);
        			}catch(e){
        				d = {};
        			}
        		}
        		if(d.code == '500'){
        			$jq(chartId).hide();
        			return;
        		}
    			if(!ajaxCall(d)){
    				return;
    			}
    			var field = d.data.field;
    			var risk_rate = [];
    			var xinshen_rate = [];
    			var all_rate = [];
    			var dates = [];
    			var toSeries = [];
    			var toData = {};
    			toData['risk_rate'] = risk_rate;
    			toData['xinshen_rate'] = xinshen_rate;
    			toData['all_rate'] = all_rate;
    			var legend = [field.risk_rate,field.xinshen_rate,field.all_rate];
    			var formData = ['risk_rate','xinshen_rate','all_rate'];
    			$jq.each(d.data.data_list,function(i,item){
    				risk_rate.push(item.risk_rate);
    				xinshen_rate.push(item.xinshen_rate);
    				all_rate.push(item.all_rate);
    				dates.push(item.date_str);
    			});
    			for(var i = 0;i < formData.length;i++){
    				var cData = {
    						name:legend[i],
    		                type:'line',
    		                data:toData[formData[i]],
    		                symbol:'circle',
    		                symbolSize:8
    				}
    				toSeries.push(cData);
    			}
    			var option = {
    					color:['#a1e9d9','#59c8e2','#e2ba59','#f293ad'],
//    	    			title: {
//    	    				text: title,
//    	    				top:10,
//    	    				textStyle: {
//    	    					color: '#464646',
//    	    					fontWeight: 'lighter',
//    	    					fontFamily: 'MicrosoftJhengHei',
//    	    					fontSize: 20
//    	    				}
//    	    			},
    	    			tooltip : {
    			            trigger: 'axis',
    			        },
    			        legend : {
    						orient : 'horizontal',
    						data : legend,
    						bottom : 10,
    						x : 'center',
    						itemWidth: 10,
    						itemHeight: 10,
    						itemGap: 10
    					},
    					grid: {
    						top:'10%',
    			            left: '3%',
    			            right: '7%',
    			            bottom: '15%',
    			            containLabel: true
    			        },
    			        xAxis : [
    			            {
    			                type : 'category',
    			                data : dates,
    			                boundaryGap : false,
    			                axisTick: {
    			                    alignWithLabel: true
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
    			        series : toSeries
    			};
    			dataCharts.setOption(option,true);

    		})
    	}
    	,initMoneyCount : function(type,date){
    		if(type == '1'){
    			selectThisWeek('.money-count-btn-left','.money-count-btn-left span','.money-count-btn-right','.money-count-btn-right span');
    		}else{
    			selectThisMonth('.money-count-btn-left','.money-count-btn-left span','.money-count-btn-right','.money-count-btn-right span');
    		}
    		var company_id = $jq('#company_id').val();
        	var moneyCountChart = echarts.init(document.getElementById('moneyCount'));
        	this.initMoneyCountChart(moneyCountChart,type,'',date,company_id);
    	}
    	//金额统计
    	,initMoneyCountChart : function(dataCharts,type,chartId,date,company_id){
    		$jq.post(basePath + 'admin/Echart/money_count_data',{'type':type,'date':date,'company_id':company_id},function(data){
    			var d = null;
        		if( data.code) {
        			d = data;
        		}else {
        			try {
        				d = JSON.parse(data);
        			}catch(e){
        				d = {};
        			}
        		}
        		if(d.code == '500'){
        			$jq(chartId).hide();
        			return;
        		}
    			if(!ajaxCall(d)){
    				return;
    			}
    			var field = d.data.field;
    			var sum_amount = [];
    			var due_amount = [];
    			var death_amount = [];
    			var dates = [];
    			var toSeries = [];
    			var toData = {};
    			toData['sum_amount'] = sum_amount;
    			toData['due_amount'] = due_amount;
    			toData['death_amount'] = death_amount;
    			var legend = [field.sum_amount,field.due_amount,field.death_amount];
    			var formData = ['sum_amount','due_amount','death_amount'];
    			$jq.each(d.data.data_list,function(i,item){
    				sum_amount.push(item.sum_amount);
    				due_amount.push(item.due_amount);
    				death_amount.push(item.death_amount);
    				dates.push(item.date_str);
    			});
    			for(var i = 0;i < formData.length;i++){
    				var cData = {
    						name:legend[i],
    		                type:'line',
    		                data:toData[formData[i]],
    		                symbol:'circle',
    		                symbolSize:8
    				}
    				toSeries.push(cData);
    			}
    			var option = {
    					color:['#a1e9d9','#59c8e2','#e2ba59','#f293ad'],
//    	    			title: {
//    	    				text: title,
//    	    				top:10,
//    	    				textStyle: {
//    	    					color: '#464646',
//    	    					fontWeight: 'lighter',
//    	    					fontFamily: 'MicrosoftJhengHei',
//    	    					fontSize: 20
//    	    				}
//    	    			},
    	    			tooltip : {
    			            trigger: 'axis',
    			        },
    			        legend : {
    						orient : 'horizontal',
    						data : legend,
    						bottom : 10,
    						x : 'center',
    						itemWidth: 10,
    						itemHeight: 10,
    						itemGap: 10
    					},
    					grid: {
    						top:'10%',
    			            left: '3%',
    			            right: '7%',
    			            bottom: '15%',
    			            containLabel: true
    			        },
    			        xAxis : [
    			            {
    			                type : 'category',
    			                data : dates,
    			                boundaryGap : false,
    			                axisTick: {
    			                    alignWithLabel: true
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
    			        series : toSeries
    			};
    			dataCharts.setOption(option,true);

    		})
    	}
    	
    	
    	,initUserCount : function(type,date){
    		var userCountChart = echarts.init(document.getElementById('userCount'));
    		if(type == '1'){
    			selectThisWeek('.user-count-btn-left','.user-count-btn-left span','.user-count-btn-right','.user-count-btn-right span');
    		}else{
    			selectThisMonth('.user-count-btn-left','.user-count-btn-left span','.user-count-btn-right','.user-count-btn-right span');
    		}
    		var company_id = $jq('#company_id').val();
        	this.initUserCountChart(userCountChart,type,'',date,company_id);
    	}
    	//用户统计
    	,initUserCountChart : function(dataCharts,type,chartId,date,company_id){
    		$jq.post(basePath + 'admin/Echart/count_view',{'type':type,'date':date,'company_id':company_id},function(data){
    			var d = null;
        		if( data.code) {
        			d = data;
        		}else {
        			try {
        				d = JSON.parse(data);
        			}catch(e){
        				d = {};
        			}
        		}
        		if(d.code == '500'){
        			$jq(chartId).hide();
        			return;
        		}
    			if(!ajaxCall(d)){
    				return;
    			}
    			var field = d.data.field;
    			var order_count = [];
    			var reg_user_count = [];
    			var due_order_count = [];
    			var death_order_count = [];
    			var dates = [];
    			var toSeries = [];
    			var toData = {};
    			toData['order_count'] = order_count;
    			toData['reg_user_count'] = reg_user_count;
    			toData['due_order_count'] = due_order_count;
    			toData['death_order_count'] = death_order_count;
    			var legend = [field.order_count,field.reg_user_count,field.due_order_count,field.death_order_count];
    			var formData = ['order_count','reg_user_count','due_order_count','death_order_count'];
    			$jq.each(d.data.data_list,function(i,item){
    				order_count.push(item.order_count);
    				reg_user_count.push(item.reg_user_count);
    				due_order_count.push(item.due_order_count);
    				death_order_count.push(item.death_order_count);
    				dates.push(item.date_str);
    			});
    			for(var i = 0;i < formData.length;i++){
    				var cData = {
    						name:legend[i],
    		                type:'line',
    		                data:toData[formData[i]],
    		                symbol:'circle',
    		                symbolSize:8
    				}
    				toSeries.push(cData);
    			}
    			var option = {
    					color:['#a1e9d9','#59c8e2','#e2ba59','#f293ad'],
//    	    			title: {
//    	    				text: title,
//    	    				top:10,
//    	    				textStyle: {
//    	    					color: '#464646',
//    	    					fontWeight: 'lighter',
//    	    					fontFamily: 'MicrosoftJhengHei',
//    	    					fontSize: 20
//    	    				}
//    	    			},
    	    			tooltip : {
    			            trigger: 'axis',
    			        },
    			        legend : {
    						orient : 'horizontal',
    						data : legend,
    						bottom : 10,
    						x : 'center',
    						itemWidth: 10,
    						itemHeight: 10,
    						itemGap: 10
    					},
    					grid: {
    						top:'10%',
    			            left: '3%',
    			            right: '7%',
    			            bottom: '15%',
    			            containLabel: true
    			        },
    			        xAxis : [
    			            {
    			                type : 'category',
    			                data : dates,
    			                boundaryGap : false,
    			                axisTick: {
    			                    alignWithLabel: true
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
    			        series : toSeries
    			};
    			dataCharts.setOption(option,true);

    		})
    	},
    	//初始化按钮的css
    	initBtnCss : function(){
    		$jq('.user-count-btn-left').css('background-color','#A1EAD9');
    		$jq('.user-count-btn-left span').css('color','#FFFFFF');
    		$jq('.money-count-btn-left').css('background-color','#A1EAD9');
    		$jq('.money-count-btn-left span').css('color','#FFFFFF');
    		$jq('.pass-count-btn-left').css('background-color','#A1EAD9');
    		$jq('.pass-count-btn-left span').css('color','#FFFFFF');
    		$jq('.oneday-count-btn-left').css('background-color','#A1EAD9');
    		$jq('.oneday-count-btn-left span').css('color','#FFFFFF');
    	},
    	
    	//监听事件
    	tool : function(){
    		//监听表单公司下拉框事件
        	form.on('select(companyId)',function(data){
        		layui.userStatistics.initEcharts();
        		form.render('select');
        	});
    	}
    	

    }



    //输出test接口
    exports('userStatistics', obj);
});  


