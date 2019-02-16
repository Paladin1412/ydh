layui.define(['jquery', 'language', 'form', 'laydate'], function (exports) {

    var $jq = layui.jquery;
    var language = layui.language;
    var form = layui.form;
    var table = layui.table;
    var laypage = layui.laypage;
    var laydate = layui.laydate;

    var obj = {
        initView: function () {
            language.render('page_business_statistical');
            layui.form.render();
            this.initRole();
            //初始化日期
            this.initDate();
            //初始化报表
            this.initBusinessEcharts();
            //初始化渠道信息
            // this.getChannelData(1, 20);

        }

        , initBusinessEcharts: function (sendData) {
            //初始化报表
            var chartIds = {};
            //业务统计
            //总点击量
            var clickAmountChart = echarts.init(document.getElementById('clickAmount'));
            chartIds['clickAmountChart'] = clickAmountChart;
            //渠道点击占比
            var channelChart = echarts.init(document.getElementById('channelRatio'));
            chartIds['channelChart'] = channelChart;
            //下载占比
            var downloadChart = echarts.init(document.getElementById('downloadRatio'));
            chartIds['downloadChart'] = downloadChart;
            //渠道下载占比
            var channelDownloadChart = echarts.init(document.getElementById('channelDownloadRatio'));
            chartIds['channelDownloadChart'] = channelDownloadChart
            //注册占比
            var regChart = echarts.init(document.getElementById('regRatio'));
            chartIds['regChart'] = regChart;
            //渠道注册占比
            var channelRegChart = echarts.init(document.getElementById('channelRegRatio'));
            chartIds['channelRegChart'] = channelRegChart;
            //申请占比
            var applyChart = echarts.init(document.getElementById('applyRatio'));
            chartIds['applyChart'] = applyChart;
            //渠道申请占比
            var channelApplyChart = echarts.init(document.getElementById('channelApplyRatio'));
            chartIds['channelApplyChart'] = channelApplyChart;
            var sendDatas = {};//定义发送数据包
            if (!sendData) {
                sendDatas['date'] = $jq('#date').val();
                sendDatas['company_id'] = $jq('#company_id').val();
            } else {
                sendDatas = sendData;
            }
            layui.businessStatistics.initEcharts(sendDatas, chartIds);
            //图表自适应
            window.addEventListener("resize", function () {
                clickAmountChart.resize();
                channelChart.resize();
                downloadChart.resize();
                channelDownloadChart.resize();
                regChart.resize();
                channelRegChart.resize();
                applyChart.resize();
                channelApplyChart.resize();
            });
        }

        /*
         * 初始化搜索框中的时间
         */
        , initDate: function () {
            laydate.render({
                elem: '#date',
                range: true,
                done: function (value) {
                    var sendData = {};
                    sendData.company_id = $jq('#company_id').val();
                    sendData.date = value;
                    layui.businessStatistics.initBusinessEcharts(sendData);
                    var is_show = $jq("#Channel_list").css("display");
                    // console.log(is_show);
                    if (is_show == "block") {
                        layui.businessStatistics.getChannelData(1, 20, sendData.date, sendData.company_id); //curr limit 日期   公司id
                    }

                }
            })
        }
        , initRole: function () {
            layui.use('verify_common', function () {
                layui.verify_common.initRole();
            });
        }

        //查询
        , search: function () {
            var sendData = {};
            sendData.company_id = $jq('#company_id').val();
            sendData.date = $jq('#date').val();
            layui.businessStatistics.initBusinessEcharts(sendData);
            var is_show = $jq("#Channel_list").css("display");
            // console.log(is_show);
            if (is_show == "block") {
                layui.businessStatistics.getChannelData(1, 20, sendData.date, sendData.company_id);
            }
        }
        , initEcharts: function (sendDatas, chartIds) {
            //业务统计
            $jq.post(basePath + 'admin/echart/get_business_data', sendDatas, function (data) {
                var d = null;
                if (data.code) {
                    d = data;
                } else {
                    try {
                        d = JSON.parse(data);
                    } catch (e) {
                        d = {};
                    }
                }
                if (d.code == '500') {
                    $jq('#businessStatistics').hide();
                    return;
                }
                if (!ajaxCall(d)) {
                    return;
                }

                //总点击量
                layui.businessStatistics.initReport(d.data.click_amount_tag, d.data.click_amount.list, chartIds.clickAmountChart);
                var clickAmount = 0;
                var clickAmountHtml = '';
                $jq.each(d.data.click_amount.list, function (index, item) {
                    clickAmount += Number(item.value);
                });
                clickAmountHtml += '<span>' + language.get('click_amount') + ' : ' + clickAmount + '</span>';
                $jq('#clickAmountSpan').html(clickAmountHtml);


                //渠道占比
                layui.businessStatistics.initReport(d.data.channel_ratio_tag, d.data.channel_ratio.list, chartIds.channelChart);
                var channelAmount = 0;
                var channelAmountHtml = '';
                $jq.each(d.data.channel_ratio.list, function (index, item) {
                    channelAmount += Number(item.value);
                    // channelAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';

                });
                channelAmountHtml += '<span>' + language.get('click_amount') + ' : ' + channelAmount + '</span>';
                $jq('#channelRatioSpan').html(channelAmountHtml);

                //下载占比
                layui.businessStatistics.initReport(d.data.download_ratio_tag, d.data.download_ratio.list, chartIds.downloadChart);
                var downloadAmount = 0;
                var downloadAmountHtml = '';
                $jq.each(d.data.download_ratio.list, function (index, item) {
                    downloadAmount += Number(item.value);
                    // downloadAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';
                });
                downloadAmountHtml += '<span>' + language.get('click_amount') + ' : ' + downloadAmount + '</span>';
                $jq('#downloadRatioSpan').html(downloadAmountHtml);
                //渠道下载占比
                layui.businessStatistics.initReport(d.data.channel_download_ratio_tag, d.data.channel_download_ratio.list, chartIds.channelDownloadChart);
                var channelDownloadAmount = 0;
                var channelDownloadAmountHtml = '';
                $jq.each(d.data.channel_download_ratio.list, function (index, item) {
                    channelDownloadAmount += Number(item.value);
                    // channelDownloadAmountHtml += '<span>' + item.name + ':' + item.value + '</span><br/>';
                });
                channelDownloadAmountHtml += '<span>' + language.get('download_amount') + ' : ' + channelDownloadAmount + '</span>';
                $jq('#channelDownloadRatioSpan').html(channelDownloadAmountHtml);
                //注册占比
                layui.businessStatistics.initReport(d.data.reg_ratio_tag, d.data.reg_ratio.list, chartIds.regChart);
                var regAmount = 0;
                var regAmountHtml = "";
                $jq.each(d.data.reg_ratio.list, function (index, item) {
                    regAmount += Number(item.value);
                    // regAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';
                });
                regAmountHtml += '<span>' + language.get('download_amount') + ' : ' + regAmount + '</span>';
                $jq('#regRatioSpan').html(regAmountHtml);
                //渠道注册占比
                layui.businessStatistics.initReport(d.data.channel_reg_ratio_tag, d.data.channel_reg_ratio.list, chartIds.channelRegChart);
                var channelRegAmount = 0;
                var channelRegAmountHtml = '';
                $jq.each(d.data.channel_reg_ratio.list, function (index, item) {
                    channelRegAmount += Number(item.value);
                    // channelRegAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';
                });
                channelRegAmountHtml += '<span>' + language.get('reg_amount') + ' : ' + channelRegAmount + '</span>';
                $jq('#channelRegRatioSpan').html(channelRegAmountHtml);
                //申请占比
                layui.businessStatistics.initReport(d.data.apply_ratio_tag, d.data.apply_ratio.list, chartIds.applyChart);
                var applyAmount = 0;
                var applyAmountHtml = '';
                $jq.each(d.data.apply_ratio.list, function (index, item) {
                    applyAmount += Number(item.value);
                    // applyAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';
                });
                applyAmountHtml += '<span>' + language.get('reg_amount') + ' : ' + applyAmount + '</span>';
                $jq('#applyRatioSpan').html(applyAmountHtml);
                //渠道申请占比
                layui.businessStatistics.initReport(d.data.channel_apply_ratio_tag, d.data.channel_apply_reg_ratio.list, chartIds.channelApplyChart);
                var channelApplyAmount = 0;
                var channelApplyAmountHtml = '';
                $jq.each(d.data.channel_apply_reg_ratio.list, function (index, item) {
                    channelApplyAmount += Number(item.value);
                    // channelApplyAmountHtml += '<span>' + item.name + ' : ' + item.value + '</span><br/>';
                });
                channelApplyAmountHtml += '<span>' + language.get('all_apply_amount') + ' : ' + channelApplyAmount + '</span>';
                $jq('#channelApplyRatioSpan').html(channelApplyAmountHtml);
            });
        }

        //初始化报表
        , initReport: function (title, data, chartId) {
            var legend = [];
            $jq.each(data, function (i, item) {
                // legend.push(item.name);
                var main = item.name + " : " + item.value;
                legend.push(main);
                item.name = main;
            })
            var option = {
                color: ['#a1e9d9', '#59c8e2', '#bce7dd', '#a1e9d9', '#07dcb3', '#2ccebb', '#4ac2c2', '#60a2b0', '#73b2cb', '#88aacf'
                    , '#9eb1e3', '#a69ed6', '#b897da', '#9e8bc9', '#c3ccd1', '#a4a4a4', '#baa39b', '#6f7074', '#5e717c', '#2e4553'],
//        				color : ['#0bc07d','#e7e7dd','#dceeea','#bce7dd','#a1e9d9','#07dcb3','#37e09e','#5fd5a7','#6cb196','#5d907d'
//        					,'#9cc2bb','#b3c1c0','#ada7b4','#beb7c6','#d4c8d6','#a4a4a4','#baa39b','#6f7074','#5e717c','#2e4553'],
//    			color : ['#2fad66','#18a48b','#9cd9ef','#8aa8ca','#7397a6','#d7bfdf','#9e8bc9','#7f608a','#a68bb0','#c78bc9','#c7c0c3','#ebccd8','#f9a7c7','#fbc3a9','#e2bba8','#fee163','#fbe5be','#fbe5be','#ffd892','#83c6aa','#6e9b88'],
//    				color : ['#2fad66','#9cd9ef','#f9a7c7','#d7bfdf','#ffd892','#a68bb0','#18a48b','#8aa8ca','#fbc3a9','#e2bba8','#fbd5e3','#9e8bc9','#83c6aa'],
                title: {
                    text: title,
                    x: 'center',
                    textStyle: {
                        color: '#464646',
                        fontWeight: 'lighter',
                        fontFamily: 'MicrosoftJhengHei',
                        fontSize: 20
                    }
                },
                tooltip: {
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
                series: [
                    {
                        name: language.get('ratio'),
                        type: 'pie',
                        radius: '45%',
                        center: ['55%', '50%'],
                        data: data,
                        itemStyle: {
                            normal: {
                                label: {
                                    show: true,
                                    // formatter: '{b} : {c} ({d}%)'
                                    formatter: '{d}%'
                                },
                                labelLine: {show: true}
                            }
                        }
                    }
                ]
            }
            chartId.setOption(option, true);
        }


        /**
         * Echart 隐藏
         */
        , echart_hidden: function () {
            $jq('#Channel_list').hide();
            $jq('#channel_hidden').hide();
            $jq('#channel_show').show();
        }

        /**
         * Echart 展示
         */
        , echart_show: function () {
            $jq('#Channel_list').show();
            $jq('#channel_hidden').show();
            $jq('#channel_show').hide();
            var date = $jq("#date").val();
            var company_id = $jq('#company_id').val();
            // this.getChannelData(1, 20,date,company_id);
            layui.businessStatistics.getChannelData(1, 20, date, company_id);
        }


        /**
         * 获取每个渠道相关的信息
         */
        , getChannelData: function (curr, limit, date, company_id) {
            var sendDatas = {};
            sendDatas.date = date;
            sendDatas.company_id = company_id;
            if (!date || date == null) {
                sendDatas.date = $jq('#date').val();
            }
            if (!company_id || company_id == null) {
                sendDatas.company_id = $jq('#company_id').val();
            }
            // $jq.post(basePath + 'admin/echart/get_channel_data_list?page=' + curr + '&limit=' + limit, sendDatas, function (data) {
            $jq.post(basePath + 'admin/Echart/get_channel_data_list', sendDatas, function (data) {
                if(!ajaxCall(data)){
                    layui.businessStatistics.echart_hidden();
                    return ;
                }
                layui.businessStatistics.initChannelMainTable(data);
            });
        }

        /**
         * 初始化表格
         */
        , initChannelMainTable: function (data) {
            var field = data.data.field;
            var data = data.data;
            var clickSum = 0;      //点击
            var downloadSum = 0;   //下载
            var registerSum = 0;   //注册
            var orderSum = 0;      //下单
            var download_click_rateSum = "0%";      // 下载/点击转化率
            var register_download_rateSum = "0%";   // 注册/下载转化率
            var register_order_rateSum = "0%";      // 下单/注册转化率
            var order_click_rateSum = "0%";         // 下单/点击转化率
            var order_download_rateSum = "0%";      // 下单/下载转化率
          
            if (data.list.length >= 0 && data.list !== null) {
                $jq.each(data.list, function (index, item) {
                    data.list[index].download_click_rate = item.download_click_rate + "%";                // 下载/点击转化率
                    data.list[index].register_download_rate = item.register_download_rate + "%";          // 注册/下载转化率
                    data.list[index].order_register_rate = item.order_register_rate + "%";                // 下单/注册转化率
                    data.list[index].order_click_rate = item.order_click_rate + "%";                      // 下单/点击转化率
                    data.list[index].order_download_rate = item.order_download_rate + "%";                // 下单/下载转化率

                    clickSum += parseFloat(item.click);
                    downloadSum += parseFloat(item.download);
                    registerSum += parseFloat(item.register);
                    orderSum += parseFloat(item.apply_order);

                });

                if( downloadSum !== 0  && clickSum !== 0){  // 下载/点击转化率
                     download_click_rateSum = ((downloadSum/clickSum)*100).toFixed(2) +"%";
                }
                if(registerSum !== 0 && downloadSum !== 0){  // 注册/下载转化率
                     register_download_rateSum = ((registerSum/downloadSum)*100).toFixed(2) +"%";
                }
                if(orderSum !== 0 && registerSum !== 0){  // 下单/注册转化率
                     register_order_rateSum = ((orderSum/registerSum)*100).toFixed(2) +"%";
                }
                if(orderSum !== 0 && clickSum !== 0){  // 下单/点击转化率
                     order_click_rateSum = ((orderSum/clickSum)*100).toFixed(2) +"%";
                }
                if(orderSum !== 0 && downloadSum !== 0){  // 下单/下载转化率
                     order_download_rateSum = ((orderSum/downloadSum)*100).toFixed(2) +"%";
                }

                var  limit = data.list.length;
                var initIndex = 0;
                table.render({
                    elem: '#ChannelMainTable',
                    data: data.list,
                    limit: limit,
                    cols: [[
                        {field: 'num', title: 'ID',templet:function(d){
                                  var size = 0;
                                    ++initIndex;
                                  return  size+initIndex;
                        }},
                        {field: 'name', title: field.name},
                        {field: 'click', title: field.click},
                        {field: 'download', title: field.download},
                        {field: 'register', title: field.register},
                        {field: 'apply_order', title: field.apply_order},
                        {field: 'download_click_rate', title: field.download_click_rate},
                        {field: 'register_download_rate', title: field.register_download_rate},
                        {field: 'order_register_rate', title: field.order_register_rate},
                        {field: 'order_click_rate', title: field.order_click_rate},
                        {field: 'order_download_rate', title: field.order_download_rate}
                    ]],
                    id: 'ChannelMainTable',
                    page: false,
                    done: function (res, curr, count) {
                        var allTableHead = $jq('.layui-table-cell span');//所有表头
                        allTableHead.each(function (index, item) {
                            item.parentElement.title = item.textContent;
                        })
                    }
                });

                // //执行重载
                // //完整功能
                // var canFlush = false;
                // laypage.render({
                //     elem: 'ChannelMainPage'
                //     , count: data.page.count
                //     , curr: data.page.page
                //     , prev: '<em><</em>'
                //     , next: '<em>></em>'
                //     , limit: data.page.limit
                //     , limits: [20, 50, 100]
                //     , layout: ['count', 'prev', 'page', 'next', 'limit', 'limits', 'skip']
                //     , jump: function (obj) {
                //         if (canFlush) {
                //             layui.businessStatistical.getChannelData(obj.curr, obj.limit);
                //         } else {
                //             canFlush = true;
                //         }
                //
                //     }
                // });

                if (data.list.length > 0) {
                    var tr = '<tr class=\"finance-sum-tr\"><td class=\"td_bg\" style="text-align: center;font-weight:bold">' + layui.language.get('sum') + '</td> <td>' + "--" + '</td> <td>' + clickSum + '</td><td >' + downloadSum + '</td> <td>' + registerSum + '</td> <td>' + orderSum + '</td><td>' + download_click_rateSum + '</td><td>' + register_download_rateSum + '</td> <td>' + register_order_rateSum + ' </td><td>' + order_click_rateSum + '</td><td>' + order_download_rateSum + '</td></tr>';
                    $jq(".layui-table-body .layui-table").append(tr);
                }
            }


        }
    }

    //输出test接口
    exports('businessStatistics', obj);
});  


