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
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
require_once("include/mood.php");
dbconn(false);

		$htmlout = '';
    $htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
    <meta name='generator' content='TBDev.net' />
	  <meta name='MSSmartTagsPreventParsing' content='TRUE' />
		<title>User Moods</title>
    <link rel='stylesheet' href='./1.css' type='text/css' />
    </head>
    <body>";

    if (isset($_GET["mood"]) && (isset($_GET["id"]))) {
    $moodid = (isset($_GET['id'])?0 + $_GET['id']:'');
    $moodname = (isset($_GET['mood'])?htmlspecialchars($_GET['mood']):'');
    $moodhdr = str_replace('+', ' ', $moodname);
    mysql_query("UPDATE users SET mood={$moodid} WHERE id={$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
    $htmlout .= "<h3 align=\"center\">" . $CURUSER['username'] . "'s Mood has been changed to {$moodhdr}!</h3><table><tr><td>";
   
    $htmlout .= "<script type='text/javascript'>
    /*<![CDATA[*/
    opener.location.reload(true);
    self.close();
    /*]]>*/
    </script>";

    }

$htmlout .= "<h3 align=\"center\">" . $CURUSER['username'] . "'s Mood</h3><table><tr><td>";

foreach($mood as $key => $value) {
    $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
    $moodid = $change[$value['id']]['id'];
    $moodname = $change[$value['id']]['name'];
    $moodurl = str_replace(' ', '+', $moodname);
    $moodpic = $change[$value['id']]['image'];
    $htmlout .= "<a href='?mood=" . $moodurl . "&amp;id=" . $moodid . "'>
    <img src='" . $TBDEV['pic_base_url'] . "moods/{$moodpic}' alt='{$moodname}' border='0' />{$moodname}</a>&nbsp;&nbsp;";
    }

$htmlout .= "<br /><a href=\"javascript:self.close();\"><font color=\"#FF0000\">Close window</font></a>";
$htmlout .= "</td></tr></table></body></html>";
print $htmlout;
?>
