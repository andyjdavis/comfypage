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
require_once('common/menu.php');
//require_once('common/general_settings.php');

$gs = Load::general_settings();

if($gs->operating_under_domain())
{
	//this page is used to go from demo to paid site
	//so if not in demo then they don't need it
	exit();
}

Globals::dont_cache();
Login::logged_in();
//track_user();

$error = null;
$success = null;

/*
if(isset($_GET['error']))
{
	$error = $_GET['error'];
}*/
$error = Globals::get_param('error', $_GET);
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Sign up for ComfyPage</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />

		<script LANGUAGE="JavaScript" SRC="common/contentServer/contentServer.js" ></script>
	</head>

	<body>
		<?php
		echo(get_menu());
		//echo(Message::get_help_link(9918448, 'Help with domains'));
		echo(Message::get_error_display($error));
		echo(Message::get_success_display($success));
		?>
					<div style='text-align:center;'>
					<p style="font-weight:bold;color:red;">It's important that you register a domain before you proceed.</p>
			    	<!--<p style="font-weight:bold;">Do you want to register a domain?</p>-->
						<table width=95% align=center style="background:white; border:solid black 2px;">
							<tr>
								<td width=50% valign=top align=center><p><b><a href=http://planetdomain.com/>I want to register a domain.</a></b><br>
						Register a domain with our partner company<br>and return here with your registered domain.<br><a href=http://planetdomain.com/>Register a domain</a></p></td>
								<td align=center valign=top><b>or</b></td>
								<td width=50% valign=top align=center><p><b><a href=register_with_existing_domain.php>I've already registered it. Let's go!</a></b></p></td>
							</tr>
						</table>
					</div>
					<div style="padding:0 5%;">
						<p><strong>What is a domain?</strong></p>
						<p>&quot;google.com&quot; is a domain. So is &quot;comfypage.com.au&quot;. It is the address of your website. It's what visitors type in to get to your site.</p>
						<p><strong>Types of domain</strong></p>
						<p>The type of domain is identified by the end piece. .com, .net or .com.au etc. The most widely used are .com, .net, .org and .info but there are plenty more about. <br />
						</p>
						<p>Anyone can register some types of domain. For example .com domains are available for all. Some domains have restrictions on them. E.g. Only Australian businesses can register .com.au domains.</p>
						<p>Make sure you are eligible for the domain you want to register.</p>
						<p><strong>You don't own a domain</strong></p>
						<p>You only license it from the relevant registrar. As long as you keep paying for it then you control it.</p>
						<p><a href="http://www.auda.org.au/domains/au-domains/">More info on .au domains</a></p>
					</div>
	  <?php echo(Globals::get_affinity_footer()); ?>
	</body>
</html>