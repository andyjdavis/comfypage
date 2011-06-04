<?php

define('TEMPLATES_DIR', 'common/templates/');
define('CUSTOM_TEMPLATE_NAME', 'Custom');
define('CUSTOM_TEMPLATE_DIR', 'site/CustomTemplate/');

function get_template_list($include_custom=true)
{
	require_once('common/utils/Globals.php');
	//require_once('common/general_settings.php');
	$tns = Globals::get_dirs_in_a_dir(TEMPLATES_DIR);
	$selected = Load::general_settings(TEMPLATE_IN_USE);
	$template_names = array();
	
	//if($selected != 'Default')
	{
		$template_names[] = $selected;
	}
	
	//$template_names[] = 'Default';
	
	if($include_custom)
	{
		if($selected != CUSTOM_TEMPLATE_NAME && custom_template_exists())
		{
			$template_names[] = CUSTOM_TEMPLATE_NAME;
		}
	}
	
	foreach($tns as $tn)
	{
		if( $tn != $selected )
		{
      $template_names[] = $tn;
		}
	}
	
	return $template_names;
}

function custom_template_exists()
{
  $dir = dir(CUSTOM_TEMPLATE_DIR);
  while (false !== $entry = $dir->read())
  {
    if ($entry == 'template.htm')
    {
      $dir->close();
      return true;
    }
  }

  $dir->close();
  return false;
}

function get_path_to_thumbnail($template_name)
{
  if($template_name==CUSTOM_TEMPLATE_NAME)
  {
    return CUSTOM_TEMPLATE_DIR . 'thumb.jpg';
  }
  else
  {
	 return TEMPLATES_DIR . "$template_name/thumb.jpg";
	}
}

function does_template_exist($template_name)
{
  if($template_name == CUSTOM_TEMPLATE_NAME && custom_template_exists() )
  {
    return true;
  }
  else
  {
  	$template_list = get_template_list();
  	return in_array($template_name, $template_list);
  }
}

function select_template($template_name)
{
	$gs = Load::general_settings();
	$gs->set(TEMPLATE_IN_USE, $template_name);
	//copy(get_path_to_template_html($template_name), 'site/template.htm');
	if($template_name != CUSTOM_TEMPLATE_NAME)
	{
		copy(get_path_to_template_css($template_name), 'site/site.css');
		set_default_feature_image($template_name);
	}
}

function set_default_feature_image($template_name)
{
	if(isset($template_settings))
 	{
		unset($template_settings);
	}
 	$path = get_path_to_template_settings($template_name);
 	if(file_exists($path))
 	{
 		require($path);
 		$gs = Load::general_settings();
 		//set_general_setting(FEATURE_IMAGE, $template_settings[FEATURE_IMAGE]);
 		$gs->set(FEATURE_IMAGE,$template_settings[FEATURE_IMAGE]);
 	}
}

function get_template_setting($setting_name, $template_name = null)
{
	$setting_value = null;

	if($template_name==null)
	{
		$template_name = Load::general_settings(TEMPLATE_IN_USE);
	}
	$path = get_path_to_template_settings($template_name);
 	if(file_exists($path))
 	{
 		require($path);
 		if(array_key_exists($setting_name, $template_settings))
 		{
 			$setting_value = $template_settings[$setting_name];
		}
 	}
 	
 	return $setting_value;
}

function get_path_to_template_files($template_name)
{
    return TEMPLATES_DIR . "$template_name/";
}

function get_path_to_template_html($template_name)
{
  if($template_name == CUSTOM_TEMPLATE_NAME )
  {
    return CUSTOM_TEMPLATE_DIR . '/template.htm';
  }
  else
  {
  	return TEMPLATES_DIR . "$template_name/template.htm";
  }
}

function get_path_to_template_css($template_name)
{
  if($template_name == CUSTOM_TEMPLATE_NAME )
  {
    //this file will never exist
    //return CUSTOM_TEMPLATE_DIR . '/site.css';
    return null;
  }
  else
  {
  	return TEMPLATES_DIR . "$template_name/site.css";
  	//return 'site/site.css';
  }
}

function get_path_to_template_settings($template_name)
{
  if($template_name == CUSTOM_TEMPLATE_NAME )
  {
    //this file will never exist
    //return CUSTOM_TEMPLATE_DIR . '/site.css';
    return null;
  }
  else
  {
  	return TEMPLATES_DIR . "$template_name/settings.php";
  }
}

?>