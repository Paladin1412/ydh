layui.define(['col_common'],function (exports) {
    var form = layui.form,
        table = layui.table,
        $jq = layui.jquery,
        laypage = layui.laypage;

    var obj ={

        initView: function () {
            layui.language.render('channelList');
            layui.language.render('channelDetail');
            layui.language.render('saveChannel');
            layui.use('col_common',function(){});
            layui.form.render();
            // 初始化表格
            this.getData(1,50);
        }


        /**
         * 第一次进页面的初始化表格
         */
        ,getData: function(curr,limit) {
            var sendData = $jq('#channelListForm').serialize();
            $jq.post(basePath+'admin/Statistical/adv?page='+curr+'&limit='+limit,sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.channelList.initViewTable(data.data);
            });
        }


        /**
         * 初始化表格
         */
        ,initViewTable: function(data) {
            //方法级渲染
            var initIndex =0;

            table.render({
                elem: '#channelListTable'
                ,data:data
                ,limit: data.length
                ,cols: [[
                    {title: 'ID',width:'6%',templet:function(d){
                            ++initIndex;
                            return initIndex;
                        }}
                    ,{field:'name', title: layui.language.get("channel_name")}
                    ,{field:'code', title: layui.language.get("channel_code")}
                    ,{field:'callback', title: layui.language.get("tg_link")}
                    ,{field:'show_data_url', title: layui.language.get("data_url")}
                    ,{field:'status', title: layui.language.get("status"),templet:function(d){
                            if(d.status == '0'){
                                return '<a style="color:#D80000">'+layui.language.get('forbidden')+'</a>';
                            }else{
                                return '<a style="color:#0BC07D">'+layui.language.get('active')+'</a>';
                            }
                        }}
                    ,{title: layui.language.get('caozuo'),width:180, templet:function(d){
                        if(d.status == '0'){
                            var btn =  '<button class="layui-btn  layui-btn-radius layui-btn-xs y-btn-moren"  onclick="layui.channelList.saveChannelStatus('+d.id+',1);" > ' +layui.language.get('active') +' </button>';
                        }else{
                            var btn =  '<button class="layui-btn  layui-btn-radius layui-btn-xs y-btn-warm"  onclick="layui.channelList.saveChannelStatus('+d.id+',0);" > ' +layui.language.get('forbidden') +' </button>';
                        }
                                return '<button class="layui-btn  layui-btn-radius layui-btn-xs y-btn-red"  onclick="layui.col_common.goChannelDetail('+d.code+');" > ' +layui.language.get('look') +' </button>'+ btn;
                        }}
                ]]
                ,id: 'channelListTable'
                ,page: false
            });

            // //执行重载
            // //完整功能
            // var canFlush = false;
            // laypage.render({
            //     elem: 'channelListPage'
            //     ,count: data.page.count
            //     ,curr:  data.page.page
            //     ,prev: '<em><</em>'
            //     ,next: '<em>></em>'
            //     ,limit: data.page.limit
            //     ,limits:[20,50,100]
            //     ,layout: ['count','prev','page','next','limit','limits','skip']
            //     ,jump: function(obj){
            //         if(canFlush) {
            //             layui.channelList.getData(obj.curr,obj.limit);
            //         }else {
            //             canFlush=true;
            //         }
            //
            //     }
            // });

        },

        /**
         * 添加渠道信息 ==》 窗口展示
         */
        addChannel : function(){
            var html = saveChannel.innerHTML;
            layer.open({
                type:1,
                area:'400px',
                title:[layui.language.get('addChannel'),'text-align:center;font-size:18px;'],
                font_size:30,
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
                content:html,
            });

        },

        /**
         * 提交渠道信息
         * @param  name   渠道名称
         */
        submitAddChannel : function(){
            var channel_name = $jq('#channel_name').val();
            if(channel_name.length == 0 || channel_name == null){
                layer.msg(layui.language.get('input_channel'),{icon:2,time:1000});           // '减免金额不能大于罚息';
                return false;
            }
            $jq.post(basePath+'/admin/Statistical/adv_add',{'name':channel_name},function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.channelList.getData(1,50);
                layer.closeAll();
            });
        },



        /**
         * 激活/禁用（隐藏）渠道信息
         * @param  channel_id     渠道标识ID
         * @param  status   0 已禁用   1 激活
         */
        saveChannelStatus : function(channel_id,status){
                var sendDatas ={};
                sendDatas.id   = channel_id;
                sendDatas.status = status;
            $jq.post(basePath+'admin/Statistical/change_status ',sendDatas,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
               layui.channelList.getData(1,50);
            });


        }


    }

    //输出 channelList 接口
    exports('channelList', obj);
});