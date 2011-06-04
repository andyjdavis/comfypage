<?php
/*
To stop form spam include this file and use get_hidden_tags and check_hidden_tags

Add the hidden tags to the form you want to stop spam on
Check the form submission is by a human by calling check_hidden_tags
*/

//add these tages to the inside of any html form
function get_hidden_tags()
{
	$blocker = new formSpamBotBlocker();
	$blocker->setTrap(true,'cpt','');
	return $blocker->makeTags();
}

//pass in the get or post array
//whichever was used in the form submission
//returns true if the form was submitted by a human
function check_hidden_tags($param)
{
	if(empty($param))
	{
		return true;
	}

	require_once("fsbb.php");
	$blocker = new formSpamBotBlocker();
	$blocker->setTrap(true,'cpt'); // called here, because it has been called on the form page as well (same trap name!)
	return $blocker->checkTags($param);
}

// +----------------------------------------------------------------------+
// | formSpamBotBlocker 0.2                                               |
// +----------------------------------------------------------------------+
// | Date: 5 Apr 2007                                                     |
// +----------------------------------------------------------------------+
// | License: LGPL                                                        |
// +----------------------------------------------------------------------+
// | formSpamBotBlocker is a PHP class, that tries to prevent web form    |
// | submissions by spambots without having to interact with a human user.|
// | It generates <input type="hidden"> or visually hidden tags (CSS)     |
// | and checks their unique names and values to identify a spambot.      |
// | No CAPTCHA-, SESSION-, COOKIE or Javascript-based methods are used.  |
// +----------------------------------------------------------------------+
// | Author: Giorgos Tsiledakis <gt(at)corissia(dot)com>                  |
// +----------------------------------------------------------------------+
//==============================================================================================
// Please take a look at the included documentation file first: readme.txt
// Use of the class:
// 1. Create the required <input> tags on the page contaning the web form
// a. Optionally set your defaults in the class source file (public variables)
// b. Include the class in your script
// c. Create an object: $blocker=new formSpamBotBlocker();
// d. Optionally call public functions or set public variables to adapt your defaults to the current web form
// e. within your html form: print $blocker->makeTags();
//
// 2. Check if the $_POST or $_GET array contains the valid parametes on the target page
// if ($_POST){ // or $_GET
// 		$blocker=new formSpamBotBlocker();
// 		$nospam=false;
// 		$nospam=$blocker->checkTags($_POST); // or $_GET
//			if ($nospam) print "Valid Submission"; // handle valid request
// 			else print "Invalid Submission"; // handle invalid request
// 	} 
//==============================================================================================
class formSpamBotBlocker{
//==============================================================================================
// PUBLIC VARIABLES AND FUNCTIONS
//==============================================================================================
var $initKey="abcd1234"; // set some string here to make the encoded names and values hard to guess 
var $minutesAfterMidnight=20; // minutes after midnight to allow a submission of a form generated at the previous day
var $minTime=2; // time in seconds needed to have passed, before o form can be submitted, set also by setTimeWindow()
var $maxTime=1800; // max time in seconds to submit a form, set also by setTimeWindow()
var $hasTrap=true; // true: a visually hidden input tag will be generated, set also by setTrap()
var $trapName="email"; // name of the visually hidden input tag, set also by setTrap()
var $trapLabel="Do not enter anything in this text box otherwise your message will not be sent!"; // label info to warn human users, who do not use CSS, set also by setTrap()
//==============================================================================================
// PUBLIC setTrap()
// param $bol: true to enable the trap tag, false to disable it [boolean, optional, default=true]
// param $name: if given, it sets the name of the trap tag [string, optional, default=false]
// param $label: if given, it sets the label of the trap tag [string, optional, default=false]
//==============================================================================================
	function setTrap($bol=true,$name=false,$label=false){
		if ($bol==false) $this->hasTrap=false;
		else{
			$this->hasTrap=true;
			if ($name) $this->trapName=$name;
			if ($label) $this->trapLabel=$label;
		}
	}
//==============================================================================================
// PUBLIC setTimeWindow()
// param $min: time in seconds needed to have passed, before o form can be submitted [numeric, optional, default=2]
// param $max: max time in seconds to submit a form [numeric, optional, default=600]
//==============================================================================================
	function setTimeWindow($min=2,$max=600){
		$this->minTime=$min;
		$this->maxTime=$max;
	}
//==============================================================================================
// PUBLIC makeTags() [string]
// generates the xhtml string for the required form input tags
//==============================================================================================
	function makeTags(){
		$this->initCode();
		$out="";
		$out.=$this->setCodeID();
		$out.=$this->userID();
		$out.=$this->dynID();
		if ($this->hasTrap) $out.=$this->trapID();
		return $out;
	}
//==============================================================================================
// PUBLIC checkTags() [boolean]
// param $arr: the $_POST or $_GET array sent by a form
// checks if there are valid parameters in the $arr array
//==============================================================================================
	function checkTags($arr=array()){
			if ($arr[$this->keyName] && $arr[$this->keyName]!=""){
				$this->getCodeID($arr[$this->keyName]);
			}
			else return false;
		if ($this->checkUserID($arr) && $this->checkDynID($arr) && $this->checkTrap($arr)) return true;
		else return false;
	}
//==============================================================================================
// PRIVATE VARIABLES AND FUNCTIONS
//==============================================================================================
var $version="v0.2 (050407)";
var $keyName="fsbb_key";
var $userIDName="";
var $dynIDName="";
//==============================================================================================
// PRIVATE userID() [string], called in function makeTags()
// generates the xhtml string for the hidden input tag, that contains some unique userID
//==============================================================================================
	function userID(){
		$userID=$this->intUserID();
		$tagName=substr($userID, $this->userIDNamestart, $this->userIDNameLength);
		$tagValue=substr($userID, $this->userIDValuestart, $this->userIDValueLength);
		$out="<input type=\"hidden\" name=\"".$tagName."\" value=\"".$tagValue."\" />\n";
		return $out;
	}
//==============================================================================================
// PRIVATE dynID() [string], called in function makeTags()
// generates the xhtml string for the hidden input tag with a name, that changes daily
//==============================================================================================
	function dynID(){
		$actDay=date("j");
		$actMonth=date("n");
		$actYear=date("Y");
		$actTime=time();
		$today=mktime(0,0,0,$actMonth,$actDay,$actYear);
		$tagName=substr($this->enc($today.$this->initKey), $this->dynIDNamestart, $this->dynIDNameLength);
		$tagValue=$this->enc($actTime,"base64");
		$out="<input type=\"hidden\" name=\"".$tagName."\" value=\"".$tagValue."\" />\n";
		return $out;
	}
//==============================================================================================
// PRIVATE setCodeID() [string], called in function makeTags()
// generates the xhtml string for the hidden input tag, that contains the key do decrypt the code passed
//==============================================================================================
	function setCodeID(){
		$out="<input type=\"hidden\" name=\"".$this->keyName."\" value=\"".$this->codeInit."\" />\n";
		return $out;
	}
//==============================================================================================
// PRIVATE trapID() [string], called in function makeTags()
// generates the xhtml string for the trag text input tag, that is hidden using CSS
// if CSS is disabled, a human user will be warned no to enter anything in this box
// It is a good idea to change the style="display:none" to class="somename"
// and set in your external CSS .somename {display:none;} to confuse spambots even more
//==============================================================================================
	function trapID(){
		$out="<span style=\"display:none;visibility:hidden;\">\n";
		$out.="<label for=\"".$this->trapName."\">".$this->trapLabel."</label>\n";
		$out.="<input type=\"text\" name=\"".$this->trapName."\" id=\"".$this->trapName."\" value=\"\" />\n";
		$out.="</span>\n";
		return $out;
	}
//==============================================================================================
// PRIVATE intUserID() [string], called in function userID()
// generates the unique userID
//==============================================================================================
	function intUserID(){
		$actSystem=$_SERVER['HTTP_USER_AGENT'];
		$actIP=$_SERVER['REMOTE_ADDR'];
		$userID=$this->enc($actSystem.$actIP.$this->initKey);
		return $userID;
	}
//==============================================================================================
// PRIVATE enc() [string]
// encoding method
//==============================================================================================
	function enc($var,$method=false){
		if ($method=="base64") return base64_encode($var);
		else return md5($var);
	}
//==============================================================================================
// PRIVATE initCode(), called in function makeTags()
// generates the required parameters to encrypt the generated hidden names and values
//==============================================================================================
	function initCode(){
		$r1=rand(10,124);
		$r2=rand(4,12);
		$r3=rand(17,89);
		$r4=rand(199,489);
		$r5=rand(1,42);
		$r6=rand(312,999);
		$userIDNameStart=rand(0,31);
		$userIDNameLength=(32-$userIDNameStart);
		$userIDValueStart=rand(0,31);
		$userIDValueLength=(32-$userIDValueStart);
		$dynIDNameStart=rand(0,31);
		$dynIDNameLength=(32-$dynIDNameStart);
		$this->userIDNamestart=$userIDNameStart;
		$this->userIDNameLength=$userIDNameLength;
		$this->userIDValuestart=$userIDValueStart;
		$this->userIDValueLength=$userIDValueLength;
		$this->dynIDNamestart=$dynIDNameStart;
		$this->dynIDNameLength=$dynIDNameLength;
		$this->codeInit=$r1.".".$userIDNameStart.".".$r2.".".$userIDNameLength.".".$r3.".".$userIDValueStart.".".$r4.".".$userIDValueLength.".".$r5.".".$dynIDNameStart.".".$r6.".".$dynIDNameLength;
	}
//==============================================================================================
// PRIVATE getCodeID(), called in function checkTags()
// sets the required perameters for the code decryption
//==============================================================================================
	function getCodeID($key){
		$keys=explode(".",$key);
		$this->userIDNamestart=$keys[1];
		$this->userIDNameLength=$keys[3];
		$this->userIDValuestart=$keys[5];
		$this->userIDValueLength=$keys[7];
		$this->dynIDNamestart=$keys[9];
		$this->dynIDNameLength=$keys[11];
	}
//==============================================================================================
// PRIVATE checkUserID() [boolean], called by function checkTags()
// checks if there is a valid userID in an array specified
//==============================================================================================
	function checkUserID($arr=array()){
		$found=false;
		$userID=$this->intUserID();
		$tagName=substr($userID, $this->userIDNamestart, $this->userIDNameLength);
		$tagValue=substr($userID, $this->userIDValuestart, $this->userIDValueLength);
			foreach ($arr as $name=>$value){
				if ($tagName==$name && $tagValue==$value){
					$found=true;
					$this->userIDName=$name;
				}
			}
		return $found;
	}
//==============================================================================================
// PRIVATE checkDynID() [boolean], called by function checkTags()
// checks if there is a valid dynID in an array specified
//==============================================================================================
	function checkDynID($arr=array()){
	    $found = null;
		$actDay=date("j");
		$actMonth=date("n");
		$actYear=date("Y");
		$now=time();
		$today=mktime(0,0,0,$actMonth,$actDay,$actYear);
		$yesterday=mktime(0,0,0,$actMonth,$actDay-1,$actYear);
		$indelay=$now-$today-($this->minutesAfterMidnight*60);
		$checktoday=substr($this->enc($today.$this->initKey), $this->dynIDNamestart, $this->dynIDNameLength);
		$checkyesterday=substr($this->enc($yesterday.$this->initKey), $this->dynIDNamestart, $this->dynIDNameLength);
			foreach ($arr as $name=>$value){
				if ($name==$checktoday OR ($name==$checkyesterday && $indelay<=0)){
					$val=base64_decode($value);
					if ($this->checkSubmisionTime($val)){
						$found=true;
						$this->dynIDName=$name;
					}
				}
			}
		return $found;
	}
//==============================================================================================
// PRIVATE checkSubmisionTime() [boolean], called by function checkDynID()
// checks if the form was submitted within the time period, set by minTime and maxTime variables
//==============================================================================================
	function checkSubmisionTime($var){
		$now=time();
		$elapsed=$now-$var;
		if (($elapsed<$this->minTime) OR ($elapsed>$this->maxTime)) return false;
		else return true; 
	}
//==============================================================================================
// PRIVATE checkTrap() [boolean], called by function checkTags()
// checks if a parameter, hidden by CSS, has some value
//==============================================================================================
	function checkTrap($arr=array()){
		$noTrap=true;
			foreach ($arr as $name=>$value){
				if ($name==$this->trapName && $value!="") $noTrap=false;
			}
		return $noTrap;	
	}
}
?>