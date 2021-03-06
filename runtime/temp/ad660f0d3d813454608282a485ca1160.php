<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:82:"G:\PHPTutorial\WWW\admin_ydh\public/../application/admin\view\login\agreement.html";i:1543399435;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, minimal-ui" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no, email=no" />
    <title>Document</title>
    <style>
        * {margin:0;padding:0;}
        p {margin: 6px 0;}
        .b_title{font-size: 22px;text-align: center;color:#333;}
        table td{   
            padding: 2px 5px;
            border-collapse: collapse;
            border: 1px solid #D2D5E0;
            line-height: 24px;
        }
        table{width:100%;}
        .main{font-size: .8rem;line-height: 2;color: #000;margin:0 auto;width:92%;}
        span{line-height: 24px;}
        .indent_2{
            text-indent: 2em;
        }
        .height_10{height: 10px;}
    </style>

</head>
<body>
    <div class="main">
        <p class="b_title">
        <span>平台服务协议</span>
        </p>
        <p class="indent_2">
            <span>合同编号：</span><?php echo $contract_number; ?>
        </p>
        <p class="indent_2">
            <span>甲方：深圳市惠泽通电子科技有限公司</span>
        </p>
        <p class="indent_2">
            <span>乙方：</span><?php echo $user['name']; ?>
        </p>
        <p class="indent_2">
            <span>身份证号码：</span><?php echo $user['idcode']; ?>
        </p>
        <br>
        <p class="indent_2">
            <span>鉴于，甲方是一家在中国境内注册并合法登记的公司；乙方（非高等院校全日制学生）为经甲方居间介绍，与出借人签订《借款协议》的借款人，出借人和借款人之间形成民间借贷关系。甲方、乙方经平等协商，根据《中华人民共和国合同法》及其他有关法律法规的规定订立本协议。</span>
        </p>
        <p class="indent_2">
            <strong><span>第一条&nbsp;</span></strong><strong><span>协议成立</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>乙方在甲方或甲方合作平台上通过类似点击确认按钮后，本协议及附件《法律文书送达地址确认书》即时成立并生效。本协议以数据电文的形式存储于甲方服务器或第三方电子数据存证平台中，乙方对此予以确认并同意。</span>
        </p>
        <p class="indent_2">
            <strong><span>第二条&nbsp;</span></strong><strong><span>定义</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>除本协议另有规定外，本协议中下列用语的定义如下：</span>
        </p>
        <p class="indent_2">
            <span>1.<span style="font-size:9px;line-height:normal;font-family:&quot;">&nbsp;&nbsp;&nbsp;</span></span><span style="line-height:24px;color:#010101;">《借款协议》</span><span style="line-height:24px;color:#010101;">是指本协议(以下简称“借款协议”或“本协议”) ,系由出借人、借款人双方通过居间人平台确认并同意接受全部条款，且经借款人亲自填写相关个人信息与借款信息后形成的网络借贷关系的合同。</span>
        </p>
        <p class="indent_2">
            <span style="line-height:24px;color:#282828;">2.<span style="font-size:9px;line-height:normal;font-family:&quot;">&nbsp;&nbsp;&nbsp;</span></span><span style="line-height:24px;color:#010101;">借款人（本协议“乙方”）是指</span><span style="line-height:24px;color:#010101;">《借款协议》</span><span style="line-height:24px;color:#010101;">中列明的持中华入民共</span><span style="line-height:24px;color:#282828;">和</span><span style="line-height:24px;color:#010101;">国法律规定的具有完全民事行为能力的个体（不含</span><span style="line-height:24px;color:#282828;">高</span><span style="line-height:24px;color:#010101;">等院校全日制学生）。借款人为居间人平台的实名注册用户,已向居间人如实提供借款所需个人信息。</span>
        </p>
        <p class="indent_2">
            <span style="line-height:24px;color:#282828;">3.<span style="font-size:9px;line-height:normal;font-family:&quot;">&nbsp;&nbsp;&nbsp;</span></span><span style="line-height:24px;color:#282828;">出</span><span style="line-height:24px;color:#010101;">借人是指</span><span style="line-height:24px;color:#010101;">《借款协议》</span><span style="line-height:24px;color:#010101;">中列明</span><span style="line-height:24px;color:#282828;">的</span><span style="line-height:24px;color:#010101;">符合</span><span style="line-height:24px;color:#282828;">中</span><span style="line-height:24px;color:#010101;">华人民</span><span style="line-height:24px;color:#282828;">共和</span><span style="line-height:24px;color:#010101;">国法律规定的具有完全民事行为能力的个体。</span><span style="line-height:24px;color:#282828;">出</span><span style="line-height:24px;color:#010101;">借人为经过居间人评估确认，授权居间人推介、选择借款人，并同意借款给借款人的资金出借方。</span>
        </p>
        <p class="indent_2">
            <span>4.<span style="font-size:9px;line-height:normal;font-family:&quot;">&nbsp;&nbsp;&nbsp;</span></span><span style="line-height:24px;color:#010101;">居</span><span style="line-height:24px;color:#282828;">间</span><span style="line-height:24px;color:#010101;">方</span>&nbsp;<span style="line-height:24px;color:#010101;">(</span><span style="line-height:24px;color:#010101;">本协议“甲方”)是指</span><span style="line-height:24px;color:#282828;">深圳市惠泽通电子科技有限公司</span><span>公司</span><span style="line-height:24px;color:#010101;">(</span><span style="line-height:24px;color:#010101;">以</span><span style="line-height:24px;color:#282828;">下简</span><span style="line-height:24px;color:#010101;">称</span><span style="line-height:24px;color:#282828;">“</span><span>惠泽通科技</span><span style="line-height:24px;color:#282828;">”</span><span style="line-height:24px;color:#010101;">)</span><span style="line-height:24px;color:#010101;">，系</span><span style="line-height:24px;color:#818282;">一</span><span style="line-height:24px;color:#010101;">家以互联网为主要渠道，为借款人与出借人实现直接借贷提供信息搜集、信息公布、资信评估、信息交互、借贷撮合、贷</span><span style="line-height:24px;color:#282828;">后</span><span style="line-height:24px;color:#010101;">管</span><span style="line-height:24px;color:#282828;">理</span><span style="line-height:24px;color:#010101;">等服务的金融信息中介公司。</span>
        </p>
        <p class="indent_2">
            <span>5.<span style="font-size:9px;line-height:normal;font-family:&quot;">&nbsp;&nbsp;&nbsp;</span></span><span>工作日：指除国家法定节假日、公休日以外的甲方对外办理业务的任何一日。</span>
        </p>
        <p class="indent_2">
            <strong><span>第三条&nbsp;</span></strong><strong><span>服务内容</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>甲方及甲方合作方为乙方提供居间、信息核验、平台管理等服务，具体如下：</span>
        </p>
        <p class="indent_2">
            <span>1</span><span>、居间服务：甲方利用自有资源和平台优势，为乙方在甲方平台上发起的借款寻找合适出借人，促成双方签订《借款协议》，实现乙方获得出借人出借款项；甲方有权根据业务经营情况为乙方选择介绍合适出借人的合作方居间平台。</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、信息核验服务：甲方对乙方提供的个人信息进行信息核验，用以评估乙方填写信息的真实性、完整性、个人信用水平与还款能力。乙方授权并委托甲方对其个人信息进行核验，必要时可以与第三方专业机构通过互联网大数据进行信息校验比对。乙方授权甲方可以使用其个人填写及甲方从互联网合理获取的数据，必要时可以向出借人、甲方关联公司及其合作方提供、使用其个人信息，具体以附件《授权书》为准。</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>、平台管理：甲方为乙方提供平台账号注册、个人信息维护、借款信息与电子合同的签署、数据加密、数据存储等相关服务。</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>、贷后服务：《借款协议》生效后，甲方对《借款协议》项下借款及乙方还款提供服务，履行提醒、推荐义务，乙方在归还借款的同时向甲方及其合作方支付服务费用。贷后管理服务内容包括：</span>
        </p>
        <p class="indent_2">
            <span>（1）</span><span>对乙方的履约情况、经营及财务状况等进行检查、提醒；</span>
        </p>
        <p class="indent_2">
            <span>（2）</span><span>对乙方在《借款协议》项下的借款资金的使用情况进行检查、提醒；</span>
        </p>
        <p class="indent_2">
            <span>（3）</span><span>提醒乙方根据《借款协议》的约定按时还款；</span>
        </p>
        <p class="indent_2">
            <span>（4）</span><span>与银行和/或第三方支付机构合作，对《借款协议》项下各类金额提供电子数据信息计算与统计，对法院需要认定的必要费用提供财务凭证。</span>
        </p>
        <p class="indent_2">
            <span>5</span><span>、逾期管理</span>
        </p>
        <p class="indent_2">
            <span>&nbsp;&nbsp;&nbsp;</span><span>乙方未能在《借款协议》约定还款时间到期前足额支付还款金额至乙方账户的，视作乙方逾期还款，甲方即对乙方的借款进行逾期管理，逾期管理服务及相关内容包括：</span>
        </p>
        <p class="indent_2">
            <span>（1）直接或间接向乙方发送通知进行款项追偿，包括但不限于以手机短信、电话、信函、电子邮件、网站通知或其他合法方式提醒或催告乙方履行《借款协议》项下的还款义务；</span>
        </p>
        <p class="indent_2">
            <span>（2）逾期管理款项的范围包括但不限于本金、利息、综合服务费、逾期费用以及实现债权的合理费用等，逾期还款按照优先偿还逾期费用以及实现债权的合理费用，其次综合服务费，再次借款利息，最后本金。</span>
        </p>
        <p class="indent_2">
            <span>（3）乙方基于本合同项下借款产生的还款义务及服务费用等，有任何应付而未付的款项时即视为逾期，乙方需要向甲方支付逾期费用。</span>
        </p>
        <p class="indent_2">
            <span>6</span><span>、提前结清</span>
        </p>
        <p class="indent_2">
            <span>（1）乙方可以发起提前结清服务申请、服务是否可用，以操作界面展示为准（受银行结算等影响，部分日期可能无法发起申请）。</span>
        </p>
        <p class="indent_2">
            <span>（2）乙方选择提前结清的：仍应按《借款协议》偿还事先约定的到期全额本息（包括本金、利息、综合服务费等）。</span>
        </p>
        <p class="indent_2">
            <span>7</span><span>、乙方与出借人签订《借款协议》，即视为甲方在本协议项下约定的义务已经履行完毕，乙方同意于借款起息日，委托并授权出借人将乙方应付的相关款项，代乙方使用出借资金预先缴纳，或不可撤销地授权甲方指定的第三方支付机构从乙方账户划扣。若乙方在款项到账前主动取消借款，则乙方需要支付甲方手续费，手续费金额相当于借款本金的20%。</span>
        </p>
        <p class="indent_2">
            <strong><span>第四条&nbsp;</span></strong><strong><span>费用</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>1</span><span>、综合服务费：指乙方因甲方或甲方合作方所提供的服务向甲方或甲方支付的费用，收费标准如下：</span>
        </p>
        <p class="indent_2">
            <table >
                <tbody>
                    <tr>
                        <td width="120" rowspan="3" align="center" >
                            <p>
                                <strong><span style="">综合服务费</span></strong><strong></strong>
                            </p>
                        </td>
                        <td width="120">
                            <p>
                                <strong><span style="">平台管理费</span></strong><strong></strong>
                            </p>
                        </td>
                        <td width="346">
                            <p>
                                <span style="">甲方借款金额的15%</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="120">
                            <p>
                                <strong><span style="">信息核验费</span></strong><strong></strong>
                            </p>
                        </td>
                        <td width="346">
                            <p>
                                <span style="">甲方借款金额的5%</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="120">
                            <p>
                                <strong><span style="">居间服务费</span></strong>
                            </p>
                        </td>
                        <td width="346">
                            <p>
                                <span style="">甲方借款金额的5%</span>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </p>
        <p class="indent_2">
            <span>2</span><span>、</span><span>甲方发生逾期应向丙方支付</span><span>逾期费用：逾期费用以逾期未还金额为基数按日收取，按照逾期未还金额（包括本金、利息）计算，以当日0：00至24：00为一日，出借人每日收取用户逾期未还金额的<span style="color:red;"><?php echo $fee['over_fee']; ?>%</span>作为逾期费用。</span>
        </p>
        <p class="indent_2">
            <strong><span>第五条&nbsp;</span></strong><strong><span>服务期限</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>&nbsp;&nbsp;</span><span>本协议项下服务期限自本协议生效之日起，至乙方在《借款协议》项下的所有义务全部履行完毕，且乙方支付完毕本协议项下的全部费用之日止。</span>
        </p>
        <p class="indent_2">
            <strong><span>第六条&nbsp;</span></strong><strong><span>甲方的权利和义务</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>1</span><span>、按照本协议约定向乙方提供各项服务；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、在本协议履行过程中，根据实际情况要求乙方予以相应配合或协助；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>、对涉及本协议项下服务内容的行为进行监督、检查，有权要求乙方提供相关资料和文件，有权要求乙方对有关问题作出说明，有权要求乙方改正借款后出现的违约、违法行为；</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>、按照《借款协议》及本协议约定先乙方收取费用；</span>
        </p>
        <p class="indent_2">
            <span>5</span><span>、甲方、出借人、授权受让人作为乙方的追偿权人，或作为乙方可能参加的诉讼或非诉程序的利害关系人，有权请求或委托律师请求乙方履行其他协议中的涉及出借人及甲方利益的义务。</span>
        </p>
        <p class="indent_2">
            <strong><span>第七条&nbsp;</span></strong><strong><span>乙方的权利和义务</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>1</span><span>、接受并配合甲方的各项服务行为；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、接受甲方的监督，向甲方提供要求的相关资料和文件，并应如实向甲方通报自身的财务状况，及时改正借款后出现的违约、违法行为；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>、在《借款协议》约定的借款到期日的18：00之前，偿还根据《借款协议》约定的还款金额，并委托甲方将借款本息支付给出借人；</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>、未经甲方书面同意，不得转让本协议项下的任何权利义务；</span>
        </p>
        <p class="indent_2">
            <span>5</span><span>、按照《借款协议》及本协议约定向甲方支付费用。</span>
        </p>
        <p class="indent_2">
            <strong><span>第八条&nbsp;</span></strong><strong><span>有关特别事项的约定</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>&nbsp;&nbsp;&nbsp;</span><span>在本协议履行过程中，涉及到以下事项时，乙方应将其情况及时向甲方通报，在取得甲方的书面同意后方可进行相应处置；</span>
        </p>
        <p class="indent_2">
            <span>1</span><span>、第三方为乙方承担全部或部分《借款协议》项下的债务的；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、乙方改变借款用途或发生重大变化的；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>、乙方出现重大诉讼、财务情况恶化等可能危及贷后资产安全的情形的；</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>、乙方发生违反《借款协议》情形的；</span>
        </p>
        <p class="indent_2">
            <span>5</span><span>、其他可能导致个人偿债能力减损的情形（如情势变更等）；</span>
        </p>
        <p class="indent_2">
            <span>6</span><span>、其他经甲方与乙方协商一致应取得甲方书面同意方可实施的事项。</span>
        </p>
        <p class="indent_2">
            <strong><span>第九条&nbsp;</span></strong><strong><span>陈述和保证</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>1</span><span>、甲方是合法登记设立的、符合中国法律规定的企业法人；并认可本协议生效即时对甲方具有法律约束力；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、乙方承诺为善意、有权缔约的完全民事行为能力人，并认可本协议生效即时对乙方具有法律约束力。</span>
        </p>
        <p class="indent_2">
            <strong><span>第十条&nbsp;</span></strong><strong><span>&nbsp;</span></strong><strong><span>违约责任</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>因任何一方的违约行为给另一方造成损失的，应由违约方承担违约及赔偿责任。</span>
        </p>
        <p class="indent_2">
            <strong><span>第十一条&nbsp;</span></strong><strong><span>协议的变更和解除</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>1</span><span>、除非本协议另有约定、本协议生效后，任何一方不得单方面变更或解除本协议。对本协议的修改或变更必须经甲方和乙方协商一致，并达成书面协议；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、如遇国家法律、法规或政策变化，致使本协议的全部或部分条款不再符合国家法律、法规或政策的要求，甲方可根据相关变动修改有关条款；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>、甲方或乙方由于不可抗力不能履行协议的，应及时通知对方并采取有效措施防止损失扩大。遭受不可抗力的一方应在事件发生后的5个工作日内向对方提供该不可抗力事件的详细情况和有关政府部门出具的有关该不可抗力事件的发生及影响的证明文件。甲方和乙方应及时协商解决措施。乙方对《借款协议》及本协议的违反不属于不可抗力。</span>
        </p>
        <p class="indent_2">
            <strong><span>第十二条&nbsp;</span></strong><strong><span>争议解决</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>双方在履行本协议过程中发生争议时，双方应协商处理；协商不成的，由本合同的签署地深圳市宝安区人民法院管辖。</span>
        </p>
        <p class="indent_2">
            <strong><span>第十三条&nbsp;</span></strong><strong><span>其他事项</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span>1</span><span>、本协议经乙方在线点击类似确认的方法签订，本协议一经签署，即视为乙方向出借人发出不可撤销的借款要约；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、有关本协议的任何修改、补充，双方均需在甲方或甲方合作方网络平台上以电子文本形式作出；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>、双方均确认，本协议的签订、生效和履行以不违反法律为前提。如果本协议中的任何一条或多条违反适用的法律，则该条约将被视为无效，但该无效条款并不影响本协议其他条款的效力；、</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>、乙方委托甲方保管所有与本协议有关的书面文件或电子信息；</span>
        </p>
        <p class="indent_2">
            <span>5</span><span>、本协议未尽事宜，由甲、乙双方协商处理，或者按国家有关法律、法规的规定执行；</span>
        </p>
        <p class="indent_2">
            <span>6</span><span>、本协议的各项补充、修订或变更，包括本协议的附件、附录及补充协议，为本协议的完整组成部分；</span>
        </p>
        <p class="indent_2">
            <span>7</span><span>、本协议中所使用的定义，除非另有规定，甲方享有解释权。</span>
        </p>
        <p class="indent_2">
            <strong><span>附表：</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <table>
                <tbody>
                    <tr>
                        <td width="120" rowspan="3" aling="center">
                            <p style="text-align:center;">
                                <strong><span style="">综合服务费</span></strong><strong></strong>
                            </p>
                        </td>
                        <td width="120">
                            <p>
                                <strong><span style="">平台管理费</span></strong><strong></strong>
                            </p>
                        </td>
                        <td width="346">
                            <p>
                                <span><?php echo $fee['platform_service_fee']; ?></span><span style="">元</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="120">
                            <p>
                                <strong><span style="">信息核验费</span></strong><strong></strong>
                            </p>
                        </td>
                        <td width="346">
                            <p>
                                <span><?php echo $fee['info_fee']; ?></span><span style="">元</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="120">
                            <p>
                                <strong><span style="">居间服务费</span></strong>
                            </p>
                        </td>
                        <td width="346">
                            <p>
                                <span><?php echo $fee['service_fee']; ?></span><span style="">元</span>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </p>
        <p class="indent_2">
            <span style="line-height:24px;color:#2B2B2B;">（以下无正文）</span>
        </p>
        <!-- <br> -->
        <p class="indent_2">
            <span style="line-height:24px;color:#2B2B2B;">(</span><span style="line-height:24px;color:#2B2B2B;">以</span><span>下</span><span style="line-height:24px;color:#2B2B2B;">无正</span><span>文，为签署页</span><span style="line-height:24px;color:#2B2B2B;">)</span>
        </p>
        <p class="indent_2">
            <span>甲方：深圳市惠泽通电子科技有限公司</span>
        </p>
        <p class="indent_2">
            <span>乙方（借款人）：</span><?php echo $user['name']; ?>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span><?php echo $ymd; ?></span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span>签署地：深圳市宝安区</span>
        </p>
        <p class="indent_2">
            <span>附件：授权委托书</span>
        </p>
        <p class="indent_2" style="text-indent:5em;">
            <span>法律文书送达地址确认书</span>
        </p>
        <p class="indent_2">
            <span style="color:red;"><br />
        </span>
        </p>
        <p class="indent_2">
            <span style="color:red;">&nbsp;</span>
        </p>
        <p class="b_title">
            <span>授权委托书</span>
        </p>
        <p class="indent_2 height_10" >
            <span>&nbsp;</span>
        </p>
        <p class="indent_2">
            <span>授权人：</span><?php echo $user['name']; ?>
        </p>
        <p class="indent_2">
            <span>被授权人：深圳市惠泽通电子科技有限公司</span>
        </p>
        <p class="indent_2 height_10">
        </p>
        <p class="indent_2">
            <span>授权人（非高等院校全日制学生）同意并签署本授权委托书，在此通过本授权委托书作出如下授权与承诺：</span>
        </p>
        <p class="indent_2">
            <span>1</span><span>、授权人应妥善保管其在被授权人平台的账户名、密码、数字证书、手机号码、手机验证码等与注册账户有关的一切信息。任何以被授权人平台注册账号名义作出的行为及生成的相应电子数据均由授权人承担相应法律后果，对于因密码泄露所致的损失，由授权人自行承担。</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、授权人变更通讯地址、电子邮件地址、电话等事项的，应于变更后二个工作日内书面通知被授权人。</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>、授权人了解并同意当授权人访问被授权人网站及其相关网站、被授权人平台移动设备客户端或使用被授权人平台的服务时，授权人须向被授权人平台主动提供一些信息，且授权人同意并授权被授权人平台通过以下途径获取被授权人的信息：</span>
        </p>
        <p class="indent_2">
            <span>1</span><span>）收集借款人留存在被授权人平台的关联公司处的信息；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>）收集借款人留存在被授权人平台合作伙伴（包括但不限于担保公司、证券机构、银行、购物平台、生活服务平台、电子商务平台等，下同）处的信息；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>）向行政机关、司法机关查询、打印、留存借款人信息；</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>）向合法留存借款人信息的自然人、法人以及其他组织收集借款人的信息。</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>、授权人同意并授权被授权人收集的授权人信息包括但不限于：</span>
        </p>
        <p class="indent_2">
            <span>1</span><span>）授权人及其关联方的身份信息，包括但不限于姓名/名称、证件号码、证件类型、住所地、电话号码、购物平台账户认证信息以及其他身份信息；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>）授权人在申请、使用被授权人提供的服务时所提供以及形成的任何数据和信息，包括但不限于授权人的信息以及授权人提供的其关联方的信息；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>）授权人及其关联方在被授权人关联公司和合作伙伴处中留存以及形成的任何数据和信息，包括但不限于授权人的履约情况、诚信情况、行为数据等；</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>）授权人的财产信息，包括但不限于授权人的个人状况、财税信息、房产信息、车辆信息、基金、保险、股票、信托、债券等投资理财信息和负债信息等；</span>
        </p>
        <p class="indent_2">
            <span>5</span><span>）授权人在行政机关、司法机关留存的任何信息，包括但不限于户籍信息/工商信息、诉讼信息、执行信息和违法犯罪信息等；</span>
        </p>
        <p class="indent_2">
            <span>6</span><span>）与授权人申请或使用的被授权人服务相关的、授权人留存在其他自然人、法人和组织的其他相关信息。</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>、为了更好地为授权人提供服务，也为了被授权人自身的风险防控，授权人同意并授权被授权人将授权人的信息用于如下用途：</span>
        </p>
        <p class="indent_2">
            <span>1</span><span>）创建数据分析模型，对授权人的信用状况、还款能力进行评估；</span>
        </p>
        <p class="indent_2">
            <span>2</span><span>）比较信息的准确性并与第三方进行验证；</span>
        </p>
        <p class="indent_2">
            <span>3</span><span>）合理评估授权人和/或其关联方的经营状况，防控风险；</span>
        </p>
        <p class="indent_2">
            <span>4</span><span>）为使授权人知晓被授权人的服务情况或了解被授权人的服务，通过电子邮件、手机短信和传真等方式向授权人发送服务状态的通知及其他商业性电子信息；</span>
        </p>
        <p class="indent_2">
            <span>5</span><span>）因授权人和/或其关联方与被授权人的纠纷未能够协商解决而需要通过借助催收及法律途径解决的，被授权人会将授权人的信息提供给催收公司、律师事务所、法院、仲裁委员会和其他有权机关；</span>
        </p>
        <p class="indent_2">
            <span>6</span><span>）预防或阻止非法的活动；</span>
        </p>
        <p class="indent_2">
            <span>7)</span><span>经授权人许可的其他用途。</span>
        </p>
        <p class="indent_2">
            <span>（以下无正文）</span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span><?php echo $ymd; ?></span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span style="">授权人：<?php echo $user['name']; ?></span>
        </p>
        <p class="indent_2">
            <span style=""><br />
        </span>
        </p>
        <p class="indent_2">
            <span style="">&nbsp;</span>
        </p>
        <p class="indent_2">
            <span>附件：</span>
        </p>
        <p class="indent_2">
            <span>《法律文书送达地址确认书》</span>
        </p>
        <p class="indent_2">
            <span>本人同意与深圳市惠泽通电子科技有限公司（以下简称“平台方”）在线订立的《平台服务协议》（以下简称“合同”），对于因合同引起的任何纠纷，本人声明司法机关（包括但不限于人民法院）可以用手机短信或电子邮件等现代通讯方式或邮寄方式向本人送达法律文书（包括诉讼文书）。</span>
        </p>
        <p class="indent_2">
            <span>1．</span><span>本人指定接收法律文书的手机号码或者电子邮箱为合同约定的或注册账户绑定的手机号码或电子邮箱，司法机关向前述码址发出法律文书即视为送达。</span>
        </p>
        <p class="indent_2">
            <span>2．</span><span>本人指定邮寄地址为本人在平台或注册时使用的户籍地址或身份证地址。</span>
        </p>
        <p class="indent_2">
            <span>3．</span><span>本人同意司法机关可采取以上一种或多种送达方式向本人送达法律文书，司法机关采取多种方式向本人送达法律文书，送达时间以上述送达方式中最先送达的为准。</span>
        </p>
        <p class="indent_2">
            <span>4．</span><span>本人确认的上述送达方式适用于各个司法阶段，包括但不限于一审、二审、再审、执行以及督促程序（含支付令送达）。</span>
        </p>
        <p class="indent_2">
            <span>5．</span><span>若本人上述送达地址有变更，本人应当及时告知平台方和司法机关（如适用）变更后的送达地址。</span>
        </p>
        <p class="indent_2">
            <span>6．</span><span>本人已阅读本确认书所有条款，并保证上述送达地址是准确的、有效的；如果提供的送达地址不确切，或不及时告知变更后的地址，使法律文书无法送达或未及时送达，本人自行承担由此产生的法律后果。</span>
        </p>
        <p class="indent_2">
            <span>（以下无正文）</span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span><?php echo $ymd; ?></span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span>确认人：</span><?php echo $user['name']; ?>
        </p>
        <p class="indent_2">
            <br />
        </p>
    
    </div>
    

    <div class="main">
        <p>
            <br/>
        </p>
        <p class="b_title">
            <span>借款协议</span>
        </p>
        <p class="indent_2">
            <span></span><span>合同编号：</span><?php echo $contract_number2; ?>
        </p>
        <p class="indent_2">
            <span style="">甲</span><span style="color:#0A0A0A">方<span>(</span></span><span style="">借款</span><span style="color:#0A0A0A">人<span>)</span>： </span><?php echo $user['name']; ?>
        </p>
        <p class="indent_2">
            <span style="color:#0A0A0A">身份证号码：</span><?php echo $user['idcode']; ?>
        </p>
        <p class="indent_2">
            <span style="color:#0A0A0A">乙方</span><span style="">(</span><span style="color:#0A0A0A">出</span><span style="">借</span><span style="color:#0A0A0A">人<span>)</span>：</span><span >洪文利</span>
        </p>
        <p class="indent_2">
            <span style="color:#0A0A0A">身份</span><span style="">证</span><span style="color:#0A0A0A">号码<span>: </span></span><span >441521198108170852</span>
        </p>
        <p class="indent_2">
            <span style="color:#0A0A0A">丙方</span><span style="">(</span><span style="color:#0A0A0A">居</span><span style="">间</span><span style="color:#0A0A0A">方<span>)</span>：深圳市惠泽通电子科技<span>(</span><span>)</span>有限公司</span>
        </p>
        <p class="height_10"></p>
        <p class="indent_2">
            <span style="color:#0A0A0A">甲乙双方通过丙方平台，就乙方向甲方提供互联网小额借款事宜，经友好平等协商达成一致意见，签署本协议以资共同遵守。</span>
        </p>
        
        <table>
            <tbody>
                <tr>
                    <td colspan="4" align="center">
                        <p style="font-size:16px;color:#333;">
                            <span>借款明细表</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td width="300">
                        <p>
                            <span>借款金额</span>
                        </p>
                    </td>
                    <td width="" colspan="3" align="center">
                        <p>
                            <?php echo $fee['amount']; ?>元
                        </p>
                    </td>
                </tr>
                <tr>
                    <td width="300">
                        <p>
                            <span >大写</span>
                        </p>
                    </td>
                    <td  align="center" colspan="3">
                        <p>
                            <?php echo $fee['b_amount']; ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td width="300" >
                        <p>
                            <span>借款用途</span>
                        </p>
                    </td>
                    <td>
                        <p>
                            <span >日常消费</span>
                        </p>
                    </td>
                    <td>
                        <p>
                            <span >年化利率</span>
                        </p>
                    </td>
                    <td width="242" style="width: 242px;height: 40px ;text-align:center;">
                        <p>
                            <span>23.7%</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td width="300">
                        <p>
                            <span>借款起息日</span>
                        </p>
                    </td>
                    <td width="241" style="width: 241px;height: 55px">
                        <p>
                            <span >自借款发放之日起</span>
                        </p>
                    </td>
                    <td width="132">
                        <p>
                            <span>借款到期日</span>
                        </p>
                    </td>
                    <td width="242">
                        <p >
                            <span><?php echo $due_ymd; ?></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td width="300" >
                        <p>
                            <span>还款方式</span>
                        </p>
                    </td>
                    <td width="614" colspan="3">
                        <p>
                            <span>到期还本付息</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td width="300">
                        <p>
                            <span>还款日</span>
                        </p>
                    </td>
                    <td width="614" colspan="3">
                        <p>
                            <span><?php echo $due_ymd; ?></span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="height_10"></p>
        <p>
            <span>释义：</span>
        </p>
        <p class="indent_2">
            <span>1.<span>&nbsp;&nbsp; </span></span><span>《借款协议》</span><span>是指本协议<span>(</span>以下简称“借款协议”或“本协议”<span>) ,</span>系由甲、乙、丙三方通过丙方平台确认并同意接受全部条款，且经甲方亲自填写相关个人信息与借款信息后形成的网络借贷关系的合同。</span>
        </p>
        <p class="indent_2">
            <span>2.<span>&nbsp;&nbsp; </span></span><span>借款人<span>(“</span>甲方</span><span style="color:#282828">”</span><span>)</span><span>是指</span><span>《借款协议》</span><span>中列明的持中华入民共</span><span style="color:#282828">和</span><span>国法律规定的具有完全民事行为能力的个体（不含</span><span style="color:#282828">高</span><span>等院校全日制学生）。甲方为丙方平台的实名注册用户<span>,</span>已向丙方如实提供借款所需个人信息。</span>
        </p>
        <p class="indent_2">
            <span style="color:#282828">3.<span>&nbsp;&nbsp; </span></span><span style="color:#282828">出</span><span>借人<span>(</span>“乙方</span><span style="color:#282828">”</span><span>)</span><span>是指</span><span>《借款协议》</span><span>中列明</span><span style="color:#282828">的</span><span>符合</span><span style="color:#282828">中</span><span>华人民</span><span style="color:#282828">共和</span><span>国法律规定的具有完全民事行为能力的个体。乙方为经过丙方评估确认，授权丙方推介、选择借款人，并同意借款给甲方的资金出借方。</span>
        </p>
        <p class="indent_2">
            <span>4.<span>&nbsp;&nbsp; </span></span><span>居</span><span style="color:#282828">间</span><span>方</span> <span>(</span><span>“丙方”<span>)</span>是指</span><span style=";line-height: 150%;color:#282828">深圳市惠泽通电子科技<span>(</span><span>)</span>有限</span><span >公司</span><span>(</span><span>以</span><span style="color:#282828">下简</span><span>称</span><span style="color:#282828">“</span><span >惠泽通科技</span><span style="color:#282828">”</span><span>)</span><span>，系</span><span style="color:#818282">一</span><span>家以互联网为主要渠道，为借款人与出借人实现直接借贷提供信息搜集、信息公布、资信评估、信息交互、借贷撮合、贷</span><span style="color:#282828">后</span><span>管</span><span style="color:#282828">理</span><span>等服务的金融信息中介公司。</span>
        </p>
        <p class="indent_2">
            <span>5.<span>&nbsp;&nbsp; </span></span><span>丙方平台是指</span><span >惠泽通科技设立、管理的具备互联网借贷</span><span>金融信息服务</span><span > 的网络平台，其形式包括但不限于：网站（网址：</span><span > www.tupulian.com</span><span >）、</span><span>手机、平板电脑等智能终端安装的应用程序（<span>App</span>）。</span>
        </p>
        <p class="indent_2">
            <span>6.<span>&nbsp;&nbsp; </span></span><span style="color:#282828">甲</span><span>方账</span><span style="color:#282828">户是</span><span>指</span><span style="color:#282828">甲</span><span>方</span><span style="color:#282828">在银行</span><span>/</span><span>第三方</span><span style="color:#282828">支付机构开立的用以接受借款<span>,</span></span><span>支</span><span style="color:#282828">付还</span><span>款</span><span style="color:#282828">的</span><span>相关</span><span style="color:#282828">账</span><span>户。</span>
        </p>
        <p class="indent_2">
            <span >7.<span>&nbsp;&nbsp; </span></span><span>乙</span><span style="color:#242424">方账户是指</span><span>乙</span><span style="color:#242424">方在银行</span><span>/</span><span style="color:#242424">第</span><span>三</span><span style="color:#242424">方</span><span>支</span><span style="color:#242424">付机构开立的或乙方指定<span>/</span>委托的用以提供</span><span >的发放借款<span>, </span>收取还款的相关要账户。</span>
        </p>
        <p class="indent_2">
            <span>8.<span>&nbsp;&nbsp; </span></span><span >本协议项下甲方、乙方、丙方单独称“一方”<span>,</span>任意两方合称“双方”<span>,</span>任意三方合称“三方”。</span>
        </p>
        <p class="indent_2">
            <span>9.<span>&nbsp;&nbsp; </span></span><span >工作日是</span><span>指</span><span >除国家法定节假日、公休日以外的丙方对外办理业务的任何</span><span>一日</span><span >。</span>
        </p>
        <p class="indent_2">
            <span>&nbsp;</span>
        </p>
        <p class="indent_2">
            <strong><span >第一条 借款人确认</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span >借款人在签约前须确认并保证借款人同时满足以下条件，并承诺在本协议有效期内始终有效：</span>
        </p>
        <p class="indent_2">
            <span >1</span><span >、借款人具有签订和履行本协议的资格和能力，借款人可以独立地作为一方诉讼主体；</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、借款人签订本协议已获得所有必需的授权或批准，签订和履行本协议不违反借款人作为签约一方的任何协议以及相关法律法规的规定，与借款人应承担的其他合同项下的义务均无抵触；</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、使用以借款人本人名义在丙方平台实名注册并设置了密码的个人账户在丙方平台所从事的所有行为均视为借款人本人行为，包括但不限于订立本协议、申请支用资金和归还借款等，该等行为的法律后果均由借款人本人承担；</span>
        </p>
        <p class="indent_2">
            <span >4</span><span >、借款人有足够的偿债能力归还本协议项下的借款本息；</span>
        </p>
        <p class="indent_2">
            <span >5</span><span >、借款人提供给丙方平台的所有文件和资料都是真实、准确、完整和有效的，不存在虚假记载、重大遗漏或误导性陈述；</span>
        </p>
        <p class="indent_2">
            <span >6</span><span >、本协议第一条所述借款人的收款账户为借款人所有并使用；</span>
        </p>
        <p class="indent_2">
            <span >7</span><span >、借款人同意《授权委托书》载明的所有内容，并连同本协议一并签署；</span>
        </p>
        <p class="indent_2">
            <span >8</span><span >、借款人同意《平台服务协议》载明的所有内容，并连同本协议一并签署。</span>
        </p>
        <p class="indent_2">
            <strong><span >第二条 借款</span></strong><strong></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、借款人、出借人一致同意本协议以数据电文形式订立并认同其效力。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、借款人使用账号、密码登录丙方平台，根据丙方平台完成合同条款阅读、查看说明与提示、填写借款信息等并最终成功提交丙方平台后，视为借款人接受借款合同所有条款（含借款利息、借款期限、还款方式等信息）。乙方同意接受合同条款并不导致借款合同立即生效，仅经过丙方平台或丙方委托的第三方机构完成对甲方的风险评估并接受出借人指令将借款资金实际划入甲方账户时，本借款合同方为生效。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、借款合同由本协议条款、借款人在丙方平台填写的所有信息、丙方平台各项规则和其他与本借款相关的页面信息组成，各组成部分均具有法律效力，与本协议条款有冲突的以本协议条款为准。</span>
        </p>
        <p class="indent_2">
            <span >4</span><span >、起息日是指乙方的出借资金到达借款人账户开始计算利息的日期。</span>
        </p>
        <p class="indent_2">
            <span >5</span><span >、还款日是指本协议约定的借款到期日<span>, </span>实际的还款日以甲方还款到达乙方账户之日为准。</span>
        </p>
        <p class="indent_2">
            <span >6</span><span >、借款期限是指从起息日起至还款日止的期间。发生以下任一情形时，甲方有权自主决定本协议项下已发放的部分或全部贷款提前到期，并授权丙方向甲方进行催收、划转或以其他任何形式主张债权：</span>
        </p>
        <p class="indent_2">
            <span >（<span>1</span>）借款人未能按本协议约定偿付到期本金和<span>/</span>或利息的；</span>
        </p>
        <p class="indent_2">
            <span >（<span>2</span>）乙方、丙方任何时候发现甲方提交的信息存在虚假、捏造情形；</span>
        </p>
        <p class="indent_2">
            <span >（<span>3</span>）乙方、丙方认为借款人存在违约行为。</span>
        </p>
        <p class="indent_2">
            <span >7</span><span >、甲方承诺，本协议项下的借款专用于借款人日常消费，不得用于以下用途（包括但不限于）：</span>
        </p>
        <p class="indent_2">
            <span >（<span>1</span>）投资股票、场外配资、期货合约、结构化产品及其他衍生品等高风险投资；</span>
        </p>
        <p class="indent_2">
            <span >（<span>2</span>）用于房地产项目开发、购买商品房<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>3</span>）用于赌博、非法交易<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>4</span>）用于国家法律法规明令禁止或限制的各项其他活动。</span>
        </p>
        <p class="indent_2">
            <strong><span >第三条 账户</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、甲方账户</span>
        </p>
        <p class="indent_2">
            <span >甲方指定下列账户作为</span><span style="color:#282828">用以接受借款、</span><span>支</span><span style="color:#282828">付还</span><span>款的账户：</span>
        </p>
        <p class="indent_2">
            <span >户<span>&nbsp; </span>名：</span><?php echo $bank['name']; ?>
        </p>
        <p class="indent_2">
            <span >账<span>&nbsp; </span>号：</span><?php echo $bank['card_num']; ?>
        </p>
        <p class="indent_2">
            <span >开户行：</span><?php echo $bank['bankcard_name']; ?>
        </p>
        <p class="indent_2">
            <span >2</span><span >、乙方账户</span>
        </p>
        <p class="indent_2">
            <span >乙方委托并授权丙方使用其自身账户发放借款、收取本金、利息、违约金、赔偿金及其他乙方基于本协议应向甲方收取的所有款项，丙方同意接受乙方委托，具体账户信息如下：</span>
        </p>
        <p class="indent_2">
            <span >户<span>&nbsp; </span>名：&nbsp;&nbsp;&nbsp;&nbsp; </span>深圳市惠泽通电子科技<span>(</span><span>)</span>有限公司</span></span>
        </p>
        <p class="indent_2">
            <span >账<span>&nbsp; </span>号：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 805880100033697</span></span></span>
        </p>
        <p class="indent_2">
            <span >开户行：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>广东华兴银行股份有限公司深圳宝安支行<span></span></span></span>
        </p>
        <p class="indent_2">
            <span >丙方收款后，应根据乙丙双方的约定及时向乙方支付收回的本息，逾期应承担违约责任，具体以双方已签署的书面合同为准。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、丙方账户</span>
        </p>
        <p class="indent_2">
            <span >根据</span><span>甲</span><span >方与丙方签</span><span>订</span><span >的</span><span >《平台服务协议》，下列账户为丙方收取平台服务费的账户。</span>
        </p>
        <p class="indent_2">
            <span >户<span>&nbsp; </span>名：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>深圳市惠泽通电子科技<span>(</span><span>)</span>有限公司
        </p>
        <p class="indent_2">
            <span >账<span>&nbsp; </span>号：&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;805880100033697
        </p>
        <p class="indent_2">
            <span >开户行：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>广东华兴银行股份有限公司深圳宝安支行
        </p>
        <p class="indent_2">
            <strong><span >第三条 正常还款</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、甲乙双方一致同意，本协议下的借款还款方式为：【到期还本付息】。甲方应于借款到期日<span>18:00</span>前通过丙方平台系统进行还款操作，或将足额将还款金额存放至甲方账户供丙方委托的银行<span>/</span>第三方支付机构自动划扣，以完成《借款明细表》列明的本息偿还。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、甲方不可撤销地授权丙方委托的银行<span>/</span>第三方支付机构有代收代扣本协议下还款相关款项的权利<span>,</span>从甲方还款账户中划扣甲方应还款项<span> (</span>如甲方逾期还款<span>, </span>应还款项包括逾期费用<span>),</span>银行<span>/</span>第三方支付机构在成功划转还款至乙方账户后视作甲方还款成功<span>,</span>由于账户余额不足、账户原因、甲方操作失误等原因导致划款失败的由甲方承担相应责任。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、乙方不可撤销地授权丙方委托的合作银行<span>/</span>第三方支付机构有代收代扣本协议下还款相关款项的权利<span>,</span>将还款本息支付至乙方账户。</span>
        </p>
        <p class="indent_2">
            <strong><span >第四条 逾期还款</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、甲方未能在还款日<span>18:00</span>前足额支付还款金额至甲方还款账户的<span>,</span>视作逾期还款。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、甲方逾期还款的<span>,</span>自还款日后第一日起<span>,</span>应向乙方承担违约责任及造成的损失，并赔偿丙方因维护本协议下债权债务关系依约履行而产生的相关合理費用<span>,</span>具体以甲方与丙方签订的《平台服务协议》为准。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、甲方逾期还款的<span>,</span>履行还款义务的顺序为<span>: </span>逾期违约金与守约方的损失及维权费用、平台服务费、利息、本金。</span>
        </p>
        <p class="indent_2">
            <span >4</span><span >、甲方逾期还款的<span>,</span>乙方有权将债权转让给适格第三方（包括但不限于丙方）。</span>
        </p>
        <p class="indent_2">
            <span >5</span><span >、甲方发生逾期情形的<span>,</span>乙方在此不可撤销的授权丙方以丙方名义进行催告<span>,</span>并授权丙方可转授权给丙方的合作方催告<span>,</span>催告方式包括但不限于<span>:</span>发送网站、<span> APP</span>推送通知<span>,</span>发送短信、发送函件、电话催告、提起诉讼<span>/</span>仲裁等。</span>
        </p>
        <p class="indent_2">
            <span >6</span><span >、甲方发生逾期应向乙方支付逾期违约金，逾期违约金以逾期未还金额（包括本金、利息）为基数按日收取<span>,</span>以当日<span>0:00</span>至<span>24:00 </span>为一日<span>,</span>乙方每日收取甲方逾期未还金额的<span>0.05%</span>作为逾期违约金<span>,</span>逾期违约金超过本金年化利率<span>36%</span>的部分不再计算与收取<span>,</span>但借款人仍应承担因借款人逾期还款<span>,</span>乙方及或丙方的维权费用<span>,</span>包括但不限于<span>:</span></span>
        </p>
        <p class="indent_2">
            <span>（<span>1</span>）<span>因债</span>权转<span>让</span>发<span>生</span>的税<span>费</span><span>;</span></span>
        </p>
        <p class="indent_2">
            <span>（<span>2</span>）<span>诉</span>讼、<span>仲裁过程中</span>支<span>出的费</span>用<span>,</span><span>包括</span>法<span>院</span>、<span>仲裁机</span>构收<span>取的费用等</span><span>;</span></span>
        </p>
        <p class="indent_2">
            <span>（<span>3</span>）审<span>计费</span>、<span>拍卖费</span>、律<span>师费、担保费等费用</span><span>;</span></span>
        </p>
        <p class="indent_2">
            <span>（<span>4</span>）<span>包括但</span>不<span>限于</span>交<span>通费</span>、<span>餐费等必要</span>差<span>旅費用。</span></span>
        </p>
        <p class="indent_2">
            <strong><span >第五条 提前结清</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、甲方可以在丙方平台发起提前结清服务申请<span>,</span>服务是否可用，以操作界面展示为准<span>(</span>受银行结算等原因影响<span>,</span>可能无法发起申请<span>) </span>。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、甲方选择提前结清的<span>:</span>甲方仍应按《借款协议》偿还事先约定的到期全额款项<span>(</span>包括综合服务费、利息、本金<span>) </span>。</span>
        </p>
        <p class="indent_2">
            <strong><span >第五条 债权、债务转让</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、未经乙方或乙方委托丙方事先书面<span>(</span>包括但不限于电子邮件等方式<span>)</span>同意<span>,</span>甲方不得将本协议项下的任何权利义务转让给任何第三方。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、乙方可通过签订《债权转让协议》向第三方转让本协议项下权利义务<span>, </span>转让后应及时通知甲方，甲方自收到乙方通知之日起应向新的债权人履行本协议项下全部义务。乙方将逾期债权转让给第三方的<span>,</span>违约金及相关维权费用的追偿权一并转让<span>,</span>乙方不再享有任何与债权相关的权利。</span>
        </p>
        <p class="indent_2">
            <strong><span >第六条 费用</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、甲方、乙方已充分知晓丙方提供本协议所约定的服务<span>, </span>丙方有权就该服务向甲方、乙方收取平台服务费<span>,</span>具体由甲方与乙方、丙方分别签署《平台服务协议》予以确认。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、甲方借款申请一旦通过丙方平台审核，甲方应根据《平台服务协议》向丙方支付平台服务费。甲方委托并授权丙方从乙方向甲方出借的资金中划付平台服务费，丙方划付平台服务费后，将乙方出借资金支付至甲方。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、甲方发生逾期的，丙方将向甲方提供</span><span>逾期管理服务，届时产生的逾期费用将由甲方承担，具体以</span><span >《平台服务协议》约定为准。</span>
        </p>
        <p class="indent_2">
            <strong><span >第七条 承诺与保证</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、甲方及乙方各自在此确认为具有完全民事权利能力和完全民事行为能力的主体<span>,</span>有权签订并履行本协议。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、乙方保证其所用于出借的资金来源合法<span>,</span>且乙方是该资金的合法支配权人<span>,</span>如第三方对资金归属、支配权、合法性等问题主张异议<span>,</span>给他方造成的损失的<span>,</span>应当赔偿损失。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、甲方、乙方应及时并如实向对方及丙方及指定的其他第三方<span>(</span>银行<span>/</span>第三方支付机构等<span>)</span>提供个体信息<span>(</span>包括但不限于姓名、身份证号、企业或组织名称、统一社会信用代码、联系方式、联系地址、职业信息、联系人信息等<span>)</span>以及借款用途等相关信息。</span>
        </p>
        <p class="indent_2">
            <span >4</span><span >、甲方、乙方承诺并保证向其他各方提供的所有本人信息均真实、完整、有效、及时<span>,</span>因上述任何本人信息及资料的变更、修改、停用等<span>,</span>至少提前<span>2</span>个工作目通知其他各方。</span>
        </p>
        <p class="indent_2">
            <span >5</span><span >、如甲方或乙方变更账户信息<span>(</span>账户名称、账号等<span>)</span>、通讯地址的<span>, </span>应当至少在款项交付日前<span>2</span>个工作日通知丙方。 如甲方或乙方因未能遵守上述承诺而导致自身损失<span>,</span>自行承担<span>,</span>导致丙方损失的<span>,</span>承担赔偿责任。</span>
        </p>
        <p class="indent_2">
            <span >6</span><span >、甲方承诺如发生任何影响或者可能影响甲方经济状况、信用状况、还款能力的事由<span>,</span>包括但不限于甲方的工作单位、职位、工作地点、薪酬等事项的变化<span>,</span>甲方应于前述变更发生之日起<span>2</span>个工作日内通知乙方、丙方。</span>
        </p>
        <p class="indent_2">
            <span >7</span><span >、各方承诺<span>,</span>各方不会利用丙方平台进行信用卡套现、洗钱、非法集资或其他不正当交易行为<span>,</span>否则应依法独立承担法律责任。</span>
        </p>
        <p class="indent_2">
            <span >8</span><span >、各方确认<span>,</span>甲方和乙方授权和委托丙方根据本协议所采取的全部行动和措施的法律后果均归属于甲方和乙方本人<span>;</span>在任何情形下<span>,</span>丙方不是本协议项下任何借款或债务的债务人或需要以其自有资金偿还本协议项下的任何借款或债务。</span>
        </p>
        <p class="indent_2">
            <span >9</span><span >、甲方、乙方无法正常使用本协议项下丙方或其合作方提供的服务时<span>,</span>有权追究丙方责任<span>,</span>但因下列事项的<span>,</span>应免除丙方责任<span>:</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>1</span>）丙方系统维护、故障、遭受黑客攻击、电信部门技术调整或故障、网站升级<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>2</span>）银行、第三方支付平台或金融相关等部门的意外事件<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>3</span>）法律法规及政策调整<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>4</span>）不可抗力。</span>
        </p>
        <p class="indent_2">
            <span >10</span><span >、各方知晓并认可丙方与银行、第三方支付机构的合作<span>,</span>确认并同意因资金在途或划转延迟等可能产生的收益归入丙方的居间报酬。</span>
        </p>
        <p class="indent_2">
            <strong><span >第八条 违约</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、发生下列任何一项或几项情形的<span>,</span>视为甲方违约<span>:</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>1</span>）甲方违反其在本协议约定、承诺及保证，未如实提供信息、履行合同义务的<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>2</span>）甲方的任何</span><span>财</span><span >产</span><span>遭受</span><span >没收、征</span><span>用</span><span >、</span><span>查封</span><span >、</span><span>扣</span><span >押、冻结</span><span>等可能影响其履</span><span >约能力</span><span>的</span><span >不</span><span>利事</span><span >件<span>,</span>且不能及时提供</span><span>有</span><span >效</span><span>补</span><span >救</span><span>措施的</span><span >;</span>
        </p>
        <p class="indent_2">
            <span >（<span>3</span>）甲</span><span>方的财</span><span >务状</span><span>况出现影响其履</span><span >约</span><span>能</span><span >力</span><span>的</span><span >不利</span><span>变</span><span >化</span><span>,</span><span>且</span><span >不</span><span>能</span><span >及</span><span>时提</span><span >供</span><span>有</span><span >效</span><span>补救措施的。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、</span><span>若甲方违约</span><span >或</span><span>丙方合理判断甲</span><span >方</span><span>可</span><span>能</span><span >发生</span><span>违约事件的</span><span >,</span><span >乙方及</span><span>/</span><span >或乙方</span><span>的债权受</span><span >让人</span><span>(</span><span >或</span><span>授权第三方</span><span >)</span><span>有权采取下列任何</span><span >一</span><span>项</span><span >或</span><span>几项</span><span >救济措施：</span>
        </p>
        <p class="indent_2">
            <span >（<span>1</span>）</span><span>立即暂缓</span><span >、</span><span>取</span><span >消发</span><span>放</span><span >全</span><span>部或部</span><span >分</span><span>借款</span><span >;</span>
        </p>
        <p class="indent_2">
            <span >（<span>2</span>）</span><span>宣告已</span><span >发</span><span>放</span><span >借</span><span>款全部提前到期</span><span >,</span><span>甲</span><span >方</span><span>应</span><span >立</span><span>即</span><span >偿</span><span>还所有应付款项<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>3</span>）</span><span>解除</span><span >本</span><span>协</span><span >议<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>4</span>）</span><span>采取法律</span><span >、</span><span>法规以</span><span >及本</span><span>协</span><span >议约定</span><span>的其他救济措施。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、乙</span><span>方的债权受让方向</span><span >乙</span><span>方支付相关款项后</span><span>,</span><span >乙</span><span>方应积极协助乙方的债权受让方向甲</span><span >方</span><span>追偿<span>(</span>包括但</span><span >不</span><span>限于签署</span><span >、 </span><span>提供相应书面材料等</span><span >) ,</span><span>若因</span><span >乙</span><span>方原</span><span>因</span><span>导致债权受让方不能追偿的<span>,</span></span><span >乙</span><span>方应向债权受让方承担赔偿责任或返还偿付款项。</span>
        </p>
        <p class="indent_2">
            <strong><span >第九条 证据和计算</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、本协议各方确认并同意<span>,</span>由丙方对本协议项下所渉的任何金额进行计算<span>; </span>丙方可通过其自身、 合作方及合作的银行<span>/</span>第三方支付机构提供的电子数据信息进行计算、统计<span>,</span>对法院需要认定的必要费用提供财务凭证<span>,</span>在无明显错误的情况下<span>,</span>上述针对本协议项下任何金额的任何证明或确定<span>,</span>应作为该金额有关事项的终局证明。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、甲乙双方委托丙方对相关金额进行计算<span>,</span>并在丙方平台发布或更新具体信息<span>,</span>上述还款明细表中列明的还款本息金额若与丙方平台发布或更新的还款金额不一致的<span>,</span>以丙方平台上发布或更新的数据为准。</span>
        </p>
        <p class="indent_2">
            <strong><span >第十条 保密条款</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、各方应将其在本协议及其附属合同、文件的签订和履行过程中取得的有关内容以及与此等内容有关的任何文件、 资料或信息视为保密信息<span>(</span>以下简称“保密信息”<span>) </span>。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、任意一方向其他各方承诺<span>,</span>其不会使用或向非本协议方披露保密信息<span>,</span>除非事先得到其他三方的书面同意。</span>
        </p>
        <p class="indent_2">
            <span >3</span><span >、下述信息不适用于保密条款<span>:</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>1</span>）该等信息已为公众所知<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>2</span>）任何适用法律要求披露的、或者有权的司法机关、政府机关、监管机关要求披露的、或者法院裁定要求披露的信息<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>3</span>）相关方在正当履行本协议时披露的或随本协议而相应披露的信息<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>4</span>）相关方从第三方获得的且无须承担保密义务的信息。</span>
        </p>
        <p class="indent_2">
            <span >但是上述第<span>(3)</span>项或第<span>(4)</span>项信息的披露必须基于接收方对上述信息保密并且接收方仅可为披露之目的使用上述信息 。</span>
        </p>
        <p class="indent_2">
            <span >4</span><span >、保密信息应绝对保密<span>,</span>未经本协议各方以书面方式一致同意<span>,</span>任何一方不得向除政府审批机构、顾问<span>(</span>包括律师、会计师、评估师和其他专业顾问<span>)</span>、为本协议的交易提供服务的机构、股东和相关工作人员之外的任何与本协议无利害关系的第三人披露本协议任何条款 。</span>
        </p>
        <p class="indent_2">
            <span >5</span><span >、不论本协议是否有数或者是否履行完毕<span>,</span>本条规定的保密义务不因此受到任何影响。保密条款在本协议合法解除、终止或履行完毕后继续有效。</span>
        </p>
        <p class="indent_2">
            <strong><span >第十一条 通知</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、本协议任何一方根据本协议约定做出的通知和<span>/</span>或文件均应以书面形式做出<span> (</span>不包括本协议第七条因贷后管理服务所涉内容<span>) ,</span>可由专人送达、挂号邮速、特快专递或通过丙方网络平台发布等方式传送<span>,</span>具体送达信息以本协议约定或各方在丙方平台的注册信息或登记信息为准 。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、通知在下列日期视为送达</span>
        </p>
        <p class="indent_2">
            <span >（<span>1</span>）短信、邮件发出即视为送达；</span>
        </p>
        <p class="indent_2">
            <span >（<span>2</span>）专人速送的通知<span>,</span>在专人速送之交付日为有效送达<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>3</span>）以特快专进<span>(</span>付清邮资<span>)</span>发出的通知<span>,</span>在寄出<span>(</span>以邮戳为凭<span>)</span>后的三个<span>(3)</span>工作日内为有效送达<span>;</span></span>
        </p>
        <p class="indent_2">
            <span >（<span>4</span>）通过丙方平台发布的方式通知的<span>,</span>在丙方平台发布之日为有效送达。</span>
        </p>
        <p class="indent_2">
            <strong><span >第十二条 法律适用和管糖</span></strong>
        </p>
        <p class="indent_2">
            <span >如果各方在本协议履行过程中发生争议<span>,</span>由本合同的签署地深圳市宝安区人民法院管辖。</span>
        </p>
        <p class="indent_2">
            <strong><span >第十三条 其他</span></strong>
        </p>
        <p class="indent_2">
            <span >1</span><span >、本协议自甲方在丙方平台完成借款操作时成立，自出借资金到达甲方账户之日起生效。</span>
        </p>
        <p class="indent_2">
            <span >2</span><span >、本协议各方委托丙方保管所有与本协议有关的书面文件或电子信息<span>;</span>本协议各方确认并同意由丙方提供的与本协议有关的书面文件或电子信息在无明显错误的情况下应作为本协议有关事项的终局证明 。</span>
        </p>
        <p class="indent_2">
            <span>（以下无正文，为签署页）</span>
        </p>
        
        <p class="height_10"></p>
        <p class="indent_2">
            <span>（以下为《借款协议》签署页）</span>
        </p>
        <p class="indent_2">
            <span>甲方(借款人)：</span><?php echo $user['name']; ?>
        </p>
        <p class="indent_2">
            <span>乙方<span>(</span>出借人<span>) </span>：</span><span >洪文利</span>
        </p>
        <p class="indent_2">
            <span>丙方<span>(</span>居间方<span>)</span>： 深圳市惠泽通电子科技<span>(</span><span>)</span>有限公司</span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span>日期：</span><?php echo $ymd; ?>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span>签署地：深圳市宝安区</span>
        </p>
        <p class="indent_2">
            <span>附件：授权委托书</span>
        </p>
        <p><span>&nbsp;</span></p>
        <p><span>&nbsp;</span></p>
        <p class="indent_2">
            <span>授权委托书</span>
        </p>
        <p class="height_10"></p>
        <p class="indent_2">
            <span>授权人：</span><?php echo $user['name']; ?>
        </p>
        <p class="indent_2">
            <span>被授权人：深圳市惠泽通电子科技<span>(</span><span>)</span>有限公司</span>
        </p>
        <p class="height_10"></p>
        <p class="indent_2">
            <span>授权人（非高等院校全日制学生）同意并签署本授权委托书，在此通过本授权委托书作出如下授权与承诺：</span>
        </p>
        <p class="indent_2">
            <span>一、</span><span>授权人一经签署本授权委托书，即视为已经同意并授权被授权人及与被授权人合作的指定第三方银行或第三方支付机构（以下简述“指定第三方”）对授权人运营的网络平台（以下简述“被授权人平台”）发起借款项目（包括本授权委托书生效之前及其之后的借款项目，具体以授权人签订的各《借款协议》、《平台服务协议》为准，以下简述相关协议）进行身份信息验证、银行卡验证等必要的信息验证。</span>
        </p>
        <p class="indent_2">
            <span>二、</span><span>授权人同意，在各相关协议生效后，指定第三方有权依照各相关协议约定的期限和金额从授权人通过本授权委托书指定的银行卡（银行卡账户信息见第十三条）进行资金的代扣、代付及划转各相关协议项下的全部应付款项。</span>
        </p>
        <p class="indent_2">
            <span>三、</span><span>授权人承诺，本授权委托书第十三条记载的授权银行卡账户是以本人真实姓名开立的合法、有效的银行卡账户，授权人同意本授权委托书第一条、第二条约定的资金代扣及转账优先于该账户其他任何用途的支付。</span>
        </p>
        <p class="indent_2">
            <span>四、</span><span>授权人同意，与授权人委托书项下的资金代扣、代还、代付及划转服务相关的任何责任，如在借款成功后，因指定第三条的系统维护等任何原因导致无法将款项代还至授权指定的银行卡中等情况发生，被授权人、被授权人合作方及指定第三方亦无需承担责任。</span>
        </p>
        <p class="indent_2">
            <span>五、</span><span>授权人在指定银行卡账户中必须留有足够余额，否则因账户余额不足或不可归责于被授权人的任何事由，导致无法及时扣款或扣款错误、失败，责任由授权人自行承担。</span>
        </p>
        <p class="indent_2">
            <span>六、</span><span>各相关协议的债权人按照相关协议约定的被授权人平台规则转让各相关协议项下的债权的，不影响本授权委托书的有效性。</span>
        </p>
        <p class="indent_2">
            <span>七、</span><span>授权人针对对授权人指定的银行卡账户向被授权人进行授权后，即视为授权人就该银行卡在授权人通过被授权人平台发起的所有借款项目范围内进行了授权，不因授权人后续更换绑定银行卡，或对其他银行卡进行授权而无效或产生任何影响。</span>
        </p>
        <p class="indent_2">
            <span>八、</span><span>本授权委托书为授权人对被授权人从其授权的指定账户中扣款和<span>/</span>或向该账户转账的授权证明，不作为收付现金的直接凭据。</span>
        </p>
        <p class="indent_2">
            <span>九、</span><span>凡本授权委托书中未约定的事项，适用各相关协议的约定，凡本授权委托书中出现的与各相关协议相同的词语或术语，如果在本授权委托书中无特别定义，适用各相关协议中相同词语和术语的定义、涵义或解释，本授权委托书的规定与各相关协议不一致的，以本授权委托书的规定为准。</span>
        </p>
        <p class="indent_2">
            <span>十、</span><span>授权人发起终止授权或变更账户、通讯地址时，在当期款项支付日<span>2</span>个工作日前通知被授权人并完成信息更新，否则自行承担所造成的风险损失。</span>
        </p>
        <p class="indent_2">
            <span>十一、</span><span>授权人保证本授权委托书的真实性、合法性、有效性、被授权人依据本授权委托书进行的操作引起的一切法律纠纷或风险，由授权人独立承担或解决。</span>
        </p>
        <p class="indent_2">
            <span>十二、</span><span>本授权委托书自授权人确认同意起生效，至授权人通过被授权人平台签订的全部相关协议履行完毕，所有款项全部还请时终止。</span>
        </p>
        <p class="indent_2">
            <span>十三、</span><span>授权人资料：</span>
        </p>
        <table>
            <tbody>
                <tr>
                    <td width="250" valign="top" >
                        <p>
                            <span >姓名</span>
                        </p>
                    </td>
                    <td width="390" valign="top" >
                        <p><?php echo $user['name']; ?></p>
                    </td>
                </tr>
                <tr>
                    <td width="250" valign="top" >
                        <p>
                            <span style=";">身份证号码</span>
                        </p>
                    </td>
                    <td width="390" valign="top" >
                        <p><?php echo $user['idcode']; ?></p>
                    </td>
                </tr>
                <tr>
                    <td width="250" valign="top" >
                        <p>
                            <span style=";">联系手机</span>
                        </p>
                    </td>
                    <td width="390" valign="top" >
                        <p><?php echo $user['phone']; ?></p>
                    </td>
                </tr>
                <tr>
                    <td width="250" valign="top" >
                        <p>
                            <span style=";">借记卡户名</span>
                        </p>
                    </td>
                    <td width="390" valign="top" >
                        <p><?php echo $bank['name']; ?></p>
                    </td>
                </tr>
                <tr>
                    <td width="250" valign="top" >
                        <p>
                            <span style=";">借记卡开户银行</span>
                        </p>
                    </td>
                    <td width="390" valign="top" >
                        <p><?php echo $bank['bankcard_name']; ?></p>
                    </td>
                </tr>
                <tr>
                    <td width="250" valign="top" >
                        <p>
                            <span style=";">借记卡账号</span>
                        </p>
                    </td>
                    <td width="390" valign="top" >
                        <p><?php echo $bank['card_num']; ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="indent_2">
            <span>（以下无正文）</span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span><?php echo $ymd; ?></span>
        </p>
        <p class="indent_2" style="text-align:right;">
            <span>授权人：</span><?php echo $user['name']; ?>
        </p>
        <p>
            <br/>
        </p>
    </div>

</body>
</html>
