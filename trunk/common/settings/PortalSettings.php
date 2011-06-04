<?php
require_once('common/class/settings.class.php');
define('USE_MY_TEMPLATE','USE_MY_TEMPLATE');
define('USE_MY_STYLE','USE_MY_STYLE');
define('PORTAL_BRAND','PORTAL_BRAND');
define('ACTIVE_ADDONS','ACTIVE_ADDONS');
class PortalSettings extends Settings
{
	function __construct($loc = 'site/config/portal_settings.php')
	{
		parent::__construct($loc);
	}
	function get_default_settings()
	{
	    return array
		(
			USE_MY_TEMPLATE => false, //should my subdomains use my template
			USE_MY_STYLE => false, //should sub-domains use my styles
			PORTAL_BRAND => '', //HTML snuippet displayed on every sub-domain
			ACTIVE_ADDONS => array(),
		);
	}
    function validate($setting_name, $setting_value)
    {
		return null;
    }
    protected function get_description_dictionary()
	{
		return array
		(
		USE_MY_TEMPLATE => 'Require sub-domains to use my template',
		PORTAL_BRAND => 'Portal branding. A HTML snippet displayed on all sub-domains. ' . Message::get_help_link(9918459, 'Help with portal branding', 'portal_branding'),
		USE_MY_STYLE => 'Require sub-domains to use my fonts and colours',
		ACTIVE_ADDONS => 'Add-ons that are available to sub-domains',
		);
	}
    protected function get_input_internal($setting_name, $setting_value)
    {
    	switch($setting_name)
		{
			case USE_MY_TEMPLATE :
			{
				return HtmlInput::get_checkbox_input($setting_name, $setting_value);
			}
			case USE_MY_STYLE :
			{
				return HtmlInput::get_checkbox_input($setting_name, $setting_value);
			}
			case PORTAL_BRAND :
			{
				return HtmlInput::get_textarea_input($setting_name, $setting_value) . '<div>' . htmlspecialchars_decode($setting_value) . '</div>';
			}
			default :
			{
				return parent::get_input_internal($setting_name, $setting_value);
			}
		}
	}
	//todo move these three functions into portal controller
	function apply_my_settings_to_portal($subdomain_name = null)
	{
		require_once('common/ServerInterface.php');
		$site_id = Load::general_settings(NEW_SITE_ID);
		$site_id = $this->stripOffSubdomain($site_id);
		$subdomains = null;
		if($site_id == 'comfypage.com') //if this site is comfypage.com
		{
			return; //don't do a damn thing
		}
		if($subdomain_name != null)
		{
			$subdomains = array();
			$subdomains[] = $subdomain_name; //list of one sub-domain
		}
		else
		{
			$subdomains = ServerInterface::get_portal_list($site_id);
		}

		$permission_settings = Load::permission_settings();
		//$default_permission_settings = $permission_settings->get_default_settings();

		$portal_brand = $this->get(PORTAL_BRAND);
		$use_my_template = $this->get(USE_MY_TEMPLATE);
		$use_my_style = $this->get(USE_MY_STYLE);

		$gs = Load::general_settings();
		$my_template = $gs->get(TEMPLATE_IN_USE);
		$feature_image = $gs->get(FEATURE_IMAGE);

		//$service_level = $this->get(SUBDOMAIN_SERVICE_LEVEL);

		$addons = $this->get(ACTIVE_ADDONS);

		foreach($subdomains as $subdomain)
		{
			$combined_site_id = "$subdomain.$site_id";
			//set if the subdomain is allowed to use a template based on my choice to force my template on subdomains
			//set_setting_on_another_site($combined_site_id ,'site/config/permission_settings.php' ,'PermissionSettings' ,array(TEMPLATE_ALLOWED => !$use_my_template, STYLE_ALLOWED => !$use_my_style));
			//$ps_settings = new PermissionSettings("$data_root/$combined_site_id/site/config/permission_settings.php");
			$ps_settings = new PermissionSettings(ServerInterface::get_sites_file_path($combined_site_id, 'site/config/permission_settings.php'));
			$ps_settings->use_working_dir = false;
			$ps_settings->set(TEMPLATE_ALLOWED, !$use_my_template);
			$ps_settings->set(STYLE_ALLOWED, !$use_my_style);

			//$gs_settings = new GeneralSettings("$data_root/$combined_site_id/site/config/general_settings.php");
			$gs_settings = new GeneralSettings(ServerInterface::get_sites_file_path($combined_site_id, 'site/config/general_settings.php'));
			$gs_settings->use_working_dir = false;
			$gs_settings->set(TEMPLATE_IN_USE, $my_template);
			$gs_settings->set(FEATURE_IMAGE, "http://$site_id/$feature_image");
			$gs_settings->set(BRANDING, $portal_brand);
			//copy template into site directory
			if($use_my_style || !ServerInterface::file_exists_in_other_site($combined_site_id, 'site/site.css'))
			{
				ServerInterface::copy_file_to_another_site($combined_site_id, 'site/site.css', 'site/site.css');
			}
			if($use_my_template || !ServerInterface::file_exists_in_other_site("$portal.$site_id", 'site/template.htm'))
			{
				ServerInterface::copy_file_to_another_site($combined_site_id, 'site/template.htm', 'site/template.htm');
			}
			//if my template is custom
			require_once('common/contentServer/template_control.php');
			//if using a custom template
			if($my_template == CUSTOM_TEMPLATE_NAME && $use_my_template || $my_template == CUSTOM_TEMPLATE_NAME && !ServerInterface::file_exists_in_other_site("$portal.$site_id", 'site/CustomTemplate/template.htm'))
			{
				//copy in custom template
				ServerInterface::copy_file_to_another_site($combined_site_id, 'site/CustomTemplate/template.htm', 'site/CustomTemplate/template.htm');
			}
			//$cc_settings = new CreditSettings("$data_root/$combined_site_id/site/config/credit_control_settings.php");
			$cc_settings = new CreditSettings(ServerInterface::get_sites_file_path($combined_site_id, 'site/config/credit_control_settings.php'));
			$cc_settings->use_working_dir = false;
			$cc_settings->set(PURCHASED_DOODADS, $addons);
		}
	}
	function setWizard($subdomain_name, $wizard)
	{
		require_once('common/utils/Globals.php');
		require_once('common/ServerInterface.php');
		$site_id = Load::general_settings(NEW_SITE_ID);
		$site_id = $this->stripOffSubdomain($site_id);
		$new_settings = array();
		$new_settings[NEED_DO_WIZARD] = $wizard;
		ServerInterface::set_setting_on_another_site("$subdomain_name.$site_id", 'site/config/general_settings.php', 'GeneralSettings', $new_settings);
	}
	//TODO this function is not a good measure of sub-domain status
	//TODO port myclub.org.au hack
	function stripOffSubdomain($s)
	{
		//if is a comfypage domain
		if(strpos($s, '.comfypage.com'))
		{
			if(substr_count($s, '.')==2)
			{
				$s = substr($s, strpos($s, '.')+1);
			}
			return $s;
		}
		else //not a comfypage domain
		{
			return $s; //return original
		}
	}
}
?>