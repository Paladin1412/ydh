layui.define(function (exports) {
    var $jq = layui.jquery;

    var obj = {

        initMain:function() {    //初始化公司列表
            var roleType = dataStore.get('collection_role_type');
            layui.col_common.initColUser();
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
                        layui.form.on('select(company_id)', function(data){
                            layui.col_common.initColUser(data.value);
                        });
                    }
                });
            }
        }
        /**
         * 初始化催收员列表
         * @param company
         */
        ,initColUser : function(company){
            var roleType = dataStore.get('collection_role_type');
            if(roleType == '4' || roleType == '5' || roleType == '6') {
                $jq('#csyList').show();
                var sendData ={};
                if(company){
                    sendData.company_id =company;
                }

                $jq.post(basePath + 'admin/Collection/collection_user', sendData,function (data) {
                    var csy_nameHtml = '<option value="">'+ layui.language.get('sel_csy') +'</option>';
                    if (data.code == 200) {
                        $jq.each(data.data.list, function (i, item) {
                            csy_nameHtml += '<option value="' + item.admin_id + '">' + item.real_name + '</option>';
                        });
                    }
                    $jq('select[name="admin_id"]').html(csy_nameHtml);
                    layui.form.render('select');
                });
            }

        }

        /**
         * 催收详情
         * @param orderNo
         * @param showBtn
         */
        ,goColDetail:function(orderNo,showBtn,orderStatus){  // 订单号、该页面是否显示申请减免和添加记录按钮
            layui.use(['colDetail'], function(){
                layui.colDetail.initView(orderNo,showBtn,orderStatus);
            });
        }

        /**
         * 显示/隐藏催收详情页
         * @param id
         */
        ,showColDetailPage:function(id) {
            if(id=='detail') {
                $jq('#paramPage').hide();
                $jq('#allColDetail').show();

            }else if(id=='parent') {
                $jq('#allColDetail').hide();
                $jq('#paramPage').show();
            }
         }

        /**
         * 渠道详情
         * @ param code   渠道code
         */
        ,goChannelDetail:function(code){
            layui.use(['channelDetail'],function(){
                layui.channelDetail.initView(code);
            });
        }


        /**
         * 显示/隐藏渠道详情页
         * @param id
         */
        ,showChannelDetailPage:function(id) {
            if(id=='detail') {
                $jq('#channelList').hide();
                $jq('#channelDetail').show();

            }else if(id=='parent') {
                $jq('#channelDetail').hide();
                $jq('#channelList').show();
            }
        }


    }

    //输出test接口
    exports('col_common', obj);
});
