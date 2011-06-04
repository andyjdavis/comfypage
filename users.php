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
 * User management
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

$error = null;
$success = null;

$us = Load::user_store();

$mu_enabled  = true;

$new_email = Globals::get_param('new_email', $_POST, '');
$new_password = Globals::get_param('new_password', $_POST, '');
if(empty($new_email) == false || empty($new_password) == false)
{
	require_once('common/utils/Validate.php');
    $error = Validate::email($new_email);
	$error = Message::format_errors($error, Validate::password($new_password));
	$admin_email = Load::general_settings(ADMIN_EMAIL);
	$user = $us->get_user($new_email); //search existing users
	if($mu_enabled == false)
	{
		$error = Message::format_errors($error, '<a href="service_levels.php">Upgrade to use this feature</a>');
	}
	if($new_email == $admin_email || $user != null)
	{
		$error = Message::format_errors($error, 'Email is already in use');
	}
	if(empty($error))
	{
		$us->create_user($new_email, $new_password);
		$success = 'User created';
		$new_email = '';
		$new_password = '';
	}
}
$delete = Globals::get_param('delete', $_GET, '');
if($delete)
{
	$us->delete($delete, false);
	Globals::redirect('users.php?success=Deleted');
}
$success = Globals::get_param('success', $_GET, $success);
function get_user_row($user)
{
	$email = $user->get(USER_EMAIL);
	return <<<END
<tr>
	<td style="width:5em;">$email</td>
	<td style="padding-left:1em;"><a href="?delete={$user->id}">Delete</a></td>
</tr>
END;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Manage Users</title>
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
		<form method="post" style="margin-bottom:0;" action="users.php">
			<table class="admin_table" align="center">
			    <tr>
			        <th colspan="2">Create New User</th>
			    </tr>
				<tr>
				    <td width="100">Email</td>
					<td><input type="text" size="50" name="new_email" value="<?php echo($new_email); ?>"></td>
				</tr>
				<tr>
				    <td>Password</td>
					<td><input type="password" size="50" name="new_password" value="<?php echo($new_password); ?>"></td>
				</tr>
				<tr>
				    <td><input type="submit" value="Create user"></td>
					<td></td>
				</tr>
			</table>
		</form>
		<table class="admin_table" align="center">
			<tr>
				<th colspan="2">Users</th>
			</tr>
			<?php
				$user_ids = $us->get_used_ids_in_store();
			    if(count($user_ids) == 0)
			    {
					echo('<tr><td><i>(No users. Create a user above.)</i></td></tr>');
				}
			    foreach($user_ids as $user_id)
			    {
					echo(get_user_row(Load::user($user_id)));
				}
			?>
		</table>
		<table class="admin_table" align="center">
			<tr>
				<th>Instructions</th>
			</tr>
			<tr>
			    <td>
			    The users you create here can log in and help update your website. They cannot view or edit <a href="account.php">account</a> or user details (seen on this page).
			    </td>
			</tr>
		</table>
	</body>
</html>
