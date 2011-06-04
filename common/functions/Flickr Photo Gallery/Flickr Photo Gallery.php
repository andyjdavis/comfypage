<?php
//TODO look into this addon as the FLICKR_SET_ID setting description seem to contradict the setting use
define('FLICKR_SET_ID', 'FLICKR_SET_ID');
define('FLICKR_COLOR', 'C');
class Flickr_Photo_Gallery extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Display a Flickr photo gallery on your ComfyPage. All the power of Flickr on your own website.';
	}
    public function get_instructions()
    {
  		return <<<END
<p>In Flickr create a photo set to display. Get the set address by opening your set in Flickr and copying and pasting the address into the textbox below.  The address should look like this<br /><br /><span style='font-family:monospace'>http://www.flickr.com/photos/21755627@N06/sets/THE_SET_ID_NUMBER/</span></p>
<p>Make sure your photos are marked as public in Flickr</p>
<p>The gallery tool is provided by <a href=http://www.flickrshow.com/ target=_blank>http://www.flickrshow.com/</a>.</p>
END;
    }
    public function get_first_stage_output($additional_inputs)
    {
	$set_id = $this->get(FLICKR_SET_ID);
	$color = $this->get(FLICKR_COLOR);
	if(!is_numeric($set_id))
	{
		//we have a url so strip out set id
		$elements = explode('/', $set_id);
		for( $i = count($elements)-1; $i >= 0; $i--)
		{
			if(is_numeric($elements[$i]))
			{
				$set_id = $elements[$i];
				break;
			}
		}
	}
	/*return <<<END
<script type="text/javascript" src="http://v6.flickrshow.com/scripts/"></script>
<center>
<div id="flickrshow1" style="height:480px;width:640px;">
<p>A slide show will appear here shortly.</p>
</div>
</center>
<script type="text/javascript">
var cesc = new flickrshow("flickrshow1", {flickr_photoset: "$set_id", theme: "$color", debug:1});
</script>
END;*/
	$flickdivid = 'f'.rand();
	return <<<END
<script type="text/javascript" src="http://api.flickrshow.com/v7/flickrshow.js"></script>
<div id="$flickdivid" style="height:480px;width:640px;">
	<p>A slide show will appear here shortly</p>
</div>
<script type="text/javascript">
var flickr$flickdivid = new flickrshow('$flickdivid', {'set':'$set_id', theme: "$color",'autoplay':true,'interval':10000});
</script>
END;

    }
    protected function get_default_settings()
	{
		$s = array
		(
			//needs to be a string as its too long for a number
			FLICKR_SET_ID => "72157603429489275",
			FLICKR_COLOR => 'blue',
		);
	  	return $s;
	}
	public function validate($setting_name, $setting_value)
	{
	    return null;
	}
	protected function get_description_dictionary()
	{
		return array
		(
		FLICKR_SET_ID => 'Flickr set address',
		FLICKR_COLOR => 'Choose a colour that will match your site',
		);
	}
	function get_input_internal($setting_name, $setting_value)
	{
	  	switch($setting_name)
	  	{
			case FLICKR_COLOR:
			{
				static $options = array();
				$options['blue'] = 'Blue';
				$options['green'] = 'Green';
				$options['grey'] = 'Grey';
				$options['pink'] = 'Pink';
				return HtmlInput::get_select_input($setting_name, $setting_value, $options);
			}
	  		default :
	  		{
	  			return parent::get_input_internal($setting_name, $setting_value);
	  		}
	  	}
	}
}
?>