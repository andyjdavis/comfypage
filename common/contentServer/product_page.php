<?php
require_once('common/contentServer/page.php');
require_once('common/menu.php');
function get_product_affinity_footer($product_id)
{
	$affinity_footer = '<a href=admin.php><span id=fsm class=translate_me>Site manager></span></a>&nbsp;&nbsp;&nbsp;';
	$affinity_footer .= '<a href=product_edit.php?' . PRODUCT_ID_URL_PARAM . '=' . $product_id . '><span id=fep class=translate_me>Edit product</span></a>&nbsp;&nbsp;&nbsp;';
	$affinity_footer .= '<a href=http://www.comfypage.com/><span id=fgafcp class=translate_me>Get a free ComfyPage website</span></a>';
	return $affinity_footer;
}

function get_product_page($product_id)
{
	if(!empty($cache_file_contents))
	{
		return $cache_file_contents;
	}

	//$product = load_product($product_id);
	$product = Load::product($product_id);

	$affinity_footer = get_product_affinity_footer($product_id);
	$central_content = get_product_display($product);

	$page = get_template();
	$page = fill_out_margins($page, $product_id);
	$page = fill_out_style($page);
	$page = fill_out_centre($page, $central_content);
	$page = fill_out_title($page, $product->get(PRODUCT_TITLE));
	$page = fill_out_affinity_footer($page, $affinity_footer);
	$page = fill_out_feature_image($page);

	//cache without the logged in controls
	//cache.php takes care of whether caching should happen
	//with regards to USE_CACHE and being logged in
//	add_product_page_to_cache($product_id, $page);

	//if logged in
	if(Login::logged_in(false))
	{
		//$controls = get_menu_item('Site manager', 'admin.php');
		//$controls .= get_menu_item('Shop manager', 'product_admin.php');
		//$controls .= get_menu_item('Edit product', 'product_edit.php?' . PRODUCT_ID_URL_PARAM . '=' . $product_id);
		//$controls .= get_menu_item('Edit borders', 'margins.php');
		//$controls .= get_menu_item('ComfyPage Help', 'http://help.comfypage.com', true);
		//$controls .= get_menu_item('Log out', 'logout.php');
		$menu = new Menu;
		$menu->add_item('Edit product', 'product_edit.php?' . PRODUCT_ID_URL_PARAM . "=$product_id");
		$menu->add_item('Edit borders', 'margins.php');
		$controls = $menu->get_menu();
		$page = fill_out_javascript($page);
		$page = fill_out_controls($page, $controls);
	}

	return $page;
}

function get_product_edit_page($product_id, $product = null, $errors = null)
{
	if($product == null)
	{
		//$product = load_product($product_id);
		$product = Load::product($product_id);
	}
	$affinity_footer = get_product_affinity_footer($product_id);
	$central_content = get_product_editor($product);
	if(!empty($errors))
	{
		$central_content = $errors . $central_content;
	}
	$page = get_template();
	$page = fill_out_margins($page, $product_id);
	$page = fill_out_style($page);
	$page = fill_out_centre($page, $central_content, false);
	$page = fill_out_title($page, $product->get(PRODUCT_TITLE));
	$page = fill_out_affinity_footer($page, $affinity_footer);
	$page = fill_out_feature_image($page);

	//if logged in
	if(Login::logged_in(false))
	{
		//$controls = get_menu_item('Site manager', 'admin.php');
		//$controls .= get_menu_item('Shop manager','product_admin.php');
		//$controls .= get_menu_item('Discard changes', 'product.php?' . PRODUCT_ID_URL_PARAM . '=' . $product_id);
		//$controls .= get_menu_item('ComfyPage Help', 'http://help.comfypage.com', true);
		//$controls .= get_menu_item('Log out','logout.php');
		$menu = new Menu;
		$menu->add_item('Discard changes', 'product.php?' . PRODUCT_ID_URL_PARAM . "=$product_id");
		$controls = $menu->get_menu();
		$page = fill_out_javascript($page);
		$page = fill_out_controls($page, $controls);
	}

	//tell css.php which section is being edited
	//$_SESSION[EDITTING_MARGIN] = CENTRE;

	return $page;
}

function get_product_property_row($key, $value, $show_emptys)
{
	$display = '';

	if(($value != null || trim($value) != '') || $show_emptys == true)
	{
		$display .= '<tr>';
		$display .= '<td align=right>';
		$display .= $key;
		$display .= '</td>';
		$display .= '<td style="font-weight:bold;">';
		$display .= $value;
		$display .= '</td>';
		$display .= '</tr>';
		return $display;
	}

	return $display;
}

function get_product_property_input($name, $value)
{
	return '<input type=text id="'.$name.'" value="' . $value . '" name="' . $name . '">';
}

function get_product_display($product)
{
	$pgs = Load::payment_general_settings();
	$pp_name = $pgs->get(SELECTED_PROCESSOR);
	$payment_processor = Load::payment_processor($pp_name);
	$currency = $pgs->get(PAYMENT_CURRENCY);
	$img = $product->get_image_html();
	$display = "<div>{$product->get(PRODUCT_RAW)}</div>";
	$display .= '<table align="center" cellpadding="6" cellspacing="6" border="0">';
	$display .= get_product_property_row('', $img, false);
	$display .= get_product_property_row('Name', $product->get(PRODUCT_TITLE), false);
	$display .= get_product_property_row('Price', Format::price($product->get(PRODUCT_PRICE), $currency, true, true), false);
	$display .= get_product_property_row('Product code', $product->get(PRODUCT_SELLER_PRODUCT_ID), false);
	$display .= '</table>';
	if($payment_processor->is_valid(true))
	{
	    $temp = null;
	    if(Login::logged_in(false))
		{
			$temp = $payment_processor->get_payment_controls($product->get(PRODUCT_TITLE), Format::price($product->get(PRODUCT_PRICE), $currency), $product->get(PRODUCT_SELLER_PRODUCT_ID));
		}
		$display .= <<<END
<table align="center" cellpadding="6" cellspacing="6" border="0">
	<tr>
		<td>$temp</td>
	</tr>
</table>
END;
	}
	else
	{
	    if(Login::logged_in(false))
	    {
			$msg = <<<END
You have not configured your options for receiving payments.<br /><a href=payment_admin.php>Configure them now</a>
END;
		}
		else
		{
		    $msg = <<<END
<p>Products not available at this time.</p><p>Website owner should <a href=payment_admin.php>log in</a></p>
END;
		}
		$display .= Message::get_error_display($msg);
	}
	return $display;
}

define('TITLE_PARAM', 'title');
define('SELLER_ID_PARAM', 'seller_id');
define('PRICE_PARAM', 'price');
define('IMAGE_PARAM', 'image');

function get_product_editor($product)
{
	if(!$product)
	{
		return '<span id="cpnullprod" class="translate_me">You are trying to edit a product that doesn\'t exist.<br />  <a href="product_admin.php">Return to the Shop Manager to create your product</a>';
	}
	//require_once('common/general_settings.php');
	$thumb_path = $product->get_thumb_path();
	$img_path = $product->get(PRODUCT_IMAGE);
	$img_id = $product->get_input_name(PRODUCT_IMAGE);
	//js for image selection
	$js = <<<END
	<script language="Javascript">
		function SelectFile()
		{
			var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes,scrollbars=yes" ;
			sOptions += ",width=" + 640 ;
			sOptions += ",height=" + 480 ;
			window.open( 'files.php?select=1', 'files', sOptions );
		}
		function SetUrl(url)
		{
		    //set the input textbox to display the path to the file they selected
			document.getElementsByName("$img_id")[0].value = url;
		}
		</script>
END;
		$img = "$js".$product->get_thumb_html();
		$display = '<form method="post">';
		$display .= '<table align="center" cellpadding="6" cellspacing="6" border="0">';
		$display .= get_product_property_row('<span id=na class=translate_me>Name</span>', $product->get_input(PRODUCT_TITLE), true);
		$display .= get_product_property_row("Picture $img", $product->get_input(PRODUCT_IMAGE) . ' <a style="font-size:smaller;" href="#" onclick="Javascript:SelectFile();">Select</a>', true);
		$display .= get_product_property_row('<span id=pr class=translate_me>Price</span>', $product->get_input(PRODUCT_PRICE), true);
		$display .= get_product_property_row('<span id=pc class=translate_me><font style="font-size:small;">(optional)</font> Product Code</span> ', $product->get_input(PRODUCT_SELLER_PRODUCT_ID), true);
		$display .= '</table>';
		$display .= '<span id=pd class=translate_me>Product Description</span> <div>' . $product->get_input(PRODUCT_RAW) . '</div>';
		$display .= '<span id="save" class="translate_me"><input style="font-size:larger;" type="submit" value=" Save "></span>';
		$display .= "<input type=\"hidden\" value=\"$product->id\" name=\"" . PRODUCT_ID_URL_PARAM . "\">";
		$display .= '</form>';
		return $display;
}

?>