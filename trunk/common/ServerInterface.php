<?php
require_once('common/utils/Globals.php');
class ServerInterface
{
	//Move website from current site ID to new domain
	public function move_to_domain($domain)
	{
		$site_id = Load::general_settings(NEW_SITE_ID);
		return ServerInterface::move_website($site_id, $domain);
	}
	//Move the website to a new site ID
	private static function move_website($site_id_origin, $site_id_dest)
	{
		//TODO Not implemented
		echo("ServerInterface::move_website NOT IMPLEMENTED");
		return "ServerInterface::move_website NOT IMPLEMENTED";
	}
	//Create a sub-domain of this website located at $subfolder_name.$parent_site_id (subfolder.example.com)
	public static function create_subdomain($subfolder_name, $admin_email, $admin_password, $parent_site_id, $lang='en')
	{
		$new_site_id = "$subfolder_name.$parent_site_id";
		$error = ServerInterface::create_website($new_site_id, false);
		if(empty($error) == false) return $error; //if a problem return
		//$path_to_general_settings = DATA_ROOT . "site_dirs/$new_site_id/site/config/general_settings.php";
		//$path_to_general_settings = ServerInterface::get_general_settings_path($new_site_id);
		$path_to_general_settings = ServerInterface::get_sites_file_path($new_site_id, 'config/general_settings.php');
		$gs = new GeneralSettings($path_to_general_settings);
		$gs->use_working_dir = false;
		$arr = array(ADMIN_EMAIL=>$admin_email,PASSWORD=>$admin_password,NEW_SITE_ID=>$new_site_id,LANG=>$lang);
		$gs->process_post($arr, array_keys($arr));
		return null;
	}
	//Create a new website with the specified site ID
	private static function create_website($site_id)
	{
	    //TODO Not implemented
	    echo("ServerInterface::create_website NOT IMPLEMENTED");
	    return "ServerInterface::create_website NOT IMPLEMENTED";
	}
	//Returns the path to the specified file in the specified site
	//$relative_file_path File path relative to site's root dir
	private static function get_sites_file_path($site_id, $relative_file_path)
	{
	    //TODO Not implemented
	    echo("ServerInterface::get_sites_file_path NOT IMPLEMENTED");
	}
	public function delete_subdomain_of_this_site($portal_name)
	{
        require_once('common/utils/Validate.php');
		$error = Validate::domain("$delete_portal.$site_id");
		if(!empty($error))
		{
			return $error;
		}
		$site_id = Load::general_settings(NEW_SITE_ID);
		$address = "$portal_name.$site_id";
		ServerInterface::delete_website($address);
	}
	private static function delete_website($site_id)
	{
		//TODO Not implemented
		echo("ServerInterface::delete_website NOT IMPLEMENTED");
	}
	//returns a list of this site's sub-domains
	public static function get_portal_list($site_id)
	{
		//TODO Not implemented
		echo("ServerInterface::get_portal_list NOT IMPLEMENTED");
		return array("testsite1", "testsite2");
	}
	public static function set_setting_on_another_site($other_site_id, $settings_file_location, $settings_class_name, $new_settings)
	{
		$abs_path = ServerInterface::get_sites_file_path($other_site_id, $settings_file_location);
		$settings_object = new $settings_class_name($abs_path);
		$settings_object->use_working_dir = false;
		$settings_object->process_post($new_settings, array_keys($new_settings));
	}
	public static function copy_file_to_another_site($other_site_id, $current_file_location, $new_file_location)
	{
		$abs_path = ServerInterface::get_sites_file_path($other_site_id, $new_file_location);
		if(file_exists($current_file_location)) copy($current_file_location, $abs_path);
	}
	public static function file_exists_in_other_site($other_site_id, $file_location)
	{
		$abs_path = ServerInterface::get_sites_file_path($other_site_id, $file_location);
		return file_exists($abs_path);
	}
	public function delete_this_site()
	{
		$site_id = Load::general_settings(NEW_SITE_ID);
		ServerInterface::delete_website($site_id);
	}
}
?>