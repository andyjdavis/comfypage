<?php
define('DONATION_EMAIL', 'DONATION_EMAIL');
define('CURRENCY_CODE', 'CURRENCY_CODE');
define('RECEIVER_NAME', 'RECEIVER_NAME');
class Accept_Donations extends AddOn
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Accept donations through your ComfyPage. Your visitors can donate with a credit card or PayPal account.';
	}
    public function get_instructions()
    {
    	return '<p>You need a PayPal business account. It\'s free so <A HREF="https://www.paypal.com/au/mrb/pal=3VB4CYMY69EBL" target="_blank">sign up here</a>. Once you\'ve signed up tell us your details below.</p>';
	}
    public function get_first_stage_output($additional_inputs)
    {
		$email = $this->get(DONATION_EMAIL);
	    $currency_code = $this->get(CURRENCY_CODE);
	    $receiver_name = $this->get(RECEIVER_NAME);
	    $donor_default_country = '';
	    $target = 'target="_blank"';
		return <<<END
		<center><form action="https://www.paypal.com/cgi-bin/webscr" method="post" $target>
		<input type="hidden" name="cmd" value="_donations">
		<input type="hidden" name="business" value="$email">
		<input type="hidden" name="item_name" value="$receiver_name">
		<input type="hidden" name="no_shipping" value="0">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="$currency_code">
		<input type="hidden" name="tax" value="0">
		<input type="hidden" name="lc" value="$donor_default_country">
		<input type="hidden" name="bn" value="PP-DonationsBF">
		<input type="image" src="https://www.paypal.com/en_AU/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
		<img alt="" border="0" src="https://www.paypal.com/en_AU/i/scr/pixel.gif" width="1" height="1">
		</form></center>
END;
	}
	protected function get_default_settings()
	{
	    $s = array();
	  	$s[DONATION_EMAIL] = null;
	  	$s[CURRENCY_CODE] = 'AUD';
	  	$s[RECEIVER_NAME] = '';
	  	return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
		{
			case CURRENCY_CODE:
			{
				return Validate::currency($setting_value);
			}
			case RECEIVER_NAME:
			{
				if(empty($setting_value))
				{
					return 'You must supply a name to identify you to your donators';
				}
				break;
			}
			case DONATION_EMAIL :
			{
				return Validate::email($setting_value);
			}
			default :
			{
				return null;
			}
		}
	}
	function get_input_internal($setting_name, $setting_value)
	{
		switch($setting_name)
		{
			case CURRENCY_CODE:
			{
			    require_once('common/utils/StaticData.php');
				return HtmlInput::get_radio_input($setting_name, $setting_value, StaticData::get_currencies());
			}
			default :
			{
				return HtmlInput::get_text_input($setting_name, $setting_value);
			}
		}
	}
	protected function get_description_dictionary()
	{
		return array
		(
		DONATION_EMAIL => 'The email address used to access your PayPal account',
		CURRENCY_CODE => 'The currency your donations should be paid in',
		RECEIVER_NAME => 'Your legal name to be displayed to those donating to you',
		);
	}
}
?>