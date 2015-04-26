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

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "{$lang['forum_new_topic_access']}";
	exit();
}


    $forumid = (int)$_GET["forumid"];

    if (!is_valid_id($forumid))
      header("Location: {$TBDEV['baseurl']}/forums.php");


    $HTMLOUT = stdhead("{$lang['forum_new_topic_newtopic']}");

    $HTMLOUT .= begin_main_frame();

    $HTMLOUT .= insert_compose_frame($forumid);

    $HTMLOUT .= end_main_frame();

    $HTMLOUT .= stdfoot();
    
    print $HTMLOUT;

    die;

?>