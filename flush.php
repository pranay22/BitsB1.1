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

require_once ('include/bittorrent.php');
require_once (ROOT_PATH.'/include/user_functions.php');
dbconn();
loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('flush'));

$id = isset($_GET['id']) ? 0+$_GET['id'] : 0;
if($id == 0)
 $id = $CURUSER['id'];
if (($CURUSER['id'] != $id) && ($CURUSER['class'] < UC_MODERATOR)) {
	header("Refresh: 2; url={$TBDEV['baseurl']}/userdetails.php?id={$CURUSER['id']}");
	stderr($lang['flush_failed'], $lang['flush_authorised']);
 }
$res = mysql_query("SELECT torrent, seeder FROM peers WHERE userid = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
mysql_query("DELETE FROM peers WHERE userid = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
if (mysql_affected_rows()) {
	while($arr = mysql_fetch_assoc($res)) {
 	if ($arr["seeder"] == "yes")
 	$updateset_s[] = "id = ".$arr['torrent'];
 	else
 	$updateset_l[] = "id = ".$arr['torrent'];
	}
	if(isset($updateset_l)) {
 	$query_l = "UPDATE torrents SET leechers = leechers - 1 WHERE ".join(" OR ", $updateset_l)." LIMIT ".count($updateset_l);
 	mysql_query($query_l) or sqlerr(__FILE__, __LINE__);
	}
	if(isset($updateset_s)) {
 	$query_s = "UPDATE torrents SET seeders = seeders - 1 WHERE ".join(" OR ", $updateset_s)." LIMIT ".count($updateset_s);
 	mysql_query($query_s) or sqlerr(__FILE__, __LINE__);
	}
}

header("Refresh: 2; url={$TBDEV['baseurl']}/userdetails.php?id=$id");
stderr($lang['flush_success'], $lang['flush_done']);
?>