<?php
define('GALLERY_DIR', 'GALLERY_DIR');
define('GRID_LAYOUT','GRID_LAYOUT');
define('USER_FILES', 'site/UserFiles');
class Gallery extends Addon
{
	function __construct()
	{
		parent::__construct(true);
	}
	public function get_addon_description()
    {
		return 'Shows a gallery of thumbnails created from a directory of images.  Displays images ordered by filename.';
	}
    public function get_instructions()
    {
        return 'Choose a folder that contains your images.';
    }
    public function get_first_stage_output($additional_inputs)
    {
	    $output = '';
		$dir = $this->get(GALLERY_DIR);
		$grid = $this->get(GRID_LAYOUT);
		if(!file_exists($dir))
		{
			$output .= '<p>Unable to find gallery directory</p>';
		}
		else
		{
			$images = Globals::scandir_legacy($dir, 0);
			$extensions = array('.gif', '.jpg', '.jpeg', '.png');
			$count = count($images);
			for($i=0; $i<$count; $i++)
			{
				if(!$this->extension_allowed($images[$i], $extensions))
				{
					unset($images[$i]);
				}
			}
			if(count($images)<1)
			{
				$output .= '<div style="text-align: center;">no images in gallery directory</div>';
			}
			else
			{
				require_once('common/cache.php');
				$cache = new Cache;
				$pwd = $dir;
				$len_user_files = strlen(USER_FILES);
				if(substr($dir,0,$len_user_files)==USER_FILES)
				{
					$pwd = substr($dir, $len_user_files);
				}

				if(!$grid)
				{
					$output .= <<<END
<script language=Javascript>
function returnObjById( id )
{
    if (document.getElementById)
        var returnVar = document.getElementById(id);
    else if (document.all)
        var returnVar = document.all[id];
    else if (document.layers)
        var returnVar = document.layers[id];
    return returnVar;
}

function picSelect(imgUrl)
{
	var div = returnObjById('pictureframe');
	div.innerHTML = '<img alt=loading src="' + imgUrl + '" />';
	return false;
}
</script>
<div style="text-align: center;border:solid 1px black;width:600px;">
	<table height=400px cellpadding=0 cellspacing=0 width=100%>
		<tr><td>
			<div id=pictureframe style="width:100%;text-align:center;"><div id=cpgselect class=translate_me>select a picture</div></div>
		</td></tr>
	</table>
	<div style=" width:100%; height:180px; overflow:auto;">
		<table width=100% cellpadding=5 align=center style="text-align:center;">
			<tr>
END;
				foreach($images as $imgName)
				{
					$imgUrl = "$dir/$imgName";
					//$imgThumbUrl = $cache->get_thumb_path($pwd, $imgName);
					$imgThumbUrl = $imgUrl;
					$imgTag = "<img width='100' alt='$imgName' src='$imgThumbUrl' />";
					$output .= "<td style='vertical-align:top;'><div onclick='return picSelect(\"$imgUrl\");'>$imgTag</div></td>";
				}
				$output .= <<<END
			</tr>
		</table>
	</div>
</div>
END;
				}
				else
				{
					$cols = 2;
					$output .= '<table width=80% cellpadding=5 align=center style="text-align:center;"><tr>';
					$i = -1;
					foreach($images as $imgName)
					{
						$i++;
						if( $i  %  $cols == 0 )
						{
							$output .= '</tr><tr>';
						}
						$imgUrl = "$dir/$imgName";
						//$imgThumbUrl = $cache->get_thumb_path($pwd, $imgName);
						$imgThumbUrl = $imgUrl;
						$imgTag = "<img width='100' alt='$imgName' src='$imgThumbUrl' />";
						$output .= "<td><a target=_blank href='$imgUrl'>$imgTag</a></td>";
					}
					while($i++ % $cols != 0)
					{
						$output .= '<td></td>';
					}
					$output .= '</tr></table>';
				}
			}
		}
		return $output;
    }
    function extension_allowed($FullStr, $EndStrArray)
	{
		$StrLen = strlen($EndStrArray[0]);
		$FullStrEnd = substr($FullStr, strlen($FullStr) - $StrLen);
		return array_search(strtolower($FullStrEnd), $EndStrArray);
	}
    protected function get_default_settings()
	{
		$s = array();
		$s[GALLERY_DIR] = USER_FILES;
		$s[GRID_LAYOUT] = false;
		return $s;
	}
	public function validate($setting_name, $setting_value)
	{
		switch($setting_name)
	  	{
			case GALLERY_DIR:
			{
				if(empty($setting_value))
				{
					return 'set gallery directory';
				}
				if(!file_exists($setting_value))
				{
					return 'directory cannot be found';
				}
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
		GALLERY_DIR => 'The directory to load images from',
		GRID_LAYOUT => 'Display your images in a grid or as a slideshow',
		);
	}
	function get_input_internal($setting_name, $setting_value)
	{
	  	switch($setting_name)
	  	{
			case GRID_LAYOUT:
			{
				$options = array();
				$options[0] = 'Slideshow Layout';
				$options[1] = 'Grid Layout';
				return HtmlInput::get_select_input($setting_name, $setting_value, $options);
			}
	  		default :
	  		{
				return HtmlInput::get_dir_input($setting_name, $setting_value);
	  		}
	  	}
	}
}
?>