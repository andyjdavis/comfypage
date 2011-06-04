<?php
define('FLASH_MOVIE_PATH', 'PATH');
define('FLASH_MOVIE_WIDTH', 'W');
define('FLASH_MOVIE_HEIGHT', 'H');
class Flash_Movie extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Provides an easy way for you to embed a flash movie in a web page';
	}
	public function get_instructions()
    {
    return <<<END
<p>Select your flash movie.</p>
END;
	}
	public function get_first_stage_output($additional_inputs, $to_email = null, $from_email = null)
    {
        $movie_url = $this->get(FLASH_MOVIE_PATH);
	    $h = $this->get(FLASH_MOVIE_HEIGHT);
	    $w = $this->get(FLASH_MOVIE_WIDTH);
	return <<<END
<div style="text-align:center;">
<object width="550" height="400">
<param name="movie" value="$movie_url">
<embed src="$movie_url" width="$w" height="$h">
</embed>
</object>
</div>
END;
    }
    protected function get_default_settings()
	{
	    $s = array();
	  	$s[FLASH_MOVIE_HEIGHT] = 400;
	  	$s[FLASH_MOVIE_WIDTH] = 550;
		$s[FLASH_MOVIE_PATH] = '';
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
		FLASH_MOVIE_WIDTH => 'Width of your movie in pixels',
		FLASH_MOVIE_HEIGHT => 'Height of your movie in pixels',
		FLASH_MOVIE_PATH => 'The path to your swf file',
		);
	}
}
?>