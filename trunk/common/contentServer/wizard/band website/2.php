<?php

require_once('common/utils/Globals.php');
require_once('common/contentServer/template_control.php');
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
	$templateName = Globals::get_param('template', $_GET);

	$permissions = Load::permission_settings();
	if($permissions->check_permission(TEMPLATE_ALLOWED) == false)
	{
		redirect_wizard($page);
	}
	if(!empty($templateName))
	{
		$templateName = urldecode($templateName);
		
		select_template($templateName);
	
		if(empty($error) == true)
		{
			redirect_wizard($page);
		}
	}
	else
	{
		//theyve just come to this page
	}
}

function redirect_wizard($page)
{
	Globals::redirect('wizard.php?page='.($page+1));
}

function echo_custom_template_row()
{
	echo('<tr><td colspan=4 style="text-align:center;">');
	$msg = '<a href="http://comfypage.com/index.php?content_id=2" target=_blank>Click here to contact us if you would like:<ul><li>a ComfyPage template to match existing materials</li><li>a custom template created for you by a graphic design professional</li></ul></a>';
	echo(Message::get_success_display($msg));
	echo('</td></tr>');
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
		<table class="admin_table">
		<tr><th class="admin_section"><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(STEP_COUNT); ?></span></th></tr>
			<tr>
				<td>
					<br />
					<?php
					echo(Message::get_error_display($error));
					?>

							<form method=get>
							<input type=hidden name=page id=page value=<?php echo($page); ?>>
							<input type=hidden name=saveData id=saveData value=1>

							<table align=center cellpadding=5 cellspacing=20 border=0 style="text-align:center;">
							<tr>
			        	<td colspan=4 align=center><span id=text class=translate_me>Select a template for your site.  This controls the look of your site<br />You can change the look any time by going to the Site Manager and clicking on 'Template'</span></td>
							</tr>
							<?php
							echo_custom_template_row();
							?>
			        <tr>
			        	<?php
			        	$imagePath = null;
			        	$imageTag = null;
			        	$templateName = null;
			        	$url = null;

			        	$template_list = get_template_list(false);

								$cols=2;
								for($i = 0; $i<count($template_list); $i++)
								{
									if($i % $cols == 0)
			        		{
			        			echo('</tr><tr>');
									}
									
									$templateName = $template_list[$i];
									$imagePath = get_path_to_thumbnail($templateName);
									
									$url = "wizard.php?template=".urlencode($templateName)."&page=$page";
								  $imageTag = '<td colspan=2><a href='.$url.'><img style="width:300px;height:200px;" src="' . $imagePath . '" /><br /><span id=s'.rand().' class=translate_me>select</span></a></td>';
								  
									echo($imageTag);
								}
								
								while($i++ % $cols != 0)
			        	{
			        		echo('<td></td>');
								}
								
			        	?>
							</tr>
			        <tr>
								<td colspan=4 style="text-align:center;">
									<span id=n class=translate_me>Click on a template to move to the next page</span>
								</td>
			        </tr>
			        <?php
						echo_custom_template_row();
					?>
		      	</table>
    			</form>

    		</td>
      </tr>
    </table>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
