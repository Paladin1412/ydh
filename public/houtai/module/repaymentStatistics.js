layui.define(['form','layer','element'],function(exports){
    var  $jq = layui.jquery,
            form = layui.form,
            table = layui.table;
            element = layui.element,
            laypage = layui.laypage;

    var obj ={
        initView : function(){
            layui.language.render('viewDetail');
            layui.form.render();
            // 初始化公司
            var roleType = dataStore.get('collection_role_type');
            if(roleType == '6'){
                $jq('#companyList').show();
                $jq.post(basePath+'admin/Base/company_list', '',function (data) {
                    if (data.code == 200) {
                        var cp_nameHtml = '<option value="">'+ layui.language.get('sel_company') +'</option>';
                        $jq.each(data.data, function (i, item) {
                            cp_nameHtml += '<option value="' + item.cp_id + '">' + item.cp_name + '</option>';
                        })
                        $jq('select[name="company_id"]').html(cp_nameHtml);
                        layui.form.on('select(company_id)', function(data){
                            layui.repaymentStatistics.getData(1,50,data.value);
                        });
                        layui.form.render('select');
                    }
                });
            }
            //初始化日期选择
            layui.laydate.render({
                elem: '#date'
                ,range : true
                , done: function (value) {
                    if (!value) return;
                    var sendDatas ={};
                    sendDatas.date = value;
                    sendDatas.company_id = $jq('#company_id').val();
                    $jq.post(basePath + 'admin/Finance/all_list',sendDatas,function(data) {
                        if (!ajaxCall(data)) {
                            return;
                        }
                        //财务统计
                        layui.repaymentStatistics.initViewTable(data);
                        //获客信息
                        layui.repaymentStatistics.initViewHuokeData(data);
                    });
                    var is_show = $jq("#financeStatistics").css("display");
                    if(is_show == "block"){
                        layui.repaymentStatistics.initFinanceCharts(sendDatas);
                    }
                }
            });



            obj.getData(1,50);
        },

        //获取数据
        getData : function(curr,limit) {
            var sendDatas = {};
            sendDatas.date = $jq('#date').val();
            sendDatas.company_id = $jq('#company_id').val();
            $jq.post(basePath + 'admin/Finance/all_list?page=' + curr + '&limit=' + limit, sendDatas, function (data) {
                    //财务统计
                    layui.repaymentStatistics.initViewTable(data);
                    //获客信息
                    layui.repaymentStatistics.initViewHuokeData(data);
            })

        },


        /**
         * 图表自适应初始化
         */

        initFinanceCharts : function(sendData){
            var chartIds = {};
            // //应收回款
            // var yingshouHuikuanChart = echarts.init(document.getElementById('yingshouHuikuanRatio'));
            //     chartIds['yingshouHuikuanChart'] = yingshouHuikuanChart;
            // //总回款
            // var sumHuikuanChart = echarts.init(document.getElementById('sumHuikuanRatio'));
            //     chartIds['sumHuikuanChart'] = sumHuikuanChart;
            //回收报告
            var huiShouReortChart = echarts.init(document.getElementById('huiShouRatio'));
                chartIds['huiShouReortChart'] = huiShouReortChart;
            //本金回收
            var benjinHuishouChart = echarts.init(document.getElementById('benjinHuishouRatio'));
                chartIds['benjinHuishouChart'] = benjinHuishouChart;

            var sendDatas = {};
            if(!sendData){
                sendDatas['date'] = $jq('#date').val();
                sendDatas['company_code'] = $jq('#company_id').val();
            }else{
                sendDatas = sendData;
            }
            layui.repaymentStatistics.initEcharts(sendDatas,chartIds);
            window.addEventListener("resize", function () {
                // yingshouHuikuanChart.resize();
                // sumHuikuanChart.resize();
                huiShouReortChart.resize();
                benjinHuishouChart.resize();
            });
        },


        /**
         * 初始化表格
         */
        initViewTable : function(data){
                var field = data.data.field;
                var data = data.data;
                    var  yinghuan_order_cntSum      = 0;   //应还款数
                    var  yihuan_order_cntSum        = 0;   //已还款数
                    var  weihuan_order_cntSum       = 0;   //未还款数
                    var  yingshou_benjinSum         = 0;   //应收本金
                    var  yingshou_benxiSum          = 0;   //应收本息
                    var  yingshou_zongjineSum       = 0;   //应收总金额
                    var  huankuan_benxiSum          = 0;   //还款本息
                    var  huankuan_zongeSum          = 0;   //还款总额
                    var  benjin_huishou_rateSum     = "0%";   //本金回收率
                    var  yingshou_huishou_rateSum   = "0%";   //应收回款率
                    var  zong_huishou_rateSum       = "0%";   //总回款率

            $jq.each(data.list,function(index,item){
                data.list[index].benjin_huishou_rate1 =item.benjin_huishou_rate+"%";            //本金回收率
                data.list[index].yingshou_huishou_rate1 =item.yingshou_huishou_rate+"%";        //应收回款率
                data.list[index].zong_huishou_rate1 =item.zong_huishou_rate+"%";                //总回收率
                data.list[index].zong_huishou_rate2 =item.zong_huishou_rate+"%";                //总回收率

                yinghuan_order_cntSum += Number(item.yinghuan_order_cnt);
                yihuan_order_cntSum   += Number(item.yihuan_order_cnt);
                weihuan_order_cntSum  += Number(item.weihuan_order_cnt);
                yingshou_benjinSum    += Number(item.yingshou_benjin_sum);
                yingshou_benxiSum     += Number(item.yingshou_benxi_sum);
                yingshou_zongjineSum  += Number(item.yingshou_zongjine_sum);
                huankuan_benxiSum     += Number(item.huankuan_benxi_sum);
                huankuan_zongeSum     += Number(item.huankuan_zonge_sum);
            });
            if(yingshou_benjinSum !== 0  && huankuan_zongeSum !== 0){  // 本金回收率【=还款总额/应还本金总和】，
                 benjin_huishou_rateSum = ((huankuan_zongeSum/yingshou_benjinSum)*100).toFixed(2) +"%";
            }
            if(yingshou_zongjineSum !== 0 && huankuan_zongeSum !== 0){  // 应收回款率【=还款总额（含罚息）/ 应收本息】，
                 yingshou_huishou_rateSum = ((huankuan_zongeSum/yingshou_benxiSum)*100).toFixed(2) +"%";
            }
            if(yingshou_benjinSum !== 0 && huankuan_zongeSum !== 0){  // 总回款率【=还款总额（含罚息）/ 应收总金额（含罚息）】
                 zong_huishou_rateSum = ((huankuan_zongeSum/yingshou_zongjineSum)*100).toFixed(2) +"%";
            }
                    yingshou_benjinSum      = formatNumber(yingshou_benjinSum);
                    yingshou_benxiSum       = formatNumber(yingshou_benxiSum);
                    yingshou_zongjineSum    = formatNumber(yingshou_zongjineSum);
                    huankuan_benxiSum       = formatNumber(huankuan_benxiSum);
                    huankuan_zongeSum       = formatNumber(huankuan_zongeSum);

           var Table = table.render({
                    elem : '#DetailsListTable',
                    data: data.list,
                    autoHeight: true,
                    limit: data.page.limit,
                    cols : [[
                        {field: 'date_str', title: layui.language.get('yhk_date'),width:"10%"},
                        {field: 'yinghuan_order_cnt', title: field.yinghuan_order_cnt},
                        {field: 'yihuan_order_cnt', title: field.yihuan_order_cnt},
                        {field: 'weihuan_order_cnt', title: field.weihuan_order_cnt},
                        {field: 'yingshou_benjin_sum', title: field.yingshou_benjin_sum,templet:function(d){
                                return formatNumber(d.yingshou_benjin_sum);
                            }},
                        {field: 'yingshou_benxi_sum', title: field.yingshou_benxi_sum,templet:function(d){
                                return formatNumber(d.yingshou_benxi_sum);
                            }},
                        {field: 'yingshou_zongjine_sum', title: field.yingshou_zongjine_sum,templet:function(d){
                                return formatNumber(d.yingshou_zongjine_sum);
                            }},
                        {field: 'huankuan_benxi_sum', title: field.huankuan_benxi_sum,templet:function(d){
                                return formatNumber(d.huankuan_benxi_sum);
                            }},
                        {field: 'huankuan_zonge_sum', title: field.huankuan_zonge_sum,templet:function(d){
                                return formatNumber(d.huankuan_zonge_sum);
                            }},
                        {field: 'benjin_huishou_rate1', title: field.benjin_huishou_rate},
                        {field: 'yingshou_huishou_rate1', title: field.yingshou_huishou_rate},
                        {field: 'zong_huishou_rate1', title: field.zong_huishou_rate},
                        // {field: 'zong_huishou_rate2', title: field.zong_huishou_rate,templet:function(d){   //遍历每行的进度条
                        //         return '<div class="layui-progress" lay-filter="zong_huishou_rate2' + d.LAY_TABLE_INDEX + '" lay-showPercent="true" style="border-radius: 0px;height:28px;background-color:#FFF"><span style="position:absolute;z-index:1;">' + d.zong_huishou_rate2 + '</span><div class="layui-progress-bar" style="border-radius: 0px;height:28px;background: -webkit-linear-gradient(left, #FFB72B , #FFEDCC);"></div></div>';
                        //  }}
                    ]],


                    id: 'DetailsListTable',
                    page: false,
                    done: function(res, curr, count){
                        var allTableHead = $jq('.layui-table-cell span');//所有表头
                        allTableHead.each(function(index,item){
                            item.parentElement.title = item.textContent;
                        })
                      // $jq.each(res.data,function(index,item){ //遍历每行的进度条
                      //     layui.element.progress('zong_huishou_rate2'+index,item.zong_huishou_rate2);
                      // })
                    }

                });

            //执行重载
            //完整功能
            var canFlush = false;
            laypage.render({
                elem: 'DetailsListPage'
                ,count: data.page.count
                ,curr:  data.page.page
                ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count', 'prev', 'page', 'next','limit','limits' , 'skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.repaymentStatistics.getData(obj.curr,obj.limit);
                    }else {
                        canFlush=true;
                    }
                }
            });
                if(data.list.length >0){
                    var tr = '<tr class=\"finance-sum-tr\" style="text-align: center;font-weight: bold"><td>' + layui.language.get('sum') + '</td> <td>' + yinghuan_order_cntSum + '</td> <td >' + yihuan_order_cntSum + '</td> <td>' + weihuan_order_cntSum + '</td> <td>' + yingshou_benjinSum + '</td> <td>' + yingshou_benxiSum + '</td> <td>' + yingshou_zongjineSum + '</td> <td>' + huankuan_benxiSum + '</td> <td>' + huankuan_zongeSum + ' </td><td>' + benjin_huishou_rateSum + '</td><td>' + yingshou_huishou_rateSum + '</td><td>' + zong_huishou_rateSum + '</td></tr>';
                      $jq("#caiwulist .layui-table-body .layui-table").append(tr);
                }



            // Table.reload();
            // var $ = layui.$, active = {
            //     reload: function(){
            //         var demoReload = $('#demoReload');
            //
            //         table.reload('testReload', {
            //             where: {
            //                 key: {
            //                     id: demoReload.val()
            //                 }
            //             }
            //         });
            //     }
            // };
            //
            // $('.demoTable .layui-btn').on('click', function(){
            //     var type = $(this).data('type');
            //     active[type] ? active[type].call(this) : '';
            // });

        },


        /**
         * 获客相关信息
         */
        initViewHuokeData : function(data){
            var field = data.data.sum_field;
            var data = data.data.sum_list;
            table.render({
                elem : '#GuestCostTable',
                data: data,
                autoHeight: true,
                // limit: data.list.length,
                limit: 10,
                cols : [[
                    {field: 'name', title: field.name,width:"10%"},
                    {field: 'order_apply_sum', title: field.order_apply_sum},
                    {field: 'order_handle_sum', title: field.order_handle_sum},
                    {field: 'order_handle_rate', title: field.order_handle_rate,templet:function(d){
                            return d.order_handle_rate + "%";
                    }},
                    {field: 'order_ht_amount', title: field.order_ht_amount,templet:function(d){
                            return formatNumber(d.order_ht_amount);
                    }},
                    {field: 'order_bj_amount', title: field.order_bj_amount,templet:function(d){
                            return formatNumber(d.order_bj_amount);
                    }},
                    {field: 'order_repay_sum', title: field.order_repay_sum},
                    {field: 'order_repay_amount', title: field.order_repay_amount,templet:function(d){
                            return formatNumber(d.order_repay_amount);
                    }},
                    {field: 'order_profit', title: field.order_profit,templet:function(d){
                            return formatNumber(d.order_profit);
                    }},
                    {field: 'order_profit_rate', title: field.order_profit_rate,templet:function(d){
                            return d.order_profit_rate + "%";
                    }},
                ]],
                id: 'GuestCostTable',
                page: false,
                done: function(res, curr, count){
                    var allTableHead = $jq('.layui-table-cell span');//所有表头
                    allTableHead.each(function(index,item){
                        item.parentElement.title = item.textContent;
                    })
                }

            });
        },


        /**
         * Echart 隐藏
         */
        echart_hidden : function(){
            $jq('#financeStatistics').hide();
            $jq('#echart_hidden').hide();
            $jq('#echart_show').show();
        },

        /**
         * Echart 展示
         */
        echart_show : function(){
             $jq('#financeStatistics').show();
             $jq('#echart_hidden').show();
             $jq('#echart_show').hide();
             var sendDatas = {};
             sendDatas.date = $jq("#date").val();
            layui.repaymentStatistics.initFinanceCharts(sendDatas);
         },


        /**
         * 财务图表数据
         */
        initEcharts : function(sendDatas,chartIds){
            $jq.post(basePath + 'admin/echart/get_finance_data',sendDatas,function(data) {
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
                    $jq('#financeStatistics').hide();
                    return;
                }
                if(!ajaxCall(d)){
                    return;
                }

                // //应收回款(本息回收)
                // layui.repaymentStatistics.inSectorCharts(d.data.yingshou_huikuan_tag,d.data.yingshou_huikuan_ratio.list,chartIds.yingshouHuikuanChart);
                // var yinghuan_benxiSum = 0;
                // $jq.each(d.data.yingshou_huikuan_ratio.list,function(index,item){
                //     yinghuan_benxiSum += Number(item.value);
                // });
                // $jq('#yingshouHuikuanRatioSpan').html('<span>' + layui.language.get('ys_benxi')+' : ' + yinghuan_benxiSum + '</span>'); //应还本息

                // //总回款
                // layui.repaymentStatistics.inSectorCharts(d.data.sum_huikuan_tag,d.data.sum_huikuan_ratio.list,chartIds.sumHuikuanChart);
                // var sum_huikuan = 0;
                // $jq.each(d.data.sum_huikuan_ratio.list,function(index,item){
                //     sum_huikuan += Number(item.value);
                // });
                // $jq('#sumHuikuanRatioSpan').html('<span>' + layui.language.get('ys_zonger') +' : '+ sum_huikuan + '</span>');  //应收总额

                //回收报告
                layui.repaymentStatistics.inColumnCharts(d.data.loan_repayment_tag,d.data.loan_repayment_ratio,chartIds.huiShouReortChart);
                var leiji_fangkuan_sum = 0;
                var leiji_yihuan_sum  = 0;
                var zhaiku_amount_sum  = 0;
                $jq.each(d.data.loan_repayment_ratio.list,function(index,item){
                    leiji_fangkuan_sum += Number(item.yingshou_benjin_sum_sum);
                    leiji_yihuan_sum += Number(item.huankuan_zonge_sum_sum);
                    zhaiku_amount_sum += Number(item.weihuankuan_benxi_sum_sum);
                });

                //累计放款总额  累计还款总额 债库金额
                $jq('#huiShouRatioSpan').html('<span>' + layui.language.get('lj_loan_zonger') +' : '+ formatNumber(leiji_fangkuan_sum) + '</span></br><span>'+layui.language.get('lj_repay_zonger')+' : '+ formatNumber(leiji_yihuan_sum) +'</span></br><span>'+layui.language.get('zhaiku_amount')+' : '+ formatNumber(zhaiku_amount_sum) +'</span>');

               //本金回收
                layui.repaymentStatistics.inCurveCharts(d.data.principal_tag,d.data.principal_ratio,chartIds.benjinHuishouChart);
                    var benjin_huishou_rateArr = [];
                    var benjin_huishou_rate_sum = 0;
                    var day_num = 0;
                $jq.each(d.data.principal_ratio.list,function(index,item){
                         ++day_num;
                    benjin_huishou_rate_sum  += Number(item.benjin_huishou_rate);
                    benjin_huishou_rateArr.push(item.benjin_huishou_rate);
                });
                var benjin_huishou_rateMax = Math.max.apply(null,benjin_huishou_rateArr)+"%";       //最大值
                var benjin_huishou_rateMin = Math.min.apply(null,benjin_huishou_rateArr)+"%";       //最小值
                var benjin_huishou_rateAvg = ((benjin_huishou_rate_sum/day_num).toFixed(2)) +"%";  //平均率
                //本金回收率Max  本金回收率Min 本金回收率Avg
                $jq('#benjinHuishouRatioSpan').html('<label style="text-align: center"></label><span>' + "Max"+' : '+ benjin_huishou_rateMax + '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>'+ "Min"+' : '+benjin_huishou_rateMin+'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>'+"Avg"+' : '+benjin_huishou_rateAvg+'</span></label>');
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
                color:['#D48265','#A1E9D9','#91C7AE','#61A0A8'],
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
        inCurveCharts2 : function(title,data,chartId){
            if(data == null  ||  data == undefined)  return;
            var list = null;
            if(data.list && data.list.length>0){
                list = data.list;
            }else {
                list = [];
            }

            var benjinhuishou_rate_field = data.field.benjinhuishou_rate;              //本金回收率
            var repayment_amount_field   = data.field.repayment_amount;                //已还款总额
            var yinghuan_benjin_field    = data.field.yinghuan_benjin;                 //应还本金

            var benjinhuishou_rate_arr = [];
            // var benjinhuishou_rate_arr1 = [];
            var repayment_amount_arr = [];
            var yinghuan_benjin_arr = [];
            var date_arr = [];
            for(var index in list){  // update number
                date_arr[index]                = list[index].date_str;               //日期分组
                benjinhuishou_rate_arr[index]  = list[index].benjin_huishou_rate;      //本金回收率
                // benjinhuishou_rate_arr1[index] = list[index].benjin_huishou_rate+"%";      //本金回收率
                // repayment_amount_arr[index]   = list[index].repayment_amount;
                // yinghuan_benjin_arr[index]    = list[index].yinghuan_benjin;

            }
            option = {
                title: {
                    text: title
                },

                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    data: date_arr
                },
                yAxis: {
                    splitLine: {
                        show: false
                    }
                },
                dataZoom: [
                    {
                        type: 'slider',
                        show: true,
                        xAxisIndex: [0],
                        handleSize: 20,//滑动条的 左右2个滑动条的大小
                        height: 20,//组件高度
                        left: 30, //左边的距离
                        right: 40,//右边的距离
                        bottom: 10,//右边的距离
                        start: 60,                                //数据窗口范围的起始百分比,表示30%
                        end: 100,                                  //数据窗口范围的结束百分比,表示70%
                    //     // handleColor: '#ddd',//h滑动图标的颜色
                    //     // handleStyle: {
                    //     //     borderColor: "#cacaca",
                    //     //     borderWidth: "1",
                    //     //     shadowBlur: 2,
                    //     //     background: "#ddd",
                    //     //     shadowColor: "#ddd",
                    //     // },
                    //     // fillerColor: new echarts.graphic.LinearGradient(1, 0, 0, 0, [{
                    //     //     //给颜色设置渐变色 前面4个参数，给第一个设置1，第四个设置0 ，就是水平渐变
                    //     //     //给第一个设置0，第四个设置1，就是垂直渐变
                    //     //     offset: 0,
                    //     //     color: '#1eb5e5'
                    //     // }, {
                    //     //     offset: 1,
                    //     //     color: '#5ccbb1'
                    //     // }]),
                    //     // backgroundColor: '#ddd',//两边未选中的滑动条区域的颜色
                    //     // showDataShadow: false,//是否显示数据阴影 默认auto
                    //     // showDetail: false,//即拖拽时候是否显示详细数值信息 默认true
                    //     // handleIcon: 'M-292,322.2c-3.2,0-6.4-0.6-9.3-1.9c-2.9-1.2-5.4-2.9-7.6-5.1s-3.9-4.8-5.1-7.6c-1.3-3-1.9-6.1-1.9-9.3c0-3.2,0.6-6.4,1.9-9.3c1.2-2.9,2.9-5.4,5.1-7.6s4.8-3.9,7.6-5.1c3-1.3,6.1-1.9,9.3-1.9c3.2,0,6.4,0.6,9.3,1.9c2.9,1.2,5.4,2.9,7.6,5.1s3.9,4.8,5.1,7.6c1.3,3,1.9,6.1,1.9,9.3c0,3.2-0.6,6.4-1.9,9.3c-1.2,2.9-2.9,5.4-5.1,7.6s-4.8,3.9-7.6,5.1C-285.6,321.5-288.8,322.2-292,322.2z',
                    //     filterMode: 'filter',
                    },
                    // //下面这个属性是里面拖到
                    // {
                    //     // type: 'inside',
                    //     show: true,
                    //     xAxisIndex: [0],
                    //     start: 1,
                    //     end: 100
                    // }

                ],
                    visualMap: {
                        top: 0,
                        right: 0,
                    //     pieces: [{
                    //     gt: 0,
                    //     lte: 50,
                    //     color: '#096'
                    // }, {
                    //     gt: 50,
                    //     lte: 100,
                    //     color: '#ffde33'
                    // }, {
                    //     gt: 100,
                    //     lte: 150,
                    //     color: '#ff9933'
                    // }, {
                    //     gt: 150,
                    //     lte: 200,
                    //     color: '#cc0033'
                    // }, {
                    //     gt: 200,
                    //     lte: 300,
                    //     color: '#660099'
                    // }, {
                    //     gt: 300,
                    //     color: '#7e0023'
                    // }],
                        outOfRange: {
                        color: '#ECC994'
                    }
                },
                series: {
                    name: benjinhuishou_rate_field,
                        type: 'line',
                        data: benjinhuishou_rate_arr,
                        markLine: {
                            silent: true,
                                data: [
                                    {
                                yAxis: 50
                            },
                                    {
                                yAxis: 100
                            },
                                    {
                                yAxis: 150
                            },
                                    {
                                yAxis: 200
                            },

                            ]
                    }
                }
            }
            chartId.setOption(option,true);
        },
        //带平行线
        inCurveCharts3 : function(title,data,chartId){
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
                repayment_amount_arr[index]   = list[index].repayment_amount;       //已还款总额
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
                color: ['#FF9933','#ECC994','#59c8e2','#c98dc1'],
                tooltip: {
                    trigger: 'axis',
                    formatter:function(params){
                        var res = params[0].name+'<br/>';
                        for(var i = 0;i<params.length;i++){
                            res += '<span style="display:inline-block;margin-right:5px;border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>';
                            res += params[i].seriesName + ':' + params[i].value + '%<br/>';
                        }
                        return res;
                    }
                },
                legend : {
                    orient : 'horizontal',
                    data : [benjinhuishou_rate_field, yinghuan_benjin_field,repayment_amount_field],
                    x : 'center',
                    // bottom : 40,
                    top : 25,
                    itemWidth: 10,
                    itemHeight: 10,
                    itemGap: 10
                },
                grid: {
                    top:'15%',
                    left: '3%',
                    right: '4%',
                    bottom: '20%',
                    containLabel: true
                },
                xAxis: {
                    data:date_arr,
                    axisLabel: {
                        rotate: 50,
                        interval: 0
                    }
                },
                yAxis: {
                    type:'value',
                    splitLine: {
                        show: true,
                    },
                    axisLabel: {
                        show: true,
                        interval: 'auto',
                        formatter: '{value}%'
                    }
                },
                dataZoom:[
                    {
                        type: 'slider',
                        show: true,
                        xAxisIndex: [0],
                        handleSize: 20,//滑动条的 左右2个滑动条的大小
                        height: 20,//组件高度
                        left: 70,
                        right: 75,
                        // bottom: 10,
                        start: 60,              //数据窗口范围的起始百分比,表示30%
                        end: 100                //数据窗口范围的结束百分比,表示70%
                    }
                ],
                series: {
                    name: benjinhuishou_rate_field,
                    type: 'line',
                    data:benjinhuishou_rate_arr,
                    markLine: {
                        silent: true,
                        data: [
                            {yAxis: '50'},
                            {yAxis: '100'},
                            {yAxis: '200'}
                        ]
                    }

                }
            };
            chartId.setOption(option,true);
        },
        //双Y轴 折线图
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
                        // interval: 50,
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
                        start: 60,              //数据窗口范围的起始百分比,表示30%
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
        },

    };

    exports('repaymentStatistics',obj);
});