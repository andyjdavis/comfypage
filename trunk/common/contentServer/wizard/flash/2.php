<?php

require_once('common/utils/Globals.php');
require_once('common/file.php');
require_once('common/contentServer/wizard/wizard_common.php');

function get_page($ps,$id)
{
	if($ps->page_exists($id)==false)
	{
		return $ps->create($id);
	}
	else
	{
		return Load::page($id);
	}
}

Globals::dont_cache();
//track_user(null, false);

$error = null;

if(Login::logged_in(true))
{
}

$wizard_page = '';
$encLogoPath = '';
$decLogoPath = '';

$disableNext = "disabled";

//&& array_key_exists('userfile', $_POST)
if($_POST )
{
	$wizard_page = Globals::get_param('page', $_POST);
	
	//theyre saving their logo
	if(array_key_exists('userfile', $_FILES) && !empty($_FILES['userfile']['name']))
	{
		require_once('common/lib/file_upload/upload.class.php');
		$file = new FileUpload();
		$file->AddGoodFileType('.swf');
		$file->SetMaxFileSize('10000000');
		$file->SetOverwrite(true);
		$file->UploadFile('userfile', 'site/UserFiles/', null);
		//echo "FileSize: " . $file->GetFileSize() . "<br>";
		//echo "Extension: " . $file->GetExtension() . "<br>";
		//echo "Filename: " . $file->GetFilename() . "<br>";
		//echo "Error: " . $file->GetError() . "<br>";
		$error = $file->GetError();
		
		if(empty($error))
		{
			$filename = $file->GetFilename();
			$decPath = 'site/UserFiles/'.$filename;
			
			require_once('common/lib/image_resize/image_resize.php');
			$real_path = $files->get_real_path($filename);

			$fm = 'Flash Movie';
			$ps = Load::page_store();

			$page = get_page($ps,INDEX);
			$page->set(RAW_CONTENT,'');
			$page->set(CONTENT_DOODAD,$fm);

			//these dont seem to result in the margins actually being created for some reason
			get_page($ps,HEADER)->set(RAW_CONTENT,'');
			get_page($ps,FOOTER)->set(RAW_CONTENT,'');
			get_page($ps,LEFT_MARGIN)->set(RAW_CONTENT,'');
			get_page($ps,RIGHT_MARGIN)->set(RAW_CONTENT,'');

			$addon = Load::addon($fm);
			$addon->set(FLASH_MOVIE_PATH,$decPath);

			Globals::redirect('wizard.php?page=' . ($wizard_page+1));
		}
	}
}
else if($_GET)
{
	$page = Globals::get_param('page', $_GET);
/*	$saveData = Globals::get_param('saveData', $_GET);

	if(!empty($saveData))
	{
		//create site front page
		$content = null;

		$businessName = Globals::get_param('businessName', $_GET);
		$encLogoPath = Globals::get_param('logoPath', $_GET);
		
		$logoImgTag = null;
		if(!empty($encLogoPath))
		{
			$decLogoPath = urldecode($encLogoPath);
			$logoImgTag = "<img src=\"$decLogoPath\" alt=\"$businessName logo\"/>";
		}
		else
		{
			$logoImgTag = '<h1>'.$businessName.'</h1>';
		}

		$content = <<<END
<div align=center><h1>$businessName</h1>
<div align="left"><img width="200" height="200" align="left" src="site/UserFiles/sample.jpg" alt="" />This is your website.  Replace this text and image with information about your organization by clicking the 'Edit Page' link at the bottom of the page.</div>
</div>
END;
		
		require_once('common/contentServer/content_db.php');
		//save_content(INDEX, $content);
		
		$headerHtml = '<div align=center>'.$logoImgTag.'</div>';
		
		$year = date('Y');
		$footerHtml = '<div align=center>&copy;'.$year.' '.$businessName.'</div>';
		
		save_content(HEADER, $headerHtml);
		save_content(FOOTER, $footerHtml);
	
		if(empty($error) == true)
		{
			Globals::redirect('wizard.php?page=' . ($page+1) . '&purpose=' . $purpose[0]);
		}
	}
	else
	{
		//theyve just come to this page
	}*/
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
		<table class="admin_table" style="padding-bottom:1em;">
			<tr>
				<th colspan=3><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(FLASH_STEP_COUNT); ?></span></th>
			</tr>
			<tr>
				<td colspan=3 align=center>
					<br />
					<?php
						echo(Message::get_error_display($error));

						$input_html = HtmlInput::get_file_input('userfile', null);
						echo <<<END
<form name="logoForm" enctype="multipart/form-data" method="POST">
<input type=hidden name=page id=page value=$page />
<p><span id=hl class=translate_me>Upload your first SWF</span></p><p>$input_html&nbsp;&nbsp;<noscript><input name=submitButton type=submit value="Upload"></noscript><a href="Javascript:document.logoForm.submit();"><span id=upl class=translate_me>Upload Movie</span></a></p>
</form>
END;
					?>
				</td>
			</tr>
			<tr>
				<td style="text-align:center;">
					<div style="width:80%;background-color:#f0f7f9;margin: 0 auto;padding:10px;">
						<span id=text1 class=translate_me>
							Once your site is created you can upload more SWFs by going to the Site Manager and clicking on Files
						</span>
					</div>
				</td>
			</tr>
		      	</table>

    		</td>
      </tr>
    </table>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
