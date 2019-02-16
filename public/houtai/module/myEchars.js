layui.define(function(exports) {

	var obj = {
		initView : function() {
			obj.initForm();
		}

		// 初始化页面 start
		,initForm : function() {
			
			$jq = layui.jquery;
			layui.laydate.render({
		   	    elem: '#date2'
		   	    ,range: true
		   	    ,min: '2018-01-01'
		   	    ,max: '2100-12-31'
	   	    	,done: function(value, date, endDate){
	   	    	    if(!value) return;
	   	    	    layui.myEchars.initCuiDingDanZouShi();
	   	    	  }
		   	  });
			
			layui.laydate.render({
		   	    elem: '#date3'
		   	    ,range: true
		   	    ,min: '2018-01-01'
		   	    ,max: '2100-12-31'
	   	    	,done: function(value, date, endDate){
	   	    	    if(!value) return;
	   	    	    layui.myEchars.initFeiYongJianMian();
	   	    	  }
		   	  });
			
			$jq.post(basePath + 'admin/Assignment/collector_view', null, function(_data) {
				if (!ajaxCall(_data)) {
					return;
				}
				layui.myEchars.cuiShouKaoHe(_data.data);
			});
			
			
			obj.initCuiDingDanZouShi();
			obj.initFeiYongJianMian();
			
			
		} // 初始化页面 end
		,initCuiDingDanZouShi:function() {
			$jq = layui.jquery;
			var sendData1 = {};
			if($jq('#date2').val()){
				sendData1.date = $jq('#date2').val();
			}
			$jq.post(basePath + 'admin/Assignment/order_view', sendData1, function(_data) {
				if (!ajaxCall(_data)) {
					return;
				}
				layui.myEchars.cuiDingDanZouShi(_data.data);
			});
		}
		,initFeiYongJianMian:function() {
			$jq = layui.jquery;
			var sendData2 = {};
			if($jq('#date3').val()){
				sendData1.date = $jq('#date3').val();
			}
			$jq.post(basePath + 'admin/Assignment/reduction_view', sendData2, function(_data) {
				if (!ajaxCall(_data)) {
					return;
				}
				layui.myEchars.feiYongJianMian(_data.data);
			});
		}
		,cuiShouKaoHe:function(_data){
			var list = null;
			if(_data.list&&_data.list.length>0){
				list = _data.list;
			}else {
				list = [];
			}
			var title = _data.field.title;
			var allOrder_Field = _data.field.order_count;
			var overOrder_Field = _data.field.collection_count;
			
			var allOrderArr=[];
			var overOrderArr=[];
			var userArr = [];
			for(var index in list){
				allOrderArr[index] =  list[index].has_case;
				overOrderArr[index] = list[index].over_case;
				userArr[index] = list[index].real_name;
			}
			
			var posList = [
			    'left', 'right', 'top', 'bottom',
			    'inside',
			    'insideTop', 'insideLeft', 'insideRight', 'insideBottom',
			    'insideTopLeft', 'insideTopRight', 'insideBottomLeft', 'insideBottomRight'
			];
			var app = {};
			app.configParameters = {
			    rotate: {
			        min: -90,
			        max: 90
			    },
			    align: {
			        options: {
			            left: 'left',
			            center: 'center',
			            right: 'right'
			        }
			    },
			    verticalAlign: {
			        options: {
			            top: 'top',
			            middle: 'middle',
			            bottom: 'bottom'
			        }
			    },
			    position: {
			        options: echarts.util.reduce(posList, function (map, pos) {
			            map[pos] = pos;
			            return map;
			        }, {})
			    },
			    distance: {
			        min: 0,
			        max: 100
			    }
			};

			app.config = {
			    rotate: 90,
			    align: 'left',
			    verticalAlign: 'middle',
			    position: 'insideBottom',
			    distance: 15,
			    onChange: function () {
			        var labelOption = {
			            normal: {
			                rotate: app.config.rotate,
			                align: app.config.align,
			                verticalAlign: app.config.verticalAlign,
			                position: app.config.position,
			                distance: app.config.distance
			            }
			        };
			        myChart.setOption({
			            series: [{
			                label: labelOption
			            }, {
			                label: labelOption
			            }]
			        });
			    }
			};


			var labelOption = {
			    normal: {
			        show: true,
			        position: app.config.position,
			        distance: app.config.distance,
			        align: app.config.align,
			        verticalAlign: app.config.verticalAlign,
			        rotate: app.config.rotate,
			        formatter: '{c}  {name|{a}}',
			        fontSize: 16,
			        rich: {
			            name: {
			                textBorderColor: '#fff'
			            }
			        }
			    }
			};

		var	option = {
			    color: ['#58c9db', '#9bead7'],
			    title: {
                text: title
                },
			    tooltip: {
			        trigger: 'axis',
			        axisPointer: {
			            type: 'shadow'
			        }
			    },
			    legend: {
			    	right:'0px',
			        data: [allOrder_Field, overOrder_Field]//'全部订单', '已完成订单'
			    },
			    toolbox: {
			        show: false,
			        orient: 'vertical',
			        left: 'right',
			        top: 'center',
			        feature: {
			            // mark: {show: true},
			            dataView: {show: true, readOnly: false},
			            magicType: {show: true, type: ['line', 'bar']},// , 'stack', 'tiled'
			          //  restore: {show: true},
			           // saveAsImage: {show: true}
			        }
			    },
			    calculable: true,
			    xAxis: [
			        {
			            type: 'category',
			            axisTick: {show: false},
			            data: userArr
			        }
			    ],
			    yAxis: [
			        {
			            type: 'value'
			        }
			    ],
			    series: [
			        {
			            name: allOrder_Field,
			            type: 'bar',
			            barGap: 0,
			           // label: labelOption,
			            data: allOrderArr
			        },
			        {
			            name: overOrder_Field,
			            type: 'bar',
			         //   label: labelOption,
			            data: overOrderArr
			        }
			    ]
			};
			 var myChart = echarts.init(document.getElementById('cuiShouKaoHe'));
		     myChart.setOption(option);
		}
		,cuiDingDanZouShi:function(_data) {
			var list = null;
			if(_data.list&&_data.list.length>0){
				list = _data.list;
			}else {
				list = [];
			}
			var title = _data.field.title;
			var allOrder_Field = _data.field.order_count;
			var realOrder_Field = _data.field.collection_count;
			var allOrderArr=[];
			var realOrderArr=[];
			var dateArr = [];
			for(var index in list){
				allOrderArr[index] =  list[index].order_count;
				realOrderArr[index] = list[index].collection_count;
				dateArr[index] = list[index].date;
			}
			option = {
				    title: {
				        text: title
				    },
				    color: ['#58c9db', '#9bead7'],
				    tooltip: {
				        trigger: 'axis'
				    },
				    legend: {
				    	right:'0px',
				        data:[allOrder_Field,realOrder_Field]
				    },
				    grid: {
				        left: '3%',
				        right: '4%',
				        bottom: '3%',
				        containLabel: true
				    },
				    
				    xAxis: {
				        type: 'category',
				        boundaryGap: false,
				        data: dateArr
				    },
				    yAxis: {
				        type: 'value'
				    },
				    series: [
				        {
				            name:allOrder_Field,
				            type:'line',
				            stack: '总量',
				            data:allOrderArr
				        },
				        {
				            name:realOrder_Field,
				            type:'line',
				            stack: '总量',
				            data:realOrderArr
				        }
				    ]
				};
			 var myChart = echarts.init(document.getElementById('cuiDingDanZouShi'));
		     myChart.setOption(option);
			
		},
		
		feiYongJianMian:function(_data) {
			var list = null;
			if(_data.list&&_data.list.length>0){
				list = _data.list;
			}else {
				list = [];
			}
			var title = _data.field.title;
			var allFee_Field = title;
			var allFeeArr=[];
			var dateArr = [];
			for(var index in list){
				allFeeArr[index] =  list[index].all_fee;
				dateArr[index] = list[index].date;
			}
			option = {
				    title: {
				        text: title
				    },
				    color: ['#c98dc1'],
				    tooltip: {
				        trigger: 'axis'
				    },
				    legend: {
				    	right:'0px',
				        data:allFeeArr
				    },
				    grid: {
				        left: '3%',
				        right: '4%',
				        bottom: '3%',
				        containLabel: true
				    },
				    
				    xAxis: {
				        type: 'category',
				        boundaryGap: false,
				        data: dateArr
				    },
				    yAxis: {
				        type: 'value'
				    },
				    series: [
				        {
				           // name:allFee_Field,
				            type:'line',
				            data:allFeeArr
				        }
				    ]
				};
			 var myChart = echarts.init(document.getElementById('feiYongJianMian'));
		     myChart.setOption(option);
		}
	}// end

	// 输出test接口
	exports('myEchars', obj);
});
