<?php
class Trophy_Case extends AddOn
{
	function __construct()
	{
		parent::__construct(false);
	}
	public function get_addon_description()
    {
		return 'Display the ComfyPage awards you have earned.';
	}
    public function get_instructions()
    {
    	  return <<<END
Display the awards you have earned for completing tasks in ComfyPage.
END;
	}
	public function get_first_stage_output($additional_inputs, $email=null, $message=null)
    {
        $mb = Load::award_settings();
        $level_achieved = $mb->get_level_achieved();
        $awards = $mb->get_awards_in_level($level_achieved + 1);
        $output = "";
        $desc = null;
        //display achieved awards
        for($i=0; $i<$level_achieved+1; $i++)
        {
			$output .= Message::get_help_link(9918469, "<img style=\"border:none;\" src=\"common/images/awards/LEVEL_0".($i+1).".png\" alt=\"Achieved level ".($i+1)."\" title=\"Achieved level ".($i+1)."\">", "LEVEL_".($i+1));
			if($i == $level_achieved)
			{
				$output .= " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
		}
		//if another level of awards to go
		if(empty($awards) == false)
		{
		    //display the tasks to be compoleted
        	foreach($awards as $award)
	        {
	        	$desc = $mb->get_description($award);
	            if($mb->get($award))
	            {
					$output .= "<img style=\"border-style:none;\" src=\"common/images/awards/$award.png\" alt=\"$desc. Done.\" title=\"$desc. Done.\">";
				}
				else
				{
				    $output .= Message::get_help_link(9918469, "<img style=\"border-style:none;opacity : 0.4;filter: alpha(opacity=40);\" src=\"common/images/awards/$award.png\" alt=\"$desc. Still to do.\" title=\"$desc. Still to do.\">", $award);
	            }
			}
			//display the award they are going for
			$output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".Message::get_help_link(9918469, "<img style=\"border:none;opacity : 0.4;filter: alpha(opacity=40);\" src=\"common/images/awards/LEVEL_0".($level_achieved+2).".png\" alt=\"Complete the tasks to achieve this award\" title=\"Complete the tasks to achieve this award\">", "LEVEL_".($i+1));
		}
		return "<center>$output</center>";
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