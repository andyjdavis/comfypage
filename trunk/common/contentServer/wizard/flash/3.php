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

$page = '';
$purpose = '';

if($_GET)
{
	$watched = Globals::get_param('watched', $_GET);
	$page = Globals::get_param('page', $_GET);
	$addons = Globals::get_param('addons', $_GET);
	
	if(!empty($watched))
	{
		$h = Globals::get_param('h', $_GET);
		$w = Globals::get_param('w', $_GET);
	
		if(!empty($h) && !empty($w))
		{
			if(!is_numeric($h) || !is_numeric($w))
			{
				$error = 'Height and width must be numeric';
			}
			else
			{
				$fm = 'Flash Movie';
				$addon = Load::addon($fm);
				$addon->set(FLASH_MOVIE_WIDTH,$w);
				$addon->set(FLASH_MOVIE_HEIGHT,$h);
				
				Load::general_settings()->set_done_wizard(true);
				Globals::clearContentDependentCaches();//will notify others of this sites existence
				Globals::redirect('index.php');
			}
		}
		else
		{
			$error = 'Enter your movies height and width';
		}
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
		<br /><table class="admin_table">
		<tr><th class="admin_section"><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(FLASH_STEP_COUNT); ?></span></th></tr>
			<tr>
				<td>
					<br />
				<?php
					echo(Message::get_error_display($error));
					?>
			    <form method="get">
			    	<input type=hidden name=page id=page value=<?php echo($page); ?>>			    	
			    	<input type=hidden name=watched id=watched value=1>
			    	
		    		<table align=center cellpadding=10 cellspacing=2>
				<tr>
					<td style="text-align:right;"><span id="width" class=translate_me>Width of your movie in pixels</span></td>
					<td><input type="text" name="w" /></td>
				</tr>
				<tr>
					<td style="text-align:right;"><span id="height" class="translate_me">Height of your movie in pixels</span></td>
					<td><input type="text" name="h" /></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center;">
						<div style="width:80%;background-color:#f0f7f9;margin: 0 auto;padding:10px;">
							<p><span id="text1" class="translate_me">ComfyPage's optional add-ons let you do more with your site.</span></p>
							<p><span id="text1" class="translate_me">Accept donations, receive messages from your visitors, run a mailing list and much more.</span></p>
							<p><span id="text1" class="translate_me">For more information go to your site, go to the Site Manager and click on Add-ons.</span></p>
						</div>
						<br /><input type="submit" value="Next" />
					</td>
				</tr>
		    		<tr>
			        	<td colspan="2" style="text-align:center;" align="center">
			        		
					</td>
			        </tr>
		      	</table>
    			</form>

    		</td>
      </tr>
    </table>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
