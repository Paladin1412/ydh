layui.define(['form','table','layer','laytpl','jquery','laydate','laypage','language'],function (exports) {
    var form    = layui.form,
        table    = layui.table,
        layer    = layui.layer,
        $jq      = layui.jquery,
        laytpl   = layui.laytpl,
        laydate  = layui.laydate,
        laypage  = layui.laypage;

    var obj = {
            initView: function () {
                layui.language.render('paramPage');
                layui.language.render('closedListTool');
                layui.form.render();
                //初始化公司和催收员 下拉框
                layui.use('col_common',function(){
                    layui.col_common.initMain();
                });
                //初始化筛选条件信息
                obj.initData();
                //初始化表格
                obj.getData(1,50);

            },

            /**
             * 初始化筛选条件信息
             */
            initData : function(){
                // 初始话还款日期框
                laydate.render({
                    elem: '#date'
                    ,range: true
                    ,min: '2018-01-01'
                    ,max: '2100-12-31'
                    ,done : function(value){
                        var sendData ={};
                        sendData.date = value;
                        sendData.due_date = $jq('#due_date').val();
                        sendData.company_id = $jq('#company_id').val();
                        sendData.admin_id = $jq('#admin_id').val();
                        sendData.search_string = $jq('#search_string').val();
                        $jq.post(basePath + 'admin/Collection/closed_list',sendData,function(data){
                            if(!ajaxCall(data)){
                                return;
                            }
                            layui.Repayments.initViewTable(data.data);
                        })
                    }
                });

                // 初始化应还款日期框
                laydate.render({
                    elem: '#due_date'
                    ,range: true
                    ,min: '2018-01-01'
                    ,max: '2100-12-31'
                    ,done : function(value){
                        var sendData ={};
                        sendData.date = $jq('#date').val();
                        sendData.due_date = value ;
                        sendData.company_id = $jq('#company_id').val();
                        sendData.admin_id = $jq('#admin_id').val();
                        sendData.collection_feedback = $jq('#collection_feedback').val();
                        sendData.search_string = $jq('#search_string').val();
                        $jq.post(basePath + 'admin/Collection/closed_list',sendData,function(data){
                            if(!ajaxCall(data)){
                                return;
                            }
                            layui.Repayments.initViewTable(data.data);
                        })
                    }
                });

            },

            /**
             * 第一次进页面的初始化表格
             */
            getData : function(curr,limit,obj){
                if(obj){
                    $jq('#order_field').val(obj.field);
                    $jq('#order_sort').val(obj.type);
                }
                var sendData = $jq('#closed_search_form').serialize();
                $jq.post(basePath + 'admin/Collection/closed_list?page='+curr+'&limit='+limit,sendData,function(data){

                    if(!ajaxCall(data)) {
                        return;
                    }
                    layui.Repayments.initViewTable(data.data,obj);
                })
            },

            /**
             * 初始化表格
             */
            initViewTable : function(data,objData){
                var field = data.field;
                if(data.list == null  ||  data.list == undefined ){
                    data.list = [];
                }
                var initIndex =0;
                table.render({
                    elem : '#closedListTable',
                    data: data.list,
                    limit: data.page.limit,
                    cols : [[
                            {field: 'num',title: 'ID',width:'6%',templet:function(d){
                                var size = data.page.limit;
                                var cur = data.page.page;
                                ++initIndex;
                                return (cur-1)*size+initIndex;
                            }}
                            ,{field:'order_no', title: layui.language.get('common_order'),width:'15.8%',templet:function(d){
                                  return '<a class="td_a" href="javascript:layui.col_common.goColDetail(\''+d.order_no+'\',false,200)" style="color:#4ACE9B;">'+d.order_no+'</a>';
                            }},
                            {field: 'real_name', title: field.real_name,width:'7.5%'},
                            {field: 'phone', title: field.phone,width:'11.6%'},
                            {field: 'due_day', title: field.due_day,width:'8%',sort:true},
                            {field: 'repay_amount', title: field.repay_amount,width:'8%',},
                            {field: 'paid_amount', title: field.paid_amount,width:'8%',},
                            {field: 'success_time', title: field.success_time,width:'8%',sort:true},
                            {field: 'due_time', title: layui.language.get('yhk_date'),width:'8%',sort:true},
                            {field: 'case_follow_name', title: field.case_follow_name,align:'center',width:'6.5%'},
                            {title: layui.language.get('caozuo'),toolbar: '#closedListTool'}, //这里的toolbar值是模板元素的选择器
                        ]]
                    ,id : 'closedListTable'
                    ,page : false
                    ,done: function(res, curr, count){
                        if(objData){
                            $jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
                        }
                        var allTableHead = $jq('.layui-table-cell span');//所有表头
                        allTableHead.each(function(index,item){
                            item.parentElement.title = item.textContent;
                        })
                    }
                });

                //执行重载
                //完整功能
                var canFlush = false;
                laypage.render({
                    elem: 'closedListPage'
                    ,count: data.page.count
                    ,curr:  data.page.page
              	    ,prev: '<em><</em>'
                    ,next: '<em>></em>'
                    ,limit: data.page.limit
                    ,limits:[20,50,100]
                    ,layout: ['count', 'prev', 'page', 'next', 'limit','limits','skip']
                    ,jump: function(obj){
                        if(canFlush) {
                            layui.Repayments.getData(obj.curr,obj.limit);
                        }else {
                            canFlush=true;
                        }
                    }
                });

            },

            /**
             * 跟进记录
             */
            followRecord :function(order_no){
                $jq.post(basePath + 'admin/Collection/closed_record',{'order_no':order_no},function(data) {
                    var field =data.data.field.follow;
                    var html = derateApprove.innerHTML;
                    var limit_num = 0;
                    if(data.data.follow){
                        limit_num = data.data.follow.length;
                    }
                    if(!data.data.follow){
                      data.data.follow =[];
                    }


                    layer.open({
                        type: 1,
                        area: ['80%', '500px'],
                        title: [layui.language.get('follow_record'), 'text-align:center;font-size:18px;'],
                        success: function (index, layero) {
                            $jq(':focus').blur();
                        },
                        content:html,
                    });

                    table.render({
                        elem: '#closed_follow_list'
                        ,data:data.data.follow
                        ,limit:limit_num
                        ,autoHeight:true
                        ,cols: [[ //表头
                            {field: 'operator_time', title:field.operator_time,sort:true}
                            // ,{field: 'follow_type', title: field.follow_type,}
                            ,{field: 'target', title: field.target,}
                            ,{field: 'target_name', title:field.target_name}
                            ,{field: 'contact_phone', title: field.contact_phone}
                            ,{field: 'contact_state', title: field.contact_state}
                            ,{field: 'collection_feedback', title: field.collection_feedback}
                            ,{field: 'content', title: field.content}
                            ,{field: 'operator_name', title: field.operator_name}
                        ]]
                    });

                });
            },

            /**
             * 跳转查看催收详情页面
             */
            collectionDetail:function(order_no){
                dataStore.set('collectionDetail_order_no',order_no);
                dataStore.set('collection_url','postloan/Repayments.html');
                dataStore.set('show_fy_record_btn',false);
                $jq.post(basePath+'admin/Collection/details',{'order_no':order_no},function(data) {
                    if(!ajaxCall(data)) {
                        return ;
                    }

                    layui.index.loadHtml('postloan/collectionDetail.html', '#main');
                })
            },



            /**
             * 处理监听事件
             */
            tool : function() {
                //表格监听事件
                table.on('tool(closedListTableEvent)',function(obj) {
                    var data = obj.data;//获取一行的数据
                    if(obj.event == 'followRecord') {       //查看跟进记录
                        layui.Repayments.followRecord(data.order_no);
                    }
                });
                table.on('sort(closedListTableEvent)',function(obj){
                    layui.Repayments.getData(1,50,obj);
                })

            }

    };


    //输出test接口
    exports('Repayments', obj);
});