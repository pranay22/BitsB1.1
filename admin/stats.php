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

if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
}

require "include/html_functions.php";
require_once "include/user_functions.php";
staffonly();

    $lang = array_merge( $lang, load_language('ad_stats') );
    
    $HTMLOUT = '';

    $HTMLOUT .= begin_main_frame();

    $res = mysql_query("SELECT COUNT(*) FROM torrents") or sqlerr(__FILE__, __LINE__);
    $n = mysql_fetch_row($res);
    $n_tor = $n[0];

    $res = mysql_query("SELECT COUNT(*) FROM peers") or sqlerr(__FILE__, __LINE__);
    $n = mysql_fetch_row($res);
    $n_peers = $n[0];

    $uporder = isset($_GET['uporder']) ? $_GET['uporder'] : '';
    $catorder = isset($_GET["catorder"]) ? $_GET["catorder"] : '';

    if ($uporder == "lastul")
      $orderby = "last DESC, name";
    elseif ($uporder == "torrents")
      $orderby = "n_t DESC, name";
    elseif ($uporder == "peers")
      $orderby = "n_p DESC, name";
    else
      $orderby = "name";

    $query = "SELECT u.id, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) as n_p
      FROM users as u LEFT JOIN torrents as t ON u.id = t.owner LEFT JOIN peers as p ON t.id = p.torrent WHERE u.class = ". UC_UPLOADER ."
      GROUP BY u.id UNION SELECT u.id, u.username AS name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) as n_p
      FROM users as u LEFT JOIN torrents as t ON u.id = t.owner LEFT JOIN peers as p ON t.id = p.torrent WHERE u.class > ". UC_UPLOADER ."
      GROUP BY u.id ORDER BY $orderby";

    $res = mysql_query($query) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) == 0)
      stdmsg($lang['stats_error'], $lang['stats_error1']);
    else
    {
      $HTMLOUT .= begin_frame($lang['stats_title1'], True);
      $HTMLOUT .= begin_table();
      
      $HTMLOUT .= "<tr>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=uploader&amp;catorder=$catorder' class='colheadlink'>{$lang['stats_uploader']}</a></td>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=lastul&amp;catorder=$catorder' class='colheadlink'>{$lang['stats_last']}</a></td>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=torrents&amp;catorder=$catorder' class='colheadlink'>{$lang['stats_torrent']}</a></td>
      <td class='colhead'>Perc.</td>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=peers&amp;catorder=$catorder' class='colheadlink'>{$lang['stats_peers']}</a></td>
      <td class='colhead'>Perc.</td>
      </tr>\n";
      
      while ($uper = mysql_fetch_assoc($res))
      {
        $HTMLOUT .= "<tr>
        <td><a href='userdetails.php?id=".$uper['id']."'><b>".$uper['name']."</b></a></td>
        <td " . ($uper['last']?(">".get_date( $uper['last'],'')." (".get_date( $uper['last'],'',0,1).")"):"align='center'>---") . "</td>
        <td align='right'>{$uper['n_t']}</td>
        <td align='right'>" . ($n_tor > 0?number_format(100 * $uper['n_t']/$n_tor,1)."%":"---") . "</td>
        <td align='right'>" . $uper['n_p']."</td>
        <td align='right'>" . ($n_peers > 0?number_format(100 * $uper['n_p']/$n_peers,1)."%":"---") . "</td></tr>\n";
      }
      $HTMLOUT .= end_table();
      $HTMLOUT .= end_frame();
    }

    if ($n_tor == 0)
      stdmsg($lang['stats_error'], $lang['stats_error2']);
    else
    {
      if ($catorder == "lastul")
        $orderby = "last DESC, c.name";
      elseif ($catorder == "torrents")
        $orderby = "n_t DESC, c.name";
      elseif ($catorder == "peers")
        $orderby = "n_p DESC, name";
      else
        $orderby = "c.name";

      $res = mysql_query("SELECT c.name, MAX(t.added) AS last, COUNT(DISTINCT t.id) AS n_t, COUNT(p.id) AS n_p
      FROM categories as c LEFT JOIN torrents as t ON t.category = c.id LEFT JOIN peers as p
      ON t.id = p.torrent GROUP BY c.id ORDER BY $orderby") or sqlerr(__FILE__, __LINE__);

      $HTMLOUT .= begin_frame($lang['stats_title2'], True);
      $HTMLOUT .= begin_table();
      $HTMLOUT .= "<tr>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=$uporder&amp;catorder=category' class='colheadlink'>{$lang['stats_category']}</a></td>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=$uporder&amp;catorder=lastul' class='colheadlink'>{$lang['stats_last']}</a></td>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=$uporder&amp;catorder=torrents' class='colheadlink'>{$lang['stats_torrent']}</a></td>
      <td class='colhead'>Perc.</td>
      <td class='colhead'><a href='admin.php?action=stats&amp;uporder=$uporder&amp;catorder=peers' class='colheadlink'>{$lang['stats_peers']}</a></td>
      <td class='colhead'>Perc.</td>
      </tr>\n";
      
      while ($cat = mysql_fetch_assoc($res))
      {
        $HTMLOUT .= "<tr>
        <td class='rowhead'>{$cat['name']}</td>
        <td " . ($cat['last']?(">".get_date( $cat['last'],'')." (".get_date( $cat['last'],'',0,1).")"):"align='center'>---") ."</td>
        <td align='right'>{$cat['n_t']}</td>
        <td align='right'>" . number_format(100 * $cat['n_t']/$n_tor,1) . "%</td>
        <td align='right'>{$cat['n_p']}</td>
        <td align='right'>" . ($n_peers > 0?number_format(100 * $cat['n_p']/$n_peers,1)."%":"---") . "</td></tr>\n";
      }
      $HTMLOUT .= end_table();
      $HTMLOUT .= end_frame();
    }

    $HTMLOUT .= end_main_frame();
    
    print stdhead($lang['stats_window_title']) . $HTMLOUT . stdfoot();
    die;
?>