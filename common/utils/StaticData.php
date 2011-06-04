<?php
class StaticData
{
	public static function get_currencies()
	{
		$c = array
		(
			'AUD' => 'Australian Dollars',
			'EUR' => 'Euros',
			'GBP' => 'UK Pound Sterling',
			'USD' => 'US Dollars',
		);
		return $c;
	}
	public static function get_languages()
	{
		return array('en','fr','es','pt','zh-cn','hi','ru','th','ko','id','sr');
	}
}
?>