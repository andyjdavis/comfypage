<?php
abstract class UsersItem extends SaveableBase
{
	public $id;
    function UsersItem($saving_location, $id)
    {
        $this->id = $id;
        parent::SaveableBase($saving_location);
    }
	//ensure inputs have IDs that don't conflict with other inputs
	public function get_input_name($setting_name)
	{
		return $setting_name.'_'.$this->id;
	}
	//get the setting name from the input name
	protected function get_setting_name($input_name)
	{
		return substr($input_name, 0, strlen('_'.$this->id));
	}
}
?>