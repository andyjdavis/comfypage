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
require_once('common/class/payment_processor.class.php');

Globals::dont_cache();
Login::logged_in();

$error = null;
$success = null;

$settings_classes = array('GeneralSettings', 'CounterSettings','MemberSettings','Permissions','Portal', 'PaymentGeneralSettings', 'AwardSettings');

$ps = Load::page_store();
$stored_pages = $ps->get_used_ids_in_store();
$prs = Load::product_store();
$stored_products = $prs->get_used_ids_in_store();
$stored_functions = Addon::get_function_keys();
$payment_processors = PaymentProcessor::get_processor_keys();

$us = Load::user_store();
$stored_user = $us->get_used_ids_in_store();

$setting_class_selected = Globals::get_param('settingclass', $_GET, null);
$page_id_selected = Globals::get_param('page', $_GET, null);
$product_id_selected = Globals::get_param('product', $_GET, null);
$function_id_selected = Globals::get_param('function', $_GET, null);
$payment_id_selected = Globals::get_param('payment', $_GET, null);
$user_id_selected = Globals::get_param('user', $_GET, null);

$display_title = null;
$display_output = null;

///if haven't chosen anything to edit
if($setting_class_selected == null && $page_id_selected == null && $product_id_selected == null && $function_id_selected == null && $payment_id_selected == null && $user_id_selected == null)
{
	//choose sometihng to start with
	$setting_class_selected = 'GeneralSettings';
}
if($setting_class_selected == 'GeneralSettings')
{
    $class = Load::general_settings();
}
else if($setting_class_selected == 'MemberSettings')
{
	$class = Load::member_settings();
}
else if($setting_class_selected == 'CounterSettings')
{
	$class = Load::counter_settings();
}
else if($setting_class_selected == 'EmailSettings')
{
	$class = Load::email_settings();
}
else if($setting_class_selected == 'PaymentGeneralSettings')
{
	$class = Load::payment_general_settings();
}
else if($setting_class_selected == 'Permissions')
{
	$class = Load::permission_settings();
}
else if($setting_class_selected == 'PayPalSettings')
{
	$class = Load::paypal_settings();
}
else if($setting_class_selected == 'Portal')
{
	$class = Load::portal_settings();
}
else if($setting_class_selected == 'AwardSettings')
{
	$class = Load::award_settings();
}
else if($page_id_selected != null)
{
    $class = Load::page($page_id_selected);
}
else if($product_id_selected != null)
{
    $class = Load::product($product_id_selected);
}
else if($function_id_selected != null)
{
    $class = Load::addon($function_id_selected);
}
else if($payment_id_selected != null)
{
    $class = Load::payment_processor($payment_id_selected);
}
else if($user_id_selected != null)
{
    $class = Load::user($user_id_selected);
}

if($_POST)
{
    if($class->process_post($_POST))
    {
        $success = 'Saved';
    }
    else
    {
        $error = 'Fix the errors<br /><a href="">Discard changes</a>';
    }
}

if($page_id_selected != null)
{
    $display_output = $class->get(RAW_CONTENT);
	$display_title = 'Page content';
}
else if($product_id_selected != null)
{
    require_once('common/contentServer/product_page.php');
	$display_output = get_product_display($class);
	$display_title = 'Product display';
}
else if($function_id_selected != null)
{
	$display_output = Addon::execute($function_id_selected, 'fake_content_id');
	$display_title = 'Add-on output';
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
	    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
	</head>
	<body style="margin:0;">
	    <?php
			require_once('common/menu.php');
			$m = new Menu();
			echo($m->get_menu());
	    ?>
		<div style="padding:0.3em;">
	        <?php
	            echo(Message::get_error_display($error));
	            echo(Message::get_success_display($success));
	            echo('<p><b>Settings</b> ');
	            foreach($settings_classes as $settings_class)
	            {
	                echo("<a href=\"database.php?settingclass=$settings_class\">$settings_class</a> &nbsp;&nbsp;");
	            }
	            echo('</p>');
				echo('<p><b>Pages</b> ');
	            foreach($stored_pages as $page_id)
	            {
	                echo("<a href=\"database.php?page=$page_id\">$page_id</a> &nbsp;&nbsp;");
	            }
	            echo('</p>');
				echo('<p><b>Products</b> ');
	            foreach($stored_products as $product_id)
	            {
	                echo("<a href=\"database.php?product=$product_id\">$product_id</a> &nbsp;&nbsp;");
	            }
	            echo('</p>');
	            echo('<p><b>Users</b> ');
	            foreach($stored_user as $user_id)
	            {
	                echo("<a href=\"database.php?user=$user_id\">$user_id</a> &nbsp;&nbsp;");
	            }
	            echo('</p>');
	            echo('<p><b>Functions</b> ');
	            foreach($stored_functions as $function_id)
	            {
	                echo("<a href=\"database.php?function=$function_id\">$function_id</a> &nbsp;&nbsp;");
	            }
	            echo('</p>');
				echo('<p><b>Payment processors</b> ');
	            foreach($payment_processors as $pp)
	            {
	                echo("<a href=\"database.php?payment=$pp\">$pp</a> &nbsp;&nbsp;");
	            }
	            echo('</p>');
	            echo($class->get_input_form());
	        ?>
	        <!--<form method="post">
	            <table align="center" cellpadding="5" width="90%" cellspacing="2" style='border:solid black 1px;'>
			        <?php
			            $setting_names = $class->get_setting_names();
			            foreach($setting_names as $setting_name)
			            {
			                echo('<tr><td>'.$class->get_description($setting_name).'</td>');
			                echo('<td width="60%">'.$class->get_input($setting_name).'</td>');
			                echo("<td><span style='color:red;'> ".$class->get_error($setting_name)."</span></td></tr>");
			            }
			        ?>
	                <tr>
	                	<td>
	    					<input style="font-size:larger;" type="submit" value=" Save ">
	    				</td>
	                </tr>
	            </table>
	        </form>-->
	        <div>
	            <?php
	                if(empty($display_title) == false)
	                {
	                    echo <<<END
<table align="center" cellpadding="5" cellspacing="2" style="border:solid black 1px">
	<tr>
		<th>$display_title</th>
	</tr>
	<tr>
		<td>$display_output</td>
	</tr>
</table>
END;
					}
				?>
	        </div>
        </div>
	</body>
</html>