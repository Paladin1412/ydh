layui.define(['form','jquery','laydate','table','laytpl','laypage','language'],function (exports) {
	
	var laydate = layui.laydate;
	var $jq = layui.jquery;
	var table = layui.table;
	var laytpl = layui.laytpl;
	var form = layui.form;
	var laypage = layui.laypage;
	var language = layui.language;
    var obj = {
        initView: function () {
        	language.render('page_employee_list');
        	language.render('employeeListTool');
        	language.render('addemployee');
            layui.form.render();
            //初始化搜索框中的时间
            obj.initDate();
            //初始化表格
            obj.getData(1,50);
        },
        /*
         * 初始化搜索框中的时间
         */
        initDate : function(){
        	laydate.render({
        		elem : '#date',
        		range : true,
        		done : function(value){
        			var search_string = $jq('#search_string').val();
        			$jq.post(basePath + 'admin/Personnel/user_list',{'date':value,'search_string':search_string},function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
        				layui.employeeList.initViewTable(data);
        			})
        		}
        	})
        },
        /*
         * 第一次进页面的初始化表格
         */
        getData : function(curr,limit){
        	var date = $jq('#date').val();
        	var search_string = $jq('#search_string').val();
        	$jq.post(basePath + 'admin/Personnel/user_list',{'page':curr,'limit':limit,'date':date,'search_string':search_string},function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		layui.employeeList.initViewTable(data);
        	})
        },
        /*
         * 初始化表格
         */
        initViewTable : function(data){
        	var field = data.data.field;
        	var initIndex = 0;
        	table.render({
        		elem : '#employeeListTable',
        		data: data.data.list,
        		limit: data.data.page.limit,
        		cols : [[
        			{field: 'num',title: 'ID',width:'5%',templet:function(d){
        				var size = data.data.page.limit;
        				var cur = data.data.page.page;
        				++initIndex;
        				return (cur-1)*size+initIndex;
        			}},
        			{field: 'user_name', title: field.user_name,width:'15%'},
        			{field: 'real_name', title: field.real_name,width:'15%'},
                    {field: 'email', title: field.email,width:'15%'},
                    {field: 'roles', title: field.role_name,width:'20%'},
                    {field: 'add_time', title: field.add_time,sort:true,width:'15%'},
                    {field: 'right', title: field.operate,width:'15%',align:'center', toolbar: '#employeeListTool'} //这里的toolbar值是模板元素的选择器
        		]],
        		id: 'employeeListTable',
        		page: false
        	});
         	  
      	  //执行重载
      	  //完整功能
        	
      	  var canFlush = false;
      	  laypage.render({
      	    elem: 'employeeListPage'
      	    ,count: data.data.page.count
      	    ,curr: data.data.page.page
      	    ,prev: '<em><</em>'
            ,next: '<em>></em>'
      	    ,limit: data.data.page.limit
      	    ,limits:[20, 50, 100]
      	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
      	    ,jump: function(obj){
      	    	
      	    	if(canFlush) {
      	    		layui.employeeList.getData(obj.curr,obj.limit);
      	    	}else {
      	    		canFlush=true;
      	    	}
      	        
      	    }
      	  });
        },
        /*
         * 新增员工--->>>新增弹出框
         */
        viewAdd : function() {
        	//得到所属公司
        	$jq.post(basePath + 'admin/Base/company_list','',function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		//(新增员工)语言国际化
        		var add_employee = language.get('add_employee');
        		var editData = {'admin_id':'','user_name':'','real_name':'','email':'','role_id':'','company_id':'','title':add_employee};
        		layui.employeeList.view(data.data,editData);
        	});
        },
        /*
         * 新增或修改弹出框
         */
        view : function(cpNameData,editData) {
        	//（请选择）语言国际化
        	var layui_select = language.get('layui_select');
        	
        	var html = addemployee.innerHTML;
        	html = html.replace('addemployeeForm','addemployeeForm2');
        	var htmlStr = laytpl(html).render(editData);
        	dataStore.set('is_edit_or_add',editData.title);//标志，作为判断是新增还是编辑以便对于密码的验证
        	layer.open({
        		type:1,
        		area:'600px',
        		title:[editData.title,'text-align:center;font-size:18px;'],
        		content:htmlStr,
        		cancel:function(){
        			//清空roles_menu和dateStore里面对应角色的信息
        			layui.employeeList.clearRolesAndDataStore();
        		}
        	});
        	//初始化公司下拉框
        	var cp_nameHtml = '<option value="">' + layui_select + '</option>';
        	$jq.each(cpNameData,function(i,item){
        		cp_nameHtml += '<option value="' + item.cp_id + '">' + item.cp_name + '</option>';
        	})
        	$jq('#company_id').html(cp_nameHtml);
        	if(editData.roles){
        		var roles_menuHtml = '';
        		$jq.each(editData.roles,function(i,item){
        			roles_menuHtml += '<li id="' + item.role_id + '">' + 
				        				'<label style="width:150px;">' + item.role_name + '</label>' + 
				        				'<a href="javascript:;" onclick="layui.employeeList.rolesDel(' + item.role_id + ')" class="role_del" id="del_' + item.role_id + '" style="margin-left:20px;color:blue" lay-filter="del">' + language.get('del') + '</a>' + 
				        				'</li>';
        			dataStore.set('roles_del' + item.role_id,item.role_name);
        		})
        		$jq('#roles_menu').append(roles_menuHtml);
        	}

        	language.render('addemployeeForm2');
            layui.form.render();
            if(editData.company_id || editData.company_id == 0){
        		$jq('select[name="company_id"]').val(editData.company_id);
        		layui.form.render();
        		$jq.post(basePath + 'admin/Base/role_list',{'cp_id':editData.company_id},function(data){
        			//初始化角色下拉框
        			var rolesHtml = '<option value="">' + layui_select + '</option>';
        			$jq.each(data.data,function(i,item){
        				rolesHtml += '<option value="' + item.role_id + '">' + item.role_name + '</option>';
        			})
        			$jq('#role_id').html(rolesHtml);
        			 form.render();
        		})
        	}
            layui.employeeList.initCss(dataStore.get('current_lan'));
        	
        },

        
        /*
         * 处理监听事件
         */
        tool : function(){
        	//表格监听事件
        	table.on('tool(employeeListTableEvent)',function(obj){
        		var data = obj.data;//获取一行的数据
        		//编辑操作
        		if(obj.event === 'edit'){
        			//(新增员工)语言国际化
            		var edit_employee = language.get('edit_employee');
        			var editData = {};
        			var cpNameData = [];
        			$jq.ajax({  
                        url: basePath + 'admin/Personnel/user_edit',  
                        type: "post",  
                        async: false,  //设置为同步
                        data: {  
                        	'admin_id':data.admin_id,'type':'1'
                        },  
                        dataType: "json",  
                        success: function (data) {  
                    		if(!ajaxCall(data)){
                    			return;
                    		}
                        	editData = data.data;
                        	editData['type'] = '2';
                        	editData['title'] = edit_employee;
                        }  
                    });  
        			//得到所属公司
        			$jq.ajax({  
                        url: basePath + 'admin/Base/company_list',  
                        type: "post",  
                        async: false,  //设置为同步
                        dataType: "json",  
                        success: function (data) {  
                    		if(!ajaxCall(data)){
                    			return;
                    		}
                        	cpNameData = data.data;
                        }  
                    });  
        			layui.employeeList.view(cpNameData,editData);
        		}
        		//禁用操作
        		if(obj.event === 'forbidden'){
        			layer.open({
        				title: [language.get('message')],
        				content: language.get('edit_to_fobidden_status'),
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					$jq.post(basePath + 'admin/Personnel/user_change',{'admin_id':data.admin_id,'status':'1'},function(data){
        		        		if(!ajaxCall(data)){
        		        			return;
        		        		}
                				layui.employeeList.initView();
                				layer.close(index);
                			})
        				}
        			})
        		}
        		//激活操作
        		if(obj.event === 'active'){
        			layer.open({
        				title: [language.get('message')],
        				content: language.get('edit_to_active_status'),
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					$jq.post(basePath + 'admin/Personnel/user_change',{'admin_id':data.admin_id,'status':'0'},function(data){
        		        		if(!ajaxCall(data)){
        		        			return;
        		        		}
                				layui.employeeList.initView();
                				layer.close(index);
                			})
        				}
        			})
        		}
        	});
        	
        	//监听表单公司下拉框事件
        	form.on('select(companyId)',function(data){
        		//（请选择）语言国际化
            	var layui_select = language.get('layui_select');
        		//清空roles_menu和dataStore里面对应角色的信息
        		layui.employeeList.clearRolesAndDataStore();
        		
        		$jq.post(basePath + 'admin/Base/role_list',{'cp_id':data.value},function(data){
        			//初始化角色下拉框
        			var rolesHtml = '<option value="">' + layui_select + '</option>';
        			$jq.each(data.data,function(i,item){
        				rolesHtml += '<option value="' + item.role_id + '">' + item.role_name + '</option>';
        			})
        			$jq('#role_id').html(rolesHtml);
        			form.render('select');
        		})
        	});
        	
        	//监听表单角色下拉框事件
        	form.on('select(roleId)',function(data){
        		//（删除）语言国际化
            	var del = language.get('del');
        		var role_id = data.value;
        		var role_name = $jq('#role_id option:selected').text();
        		if(language.get('layui_select') == role_name){
        			return;
        		}
        		var roles_menuHtml = '';
//        		console.log(dataStore.get('roles_del' + role_id));判断该角色id是否存于dataStore中，不存在则添加
        		if(!dataStore.get('roles_del' + role_id)){
        			roles_menuHtml = '<li id="' + role_id + '">' + 
        									'<label style="width:150px;">' + role_name + '</label>' + 
        									'<a href="javascript:;" onclick="layui.employeeList.rolesDel(' + role_id + ')" class="role_del" id="del_' + role_id + '" style="margin-left:20px;color:blue" lay-filter="del">' + del + '</a>' + 
        								'</li>';
            		dataStore.set('roles_del'+role_id,role_name);
        		}
        		$jq('#roles_menu').append(roles_menuHtml);
        	});
        	
        	//监听表单验证事件
        	form.verify({
        		user_name : function(value,item){
        			if(value.length < 4){
        				return language.get('username_length_limit');
        			}
        		},
        		real_name : function(value,item){
        			if(value.length == 0){
        				return language.get('name_not_null');
        			}
        		},
        		password : function(value,item){
        			if(dataStore.get('is_edit_or_add') == language.get('add_employee')){
        				if(value.length == 0){
            				return language.get('password_not_null');
            			}
        			}
        		},
        		email: [/^[a-zA-Z0-9._%-]+@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,4}$|^1[3|4|5|7|8]\d{9}$/, language.get('email_reg_not_right')],
        		companyId : function(value,item){
        			if(value.length == 0){
        				return language.get('company_not_null');
        			}
        		},
        		roles : function(value,item){
        			if($jq('#roles_menu li').length == 0){
        				return language.get('role_not_null');
        			}
        		}
        	});
        	
        	//监听表单提交
        	form.on('submit(addOrUpdateemployeeForm)',function(data){
        		//得到表单里面数据的新密码以及id
        		var role_id = [];
        		var lis = document.getElementById('roles_menu').getElementsByTagName("li");
        		for(var i = 0;i < lis.length; i++){
        			role_id.push(lis[i].id);
        			dataStore.del('roles_del'+lis[i].id);
        		}
        		data.field['role_id'] = role_id.join(',');
        		
        		if($jq('#admin_id').val().length > 0){
        			data.field['type'] = '2';
        			$jq.post(basePath + 'admin/Personnel/user_edit',data.field,function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
                		layer.msg(layui.language.get('operate_finished'),{icon:1,time:1000},function(){
                            layer.closeAll();
                            layui.employeeList.initView();
                        });
            		})
        		}else{
        			$jq.post(basePath + 'admin/Personnel/user_add',data.field,function(data){
                		if(!ajaxCall(data)){
                			return;
                		}
                		layer.msg(layui.language.get('operate_finished'),{icon:1,time:1000},function(){
                            layer.closeAll();
                            layui.employeeList.initView();
                        });
            		})
        		}
        		//至关重要，防止表单提交
        		return false;
        	});
        },
        /*
         * 移除所选角色
         */
        rolesDel : function(role_id){
        	$jq('#' + role_id).remove();
        	dataStore.del('roles_del' + role_id);
        },
        /*
         * 清空roles_menu和dataStore里面对应角色的信息
         */
        clearRolesAndDataStore : function(){
        	var lis = document.getElementById('roles_menu').getElementsByTagName("li");
    		for(var i = 0;i < lis.length; i++){
    			dataStore.del('roles_del'+lis[i].id);
    		}
    		$jq('#roles_menu li').remove();
        },
        /*
         * 初始化css
         */
        initCss : function(lan){
        	if(lan == 'id' || lan == 'en'){
        		$jq('.layui-form-label-id').css('width','130px');
        		$jq('.layui-input-inline-id').css('width','350px');
        	}else if(lan == 'cn'){
        		$jq('.layui-form-label-id').css('width','80px');
        		$jq('.layui-input-inline-id').css('width','400px');
        	}
        }
        
    }

    //输出test接口
    exports('employeeList', obj);
});  


