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
require_once("include/user_functions.php");
require_once("include/html_functions.php");
require_once("include/pager_functions.php");

dbconn(false);

loggedinorreturn();
staffonly();

$HTMLOUT ="";
$ip="";
$mask="";
$lang = array_merge( load_language('global') );

if ($CURUSER['class'] < UC_MODERATOR)
stderr("Error", "No Access");

function ratios($up,$down, $color = True)
{
	if ($down > 0)
	{
		$r = number_format($up / $down, 2);
	if ($color)
			$r = "<font color='".get_ratio_color($r)."'>$r</font>";
	}
	else
		if ($up > 0)
		  $r = "Inf.";
	  else
		  $r = "---";
	return $r;
}


$HTMLOUT .= begin_main_frame();

$HTMLOUT .="<h1>Search in IP History</h1>\n
<form method='get' action='ipsearch.php'>\n
<table align='center' border='1' cellspacing='0' width='115' cellpadding='5'>\n
<tr>
<td align='left'>IP:</td>\n
<td align='left'>
<input type='text' name='ip' size='40' value='" . htmlspecialchars($ip) . "' />\n
</td></tr>
<tr>
<td align='left'>Mask:</td>
<td align='left'>\n
<input type='text' name='mask' size='40' value='" . htmlspecialchars($mask) . "' /></td></tr>\n
<tr>
<td align='right' colspan='2'><input type='submit' value='Search !' style='height: 20px' />

</td></tr></table></form><br /><br />\n";

$ip = isset($_GET["ip"]) ? htmlspecialchars(trim($_GET["ip"])) : '';
if ($ip)
  {
	  $regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";
	if (!preg_match($regex, $ip))
	{
		$HTMLOUT .= stdmsg("Error", "Invalid IP.");
		$HTMLOUT .= end_main_frame();
		print stdhead("IP Search") . $HTMLOUT . stdfoot();
		die();
	}
  $mask = isset($_GET["mask"]) ? htmlspecialchars(trim($_GET["mask"])) : '';
	if ($mask == "" || $mask == "255.255.255.255")
	{
	   $where1 = "u.ip = '$ip'";
	   $where2 = "iplog.ip = '$ip'";
	   $dom = @gethostbyaddr($ip);
	   if ($dom == $ip || @gethostbyname($dom) != $ip)
		  $addr = "";
	   else
		  $addr = $dom;
	}
	else
	{
	   if (substr($mask,0,1) == "/")
   	   {
		   $n = substr($mask, 1, strlen($mask) - 1);
		 if (!is_numeric($n) or $n < 0 or $n > 32)
		 {
			$HTMLOUT .= stdmsg("Error", "Invalid subnet mask.");
   	                $HTMLOUT .= end_main_frame();
			print stdhead("IP Search") . $HTMLOUT . stdfoot();
			die();
		 }
		 else
			 $mask = long2ip(pow(2,32) - pow(2,32-$n));
	   }
	   elseif (!preg_match($regex, $mask))
	   {
		  $HTMLOUT .= stdmsg("Error", "Invalid subnet mask.");
		  $HTMLOUT .= end_main_frame();
		  print stdhead("IP Search") . $HTMLOUT . stdfoot();
		  die();
	   }
	   $where1 = "INET_ATON(u.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
	   $where2 = "INET_ATON(iplog.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
	   $addr = "Mask: $mask";
	}

  $queryc = "SELECT COUNT(*) FROM
		   (
			 SELECT u.id FROM users AS u WHERE $where1
			 UNION SELECT u.id FROM users AS u RIGHT JOIN iplog ON u.id = iplog.userid WHERE $where2
			 GROUP BY u.id
		   ) AS ipsearch";
		   
  $res = mysql_query($queryc) or sqlerr(__FILE__, __LINE__);
  $row = mysql_fetch_array($res);
  $count = $row[0];
  
  if ($count == 0)
  {
	  $HTMLOUT .="<br /><b>No users found</b>\n";
	  $HTMLOUT .= end_main_frame();
	  print stdhead("IP Search") . $HTMLOUT . stdfoot();
	  die;
  }
	  
  $order= isset($_GET['order']) &&  $_GET['order'];
  $page = isset($_GET['page']) && 0 + $_GET['page'];
  $perpage = 20;
  $pager = pager($perpage, $count, "ipsearch.php?ip=$ip&amp;mask=$mask&amp;order=$order&amp;");


  if ($order == "added")
	$orderby = "added DESC";
 elseif ($order == "username")
	$orderby = "UPPER(username) ASC";
 elseif ($order == "email")
	$orderby = "email ASC";
 elseif ($order == "last_ip")
	$orderby = "last_ip ASC";
 elseif ($order == "last_access")
	$orderby = "last_ip ASC";
 else
	$orderby = "access DESC";

  $query1 = "SELECT * FROM (
		  SELECT u.id, u.username, u.ip AS ip, u.ip AS last_ip, u.last_access, u.last_access AS access, u.email, u.invitedby, u.added, u.class, u.uploaded, u.downloaded, u.donor, u.enabled, u.warned
		  FROM users AS u
		  WHERE $where1
		  UNION SELECT u.id, u.username, iplog.ip AS ip, u.ip as last_ip, u.last_access, max(iplog.access) AS access, u.email, u.invitedby, u.added, u.class, u.uploaded, u.downloaded, u.donor, u.enabled, u.warned
		  FROM users AS u
		  RIGHT JOIN iplog ON u.id = iplog.userid
		  WHERE $where2
		  GROUP BY u.id ) as ipsearch
		  GROUP BY id
		  ORDER BY $orderby
		  ".$pager['limit']."";


  $res = mysql_query($query1) or sqlerr(__FILE__, __LINE__);

  $HTMLOUT .= begin_frame("".htmlspecialchars($count)." users have used the IP: ".htmlspecialchars($ip)." (".htmlspecialchars($addr).")", True);

   if ($count > $perpage)
   $HTMLOUT .= $pager['pagertop'];
	 $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'>\n";
	 $HTMLOUT .= "<tr>
	  <td class='colhead'><a href='{$TBDEV['baseurl']}/ipsearch?ip=$ip&amp;mask=$mask&amp;order=username'>Username</a></td>".
		"<td class='colhead'>Ratio</td>".
		"<td class='colhead'><a href='{$TBDEV['baseurl']}/ipsearch?ip=$ip&amp;mask=$mask&amp;order=email'>Email</a></td>".
		"<td class='colhead'><a href='{$TBDEV['baseurl']}/ipsearch?ip=$ip&amp;mask=$mask&amp;order=last_ip'>Last IP</a></td>".
		"<td class='colhead'><a href='{$TBDEV['baseurl']}/ipsearch?ip=$ip&amp;mask=$mask&amp;order=last_access'>Last access</a></td>".
		"<td class='colhead'>Num of IP's</td>".
		"<td class='colhead'><a href='{$TBDEV['baseurl']}/ipsearch?ip=$ip&amp;mask=$mask'>Last access on <br />".htmlspecialchars($ip)."</a></td>".
		"<td class='colhead'><a href='{$TBDEV['baseurl']}/ipsearch?ip=$ip&amp;mask=$mask&amp;order=added'>Added</a></td>".
		"<td class='colhead'>Invited by</td></tr>";

	while ($user = mysql_fetch_array($res))
	{
		if ($user['added'] == '0')
		  $user['added'] = '---';
	  if ($user['last_access'] == '0')
		  $user['last_access'] = '---';

	  if ($user['last_ip'])
	  {
		$nip = ip2long($user['last_ip']);
		$auxres = mysql_query("SELECT COUNT(*) FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
		$array = mysql_fetch_row($auxres);
   		if ($array[0] == 0)
			  $ipstr = $user['last_ip'];
		else
			  $ipstr = "<a href='{$TBDEV['baseurl']}/testip.php?ip=" . $user['last_ip'] . "'><font color='#FF0000'><b>" .htmlspecialchars( $user["last_ip"]) . "</b></font></a>";
			}
			else
		  $ipstr = "---";
		  
		$resip = mysql_query("SELECT ip FROM iplog WHERE userid=" . sqlesc($user["id"]) . " GROUP BY iplog.ip") or sqlerr(__FILE__, __LINE__);
		$iphistory = mysql_num_rows($resip);
		
		
		if ($user["invitedby"] > 0)
		{
		   $auxres = mysql_query("SELECT username FROM users WHERE id=".sqlesc($user["invitedby"])."");
		   $array = mysql_fetch_array($auxres);
		   $invitedby = $array["username"];
		   if ($invitedby == "")
			  $invitedby = "<i>[Deleted]</i>";
		   else
			  $invitedby = "<a href='{$TBDEV['baseurl']}/userdetails.php?id=$user[invitedby]'>".htmlspecialchars($invitedby)."</a>";
		}
		else
		   $invitedby = "--";

	   	$HTMLOUT .= "<tr>
	   	<td><b><a href='{$TBDEV['baseurl']}/userdetails.php?id=" . $user['id'] . "'>" . $user['username'] . "</a></b>" . get_user_icons($user) . "</td>".
		  "<td>" . ratios($user['uploaded'], $user['downloaded']) . "</td>
		  <td>" . $user['email'] . "</td><td>" . $ipstr . "</td>
		  <td><div align='center'>" . get_date($user['last_access'],'DATE' ,1,0) . "</div></td>
		  <td><div align='center'><b><a href='{$TBDEV['baseurl']}/iphistory.php?id=" . $user['id'] . "'>" . htmlspecialchars($iphistory). "</a></b></div></td>
		  <td><div align='center'>" . get_date($user['access'],'DATE' ,1,0) . "</div></td>
		  <td><div align='center'>" . get_date($user['added'],'DATE' ,1,0) . "</div></td>
		  <td><div align='center'>" . $invitedby . "</div></td>
		  </tr>\n";
	}
	
	$HTMLOUT .= "</table>";
	
	if ($count > $perpage)
  $pager['pagerbottom'];

  $HTMLOUT .= end_frame();

}

$HTMLOUT .= end_main_frame();
print stdhead("Ip Search") . $HTMLOUT . stdfoot();
die;
?>