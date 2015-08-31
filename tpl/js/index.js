$(function(){
    $(".index_alert").hover(function(){
	$(this).find(".alert_del").css("visibility","visible");

    },function(){
	$(this).find(".alert_del").css("visibility","hidden");
    }
			   );
    
    $(".alert_del").live('click',function(){
	var a = $(this).parent().parent().find(".index_alert_id").text();
	var pa= $(this).parent().parent();
	$.post("message.php?action=del",
	          
	       {"id":a},
	       function(msg){
	 
		   //登陆成功
		   if(msg == "删除成功") {
		       pa.remove();
		   }

		   //登陆不成功
		   if(msg == "删除不成功") {
		 
		   }
	       },"text");//end of $.post


	
    });
});//end of all
