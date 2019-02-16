layui.define(function (exports) {
    var form = layui.form,
        table = layui.table,
        $jq = layui.jquery,
        upload = layui.upload,
        language = layui.language,
        laypage = layui.laypage;

    var obj ={

        initView: function () {
            layui.language.render('viewDetail');
            layui.language.render('addLinkList');
            layui.form.render();
            // 初始化表格
            this.getData(1,50);

        }


        /**
         * 第一次进页面的初始化表格
         */
        ,getData: function(curr,limit) {
            $jq.post(basePath+'admin/Pormotion/showInfo?page='+curr+'&limit='+limit,'',function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layui.referralLink.initViewTable(data.data);
            });
        }


        /**
         * 初始化表格
         */
        ,initViewTable: function(data) {
            //方法级渲染
            var initIndex =0;
            var datas = data.list ;
            if(datas == null){
                datas = [];
            }
            table.render({
                elem: '#referralLinkListTable'
                ,data:datas
                ,limit: datas.length
                ,cols: [[
                    {title: 'ID',width:'5%',templet:function(d){
                            ++initIndex;
                            return initIndex;
                        }}
                    ,{field:'company_url', title: layui.language.get("company_url")}
                    ,{field:'android_url', title: layui.language.get("android_url")}
                    ,{field:'app_url', title: layui.language.get("ios_url")}
                    ,{field:'image_url', title: layui.language.get("image_url"),templet:function(d){
                            var html_img = '<img  id="link_background" class="y-img-li link_background"  src="'+d.image_url+'"  onclick="layui.referralLink.viewPhoto();">';
                            return html_img;
                     }}
                     // ,{title:layui.language.get('caozuo'),templet:function(d){
                     //   return '<button class="layui-btn layui-btn-radius layui-btn-xs y-btn-red"  onclick="layui.referralLink.editLink(\''+d.id+'\');" > ' +layui.language.get('edit') +' </button>';
                     //
                     //    }}
                ]]
                ,id: 'referralLinkListTable'
                ,page: false
            });

            var canFlush = false;
            laypage.render({
                elem: 'referralLinklListPage'
                ,count: data.page.count
                ,curr:  data.page.page
                ,prev: '<em><</em>'
                ,next: '<em>></em>'
                ,limit: data.page.limit
                ,limits:[20,50,100]
                ,layout: ['count','prev','page','next','limit','limits','skip']
                ,jump: function(obj){
                    if(canFlush) {
                        layui.referralLink.getData(obj.curr,obj.limit);
                    }else {
                        canFlush=true;
                    }

                }
            });

        },


        /**
         * 添加渠道信息 ==》 窗口展示
         */
        addLink : function(){
            layer.open({
                type:1,
                area:'800px',
                title:[layui.language.get('tianjia')+layui.language.get('referra_link'),'text-align:center;font-size:18px;'],
                font_size:30,
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
                content:addLinkList.innerHTML
            });

            upload.render({

                elem : '#image_upload'
                ,url: basePath + 'admin/Company/company_upload_image'
                ,multiple: false
                ,field:'image'
                ,done: function(res){
                    var code = "\'" + res.data.image_code + "\'";
                    $jq('#image_all').append('<div id="' + res.data.image_code + '" style="margin:10px;width:100px;text-align:center" class="layui-inline">' +
                        '<input type="hidden" id="ImageLink" value="'+ res.data.image_code +'">' +
                        '<img class="y-img-li" onclick="showImg(this)" src="'+ res.data.image_url +'" alt="" class="layui-upload-img">' +
                        '<label>' +
                        '<a href="javascript:;" onclick="layui.referralLink.del('+code+')" style="color:red;font-size:20px;">' + language.get('del') + '</a>' +
                        '</label>' +
                        '</div>')
                    $jq('#image_code').val(res.data.image_code);
                    $jq('#image_url').val(res.data.image_url);
                }

            });
            upload.render({
                elem : '#android_url_upload'
                ,url: basePath + 'admin/Company/company_upload_image'
                ,accept: 'file'
                ,exts: 'zip|rar|apk'
                ,field:'image'
                ,size: 2048 //最大允许上传的文件大小
                ,done: function(res){
                        if(res.code == '200'){
                            layer.msg(layui.language.get('success'), {
                                icon: 1,
                                time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                            }, function(){
                                $jq('#android_code').val(res.data.image_code);
                                $jq('#android_url').val(res.data.image_url);
                                $jq('#upload_res').html("上传成功");
                                $jq('#upload_res').css({
                                    'font-size' : '28px',
                                    'color' : 'green'
                                });
                            });
                        }else{
                            layer.msg(layui.language.get('fail')+'请重新上传', {
                                icon: 2,
                                time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                            },function(){
                                $jq('#upload_res').html("上传失败");
                                $jq('#upload_res').css({
                                    'font-size' : '28px',
                                    'color' : 'red'
                                });
                                return false;
                            })

                        }
                }
            })

            layui.form.render();
        },


        /**
         * 编辑渠道信息 ==》 窗口展示
         * @param   id
         */
        editLink : function(id){
            var sendDatas = {};
            sendDatas.id = id;
            $jq.post(basePath+'admin/Pormotion/showInfo',sendDatas,function(data){
                if(!ajaxCall(data)) {
                    return;
                }

                var html = addLinkList.innerHTML;
                var htmlStr = laytpl(html).render(data);
                layer.open({
                    type:1,
                    area:'800px',
                    title:[layui.language.get('edit')+layui.language.get('referra_link'),'text-align:center;font-size:18px;'],
                    font_size:30,
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
                    // content:addLinkList.innerHTML
                    content:htmlStr
                });

                upload.render({
                    elem : '#image_upload'
                    ,url: basePath + 'admin/Company/company_upload_image'
                    ,multiple: false
                    ,field:'image'
                    ,done: function(res){
                        var code = "\'" + res.data.image_code + "\'";
                        $jq('#image_all').append('<div id="' + res.data.image_code + '" style="margin:10px;width:100px;text-align:center" class="layui-inline">' +
                            '<input type="hidden" id="ImageLink" value="'+ res.data.image_code +'">' +
                            '<img class="y-img-li" onclick="showImg(this)" src="'+ res.data.image_url +'" alt="" class="layui-upload-img">' +
                            '<label>' +
                            '<a href="javascript:;" onclick="layui.referralLink.del('+code+')" style="color:red;font-size:20px;">' + language.get('del') + '</a>' +
                            '</label>' +
                            '</div>')
                        $jq('#image_code').val(res.data.image_code);
                        $jq('#image_url').val(res.data.image_url);
                    }
                });

                layui.form.render();
            });
        },





        /**
          * 删除图片
          */
        del : function(data){
            document.getElementById(data).remove();
            var image_all = $jq('#image_all').val().replace(data,'');
            $jq('#image_all').val(image_all);
         },





        /**
         * 提交推广链接信息
         */
        submitAddLink : function(){
            var sendDatas ={};
            sendDatas.company_url  = $jq('#company_url').val();
            sendDatas.appurl       = $jq('#appurl').val();
            sendDatas.image_code   = $jq('#image_code').val();
            sendDatas.image_url    = $jq('#image_url').val();
            sendDatas.android_code = $jq('#android_code').val();
            sendDatas.android_url  = $jq('#android_url').val();
            if(sendDatas.company_url.length == 0 ){
                layer.msg(layui.language.get('company_url'),{icon:2,time:1000});
                return false;
            }
            if(sendDatas.appurl.length == 0 ){
                layer.msg(layui.language.get('ios_url'),{icon:2,time:1000});
                return false;
            }
            if(sendDatas.android_url.length == 0 ){
                layer.msg(layui.language.get('android_url'),{icon:2,time:1000});
                return false;
            }
            // if(sendDatas.image_code.length == 0 ){
            //     layer.msg(layui.language.get('image_code'),{icon:2,time:1000});
            //     return false;
            // }
            if(sendDatas.image_url.length == 0 ){
                layer.msg(layui.language.get('image_url'),{icon:2,time:1000});
                return false;
            }
            $jq.post(basePath+'/admin/Pormotion/addExtensionLink',sendDatas,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layer.msg(layui.language.get('success'), {
                    icon: 1,
                    time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                }, function(){
                    layui.referralLink.getData(1,50);
                    layer.closeAll();
                });

            });
        },



        viewPhoto :function(){
            // $jq(function() {
            //     $jq('#link_background').viewer({
            //         url: 'data-original',
            //     });
            //
            // });
            $(function() {
                $('.link_background').viewer({
                    url: 'data-original',
                });
            });
        }

    }

    //输出 referralLink 接口
    exports('referralLink', obj);
});