<?php
define('PAGE_VIEW_COUNT_NEW', 'PAGE_VIEW_COUNT_NEW');
define('PAGE_VIEW_MONTHLY_LIMIT', 500);
require_once('common/class/settings.class.php');
class CounterSettings extends Settings
{
	function CounterSettings($loc = 'site/config/counter.php')
	{
	   parent::Settings($loc);
	}
	function get_default_settings()
	{
		$s = array
		(
	        PAGE_VIEW_COUNT_NEW => 0,
		);
		return $s;
	}
	function validate($setting_name, $setting_value)
	{
		return null;
	}
	//notify = true will send email when warning level is reached
	//warning = true will return warning messages as well as max views reached message
	function check_page_views($notify = true, $warning = true)
	{
		$gs = Load::general_settings();
		//reset for new month
		//$last_reset = getdate(get_page_view_last_reset());
		$last_reset = getdate($gs->get(PAGE_VIEW_LAST_RESET));
		$now = strtotime('now');
		$now_info = getdate($now);
		//if this month isn't the same month as the last reset
		if($now_info['month'] != $last_reset['month'])
		{
			$page_view_count = $this->get(PAGE_VIEW_COUNT_NEW);
			//reset hit counter
			$this->set(PAGE_VIEW_COUNT_NEW, 0);
			//set_page_view_last_reset($now);
			$gs->set(PAGE_VIEW_LAST_RESET, $now);
			//backup site at same time
			//backup_site();
			//$this->counter_log('Page view counter reset', "$page_view_count to 0");
			return null;
		}
		return null; //we decided to stop worrying about page views
	}
	function page_viewed()
	{
		if(Login::logged_in(false))
		{
		    return;
		}
		//$page_view_count = $this->get_page_view_count();
		$page_view_count = $this->get(PAGE_VIEW_COUNT_NEW);
		//$page_view_limit = get_page_view_limit();
		$page_view_limit = PAGE_VIEW_MONTHLY_LIMIT;
		//$aux_page_views = $this->get_aux_page_views();
		//if monthly quota is available
		if($page_view_count < $page_view_limit)
		{
			//$new_page_view_count = $this->get_page_view_count() + 1;
			$new_page_view_count = $page_view_count + 1;
			//$this->set_page_view_count($new_page_view_count);
			$this->set(PAGE_VIEW_COUNT_NEW, $new_page_view_count);
			//$this->counter_log('Page viewed');
			//if($new_page_view_count == $page_view_limit) //just reached limit
			//{
			//	$this->counter_log('Just reached page view limit', "Limit is $page_view_limit. Aux page views is $aux_page_views.");
			//}
		}
		/*else //if used up monthly quota
		{
			//if extra page views available
			if($aux_page_views > 0)
			{
			    //use one extra page view
				$this->set_aux_page_views($aux_page_views - 1);
				$new_aux_views = $aux_page_views - 1;
				$this->counter_log('Page viewed');
				if($aux_page_views - 1 == 0)
				{
					$this->counter_log('Just used last aux page view');
				}
			}
			//else
			//{
			//	$this->counter_log('Page viewed');
			//}
		}*/
		$this->check_page_views();
	}
}
?>