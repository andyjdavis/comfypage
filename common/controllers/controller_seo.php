<?php
define('SEARCHRANK_STORE_DIR','site/store/searchranks');

define('SEO_MAX_RESULTS',100);
//define('FREE_MAX_RESULTS',100);
//define('COUCH_MAX_RESULTS',1000);

class controller_seo
{
    //public interface
    //------------------------------------------------------------------

    /**
     * Check we havent asked google in the past 1-7 days as the indexes probably
     * won't have changed and we may get banned for querying over and over
     */
    public function can_query_google($keyphrase) {
	require_once('common/users_items/SearchRank.php');
	$sr = new SearchRank($keyphrase);
	$ranks = $sr->get(SEARCHRANK_ARRAY);

	if( !$ranks || sizeof($ranks)==0 ) {
	    return true;
	}

	$keys = array_keys($ranks);
	ksort($keys);
	$last_queried = $keys[sizeof($keys)-1];

        $t = time();

	if( $t < ($last_queried-172800) ) { //172800 == 2 days in seconds
	    return true;
	}
	return false;
    }
    public function get_top_rank() {
	require_once('common/file.php');
	require_once('common/users_items/SearchRank.php');

	$toprank=-1;

	$file_admin = new FileAdmin();
	$keywords = $file_admin->get_file_list(SEARCHRANK_STORE_DIR, false, true);
	foreach($keywords as $keyword) {
	    $keyword = substr($keyword,0,-4); //chop '.php' off file name
	    $sr = new SearchRank($keyword);
	    foreach($sr->get(SEARCHRANK_ARRAY) as $ts=>$r) {
		if($r>$toprank) {
		    $toprank = $r;
		}
	    }
	}
	return $toprank;
    }
    public function get_keyphrase_rank($keyphrase, $siteid, $max=100) {
	require_once('common/users_items/SearchRank.php');
	require_once('common/lib/PhpKeywordAnalyser.inc.php');
	$analyzer = new PhpKeywordAnalyser($keyphrase, $siteid, $max);
	$rank = $analyzer->getRank();

	$this->check_setup();

	$rankObj = new SearchRank($keyphrase);
	$rankObj->add_rank($rank, time());
	$rankObj->commit();

	return $rank;
    }
    public function is_site_in_google($siteid) {
	//check previous results. if we're listed for any keyword then we're listed
	require_once('common/file.php');
	require_once('common/users_items/SearchRank.php');

	$file_admin = new FileAdmin();
	$keywords = $file_admin->get_file_list(SEARCHRANK_STORE_DIR, false, true);
	foreach($keywords as $keyword) {
	    $keyword = substr($keyword,0,-4); //chop '.php' off file name
	    $sr = new SearchRank($keyword);
	    foreach($sr->get(SEARCHRANK_ARRAY) as $ts=>$r) {
		if($r>0) {
		    return true;
		}
	    }
	}

	//if not found then ask google by searching for our domain
	return $this->get_keyphrase_rank($siteid, $siteid) > 0;
    }
    public function get_keyword_rank_table($keyword) {
	require_once('common/users_items/SearchRank.php');
	$ranks = array();

	//todo if they have a bunch of -1 ranks at the beginning dont bother displaying them

	$s = "<table id='keywordtable'><tr><th style='padding-right:50px;'>date</th><th>rank</th></tr>";
	$sr = new SearchRank($keyword);

	$d = null;
	$gotrank = false;
	foreach($sr->get(SEARCHRANK_ARRAY) as $ts=>$r) {
	    if($r==-1 and !$gotrank) {
		continue; //dont bother including a bunch of -1s at the beginning
	    }
	    if($gotrank && $r>0) {
		$gotrank = true;
	    }
	    $d = date('j M',$ts);
	    $s.="<tr><td>$d</td><td>$r</td></tr>";
	    $ranks[] = $r;
	}

	/*$s = "<table id='keywordtable' style='border:1px solid gray;width:100%;'>";
	$sr = new SearchRank($keyword);
	$ranks = $sr->get(SEARCHRANK_ARRAY);

	$d = null;
	//$firstrow = "<thead><tr><td></td><th>$d</th>";
	//$secondrow = "<tbody><tr><th>$keyword</th><td>-100</td>";
	$firstrow = "<thead><tr><td></td><th></th>";
	$secondrow = "<tbody><tr><th>$keyword</th><td></td>";
	foreach($ranks as $ts=>$r) {
	    $d = date('j M',$ts);
	    $firstrow.="<th>$d</th>";
	    $r = 0-$r;
	    $secondrow.="<td>$r</td>";
	}
	$firstrow.='<th></th></tr></thead>';
	$secondrow.='<td></td></tr></tbody>';
	$s.=$firstrow;
	$s.=$secondrow;*/

	$s.='</table>';
	return array($s,$ranks);
    }
    public function get_sorted_array_of_words($s) {
	$s = trim($s);
	$s = $this->strip_html_tags($s);
	$s = html_entity_decode( $s, ENT_QUOTES, "UTF-8" );
	$s = strtolower($s);

	include('common/stop_words.php');

	$array = preg_split('@[\W]+@', $s, -1, PREG_SPLIT_NO_EMPTY);

	$sorted_array = array_count_values($array);
	arsort($sorted_array);
	$word_count = count($sorted_array);

	//only return 10 most frequent words
	$new_sorted_array = array();
	$i = 0;
	foreach($sorted_array as $k=>$v)
	{
		if(!in_array($k, $stop_words))
		{
			$new_sorted_array[$k] = $v;
			$i++;
			if($i>4)
			{
				break;
			}
		}
	}
	return array($word_count, $new_sorted_array);
    }

    //private stuff
    //------------------------------------------------------------------
    private function check_setup() {
	require_once('common/utils/Globals.php');
	require_once('common/file.php');

	$file_admin = new FileAdmin();
	if(!$file_admin->folder_does_exist(SEARCHRANK_STORE_DIR))
	{
		$file_admin->mkdir_r(SEARCHRANK_STORE_DIR, 0744);
	}
    }

    /**
    * Remove HTML tags, including invisible text such as style and
    * script code, and embedded objects.  Add line breaks around
    * block-level tags to prevent word joining after tag removal.
    */
    private function strip_html_tags( $text )
    {
	$text = preg_replace(
	    array(
	      // Remove invisible content
		'@<head[^>]*?>.*?</head>@siu',
		'@<style[^>]*?>.*?</style>@siu',
		'@<script[^>]*?.*?</script>@siu',
		'@<object[^>]*?.*?</object>@siu',
		'@<embed[^>]*?.*?</embed>@siu',
		'@<applet[^>]*?.*?</applet>@siu',
		'@<noframes[^>]*?.*?</noframes>@siu',
		'@<noscript[^>]*?.*?</noscript>@siu',
		'@<noembed[^>]*?.*?</noembed>@siu',
	      // Add line breaks before and after blocks
		'@</?((address)|(blockquote)|(center)|(del))@iu',
		'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
		'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
		'@</?((table)|(th)|(td)|(caption))@iu',
		'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
		'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
		'@</?((frameset)|(frame)|(iframe))@iu',
	    ),
	    array(
		' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
		"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
		"\n\$0", "\n\$0",
	    ),
	    $text );
	    return strip_tags( $text );
	}
    }
?>