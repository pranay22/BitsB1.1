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
|   Banned client management system v1.2
+------------------------------------------------
**/

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";
require_once "include/bt_client_functions.php";

    dbconn(false);
	$lang = array_merge( load_language('global'), load_language('client_ban') );
    staffonly();
    if ($CURUSER['class'] < UC_ADMINISTRATOR) 
        stderr("Error", "This is not your place.");
	
	$HTMLOUT = '';
 	$filename = "include/banned_clients.txt";
	if (filesize($filename) == 0 || !file_exists($filename))
 	$banned_clients = array();
	else {
 	$handle = fopen($filename, "r");
 	$banned_clients = unserialize(fread($handle, filesize($filename)));
 	fclose($handle);
	}
	(isset($_GET["id"]) ? $id = intval($_GET["id"]) : $id = "");
	(isset($_GET["returnto"]) ? $url = urldecode($_GET["returnto"]) : $url = $_SERVER["PHP_SELF"]);
	(isset($_POST["confirm"]) ? $confirm = $_POST["confirm"] : $confirm = "");

	if ($id == "" && !empty($banned_clients)) {

 $HTMLOUT .="<p align='center'><font size=3><strong><u>{$lang['client_ban_current']}</u></strong></font></p>
 	<table align='center' width=70%>
 	<tr>
 	<td class='header' align='center'><strong>{$lang['client_ban_client']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_agent']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_peerid']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_peerasc']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_ban_reason']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_remove']}</strong></td>
 	</tr>";
 foreach($banned_clients as $k => $v)
 	{
 	$HTMLOUT .="<tr>
 	<td class='header' align='center'>".$v["client_name"]."</td>
 	<td class='lista' align='center'>".$v["user_agent"]."</td>
 	<td class='lista' align='center'>".$v["peer_id"]."</td>
 	<td class='lista' align='center'>".$v["peer_id_ascii"]."</td>
 	<td class='lista' align='center'>".stripslashes($v["reason"])."</td>
 	<td class='lista' align='center'><a href='client_clearban.php?id=".$k."&amp;returnto=".urlencode($url)."'><img src='pic/smilies/thumbsup.gif' border='0' alt='Remove Ban?'></a></td>
 	</tr>
 	</table>";
    print stdhead("{$lang['client_ban_stdhead2']}") . $HTMLOUT . stdfoot();
 	exit();
 	}
	}

 	if ($confirm == "Yes") {
 	unset($banned_clients[$id]);
 	$data = serialize($banned_clients);

 	$fd = fopen($filename, "w") or die("Can't update $filename, please CHMOD it to 777");
 	fwrite($fd, $data) or die("Can't save file");
 	fclose($fd);

 	stderr("{$lang['client_ban_success']}", "{$lang['client_ban_removed']}<a href='$url'>Return</a>");
		}
    $row = 0; 
    if ($banned_clients)   
        $row = $banned_clients[$id];
	if ($row) {

 	$HTMLOUT .="<p align='center'>{$lang['client_ban_remove1']}</p>
 	<form method='post' name='action'>
 	<table align='center' width=70%>
 	<tr>
    <td class='header' align='center'><strong>{$lang['client_ban_client']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_agent']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_peerid']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_peerasc']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_ban_reason']}</strong></td>
 	</tr>
 	<tr>
    <td class='lista' align='center'>".$row["client_name"]."</td>
 	<td class='lista' align='center'>".$row["user_agent"]."</td>
 	<td class='lista' align='center'>".$row["peer_id"]."</td>
 	<td class='lista' align='center'>".$row["peer_id_ascii"]."</td>
 	<td class='lista' align='center'>".stripslashes($row["reason"])."</td>
 	</tr>
 	<tr>
 	<td class='block'colspan='5'><strong>&nbsp;</strong></td>
 	</tr>
 	</table>
 	<p align='center'>{$lang['client_ban_sure']}</p>
 	<center>
 	<input type='submit' name='confirm' value='Yes'>&nbsp;<input type='submit' name='confirm' value='No'>
 	<center></form>"; 
	}
	else {
 	stderr("{$lang['client_ban_error']}","{$lang['client_ban_no']}");
 	exit();
	}
 	
	print stdhead("{$lang['client_ban_stdhead3']}") . $HTMLOUT . stdfoot();
	die;
	
?>