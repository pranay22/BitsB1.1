<?php 
/*
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.0
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
+------------------------------------------------
*/
 
require_once("include/bittorrent.php"); 

dbconn(); 

loggedinorreturn(); 
global $TBDEV; 
$pm_what = isset($_POST["pm_what"]) && $_POST["pm_what"] =="last10" ? "last10" : "owner"; 
$reseedid = 0 + $_POST["reseedid"]; 
$uploader = 0 + $_POST["uploader"]; 
$use_subject = true; //set it to false if you dont use subject in pms 
$subject = "Request reseed!"; 
$pm_msg = "User " . $CURUSER["username"] . " asked for a reseed on torrent ".$TBDEV['baseurl']."/details.php?id=" . $reseedid . " !\nThank You!"; 
 
$pms = array(); 
if ($pm_what == "last10" ) { 
        $res = mysql_query("SELECT snatched.userid, snatched.torrentid FROM snatched  where snatched.torrentid =$reseedid AND snatched.seeder='yes' LIMIT 10") or sqlerr(__FILE__, __LINE__); 
        while($row = mysql_fetch_assoc($res)) 
                $pms[] = "(0,".$row["userid"].",".sqlesc(time()).",".sqlesc($pm_msg).($use_subject ? ",".sqlesc($subject) : "").")"; 
} 
elseif($pm_what == "owner") 
                $pms[] = "(0,$uploader,".sqlesc(time()).",".sqlesc($pm_msg).($use_subject ? ",".sqlesc($subject) : "").")"; 
                 
if(count($pms) > 0)              
mysql_query("INSERT INTO messages (sender, receiver, added, msg ".($use_subject ? ", subject" : "")." ) VALUES ".join(",",$pms)) or sqlerr(__FILE__, __LINE__); 
 
mysql_query("UPDATE torrents set last_reseed=".sqlesc(time())." WHERE id= $reseedid ") or sqlerr(__FILE__, __LINE__); 
header("Refresh: 0; url=./details.php?id=$reseedid&reseed=1"); 
?>