layui.define(['jquery','form','language','upload'],function(exports) {
	
	var $jq = layui.jquery;
	var form = layui.form;
	var language = layui.language;
	var upload = layui.upload;

	var obj = {
		initView : function() {
			obj.initCss(dataStore.get('current_lan'));
			
			language.render('page_company_apply');
			obj.initForm();
			obj.renderFile();
		},
		renderFile : function() {
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
	    	    										'<a href="javascript:;" onclick="layui.company_apply.del('+code+')" style="color:blue;font-size:24px;">' + language.get('del') + '</a>' +
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
	    	    										'<a href="javascript:;" onclick="layui.company_apply.del('+code+')" style="color:blue;font-size:24px;">' + language.get('del') + '</a>' +
	    	    										'</label>' +
	    	    										'</div>')
	    	    	var cp_contract = $jq('#cp_contract').val();
		    		$jq('#cp_contract').val(cp_contract+','+res.data.image_code);
	    	    }
	    			
	    	})
		}
		// 初始化页面 start
		,
		initForm : function() {
			// state
			$jq.post(basePath + 'admin/Base/get_country_list', '', function(_data) {

				if (!ajaxCall(_data)) {
					return;
				}
				
				var data = _data.data;
				if (!data)
					return;
				var html = '<option value="">' + language.get('layui_select') + '</option>';
				for ( var index in data) {
					var item = data[index];
					html = html + '<option value="' + item.c_id + '">' + item.country_name + '</option>';
				}
				$jq('#cp_country').html(html);
				form.render('select');

			});
			layui.form.render();
		} // 初始化页面 end
	    /*
	     * 删除图片
	     */
	    ,del : function(data){
	    	document.getElementById(data).remove();
	    	var cp_contract = $jq('#cp_contract').val().replace(','+data,'');
	    	$jq('#cp_contract').val(cp_contract);
	    	var business_card = $jq('#business_card').val().replace(','+data,'');
	    	$jq('#business_card').val(business_card);
	    }
	    /*
	     * 处理监听事件
	     */
	    ,tool : function(){
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
	    		cpEmail: [/^[a-zA-Z0-9._%-]+@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,4}$|^1[3|4|5|7|8]\d{9}$/, language.get('cp_email_reg_not_right')],
	    		cpaddress : function(value,item){
	    			if(value.length == 0){
        				return language.get('cp_address_not_null');
        			}
	    		},
	    		cpCountry : function(value,item){
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
	    	
	    	//表单提交
	    	form.on('submit(applyCompanyForm)',function(data){
	    		if(data.field.business_card.substr(0,1) == ','){
	    			data.field.business_card = data.field.business_card.substr(1);
	    		}
	    		if(data.field.cp_contract.substr(0,1) == ','){
	    			data.field.cp_contract = data.field.cp_contract.substr(1);
	    		}
	    		data.field['type'] = '2';
	    		$jq.post(basePath + 'admin/Company/company_add',data.field,function(data){
	    			if(!ajaxCall(data)){
	        			return;
	        		}
            		layer.msg(layui.language.get('operate_finished'),{icon:1,time:1000},function(){
                        layer.closeAll();
                    });
	    			//清空图片区域
	    	    	$jq('#business_card_all').empty();
	    	    	$jq('#cp_contract_all').empty();
	    			layui.company_apply.initView();
	    		})
	    		return false;
	    	})
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
	exports('company_apply', obj);
});

