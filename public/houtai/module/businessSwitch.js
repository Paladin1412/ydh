layui.define(['jquery','form','language'],function (exports) {
	
	var $jq = layui.jquery;
	var form = layui.form;
	var language = layui.language;
	
    var obj = {
        initView: function () {
        	language.render('page_business_switch');
        	
        	//根据语言调整宽度
        	this.initCss();
        	
        	$jq.post(basePath + 'admin/System/loan_change',{'type':'1'},function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
        		//放款状态
        		if(data.data.type_id){
        			$jq('#status').removeAttr('disabled');
        			$jq('#type_id').val(data.data.type_id);
        			var status = data.data.open;
        			$jq('#status').val(status);
        			if(status && status == '1'){
        				$jq('#status').attr('checked','');
        			}else{
        				$jq('#status').removeAttr('checked');
        			}
        		}else{
        			$jq('#status').attr('disabled','disabled');
        		}


        		//获取角色类型
        		var roleType = dataStore.get('global_role_type');
        		if(roleType == '6'){
        			$jq('#hid_code').show();
        			var code_status = data.data.is_verify_open;
        			$jq('#code_status').val(code_status);
        			if(code_status == '1'){
        				$jq('#code_status').attr('checked','');
        			}else{
        				$jq('#code_status').removeAttr('checked');
        			}
        		}else{
        			$jq('#hid_code').hide();
        		}

        		if(roleType == '6' || roleType == '5'){
                    $jq('#sms_set').show();
                    $jq("input[name=sms_choice][value=1]").attr("checked", data.data.sms_choice == "1" ? true : false);
                    $jq("input[name=sms_choice][value=2]").attr("checked", data.data.sms_choice == "2" ? true : false);
                    form.render();
				}

    		});
        },



        /*
         * 处理监听事件
         */
        
        tool : function(){
        	//业务开关
        	form.on('switch(switchbusiness)',function(data){
        		if(data.value == '1'){
        			layer.open({
        				title: [language.get('message')],
        				content: language.get('edit_to_closed_status'),
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					layui.businessSwitch.editStatus('2','0','');
        					$jq('#status').val('0');
        				},
        				btn2:function(index){
        					layui.businessSwitch.editStatus('2','1','');
        					$jq('#status').val('1');
        				},
        				cancel:function(){
        					layui.businessSwitch.editStatus('2','1','');
        					$jq('#status').val('1');
        				}
        			})
        		}else{
        			layer.open({
        				title: [language.get('message')],
        				content: language.get('edit_to_opened_status'),
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					layui.businessSwitch.editStatus('2','1','');
        					$jq('#status').val('1');
        				},
        				btn2:function(index){
        					layui.businessSwitch.editStatus('2','0','');
        					$jq('#status').val('0');
        				},
        				cancel:function(){
        					layui.businessSwitch.editStatus('2','0','');
        					$jq('#status').val('0');
        				}
        			})
        		}
        	});
        	//验证码切换
        	form.on('switch(switchCodeStatus)',function(data){
        		if(data.value == '1'){
        			layer.open({
        				title: [language.get('message')],
        				content: language.get('edit_to_close_status'),
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					layui.businessSwitch.editStatus('2','','2');
        					$jq('#code_status').val('2');
        				},
        				btn2:function(index){
        					layui.businessSwitch.editStatus('2','','1');
        					$jq('#code_status').val('1');
        				},
        				cancel:function(){
        					layui.businessSwitch.editStatus('2','','1');
        					$jq('#code_status').val('1');
        				}
        			})
        		}else{
        			layer.open({
        				title: [language.get('message')],
        				content: language.get('edit_to_open_status'),
        				btn: [language.get('certainly'),language.get('cancel')],
        				yes: function(index){
        					layui.businessSwitch.editStatus('2','','1');
        					$jq('#code_status').val('1');
        				},
        				btn2:function(index){
        					layui.businessSwitch.editStatus('2','','2');
        					$jq('#code_status').val('2');
        				},
        				cancel:function(){
        					layui.businessSwitch.editStatus('2','','2');
        					$jq('#code_status').val('2');
        				}
        			})
        		}
        	});
        	//短信切换
            // form.on('radio(sms_choice)', function(data){
             //    var sendDatas ={};
             //        sendDatas.type ='2';
             //        sendDatas.sms_choice = data.value;
             //    layer.open({
             //        title: [language.get('message')],
             //        content: language.get('yes_change'),
             //        btn: [language.get('certainly'),language.get('cancel')],
             //        yes: function(){
             //            $jq.post(basePath+'admin/System/loan_change',sendDatas,function(data) {
             //                if (!ajaxCall(data)) {
             //                    return;
             //                }
             //                layui.businessSwitch.initView();
             //                layer.closeAll();
             //            });
             //        },
             //        btn2:function(){
             //            layui.businessSwitch.initView();
             //        },
             //        cancel:function(){
             //            layui.businessSwitch.initView();
             //        }
             //    })
            //
            //
            //
            //
            // });

        },
    /**
	 * 修改状态
	 * @param type 类型
	 * @param status
	 * @param investStatus 投资状态
	 * @param codeStatus   验证码
     */

        editStatus : function(type,status,codeStatus){
        	var sendDatas = {};
        	sendDatas.type_id = $jq('#type_id').val();
        	sendDatas.type   = type;
        	sendDatas.status = status;
        	sendDatas.is_verify_state = codeStatus;
			$jq.post(basePath + 'admin/System/loan_change',sendDatas,function(data){
        		if(!ajaxCall(data)){
        			return;
        		}
				layui.businessSwitch.initView();
				layer.closeAll();
			})
        },



        /**
         * 根据语言调整宽度
         */
        initCss : function(){
        	var lan = dataStore.get('current_lan');
        	if(lan == 'cn'){
        		$jq('.layui-form-label-lan').css('width','100px');
        	}else if(lan == 'id' || lan == 'en'){
        		$jq('.layui-form-label-lan').css('width','125px');
        	}
        }
        
    }

    //输出test接口
    exports('businessSwitch', obj);
});  


