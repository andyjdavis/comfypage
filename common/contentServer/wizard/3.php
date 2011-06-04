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

$page = '';

$itemsDisplay = array('Home','Contact','About Us', 'Products', 'Services');
$itemsValue =   array('Home','Contact','About_Us', 'Products', 'Services');
//an array containing the stock content
$content = array();
$tmpContentIndex = 0;

//Home
$content[$tmpContentIndex++] = '';//this is needed to make the for loop below work

//Contact
$content[$tmpContentIndex++] = <<<END
<div align=center><h1>Contact</h1>
Replace this text with your contact details</div>
END;

//About Us
$content[$tmpContentIndex++] = <<<END
<div align=center><h1>About Us</h1>
Replace this text with a description of you</div>
END;

//Products
$content[$tmpContentIndex++] = <<<END
<div align="center">
<h1>Products</h1>
<p>Replace this text with information about your products.</p>
<p>Add products by clicking on&nbsp;<img alt="" src="common/images/Shop%20manager.png">in the toolbar at the top of the page</p>
</div>
END;

//Services
$content[$tmpContentIndex++] = <<<END
<div align=center><h1>Services</h1>
Replace this text with information about your services</div>
END;

unset($tmpContentIndex);

if($_GET) {
	$page = Globals::get_param('page', $_GET);
}
if($_POST) {
	//if contact page check box is disabled it wont be included in the post
	$_POST[$itemsDisplay[1]] = 'on';

	$page = Globals::get_param('page', $_POST);
	$menu_location = Globals::get_param('menu_location', $_POST);
	$menuHtml = '';
	$menu_seperator = null;

	if( !empty($menu_location) ) {
		Load::page(LEFT_MARGIN)->set(RAW_CONTENT, ' ');
		Load::page(RIGHT_MARGIN)->set(RAW_CONTENT, ' ');

		$ps = Load::page_store();
		$pages = $ps->load_users_pages();

		//we start at 1 as 0 (home page) is created elsewhere
		for($i = 1; $i<sizeof($content); $i++)
		{
			if(isset($_POST[$itemsValue[$i]]))
			{
				//just in case theyre rerunning the wizard check if the pages already exist
				if(!DoesPageExist($pages, $itemsDisplay[$i]))
				{
					$new_page = $ps->create();
					$new_page->set(CONTENT_TITLE,$itemsDisplay[$i]);
					$new_page->set(RAW_CONTENT,$content[$i]);
					/*if(is_dept_sport_site() && $itemsDisplay[$i] == 'Contact')
					{
						$new_page->set(CONTENT_DOODAD,'Contact Us Form');
					}*/
					$new_page->commit();
					//echo("creating $itemsDisplay[$i]<br>");
				}
				else {
					//echo("$itemsDisplay[$i] already exists<br>");
				}
			}
			else {
				//echo("$itemsValue[$i] is not set<br>");
			}
		}

		//push custom menu items
		$i = 1;
		while(null != $customItem = Globals::get_param('custom'.$i++, $_POST))
		{
			if(!DoesPageExist($pages, $customItem))
			{
				$new_page = $ps->create();
				$new_page->set(CONTENT_TITLE,$customItem);
				$new_page->set(RAW_CONTENT,'<div align=center><h1>'.$customItem.'</h1>Modify this page however you like</div>');
			}
		}

		set_menu_location($menu_location);
	
		if(empty($error) == true)
		{
			Globals::redirect( 'wizard.php?page='.($page+1) );
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
	<form name=theForm method=post style="padding:0;margin:0;">
		<input type=hidden name=page id=page value=<?php echo($page); ?>>
		<table class="admin_table" border="0" cellspacing="20px">
		<tr><th colspan=2><span id=t class=translate_me>Wizard page <?php echo($page); ?> of <?php echo(STEP_COUNT); ?></span></th></tr>
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
				$disabled = '';
				for($i = 1; $i<count($itemsDisplay); $i++)
				{
					//if is sport and rec site and this is the Contact item
					if(/*is_dept_sport_site() &&*/ $itemsDisplay[$i] == 'Contact')
					{
						$disabled = 'disabled';
					}else{
						$disabled = '';
					}

					echo("<tr><td align=right><INPUT checked $disabled TYPE=CHECKBOX NAME='{$itemsValue[$i]}'></td><td align=left>{$itemsDisplay[$i]}</td></tr>");
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
		</tr>
		</table>
	</form>
        
    <?php echo(Globals::get_affinity_footer(false)); ?>
	</body>
</html>
