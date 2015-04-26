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

$res = sql_query('SELECT userid, request FROM requests WHERE id = '.$id) or sqlerr(__FILE__, __LINE__);
$num = mysql_fetch_assoc($res);

if ($CURUSER['id'] != $num['userid'] && $CURUSER['class'] < UC_MODERATOR)
    stderr("Error", "This is not your Request to delete!");	

if (!isset($_GET['sure']))
    stderr('Delete Request', "You`re about to delete this request. Click\n <a class='altlink' href='viewrequests.php?id=$id&amp;del_req&amp;sure=1'>here</a>, if you`re sure.", false);
else {
    sql_query('DELETE FROM requests WHERE id = '.$id) or sqlerr(__FILE__,__LINE__);
    sql_query('DELETE FROM voted_requests WHERE requestid = '.$id) or sqlerr(__FILE__,__LINE__);
    sql_query('DELETE FROM comments WHERE request = '.$id) or sqlerr(__FILE__,__LINE__);
    write_log('Request: '.$id.' ('.$num['request'].') was deleted from the Request section by '.$CURUSER['username']);

    header('Refresh: 0; url=viewrequests.php');
}
?>