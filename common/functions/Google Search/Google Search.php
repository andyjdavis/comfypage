<?php
define('GOOGLE_SEARCH_CLIENT_ID', 'GOOGLE_SEARCH_CLIENT_ID');
define('GOOGLE_SEARCH_DOMAIN', 'GOOGLE_SEARCH_DOMAIN');
class Google_Search extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Visitors can Google search your site';
	}
    public function get_instructions()
    {
    	return <<<END
<p>Give your visitors the ability to search either the whole Internet or just one site (e.g. yoursite.com).</p>
<p><a href=http://www.help.comfypage.com/index.php?content_id=3482366>Click here</a> for information on search engines and your site.</p>
<p>If you have a Google Adsense account you can make money from the searches performed by your visitors. Click on the banner to sign up for a free account.</p>
<div align=center>
<script type="text/javascript"><!--
google_ad_client = "pub-4271888678667754";
google_ad_width = 180;
google_ad_height = 60;
google_ad_format = "180x60_as_rimg";
google_cpa_choice = "CAAQsZTuiwIaCLlbyUs097YXKKfC93MwAA";
//-->
</script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>
<p>After you sign up for Adsense complete the options below.</p>
<p>You can use this add-on even if you dont sign up for Adsense but you will not receive any revenue.</p>
END;
	}
	public function get_first_stage_output($additional_inputs, $email=null, $message=null)
    {
	    $domain = $this->get(GOOGLE_SEARCH_DOMAIN);
		$client_id = $this->get(GOOGLE_SEARCH_CLIENT_ID);

		if(empty($client_id))
		{
			$client_id = Globals::get_affinity_google_client_id();
		}

		return <<<END
	<div align=center>
<!-- SiteSearch Google -->
<form method="get" action="http://www.google.com/custom" target="_top">
<table border="0">
<tr><td nowrap="nowrap" valign="top" align="left" height="32">

</td>
<td nowrap="nowrap">
<input type="hidden" name="domains" value="$domain"></input>
<label for="sbi" style="display: none">Enter your search terms</label>
<input type="text" name="q" size="31" maxlength="255" value="" id="sbi"></input>
<label for="sbb" style="display: none">Submit search form</label>
<input type="submit" name="sa" value="Google Search" id="sbb"></input>
</td></tr>
<tr>
<td>&nbsp;</td>
<td nowrap="nowrap">
<table>
<tr>
<td>
<input type="radio" name="sitesearch" value="" checked id="ss0"></input>
<label for="ss0" title="Search the Web"><font size="-1" color="black">Web</font></label></td>
<td>
<input type="radio" name="sitesearch" value="$domain" id="ss1"></input>
<label for="ss1" title="Search $domain"><font size="-1" color="black">$domain</font></label></td>
</tr>
</table>
<input type="hidden" name="client" value="$client_id"></input>
<input type="hidden" name="forid" value="1"></input>
<input type="hidden" name="ie" value="ISO-8859-1"></input>
<input type="hidden" name="oe" value="ISO-8859-1"></input>
<input type="hidden" name="cof" value="GALT:#008000;GL:1;DIV:#336699;VLC:663399;AH:center;BGC:FFFFFF;LBGC:336699;ALC:0000FF;LC:0000FF;T:000000;GFNT:0000FF;GIMP:0000FF;FORID:1"></input>
<input type="hidden" name="hl" value="en"></input>
</td></tr></table>
</form>
<!-- SiteSearch Google -->
</div>
END;
    }
    protected function get_default_settings()
	{
	    $s = array();
	  	$s[GOOGLE_SEARCH_CLIENT_ID] = '';
	  	$s[GOOGLE_SEARCH_DOMAIN] = $_SERVER['HTTP_HOST'];
	  	return $s;
	}
	public function validate($setting_name, $setting_value)
	{
	  	switch($setting_name)
	  	{
	  		case GOOGLE_SEARCH_DOMAIN :
	  	    {
	  			return Validate::domain($setting_value);
	  		}
	  		default :
	  		{
	  			return null;
	  		}
	  	}
	}
    protected function get_description_dictionary()
	{
		return array
		(
		GOOGLE_SEARCH_CLIENT_ID => "Your Google client ID that identifies you to Google eg 'pub-1234567891234567'.  To get this number log in to Google adsense and got to the bottom of the 'My Account' section.",
		GOOGLE_SEARCH_DOMAIN => "Restrict searches to one website. Enter the website address here.  E.g. <i>example.com</i>",
		);
	}
}
?>