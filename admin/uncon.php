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

require_once "include/user_functions.php";
staffonly();
$lang = array_merge( $lang, load_language('ad_uncon') );
$HTML = "";
if ((isset($_GET['delete']))) {
$userid = isset($_GET["userid"]) ? $_GET["userid"] : '';
$a = sql_query("select username from users where id = {$userid}");
if (!$a) stderr('Error','');
$h = mysql_fetch_array($a);
$sure = isset($_GET["sure"]) ? $_GET["sure"] : '';
if (!$sure)
stderr("{$lang['uncon_delete_sure']}", sprintf($lang['uncon_delete_sure_text'], "<a href='{$_SERVER["PHP_SELF"]}?action=uncon&amp;delete=1&amp;userid={$userid}&amp;sure=1'>here</a>"));
}
$r = sql_query("select * from users where status = 'pending' order by username") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($r) > 0) {
$HTML .= "<table width='100%' cellpadding='5' cellspacing='0'><tr>
		  <td class='colhead'>{$lang['uncon_username']}</td>
		  <td class='colhead'>{$lang['uncon_email']}</td>
		  <td class='colhead'>{$lang['uncon_added']}</td>
		  <td class='colhead'>{$lang['uncon_status']}/{$lang['uncon_okay']}</td>
		  <td class='colhead'>{$lang['uncon_delete']}</td>
		  </tr>\n";
while ($q = mysql_fetch_assoc($r)) {
$HTML .= "<tr>
		  <td><a href='userdetails.php?id={$q['id']}'>{$q['username']}</a></td>
		  <td>{$q['email']}</td>
		  <td>".get_date($q['added'], 'LONG')."</td>
		  <td>
		  <form method='post' action='modtask.php'>
		  <input type='hidden' name='action' value='confirmuser'/>
		  <input type='hidden' name='userid' value='{$q['id']}'/>
		  <input type='hidden' name='ret' value='{$_SERVER["PHP_SELF"]}?action=uncon'/>
		  <select name='confirm'>
		  <option value='pending' selected='selected'>{$lang['uncon_pending']}</option>
		  <option value='confirmed'>{$lang['uncon_confirmed']}</option>
		  </select>
		  <input type='submit' value='{$lang['uncon_okay']}' class='btn'/></form></td>
		  <td><input type='button' onclick=\"location.href='{$_SERVER["PHP_SELF"]}?action=uncon&amp;delete=1&amp;userid={$q['id']}'\" value='Delete this user'/></td>
		  </tr>\n";
}
$HTML .= "</table>\n";
}
else
$HTML .= "<p style='font-weight:bold;'>{$lang['uncon_none']}</p>";

print stdhead($lang['uncon_head']) . $HTML . stdfoot();
?>