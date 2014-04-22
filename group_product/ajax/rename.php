<?php
include('../../../config/config.inc.php');
session_start();
global $smarty;
$main_id=$_GET['id_product'];
$gsql=Db::getInstance()->ExecuteS("SELECT max(id_product) as id_product from "._DB_PREFIX_."product where id_product!='$main_id'");
$dup_id=$gsql[0]['id_product'];
if($dup_id)
{
$sql=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."group_product SET `duplicated_id` ='$dup_id' WHERE `parent_product` ='$main_id' order by id desc limit 1");
$name=Db::getInstance()->getRow("select product_name from "._DB_PREFIX_."group_product where duplicated_id='$dup_id' and product_name!=''");
if(!empty($name['product_name']))
{
	$update_name=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."product_lang SET `name` ='".$name['product_name']."' WHERE `id_product` ='$dup_id' and `name`!=''");
}
$up_status=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."product SET `active` ='1' WHERE `id_product` ='$dup_id'");
$shop_status=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."product_shop SET `active` ='1' WHERE `id_product` ='$dup_id'");
		if($sql && $up_status && $shop_status)		{			echo "1";		}}?>
		
