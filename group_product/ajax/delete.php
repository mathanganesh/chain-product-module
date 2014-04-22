<?php
include('../../../config/config.inc.php');
session_start();
global $smarty;
$product=$_GET['id_product'];
$cart_id=$_GET['cart_id'];
$shop_id=$_GET['shop_id'];
$gsql=Db::getInstance()->Execute("DELETE from "._DB_PREFIX_."cart_product where id_product='$product' and id_cart='$cart_id' and id_shop='$shop_id'");
$delcusdata=Db::getInstance()->Execute("DELETE from "._DB_PREFIX_."customized_data where id_customization=(select id_customization from "._DB_PREFIX_."customization where id_product='$product' and id_cart='$cart_id'");
$delcus=Db::getInstance()->Execute("DELETE from "._DB_PREFIX_."customization where id_product='$product' and id_cart='$cart_id'");
if($gsql)
{
echo "1";
}
?>

		

