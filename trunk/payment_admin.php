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
 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
include_once('common/menu.php');
require_once('common/settings/PaymentGeneralSettings.php');

define('SAVE', 'save');

Globals::dont_cache();
Login::logged_in();

$error = null;
$success = null;

$gps = Load::payment_general_settings();
$chosen_processor_name = $gps->get(SELECTED_PROCESSOR);
$new_processor = Globals::get_param(SELECTED_PROCESSOR, $_GET);
$chosen_processor = Load::payment_processor($chosen_processor_name);
$pps_names = PaymentProcessor::get_processor_keys();

//if saving settings of payment processor
if(isset($_POST[SAVE]))
{
    //get array of payment general setting names
    $general_settings = $gps->get_setting_names();
    //find the key of the SELECTED_PROCESSOR setting name
    $key = array_search(SELECTED_PROCESSOR, $general_settings);
    //don't process the SELECTED_PROCESSOR
    unset($general_settings[$key]);
    //make sure both of these funcitons get called because
    //before I had them in the if statement but the second call wasn't
    //being made as the first call was returning false
    //and the && statement would stop the second executing
    $pp_ok = $chosen_processor->process_post($_POST);
    $gp_ok = $gps->process_post($_POST, $general_settings);
    if($pp_ok && $gp_ok)
    {
        $success = 'Options saved';
    }
    else
    {
        $error = $chosen_processor->get_error_message();
        $error = Message::format_errors($error, $gps->get_error_message());
        $error = "Not saved<br />$error<br /><a href=payment_admin.php>Discard changes</a>";
    }
}
else if($new_processor != null)
{
    if($gps->process_post($_GET, array(SELECTED_PROCESSOR)))
    {
        $chosen_processor_name = $new_processor;
        $chosen_processor = Load::payment_processor($chosen_processor_name);
        if($chosen_processor->is_valid(true) == false || $gps->is_valid(true) == false)
        {
            $error = "Complete the configuration";
            $error = Message::format_errors($error, $gps->get_error_message());
            $error = Message::format_errors($error, $chosen_processor->get_error_message());
        }
        $success = 'Payment processor changed';
    }
    else //else keep the old one
    {
        $new_processor_error = $gps->get_error(SELECTED_PROCESSOR);
        $error .= "New settings have not been saved<br />$new_processor_error";
    }
}
else if($chosen_processor->is_valid(true) == false || $gps->is_valid(true) == false)
{
    $error = "Complete the configuration";
    $error = Message::format_errors($error, $gps->get_error_message());
    $error = Message::format_errors($error, $chosen_processor->get_error_message());
}
function get_processor_row($name, $description, $is_selected_processor, $paid_for = true)
{
	$in_use = '<i>In use</i>';
	if($is_selected_processor == false)
	{
		$in_use = "<a href=payment_admin.php?".SELECTED_PROCESSOR."=$name>Use $name</a>";
	}
	return <<<END
<tr>
	<td align="center" nowrap width="15%"><b style="font-size:larger;">$name</b></td>
	<td align="justify">$description</td>
	<td align="center" nowrap width="15%">$in_use</td>
</tr>
END;
}
function get_setting_row($setting_name, $description, $input_html)
{
	return <<<END
<tr>
	<td align="justify">$description</td>
	<td align="center">$input_html</td>
</tr>
END;
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Payment Options</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<?php
	        echo(get_menu());
	        echo(Message::get_success_display($success));
			echo(Message::get_error_display($error));
		?>
		<!-- PAYMENT PROCESSOR SELECTION -->
		<table class="admin_table" align="center">
        	<tr>
	            <th colspan="3">Payment Processor</th>
				<td align="right" nowrap>Help with <?php echo(Message::get_help_link(9918444, 'Payment processor')); ?></td>
            </tr>
            <?php
                $is_selected_processor = null;
                $payment_processor_name = null;
                $payment_processor = null;
				for($i=0; $i<count($pps_names); $i++)
				{
					$payment_processor_name = $pps_names[$i];
					$payment_processor = Load::payment_processor($payment_processor_name);
					$is_selected_processor = ($payment_processor_name == $chosen_processor_name);
					echo(get_processor_row($payment_processor_name, $payment_processor->get_processor_description(), $is_selected_processor));
				}
            ?>
        </table>
		<table class="admin_table" align="center">
			<tr>
				<th>Instructions</th>
			</tr>
			<tr>
				<td align="justify" style="padding-left:4em;padding-right:4em;"><?php echo($chosen_processor->get_instructions()); ?></td>
			</tr>
		</table>
		<!-- PAYMENT OPTIONS -->
		<form method="post">
    		<table class="admin_table" align="center">
			<tr>
	        	<th>Payment Options </th>
	        	<td align="right" nowrap>Help with <?php echo(Message::get_help_link(9918445, 'Payment options')); ?></td>
            </tr>
			<!-- GENERAL PAYMENT SETTINGS -->
			<?php
				//get the names of the general settings
				$setting_names = $gps->get_setting_names();
				//for each setting
				for($i=0; $i<count($setting_names); $i++)
				{
					$setting_name = $setting_names[$i];
					//not choosing the processor here
					if($setting_name != SELECTED_PROCESSOR)
					{
						$setting_value = $gps->get($setting_name);
						$setting_description = $gps->get_description($setting_name);
						$setting_input_html = $gps->get_input($setting_name, $setting_value);
						echo(get_setting_row($setting_name, $setting_description, $setting_input_html));
					}
				}
			?>
			<!-- PAYMENT PROCESSOR SPECIFIC OPTIONS -->
			<?php
				$setting_names = $chosen_processor->get_setting_names();
				//for each setting
				for($i=0; $i<count($setting_names); $i++)
				{
					$setting_name = $setting_names[$i];
					$setting_value = $chosen_processor->get($setting_name);
					$setting_description = $chosen_processor->get_description($setting_name);
					$setting_input_html = $chosen_processor->get_input($setting_name, $setting_value);
					echo(get_setting_row($setting_name, $setting_description, $setting_input_html));
				}
			?>
        	<tr>
            	<td colspan="2"><input type="submit" value="Save" style="font-size:larger;"></td>
            </tr>
    	</table>
		<input type="hidden" name="<?php echo(SAVE); ?>" value="1">
	    </form>
	    <?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>