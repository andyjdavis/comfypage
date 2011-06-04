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

require_once('common/contentServer/product_page.php');
require_once('common/utils/Globals.php');
require_once('common/users_items/Product.php');

//site_enabled_check();
//track_user();

$counter = Load::counter_settings();
$counter->page_viewed();

$product_id = Globals::get_param(PRODUCT_ID_URL_PARAM, $_GET);
if($product_id != null)
{
	$prs = Load::product_store();
	if($prs->store_item_exists($product_id) == false)
	{
        require_once('common/contentServer/content_page.php');
        $page = get_content_page(ERROR_CONTENT);
	}
	else
	{
		$page = get_product_page($product_id);
	}
	echo($page);
	exit();
}
else
{
	Globals::redirect('index.php');
}

?>