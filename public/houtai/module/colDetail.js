layui.define(['col_common','language'],function(exports){
    var $jq   = layui.jquery,
        table = layui.table,
        layer = layui.layer,
        form  = layui.form;

    var obj = {
        initView : function(order_no,show_btn,order_status) { // initView start
            layui.language.render('details_box');
            layui.language.render('phoneList');
            layui.language.render('applyDreate');
            layui.language.render('addRecord');
            //缓存该详情页跟进记录是否显示加载更多
            dataStore.set('showNumType',show_btn);
            var sendData={};
            sendData.order_no = order_no;
            $jq.post(basePath+'admin/Collection/details',sendData,function(data){
                if(!ajaxCall(data)) {
                    return;
                }
                $jq.get('postloan/colDetail.html',sendData,function(html) {
                    if(!ajaxCall(html)) {
                        return;
                    }
                    var roleType = dataStore.get('collection_role_type');
                    layui.col_common.showColDetailPage('detail');
                    layui.colDetail.loadAllDetail(data.data,html);
                    if( show_btn  == true){  //是否显示 申请减免和添加记录按钮
                        if (roleType == '2' || roleType == '4' || roleType == '5' || roleType == '6') {
                            if( order_status !== '200'){   //如果订单未还款
                                $jq('#apply_derate_btn').show();   //申请减免按钮
                                $jq('#record_btn').show();         //添加记录按钮
                                $jq('#add_follow').show();         //添加记录表格
                                layui.colDetail.initFollowInfoNew(order_no);
                            }
                            //初始化按钮的css
                            layui.colDetail.initBtnCss(dataStore.get('current_lan'));
                        }
                    }
                });
            });

        }


        /**
         *  客户信息和费用
         */
        ,loadAllDetail:function(datas,_html){
            var lang_img ={};
            lang_img.id_card_photo = layui.language.get('id_card_photo'); //身份证
            lang_img.best_photo = layui.language.get('best_photo'); //最佳图片
            lang_img.credit_photo = layui.language.get('credit_photo'); //签名图片
            lang_img.tax_card = layui.language.get('tax_card');   //税卡
            lang_img.security_card = layui.language.get('security_card'); //社保卡
            lang_img.family_card = layui.language.get('family_card');  //家庭卡
            lang_img.staff_card = layui.language.get('staff_card');   //员工卡
            lang_img.work_prove = layui.language.get('work_prove');  //在职证明
            lang_img.salary_card = layui.language.get('salary_card');    //工资卡
            datas.field.lang_img = lang_img;

            layui.laytpl(_html).render(datas,function(html){
                $jq('#allColDetail').html(html);
            });
            layui.language.render('allColDetail');
            layui.colDetail.initContactInfo(datas.contact, datas.field.contact);
            layui.colDetail.initFollowInfo(datas.follow, datas.field.follow);

        }


        /**
         *  联系方式
         */
        ,initContactInfo:function(data,field_contact) {
            var limit_num = 0;
            if(data){
                limit_num = data.length;
            }
            var table = layui.table;
            table.render({
                elem: '#contact_info'
                ,data:data
                ,limit:limit_num
                ,autoHeight:true
                ,cols: [[ //表头
                    {field: 'relation', title: field_contact.relation,}
                    ,{field: 'name', title: field_contact.name}
                    ,{field: 'phone', title: field_contact.phone}
                    ,{field: 'phone_status', title: field_contact.contact_state}
                ]]
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
                $jq('select[name="target"]').html(targetHtml);
                //电话状态
                var phone_arr = data.data.contact_state.value;
                var contact_stateHtml = '<option value="">-- ' + layui.language.get('please_sel') + ' --</option>';
                for (var item in phone_arr) {
                    contact_stateHtml += '<option value="' + item + '">' + phone_arr[item] + '</option>';
                }
                $jq('select[name="contact_state"]').html(contact_stateHtml);
                //催收反馈
                var follow_feed_arr = data.data.follow_feed.value;
                var follow_feedHtml = '<option value="">-- ' + layui.language.get('please_sel') + ' --</option>';
                for (var item in follow_feed_arr) {
                    follow_feedHtml += '<option value="' + item + '">' + follow_feed_arr[item] + '</option>';
                }
                $jq('select[name="follow_feed"]').html(follow_feedHtml);
                form.render('select');
                form.render();
            });
            return false;
        }


        /**
         *  跟进记录显示
         */
        ,initFollowInfo:function(data,field) {

            if( data == undefined ){
                var data = [];
            }
            var limit_num = 0;
            if(data  && data !== undefined){
                limit_num = data.length;
            }
            var table = layui.table;
            table.render({
                elem: '#follow_info'
                ,autoHeight:true
                ,data:data
                ,id: 'follow_info'
                ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                    layout: [ 'count', 'prev', 'page', 'next','limit', 'skip'] //自定义分页布局
                    ,curr:1 //设定初始在第 5 页
                    ,limits:[5,10,20]
                }
                ,limit: 10
                ,cols: [[ //表头
                     {field: 'operator_time', title:field.operator_time}
                    // ,{field: 'follow_type', title: field.follow_type,}
                    ,{field: 'target', title: field.target,}
                    ,{field: 'target_name', title:field.target_name}
                    ,{field: 'contact_phone', title: field.contact_phone}
                    ,{field: 'contact_state', title: field.contact_state}
                    ,{field: 'collection_feedback', title: field.collection_feedback}
                    ,{field: 'content', title: field.content}
                    ,{field: 'operator_name', title: field.operator_name}
                ]]

            });
        }


        /**
         * 默认显示10条数据
         */
        ,showListNum :function(type,count){
            //获取当前列表的Tr下标
             $jq(" #follow_record  .layui-table-view  .layui-table-box  .layui-table-main table tbody").find("tr").each(function(){
                      var RowIndex = parseInt(this.sectionRowIndex);
                      if( type == false && RowIndex >9 ){
                          this.style.display = "none";
                      }
                      if(type == true ){
                          this.style.display = "block";
                      }
                });

            if(type == false && count >10){
                var  html_show = '<td colspan="10"    style="border-bottom:none;"><div class="layui-flow-more"  id="layui-flow-more" style="text-align: center;"><a href="javascript:;" onclick="layui.colDetail.showListNum(true,\''+count+'\');"><cite>'+layui.language.get('list_more')+'</cite></a></div></td>';
                $jq("#follow_record  .layui-table-view  .layui-table-box  .layui-table-main table tbody").append(html_show);
            }
            if(type == true){
                $jq("#layui-flow-more").css("display","none");
            }

        }


        /**
         *  手机通讯录显示
         *  @param order_id 订单ID
         *  @param user_id  用户ID
         *  @param search_string 搜索条件
         */
        ,PhoneBook : function(user_id,order_id,search_string,tab_btn){
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
                $jq('#tab6').addClass('y-btn-green');
                if(tab_btn =='benren_list'){
                    tab_data =data.data.tab1.list;
                }else if(tab_btn =='contact_list1'){
                    tab_data =data.data.tab2.list;
                }else if(tab_btn =='contact_list2'){
                    tab_data =data.data.tab3.list;
                }
                // else if(tab_btn =='contact_list3'){
                //     tab_data =data.data.tab4.list;
                // }else if(tab_btn =='contact_list4'){
                //     tab_data =data.data.tab5.list;
                // }
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
                    $jq('#tab1').removeClass('y-btn-green');
                    $jq('#tab2').removeClass('y-btn-green');
                    $jq('#tab3').removeClass('y-btn-green');
                    $jq('#tab4').removeClass('y-btn-green');
                    $jq('#tab5').removeClass('y-btn-green');
                    $jq('#tab6').addClass('y-btn-green');
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
             if(tab_btn =='phone_list') {
                    tab_data =data.data.tab6.list;
                    $jq('#tab1').removeClass('y-btn-green');
                    $jq('#tab2').removeClass('y-btn-green');
                    $jq('#tab3').removeClass('y-btn-green');
                    $jq('#tab4').removeClass('y-btn-green');
                    $jq('#tab5').removeClass('y-btn-green');
                    $jq('#tab6').addClass('y-btn-green');
                }else if(tab_btn =='benren_list'){
                    tab_data =data.data.tab1.list;
                    $jq('#tab1').addClass('y-btn-green');
                    $jq('#tab2').removeClass('y-btn-green');
                    $jq('#tab3').removeClass('y-btn-green');
                    $jq('#tab4').removeClass('y-btn-green');
                    $jq('#tab5').removeClass('y-btn-green');
                    $jq('#tab6').removeClass('y-btn-green');
                }else if(tab_btn =='contact_list1'){
                    tab_data =data.data.tab2.list;
                    $jq('#tab1').removeClass('y-btn-green');
                    $jq('#tab2').addClass('y-btn-green');
                    $jq('#tab3').removeClass('y-btn-green');
                    $jq('#tab4').removeClass('y-btn-green');
                    $jq('#tab5').removeClass('y-btn-green');
                    $jq('#tab6').removeClass('y-btn-green');
                }else if(tab_btn =='contact_list2'){
                    tab_data =data.data.tab3.list;

                    $jq('#tab1').removeClass('y-btn-green');
                    $jq('#tab2').removeClass('y-btn-green');
                    $jq('#tab3').addClass('y-btn-green');
                    $jq('#tab4').removeClass('y-btn-green');
                    $jq('#tab5').removeClass('y-btn-green');
                    $jq('#tab6').removeClass('y-btn-green');
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


        /**
         * 申请减免
         * @param order_no 订单号
         * @param user_name 用户名
         * @param repay_amount 应还款总额
         * @param over_interest  罚息
         */
        ,applyDerate : function(order_no,name,repay_amount,over_interest){
            var roleType = dataStore.get('collection_role_type');
                if(roleType == '6'){
                    layer.open({
                        title: layui.language.get('prompt'),           //'提示'
                        content: layui.language.get('no_can'),
                        success: function (index, layero) {
                            $jq(':focus').blur();
                        },
                        btn : [ layui.language.get('certainly')]        //按钮
                    });
                    return false;
                }
            $jq.post(basePath + 'admin/Collection/reduction_param',{'order_no':order_no},function(data) {
                if (!ajaxCall(data)) {
                    return;
                }


                var html = applyDreate.innerHTML;
                layer.open({
                    type: 1,
                    title: [layui.language.get('apply_derate'), 'text-align:center;font-size:18px;'],
                    area: ['700px'],
                    success: function (index, layero) {
                        $jq(':focus').blur();
                    },
                    content: html
                });

                $jq("#derate_order_no").attr('value', order_no);             //订单号
                $jq("#derate_real_name").attr('value', name);                //姓名
                $jq("#derate_repay_amount").attr('value', repay_amount);     //应还金额
                $jq("#derate_over_interest").attr('value', over_interest);   //逾期罚息

            });
        }


        /**
         * 申请减免--提交
         * @param order_no 订单号
         */
        ,derateSubmit : function(){
            var  sendDatas = {};
            var over_interest  = $jq('input[name="over_interest"]').val();                   //  逾期罚息
                sendDatas.order_no = $jq('#derate_order_no').val();                          // '订单号';
                sendDatas.reduction_fee = $jq('input[name="reduction_fee"]').val();          // '减免费用';
                sendDatas.reduction_remark = $jq('#derate_reduction_remark').val();          // '备注'
                if( sendDatas.reduction_fee.length == 0){
                    layer.msg(layui.language.get('no_jmcost'),{icon:2,time:1000});           // '减免金额不能为空';
                    return false;
                }
                  var regPos = /^[1-9]\d*$/; // 非负整数
                if( !regPos.test(Number(sendDatas.reduction_fee))){
                    layer.msg(layui.language.get('int_derate_amount'),{icon:2,time:1000});   // '减免金额需正整数';
                    return false;
                }
                 if( Number(over_interest) < Number(sendDatas.reduction_fee) ){
                     layer.msg(layui.language.get('jmnogtfx'),{icon:2,time:1000});           // '减免金额不能大于罚息';
                     return false;
                    }

                if( sendDatas.reduction_remark.length == 0){
                    layer.msg(layui.language.get('input_remark'),{icon:2,time:1000});        // '请输入备注';
                    return false;
                }
            //防止多次触发提交
            $jq('#addApplyDreate_submit').attr('disabled',true);
            $jq('#addApplyDreate_submit').removeClass('y-btn-green');
            $jq('#addApplyDreate_submit').addClass('layui-btn-disabled');
            $jq.post(basePath + 'admin/Collection/reduction_add',sendDatas,function(data){

                if(!ajaxCall(data)){
                    //如果提交不成功，重新开始触发提交按钮
                    $jq('#addApplyDreate_submit').attr('disabled',false);
                    $jq('#addApplyDreate_submit').addClass('y-btn-green');
                    $jq('#addApplyDreate_submit').removeClass('layui-btn-disabled');
                    return;
                }
                if(data.code ==200){
                    layer.msg(layui.language.get('apply_derate_suc'),{icon:1,time:1000},function(){

                        layer.closeAll();
                        $jq.post(basePath + 'admin/Collection/details',{'order_no':sendDatas.order_no},function(data) {
                            //重新渲染最新的跟进记录
                            layui.colDetail.initFollowInfo(data.data.follow,  data.data.field.follow);

                        })
                    });
                }
            });
            return false;

        }


        /**
         * 添加记录--提交
         * @param  obj
         */
        ,recordSubmit : function(){
            var sendData= {};
            sendData.order_no      = $jq('#order_no').val();                // '订单号不能为空';
            sendData.user_id       = $jq('#user_id').val();                 // '用户ID不能为空';
            sendData.target_name   = $jq('#target_name').val();             // '姓名不能为空';
            sendData.target        = $jq('#target').val();                  // '请选择跟进对象'
            sendData.contact_phone = $jq('#contact_phone').val();           // '请输入联系电话';
            sendData.contact_state = $jq('#contact_state').val();           // '请选择电话状态';
            sendData.follow_feed   = $jq('#follow_feed').val();             // '请选择催收反馈';
            sendData.content       = $jq('#content').val();                 // '请输入内容';

            if( sendData.target_name.length == 0){
                layer.msg(layui.language.get('not_name'),{icon:2,time:1000});
                return false;
            }
            if( sendData.target <1  ){
                layer.msg(layui.language.get('sel_follow_obj'),{icon:2,time:1000});
                return false;
            }
            if( sendData.contact_phone.length == 0){
                layer.msg(layui.language.get('input_phone'),{icon:2,time:1000});
                return false;
            }

            var regPos = /^[0-9]\d*$/; // 整数
            if( !regPos.test(Number(sendData.contact_phone))){
                layer.msg(layui.language.get('input_num'),{icon:2,time:1000});   // '请输入数字';
                return false;
            }

            if( sendData.contact_state <1 ){
                layer.msg(layui.language.get('sel_phone_status'),{icon:2,time:1000});
                return false;
            }
            // if( sendData.follow_type == 0){
            //     layer.msg(layui.language.get('sel_follow_type'),{icon:2,time:2000});
            //     return false;
            // }
            if( sendData.follow_feed <1 ){
                layer.msg(layui.language.get('please_sel')+layui.language.get('sel_col_feed'),{icon:2,time:1000});
                return false;
            }
            if( sendData.content.length == 0){
                layer.msg(layui.language.get('input_follow_record'),{icon:2,time:1000});
                return false;
            }

            //防止多次触发提交
            $jq('#record_btn_css').attr('disabled',true);
            $jq('#record_btn_css').removeClass('y-btn-green');
            $jq('#record_btn_css').addClass('layui-btn-disabled');

            $jq.post(basePath + 'admin/Collection/record_add',sendData,function(data){
                if(!ajaxCall(data)){
                    //如果提交不成功，重新开始触发提交按钮
                    $jq('#record_btn_css').addClass('y-btn-green');
                    $jq('#record_btn_css').removeClass('layui-btn-disabled');
                    $jq('#record_btn_css').attr("disabled",false);
                    $jq('#record_btn_css').removeAttr("disabled");
                    return false;
                }
                if(data.code ==200){
                    layer.msg(layui.language.get('add_suc'),{icon:1,time:1000},function(){
                        
                         $jq.post(basePath + 'admin/Collection/details',{'order_no':sendData.order_no},function(data) {
                             //如果提交成功，重新开始触发提交按钮
                             $jq('#record_btn_css').addClass('y-btn-green');
                             $jq('#record_btn_css').removeClass('layui-btn-disabled');
                             $jq('#record_btn_css').attr("disabled",false);
                             $jq('#record_btn_css').removeAttr("disabled");

                             //重新渲染最新的跟进记录
                             layui.colDetail.initFollowInfo(data.data.follow,  data.data.field.follow);
                          })
                    });
                }
                return false;
            });
            return false;
        }


        /**
         * 初始化添加记录按钮的间距
         */
        ,initBtnCss : function(lan){
            if(lan == 'cn'){
                $jq('#record_btn').css('margin-right','120px');
            }else if(lan == 'id'){
                $jq('#record_btn').css('margin-right','160px');
            }else if(lan == 'en'){
            	$jq('#record_btn').css('margin-right','180px');
            }
        }

    }

        //输出colDetail接口
        exports('colDetail',obj);
});


