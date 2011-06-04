<?php
/*
This file should only be included once in the whole project inside saveable_base.
If you are planning on including it elsewhere then you are doing something wrong.
Call with Validate::email();
Trying to name them in the same manner as above. Looks nice. Easy to use.
*/
class Validate
{
    public static function required($value, $field_name = null)
    {
    	if($value == null || trim($value) == '')
    	{
    	    if($field_name == null)
    	    {
    			return 'The field is required';
    		}
    		else
    		{
    			return $field_name . ' is required';
    		}
    	}
    	else
    	{
    		return null;
    	}
    }
    public static function email($email)
    {
    	if(Validate::required($email) != null)
    	{
    		return 'A blank email address is not allowed';
    	}

    	$return_on_error = $email . ' is not a valid email address';

    	 // First, we check that there's one @ symbol, and that the lengths are right
    	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email))
    	{
    		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
    		return $return_on_error;
    	}

    	// Split it into sections to make life easier
    	$email_array = explode("@", $email);
    	$local_array = explode(".", $email_array[0]);

    	for ($i = 0; $i < sizeof($local_array); $i++)
    	{
    		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i]))
    		{
    			return $return_on_error;
    		}
    	}

    	$domain_error = Validate::domain($email_array[1]);

    	if(empty($domain_error) == false)
    	{
    		return $return_on_error;
    	}

    	return null;
    }
    //validate content that the user has entered that will be rendered on a page
    public static function content($content)
    {
        $content = strtolower($content);
        //don't allow javascript redirects
        //quick and dirty fix
	$bad = false;

        if( stripos($content, 'window.location')!==false ) {
            $bad = true;
	}
	else if( stripos($content, 'http-equiv="refresh"')!==false
	      || stripos($content, 'http-equiv=\'refresh\'')!==false ) { //cant block 'http-equiv=' as that includes content-type header
	    $bad = true;
	}

	if($bad) {
	    $errormsg = 'Illegal content found';
	    $errormsgtoemail = 'Illegal content trying to be save';

	    Globals::send_email_to_us($errormsgtoemail, $content);
	    return $errormsg;
	}
	return null;
    }
    public static function language($lang)
    {
        require_once('common/utils/StaticData.php');
    	$whiteList = StaticData::get_languages();
    	if(!in_array($lang, $whiteList))
    	{
    		return 'Invalid language';
    	}
    	return null;
    }
    public static function password($password)
    {
    	$min_length = 6;

    	if(strlen($password) < $min_length)
    	{
    		return "Password must be at least $min_length characters";
    	}

    	return null;
    }
	public static function addon($addon_key)
    {
        if(empty($addon_key))
        {
			return null;
		}
    	if(Addon::exists($addon_key)==false)
		{
			return "Add-on $addon_key does not exist";
		}
    	return null;
    }
	//TODO this function is accepting "11" as a valid domain. It really should not.
    public static function domain($domain)
    {
    	if(Validate::required($domain) != null)
    	{
    		return 'A blank domain is not allowed';
    	}

    	$return_on_error = $domain . ' is not a valid domain';

    	// Check if domain is IP. If not, it should be valid domain name
    	if (!ereg("^\[?[0-9\.]+\]?$", $domain))
    	{
    		$domain_array = explode(".", $domain);
    		if (sizeof($domain_array) < 2)
    		{
    			return $return_on_error; // Not enough parts to domain
    		}

    		for ($i = 0; $i < sizeof($domain_array); $i++)
    		{
    			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i]))
    			{
    				return $return_on_error;
    			}
    		}
    	}

    	return null;
    }
    public static function price($price, $price_name = null)
	{
		if(preg_match("/^[0-9]+(.[0-9]{2})?$/", $price) == false)
		{
		    if(empty($price_name))
		    {
				return 'Price is invalid';
			}
			else
			{
				return "$price_name is not a valid price";
			}
		}
		else
		{
			return null;
		}
	}
	public static function sitename($siteName)
	{
		$generic_msg = 'Site name must be letters and numbers only. No spaces.';
		$matchCount = preg_match('/[^a-z0-9\._]/', $siteName);
		if($matchCount > 0)
		{
			return $generic_msg;
		}
		else
		{
			if(empty($siteName)) return 'Enter a site name';
			if(strpos($siteName, '.') !== false) return 'No full-stops allowed'; //if it contains a '.'
			//apparently some versions of IE don't like underscores
			if(strpos($siteName, '_') !== false) return 'No underscores allowed. These things _'; //if it contains a '_'
			if(strpos($siteName, '..') !== false) return $generic_msg; //if it contains a '..'
			if(strpos($siteName, '/') === 0) return $generic_msg; //if it starts with a '/'
			if(strpos($siteName, '~') !== false) return $generic_msg; //if it contains a '~'
			if(substr_count($siteName,"w")==strlen($siteName)) return $generic_msg; //if it contains all w's ie wwww.comfypage.com
			return null;
		}
	}
	public static function currency($currency)
	{
	    require_once('common/utils/StaticData.php');
		if(in_array($currency, array_keys(StaticData::get_currencies())))
		{
			return null;
		}
		else
		{
			return 'Invalid currency selected';
		}
	}
}
?>