layui.define(['verify_common','language'],function (exports) {
	
	var $jq = layui.jquery,
		table =layui.table;
	
    var obj = {
        initView: function (orderNo,view_type) { //页面类型  0 所有审批,通过  1 待初审，待终审
        	obj.initData(orderNo,view_type)
        }
        ,initData:function(orderNo,view_type){
            var sendData={};
            sendData.order_no = orderNo;

            $jq.post(basePath+'admin/Order/order_handle_info',sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                $jq.get('verify/verify-detail.html',sendData,function(html){
                    if(!ajaxCall(html)) {
                        return;
                    }
                layui.verify_common.showDetailPage('detail');
                layui.verify_detail.loadDetailData(data.data,html,orderNo,view_type);


                });
            });
        }

        /**
         * 初始化详情页
         * @param data
         * @param _html
         * @param order_no
         * @param view_type 页面类型  0 所有审批,通过  1 待初审，待终审
         */
        ,loadDetailData:function(data,_html,order_no,view_type) {
                var lang_img ={};
                lang_img.id_card_photo = layui.language.get('id_card_photo'); //身份证
                lang_img.credit_photo = layui.language.get('credit_photo'); //签名图片
                lang_img.tax_card = layui.language.get('tax_card');   //税卡
                lang_img.security_card = layui.language.get('security_card'); //社保卡
                lang_img.family_card = layui.language.get('family_card');  //家庭卡
                lang_img.staff_card = layui.language.get('staff_card');   //员工卡
                lang_img.work_prove = layui.language.get('work_prove');  //在职证明
                lang_img.salary_card = layui.language.get('salary_card');    //工资卡
                lang_img.face_check = layui.language.get('face_check');    //假脸判定
                lang_img.living_photo = layui.language.get('living_photo');    //活体照片
                data.field.lang_img = lang_img;
                var roleType = dataStore.get('global_role_type');
                data.role_type = roleType;

                layui.laytpl(_html).render(data,function(html){
                    $jq = layui.jquery;
                    $jq('#all_verify_detail').html(html);
                });
                if(view_type == '0'){
                    $jq('#add_approve_jieguo').hide();
                }
                layui.language.render('all_verify_detail');
                layui.language.render('phoneList');
                layui.verify_detail.handel_no_pass_reason();
                if(view_type == "1"){
                    layui.verify_detail.initFollowInfoNew(order_no);
                }
                layui.verify_detail.loadDetailHandelLog(data.handle_log,data.field.order_flow_log);
                layui.verify_detail.loadDetailApproveLog(data.order_review_log,data.field.order_review_log);
                layui.verify_detail.historyOrderRecord(data.history_order_list,data.field.history_order);

            if(  roleType == '1' && data.order_info.now_state == '4'){   //如果是信审专员，待审核订单则不展示审核框
                $jq('#add_approve_jieguo').hide();
            }

        }


        /**
         * 获取信审不通过原因
         * @returns {boolean}
         */
         ,handel_no_pass_reason:function() {
                $jq.post(basePath+'admin/Base/get_handle_not_pass_list',"",function(data_handle) {
                    if (!ajaxCall(data_handle)) {
                        return;
                    }

                    var data_handle_arr =data_handle.data;
                    var approve_reasonHtml = '<option value="">-- ' + layui.language.get('please_sel') + ' --</option>';

                    for (var item in data_handle_arr) {
                        approve_reasonHtml += '<option value="' + item + '">'+ item +' (' +data_handle_arr[item] +')'+ '</option>';
                    }
                    $jq('#approve_flow_type').html(approve_reasonHtml);
                    layui.form.render('select');
                    layui.form.render();
                });
                return false;
            }


        /**
         * 审核记录
         * @param order_review_log_data
         * @param field
         */
        ,loadDetailApproveLog:function(order_review_log_data,field) {
            var data = {};
            if(order_review_log_data){
                data = order_review_log_data;
            }
            $jq = layui.jquery;
            var table = layui.table;
            //方法级渲染
            table.render({
                elem: '#approve_log'
                ,data:data
                ,autoHeight:true
                ,id: 'approve_log'
                ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                    layout: [ 'count', 'prev', 'page', 'next','limit', 'skip'] //自定义分页布局
                    ,curr:1 //设定初始在第 5 页
                    ,limits:[3,5]
                }
                ,limit: 5
                ,cols: [[
                    {field:'add_time', title: field.add_time}
                    ,{field:'real_name', title: field.admin_name}
                    ,{field:'result_type', title: field.review_desc}
                    ,{field:'remark', title: field.remark}
                    ,{field:'refuse_type', title: field.refuse_desc}

                ]]
            });
        }


        /**
         *  电核记录
         * @param folow_data
         * @param field
         */
        ,loadDetailHandelLog:function(folow_data,field) {
            var data = {};
            if(folow_data){
                data = folow_data;
            }
            $jq = layui.jquery;
            var table = layui.table;
              //方法级渲染
              table.render({
                elem: '#handle_log'
                ,data:data
                ,autoHeight:true
                ,id: 'handle_log'
                ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                      layout: [ 'count', 'prev', 'page', 'next','limit', 'skip'] //自定义分页布局
                      ,curr:1 //设定初始在第 5 页
                      ,limits:[3,5]
                  }
                ,limit: 5
                ,cols: [[
                  {field:'add_time', title: field.add_time}
                 ,{field:'flow_name', title: field.name}
                 ,{field:'flow_relation', title: field.relation}
                 ,{field:'flow_phone', title: field.phone}
                 ,{field:'flow_desc', title: field.remark}
                 ,{field:'phone_status', title: field.status}
                 ,{field:'real_name', title: field.admin_name}
                ]]

              });
        }


        /**
         *  历史订单记录
         * @param history_order_data
         * @param field
         */
        ,historyOrderRecord:function(history_order_data,field) {
                var data ={};
                if(history_order_data){
                    data = history_order_data;
                }
                //方法级渲染
                table.render({
                    elem : '#history_order_record',
                    data: data,
                    autoHeight:true,
                    cols : [[
                        {field:'order_no', title: field.order_no}
                        ,{field:'lending_time', title: field.lending_time}
                        ,{field:'repay_time', title: field.repay_time}
                        ,{field:'due_day', title: field.over_day}
                    ]]
                   ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                        layout: [ 'count', 'prev', 'page', 'next','limit', 'skip'] //自定义分页布局
                        ,curr:1 //设定初始在第 5 页
                        ,limits:[3,5]
                    }
                    ,limit: 5
                });
            }


         /**
         * @param orderNo
         * @param state
         * @param msg
         */
        ,verify:function(orderNo,state,msg) {
            $jq = layui.jquery;
            var sendData = {};
            sendData.order_no = orderNo;
            sendData.handle_state = state;
            if(state==2){
                sendData.flow_type = $jq('#flow_desc').val();
            }
            $jq('#verify_detail_1').attr('disabled',true);
            $jq('#verify_detail_1').removeClass('y-btn-green');
            $jq('#verify_detail_1').addClass('layui-btn layui-btn-disabled');

            $jq('#verify_detail_2').attr('disabled',true);
            $jq('#verify_detail_2').removeClass('y-btn-green');
            $jq('#verify_detail_2').addClass('layui-btn layui-btn-disabled');

            $jq.post(basePath+'admin/Order/handle_change',sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                layer.closeAll();
                layui.verify_common.showDetailPage('parent');
            });
        }


        /**
         * 审核审核结果提交
         * @param order_no  订单号
         * @param handle_state  审核状态   1为 初审通过  2为 初审不通过(拒绝) 3 终审通过 4 终审不通过(退回)
         */
        ,ShenHeSubmit:function(handle_state) {
            var confirm = layui.language.get('queren')+"?";
            if(handle_state == '1' || handle_state == '3'){
                 confirm = layui.language.get('sure_to_pass');
            }else if(handle_state == '4'){
                confirm = layui.language.get('queren')+layui.language.get('tuihui_btn')+' ?';
            }else{
                confirm = layui.language.get('sure_to_refuse');
            }

            layer.open({
                title: [layui.language.get('kindly_reminder'),'text-align:center;'],
                content: confirm,
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
                btn: [layui.language.get('certainly'),layui.language.get('cancel')],
                yes: function(index){
                    var sendDatas = {};
                    sendDatas.order_no     = $jq('#verify_detail_order_no').val();
                    sendDatas.handle_state = handle_state;     // 跟进状态
                    if(handle_state == '1' || handle_state == '3'){
                        sendDatas.remark = $jq('#approve_remark').val(); // 通过备注
                    }else if(handle_state == '2' ||  handle_state == '4'){
                        sendDatas.remark = $jq('#approve_remark_reject').val(); // 拒绝备注
                    }
                    // 选择拒绝原因，填写备注之后才能点击拒绝，填写备注，才能点击通过
                    if(handle_state == '1' ){
                        if( sendDatas.remark.length == 0 ){
                            layer.msg(layui.language.get('input_remark'),{time: 1000, icon:2});
                            return ;
                        }
                    }
                    if(handle_state == '2' || handle_state == '4'){  //待初审拒绝必须选择原因，待终审不需要
                        if(handle_state == '2' ){
                            sendDatas.flow_type    = $jq('#approve_flow_type').val(); // 拒绝类型
                            if( sendDatas.flow_type == 0 ){
                                layer.msg(layui.language.get('please_sel')+layui.language.get('reject_reason')+"",{time: 1000, icon:2});
                                return ;
                            }
                        }

                        if( sendDatas.remark.length == 0 ){
                            layer.msg(layui.language.get('input_remark'),{time: 1000, icon:2});
                            return ;
                        }
                    }
                    layer.closeAll();
                    $jq.post(basePath+'admin/Order/handle_change',sendDatas,function(data){
                        if(data.code !== 200){
                            layer.msg(data.message,{time: 2000, icon:2});
                            return false;
                        }

                        if(handle_state == '1' || handle_state == '2' ){
                            layui.verify_common.showDetailPage('parent');
                            layui.not_verify.initView();
                        }

                        if(handle_state == '3' || handle_state == '4'){  //跳转页面
                            layui.verify_common.showDetailPage('parent');
                            layui.finalAppeal.initView();
                        }

                        layer.closeAll();
                    });

                },
                btn2:function(index){
                    layer.closeAll();
                },
                cancel:function(){
                    layer.closeAll();
                }
            });

        }


        /**
         * 添加跟进记录获取选项
         * @param
         */
        ,initFollowInfoNew:function(order_no){
            $jq.post(basePath + 'admin/Collection/record_param',{'order_no':order_no},function(data) {
                if (!ajaxCall(data)) {
                    return;
                }
                //跟进对象
                var targe_arr = data.data.target.value;
                var targetHtml = '<option value="">-- ' + layui.language.get('please_sel') + ' --</option>';
                targetHtml += '<option value="10">' + targe_arr['10'] + '</option>';   //把最后一个key 放在最前面
                delete targe_arr[10];   //格式化最后一个key 的 属性值
                for (var item in targe_arr) {
                    targetHtml += '<option value="' + item + '">' + targe_arr[item] + '</option>';
                }
                $jq('select[name="flow_relation"]').html(targetHtml);
                //电话状态
                var phone_arr = data.data.contact_state.value;
                var contact_stateHtml = '<option value="">-- ' + layui.language.get('please_sel') + ' --</option>';
                for (var item in phone_arr) {
                    contact_stateHtml += '<option value="' + item + '">' + phone_arr[item] + '</option>';
                }
                $jq('select[name="phone_status"]').html(contact_stateHtml);
                layui.form.render('select');
                layui.form.render();
            });
            return false;
        }


        /**
         * 电核记录提交
         * @param order_id  订单id
         */
        ,submitDianhe:function() {
            var sendData= {};
            sendData.order_no       = $jq('#verify_detail_order_no').val();    // '订单号不能为空';
            sendData.flow_name      = $jq('#flow_name').val();                // '姓名不能为空';
            sendData.flow_relation  = $jq('#flow_relation').val();      // '请选择跟进对象'
            sendData.flow_phone     = $jq('#flow_phone').val();                 // '请输入联系电话';
            sendData.phone_status   = $jq('#phone_status').val();             // '请选择电话状态';
            sendData.flow_desc      = $jq('#flow_desc').val();             // '请输入内容';
            if( sendData.flow_name.length == 0){
                layer.msg(layui.language.get('not_name'),{icon:2,time:1000});
                return false;
            }
            if( sendData.flow_relation <1  ){
                layer.msg(layui.language.get('sel_follow_obj'),{icon:2,time:1000});
                return false;
            }
            if( sendData.flow_phone.length == 0){
                layer.msg(layui.language.get('input_phone'),{icon:2,time:1000});
                return false;
            }
            var regPos = /^[0-9]\d*$/; // 整数
            if( !regPos.test(Number(sendData.flow_phone))){
                layer.msg(layui.language.get('input_num'),{icon:2,time:1000});   // '请输入数字';
                return false;
            }
            if( sendData.phone_status <1 ){
                layer.msg(layui.language.get('sel_phone_status'),{icon:2,time:1000});
                return false;
            }
            if( sendData.flow_desc.length == 0){
                layer.msg(layui.language.get('input_follow_record'),{icon:2,time:1000});
                return false;
            }
            // 防止多次触发提交
            $jq('#add_dianhe').attr('disabled',true);
            $jq('#add_dianhe').removeClass('y-btn-green');
            $jq('#add_dianhe').addClass('layui-btn-disabled');

            $jq.post(basePath + 'admin/Order/order_flow_log',sendData,function(data){
                if(!ajaxCall(data)){
                    // 如果提交不成功，重新开始触发提交按钮
                    $jq('#add_dianhe').addClass('y-btn-green');
                    $jq('#add_dianhe').removeClass('layui-btn-disabled');
                    $jq('#add_dianhe').attr("disabled",false);
                    $jq('#add_dianhe').removeAttr("disabled");
                    return false;
                }

                if(data.code ==200){
                    layer.msg(layui.language.get('add_suc'),{icon:1,time:1000},function(){
                        $jq.post(basePath+'admin/Order/order_handle_info',{'order_no':sendData.order_no},function(data){
                            if(!ajaxCall(data)) {
                                return;
                            }
                            $jq('#add_dianhe').addClass('y-btn-green');
                            $jq('#add_dianhe').removeClass('layui-btn-disabled');
                            $jq('#add_dianhe').attr("disabled",false);
                            $jq('#add_dianhe').removeAttr("disabled");
                            layui.verify_detail.loadDetailHandelLog(data.data.handle_log, data.data.field.order_flow_log);
                        });
                    });
                }
                return false;
            });
            return false;
        }


        /**
         *  手机通讯录显示
         *  @param order_id 订单ID
         *  @param user_id  用户ID
         *  @param search_string 搜索条件
         */
        ,PhoneBook : function(user_id,order_id,search_string,tab_btn){
            layui.laydate.render({  // 催收日期
                elem: '#follow_date'
                ,range: true
                ,min: '2018-01-01'
                ,max: '2100-12-31'
                ,done : function(value){
                    var sendData ={};
                    sendData.follow_date = value;
                    sendData.company_id = $jq('#company_id').val();
                    sendData.admin_id = $jq('#admin_id').val();
                    sendData.collection_feedback = $jq('#collection_feedback').val();
                    sendData.search_string = $jq('#search_string').val();
                    sendData.date = $jq('#date').val();
                    $jq.post(basePath + 'admin/Collection/cllection_going',sendData,function(data){
                        if(!ajaxCall(data)){
                            return;
                        }
                        layui.inCollection.initViewTable(data.data);
                    })
                }
            });
            var sendDatas ={};
            sendDatas.user_id  = user_id;
            sendDatas.order_id = order_id;
            if(search_string){
                sendDatas.search_string = search_string;
            }
            dataStore.set('phone_search_string_user_id',sendDatas.user_id);
            dataStore.set('phone_search_string_order_id',sendDatas.order_id);
            var tab_data =[];
            $jq.post(basePath + 'admin/Collection/phone_list',sendDatas,function(data){
                 tab_data =data.data.tab6.list;
                 $jq('#tab6').addClass('y-btn-red');
                if(tab_btn =='benren_list'){
                    tab_data =data.data.tab1.list;
                }else if(tab_btn =='contact_list1'){
                    tab_data =data.data.tab2.list;
                }else if(tab_btn =='contact_list2'){
                    tab_data =data.data.tab3.list;
                }
                var  Num =0;
                var  limit = tab_data.length;


                if(tab_btn == 'phone_list'){
                    table.render({
                        elem : '#phone_list',
                        data: tab_data,
                        limit:limit,
                        autoHeight:true,
                        cols : [[
                            {title:'ID',width: '20%',templet:function(){
                                    ++Num ;
                                    return Num;
                                }},  //ID
                            {field: 'name', title:layui.language.get('real_name'),width:'30%'},   //姓名
                            {field: 'phone', title:layui.language.get('phone_no')},  //手机号
                        ]],
                        page: false
                    });
                }else{
                    table.render({
                        elem : '#phone_list',
                        data: tab_data,
                        limit:limit,
                        autoHeight:true,
                        cols : [[
                            {title:'ID',width: '10%',templet:function(){
                                    ++Num ;
                                    return Num;
                                }},  //ID
                            {field: 'name', title:layui.language.get('real_name'),width:'30%'},   //姓名
                            {field: 'phone', title:layui.language.get('phone_no')},  //手机号
                            {field: 'from', title:layui.language.get('contact_from'),width: '20%'}  //来源
                        ]],
                        page: false
                    });
                }

             });

            var html = phoneList.innerHTML;
            layer.open({
                type:1,
                area:['1000px','600px'],
                title:[layui.language.get('phone_book'),'text-align:center;font-size:18px;'],
                success: function (index, layero) {
                    $jq(':focus').blur();
                },
                content:html,
            });
        }


        /**
         *  通讯录搜索
         * @param tab_btn
         */
        ,PhoneBookSearch : function(tab_btn) {
            var sendDatas ={};
            sendDatas.user_id  = dataStore.get('phone_search_string_user_id');
            sendDatas.order_id = dataStore.get('phone_search_string_order_id');
            sendDatas.search_string = $jq("#phone_search_string").val();
            $jq.post(basePath + 'admin/Collection/phone_list',sendDatas,function(data){
                if(tab_btn =='phone_list') {
                    tab_data =data.data.tab6.list;
                    $jq('#tab1').removeClass('y-btn-red');
                    $jq('#tab2').removeClass('y-btn-red');
                    $jq('#tab3').removeClass('y-btn-red');
                    $jq('#tab4').removeClass('y-btn-red');
                    $jq('#tab5').removeClass('y-btn-red');
                    $jq('#tab6').addClass('y-btn-oldLace');
                }else if(tab_btn =='benren_list'){
                    tab_data =data.data.tab1.list;
                }else if(tab_btn =='contact_list1'){
                    tab_data =data.data.tab2.list;
                }else if(tab_btn =='contact_list2'){
                    tab_data =data.data.tab3.list;
                }

                var Num = 0;
                var limit = tab_data.length;
                if(tab_btn == 'phone_list'){
                    table.render({
                        elem : '#phone_list',
                        data: tab_data,
                        limit:limit,
                        autoHeight:true,
                        cols : [[
                            {title:'ID',width: '20%',templet:function(){
                                    ++Num ;
                                    return Num;
                                }},  //ID
                            {field: 'name', title:layui.language.get('real_name'),width:'30%'},   //姓名
                            {field: 'phone', title:layui.language.get('phone_no')},  //手机号
                        ]],
                        page: false
                    });
                }else{
                    table.render({
                        elem : '#phone_list',
                        data: tab_data,
                        limit:limit,
                        autoHeight:true,
                        cols : [[
                            {title:'ID',width: '10%',templet:function(){
                                    ++Num ;
                                    return Num;
                                }},  //ID
                            {field: 'name', title:layui.language.get('real_name'),width:'30%'},   //姓名
                            {field: 'phone', title:layui.language.get('phone_no')},  //手机号
                            {field: 'from', title:layui.language.get('contact_from'),width: '20%'}  //来源
                        ]],
                        page: false
                    });
                }

            });

        }


        /**
         * 切换表格
         * @param table
         */
        ,PhoneBookSwitch :function(tab_btn){
            var sendDatas ={};
            sendDatas.user_id  = dataStore.get('phone_search_string_user_id');
            sendDatas.order_id = dataStore.get('phone_search_string_order_id');
            sendDatas.search_string = $jq("#phone_search_string").val();
            var tab_data =[];
            $jq.post(basePath + 'admin/Collection/phone_list',sendDatas,function(data){
;                    if(tab_btn =='phone_list') {
                    tab_data =data.data.tab6.list;
                    $jq('#tab1').removeClass('y-btn-red');
                    $jq('#tab2').removeClass('y-btn-red');
                    $jq('#tab5').removeClass('y-btn-red');
                    $jq('#tab6').addClass('y-btn-oldLace');
                }else if(tab_btn =='benren_list'){
                    tab_data =data.data.tab1.list;
                    $jq('#tab1').addClass('y-btn-oldLace');
                    $jq('#tab2').removeClass('y-btn-red');
                    $jq('#tab3').removeClass('y-btn-red');
                    $jq('#tab4').removeClass('y-btn-red');
                    $jq('#tab5').removeClass('y-btn-red');
                    $jq('#tab6').removeClass('y-btn-red');
                }else if(tab_btn =='contact_list1'){
                    tab_data =data.data.tab2.list;
                    $jq('#tab1').removeClass('y-btn-red');
                    $jq('#tab2').addClass('y-btn-oldLace');
                    $jq('#tab3').removeClass('y-btn-red');
                    $jq('#tab4').removeClass('y-btn-red');
                    $jq('#tab5').removeClass('y-btn-red');
                    $jq('#tab6').removeClass('y-btn-red');
                }else if(tab_btn =='contact_list2'){
                    tab_data =data.data.tab3.list;

                    $jq('#tab1').removeClass('y-btn-red');
                    $jq('#tab2').removeClass('y-btn-red');
                    $jq('#tab3').addClass('y-btn-oldLace');
                    $jq('#tab4').removeClass('y-btn-red');
                    $jq('#tab5').removeClass('y-btn-red');
                    $jq('#tab6').removeClass('y-btn-red');

                }
                var Num =0;
                var  limit = tab_data.length;
                if(tab_btn == 'phone_list'){
                    table.render({
                        elem : '#phone_list',
                        data: tab_data,
                        limit:limit,
                        autoHeight:true,
                        cols : [[
                            {title:'ID',width: '20%',templet:function(){
                                    ++Num ;
                                    return Num;
                                }},  //ID
                            {field: 'name', title:layui.language.get('real_name'),width:'30%'},   //姓名
                            {field: 'phone', title:layui.language.get('phone_no')},  //手机号
                        ]],
                        page: false
                    });
                }else{
                    table.render({
                        elem : '#phone_list',
                        data: tab_data,
                        limit:limit,
                        autoHeight:true,
                        cols : [[
                            {title:'ID',width: '10%',templet:function(){
                                    ++Num ;
                                    return Num;
                                }},  //ID
                            {field: 'name', title:layui.language.get('real_name'),width:'30%'},   //姓名
                            {field: 'phone', title:layui.language.get('phone_no')},  //手机号
                            {field: 'from', title:layui.language.get('contact_from'),width: '20%'}  //来源
                        ]],
                        page: false
                    });
                }
            });
        }



        //初始化css
        ,initCss : function(lan){
            if(lan == 'id' || lan == 'en'){
                $jq('.layui-form-label-id').css('width','120px');
                $jq('.layui-input-inline-id').css('width','200px');
                $jq('.layui-textarea-id').css('width','200px');
            }else if(lan == 'cn'){
                $jq('.layui-form-label-id').css('width','80px');
                $jq('.layui-input-inline-id').css('width','220px');
                $jq('.layui-textarea-id').css('width','220px');
            }
        }


        /**
         * 获取运营商数据
         */
        ,operationData : function(userId,phone){
            // phone = '13417977171';
            var url = 'houtai/operators/'+phone+'.html';
            var domain = document.domain;  //获取当前域名
            domain = 'http://'+domain+'/';
            $jq.post(basePath + 'admin/Operators/file_is_exist',{'file_url':url},function(data){
                if(!ajaxCall(data)){
                    return false;
                }
                if(data.code =='200'){
                    url = domain +data.url;
                    // url = data.url;
                    console.log(url);
                    window.open(url);
                }

            });



        }


        /**
         * 获取百融数据
         * order_no
         */
        ,bairongData : function (order_no) {
            var  sendDatas = {};
            sendDatas.type = 1 ;
            sendDatas.order_no = order_no ;
            var is_open = true;
			  $jq.post(basePath + 'admin/Collection/hundred_funsion_info',sendDatas,function(data){
				     if(!ajaxCall(data)){
						 return false;
					 }
                      if(data.code !==200 || data.data.length <=0){
                          layer.open({
                              title: layui.language.get('prompt'),           //'提示'
                              content: "暂无百融报告",
                              success: function (index, layero) {
                                  $jq(':focus').blur();
                              },
                              btn : [ layui.language.get('certainly')]		//按钮

                          });
                          return false;
                      }

                      var flag_rulespeciallist_c =  "";
                      var Rule_final_decision =  "";
                      var Rule_final_weight =  "";
                      if(data.data){
				          flag_rulespeciallist_c =  data.data.flag_rulespeciallist_c;
                          Rule_final_decision =  data.data.Rule_final_decision;
                          Rule_final_weight =  data.data.Rule_final_weight;
                      }
                      var table_bairong  =  '<div class="info">';
                          table_bairong  += '<h2 class="title">百融黑名单信息</h2>';
                          table_bairong  += '<div class="items cross">';
                          table_bairong  += '<table><caption class="middle">反欺诈概况</caption><thead>';
                          table_bairong  += '<tr><th width="40%">检测项目</th><th width="60%">结果</th></tr>';
                          table_bairong  += '</thead><tbody>';
                          table_bairong  += '<tr><td>反欺诈规则输出标识 </td><td><p class="blackList"><span class="prefix" >'+ flag_rulespeciallist_c+'</span></p></td></tr>';
                          table_bairong  += '<tr><td>最终决策结果 </td><td><p class="blackList"><span class="prefix" >'+ Rule_final_decision+'</span></p></td></tr>';
                          table_bairong  += '<tr><td>最终规则评分 </td><td><p class="blackList"><span class="prefix" >'+ Rule_final_weight+'</span></p></td></tr>';
                          table_bairong  += '</tbody></table></div><!--items-->';

                      var  rule_bairong  =  '<div class="items cross">';
                          rule_bairong   += '<table><caption class="middle">命中规则名称和权重</caption><thead>';
                          rule_bairong   += '<tr><th width="20%">标识</th><th width="50%">规则名称</th><th width="30%">权重</th></tr>';
                          rule_bairong   += '</thead><tbody>';
                          if(data.data.rule.length>0){
                              $jq.each(data.data.rule,function(index,item){
                                  rule_bairong  += '<tr><td>'+index+'</td><td>'+item.Rule_name+'</td><td>'+item.Rule_weight+'</td> </tr>';
                              });
                          }else{
                              rule_bairong  += '<tr><td colspan="3" style="text-align: center">暂无数据</td></tr>';
                          }

                      rule_bairong  +='</tbody></table> </div><!--items cross-->';

                      var html = bairongDatas.innerHTML;
                      layer.open({
                          type: 1,
                          area: ['1000px', '600px'],
                          title: ["百融报告", 'text-align:center;font-size:18px;'],
                          success: function (index, layero) {
                              $jq(':focus').blur();
                          },
                          content: html,
                      });
                     $jq('#bairong_html_div').html(table_bairong+rule_bairong);

                     return false;
            });
        }


        /**
         * 获取新颜数据
         * order_no
         */
        ,xinyanData : function (order_no) {
            var  sendDatas = {};
                sendDatas.type = 2 ;
                sendDatas.order_no = order_no ;
			  $jq.post(basePath + 'admin/Collection/hundred_funsion_info',sendDatas,function(data){
				     if(!ajaxCall(data)){
						 return false;
					 }

                      if(data.code !==200){
                          layer.open({
                              title: layui.language.get('prompt'),           //'提示'
                              content: "暂无新颜报告",
                              success: function (index, layero) {
                                  $jq(':focus').blur();
                              },
                              btn : [ layui.language.get('certainly')]		//按钮
                          });
                          return false;
                      }

                      var heijing = data.data.hunuo_order_risk_heijing; //黑镜返回信息
                      var xwld = data.data.hunuo_order_risk_xwld;       //行为雷达信息
                      var fmlh = data.data.hunuo_order_risk_fmlh;       //负面拉黑信息
                      //var fmxb = data.data.hunuo_order_risk_fmxb;       //负面洗白信息

                      var heijing_datas  =  '<div class="info">';
                          heijing_datas  += '<h2 class="title">黑镜返回信息表</h2>';
                          heijing_datas  += '<div class="items cross">';
                          heijing_datas  += '<table><thead>';
                          heijing_datas  += '<tr><th width="40%">检测项目</th><th width="60%">结果</th></tr>';
                          heijing_datas  += '</thead><tbody>';;
                          heijing_datas  += '<tr><td>'+"信贷黑镜"+'</td><td>'+heijing.loan_black+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"诚信黑镜"+'</td><td>'+heijing.integrity_black+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"欺诈黑镜"+'</td><td>'+heijing.cheat_black+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"服务端生成的唯一设备ID"+'</td><td>'+heijing.xyid+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"设备类型"+'</td><td>'+heijing.deviceType+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"操作系统"+'</td><td>'+heijing.deviceSys+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"IP地址"+'</td><td>'+heijing.ip+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否代理"+'</td><td>'+heijing.isAgent+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"应用名称"+'</td><td>'+heijing.appName+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"设备名称"+'</td><td>'+heijing.deviceName+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否为模拟器"+'</td><td>'+heijing.simulator+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"MAC地址"+'</td><td>'+heijing.mac+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"系统版本"+'</td><td>'+heijing.sysVer+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否有root权限"+'</td><td>'+heijing.isRoot+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否多开分身"+'</td><td>'+heijing.isVirtualApp+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否越狱机"+'</td><td>'+heijing.isJailbreak+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否伪装操作系统"+'</td><td>'+heijing.pretendSystem+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否伪装分辨率"+'</td><td>'+heijing.pretendResolution+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否伪装浏览器"+'</td><td>'+heijing.pretendBrowser+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"是否篡改报文"+'</td><td>'+heijing.isFalsfy+'</td> </tr>';
                          heijing_datas  += '<tr><td>'+"查询结果描述"+'</td><td>'+heijing.desc+'</td> </tr>';
                          heijing_datas  += '</tbody></table> </div></div><!--items cross-->';



                  var  xwld_datas   = '<div class="info">';
                        xwld_datas  += '<h2 class="title">行为雷达信息</h2>';
                        xwld_datas  += '<div class="items cross">';
                        xwld_datas  += '<table><thead>';
                        xwld_datas  += '<tr><th width="40%">检测项目</th><th width="60%">结果</th></tr>';
                        xwld_datas  += '</thead><tbody>';
                        xwld_datas  += '<tr><td>'+"贷款行为分"+'</td><td>'+xwld.loans_score+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"贷款行为置信度"+'</td><td>'+xwld.loans_credibility+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"贷款放款总订单数"+'</td><td>'+xwld.loans_count+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"贷款已结清订单数"+'</td><td>'+xwld.loans_settle_count+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"贷款逾期订单数"+'</td><td>'+xwld.loans_overdue_count+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"贷款机构数"+'</td><td>'+xwld.loans_org_count+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"消费金融类机构数"+'</td><td>'+xwld.consfin_org_count+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"网络贷款类机构数"+'</td><td>'+xwld.loans_cash_count+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"近1个月贷款笔数"+'</td><td>'+xwld.latest_one_month+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"近3个月贷款笔数"+'</td><td>'+xwld.latest_three_month+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"近6个月贷款笔数"+'</td><td>'+xwld.latest_six_month+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"历史贷款机构成功扣款笔数"+'</td><td>'+xwld.history_suc_fee+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"历史贷款机构失败扣款笔数"+'</td><td>'+xwld.history_fail_fee+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"近1个月贷款机构成功扣款笔数"+'</td><td>'+xwld.latest_one_month_suc+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"近1个月贷款机构失败扣款笔数"+'</td><td>'+xwld.latest_one_month_fail+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"信用贷款时长"+'</td><td>'+xwld.loans_long_time+'</td> </tr>';
                        xwld_datas  += '<tr><td>'+"最近一次贷款时间"+'</td><td>'+xwld.loans_latest_time+'</td> </tr>';
                        xwld_datas  +='</tbody></table> </div></div><!--items cross-->';

                  var  fmlh_datas   = '<div class="info">';
                          fmlh_datas  += '<h2 class="title">负面拉黑信息</h2>';
                          fmlh_datas  += '<div class="items cross">';
                          fmlh_datas  += '<table><thead>';
                          fmlh_datas  += '<tr><th width="40%">检测项目</th><th width="60%">结果</th></tr>';
                          fmlh_datas  += '</thead><tbody>';
                          fmlh_datas  += '<tr><td>'+"最大逾期金额"+'</td><td>'+fmlh.max_overdue_amt+'</td> </tr>';
                          fmlh_datas  += '<tr><td>'+"最长逾期天数"+'</td><td>'+fmlh.max_overdue_days+'</td> </tr>';
                          fmlh_datas  += '<tr><td>'+"最近逾期时间"+'</td><td>'+fmlh.latest_overdue_time+'</td> </tr>';
                          fmlh_datas  += '<tr><td>'+"当前逾期机构数"+'</td><td>'+fmlh.currently_overdue+'</td> </tr>';
                          fmlh_datas  += '<tr><td>'+"当前履约机构数"+'</td><td>'+fmlh.currently_performance+'</td> </tr>';
                          fmlh_datas  += '<tr><td>'+"异常还款机构数"+'</td><td>'+fmlh.acc_exc+'</td> </tr>';
                          fmlh_datas  += '<tr><td>'+"睡眠机构数"+'</td><td>'+fmlh.acc_sleep+'</td> </tr>';
                          fmlh_datas  += '<tr><td>'+"查询结果描述"+'</td><td>'+fmlh.desc+'</td> </tr>';
                          fmlh_datas  +='</tbody></table> </div></div><!--items cross-->';


                  /*var  fmxb_datas   = '<div class="info">';
                          fmxb_datas  += '<h2 class="title">负面洗白信息</h2>';
                          fmxb_datas  += '<div class="items cross">';
                          fmxb_datas  += '<table><thead>';
                          fmxb_datas  += '<tr><th width="40%">检测项目</th><th width="60%">结果</th></tr>';
                          fmxb_datas  += '</thead><tbody>';
                          fmxb_datas  += '<tr><td>'+"最大履约金额"+'</td><td>'+fmxb.max_performance_amt+'</td> </tr>';
                          fmxb_datas  += '<tr><td>'+"最近履约时间"+'</td><td>'+fmxb.latest_performance_time+'</td> </tr>';
                          fmxb_datas  += '<tr><td>'+"履约笔数"+'</td><td>'+fmxb.count_performance+'</td> </tr>';
                          fmxb_datas  += '<tr><td>'+"贷款已结清订单数"+'</td><td>'+fmxb.currently_overdue+'</td> </tr>';
                          fmxb_datas  += '<tr><td>'+"当前逾期机构数"+'</td><td>'+fmxb.currently_performance+'</td> </tr>';
                          fmxb_datas  += '<tr><td>'+"当前履约机构数"+'</td><td>'+fmxb.acc_exc+'</td> </tr>';
                          fmxb_datas  += '<tr><td>'+"异常还款机构数"+'</td><td>'+fmxb.acc_sleep+'</td> </tr>';
                          fmxb_datas  += '<tr><td>'+"睡眠机构数"+'</td><td>'+fmxb.desc+'</td> </tr>';
                          fmxb_datas  +='</tbody></table> </div></div><!--items cross-->';*/


                  var html = xinyanDatas.innerHTML;
                  var xinyan_datas = heijing_datas + xwld_datas + fmlh_datas;
                  layer.open({
                      type:1,
                      area:['1000px','600px'],
                      title:['新颜报告','text-align:center;font-size:18px;'],
                      success: function (index, layero) {
                          $jq(':focus').blur();
                      },
                      content:html,
                  });

                     $jq('#xinyan_html_div').html(xinyan_datas);
            });

        }

    } // end


    //输出 verify_detail 接口
    exports('verify_detail', obj);
});  


