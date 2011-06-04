<?php

require_once('common/utils/Globals.php');
require_once('common/utils/Broadcast.php');
require_once('common/contentServer/wizard/wizard_common.php');

Globals::dont_cache();
//track_user(null, false);

$error = null;

if(Login::logged_in(true))
{
}

$page = '';
$purpose = '';

Load::general_settings()->set_done_wizard(true);
Globals::clearContentDependentCaches();//will notify others of this sites existence

if($_GET)
{
	$purpose = Globals::get_param('purpose', $_GET);
	$watched = Globals::get_param('watched', $_GET);
	$page = Globals::get_param('page', $_GET);
	$servicelevels = Globals::get_param('servicelevels', $_GET);
	
	if(!empty($watched))
	{
		if(!empty($servicelevels))
		{
			Globals::redirect('service_levels.php');
		}
		else
		{
			Globals::redirect('edit.php?content_id=INDEX');
		}
	}
}

$foldingChair_link = <<<END
<noscript>
<input type=submit name=noservicelevels value=' Take me to my site '>
</noscript>
<a href="edit.php?content_id=INDEX"><p><span id=noshow class=translate_me>Take me to my site</span></p></a>
END;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Wizard page <?php echo($page); ?></title>
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
				<th class="admin_section"><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(STEP_COUNT); ?></span></th>
			</tr>
			<tr>
			       	<td colspan="2" style="text-align:center;">
					<br />
					<?php
						echo(Message::get_error_display($error));
					?>
					<form method=get>
					<input type=hidden name=page id=page value=<?php echo($page); ?>>
					<input type=hidden name=purpose id=purpose value=<?php echo($purpose); ?>>
			    	
					<input type=hidden name=watched id=watched value=1>
			       		<!--<p>ComfyPage comes in three flavours.</p>
					<p>You can upgrade any time or use ComfyPage for free, forever</p><br />-->
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;">
					<p><h2><span id="cong" class=translate_me>Congratulations</span></h2></p>
					<p><span id="comp1" class=translate_me>Your site is ready and available to anyone, anywhere in the world.</span></p>
					<p><span id=noshow class=translate_me><a href="edit.php?content_id=INDEX">Take me to my site</a></span></p>
					</form>
				</td>
			</tr>
		</table>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
