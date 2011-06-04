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
require_once('common/menu.php');
require_once('common/utils/Load.php');

Globals::dont_cache();
Login::logged_in();

define('DELETE_PAGE_URL_PARAM', 'delete');
define('CREATE_PAGE_URL_PARAM', 'create');
define('COPY_PAGE_URL_PARAM', 'copy');
define('BACKUP_URL_PARAM', 'backup');

$error = null;
$success = null;

$ps = Load::page_store();

$delete_id = Globals::get_param(DELETE_PAGE_URL_PARAM, $_GET);
if($delete_id != null)
{
	$error = $ps->delete($delete_id);
	if(empty($error))
	{
		$success = 'Deleted';
	}
}
$new_title = Globals::get_param(CREATE_PAGE_URL_PARAM, $_POST);
if($new_title != null)
{
	$new_page = $ps->create();
	$new_page->set(CONTENT_TITLE, $new_title);
	$new_page->set(CONTENT_DESC, $new_title);
	Load::award_settings()->bestow_award(ADD_PAGE_AWARD);
	Globals::redirect('edit.php?'. CONTENT_ID_URL_PARAM .'=' . $new_page->id);
}
$copy_from_id = Globals::get_param(COPY_PAGE_URL_PARAM, $_GET);
if($copy_from_id != null)
{
    $new_page = $ps->copy(Load::page($copy_from_id));
    $title = $new_page->get(CONTENT_TITLE).' (copy)';
    $new_page->set(CONTENT_TITLE, $title);
	$m = Load::member_settings();
	if($m->is_members_only_page($copy_from_id))
	{
		$m->add_members_only_page($new_page->id);
	}
	Globals::redirect('edit.php?'. CONTENT_ID_URL_PARAM .'=' . $new_page->id);
}
$backup = Globals::get_param(BACKUP_URL_PARAM, $_GET);
if($backup != null)
{
	// increase script timeout value
	ini_set("max_execution_time", 300);
	// create object
	$zip = new ZipArchive();
	// open archive
	if ($zip->open("site/UserFiles/sitebackup.zip", ZIPARCHIVE::CREATE) !== TRUE) {
		die ("Could not open archive");
	}
	// initialize an iterator
	// pass it the directory to be processed
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("site/"));
	// iterate over the directory
	// add each file found to the archive
	foreach ($iterator as $key=>$value) {
		//ignore . and ..
		$test = '.';
		if ( substr_compare($key, $test, -strlen($test), strlen($test)) !== 0 ) {
			$zip->addFile(realpath($key), $key) or die ("ERROR: Could not add file: $key");
		}
	}
	// close and save archive
	$zip->close();
	$success = '<a href="site/UserFiles/sitebackup.zip">Download Backup</a>';
}

$user_pages = $ps->load_users_pages();
$index_page = $ps->load_index_page();

//used to remove chars from strings that will ultimately be html object IDs that wont work.  control characters.
function MakeSafe($s)
{
	return preg_replace("/[^a-zA-Z0-9]/", "", $s);
}
function GetPageHtmlRow($contentId, $title, $view, $edit, $delete, $function, $purpose = null, $copy = true, $is_private = false)
{
	if(empty($title))
	{
		$title = '<i>(No title)</i>';
	}
	if(strlen($title) > 40)
	{
		$title = substr($title, 0, 40).'...';
	}
	$del = null;
	if($delete)
	{
		$del = '<a href=admin.php?' . DELETE_PAGE_URL_PARAM . '=' . $contentId. '><span id="delete_'.$contentId.'" class="translate_me">Delete</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	$content_id_param = CONTENT_ID_URL_PARAM;
	$copy_param = COPY_PAGE_URL_PARAM;
	echo <<<END
<tr>
	<td nowrap style="font-weight:bold;">$title</td>
	<td>
	&nbsp;&nbsp;&nbsp;&nbsp;<a href=index.php?$content_id_param=$contentId><span id="view_$contentId" class="translate_me">View</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="edit.php?$content_id_param=$contentId"><span id="edit_$contentId" class="translate_me">Edit</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="admin.php?$copy_param=$contentId"><span id="copy_$contentId" class="translate_me">Copy</span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	$del
	</td>
</tr>
END;
}
function GetOptionRow($link, $name, $description, $help=null)
{
	GetOption($link, $name, $description, $help, false);
}
function GetOption($link, $name, $description, $help=null, $add_break=true)
{
    $safe_name = MakeSafe($name);
    echo('<a style="font-weight:bold;" href=' . $link . '><span id="'.$safe_name.'" class="translate_me">' . $name . '</span></a>');
    if($help != null)
    {
	echo('<span>&nbsp;&nbsp;&nbsp;&nbsp;'.Message::get_help_link($help).'</span>');
    }
    if($add_break)
    {
	echo('<div id="'.$safe_name.'_desc" class="translate_me">' . $description . '</div>');
    }
    else
    {
	echo('&nbsp;&nbsp;&nbsp;&nbsp;<span id="'.$safe_name.'_desc" class="translate_me">' . $description . '</span>');
    }
    if($add_break)
    {
	echo('<br />');
    }
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Site Manager</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
		<?php
			echo(get_menu('Site manager'));
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
			//echo($page_view_errors);
		?>
	    <!-- MENU -->
	    <?php
			$permissions = Load::permission_settings();
			$template_allowed = $permissions->check_permission(TEMPLATE_ALLOWED);
			$style_allowed = $permissions->check_permission(STYLE_ALLOWED);
			$menu_width = '25%';
			if(!$template_allowed && !$style_allowed)
			{
				$menu_width = '33%';
			}
	    ?>
	    <table align="center" class="admin_table" style="padding-top:10px;padding-left:20px;padding-right:20px;">
	    <tr>
			<?php
			if($template_allowed || $style_allowed)
			{
				echo("<td style=\"vertical-align:top;width:$menu_width\">");
				echo('<p style="text-align: center;"><img height="48px" width="48px" src="common/images/appearance.png" /></p>');
			}
			if($template_allowed)
			{
				GetOption('template.php', 'Template', "Change your site's look as often as you like", null);
			}
			if($style_allowed)
			{
				GetOption('editstyle.php', 'Styles', 'Fonts and colours');
			}
			if($template_allowed || $style_allowed)
			{
				echo('</td>');
			}
			?>
		<td style="vertical-align:top;width:<?php echo($menu_width); ?>;">
			<p style="text-align: center;"><img height="48px" width="48px" src="common/images/promotion.png" /></p>
			<?php
			GetOption('promotion.php', 'Promotion', 'Some sites to help you promote yourself', null);
			GetOption('mail.php', 'Mailing lists', 'Stay in contact with your visitors');
			?>
		</td>
		<td style="vertical-align:top;width:<?php echo($menu_width); ?>;">
			<p style="text-align: center;"><img height="48px" width="48px" src="common/images/settings.png" /></p>
			<?php
			GetOption('account.php', 'Account', 'Login details, tracking and domain name', null);
			GetOption('members.php', 'Password Protection', 'Password protect your pages');
			GetOption('users.php', 'Multiuser Access', 'Let multiple people edit your site');
			?>
		</td>
		<td style="vertical-align:top;width:<?php echo($menu_width); ?>;">
			<p style="text-align: center;"><img height="48px" width="48px" src="common/images/extras.png" /></p>
			<?php
			GetOption('function_admin.php', 'Add-ons', 'Extra website abilities', '6964999');
			$gen_set = Load::general_settings();
			if(!$gen_set->operating_under_subdomain())
			{
				GetOption('portal.php', 'Portal', 'Create sub-websites', null);
			}
			else
			{
				echo('<div style="color:gray;"><a target="_blank" href="http://comfypage.com/index.php?content_id=50">Portal</a><br />To create subsites <a href="account.php">move to a domain</a></div><br />');
			}
			GetOption('product_admin.php', 'Shop Manager', 'Got something to sell?');
			?>
		</td>
	    </tr>
		</table>
		<?php echo(Addon::execute('Trophy Case', null)); ?>
		<!-- PAGES -->
        <table align="center" class="admin_table" cellpadding="0" cellspacing="0">
            <tr>
		<td width="65%">
		    <table align="center" class="admin_table" style="border:none;margin:0;">
			<?php
			    echo('<tr><th colspan="3"><span class="translate_me" id="c">Content</span></th></tr>');
			    echo('<tr><td colspan="3">');
			    GetOptionRow('margins.php', 'Borders&nbsp;&nbsp;&nbsp;', 'Your site\'s header, footer, left and right margin');
			    echo('</td></tr>');
			    echo('<tr><td colspan="3">');
			    GetOptionRow('files.php', 'Your Files', 'Manage files and images');
			    echo('</td></tr>');			    
			    echo('<tr><td colspan="3">');
			    GetOptionRow('admin.php?backup=1', 'Backup', 'Backup up files and pages');
			    echo('<br /><br /></td></tr>');
			    //echo('<tr><td colspan="3" style="height:0px;text-align:center;"><div style="position:absolute; left:70%;">'.$service_level.'</div></td></tr>');
			    echo('<tr><th colspan="3"><span class="translate_me" id="yp">Your Pages</span></th></tr>');
			    GetPageHtmlRow($index_page->id, $index_page->get(CONTENT_TITLE), true, true, false, true, null, true);
			    //for($i=0; $i<count($user_pages); $i++)
			    foreach($user_pages as $user_page) {
				GetPageHtmlRow($user_page->id, $user_page->get(CONTENT_TITLE), true, true, true, true, null, true);
			    }
			?>
		    </table>
		</td>
		<td style="padding-top:1em;" align="center" valign="top"></td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <form name="newPage" method="post">
			<table width="100%">
			    <tr>
				<td width="90%" nowrap><p style="padding-top:0.5em;"><input value="New page title" type="text" name="create" size="50" /><a href="Javascript:document.newPage.submit();"> <span id="apba" class="translate_me">Add Page</span></a></p></td>
				<td align="right"><p style="padding-top:0.5em;"><?php echo('<a href="trash.php"><img border="0" src="common/images/bin.png" alt="Trash"></a>'); ?></p></td>
			    </tr>
			</table>
		    </form>
		</td>
	    </tr>
	</table>
	<?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>
