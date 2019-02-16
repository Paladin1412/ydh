layui.define(['form', 'tree', 'layer'],function(exports){
    var  $jq = layui.jquery,
            form = layui.form,
            table = layui.table;
            laypage = layui.laypage;

    var obj ={
        initView : function(){
            layui.language.render('viewLoan');
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
                            layui.loanStatistics.getData(1,50,data.value);
                        });
                        layui.form.render('select');
                    }
                });
            }
            //初始化日期选择
            layui.laydate.render({
                elem: '#date'
                ,range:true
                , done: function (value) {
                    if (!value) return;
                    var sendDatas ={};
                    sendDatas.date = value;
                    $jq.post(basePath + 'admin/Finance/pay_list',sendDatas,function(data) {
                        if (!ajaxCall(data)) {
                            return;
                        }
                        layui.loanStatistics.initViewTable(data);
                    });
                }
            });
            obj.getData(1,50);

        },

        //获取数据
        getData : function(curr,limit){
            var sendDatas ={};
                sendDatas.date = $jq('#date').val();
                sendDatas.company_id = $jq('#company_id').val();
            $jq.post(basePath + 'admin/Finance/pay_list?page='+curr+'&limit='+limit,sendDatas,function(data){
                layui.loanStatistics.initViewTable(data);
            })
        },

        /**
         * 初始化表格
         */
        initViewTable : function(data){
                var field = data.data.field;
                var data = data.data;
                    var  order_cntSum            = 0;   //应放款数
                    var  order_success_cntSum    = 0;   //已放款数
                    var  order_fail_cntSum       = 0;   //放款失败数
                    var  order_success_sumSum    = 0;   //放款总额
                    var  order_ruzhang_sumSum    = 0;   //入账总额

            $jq.each(data.list,function(index,item){
            	order_cntSum += Number(item.order_cnt);
            	order_success_cntSum    += Number(item.order_success_cnt);
            	order_fail_cntSum       += Number(item.order_fail_cnt);
            	order_success_sumSum    += Number(item.order_success_sum);
                order_ruzhang_sumSum    += Number(item.order_repayment_sum);
            });

            table.render({
                    elem : '#loanStatisticsListTable',
                    data: data.list,
                    autoHeight: true,
                    // limit: 31,
                    limit: data.page.limit,
                    cols : [[
                        {field: 'date_str', title: field.date_str,},
                        {field: 'order_cnt', title: field.order_cnt},
                        {field: 'order_success_cnt', title: field.order_success_cnt},
                        {field: 'order_fail_cnt', title: field.order_fail_cnt},
                        {field: 'order_success_sum', title: field.order_success_sum,templet:function(d){
                                return  formatNumber(d.order_success_sum);
                         }},
                        {field: 'order_repayment_sum ', title: field.order_repayment_sum,templet:function(d){
                                return  formatNumber(d.order_repayment_sum);
                        }}
                    ]],
                    id : 'loanStatisticsListTable',
                    page: false,
                   done: function(res, curr, count){
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
                elem: 'loanStatisticsListPage'
                ,count: data.page.count
                ,curr:  data.page.page
                ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count', 'prev', 'page', 'next','limit','limits' , 'skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.loanStatistics.getData(obj.curr,obj.limit);
                    }else {
                        canFlush=true;
                    }

                }
            });


                if(data.list.length >0){
                    var  tr = '<tr class=\"finance-sum-tr\" style="text-align: center;font-weight: bold;"><td>' + layui.language.get('sum') + '</td> <td>'+ order_cntSum+'</td> <td >'+ order_success_cntSum +'</td> <td>'+ order_fail_cntSum +'</td> <td>'+ formatNumber(order_success_sumSum) +'</td> <td>'+ formatNumber(order_ruzhang_sumSum) +'</td></tr>';
                    $jq(".layui-table-body .layui-table").append(tr);
                }

        }

    }

    //输出 loanStatistics 接口
    exports('loanStatistics',obj);
});