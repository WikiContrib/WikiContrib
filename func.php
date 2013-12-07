<?php
ini_set('max_execution_time', 300);

/**
 * Function name: user_exist
 *
 * Checks if the entered username is valid on the current wikisite
 *
 * @param $nom - username that is to be verified
 * @param $wikisite - website where the username is to be verified
 * @return bool - True if the username in valid, false otherwise
 */
function user_exist($nom, $wikisite){
	$jsonurl = $wikisite.'/w/api.php?action=query&list=users&format=json&usprop=registration&ususers='.$nom;
	$json = curl_get_file_contents($jsonurl);
    $res = $json['content'];    //The json content received from the $jsonurl
    $obj = json_decode($res,true);
	$user = $obj['query']['users'];
    if(isset($user[0])){
        $result = array_key_exists('registration',$user[0]);
    } else {
        $result = false;
    }
    return $result;
}


/**
 * Function name: curl_get_file_contents
 *
 * Grabs the data from a given url
 *
 * @param $url - url that is to be processed
 * @return String - returns a json encoded string
 */
function curl_get_file_contents( $url ){
    $options = array(
        CURLOPT_RETURNTRANSFER => true,             // return web page
        CURLOPT_HEADER         => false,            // don't return headers
        CURLOPT_FOLLOWLOCATION => true,             // follow redirects
        CURLOPT_ENCODING       => "",               // handle all encodings
        CURLOPT_USERAGENT      => "Wiki crawler",   // who am i
        CURLOPT_AUTOREFERER    => true,             // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,              // timeout on connect
        CURLOPT_TIMEOUT        => 120,              // timeout on response
        CURLOPT_MAXREDIRS      => 10,               // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}

/**
 * Function name: createdByUser
 *
 * This function is processing multiple queries simultaneously
 * Grabs all the contributions made by a given user, from a given url, in a given year
 * and keeps only the contributions where the ['parentid'] field will be equal to "0"
 *
 * @param $nom - the user that will be investigated
 * @param $wikisite - the website where the investigation will take place
 * @param $annee - year for which the investigation will take place
 * @return array - returns an array containing the page's id, title and the timestamp which indicates when the page was created
 */
function createdByUser($nom,$wikisite,$annee){
    $nom = urlencode($nom);                 // some names can contain spaces
    $rvstart = $annee."-01-01T00:00:00Z";   // the beginning of the first year
    $anneefin = $annee + 1;
    $rvend = $anneefin."-01-01T00:00:00Z";  // the beginning of the second year
    $nodes = array();   //list of urls needed to grab data

    /**
     * this loop creates a list of urls, each of them being a query for all user's contributions in a three-day period
     * @var $currentDate - the beginning of the time interval for each query
     * @var $newEndDate - the end of the time interval for each query
     */
    for($currentDate = strtotime($rvstart); $currentDate <= strtotime($rvend); $currentDate += (60 * 60 * 24 * 3)){
        $year = gmdate('Y',$currentDate);
        $month = gmdate('m',$currentDate);
        $day = gmdate('d',$currentDate);
        $hour = gmdate('H',$currentDate);
        $min = gmdate('i',$currentDate);
        $sec = gmdate('s',$currentDate);

        $endYear = gmdate('Y',$currentDate + (60 * 60 * 24 * 3)); // currentDate + 3 days
        $endMonth = gmdate('m',$currentDate + (60 * 60 * 24 * 3));
        $endDay = gmdate('d',$currentDate + (60 * 60 * 24 * 3));
        $endHour = gmdate('H',$currentDate + (60 * 60 * 24 * 3));
        $endMin = gmdate('i',$currentDate + (60 * 60 * 24 * 3));
        $endSec = gmdate('s',$currentDate + (60 * 60 * 24 * 3));
        $newEndDate = $endYear."-".$endMonth."-".$endDay."T".$endHour.":".$endMin.":".$endSec."Z";

        // if the end date is later than the first day of the second year, $newEndDate will become the first day of the second year
        if($currentDate + (60 * 60 * 24 * 3) > strtotime($rvend)){
            $fin = strtotime($rvend);
            $endYear = gmdate('Y',$fin);
            $endMonth = gmdate('m',$fin);
            $endDay = gmdate('d',$fin);
            $endHour = gmdate('H',$fin);
            $endMin = gmdate('i',$fin);
            $endSec = gmdate('s',$fin);
            $newEndDate = $endYear."-".$endMonth."-".$endDay."T".$endHour.":".$endMin.":".$endSec."Z";
        }

        $newStartDate = $year."-".$month."-".$day."T".$hour.":".$min.":".$sec."Z";

        $jsonurl = $wikisite."/w/api.php?action=query&list=usercontribs&format=json&uclimit=max&ucstart=".$newStartDate."&ucend=".$newEndDate."&ucuser=".$nom."&ucnamespace=0%7C4%7C6%7C8&ucdir=newer&ucprop=ids%7Ctitle%7Ctimestamp";
        $nodes[] = $jsonurl;    // add url to the list
    }

    // start processing multiple queries simultaneously
    $mh = curl_multi_init();
    $curl_array = array();
    foreach($nodes as $i => $url)
    {
        $curl_array[$i] = curl_init($url);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $curl_array[$i]);
    }
    $running = NULL;
    do {
        usleep(10000);
        curl_multi_exec($mh,$running);
    } while($running > 0);

    $res = array();
    $result = array();
    foreach($nodes as $i => $url){
        $res[$url] = curl_multi_getcontent($curl_array[$i]);
        $obj[$url] = json_decode($res[$url],true);
        $contribution[$url] = $obj[$url]['query']['usercontribs'];
        if(sizeof($contribution[$url]) != 0){   // if the content is not empty, continue
            foreach($contribution[$url] as $contrib){
                if($contrib['parentid'] == 0){  //if parentid = 0, that means this contribution was the creation of the page
                    $result[] = Array($contrib['pageid'],$contrib['title'],$contrib['timestamp']);
                }
            }
        }
    }

    foreach($nodes as $i => $url){
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }
    curl_multi_close($mh);
    // stop processing multiple queries simultaneously

    return $result;
}

/**
 * Function name: getUsersLatestContrib
 *
 * Finds the date of the last contribution of the given user, on a given page, on a given website
 *
 * @param $user
 * @param $wikisite
 * @param $pageId
 * @return String  - returns a string of a wiki timestamp
 */
function getUsersLatestContrib($user, $wikisite, $pageId){
    $result = '';
    $jsonurl = $wikisite."/w/api.php?action=query&prop=revisions&format=json&rvprop=ids%7Ctimestamp%7Cuser&rvlimit=1&rvdir=older&rvuser=".$user."&pageids=".$pageId;
    $json = curl_get_file_contents($jsonurl);
    $res = $json['content'];
    $obj = json_decode($res,true);
    if(!is_null($obj['query']['pages'][$pageId]['revisions'])){
        $result = $obj['query']['pages'][$pageId]['revisions'][0]['timestamp'];
    }
    return $result;
}

/**
 * (currently not in use!)
 * Function name: getLatestContrib
 *
 * Finds the date of the last contribution on a given page, on a given website
 *
 * @param $wikisite
 * @param $pageId
 * @return String  - returns a string of a wiki timestamp
 */
function getLatestContrib($wikisite, $pageId){
    $result = '';
    $jsonurl = $wikisite."/w/api.php?action=query&prop=revisions&format=json&rvprop=ids%7Ctimestamp%7Cuser&rvlimit=1&rvdir=older&pageids=".$pageId;
    $json = file_get_contents($jsonurl, true);
    $obj = json_decode($json,true);
    if(!is_null($obj['query']['pages'][$pageId]['revisions'])){
        $result = $obj['query']['pages'][$pageId]['revisions'][0]['timestamp'];
    }
    return $result;
}

/**
 * (currently not in use!)
 * Function name: calcTimestampDiff
 *
 * Computes the difference in days between a wiki timestamp and today's date
 *
 * @param $timestamp
 * @return Integer  - returns a number of days since the given value of the timestamp
 */
function calcTimestampDiff($timestamp){
    $d1 = new DateTime($timestamp);
    $d2 = new DateTime('now');
    $interval = $d2->diff($d1);
    return $interval->days;
}

/*
Fonction subscription:
	permet de trouver la date d'inscription
	et de la retourner en String
*/
function subscription($name,$wikisite){
	    $jsonurl = $wikisite."/w/api.php?action=query&list=users&format=json&usprop=registration&ususers=".$name;
		$json = file_get_contents($jsonurl, true);
		$obj = json_decode($json,true);
		$date = substr($obj['query']['users'][0]['registration'],0,10);
		return $date;		
}
/*
Fonction sexe:
	permet de trouver la  sexe 
	et de la retourner en String
*/
function sexe($name,$wikisite){
	    $jsonurl = $wikisite."/w/api.php?action=query&list=users&format=json&usprop=gender&ususers=".$name;
		$json = file_get_contents($jsonurl, true);
		$obj = json_decode($json,true);
		$sexe = $obj['query']['users'][0]['gender'];
		if($sexe == "male"){
			return "Masculin";
		}elseif($sexe=="female"){
			return "Feminin";
		}else{
		return "Inconnu";
		}
}