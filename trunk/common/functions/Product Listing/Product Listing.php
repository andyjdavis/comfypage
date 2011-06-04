<?php
class Product_Listing extends Addon
{
	function __construct()
	{
		parent::__construct(false);
	}
	public function get_addon_description()
    {
		return 'Show visitors your full list of products that are available for online sale.';
	}
    public function get_instructions()
    {
  		return null;
	}
	public function get_first_stage_output($additional_inputs)
    {
		$prs = Load::product_store();
		$pgs = Load::payment_general_settings();
		$products = $prs->load_all_products();
		$product_id_url_param = PRODUCT_ID_URL_PARAM;
		$currency_code = $pgs->get(PAYMENT_CURRENCY);
		$currency_name = $pgs->get_name_of_currency_in_use();
		$row_count = 0;
		$cells_per_row = 3; //how many products across page
		$payment_processor = Load::payment_processor(); //get payment processor in use
		if(empty($products))
		{
			$output = '<div style="text-align:center;">No products currently available</div>';
		}
		else
		{
			$output = '<table cellpadding="5" cellspacing="3" align="center" border="0">';
			foreach($products as $product)
			{
			    $product_id = $product->id;
			    $product_title = $product->get(PRODUCT_TITLE);
			    $price = Format::price($product->get(PRODUCT_PRICE), $currency_code, true, false);
				if($row_count % $cells_per_row == 0)
				{
					$output .= '<tr>';
				}
				$controls = $payment_processor->get_payment_controls($product->get(PRODUCT_TITLE), Format::price($product->get(PRODUCT_PRICE), $currency_code), $product->get(PRODUCT_SELLER_PRODUCT_ID));
				$img = $product->get_thumb_html();
				$output .= <<<END
	<td valign=top width=150 align=center>$img<br><a href=product.php?$product_id_url_param=$product_id>$product_title</a><br>$price<br>$controls</td>
END;
				if($row_count % $cells_per_row == $cells_per_row - 1)
				{
					$output .= '</tr>';
				}
				$row_count++;
			}
			$output .= <<<END
			<tr>
			<td colspan=2>Prices are in <b>$currency_name</b></td>
			</tr>
	</table>
END;
		}
		return $output;
    }
    protected function get_default_settings()
	{
		return array();
	}
	public function validate($setting_name, $setting_value)
	{
	    return null;
	}
}
?>