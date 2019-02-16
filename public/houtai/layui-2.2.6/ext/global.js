var  dataStore  = {
		get:function(key) {
			return localStorage.getItem(key);
			var obj = localStorage.getItem(key);
			if(null==obj) return null;
			return obj[key];
		},
		set:function(key,v) {
			localStorage.setItem(key,v);
		},
		del: function(k) {
			localStorage.removeItem(k);
		}
}

function ajaxCall(data) {
	if(!data) return;
	var d = null;
	if( data.code) {
		d = data;
	}else {
		try {
			d = JSON.parse(data);
		}catch(e){
			d = {};
		}
		
	}
	if(d.code=='200' ) {
		return true;
	}
	if(d.code=='401' || d.code=='402' || d.code=='403' ||d.code=='404') {
        $jq = layui.jquery;
		layer.open({
              title: layui.language.get('prompt'),           //'提示'
			  content: d.message,
            success: function (index, layero) {
                $jq(':focus').blur();
            },
              btn : [ layui.language.get('certainly')]		//按钮
			}); 
		return false;
	}
	if(d.code=='500' || d.code=='201') {
		$jq = layui.jquery;
		layer.open({
              title: layui.language.get('prompt'),           //'提示'
			  content: d.message||layui.language.get('error_tips'),
            success: function (index, layero) {
                $jq(':focus').blur();
            },
			  btn : [ layui.language.get('certainly')]		//按钮
			}); 
		return false;
	}
	if(d.code=='800') {
		location.href='login.html';
		return false;
	}
	return true;
}


// 设置最小可选的日期
function minDate(){
    var now = new Date();
    return now.getFullYear()+"-" + (now.getMonth()+1) + "-" + now.getDate();
}


in_array=function(e){
    var r=new RegExp(','+e+',');
    return (r.test(','+this.join(this.S)+','));
};





