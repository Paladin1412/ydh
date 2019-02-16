 layui.define(function (exports) {

    var $jq = layui.jquery;
    var language = layui.language;
    var form = layui.form;

    var obj ={
        initView: function () {
            //初始化
            this.initCompany();
            language.render('y-page-user');
            //初始化报表
            this.initEcharts();
            //初始化按钮的css
            layui.statisticsReport.initBtnCss();
        }

        /**
         * 初始化公司列表
         */
        ,initCompany : function(){
            var roleType = dataStore.get('collection_role_type');
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
                    }
                });
            }

        }

        /**
         * 图表展示
         */
        ,initEcharts : function() {

            // 收支金额统计表
            layui.statisticsReport.initszStatisticsChart('1',"");  //默认自然月显示

            //初始化时间
            layui.statisticsReport.initDate();
        }

        ,initDate : function(){
            layui.laydate.render({
                elem: '#sztjDate'
                ,range: true
                ,done : function(value){
                    layui.statisticsReport.initszStatistics('1',value);
                }
            });

        }


        /**
         * 初始化收支金额报表
         * @param type
         * @param date
         */
        ,initszStatistics : function(type,date){
            if(date == "" || date == undefined){
                date = $jq("#sztjDate").val();
            }
            if(type == '2'){  //周
                selectThisWeek('.szStatistics-btn-left','.szStatistics-btn-left span','.szStatistics-btn-right','.szStatistics-btn-right span');
            }else{   //月
                selectThisMonth('.szStatistics-btn-left','.szStatistics-btn-left span','.szStatistics-btn-right','.szStatistics-btn-right span');
            }

            this.initszStatisticsChart(type,date);
        }
        ,initszStatisticsChart : function(type,date){
            var company_id = $jq('#company_id').val();
            $jq.post(basePath + 'admin/Echart/finance_chart',{'type':type,'date':date,'company_id':company_id},function(data) {
                if(!ajaxCall(data)){
                    return;
                }
                layui.statisticsReport.initszStatisticsChartDatas(data.data);
            });

            // if(type == '2'){
            //     $jq.post('./json/financial/week_shouzhi.json'," ",function(data) {
            //
            //         if(!ajaxCall(data)){
            //             return;
            //         }
            //         var _data = data.data;
            //         layui.statisticsReport.initszStatisticsChartDatas(_data);
            //     });
            // }else{
            //     $jq.post('./json/financial/month_shouzhi.json'," ",function(data) {
            //         if(!ajaxCall(data)){
            //             return;
            //         }
            //         var _data = data.data;
            //         layui.statisticsReport.initszStatisticsChartDatas(_data);
            //     });
            // }

        }
        ,initszStatisticsChartDatas : function(data){
            if(data == null  ||  data == undefined){
                return false;
            }
            var list = null;
            if(data.data_list && data.data_list.length>0){
                list = data.data_list;
            }else {
                list = [];
            }
            // console.log(list);
            // var loan_Field  = data.field.pay_sum;                             //放款总金额
            // var repayment_Field = data.field.repay_sum;                       //回款款总金额
            var loan_Field  = layui.language.get('loan_sum_amount');            //放款总金额
            var repayment_Field = layui.language.get('repay_sum_amount');       //回款款总金额

            var loan_sumArr=[];
            var repay_sumArr=[];
            var groupArr = [];
            for(var index in list){
                // groupArr[index] = list[index].group;       // 周、月分组
                loan_sumArr[index] =  list[index].pay_sum;   //放款总金额
                repay_sumArr[index] = list[index].repay_sum;     //回款款总金额
                groupArr[index] = list[index].date_str;       // 周、月分组
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
//            var kaohe_titlekaohe_title = layui.language.get('approval_report');
            var	option = {
                color: ['#a1e9d9','#59c8e2'],
//                title: {
//                    text: kaohe_title,
//    				textStyle: {
//    					color: '#464646',
//    					fontWeight: 'lighter',
//    					fontFamily: 'MicrosoftJhengHei',
//    					fontSize: 20
//    				}
//                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend : {
                    orient : 'horizontal',
                    data : [loan_Field , repayment_Field],
                    x : 'center',
                    bottom : 10,
                    itemWidth: 10,
                    itemHeight: 10,
                    itemGap: 10
                },
                grid: {
                    top:'10%',
                    left: '3%',
                    right: '4%',
                    // bottom: '5%',
                    bottom: '30%',
                    containLabel: true
                },
                xAxis: [
                    {
                        type : 'category',
                        data : groupArr,
                        boundaryGap: false,
                        axisLabel: {
                            rotate: 40,
                            interval: 0
                        },
                        // axisTick: {
                        //     alignWithLabel: true
                        // }
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
                        }
                    }
                ],
                series: [
                    {
                        name: loan_Field ,
                        type: 'bar',
                        barGap: 0,
                        barMaxWidth :50,
                        data: loan_sumArr
                    },
                    {
                        name: repayment_Field,
                        type: 'bar',
                        barMaxWidth :50,
                        data: repay_sumArr
                    }
                ]
            };
            var myChart = echarts.init(document.getElementById('szStatistics'));
            myChart.setOption(option);

        }

        /**
         * 初始化css
         */
        ,initBtnCss : function(){
            // $jq('.szStatistics-btn-left').css('background-color','#A1EAD9');
            // $jq('.szStatistics-btn-left span').css('color','#FFFFFF');
            $jq('.szStatistics-btn-right').css('background-color','#A1EAD9');
            $jq('.szStatistics-btn-right span').css('color','#FFFFFF');

        }

        /**
         * 监听事件
         */
       ,tool : function(){
            //监听表单公司下拉框事件
            form.on('select(companyId)',function(data){
                layui.statisticsReport.initEcharts();
                form.render('select');
            });
        }

    }

    //输出 statisticsReport 接口
    exports('statisticsReport', obj);
});