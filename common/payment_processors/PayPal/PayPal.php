<?php
define('PAYPAL_EMAIL', 'PayPal_Email_Address');
class PayPal extends PaymentProcessor
{
	public function get_processor_description()
	{
	    return 'An easy way for customers to pay you by credit card or with a PayPal account';
	}
    public function get_instructions()
    {
        return <<<END
<p>A <b>PayPal Business</b> account allows you to receive payments from credit cards or PayPal accounts. Enter and save your PayPal information below.</p>

<p>Click on the PayPal banner to sign up for a PayPal Business account</p>

<p style='text-align:center;'><!-- Begin PayPal Logo --><A HREF="https://www.paypal.com/au/mrb/pal=3VB4CYMY69EBL" target="_blank"><IMG  SRC="http://images.paypal.com/en_AU/i/bnr/paypal_mrb_banner.gif" BORDER="0" ALT="Sign up for PayPal and start accepting credit card payments instantly."></A><!-- End PayPal Logo --></p>
END;
	}
	protected function get_default_settings()
	{
		$settings = array();
		$settings[PAYPAL_EMAIL] = 'PayPal Email Address';
		return $settings;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
		{
			case PAYPAL_EMAIL:
			{
				return Validate::email($setting_value);
			}
			default :
			{
				return null;
			}
		}
	}
	protected function get_description_dictionary()
	{
		return array
		(
            PAYPAL_EMAIL => 'PayPal email address. The email address you used to sign up for PayPal. This address identifies you to PayPal.',
		);
	}
	public function get_payment_controls($poduct_name, $unit_price, $product_id)
	{
	    $pgs = Load::payment_general_settings();
		$currency = $pgs->get(PAYMENT_CURRENCY);
		$paypal_email = $this->get(PAYPAL_EMAIL);
		$shipping_setting = $pgs->get(PAYMENT_SHIPPING);
		if($shipping_setting == false)
		{
			$shipping = '1';
		}
		else
		{
	        $shipping = '2';
		}
		$add_to_cart = <<<END
<td>
<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="add" value="1">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="business" value="$paypal_email">
<input type="hidden" name="item_name" value="$poduct_name">
<input type="hidden" name="item_number" value="$product_id">
<input type="hidden" name="amount" value="$unit_price">
<input type="hidden" name="no_shipping" value="$shipping">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="$currency">
<input type="hidden" name="bn" value="PP-ShopCartBF">
<input type="submit" value="Add to Cart">
</form>
</td>
<td>
<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="business" value="$paypal_email">
<input type="submit" value="View Cart">
<input type="hidden" name="display" value="1">
</form>
</td>
END;
	$controls = <<<END
<table>
<tr>
$add_to_cart
</tr>
</table>
END;
		return $controls;
	}
}
?>