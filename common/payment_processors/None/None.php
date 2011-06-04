<?php
class None extends PaymentProcessor
{
	public function get_processor_description()
	{
	    return 'Use this processor if you don\'t want to receive payments';
	}
    public function get_instructions()
    {
        return '<p>There are no instructions for this payment processor. If you are deciding on which payment processor to use we recommend PayPal. <a href=payment_admin.php?selected_processor=PayPal>Click here to use PayPal</a></p>';
	}
	protected function get_default_settings()
	{
	  	return array();
	}
	public function validate($setting_name, $setting_value)
	{
		return null;
	}
	public function get_payment_controls($poduct_name, $unit_price, $product_id)
	{
		return null;
	}
}
?>