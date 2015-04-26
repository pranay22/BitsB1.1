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
  
if ($CURUSER['class'] < UC_STAFF_LEADER)
stderr("Sorry", "No Permissions.");

$lang = array_merge($lang, load_language('ad_index') );

$HTMLOUT='';
 
    //==Windows Server Load
    $HTMLOUT .="
    <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_serverload']}</span></div>
    <br />
    <table width='100%' border='1' cellspacing='0' cellpadding='1'>
		<tr><td align='center'>
		<table class='main' border='0' width='402'>
    <tr><td style='padding: 0px; background-image: url({$TBDEV['pic_base_url']}loadbarbg.gif); background-repeat: repeat-x'>";
    $perc = get_server_load();
    $percent = min(100, $perc);
    //$HTMLOUT .= "Global Server Load: ($percent %)<center><table class='main' border='0' width='400'></center><tr><td style='padding: 0px; background-image: url(pic/loadbarbg.gif); background-repeat: repeat-x'>";
    if ($percent <= 70) $pic = "loadbargreen.gif";
    elseif ($percent <= 90) $pic = "loadbaryellow.gif";
    else $pic = "loadbarred.gif";
    $width = $percent * 4;
    $HTMLOUT .="<img height='15' width='$width' src=\"{$TBDEV['pic_base_url']}{$pic}\" alt='$percent%' /><br /><br />Currently {$percent}% CPU usage.<br /></td></tr></table></td></tr></table></div><br />";
    //==End
    
    /*
    //==Server Load linux
    $HTMLOUT .="
    <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_serverload']}</span></div>
    <br />
    <table width='100%' border='1' cellspacing='0' cellpadding='1'>
			<tr><td align='center'>
		    <table class='main' border='0' width='402'>
    			<tr><td style='padding: 0px; background-image: url({$TBDEV['pic_base_url']}loadbarbg.gif); background-repeat: repeat-x'>";
    $percent = min(100, round(exec('ps ax | grep -c apache') / 256 * 100));
    if ($percent <= 70) $pic = "loadbargreen.gif";
    elseif ($percent <= 90) $pic = "loadbaryellow.gif";
    else $pic = "loadbarred.gif";
    $width = $percent * 4;
    $HTMLOUT .="<img height='15' width='$width' src=\"{$TBDEV['pic_base_url']}{$pic}\" alt='$percent%' /><br /></td></tr></table></td></tr></table></div><br />";
    //==End
    */

 
print stdhead("Server Load") . $HTMLOUT . stdfoot();
?>