layui.define(['jquery','table','laypage','language','laytpl','form','upload'],function (exports) {
	
	var $jq = layui.jquery;
	var table = layui.table;
	var laypage = layui.laypage;
	var language = layui.language;
	var laytpl = layui.laytpl;
	var form = layui.form;
	var upload = layui.upload;
	
	
    var obj = {
        initView: function () {
        	language.render('page_company_verify');
        	language.render('companyVerifyTool');
        	language.render('companyVerifyInfo');
            obj.getData(1,20);   
        }
	    // 请求数据 start
	    ,getData: function(curr,limit) {
	    	var $jq = layui.jquery;
	    	$jq.post(basePath+'admin/Company/company_todo',{'page':curr,'limit':limit},function(data){
	    		if(!ajaxCall(data)) {
	    			return;
	    		}
	    		layui.company_verify.loadData(data.data);
	    	}); 
	    }
	    ,loadData: function(data) {
	    	
	    	var fieldArr = data.field;
	    	var initIndex = 0;
	    	  //方法级渲染
	    	  table.render({
	    	    elem: '#company_verify_table'
	    	    ,data:data.list
	    	    ,limit:1000
	    	    ,cols: [[
	    			{field: 'num',title: 'ID',width:50,templet:function(d){
	    				var size = data.page.limit;
	    				var cur = data.page.page;
	    				++initIndex;
	    				return (cur-1)*size+initIndex;
	    			}}
//					,{field:'cp_id', title: 'ID'}
					,{field:'cp_name', title: fieldArr['cp_name'],minWidth:96}
					,{field:'cp_contact_person', title: fieldArr['cp_contact_person'],minWidth:96}
					,{field:'cp_mobile', title: fieldArr['cp_mobile'],minWidth:96}
					,{field:'cp_address', title: fieldArr['cp_address'],minWidth:96}
					,{field:'country_name', title: fieldArr['cp_country']}
					,{field:'apply_status', title: fieldArr['status']}
					,{field:'apply_date', title: fieldArr['operator_date'],width:"10%"}
					,{field:'right',align:'center', title: fieldArr['operate'],width:"30%",toolbar: '#companyVerifyTool'}
	    	    ]]
	    	    ,id: 'company_verify_table'
	    	    ,page: false
	    	  });
	    	  
	    	  //执行重载
	    	  //完整功能
	    	  var canFlush = false;
	    	  laypage.render({
	    	    elem: 'company_verify_page'
	    	    ,count: data.page.count
	    	    ,curr: data.page.page
          	    ,prev: '<em><</em>'
                ,next: '<em>></em>'
	    	    ,limit: data.page.limit
	    	    ,limits:[20, 50, 100]
	    	    ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
	    	    ,jump: function(obj){
	    	    	
	    	    	if(canFlush) {
	    	    		layui.company_verify.getData(obj.curr,obj.limit);
	    	    	}else {
	    	    		canFlush=true;
	    	    	}
	    	        
	    	    }
	    	  });
	    }
	    /*
	     * 处理监听事件
	     */
	    ,tool : function(){
	    	table.on('tool(companyVerifyEvent)',function(obj){
	    		var data = obj.data;//获取一行的数据
	    		//查看
	    		if(obj.event === 'look'){
	    			$jq.post(basePath + 'admin/Company/company_edit',{'cp_id':data.cp_id,'type':'1'},function(data){
	    	    		if(!ajaxCall(data)){
	            			return;
	            		}
	    	    		layui.company_verify.lookCompanyVerify(data.data);
	    	    	})
	    		}
	    		//编辑
	    		if(obj.event === 'edit'){
	    			$jq.post(basePath + 'admin/Company/company_edit',{'cp_id':data.cp_id,'type':'1'},function(data){
	    	    		if(!ajaxCall(data)){
	            			return;
	            		}
	    	    		$jq('#paramPage').hide();
	    		    	$jq('#itemPage').show();
	    	    		layui.company_verify.editCompanyVerify(data.data);
	    	    	})
	    		}
	    		//通过
	    		if(obj.event === 'pass'){
	    			layer.open({
        				title: [language.get('message')],
        				content: language.get('sure_pass_applay'),
                        success: function (index, layero) {
                            $jq(':focus').blur();
                        },
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					$jq.post(basePath + 'admin/Company/company_change',{'cp_id':data.cp_id,'type':'1'},function(data){
        		        		if(!ajaxCall(data)){
        		        			return;
        		        		}
                				layui.company_verify.initView();
                				layer.close(index);
                			})
        				}
        			})
	    		}
	    		//拒绝
	    		if(obj.event === 'refuse'){
	    			layer.open({
        				title: [language.get('message')],
        				content: language.get('sure_refuse_applay'),
                        success: function (index, layero) {
                            $jq(':focus').blur();
                        },
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					$jq.post(basePath + 'admin/Company/company_change',{'cp_id':data.cp_id,'type':'2'},function(data){
        		        		if(!ajaxCall(data)){
        		        			return;
        		        		}
                				layui.company_verify.initView();
                				layer.close(index);
                			})
        				}
        			})
	    		}
	    		
	    	});
	    	//表单验证
	    	form.verify({
	    		cpNum : function(value,item){
	    			if(value.length == 0){
        				return language.get('cp_num_not_null');
        			}
	    		},
	    		cpName : function(value,item){
	    			if(value.length == 0){
        				return language.get('cp_name_not_null');
        			}
	    		},
	    		cpMobile : function(value,item){
	    			if(value.length == 0){
        				return language.get('cp_mobile_not_null');
        			}
	    		},
	    		cpContactPerson : function(value,item){
	    			if(value.length == 0){
        				return language.get('cp_contact_person_not_null');
        			}
	    		},
	    		cpLegPerson : function(value,item){
	    			if(value.length == 0){
        				return language.get('cp_leg_person_not_null');
        			}
	    		},
	    		cpEmail: [/^[a-z0-9._%-]+@([a-z0-9-]+\.)+[a-z]{2,4}$|^1[3|4|5|7|8]\d{9}$/, language.get('cp_email_reg_not_right')],
	    		cpaddress : function(value,item){
	    			if(value.length == 0){
        				return language.get('cp_address_not_null');
        			}
	    		},
	    		cpCountry : function(value,item){
	    			var lan = language.get('layui_select');
	    			if(value.length == 0){
        				return language.get('cp_country_not_null');
        			}
	    		},
	    		businessCard : function(value,item){
	    			if(value.length == 0){
        				return language.get('business_card_not_null');
        			}
	    		},
	    		cpContract : function(value,item){
	    			if(value.length == 0){
        				return language.get('contract_not_null');
        			}
	    		}
	    	});
	    	
	    	//监听表单提交
	    	form.on('submit(editCompanyForm)',function(data){
	    		if(data.field.business_card.substr(0,1) == ','){
	    			data.field.business_card = data.field.business_card.substr(1);
	    		}
	    		if(data.field.cp_contract.substr(0,1) == ','){
	    			data.field.cp_contract = data.field.cp_contract.substr(1);
	    		}
	    		data.field['type'] = '2';
	    		$jq.post(basePath + 'admin/Company/company_edit',data.field,function(data){
	    			if(!ajaxCall(data)){
	        			return;
	        		}
            		layer.msg(layui.language.get('operate_finished'),{icon:1,time:1000},function(){
                        layer.closeAll();
                        layui.company_verify.backCompany();
                    });
	    			
	    		})
	    		return false;
	    	})
	    }
	    /*
	     * 查看公司审核详情
	     */
	    ,lookCompanyVerify : function(data){
	    	var html = companyVerifyInfo.innerHTML;
	    	var htmlStr = laytpl(html).render(data);
	    	layer.open({
	    		title: language.get('message'),
	    		type: 1,
	    		area: ['1000px'],
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
	    		content: htmlStr
	    	})
	    }
	    /*
	     * 编辑合作公司
	     */
	    ,editCompanyVerify : function(data){
	    	//清空图片区域
	    	$jq('#business_card_all').empty();
	    	$jq('#cp_contract_all').empty();
	    	
	    	//初始化所属国家下拉框
	    	var cp_country = data.cp_country;
	    	$jq.post(basePath + 'admin/Base/get_country_list','',function(_data){
	    		if(!ajaxCall(_data)){
        			return;
        		}
	    		var countryHtml = '<option value="">' + language.get('layui_select') + '</option>';
	    		$jq.each(_data.data,function(index,item){
	    			countryHtml += '<option value="' + item.c_id + '">' + item.country_name + '</option>'
	    		})
	    		$jq('#cp_country').html(countryHtml);
	    		if(cp_country){
		    		$jq('#cp_country').val(cp_country);
		    	}
	    		form.render('select');
	    		
	    	});
	    	
	    	var html = editCompany.innerHTML;
	    	laytpl(html).render(data,function(htmlStr){
	    		$jq('#itemPage').html(htmlStr);
	    	})
	    	
	    	$jq.each(data.cp_contract,function(i,item){
	    		var cp_contract = $jq('#cp_contract').val();
	    		$jq('#cp_contract').val(cp_contract+','+item.image_code);
	    	})
	    	
	    	$jq.each(data.business_card,function(i,item){
	    		var business_card = $jq('#business_card').val();
	    		$jq('#business_card').val(business_card+','+item.image_code);
	    	})
	    		
	    	layui.company_verify.initCss(dataStore.get('current_lan'));
	    	language.render('itemPage');
	    	//上传图片
	    	layui.company_verify.uploadRender();
	    	
	    	form.render();
	    	
	    }
	    /*
	     * 上传图片
	     */
	    ,uploadRender : function(){
	    	upload.render({
	    		elem : '#business_card_upload'
	    		,url: basePath + 'admin/Company/company_upload_image'
	    		,multiple: true
	    		,field:'image'
	    	    ,done: function(res){
	    	    	var code = "\'" + res.data.image_code + "\'";
	    	    	$jq('#business_card_all').append('<div id="' + res.data.image_code + '" style="margin:10px;width:100px;text-align:center" class="layui-inline">' +
	    	    										'<input type="hidden" id="businessImageCode" value="'+ res.data.image_code +'">' +
	    	    										'<img class="y-img-li" onclick="showImg(this)" src="'+ res.data.image_url +'" alt="" class="layui-upload-img">' +
	    	    										'<label>' +
	    	    										'<a href="javascript:;" onclick="layui.company_verify.del('+code+')" style="color:blue;font-size:24px;">' + language.get('del') + '</a>' +
	    	    										'</label>' +
	    	    										'</div>')
	    	    	var business_card = $jq('#business_card').val();
		    		$jq('#business_card').val(business_card+','+res.data.image_code);
	    	    }
	    			
	    	});
	    	upload.render({
	    		elem : '#cp_contract_upload'
	    		,url: basePath + 'admin/Company/company_upload_image'
	    		,multiple: true
	    		,field:'image'
	    	    ,done: function(res){
	    	    	var code = "\'" + res.data.image_code + "\'";
	    	    	$jq('#cp_contract_all').append('<div id="' + res.data.image_code + '" style="margin:10px;width:100px;text-align:center" class="layui-inline">' +
	    	    										'<input type="hidden" id="contractImageCode" value="'+ res.data.image_code +'">' +
	    	    										'<img class="y-img-li" onclick="showImg(this)" src="'+ res.data.image_url +'" alt="" class="layui-upload-img">' +
	    	    										'<label>' +
	    	    										'<a href="javascript:;" onclick="layui.company_verify.del('+code+')" style="color:blue;font-size:24px;">' + language.get('del') + '</a>' +
	    	    										'</label>' +
	    	    										'</div>')
	    	    	var cp_contract = $jq('#cp_contract').val();
		    		$jq('#cp_contract').val(cp_contract+','+res.data.image_code);
	    	    }
	    			
	    	})
	    }
	    /*
	     * 返回父页面
	     */
	    ,backCompany : function(){
	    	$jq('#paramPage').show();
	    	$jq('#itemPage').hide();
	    	layui.company_verify.initView();
	    	return false;
	    }
	    /*
	     * 删除图片
	     */
	    ,del : function(data){
	    	document.getElementById(data).remove();
	    	var cp_contract = $jq('#cp_contract').val().replace(','+data,'');
	    	$jq('#cp_contract').val(cp_contract);
	    	var business_card = $jq('#business_card').val().replace(','+data,'');
	    	$jq('#business_card').val(business_card);
	    },
	    //初始化css
	    initCss : function(lan){
	    	if(lan == 'id' || lan == 'en'){
	    		$jq('.layui-form-label-id').css('width','250px');
	    		$jq('.layui-elem-field-id').css('margin-left','100px');
	    		$jq('.layui-upload-drag-id').css('margin-left','100px');
	    		$jq('.layui-elem-quote-id').css('margin-left','100px');
	    		$jq('.layui-btn-id').css('margin-left','200px');
	    	}else if(lan == 'cn'){
	    		$jq('.layui-form-label-id').css('width','100px');
	    		$jq('.layui-elem-field-id').css('margin-left','20px');
	    		$jq('.layui-upload-drag-id').css('margin-left','20px');
	    		$jq('.layui-elem-quote-id').css('margin-left','20px');
	    		$jq('.layui-btn-id').css('margin-left','100px');
	    	}
	    }

    }


    //输出test接口
    exports('company_verify', obj);
});  


