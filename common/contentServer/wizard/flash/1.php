<?php

require_once('common/utils/Globals.php');
require_once('common/contentServer/template_control.php');

require_once('common/contentServer/wizard/wizard_common.php');
require_once('common/contentServer/style.php');

Globals::dont_cache();
//track_user(null, false);

$error = null;

if(Login::logged_in(true))
{
}

$page = '';

if($_GET)
{
	$bgColor = Globals::get_param('tdbgc_background_color', $_GET);
	$page = Globals::get_param('page', $_GET);
	
	if(!empty($bgColor))
	{
		select_template('Default');

		$styles = loadSheet('site/site.css');
		$styles['body']['background-color'] = $bgColor;
		$td_tags = array('header', 'leftMargin', 'rightMargin', 'footer', 'centre');	
		foreach($td_tags as $td_tag)
		{
			$styles['td' . '.' . $td_tag]['background-color'] = $bgColor;
		}
		saveSheet('site/site.css', $styles);
	
		Globals::redirect('wizard.php?page=' . ($page+1));
	}
}

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
		<link rel="stylesheet" href="common/contentServer/js_color_picker_v2/js_color_picker_v2.css" media="screen">
		<script type="text/javascript" src="common/contentServer/js_color_picker_v2/js_color_picker_v2.js"></script>
		
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	
	<body>
						<?php
					echo(Message::get_error_display($error));
					?>
		<table class="admin_table">
			<tr>
				<th colspan=4 class="admin_section"><span id=pt class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(FLASH_STEP_COUNT); ?></span></th>
			</tr>
			<tr>
				<td colspan=4 align=center>
					<span id=ins class=translate_me>This wizard will help you create a ComfyPage to display your flash movies</span>
					<br /><br /><br />
				</td>
			</tr>
			<tr>
				<td id="exampletdbgc" align="center">
					<form method=get style="padding:0; margin:0;">
						<input type=hidden name=page id=page value=<?php echo($page); ?>>
						<span id=q class=translate_me>What colour would you like the page around the movie to be?</span> <?php WriteColour('tdbgc_background_color',''); ?>
						<br /><br /><br /><input type="submit" value="Next" />
					</form>
				</td>
			</tr>
    		</td>
      </tr>
    </table>
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
