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

Globals::dont_cache();
Login::logged_in();

$error = null;
$success = null;

$function_list = Addon::get_function_keys();
$success = Globals::get_param('success', $_GET);

function GetFunctionTableRow($name, $desc, $config_required)
{
    $link_text = 'Configure and test';
    if($config_required == false) $link_text = 'Test';
    $link = "<a href='function.php?" . FUNCTION_GET_PARAM . "=$name'>$link_text</a>";
    return get_generic_row($name, $desc, $link);
}
function get_generic_row($title, $desc, $link)
{
    $rand = rand();
    return <<<END
<tr>
<td width=200 nowrap valign=top style="font-weight:bold;text-align:center;"><span id=t$rand class=translate_me>$title</span></td>
<td width=300 valign=top style="text-align:left;padding-bottom:20px;"><span id=d$rand class=translate_me>$desc</span></td>
<td nowrap valign=top><span id=l$rand class=translate_me>$link</span></td>
</tr>
END;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Add-on List</title>
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
        	<table class=admin_table align="center">
        		<th colspan=4><span id="aoT" class=translate_me>Add-ons</span></th>
				<?php
			        	foreach($function_list as $function)
			        	{
				    		$doodad = Load::addon($function);
							if($doodad != null)
							{
									echo(GetFunctionTableRow($function, $doodad->get_addon_description(), $doodad->requires_config()));
							}
						}
				?>
        	</table>
	    <?php echo(Globals::get_affinity_footer()); ?>
	    
	</body>
</html>
