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
	
$res = sql_query('SELECT * FROM voted_requests WHERE requestid = '.$id.' and userid = '.$CURUSER['id']) or sqlerr(__FILE__,__LINE__);
$arr = mysql_fetch_assoc($res);

if ($arr) {
    $HTMLOUT .= "
<h3>You've Already Voted</h3>
<p style='text-decoration:underline;'>1 vote per request is allowed</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>request details</b></a> | 
<a class='altlink' href='viewrequests.php'><b>all requests</b></a></p>
<br /><br />";
}
else {
    sql_query('UPDATE requests SET hits = hits+1 WHERE id='.$id) or sqlerr(__FILE__,__LINE__);
    if (mysql_affected_rows()) {
        mysql_query('INSERT INTO voted_requests VALUES(0, '.$id.', '.$CURUSER['id'].')') or sqlerr(__FILE__,__LINE__);
        $HTMLOUT .=  "
<h3>Vote accepted</h3>
<p style='text-decoration:underline;'>Successfully voted for request $id</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>request details</b></a> |
<a class='altlink' href='viewrequests.php'><b>all requests</b></a></p>
<br /><br />";
    } else {
        $HTMLOUT .=  "
<h3>Error</h3>
<p style='text-decoration:underline;'>No such ID $id</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>request details</b></a> |
<a class='altlink' href='viewrequests.php'><b>all requests</b></a></p>
<br /><br />"; 
    }
}

/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Vote').$HTMLOUT.stdfoot();
?>