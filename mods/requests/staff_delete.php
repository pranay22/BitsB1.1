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

if ($CURUSER['class'] >= UC_MODERATOR) {
    
    if (empty($_POST['delreq']))
       stderr('ERROR', "Don't leave any fields blank.");
       
    sql_query("DELETE FROM requests WHERE id IN (".implode(", ", array_map("sqlesc",$_POST['delreq'])).")");
    sql_query("DELETE FROM voted_requests WHERE requestid IN (".implode(", ", array_map("sqlesc",$_POST['delreq'])).")");
    sql_query("DELETE FROM comments WHERE request IN (".implode(", ", array_map("sqlesc",$_POST['delreq'])).")");
    header('Refresh: 0; url=viewrequests.php');
    die();
}
else
    stderr('ERROR', 'tweedle-dee tweedle-dum');
?>