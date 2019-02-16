layui.define(function(exports){
    var  $jq = layui.jquery;

    var obj ={
        initView : function(){
            layui.use(['language'],function(language){
                language.render('homePage');
            });
            this.initFinanceCharts();
            layui.form.render();
        },


        /**
         * 图表自适应初始化
         */
        initFinanceCharts : function(){
            var chartIds = {};
            //应收回款
           // var yingshouHuikuanChart = echarts.init(document.getElementById('yingshouHuikuanRatio'));
           // chartIds['yingshouHuikuanChart'] = yingshouHuikuanChart;
            //总回款
           // var sumHuikuanChart = echarts.init(document.getElementById('sumHuikuanRatio'));
           // chartIds['sumHuikuanChart'] = sumHuikuanChart;
            //回收报告
            var huiShouReortChart = echarts.init(document.getElementById('huiShouRatio'));
            chartIds['huiShouReortChart'] = huiShouReortChart;
            //本金回收
            var benjinHuishouChart = echarts.init(document.getElementById('benjinHuishouRatio'));
            chartIds['benjinHuishouChart'] = benjinHuishouChart;

            var sendDatas = {};
            // if(!sendData){
            //     sendDatas['date'] = $jq('#date').val();
            // }else{
            //     sendDatas = sendData;
            // }
            this.initEcharts(sendDatas,chartIds);
            window.addEventListener("resize", function () {
               // yingshouHuikuanChart.resize();
               // sumHuikuanChart.resize();
                huiShouReortChart.resize();
                benjinHuishouChart.resize();
            });
        },



        /**
         * 财务图表数据
         */
        initEcharts : function(sendDatas,chartIds){
            $jq.post(basePath + 'admin/echart/get_finance_data_all',sendDatas,function(data) {

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
                if(!ajaxCall(d)){
                    return;
                }

                //回收报告
                obj.inColumnCharts(d.data.loan_repayment_tag,d.data.loan_repayment_ratio,chartIds.huiShouReortChart);
                var leiji_fangkuan_sum = 0;
                    var leiji_yihuan_sum  = 0;
                    var zhaiku_amount_sum  = 0;
                    $jq.each(d.data.loan_repayment_ratio.list,function(index,item){
                        leiji_fangkuan_sum += Number(item.yingshou_benjin_sum_sum);
                        leiji_yihuan_sum += Number(item.huankuan_zonge_sum_sum);
                        zhaiku_amount_sum += Number(item.weihuankuan_benxi_sum_sum);
                    });
                    //累计放款总额  累计还款总额 债库金额
                    $jq('#huiShouRatioSpan').html('<span>' + layui.language.get('lj_loan_zonger') +' : '+ formatNumber(leiji_fangkuan_sum) + '</span></br>'
                                                +'<span>'+layui.language.get('lj_repay_zonger')+' : '+ formatNumber(leiji_yihuan_sum) +'</span></br>'
                                                +'<span>'+layui.language.get('zhaiku_amount')+' : '+ formatNumber(zhaiku_amount_sum) +'</span>'
                                                );
                //本金回收
                obj.inCurveCharts(d.data.principal_tag,d.data.principal_ratio,chartIds.benjinHuishouChart);
                    var benjin_huishou_rateArr = [];
                    var benjin_huishou_rate_sum = 0;
                    var day_num = 0;
                    $jq.each(d.data.principal_ratio.list,function(index,item){
                        ++day_num;
                        benjin_huishou_rate_sum  += Number(item.benjin_huishou_rate);
                        benjin_huishou_rateArr.push(item.benjin_huishou_rate);
                    });
                    var benjin_huishou_rateMax = Math.max.apply(null,benjin_huishou_rateArr)+"%"; //最大值
                    var benjin_huishou_rateMin = Math.min.apply(null,benjin_huishou_rateArr)+"%"; //最小值
                    benjin_huishou_rateAvg = ((benjin_huishou_rate_sum/day_num).toFixed(2))+"%";
                    //本金回收率Max  本金回收率Min 本金回收率Avg
                    $jq('#benjinHuishouRatioSpan').html('<span style="text-align: center">' + "Max"+' : '+ benjin_huishou_rateMax + '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>'+ "Min"+' : '+benjin_huishou_rateMin+'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>'+"Avg"+' : '+benjin_huishou_rateAvg+'</span>');

            });

        },


        /**
         * 扇形图
         */
        inSectorCharts : function(title,data,chartId){
            var legend = [];
            $jq.each(data,function(i,item){
                // legend.push(item.name);
                var main =item.name +" : " +item.value;
                legend.push(main);
                item.name = main;
            })
            var option = {
                color:['#D48265','#91C7AE','#61A0A8'],
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
                    top: 30,
                    data: legend
                },
                series : [
                    {
                        name: layui.language.get('ratio'),
                        type: 'pie',
                        radius : '50%',
                        center: ['55%', '55%'],
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

        },


        /**
         * 曲线图
         */
        inCurveCharts3 : function(title,data,chartId){
            if(data == null  ||  data == undefined)  return;
            var list = null;
            if(data.list && data.list.length>0){
                list = data.list;
            }else {
                list = [];
            }

            var benjinhuishou_rate_field = data.field.benjinhuishou_rate; //本金回收率
            var benjinhuishou_rate_arr = [];
            var date_arr = [];
			var benjin_field = data.field.yinghuan_benjin;              //应还本金
            var benjin_arr = [];
			var huankuan_field = data.field.repayment_amount;           //已还款总额
            var huankuan_arr = [];
            
			var min = 0;
			var max = 0;
			var avg = 0;
			var sum = 0;
			var i = 0;
            for(var index in list){
                date_arr[index]                = list[index].date_str;                 //日期分组
                benjinhuishou_rate_arr[index] = list[index].benjin_huishou_rate;       //本金回收率
				benjin_arr[index] = list[index].yingshou_benjin_sum;                    //应还本金
				huankuan_arr[index] = list[index].huankuan_zonge_sum;                   //已还款总额
				var item = Number.parseFloat(list[index].benjin_huishou_rate);
				if(i==0){min=item;}
				if(item<min){min=item;}
				if(item>max){max=item;}
                    sum += item;
                    ++i;
            }
			
                if(i>0){
                    avg = sum/i;
                    avg = avg.toFixed(2);
                }
			document.getElementById('max').innerHTML='<span style="margin-left:36px;">Max:'+max+'</span>'
			                                       +'<span style="margin-left:36px;">Min:'+min+'</span>'
												   +'<span style="margin-left:36px;">Avg:'+avg+'</span>';
			
            option = {
                title: {
                    text:title,
                    x:'center',
                    textStyle: {
                        color: '#464646',
                        fontWeight: 'lighter',
                        fontFamily: 'MicrosoftJhengHei',
                        fontSize: 20
                    }
                },
                color: ['#FF9933','#9e2d04','#59c8e2'],
				  legend: {
						data:[data.field.benjinhuishou_rate,data.field.yinghuan_benjin,data.field.repayment_amount],
						top:25,
						orient : 'horizontal',
						align :'left'
					},
                tooltip: {
                    trigger: 'axis',
                    formatter:function(params){
						var p = '';
						if(params.dataIndex=='1'){
							p = '%';
						}
                        var res = params[0].name+'<br/>';
                        for(var i = 0;i<params.length;i++){
                            res += '<span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>';
                            res += params[i].seriesName + ':' + params[i].value +p+ '<br/>';
                        }
                        return res;
                    }
                },
                grid: {
					bottom: '30%'
                },
                xAxis: {
                    data:date_arr,
                    axisLabel: {
                        rotate: 50,
                        interval: 0
                    }
                },
                yAxis: [
					{
						type:'value',
						name:'num',
						position:'left',
						max:800000000
						/*splitLine: {
							show: true,
						} ,
						axisLabel: {
							show: true,
							interval: 'auto',
							formatter: '{value}'
						} */
					},
					{
						// type:'category',
						name:'%',
						position:'right'
						// data:[0,50,100,120]
						/*splitLine: {
							show: true,
						} ,
						axisLabel: {
							show: true,
							interval: 'auto',
							formatter: '{value}'
						}*/
					}
				],
                dataZoom:[
                    {
                        type: 'slider',
                        show: true,
                        xAxisIndex: [0],
                        handleSize: 20,//滑动条的 左右2个滑动条的大小
                        height: 20,//组件高度
                        left: 30, //左边的距离
                        right: 40,//右边的距离
                        bottom: 10,//右边的距离
                        start: 80,                                //数据窗口范围的起始百分比,表示30%
                        end: 100                                  //数据窗口范围的结束百分比,表示70%
                    }
                ],

                series: [
					{
						name: benjinhuishou_rate_field,
						yAxisIndex:1,
						type: 'line',
						data:benjinhuishou_rate_arr
					},
					{
						name: benjin_field,
						
						yAxisIndex:0,
						type: 'line',
						data:benjin_arr
					},
					{
						name: huankuan_field,
						yAxisIndex:0,
						type: 'line',
						data:huankuan_arr
					}
				]
            };
			
			
            chartId.setOption(option,true);
			
        },


        inCurveCharts : function(title,data,chartId){
            if(data == null  ||  data == undefined)  return;
            var list = null;
            if(data.list && data.list.length>0){
                list = data.list;
            }else {
                list = [];
            }

            var benjinhuishou_rate_field = data.field.benjinhuishou_rate;              //本金回收率
            var yinghuan_benjin_field    = data.field.yinghuan_benjin;                 //应还本金
            var repayment_amount_field   = data.field.repayment_amount;                //已还款总额
            var benjinhuishou_rate_arr = [];
            var yinghuan_benjin_arr    = [];
            var repayment_amount_arr   = [];
            var date_arr = [];
            for(var index in list){
                date_arr[index]               = list[index].date_str;               //日期分组
                benjinhuishou_rate_arr[index] = list[index].benjin_huishou_rate;    //本金回收率
                yinghuan_benjin_arr[index]    = list[index].yingshou_benjin_sum;        //应还本金
                repayment_amount_arr[index]   = list[index].huankuan_zonge_sum;       //已还款总额
            }
            option = {
                title: {
                    text:title,
                    x:'center',
                    textStyle: {
                        color: '#464646',
                        fontWeight: 'lighter',
                        fontFamily: 'MicrosoftJhengHei',
                        fontSize: 20
                    }
                },
                color: ['#FF0000','#59c8e2','#FF9933'],
                tooltip: {
                    trigger: 'axis',
                    formatter:function(params){
                        var res = params[0].name+'<br/>';
                        for(var i = 0;i<params.length-1;i++){
                            res += '<span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>';
                            res +=params[i].seriesName + ': ' + formatNumber(params[i].value) + '<br/>';
                        }
                        //本金回收率需要添加 %
                        res += '<span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:' + params[2].color + '"></span>';
                        res +=  params[2].seriesName + ': ' + params[2].value + '%<br/>';
                        return res;
                    }

                },
                legend: {
                    data:[benjinhuishou_rate_field,yinghuan_benjin_field,repayment_amount_field],
                    x : 'center',
                    top : 30,
                    itemWidth: 10,
                    itemHeight: 10,
                    itemGap: 10
                },
                grid: {
                    top:'20%',
                    left: '3%',
                    right: '4%',
                    bottom: '24%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type: 'category',
                        data: date_arr,
                        axisPointer: {
                            type: 'shadow'
                        },
                        axisLabel: {
                            rotate: 50,
                            interval: 0
                        }
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: 'Num',
                        // axisLabel: {
                        //     formatter: '{value} ml'
                        // }
                    },
                    {
                        type: 'value',
                        name: '%',
                        min: 0,
                        axisLabel: {
                            formatter: '{value}%'
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
                        start: 80,              //数据窗口范围的起始百分比,表示30%
                        end: 100                //数据窗口范围的结束百分比,表示70%
                    }
                ],
                series: [
                    {
                        name:yinghuan_benjin_field,
                        type:'line',
                        data:yinghuan_benjin_arr
                    },
                    {
                        name:repayment_amount_field,
                        type:'line',
                        data:repayment_amount_arr
                    },
                    {
                        name:benjinhuishou_rate_field,
                        type:'line',
                        yAxisIndex: 1,
                        data:benjinhuishou_rate_arr
                    }
                ]
            };
            chartId.setOption(option,true);
        },


        /**
         * 柱状图
         */
        inColumnCharts : function(title,data,chartId){
            if(data == null  ||  data == undefined){
                return false;
            }
            var list = null;
            if(data.list && data.list.length>0){
                list = data.list;
            }else {
                list = [];
            }
            var loan_amount_Field = data.field.loan_amount;                     //放款总金额
            var repayment_amount_Field = data.field.repayment_amount;           //已还款总额
            var zhaiku_amount_Field = data.field.weihuan_amount;                 //债库金额

            var loan_amountArr      =[];
            var repayment_amountArr =[];
            var zhaiku_amountArr    =[];
            var dateArr             =[];
            for(var index in list){
                loan_amountArr[index] =  list[index].yingshou_benjin_sum_sum;               //放款总金额
                repayment_amountArr[index] = list[index].huankuan_zonge_sum_sum;      //已还款总额
                zhaiku_amountArr[index] = list[index].weihuankuan_benxi_sum_sum;            //债库金额
                dateArr[index] = list[index].date_str;                              //时间
            }

            var	option = {
                color: ['#a1e9d9','#59c8e2','#E22F27','#FF0000'],
                title: {
                    text: title,
                    x:'center',
                    textStyle: {
                        color: '#464646',
                        fontWeight: 'lighter',
                        fontFamily: 'MicrosoftJhengHei',
                        fontSize: 20
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend : {
                    orient : 'horizontal',
                    data : [loan_amount_Field, repayment_amount_Field,zhaiku_amount_Field],
                    x : 'center',
                    // bottom : 40,
                    top : 40,
                    itemWidth: 10,
                    itemHeight: 10,
                    itemGap: 10
                },
                grid: {
                    top:'20%',
                    left: '3%',
                    right: '4%',
                    bottom: '24%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type : 'category',
                        data : dateArr,
                        axisTick: {
                            alignWithLabel: true
                        },
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        splitLine:{
                            show:true,
                            lineStyle:{
                                color:'#e9ebf3',
                                type:'dashed'
                            }
                        },
                        // axisLabel: {
                        //     show: true,
                        //     interval: 'auto',
                        //     formatter: '{value}%'
                        // }
                    }
                ],
                // dataZoom:[
                //     {
                //         type: 'slider',
                //         show: true,
                //         xAxisIndex: [0],
                //         handleSize: 20,//滑动条的 左右2个滑动条的大小
                //         height: 20,//组件高度
                //         left: 30, //左边的距离
                //         right: 40,//右边的距离
                //         bottom: 10,//右边的距离
                //         start: 60,                                //数据窗口范围的起始百分比,表示30%
                //         end: 100                                  //数据窗口范围的结束百分比,表示70%
                //     }
                // ],
                series: [
                    {
                        name: loan_amount_Field,
                        type: 'bar',
                        barMaxWidth :30,
                        data: loan_amountArr
                    },
                    {
                        name: repayment_amount_Field,
                        type: 'bar',
                        barMaxWidth :30,
                        data: repayment_amountArr
                    },
                    {
                        name: zhaiku_amount_Field,
                        type: 'bar',
                        barMaxWidth :30,
                        data: zhaiku_amountArr
                    }
                ]
            };

            chartId.setOption(option);
        }

    };

    exports('index_report',obj);
});