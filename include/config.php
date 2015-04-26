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

error_reporting(E_ALL);

define('SQL_DEBUG', 2);

/* Compare php version for date/time stuff etc! */
	if (version_compare(PHP_VERSION, "5.1.0RC1", ">="))
		date_default_timezone_set('Europe/London');


define('TIME_NOW', time());

$TBDEV['time_adjust'] =  0;
$TBDEV['time_offset'] = '0'; 
$TBDEV['time_use_relative'] = 1;
$TBDEV['time_use_relative_format'] = '{--}, h:i A';
$TBDEV['time_joined'] = 'j-F y';
$TBDEV['time_short'] = 'jS F Y - h:i A';
$TBDEV['time_long'] = 'M j Y, h:i A';
$TBDEV['time_tiny'] = '';
$TBDEV['time_date'] = '';

// DB setup
$TBDEV['mysql_host'] = "localhost";
$TBDEV['mysql_user'] = "pranay";
$TBDEV['mysql_pass'] = "pritam22";
$TBDEV['mysql_db']   = "bitsb";

// Cookie setup
$TBDEV['cookie_prefix']  = 'bitsb_'; // This allows you to have multiple trackers, eg for demos, testing etc.
$TBDEV['cookie_path']    = '/bitsb'; // ATTENTION: You should never need this unless the above applies eg: /tbdev
$TBDEV['cookie_domain']  = ''; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
                              
$TBDEV['site_online'] = 1;
$TBDEV['tracker_post_key'] = 'changethisorelse';
$TBDEV['max_torrent_size'] = 1000000;
$TBDEV['announce_interval'] = 60 * 30;
$TBDEV['signup_timeout'] = 86400 * 3;
$TBDEV['minvotes'] = 1;
//increased due to testing purpose, normal value: 60 days
$TBDEV['max_dead_torrent_time'] = 3600 * 3600;

// Max users on site
$TBDEV['maxusers'] = 500; // LoL Who we kiddin' here?
// Number of invites allowed after reaching the user number limit
$TBDEV['invites'] = 200; // set this to what you want
$TBDEV['openreg'] = true; //==true=open, false = closed (This is thw switch for opening or closing the open reg system)
$TBDEV['funds'] = 100; //Monthly donations in $.

if ( strtoupper( substr(PHP_OS, 0, 3) ) == 'WIN' )
  {
    $file_path = str_replace( "\\", "/", dirname(__FILE__) );
    $file_path = str_replace( "/include", "", $file_path );
  }
  else
  {
    $file_path = dirname(__FILE__);
    $file_path = str_replace( "/include", "", $file_path );
  }
  
define('ROOT_PATH', $file_path);
$TBDEV['torrent_dir'] = ROOT_PATH . '/torrents'; # must be writable for httpd user  
$TBDEV['dictbreaker'] = ROOT_PATH . '/dictbreaker'; 

# the first one will be displayed on the pages
$TBDEV['announce_urls'] = array();
$TBDEV['announce_urls'][] = "http://localhost/bitsb/announce.php";
//$TBDEV['announce_urls'] = "http://localhost:2710/announce";
//$TBDEV['announce_urls'] = "http://domain.com:83/announce.php";

if ($_SERVER["HTTP_HOST"] == "127.0.0.1")
  $_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"];
  
$TBDEV['baseurl'] = "http://" . $_SERVER["HTTP_HOST"]."/bitsb";

/*
## DO NOT UNCOMMENT THIS: IT'S FOR LATER USE!
$host = getenv( 'SERVER_NAME' );
$script = getenv( 'SCRIPT_NAME' );
$script = str_replace( "\\", "/", $script );

  if( $host AND $script )
  {
    $script = str_replace( '/index.php', '', $script );

    $TBDEV['baseurl'] = "http://{$host}{$script}";
  }
*/

// Email for sender/return path.
$TBDEV['site_email'] = "noreply@localhost";

$TBDEV['site_name'] = "BitsB";
$TBDEV['torrent_prefix'] = 'BitsB';	//Prefix of every torrent in download.php while downloading torrent

$TBDEV['language'] = 'en';
$TBDEV['msg_alert'] = 1;    // saves a query when off (1 on/0 off)
$TBDEV['staffmsg_alert'] = 1;   // saves a query when off (1 on/0 off)
$TBDEV['report_alert'] = 1; // saves a query when off (1 on/0 off)
$TBDEV['reports']      = 1; // saves a query when off (1 on/0 off)
$TBDEV['karma']        = 1; // saves a query when off (1 on/0 off)
$TBDEV['textbbcode']   = 1; // saves a query when off (1 on/0 off)
$TBDEV['max_slots'] =1; // saves a query when off (1 on/0 off)  (turns on or off max slots system)
$TBDEV['forums_online'] = 1;    // forum online or offline option (1 online/0 offine)
$TBDEV['forums_autoshout_on'] = 1;  //forum autoshout ON/OFF in shoutbox
$TBDEV['forums_seedbonus_on'] = 1;	//forum seedbonus addition ON/OFF
$TBDEV['bot_id'] = 2;	//Robot ID of shoutbox
$TBDEV['latest_posts_limit'] = 5; //query limit for latest forum posts on index

$TBDEV['autoclean_interval'] = 900;
$TBDEV['sql_error_log'] = ROOT_PATH.'/logs/sql_err_'.date("M_D_Y").'.log';
$TBDEV['pic_base_url'] = $TBDEV['baseurl']."/pic/";
$TBDEV['stylesheet'] = "./1.css";
$TBDEV['readpost_expiry'] = 14*86400; // 14 days
//set this to size of user signatures
$TBDEV['sig_img_height'] = 100;
$TBDEV['sig_img_width'] = 500;
//set this to size of user avatars
$TBDEV['av_img_height'] = 100;
$TBDEV['av_img_width'] = 100;
$TBDEV['bucket_maxsize'] = 500*1024; #max size set to 500kb
$TBDEV['allowed_ext'] = array('image/gif', 'image/png', 'image/jpeg');
$TBDEV['failedlogins'] = 3; // Max failed logins before IP ban

//Anti-flood system
$TBDEV['flood_time'] = 900; //comment/forum/pm flood limit 900=15mins
$TBDEV['flood_file'] = ROOT_PATH . '/include/settings/limitfile.txt';

//for youtube mod (torrents)
$TBDEV['movie_cats'] = array(5,11,3,10);
$youtube_pattern = "/^http\:\/\/www\.youtube\.com\/watch\?v\=[\w]{11}/i";

//Username blacklisting system
$TBDEV['nameblacklist'] = ROOT_PATH.'/cache/nameblacklist.txt';

define ('UC_BANNED', 0);
define ('UC_USER', 1);
define ('UC_POWER_USER', 2);
define ('UC_VIP', 3);
define ('UC_UPLOADER', 4);
define ('UC_FORUM_MOD', 5);
define ('UC_MODERATOR', 6);
define ('UC_ADMINISTRATOR', 7);
define ('UC_SYSOP', 8);
define ('UC_STAFF_LEADER', 9);

//== Add ALL staff IDs before promote them or before add this code. 
$TBDEV['allowed_staff']['id'] = array(1,3);

//Do not modify -- versioning system
//This will help identify code for support issues at tbdev.net
define ('BITSB_VERSION','BitsB_1.1');

?>