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

$torrentid = (isset($_POST['torrentid']) ? (int)$_POST['torrentid'] : 0);

if ($torrentid < 1)
    stderr('Error', 'That ID looks funky!');  

$res = sql_query("SELECT id FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__,__LINE__);
$arr = mysql_fetch_assoc($res);

if (!$arr)
    stderr("Error", "No torrent with that ID $torrentid");
          
$res = sql_query("SELECT users.username, requests.userid, requests.torrentid, requests.request FROM requests inner join users on requests.userid = users.id where requests.id = $id") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res);
if ($CURUSER['id'] == $arr['userid'])
    stderr('Error', 'ID is your own. Cannot fill your own Requests.');

$msg = "Your request, [b]".htmlspecialchars($arr['request'])."[/b] has been filled by [b]".$CURUSER['username']."[/b]. You can download your request from [b][url=details.php?id=".$torrentid."]".$TBDEV['baseurl']."/details.php?id=".$torrentid."[/url][/b].  Please do not forget to leave thanks where due.  

If for some reason this is not what you requested, please reset your request so someone else can fill it by following [b][url=".$TBDEV['baseurl']."/viewrequests.php?id=$id&req_reset]this[/url][/b] link.  Do [b]NOT[/b] follow this link unless you are sure that this does not match your request.";

sql_query("UPDATE requests SET torrentid = ".$torrentid.", filledby = $CURUSER[id] WHERE id = $id") or sqlerr(__FILE__, __LINE__);

sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, location) VALUES(0, 0, $arr[userid], ".TIME_NOW.", ".sqlesc($msg).", 'Request Filled', 1)") or sqlerr(__FILE__, __LINE__);
//$Cache->delete_value('inbox_new_'.$arr['userid'].'');

if ($TBDEV['karma'] && isset($CURUSER['seedbonus']))
    sql_query("UPDATE users SET seedbonus = seedbonus+".$TBDEV['req_comment_bonus']." WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);

$res = sql_query("SELECT `userid` FROM `voted_requests` WHERE `requestid` = $id AND userid != $arr[userid]") or sqlerr(__FILE__, __LINE__);

$msgs_buffer = array();

if (mysql_num_rows($res) > 0) {
    
    $pn_subject = sqlesc("Request ".$arr['request']." was just uploaded");
    
    $pn_msg     = sqlesc("The Request you voted for [b]".$arr['request']."[/b] has been filled by [b]".
    $CURUSER['username']."[/b]. You can download your request from 
    [b][url=details.php?id=".$torrentid."]".$TBDEV['baseurl']."/details.php?id=".$torrentid."[/url][/b].
      Please do not forget to leave thanks where due.");

    while ($row = mysql_fetch_assoc($res))
        $msgs_buffer[]= '(0, '.$row['userid'].', '.TIME_NOW.', '.$pn_msg.', '.$pn_subject.')';

    $pn_count = count($msgs_buffer);
        if ($pn_count > 0) {
            sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ".implode(', ',$msgs_buffer)) or sqlerr(__FILE__,__LINE__);
            //write_log('[Request Filled Messaged '.$pn_count.' members');
        }
    unset ($msgs_buffer);
}
mysql_free_result($res);

$HTMLOUT .= "<table class='main' width='750px' border='0' cellspacing='0' cellpadding='0'>" .
      "<tr><td class='embedded'>\n";

$HTMLOUT .=  "<h1 align='center'>Success!</h1>
<table cellspacing='10' cellpadding='10'>
<tr><td align='left'>Request $id (".htmlspecialchars($arr['request']).") successfully filled with <a class='altlink' href='details.php?id=".$torrentid."'>".$TBDEV['baseurl']."/details.php?id=".$torrentid."</a>.  
<br /><br />User <a class='altlink' href='userdetails.php?id=$arr[userid]'><b>$arr[username]</b></a> automatically PMd.  <br /><br />
If you have made a mistake in filling in the URL or have realised that your torrent does not actually satisfy this request
, please reset the request so someone else can fill it by clicking <a class='altlink' href='viewrequests.php?id=$id&amp;req_reset'>HERE</a> 
<br /><br />Do <b>NOT</b> follow this link unless you are sure there is a problem.<br /><br />
<a class='altlink' href='viewrequests.php'>View all requests</a>
</td></tr></table>";

$HTMLOUT .= "</td></tr></table>\n";

/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Request Filled').$HTMLOUT.stdfoot();
?>