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
require_once ROOT_PATH.'/include/user_functions.php';
dbconn(false);
$lang = array_merge( load_language('global'), load_language('delete') );

staffonly();

if ($CURUSER['class'] < UC_SYSOP)
    stderr($lang['delete_sorry'], $lang['delete_nosysop']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
      
    if (!$username)
        stderr($lang['delete_error'], $lang['delete_correct']);

    $res = mysql_query("SELECT * FROM users WHERE username=" . sqlesc($username)) or sqlerr();
    if (mysql_num_rows($res) != 1)
        stderr($lang['delete_error'], $lang['delete_verify']);
    $arr = mysql_fetch_assoc($res);

    $id = $arr['id'];
    $res = mysql_query("DELETE FROM users WHERE id=" . sqlesc($id) . "") or sqlerr();
    if (mysql_affected_rows() != 1)
    stderr($lang['delete_error'], $lang['delete_unable']);
    write_log("$arr[username] | ID: $arr[id] | Deleted by staff");
    header("Refresh: 5; url='{$TBDEV['baseurl']}/index.php'");
    stderr($lang['delete_success'], "The account " . htmlspecialchars($username) . " was deleted.<br />You'll be automatically redirected to home page in 5 seconds.");
}

print stdhead($lang['delete_dusers']) . $HTMLOUT . stdfoot();
?>