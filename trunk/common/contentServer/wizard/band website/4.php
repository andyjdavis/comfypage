<?php
require_once('common/utils/Globals.php');
require_once('common/contentServer/wizard/wizard_common.php');
require_once('common/utils/Broadcast.php');

Globals::dont_cache();
//track_user(null, false);

$error = null;

if(Login::logged_in(true))
{
}

$wizard_page = Globals::get_param('page', $_GET);
Load::general_settings()->set_done_wizard(true);
Globals::clearContentDependentCaches();//will notify others of this sites existence
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Wizard page <?php echo($wizard_page); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="common/admin_pages.css" rel="stylesheet" type="text/css" />
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	
	<body>
		<br />
		<table class="admin_table" align="center">
			<tr>
				<th class="admin_section"><span id=t class=translate_me>Wizard page <?php echo($wizard_page); ?> of <?php echo(BAND_WEBSITE_STEP_COUNT); ?></span></th>
			</tr>
			<tr>
			       	<td colspan="2" style="text-align:center;">
					<br />
					<?php
						echo(Message::get_error_display($error));
					?>
					<form method=get>
					<input type=hidden name=page id=page value=<?php echo($page); ?>>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;">
					<p><h2><span id="cong" class=translate_me>Congratulations</span></h2></p>
					<p><span id="comp1" class=translate_me>Your site is ready and available to anyone, anywhere in the world.</span></p>
					<p><span id="comp3" class=translate_me>That may sound scary but we're with you every step of the way.</p>
					<p><span id=noshow class=translate_me><a href="edit.php?content_id=INDEX">Take me to my site</a></span></p>
					</form>
				</td>
			</tr>
		</table>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
