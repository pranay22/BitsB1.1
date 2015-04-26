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
require_once "include/user_functions.php";
require_once "include/html_functions.php";
dbconn(false);
loggedinorreturn();
staffonly();

$ip="";
$mask="";

$lang = array_merge( load_language('global'), load_language('ad_ipcheck') );

if ($CURUSER["class"] < UC_MODERATOR)
header( "Location: {$TBDEV['baseurl']}/index.php");

$HTMLOUT ="";

$delete = isset($_GET["action"]) ? htmlspecialchars(trim($_GET["action"])) : '';

$user=0;

$HTMLOUT .="<form method='post' action='?action=warnpm'>
<input class='button' type='submit' value='Send Warn PM' style='height: 20px; width: 115px' />\n";
$HTMLOUT .= begin_frame($lang['ipcheck_users'], true);
$HTMLOUT .= begin_table();
$res = mysql_query("SELECT * FROM users WHERE enabled='yes' AND status='confirmed' AND ip<>'' ORDER BY ip") or sqlerr();
$num = mysql_num_rows($res);
$HTMLOUT .="

 <tr align='center'>
 <td class='colhead' width='90'>{$lang['ipcheck_user']}</td>
 <td class='colhead' width='70'>{$lang['ipcheck_email']}</td>
 <td class='colhead' width='70'>{$lang['ipcheck_regged']}</td>
 <td class='colhead' width='75'>{$lang['ipcheck_lastacc']}</td>
 <td class='colhead' width='70'>{$lang['ipcheck_dload']}</td>
 <td class='colhead' width='75'>{$lang['ipcheck_upped']}</td>
 <td class='colhead' width='9'>{$lang['ipcheck_ratio']}</td>
 <td class='colhead' width='15'>{$lang['ipcheck_ip']}</td>
 <td class='colhead' width='3'>{$lang['ipcheck_warned']}</td>
 <td class='colhead' width='15'>{$lang['ipcheck_clear']}</td>
 </tr>\n";
$ip="";
$uc = 0;
while($ras=mysql_fetch_assoc($res))
{
if ($ip <> $ras['ip'])
{
$ros = mysql_query("SELECT * FROM users WHERE ip=".sqlesc($ras["ip"])." ORDER BY id") or sqlerr(__FILE__, __LINE__);
$num2 = mysql_num_rows($ros);
if ($num2 > 1)
{
$uc++;
while($arr = mysql_fetch_assoc($ros))
{
if ($delete == "warnpm") {
$userid = (int) $arr["id"];
$ip = sqlesc($arr["ip"]);
$name = $arr["username"];
$subject = sqlesc("Duplicate ip");
$msg = sqlesc("Hello ".htmlspecialchars($name)."
We have discovered there are more then one account registered on your IP ".htmlspecialchars($arr["ip"]).", therefore you have exactly one week to contact staff and tell us why.\n
If we don't hear from you all accounts on the IP will be disabled and the IP will be banned!\n
".$TBDEV['site_name']." staff");
$date = time();
{
$a = mysql_query("SELECT warnpm FROM iplog WHERE ip = ".sqlesc($arr["ip"])."");
$b = mysql_fetch_assoc($a);
if ($b['warnpm'] == 0)
{
mysql_query("UPDATE iplog SET warnpm = $date WHERE ip =".sqlesc($arr["ip"])."") or sqlerr(__FILE__, __LINE__);
mysql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES (0, $arr[id], $date, $msg, $subject)") or sqlerr(__FILE__, __LINE__);

}
}
header('Refresh: 2; url='.$TBDEV['baseurl'].'/ipcheck.php');
stderr("Success","<font size='2' color='red'><br />PM sent to ".htmlspecialchars($name)." !</font>");
}
else if ($delete == "clear") {
$ip = sqlesc($arr["ip"]);
$name = $arr["username"];
$subject = sqlesc("Duplicate ip");
$msg = sqlesc("Hello ".htmlspecialchars($name)."
$CURUSER[username] has cleared your ban.\n
".$TBDEV['site_name']." staff");
$date = time();
{
$a = mysql_query("SELECT warnpm FROM iplog WHERE ip =".sqlesc($arr["ip"])."") or sqlerr(__FILE__, __LINE__);
$b = mysql_fetch_assoc($a);
if ($b['warnpm'] != 0)
{
mysql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES (0, $arr[id], $date, $msg, $subject)") or sqlerr(__FILE__, __LINE__);

mysql_query("UPDATE iplog SET warnpm = '0' WHERE ip =".sqlesc($arr["ip"])."") or sqlerr(__FILE__, __LINE__);
}
}
header('Refresh: 2; url='.$TBDEV['baseurl'].'/ipcheck.php');
stderr("Success","<font size='2' color='red'><br />PM sent to ".htmlspecialchars($name)." Dupe ip warn has been reset !</font>");
}
else {   
    if ($arr['added'] == 0)
    $arr['added'] = '-';
    if ($arr['last_access'] == 0)
    $arr['last_access'] = '-';
    if($arr["downloaded"] != 0)
    $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
    else
    $ratio="---";
 
    $ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
    $uploaded = mksize($arr["uploaded"]);
    $downloaded = mksize($arr["downloaded"]);
    $added = get_date($arr['added'], 'DATE', 1,0);
    $last_access = get_date($arr['last_access'], '', 1,0);
    if($uc%2 == 0)
    $utc = "a08f74";
    else
    $utc = "bbaf9b";
	  $a = mysql_query("SELECT warnpm FROM iplog WHERE ip =".sqlesc($arr["ip"])."") or sqlerr(__FILE__, __LINE__);
	  $b = @mysql_fetch_assoc($a);
	  if ($b['warnpm'] == 0)
	  $warnpm = "<font color='red'>{$lang['ipcheck_no']}</font>";
	  else
	  $warnpm = "<font color='green'>{$lang['ipcheck_yes']}<br />".get_date($b['warnpm'], 'DATE',1,0)."</font>";
    $HTMLOUT .="
   
   <tr bgcolor='#$utc'>
   <td align='left'><b><a href='{$TBDEV['baseurl']}/userdetails.php?id=" . $arr['id'] . "'>" . $arr['username'] . "</a></b>" . get_user_icons($arr) . "</td>
   <td align='left'>".htmlspecialchars($arr["email"])."</td>
   <td align='left'>".htmlspecialchars($added)."</td>
   <td align='center'>".htmlspecialchars($last_access)."</td>
   <td align='center'>".htmlspecialchars($downloaded)."</td>
   <td align='center'>".htmlspecialchars($uploaded)."</td>
   <td align='center'>$ratio</td>
   <td align='center'>".htmlspecialchars($arr["ip"])."</td>
	 <td align='center'>$warnpm</td>
	 <td align='center'><a href='?action=clear'>Clear  ".htmlspecialchars($arr["ip"])."</a></td></tr>\n";
   $ip = 0 + $arr['ip'];
		}
    }
    }
    }
    }
$HTMLOUT .= end_table();
$HTMLOUT .= end_frame();
$HTMLOUT .="</form>";

print stdhead('Ip Check') . $HTMLOUT . stdfoot();
?>