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
 * Manage page password protection
 *

 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
require_once('common/menu.php');
require_once('common/utils/Load.php');

Globals::dont_cache();
Login::logged_in();
//site_enabled_check();
//track_user();

$error = null;
$success = null;

$ps = Load::page_store();
$user_pages = $ps->load_users_pages();
$index_page = $ps->load_index_page();

$m = Load::member_settings();
//$setting_names = array(MEMBERS_ONLY_PAGES);
$success = $m->process_protections($_GET); //process public/private changes
if(empty($success) == false)
{
	Load::award_settings()->bestow_award(PASSWORD_PROTECT_PAGE_AWARD);
}

if($_POST)
{
	$setting_names = array(MEMBERS_AREA_PASSWORD);
	$m->process_post($_POST, $setting_names);
	$error = $m->get_error_message();
	$success = 'Saved';
}
function GetPageHtmlRow($contentId, $title, $is_private = false)
{
	if(empty($title))
	{
		$title = '<i>(No title)</i>';
	}
	if(strlen($title)>40)
	{
		$title = substr($title, 0, 40).'...';
	}
	echo('<tr style="height:26px;">');
	echo('<td nowrap style="font-weight:bold;width:100px;padding-right:1em;">' . $title . '</td>');
	if($is_private)
	{
		echo('<td width=30 align=center>');
		echo('<img src="common/images/lock.gif" width=15 style="vertical-align:baseline" />');
		echo('</td>');
		echo('<td nowrap width=250>');
		echo('<a href=members.php?' . MAKE_PUBLIC . '=' . $contentId . '><span id=pub'.$contentId.' class=translate_me>Click to remove password protection</span></a>');
		echo('</td>');
	}
	else
	{
	    echo('<td></td>');
		echo('<td nowrap width=250>');
		echo('<a href=members.php?' . MAKE_PRIVATE . '=' . $contentId . '><span id=priv'.$contentId.' class=translate_me>Click to password protect</span></a>');
		echo('</td>');
	}
	echo("<td><a href=index.php?content_id=$contentId>View</a></td>");
	echo('</tr>');
}
	
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Passwor Protection for Pages</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
		<?php
			echo(get_menu());
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
		?>
		<form method=post style="padding:0;margin:0;">
			<table class="admin_table" border="0" align="center">
				<tr>
					<th colspan=2><span id="accOptTitle" class="translate_me">Password Protection</span></th>
				</tr>
				<tr>
					<td nowrap align="justify" valign="top" style="width:100px;padding-right:1em;"><?php echo($m->get_description(MEMBERS_AREA_PASSWORD)); ?></td>
					<td><?php echo($m->get_input(MEMBERS_AREA_PASSWORD)); ?></td>
				</tr>
				<tr>
					<td colspan="2"><span id="save" class="translate_me"><input type=submit value="Save" style="font-size:larger;"></span></td>
				</tr>
			</table>
		</form>
        <table class="admin_table" border="0" align="center">
        	<tr>
				<th><span id=yourPage class=translate_me>Your Pages</span></th>
			</tr>
			<?php
				$is_private = $m->is_members_only_page($index_page->id);
        	    GetPageHtmlRow($index_page->id, $index_page->get(CONTENT_TITLE), $is_private);
        		for($i=0; $i<count($user_pages); $i++)
        		{
					$is_private = $m->is_members_only_page($user_pages[$i]->id);
 			      	GetPageHtmlRow($user_pages[$i]->id, $user_pages[$i]->get(CONTENT_TITLE), $is_private);
        		}
        	?>
        </table>
        <table class=admin_table border=0 align="center">
        	<tr>
				<th><span id=yourPage class=translate_me>Instructions</span></th>
			</tr>
			<tr>
			    <td>
			        If you want to make certain pages available only to certain people then use password protection. Click to make the pages password protected in the list. Give the password only to those who should have access.
			    </td>
			</tr>
        </table>
	<?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>