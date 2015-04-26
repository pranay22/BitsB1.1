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

ob_start('ob_gzhandler');

require_once 'include/bittorrent.php';
require_once 'include/user_functions.php';
require_once "include/html_functions.php";
//require_once "include/pager_functions.php";

dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('takefilesearch') );

    if(isset($_POST['search']) && !empty($_POST['search'])) {
      
      $cleansearchstr = sqlesc($_POST['search']);
      //print $cleansearchstr;
      }
      else
      stderr($lang['tfilesearch_oops'], $lang['tfilesearch_nuffin']);


    $query = mysql_query("SELECT id, filename, MATCH (filename)
                AGAINST ($cleansearchstr IN BOOLEAN MODE) AS score
                FROM files WHERE MATCH (filename) AGAINST ($cleansearchstr IN BOOLEAN MODE)
                ORDER BY score DESC");

    if(mysql_num_rows($query) == 0)
      stderr($lang['tfilesearch_error'], $lang['tfilesearch_nothing']);

    $HTMLOUT = '';
  	
    $HTMLOUT .= begin_table();

    $HTMLOUT .= "<tr>
    <td class='colhead'>{$lang['tID']}</td>
    <td class='colhead' align='left'>{$lang['tfilename']}</td>
    <td class='colhead' align='left'>{$lang['tscore']}</td>";
    
    while($row = mysql_fetch_assoc($query)) 
    {
      $HTMLOUT .= "<tr><td>{$row['id']}</td><td>".htmlspecialchars($row['filename'])."</td><td>{$row['score']}</td></tr>";
    }
    
    $HTMLOUT .= end_table();
    
    print stdhead($lang['tstdhead']) . $HTMLOUT . stdfoot();
?>