
/**
 * 时间处理
 * @param inputTime
 * @returns
 */
function formatDateTime(inputTime) {    
    var date = new Date(inputTime);  
    var y = date.getFullYear();    
    var m = date.getMonth() + 1;    
    m = m < 10 ? ('0' + m) : m;    
    var d = date.getDate();    
    d = d < 10 ? ('0' + d) : d;    
    var h = date.getHours();  
    h = h < 10 ? ('0' + h) : h;  
    var minute = date.getMinutes();  
    var second = date.getSeconds();  
    minute = minute < 10 ? ('0' + minute) : minute;    
    second = second < 10 ? ('0' + second) : second;   
    return y + '-' + m + '-' + d+' '+h+':'+minute+':'+second;    
}; 

//一般图片
function showImg(obj,photo_name) {
	$jq = layui.jquery;
	var  src = $jq(obj).attr('src');

    if(photo_name == undefined){
	    photo_name = 'Tip';
    }
	layer.open({
		   type: 1,
           title:[photo_name,'text-align:center;font-size:18px;'],
           content:'<div id="div-img" ><img  id="img-transform" src="'+src+'"/><div style="width:20px;height:20px;margin:0 auto;margin-top:5px;"><i class="layui-icon layui-icon-refresh"  id="img-xz"></i></div></div>'
		});

	//默认顺时针旋转90°
    var deg =0;
    document.getElementById("img-xz").onclick = function () {
        deg += 90;
        document.getElementById("img-transform").style.transform = "rotate(" + deg + "deg)";
    }
    // $jq('#img_xz').click(function() {
    //     deg += 90;
    //     $jq("#div-img").css('transform', 'rotate(' + deg + 'deg)');
    // });
};

// 签名图片
function showCreditImg(obj,photo_name) {
    $jq = layui.jquery;
    var  src = $jq(obj).attr('src');
    layer.open({
        type: 1,
        title:[photo_name,'text-align:center;font-size:18px;'],
        content:'<div id="div-img" ><img  id="img-transform" src="'+src+'"/><div style="width:20px;margin:5px auto;margin-top:25px;"><i class="layui-icon layui-icon-refresh"  id="img-xz"></i></div></div>'
    });
    //默认顺时针旋转90°
    var deg =0;
    document.getElementById("img-xz").onclick = function () {
        deg += 90;
        document.getElementById("img-transform").style.transform = "rotate(" + deg + "deg)";
    }
};

//报表按钮（选中本周）
function selectThisWeek(left,leftSpan,right,rightSpan){
	$jq = layui.jquery;
	$jq(left).css('background-color','#1c3368');
	$jq(leftSpan).css('color','#FFFFFF');
	$jq(right).css('background-color','');
	$jq(rightSpan).css('color','');
}

//报表按钮（选中本月）
function selectThisMonth(left,leftSpan,right,rightSpan){
	$jq = layui.jquery;
	$jq(left).css('background-color','');
	$jq(leftSpan).css('color','');
	$jq(right).css('background-color','#1c3368');
	$jq(rightSpan).css('color','#FFFFFF');
}





/*
var dataStore = {
	init: function(){
		$jq = layui.jquery;
		var data = $jq('#dataStore').data();
		if(!data || !data.version) {
			var obj = {};
			obj.data={};
			obj.version='v1';
			$jq('#dataStore').data(obj);
		}
	}
    ,get: function(k) {
    	
    	this.init();
    	$jq = layui.jquery;
		var data = $jq('#dataStore').data().data;
		return data[k];
    }
    ,set: function(k,v) {
    	this.init();
    	$jq = layui.jquery;
		var data = $jq('#dataStore').data().data;
		data[k] = v;
		var obj = {};
		obj.data=data;
		$jq('#dataStore').data(obj);
    }
    ,del: function(k) {
    	this.init();
    	$jq = layui.jquery;
		var data = $jq('#dataStore').data().data;
		delete(data[k]);
    }


} */
function goView(url) {
    var $jq = layui.jquery;
    $jq('#left a[data-href="#'+url+'"]').click();
}

//hours为空字符串时,cookie的生存期至浏览器会话结束。hours为数字0时,建立的是一个失效的cookie,这个cookie会覆盖已经建立过的同名、同path的cookie（如果这个cookie存在）。
function setCookie(name,value,hours,path){
    var name = escape(name);
    var value = escape(value);
    var expires = new Date();
    expires.setTime(expires.getTime() + hours*3600000);
    path = path == "" ? "" : ";path=" + path;
    _expires = (typeof hours) == "string" ? "" : ";expires=" + expires.toUTCString();
    document.cookie = name + "=" + value + _expires + path;


}
//获取cookie值
function getCookieValue(name){
    var name = escape(name);
    //读cookie属性，这将返回文档的所有cookie
    var allcookies = document.cookie;
    //查找名为name的cookie的开始位置
    name += "=";
    var pos = allcookies.indexOf(name);
    //如果找到了具有该名字的cookie，那么提取并使用它的值
    if (pos != -1){                       //如果pos值为-1则说明搜索"version="失败
        var start = pos + name.length;         //cookie值开始的位置
        var end = allcookies.indexOf(";",start);    //从cookie值开始的位置起搜索第一个";"的位置,即cookie值结尾的位置
        if (end == -1) end = allcookies.length;    //如果end值为-1说明cookie列表里只有一个cookie
        var value = allcookies.substring(start,end); //提取cookie的值
        return (value);              //对它解码
    }
    else return "";                //搜索失败，返回空字符串
}

function deleteCookie(name,path){   /**根据cookie的键，删除cookie，其实就是设置其失效**/
var name = escape(name);
    var expires = new Date(0);
    path = path == "" ? "" : ";path=" + path;
    document.cookie = name + "="+ ";expires=" + expires.toUTCString() + path;
}


//金额 数字格式化,从右往左每隔三位加逗号
function formatNumber(str) {
    var str = (str || 0).toString();
    if(str.length <= 3){
        return str;
    } else {
        return formatNumber(str.substr(0,str.length-3))+','+str.substr(str.length-3);
    }
}


//金额 数字格式化,从右往左每隔三位小数加逗号
function add_comma_toThousands(num) {
    var num = (num || 0).toString();
    var result = '';
    while (num.length > 3) {
        result = ',' + num.slice(-3) + result;
        num = num.slice(0, num.length - 3);
    }
    if (num) {
        result = num + result;
    }
    return result;
}
