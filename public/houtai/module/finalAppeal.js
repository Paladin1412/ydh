layui.define(['verify_common','jquery','table'],function (exports) {
	
	var $jq = layui.jquery;
	var table = layui.table;
	var form = layui.form;

    var obj = {
        initView: function () {
        	layui.use('verify_common',function(){
        		layui.verify_common.initRole();
        	});
            obj.initForm();
            //获取专案标签
            obj.initQuality();
            obj.getData(1,50);
        }
        /**
         * 初始化页面
         */
        ,initForm:function() {
            layui.language.render('page_not_verify');
            layui.language.render('assistXinshen');
            layui.form.render();

         //日期
        var laydate = layui.laydate;
          laydate.render({
            elem: '#not_verify_date'
            ,range: true
            ,min: '2018-01-01'
            ,max: '2100-12-31'
            ,done: function(value){
                var sendData = {};
                sendData.date = value
                sendData.company_id = $jq('#company_id').val();
                sendData.admin_id = $jq('#admin_id').val();
                sendData.search_string = $jq('#search_string').val();

                $jq.post(basePath+'admin/Order/order_todo_end',sendData,function(data){
                    if(!ajaxCall(data)) {
                        return;
                    }
                    layui.finalAppeal.loadData(data.data);
                });
            }
          });
        } // 初始化页面 end


        /**
         * 获取专案标签
         */
        ,initQuality : function(value){
            $jq.post(basePath + 'admin/base/get_order_quality','',function(data){
                if (data.code == 200) {
                    var qualityHtml = '<option value=""></option>';
                    $jq.each(data.data, function (i, item) {
                            qualityHtml += '<option value="'+ i +'">' + item + '</option>';
                    })
                    $jq('select[name="quality"]').html(qualityHtml);
                    layui.form.render('select');
                }
            })
        }



        /**
         * 请求数据
         */
        ,getData: function(curr,limit,obj) {
            var $jq = layui.jquery;
            if(obj){
                $jq('#order_field').val(obj.field);
                $jq('#order_sort').val(obj.type);
            }
            var sendData = $jq('#not_verify_form').serialize();

            if(sendData&&sendData.indexOf('admin_id=-100')>-1){
                sendData=sendData.replace('admin_id=-100','admin_id=');
            }
            if(sendData&&sendData.indexOf('company_id=-100')>-1){
                sendData=sendData.replace('company_id=-100','company_id=');
            }
            $jq.post(basePath+'admin/Order/order_todo_end?page='+curr+'&limit='+limit,sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.finalAppeal.loadData(data.data,obj);
            });
        }

        /**
        * 渲染数据
        */
        ,loadData: function(data,objData) {
            var fieldArr = data.field;

            var table = layui.table;
            var laypage = layui.laypage;
            var initIndex = 0;
              //方法级渲染
              table.render({
                elem: '#not_verify_table'
                ,data:data.list
                ,limit:data.page.limit
                ,cols: [[
                    {type:'checkbox',width:'3%'},
    			{field: 'num',title: 'ID',width:'6%',templet:function(d){
                        var size = data.page.limit;
                        var cur = data.page.page;
                        ++initIndex;
                        return (cur-1)*size+initIndex;
                    }}
                    ,{field:'order_no', title: fieldArr['order_no'],width:'15.4%',  templet:function(d){
                        return '<a class="td_a" href="javascript:layui.verify_common.goDetail(\''+d.order_no+'\',1)">'+d.order_no+'</a>';
                    }
                  }
                  ,{field:'name', title: fieldArr['user_name'],width:'7.75%'}
                 ,{field:'idcode', title: fieldArr['user_card'],width:'9%'}
                 ,{field:'phone', title: fieldArr['user_phone'],width:'11.5%'}
                  ,{field:'application_amount', title: fieldArr['application_amount'],width:'7.8%'}
                  ,{field:'application_term', title: fieldArr['application_term'],width:'7.05%'}
                  ,{field:'add_time', title: fieldArr['create_time'],sort:true,width:'8.5%'}
                   ,{field:'handle_state', title: fieldArr['handle_state'],width:'8.5%'}
                   ,{field:'second_admin', title: "终审人",width:'7.95%'}
                   ,{title: fieldArr['operate'],templet:function(d){
                                return '<button onclick="layui.verify_common.showOrder(\''+d.order_no+'\',\''+d.company_code+'\')" class="layui-btn layui-btn-xs y-btn-red">'+layui.language.get('contract')+'</button>';
                            }
                   }
                ]]
                ,id: 'not_verify_table'
                ,page: false
                ,done: function(res, curr, count){
                    if(objData){
                        $jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
                    }
                      layui.finalAppeal.addBackGround(res.data);
                }
              });

              //执行重载
              //完整功能
              var canFlush = false;
              laypage.render({
                elem: 'not_verify_page'
                ,count: data.page.count
                ,curr: data.page.page
                ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20, 50, 100]
                ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
                ,jump: function(obj){

                    if(canFlush) {
                        layui.finalAppeal.getData(obj.curr,obj.limit,objData);
                    }else {
                        canFlush=true;
                    }

                }
              });

         }


        /**
         * 专案客户 增加浅黄色背景
         * @param data
         * @param quality 标记订单是否属于专案标签 1 是 0 不是
         */
        ,addBackGround :function(data){
            // 方法一
            var quality_order_no_ary =  new Array();
            if(!data || data.length<1) return;
            for(var index=0; index<data.length; index++) {
                var item = data[index];
                if(!item) continue;
                if( item.quality == '1'){  //属于专案标签
                    quality_order_no_ary.push(item.order_no);
                }
            }
            //专案客户 增加浅黄色背景
            var order_no = $jq('.layui-table-body td[data-field="order_no"] .layui-table-cell');
            order_no.each(function(index,item){
                if(quality_order_no_ary.indexOf($jq(this).text()) > -1){
                    // $jq($jq(this).parents('tr')).css({'background-color':'#FFF5EE'});
                    $jq($jq(this).parents('tr')).css({'background-color':'#FFFFCC'});
                }
            });
        }


        /**
         * 自动分配订单
         */
        ,autoFen:function() {
            var $jq = layui.jquery;
            layui.layer.confirm(layui.language.get('confirm_freedistr_approval'), { title: [layui.language.get('kindly_reminder'),'text-align:center;'],success: function (index, layero) {
                    $jq(':focus').blur();
                },},function() {
                $jq.post(basePath+'admin/Order/auto_mode',{'type':2},function(data){
                    if(!ajaxCall(data)) {
                        return false;
                    }
                    var arr = $jq('#not_verify_page .layui-laypage-curr');
                    var cur = 1;
                    if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                        cur = $jq(arr.children()[1]).text();
                    }
                    var limits = $jq("#not_verify_page .layui-laypage-limits select option:selected").val();
                    if (data.code == 200) {
                        layer.msg(layui.language.get('distribution_suc'), {
                            icon: 1,
                            time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                        }, function(){
                            layer.closeAll();
                            layui.finalAppeal.getData(cur, limits);
                        });

                    }
                });
            });
        }


        /**
         * 批量分配订单
         */
        ,piliangFen :function(){
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

            var checkList = layui.table.checkStatus('not_verify_table');
            var ids = new Array();
            for (var index in checkList.data) {
                var item = checkList.data[index];
                ids.push(item['order_no']);
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
            $jq.post(basePath+'admin/Base/handle_user_list',{'admin_class':'7'},function(data){
                if(!ajaxCall(data)) {
                    return false;
                }
                var html= assistXinshen.innerHTML;
                var limit_num = 0;
                if(data){
                    limit_num = data.data.length;
                }
                layer.open({
                    type:1,
                    area:['600px','500px'],
                    title:[layui.language.get('fenpei_xsy'),'text-align:center;font-size:18px;'],
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
                    content:html,
                });
                table.render({
                    elem : '#xisnhenList',
                    data: data.data,
                    limit:limit_num,
                    autoHeight:true,
                    cols : [[
                        {type:'checkbox',width:80},
                        {field: 'admin_id', title: "ID",width: 80},
                        {field: 'real_name', title: layui.language.get('real_name') },
                    ]],
                    id: 'xisnhenID',
                    page: false
                });
            });
            return false;

        }

        /**
         * 批量分配订单提交
         */
        ,doPiliangFen:function() {
            var $jq = layui.jquery;
            var user = $jq('#user4fenpei').val();
            if(!user) {
                return;
            }
            var checkList = layui.table.checkStatus('not_verify_table');

            var ids = '';
            for(var index in checkList.data) {
                var item = checkList.data[index];
                if(index==0){
                    ids = ids+''+item['order_id'];
                } else {
                    ids = ids+'-'+item['order_id'];
                }

            }
            var sendData = {};
            sendData.order_arr = ids;
            sendData.admin_id = user;
            sendData.type = 2;
            $jq.post(basePath+'admin/Order/order_distribution_user',sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                var arr = $jq('#not_verify_page .layui-laypage-curr');
                var cur = 1;
                if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                    cur = $jq(arr.children()[1]).text();
                }
                layui.finalAppeal.getData(cur,50);
                layer.closeAll();
            });
        }


        /**
         * 处理监听事件
         */
        ,tool : function(){
            table.on('sort(notVerifyTableEvent)',function(obj){
                layui.finalAppeal.getData(1,50,obj);
            })

                //表单监听事件
                form.on('submit(assistXinshenForm)', function (data) {   //分配信审员员们提交
                    var checkOrderList = layui.table.checkStatus('not_verify_table');
                    var ids = new Array();
                    var sendData = {};
                    for (var index in checkOrderList.data) {
                        var item = checkOrderList.data[index];
                        // ids.push(item['order_no']);  //订单号
                        ids.push(item['order_id']);  //订单 ID
                    }
                    var ids_str = ids.join("-");
                        sendData.order_arr = ids_str;
                    var admin_ids = new Array();
                    var collector_list  = layui.table.checkStatus('xisnhenID');
                    for (var index in collector_list.data) {
                        var item = collector_list.data[index];
                        admin_ids.push(item['admin_id']);
                    }
                    var admin_id_arr = admin_ids.join("-");
                        sendData.admin_arr = admin_id_arr;
                        sendData.type = 2;
                    if(admin_ids.length <= 0 ){
                        layer.open({
                            title: layui.language.get('prompt'),           //'提示'
                            success: function (index, layero) {
                                $jq(':focus').blur();
                            },
                            content: layui.language.get('please_sel')+layui.language.get('xinshenyuan'),   //'请选择信审员'
                            btn : [ layui.language.get('certainly') ]//按钮
                        });
                        return false;
                    }
                    //防止多次触发提交
                    $jq('#distri_submit').attr('disabled',true);
                    $jq('#distri_submit').removeClass('y-btn-red');
                    $jq('#distri_submit').addClass('layui-btn-disabled');

                    $jq.post(basePath+'admin/Order/order_distribution_user',sendData,function(data){
                        if(!ajaxCall(data)) {
                            //如果提交不成功，重新开始触发提交按钮
                            $jq('#distri_submits').attr('disabled',false);
                            $jq('#distri_submits').addClass('y-btn-deepblue');
                            $jq('#distri_submits').removeClass('layui-btn-disabled');
                            return false;
                        }
                        //跳转页数以及每页显示的条数
                        var arr = $jq('#not_verify_page .layui-laypage-curr');
                        var cur = 1;
                        if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                            cur = $jq(arr.children()[1]).text();
                        }
                        var limits = $jq("#not_verify_page .layui-laypage-limits select option:selected").val();
                        if(data.code== 200){
                            layer.msg(layui.language.get('distribution_suc'), {    //分配成功
                                icon: 1,
                                time: 500
                            }, function(){
                                layer.closeAll();
                                layui.finalAppeal.getData(cur,limits);
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
    exports('finalAppeal', obj);
});  


