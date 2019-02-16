layui.define(['form','table','layer','laytpl','jquery','laydate','laypage','language','col_common'],function (exports) {
    var form    = layui.form,
        table    = layui.table,
        layer    = layui.layer,
        $jq      = layui.jquery,
        laypage  = layui.laypage;

    var myData = null;
    var obj = {
        initView: function () {


            layui.language.render('paramPage');
            layui.language.render('assistCollectorAll');
            layui.form.render();
            //初始化筛选条件信息
            this.initData();
            //初始化按钮的css
            this.initBtnCss(dataStore.get('current_lan'));
            //初始化公司和催收员 下拉框
            layui.use('col_common',function(){
                layui.col_common.initMain();
            });
            var roleType = dataStore.get('collection_role_type');
                // if( roleType == '4' || roleType == '5' || roleType == '6') {
                //     //显示催收员分配和自由分配按钮（贷后主管才有权限）
                //     // $jq('#Distribution').show();$jq('#freeDistribution').show();
                //     $jq('#hid_fenpei').show();
                // }
            if( roleType == '4' || roleType == '5') {  //催收主管、公司管理员展示报表  -- 平台级超级管理员不展示报表
                    $jq('#echart_show').show();
                }
            //初始化图表数据
            // obj.initEcharts();

            //初始化表格
            this.getData(1,50);
        },

        /**
         * 初始化筛选条件信息
         */
        initData : function(){
            //初始化数据表格的时间框
            layui.laydate.render({   //订单走势
                elem: '#ddzs_date'
                , range: true
                , min: '2018-01-01'
                , max: '2100-12-31'
                , done: function (value, date, endDate) {
                    var  sendData = {};
                         sendData.date = value;
                    var cuiDingDanZouShi = echarts.init(document.getElementById('cuiDingDanZouShi'));
                    $jq.post(basePath + 'admin/Assignment/order_view', sendData, function(data) {
                        if ( data.code ==500) {
                            $jq('#cuiDingDanZouShi_div').hide();
                            return;
                        }
                        layui.allCollection.cuiDingDanZouShi(data.data,cuiDingDanZouShi);
                    });
                }
            });

            layui.laydate.render({  //费用减免
                elem: '#fyjm_date'
                , range: true
                , min: '2018-01-01'
                , max: '2100-12-31'
                , done: function (value, date, endDate) {
                    var  sendData3 = {};
                         sendData3.date = value;
                    $jq.post(basePath + 'admin/Assignment/reduction_view',sendData3, function(data) {
                        if (data.code ==500) {
                            $jq('#feiYongJianMian_div').hide();
                            return;
                        }
                        var feiYongJianMian = echarts.init(document.getElementById('feiYongJianMian'));
                        layui.allCollection.feiYongJianMian(data.data,feiYongJianMian);
                    });
                }
            });

            layui.laydate.render({ //订单还款走势
                elem: '#ddrp_date'
                , range: true
                , min: '2018-01-01'
                , max: '2100-12-31'
                , done: function (value, date, endDate) {
                    var  sendData4 = {};
                    sendData4.date = value;
                    $jq.post(basePath + 'admin/Assignment/repay_view',sendData4,function(data){
                        if( data.code ==500 ){
                            $jq('#cuiDingDanRepay_div').hide();
                            return;
                        }
                        //初始化订单走势报表
                        var cuiDingDanRepay = echarts.init(document.getElementById('cuiDingDanRepay'));
                        layui.allCollection.cuiDingDanRepay(data.data,cuiDingDanRepay);
                    })

                }
            })

            layui.laydate.render({   //承诺还款统计
                elem: '#promise_time'
                , range: true
                , min: '2018-01-01'
                , max: '2100-12-31'
                , done: function (value, date, endDate) {
                    var  sendData = {};
                    sendData.promise_time = value;
                    $jq.post(basePath + 'admin/Collection/collection_log', sendData, function(data) {
                        layui.allCollection.initRepayStatisticsTable(data.data);
                    });
                }
            });

            layui.laydate.render({  //应还日期
                elem: '#date'
                ,range: true
                ,min: '2018-01-01'
                ,max: '2100-12-31'
                ,done : function(value){
                    var sendData ={};
                    sendData.date = value;
                    sendData.company_id = $jq('#company_id').val();
                    sendData.admin_id = $jq('#admin_id').val();
                    sendData.collection_feedback = $jq('#collection_feedback').val();
                    sendData.search_string = $jq('#search_string').val();
                    sendData.follow_date = $jq('#follow_date').val();

                    $jq.post(basePath + 'admin/Collection/all',sendData,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.allCollection.initViewTable(data.data);
                    })

                }});

            layui.laydate.render({  // 催收日期
                 elem: '#follow_date'
                ,range: true
                ,min: '2018-01-01'
                ,max: '2100-12-31'
                ,done : function(value){
                    var sendData ={};
                    sendData.follow_date = value;
                    sendData.company_id = $jq('#company_id').val();
                    sendData.admin_id = $jq('#admin_id').val();
                    sendData.collection_feedback = $jq('#collection_feedback').val();
                    sendData.search_string = $jq('#search_string').val();
                    sendData.date = $jq('#date').val();
                    $jq.post(basePath + 'admin/Collection/all',sendData,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.allCollection.initViewTable(data.data);
                    })
                }
            });

            // 初始化催收反馈
            $jq.post(basePath+'admin/Collection/collection_feed', function (data) {
                if(data.code == 200){
                    var feed_data =data.data.follow_feed.value;
                    var follow_feedHtml = '<option value="">'+ layui.language.get('sel_col_feed') +'</option>';
                    for(var item in feed_data ){
                        follow_feedHtml += '<option value="' + item + '">' + feed_data[item] + '</option>';
                    }
                    $jq('select[name="collection_feedback"]').html(follow_feedHtml);
                    form.render('select');
                }
            });

        },



        /**
         * Echart数据展示
         */
        //隐藏图表
        echart_hidden : function(){
            $jq('#echarts').hide();
            $jq('#echart_hidden').hide();
            $jq('#echart_show').show();
        },

        //显示图表
        echart_show : function(){
            $jq('#echarts').show();
            $jq('#echart_hidden').show();
            $jq('#echart_show').hide();
            layui.allCollection.initEcharts();
            layui.allCollection.initRepayStatistics();
        },
        
        /**
         * 获取承诺还款数据
         */
        initRepayStatistics : function(){
        	var sendDatas = {};
            sendDatas.return_date = $jq('#promise_time').val();
            sendDatas.company_id = $jq('#company_id').val();
            $jq.post(basePath + 'admin/Collection/collection_log', sendDatas, function (data) {
            	layui.allCollection.initRepayStatisticsTable(data.data);
            })
        },
        
        /**
         * 初始化承诺还款表格
         */
        initRepayStatisticsTable : function(data){
        	 var field = data.field;
        	 $jq.each(data.list,function(index,item){
        		 data.list[index].rate = item.rate + '%';
        	 })
        	 table.render({
                 elem : '#PromiseRepaymentTable',
                 data: data.list,
                 autoHeight: true,
                 cols : [[
                     {field: 'real_name', title: field.real_name,width:"10%"},//催收员
                     {field: 'order_cnt_sum', title: field.order_cnt_sum},//承诺还款笔数
                     {field: 'order_ontime_sum', title: field.order_ontime_sum},//已还款笔数
                     {field: 'order_undue_sum', title: field.order_undue_sum},//未还款笔数
                     {field: 'order_due_sum', title: field.order_due_sum},//超出承诺期限
                     {field: 'rate', title: field.rate},//回收率
                 ]],
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
         * 图表数据展示
         */
        initEcharts : function(){
            var chartIds ={};
            //催收考核报表
            var cuiShouKaoHeId = echarts.init(document.getElementById('cuiShouKaoHe'));
                 chartIds['cuiDingKaoHe']  = cuiShouKaoHeId;
            //催收订单走势报表
            var cuiDingDanZouShiId = echarts.init(document.getElementById('cuiDingDanZouShi'));
                 chartIds['cuiDingDanZouShi']  = cuiDingDanZouShiId;
            //费用减免金额
            var feiYongJianMianId  = echarts.init(document.getElementById('feiYongJianMian'));          // chartIds['cuiDingDanRepay'] = echarts.init(document.getElementById('cuiDingDanRepay'));     //订单还款走势表
                chartIds['feiYongJianMian']  = feiYongJianMianId;
            //订单还款走势表
            var cuiDingDanRepayId  = echarts.init(document.getElementById('cuiDingDanRepay'));          // chartIds['cuiDingDanRepay'] = echarts.init(document.getElementById('cuiDingDanRepay'));     //订单还款走势表
            chartIds['cuiDingDanRepay']  = cuiDingDanRepayId;

            layui.allCollection.initReport(chartIds);
            //图表自适应
            window.addEventListener("resize", function () {
                  cuiShouKaoHeId.resize();
                  cuiDingDanZouShiId.resize();
                  feiYongJianMianId.resize();
                  cuiDingDanRepayId.resize();
                });
        },

        //请求报表数据
        initReport : function(chartIds){
            //初始化催收考核报表
            $jq.post(basePath + 'admin/Assignment/collector_view', null, function (datas) {
                    if( datas.code == 500) {
                         $jq('#cuiShouKaoHe_div').hide();
                                return false;
                         }
                    layui.allCollection.cuiShouKaoHe(datas.data,chartIds.cuiDingKaoHe);
             });
            //初始化催收订单走势报表
            layui.allCollection.initCuiDingDanZouShi(chartIds.cuiDingDanZouShi);
            //初始化费用减免金额报表
            layui.allCollection.initFeiYongJianMian(chartIds.feiYongJianMian);
            //初始化订单还款走势表
            layui.allCollection.initCuiDingDanRepay(chartIds.cuiDingDanRepay);
        },


            /**
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit,obj){
            if(obj){
                $jq('#order_field').val(obj.field);
                $jq('#order_sort').val(obj.type);
            }
            var sendDatas = $jq('#allCollectionForm').serialize();
            $jq.post(basePath+'admin/Collection/all?page='+curr+'&limit='+limit,sendDatas,function(data){
                if(!ajaxCall(data)) {
                    return ;
                }
                layui.allCollection.initViewTable(data.data,obj);
            });
        },


        /**
         * 初始化表格
         */
        initViewTable : function(data,objData){
            var field = data.field;
            myData = data.list;
            if(data.list == null  ||  data.list == undefined ){
                data.list = [];
            }
            var initIndex =0;
            table.render({
                elem: '#allColListTable',//初始化到哪个位置
                data: data.list,
                limit: data.page.limit,
                cols: [[
                     // {type:'checkbox',width:'3%'}
                    {field: 'num',title: 'ID',width:'6%',templet:function(d){
                            var size = data.page.limit;
                            var cur = data.page.page;
                            ++initIndex;
                            return (cur-1)*size+initIndex;
                        }}
                    ,{field:'order_no', title:layui.language.get('common_order'),width:'15.6%',templet:function(d){
                            return '<a class="td_a" href="javascript:layui.col_common.goColDetail(\''+d.order_no+'\',true,\''+d.order_status+'\')" style="color:#4ACE9B;">'+d.order_no+'</a>';
                     }}
                    ,{field:'real_name', title: field.real_name,width:'8.3%'}
                    ,{field:'phone', title:field.phone,width:'11.8%'}
                    // ,{field:'due_day', title: layui.language.get('common_due_day'),width:'7%',sort:true}
                    ,{field:'due_day', title: field.due_day,width:'7%',sort:true}
                    ,{field:'repay_amount', title:field.repay_amount,width:'8.3%'}
                    ,{field:'due_time', title: field.due_time,width:'7%',sort:true}
                    ,{field:'case_status', title:field.collection_status,width:'10%'}
                    ,{field:'followup_feed', title: field.followup_feed,width:'8.8%'}
                    ,{field:'follow_time', title: field.follow_time,width:'7%'}
                    ,{field:'case_follow_name', title: field.case_follow_name}
                ]]
                ,id: 'allColListTable'
                ,page: false
                ,done: function(res, curr, count){
                        var data = res.data;
                            //离还款日期的前两天添加背景色
                            var dueDays = $jq('.layui-table-body td[data-field="due_day"] .layui-table-cell');
                            dueDays.each(function(index,item){
                                if(parseFloat($jq(this).text()) < 1){
                                    $jq($jq(this).parents('tr')).css({'background-color':'#FFF5EE'});
                                }
                            });
                            //排序图表显示
                            if(objData){
                                $jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
                            }
                            // layui.allCollection.addBackGround(data);
                            // layui.allCollection.removeCheckbox(data);
                        }

            });
            //执行重载
            //完整功能
            var canFlush = false;
            laypage.render({
                elem: 'allColListPage'
                ,count: data.page.count
                ,curr:  data.page.page
          	    ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count', 'prev', 'page', 'next','limit','limits','skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.allCollection.getData(obj.curr,obj.limit,objData);
                    }else {
                        canFlush=true;
                    }

                }
            });

        },

        //离还款日期的前两天添加背景色
        addBackGround:function(data) {
             var list = data;
             if(!list || list.length<1) return;
             for(var index=0; index<list.length; index++) {
                 var item = list[index];
                 if(!item) continue;
                 // layui.use('verify_common',function(){
                 //    var now = layui.verify_common.getDay(0,"-");
                 //    var qiantian = layui.verify_common.getDay(-2,"-");
                 //     if( (item.due_time >= qiantian)   && (item.due_time  < now)){
                 //         var obj = $jq('#allColListTable').next();
                 //         var objTable = obj.find('div.layui-table-body table');
                 //         objTable.find('tr[data-index="'+index+'"]').css({'background-color':'#FFF5EE'});
                 //     }
                 // });
                 if( item.due_day < 1 ){

                     var obj = $jq('#allColListTable').next();
                     var objTable = obj.find('div.layui-table-body table');
                     objTable.find('tr[data-index="'+index+'"]').css({'background-color':'#FFF5EE'});
                 }

             }
        },

        // removeCheckbox :function (data){
            // var allck = true;
            // for (var item in data_check) {   //全选按钮不可点击
            //     console.log(data_check[item]);
            //     if (data[item].order_status == 200) {//关键点如果data中order_status包含200那么就不能全选
            //
            //         console.log(data_check[item].order_no );
            //         allck = false;
            //         break;
            //     }
            // }
            // if (!allck) {
            //     $jq(".layui-table-header").find("input[name = 'layTableCheckbox'][lay-filter='layTableAllChoose']").each(function () {
            //         $jq(this).attr("disabled", 'disabled').next().removeClass("layui-form-checked");
            //         form.render('checkbox');
            //     });
            // }
            // var i = 0;
            // $jq(".layui-table-body.layui-table-main").find("input[name='layTableCheckbox']").each(function () {
            //     if (data[i].order_status == 200) {
            //         // form.render('checkbox');
            //         $jq (this).attr("disabled", 'disabled').removeAttr("checked");
            //         $jq(this).attr("disabled", 'disabled').next().removeClass("layui-form-checked");
            //
            //     }
            //     i++;
            // });
        //     var list = data;
        //     for(var index=0; index<list.length; index++) {
        //                 var item = list[index];
        //                 if(!item) continue;
        //         $jq(".layui-table-body.layui-table-main").find("input[name='layTableCheckbox']").each(function () {
        //
        //             if (item.order_status == 200) {
        //                 form.render('checkbox');
        //                 $jq (this).attr("disabled", 'disabled').removeAttr("checked");
        //                 $jq(this).attr("disabled", 'disabled').next().removeClass("layui-form-checked");
        //
        //             }
        //
        //         });
        //
        //     }
        //
        // },

        //已还款的订单不显示 checkbox
        removeCheckbox:function(data){
            var list = myData;
            if(!list || list.length<1) return;
            for(var index=0; index<list.length; index++) {
                var item = list[index];
                if(!item) continue;

                if(item.order_status == '200') {
                    var obj = $jq('#allColListTable').next();
                    var objTable = obj.find('div.layui-table-body table');
                    var objTr = objTable.find('tr[data-index="'+index+'"]');
                    objTr.find('td[data-field="'+0+'"] .layui-form-checkbox').html( '-' );

                }

            }
        },


        /**
         *  催收考核报表
         * @param _data
         * @param chartId
         * @returns {boolean}
         */
        cuiShouKaoHe:function(_data,chartId){
            if(_data == null  ||  _data == undefined){
                return false;
            }
            var list = null;
            if(_data.list&&_data.list.length>0){
                list = _data.list;
            }else {
                list = [];
            }
            var allOrder_Field = _data.field.order_count;               //全部催收
            var sjOrder_Field = layui.language.get('sj_cs_num');     //title  实际催收
            var allOrderArr=[];
            var sjOrderArr=[];
            var userArr = [];
            for(var index in list){
                allOrderArr[index] =  list[index].all_case;   //全部订单
                sjOrderArr[index] = list[index].has_case;     //实际催收订单
                userArr[index] = list[index].real_name;       //催收员名称
            }

            var	option = {
                color: ['#a1e9d9','#59c8e2'],
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
		        legend : {
					orient : 'horizontal',
					data : [allOrder_Field, sjOrder_Field],
					x : 'center',
					bottom : 20,
					itemWidth: 10,
					itemHeight: 10,
					itemGap: 10
				},
		        grid: {
		        	top:'7%',
		            left: '4%',
		            right: '5%',
		            bottom: '23%',
		            containLabel: true
		        },
                xAxis: [
                    {
                    	type : 'category',
		                data : userArr,
		                axisTick: {
		                    alignWithLabel: true
		                }
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
                        name: allOrder_Field,
                        type: 'bar',
                        barGap: 0,
                        barMaxWidth :50,
                        data: allOrderArr
                    },
                    {
                        name: sjOrder_Field,
                        type: 'bar',
                        barMaxWidth :50,
                        data: sjOrderArr
                    }
                ]
            };
            chartId.setOption(option);
        },


        /**
         * 催收订单走势报表
         * @param chartId
         */
        initCuiDingDanZouShi:function(chartId) {
            $jq.post(basePath + 'admin/Assignment/order_view', {'type':'1'}, function(data) {
                if ( data.code ==500) {
                    $jq('#cuiDingDanZouShi_div').hide();
                    return;
                }
                layui.allCollection.cuiDingDanZouShi(data.data,chartId);
            });
        },
        //以日、周、月 为维度查询订单走势报表
        getDingDanZouShiDates : function(type){
    		if(type == '1'){
    			selectThisWeek('.allcollection-btn-left','.allcollection-btn-left span','.allcollection-btn-right','.allcollection-btn-right span');
    		}else{
    			selectThisMonth('.allcollection-btn-left','.allcollection-btn-left span','.allcollection-btn-right','.allcollection-btn-right span');
    		}
            $jq.post(basePath + 'admin/Assignment/order_view',{'type':type},function(data){
                if(!ajaxCall(data)){
                    return false;
                }
                //初始化订单走势报表
                var  cuiDingDanZouShiId = echarts.init(document.getElementById('cuiDingDanZouShi'));
                layui.allCollection.cuiDingDanZouShi(data.data,cuiDingDanZouShiId);
            })
        },
        cuiDingDanZouShi:function(_data,chartId) {
            if(_data == null  ||  _data == undefined)  return;
            var list = null;
            if(_data.list&&_data.list.length>0){
                list = _data.list;
            }else {
                list = [];
            }
            var allOrder_Field = _data.field.order_count;
            var realOrder_Field = _data.field.collection_count;
            var allOrderArr=[];
            var realOrderArr=[];
            var dateArr = [];
            for(var index in list){  // update number
                allOrderArr[index] = list[index].order_count;
                realOrderArr[index] = list[index].collection_count;
                dateArr[index] = list[index].date;
            }
            option = {
                color: ['#a1e9d9','#59c8e2'],
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
					orient : 'horizontal',
					data : [allOrder_Field,realOrder_Field],
					bottom : 20,
					x : 'center',
					itemWidth: 10,
					itemHeight: 10,
					itemGap: 10
                },
                grid: {
                	top: '7%',
                    left: '4%',
                    right: '5%',
                    bottom: '23%',
                    containLabel: true
                },

                xAxis: {
	                type : 'category',
	                data : dateArr,
	                boundaryGap : false,
	                axisTick: {
	                    alignWithLabel: true
	                },
                    axisLabel: {
                        rotate: 40,
                        interval: 0
                    }
                },
                yAxis: {
                    type: 'value',
	                splitLine:{
	                	show:true,
	                	lineStyle:{
	                		color:'#e9ebf3',
	                		type:'dashed'
	                	}
	                }
                },
                dataZoom:[
                    {
                        type: 'slider',
                        show: true,
                        xAxisIndex: [0],
                        handleSize: 20,//滑动条的 左右2个滑动条的大小
                        height: 20,//组件高度
                        left: 70, //左边的距离
                        right: 75,//右边的距离
                        bottom: -3,//右边的距离
                        // start: 60,                                //数据窗口范围的起始百分比,表示30%
                        // end: 100                                  //数据窗口范围的结束百分比,表示70%
                    }
                ],
                series: [
                    {
                        name:allOrder_Field,
                        type:'line',
                        data:allOrderArr,
                        symbol:'circle',
                        symbolSize:8
                    },
                    {
                        name:realOrder_Field,
                        type:'line',
                        data:realOrderArr,
                        symbol:'circle',
                        symbolSize:8
                    }
                ]
            };
            chartId.setOption(option,true);

        },



        /**
         * 费用减免金额报表
         * @param value
         * @param chartId
         */
        initFeiYongJianMian:function(chartId) {

                $jq.post(basePath + 'admin/Assignment/reduction_view',{"type":"1"}, function(data) {
                    if (data.code ==500) {
                        $jq('#feiYongJianMian_div').hide();
                        return;
                    }
                    layui.allCollection.feiYongJianMian(data.data,chartId);
                });
            },
        //以日、周、月 为维度查询费用减免金额报表
        getFeiYongJianMianDates :function(type){
    		if(type == '1'){
    			selectThisWeek('.fyjm-btn-left','.fyjm-btn-left span','.fyjm-btn-right','.fyjm-btn-right span');
    		}else{
    			selectThisMonth('.fyjm-btn-left','.fyjm-btn-left span','.fyjm-btn-right','.fyjm-btn-right span');
    		}
            $jq.post(basePath + 'admin/Assignment/reduction_view',{'type':type},function(data){
                if(!ajaxCall(data)){
                    return;
                }
                //初始化减免金额报表
                var  feiYongJianMian = echarts.init(document.getElementById('feiYongJianMian'));
                layui.allCollection.feiYongJianMian(data.data,feiYongJianMian);
            })

        },
        feiYongJianMian:function(_data,chartId) {
            if(_data == null  ||  _data == undefined)  return;
            var list = null;
                if( _data.list && _data.list.length>0){
                    list = _data.list;
                }else {
                    list = [];
                }
                var title = _data.field.title;
                var allFee_Field =_data.field.all_fee;
                var allNum_Field = _data.field.all_num;
                var allFeeArr=[];
                var allNumArr=[];
                var dateArr = [];
                for(var index in list){  // update number
                    allFeeArr[index] =  list[index].all_fee;
                    allNumArr[index] =  list[index].all_num;
                    dateArr[index]   =  list[index].date;
                }
                option = {
                    color: ['#a1e9d9','#c98dc1'],
                    tooltip: {
                        trigger: 'axis'
                    },
                    legend: {
    					orient : 'horizontal',
    					// data : [allFee_Field],
    					data : [allNum_Field,allFee_Field],
    					bottom : 20,
    					x : 'center',
    					itemWidth: 10,
    					itemHeight: 10,
    					itemGap: 10
                    },
                    grid: {
                    	top: '7%',
                        left: '4%',
                        right: '5%',
                        bottom: '23%',
                        containLabel: true
                    },

                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: dateArr,
		                axisTick: {
		                    alignWithLabel: true
		                },
                        axisLabel: {
                            rotate: 50,
                            interval: 0
                        }
                    },
                    yAxis: {
                        type: 'value',
		                splitLine:{
		                	show:true,
		                	lineStyle:{
		                		color:'#e9ebf3',
		                		type:'dashed'
		                	}
		                }
                    },
                    dataZoom:[
                        {
                            type: 'slider',
                            show: true,
                            xAxisIndex: [0],
                            handleSize: 20,//滑动条的 左右2个滑动条的大小
                            height: 20,
                            left: 70,
                            right: 75,
                            bottom: -3,
                            // start: 60,                                //数据窗口范围的起始百分比,表示30%
                            // end: 100                                  //数据窗口范围的结束百分比,表示70%
                        }
                    ],
                    series: [
                        {
                            name:allNum_Field,
                            type:'line',
                            data:allNumArr,
                            symbol:'circle'
                        },
                        {
                            name:allFee_Field,
                            type:'line',
                            data:allFeeArr,
                            symbol:'circle'
                        }
                    ]
                };

                chartId.setOption(option);
        },


        /**
         * 还款订单走势报表
         * @param value
         */
        initCuiDingDanRepay:function(chartId) {
            $jq.post(basePath + 'admin/Assignment/repay_view', {"type":"1"}, function(data) {
                if ( data.code ==500) {
                    $jq('#cuiDingDanRepay_div').hide();
                    return false;
                }
                layui.allCollection.cuiDingDanRepay(data.data,chartId);
            });
        },
        //以日、周、月 为维度查询订单还款走势报表
        getDingDanRepayDates : function(type){
            if(type == '1'){
                selectThisWeek('.repay-btn-left','.repay-btn-left span','.repay-btn-right','.repay-btn-right span');
            }else{
                selectThisMonth('.repay-btn-left','.repay-btn-left span','.repay-btn-right','.repay-btn-right span');
            }
            $jq.post(basePath + 'admin/Assignment/repay_view',{'type':type},function(data){
                if(!ajaxCall(data)){
                    if ( data.code ==500) {
                        $jq('#cuiDingDanRepay_div').hide();
                    }
                    return false;
                }
                //初始化订单走势报表
                var cuiDingDanRepay = echarts.init(document.getElementById('cuiDingDanRepay'));
                layui.allCollection.cuiDingDanRepay(data.data,cuiDingDanRepay);
            })
        },
        //还款订单走势报表
        cuiDingDanRepay:function(_data,chartId) {
            if(_data == null  ||  _data == undefined)  return;
            var list = null;
            if(_data.list&&_data.list.length>0){
                list = _data.list;
            }else {
                list = [];
            }
            var title = _data.field.title;
            var cur_yinghuankuan_Field = _data.field.cur_yinghuankuan_count; //当天到期应还款订单数
            var cur_yihuankuan_Field = _data.field.cur_yihuankuan_count; //当天到期且已经还款订单数
            var all_huan_Field = _data.field.all_huan_count; //历史所有已经还款数
            var all_yinghuan_Field = _data.field.all_yinghuan_count; //历史所以应还款数

            var cur_yinghuankuan_arr=[];
            var cur_yihuankuan_arr=[];
            var all_huan_arr=[];
            var all_yinghuan_arr=[];
            var dateArr = [];
            for(var index in list){  // update number
                cur_yinghuankuan_arr[index] = list[index].cur_yinghuankuan_count;
                cur_yihuankuan_arr[index] = list[index].cur_yihuankuan_count;
                all_huan_arr[index] = list[index].all_huan_count;
                all_yinghuan_arr[index] = list[index].all_yinghuan_count;
                dateArr[index] = list[index].date;
            }
            option = {
//                title: {
//                    text: title,
//    				textStyle: {
//    					color: '#464646',
//    					fontWeight: 'lighter',
//    					fontFamily: 'MicrosoftJhengHei',
//    					fontSize: 20
//    				}
//                },
                // color: ['#58c9db', '#9bead7'],
                color: ['#a1e9d9','#59c8e2', '#c98dc1', '#08AD1F'],
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    orient : 'horizontal',
                    data : [cur_yinghuankuan_Field,cur_yihuankuan_Field,all_huan_Field,all_yinghuan_Field],
                    bottom : 20,
                    x : 'center',
                    itemWidth: 10,
                    itemHeight: 10,
                    itemGap: 10
                },
                grid: {
                    top: '7%',
                    left: '4%',
                    right: '5%',
                    bottom: '23%',
                    containLabel: true
                },

                xAxis: {
                    type : 'category',
                    data : dateArr,
                    boundaryGap : false,
                    axisTick: {
                        alignWithLabel: true
                    },
                    axisLabel: {
                        rotate: 40,
                        interval: 0
                    }
                },
                yAxis: {
                    type: 'value',
                    splitLine:{
                        show:true,
                        lineStyle:{
                            color:'#e9ebf3',
                            type:'dashed'
                        }
                    }
                },
                dataZoom:[
                    {
                        type: 'slider',
                        show: true,
                        xAxisIndex: [0],
                        handleSize: 20,//滑动条的 左右2个滑动条的大小
                        height: 20,//组件高度
                        left: 70, //左边的距离
                        right: 75,//右边的距离
                        bottom: -3,//右边的距离
                        // start: 60,                                //数据窗口范围的起始百分比,表示30%
                        // end: 100                                  //数据窗口范围的结束百分比,表示70%
                    }
                ],
                series: [
                    {
                        name:cur_yinghuankuan_Field,
                        type:'line',
                        data:cur_yinghuankuan_arr,
                        symbol:'circle',
                        symbolSize:8
                    },
                    {
                        name:cur_yihuankuan_Field,
                        type:'line',
                        data:cur_yihuankuan_arr,
                        symbol:'circle',
                        symbolSize:8
                    },
                    {
                        name:all_huan_Field,
                        type:'line',
                        data:all_huan_arr,
                        symbol:'circle',
                        symbolSize:8
                    },
                    {
                        name:all_yinghuan_Field,
                        type:'line',
                        data:all_yinghuan_arr,
                        symbol:'circle',
                        symbolSize:8
                    }
                ]
            };
            chartId.setOption(option,true);

        },

        // echarts end

        /**
         * 分配催收员--->>>新增弹出框
         */
        Distribution :function(){
         	var global_role_type = dataStore.get('global_role_type');
        	if(global_role_type == '6'){
        		layer.open({
                    title: layui.language.get('prompt'),           //'提示'
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
      			    content: layui.language.get('no_promission_to_operate'),
                    btn : [ layui.language.get('certainly')]//按钮
      			}); 
        		return false;
        	}

            var checkList = layui.table.checkStatus('allColListTable');
            var ids = new Array();
            for (var index in checkList.data) {
                var item = checkList.data[index];
                if(item.order_status !== '200'){
                    ids.push(item['order_no']);
                }
            }
            if( !ids.length  || ids.length == 0 ) {
                layer.open({
                    title: layui.language.get('prompt'),           //'提示'
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
                    content: layui.language.get('sel_order_no'),   //'请选择订单号'
                    btn : [ layui.language.get('certainly') ]//按钮
                });
                return false;
            }

            $jq.post(basePath+'admin/Assignment/collection_admin_list',null,function(data){
                    if(!ajaxCall(data)) {
                        return false;
                    }
                    var html= assistCollectorAll.innerHTML;
                    var limit_num = 0;
                    if(data){
                        limit_num = data.data.list.length;
                    }
                layer.open({
                    type:1,
                    area:['900px','500px'],
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
                    title:[layui.language.get('fenpei_csy'),'text-align:center;font-size:18px;'],
                    content:html,
                });
                var field =data.data.field;
                table.render({
                    elem : '#allCollectorList',
                    data: data.data.list,
                    limit:limit_num,
                    autoHeight:true,
                    cols : [[
                        {title:'--',width:80,templet:function(d){
                                return '<input type="radio" class="layui-input" name="collector"  value="'+d.admin_id+'" />';
                            }},
                        {field: 'admin_id', title: field.list_no,width: 80},
                        {field: 'real_name', title: field.real_name },
                        {field: 'role_name', title: field.role_name },
                        {field: 'has_case', title: field.has_case},
                        // {field: 'can_case', title:field.can_case}
                    ]],
                    id: 'all_col_id',
                    page: false
                });
               form.render();
            });
            return false;
        },

        /**
         * 自动分配催收员
         */
        freeDistribution :function(){
            layer.confirm(layui.language.get('confirm_freedistr'), {
                title: [layui.language.get('kindly_reminder'),'text-align:center;'],
                btn : [ layui.language.get('certainly'), layui.language.get('cancel') ]//按钮
            },function() {
                //逾期派单
                var sendData = {};
                sendData.type = 3;
                $jq.post(basePath + 'admin/Assignment/collection_automatic', sendData, function (data) {
                        if (!ajaxCall(data)) {
                            return false;
                        }
                    //跳转页数以及每页显示的条数
                    var arr = $jq('#allColListPage .layui-laypage-curr');
                    var cur = 1;
                    if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                        cur = $jq(arr.children()[1]).text();
                    }
//                    var limits = $jq("#advanceDispatchListPage .layui-laypage-limits select option:selected").val();
                    var limits = $jq("#allColListPage .layui-laypage-limits select option:selected").val();

                        if (data.code == 200) {
                            layer.msg(layui.language.get('distribution_suc'), {
                                icon: 1,
                                time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                            }, function(){
                                layer.closeAll();
                                layui.allCollection.getData(cur, limits);
                            });

                        }

                });
                return false;
            });
        },

        /**
         * 跳转查看催收详情页面
         */
        collectionDetail:function(order_no){
            dataStore.set('collectionDetail_order_no',order_no);
            dataStore.set('collection_url','postloan/allCollection.html');
            dataStore.set('collection_view','allCollection');
            dataStore.set('show_fy_record_btn',true);
            $jq.post(basePath+'admin/Collection/details',{'order_no':order_no},function(data) {
                if(!ajaxCall(data)) {
                    return ;
                }
                layui.index.loadHtml('postloan/collectionDetail.html','#main');
            })

            // $jq.get('postloan/collectionDetail.html',{order_no:order_no}, function (data) {
            //     $jq(id).html(data);
            // });
        },


        /**
         * 处理监听事件
         */
        tool :function(){
            //监听表格复选框选择
            table.on('checkbox(allColListTableEvent)', function (obj) {
                var checkStatus = table.checkStatus('allColListTable');
                var data_arr = checkStatus.data;
                setTimeout(layui.allCollection.removeCheckbox(data_arr),1000);
            });

            //排序触发事件
            table.on('sort(allColListTableEvent)',function(obj){
                //离还款日期的前两天添加背景色
                var dueDays = $jq('.layui-table-body td[data-field="due_day"] .layui-table-cell');
                dueDays.each(function(index,item){
                    if(parseFloat($jq(this).text()) < 1){
                        $jq($jq(this).parents('tr')).css({'background-color':'#FFF5EE'});
                    }
                });

                //排序重新加载获取最新数据
                layui.allCollection.getData(1,50,obj);

            });


            //表格监听事件
            form.on('submit(assistCollectorAllForm)', function (data) {   //分配催收员提交
                var checkList = layui.table.checkStatus('allColListTable');
                var ids = new Array();
                var sendData = {};
                for (var index in checkList.data) {
                    var item = checkList.data[index];
                    if(item.order_status !== '200'){
                        ids.push(item['order_no']);
                    }

                }
                var ids_str = ids.join("-");
                sendData.order_arr = ids_str;
                // 获取当前选择的催收员id
                var collector_id  = $jq('input:radio[name="collector"]:checked').val();
                if(collector_id  == null  || collector_id == undefined ) {
                    layer.open({
                        title:layui.language.get('prompt')           //'提示'
                        ,success: function (index, layero) {
                                $jq(':focus').blur();
                            }
                        ,content:layui.language.get('sel_csy')   //'请选择催收员'
                    });
                    return false;
                }
                sendData.admin_id = collector_id;
                //防止多次触发提交
                $jq('#distri_submits').attr('disabled',true);
                $jq('#distri_submits').removeClass('y-btn-green');
                $jq('#distri_submits').addClass('layui-btn-disabled');

                $jq.post(basePath+'admin/Assignment/collection_manual',sendData,function(data){
                    if(!ajaxCall(data)) {
                        //如果提交不成功，重新开始触发提交按钮
                        $jq('#distri_submits').attr('disabled',false);
                        $jq('#distri_submits').addClass('y-btn-green');
                        $jq('#distri_submits').removeClass('layui-btn-disabled');
                        return false;
                    }
                    //跳转页数以及每页显示的条数
                    var arr = $jq('#allColListPage .layui-laypage-curr');
                    var cur = 1;
                    if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                        cur = $jq(arr.children()[1]).text();
                    }
                    var limits = $jq("#allColListPage .layui-laypage-limits select option:selected").val();

                    if(data.code== 200){
                        layer.msg(layui.language.get('distribution_suc'), {  //分配成功
                            icon: 1,
                            time: 1000    //1秒关闭（如果不配置，默认是3秒）
                        }, function(){
                            layer.closeAll();
                            layui.allCollection.getData(cur,limits);
                        });
                    }else{
                        layer.msg(data.message, {
                            icon: 2,
                            time: 1000    //1秒关闭（如果不配置，默认是3秒）
                        });

                    }

                });
                return false;
            });

        },

        /**
         * 初始化按钮的Css
         */
        initBtnCss : function(lan){
                $jq('.allcollection-btn-left').css('background-color','#1c3368');
                $jq('.allcollection-btn-left span').css('color','#FFFFFF');
                $jq('.fyjm-btn-left').css('background-color','#1c3368');
                $jq('.fyjm-btn-left span').css('color','#FFFFFF');
                $jq('.repay-btn-left').css('background-color','#1c3368');
                $jq('.repay-btn-left span').css('color','#FFFFFF');
                if(lan == 'cn'){
                    $jq('.echart-btn-show').css('margin-left','38%');
                }else if(lan == 'id'){
                    $jq('.echart-btn-show').css('margin-left','31%');
                }
            }



        };




    //输出 allCollection 接口
    exports('allCollection', obj);

});