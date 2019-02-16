layui.define(function (exports) {
    var  $jq = layui.jquery,form = layui.form;

    var obj ={
         initView : function(){
                //初始化
                layui.language.render('y-page-user');
                // 初始化渠道商列表
                layui.channelStatistics.channelList();
                //初始化报表
                this.initEcharts();
                //初始化按钮的css
                layui.channelStatistics.initBtnCss();
         }

        /**
         * 初始化渠道列表
         */
        ,channelList : function(){
            var roleType = dataStore.get('collection_role_type');
            // if(roleType == '6' || roleType == ''){  //如果角色为平台超级管理员或者运营部门 即可查看
            if(roleType == '6'){  //如果角色为平台超级管理员或者运营部门 即可查看
                $jq('#hid_channel').show();
                $jq.post(basePath+'admin/Base/get_company_statistical', '',function (data) {
                    if (data.code == 200) {
                        var channelHtml = '<option value="">'+ layui.language.get('sel_channel') +'</option>';
                        // $jq.each(data.data, function (i, item) {
                        //     channelHtml += '<option value="' + item.cp_id + '">' + item.cp_name + '</option>';
                        // })
                        // channelHtml +='<option value="1">' + "渠道1" + '</option>'+'<option value="2">' +"渠道2" + '</option>'+'<option value="3">' + "渠道3"+ '</option>';
                        $jq('select[name="channel_id"]').html(channelHtml);
                        layui.form.render('select');
                    }
                });

             }
         }



        /**
         * 图表展示
         */
        ,initEcharts : function() {

            // 渠道统计表
            layui.channelStatistics.initChannel('1',"");  //默认自然月显示

            //初始化时间
            // layui.channelStatistics.initDate()

        }

        /**
         * 获取渠道信息
         * @param type
         * @param value
         */
        ,initChannel : function(type,value){

            if(type == '2'){  //周
                selectThisWeek('.channel-btn-left','.channel-btn-left span','.channel-btn-right','.channel-btn-right span');
            }else{   //月
                selectThisMonth('.channel-btn-left','.channel-btn-left span','.channel-btn-right','.channel-btn-right span');
            }
            this.initChannelChart(type,"");
        }

        ,initChannelChart : function(type,value){
            $jq.post(basePath + 'admin/Echart/count_view',{type:'1',date:" ",company_id:" "},function(data) {
                layui.channelStatistics.initChannelChartDatas(data);
            })
        }
        ,initChannelChartDatas : function(data){
            // "order_count":"订单量","reg_user_count":"注册量","due_order_count":"逾期订单量","death_order_count":"坏账量
            var field = data.data.field;
            var click_count = [];
            var down_install_count = [];
            var register_count = [];
            var apply_order_count = [];
            var dates = [];
            var toSeries = [];
            var toData = {};
            toData['order_count'] = click_count;
            toData['reg_user_count'] = down_install_count;
            toData['due_order_count'] = register_count;
            toData['death_order_count'] = apply_order_count;
            // var legend = [field.order_count,field.reg_user_count,field.due_order_count,field.death_order_count];
            //标题名称 点击量 、下载安装量 、注册量 、申请订单量
            var legend = [layui.language.get('click_num'),layui.language.get('down_install_num'),layui.language.get('register_num'),layui.language.get('apply_order_num')];
            var formData = ['order_count','reg_user_count','due_order_count','death_order_count'];
            $jq.each(data.data.data_list,function(i,item){
                click_count.push(item.order_count);
                down_install_count.push(item.reg_user_count);
                register_count.push(item.due_order_count);
                apply_order_count.push(item.death_order_count);
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
            var channelCharts = echarts.init(document.getElementById('channelStatistics'));
            channelCharts.setOption(option,true);

        }

        /**
         * 初始化 CSS 样式
         */
        ,initBtnCss : function(){
            $jq('.channel-btn-right').css('background-color','#A1EAD9');
            $jq('.channel-btn-right span').css('color','#FFFFFF');

        }


        /**
         * 监听事件
         */
        ,tool : function(){
            //监听表单公司下拉框事件
            form.on('select(channelId)',function(data){
                layui.channelStatistics.initEcharts();
                form.render('select');
            });

        }

    }

    //输出 statisticsReport 接口
    exports('channelStatistics', obj);
});