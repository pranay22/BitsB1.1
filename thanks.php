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

  require_once('include/bittorrent.php');
  dbconn();
  loggedinorreturn();
  
  $uid = $CURUSER['id'];
  $tid = isset($_POST['torrentid']) ? 0 + $_POST['torrentid'] : 0;
  
  if ($uid > 0 && $tid > 0)
    {
      $ct = mysql_result(mysql_query("SELECT COUNT(id) FROM thanks WHERE userid={$uid} AND torrentid={$tid}"), 0);
      @mysql_query("UPDATE users SET seedbonus = seedbonus+1.0 WHERE id = ".sqlesc($CURUSER["id"])."") or sqlerr(__FILE__, __LINE__);
      if ($ct == 0)
          $res = mysql_query("INSERT INTO thanks (torrentid, userid) VALUES ($tid, $uid)");
      
      header('Location: ' . $TBDEV['baseurl'] . '/details.php?id=' . $tid . '&thanks=1');
    }
  else
      header('Location: ' . $TBDEV['baseurl'] . '/details.php?id=' . $tid);
?>
