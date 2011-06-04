<?php
require_once('common/utils/Globals.php');
require_once('common/contentServer/wizard/wizard_common.php');

Globals::dont_cache();
//track_user(null, false);

$error = null;

if(Login::logged_in(true))
{
}

$page = '';
$disableNext = "";

if($_GET)
{
	$page = Globals::get_param('page', $_GET);
	$name = Globals::get_param('name', $_GET);

	if(!empty($name))
	{	
		$headerHtml = '<div align=center><h1>'.$name.'</h1></div>';
		Load::page(HEADER)->set(RAW_CONTENT,$headerHtml);
		
		//$year = date('Y');
		//$footerHtml = '<div align=center>&copy;'.$year.' '.$your_name.'</div>';
		$footerHtml = '';
		Load::page(FOOTER)->set(RAW_CONTENT,$footerHtml);

		if(empty($error) == true)
		{
			Globals::redirect('wizard.php?page=' . ($page+1));
		}
	}
	else
	{
		//theyve just come to this page
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
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	
	<body>
		<table class="admin_table" cellspacing="20px;">
			<tr><th colspan="2" class="admin_section"><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(BAND_WEBSITE_STEP_COUNT); ?></span></th></tr>
			<tr>
				<?php echo($msg_info_td); ?>
				<td align="center">
					<?php
					echo(Message::get_error_display($error));
					?>
					<form method=get>
						<input type=hidden name=page id=page value=<?php echo($page); ?>>
						<p><span id="n" class=translate_me>What is your name or the name of your band?</span>&nbsp;&nbsp;<input type=text size=40 name=name></p>
						<p>This will be in your site's header.</p>
						<p>If you need to change it later go to the Site Manager and click on Borders.</p>
						<p><span id="s" class=translate_me><input type=submit <?php echo($disableNext); ?> value=' Next '></span></p>
					</form>
				</td>
			</tr>
		</table>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
