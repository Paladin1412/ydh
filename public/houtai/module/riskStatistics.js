layui.define(['jquery','language','form','laydate'],function (exports) {
	
	var $jq = layui.jquery;
	var language = layui.language;
	var form = layui.form;
	var laydate = layui.laydate;
	
    var obj = {
        initView: function () {
        	//初始化角色
        	this.initRole();
        	language.render('page_risk_statistical');
        	//初始化报表
        	this.initRiskEcharts();
        	
        	//初始化时间
        	this.initDate();
        }
    	,initRiskEcharts : function(sendData){
    		var chartIds = {};
        	//风控统计
    		//跑分占比(相当于风控占比)
        	var riskPassChart = echarts.init(document.getElementById('riskPassRatio'));
        	chartIds['riskPassChart'] = riskPassChart;
        	//渠道跑分占比(相当于渠道风控通过占比)
        	var channelRiskPassChart = echarts.init(document.getElementById('channelRiskPassRatio'));
        	chartIds['channelRiskPassChart'] = channelRiskPassChart;
        	//信审通过量占比
        	var approvalPassChart = echarts.init(document.getElementById('approvalPassRatio'));
        	chartIds['approvalPassChart'] = approvalPassChart;
        	//渠道信审通过量占比
        	var channelApprovalPassChart = echarts.init(document.getElementById('channelApprovalPassRatio'));
        	chartIds['channelApprovalPassChart'] = channelApprovalPassChart;
        	var sendDatas = {};//定义发送数据包
        	if(!sendData){
    			sendDatas['date'] = $jq('#date').val();
    			sendDatas['company_id'] = $jq('#company_id').val();
        	}else{
        		sendDatas = sendData;
        	}
        	layui.riskStatistics.initEcharts(sendDatas,chartIds);
        	
			window.addEventListener("resize", function () {
				riskPassChart.resize();
				channelRiskPassChart.resize();
				approvalPassChart.resize();
				channelApprovalPassChart.resize();
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
					layui.riskStatistics.initRiskEcharts(sendData);
				}
			})
		}
		,initRole : function(){
	    	layui.use('verify_common',function(){
	    		layui.verify_common.initRole();
	    	});
		}
		//查询
		,search : function(){
			var sendData = {};
			sendData.company_id = $jq('#company_id').val();
			sendData.date = $jq('#date').val();
			layui.riskStatistics.initRiskEcharts(sendData);
		}
		,initEcharts : function(sendDatas,chartIds){
			//风控统计
			$jq.post(basePath + 'admin/echart/get_risk_data',sendDatas,function(data){
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
	    			$jq('#riskStatistics').hide();
	    			return;
	    		}
	    		if(!ajaxCall(d)){
	    			return;
	    		}
	    		
	    		
	    		//跑分占比(相当于风控占比)
	    		layui.riskStatistics.initReport(d.data.risk_pass_ratio_tag,d.data.risk_pass_ratio.list,chartIds.riskPassChart);
	    		var riskPassAmount = 0;
	    		var riskPassAmountHtml = '';
	    		$jq.each(d.data.risk_pass_ratio.list,function(index,item){
	    			riskPassAmount += Number(item.value);
	    			// riskPassAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';

	    		});
	    		riskPassAmountHtml += '<span>' + language.get('all_apply_amount') + ' : ' + riskPassAmount + '</span>';
	    		$jq('#riskPassRatioSpan').html(riskPassAmountHtml);
	    		//渠道跑分占比(相当于渠道风控通过占比)
	    		layui.riskStatistics.initReport(d.data.channel_risk_pass_ratio_tag,d.data.channel_risk_pass_ratio.list,chartIds.channelRiskPassChart);
	    		var channelRiskPassAmount = 0;
	    		var channelRiskPassAmountHtml = '';
	    		$jq.each(d.data.channel_risk_pass_ratio.list,function(index,item){
	    			channelRiskPassAmount += Number(item.value);
	    			// channelRiskPassAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';

	    		});
	    		channelRiskPassAmountHtml += '<span>' + language.get('risk_pass_amount') + ' : ' + channelRiskPassAmount + '</span>';
	    		$jq('#channelRiskPassRatioSpan').html(channelRiskPassAmountHtml);
	    		//信审通过量占比
	    		layui.riskStatistics.initReport(d.data.approval_pass_ratio_tag,d.data.approval_pass_ratio.list,chartIds.approvalPassChart);
	    		var approvalPassAmount = 0;
	    		var approvalPassAmountHtml = '';
	    		$jq.each(d.data.approval_pass_ratio.list,function(index,item){
	    			approvalPassAmount += Number(item.value);
	    			// approvalPassAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';
	    		});
	    		approvalPassAmountHtml += '<span>' + language.get('risk_pass_amount') + ' : ' + approvalPassAmount + '</span>';
	    		$jq('#approvalPassRatioSpan').html(approvalPassAmountHtml);
	    		//渠道信审通过量占比
	    		layui.riskStatistics.initReport(d.data.channel_approval_pass_ratio_tag,d.data.channel_approval_pass_ratio.list,chartIds.channelApprovalPassChart);
	    		var channelApprovalPassAmount = 0;
	    		var channelApprovalPassAmountHtml = '';
	    		$jq.each(d.data.channel_approval_pass_ratio.list,function(index,item){
	    			channelApprovalPassAmount += Number(item.value);
	    			// channelApprovalPassAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';
	    		});
	    		channelApprovalPassAmountHtml += '<span>' + language.get('approval_pass_amount') + ' : ' + channelApprovalPassAmount + '</span>';
	    		$jq('#channelApprovalPassRatioSpan').html(channelApprovalPassAmountHtml);
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
    			        // formatter: "{a} <br/>{b} : {c} ({d}%)"
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
    			            radius : '45%',
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
    exports('riskStatistics', obj);
});  


