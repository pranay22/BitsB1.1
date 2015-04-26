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

    $lang = array_merge( load_language('global'), load_language('confirm') );
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $md5 = isset($_GET['secret']) ? $_GET['secret'] : '';

    if (!is_valid_id($id))
      stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
    
    if (! preg_match( "/^(?:[\d\w]){32}$/", $md5 ) )
		{
			stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_key']}");
		}
		
    dbconn();


    $res = @mysql_query("SELECT passhash, editsecret, status FROM users WHERE id = $id");
    $row = @mysql_fetch_assoc($res);

    if (!$row)
      stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");

    if ($row['status'] != 'pending') 
    {
      header("Refresh: 0; url={$TBDEV['baseurl']}/ok.php?type=confirmed");
      exit();
    }

    //$sec = hash_pad($row['editsecret']);
    $sec = $row['editsecret'];
    if ($md5 != $sec)
      stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");

    @mysql_query("UPDATE users SET status='confirmed', editsecret='' WHERE id=$id AND status='pending'");

    if (!mysql_affected_rows())
      stderr("{$lang['confirm_user_error']}", "{$lang['confirm_cannot_confirm']}");

    logincookie($id, $row['passhash']);

    header("Refresh: 0; url={$TBDEV['baseurl']}/ok.php?type=confirm");

?>