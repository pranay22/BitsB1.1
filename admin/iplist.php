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
+------------------------------------------------
**/
 
require_once "include/bittorrent.php"; 
require_once "include/user_functions.php"; 
 
dbconn(false); 
loggedinorreturn(); 
 
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

//secureip(UC_MODERATOR); 
 
    $lang = array_merge( $lang, load_language('ad_iplist') ); 
 
    $HTMLOUT = ''; 
 
    $id = 0 + $_GET["id"]; 
 
    if ($id == "0") 
        stderr("{$lang['stderr_error']}", "{$lang['stderr_errormsg']}"); 
 
    if (get_user_class() < UC_MODERATOR) 
        stderr("{$lang['stderr_error']}", "{$lang['stderr_errormsg2']}"); 
 
//start IPlogger----------------------------------------- 
  $ne_res = sql_query("SELECT * FROM users WHERE id = $id") or die(mysql_error()); 
while ($arr2 = mysql_fetch_assoc($ne_res)) { 
          $HTMLOUT .="<b>{$lang['iplist_user']} $arr2[username]<br />{$lang['iplist_email']} $arr2[email]<br />{$lang['iplist_ips']} </b>"; 
 
} 
 
  $ip_res = sql_query("SELECT * FROM ips WHERE userid = $id") or die(mysql_error()); 
while ($arr = mysql_fetch_assoc($ip_res)) { 
 
          $HTMLOUT .="<b> $arr[ip] ::</b>"; 
 
} 
 
//end IPlogger--------------------------------------------- 
 
    print stdhead('IPList') . $HTMLOUT . stdfoot(); 
?>