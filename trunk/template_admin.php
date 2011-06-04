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
include_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();

$permissions = Load::permission_settings();
$permissions->check_permission(TEMPLATE_ALLOWED, false);

$error = null;
$success = null;

//do this after saving the newly selected template
$template_in_use = Load::general_settings(TEMPLATE_IN_USE);
$template_list = get_template_list();

function get_template_table_cell($template_name, $in_use = false, $is_purchased = true)
{
	$thumb_src = get_path_to_thumbnail($template_name);
	$highlight_style = '';

	if($in_use)
	{
		$highlight_style = 'background:#80FF80;';

return <<<END
		<td NOWRAP align=center style="border:solid black 2px;$highlight_style">
		<p>$template_name</p>
		<p><img alt='Preview template' src="$thumb_src" width=150 height=100 border=1 style="border-color:black;"></p>
		<p><a href="template_custom.php" target="_parent"><span class="translate_me" id="ec">Edit</span></a>&nbsp;&nbsp;&nbsp;&nbsp;<a target="template_example" href="template_example.php?TEMPLATE=$template_name"><span id=$template_name class=translate_me>Preview</span></a></p>
		</td>
END;
	}
	else
	{
		$select_or_buy = "Purchase";
		if($is_purchased)
		{
	        $select_or_buy = '<span id=s'.$template_name.' class=translate_me>Select</span>';
		}
		$select_or_buy = "<a target=_parent href='template.php?use=$template_name' onclick='return confirm_use_template();'>$select_or_buy</a>";

		return <<<END
		<td NOWRAP align=center style="border:solid black 2px;$highlight_style">
		<p>$template_name</p>
		<p><a target=template_example href="template_example.php?TEMPLATE=$template_name"><img alt='Preview template' src="$thumb_src" width=150 height=100 border=1 style="border-color:black;"></a></p>
		<p>$select_or_buy&nbsp;&nbsp;&nbsp;<a target=template_example href="template_example.php?TEMPLATE=$template_name"><span id=$template_name class=translate_me>Preview</span></a></p>
		</td>
END;
	}
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Template Manager</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />

		<script LANGUAGE="JavaScript" SRC="common/contentServer/contentServer.js" ></script>

			<script language=Javascript>
		function confirm_use_template()
		{
			return confirm('Using a template overwrites your style settings. Continue?');
		}
	</script>
	<?php
			echo(Message::get_language_JS_block());
	?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>

	<body>
		<?php
	        $menu = new Menu;
	        echo($menu->get_menu());
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
		?>

		<table align=center border=0 width=100% cellpadding=10>
		<tr>
		<?php

		$cells_per_row = 4;

		for($i = 0; $i<count($template_list); $i++)
		{
		    $name = $template_list[$i];
		    //true if is currently in use template
		    $in_use = ($name == $template_in_use);
			echo(get_template_table_cell($name, $in_use));
		}

		//add table cell for those who want a custom template
		$highlight_style = '';
		$lastCell = <<<END
		<td NOWRAP align="center" style="border:solid black 2px;$highlight_style">
		<p><span id="dl" class="translate_me">None you like?</span></p>
		<p><a target="_blank" href="http://www.help.comfypage.com/index.php?content_id=9918441"><span id="mo" class=translate_me>You can make your own</span></a></p>
		<p>or</p>
		<p><a target="_blank" href="http://comfypage.com/index.php?content_id=58"><span id="mo" class=translate_me>We can make one for you</span></a></p>
		</td>
END;
		echo($lastCell);

		?>
		</tr>
		</table>
	</body>
</html>
