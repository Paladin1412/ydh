layui.define(['form', 'tree', 'layer'],function(exports){

    var  $jq = layui.jquery,
         form = layui.form,
         tree = layui.tree;

    var obj ={
        /**
         * 初始化
         */
        initView : function(){
            layui.language.render('paramPage');
            layui.language.render('addMenu');
            layui.language.render('editMenu');
            layui.language.render('view');
            //初始化表格
            obj.initViewMain();
            form.render();
        }

        /**
         * 初始化信息
         */
        ,initViewMain : function(){
            $jq.post(basePath + 'admin/Menu/menu_list','',function(data){
                layui.menuList.menuTreeList2(data.data);
            })

        }

        /**
         * 展示 树状图菜单列表
         */
        ,menuTreeList1 : function(data){
            var layout = [
                {name:'菜单名称',treeNodes:true,headerClass:"value_col",colClass:"value_col",style:""}
                ,{name: 'Url', field: 'url', headerClass: 'value_col', colClass: 'value_col', style: 'width: 30%'},
                ,{name: '操作', headerClass: 'value_col', colClass: 'value_col', style: 'width: 30%', render: function(row) { return '<a style="color:#0BC07D;font-weight: bold"  class="menu_level"> <i class="layui-icon">&#xe66c;</i> '+row.name+'</a>'; render(row)}}
            ];
            var data =  [ //节点
                {
                    name: '常用文件夹'
                    ,id: 1
                    ,alias: 'changyong'
                    ,children: [
                        {
                            name: '所有未读（设置跳转）'
                            ,id: 11
                            ,href: 'http://www.layui.com/'
                            ,alias: 'weidu'
                        }, {
                            name: '置顶邮件'
                            ,id: 12
                        }, {
                            name: '标签邮件'
                            ,id: 13
                        }
                    ]
                }, {
                    name: '我的邮箱'
                    ,id: 2
                    // ,spread: true
                    ,children: [
                        {
                            name: 'QQ邮箱'
                            ,id: 21
                            // ,spread: true
                            ,children: [
                                {
                                    name: '收件箱'
                                    ,id: 211
                                    ,children: [
                                        {
                                            name: '所有未读'
                                            ,id: 2111
                                        }, {
                                            name: '置顶邮件'
                                            ,id: 2112
                                        }, {
                                            name: '标签邮件'
                                            ,id: 2113
                                        }
                                    ]
                                }, {
                                    name: '已发出的邮件'
                                    ,id: 212
                                }, {
                                    name: '垃圾邮件'
                                    ,id: 213
                                }
                            ]
                        }, {
                            name: '阿里云邮'
                            ,id: 22
                            ,children: [
                                {
                                    name: '收件箱'
                                    ,id: 221
                                }, {
                                    name: '已发出的邮件'
                                    ,id: 222
                                }, {
                                    name: '垃圾邮件'
                                    ,id: 223
                                }
                            ]
                        }
                    ]
                }
                ,{
                    name: '收藏夹'
                    ,id: 3
                    ,alias: 'changyong'
                    ,children: [
                        {
                            name: '爱情动作片'
                            ,id: 31
                            ,alias: 'love'
                        }, {
                            name: '技术栈'
                            ,id: 12
                            ,children: [
                                {
                                    name: '前端'
                                    ,id: 121
                                }
                                ,{
                                    name: '全端'
                                    ,id: 122
                                }
                            ]
                        }
                    ]
                }
            ];

            var tree1 = layui.tree({
                    elem: '#treeMenu1', //传入元素选择器
                    // spreadable: true, //设置是否全展开，默认不展开
                    checkbox : true,
                    skin: 'shihuang',
                    nodes: data,
                    layout: layout
                });
        }


        ,menuTreeList : function(data){
            layui.use(['treeGrid'],function(){
                var treeGrid = layui.treeGrid;
                var tree1 = treeGrid.set({
                // var tree1 = treeGrid.render({
                    elem: '#treeMenu', //传入元素选择器
                    checkbox : true,
                    cellMinWidth: 100,
                    spreadable: false, //设置是否全展开，默认不展开
                    data: data,
                    treeId: 'id',//树形id字段名称
                    treeUpId: 'pid',//树形父id字段名称
                    treeShowName: 'name',//以树形式显示的字段
                    singleSelect: true,
                    cols: [
                        [
                          {field: 'id',title: 'ID'}
                        , {field: 'name',title: '菜单名称',templet:function(row){
                                if(row.pid == 0){
                                    return '<a style="color:#0BC07D;font-weight: bold"  class="menu_level"> <i class="layui-icon">&#xe66c;</i> '+row.name+'</a>';
                                }else{
                                    return '<a class="menu_level" >'+'│──&nbsp;'+row.name+'</a>';
                                }
                            }}
                        , {field: 'pid', title: 'PID' }
                        , {field: 'status', title: '状态',templet:function(row){
                            if(row.status == "0"){
                                return '<a class="layui-btn layui-btn-radius layui-btn-sm layui-btn-warm">正常</a>';
                            }else if(row.status == "1"){
                                return '<a class="layui-btn layui-btn-radius layui-btn-sm layui-btn-danger">禁用</a>';
                            }
                          }}
                        , {field: 'group', title: 'Group'}
                        , {title: '操作',templet:function(row){
                                return '<a class="layui-btn layui-btn-sm" style="display:inline-block;text-align:center" onclick="layui.menuList.menuEdit(' + row.id + ','+1+')"><i class="layui-icon">&#xe642;</i> 编辑</a>'; //列渲染
                                // return '<a class="layui-btn layui-btn-sm" onclick="layui.menuList.menuEdit(' + row.id + ','+1+')"><i class="layui-icon">&#xe642;</i> 编辑</a>'+'<a class="layui-btn layui-btn-sm layui-btn-danger" onclick="layui.menuList.menuDel(' + row.id + ')"><i class="layui-icon">&#xe640;</i> 删除</a>'; //列渲染
                            }
                        }
                    ]],
                });
                form.render();
                treeGrid.render();
            });
        }

        ,menuTreeList2 : function(data){
            layui.use(['laytpl','grid','treeGridNew'], function () {
                var laytpl = layui.laytpl,
                    treegrid = layui.treeGridNew;
                treegrid.config.render = function (viewid, data) {
                    var view = document.getElementById(viewid).innerHTML;
                    return laytpl(view).render(data) || '';
                };
                var rows = [];

                var tree1=treegrid.createNew({
                    elem: 'treeMenuNew',
                    view: 'view',
                    data: { rows: data},
                    parentid: 'pid',
                    singleSelect: true
                });
                tree1.build();
                $jq('.layui-btn').on('click', function () {
                    var id,
                        row = tree1.getRow();
                    if (row != null)
                        id = row.id;

                    switch ($jq(this).attr('lay-filter')) {
                        case 'expandAll': {
                            tree1.expandAll(id)
                        } break;
                        case 'collapseAll': {
                            tree1.collapseAll(id)
                        } break;
                        case 'expand': {
                            tree1.expand(id)
                        } break;
                        case 'collapse': {
                            tree1.collapse(id)
                        } break;
                    }
                });
                form.render();
            });
            form.render();
        }
        /**
         *  添加菜单 --> 弹出框
         */
        ,menuAdd : function(){
            $jq.post(basePath + 'admin/Menu/menu_all','',function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                var html = addMenu.innerHTML;
                layer.open({
                    type:1,
                    area:['700px','700px'],
                    title:[layui.language.get('menu_add'),'text-align:center;font-size:18px;'],
                    content:html,
                });
                var menuHtml = '<option value="">'+ "│──" +layui.language.get('top')+" ── | " +'</option>';
                $jq.each(data.data, function (i, item) {
                    menuHtml += '<option value="' + item.value + '">' + item.name + '</option>';
                })
                $jq('select[name="pid"]').html(menuHtml);
                layui.form.render('select');
                form.render();
            });


        }


        /**
         * 编辑菜单
         */
        ,menuEdit : function(id,type){
            var sendDatas ={};
            sendDatas.id = id;
            sendDatas.type = type;  //type =1  获取信息
            //获取菜单信息
            $jq.post(basePath + '/admin/Menu/menu_edit',sendDatas,function(data) {
                if (!ajaxCall(data)) {
                    return;
                }
            //获取菜单列表
             $jq.post(basePath + 'admin/Menu/menu_all','',function(_data) {
                var html = editMenu.innerHTML;
                var htmlStr = layui.laytpl(html).render(data.data);
                layer.open({
                    type: 1,
                    area: ['700px', '700px'],
                    title: [layui.language.get('menu_edit'), 'text-align:center;font-size:18px;'],
                    content: htmlStr,
                })

                var menuHtml = '<option value="">'+ "│──" +layui.language.get('top')+" ── | " +'</option>';
                    $jq.each(_data.data, function (i, item) {
                            menuHtml += '<option value="' + item.value + '" >' + item.name + '</option>';
                    })
                        $jq("#pid_edit").html(menuHtml);
                        $jq('#pid_edit').val(data.data.pid);
                        $jq('#level_edit').val(data.data.level);
                        form.render('select');
                        form.render();
               });
            });
        }

        /**
         * 删除菜单
         */
        ,menuDel : function(id){
            layer.confirm("确定删除？",{
                title: [layui.language.get('kindly_reminder'),'text-align:center;'],
                btn : [ layui.language.get('certainly'), layui.language.get('cancel') ]//按钮
            },function(){
                // $jq.post(basePath+'admin/Login/logout',function(data) {
                //     if (data.code == '200') {
                //         dataStore.set('now_login_user_realname', null);   //销毁缓存的用户名
                //         layer.msg(layui.language.get('logout_suc'), {
                //             icon: 1,
                //             time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                //         }, function(){
                //             window.location.href='login.html';
                //         });
                //     }
                // });
                    layer.msg("删除成功", {icon: 1, time: 500    //0.5秒关闭（如果不配置，默认是3秒）
                         }, function(){
                           layui.menuList.initViewMain();
                    });

            });

        }

        /**
         *  监听事件
         */
        ,tool : function(){

            //监听表单提交验证事件
            form.verify({
                name : function(value,item){
                    if(value.length == 0){
                        return layui.language.get('null_menu_name');
                        // return "请输入菜单名称";
                    }
                },
                en_name : function(value,item){
                    if(value.length == 0){
                        return layui.language.get('null_menu_name');
                        // return "请输入菜单名称";
                    }
                },
                group : function(value,item){
                    if(value.length == 0){
                        return layui.language.get('null_group');
                        // return "请输入分组标识";
                    }
                },
                sort : function(value,item){
                    if( value.length == 0 ){
                        return layui.language.get('null_no');
                        // return "序号不能为空";
                    }
                    var regPos = /^[1-9]\d*$/; // 非负整数
                    if( !regPos.test(value) ){
                        return layui.language.get('no_fail');
                        // return "排序序号错误";
                    }
                },
                // level : function(value,item){
                //     if(value.length == 0){
                //         // return layui.language.get('role_name_not_null');
                //         return "请选择菜单级别";
                //     }
                // },
                right : function(value,item){
                    if(value.length == 0){
                        return layui.language.get('power_code');
                        // return "请输入权限码";
                    }
                },

            });


            //监听 添加菜单 表单提交
            form.on('submit(addMenuForm)',function(data){
                //防止多次触发提交
                $jq('#add_menu_sub').attr('disabled',true);
                $jq('#add_menu_sub').removeClass('y-btn-green');
                $jq('#add_menu_sub').addClass('layui-btn-disabled');
                console.log(data);
                //得到表单里面数据
                $jq.post(basePath + 'admin/Menu/menu_add',data.field,function(data){
                    if(!ajaxCall(data)) {
                        //如果提交不成功，重新开始触发提交按钮
                        $jq('#add_menu_sub').attr('disabled',false);
                        $jq('#add_menu_sub').addClass('y-btn-green');
                        $jq('#add_menu_sub').removeClass('layui-btn-disabled');
                        return;
                    }
                    if(data.code ==200 ){
                        layer.msg(layui.language.get('add_suc'), {icon: 1,time: 2000,shade : [0.5 , '#000' , true]},function(){
                            layer.closeAll();
                            layui.menuList.initView(); //重新加载页面
                        });
                    }else{
                        layer.msg(data.message, {icon: 2});
                    }
                })

                //至关重要，防止表单提交
                return false;
            });


            //监听 编辑菜单 表单提交保存
            form.on('submit(editMenuForm)',function(data){
                //防止多次触发提交
                $jq('#edit_menu_sub').attr('disabled',true);
                $jq('#edit_menu_sub').removeClass('y-btn-green');
                $jq('#edit_menu_sub').addClass('layui-btn-disabled');
                data.field.type = 2;  //保存修改信息
               //得到表单里面数据
                data.field.id = $jq("#edit_menu_id").val();
                $jq.post(basePath + 'admin/Menu/menu_edit',data.field,function(data){
                    if(!ajaxCall(data)) {
                        //如果提交不成功，重新开始触发提交按钮
                        $jq('#edit_menu_sub').attr('disabled',false);
                        $jq('#edit_menu_sub').addClass('y-btn-green');
                        $jq('#edit_menu_sub').removeClass('layui-btn-disabled');
                        return;
                    }
                    if(data.code ==200 ){
                        layer.msg(layui.language.get('add_suc'), {icon: 1,time: 2000,shade : [0.5 , '#000' , true]},function(){
                            layer.closeAll();
                            layui.menuList.initView(); //重新加载页面
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

    //输出 menuList 接口
    exports('menuList', obj);
});