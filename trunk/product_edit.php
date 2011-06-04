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
//require_once('common/contentServer/product_globals.php');
require_once('common/utils/Globals.php');
require_once('common/contentServer/product_page.php');
Globals::dont_cache();
Login::logged_in(true);
//site_enabled_check();
$product_id = Globals::get_param(PRODUCT_ID_URL_PARAM, $_POST);
if($product_id != null)
{
	$product = Load::product($product_id);
	$product->process_post($_POST);
	$errors = $product->get_error_message();
	if(empty($errors))
	{
		//include_once('common/lib/image_resize/image_resize.php');
		$product_image = $product->get(PRODUCT_IMAGE);
		//resize_to_a_width($product_image, 100, true, true, $product->get_thumb_path());
		copy($product_image, $product->get_thumb_path());
		if(empty($product_image)) //if have not specified an image
		{
			$product->delete_thumb();
		}
		Globals::redirect('product.php?'.PRODUCT_ID_URL_PARAM."=$product_id");
	}
	else
	{
		$page = get_product_edit_page($product->id, $product, Message::get_error_display($errors));
		echo($page);
	}
	exit();
}
$product_id = Globals::get_param(PRODUCT_ID_URL_PARAM, $_GET);
if($product_id != null)
{
	$prs = Load::product_store();
	if($prs->store_item_exists($product_id) == false)
	{
	    require_once('common/contentServer/content_page.php');
		$page = get_content_page(ERROR_CONTENT);
		//track_user('Requested product that does not exist');
	}
	else
	{
		//want to display content in an editable form
		$page = get_product_edit_page($product_id);
		//track_user('Editing product');
	}
	echo($page);
	exit();
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>No product error</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		<?php echo(Message::get_error_display('No product was identified')); ?>
	</body>
</html>