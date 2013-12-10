<?php

include 'func.php';
$pageId = rawurlencode($_POST['id']);
$pageTitle = str_replace(' ', '_', $_POST['titre']);
$wikisite = $_POST['wikisite'];
$year = $_POST['annee'];
$rvstart = $year . "-01-01T00%3A00%3A00Z";
$anneefin = $year + 1;
$rvend = $anneefin . "-01-01T00%3A00%3A00Z";
$username = urlencode($_POST['nom']);
$latestUsersTime = getUsersLatestContrib($username, $wikisite, $pageId); //the last contribution of the given user


/**
 *
 * This part of code is processing multiple queries simultaneously
 * Grabs all the data from http://stats.grok.se regarding page views for each month of the year
 * and computes the sum of page views during the given year
 *
 */
$total = 0;
if ($wikisite == 'http://en.wikipedia.org') {
    $lang = "en";
} else {
    $lang = "fr";
}
$nodes = array(
    'http://stats.grok.se/json/' . $lang . '/' . $year . '01/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '02/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '03/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '04/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '05/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '06/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '07/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '08/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '09/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '10/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '11/' . $pageTitle,
    'http://stats.grok.se/json/' . $lang . '/' . $year . '12/' . $pageTitle,
);
$mh = curl_multi_init();
$curl_array = array();

foreach ($nodes as $i => $url) {
    $curl_array[$i] = curl_init($url);
    curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
    curl_multi_add_handle($mh, $curl_array[$i]);
}
$running = NULL;
do {
    usleep(10000);
    curl_multi_exec($mh, $running);
} while ($running > 0);

foreach ($nodes as $i => $url) {
    $res[$url] = curl_multi_getcontent($curl_array[$i]);
    $obj[$url] = json_decode($res[$url], true);
    $visite[$url] = $obj[$url]['daily_views'];
    if (!is_null($visite[$url])) {
        $total += array_sum($visite[$url]);
    }
}

foreach ($nodes as $i => $url) {
    curl_multi_remove_handle($mh, $curl_array[$i]);
}
curl_multi_close($mh);
$result0 = $total; // the number of page views on the given page
//end page views

/**
 *
 * This part of code counts the number of modifications on the given page, during the given year
 *
 */
$result1 = 0;
$jsonurl = $wikisite . "/w/api.php?action=query&prop=revisions&format=json&rvprop=ids%7Ctimestamp&rvlimit=max&rvstart=" . $rvstart . "&rvend=" . $rvend . "&rvdir=newer&pageids=" . $pageId;
$json = curl_get_file_contents($jsonurl);
$res = $json['content'];
$obj = json_decode($res, true);
$revision = $obj['query']['pages'][$pageId]['revisions'];
$countrevision = count($revision);
$result1 = $countrevision;  // the number of modifications during the given year

/**
 *
 * This part of code counts the number of modifications on the given page since the last contribution of the given user
 *
 */
$result2 = 0;
$jsonurl = $wikisite . "/w/api.php?action=query&prop=revisions&format=json&rvprop=ids%7Ctimestamp%7Cuser&rvlimit=max&rvstart=" . $latestUsersTime . "&rvdir=newer&pageids=" . $pageId;
$json = curl_get_file_contents($jsonurl);
$res = $json['content'];
$obj = json_decode($res, true);
$revisions = $obj['query']['pages'][$pageId]['revisions'];
if (!is_null($revisions)) {
    $result2 = sizeof($revisions);  // the number of modifications since the last contribution of the given user
}

/**
 *
 * This part of code counts the number of days since the last contribution on the given page
 *
 */
$timestamp = '';
$result3 = 0;
$jsonurl = $wikisite . "/w/api.php?action=query&prop=revisions&format=json&rvprop=ids%7Ctimestamp%7Cuser&rvlimit=1&rvdir=older&pageids=" . $pageId;
$json = curl_get_file_contents($jsonurl);
$res = $json['content'];
$obj = json_decode($res, true);
if (!is_null($obj['query']['pages'][$pageId]['revisions'])) {
    $timestamp = $obj['query']['pages'][$pageId]['revisions'][0]['timestamp'];  // the timestamp of the last contribution on the given page
}
if ($timestamp != '') {
    $d1 = new DateTime($timestamp);
    $d2 = new DateTime('now');
    $interval = $d2->diff($d1);
    $result3 = $interval->days;     // the number of days since the last contribution on the given page
}

/**
 *
 * This part of code counts the number of days since the user's last contribution on the given page
 *
 */
$timestamp = '';
$result4 = 0;
$jsonurl = $wikisite . "/w/api.php?action=query&prop=revisions&format=json&rvprop=ids%7Ctimestamp%7Cuser&rvlimit=1&rvdir=older&rvuser=" . $username . "&pageids=" . $pageId;
$json = curl_get_file_contents($jsonurl);
$res = $json['content'];
$obj = json_decode($res, true);
if (!is_null($obj['query']['pages'][$pageId]['revisions'])) {
    $timestamp = $obj['query']['pages'][$pageId]['revisions'][0]['timestamp'];  // the timestamp of the user's last contribution on the given page
}
if ($timestamp != '') {
    $d1 = new DateTime($timestamp);
    $d2 = new DateTime('now');
    $interval = $d2->diff($d1);
    $result4 = $interval->days;     // the number of days since the user's last contribution on the given page
}

/**
 *
 * This part of code searches if the page is not redirected
 *
 */
$result5 = 'Non';
$jsonurl = $wikisite."/w/api.php?action=query&prop=info&format=json&pageids=".$pageId."&redirects=";
$json = curl_get_file_contents($jsonurl);
$res = $json['content'];
$obj = json_decode($res,true);
$isRedirected = isset($obj['query']['redirects']);
if($isRedirected){
    $result5 = 'Oui';
}

/**
 *
 * This part of code returns the page url, if the page redirects to another page, it will return the redirected page
 *
 */
$jsonurl = $wikisite."/w/api.php?action=query&prop=info&format=json&inprop=url&pageids=".$pageId;
$json = curl_get_file_contents($jsonurl);
$res = $json['content'];
$obj = json_decode($res,true);
$result6 = $obj['query']['pages'][$pageId]['fullurl'];

/**
 *
 * This array is sent back to the affichage.php page as a response to the "plus()" function's ajax call
 *
 */
$finalResult = array();
$finalResult[] = $result0;  // the number of page views on the given page
$finalResult[] = $result1;  // the number of modifications during the given year
$finalResult[] = $result2;  // the number of modifications since the last contribution of the given user
$finalResult[] = $result3;  // the number of days since the last contribution on the given page
$finalResult[] = $result4;  // the number of days since the user's last contribution on the given page
$finalResult[] = $result5;  // is redirected?
$finalResult[] = $result6;  // url

echo json_encode($finalResult);