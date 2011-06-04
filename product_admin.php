<?php
// This file is part of ComfyPage - http://comfypage.com
//
// ComfyPage is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ComfyPage is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ComfyPage.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 *

 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');

Globals::dont_cache();
Login::logged_in();
//site_enabled_check();
//track_user();

define('DELETE_PRODUCT_URL_PARAM', 'delete');
define('CREATE_PRODUCT_URL_PARAM', 'create');
define('COPY_PRODUCT_URL_PARAM', 'copy');

$error = null;
$success = null;

//require_once('common/contentServer/payment_general_settings.php');
$pgs = Load::payment_general_settings();
$currency_in_use = $pgs->get(PAYMENT_CURRENCY);

$prs = Load::product_store();

$delete_product_id = Globals::get_param(DELETE_PRODUCT_URL_PARAM, $_GET);
if($delete_product_id != null)
{
	$error = $prs->delete($delete_product_id);
	if(empty($error))
	{
		$success = 'Deleted';
	}
}
$product_title = Globals::get_param(CREATE_PRODUCT_URL_PARAM, $_GET);
if($product_title != null)
{
	Load::award_settings()->bestow_award(ADD_PRODUCT_AWARD);
	$new_product = $prs->create();
	$new_product->set(PRODUCT_TITLE, $product_title);
	Globals::redirect('product_edit.php?'. PRODUCT_ID_URL_PARAM .'=' . $new_product->id);
}
$copy_from_id = Globals::get_param(COPY_PRODUCT_URL_PARAM, $_GET);
if($copy_from_id != null)
{
    $new_product = $prs->copy(Load::product($copy_from_id));
    $title = $new_product->get(PRODUCT_TITLE).' (copy)';
    $new_product->set(PRODUCT_TITLE, $title);
	Globals::redirect('product_edit.php?'.PRODUCT_ID_URL_PARAM."={$new_product->id}");
}

$products = $prs->load_all_products();
$product_count = sizeof($products);

function GetProductHtmlRow($product_id, $seller_product_id, $title, $price, $currency)
{
	//require_once('common/validation.php');
	$rand = rand();
	if(empty($title))
	{
		$title = '<i><span id=nt'.$rand.' class=translate_me>(No title)</span></i>';
	}
	if(strlen($title)>40)
	{
		$title = substr($title, 0, 40).'...';
	}
	if(empty($seller_product_id))
	{
		$seller_product_id = '<i><span id=nc'.$rand.' class=translate_me>(No code)</span></i>';
	}
	//$price = format_price($price, $currency);
	$price = Format::price($price, $currency);
	$view = '<a href=product.php?' . PRODUCT_ID_URL_PARAM . '=' . $product_id . '><span id=v'.$rand.' class=translate_me>View</span></a>';
	$edit = '<a href=product_edit.php?' . PRODUCT_ID_URL_PARAM . '=' . $product_id . '><span id=e'.$rand.' class=translate_me>Edit</span></a>';
	$copy = '<a href=product_admin.php?' . COPY_PRODUCT_URL_PARAM . '=' . $product_id . '><span id=c'.$rand.' class=translate_me>Copy</span></a>';
	$delete = '<a href=product_admin.php?' . DELETE_PRODUCT_URL_PARAM . '=' . $product_id. '><span id=d'.$rand.' class=translate_me>Delete</span></a>';
	return <<<END
	<tr>
	<td nowrap style="font-weight:bold;">$title</td>
	<td nowrap>&nbsp;&nbsp;$seller_product_id&nbsp;&nbsp;</td>
	<td align=right nowrap>&nbsp;$price&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td width=80%>
		$view&nbsp;&nbsp;&nbsp;
		$edit&nbsp;&nbsp;&nbsp;
		$copy&nbsp;&nbsp;&nbsp;
		$delete
	</td>
	</tr>
END;
}

function GetOptionRow($link, $name, $description, $help)
{
	$rand = rand();
	if($help)
	{
		$help_text = Message::get_help_link($help);
	}
	return <<<END
	<tr>
	<td nowrap><a style='font-weight:bold;' href=$link><span id=n$rand class=translate_me>$name</span></a></td>
	<td width=90% nowrap>&nbsp;&nbsp;&nbsp;<span id=d$rand class=translate_me>$description</span></td>
	<td style="text-align:center;">
	</td>
	</tr>
END;
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Shop Manager</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
		<?php
			echo(get_menu('Shop manager'));
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
		?>
		    <!-- MENU -->
		    <table class=admin_table align="center">
		    	<tr>
		    		<th colspan=3><span id=theshoM class=translate_me>Shop Menu</span></th>
		    	</tr>
				<tr>
				    <?php
						echo(GetOptionRow('payment_admin.php', 'Payment options', 'Configure how you receive payments', null));
					?>
				</tr>
			</table>
	    	<!-- YOUR PRODUCTS -->
        	<table class=admin_table align="center">
		    <tr>
				    <th colspan=7 class="admin_section"><span id=yp class=translate_me>Your Products</span></th>
			    </tr>
		    <?php
			if($product_count == 0)
			{
					    echo('<td>&nbsp;&nbsp;&nbsp;<i>(No products)</i></td>');
				    }
			    //for($i=0; $i<$product_count; $i++)
			    foreach($products as $prd)
			    {
				    echo(GetProductHtmlRow($prd->id, $prd->get(PRODUCT_SELLER_PRODUCT_ID), $prd->get(PRODUCT_TITLE), $prd->get(PRODUCT_PRICE), $currency_in_use));
				    }
		    ?>
		    <tr>
			<td colspan="4">
			    <form name="newProduct" method="get">
				<table width="100%">
				    <tr>
					<td width="90%" nowrap><P style='padding-top:0.5em;'><input type=text value="New product title" name="create" size="50"> <a href="Javascript:document.newProduct.submit();"><span id="apba" class="translate_me">Add Product</span></a></p></td>
					<td align="right"><P style="padding-top:0.5em;"><a href="trash.php"><img border="0" src="common/images/bin.png" alt="Trash"></a></p></td>
				    </tr>
				</table>
			    </form>
			</tr>
		</table>
	  <?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>