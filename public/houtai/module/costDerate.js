layui.define(['form','table','layer','laytpl','jquery','laydate','laypage','language','col_common'],function (exports) {
    var form    = layui.form,
        table    = layui.table,
        layer    = layui.layer,
        $jq      = layui.jquery,
        laytpl   = layui.laytpl,
        laypage  = layui.laypage,
        language = layui.language;

    var obj = {
        initView: function () {
            layui.language.render('viewDetail');
            layui.language.render('costDerateListTool');
            layui.language.render('derateApprove');
            layui.form.render();
            //初始化公司和催收员 下拉框
            layui.use('col_common',function(){
                layui.col_common.initMain();
            });

            //初始化筛选条件信息 --日期
            layui.laydate.render({
                elem : '#date',
                range : true,
                min: '2018-01-01',
                max: '2100-12-31',
                done : function(value){
                    var sendData ={};
                    sendData.date = value;
                    sendData.company_id = $jq('#company_id').val();
                    sendData.admin_id = $jq('#admin_id').val();
                    sendData.search_string = $jq('#search_string').val();
                    $jq.post(basePath + 'admin/Collection/reduction_list',sendData,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.costDerate.initViewTable(data.data);
                    })
                }
            });
            //初始化表格
            obj.getData(1,50);

        },


        /**
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit,obj){
            if(obj){
                $jq('#order_field').val(obj.field);
                $jq('#order_sort').val(obj.type);
            }
            var sendData = $jq('#cost_derate_form').serialize();
             $jq.post(basePath + 'admin/Collection/reduction_list?page='+curr+'&limit='+limit,sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.costDerate.initViewTable(data.data,obj);
            })
        },

        /**
        * 初始化表格
        */
        initViewTable : function(data,objData){
            var field    = data.field;
            var roleType = dataStore.get('collection_role_type');
            var initIndex = 0;
            table.render({
                elem : '#costDerateListTable',
                data: data.list,
                limit:data.page.limit,
                cols : [[
                        {field: 'num',title: 'ID',width:'6%',templet:function(d){
                                var size = data.page.limit;
                                var cur = data.page.page;
                                ++initIndex;
                                return (cur-1)*size+initIndex;
                            }},
                        {field:'order_no',title: layui.language.get('common_order'),width:'15.8%',templet:function(d){
                                return '<a class="td_a" href="javascript:layui.col_common.goColDetail(\''+d.order_no+'\',false)" style="color:#4ACE9B;">'+d.order_no+'</a>';
                            }},
                        {field: 'user_name', title: field.user_name,width:'10.2%'},
                        {field: 'repay_amount', title: field.repay_amount,width:'11.3%'},
                        {field: 'reduction_fee', title: field.reduction_fee,width:'11.7%'},
                        {field: 'apply_date', title: field.apply_date,width:'10%',sort:true},
                        {field: 'apply_name', title: field.apply_name,width:'10%' },
                        {field: 'reduction_status', title: field.reduction_status,width:'11%'},
                        // {title: layui.language.get('caozuo'),width:'13%',toolbar: '#costDerateListTool'} //这里的toolbar值是模板元素的选择器
                        {title: layui.language.get('caozuo'), templet:function(d){
                                if(roleType=='4' || roleType=='5' || roleType=='6' ) {
                                    return '<button class="layui-btn layui-btn-radius layui-btn-xs y-btn-red"  onclick="layui.costDerate.approveDetail(\''+d.reduction_id+'\');" > ' +layui.language.get('audit_detail') +' </button>';
                                }else{
                                    return '--';
                                }
                            }}

                ]]
                ,id : 'costDerateListTable'
                ,page : false
                ,done : function(res,curr,count){
                    if(objData){
                        $jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
                    }
                }
            });

            //执行重载
            //完整功能
            var canFlush = false;
            laypage.render({
                elem: 'costDerateListPage'
                ,count: data.page.count
                ,curr:  data.page.page
          	    ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count', 'prev', 'page', 'next','limit','limits','skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.costDerate.getData(obj.curr,obj.limit,objData);
                    }else {
                        canFlush=true;
                    }

                }
            });


        },

        /**
         * 减免审批详情--->>>新增弹出框
         */
        approveDetail : function(reduction_id) {
             //得到该订单审批信息
            $jq.post(basePath + 'admin/Collection/reduction_details',{"reduction_id":reduction_id},function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                var data =data.data;
                var html = derateApprove.innerHTML;
                var htmlStr = laytpl(html).render(data);
                layer.open({
                    type:1,
                    area:'900px',
                    title:[layui.language.get('derate_audit_detail'),'text-align:center;font-size:18px;'],
                    font_size:30,
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
                    content:htmlStr,
                });


                if(data.list.reduction_status){
                    if(data.list.reduction_status == '1'){
                        $jq('#reduction_sub_div').hide();
                        $jq('#reduction_status div').remove();
                        $jq('#reduction_status').html(layui.language.get('pass'));
                    }else if( data.list.reduction_status == '2'){
                        $jq('#reduction_sub_div').hide();
                        $jq('#reduction_status div').remove();
                        $jq('#reduction_status').html(layui.language.get('refuse'));
                    }

                }

                form.render();
            });

        },


        /**
         * 跳转查看催收详情页面
         */
        collectionDetail:function(order_no){
            dataStore.set('collectionDetail_order_no',order_no);
            dataStore.set('collection_url','postloan/costDerate.html');
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
        tool : function(){
            // //表格监听事件
            table.on('tool(costDerateListTableEvent)',function(obj) {
                // var data = obj.data;//获取一行的数据
                // if(obj.event === 'approve') {       //查看数据详情
                //     layui.costDerate.approveDetail(data.reduction_id);
                // }
            });
            table.on('sort(costDerateListTableEvent)',function(obj){
                layui.costDerate.getData(1,50,obj);
            })

            //监听表单提交 -- 减免审批详情
            form.on('submit(dreateAddForm)',function(data){
                //防止多次触发提交
                $jq('#dreateAdd_sub').attr('disabled',true);
                $jq('#dreateAdd_sub').removeClass('y-btn-green');
                $jq('#dreateAdd_sub').addClass('layui-btn-disabled');
                //得到表单里面数据
                $jq.post(basePath + 'admin/Collection/reduction_save',{"reduction_id":data.field.reduction_id,"reduction_status":data.field.reduction_status},function(data){

                    if(!ajaxCall(data)) {
                        //如果提交不成功，重新开始触发提交按钮
                        $jq('#dreateAdd_sub').attr('disabled',false);
                        $jq('#dreateAdd_sub').addClass('y-btn-green');
                        $jq('#dreateAdd_sub').removeClass('layui-btn-disabled');
                        return;
                    }
                    //跳转页数以及每页显示的条数
                    var arr = $jq('#costDerateListPage .layui-laypage-curr');
                    var cur = 1;
                    if(arr.children() && arr.children()[1] && $jq(arr.children()[1]).text()) {
                        cur = $jq(arr.children()[1]).text();
                    }
                    var limits = $jq("#costDerateListPage .layui-laypage-limits select option:selected").val();
                    if(data.code ==200 ){
                        var derate_audit_suc = layui.language.get('derate_audit_suc');
                        layer.msg(derate_audit_suc, {icon: 1,time: 2000,shade : [0.5 , '#000' , true]},function(){
                            layer.closeAll();
                            layui.costDerate.getData(cur,limits);
                        });
                    }else{
                        layer.msg(data.message, {icon: 2});
                    }
                })

                //至关重要，防止表单提交
                return false;
            });
        }



    }



    //输出 costDerate 接口
    exports('costDerate', obj);

});