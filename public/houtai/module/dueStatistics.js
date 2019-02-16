layui.define(['jquery','language','form','laydate'],function (exports) {
	
	var $jq = layui.jquery;
	var language = layui.language;
	var form = layui.form;
	var laydate = layui.laydate;
	
    var obj = {
        initView: function () {
        	//初始化角色
        	this.initRole();
        	language.render('page_due_statistical');
        	//初始化报表
        	this.initDueEcharts();
        	//初始化时间
        	this.initDate();
        }
    	,initDueEcharts : function(sendData){
    		var chartIds = {};
        	//风控统计
    		//入催率
        	var inCollectChart = echarts.init(document.getElementById('inCollectRatio'));
        	chartIds['inCollectChart'] = inCollectChart;
        	//渠道入催率
        	var channelInCollectChart = echarts.init(document.getElementById('channelInCollectRatio'));
        	chartIds['channelInCollectChart'] = channelInCollectChart;
        	//逾期/订单占比
        	var dueOrderChart = echarts.init(document.getElementById('dueOrderRatio'));
        	chartIds['dueOrderChart'] = dueOrderChart;
        	//逾期占比
        	var dueChart = echarts.init(document.getElementById('dueRatio'));
        	chartIds['dueChart'] = dueChart;
        	//渠道逾期占比
        	var channelDueChart = echarts.init(document.getElementById('channelDueRatio'));
        	chartIds['channelDueChart'] = channelDueChart;
        	//逾期3天占比(1-3)
        	var dueThreeDaysChart = echarts.init(document.getElementById('dueThreeDaysRatio'));
        	chartIds['dueThreeDaysChart'] = dueThreeDaysChart;
        	//逾期10天占比(4-10)
        	var dueTenDaysChart = echarts.init(document.getElementById('dueTenDaysRatio'));
        	chartIds['dueTenDaysChart'] =dueTenDaysChart;
        	//逾期15天占比(11-15)
        	var dueFifteenDaysChart = echarts.init(document.getElementById('dueFifteenDaysRatio'));
        	chartIds['dueFifteenDaysChart'] = dueFifteenDaysChart;
        	//逾期30天占比(16-30)
        	var dueThirtyDaysChart = echarts.init(document.getElementById('dueThirtyDaysRatio'));
        	chartIds['dueThirtyDaysChart'] = dueThirtyDaysChart;
        	//逾期超过30天占比(30+)
        	var dueOverThirtyDaysChart = echarts.init(document.getElementById('dueOverThirtyDaysRatio'));
        	chartIds['dueOverThirtyDaysChart'] = dueOverThirtyDaysChart;
        	var sendDatas = {};//定义发送数据包
        	if(!sendData){
        		sendDatas['date'] = $jq('#date').val();
    			sendDatas['company_id'] = $jq('#company_id').val();
        	}else{
        		sendDatas = sendData;
        	}
        	layui.dueStatistics.initEcharts(sendDatas,chartIds);
			window.addEventListener("resize", function () {
				inCollectChart.resize();
				channelInCollectChart.resize();
				dueOrderChart.resize();
				dueChart.resize();
				channelDueChart.resize();
				dueThreeDaysChart.resize();
				dueTenDaysChart.resize();
				dueFifteenDaysChart.resize();
				dueThirtyDaysChart.resize();
				dueOverThirtyDaysChart.resize();
			});
    	}
		,initRole : function(){
	    	layui.use('verify_common',function(){
	    		layui.verify_common.initRole();
	    	});
		}
    	/*
		 * 初始化搜索框中的时间
		 */
		,initDate : function(){
			laydate.render({
				elem : '#date',
				range : true,
				done : function(value){
					var sendData = {};
					sendData.company_id = $jq('#company_id').val();
					sendData.date = value;
					layui.dueStatistics.initDueEcharts(sendData);
				}
			})
		}
		//查询
		,search : function(){
			var sendData = {};
			sendDatas['date'] = $jq('#date').val();
			sendData.company_id = $jq('#company_id').val();
			layui.dueStatistics.initEcharts(sendData);
		}
		,initEcharts : function(sendDatas,chartIds){
			//风控统计
			$jq.post(basePath + 'admin/echart/get_due_data',sendDatas,function(data){
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
	    			$jq('#dueStatistics').hide();
	    			return;
	    		}
	    		if(!ajaxCall(d)){
	    			return;
	    		}

	    		//入催率
	    		layui.dueStatistics.initReport(d.data.in_collect_ratio_tag,d.data.in_collect_ratio.list,chartIds.inCollectChart);
	    		var inCollectAmount = 0;
	    		var inCollectAmountHtml = "";
	    		$jq.each(d.data.in_collect_ratio.list,function(index,item){
	    			inCollectAmount += Number(item.value);
                    // inCollectAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
	    		 inCollectAmountHtml +='<span>' + language.get('repayment_amount') + ':' + inCollectAmount + '</span>'
	    		$jq('#inCollectRatioSpan').html(inCollectAmountHtml);

	    		//渠道入催率
	    		layui.dueStatistics.initReport(d.data.channel_in_collect_pass_ratio_tag,d.data.channel_in_collect_pass_ratio.list,chartIds.channelInCollectChart);
	    		var channelInCollectAmount = 0;
	    		var channelInCollectAmountHtml = "";

	    		$jq.each(d.data.channel_in_collect_pass_ratio.list,function(index,item){
	    			channelInCollectAmount += Number(item.value);
	    			// channelInCollectAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                channelInCollectAmountHtml += '<span>' + language.get('first_due_amount') + ':' + channelInCollectAmount + '</span>'
	    		$jq('#channelInCollectRatioSpan').html(channelInCollectAmountHtml);

	    		//逾期/订单占比
	    		layui.dueStatistics.initReport(d.data.due_order_ratio_tag,d.data.due_order_ratio.list,chartIds.dueOrderChart);
	    		var dueOrderAmount = 0;
	    		var dueOrderAmountHtml = "";
	    		$jq.each(d.data.due_order_ratio.list,function(index,item){
	    			dueOrderAmount += Number(item.value);
                    // dueOrderAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                dueOrderAmountHtml += '<span>' + language.get('loan_order_amount') + ':' + dueOrderAmount + '</span>'
	    		$jq('#dueOrderRatioSpan').html(dueOrderAmountHtml);

	    		//逾期占比
	    		layui.dueStatistics.initReport(d.data.due_ratio_tag,d.data.due_ratio.list,chartIds.dueChart);
	    		var dueAmount = 0;
	    		var dueAmountHtml = "";
	    		$jq.each(d.data.due_ratio.list,function(index,item){
	    			dueAmount += Number(item.value);
                    // dueAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                dueAmountHtml +='<span>' + language.get('due_amount') + ':' + dueAmount + '</span>';
	    		$jq('#dueRatioSpan').html(dueAmountHtml);

	    		//渠道逾期占比
	    		layui.dueStatistics.initReport(d.data.channel_due_ratio_tag,d.data.channel_due_ratio.list,chartIds.channelDueChart);
	    		var channelDueAmount = 0;
	    		var channelDueAmountHtml = "";
	    		$jq.each(d.data.channel_due_ratio.list,function(index,item){
	    			channelDueAmount += Number(item.value);
                    // channelDueAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                channelDueAmountHtml +='<br/><span>' + language.get('due_amount') + ':' + channelDueAmount + '</span>'
	    		$jq('#channelDueRatioSpan').html(channelDueAmountHtml);

	    		//逾期3天占比(1-3)
	    		layui.dueStatistics.initReport(d.data.due_three_days_ratio_tag,d.data.due_three_days_ratio.list,chartIds.dueThreeDaysChart);
	    		var dueThreeDaysAmount = 0;
	    		var dueThreeDaysAmountHtml = "";
	    		$jq.each(d.data.due_three_days_ratio.list,function(index,item){
	    			dueThreeDaysAmount += Number(item.value);
                    // dueThreeDaysAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                dueThreeDaysAmountHtml += '<span>' + language.get('due_three_days_order_amount') + ':' + dueThreeDaysAmount + '</span>'
	    		$jq('#dueThreeDaysRatioSpan').html(dueThreeDaysAmountHtml);

	    		//逾期10天占比(4-10)
	    		layui.dueStatistics.initReport(d.data.due_ten_days_ratio_tag,d.data.due_ten_days_ratio.list,chartIds.dueTenDaysChart);
	    		var dueTenDaysAmount = 0;
	    		var dueTenDaysAmountHtml = "";
	    		$jq.each(d.data.due_ten_days_ratio.list,function(index,item){
	    			dueTenDaysAmount += Number(item.value);
                    // dueTenDaysAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                dueTenDaysAmountHtml += '<span>' + language.get('due_ten_days_order_amount') + ':' + dueTenDaysAmount + '</span>'
	    		$jq('#dueTenDaysRatioSpan').html(dueTenDaysAmountHtml);

	    		//逾期15天占比(11-15)
	    		layui.dueStatistics.initReport(d.data.due_fifteen_days_ratio_tag,d.data.due_fifteen_days_ratio.list,chartIds.dueFifteenDaysChart);
	    		var dueFifteenDaysAmount = 0;
	    		var dueFifteenDaysAmountHtml = "";
	    		$jq.each(d.data.due_fifteen_days_ratio.list,function(index,item){
	    			dueFifteenDaysAmount += Number(item.value);
                    // dueFifteenDaysAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                dueFifteenDaysAmountHtml +='<span>' + language.get('due_fifteen_days_order_amount') + ':' + dueFifteenDaysAmount + '</span>';
	    		$jq('#dueFifteenDaysRatioSpan').html(dueFifteenDaysAmountHtml);

	    		//逾期30天占比(16-30)
	    		layui.dueStatistics.initReport(d.data.due_thirty_days_ratio_tag,d.data.due_thirty_days_ratio.list,chartIds.dueThirtyDaysChart);
	    		var dueThirtyDaysAmount = 0;
	    		var dueThirtyDaysAmountHtml = "";
	    		$jq.each(d.data.due_thirty_days_ratio.list,function(index,item){
	    			dueThirtyDaysAmount += Number(item.value);
                    // dueThirtyDaysAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
                dueThirtyDaysAmountHtml += '<span>' + language.get('due_thirty_days_order_amount') + ':' + dueThirtyDaysAmount + '</span>';
	    		$jq('#dueThirtyDaysRatioSpan').html(dueThirtyDaysAmountHtml);

	    		//逾期超过30天占比(30+)
	    		layui.dueStatistics.initReport(d.data.due_over_thirty_days_ratio_tag,d.data.due_over_thirty_days_ratio.list,chartIds.dueOverThirtyDaysChart);
	    		var dueOverThirtyDaysAmount = 0;
	    		var dueOverThirtyDaysAmountHtml = "";
	    		$jq.each(d.data.due_over_thirty_days_ratio.list,function(index,item){
	    			dueOverThirtyDaysAmount += Number(item.value);
                    // dueOverThirtyDaysAmountHtml += '<span>'+ item.name + ':' + item.value + '</span><br/>';
	    		});
	    		dueOverThirtyDaysAmountHtml += '<span>' + language.get('due_over_thirty_days_order_amount') + ':' + dueOverThirtyDaysAmount + '</span>';
	    		$jq('#dueOverThirtyDaysRatioSpan').html(dueOverThirtyDaysAmountHtml);
			})
		}
		
        //初始化报表
		,initReport : function(title,data,chartId){
    		var legend = [];
    		$jq.each(data,function(i,item){
                // legend.push(item.name);
               var main =item.name +" : " +item.value;
    			legend.push(main);
                item.name = main;
    		})

    		var option = {
					color : ['#a1e9d9','#59c8e2','#bce7dd','#a1e9d9','#07dcb3','#2ccebb','#4ac2c2','#60a2b0','#73b2cb','#88aacf'
									,'#9eb1e3','#a69ed6','#b897da','#9e8bc9','#c3ccd1','#a4a4a4','#baa39b','#6f7074','#5e717c','#2e4553'],
    				title : {
    			        text: title,
    			        x:'center',
    			        textStyle: {
	    					color: '#464646',
	    					fontWeight: 'lighter',
	    					fontFamily: 'MicrosoftJhengHei',
	    					fontSize: 20
	    				}
    			    },
    			    tooltip : {
    			        trigger: 'item',
    			        // formatter: "{a} <br/>{b} : {c} ({d}%)"  //由于标题已经放入数值  所以不需要显示
    			        // formatter: "{a} <br/>{b} ({d}%)"
    			        // formatter: "{d}% ({b})"
    			        // formatter: "{b} ({d}%)"
    			        // formatter: "({b}) {d}%"
    			        formatter: " {d}%  ({c})"
    			    },
    			    legend: {
    			    	orient: 'vertical',
    			        left: 'left',
    			        top: 50,
    			        data: legend
    			    },
    			    series : [
    			        {
    			            name: language.get('ratio'),
    			            type: 'pie',
    			            // radius : '45%',
    			            radius : '46%',
    			            center: ['55%', '50%'],
    			            data: data,
                            itemStyle:{
                                normal:{
                                    label:{
                                        show: true,
                                        // formatter: '{b} : {c} ({d}%)'
                                        formatter: '{d}%'
                                    },
                                    labelLine :{show:true}
                                }
                            }
                        }
    			    ]
        		}
    		chartId.setOption(option, true);
        }
    }



    //输出test接口
    exports('dueStatistics', obj);
});  


