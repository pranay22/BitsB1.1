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

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
dbconn();
loggedinorreturn();

$lang =  array_merge( load_language('global') );

$HTMLOUT='';

if (!mkglobal("torrent"))
stderr("Error", "missing form data");

$userid = $CURUSER['id'];
if (!is_valid_id($userid))
stderr("Error", "Invalid ID.");

if ($userid != $CURUSER["id"])
stderr("Error", "Access denied.");

$torrentid = 0 + $_GET["torrent"];
if (!is_valid_id($torrentid))
die();

if (!isset($torrentid))
stderr("Error", "Failed. No torrent selected");

$action = isset($_GET["action"]) ?$_GET["action"] : '';

if ($action == 'add')
{

$torrentid = (int)$_GET['torrent'];
$sure = isset($_GET['sure'])?$_GET['sure']:'';
if (!is_valid_id($torrentid))
stderr("Error", "Invalid ID.");

$hash = md5('s5l6t0mu55yt4hwa7e5'.$torrentid.'add'.'s5l6t0mu55yt4hwa7e5');
if (!$sure)
 stderr("Add Bookmark","Do you really want to add this bookmark? Click\n" .
"<a href='?torrent=$torrentid&amp;action=add&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", FALSE);

if ($_GET['h'] != $hash)
stderr('Error','what are you doing?');

function addbookmark($torrentid) {
global $CURUSER;
if ((get_row_count("bookmarks", "WHERE userid=$CURUSER[id] AND torrentid = $torrentid")) > 0)
stderr("Error", "Torrent already bookmarked");
mysql_query("INSERT INTO bookmarks (userid, torrentid) VALUES ($CURUSER[id], $torrentid)") or sqlerr(__FILE__,__LINE__);
}

$HTMLOUT .= addbookmark($torrentid);
$HTMLOUT .="<h2>Bookmark added!</h2>";
}

if ($action == 'delete')
{
$torrentid = (int)$_GET['torrent'];
$sure = isset($_GET['sure'])?$_GET['sure']:'';
if (!is_valid_id($torrentid))
stderr("Error", "Invalid ID.");

$hash = md5('s5l6t0mu55yt4hwa7e5'.$torrentid .'delete'.'s5l6t0mu55yt4hwa7e5');
if (!$sure)
stderr("Delete Bookmark","Do you really want to delete this bookmark? Click\n" .
"<a href='?torrent=$torrentid&amp;action=delete&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", FALSE);

if ($_GET['h'] != $hash)
stderr('Error','what are you doing?');

function deletebookmark($torrentid) {
global $CURUSER;
mysql_query("DELETE FROM bookmarks WHERE torrentid = $torrentid AND userid = $CURUSER[id]");
}

$HTMLOUT .= deletebookmark($torrentid);
$HTMLOUT .="<h2>Bookmark deleted!</h2>";
}

elseif ($action == 'public')
{
$torrentid = (int)$_GET['torrent'];
$sure = isset($_GET['sure'])?$_GET['sure']:'';
if (!is_valid_id($torrentid))
stderr("Error", "Invalid ID.");

$hash = md5('s5l6t0mu55yt4hwa7e5'.$torrentid.'public'.'s5l6t0mu55yt4hwa7e5');
if (!$sure)
stderr("Share Bookmark","Do you really want to mark this bookmark public? Click\n" .
"<a href='?torrent=$torrentid&amp;action=public&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", FALSE);

if ($_GET['h'] != $hash)
stderr('Error','what are you doing?');

function publickbookmark($torrentid) {
global $CURUSER;
mysql_query("UPDATE bookmarks SET private = 'no' WHERE private = 'yes' AND torrentid = $torrentid AND userid = $CURUSER[id]");
}

$HTMLOUT .= publickbookmark($torrentid);
$HTMLOUT .="<h2>Bookmark made public!</h2>";
}

elseif ($action == 'private')
{
$torrentid = (int)$_GET['torrent'];
$sure = isset($_GET['sure'])?$_GET['sure']:'';
if (!is_valid_id($torrentid))
stderr("Error", "Invalid ID.");

$hash = md5('s5l6t0mu55yt4hwa7e5'.$torrentid.'private'.'s5l6t0mu55yt4hwa7e5');
if (!$sure)
stderr("Make Bookmark Private","Do you really want to mark this bookmark private? Click\n" .
"<a href='?torrent=$torrentid&amp;action=private&amp;sure=1&amp;h=$hash'>here</a> if you are sure.", FALSE);

if ($_GET['h'] != $hash)
stderr('Error','what are you doing?');

if (!is_valid_id($torrentid))
stderr("Error", "Invalid ID.");

function privatebookmark($torrentid) {
global $CURUSER;
mysql_query("UPDATE bookmarks SET private = 'yes' WHERE private = 'no' AND torrentid = $torrentid AND userid = $CURUSER[id]");
}

$HTMLOUT .= privatebookmark($torrentid);
$HTMLOUT .="<h2>Bookmark made private!</h2>";
}

if (isset($_POST["returnto"]))
$ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">Go back to whence you came</a>";
else
$ret = "<a href=\"bookmarks.php\">Go to My Bookmarks</a><br /><br />
<a href=\"browse.php\">Go to Browse</a>";
    $HTMLOUT .= $ret;
print stdhead('Bookmark') . $HTMLOUT . stdfoot();
?>