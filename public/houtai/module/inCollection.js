layui.define(function (exports) {
    var form    = layui.form,
        table    = layui.table,
        $jq      = layui.jquery,
        laypage  = layui.laypage;

    var obj = {
        initView: function () {
            layui.language.render('paramPage');
            layui.language.render('assistCollector');
            layui.form.render();
            //初始化筛选条件信息
            this.initData();
            //初始化公司和催收员 下拉框
            layui.use('col_common',function(){
                layui.col_common.initMain();
            });
            var roleType = dataStore.get('collection_role_type');
            if( roleType == '4' || roleType == '5' || roleType == '6') {
                //显示催收员分配和自由分配按钮（贷后主管才有权限）
                 $jq('#hid_fenpei').show();
            }
            // 初始化表格
            this.getData(1,50);
        }

        /**
        * 初始化筛选条件信息
        */
        ,initData:function() {

            layui.laydate.render({  // 应还日期
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

                    $jq.post(basePath + 'admin/Collection/cllection_going',sendData,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.inCollection.initViewTable(data.data);
                    })
                }
            });


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
                    $jq.post(basePath + 'admin/Collection/cllection_going',sendData,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.inCollection.initViewTable(data.data);
                    })
                }
            });


            //初始化催收反馈
            $jq.post(basePath+'admin/Collection/collection_feed', function (data) {
                if(data.code == 200){
                    var feed_data =data.data.follow_feed.value;
                    var follow_feedHtml = '<option value="">'+layui.language.get('sel_col_feed')+'</option>';
                    for(var item in feed_data ){
                        follow_feedHtml += '<option value="' + item + '">' + feed_data[item] + '</option>';
                    }
                    $jq('select[name="collection_feedback"]').html(follow_feedHtml);
                    form.render('select');
                }
            });


            //初始化订单逾期属性
            $jq.post(basePath+'admin/base/get_collection_s', function (data) {
                if(data.code == 200){
                    var overValHtml = '<option value="">'+layui.language.get('over_val')+'</option>';
                    $jq.each(data.data, function (i, item) {
                        overValHtml += '<option value="'+ i +'">' + item + '</option>';
                    })

                    $jq('select[name="s"]').html(overValHtml);
                    form.render('select');
                }
            });

        }

        /**
         * 第一次进页面的初始化表格
         */
        ,getData: function(curr,limit,obj) {
            if(obj){
                $jq('#order_field').val(obj.field);
                $jq('#order_sort').val(obj.type);
            }
            var sendDatas = $jq('#inCollectionForm').serialize();
            $jq.post(basePath+'admin/Collection/cllection_going?page='+curr+'&limit='+limit,sendDatas,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.inCollection.initViewTable(data.data,obj);
            });
        }

        /**
        * 初始化表格
        */
        ,initViewTable: function(data,objData) {
            var field = data.field;
            var initIndex =0;
            if(data.list == null  ||  data.list == undefined ){
                data.list = [];
            }
            //方法级渲染
            table.render({
                elem: '#inColListTable'
                ,data:data.list
                ,limit: data.page.limit
                ,cols: [[
                    {type:'checkbox',width:'3%'}
                    ,{field: 'num',title: 'ID',width:'6%',templet:function(d){
                            var size = data.page.limit;
                            var cur = data.page.page;
                            ++initIndex;
                            return (cur-1)*size+initIndex;
                        }}
                    ,{field:'order_no', title: layui.language.get('common_order'),width:'15.6%',templet:function(d){
                          return '<a class="td_a" href="javascript:layui.col_common.goColDetail(\''+d.order_no+'\',true,'+d.order_no+')" style="color:#4ACE9B;">'+d.order_no+'</a>';
                     }}
                    ,{field:'real_name', title: field.real_name,width:'8.3%'}
                    ,{field:'phone', title:field.phone,width:'11.8%'}
                    ,{field:'due_day', title:field.due_day,width:'7.7%',sort:true}
                    ,{field:'repay_amount', title:field.repay_amount,width:'8.3%'}
                    ,{field:'due_time', title: field.due_time,width:'10.5%',sort:true}
                    ,{field:'followup_feed', title: field.followup_feed,width:'8.8%'}
                    ,{field:'operator_time', title: field.operator_time,width:'10.5%'}
                    ,{field:'case_follow_name', title: field.case_follow_name}
                ]]
                ,id: 'inColListTable'
                ,page: false
                ,done: function(res, curr, count){
                    var data = res.data;
                    //排序图表显示
                    if(objData){
                        $jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
                    }
                    layui.inCollection.addBackGround(data);
                }
            });

            //执行重载
            //完整功能
            var canFlush = false;
            laypage.render({
                 elem: 'inColListPage'
                ,count: data.page.count
                ,curr:  data.page.page
          	    ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count','prev','page','next','limit','limits','skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.inCollection.getData(obj.curr,obj.limit,objData);
                    }else {
                        canFlush=true;
                    }

                }
            });

        },

        // 增加背景色
        addBackGround :function(data){
            //方法一
            //离还款日期的前两天添加背景色
            var dueDays = $jq('.layui-table-body td[data-field="due_day"] .layui-table-cell');
            dueDays.each(function(index,item){
                if(parseFloat($jq(this).text()) < 1){
                    $jq($jq(this).parents('tr')).css({'background-color':'#FFF5EE'});
                }
            });

            //方法二
           /* var list = data;
            if(!list || list.length<1) return;
            for(var index=0; index<list.length; index++) {
                var item = list[index];
                if(!item) continue;
                // layui.use('verify_common',function(){
                //     var now = layui.verify_common.getDay(0,"-");
                //     var qiantian = layui.verify_common.getDay(-2,"-");
                //     if( (item.due_time >= qiantian)   && (item.due_time  < now)){
                //         var obj = $jq('#inColListTable').next();
                //         var objTable = obj.find('div.layui-table-body table');
                //         objTable.find('tr[data-index="'+index+'"]').css({'background-color':'#FFF5EE'});
                //     }
                // });

                if( item.due_day < 1 ){
                      var obj = $jq('#inColListTable').next();
                      var objTable = obj.find('div.layui-table-body table');
                      objTable.find('tr[data-index="'+index+'"]').css({'background-color':'#FFF5EE'});
                }

            }*/

        },


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

            var checkList = layui.table.checkStatus('inColListTable');
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
                    content:layui.language.get('sel_order_no'),   //'请选择订单号'
                    btn : [ layui.language.get('certainly') ]//按钮
                });
                return false;
            }

            $jq.post(basePath+'admin/Assignment/collection_admin_list',null,function(data){
                if(!ajaxCall(data)) {
                    return false;
                }
                var html= assistCollector.innerHTML;
                var limit_num = 0;
                if(data){
                    limit_num = data.data.list.length;
                }
                layer.open({
                    type:1,
                    area:['900px','500px'],
                    title:[layui.language.get('fenpei_csy'),'text-align:center;font-size:18px;'],
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
                    content:html,
                });
                var field =data.data.field;
                table.render({
                    elem : '#CollectorList',
                    data: data.data.list,
                    limit:limit_num,
                    autoHeight:true,
                    cols : [[
                        {type:'checkbox',width:80},
                        // {title:'--',width:80,templet:function(d){
                        //         return '<input type="radio" class="layui-input" name="collector"  value="'+d.admin_id+'" />';
                        //     }},
                        {field: 'admin_id', title: field.list_no,width: 80},
                        {field: 'real_name', title: field.real_name },
                        {field: 'role_name', title: field.role_name },
                        {field: 'has_case', title: field.has_case},
                        // {field: 'can_case', title:field.can_case}
                    ]],
                    id: 'CollectorListID',
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
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
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
                    var arr = $jq('#inColListPage .layui-laypage-curr');
                    var cur = 1;
                    if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                        cur = $jq(arr.children()[1]).text();
                    }
                    var limits = $jq("#inColListPage .layui-laypage-limits select option:selected").val();

                    if (data.code == 200) {
                        layer.msg(layui.language.get('distribution_suc'), {
                            icon: 1,
                            time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                        }, function(){
                            layer.closeAll();
                            layui.inCollection.getData(cur, limits);
                        });

                    }

                });
                return false;
            });
        },



        /**
         * 催收详情
         */
        collectionDetail : function(order_no){
            dataStore.set('collectionDetail_order_no',order_no);
            dataStore.set('collection_url','postloan/inCollection.html');
            dataStore.set('collection_view','inCollection');
            dataStore.set('show_fy_record_btn',true);
            $jq.post(basePath+'admin/Collection/details',{'order_no':order_no},function(data) {
                if(!ajaxCall(data)) {
                    return ;
                }

                layui.index.loadHtml('postloan/collectionDetail.html','#main');
            })

        },


        tool : function(){

            //排序触发事件
            table.on('sort(inColListTableEvent)',function(obj){
                //离还款日期的前两天添加背景色
                var dueDays = $jq('.layui-table-body td[data-field="due_day"] .layui-table-cell');
                dueDays.each(function(index,item){
                    if(parseFloat($jq(this).text()) < 1){
                        $jq($jq(this).parents('tr')).css({'background-color':'#FFF5EE'});
                    }
                });
                //排序重新加载获取最新数据
                layui.inCollection.getData(1,50,obj);

            });

            //表单监听事件
            form.on('submit(assistCollectorForm)', function (data) {   //分配催收员们提交
                var checkOrderList = layui.table.checkStatus('inColListTable');
                var ids = new Array();
                var sendData = {};
                for (var index in checkOrderList.data) {
                    var item = checkOrderList.data[index];
                    // if(item.order_status !== 200){
                    ids.push(item['order_no']);
                    // }
                }
                var ids_str = ids.join("-");
                sendData.order_arr = ids_str;
                // 获取当前选择的催收员id
              /*  var collector_id  = $jq('input:radio[name="collector"]:checked').val();
                if(collector_id  == null  || collector_id == undefined ) {
                    layer.open({
                        title:layui.language.get('prompt')           //'提示'
                        ,content:layui.language.get('sel_csy') //'请选择催收员'
                    });
                    return false;
                }
                   sendData.admin_id = collector_id;
                */
                var admin_ids = new Array();
                var collector_list  = layui.table.checkStatus('CollectorListID');
                for (var index in collector_list.data) {
                    var item = collector_list.data[index];
                    admin_ids.push(item['admin_id']);
                }
                var admin_id_arr = admin_ids.join("-");
                sendData.admin_arr = admin_id_arr;
                if(admin_ids.length <= 0 ){
                    layer.open({
                        title: layui.language.get('prompt'),           //'提示'
                        success: function (index, layero) {
                            $jq(':focus').blur();
                        },
                        content: layui.language.get('please_sel')+layui.language.get('sel_csy'),   //'请选择催收员'
                        btn : [ layui.language.get('certainly') ]//按钮
                    });
                    return false;
                }
                //判断分配的催收员数不能大于订单数
                // if(admin_ids.length > ids.length ){
                //     layer.open({
                //         title: layui.language.get('prompt'),           //'提示'
                //         content: layui.language.get('csynum_lt_ordernum'),   //'催收员数不能大于订单数'
                //         btn : [ layui.language.get('certainly') ]//按钮
                //     });
                //     return false;
                // }

                //防止多次触发提交
                $jq('#distri_submit').attr('disabled',true);
                $jq('#distri_submit').removeClass('y-btn-green');
                $jq('#distri_submit').addClass('layui-btn-disabled');
                $jq.post(basePath+'admin/Assignment/collection_manual',sendData,function(data){
                    if(!ajaxCall(data)) {
                        //如果提交不成功，重新开始触发提交按钮
                        $jq('#distri_submits').attr('disabled',false);
                        $jq('#distri_submits').addClass('y-btn-green');
                        $jq('#distri_submits').removeClass('layui-btn-disabled');
                        return false;
                    }
                    //跳转页数以及每页显示的条数
                    var arr = $jq('#inColListPage .layui-laypage-curr');
                    var cur = 1;
                    if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                        cur = $jq(arr.children()[1]).text();
                    }
                    var limits = $jq("#inColListPage .layui-laypage-limits select option:selected").val();
                    if(data.code== 200){
                        layer.msg(layui.language.get('distribution_suc'), {    //分配成功
                            icon: 1,
                            time: 500
                        }, function(){
                            layer.closeAll();
                            layui.inCollection.getData(cur,limits);
                        });
                    }else {
                        layer.msg(data.message,{
                            icon: 2,
                            time: 500
                        });

                    }
                });
                return false;
            });


         }

    }


    //输出test接口
    exports('inCollection', obj);
});  


