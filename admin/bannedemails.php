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

require_once "include/html_functions.php";
require_once "include/bbcode_functions.php";
require_once "include/pager_functions.php";
require_once "include/user_functions.php";
$lang = array_merge( $lang, load_language('ad_banemail') );
staffonly();

if (get_user_class() < UC_ADMINISTRATOR)
stderr("Error", "Access Denied!");
/* Ban emails by x0r @tbdev.net */
$HTMLOUT ='';
$remove = isset($_GET['remove']) ? (int)$_GET['remove'] : 0;
if (is_valid_id($remove)) {
mysql_query("DELETE FROM bannedemails WHERE id = '$remove'") or sqlerr(__FILE__, __LINE__);
write_log("{$lang['ad_banemail_log1']} $remove {$lang['ad_banemail_log2']} $CURUSER[username]");
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$email = trim($_POST["email"]);
$comment = trim($_POST["comment"]);
if (!$email || !$comment)
stderr("{$lang['ad_banemail_error']}", "{$lang['ad_banemail_missing']}");
mysql_query("INSERT INTO bannedemails (added, addedby, comment, email) VALUES(" . sqlesc(time()) . ", $CURUSER[id], " . sqlesc($comment) . ", " . sqlesc($email) . ")") or sqlerr(__FILE__, __LINE__);
header("Location: $_SERVER[REQUEST_URI]");
die;
}

ob_start("ob_gzhandler");
$HTMLOUT .=begin_frame("{$lang['ad_banemail_add']}",true);
$HTMLOUT .="<form method=\"post\" action=\"admin.php?action=bannedemails\">
<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='rowhead'>{$lang['ad_banemail_email']}</td><td><input type=\"text\" name=\"email\" size=\"40\"/></td></tr>
<tr><td class='rowhead'align='left'>{$lang['ad_banemail_comment']}</td><td><input type=\"text\" name=\"comment\" size=\"40\"/></td></tr>
<tr><td colspan='2'>{$lang['ad_banemail_info']}</td></tr>
<tr><td colspan='2' align='center'><input type=\"submit\" value=\"{$lang['ad_banemail_ok']}\" class=\"btn\"/></td></tr>
</table>\n</form>\n";
$HTMLOUT .=end_frame();
$count1 = get_row_count('bannedemails');
$perpage = 15;
$pager = pager($perpage, $count1, 'admin.php?action=bannedemails&amp;');
$res = mysql_query("SELECT * FROM bannedemails ORDER BY added DESC ".$pager['limit']."") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .=begin_frame("{$lang['ad_banemail_current']}",true);
if ($count1 > $perpage)
$HTMLOUT .= $pager['pagertop'];
if (mysql_num_rows($res) == 0)
$HTMLOUT .="<p align='center'><b>{$lang['ad_banemail_nothing']}</b></p>\n";
else {
$HTMLOUT .="<table border='1' cellspacing='0' cellpadding='5'>\n";
$HTMLOUT .="<tr><td class='colhead'>{$lang['ad_banemail_add1']}</td><td class='colhead' align='left'>{$lang['ad_banemail_email']}</td>" . "<td class='colhead' align='left'>{$lang['ad_banemail_by']}</td><td class='colhead' align='left'>{$lang['ad_banemail_comment']}</td><td class='colhead'>{$lang['ad_banemail_remove']}</td></tr>\n";
while ($arr = mysql_fetch_assoc($res)) {
$r2 = mysql_query("SELECT username FROM users WHERE id = $arr[addedby]") or sqlerr(__FILE__, __LINE__);
$a2 = mysql_fetch_assoc($r2);
$HTMLOUT .="<tr><td align='left'>".get_date($arr['added'], '')."</td><td align='left'>$arr[email]</td><td align='left'><a href='userdetails.php?id=$arr[addedby]'>$a2[username]" . "</a></td><td align='left'>$arr[comment]</td><td align='left'><a href='admin.php?action=bannedemails&remove=$arr[id]'>{$lang['ad_banemail_remove1']}</a></td></tr>\n";
}
$HTMLOUT .="</table>\n";
}
if ($count1 > $perpage)
$HTMLOUT .= $pager['pagerbottom'];
$HTMLOUT .=end_frame();
/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead("{$lang['ad_banemail_head']}").$HTMLOUT.stdfoot();
?>