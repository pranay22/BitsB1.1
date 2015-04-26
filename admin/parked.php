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
|   Parked users viewing system v0.2
+------------------------------------------------
**/

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

require_once "include/user_functions.php";
require_once "include/html_functions.php";
staffonly();

$lang = array_merge( $lang, load_language('ad_parked') );

if (get_user_class() < UC_MODERATOR){
stderr("{$lang['ad_parked_error']}", "{$lang['ad_parked_denied']}");
write_log("$CURUSER[username] {$lang['ad_parked_log']}"); 
}
$HTMLOUT = '';
//$HTMLOUT .= begin_main_frame();
$HTMLOUT .= begin_frame("{$lang['ad_parked_head']}",true);
$HTMLOUT .= '<table width="100%" border="0" align="center" cellpadding="2" cellspacing="0">';
$HTMLOUT .= "<tr><td class='colhead' align='left'>{$lang['ad_parked_user']}</td><td class='colhead'>{$lang['ad_parked_ratio']}</td><td class='colhead'>{$lang['ad_parked_ip']}</td><td class='colhead'>{$lang['ad_parked_join']}</td><td class='colhead'>{$lang['ad_parked_access']}</td><td class='colhead'>{$lang['ad_parked_invite']}</td><td class='colhead'>{$lang['ad_parked_down']}</td><td class='colhead'>{$lang['ad_parked_up']}</td></tr>";

$result = sql_query ("SELECT * FROM users WHERE parked = 'yes' ORDER BY added DESC limit 50");
if ($row = mysql_fetch_array($result)) {
do {
if ($row["uploaded"] == "0") { $ratio = "inf"; }
elseif ($row["downloaded"] == "0") { $ratio = "inf"; }
else {
$ratio = number_format($row["uploaded"] / $row["downloaded"], 3);
$ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
}
$invitedby = sql_query('SELECT username FROM users WHERE id = ' . sqlesc($row['invitedby']));
$invitedby2 = mysql_fetch_array($invitedby);
if ($invitedby2 == "0"){
$invite = "---"; 
}else {
$invite = "<a href='{$TBDEV['baseurl']}/userdetails.php?id=".$row['invitedby']."'>".htmlspecialchars($invitedby2['username']).""; 
}
$HTMLOUT .= "<tr><td><a href='userdetails.php?id=".$row["id"]."'><b>".$row["username"]."</b></a></td><td><strong>".$ratio."</strong></td><td>".$row["ip"]."</td><td>".get_date($row['added'], '')."</td><td>".get_date($row["last_access"], '')."</td><td>$invite</td><td>".mksize($row["downloaded"])."</td><td>".mksize($row["uploaded"])."</td></tr>";


} while($row = mysql_fetch_array($result));
} else {
$HTMLOUT = stderr('Error', 'Hurray, no accounts are parked atm!');
}
$HTMLOUT .= "</table>";
$HTMLOUT .= end_frame();
//$HTMLOUT .= end_main_frame();

print stdhead("{$lang['ad_parked_head']}").$HTMLOUT.stdfoot();
?>