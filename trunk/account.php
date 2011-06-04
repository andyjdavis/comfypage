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
 * ComfyPage frontpage
 *

 * @copyright  2006 onwards Affinity Software (http://affinitysoftware.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if(!isset($_SESSION)) {
    session_start();
}

require_once('common/utils/Globals.php');
//require_once('common/paypal/paypal_functions.php');
require_once('common/menu.php');

Globals::dont_cache();
Login::logged_in();
//track_user();

define('SAVE_ACCOUNT_SETTINGS','SAVE_ACCOUNT_SETTINGS');

$error = null;
$success = null;

require_once('common/utils/Load.php');
$gs = Load::general_settings();
$site_address = $gs->get(NEW_SITE_ID);
//$page_view_limit = PAGE_VIEW_MONTHLY_LIMIT;
//$counter = Load::counter_settings();
//$page_view_count = $counter->get(PAGE_VIEW_COUNT_NEW);

//$unlimited_start = $counter->get_12_months_unlimited_start_date();
//$unlimited_end = $counter->get_12_months_unlimited_end_date();

$del = Globals::get_param('del', $_POST);
if($del=='42')
{
	require_once('common/ServerInterface.php');
	$si = new ServerInterface;
	$si->delete_this_site();
	//Globals::redirect('http://comfypage.com');
}

$success = Globals::get_param('success', $_GET	);
//$credit_control = Load::credit_settings();
//$txn_history = $credit_control->get(CREDIT_USAGES);

//$paypal = Load::paypal_settings();
//$payments_pending = $paypal->get(PAYMENTS_PENDING);
$operating_under_domain = $gs->operating_under_domain();

$setting_names = array(ADMIN_EMAIL, PASSWORD, LANG, TRACKING_CODE);
if(isset($_POST[SAVE_ACCOUNT_SETTINGS]))
{
    $gs->process_post($_POST, $setting_names);
    $error = $gs->get_error_message();
    $tracking_code = $gs->get(TRACKING_CODE);
    if(empty($tracking_code) == false)
    {
		Load::award_settings()->bestow_award(ADD_TRACKING_CODE_AWARD);
	}
    if(empty($error))
    {
		$success = 'Saved';
	}
	else
	{
		$error = Message::format_errors($error, '<a href="account.php">Discard changes</a>');
	}
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Account Options</title>
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
		<form method="post" style="padding:0;margin:0;">
        	<table class="admin_table" align="center">
        		<tr>
        			<th colspan=2><span id="accOptTitle" class="translate_me">Account Options</span></th>
        		</tr>
                <?php
                foreach($setting_names as $setting_name)
                {
					$description = $gs->get_description($setting_name);
					$input_html = $gs->get_input($setting_name);
					echo <<<END
<tr>
	<td align="justify" valign="top">$description</td>
	<td align="center">$input_html</td>
</tr>
END;
                }
                ?>
                <tr>
                    <td colspan=2><span id="save" class="translate_me"><input type=submit value="Save" style="font-size:larger;"></span></td>
                </tr>
            </table>
            <input type=hidden name=<?php echo(SAVE_ACCOUNT_SETTINGS); ?> value=1>
        </form>

	<table class="admin_table" align="center">
		<tr>
			<th colspan=3><span id="siteInfoHeader" class="translate_me">Site Information</span></th>
		</tr>
		<tr>
			<td colspan=3 style="padding-top:10px;"><b><span id="siteAddyHeader" class="translate_me">Site Address</span></b></td>
		</tr>
		<?php
				$own_domain_msg = Message::get_help_link(9918455, "Can't find your ComfyPage at this address?");
				if($operating_under_domain == false)
				{
					$own_domain_msg = ' <a style="font-size:smaller;font-weight:bold;" href=register.php><span id="moveToDomain" class="translate_me">Move to your own domain for free</span></a>';
				}
				?>
		    <tr>
				<td nowrap width=10%><span id=sAd class=translate_me>Site address is <?php echo("<b>http://$site_address</b>"); ?></span></td>
				<td colspan=2><?php echo($own_domain_msg); ?></td>
			</tr>
		<!--<tr>
			<td colspan=3 style="padding-top:10px;"><b><span id="othlogHeader" class="translate_me">More Users</span></b></td>
		</tr>
		<tr>
                    <td colspan="3"><a href="users.php"><span id="othlogbody" class="translate_me">Let more people edit your ComfyPage</span></a></td>
                </tr>-->
		<!--<tr>
			<td colspan="2" style="padding-top:10px;"><b><span id="siteAddyHeader" class="translate_me">Page Views</span></b></td>
			<td align="right"></td>
		</tr>
		<?php
			$more_than = null;
			if($page_view_count == $page_view_limit)
			{
				$more_than = 'more than';
			}
		?>
		<tr>
			<td nowrap><span id="pageViews" class="translate_me"><?php echo("<b>$more_than $page_view_count of $page_view_limit</b> page views used this month "); echo(' '.Message::get_help_link('9918449')); ?></span></td>
		</tr>-->
		<tr>
		    <td>
		        <?php
					//if there are payments pending
					if(empty($payments_pending) == false)
					{
						$keys = array_keys($payments_pending);
						echo('<tr><td><b><span id="payPend" class="translate_me">Payment pending for</span></b>&nbsp;&nbsp;&nbsp;');
						foreach($keys as $key)
						{
							echo($payments_pending[$key]. '&nbsp;&nbsp;&nbsp;');
						}
						echo('</td></tr>');
					}
		        ?>
		    </td>
		</tr>
	</table>
		<table class="admin_table" align="center">
			<tr><th><span id="delSite" class="translate_me">Delete Site</span></th></tr>
			<tr>
				<td style="text-align:center;">
				If you want your site immediately deleted click the below link.  Only do this if you are absolutely sure you do not want your site.
					<form name="delSite" method="post">
						<input value="42" type="hidden" name="del" />
						<a href="Javascript:if(confirm('delete site?')){document.delSite.submit();}"> <span id="apba" class="translate_me">Delete my website</span></a>
					</form>
				</td>
			</tr>
		</table>
	    <?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>