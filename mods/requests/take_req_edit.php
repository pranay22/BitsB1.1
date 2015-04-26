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

$res = sql_query("SELECT userid, cat FROM requests WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$num = mysql_fetch_assoc($res);

if ($CURUSER['id'] != $num['userid'] && $CURUSER['class'] < UC_MODERATOR)
    stderr('Error', 'Access denied.');
    
$request = (isset($_POST['requesttitle']) ? htmlspecialchars($_POST['requesttitle']) : '');

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

$descr  = "$pic";
$descr .= isset($_POST['body']) ? $_POST['body'] : '';

if (!$descr)
    stderr('Error', 'You must enter a description!');

$cat = (isset($_POST['category']) ? (int)$_POST['category'] : ($num['cat'] != '' ? $num['cat'] : 0));

if (!is_valid_id($cat))
	stderr('Error', 'You must select a category to put the request in!');
	
$request    = sqlesc($request);
$descr      = sqlesc($descr);
$filledby   = isset($_POST['filledby']) ? (int)$_POST['filledby'] : 0;
$filled     = isset($_POST['filled']) ? $_POST['filled'] : 0;
$torrentid  = isset($_POST['torrentid']) ? (int)$_POST['torrentid'] : 0;

if ($filled) {
    if (!is_valid_id($torrentid))
	    stderr('Error', 'Not a valid torrent ID!');
    	   
    // could play around here if want to allow own requests or to fill as System, etc. =]
    
    //if ($CURUSER['id'] == $filledby)
        //stderr('Error', 'ID is your own. Cannot fill your own Requests.');
        //$filledby = 0;
    //else {
        $res = sql_query("SELECT id FROM users WHERE id = ".$filledby);
        if (mysql_num_rows($res) == 0)
               stderr('Error', 'ID doesn\'t match any users, try again');    
  //  }    
   $res = sql_query("SELECT id FROM torrents WHERE id = ".$torrentid);
    if (mysql_num_rows($res) == 0)
           stderr('Error', 'ID doesn\'t match any torrents, try again');
    	   
    sql_query("UPDATE requests SET cat = $cat, request = $request, descr = $descr, filledby = $filledby, torrentid=$torrentid WHERE id = $id") or sqlerr(__FILE__,__LINE__);
}
else
    sql_query("UPDATE requests SET cat = $cat, filledby = 0, request = $request, descr = $descr, torrentid = 0 WHERE id = $id") or sqlerr(__FILE__,__LINE__);

header("Refresh: 0; url=viewrequests.php?id=$id&req_details");

?>