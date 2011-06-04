<?php
require_once('common/class/settings.class.php');
define('PAYMENT_CURRENCY', 'currency');
define('PAYMENT_SHIPPING', 'shipping');
define('SELECTED_PROCESSOR', 'selected_processor');
class PaymentGeneralSettings extends Settings
{
	function PaymentGeneralSettings($loc = 'site/config/general_payment.php')
	{
	   parent::Settings($loc);
	}
	function get_default_settings()
	{
		$s = array
		(
	        PAYMENT_CURRENCY => 'AUD',
	        SELECTED_PROCESSOR => 'None',
	        PAYMENT_SHIPPING => true,
		);
		return $s;
	}
	protected function get_description_dictionary()
	{
		return array
		(
		PAYMENT_CURRENCY => 'The currency your shop will use. When you change currency the prices of your products will not be altered to reflect the new currency. You will need to change the product prices seperately.',
		PAYMENT_SHIPPING => 'Should a shipping charge be applied to all of your products?',
		);
	}
    function validate($setting_name, $setting_value)
    {
       	switch($setting_name)
		{
			case PAYMENT_CURRENCY:
			{
			    require_once('common/utils/StaticData.php');
			    $legit_currencies = StaticData::get_currencies();
				if(isset($legit_currencies[$setting_value]))
			    {
					return null;
				}
				else
				{
					return "The currency is not supported";
				}
			}
			case SELECTED_PROCESSOR:
			{
			    $legit_processors = PaymentProcessor::get_processor_keys();
				if(in_array($setting_value, $legit_processors))
			    {
					return null;
				}
				else
				{
					return "Payment processor not supported";
				}
			}
			default :
			{
				return null;
			}
		}
    }
    protected function get_input_internal($setting_name, $setting_value)
    {
        switch($setting_name)
		{
			case PAYMENT_CURRENCY:
			{
			    require_once('common/utils/StaticData.php');
			    $allowed_currencies = StaticData::get_currencies();
				return HtmlInput::get_select_input($setting_name, $setting_value, $allowed_currencies);
			}
			case PAYMENT_SHIPPING:
			{
				return HtmlInput::get_checkbox_input($setting_name, $setting_value);
			}
			case SELECTED_PROCESSOR:
			{
			    //turn list of payment processors into select input options list
			    $options = array();
			    foreach(PaymentProcessor::get_processor_keys() as $pp)
			    {
			        $options[$pp] = $pp;
				}
				return HtmlInput::get_select_input($setting_name, $setting_value, $options);
			}
    		default :
    		{
    		    return parent::get_input_internal($setting_name, $setting_value);
    		}
		}
    }
	function get_name_of_currency_in_use()
	{
	    require_once('common/utils/StaticData.php');
		$allowed_currencies = StaticData::get_currencies();
		return $allowed_currencies[$this->get(PAYMENT_CURRENCY)];
	}
}
?>