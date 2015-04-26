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
require_once ROOT_PATH.'/include/user_functions.php';
require_once ROOT_PATH.'/include/pager_functions.php';
dbconn();
loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('snatches') );

$HTMLOUT="";

$id = 0 + $_GET["id"];

if (!is_valid_id($id))
  stderr("Error", "It appears that you have entered an invalid id.");

$res = mysql_query("SELECT id, name FROM torrents WHERE id = ".sqlesc($id)."") or sqlerr();
$arr = mysql_fetch_assoc($res);

if (!$arr)
  stderr("Error", "It appears that there is no torrent with that id.");

$res = mysql_query("SELECT COUNT(*) FROM snatched WHERE torrentid =".sqlesc( $id ) ."") or sqlerr();
$row = mysql_fetch_row($res);
$count = $row[0];

$perpage = 15;
$pager = pager($perpage, $count, "snatches.php?id=$id&amp;");

if (!$count)
  stderr("No snatches", "It appears that there are currently no snatches for the torrent <a href=details.php?id=$arr[id]>$arr[name]</a>.");

$HTMLOUT .= "<h1>Snatches for torrent <a href='{$TBDEV['baseurl']}/details.php?id=$arr[id]'>$arr[name]</a></h1>\n";
$HTMLOUT .= "<h2>Currently $row[0] snatch".($row[0] == 1 ? "" : "es")."</h2>\n";

$HTMLOUT .= $pager['pagertop'];
$HTMLOUT .= "<table width='80%'border='0' cellspacing='0' cellpadding='5'>
<tr>
<td class='colhead' align='left'>{$lang['snatches_username']}</td>
<td class='colhead' align='center'>{$lang['snatches_connectable']}</td>
<td class='colhead' align='right'>{$lang['snatches_uploaded']}</td>
<td class='colhead' align='right'>{$lang['snatches_upspeed']}</td>
<td class='colhead' align='right'>{$lang['snatches_downloaded']}</td>
<td class='colhead' align='right'>{$lang['snatches_downspeed']}</td>
<td class='colhead' align='right'>{$lang['snatches_ratio']}</td>
<td class='colhead' align='right'>{$lang['snatches_completed']}</td>
<td class='colhead' align='right'>{$lang['snatches_seedtime']}</td>
<td class='colhead' align='right'>{$lang['snatches_leechtime']}</td>
<td class='colhead' align='center'>{$lang['snatches_lastaction']}</td>
<td class='colhead' align='center'>{$lang['snatches_completedat']}</td>
<td class='colhead' align='center'>{$lang['snatches_client']}</td>
<td class='colhead' align='center'>{$lang['snatches_port']}</td>
<td class='colhead' align='center'>{$lang['snatches_announced']}</td>
<td class='colhead' align='center'>{$lang['snatches_seeder']}</td>
</tr>\n";


$res = mysql_query("SELECT s.*, size, username, warned, enabled, class, timesann, donor FROM snatched AS s INNER JOIN users ON s.userid = users.id INNER JOIN torrents ON s.torrentid = torrents.id WHERE torrentid = $id ORDER BY complete_date DESC ".$pager['limit']."") or sqlerr();
while ($arr = mysql_fetch_assoc($res)) {

 $upspeed = ($arr["upspeed"] > 0 ? mksize($arr["upspeed"]) : ($arr["seedtime"] > 0 ? mksize($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) : mksize(0)));
 $downspeed = ($arr["downspeed"] > 0 ? mksize($arr["downspeed"]) : ($arr["leechtime"] > 0 ? mksize($arr["downloaded"] / $arr["leechtime"]) : mksize(0)));
 $ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"], 3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));
 $completed = sprintf("%.2f%%", 100 * (1 - ($arr["to_go"] / $arr["size"])));
 $seed = $arr["seeder"] == "yes" ? "<font color='green'>Yes</font>" : "<font color='red'>No</font>";

  $HTMLOUT .= "<tr>
  <td align='left'><a href='{$TBDEV['baseurl']}/userdetails.php?id=$arr[userid]'>$arr[username]</a></td>
  <td align='center'>".($arr["connectable"] == "yes" ? "<font color='green'>Yes</font>" : "<font color='red'>No</font>")."</td>
  <td align='right'>".mksize($arr["uploaded"])."</td>
  <td align='right'>$upspeed/s</td>
  <td align='right'>".mksize($arr["downloaded"])."</td>
  <td align='right'>$downspeed/s</td>
  <td align='right'>$ratio</td>
  <td align='right'>$completed</td>
  <td align='right'>".mkprettytime($arr["seedtime"])."</td>
  <td align='right'>".mkprettytime($arr["leechtime"])."</td>
  <td align='center'>".get_date($arr["last_action"], '',0,1)."</td>
  <td align='center'>".get_date($arr["complete_date"], '',0,1)."</td>
  <td align='center'>".$arr["agent"]."</td>
  <td align='center'>".$arr["port"]."</td>
  <td align='center'>".$arr["timesann"]."</td>
  <td align='center.>{$seed}</td>
  </tr>\n";
}
$HTMLOUT .= "</table>\n";
$HTMLOUT .= $pager['pagerbottom'];

print stdhead('Snatches') . $HTMLOUT . stdfoot();
die;
?>