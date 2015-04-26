<?php 
/**
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.0
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
|   BitsB request system v1.2
+------------------------------------------------
**/

if (!defined('IN_BITSB_REQUESTS')) exit('No direct script access allowed');

$request = (isset($_POST['requesttitle']) ? $_POST['requesttitle'] : '');

if ($request == '')
    stderr('Error', 'You must enter a title!');	
	   
$cat = (isset($_POST['category']) ? (int)$_POST['category'] : 0);
if (!is_valid_id($cat))
    stderr('Error', 'You must select a category to put the request in!');	   

$descrmain =  (isset($_POST['body']) ? $_POST['body'] : '');
if (!$descrmain)
    stderr('Error', 'You must enter a description!');	

$pic = '';
if (!empty($_POST['picture'])) {
    if (!preg_match('/^https?:\/\/([a-zA-Z0-9\-\_]+\.)+([a-zA-Z]{1,5}[^\.])(\/[^<>]+)+\.(jpg|jpeg|gif|png|tif|tiff|bmp)$/i', $_POST['picture']))
        stderr('Error', "Picture MUST be in jpg, gif or png format. Make sure you include http:// in the URL.");

    $picture  = $_POST['picture'];
//    $picture2 = trim(urldecode($picture));
//    $headers  = get_headers($picture2);
//    if (strpos($headers[0], '200') === false)
//        $picture = $TBDEV['baseurl'].'/pic/notfound.png';
    $pic = "[img]".$picture."[/img]\n";
}

$descr    = "$pic";
$descr   .= "$descrmain";
$request2 = sqlesc($request);
$descr    = sqlesc($descr);

sql_query("INSERT INTO requests (hits, userid, cat, request, descr, added) VALUES(1,$CURUSER[id], $cat, $request2, $descr, ".TIME_NOW.")") or sqlerr(__FILE__,__LINE__);
$id = mysql_insert_id();

sql_query("INSERT INTO voted_requests VALUES(0, $id, $CURUSER[id])") or sqlerr();

if ($TBDEV['karma'] && isset($CURUSER['seedbonus']))
    mysql_query("UPDATE users SET seedbonus = seedbonus-".$TBDEV['req_cost_bonus']." WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);

write_log("Request (".$request.") was added to the Request section by $CURUSER[username]");

/** IRC announce **/
//$message = " [b][color=blue]New request[/color][/b]  [url=viewrequests.php?id=$id&req_details] ".$request."[/url]  ";
//autoshout($message);

header("Refresh: 0; url=viewrequests.php?id=$id&req_details");

?>