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

// Include the random string file
//require 'rand.php';
$str = '';
	for($i=0; $i<6; $i++){
$str .= chr(rand(0,25)+65);
}

// Begin a new session
session_start();

// Set the session contents
$_SESSION['captcha_id'] = $str;

?>