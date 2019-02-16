layui.define(['col_common','language','laydate'],function(exports){
    var $jq   = layui.jquery,
        table = layui.table,
        form = layui.form,
        language=layui.language;

    var obj = {
        initView : function(code){
            if(code != 123466){
                code = code - 62;
                code = '62'+code.toString();
            }
            layui.form.render();
            var sendDatas = {};
            sendDatas.code = code;
            //得到该渠道相关信息
            $jq.post(basePath + 'admin/Statistical/all',sendDatas,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                 $jq.get('channel/channelDetail.html',sendDatas,function(html) {
                    if (!ajaxCall(html)) {
                        return;
                    }
                    layui.col_common.showChannelDetailPage('detail');
                    layui.channelDetail.initDatas(code,data.data,html);
                });
            });

        }

        /**
         * show 渠道详情信息
         */
        ,initDatas : function(code,data,html){
            layui.laytpl(html).render(data,function(html){
                $jq('#channelDetail').html(html);
            });
            layui.language.render('channelDetail');
            //初始化筛选条件信息 --日期
            layui.laydate.render({
                elem : '#date1',
                range : true,
                done : function(value){
                    var sendDatas = {};
                    sendDatas.code = code;
                    sendDatas.date = value;
                    $jq.post(basePath + 'admin/Statistical/all',sendDatas,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.channelDetail.channelMain(data.data);
                    })
                }
            });
         layui.channelDetail.channelMain(data);
        }


        ,channelMain : function(data){
                var list = data.list;
                if( list[0].click == '0'  &&  list[0].download == '0'  &&  list[0].register == '0' ){
                    var list = [];
                }
                var field  = data.field;
                table.render({
                    elem : '#channelDetailTable',
                    // data : data.list,
                    data : list,
                    autoHeight:true,
                    cols : [[
                        {field: 'click', title: layui.language.get('click')},
                        {field: 'download', title: layui.language.get('download')},
                        {field: 'register', title: layui.language.get('register')},
                        {field: 'order', title: layui.language.get('apply_order')}
                    ]]
                    ,page : false
                });
        }

    }

    //输出 channelDetail 接口
    exports('channelDetail',obj);

});