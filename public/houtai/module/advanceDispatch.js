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
            layui.language.render('assistCollector');
            layui.form.render();
            //初始化公司和催收员 下拉框
            layui.use('col_common',function(){
                layui.col_common.initMain();
            });
            //初始化筛选条件信息
            obj.initData();
            //初始化表格
            obj.getData(1,20);

        },
        /**
         * 初始化信息
         */
        initData : function(){
            // 初始化时间框
            layui.laydate.render({
                elem: '#date'
                ,range: true
                ,min: '2018-01-01'
                ,max: '2100-12-31'
                ,done : function(value){
                    var sendData ={};
                    sendData.date = value;
                    sendData.company_id = $jq('#company_id').val();
                    sendData.admin_id = $jq('#admin_id').val();
                    sendData.search_string = $jq('#search_string').val();
                    $jq.post(basePath + 'admin/Collection/advance_case_list',sendData,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.advanceDispatch.initViewTable(data.data);
                    })
                }
            });
            var roleType = dataStore.get('collection_role_type');
            if(roleType == '4' || roleType == '5' || roleType == '6') {
                //显示催收员分配和自由分配按钮（贷后主管才有权限）
                // $jq('#Distribution').show();$jq('#freeDistribution').show();
                $jq('#hid_fenpei').show();
            }

        },


        /**
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit,obj){
            if(obj){
                $jq('#order_field').val(obj.field);
                $jq('#order_sort').val(obj.type);
            }
            var sendData = $jq('#advanceDispatchForm').serialize();
            $jq.post(basePath + 'admin/Collection/advance_case_list?page='+curr+'&limit='+limit,sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.advanceDispatch.initViewTable(data.data,obj);
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
                elem : '#advanceDispatchListTable',
                data: data.list,
                limit: data.page.limit,
                cols : [[
                        {type:'checkbox',width:'3%'},
                        {field: 'num',title: 'ID',width:'7%',templet:function(d){
                            var size = data.page.limit;
                            var cur = data.page.page;
                            ++initIndex;
                            return (cur-1)*size+initIndex;
                        }},
                        {field:'order_no', title: layui.language.get('common_order'),width:'16%',templet:function(d){
                              return '<a class="td_a" href="javascript:layui.col_common.goColDetail(\''+d.order_no+'\',false,170)" style="color:#4ACE9B;">'+d.order_no+'</a>';
                        }},
                        {field: 'real_name', title: field.real_name,width:'15%'},
                        {field: 'phone', title: field.phone,width:'12%'},
                        {field: 'repay_amount', title: field.repay_amount,width:'15%'},
                        {field: 'due_time', title: field.due_time,width:'16%',sort:true},
                        {field: 'case_follow_name', title: field.case_follow_name,align:'center',width:'16%'},
                    ]]
                ,id : 'advanceDispatchListTable'
                ,page : false
                ,done :function(res,curr,count){
                    if(objData){
                        $jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
                    }
                }
            });

            //执行重载
            //完整功能
            var canFlush = false;
            laypage.render({
                elem: 'advanceDispatchListPage'
                ,count: data.page.count
                ,curr:  data.page.page
          	    ,prev: '<em><</em>'
              	,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count', 'prev', 'page', 'next','limit','limits' , 'skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.advanceDispatch.getData(obj.curr,obj.limit,objData);
                    }else {
                        canFlush=true;
                    }

                }
            });

        },

        // 增加背景色 (弃用)
        addBackGround :function(data){
            var list = data;
            if(!list || list.length<1) return;
            for(var index=0; index<list.length; index++) {
                var item = list[index];

                if(!item) continue;
                layui.use('verify_common',function(){
                    var now = layui.verify_common.getDay(0,"-");
                    tqsanday = layui.verify_common.getDay(3,"-");

                    if( now<=item.due_time  &&  item.due_time <= tqsanday ){
                        var obj = $jq('#advanceDispatchListTable').next();
                        var objTable = obj.find('div.layui-table-body table');
                        objTable.find('tr[data-index="'+index+'"]').css({'background-color':'#FFFAFA'});
                    }
                });

            }

        },






        /**
         * 分配催收员页面 -->> 弹出窗口
         */
        Distribution :function(){
            var global_role_type = dataStore.get('global_role_type');
            if(global_role_type == '6'){
                layer.open({
                    title: layui.language.get('prompt'),           //'提示'
                    content: layui.language.get('no_promission_to_operate'),
                    btn : [ layui.language.get('certainly')]//按钮
                });
                return false;
            }

            var checkList = layui.table.checkStatus('advanceDispatchListTable');
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
                    content:layui.language.get('sel_order_no'),  //'请选择订单号'
                    btn : [ layui.language.get('certainly')]		//按钮
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
                   content:html,
               });
               var field =data.data.field;
                table.render({
                    elem : '#CollectorList',
                    data: data.data.list,
                    autoHeight:true,
                    limit:limit_num,
                    cols : [[
                        {type:'checkbox',width:80},
                        {field: 'admin_id', title: field.list_no,width: 80},
                        {field: 'real_name', title: field.real_name},
                        {field: 'role_name', title: field.role_name},
                        {field: 'has_case', title: field.has_case},
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
            // layer.confirm(layui.language.get('confirm_freedistr'), { title: [layui.language.get('kindly_reminder'),'text-align:center;']},function() {
            layer.confirm(layui.language.get('confirm_freedistr'), {
                title: [layui.language.get('kindly_reminder'),'text-align:center;'],
                btn : [ layui.language.get('certainly'), layui.language.get('cancel') ]//按钮
            },function() {
                //提早派单
                var sendData = {};
                sendData.type = 1;
                $jq.post(basePath + 'admin/Assignment/collection_automatic', sendData, function (data) {
                    if (!ajaxCall(data)) {
                        return;
                    }
                    //跳转页数以及每页显示的条数
                    var arr = $jq('#advanceDispatchListPage .layui-laypage-curr');
                    var cur = 1;
                    if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                        cur = $jq(arr.children()[1]).text();
                    }
                    var limits = $jq("#advanceDispatchListPage .layui-laypage-limits select option:selected").val();

                    if (data.code == 200) {
                        layer.msg(layui.language.get('distribution_suc'), {
                            icon: 1,
                            time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                        }, function(){
                            layer.closeAll();
                            layui.advanceDispatch.getData(cur, limits);
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
            dataStore.set('collection_url','postloan/advanceDispatch.html');
            dataStore.set('show_fy_record_btn',false);
            $jq.post(basePath+'admin/Collection/details',{'order_no':order_no},function(data) {
                if(!ajaxCall(data)) {
                    return ;
                }

                layui.index.loadHtml('postloan/collectionDetail.html','#main');
            })

        },

        /**
         * 处理监听事件
         */
        tool : function() {
            //表格监听事件
            table.on('sort(advanceDispatchListTableEvent)',function(obj){
                layui.advanceDispatch.getData(1,20,obj);
            })

            //表单监听事件
            form.on('submit(assistCollectorForm)', function (data) {   //分配催收员们提交
                var checkList = layui.table.checkStatus('advanceDispatchListTable');
                var ids = new Array();
                var sendData = {};
                for (var index in checkList.data) {
                    var item = checkList.data[index];
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
                        content: layui.language.get('please_sel')+layui.language.get('sel_csy'),   //'请选择催收员'
                        btn : [ layui.language.get('certainly') ]//按钮
                    });
                    return false;
                }
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
                        var arr = $jq('#advanceDispatchListPage .layui-laypage-curr');
                        var cur = 1;
                        if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                            cur = $jq(arr.children()[1]).text();
                        }
                        var limits = $jq("#advanceDispatchListPage .layui-laypage-limits select option:selected").val();

                        if(data.code== 200){
                            layer.msg(layui.language.get('distribution_suc'), {    //分配成功
                                icon: 1,
                                time: 500
                            }, function(){
                                layer.closeAll();
                                layui.advanceDispatch.getData(cur,limits);
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


    };


    //输出 advanceDispatch 接口
    exports('advanceDispatch', obj);
});