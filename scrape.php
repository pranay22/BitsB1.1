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

require_once("include/config.php");

if (!@mysql_connect($TBDEV['mysql_host'], $TBDEV['mysql_user'], $TBDEV['mysql_pass']))
  {
	  exit();
  }
    
  @mysql_select_db($TBDEV['mysql_db']) or exit();

function hash_where($name, $hash) 
  {
    $shhash = preg_replace('/ *$/s', "", $hash);
    return "$name = '" . mysql_real_escape_string($hash) . "'";
  }


  $r = 'd5:filesd';

  $fields = "info_hash, times_completed, seeders, leechers";

  if (!isset($_GET["info_hash"]))
    $query = "SELECT $fields FROM torrents ORDER BY info_hash";
  else
    $query = "SELECT $fields FROM torrents WHERE " . hash_where( "info_hash", @pack('H*', stripslashes( $_GET["info_hash"] ) ));

  $res = mysql_query($query);

  while ($row = mysql_fetch_assoc($res))
  {
    $r .= '20:'.str_pad($row['info_hash'], 20).'d8:completei'.$row['seeders'].'e10:downloadedi'.$row['times_completed'].'e10:incompletei'.$row['leechers'].'ee';
  }

  $r .= 'ee';

  header("Content-Type: text/plain");
  print($r);

?>