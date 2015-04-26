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
+------------------------------------------------
**/

    define('IN_TBDEV_ADMIN', TRUE);

    require_once "include/bittorrent.php";
    require_once "include/user_functions.php";

    dbconn(false);

    loggedinorreturn();
    
    $lang = array_merge( load_language('global'), load_language('admin') );
  
    if ($CURUSER['class'] < UC_MODERATOR)
      stderr("{$lang['admin_user_error']}", "{$lang['admin_unexpected']}");
  
  
    $action = isset($_GET["action"]) ? $_GET["action"] : '';
    $forum_pic_url = $TBDEV['pic_base_url'] . 'forumicons/';
  
    define( 'F_IMAGES', $TBDEV['pic_base_url'] . 'forumicons');
    define( 'POST_ICONS', F_IMAGES.'/post_icons');
    
    $ad_actions = array('bans'            => 'bans', 
                        'adduser'         => 'adduser', 
                        'stats'           => 'stats', 
                        'delacct'         => 'delacct', 
                        'testip'          => 'testip', 
                        'usersearch'      => 'usersearch', 
                        'mysql_overview'  => 'mysql_overview', 
                        'mysql_stats'     => 'mysql_stats', 
                        'categories'      => 'categories', 
                        'newusers'        => 'newusers', 
                        'resetpassword'   => 'resetpassword',
                        'docleanup'       => 'docleanup',
                        'log'             => 'log',
                        'news'            => 'news',
                        'forummanage'     => 'forummanage',
                        'pmview'	      =>'pmview',
                        'forummanager'    => 'forummanager',
                        'moforums'        => 'moforums',
                        'msubforums'      => 'msubforums',
                        'shistory'        => 'shistory',
                        'uncon'           => 'uncon',
                        'failedlogins'    => 'failedlogins',
                        'latest'          => 'latest',
                        'freeleech'       => 'freeleech',
                        'freeslots'       => 'freeslots',
                        'freeusers'       => 'freeusers',
                        'stats_extra'     => 'stats_extra',
                        'cheaters'        => 'cheaters',
                        'floodlimit'      => 'floodlimit',
                        'massbonus'       => 'massbonus',
                        'whois' 	      => 'whois',
                        'grouppm'         => 'grouppm',
                        'reports'         => 'reports',
                        'changemail'	  => 'changemail',
                        'changename'	  => 'changename',
                        'snatched_torrents'   => 'snatched_torrents',
                        'comment_overview'    => 'comment_overview',
                        'inactive'        => 'inactive',
                        'bannedemails'    => 'bannedemails',
                        'reset'	          => 'reset',
                        'findnotconnectable'  => 'findnotconnectable',
                        'sysoplog'        => 'sysoplog',
                        'ipcheck'	      => 'ipcheck',
                        'slotmanage'      => 'slotmanage',
                        'nameblacklist'   => 'nameblacklist',
                        'editlog'	      => 'editlog',
                        'datareset'       => 'datareset',
                        'load'            => 'load',
                        'snews2'	      => 'snews2',
                        'snews'	          => 'snews',
                        'system_view'   => 'system_view',
                        'ipcheck' =>'ipcheck',
                        'massmail'=>'massmail',
                        'detectclients'   => 'detectclients',
                        'iphistory'       => 'iphistory',
                        'iplist'          => 'iplist',
                        'bonusmanage' => 'bonusmanage',
                        'donations' => 'donations',
                        'parked' => 'parked',
                        );
    
    if( in_array($action, $ad_actions) AND file_exists( "admin/{$ad_actions[ $action ]}.php" ) )
    {
      require_once "admin/{$ad_actions[ $action ]}.php";
    }
    else
    {
      require_once "admin/index.php";
    }
    
?>