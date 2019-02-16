layui.define(function (exports) {
    var form = layui.form,
        table = layui.table,
        $jq = layui.jquery,
        laypage = layui.laypage;

    var obj ={

        initView: function () {
            layui.language.render('paramPage');
            layui.form.render();
            //初始化筛选条件信息
            this.initData();
            // 初始化表格
            this.getData(1,50);
        }

        /**
         * 初始化筛选条件信息
         */
        ,initData:function() {
            // 初始化时间框
            layui.laydate.render({
                elem: '#date'
                , range: true
                , min: '2018-01-01'
                , max: '2100-12-31'
                , done: function (value) {
                    var sendData = {};
                    sendData.date = value;
                    sendData.company_id = $jq('#company_id').val();
                    sendData.search_string = $jq('#search_string').val();
                    $jq.post(basePath + 'admin/Finance/payment_list', sendData, function (data) {
                        if (!ajaxCall(data)) {
                            return;
                        }
                        layui.loanList.initViewTable(data.data);
                    })
                }
            });
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
                        layui.form.render('select');
                    }
                });
            }

        }

        /**
         * 第一次进页面的初始化表格
         */
        ,getData: function(curr,limit) {
            var sendData = $jq('#loanListForm').serialize();
            $jq.post(basePath+'admin/Finance/payment_list?page='+curr+'&limit='+limit,sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.loanList.initViewTable(data.data);
            });
        }


        /**
         * 初始化表格
         */
        ,initViewTable: function(data) {
            var field = data.field;
            var initIndex =0;
            //方法级渲染
            table.render({
                elem: '#loanListTable'
                ,data:data.list
                ,limit: data.page.limit
                ,cols: [[
                    {field: 'num',title: 'ID',width:'7%',templet:function(d){
                            var size = data.page.limit;
                            var cur = data.page.page;
                            ++initIndex;
                            return (cur-1)*size+initIndex;
                        }}
                    ,{field:'order_no', title: layui.language.get('common_order')}
                    ,{field:'name', title: field.name}
                    ,{field:'price', title:field.price}
                    ,{field:'add_time', title: field.add_time}

                ]]
                ,id: 'loanListTable'
                ,page: false
            });

            //执行重载
            //完整功能
            var canFlush = false;
            laypage.render({
                elem: 'loanListPage'
                ,count: data.page.count
                ,curr:  data.page.page
                ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count','prev','page','next','limit','limits','skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.loanList.getData(obj.curr,obj.limit);
                    }else {
                        canFlush=true;
                    }

                }
            });

        },

        tool : function(){

        }

    }

    //输出 loanList 接口
    exports('loanList', obj);
});