<?php
/**
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.0
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
|   Standard Rules system v0.6
+------------------------------------------------
**/

ob_start("ob_gzhandler");

require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";
require_once"include/bbcode_functions.php";

dbconn();

//loggedinorreturn();
    $lang = array_merge( load_language('global'), load_language('rules') );

    $HTMLOUT = '';
    $HTMLOUT .= begin_main_frame();

    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_general_header']}</div>
    <div class='headbody' style='width:730px;'>{$lang['rules_general_body']}</div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_downloading_header']}</div>
    <div class='headbody' style='width:730px;'>{$lang['rules_downloading_body']}</div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_forum_header']}</div>
    <div class='headbody' style='width:730px;'>{$lang['rules_forum_body']}</div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_avatar_header']}</div>
    <div class='headbody' style='width:730px;'>{$lang['rules_avatar_body']}</div><br />";

    if (isset($CURUSER) AND $CURUSER['class'] >= UC_UPLOADER) {
        $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_uploading_header']}</div>
        <div class='headbody' style='width:730px;'>{$lang['rules_uploading_body']}</div><br />";
    }
    
    if (isset($CURUSER) AND $CURUSER['class'] >= UC_MODERATOR) {
        
        $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_moderating_header']}</div>
        <div class='headbody' style='width:730px;'>
        <table border='0' cellspacing='3' cellpadding='0'>
        {$lang['rules_moderating_body']}
        </table></div><br />"; 
      
        $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_mod_rules_header']}</div>
        <div class='headbody' style='width:730px;'>{$lang['rules_mod_rules_body']}</div><br />"; 
      
        $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['rules_mod_options_header']}</div>
        <div class='headbody' style='width:730px;'>{$lang['rules_mod_options_body']}</div><br />"; 
    }
    
    $HTMLOUT .= end_main_frame();
    
    print stdhead("{$lang['rules_rules']}") . $HTMLOUT . stdfoot();
?>