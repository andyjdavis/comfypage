<?php
//Base class that does grunt-work
//A Saveablebase object represents a set of values in an array that can be;
//saved to a location
//outputted as HTML input items
//validated
//processes postback
//only saves if needed
//only loads settings once per instance
//TODO implement use of cache
define('LAST_MODIFIED', 'last_modified');
define('LAST_MODIFIED_FORMAT_STRING', 'D, d M Y H:i:s O');
abstract class SaveableBase
{
    //child classes must define $save_location in their constructor
    private $save_location;
    //and default settings
    private $default_settings = array();
    //array of settings loaded from DB
    private $settings;
    //true when settings have been loaded
    private $settings_loaded = false;
    //true if unsaved settings in the array
    protected $save_required = false;
    protected $errors;
    private $working_dir;
    //set this to false to use an absolute save location
    //when false the working dir is not appended
    public $use_working_dir = true;

    protected function SaveableBase($saving_location)
    {
        //need to remember the dir we are working in
        //because on destruciton the working dir changes to the web server root
        //see http://us2.php.net/manual/en/language.oop5.decon.php#56996
        $this->working_dir = dirname($_SERVER["SCRIPT_FILENAME"]);
        $this->save_location = $saving_location;
	//start with default settings
        $this->default_settings = $this->get_default_settings();
	$this->default_settings[LAST_MODIFIED] = date(LAST_MODIFIED_FORMAT_STRING); //add last modified value
        $this->settings = $this->default_settings;
	//saved settings will be loaded later if needed
    }
    //destructor called when the object is unloaded
    function __destruct()
	{
	    //save if changes have been made
    	$this->commit();
   	}
    //return an array of key-value pairs that represent the setting name and the setting's default value
    protected abstract function get_default_settings();
    //validate the item and return an error message. Return null if valid.
    public abstract function validate($item_name, $item_value);
    //returns an associative array of setting descriptions keyed on the setting name
    //override this in a child class to provide custom description settings
    protected function get_description_dictionary()
    {
		return null;
	}
	//returns the setting description
    public function get_description($setting_name)
    {
        static $dict = null;
        //if the descriptions haven't been loaded
        if($dict == null)
        {
            //get the descriptions
            $dict = $this->get_description_dictionary();
		}
		//if there is a description for the setting
        if(isset($dict[$setting_name]))
        {
            //return the description
			return $dict[$setting_name];
		}
		//return the default setting name
		return "($setting_name)";
	}
    //gets actual values for settings from cache or saved settings file
    private function load()
    {
        //indicate that settings have been loaded
        $this->settings_loaded = true;
    	//if the settings file doesn't exist
    	if(file_exists($this->save_location) == false)
    	{
    	   //settings array has been set to default settings previously
    	   //so there is no need to do anything else
    	   return;
    	}
    	//TODO if in cache
    	if(false)
    	{
            //TODO get $saved_settings array from cache
            //has to be called $saved_settings
        }
        else
        {
            //include saved_settings array from disk
            $include = require($this->save_location);
    	}
		//start with default values so if there are any new
		//settings that didn't exist in a previous saved settings file
		//they will now be included
		$default_settings_names = array_keys($this->default_settings);
		//$default_settings_names[] = LAST_MODIFIED;//this needs to be here or last modified won't be loaded from file
		foreach($default_settings_names as $setting_name)
		{
			//if the saved settings have a value for the setting
			if(isset($saved_settings[$setting_name]))
			{
                if(is_array($saved_settings[$setting_name]))
    			{
    				//TODO may need to strip slashes from every item in array
    				$this->settings[$setting_name] = $saved_settings[$setting_name];
    			}
    			else
    			{
    				$this->settings[$setting_name] = stripslashes($saved_settings[$setting_name]);
    			}
			}
		}
    }
    //get an HTML input name based on the setting name
    //child classes can override this to give their input elements unique IDs
    //needs to be public so it can be used to generate javascript
	public function get_input_name($setting_name)
	{
	    //this default behaviour is to use the setting name as the input name
		return $setting_name;
	}
	//inverse operation of get_input_name()
	protected function get_setting_name($input_name)
	{
		return $input_name;
	}
    //takes array of postback data (i.e. $_POST or $_GET), validates the data.
    //Updates this objects settings array with settings determined form the postback values
    //$setting_names - names of settings to be processed. Use to only process certain setting names
    public function process_post($posted_data, $setting_names = null)
    {
        //if haven't supplied a filtered set of settings names
        if(empty($setting_names))
        {
            //use all of them
            $setting_names = $this->get_setting_names();
        }
        $this->errors = array();
        $temp = null;
        foreach($setting_names as $setting_name)
    	{
            //if this changes also change get_input()
            $input_name = $this->get_input_name($setting_name);
    	    //if the setting exists in the array
            if(isset($posted_data[$input_name]))
            {
                $new_value = stripslashes($posted_data[$input_name]);
                $this->set($setting_name, $new_value);
            }
            //else if it is flagged as an array setting
            else if(isset($posted_data["array_$input_name"]))
            {
                //process the array setting
                //create new array, which is string split on newline character
                $new_values = explode("\n", stripslashes($posted_data["array_$input_name"]));
                $new_array = array();
                foreach($new_values as $new_value)
                {
                    //get rid of whitespace including new linews
                    $new_value = trim($new_value);
                    //if there is something left
                    if(empty($new_value) == false)
                    {
                        //save it in the array of values
                        $new_array[] = $new_value;
                    }
                }
                $this->set($setting_name, $new_array);
            }
            else //not set but there must be a setting called that so it was an unchecked checkbox
            {
                    $this->set($setting_name, false);
            }
    	}
    	if(count($this->errors) == 0)
    	{
            return true;
        }
        else
        {
            return false;
        }
    }
    //Derived classes can override this but can call this as a default option
    protected function get_input_internal($setting_name, $setting_value, $input_name = null)
    {
        //TODO as we haven't altered many of the child classes to pass through the input name
        //we are sort of working around the issue by setting it here
        //this is a workaround and should be fixed
        //make input name a required parameter and pass it through in all the child classes
        if(empty($input_name))
        {
            $input_name = $this->get_input_name($setting_name);
		}
        if(is_array($setting_value) == false)
        {
            return HtmlInput::get_text_input($input_name, $setting_value);
        }
        else
        {
            return HtmlInput::get_array_input("array_$input_name", $setting_value);
        }
    }
    //derived classes should not override this. Override get_input_internal().
    public function get_input($setting_name)
    {
        //include the file here so it never has to be included by derived classes
        //object-oriented file inclusion. YEAH!
        //require_once('common/utils/Html.php');
        $error = $this->get_error($setting_name);
		//if this(appending the id) changes also change process_post()
    	$input = $this->get_input_internal($setting_name, $this->get($setting_name), $this->get_input_name($setting_name));
    	if(empty($error))
    	{
            return $input;
        }
        else
        {
            return <<<END
$input <img align="top" width="20" src="common/images/cross.png" alt="$error" title="$error">
END;
        }
    }
    public function get_setting_names()
    {
        //use all of them
        $setting_names = array_keys($this->settings);
	//$setting_names = array_keys($this->get_default_settings());//do we want to use default settings so new settings will be listed

	//remove last modified date. Don't want it updated here.
	unset($setting_names[array_search(LAST_MODIFIED, $setting_names)]);
	return $setting_names;
    }
    public function get($setting_name)
	{
	   if($this->settings_loaded == false)
	   {
	       $this->load();
       }
       return $this->settings[$setting_name];
	}
	public function set($setting_name, $setting_value)
	{
	    $value_has_changed = false;
		if($this->settings_loaded == false)
		{
			$this->load();
		}
		//if a save hasn't been required as of yet
		if($this->save_required == false)
		{
			//check if this new value will require a save
			if($this->settings[$setting_name] != $setting_value)
			{
			    $value_has_changed = true;
				$this->save_required = true;
			}
		}
		$this->settings[$setting_name] = $setting_value;
		require_once('common/utils/Validate.php');
		$temp = $this->validate($setting_name, $this->settings[$setting_name]);
		if(empty($temp) == false)
		{
			//remember the error message and key it on the setting name
			$this->errors[$setting_name] = $temp;
		}
		return $value_has_changed;
	}
	public function get_error($setting_name)
	{
        if(isset($this->errors[$setting_name]))
        {
            return $this->errors[$setting_name];
        }
        return null;
    }
    public function commit() {
        if($this->save_required == false) {
            return;
        }
        if(count($this->errors) != 0)
        {
            return;
        }

        //add the modified date now that we know it will be saved
	$this->settings[LAST_MODIFIED] = date(LAST_MODIFIED_FORMAT_STRING);

        $array_as_string = var_export($this->settings, true);
    	$php_content = '<?php $saved_settings = '.$array_as_string.'; ?'.'>'; //not a variable name. I want '$saved_settings' be printed as '$saved_settings'
    	//use saved working dir to find full path because if called during destruction working dir will change
        $full_path = $this->save_location;
        if($this->use_working_dir)
        {
                $full_path = $this->working_dir.'/'.$this->save_location;
        }
    	file_put_contents($full_path, $php_content);
    	$this->save_required = false;
    }
    //gets all errors in a formatted string
    //if no errors then returns null
    public function get_error_message()
    {
        //require_once('common/utils/Html.php');
        $error_msg = null;
        if($this->errors != null)
        {
	        foreach($this->errors as $error)
	        {
	            $error_msg = Message::format_errors($error_msg, $error);
	        }
        }
        return $error_msg;
    }
    //add a new value to an array setting
    protected function add_item_to_array_setting($setting_name, $new_value, $array_key = null)
    {
        $mop = $this->get($setting_name);
        //if already in array
		if(in_array($new_value, $mop))
		{
		    //don't re-add
			return null;
		}
		if(empty($array_key))
		{
			//add the value
			$mop[] = $new_value;
		}
		else
		{
			$mop[$array_key] = $new_value;
		}
		//set the array
		$this->set($setting_name, $mop);
	}
	protected function delete_item_from_array_setting($setting_name, $delete_value)
	{
  		$mop = $this->get($setting_name);
		//get key of value
		$key = array_search($delete_value, $mop);
		//remove value
		unset($mop[$key]);
		//set array
		$this->set($setting_name, $mop);
	}
	public function is_valid($revalidate = false)
	{
	    if($revalidate)
	    {
	        require_once('common/utils/Validate.php');
	    	$setting_names = $this->get_setting_names();
			foreach($setting_names as $setting_name)
	    	{
	            $temp = $this->validate($setting_name, $this->get($setting_name));
	            if(empty($temp) == false)
	            {
	                //remember the error message and key it on the setting name
	                $this->errors[$setting_name] = $temp;
	            }
	    	}
		}
		return count($this->errors) == 0;
	}
	public function get_input_form()
	{
		 $s = <<<END
 <form method="post">
<table align="center" cellpadding="5" width="100%" cellspacing="2" style='border:solid black 1px;'>
END;
		$setting_names = $this->get_setting_names();
		foreach($setting_names as $setting_name)
		{
			$s.= '<tr><td>'.$this->get_description($setting_name).'</td>';
			$s.= '<td width="60%">'.$this->get_input($setting_name).'</td>';
			$s.= "<td><span style='color:red;'> ".$this->get_error($setting_name)."</span></td></tr>";
		}
$s .= <<<END
		<tr>
			<td>
				<input style="font-size:larger;" type="submit" value=" Save ">
			</td>
		</tr>
	</table>
</form>
END;
		return $s;
	}
}
?>