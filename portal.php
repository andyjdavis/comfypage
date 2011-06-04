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
require_once('common/utils/Validate.php');
require_once('common/menu.php');
require_once('common/utils/PasswordGenerator.php');

Globals::dont_cache();
Login::logged_in();

$error = null;
$success = null;

$gs = Load::general_settings();

if($gs->operating_under_subdomain())
{
	echo('only sites running on their own domain can create subdomains');
	exit();
}

$portal_settings = Load::portal_settings();
$portal_settings_names = array(USE_MY_TEMPLATE, USE_MY_STYLE, PORTAL_BRAND);
if(isset($_POST['save_portal_settings']))
{
	if($portal_settings->process_post($_POST, $portal_settings_names))
	{
		$success = 'Saved';
		$portal_settings->apply_my_settings_to_portal();
	}
	else
	{
		$error = $portal_settings->get_error_message();
	}
}
$create_email = $gs->get(ADMIN_EMAIL);
$portal_allowed = $gs->operating_under_domain();
if($portal_allowed == false)
{
	$error = '<p>You must have your own domain to use the portal</p><p><a href=register.php>Move to your own domain</a></p>';
}
$new_portal = Globals::get_param('create', $_POST, 'New site name');
if(isset($_POST['create']) && $portal_allowed)
//because the default value is not null we can't test for null to see if this action should be taken
//check for presence of create var
{
	$new_portal = strtolower($new_portal);
	$site_id = Load::general_settings(NEW_SITE_ID);
	$create_email = Globals::get_param('create_email', $_POST);

	$portal_error = Validate::domain("$new_portal.$site_id");
	$email_error = Validate::email($create_email);
	$error = Validate::sitename($new_portal);

	$error = Message::format_errors($error, $email_error);
	if(empty($error))
	{
		require_once('common/ServerInterface.php');
		$password = PasswordGenerator::generate();
		//$error = create_my_comfypage_website($new_portal, $create_email, $password, $site_id);
		$error = ServerInterface::create_subdomain($new_portal, $create_email, $password, $site_id);
		if(empty($error))
		{
			require_once('common/settings/PortalSettings.php');
			$portal = new PortalSettings();
			$portal->apply_my_settings_to_portal($new_portal);
			$msg = <<<END
I have been created for you by the administrator of $site_id. You can use me to create your own website.

Here are my details:
Location: http://$new_portal.$site_id/
Login email: $create_email
Password: $password (Remember to change the password once you log in)
END;
			Globals::send_email_to_admin('ComfyPage sub-domain created', $msg, $create_email, "$new_portal.$site_id");
			$success = 'Sub-domain created';
			//reset to default value
			$new_portal = 'New site name';
		}
	}
}

$delete_portal = Globals::get_param('delete', $_GET);
if($delete_portal != null)
{
	$gs = Load::general_settings();
	$site_id = $gs->get(NEW_SITE_ID);
	//$new_portal = $_POST['create'];
	$delete_portal = strtolower($delete_portal);
	//$error = Validate::domain("$delete_portal.$site_id");
	//if(empty($error))
	{
		require_once('common/ServerInterface.php');
		$si = new ServerInterface;
		$error = $si->delete_subdomain_of_this_site($delete_portal);
		if(empty($error))
		{
			//redirect to get rid of query string
			Globals::redirect('portal.php');
		}
	}
}

$site_id = $gs->get(NEW_SITE_ID);
require_once('common/ServerInterface.php');
$si = new ServerInterface;
$subdomains = $si->get_portal_list($site_id);

function get_portal_row($site_id,$portal_key)
{
	$portal_address = "$portal_key.$site_id";
	$r = rand();
	return <<<END
<tr>
	<td style="width:4em;" nowrap>$portal_address</td>
	<td style="width:4em;"><a href="http://$portal_address/"><span id="o$r" class=translate_me>Open</a></td>
	<td><a href="portal.php?delete=$portal_key" onclick="return confirmDeletion();"><span id="d$r" class=translate_me>Delete</span></a></td>
</tr>
END;
}
function get_setting_html_row($name, $description, $input_html, $configured_correctly)
{
	$row = '<tr';

	if($configured_correctly == false)
	{
		$row .= ' style="background:#FFA0A0;" ';
	}

	$row .= '>';
	$r = rand();
	$row .= '<td width=50%><span id="d'.$r.'" class=translate_me>';
	$row .= $description;
	$row .= '</td>';
	$row .= "<td align=center>$input_html</td>";
	$row .= '</tr>';
	return $row;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Portal Manager</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
		<script language=Javascript>
		var force_template = null;
		function remember_checkbox_value()
		{
			//remeber what the vlaue was at the beggining
			force_template = document.portal_options.USE_MY_TEMPLATE.checked;
		}
		function confirmDeletion()
		{
			return confirm('Permanently delete?');
		}
		//ask them if they want to overwrite all subdomain's templates
		function confirmSave()
		{
			//get the new checkbox value
			new_force_template_value = document.portal_options.USE_MY_TEMPLATE.checked;
			//if it has gone from not checked to checked
			if(new_force_template_value && !force_template)
			{
				return confirm("All sub-domains will have their templates overwritten?");
			}
		}
		</script>
	</head>
	<body onLoad="remember_checkbox_value();">
		<?php
		    echo(get_menu());
			echo(Message::get_error_display($error));
			echo(Message::get_success_display($success));
		?>
		<form method=post name=portal_options style="margin:0;" onSubmit="return confirmSave();">
		<table class=admin_table align="center">
			<tr>
				<th colspan=2><span id="po" class=translate_me>Portal Options</span></th>
			</tr>
			<?php
				foreach($portal_settings_names as $psn)
				{
					$setting_value = $portal_settings->get($psn);
					$setting_description = $portal_settings->get_description($psn);
					$setting_html = $portal_settings->get_input($psn, $setting_value);
					$configured_correctly = true;
					echo(get_setting_html_row($psn, $setting_description, $setting_html, $configured_correctly));
				}
			?>
			<tr>
				<td colspan='2'><a href="function.php?function=Portal Signup"><span id="moopsa" class=translate_me>There are more portal related options on the portal signup add-on</span></a></td>
			</tr>
			<tr>
				<td colspan='2'><input type=submit value=Save ><input type=hidden name=save_portal_settings value=1></td>
			</tr>
		</table>
		</form>
		<form method=post style="margin:0;">
			<table class=admin_table align="center">
				<tr>
					<th colspan=2><span id="csd" class=translate_me>Create sub-domain</span></th>
				</tr>
				<tr>
					<td nowrap><span id="sdn" class=translate_me>Sub-domain's name</span></td>
					<td><input type=text size=15 value="<?php echo($new_portal); ?>" name=create>.<?php echo($site_id); ?></td>
				</tr>
				<tr>
					<td width=20% nowrap><span id="sae" class=translate_me>Subdomain's administrator's email</span></td>
					<td><input type=text size=30 value="<?php echo($create_email); ?>" name=create_email></td>
				</tr>
				<tr>
					<td><input type=submit value=Create></td>
				</tr>
			</table>
		</form>
		<table class=admin_table align="center">
			<tr>
				<th colspan=3><span id="ysd" class=translate_me>Your sub-domains</span></th>
			</tr>
		<?php
			if(count($subdomains) == 0)
			{
				echo('<tr><td align=center><i><span id="yhnsd" class=translate_me>(You have no sub-domains)</span></i></td></tr>');
			}
			else
			{
				$site_id = Load::general_settings(NEW_SITE_ID);
				foreach($subdomains as $subdomain)
				{
					echo(get_portal_row($site_id,$subdomain));
				}
			}
		?>
		</table>
		<table class=admin_table align="center">
			<tr>
				<th>Instructions</th>
			</tr>
			<tr>
				<td style="padding:0.5em 6%;">
					<p>A portal lets you split a website into smaller sites. The smaller websites (called sub-domains) will each have their own address like in this example...</p>
					<p style="font-style:italic;">John owns a pet shop and has used ComfyPage to move his shop online. His web-address is mypetshop.com. He has so much information on dogs, cats and mice that he decides to give each one a sub-domain.</p>
					<ul>
						<li>dogs.mypetshop.com</li>
						<li>cats.mypetshop.com</li>
						<li>mice.mypetshop.com</li>
					</ul>
					<p style="font-style:italic;">Each sub-domain is a ComfyPage. For now he can manage them all himself but when he takes on more staff he can delegate a sub-domain to each of his employees.</p>
					<p><?php echo(Message::get_help_link(9918459, 'Read the portal help topic')); ?></p>
					<p><a href="function.php?function=Portal Signup">Click here for portal sign up add-on options</a></p>
					<p style="font-size:smaller;">Did you notice that mypetshop.com could be "My Pet Shop" or "My Pets Hop"? :)</p>
				</td>
			</tr>
		</table>
	    <?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>