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
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('failedlogins') );

if ($CURUSER['class'] < UC_ADMINISTRATOR)
stderr($lang['failed_sorry'], "{$lang['failed_acc_deny']}");

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$id = isset($_GET['id']) ? (int) $_GET['id'] : '';

function validate ($id)
{
global $lang;
if (!is_valid_id($id))
stderr($lang['failed_sorry'], "{$lang['failed_bad_id']}");
else
return true;
}

//==Actions
if ($action == 'ban'){
validate($id);
mysql_query("UPDATE failedlogins SET banned = 'yes' WHERE id=".sqlesc($id)."");
header('Refresh: 2; url='.$TBDEV['baseurl'].'/failedlogins.php');
stderr($lang['failed_success'],"{$lang['failed_message_ban']}");
exit();
}

if ($action == 'removeban') {
validate($id);
mysql_query("UPDATE failedlogins SET banned = 'no' WHERE id=".sqlesc($id)."") ;
header('Refresh: 2; url='.$TBDEV['baseurl'].'/failedlogins.php');
stderr($lang['failed_success'],"{$lang['failed_message_unban']}");
exit();
}

if ($action == 'delete') {
validate($id);
mysql_query("DELETE FROM failedlogins WHERE id=".sqlesc($id)."");
header('Refresh: 2; url='.$TBDEV['baseurl'].'/failedlogins.php');
stderr($lang['failed_success'],"{$lang['failed_message_deleted']}");
exit();
}
//==End
//==Main output
$HTMLOUT ="";

$HTMLOUT .="<table border='1' cellspacing='0' cellpadding='5' width='80%'>\n";

$res = mysql_query("SELECT f.*,u.id as uid, u.username FROM failedlogins as f LEFT JOIN users as u ON u.ip = f.ip ORDER BY f.added DESC") or sqlerr(__FILE__,__LINE__);

if (mysql_num_rows($res) == 0)
  $HTMLOUT .="<tr><td colspan='2'><b>{$lang['failed_message_nothing']}</b></td></tr>\n";
else
{  
  $HTMLOUT .="<tr><td class='colhead'>ID</td><td class='colhead' align='left'>{$lang['failed_main_ip']}</td><td class='colhead' align='left'>{$lang['failed_main_added']}</td>".
	"<td class='colhead' align='left'>{$lang['failed_main_attempts']}</td><td class='colhead' align='left'>{$lang['failed_main_status']}</td></tr>\n";
  while ($arr = mysql_fetch_assoc($res))
  {
  $HTMLOUT .="<tr><td align='left'><b>$arr[id]</b></td>
  <td align='left'><b>$arr[ip]" . ($arr['uid'] ? "<a href='{$TBDEV['baseurl']}/userdetails.php?id=$arr[uid]'>" : "" ) . " " . ( $arr['username'] ? "($arr[username])" : "" ) . "</a></b></td>
  <td align='left'><b>".get_date($arr['added'], '', 1,0)."</b></td>
  <td align='left'><b>$arr[attempts]</b></td>
  <td align='left'>".($arr['banned'] == "yes" ? "<font color='red'><b>{$lang['failed_main_banned']}</b></font> <a href='?action=removeban&amp;id=$arr[id]'><font color='green'>[<b>{$lang['failed_main_remban']}</b>]</font></a>" : "<font color='green'><b>{$lang['failed_main_noban']}</b></font> <a href='?action=ban&amp;id=$arr[id]'><font color='red'>[<b>{$lang['failed_main_ban']}</b>]</font></a>")."  <a onclick=\"return confirm('{$lang['failed_main_delmessage']}');\" href='?action=delete&amp;id=$arr[id]'>[<b>{$lang['failed_main_delete']}</b>]</a></td></tr>\n";
  }
  }
$HTMLOUT .="</table>\n";
print stdhead($lang['failed_main_logins']) .$HTMLOUT . stdfoot();
?>