<?php
if (!defined('_PS_VERSION_'))
exit;

class group_product extends Module
	{
       public function __construct()
       {
			$this->name = 'group_product';
			$this->tab = 'Blocks';
			$this->version = 1.6;
			$this->author = 'Mani';
			$this->need_instance = 0;
			parent::__construct();
			$this->displayName = $this->l('Chain Configurator');
			$this->description = $this->l('Grouping the Products');    
		}
		public function install()
		{
			if (!parent::install()  || !$this->installDB() || !$this->getAnchor2() || !$this->registerHook('displayAdminProductsExtra') || !$this->registerHook('actionProductDelete')
			|| !$this->registerHook('displayRightColumnProduct') || !$this->registerHook('actionCartSave') || !$this->registerHook('displayShoppingCartFooter') || !$this->registerHook('displayHeader') || !$this->registerHook('ShoppingCart') || !$this->registerHook('displayBackOfficeTop') )
			return false;
		}
		
		public function installDB()
		{
			
		Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'shopconfigure` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
		`id_shop` INT(10) NOT NULL, `category_id` INT(10) NOT NULL) ENGINE = INNODB  DEFAULT CHARSET=utf8 ;');
			
		Db::getInstance()->execute('
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'group_product` (
				 `id` BIGINT( 50 ) NOT NULL AUTO_INCREMENT ,`product_name` VARCHAR( 500 ) NOT NULL ,
				`parent_product` VARCHAR( 100 ) NOT NULL ,`child_product` VARCHAR( 100 ) NOT NULL , `duplicated_id` INT( 100 ) NOT NULL,
				PRIMARY KEY (  `id` )) ENGINE = INNODB  DEFAULT CHARSET=utf8 ;');				
			Db::getInstance()->execute('
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cart_edit` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `id_cart` int(10) NOT NULL,
				  `id_product` int(10) NOT NULL,
				  `id_product_attribute` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
				  `id_guest` int(11) NOT NULL,
				  `id_customer` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE = INNODB  DEFAULT CHARSET=utf8 ;'); 
			Db::getInstance()->execute('
				ALTER TABLE `'._DB_PREFIX_.'cart_product` ADD `parent` int(10) NOT NULL'); 		
			return true;
		}
		
		public function uninstall()
		{
			if (!parent::uninstall() || !$this->uninstallDB() || !$this->override())
				return false;
			return true;
		}
		
		public function uninstallDB()
		{	
			Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'shopconfigure`;');
			Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'group_product`;');
			Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'cart_edit`;');			
			Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'cart_product` DROP `parent`;');			
			return true;
		}
		
		public function override()
		{
			$dirpath=_PS_ROOT_DIR_;
			$controllerfile=$dirpath."/override/controllers/front/OrderController.php";
			$controllerfile2=$dirpath."/override/controllers/front/OrderOpcController.php";
			$cache=$dirpath."/cache/class_index.php";
			if (file_exists($controllerfile)) 
				{
					unlink($controllerfile);
					unlink($cache);
				}
			if (file_exists($controllerfile2)) 
				{
					unlink($controllerfile);
					unlink($cache);
				}
			return true;
		}
		
		public function _displayForm()
		{
		global $cookie;
		$getshop=Db::getInstance()->ExecuteS("select distinct(id_shop) as id_shop,name from "._DB_PREFIX_."shop");
		$html="<fieldset><legend>".$this->l('Configure Hidden Categories')."</legend><table>";
		foreach($getshop as $shop)
		{
			$getselected=Db::getInstance()->ExecuteS("select category_id from "._DB_PREFIX_."shopconfigure where id_shop=".$shop['id_shop']);
			$html.="<tr><form id=".$shop['id_shop']." name=".$shop['id_shop']." action=".Tools::safeOutput($_SERVER['REQUEST_URI'])." method='post'><td>";
			$html.="<input type='text' readonly value=".$shop['name']." name='shopname'>";
			$html.="<input type='hidden' value=".$shop['id_shop']." name='shopid'>";
			$html.="</td><td>";
			$html.="<select name='selectcategory'>";
			$getchild=Db::getInstance()->ExecuteS("select id_category,name from "._DB_PREFIX_."category_lang WHERE id_category in(select id_category from "._DB_PREFIX_."category_shop where id_shop=".$shop['id_shop'].") and id_lang=".$this->context->cookie->id_lang." group by id_category");
			foreach($getchild as $child)
			{
				if($child['id_category']==$getselected[0]['category_id'])
				{
				$html.="<option value=".$child['id_category']." selected>".$child['name']."</option>";
				}
				else
				{
					$html.="<option value=".$child['id_category'].">".$child['name']."</option>";
				}
			}
			$html.="</select></td>";
			$html.="<td><input type='submit' name='save' value=".$this->l('Save')." class='button' /></form></td></tr>";
		}
		$html.="</table></fieldset>";
		return $html;	
	}
	
	public function getContent()
	{
		if (Tools::isSubmit('save'))
		{
			$count ="SELECT COUNT(*) as count FROM "._DB_PREFIX_."shopconfigure WHERE id_shop=".Tools::getValue('shopid');
				$rque = Db::getInstance()->getValue($count);
				if($rque['count'] >0)
				{
					$sql=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."shopconfigure SET 
					`category_id`=".Tools::getValue('selectcategory')."
					 WHERE `id_shop` =".Tools::getValue('shopid'));	
					if($sql)
					$output .= '<div class="conf confirm">'.$this->l('Settings Updated').'</div>';
				}
				else
				{
					$data = array('id_shop' => Tools::getValue('shopid'),
					'category_id' => Tools::getValue('selectcategory'));
					$res=Db::getInstance()->insert('shopconfigure', $data);
					if($res)
					$output .= '<div class="conf confirm">'.$this->l('Settings Saved').'</div>';
				}
		}
		
		 $output .= $this->_displayForm();  
		return $output;
	}
		
		public function hookactionProductDelete($params)
		{	
			$id_product = (int)Tools::getValue('id_product');
			return Db::getInstance()->executeS(
				"DELETE	FROM `"._DB_PREFIX_."group_product`
				WHERE `duplicated_id` = $id_product"
			);			
		}		
		public function hookdisplayBackOfficeTop($params)
		{	
			$controller=$_GET['controller'];
			$base="http://". $_SERVER['HTTP_HOST'] . __PS_BASE_URI__;
			if($controller=="AdminProducts")
			{
			$this->smarty->assign(array('ajaxbase'=>$base));
			
			$this->context->controller->addJS(($this->_path).'js/backoffice.js');
			}
			return $this->display(__FILE__, 'ajaxbase.tpl');	
		}						
		
		public function getAnchor2($id_product_attribute,$id_product)    		
		{        		
			$attributes = Product::getAttributesParams($id_product, $id_product_attribute);  		
			$anchor = '#';        			
			foreach ($attributes as &$a)       			
			{           				
				foreach ($a as &$b)       				
				$b = str_replace('-', '_', Tools::link_rewrite($b)); 				
				$anchor .= '/'.$a['group'].'-'.$a['name'];      			
			}       			
			return $anchor;  		
		}
		
		public function hookdisplayHeader($params)
		{	
			global $smarty;
			global $cart;
			global $cookie;	
			$id_cart=$this->context->cart->id;
			$id_shop = (int)Context::getContext()->shop->id;
			$id_pr=$_GET['id_product'];			
			$get=new Product($id_pr);	
			$catid=$get->id_category_default;	
			$gethiddencategory=Db::getInstance()->getRow("select * from `"._DB_PREFIX_."shopconfigure` where id_shop='".$id_shop."' and category_id='".$catid."'");		
			if(empty($id_cart) && is_array($gethiddencategory))	
			{				
				header("Location: index.php");	
			}
			if(!empty($id_cart))
			{
				$getch=Db::getInstance()->ExecuteS("SELECT * from `"._DB_PREFIX_."group_product`");
				$rootchain=array();
				foreach($getch as $getchild)
				{					
					$childs=explode(",",$getchild['child_product']);
					if(in_array($id_pr,$childs))
					{
						if(is_array($gethiddencategory))
						{
							$rootchain[]=$getchild['duplicated_id'];
						}
					}					
				}
				
				$cart = new Cart($id_cart);
				$cartProducts = $cart->getProducts();
				$cprd=array();
				if(!empty($rootchain))
				{
					foreach($cartProducts as $cp)
					{
						if(in_array($cp['id_product'],$rootchain))
						{
							$cprd[]=$cp['id_product'];
						}					
					}
					if(empty($cprd))
					{
						header("Location: index.php");
					}					
				}
				
											
								
				$dupids=Db::getInstance()->ExecuteS("SELECT id_product FROM `"._DB_PREFIX_."cart_product` WHERE parent=id_product and id_cart=$id_cart");
				$duplist=array();
				foreach($dupids as $dup)
				{					
					$duplist[]=$dup['id_product'];	
				}				
				$nor=Db::getInstance()->ExecuteS("SELECT id_product FROM `"._DB_PREFIX_."cart_product` WHERE parent='' and id_cart=$id_cart");
				$normal=array();		
				foreach($nor as $nid)	
				{					
					$normal[]=$nid['id_product'];
				}			
				$totalids=array_merge($duplist,$normal);
				$smarty->assign('duplicate',$totalids);
				$smarty->assign('normal',$normal);	
				$id_shop = (int)Context::getContext()->shop->id;				$getHiddentProducts=Db::getInstance()->ExecuteS("select id_product from `"._DB_PREFIX_."category_product` where id_category=(select category_id from `"._DB_PREFIX_."shopconfigure` where id_shop=".$id_shop.")");
				$ids=array();		
				foreach($getHiddentProducts as $listid)
				{
				   $ids[]=$listid['id_product'];
				}		
				$viewd=explode(',',$cookie->viewed);
				foreach($viewd as $allview)
				{					
					if(!in_array($allview,$ids))
					{				
					$finalviewed[]=$allview;	
					}			
				}			
				$cookie->viewed=implode(',',$finalviewed);		
			}		
		}		

		public function hookactionCartSave($params)
		{	
			global $cart;
			global $cookie;	
			$id_cart=$this->context->cart->id;
			$id=(int)Tools::getValue('id_product');
			$token=Tools::getValue('token');
			$cookie->token=$token;
			$del = Tools::getValue('delete');
			$check_chain = Tools::getValue('chain');
			$edit = Tools::getValue('edit');
			$id_customer=$this->context->cookie->id_customer;
			if($id_customer=="")
			{
				$id_customer=0;
			}
			$id_guest=$this->context->cookie->id_guest;
			if($del!=1 && $id!="") //del value
			{	
				$sql=Db::getInstance()->getRow("select * from "._DB_PREFIX_."word_front where id_product='$id' and guest='$id_guest' and customer='$id_customer'");
				$left=$sql['left_word'];
				$right=$sql['right_word'];					
				$update=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."cart_product SET `left_word`='$left',			`right_word`='$right' WHERE `id_product` ='$id' and id_cart='$id_cart'");
				$cart=Db::getInstance()->getRow("select * from "._DB_PREFIX_."cart_product where id_product='$id' and id_cart='$id_cart' order by date_add desc");
				$cartinfo=$cart['id_product_attribute'];
				$att_count=Db::getInstance()->getRow("select COUNT(*) as count from `"._DB_PREFIX_."cart_edit` where id_product='$id' and id_cart='$id_cart' and id_guest='$id_guest' and id_customer='$id_customer'");
				if($att_count['count']>0)
				{
					$sql=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."cart_edit SET `id_product_attribute`='$cartinfo' WHERE id_product='$id' and id_cart='$id_cart' and id_guest='$id_guest' and id_customer='$id_customer'");
				}
				else
				{
				$sql = Db::getInstance()->insert('cart_edit', array(
										'id_cart' => pSQL($id_cart),
									'id_product' => pSQL($id),
									'id_product_attribute' => pSQL($cartinfo),
									'id_guest' => pSQL($id_guest),
									'id_customer' => pSQL($id_customer),
															));
				}
				//$page_url="http://" . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . "index.php?";
				$lang=$this->context->cookie->id_lang;								
				$page_url="index.php?id_lang=".$lang."&";
				$prd_button=Db::getInstance()->getRow("select COUNT(*) as count from `"._DB_PREFIX_."group_product` WHERE `duplicated_id` = $id");
				if($prd_button['count']>0 && $check_chain=="") // check duplicate & chain off
					{	
						$cookie->main_id=$id;
						$cookie->id_parent_chain=$id;
						$getipa=Db::getInstance()->getRow("select id_product_attribute from "._DB_PREFIX_."cart_product where id_product='$id' and id_cart='$id_cart'");
						$cookie->ipa=$getipa['id_product_attribute'];
						$child=Db::getInstance()->getRow("select * from "._DB_PREFIX_."group_product where duplicated_id='$id'");
						$child_ids=$child['child_product'];
						$chain=$id.",".$child_ids;
						$list=explode(",",$chain);
						$cookie->inside=$chain;						
						if(in_array($id,$list))
							{	
								$pos=array_search($id,$list);							
								$nextid=$list[$pos+1];
								if(is_numeric($nextid))
									{									
										$cookie->chain="on";
										$cookie->loop=$chain;
										$sql=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."cart_product SET `parent`='$id' WHERE `id_cart` ='$id_cart' and `id_product`='$id'");
									}
							}
					} // check duplicate & chain off
					
					else if($prd_button['count']>0 && $check_chain="on") // check chain on
					{	
						$cookie->main_id=$id;
						$parent_id=$cookie->id_parent_chain;
						$child=Db::getInstance()->ExecuteS("select * from "._DB_PREFIX_."group_product where duplicated_id='$id'");
						$child_ids=$child[0]['child_product'];
						$replace=$id.",".$child_ids; // nest loop starts
						$nested=$cookie->loop;
						$nested= implode(',', array_keys(array_flip(explode(',', $nested))));
						$chain=str_replace($id,$replace,$nested);	
						$chain= implode(',', array_keys(array_flip(explode(',', $chain))));
						$list=explode(",",$chain);
						if(in_array($id,$list))
							{	
								$pos=array_search($id,$list);							
								$nextid=$list[$pos+1];
								if(is_numeric($nextid))
									{											
										$cookie->chain="on";
										$cookie->loop=$chain;	
										$sql=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."cart_product SET `parent`='$parent_id' WHERE `id_cart` ='$id_cart' and `id_product`='$id'");
									}
									else
									{
									$cookie->loop="";
									}
							}
					} // check chain on
					///
					else
					{
						$chain=$cookie->chain;
						$loop=$cookie->loop;
						$parent_id=$cookie->id_parent_chain;
						$array=explode(",",$loop);
						$sql=Db::getInstance()->ExecuteS("select count(*) as count from "._DB_PREFIX_."cart_product where id_cart='$id_cart' and id_product='$array[0]'");
						$count=$sql[0]['count'];
						// here we have to verify chain separated
						$id_shop = (int)Context::getContext()->shop->id;
						$get=new Product($id);	
						$catid=$get->id_category_default;	
						$gethiddencategory=Db::getInstance()->getRow("select * from `"._DB_PREFIX_."shopconfigure` where id_shop='".$id_shop."' and category_id='".$catid."'");	
						if(is_array($gethiddencategory))
						{
							$getch=Db::getInstance()->ExecuteS("SELECT * from `"._DB_PREFIX_."group_product`");
							$rootchain=array();
							foreach($getch as $getchild)
							{					
								$childs=explode(",",$getchild['child_product']);
								if(in_array($id,$childs))
								{
									if(is_array($gethiddencategory))
									{
										$rootchain[]=$getchild['duplicated_id'];
									}
								}					
							}					
							$cart = new Cart($id_cart);
							$cartProducts = $cart->getProducts();
							$cprd=array();
							if(!empty($rootchain))
							{
								foreach($cartProducts as $cp)
								{
									if(in_array($cp['id_product'],$rootchain))
									{
										$cprd[]=$cp['id_product'];
									}					
								}
								if(empty($cprd))
								{
									Db::getInstance()->execute("delete from "._DB_PREFIX_."cart_product where id_product='".$id."' and id_cart='".$id_cart."'");
								}					
							}
						}	 										
						
						// here we have to verify chain separated
						if($chain=="on" && $count>0) //check chain or not
						{								
							if(in_array($id,$array))
							{	
								$pos=array_search($id,$array);							
								$nextid=$array[$pos+1];
								if(is_numeric($nextid))
								{																					
									$sql=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."cart_product SET `parent`='$parent_id' WHERE `id_cart` ='$id_cart' and `id_product`='$id'");
									$att_id=Db::getInstance()->getRow("select id_product_attribute from "._DB_PREFIX_."cart_edit where id_product='$nextid' and id_cart='$id_cart' and id_customer='$id_customer' and id_guest='$id_guest'");	
									$id_attrib_product=$att_id['id_product_attribute'];
									$text=$this->getAnchor2($id_attrib_product,$nextid);				
									if($text!="")
									{
										$text="&edit=on".$text;
									}
									
									$cookie->nextchainid=$nextid;
									$cookie->nextchaintext=$text;									
									/* $update=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."customization SET `quantity`='1',`in_cart`='1' WHERE `id_cart` ='".$id_cart."' and `id_product`='".$id."'"); */
									//header("location:".$page_url."id_product=".$nextid."&controller=product&chain=on".$text."");
									//exit();										
								}
								else
								{	
									$sql=Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."cart_product SET `parent`='$parent_id' WHERE `id_cart` ='$id_cart' and `id_product`='$id'");
									$cookie->chain="";
									$cookie->loop="";
									$cookie->main_id="";
									$cookie->id_parent_chain="";
									$cookie->nextchainid="";
									$cookie->nextchaintext="";	
								}
								}
						} // chain checking	
						else
						{
							$cookie->chain="";
							$cookie->loop="";
							$cookie->main_id="";
							$cookie->id_parent_chain="";
							$cookie->nextchainid="";
							$cookie->nextchaintext="";	
						}
					}
					
			}//del value not to "1"
			else if($del==1)
			{	
				global $cookie;
				$id_cart= $this->context->cookie->id_cart;
				$status=Db::getInstance()->ExecuteS("select COUNT(*) as count from "._DB_PREFIX_."group_product where duplicated_id='$id'");
				if($status[0]['count']>0)
				{	
					$del=Db::getInstance()->ExecuteS("select * from `"._DB_PREFIX_."group_product` WHERE duplicated_id='$id'");
					$id_child=$del[0]['child_product'];
					$array=str_replace(",","','",$id_child);
					$nest_child=Db::getInstance()->ExecuteS("select * from "._DB_PREFIX_."group_product where duplicated_id in('$array')");
					$total=array();
					foreach($nest_child as $result)
						{	
							$total[$result['duplicated_id']]=$result['duplicated_id'].",".$result['child_product'];
						}
					foreach($total as $key=>$value)
						{
							$id_child=str_replace($key,$value,$id_child);
						}
					$del_id=str_replace(",","','",$id_child);
					$delete=Db::getInstance()->execute("delete from "._DB_PREFIX_."cart_product where id_product in ('$del_id') and id_cart=$id_cart");
					$delcusdata=Db::getInstance()->Execute("DELETE from "._DB_PREFIX_."customized_data where id_customization in (select id_customization from "._DB_PREFIX_."customization where id_product in ('$del_id') and id_cart='$id_cart')");
					$deletecustom=Db::getInstance()->execute("delete from "._DB_PREFIX_."customization where id_product in ('$del_id') and id_cart=$id_cart");
					$cookie->chain="";
					$cookie->loop="";
					$cookie->main_id="";
					$cookie->id_parent_chain="";
					if($edit=="on")
					{	
						$att_id=Db::getInstance()->getRow("select id_product_attribute from "._DB_PREFIX_."cart_edit where id_product='$id' and id_cart='$id_cart' and id_customer='$id_customer' and id_guest='$id_guest'");	
								
									$id_attrib_product=$att_id['id_product_attribute'];
									$text=$this->getAnchor2($id_attrib_product,$id);
									if($text!="")
									{
										$text="&edit=on".$text;
									}						
						//Tools::redirect("index.php?id_product=".$id."&controller=product".$text."");
						header("location:index.php?id_product=".$id."&controller=product".$text."");
									exit();	
					}
				}					
			}//del value equal to "1"
		} //hookactionCartSave
		
		//public function hookdisplayProductButtons($params)
		public function hookdisplayRightColumnProduct($params)
		{	
			global $smarty;
			global $cookie;
			$id_cart=$this->context->cart->id;
			$id_shop = (int)Context::getContext()->shop->id;
			$current=$_GET['id_product'];
			$base="http://". $_SERVER['HTTP_HOST'] . __PS_BASE_URI__;
			$checkmain=Db::getInstance()->ExecuteS("select duplicated_id from "._DB_PREFIX_."group_product");
			$dupids=array();
			foreach($checkmain as $result)
			{
				$dupids[]=$result['duplicated_id'];
			}
			if(in_array($current,$dupids))
			{
				$this->context->controller->addCSS(($this->_path).'css/chain.css');
			}
			$id_customer=$this->context->cookie->id_customer;
			if($id_customer=="")
			{
				$id_customer=0;
			}
			$id_guest=$this->context->cookie->id_guest;
			$parent_id=$cookie->id_parent_chain;
			$gsql=Db::getInstance()->getRow("select * from "._DB_PREFIX_."product_lang where id_product='$parent_id'");
			$name=$gsql['name'];			
			$lang=$this->context->cookie->id_lang;
			$page_url="index.php?id_lang=".$lang."&";
			$token=$cookie->token;			
			$add_url=$page_url."controller=cart&add=1&id_product=".$current."&token=".$token."&chain=on";
			//$chain=$_REQUEST['chain'];
			$chain=Tools::getValue('chain');
			$loop=$cookie->loop;
			$list=explode(",",$loop);
			if($chain=="on")
				{
					if(in_array($current,$list))
					{
					$this->context->controller->addCSS(($this->_path).'css/chain.css');
					$this->context->controller->addJS(($this->_path).'js/chain.js');
					$pos=array_search($current,$list);
					$minuspos=$pos-1;
					$prev=$list[$minuspos];
					
					$nextpos=$pos+1;

						if($nextpos==count($list))
						{
						$nextid='';
						}
						else
						{
						$nextid=$list[$nextpos];
						}					
				
					
					$att_id=Db::getInstance()->getRow("select id_product_attribute from "._DB_PREFIX_."cart_edit where id_product='$nextid' and id_cart='$id_cart' and id_customer='$id_customer' and id_guest='$id_guest'");	
									$id_attrib_product=$att_id['id_product_attribute'];
									$text=$this->getAnchor2($id_attrib_product,$nextid);				
									if($text!="")
									{
										$text="&edit=on".$text;
									}
					$prev_attribute=Db::getInstance()->getRow("select id_product_attribute from "._DB_PREFIX_."cart_edit where id_product='$prev' and id_cart='$id_cart' and id_customer='$id_customer' and id_guest='$id_guest'");	
									$prev_attrib_product=$prev_attribute['id_product_attribute'];
									$prevtext=$this->getAnchor2($prev_attrib_product,$prev);				
									if($prevtext!="")
									{
										$prevtext="&edit=on".$prevtext;
									}
					if($nextid!="")
						{
						$this->smarty->assign(array('prev'=>$prev,'purl'=>$page_url,'next'=>$nextid,'chain'=>$chain,'add_url'=>$add_url,'selected'=>$text,'current_parent'=>$parent_id,'name'=>$name,'static_token' => Tools::getToken(false),'ipa'=>$cookie->ipa,'prevselect'=>$prevtext,'previpa'=>$prev_attrib_product,'cart_id'=>$id_cart,'shop_id'=>$id_shop,'base'=>$base));
						}
						else
						{
							$nextid="";
							$this->smarty->assign(array('prev'=>$prev,'purl'=>$page_url,'next'=>$nextid,'chain'=>$chain,'add_url'=>$add_url,'selected'=>$text,'current_parent'=>$parent_id,'name'=>$name,'static_token' => Tools::getToken(false),'ipa'=>$cookie->ipa,'prevselect'=>$prevtext,'previpa'=>$prev_attrib_product,'cart_id'=>$id_cart,'shop_id'=>$id_shop,'base'=>$base));
						}
						return $this->display(__FILE__, 'front.tpl');		
					}						
				}
		}
		public function hookShoppingCart($params)
		{	
			global $cart;
			global $cookie;				
			$id_cart=$this->context->cart->id;
			$id_customer=$this->context->cookie->id_customer;
			if($id_customer=="")
			{
				$id_customer=0;
			}
			$id_guest=$this->context->cookie->id_guest;
			$loop=$cookie->loop;
			$array=explode(",",$loop);
			$main=$cookie->main_id;
			$nextchain=$cookie->nextchainid;
			$nextchaintext=$cookie->nextchaintext;
			if($main!="")
			{
				if(in_array($main,$array))
					{
						$pos=array_search($main,$array);							
						$nextid=$array[$pos+1];
						if(is_numeric($nextid))
							{
								$cookie->main_id="";
								$att_id=Db::getInstance()->getRow("select id_product_attribute from "._DB_PREFIX_."cart_edit where id_product='$nextid' and id_cart='$id_cart' and id_customer='$id_customer' and id_guest='$id_guest'");	
									$id_attrib_product=$att_id['id_product_attribute'];
									$text=$this->getAnchor2($id_attrib_product,$nextid);
									if($text!="")
									{
										$text="&edit=on".$text;
									}
								//header("location:".$page_url."id_product=".$nextid."&controller=product&chain=on".$text."");
								//Tools::redirect("index.php?id_product=".$nextid."&controller=product&chain=on".$text."");
								header("location:index.php?id_product=".$nextid."&controller=product&chain=on".$text."");
								exit();							
							}
					}			
			}
			else if(!empty($nextchain))
			{
				header("location: index.php?id_product=".$nextchain."&controller=product&chain=on".$nextchaintext."");
				$cookie->nextchainid="";
				$cookie->nextchaintext="";
				exit();
			}
			else
			{		
					$cookie->chain="";
					$cookie->loop="";
					$cookie->main_id="";
					$cookie->id_parent_chain="";
					$cookie->nextchainid="";
					$cookie->nextchaintext="";
			}
		}
		
		public static function getCategories($id_lang = false, $active = true, $order = true, $sql_filter = '', $sql_sort = '', $sql_limit = '')
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'category` c
			'.Shop::addSqlAssociation('category', 'c').'
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').'
			WHERE 1 '.$sql_filter.' '.($id_lang ? 'AND `id_lang` = '.(int)$id_lang : '').'
			'.($active ? 'AND `active` = 1' : '').'
			'.(!$id_lang ? 'GROUP BY c.id_category' : '').'
			'.($sql_sort != '' ? $sql_sort : 'ORDER BY c.`level_depth` ASC, category_shop.`position` ASC').'
			'.($sql_limit != '' ? $sql_limit : '')
		);
			return $result;
	}
		
		public function hookDisplayAdminProductsExtra($params) 
		{				
			global $cookie;	
			$id_lang=$this->context->cookie->id_lang;
			$page_url=Tools::getHttpHost().__PS_BASE_URI__."index.php?id_lang=".$lang."&";
			$id_shop = (int)Context::getContext()->shop->id;
			$group = (int)Context::getContext()->shop->id_shop_group;
			$product =(int)Tools::getValue('id_product');
			$status=Db::getInstance()->ExecuteS("select * from "._DB_PREFIX_."product where id_product='$product'");
			$stat_check=$status[0]['active'];
			if($stat_check==0)
			{
			$reason='You Cant Choose this product as your Main Product.';
			$this->smarty->assign(array('reason'=>$reason));
			}
			else
			{					
				$ch_prd ="SELECT COUNT(*) as count FROM "._DB_PREFIX_."group_product WHERE duplicated_id='$product'";
				$rque = Db::getInstance()->getValue($ch_prd);
				if($rque['count'] >0)
				{	
					$gsql=Db::getInstance()->ExecuteS("select * from "._DB_PREFIX_."product_lang where id_product='$product' and id_lang='$id_lang' and id_shop='$id_shop'");
					$main_prd=$gsql[0]['name'];
					$catids= $this->getCategories((int)Context::getContext()->language->id,false,true);
					$categoryids=array();
					foreach($catids as $pd)
					{
						$categoryids[]=$pd['id_category'];
					}
					$imp=implode("','",$categoryids);
					$products=Db::getInstance()->ExecuteS("select id_product,name from "._DB_PREFIX_."product_lang where id_product!='$product' and id_product IN ( SELECT id_product FROM "._DB_PREFIX_."category_product WHERE id_category IN ('$imp')) and id_lang='$id_lang' and id_shop='$id_shop' group by id_product ");
					$chain_prd=Db::getInstance()->ExecuteS("select * from "._DB_PREFIX_."group_product where duplicated_id='$product'");
					$chain_id=$chain_prd[0]['child_product'];
					$array=str_replace(",","','",$chain_id);
					$chain_list=Db::getInstance()->ExecuteS("select id_product,name from "._DB_PREFIX_."product_lang where id_product in ('$array') and id_lang='$id_lang' and id_shop='$id_shop' group by id_product order by find_in_set(id_product, '$chain_id') ");
					$token=$_GET['token'];
					$pageURL = 'http';
					if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
					 $pageURL .= "://";
					 if ($_SERVER["SERVER_PORT"] != "80") {
					 $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
					 } else {
					  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
					 }
					$curent=explode("index.php?",$pageURL);
					$current_url=$curent[0];
					$chain_name="not available";
					$this->smarty->assign(array(
										'main_product_id'=>$product,
										'main_product'	=>$main_prd,
										'product_list' =>$products,
										'chain_name' => $chain_name,
										'chain_list' => $chain_list,
										'token' => $token,
										'baseurl' => "http://" . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ ,
										'admin_url' => $current_url
										));				
				}
				else				
				{
					$gsql=Db::getInstance()->ExecuteS("select * from "._DB_PREFIX_."product_lang where id_product='$product' and id_lang='$id_lang' and id_shop='$id_shop' ");
					$main_prd=$gsql[0]['name'];
					$catids= $this->getCategories((int)Context::getContext()->language->id,false,true);
					$categoryids=array();
					foreach($catids as $pd)
					{
						$categoryids[]=$pd['id_category'];
					}
					$imp=implode("','",$categoryids);
					$products=Db::getInstance()->ExecuteS("select id_product,name from "._DB_PREFIX_."product_lang where id_product!='$product' and id_product IN ( SELECT id_product FROM "._DB_PREFIX_."category_product WHERE id_category IN ('$imp')) and id_lang='$id_lang' and id_shop='$id_shop' group by id_product ");
					$chain_name="";
					$token=$_GET['token'];
					$pageURL = 'http';
					if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
					 $pageURL .= "://";
					 if ($_SERVER["SERVER_PORT"] != "80") {
					 $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
					 } else {
					  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
					 }
					$curent=explode("index.php?",$pageURL);
					$current_url=$curent[0];
					$this->smarty->assign(array(
										'main_product_id'=>$product,
										'main_product'	=>$main_prd,
										'chain_name' => $chain_name,
										'product_list' =>$products,
										'token' => $token,
										'baseurl' => "http://" . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ ,
										'admin_url' => $current_url
										));				
				}
			}
			return $this->display(__FILE__, 'group_product.tpl');
		}
   }

 ?>
