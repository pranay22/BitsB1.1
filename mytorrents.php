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

require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";
require_once "include/pager_functions.php";
require_once "include/torrenttable_functions.php";

dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('mytorrents') );
    $lang = array_merge( $lang, load_language( 'torrenttable_functions' ));
    $HTMLOUT = '';

    $where = "WHERE owner = " . $CURUSER["id"] . " AND banned != 'yes'";
    $res = mysql_query("SELECT COUNT(*) FROM torrents $where");
    $row = mysql_fetch_array($res,MYSQL_NUM);
    $count = $row[0];

    if (!$count) 
    {

      $HTMLOUT .= "{$lang['mytorrents_no_torrents']}";
      $HTMLOUT .= "{$lang['mytorrents_no_uploads']}";

    }
    else 
    {
      $pager = pager(20, $count, "mytorrents.php?");

      //$res = sql_query("SELECT torrents.type, torrents.nuked, torrents.sticky, torrents.free,  torrents.comments, torrents.anonymous, torrents.leechers, torrents.seeders, IF(torrents.numratings < {$TBDEV['minvotes']}, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.id, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, save_as, numfiles, added, size, views, visible, hits, times_completed, category, freeslots.tid, freeslots.uid, freeslots.free AS freeslot, freeslots.double AS doubleup FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id LEFT JOIN freeslots ON (torrents.id=freeslots.tid AND freeslots.uid={$CURUSER['id']}) $where ORDER BY id DESC ".$pager['limit']);
      $query = "SELECT torrents.visible, torrents.id, torrents.category, torrents.nuked, torrents.nukereason, torrents.leechers, torrents.seeders, torrents.name, torrents.sticky, torrents.times_completed, torrents.size, torrents.added, torrents.type, torrents.free, torrents.comments,torrents.numfiles,torrents.filename,torrents.owner,torrents.anonymous,IF(torrents.nfo <> '', 1, 0) as nfoav," .
//	"IF(torrents.numratings < {$TBDEV['minvotes']}, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, categories.name AS cat_name, categories.image AS cat_pic, users.username FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";
	"categories.name AS cat_name, categories.image AS cat_pic, users.username, freeslots.tid, freeslots.uid, freeslots.free AS freeslot, freeslots.double AS doubleup FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id LEFT JOIN freeslots ON (torrents.id=freeslots.tid AND freeslots.uid={$CURUSER['id']}) $where ORDER BY id DESC {$pager['limit']}";
      $res = sql_query($query) or die(mysql_error());
      
      $HTMLOUT .= $pager['pagertop'];

      $HTMLOUT .= torrenttable($res, "mytorrents");

      $HTMLOUT .= $pager['pagerbottom'];
    }

    print stdhead($CURUSER["username"] . "'s torrents") . $HTMLOUT . stdfoot();

?>