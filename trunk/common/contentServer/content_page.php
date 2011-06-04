<?php
require_once('common/contentServer/page.php');
include_once('common/menu.php');

function get_content_page($content_id, $template_name = null, $hide_menu = false, $msg = null)
{	
    $content = Load::page($content_id);
    $site_id = Load::general_settings(NEW_SITE_ID);

    $last_modified_string = '';
    if ($content->get(CONTENT_SHOW_DATE)) {
	$last_modified_string = '<div class="last_modified">'.substr($content->get(LAST_MODIFIED),0,-15).'</div>';
    }

    $raw = add_form($site_id, $content_id, $content->get(RAW_CONTENT));

    $function_content = Addon::execute($content->get(CONTENT_DOODAD), $content_id);

    $central_content = Message::get_message_display($msg).$last_modified_string.$raw.$function_content.$content->get(CONTENT_EMBED);

    $affinity_footer = null;
    if( !$hide_menu ) {
	$affinity_footer .= '<a href="admin.php"><span id="afsm" class="translate_me">Site manager</span></a>&nbsp;&nbsp;&nbsp;';
	$affinity_footer .= '<a href="edit.php?' . CONTENT_ID_URL_PARAM . '=' . $content_id . '"><span id=afep class=translate_me>Edit page</span></a>&nbsp;&nbsp;&nbsp;';
	$affinity_footer .= '<a href="rss.php"><span id=afrss class=translate_me>RSS</span></a>&nbsp;&nbsp;&nbsp;';
	//we could use a rand num to output 1 of several links back to cp.com thus hitting multiple key phrases
	$affinity_footer .= '<a href="http://www.comfypage.com"><span id=afgfs class=translate_me>Build a website</span></a>';
    }

    $page = get_template($template_name);
    $page = fill_out_margins($page, $content_id);
    $page = fill_out_style($page, $template_name);
    $page = fill_out_centre($page, $central_content, !Login::logged_in(false));
    $page = fill_out_title($page, $content->get(CONTENT_TITLE));
    $page = fill_out_description($page, $content->get(CONTENT_DESC));
    $page = fill_out_affinity_footer($page, $affinity_footer);
    $page = fill_out_feature_image($page, $template_name);
    $page = fill_out_javascript($page);

    //if a function isn't posting back to itself
    /*if(IsPostBack() == false)
    {
	    //cache.php takes care of whether caching should happen
	    //with regards to USE_CACHE and being logged in
	    add_content_page_to_cache($content_id, $page);
    }*/
//	$members = ;

    if( Login::logged_in(false) && empty($template_name) == true && !$hide_menu )
    {
	    $menu = new Menu;
	    $menu->add_item('Edit page', "edit.php?".CONTENT_ID_URL_PARAM."=$content_id");
	    $menu->add_item('Edit menu', 'function.php?function=Page%20Listing');
	    $menu->add_item('SEO', "seo.php?content_id=$content_id");
	    $menu->add_item('Password protection', 'members.php');

	    if($content_id != INDEX)
	    {
		$menu->add_item('Delete page', "admin.php?delete=$content_id");
	    }

	    require_once('common/contentServer/template_control.php');
	    //require_once('common/permissions.php');
	    //$permissions = new Permissions;
	    $permissions = Load::permission_settings();
	    $controls_template = $permissions->check_permission(TEMPLATE_ALLOWED);
	    if(get_template_setting(FEATURE_IMAGE)!=null && $controls_template)
	    {
		$menu->add_item('Feature image', "feature_image.php?content_id=$content_id");
	    }

	    $on_this_page = null;
	    if($content_id == INDEX) $on_this_page = 'Home page';
	    $controls = $menu->get_menu($on_this_page);
	    $page = fill_out_controls($page, $controls);
    }
    else if(Load::member_settings()->logged_in() && Login::logged_in(false) == false)
    {
	    $menu = new Menu;
	    $menu->add_item('Log out', 'members_logout.php');
	    $controls = $menu->get_menu(null, true);
	    $page = fill_out_controls($page, $controls);
    }

    return $page;
}

function get_content_edit_page($content_id)
{
	//$content = load_content($content_id);
	$content = Load::page($content_id);
	$function_name = $content->get(CONTENT_DOODAD);
	$function_content = Addon::execute($function_name, $content_id);
	$editor = get_editor_in_form($content, 'edit.php', '100%', $content->get(CONTENT_TITLE));
	$menu = new Menu;
	//$menu->add_item('Discard changes', $content_id.'.htm');
	$menu->add_item('Discard changes', "index.php?content_id=$content_id");
	$controls = $menu->get_menu();

	$page = get_template();
	$page = fill_out_margins($page, $content_id);
	$page = fill_out_style($page);
	$page = fill_out_centre($page, $editor . $function_content, false);
	$page = fill_out_title($page, $content->get(CONTENT_TITLE));
	$page = fill_out_javascript($page);
	$page = fill_out_controls($page, $controls);
	$page = fill_out_feature_image($page);
	//tell css.php which section is being edited
	//$_SESSION[EDITTING_MARGIN] = CENTRE;
	return $page;
}

function get_margin_edit_page($edit_target)
{
	$editing = false;
	$lmargin = Load::page(LEFT_MARGIN);
	$rmargin = Load::page(RIGHT_MARGIN);
	$header = Load::page(HEADER);
	$footer = Load::page(FOOTER);
	//local variables that may be overwritten with the editor
	$lmargin_content = $lmargin->get(RAW_CONTENT);
	$rmargin_content = $rmargin->get(RAW_CONTENT);
	$header_content = $header->get(RAW_CONTENT);
	$footer_content = $footer->get(RAW_CONTENT);
	if($edit_target != -1)
	{
		$editing = true;
		//set width of editor so it doesn't get squished out of existence
		$force_width = 500;
		switch($edit_target)
		{
			case LEFT_MARGIN:
				$lmargin_content = get_margin_editor_in_form($lmargin, 'margins.php', $force_width);
				break;
			case RIGHT_MARGIN:
				$rmargin_content = get_margin_editor_in_form($rmargin, 'margins.php', $force_width);
				break;
			case HEADER:
				$header_content = get_margin_editor_in_form($header, 'margins.php');
				break;
			case FOOTER:
				$footer_content = get_margin_editor_in_form($footer, 'margins.php');
				break;
		}
		//tell css.php what margin I'm editting
		//$_SESSION[EDITTING_MARGIN] = $edit_target;
	}
	if($editing)
	{
		$centreControls = Message::get_message_display('Remember to save') . '</center>';
		//track_user('Editing margin');
	}
	else
	{
  		$helpLink = '<p style="text-align:center;">'.Message::get_help_link(3345824, 'How does this work?').'</p>';
		$centreControls = '<table width=450 align=center cellpadding=5 cellspacing=2 style="margin-top:1em;text-align:center; border:solid black 1px; background:white;">';
		$centreControls .= '<tr>';
		$centreControls .= '<th style="text-align:left;color:#C0C0C0;" colspan=3><span id=cpce class=translate_me>Edit</span></th>';
		$centreControls .= '</tr>';
		$centreControls .= '<tr><td /><td><a style="color:blue;" href=margins.php?edit=' . HEADER . '><span id=cpch class=translate_me>header</span></a><td /></td></tr>';
		$centreControls .= '<tr>';
		$centreControls .= '<td><a style="color:blue;" href=margins.php?edit=' . LEFT_MARGIN . '><span id=cpclm class=translate_me>left margin</span></a></td>';
		$centreControls .= '<td />';
		$centreControls .= '<td><a style="color:blue;" href=margins.php?edit=' . RIGHT_MARGIN . '><span id=cpcrm class=translate_me>right margin</span></a></td>';
		$centreControls .= '</tr>';
		$centreControls .= '<tr><td /><td><a style="color:blue;" href=margins.php?edit=' . FOOTER . '><span id=cpcf class=translate_me>footer</span></a><td /></td></tr>';
		$centreControls .= '</table>';
		$header_doodad_choice = get_content_doodad_choice($header);
		$left_doodad_choice = get_content_doodad_choice($lmargin);
		$right_doodad_choice = get_content_doodad_choice($rmargin);
		$footer_doodad_choice = get_content_doodad_choice($footer);
		$JS_block = Message::get_language_JS_block();
		$centreControls .= <<<END
		$JS_block
		<script LANGUAGE="JavaScript" SRC="common/contentServer/contentServer.js"></script>
 <form method="post">
 <input type="hidden" name="addon" value="1">
<table width="450" align="center" cellpadding="5" cellspacing="2" style="margin-top:1em;text-align:center; border:solid black 1px; background:white;">
<tr>
<th style="color:C0C0C0;text-align:left;" colspan="2">Add-ons</th>
</tr>
<tr>
	<td colspan="2"><span id="ha" class="translate_me">header</span><br>$header_doodad_choice</td>
</tr>
<tr>
	<td width="200"><span id="lma" class="translate_me">left margin</span><br>$left_doodad_choice</td>
	<td width="200"><span id="rma" class="translate_me">right margin</span><br>$right_doodad_choice</td>
</tr>
<tr>
	<td colspan="2"><span id=fa class=translate_me>footer</span><br>$footer_doodad_choice</td>
</tr>
<tr>
	<td align="left" colspan="2"><span id=sa class=translate_me><input type=submit value="Save add-ons" name=save_doodads></span></td>
</tr>
</table>
</form>
$helpLink
END;
	}
	$menu = new Menu;
	if($editing)
	{
		$menu->add_item('Discard changes', 'margins.php');
	}
	$controls = $menu->get_menu();
  	$page = get_template();
	$header_function_content = Addon::execute($header->get(CONTENT_DOODAD), null);
	$left_function_content = Addon::execute($lmargin->get(CONTENT_DOODAD), null);
	$right_function_content = Addon::execute($rmargin->get(CONTENT_DOODAD), null);
	$footer_function_content = Addon::execute($footer->get(CONTENT_DOODAD), null);
	$lmargin_embed = $lmargin->get(CONTENT_EMBED);
	$rmargin_embed = $rmargin->get(CONTENT_EMBED);
	$header_embed = $header->get(CONTENT_EMBED);
	$footer_embed = $footer->get(CONTENT_EMBED);
	$page = fill_out_header($page, $header_content.$header_function_content.$header_embed);
	$page = fill_out_left_margin($page, $lmargin_content.$left_function_content.$lmargin_embed);
	$page = fill_out_right_margin($page, $rmargin_content.$right_function_content.$rmargin_embed);
	$page = fill_out_footer($page, $footer_content.$footer_function_content.$footer_embed);
	$page = fill_out_style($page);
	$page = fill_out_centre($page, $centreControls, false);
	$page = fill_out_title($page, 'Edit the margins and remember to save');
	$page = fill_out_controls($page, $controls);
	$page = fill_out_feature_image($page);
	return $page;
}

function get_margin_editor_in_form($content, $target, $width='100%')
{
	/*
	$images_help = Message::get_help_link(8029795, 'Help inserting images');
	$links_help = Message::get_help_link(9918435, 'Help linking to other pages');
	$embedded_help = Message::get_help_link(9918461, 'Add videos, maps, hit counter, a forum, and more');
	$help_links = <<<END
<p align="center">$images_help &nbsp;&nbsp; $links_help</p><p style="text-align:center;">$embedded_help </p>
END;
*/
	$editor_html = $content->get_input(RAW_CONTENT);
	$embed_textbox = '<b><span class="translate_me" id="pe">Embed Service</span></b> <a target="_blank" href="http://help.comfypage.com/index.php?content_id=9918461">get embed code</a><br />'.$content->get_input(CONTENT_EMBED);
	//$function = $content->get_input(CONTENT_DOODAD);
	$save_button = '<span id=editsave class=translate_me><input style="font-size:larger;" type=submit value=" Save "></span>';
	$hidden_input = '<input type="hidden" name="'.CONTENT_ID_URL_PARAM.'" value="' . $content->id .'">';
	$target_name = EDIT_TARGET;
	$form = <<<END
<form action="$target" method="post">
$hidden_input
<p>$save_button</p>
$editor_html
<p>$embed_textbox</p>
<p>$save_button</p>
<input type="hidden" name="$target_name" value="$content->id" />
</form>
END;
	return $form;
}

function get_editor_in_form($content, $target, $width='100%')
{
	$images_help = Message::get_help_link(8029795, 'Help inserting images');
	$links_help = Message::get_help_link(9918435, 'Help linking to other pages');
	$embedded_help = Message::get_help_link(9918461, 'Add videos, maps, hit counter, a forum, and more');
	$help_links = <<<END
<p align="center">$images_help &nbsp;&nbsp; $links_help</p><p style="text-align:center;">$embedded_help </p>
END;
	//$editor_html = get_editor_html($content->id, $content->get(RAW_CONTENT), $width);
	$editor_html = $content->get_input(RAW_CONTENT);
	$title_textbox = '<b><span class="translate_me" id="pt">Page title</span></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$content->get_input(CONTENT_TITLE);
	$desc_textbox = '<b><span class="translate_me" id="pd">Description</span></b> '.$content->get_input(CONTENT_DESC);
	$embed_textbox = '<b><span class="translate_me" id="pe">Embed Service</span></b> <a target="_blank" href="http://help.comfypage.com/index.php?content_id=9918461">get embed code</a><br />'.$content->get_input(CONTENT_EMBED);
	//$blog_post_checkbox = '<b><span class="translate_me" id="smc">Blog post</span></b> '.$content->get_input(CONTENT_SHOW_DATE);
	//$date_modified_editor = '<b><span class="translate_me" id="dme">Date written</span></b> '.$content->get_input(LAST_MODIFIED);
	$function_name = $content->get(CONTENT_DOODAD);
	$doodad = Load::addon($function_name);
	$addOnConfigLink = null;
	if($doodad && $doodad->requires_config())
	{
		$addOnConfigLink = '<a href="function.php?function='.$function_name.'" target="_blank"><span class="translate_me" id="ca">Configure '.$function_name.'</span></a>';
	}
	$addOnDropDown = "<span id=cpsf$content->id class=addonSelect>".$content->get_input(CONTENT_DOODAD).' <span id="addonConfigSpan">'.$addOnConfigLink.'</span></span>';
	$save_button = '<span id=editsave class=translate_me><input style="font-size:larger;" type=submit value=" Save "></span>';

	$hidden_input = '<input type="hidden" name="'.CONTENT_ID_URL_PARAM.'" value="' . $content->id .'">';
	$hidden_input .= HtmlInput::get_hidden_input($content->get_input_name(CONTENT_SEO_TARGET), $content->get(CONTENT_SEO_TARGET));

	$show_advanced_JS = <<<END
<script type="text/javascript">
function toggle_advanced()
{
	toggle_visibility('advancedsettings');
}
toggle_visibility("advancedsettings");
</script>
END;
	$form = <<<END
<form action="$target" method="post">
$hidden_input
<p>$save_button</p>
<p>$title_textbox</p>
<p><b><span class="translate_me" id="a">Add-on</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b> $addOnDropDown</p>
$editor_html
<a href="" id="advancedlink" onclick="Javascript:toggle_advanced();return false;">Advanced Settings</a>
<div id="advancedsettings" class="hide" >
<p>$desc_textbox</p>
<p>$embed_textbox</p>
</div>
<p>$save_button</p>
</form>
$show_advanced_JS
END;
	return $help_links.$form;
}

function get_content_doodad_choice($content)
{
	//$function_name = $content->get(CONTENT_DOODAD);
	//return '<span id="cpsf'.$contentId.'" class="addonSelect">'.Addon::get_addon_select_box($content->id, $function_name).'</span>';
	return '<span id="cpsf'.$content->id.'" class="addonSelect">'.$content->get_input(CONTENT_DOODAD).'</span>';
}

?>