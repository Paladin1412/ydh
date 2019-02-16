layui.define(['form','table','layer','laytpl','jquery','laydate','laypage','language'],function (exports) {
    var form    = layui.form,
        table    = layui.table,
        layer    = layui.layer,
        $jq      = layui.jquery,
        laypage  = layui.laypage;

    var obj = {
        initView: function () {
            layui.language.render('viewDetail');
            layui.form.render();
            //初始化日期选择
            layui.laydate.render({
                elem: '#date'
                ,range:true
                , done: function (value) {
                    console.log(value);
                    console.log("yonghu");
                    var sendDatas ={};
                    sendDatas.date = value;
                    $jq.post(basePath + 'admin/Operation/user_statistics',sendDatas,function(data) {
                        if (!ajaxCall(data)) {
                            return;
                        }
                        layui.usersStatistics.initViewTable(data.data);
                    });
                }
            });
            //初始化表格
            obj.getData(1,20);

        },

        /**
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit,obj){
            if(obj){
                $jq('#order_field').val(obj.field);
                $jq('#order_sort').val(obj.type);
            }
            var sendData = $jq('#usersStatisticsListForm').serialize();
            $jq.post(basePath + 'admin/Operation/user_statistics?page='+curr+'&page_size='+limit,sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.usersStatistics.initViewTable(data.data,obj);
            })
        },

        /**
         * 初始化表格
         */
        initViewTable : function(data,objData){
            var field    = data.field;
            table.render({
                elem : '#usersStatisticsListTable',
                data: data.list,
                limit:data.page.page_size,


                cols : [[
                    // {field: 'id', title: 'ID'},
                    {field: 'date_str', title: field.date_str},
                    {field: 'register_user_num', title: field.register_user_num},
                    {field: 'borrow_user_num', title: field.borrow_user_num},
                    {field: 'invest_user_num', title: field.invest_user_num},
                    {field: 'total_borow_num', title: field.total_borow_num},
                    {field: 'total_invest_num', title: field.total_invest_num},
                    {field: 'total_user_num', title: field.total_user_num}
                ]]
                ,id : 'usersStatisticsListTable'
                ,page : false
                ,done : function(res,curr,count){
                    // if(objData){
                    //     $jq('.layui-table-header th[data-field="' + objData.field + '"] span').attr('lay-sort',objData.type);
                    // }
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
                elem: 'usersStatisticsListPage'
                ,count: data.page.count
                ,curr:  data.page.page
                ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.page_size
                ,limits:[20,50,100]
                ,layout: ['count', 'prev', 'page', 'next','limit','limits','skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.usersStatistics.getData(obj.curr,obj.limit,objData);
                    }else {
                        canFlush=true;
                    }

                }
            });
            if(data.list.length >0){
                var  tr = '<tr class=\"finance-sum-tr\" style="text-align: center;font-weight: bold;"><td>' + layui.language.get('sum') + '</td>'
                    +'<td>'+ data.statis.register_user_num_statis +'</td> '
                    +'<td>'+ data.statis.borrow_user_num_statis +'</td>'
                    +'<td>'+ data.statis.invest_user_num_statis +'</td>'
                    +'<td>'+ "--" +'</td>'
                    +'<td>'+ "--" +'</td>'
                    +'<td>'+ "--" +'</td></tr>';
                $jq(".layui-table-body .layui-table").append(tr);
            }

        }



    }


    //输出 usersStatistics 接口
    exports('usersStatistics', obj);

});