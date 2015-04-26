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
+------------------------------------------------
**/

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn();

$pkey = isset($_GET['passkey']) && strlen($_GET['passkey']) == 32 ? $_GET['passkey'] : '';
if(!empty($pkey)) {
	$q0 = mysql_query("SELECT * FROM users where passkey = ".sqlesc($pkey)) or sqlerr(__FILE__, __LINE__);
	if(mysql_num_rows($q0) == 0)
		die($lang['download_passkey']);
	else
		$CURUSER = mysql_fetch_assoc($q0);
}else
	loggedinorreturn();

//having problems with donload disable entry in the download.php, so I've commented out for future solution.

/*if (!($CURUSER["id"] == $row["owner"])) {
if ($CURUSER["downloadpos"] == "no")
stderr("ERROR","Your download rights have been disabled.");
}*/

  $lang = load_language('download');
  
  $id = isset($_GET['torrent']) ? intval($_GET['torrent']) : 0;

  if ( !is_valid_id($id) )
    stderr("{$lang['download_user_error']}", "{$lang['download_no_id']}");


  $res = mysql_query("SELECT name, filename FROM torrents WHERE id = $id") or sqlerr(__FILE__, __LINE__);
  $row = mysql_fetch_assoc($res);

  $fn = "{$TBDEV['torrent_dir']}/$id.torrent";

  if (!$row || !is_file($fn) || !is_readable($fn))
    httperr();


  @mysql_query("UPDATE torrents SET hits = hits + 1 WHERE id = $id");
  /** free mod for TBDev 09 by pdq **/
    include ROOT_PATH.'/mods/freeslots_inc.php';
  /** end **/

  require_once "include/benc.php";



  if (!isset($CURUSER['passkey']) || strlen($CURUSER['passkey']) != 32) 
  {

    $CURUSER['passkey'] = md5($CURUSER['username'].time().$CURUSER['passhash']);

    @mysql_query("UPDATE users SET passkey='{$CURUSER['passkey']}' WHERE id={$CURUSER['id']}");

  }



  $dict = bdec_file($fn, filesize($fn));

  $dict['value']['announce']['value'] = "{$TBDEV['announce_urls'][0]}?passkey={$CURUSER['passkey']}";

  $dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];

  $dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);



  header('Content-Disposition: attachment; filename="['.$TBDEV['torrent_prefix'].']'.$row['filename'].'"');

  header("Content-Type: application/x-bittorrent");



  print(benc($dict));



?>