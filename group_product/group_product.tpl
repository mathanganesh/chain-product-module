<html>
<head>

	<script type="text/javascript">
	{literal}
	$(document).ready(function(){
					
						serialize();
					var base_url = "{/literal}{$baseurl}{literal}";
					var admin_url = "{/literal}{$admin_url}{literal}";
					$("#addItem").click(add);
					$("#availableItems").dblclick(add);
					$("#removeItem").click(remove);
					$("#items").dblclick(remove);										
					 $("#move-up").click(moveUp); 
					$("#move-down").click(moveDown);					
					$("#reset-list").click(resetList);
					function add()
					{
						$("#availableItems option:selected").each(function(i){
							var val = $(this).val();
							var space ="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
							var text = $(this).text();
							text = text.replace(/(^\s*)|(\s*$)/gi,"");
							$("#items").append("<option value=\""+val+"\">"+space+text+"</option>");
						});
						serialize();
						return false;
					}
					function remove()
					{
						$("#items option:selected").each(function(i){
							$(this).remove();
						});
						serialize();
						return false;
					}

					
					function moveUp() {
						  $("#items option:selected").each(function() {
							var listItem = $(this);
							var listItemPosition = $("#items option").index(listItem) + 1;
						 
							if (listItemPosition == 1) return false;
						 
							listItem.insertBefore(listItem.prev());
							
						  });
						  serialize();
						}						
					function moveDown() {
					  var itemsCount = $("#items option").length;
					 
					  $($("#items option:selected").get().reverse()).each(function() {
						var listItem = $(this);
						var listItemPosition = $("#items option").index(listItem) + 1;
					 
						if (listItemPosition == itemsCount) return false;
					 
						listItem.insertAfter(listItem.next());
					  });
					  serialize();
					}
					
					var originalItems = $("#items option");
					 
					function resetList() {
					  $("#items").html(originalItems);
					  $("#result").html("");
					}
						
					function serialize()
					{
						var options = "";
						$("#items option").each(function(i){
							options += $(this).val()+",";
						});
						$("#itemsInput").val(options.substr(0, options.length - 1));
					}
					
					$("#save").live("click", function (e, index) {
								var str= $("#new_name").val();
								var save_name= $.trim(str);
								var list=$("#itemsInput").val();
								
								if(list=="")
								{
								alert("Please Select Minimum one chain product.");
								return false;
								}
								else
								{
								$("#result").html("<img src= '"+base_url+"modules/group_product/images/sample.gif' />");
								$.ajax
								({
								type: "POST",
								url: base_url+"modules/group_product/ajax/save.php",
								data: $("#myform").serialize()
								}).done(function(response) {
									var res=$.trim(response);
									var main_id = "{/literal}{$main_product_id}{literal}";
									var token = "{/literal}{$token}{literal}";
								if(res=='1')
								{										
									$.ajax
									({
									type: "GET",
									url: admin_url+"index.php?controller=AdminProducts&id_product="+main_id+"&duplicateproduct&token="+token						
									}).done(function(){
														$.ajax
														({
														type: "GET",
														url: base_url+"modules/group_product/ajax/rename.php?id_product="+main_id						
														}).done(function(response){
														var re=$.trim(response);
														if(re=='1')
															{															if(save_name==""){
															$("#result").html("<span style='color:green;font-size:15px;'>Product Created Successfully.</span>");															}else{															$("#result").html("<span style='color:green;font-size:15px;'>Product Created and Renamed Successfully.</span>");															}
															}
															else
															{
															$("#result").html("<span style='color:red;font-size:15px;'>Product Created,But not Renamed.</span>");
															}
														});				
									});
								}
								else if(res=='2')
								{
								$("#result").html("<span style='color:red;font-size:15px;'>Product Not Saved Properly.</span>");
								}
								else if(res=='3')
								{
								$("#result").html("<span style='color:green;font-size:15px;'>Product Updated Successfully.</span>");
								}
								else if(res=='4')
								{
								$("#result").html("<span style='color:red;font-size:15px;'>Product Not Updated Properly.</span>");
								}
								});
								return false;
								}
					});
					
				});//jquery end
	{/literal}
	</script>
<form action="" method="post" id="myform">
<input type="hidden" name="prdct" value='{$main_product}' id="prdct" />
<input type="hidden" name="main_id" value='{$main_product_id}' id="main_id" />

				<div class="margin-form">
					<input type="hidden" name="items" id="itemsInput" value="" size="70" />
				</div>
				{if $reason eq ''}
				<div>
				<h2>Your Main Product is : {$main_product} </h2>
				{if $chain_name eq ""}
				<h3>Save As : <input type="text" size="30" id='new_name' name="new_name" value=''/> </h3>
				{else}
				<input type="hidden" size="30" id='new_name' name="new_name" value='{$main_product}'/>
				{/if}
				</div>
				<table style="margin-left: 130px;">
					<tbody>
						<tr>
							<td style="padding-left: 20px;">
								<div style="margin-left:10px;font-weight:bold;font-size:15px;">Existing Products</div>
								<select multiple="multiple" id="availableItems" style="width: 300px; height: 160px;">
									{foreach  item=contact from=$product_list}
									<option value="{$contact.id_product}" style="font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$contact.name}</option>
									{/foreach}
								</select><br />
								<br />
								<a href="#" id="addItem" style="border: 1px solid rgb(170, 170, 170); margin: 2px; padding: 2px; text-align: center; display: block; text-decoration: none; background-color: rgb(250, 250, 250); color: rgb(18, 52, 86);">Add &gt;&gt;</a>
							</td>
							<td>
								<div style="margin-left:10px;font-weight:bold;font-size:15px;">Chain Products</div>
								<select multiple="multiple" id="items" style="width: 300px; height: 160px;">
								{if $chain_list neq ''}
								{foreach  item=chain from=$chain_list}
								<option value="{$chain.id_product}" style="font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$chain.name}</option>
								{/foreach}
								{/if}
								</select><br/>
								<br/>
								<a href="#" id="removeItem" style="border: 1px solid rgb(170, 170, 170); margin: 2px; padding: 2px; text-align: center; display: block; text-decoration: none; background-color: rgb(250, 250, 250); color: rgb(18, 52, 86);">&lt;&lt; Remove</a>
							</td>
							<td>
							<input id="move-up" type="button" value="Move Up" class="button" style="margin-left:10px"/>
							<br/>
							<br/>
							<input id="move-down" type="button" value="Move Down" class="button" style="margin-left:10px"/> 
							</td>
						</tr>
						<tr><td height="20">&nbsp;</td></tr>
						<tr>
						<td colspan="2" align="center">
						<div align="center" style="float:left;margin-left:270px">
						<input id="reset-list" type="reset" value="Reset" class="button"/>
						<input type="submit" name="save" id="save" value="Save" class="button" /></div>
						<div style="float:left;margin-left:10px;" id="result"></div>
						</td>
						</tr>
					</tbody>
				</table>
				{elseif $reason neq ''}
				<h2>{$reason} </h2>
				{/if}

			</form>
			</head>
</html>