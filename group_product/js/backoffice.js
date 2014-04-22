$(document).ready(function(){
$('.delete').removeAttr('onclick').click(function(e){
e.preventDefault();
var url = $(this).attr("href");
var server = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname;
var c= url.split('index.php');
var d=c[1];
var final=server+d;
var a= url.split('id_product=');
var next= a[1];
var id=next.split('&');    
var base_url = $("#ajaxbase").val();
$.ajax({
		type: "POST",	
		url: base_url+"modules/group_product/ajax/check_chain.php",	
		data: {id:id[0]}		
		}).done(function(response) {
		var res=$.trim(response);	
			if(res=='1')										
			{
				if(confirm('This Product is configured in chain.Deleting this product will affect your chain.Are you sure to do this?'))
				{
					location.href=url;
				}
				else
				{
					return false;
				}
			}
			else if(res=='2')
			{
				if(confirm('Are you sure to delete this product?'))
				{
					location.href=url;
				}
				else
				{
					return false;
				}
			}
		});
		
});
});

