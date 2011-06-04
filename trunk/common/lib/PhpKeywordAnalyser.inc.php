<?php 
/**
 * This class crawls google and find out on which page a website is lisited, based on the keyword searched.
 *
 * @author Rochak Chauhan <rochakchauhan@gmail.com>
 * @see This class does NOT calculated the Google Page Rank
 */
class PhpKeywordAnalyser {
	private $website="";
	private $keyword="";
	private $url="";
	private $start=0;
	private $page=false;
	private $records=false;

	/**
	 * Function to pre process and store the values of Keyword and Website
	 *
	 * @param string $keyword
	 * @param string $website
	 * @return resource
	 */
	public function __construct($keyword, $website, $maxresults){
		if(trim($keyword)==""){
			trigger_error("Keyword cannot the be blank.",E_USER_ERROR); die();
		}
		if(trim($website)==""){
			trigger_error("Website cannot be left blank.",E_USER_ERROR); die();
		}
		$website=strtolower(trim($website));
		$website=str_replace("http://www.","",$website);
		$website=str_replace("http://","",$website);
		$website=str_replace("www.","",$website);
		$website=str_replace("/","",$website);
	
		$this->website=$website;
		$this->keyword=trim($keyword);
		$this->maxresults = $maxresults;
		//$this->enableVerbose=$enableVerbose;
                $this->enableVerbose=null;
		$this->start = 0;
		$this->updateUrl($keyword, $this->start);
	}

	/**
	 * This function starts the crawling process and it verbose the content as it goes to next page.
	 *
	 * @return string [buffer]
	 */
	public function getRank() {
	    $i = 100;
	    $maxqueries = round($this->maxresults/$i);
	    $querynum = 0;
	    while( $querynum<$maxqueries) {
		$records= $this->getRecordsAsArray($this->url);
		$count=count($records);
		for($n=1;$n<$count;$n++){
		    $link=$records[$n-1][2];
		    $link=strip_tags($link);
		    $link=str_replace("http://www.","",$link);
		    $link=str_replace("http://","",$link);
		    $link=str_replace("www.","",$link);
		    $pos=strpos($link, "/");
		    $link=trim(substr($link,0,$pos));

		    if($this->website==$link){
			    return $n;
		    }
		}
		$this->updateUrl($this->keyword, $i);
		$querynum++;
	    }
	    return -1;
	}

	/**
	 * This function starts the crawling process and it verbose the content as it goes to next page.
	 *
	 * @return string [buffer]
	 */
	/*public function initSpider(){
		echo "<p>Searching: <b>".$this->keyword."</b> and Looking for: <b>".$this->website."</b></p>";
		echo str_repeat(" ", 256);
		$i=10;
		$c=1;
		while($c<=10) {			
			echo "<ul><li><b>Searching in Page: $c</b></li>";				
			flush();ob_flush();
			$records= $this->getRecordsAsArray($this->url); 
			$count=count($records);
			echo "<ul>";
			for($k=0;$k<$count;$k++){
				$j=$k+1;
				$link=$records[$k][2];
				$link=strip_tags($link);
				$link=str_replace("http://www.","",$link);
				$link=str_replace("http://","",$link);
				$link=str_replace("www.","",$link);
				$pos=strpos($link, "/");
				$link=trim(substr($link,0,$pos));
				
				if($this->website==$link){
					$domain=$this->website;
					echo "<li><h1>Result was found in Page: $c and Record: $j</h1></li>";
					echo "<div>Congrats, We searched google's top 10 pages for <b>\"".$this->keyword."</b>\", we found your domain <b>\"$domain\"</b> listed on page: $c at $j place </div>";echo "</ul></ul>";
					exit;
				}
				else{
					echo "<li>Result not found on Page: $c and Record: $j</li>";
				}				
			}
			echo "</ul></ul>";
			$c++;
			$this->updateUrl($this->keyword, $i);
		}
		echo "Crawled through all 10 pages.";			
		
		if($this->page==false){
			$domain=$this->website;
			$keyword=$this->keyword;
			echo "<div>Sorry, We searched google's top 10 pages for <b>\"$keyword\"</b>, but was unable to find your domain <b>\"$domain\"</b> listed anywhere. </div>";
		}
		else {
			$page=$this->page;
			$records=$this->records;
			$domain=$this->website;
			$keyword=$this->keyword;
			echo "<div>Congrats, We searched google's top 10 pages for <b>\"$keyword\"</b>, we found your domain <b>\"$domain\"</b> listed on page: $page at $record place </div>";
		}
	}*/
	
	/**
	 * Function to get records as an array.
	 *
	 * @access private
	 * @param string $url
	 *
	 * @return array
	 */
	private function getRecordsAsArray($url){
		$matches=array();
		$pattern='/<div class="s"(.*)\<cite\>(.*)\<\/cite\>/Uis';
		$html=$this->getCodeViaFopen($url);
		preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
		return $matches;
	}
	
	/**
	 * Function to update the google search query.
	 *
	 * @access private
	 * @param string $keyword
	 * @param string $start
	 *
	 * @return string
	 */
	private function updateUrl($keyword, $start){
		$this->start=$this->start+$start;	
		$keyword=trim($keyword);
		$keyword=urlencode($keyword);
		//return "http://www.google.com/search?start=".$this->start."&q=$keyword";
		$this->url = "http://www.google.com/search?&q=$keyword&num=100&start={$this->start}";
		return $this->url;
	}

	/**
	 * Function to get HTML code from remote url
	 *
	 * @access private
	 * @param string $url
	 *
	 * @return string
	 */
	private function getCodeViaFopen($url){
		$returnStr="";
		$fp=fopen($url, "r") or die("ERROR: Invalid search URL");
		while (!feof($fp)) {
			$returnStr.=fgetc($fp);
		}
		fclose($fp);
		return $returnStr;
	}
}
