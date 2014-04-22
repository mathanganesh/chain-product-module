$(document).ready(function(){
 //$(".exclusive").remove();
 $("#quantity_wanted_p").hide();
 $("#add_to_cart .exclusive").remove();
 var max=$("#max").val();
 var main_parent=$("#main_parent").val();
 var parent_name=$("#parent_name").val();
 var alertmessage=$("#alertmessage").val();
 var stopbuy1=$("#stopbuy1").val();
 var stopbuy2=$("#stopbuy2").val();
 var previpa=$("#previpa").val();
 
 
 var base_url = $("#baseurl").val();
$("#add_next").one("click",function(e){
  e.preventDefault();
  if(typeof(max)!="undefined")
  {
			var left= $.trim($("#left").val());	
			var right= $.trim($("#right").val());			
			var product= $.trim($("#product_id").val());	
			var guest= $.trim($("#guest").val());			
			var customer= $.trim($("#customer").val());			
			if(left=="" && right=="")								
				{	
					alert(alertmessage);
					window.location.reload();
					return false;					
				}	
			else
				{
					$("#front_result").html("<img src= '"+base_url+"modules/group_product/images/front.gif' />");	
					$.ajax({
					type: "POST",	
					url: base_url+"modules/product_word/ajax/save_front.php",	
					data: {left:left,right:right,product:product,guest:guest,customer:customer}		
					}).done(function(response) {
					var res=$.trim(response);	
					if(res=='1')										
					{								
					$("#front_result").html("<span style='color:green;font-size:13px;'>Saved Successfully.</span>");
					$("#buy_block").submit();
					}											
					else if(res=='2')		
					{										
					$("#front_result").html("<span style='color:green;font-size:13px;'>Not Saved.</span>");	return false;
					}										
					else if(res=='3')		
					{										
					$("#front_result").html("<span style='color:green;font-size:13px;'>Updated Successfully.</span>");
					$("#buy_block").submit();
					}		
					else if(res=='4')	
					{						
					$("#front_result").html("<span style='color:red;font-size:13px;'>Not Updated.</span>");	return false;
					}				
					
					});		
					//return false;
				}
  }
  else
  {
	  $("#buy_block").submit();
  }
 
});

	$('#back_product').removeAttr('onclick').click(function(e){	
	e.preventDefault();
	var previd=$("#previd").val();
	var cart_id=$("#cart_id").val();
	var shop_id=$("#shop_id").val();
	var base=$("#base").val();
	var preselect=$("#preselect").val();
	$.ajax({
					type: "GET",
					url: base+"modules/group_product/ajax/delete.php?id_product="+previd+"&cart_id="+cart_id+"&shop_id="+shop_id+""		
					}).done(function(response) {
					var res=$.trim(response);	
					if(res=='1')										
					{
						window.location.href="index.php?&id_product="+previd+"&controller=product&chain=on"+preselect;
					}					
					});	
		
	});

	$("body a").click(function(event) {
	event.preventDefault();
	var id = event.target.id;
	var checkclass= $(this).attr('class');
	var path=$(this).attr('href');	
	var token=$("#token").val();
	var ipa=$("#ipa").val();
	 if(id!="add_next" && id!="back_product" && id!="group_next" && checkclass!='thickbox shown' && checkclass!='thickbox' && checkclass!='color_pick' && checkclass!='idTabHrefShort selected' && checkclass!='selected')
        {
          var check=  confirm(stopbuy1 + parent_name + stopbuy2);
            if(check)
            {
				$.ajax({
					type: "GET",
					url: "index.php?controller=cart&delete=1&id_product="+main_parent+"&ipa="+ipa+"&token="+token			
					}).done(function() {					
					window.location.href=path;
					});
			}
			else
			{
			return false;			}
        }
		else
        {
			return true;	//window.location.href=path;
        }
    });
});