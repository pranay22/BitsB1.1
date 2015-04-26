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

require_once ("include/user_functions.php");
require_once ("include/html_functions.php");
require_once ("include/bbcode_functions.php");
staffonly();

if ($CURUSER['class'] < UC_MODERATOR)
stderr("Error", "Access denied.");

$lang = array_merge( $lang );

$HTMLOUT = '';

$limit = 25;

if (isset($_GET["amount"]) && (int)$_GET["amount"]) {
    if (intval($_GET["amount"]) != $_GET["amount"]) {
        stderr("Error", "Amount wasn't an integer.");
    }

    $limit = 0 + $_GET["amount"];
    if ($limit > 999)
    $limit = 1000;
    if ($limit < 10)
    $limit = 10;
}

$HTMLOUT .="<p align=\"center\">Showing&nbsp;{$limit}&nbsp;latest&nbsp;comments.</p>\n";

$subres = sql_query("SELECT comments.id, torrent, text, user, comments.added , editedby, editedat, avatar, warned, " . "username, title, class FROM comments LEFT JOIN users ON comments.user = users.id " . " ORDER BY comments.id DESC limit 0," . $limit) or sqlerr(__FILE__, __LINE__);
$allrows = array();
while ($subrow = mysql_fetch_assoc($subres))
$allrows[] = $subrow;

function commenttable_new($rows)
{
    global $CURUSER;
    $htmlout='';
    $htmlout .= begin_main_frame();
    $htmlout .= begin_frame();
    $count = 0;
    foreach ($rows as $row) {
        $subres = sql_query("SELECT name from torrents where id=" . sqlesc($row["torrent"])) or sqlerr(__FILE__, __LINE__);
        $subrow = mysql_fetch_assoc($subres);
        $htmlout .="<br /><a href=\"details.php?id=" . htmlspecialchars($row["torrent"]) . "\">" . htmlspecialchars($subrow["name"]) . "</a><br />\n";
        $htmlout .="<p class='sub'>#" . $row["id"] . "&nbsp;by&nbsp;";
        if (isset($row["username"])) {
        $htmlout .="<a name='comm" . $row["id"] . "' href='./userdetails.php?id=" . htmlspecialchars($row["user"]) . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>" . ($row["warned"] == "yes" ? "<img src=\"pic/warned.png\" alt=\"Warned\" />" : "");
        } else {
        $htmlout .="<a name=\"comm" . htmlspecialchars($row["id"]) . "\"><i>(orphaned)</i></a>\n";
        }
        $htmlout .="&nbsp;at&nbsp;" . get_date($row["added"], 'DATE',0,1) . "" . "&nbsp;-&nbsp;[<a href='./comment.php?action=edit&amp;cid=$row[id]'>Edit</a>]" . "&nbsp;-&nbsp;[<a href='comment.php?action=delete&amp;cid=$row[id]'>Delete</a>]</p>\n";
        $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
        if (!$avatar) {
        $avatar = "./pic/default_avatar.gif";
        }
        $htmlout .= begin_table(true);
        $htmlout .="<tr valign='top'>\n";
        $htmlout .="<td align='center' width='150' style='padding: 0px'><img width='150' src='$avatar' alt='Avatar' title='Avatar' /></td>\n";
        $htmlout .="<td class='text'>" . format_comment($row["text"]) . "</td>\n";
        $htmlout .="</tr>\n";
        $htmlout .= end_table();
    }
    $htmlout .= end_frame();
    $htmlout .= end_main_frame();
    return $htmlout;
    }

$HTMLOUT .= commenttable_new($allrows);
print stdhead("Comments") . $HTMLOUT . stdfoot();
die;
?>