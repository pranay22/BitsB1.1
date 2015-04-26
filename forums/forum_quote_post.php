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
	print "{$lang['forum_quote_post_access']}";
	exit();
}

    
  //-------- Action: Quote

		$topicid = (int)$_GET["topicid"];

		if (!is_valid_id($topicid))
			stderr("{$lang['forum_quote_post_error']}", "{$lang['forum_quote_post_invalid']}");

    $HTMLOUT = stdhead("{$lang['forum_quote_post_reply']}");

    $HTMLOUT .= begin_main_frame();

    $HTMLOUT .= insert_compose_frame($topicid, false, true);

    $HTMLOUT .= end_main_frame();

    $HTMLOUT .= stdfoot();
    
    print $HTMLOUT;

    die;

  
?>