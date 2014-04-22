<?php
include('../../../config/config.inc.php');
session_start();
global $smarty;
$id=$_POST['id'];
$check=Db::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."group_product");
			$total=array();
			foreach($check as $res)
			{
			$total[]=$res['duplicated_id'].",".$res['child_product'].",";
			}
			$sample=implode("",$total);
			$array=explode(",",$sample);
			if(in_array($id,$array))
			{
				echo "1";
			}
			else
			{
				echo "2";
			}
	?>
		
