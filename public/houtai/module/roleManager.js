layui.define(['jquery','laydate','table','laypage','laytpl','form','language','authtree'],function (exports) {
	
	var laydate = layui.laydate;
	var $jq = layui.jquery;
	var table = layui.table;
	var laypage = layui.laypage;
	var laytpl = layui.laytpl;
	var form = layui.form;
	var language = layui.language;
    var authtree = layui.authtree;

    var obj = {
        initView: function () {
        	language.render('paramPage');
        	language.render('roleManagerTool');
        	language.render('itemPage');
        	language.render('addPage');
        	
        	var roleType = dataStore.get('global_role_type');
        	if(roleType == '6'){
        		$jq('#hid_add_role').show();
        	}else{
        		$jq('#hid_add_role').hide();
        	}

        	//初始化搜索框中的时间
        	this.initDate();
        	//初始化表格
        	this.getData(1,50);
        },

        /**
         * 初始化搜索框中的时间
         */
        initDate : function(){
        	laydate.render({
        		elem : '#date',
        		range : true,
        		min: '2018-01-01',
        	   	max: '2100-12-31',
        		done : function(value){
        			var search_string = $jq('#search_string').val();
        			$jq.post(basePath + 'admin/Role/role_index',{'date':value,'search_string':search_string},function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
        				layui.roleManager.initViewTable(data);
        			})
        		}
        	})
        },


        /**
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit){
        	var sendDatas = {};
            sendDatas.page  = curr;
            sendDatas.limit = limit;
        	sendDatas.date  = $jq('#date').val();
        	sendDatas.search_string = $jq('#search_string').val();

        	$jq.post(basePath + 'admin/Role/role_index',sendDatas,function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		layui.roleManager.initViewTable(data);
        	})
        },


        /**
         * 初始化表格
         */
        initViewTable : function(data){
        	var field = data.data.field;
        	var initIndex = 0;
        	table.render({
        		elem : '#roleManagerTable',
        		data: data.data.list,
        		limit:data.data.page.limit,
        		cols : [[
        			{field: 'num',title: 'ID',width:80,templet:function(d){
        				var size = data.data.page.limit;
        				var cur = data.data.page.page;
        				++initIndex;
        				return (cur-1)*size+initIndex;
        			}},
        			{field: 'role_name', title: field.role_name},
                    {field: 'role_desc', title: field.role_desc},
                    {field: 'right', title: field.operate,align:'center', width: 250,toolbar: '#roleManagerTool'} //这里的toolbar值是模板元素的选择器
        		]],
        		id: 'roleManagerTable',
        		page: false
        	});
        	  //执行重载
        	  //完整功能
          	
        	  var canFlush = false;
        	  laypage.render({
        	    elem: 'roleManagerPage'
        	    ,count: data.data.page.count
        	    ,curr: data.data.page.page
          	    ,prev: '<em><</em>'
                ,next: '<em>></em>'
        	    ,limit: data.data.page.limit
        	    ,limits:[20, 50, 100]
        	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
        	    ,jump: function(obj){
        	    	if(canFlush) {
        	    		layui.roleManager.getData(obj.curr,obj.limit);
        	    	}else {
        	    		canFlush=true;
        	    	}
        	        
        	    }
        	  });
        },

        /**
         * 处理监听事件
         */
        tool : function() {
        	//表格监听事件
        	table.on('tool(roleManagerEvent)',function(obj){
        		var data = obj.data;//获取一行的数据
        		//编辑操作
        		if(obj.event === 'edit'){
        			$jq('#paramPage').hide();
        			$jq('#itemPage').show();
        			$jq('#addPage').hide();
        			$jq.post(basePath + 'admin/Role/role_edit',{'role_id':data.role_id,'type':'1'},function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
        				layui.roleManager.initEditView(data);
        			})
        		}
        		//禁用操作
        		if(obj.event === 'forbidden'){
        			layer.open({
        				title: [language.get('message')],
                success: function (index, layero) {
                            $jq(':focus').blur();
                        },
        				content: language.get('edit_to_fobidden_status'),
                        
						btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					$jq.post(basePath + 'admin/Role/role_change',{'role_id':data.role_id,'status':'1'},function(data){
        		        		if(!ajaxCall(data)){
        		        			return;
        		        		}
                				layui.roleManager.initView();
                				layer.close(index);
                			})
        				}
        			})
        		}
        		//激活操作
        		if(obj.event === 'active'){
        			layer.open({
        				title: [language.get('message')],
        				
                        success: function (index, layero) {
                            $jq(':focus').blur();
                        },
content: language.get('edit_to_active_status'),
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					$jq.post(basePath + 'admin/Role/role_change',{'role_id':data.role_id,'status':'0'},function(data){
        		        		if(!ajaxCall(data)){
        		        			return;
        		        		}
                				layui.roleManager.initView();
                				layer.close(index);
                			})
        				}
        			})
        		}
        	});
        	
        	//表单监听事件(编辑角色状态切换)
        	form.on('switch(switchStatus)',function(data){
        		
        		if(data.value == '1'){
        			$jq('#status').val('0');
        		}else{
        			$jq('#status').val('1');
        		}
        	});

        	//表单监听事件(添加角色状态切换)
        	form.on('switch(switchStatusAdd)',function(data){
        		
        		if(data.value == '1'){
        			$jq('#status').val('0');
        		}else{
        			$jq('#status').val('1');
        		}
        	});

        	//监听表单提交验证事件
        	form.verify({
        		roleName : function(value,item){
        			if(value.length == 0){
        				return language.get('role_name_not_null');
        			}
        		},
        		roleDesc : function(value,item){
        			if(value.length == 0){
        				return language.get('role_desc_not_null');
        			}
        		},
        		companyId : function(value,item){
        			if(value.length == 0){
        				return language.get('company_not_null');
        			}
        		},
                roleType : function(value,item){
                    if(value.length == 0){
                        return language.get('role_type_not');
                    }
                },
        		// permissionAllocation : function(value,item){
                // 	var menu_id = [];
                // $jq('.checkb input:checked').each(function(){
                // 	menu_id.push($jq(this).val());
                // });
                // if(menu_id.length == 0){
                // 	return language.get('please_select_permission');
                // }
                // }
        	})

        	//表单监听事件(表单提交添加)
        	form.on('submit(addRoleForm)',function(data){
        		
        		var role_name = $jq('#role_name_add').val();
        		var role_desc = $jq('#role_desc_add').val();
        		var status = $jq('#status_add').val();
        		var company_id = $jq('#company_id_add').val();
        		var admin_class = $jq('#role_type_add').val();

        		
        		$jq.post(basePath + 'admin/Role/role_add',{
        			'role_name':role_name,
        			'role_desc':role_desc,
        			'status':status,
        			'company_id':company_id,
        			'admin_class':admin_class
        		},function(data){
        	 		if(!ajaxCall(data)) {
            			return;
            		}
        	 		$jq('#paramPage').show();
        			$jq('#itemPage').hide();
        			$jq('#addPage').hide();
        	 		layui.roleManager.initView();
        		})
        		return false;
        		
        	});


            form.on('submit(editRoleForm)', function(data){
                    layui.use(['authtree'], function () {
                        var authtree = layui.authtree;
                         authids = authtree.getChecked('#LAY-auth-tree-index');
                    });
                if(authids.length == 0){
                    layer.msg(layui.language.get('please_select_permission'),{icon:2,time:1000});
                    return false;
                }
                data.field.menu_id = authids.join(',');
                data.field.type = '2';


                $jq.post(basePath + 'admin/Role/role_edit',data.field,function(data){
                    if(!ajaxCall(data)) {
                        return;
                    }
                    layer.msg(layui.language.get('success'),{icon:1,time:1000},function(){
                        $jq('#paramPage').show();
                        $jq('#itemPage').hide();
                        layui.roleManager.initView();
                    });

                })

                return false;
            });


        },


        /**
         * 初始化角色编辑页面
         */
        initEditView : function(editData){

        	//（请选择）语言国际化
        	var layui_select = language.get('layui_select');

        	//初始化css
        	layui.roleManager.initCss(dataStore.get('current_lan'));
        	
        	//角色id
        	var roleId = editData.data.role_info.role_id;
        	if(roleId){
        		$jq('#role_id').val(roleId);
        	}
        	
        	//角色名称
        	var roleName = editData.data.role_info.role_name;
        	if(roleName){
        		$jq('#role_name').val(roleName);
        	}
        	//角色描述
        	var roleDesc = editData.data.role_info.role_desc;
        	if(roleDesc){
        		$jq('#role_desc').val(roleDesc);
        	}
        	//角色状态(0正常 1禁用)
        	var status = editData.data.role_info.status;
        	if(status & status == '1'){
        		$jq('#status').removeAttr('checked');
        		$jq('#status').val('1');
        	}else{
        		$jq('#status').attr('checked','');
        		$jq('#status').val('0');
        	}
        	
        	//初始化公司下拉框
        	$jq.post(basePath + 'admin/Base/company_list','',function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
            	var cp_nameHtml = '<option value="">' + layui_select + '</option>';
            	$jq.each(data.data,function(i,item){
            		cp_nameHtml += '<option value="' + item.cp_id + '">' + item.cp_name + '</option>';
            	})
            	$jq('#company_id').html(cp_nameHtml);
            	
            	//展示选中的下拉框
            	var companyId = editData.data.role_info.company_id;
            	if(companyId || companyId == '0'){
            		$jq('#company_id').val(companyId);
            	}
            	form.render('select');
        	});

            // //初始化角色属性下拉框
            // $jq.post(basePath + 'admin/Base/role_attribute_list','',function(data){
            //     if(!ajaxCall(data)){
            //         return;
            //     }
            //     var role_typeHtml = '<option value="">' + layui_select + '</option>';
            //     $jq.each(data.data,function(i,item){
            //         role_typeHtml += '<option value="' + item.id + '">' + item.name + '</option>';
            //     })
            //     $jq('#role_type').html(role_typeHtml);
            //     form.render('select');
            // });

            layui.use(['authtree'], function () {
                var authtree = layui.authtree;
                // 渲染时传入渲染目标ID，树形结构数据（具体结构看样例，checked表示默认选中），以及input表单的名字
                authtree.render('#LAY-auth-tree-index', editData.data.all_menu, {
                    inputname: 'authids[]'
                    ,layfilter: 'lay-check-auth'
                    // ,autoclose: false
                    // ,autochecked: false
                    // ,openchecked: true
                    // ,openall: true
                    ,autowidth: true
                });

            });

        },

        /**
		 * 角色编辑提交
         * @param role_id_arr
         */
		role_edit_sub :function(role_id_arr){
            var sendDatas = {};
            sendDatas.menu_id = role_id_arr.join(',');
            sendDatas.type = '2';
            sendDatas.status    = $jq('#status').val();
            sendDatas.role_id   = $jq('#role_id').val();
            sendDatas.role_name = $jq('#role_name').val();
            sendDatas.role_desc = $jq('#role_desc').val();
            sendDatas.status    = $jq('#status').val();
            sendDatas.company_id = $jq('#company_id').val();
            if(sendDatas.role_name.length == 0){
                layer.msg(layui.language.get('role_name_not_null'),{icon:2,time:1000});
                return false;
            }
            if(sendDatas.role_desc.length == 0){
                layer.msg(layui.language.get('role_desc_not_null'),{icon:2,time:1000});
                return false;
            }
            if(sendDatas.company_id.length == 0){
                layer.msg(layui.language.get('company_not_null'),{icon:2,time:1000});
                return false;
            }
            if(sendDatas.menu_id.length == 0){
                layer.msg(layui.language.get('please_select_permission'),{icon:2,time:1000});
                return false;
            }

            $jq.post(basePath + 'admin/Role/role_edit',sendDatas,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layer.msg(layui.language.get('success'),{icon:1,time:1000},function(){
                    $jq('#paramPage').show();
                    $jq('#itemPage').hide();
                    layui.roleManager.initView();
                });

            })
            return false;

		},



        // 全不选
		uncheckAll: function (dst){
        layui.use(['jquery', 'layer', 'authtree'], function(){
            var layer = layui.layer;
            var authtree = layui.authtree;
            authtree.uncheckAll(dst);
			});
		},

		// 全选
        checkAll : function (dst){
            layui.use(['jquery', 'layer', 'authtree'], function(){
                var layer = layui.layer;
                var authtree = layui.authtree;
                authtree.checkAll(dst);
            });
		},

		// 显示全部
		showAll : function (dst){
			layui.use(['jquery', 'layer', 'authtree'], function(){
				var layer = layui.layer;
				var authtree = layui.authtree;

				authtree.showAll(dst);
			});
		},

		// 隐藏全部
		closeAll: function (dst){
			layui.use(['jquery', 'layer', 'authtree'], function(){
				var layer = layui.layer;
				var authtree = layui.authtree;

				authtree.closeAll(dst);
			});
		},



        /*
         * 初始化css
         */
        initCss : function(lan){
        	if(lan == 'id'){
        		$jq('.layui-form-label-id').css('width','150px');
        		$jq('.layui-btn-id').css('margin-left','150px');
        	}else if(lan == 'cn'){
        		$jq('.layui-form-label-id').css('width','80px');
        		$jq('.layui-btn-id').css('margin-left','80px');
        	}
        },
        /*
         * 新增角色
         */
        viewAdd : function(){
			$jq('#paramPage').hide();
			$jq('#itemPage').hide();
			$jq('#addPage').show();
			
			var layui_select = language.get('layui_select');
			
			//初始化状态为开启
			$jq('#status_add').val(0);
			$jq('#status_add').attr('checked','');
			
        	//初始化公司下拉框
        	$jq.post(basePath + 'admin/Base/company_list','',function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
            	var cp_nameHtml = '<option value="">' + layui_select + '</option>';
            	$jq.each(data.data,function(i,item){
            		cp_nameHtml += '<option value="' + item.cp_id + '">' + item.cp_name + '</option>';
            	})
            	$jq('#company_id_add').html(cp_nameHtml);
            	
            	form.render('select');
        	});
            //初始化角色属性下拉框
            $jq.post(basePath + 'admin/Base/role_attribute_list','',function(data){
                if(!ajaxCall(data)){
                    return;
                }

                var role_typeHtml = '<option value="">' + layui_select + '</option>';
                $jq.each(data.data,function(i,item){
                    role_typeHtml += '<option value="' + item.id + '">' + item.name + '</option>';
                })
                $jq('#role_type_add').html(role_typeHtml);
                form.render('select');
            });
			
			form.render();
        }
    }

    //输出test接口
    exports('roleManager', obj);
});  


