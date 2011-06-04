<?php
define('THERM_CURR', 'THERM_CURR');
define('THERM_TARGET', 'THERM_TARGET');
class Thermometer extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Display the progress of a campaign you are running to collect money, signatures, sales or anything else. This thermometer progress meter may be helpful.';
	}
    public function get_instructions()
    {
    	return 'Set your target and your current number.';
	}
	public function get_first_stage_output($additional_inputs, $email=null, $message=null)
    {
	    $current = $this->get(THERM_CURR);
	    $target = $this->get(THERM_TARGET);
	    $unit = 'none';
		return <<<END
<div style="text-align: center;"><img src='common/functions/Thermometer/thermo.php?max=$target&current=$current&unit=$unit'></div>
END;
    }
	protected function get_default_settings()
	{
	    $s = array();
	  	$s[THERM_CURR] = 1;
	  	$s[THERM_TARGET] = 100;
	  	return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
		{
			case THERM_TARGET:
			{
				if(empty($setting_value))
				{
					return 'Enter a target';
				}
				else if(!is_numeric($setting_value))
				{
					return 'Target must be a number';
				}
			}
			case THERM_CURR:
			{
				if(empty($setting_value))
				{
					return 'Enter your current progress';
				}
				else if(!is_numeric($setting_value))
				{
					return 'Current progress must be a number';
				}
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
			THERM_CURR => 'Your progress',
			THERM_TARGET => 'Your target',
		);
	}
}
?>