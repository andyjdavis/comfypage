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
require_once('common/contentServer/template_control.php');
require_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();

$permissions = Load::permission_settings();
$permissions->check_permission(TEMPLATE_ALLOWED, false);

$errors = null;
$success = null;

$gs = Load::general_settings();
$site_id = $gs->get(NEW_SITE_ID);

include_once('common/contentServer/page.php');
$template = get_template();

$new_template = Globals::get_param('template_edit', $_POST, null, false);
$save_var = Globals::get_param('save_template', $_POST);
if($save_var == '1')
{
	$new_template = stripslashes($new_template);
	$template = $new_template;
	//save the template
	//set to custom template
 	$errors = validateTemplate($new_template);
	if(empty($errors))
	{
		$gs->set(TEMPLATE_IN_USE, CUSTOM_TEMPLATE_NAME);
		file_put_contents('site/CustomTemplate/template.htm', $new_template);
		$portal = Load::portal_settings();
		$portal->apply_my_settings_to_portal();
		$success = 'Saved';
    }
    else
    {
		$errors = Message::format_errors($errors, '<br><a href=template_custom.php>Discard changes</a>');
	}
}

function validateTemplate($new_template)
{
	$errors = '';
	if(strstr($new_template, '<!--%CONTROLS%-->') == false)
	{
		$errors = Message::format_errors($errors, 'Missing the <input type=text value="&lt;!--%CONTROLS%--&gt;"> tag');
	}
	if(strstr($new_template, '<!--%CENTRE%-->') == false)
	{
		$errors = Message::format_errors($errors, 'Missing the <input type=text value="&lt;!--%CENTRE%--&gt;"> tag');
	}
	if(strstr($new_template, '<!--%AFFINITY%-->') == false)
	{
		$errors = Message::format_errors($errors, 'Missing the <input type=text value="&lt;!--%AFFINITY%--&gt;"> tag');
	}
	require_once('common/utils/Validate.php');
	$illegal = Validate::content($new_template);
	$errors = Message::format_errors($errors, $illegal);
	return $errors;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<HTML>
<HEAD>
  <title>Edit Template</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
	<?php
		echo(Message::get_language_JS_block());
	?>
	<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
</HEAD>
<BODY>
	<?php
	    $menu = new Menu;
	    $menu->add_item('Templates', 'template.php');
	    echo($menu->get_menu());
	    echo(Message::get_error_display($errors));
	    echo(Message::get_success_display($success));
	?>
	<form method=post>
	<input type=hidden name='save_template' value='1'>
	<table class=admin_table>
	    <tr>
	        <td style="padding-left:2em;"><input type=submit value=Save>&nbsp;&nbsp;&nbsp;<a href="template.php"><span class="translate_me" id="d1">Discard Changes</span></a></td>
	    </tr>
	    <tr>
	        <td align="center"><textarea name=template_edit rows=25 style="width:98%"><?php echo($template); ?></textarea></td>
	    </tr>
	    <tr>
	        <td style="padding-left:2em;"><input type=submit value=Save>&nbsp;&nbsp;&nbsp;<a href="template.php"><span class="translate_me" id="d2">Discard Changes</span></a></td>
	    </tr>
	</table>
	</form>
	<table class=admin_table>
		<tr>
	        <th>Instructions</th>
	    </tr>
	    <tr>
	        <td>
	        	<p>Every page of your site uses this template.</p>
				<p>Upload your template's files (images etc) with the <a href=files.php>file manager</a></p>
				<p>Your files are located at <b>http://<?php echo($site_id); ?>/site/UserFiles/</b></p>
				<p>These tags are used to make your template work well with ComfyPage.</p>
				<table border="0" align="center" cellpadding="3">
				    <tr>
						<th style="color:black;">Tag</th>
						<th style="color:black;">Purpose</th>
						<th style="color:black;">Required?</th>
					</tr>
				    <tr>
				        <td><input type=text value="&lt;!--%TITLE%--&gt;" READONLY></td>
				        <td>Title of each page</td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%META_DESCRIPTION%--&gt;" READONLY></td>
				        <td>Meta description tag</td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%JS%--&gt;" READONLY></td>
				        <td>JavaScript to help ComfyPage</td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="%STYLE%" READONLY></td>
				        <td>Styles defined by <a href="editstyle.php">style editor</a></td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%CONTROLS%--&gt;" READONLY></td>
				        <td>Admin control bar</td>
				        <td style="font-weight:bold;color:red;">Required</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%LEFT_MARGIN%--&gt;" READONLY></td>
				        <td>Left margin from <a href="margins.php">border editor</a></td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%RIGHT_MARGIN%--&gt;" READONLY></td>
				        <td>Right margin from <a href="margins.php">border editor</a></td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%CENTRE%--&gt;" READONLY></td>
				        <td>Main content of each page that you edit</td>
				        <td style="font-weight:bold;color:red;">Required</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%HEADER%--&gt;" READONLY></td>
				        <td>Header from <a href="margins.php">border editor</a></td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%FOOTER%--&gt;" READONLY></td>
				        <td>Footer from <a href="margins.php">border editor</a></td>
				        <td>Recommended</td>
					</tr>
					<tr>
				        <td><input type=text value="&lt;!--%AFFINITY%--&gt;" READONLY></td>
				        <td>Log in links (Site manager, Edit page etc)</td>
				        <td style="font-weight:bold;color:red;">Required</td>
					</tr>
				</table>
			</td>
	    </tr>
	</table>
    <?php echo(Globals::get_affinity_footer()); ?>
</BODY>
</HTML>
