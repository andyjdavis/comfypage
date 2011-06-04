<?php

require_once('common/utils/Globals.php');
require_once('common/contentServer/template_control.php');
require_once('common/contentServer/wizard/wizard_common.php');

define('PAGE_LISTING', 'Page Listing');

Globals::dont_cache();
//track_user(null, false);

$error = null;

if(Login::logged_in(true))
{
}

$wizard_page = '';
$disableNext = "";

$itemsDisplay = array('Home','About','Gigs', 'Photos', 'Downloads','Contact');
$itemsValue = array('Home','About', 'Gigs', 'Photos', 'Downloads','Contact');
//an array containing the stock content
$content = array();
$tmpContentIndex = 0;

//Home
$content[$tmpContentIndex++] = '';//this is needed to make the for loop below work

//About
$content[$tmpContentIndex++] = <<<END
<div align=center><h1>About</h1>
Replace this text with a description of you/your band</div>
END;

//Gigs
$content[$tmpContentIndex++] = <<<END
<h1 style="text-align: center;">Gigs</h1>
<table cellspacing="1" cellpadding="1" border="0" align="center" width="90%">
    <thead>
        <tr>
            <th scope="col">When</th>
            <th scope="col">Where</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center;">gig date goes here</td>
            <td style="text-align: center;">a description of where it is goes here</td>
        </tr>
        <tr>
            <td style="text-align: center;">&nbsp;</td>
            <td style="text-align: center;">&nbsp;</td>
        </tr>
    </tbody>
</table>
END;

//Photos
$content[$tmpContentIndex++] = <<<END
<h1 style="text-align: center;">Photos</h1>
<table cellspacing="1" cellpadding="1" border="0" align="center" width="90%">
    <tbody>
        <tr>
            <td>
            <p style="text-align: center;"><img alt="" style="" src="site/UserFiles/sample.jpg" /></p>
            </td>
            <td>
            <p style="text-align: center;"><img alt="" style="" src="site/UserFiles/sample.jpg" /></p>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">&nbsp;</td>
            <td style="text-align: center;">&nbsp;</td>
        </tr>
        <tr>
            <td style="text-align: center;">&nbsp;</td>
            <td style="text-align: center;">&nbsp;</td>
        </tr>
    </tbody>
</table>
<p style="text-align: center;">Images can be edited by right-clicking on them while editing the page. Insert new images by clicking on <img src="http://www.help.comfypage.com/site/UserFiles/toolbar/FCK_image.gif" alt="fckeditor - insert image button" /> in the editor.</p>
END;

//Downloads
$content[$tmpContentIndex++] = <<<END
<h1 style="text-align: center;">Downloads</h1>
<table cellspacing="1" cellpadding="1" border="0" align="center" width="90%">
    <thead>
        <tr>
            <th scope="col">Link</th>
            <th scope="col">What is it?</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><a href="site/UserFiles/sample.jpeg">replace this with links to files you have uploaded</a></td>
            <td>An example download link</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </tbody>
</table>
<p>To upload files go to the <strong>Site manager</strong> by clicking on <img alt="site manager button" src="common/images/Site%20manager.png" /> in the toolbar then click on <strong>Files</strong>. (Then probably remove this so your visitors don't see it)</p>
END;

//Contact
$content[$tmpContentIndex++] = <<<END
<div align=center><h1>Contact</h1>
Replace this text with your contact details or add the contact form add-on</div>
END;

unset($tmpContentIndex);

if($_GET)
{
	//var_dump($_GET);
	$wizard_page = Globals::get_param('page', $_GET);
	$saveData = Globals::get_param('saveData', $_GET);
	$menu_location = Globals::get_param('menu_location', $_GET);
	$menuHtml = '';
	$menu_seperator = null;

	if(!empty($saveData))
	{
		Load::page(LEFT_MARGIN)->set(RAW_CONTENT,'');
		Load::page(RIGHT_MARGIN)->set(RAW_CONTENT,'');

		$pages = Load::page_store()->load_users_pages();
		
		//we start at 1 as 0 (home) content created elsewhere
		$page = null;
		$ps = Load::page_store();
		for($i = 1; $i<sizeof($content); $i++)
		{
			if(isset($_GET[$itemsValue[$i]]))
			{
				$new_page_title = $itemsDisplay[$i];
				$page_exists = false;
				
				//just in case theyre rerunning the wizard check if the pages already exist
				if($ps->page_exists($new_page_title)==false)
				{
					//$contentId = createContent($new_page_title);
					//save_content($contentId, $content[$i]);
					$page = $ps->create();
					$page->set(CONTENT_TITLE,$new_page_title);
					$page->set(RAW_CONTENT,$content[$i]);
					/*TODO What happened to this function? What replaces it?
					 * if(is_dept_sport_site() && $itemsDisplay[$i] == 'Contact')
					{
						//SetFunctionForContent($contentId, 'Contact Us Form');
						$page->set(CONTENT_DOODAD,'Contact Us Form');
					}*/
				}
			}
		}

		//push custom menu items
		$i = 1;
		while(null != $customItem = Globals::get_param('custom'.$i++, $_GET))
		{
			if($ps->page_exists($customItem)==false)
			{
				//$contentId = createContent($customItem);
				//save_content($contentId, '<div align=center><h1>'.$customItem.'</h1>Modify this page however you like</div>');
				$page = $ps->create();
				$page->set(CONTENT_TITLE,$customItem);
				$page->set(RAW_CONTENT, '<div align=center><h1>'.$customItem.'</h1>Modify this page however you like</div>');
			}
		}

		set_menu_location($menu_location);
	
		if(empty($error) == true)
		{
			Globals::redirect('wizard.php?page=' . ($wizard_page+1));
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
		
		<script language="Javascript">
			var customIndex = 2;

			function AddCustomMenuItem()
			{
				var html = "<br /><input id=custom" + customIndex + " name=custom" + customIndex + " type='text' onKeyPress=\"TextBoxChange(" + customIndex + ")\" />";
				customIndex++;
				//document.getElementById('customMenuItemsDiv').innerHTML += html;

	      var ni = document.getElementById('customMenuItemsDiv');
	      var newdiv = document.createElement('div');

	      //newdiv.setAttribute('id','custom' + customIndex++);
	      newdiv.innerHTML = html;
	      ni.appendChild(newdiv);

				//alert(document.getElementById('customMenuItemsDiv').innerHTML);
			}
			
			function TextBoxChange(index)
			{
				if(index == customIndex-1 )
				{
					AddCustomMenuItem();
				}
			}
		</script>
		
		<?php
			echo(Message::get_language_JS_block());
		?>
		<script LANGUAGE=JavaScript SRC="common/contentServer/contentServer.js"></script>
	</head>
	<body>
	<?php
		echo(Message::get_error_display($error));
	?>
	<form name=theForm method=get style="padding:0;margin:0;">
		<input type=hidden name=page id=page value=<?php echo($page); ?>>
		<input type=hidden name=saveData value=1>
		<table class="admin_table" border="0" cellspacing="20px">
		<tr><th colspan=2><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(BAND_WEBSITE_STEP_COUNT); ?></span></th></tr>
		<tr>
			<td>
			<table  border="0" cellpadding="0" cellspacing="0" style="width:100%;text-align:center;">
			<tr>
				<td align=center colspan=2><p><span id=wm class=translate_me>Where would you like your site menu?</span></p>
				    <table cellpadding=5 align=center border=1 width=300 style="margin-bottom:1em;">
					    <tr>
					    	<td colspan=3 style="text-align:center;"><label><input type=radio name=menu_location value=t><span id=at class=translate_me>Along the top</span></label></td>
					    </tr>
					    <tr>
					        <td><label><input checked type=radio name=menu_location value=l><br><span id=dl class=translate_me>Down<br>the<br>left</span</label></td>
					        <td align=center><span id=yourCP class=translate_me>Your ComfyPage</span></td>
					        <td align=right><label><input type=radio name=menu_location value=r><br><span id=dr class=translate_me>Down<br>the<br>right</span></label></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=2 align=center><span id=tick class=translate_me>Tick the menu items you want</span></td>
			</tr>
			<?php
				//output home option seperate as its disabled
				echo('<tr><td width=45% align=right><INPUT checked TYPE=CHECKBOX disabled NAME="'.$itemsValue[0].'"></td><td align=left>'.$itemsDisplay[0].'</td></tr>');
				for($i = 1; $i<count($itemsDisplay); $i++)
				{		
					//if is sport and rec site and this is the Contact item
					/*TODO Where did the sport and rec function go? What is happening with them?
					 * if(is_dept_sport_site() && $itemsDisplay[$i] == 'Contact')
					{
						//hide the input because we want to force it on them
						echo('<tr><td align=right><INPUT checked TYPE=hidden NAME="'.$itemsValue[$i].'"></td><td align=left></td></tr>');
					}else{*/
						echo('<tr><td align=right><INPUT checked TYPE=CHECKBOX NAME="'.$itemsValue[$i].'"></td><td align=left>'.$itemsDisplay[$i].'</td></tr>');
					//}
				}		
			?>
			<tr>
				<td align=center colspan=2><span id=type class=translate_me>Type in more menu items</span>
					<div id="customMenuItemsDiv">
						<input id=custom1 name=custom1 type='text' onKeyPress="Javascript:TextBoxChange(1);" />
					</div>
					<br />
					<noscript>
						<input type=submit <?php echo($disableNext); ?> value=' Next '>
					</noscript>
					<a href="Javascript:document.theForm.submit();"><span id=next class=translate_me>Save Menu Items</span></a>
				</td>
			</tr>
		</table>
		</td>
		<td align="center" style="padding:15px;width:40%;">
			<div style="background-color:#e4f3f3;padding:10px;">
			<p><span id="b1" class=translate_me>Forums, maps and videos (or almost anything else) can be embedded in your ComfyPage site.</span></p>
			<p><span id="b2" class=translate_me>It's your site.  Make it what <b>you</b> want.</span></p>
			<p><a href="http://help.comfypage.com/index.php?content_id=9918461" target="_blank">Click here to learn more...</a></p></div>
		</td>
		</tr>
		</table>
	</form>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
