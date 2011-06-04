<?php
class Subdomain_Listing extends Addon
{
	function __construct()
	{
		parent::__construct(false);
	}
	public function get_addon_description()
    {
		return 'The subdomain listing displays links to all of your subdomains';
	}
    public function get_instructions()
    {
    	return 'Useful for sites that have transferred to their own domain. Generates a list of your subdomains.';
	}
	public function get_first_stage_output($additional_inputs)
    {
		$output = '';

		$gs = Load::general_settings();
		$portal_allowed = $gs->operating_under_domain();
		if($portal_allowed == false)
		{
			$output .= '<p>You must move to your own domain to have subdomains</p><p><a href=register.php>Move to your own domain</a></p>';
		}
		else
		{
			$site_id = $gs->get(NEW_SITE_ID);

			//if(!file_exists('common/server_interface.php')
			//	|| !file_exists('/home/camand/etc/our_scripts/domains.php'))
			//{
			//	$output .= '<p>Unable to find server interface to get subdomain listing</p>';
			//}
			//else
			{
				require_once('common/ServerInterface.php');
				$portals = Serverinterface::get_portal_list($site_id);
				sort($portals);

				$cols = 2;

				$output .= '<table width=100% cellpadding=5 align=center><tr>';
				$i = 0;
				for($i = 0; $i<count($portals); $i++)
				{
					if($i % $cols == 0)
	    			{
	    				$output .= '</tr><tr>';
					}

					$portal = $portals[$i];
					$portal_address = "$portal.$site_id";

					$output .= "<td><a href='http://$portal_address'>$portal_address</a></td>";
				}

				while($i++ % $cols != 0)
	    		{
	    			$output .= '<td></td>';
				}

				$output .= '</tr></table>';
			}
		}

		return $output;
    }
    protected function get_default_settings()
	{
		$s = array();
		return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		return null;
	}
}
?>