<?php
class Broadcast
{
	/**
	 * Notify other websites of changes to this ComfyPage site.
	 * Should only be called from Globals::clearContentDependentCaches()
	 */
	function notifyOthersOfAddedOrRemovedContent()
	{
	    $gs = Load::general_settings();
		//avoid spamming others with a bunch of messages while the user does the wizard
		if($gs->get(DONE_WIZARD))
		{
			$url = $gs->get(NEW_SITE_ID);
			$title = $url;
			$rss_url = $url.'%2Frss.php';
			$url = 'http://www.google.com/webmasters/tools/ping?sitemap=http%3A%2F%2F'.$url.'%2Fmap.php';
			@fopen($url,'rb');
		}
	}
}
?>