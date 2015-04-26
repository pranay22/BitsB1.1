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
//require_once("user_functions.php");
staffonly();

	$lang = array_merge( $lang, load_language('ad_pmview') );
	
	if ($CURUSER['class'] < UC_SYSOP) { 
		stderr("{$lang['pmview_error']}", "{$lang['pmview_noacc']}");
	}
	
	$resx = sql_query("SELECT COUNT(*) FROM messages");
    $rowx = mysql_fetch_array($resx,MYSQL_NUM);
    $count = $rowx[0];
	
	$pager = pager(50, $count, "admin.php?action=pmview&" . "");
	
	$HTMLOUT = '';
	
	$res = sql_query("SELECT m.*, u1.username as msgSender, u2.username as msgReceiver FROM messages as m 
                    LEFT JOIN users as u1 on u1.id = m.sender
                    LEFT JOIN users as u2 on u2.id = m.receiver ORDER BY m.id DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
	$HTMLOUT .= "<h1>{$lang['pmview_title']}</h1>";
	
	$HTMLOUT .= $pager['pagertop'];
	
	$HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5' width='80%'>";
	$HTMLOUT .= "<tr><td class='colhead' align='left'>{$lang['pmview_from']}</td>
 	<td class='colhead' align='left'>{$lang['pmview_to']}</td>
 	<td class='colhead' align='left'>{$lang['pmview_message']}</td></tr>";
	
	while ($arr = mysql_fetch_assoc($res))
	{  
		$sender = ($arr["sender"] != 0 ? "<a href='userdetails.php?id=" . $arr["sender"] . "'><b>" . $arr["msgSender"] . "</b></a>" : "<font color='red'><b>System</b></font>");
		$receiver = "<a href='userdetails.php?id=" . $arr["receiver"] . "'><b>" . $arr["msgReceiver"] . "</b></a>"; 
		$msg = format_comment($arr["msg"]);
		$added = format_comment($arr["added"]);
		$HTMLOUT .= "<tr><td>{$sender}</td>
		<td>{$receiver}</td>
		<td align='left'>{$msg}</td></tr>";
	}

	$HTMLOUT .= "</table>";
	$HTMLOUT .= $pager['pagerbottom'];
	$HTMLOUT .= "<p>{$lang['pmview_info']}</p>";


	print stdhead("{$lang['pmview_header']}") . $HTMLOUT . stdfoot();
	
?>