<div id="chain_div">
<p id="chain_product">
<input type="hidden" name="chain" value="on" />
<input type="hidden" name="main_parent" id="main_parent" value="{$current_parent}" />
<input type="hidden" name="parent_name" id="parent_name" value="{$name}" />
<input type="hidden" name="cart_id" id="cart_id" value="{$cart_id}" />
<input type="hidden" name="shop_id" id="shop_id" value="{$shop_id}" />
<input type="hidden" name="token" id="token" value="{$static_token}" />
<input type="hidden" name="ipa" id="ipa" value="{$ipa}" />
<input type="hidden" name="previpa" id="previpa" value="{$previpa}" />
<input type="hidden" name="previd" id="previd" value="{$prev}" />
<input type="hidden" name="preselect" id="preselect" value="{$prevselect}" />
<input type="hidden" name="base" id="base" value="{$base}" />
<input type="hidden" name="alertmessage" id="alertmessage" value="{l s='Please Enter Texts For Products.' mod='group_product'}" />
<input type="hidden" name="getcustext" id="getcustext" value="{l s='Please Enter Customization Texts' mod='group_product'}" />
<input type="hidden" name="stopbuy1" id="stopbuy1" value="{l s='you are going to stop the buying of ' mod='group_product'}" />
<input type="hidden" name="stopbuy2" id="stopbuy2" value="{l s=' and remove all choices made. Do you really want to do this? ' mod='group_product'}" />
{if $next neq ""}
	{if $prev neq ""}
	<span class="chain_image"></span>
		
		<input type="button" class="redirect_cart button" value="{l s='Yes,Please Add' mod='group_product'}" name="Submit" id="add_next">		
	<a href="{$purl}id_product={$next}&controller=product&chain={$chain}{$selected}">
	<input type="button" class="button" value="{l s='No thanks,Continue' mod='group_product'}" id="group_next" onClick="location.href ='{$purl}id_product={$next}&controller=product&chain={$chain}{$selected}'"/>
	</a>
	<a href="{$purl}id_product={$prev}&controller=product&chain={$chain}">
	<input type="button" class="button" value="{l s='Back' mod='group_product'}" id="back_product" onClick="location.href ='{$purl}id_product={$prev}&controller=product&chain={$chain}{$prevselect}'"/>
	</a>
	{else}
	<a href="{$purl}id_product={$next}&controller=product&chain={$chain}{$selected}">
	<input type="button" class="button" value="{l s='Add to Cart' mod='group_product'}" id="add_next" />
	</a>
	{/if}
{else}

		<input type="button" class="redirect_cart button" value="{l s='Yes,Please Add' mod='group_product'}" name="Submit" name="Submit" id="add_next" >		
<a href="{$purl}controller=order&ipa">
<input type="button" class="button" value="{l s='No thanks,Continue' mod='group_product'}" id="group_next" onclick="location.href ='{$purl}controller=order&ipa'"/>
</a>
<a href="{$purl}id_product={$prev}&controller=product&chain={$chain}{$prevselect}">
<input type="button" class="button" value="{l s='Back' mod='group_product'}" id="back_product" onclick="location.href ='{$purl}id_product={$prev}&controller=product&chain={$chain}'"/>
</a>
{/if}
</p>
</div>