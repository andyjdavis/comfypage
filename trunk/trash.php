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
 * Area that holds deleted pages
 *
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();

//types of action
define('ACTION_PARAM', 'action');
define('RESTORE_VALUE', 'restore');
define('DELETE_VALUE', 'delete');
define('EMPTY_VALUE', 'empty');
//types of items
define('TYPE_PARAM', 'type');
define('VALUE_PAGE', 'page');
define('VALUE_PRODUCT', 'product');
//the id of the item
define('ID_PARAM', 'id');

$error = null;
$success = null;

$ps = Load::page_store();
$prs = Load::product_store();

$action = Globals::get_param(ACTION_PARAM, $_GET);
if($action != null)
{
	if($action == EMPTY_VALUE)
	{
		$page_ids = $ps->empty_trash();
		//require_once('common/members.php');
		//$m = new Members;
		$m = Load::member_settings();
		//remove members only listing for this page
		foreach($page_ids as $pi)
		{
			$m->delete_members_only_page($pi);
		}
		//delete_all_products_from_trash();
		$prs->empty_trash();
		//track_user('Emptied trash');
	}

	if($action == RESTORE_VALUE)
	{
		$item_type = Globals::get_param(TYPE_PARAM, $_GET);
		$item_id = Globals::get_param(ID_PARAM, $_GET);
		if($item_type == VALUE_PAGE)
		{
			//restore_page_from_trash($item_id);
			$ps->restore_from_trash($item_id);
			//track_user('Page restored from trash');
		}
		else if($item_type == VALUE_PRODUCT)
		{
            //restore_product_from_trash($item_id);
            $prs->restore_from_trash($item_id);
            //track_user('Product restored from trash');
		}

		$success = 'Item restored';
	}
	else if($action == DELETE_VALUE)
	{
		//$item_type = $_GET[TYPE_PARAM];
		$item_type = Globals::get_param(TYPE_PARAM, $_GET);
		//$item_id = $_GET[ID_PARAM];
		$item_id = Globals::get_param(ID_PARAM, $_GET);

		if($item_type == VALUE_PAGE)
		{
			//delete_page_from_trash($item_id);
			$ps->delete_from_trash($item_id);
			//require_once('common/members.php');
			//$m = new Members;
			$m = Load::member_settings();
			$m->delete_members_only_page($item_id);
			//track_user('Page deleted from trash');
		}
		else if($item_type == VALUE_PRODUCT)
		{
            //delete_product_from_trash($item_id);
            $prs->delete_from_trash($item_id);
            //track_user('Product restored from tash');
		}

		$success = 'Item deleted';
	}
}
$pages = $ps->get_used_ids_in_trash();
$products = $prs->get_used_ids_in_trash();
function get_deleted_item_row($display_text, $item_id, $item_type)
{
	$action = ACTION_PARAM;
	$restore = RESTORE_VALUE;
	$delete = DELETE_VALUE;
	$type = TYPE_PARAM;
	$id_param = ID_PARAM;

	if(empty($display_text))
	{
		$display_text = '<i>(No title)</i>';
	}

	return <<<END
	<tr>
	<td>$display_text</td>
	<td align=center width=100><a href=trash.php?$action=$restore&$type=$item_type&$id_param=$item_id>Restore</a></td>
	<td align=center width=100><a href=trash.php?$action=$delete&$type=$item_type&$id_param=$item_id onclick="return confirmDeletion();">Delete</a></td>
	</tr>
END;
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Trash</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />

		<script LANGUAGE="JavaScript" SRC="common/contentServer/contentServer.js" ></script>

		<script language=Javascript>
		//function confirmDeletion()
		//{
		//	return confirm('Permanently delete?');
		//}
		</script>
	</head>

	<body>
		<?php
			//<a href=trash.php?action=empty onclick="return confirmDeletion();">Empty trash</a>&nbsp;&nbsp;&nbsp;
			$menu = new Menu;
			$menu->add_item('Empty trash', 'trash.php?action=empty');
			echo($menu->get_menu());
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
		?>
	    <table class=admin_table>
	    <tr>
	        <th colspan=3>Pages</th>
	    </tr>
	    <?php

	    if(count($pages) == 0)
	    {
	        echo('<tr><td align="center" colspan="3"><i>No pages in trash</i></td></tr>');
	    }
	    else
	    {
		    foreach($pages as $page_id)
		    {
		        //$page = Load::page($page_id);
		        //$page->set_save_location("site/store/pages/trash/$page_id.php");
		        $page = new Page($page_id, "site/store/pages/trash/$page_id.php");
		    	echo(get_deleted_item_row($page->get(CONTENT_TITLE), $page->id, VALUE_PAGE));
		    }
	    }

	    ?>
	    </table>
	    <table class=admin_table>
	    <tr>
	        <th colspan=3>Products</th>
	    </tr>
	    <?php
		    if(count($products) == 0)
		    {
		        echo('<tr><td align=center colspan=3><i>No products in trash</i></td></tr>');
		    }
		    else
		    {
			    foreach($products as $product_id)
			    {
			        //$product = Load::product($product_id);
			        //$product->set_save_location("site/store/products/trash/$product_id.php");
			        $product = new Product($product_id, "site/store/products/trash/$product_id.php");
			    	echo(get_deleted_item_row($product->get(PRODUCT_TITLE), $product->id, VALUE_PRODUCT));
			    }
		    }
	    ?>
		</table>
	    <?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>