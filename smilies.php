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

require "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";
require_once "include/emoticons.php";

dbconn(false);
loggedinorreturn();

    $lang = load_language('global');
    
    $HTMLOUT = stdhead();
    $HTMLOUT .= begin_main_frame();
    $HTMLOUT .= insert_smilies_frame();
    $HTMLOUT .= end_main_frame();
    $HTMLOUT .= stdfoot();
    print $HTMLOUT ;
?>