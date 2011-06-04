<?php
abstract class PaymentProcessor extends SaveableBase
{
    function PaymentProcessor()
    {
        //Start with class name. Replace underscores with spaces. This makes the storage point.
        $storage_name = $class_name = str_replace('_', ' ', get_class($this));
        parent::SaveableBase("site/config/payment/$storage_name.php");
    }
    //get a description of the payment processor
    public abstract function get_processor_description();
    //get the instructions for the processor
    public abstract function get_instructions();
    //get the displayable controls to make a purchase with this processor
    public abstract function get_payment_controls($poduct_name, $unit_price, $product_id);
    //get the keys of all the processors
	public static function get_processor_keys()
	{
	    static $keys; //only need one of these for all instances
	    //if list of keys not loaded
	    if($keys == null)
	    {
			$keys = Globals::get_dirs_in_a_dir('common/payment_processors');
			natcasesort($keys);
		}
		return $keys;
	}
	//does the processor exist
	public static function exists($pp_key)
	{
		return in_array($pp_key, Addon::get_processor_keys());
	}
}
?>