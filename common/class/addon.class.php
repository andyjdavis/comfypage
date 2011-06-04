<?php
/*
For now add-ons are stored under their displayable name at
common/funcitons/Addon Name/Addon Name.php
The class name will be the same as the addon name with underscores replacing spaces (e.g. AddonName).
In the future we need to decouple the backend storage from the displayable name.
*/
define('COMMON_FUNCTIONS_PATH', 'common/functions/');
define('SITE_FUNCTIONS_PATH', 'site/functions/');
define('POST_BACK_PARAM', 'postback');
abstract class Addon extends SaveableBase
{
	const POST_BACK_PARAM = 'postback';
	private $requires_config;
    function Addon($requires_config)
    {
        //Start with class name. Replace underscores with spaces. This makes the storage point.
        $storage_name = $class_name = str_replace('_', ' ', get_class($this));
        parent::SaveableBase("site/config/functions/$storage_name.php");
        $this->requires_config = $requires_config;
    }
    public abstract function get_addon_description();
    public abstract function get_instructions();
    public abstract function get_first_stage_output($additional_inputs);
    //default second stage output is the same as first stage
    //override to process a postback
    public function get_second_stage_output($vars, $additional_inputs)
    {
		return $this->get_first_stage_output($additional_inputs);
	}
    public function requires_config()
    {
		return $this->requires_config;
	}
	public static function get_function_keys()
	{
	    static $addon_keys; //only need one of these for all instances
	    //if list of keys not loaded
	    if($addon_keys == null)
	    {
			$site_functions = Globals::get_dirs_in_a_dir(SITE_FUNCTIONS_PATH);
			$common_functions = Globals::get_dirs_in_a_dir(COMMON_FUNCTIONS_PATH);
			$addon_keys = array_merge($common_functions, $site_functions);
			natcasesort($addon_keys);
		}
		return $addon_keys;
	}
	public static function exists($addon_key)
	{
		return in_array($addon_key, Addon::get_function_keys());
	}
	function is_post_back()
	{
		return array_key_exists(Addon::POST_BACK_PARAM, $_GET);
	}
	public static function execute($functionKey, $contentId, $configuration = false)
	{
		if(empty($functionKey))
		{
			return null;
		}
		if(Addon::exists($functionKey) == false)
		{
		    if(Login::logged_in(false))
		    {
		    	return Message::get_error_display('The add-on no longer exists');
		    }
		    else
		    {
				return null;
			}
		}
		$output = null;
		$doodad = Load::addon($functionKey);
		if( !$doodad ) {
		    $output .= Message::get_error_display("The add-on ($functionKey) does not exist");
		}
		else if($doodad->requires_config() && $doodad->is_valid() == false)
		{
			$output .= Message::get_error_display('The <b>' . $functionKey . '</b> add-on requires configuration<br><a href="function.php?' . FUNCTION_GET_PARAM . '=' . $functionKey . '">Configure this add-on</a>');
		}
		else
		{
			//additional form inputs that must be added to a function's form so
			//the content id and post back can be identified
			$additional_inputs = '';

			//dont include content_id param if we're using blah.htm style urls
			if( !$_SERVER || array_key_exists('PHP_SELF',$_SERVER)==false || preg_match('/\.htm/i',$_SERVER['PHP_SELF']) == false) {
				$additional_inputs .= '<input type=hidden name=' . CONTENT_ID_URL_PARAM . ' value="' . $contentId . '">';
			}

			//can check this post back param to see if this is the post back
		  	$additional_inputs .= '<input type=hidden name=' . POST_BACK_PARAM . ' value="' . $functionKey . '">';
		  	if($configuration)
		  	{
				$additional_inputs .= '<input type=hidden name="' . FUNCTION_GET_PARAM . '" value="' . $functionKey . '">';
			}
			//if this is a post back
			//and only execute the doodad that is posting back
			//don;t execute other doodads on the page
			if(Addon::is_post_back() && $_GET[POST_BACK_PARAM] == $functionKey)
			{
				$vars = array();
				$get_keys = array_keys($_GET);
				foreach($get_keys as $gk)
				{
					//validate it all baby
					$vars[$gk] = Globals::get_param($gk, $_GET);
				}
				require_once('common/utils/Validate.php');
			    //process request data
				$output .= $doodad->get_second_stage_output($vars, $additional_inputs);
			}
			else
			{
				$output .= $doodad->get_first_stage_output($additional_inputs);
			}
		}
		return '<div>' . $output . '</div>';
	}
	public static function get_addon_select_box($select_name, $selected)
	{
		$functions = Addon::get_function_keys();
		$function_select_options = array();
		$function_select_options[""] = 'No add-on selected';
		$aoc = 'var aoc=new Array();';
		foreach($functions as $function)
		{
			$function_select_options[$function] = $function;

			$doodad = Load::addon($function);
			if($doodad != null && $doodad->requires_config())
			{
				$aoc .= 'aoc["'.$function.'"]="<a href=\"function.php?function='.$function.'\" target=\"_blank\">Configure '.$function.'</a>";';
			}
		}
		$onchangeJS = '<script language="Javascript">
		function addonChanged(v)
		{
			if(v)
			{
				'.$aoc.'
				if(aoc[v]==undefined)
				{
					document.getElementById("addonConfigSpan").innerHTML = "";
				}
				else
				{
					document.getElementById("addonConfigSpan").innerHTML = aoc[v];
				}
			}
			else
			{
				document.getElementById("addonConfigSpan").innerHTML = "";
			}
		}
		</script>';

		return $onchangeJS.HtmlInput::get_select_input($select_name, $selected, $function_select_options, 'addonChanged(this.value);');
	}
}
?>