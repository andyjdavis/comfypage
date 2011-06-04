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

$function_key = null;
$function_exists = null;

define('SAVE_VAR', 'SAVE_VAR');
define('CHECKBOX_TRUE', 'true');

//if a success msg has been sent to this page
$success = Globals::get_param('success', $_GET);
$function_key = Globals::get_param(FUNCTION_GET_PARAM, $_GET);
$function_key = Globals::get_param(FUNCTION_GET_PARAM, $_POST, $function_key);

if($function_key != null)
{
    $function_exists = Addon::exists($function_key);
    if($function_exists == false)
    {
        $error = 'The specified add-on does not exist. Select an add-on from the <a href=function_admin.php>add-on list</a>';
    }
}
else
{
    $error = 'No add-on was specified. Select one from the <a href=function_admin.php>add-on list</a>';
}
$doodad = Load::addon($function_key);
if(isset($_POST[SAVE_VAR]))
{
    if($doodad->process_post($_POST))
    {
        $success = 'Saved';
    }
    else
    {
        $error = 'Fix the errors<br /><a href="">Discard changes</a>';
    }
}
else
{
    //if not saving still need to check validity of settings
    if($doodad->is_valid(true) == false)
    {
        $error = 'Please complete the configuration';
    }
}

//execute here before any output. In case the function does a redirct
//$execution_output = ExecuteFunction($function_key, '""', true);
$execution_output = Addon::execute($function_key, '""', true);
function GetFunctionItemTableRow($description, $input)
{
    $row = '<tr>';
    $row .= '<td valign="top">';
    $row .= Globals::t($description);
    $row .= '</td>';
    $row .= '<td valign="top" width="70%">';
    $row .= $input;
    $row .= '</td>';
    $row .= '</tr>';
    return $row;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Add-on Configuration</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
	</head>
	<body>
	<?php
	    $menu = new Menu;
	    $menu->add_item('Add-on list', 'function_admin.php');
		echo($menu->get_menu());
		echo(Message::get_error_display($error));
		echo(Message::get_success_display($success));
	?>
		<?php
			if($function_exists)
		    {
		        $doodad = Load::addon($function_key);
		?>
		<?php
		    if($doodad->requires_config())
		    {
		?>
		<table class=admin_table align="center">
	        <tr>
		        <th class="admin_section"><span id=ins class=translate_me>Instructions</span></th>
	        </tr>
	        <tr>
		        <td style='padding-left:4em;padding-right:4em;'><span id=theIns class=translate_me><?php echo($doodad->get_instructions()); ?></span></td>
	        </tr>
        </table>
			<form method=post style="margin:0;padding:0;">
		        <table class=admin_table align="center" cellpadding=5>
		        	<tr>
			               <th class="admin_section" colspan=2><span id=aoo class=translate_me>Add-on Options</span></th>
		            </tr>
		            <tr>
		            <?php
		                $setting_names = $doodad->get_setting_names();
		            	foreach($setting_names as $setting_name)
	        			{
	        			    $setting_value = $doodad->get($setting_name);
	        			    $setting_description = $doodad->get_description($setting_name);
	        			    $setting_input = $doodad->get_input($setting_name, $setting_value);
							echo(GetFunctionItemTableRow($setting_description, $setting_input));
     					}
		            ?>
		            </tr>
		            <tr>
		            	<td>
							<span id="sc" class="translate_me"><input style="font-size:larger;" type="submit" value=' Save '></span>
							<input type="hidden" name="<?php echo(SAVE_VAR); ?>" value="true" >
							<input type="hidden" name="<?php echo(FUNCTION_GET_PARAM); ?>" value="<?php echo($function_key); ?>" >
						</td>
		            </tr>
		        </table>
		        </form>
			<?php
					echo(Message::get_message_display('<b>Save your changes before testing</b>', 'white'));
				} //if($function_to_configure->config_required)
			?>
		    	<!-- TEST FUNCTION -->
		        <table class="admin_table" align="center">
		        	<tr>
			               <th class="admin_section" colspan=2><span id="test" class="translate_me">Test the <i><?php echo($function_key); ?></i></span><?php if(Addon::is_post_back()) echo('&nbsp;&nbsp;&nbsp; <a href="function.php?' . FUNCTION_GET_PARAM . '=' . $function_key . '"><span id=rtest class=translate_me>Reset the test</span></a>'); ?></th>
		            </tr>
		            <tr>
		            	<td>
		            		<?php echo($execution_output); ?>
						</td>
		            </tr>
		        </table>
			<?php
			    } //if($function_to_configure != null)
			?>
	    <?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>