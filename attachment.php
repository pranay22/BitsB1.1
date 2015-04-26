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
require_once("include/bbcode_functions.php");
dbconn();
loggrdinorreturn();
parked();

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL &~E_NOTICE);
@ini_set('zlib.output_compression', 'Off');
@set_time_limit(0);
if (@ini_get('output_handler') == 'ob_gzhandler' AND @ob_get_length() !== false) { // if output_handler = ob_gzhandler, turn it off and remove the header sent by PHP
    @ob_end_clean();
    header('Content-Encoding:');
}

if (empty($_REQUEST['attachmentid'])) {
    // return not found header
    httperr();
}

$id = (int)$_GET['attachmentid'];

$attachment_dir = ROOT_PATH . "forum_attachments";
$at = mysql_query("SELECT * FROM attachments WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$resat = mysql_fetch_assoc($at);
$filename = $attachment_dir . '/' . $resat['filename'];

if (!$resat || !is_file($filename) || !is_readable($filename)) {
    // return not found header
    httperr();
}
if ($_GET['action'] == 'delete') {
    if (get_user_class() >= UC_MODERATOR) {
        @unlink($filename);
        mysql_query("DELETE FROM attachments WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        mysql_query("DELETE FROM attachmentdownloads WHERE fileid=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        die('<font color=\"red\">File successfull deleted...');
    } else {
        httperr();
    }
}
$file_extension = strtolower(substr(strrchr($filename, "."), 1));
switch ($file_extension) {
    case "pdf": $ctype = "application/pdf";
        break;
    case "exe": $ctype = "application/octet-stream";
        break;
    case "zip": $ctype = "application/zip";
        break;
    case "rar": $ctype = "application/zip";
        break;
    case "doc": $ctype = "application/msword";
        break;
    case "xls": $ctype = "application/vnd.ms-excel";
        break;
    case "ppt": $ctype = "application/vnd.ms-powerpoint";
        break;
    case "gif": $ctype = "image/gif";
        break;
    case "png": $ctype = "image/png";
        break;
    case "jpeg":
    case "jpg": $ctype = "image/jpg";
        break;
    default: $ctype = "application/force-download";
}

mysql_query("UPDATE attachments SET downloads = downloads + 1 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$res = mysql_query("SELECT fileid FROM attachmentdownloads WHERE fileid=" . sqlesc($id) . " AND userid=" . sqlesc($CURUSER['id']));
if (mysql_num_rows($res) == "0")
    mysql_query("INSERT INTO attachmentdownloads (filename,fileid,username,userid,date,downloads) VALUES (" . sqlesc($resat['filename']) . ", " . sqlesc($id) . ", " . sqlesc($CURUSER['username']) . ", " . sqlesc($CURUSER['id']) . ", " . sqlesc(get_date_time()) . ", 1)") or sqlerr(__FILE__, __LINE__);
else
    mysql_query("UPDATE attachmentdownloads SET downloads = downloads + 1 WHERE fileid=" . sqlesc($id) . " AND userid=" . sqlesc($CURUSER['id']));
header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false); // required for certain browsers
header("Content-Type: $ctype");
// change, added quotes to allow spaces in filenames, by Rajkumar Singh
header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($filename));
readfile("$filename");
exit();

?>
