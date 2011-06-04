<?php

/*
A set of functions to aid in completing a page based
on the template page.

The page editor is created with get_editor_html.
*/

//looks for an input element not in a form and wraps the content in a <form> if necessary
function add_form($site_id, $content_id, $raw) {
    $formpos = stripos($raw,'<form ');
    $inputpos = stripos($raw,'<input ');
    $submitpos = stripos($raw,'"submit"') | stripos($raw,'\'submit\'');
    if($formpos === false && $inputpos)
    {
	require_once('common/lib/form_spam_blocker/fsbb.php');
	$hidden_tags = get_hidden_tags();

	$logged_in = Login::logged_in(false);

        $form_start = null;
	if(!$submitpos && $logged_in)
	{
	    $form_start .= Message::get_error_display('<p style="text-align:center;">Text boxes found.  Button with type "Submit" not found.<br /><a target="_blank" href="http://help.comfypage.com/index.php?content_id=9918468">Help with forms</a></p>');
	}
	$form_start .= '<form method="POST" action="http://'.$site_id.'/index.php"><input type="hidden" name="'.CONTENT_ID_URL_PARAM.'" value="'.$content_id.'" />'.$hidden_tags;
        
	$form_end = '</form>';

	return $form_start.$raw.$form_end;
    }
    return $raw;
}

function get_template($template_name = null)
{
	require_once('common/contentServer/template_control.php');
	if(empty($template_name))
	{
		$template_name = Load::general_settings(TEMPLATE_IN_USE);
	}
	$tp = get_path_to_template_html($template_name);
	if(file_exists($tp))
	{
		return file_get_contents($tp);
	}
	else
	{
		echo 'The requested template does not exist';
		exit();
	}
}

//Shortcut function that fills in the margins
//Use this when not editting a margin
function fill_out_margins($page, $content_id)
{
    $site_id = Load::general_settings(NEW_SITE_ID);

    $lmargin = Load::page(LEFT_MARGIN);
    $rmargin = Load::page(RIGHT_MARGIN);
    $header = Load::page(HEADER);
    $footer = Load::page(FOOTER);

    $function_name = $header->get(CONTENT_DOODAD);
    $header_content = add_form($site_id, $content_id, $header->get(RAW_CONTENT));
    $header_function_content = Addon::execute($function_name, $content_id);

    $function_name = $lmargin->get(CONTENT_DOODAD);
    $left_content = add_form($site_id, $content_id, $lmargin->get(RAW_CONTENT));
    $left_function_content = Addon::execute($function_name, $content_id);

    $function_name = $rmargin->get(CONTENT_DOODAD);
    $right_content = add_form($site_id, $content_id, $rmargin->get(RAW_CONTENT));
    $right_function_content = Addon::execute($function_name, $content_id);

    $function_name = $footer->get(CONTENT_DOODAD);
    $footer_content = add_form($site_id, $content_id, $footer->get(RAW_CONTENT));
    $footer_function_content = Addon::execute($function_name, $content_id);

    $page = fill_out_header($page, $header_content.$header_function_content.$header->get(CONTENT_EMBED));
    $page = fill_out_left_margin($page, $left_content.$left_function_content.$lmargin->get(CONTENT_EMBED));
    $page = fill_out_right_margin($page, $right_content.$right_function_content.$rmargin->get(CONTENT_EMBED));
    $page = fill_out_footer($page, $footer_content.$footer_function_content.$footer->get(CONTENT_EMBED));
    return $page;
}


function fill_out_header($page, $content)
{
	$page = str_replace('<!--%HEADER%-->', $content, $page);
	return $page;
}

function fill_out_left_margin($page, $content)
{
	$page = str_replace('<!--%LEFT_MARGIN%-->', $content, $page);
	return $page;
}

function fill_out_right_margin($page, $content)
{
	$page = str_replace('<!--%RIGHT_MARGIN%-->', $content, $page);
	return $page;
}

function fill_out_footer($page, $content)
{
	$tracking_code = null;
	if(Login::logged_in(false) == false) //if not logged in
	{
		$tracking_code = Load::general_settings(TRACKING_CODE);
	}
	$page = str_replace('<!--%FOOTER%-->', $content.$tracking_code, $page);
	return $page;
}

function fill_out_javascript($page)
{
	$to_include = Message::get_language_JS_block();
	$to_include .= '<script LANGUAGE=JavaScript type="text/javascript" SRC="common/contentServer/contentServer.js"></script>';
	//tacking on the rss autodiscovery here is a bit dodgy but works
	$to_include .= '<link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php">';
	$page = str_replace('<!--%JS%-->', $to_include, $page);
	return $page;
}

function fill_out_style($page, $template_name = null)
{
	//the style token doesn't need to be in HTML comments
	//because it is already located within HTML comments in template.htm
	//it's best not to put HTML comments within themselves as it
	//may not be valid HTML

	//require_once('common/contentServer/template_control.php');
	$style = null;
	if(!empty($template_name))
	{
	   //had to add check against template_css being null
	   //if using custom template then template css is null
	   $template_css = get_path_to_template_css($template_name);
	   if(empty($template_css) == false)
	   {
		  $style = file_get_contents(get_path_to_template_css($template_name));
	   }
	}
	else
	{
		$style = file_get_contents('site/site.css');
	}

	//$common_buttons = @file_get_contents('common/buttons.css');
	//$site_buttons = @file_get_contents('site/buttons.css');

	//$page = str_replace('%STYLE%', $style.$common_buttons.$site_buttons, $page);
	$page = str_replace('%STYLE%', $style, $page);
	
	return $page;
}

function fill_out_controls($page, $controls)
{
/*
	$controls = '<div style="	background-color : #305B7E;
	color : black;
	padding : 5px;
	border : solid black 2px;
	font-family : arial;
	font-size:smaller;
	font-weight:bold;">' . $controls . '</div>';
	*/
	$page = str_replace('<!--%CONTROLS%-->', $controls, $page);
	return $page;
}


/*
DONE change edit.php to not append google ads even if they should be displayed. google adsense block and fckeditor dont appear to play well together. sometimes neither loads. sometimes one of the other loads.
*/
function fill_out_centre($page, $centre_content, $display_ads = true)
{
	//$page = str_replace('<!--%CENTRE%-->', $centre_content.$ads, $page);
	$page = str_replace('<!--%CENTRE%-->', $centre_content, $page);
	return $page;
}

function fill_out_title($page, $title, $description=null)
{
	$page = str_replace('<!--%TITLE%-->', $title, $page);
	return $page;
}

function fill_out_description($page, $description=null)
{
	if($description)
	{
		$desc_tag = '<meta name="description" content="'.$description.'">';
		$page = str_replace('<!--%META_DESCRIPTION%-->', $desc_tag, $page);
	}
	else
	{
		$page = str_replace('<!--%META_DESCRIPTION%-->', null, $page);
	}
	return $page;
}

function fill_out_affinity_footer($page, $footer_contents)
{
	$snippet = Load::general_settings(BRANDING);
	$page = str_replace('<!--%AFFINITY%-->', "$snippet<br>$footer_contents", $page);
	return $page;
}

function fill_out_feature_image($page, $template_name = null)
{
	$image_path = null;

	if(!empty($template_name))
	{
		$image_path = get_template_setting(FEATURE_IMAGE, $template_name);
	}
	else
	{
		//$image_path = get_general_setting(FEATURE_IMAGE);
		$gs = Load::general_settings();
		$image_path = $gs->get(FEATURE_IMAGE);
	}
	
	$page = str_replace('<!--%FEATURE_IMAGE%-->', $image_path, $page);
	return $page;
}

/*function get_editor($id, $raw, $width='100%')
{
	$oFCKeditor = new FCKeditor($id);
	$oFCKeditor->Value = $raw;
	$oFCKeditor->BasePath = 'common/contentServer/FCKeditor/';
	$oFCKeditor->Height = '500';
	$oFCKeditor->Width = $width;
	//$oFCKeditor->setLang(get_site_language());
	$oFCKeditor->Config['AutoDetectLanguage'] = false ;
	$oFCKeditor->Config['DefaultLanguage'] = get_site_language();

	return $oFCKeditor;
}*/

/*function get_editor_html($id, $raw, $width='100%')
{
	require_once('common/contentServer/FCKeditor/fckeditor.php');
	$oFCKeditor = get_editor($id, $raw, $width);
//	$IE_message = <<<END
//<!--[if IE]>
//Internet Explorer detected.  IE has some problems we're figuring out.  If the editor doesnt load below then use FireFox (Its free, fast and secure) <a target=_blank href="http://www.mozilla.com/en-US/firefox">Download</a><br />
//<![endif]-->
//END;
	return $noScript = GetNoscriptMessage() . $oFCKeditor->CreateHtml();
}*/

?>
