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
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/contentServer/content_page.php');

Globals::dont_cache();
Login::logged_in(true);
//site_enabled_check();
Globals::self_install_checks();

$error = null;

//if saving then there will be post data
$content_id = Globals::get_param(CONTENT_ID_URL_PARAM, $_POST);
if($content_id)
{
    $page = Load::page($content_id);
    //remember old add on
    $addon = $page->get(CONTENT_DOODAD);
    if($page->process_post($_POST))
    {
	$new_addon = $page->get(CONTENT_DOODAD);
	//if added an add on
	if(empty($addon) && empty($new_addon) == false) {
	    Load::award_settings()->bestow_award(SET_ADD_ON_AWARD);
	}
	Load::award_settings()->bestow_award(EDIT_PAGE_AWARD);
	//redirect to view their changes
	Globals::redirect('index.php?'.CONTENT_ID_URL_PARAM."=$content_id");
    }
}
//if just arriving at this page then the GET will have the content ID
$content_id = Globals::get_param(CONTENT_ID_URL_PARAM, $_GET, $content_id);
if($content_id)
{
    $ps = Load::page_store();
    if($ps->store_item_exists($content_id))
    {
        //display the editor
        $page = get_content_edit_page($content_id);
        echo($page);
        exit();
    }
    else
    {
        $error = 'Non-existent content specified';
    }
}
if($content_id == null)
{
    $error = 'No content was identified';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>No content error</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
		<?php echo(Message::get_error_display($error)); ?>
	</body>
</html>