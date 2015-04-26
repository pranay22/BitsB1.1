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

require_once ("include/user_functions.php");
require_once ("include/html_functions.php");
require_once ("include/pager_functions.php");

$lang = array_merge( $lang , load_language('ad_snatched_torrents'));
staffonly();
$HTMLOUT="";
 
if ($CURUSER['class'] < UC_MODERATOR)
stderr("Sorry", "No Permissions.");

function get_snatched_color($st)
{
global $lang;
$secs = $st;
$mins = floor($st / 60);
$hours = floor($mins / 60);
$days = floor($hours / 24);
$week = floor($days / 7);
$month = floor($week / 4);
if ($month > 0) {
$week_elapsed = floor(($st - ($month * 4 * 7 * 24 * 60 * 60)) / (7 * 24 * 60 * 60));
$days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color='lime'><b>$month months.<br />$week_elapsed W. $days_elapsed D.</b></font>";
}
if ($week > 0) {
$days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color='lime'><b>$week W. $days_elapsed D.<br />$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($days > 2) {
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color='lime'><b>$days D.<br />$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($days > 1) {
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color='green'><b>$days D.<br />$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($days > 0) {
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color='#CCFFCC'><b>$days D.<br />$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($hours > 12) {
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color='yellow'><b>$hours:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($hours > 0) {
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color='red'><b>$hours:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($mins > 0) {
$secs_elapsed = floor($st - $mins * 60);
return "<font color='red'><b>0:$mins:$secs_elapsed</b></font>";
}
if ($secs > 0) {
return "<font color='red'><b>0:0:$secs</b></font>";
}
return "<font color='red'><b>{$lang['ad_snatched_torrents_none']}<br />{$lang['ad_snatched_torrents_reported']}</b></font>";
}

$count = number_format(get_row_count("snatched", "WHERE complete_date != '0'"));

$HTMLOUT .="<h2 align='center'>{$lang['ad_snatched_torrents_allsnatched']}</h2>
<font class='small'>{$lang['ad_snatched_torrents_currently']}&nbsp;".htmlspecialchars($count)."&nbsp;{$lang['ad_snatched_torrents_snatchedtor']}</font>";
$HTMLOUT .= begin_main_frame();
$res = sql_query("SELECT COUNT(id) FROM snatched") or sqlerr();
$row = mysql_fetch_row($res);
$count = $row[0];
$snatchedperpage = 15;

$pager = pager($snatchedperpage, $count, "admin.php?action=snatched_torrents&amp;");
$HTMLOUT .= $pager['pagertop'];

$sql = "SELECT sn.userid, sn.id, sn.torrentid, sn.timesann, sn.hit_and_run, sn.mark_of_cain, sn.uploaded, sn.downloaded, sn.start_date, sn.complete_date, sn.seeder, sn.leechtime, sn.seedtime, u.username, t.name ".
"FROM snatched AS sn ".
"LEFT JOIN users AS u ON u.id=sn.userid ".
"LEFT JOIN torrents AS t ON t.id=sn.torrentid WHERE complete_date != '0'".
"ORDER BY sn.complete_date DESC ".$pager['limit']."";
$result = sql_query($sql) or print(mysql_error());
if( mysql_num_rows($result) != 0 ) {

$HTMLOUT .="<table width='100%' border='1' cellspacing='0' cellpadding='5' align='center'>
<tr>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_name']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_torname']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_hnr']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_marked']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_announced']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_upload']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_download']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_seedtime']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_leechtime']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_startdate']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_enddate']}</td>
<td class='table' align='center' width='1%'>{$lang['ad_snatched_torrents_seeding']}</td>
</tr>";

while($row = mysql_fetch_assoc($result)) {
$smallname =substr(htmlspecialchars($row["name"]) , 0, 25);
if ($smallname != htmlspecialchars($row["name"])) {
$smallname .= '...';
}
$HTMLOUT .="<tr><td><a href='/userdetails.php?id=".$row['userid']."'><b>".$row['username']."</b></a></td>
<td align='center'><a href='/details.php?id=".$row['torrentid']."'><b>".$smallname."</b></a></td>
<td align='center'><b>".get_date($row['hit_and_run'], 'LONG',0,1)."</b></td>
<td align='center'><b>".($row['mark_of_cain'])."</b></td>
<td align='center'><b>".($row['timesann'])."</b></td>
<td align='center'><b>".mksize($row['uploaded'])."</b></td>
<td align='center'><b>".mksize($row['downloaded'])."</b></td>
<td align='center'><b>".get_snatched_color($row["seedtime"])."</b></td>
<td align='center'><b>".mkprettytime($row["leechtime"])."</b></td>
<td align='center'><b>".get_date($row['start_date'], 'LONG',0,1)."</b></td>";

if ($row['complete_date'] > 0)
$HTMLOUT .="<td align='center'><b>".get_date($row['complete_date'], 'LONG',0,1)."</b></td>";
else
$HTMLOUT .="<td align='center'><b><font color='red'>{$lang['ad_snatched_torrents_ncomplete']}</font></b></td></tr>";
$HTMLOUT .="<td align='center'><b>".($row['seeder'] == 'yes' ? "<img src='".$TBDEV['pic_base_url']."online.gif' alt='Online' title='Online' />" : "<img src='".$TBDEV['pic_base_url']."offline.gif' alt='Offline' title='Offline' />")."</b></td></tr>";
}
$HTMLOUT .="</table>";
}
else
$HTMLOUT .="{$lang['ad_snatched_torrents_nothing']}";
$HTMLOUT .= $pager['pagertop'];
$HTMLOUT .= end_main_frame();
print stdhead('Snatched Torrents Overview') . $HTMLOUT . stdfoot();
die;
?>