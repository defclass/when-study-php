function newcode(wz){
    var  img_obj = document.getElementById('imgcode');
    img_obj.src =wz + new Date().getTime();

}

function ajax_xmlhttp(){
    var XmlHttp;
    if (window.ActiveXObject)
    {
        var arr=["MSXML2.XMLHttp.6.0","MSXML2.XMLHttp.5.0",
                 "MSXML2.XMLHttp.4.0","MSXML2.XMLHttp.3.0","MSXML2.XMLHttp","Microsoft.XMLHttp"];
        for(var i=0;i<arr.length;i++)
        {
            try
            {
                XmlHttp=new ActiveXObject(arr[i]);
                return XmlHttp;
            }
            catch(error)
            {
                
            }
        }
    }
    else
    {
        try
        {
            XmlHttp=new XMLHttpRequest();
            return XmlHttp;
        }
        catch(otherError)
        {
            
        }
    } 
}

function m_login(ref){
    var msg = document.getElementById("msg");
    var ajax = ajax_xmlhttp(); //将xmlhttprequest对象赋值给一个变量．
    var f=document.user_info;
    var username=user_info.username.value;
    var password=user_info.password.value;
    var code=user_info.code.value;


    if(username.length==0 || password.length==0 || code.length!=4)
    {
	
    	if(username.length==0 || password.length==0 || password=="密码" || username=="用户名/邮箱/手机")
    	{
    	    msg.innerHTML = "用户名和密码不能为空.";
    	}
    	else
    	{
    	    msg.innerHTML = "请填写正确的验证码.";
    	}
    }
    else
    {
	
    	var postdata="username="+ username +"&password="+ password +"&code="+ code;
    	ajax.open("post","index.php?action=login"+"&ref="+ref,true);//设置请求方式，请求文件，异步请求
    	ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    	ajax.onreadystatechange = function(){//你也可以这里指定一个已经写好的函数名称
    	    if(ajax.readyState==4){//数据返回成功
    		if(ajax.status == 200){
    		    newcode('code.php?timeamp=');
    		    //alert(ajax.responseText);
    		    str1=ajax.responseText;
    		    if(str1.indexOf("成功")>0)
    		    {
			
			
    			location='main.php';
    		    }
    		    else
    		    {
    			msg.innerHTML = str1;
	            }
    		}    
    	    }
    	}
    	ajax.send(postdata);
	
    }
    
}
