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

//==Start execution time (for mysql & php percentage & efficiency)
$q['start'] = microtime(true);
global $t;
$t=$q['start'];
//==End

require_once("include/config.php");
//require_once("cleanup.php");  <<-- Disabled because I use this source for testing :)
require_once ROOT_PATH.'/cache/free_cache.php';

/**** validip/getip courtesy of manolete <manolete@myway.com> ****/
// IP Validation
function validip($ip)
{
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
		// reserved IANA IPv4 addresses
		// http://www.iana.org/assignments/ipv4-address-space
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r)
		{
				$min = ip2long($r[0]);
				$max = ip2long($r[1]);
				if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

// Patched function to detect REAL IP address if it's valid
function getip() {
   if (isset($_SERVER)) {
     if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
     } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
       $ip = $_SERVER['HTTP_CLIENT_IP'];
     } else {
       $ip = $_SERVER['REMOTE_ADDR'];
     }
   } else {
     if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
       $ip = getenv('HTTP_X_FORWARDED_FOR');
     } elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
       $ip = getenv('HTTP_CLIENT_IP');
     } else {
       $ip = getenv('REMOTE_ADDR');
     }
   }

   return $ip;
 }

function parked()
{
global $CURUSER;
if ($CURUSER["parked"] == "yes")
stderr("Error", "Your account is currently parked.");
}

function dbconn($autoclean = false)
{
    global $TBDEV;

    if (!@mysql_connect($TBDEV['mysql_host'], $TBDEV['mysql_user'], $TBDEV['mysql_pass']))
    {
	  switch (mysql_errno())
	  {
		case 1040:
		case 2002:
			if ($_SERVER['REQUEST_METHOD'] == "GET")
				die("<html><head><meta http-equiv='refresh' content=\"5 $_SERVER[REQUEST_URI]\"></head><body><table border='0' width='100%' height='100%'><tr><td><h3 align='center'>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>");
			else
				die("Too many users. Please press the Refresh button in your browser to retry.");
        default:
    	    die("[" . mysql_errno() . "] dbconn: mysql_connect: " . mysql_error());
      }
    }
    mysql_select_db($TBDEV['mysql_db'])
        or die('dbconn: mysql_select_db: ' . mysql_error());
    //sql_query("SET NAMES utf8");
    userlogin();

    if ($autoclean)
        register_shutdown_function("autoclean");
}

function autoshout($msg = '')
{
    $message = $msg;
    sql_query("INSERT INTO shoutbox (date, text, userid, username) VALUES (" . implode(", ", array_map("sqlesc", array(time(), $message, '2', 'System'))) . ")") or sqlerr(__FILE__, __LINE__);
}

function time_return($stamp){
    $ysecs = 365*24*60*60;
    $mosecs = 31*24*60*60;
    $wsecs = 7*24*60*60;
    $dsecs = 24*60*60;
    $hsecs = 60*60;
    $msecs = 60;

    $years = floor($stamp/$ysecs);
    $stamp %= $ysecs;
    $months = floor($stamp/$mosecs);
    $stamp %= $mosecs;
    $weeks = floor($stamp/$wsecs);
    $stamp %= $wsecs;
    $days = floor($stamp/$dsecs);
    $stamp %= $dsecs;
    $hours = floor($stamp/$hsecs);
    $stamp %= $hsecs;
    $minutes = floor($stamp/$msecs);
    $stamp %= $msecs;
    $seconds = $stamp;

    if($years == 1){
        $nicetime['years'] = "1 year";}
    elseif($years > 1){
        $nicetime['years'] = $years."years";}
    if($months == 1){
        $nicetime['months'] = "1 month";}
    elseif($months > 1){
        $nicetime['months'] = $months." months";}
    if($weeks == 1){
        $nicetime['weeks'] = "1 week";}
    elseif($weeks > 1){
        $nicetime['weeks'] = $weeks." weeks";}
    if($days == 1){
        $nicetime['days'] = "1 day";}
    elseif($days > 1){
        $nicetime['days'] = $days." days";}
    if($hours == 1){
        $nicetime['hours'] = "1 hour";}
    elseif($hours > 1){
        $nicetime['hours'] = $hours." hours";}
    if($minutes == 1){
        $nicetime['minutes'] = "1 minute";}
    elseif($minutes > 1){
        $nicetime['minutes'] = $minutes." minutes";}
    if($seconds == 1){
        $nicetime['seconds'] = "1 second";}
    elseif($seconds > 1){
        $nicetime['seconds'] = $seconds." seconds";}
    if(is_array($nicetime)){
        return implode(", ", $nicetime);}
} 

function userlogin() {
    global $TBDEV;
    unset($GLOBALS["CURUSER"]);
    $ip_octets = explode( ".", getenv('REMOTE_ADDR') );

    $ip = getip();
	$nip = ip2long($ip);

    require_once "cache/bans_cache.php";
    if(count($bans) > 0) {
      foreach($bans as $k) {
        if($nip >= $k['first'] && $nip <= $k['last']) {
        header("HTTP/1.0 403 Forbidden");
        print "<html><body><h1>403 Forbidden</h1>Unauthorized IP address. Your IP or ISP is banned from entering our site, so get lost!</body></html>\n";
        exit();
        }
      }
      unset($bans);
    }
    if (!$TBDEV['site_online'] || !get_mycookie('uid') || !get_mycookie('pass')|| !get_mycookie('hashv') ) 
       return; 
    $id = 0 + get_mycookie('uid'); 
    if (!$id OR (strlen( get_mycookie('pass') ) != 32) OR (get_mycookie('hashv') != hashit($id,get_mycookie('pass')))) 
       return;    
    $res = sql_query("SELECT * FROM users WHERE id = $id AND enabled='yes' AND status = 'confirmed'");// or die(mysql_error());
    $row = mysql_fetch_assoc($res);
    /** Varifying the allowed id's of staffs from config.php **/
    if ($row["class"] >= UC_MODERATOR) { 
        $allowed_ID =  $TBDEV['allowed_staff']['id']; 
        if (!in_array(((int)$row["id"]),$allowed_ID,true)){ 
        $msg = "Fake Account Detected: Username: ".$row["username"]." - UserID: ".$row["id"]." - UserIP : ".getip(); 
        write_log($msg); 
        /** Demote and disable **/ 
        sql_query("UPDATE users SET enabled = 'no', class = 0 WHERE id = '".$row["id"]."'") or sqlerr(__file__, __line__); 
        logoutcookie(); 
        }
    }
    if (!$row)
        return;
    //$sec = hash_pad($row["secret"]);
    if(get_mycookie('pass') !== md5($row["passhash"]."-".$ip_octets[0]."-".$ip_octets[1])) 
        return;
    
    //total online-time by d6bmg
    $time = time();
    if($time - $row['last_access_numb'] < 300){
    $onlinetime = time() - $row['last_access_numb'];
    $userupdate[] = "onlinetime = onlinetime + ".sqlesc($onlinetime);
    }
    $userupdate[] = "last_access_numb = ".sqlesc($time);
    //end online-time

    $userupdate[] = "last_access = ".sqlesc( TIME_NOW ); 
    $userupdate[] = "ip = ".sqlesc($ip);

    sql_query("UPDATE users SET ".implode(", ", $userupdate)." WHERE id=" . $row["id"]); // or die(mysql_error());
    //end
    //mysql_query("UPDATE users SET last_access='" . TIME_NOW . "', ip=".sqlesc($ip)." WHERE id=" . $row["id"]);// or die(mysql_error());  (decrypted)
    $row['ip'] = $ip;
    //==Ip log
    if (($ip != $row["ip"]) && $row["ip"])
    sql_query('INSERT INTO iplog (ip, userid, access) VALUES (' . sqlesc($ip) . ', ' . $row['id'] . ', \'' . $row['last_access'] . '\') on DUPLICATE KEY update access=values(access)');
    //==End
    $GLOBALS["CURUSER"] = $row;
}

function autoclean() {
    global $TBDEV;
    $now = time();
    //$docleanup = 0;
    $res = sql_query("SELECT value_u FROM avps WHERE arg = 'lastcleantime'");
    $row = mysql_fetch_array($res);
    if (!$row) {
        sql_query("INSERT INTO avps (arg, value_u) VALUES ('lastcleantime',$now)");
        return;
    }
    $ts = $row[0];
    if ($ts + $TBDEV['autoclean_interval'] > $now)
        return;
    sql_query("UPDATE avps SET value_u=$now WHERE arg='lastcleantime' AND value_u = $ts");
    if (!mysql_affected_rows())
        return;
    docleanup();
}

function flood_limit($table) {
global $CURUSER,$TBDEV,$lang;
	if(!file_exists($TBDEV['flood_file']) || !is_array($max = unserialize(file_get_contents($TBDEV['flood_file']))))
		return;
	if(!isset($max[$CURUSER['class']]))
	return;
	$tb = array('posts'=>'posts.userid','comments'=>'comments.user','messages'=>'messages.sender');
	$q = sql_query('SELECT min('.$table.'.added) as first_post, count('.$table.'.id) as how_many FROM '.$table.' WHERE '.$tb[$table].' = '.$CURUSER['id'].' AND '.time().' - '.$table.'.added < '.$TBDEV['flood_time']);
	$a = mysql_fetch_assoc($q);
	if($a['how_many'] > $max[$CURUSER['class']])
        stderr($lang['gl_sorry'] ,$lang['gl_flood_msg'].''.mkprettytime($TBDEV['flood_time'] - (time() - $a['first_post'])));
}

function CutName ($txt, $len){
$len = 30;
return (strlen($txt)>$len ? substr($txt,0,$len-1) .'...':$txt);
}

function unesc($x) {
    if (get_magic_quotes_gpc())
        return stripslashes($x);
    return $x;
}

function mksize($bytes)
{
	if ($bytes < 1000 * 1024)
		return number_format($bytes / 1024, 2) . " kB";
	elseif ($bytes < 1000 * 1048576)
		return number_format($bytes / 1048576, 2) . " MB";
	elseif ($bytes < 1000 * 1073741824)
		return number_format($bytes / 1073741824, 2) . " GB";
	else
		return number_format($bytes / 1099511627776, 2) . " TB";
}

/*function mksizeint($bytes)
{
	$bytes = max(0, $bytes);
	if ($bytes < 1000)
		return floor($bytes) . " B";
	elseif ($bytes < 1000 * 1024)
		return floor($bytes / 1024) . " kB";
	elseif ($bytes < 1000 * 1048576)
		return floor($bytes / 1048576) . " MB";
	elseif ($bytes < 1000 * 1073741824)
		return floor($bytes / 1073741824) . " GB";
	else
		return floor($bytes / 1099511627776) . " TB";
}*/

function mkprettytime($s) {
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        }
        else
            $v = $s;
        $t[$y[1]] = $v;
    }
    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
//    if ($t["min"])
        return sprintf("%d:%02d", $t["min"], $t["sec"]);
//    return $t["sec"] . " secs";
}

function mkglobal($vars) {
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = unesc($_GET[$v]);
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = unesc($_POST[$v]);
        else
            return 0;
    }
    return 1;
}

function validfilename($name) {
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email) {
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function sqlesc($x) {
    return "'".mysql_real_escape_string($x)."'";
}

function sqlwildcardesc($x) {
    return str_replace(array("%","_"), array("\\%","\\_"), mysql_real_escape_string($x));
}

function stdhead($title = "", $msgalert = true,$staffmsgalert = true) {
    global $CURUSER, $TBDEV, $lang, $free;

    if (!$TBDEV['site_online'])
      die("Site is down for maintenance, please check back again later... thanks<br />");
    
    /*//++++++++++++++++++++++++++++++++++
    //******** ??d site close *********
    //++++++++++++++++++++++++++++++++++
    $res = sql_query("SELECT * FROM siteonline") or sqlerr(__FILE__, __LINE__);//???????? ???????? ?? ????
    $row = mysql_fetch_array($res);
    
    //Variables for the notification of the closure of the site (see stdhead.php)
    if ($row["onoff"] !=1){
        $my_siteoff = 1;
        $my_siteopenfor = $row['class_name'];
    }
    ////========================================================================================//
    //$row["onoff"] = 1;
    //EMERGENCY SIGN: uncomment if you can not enter!//
    //======================================================================================//
    if (($row["onoff"] !=1) && (!$CURUSER)){ //Check: whether the site is closed and if the guest:
        die("<title>Site CLOSED!</title>
        <table width='100%' height='100%' style='border: 8px ridge #FF0000'><tr><td align='center'>
        <h1 style='color: #CC3300;'>".$row['reason']."</h1>
        <h1 style='color: #CC3300;'>Please, try later...</h1>
        <br><center><form method='post' action='takesiteofflogin.php'>
        <table border='1' cellspacing='1' id='table1' cellpadding='3' style='border-collapse: collapse'>
        <tr><td colspan='2' align='center' bgcolor='#CC3300'>
        <font color='#FFFFFF'><b>For Users:</b></font></td></tr>
        <tr><td><b>Name:</b></td><td>
        <input type='text' size=20 name='username'></td></tr>
        <tr><td><b>Password:</b></td>
        <td><input type='password' size=20 name='password'></td></tr>
        <tr><td colspan='2' align='center'><input type='submit' value='Gooo!'></td>
        </tr></table>
        </form></center>
        </td></tr></table>");
        }
        elseif (($row["onoff"] !=1) and (($CURUSER["class"] < $row["class"]) && ($CURUSER["id"] != 1))){ //Check: whether the site is closed, the class of user is less
        //than a allowable and whether you're an admin (ID = 1)
        die("<title>Site CLOSED!</title>
        <table width='100%' height='100%' style='border: 8px ridge #FF0000'><tr><td align='center'>
        <h1 style='color: #CC3300;'>".$row['reason']."</h1>
        <h1 style='color: #CC3300;'>Please, try later...</h1>
        </td></tr></table>");
        }
        //++++++++++++++++++++++++++++++++++
        //******** ??d site close *********
        //++++++++++++++++++++++++++++++++++*/

    //header("Content-Type: text/html; charset=iso-8859-1");
    //header("Pragma: No-cache");
    if ($title == "")
        $title = $TBDEV['site_name'] .(isset($_GET['tbv'])?" (".TBVERSION.")":'');
    else
        $title = $TBDEV['site_name'].(isset($_GET['tbv'])?" (".TBVERSION.")":''). " :: " . htmlspecialchars($title);
        
    if ($CURUSER) {
    /*
    $ss_a = @mysql_fetch_array(@sql_query("select uri from stylesheets where id=" . $CURUSER["stylesheet"]));

    if ($ss_a) $ss_uri = $ss_a["uri"];
    */
      $TBDEV['stylesheet'] = isset($CURUSER['stylesheet']) ? "{$CURUSER['stylesheet']}.css" : $TBDEV['stylesheet'];
    }
  
    if ($TBDEV['msg_alert'] && $msgalert && $CURUSER) {
      $res = sql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " && unread='yes'") or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_row($res);
      $unread = $arr[0];
    }
    if ($TBDEV['staffmsg_alert'] && $staffmsgalert && $CURUSER) {
	   $rese = sql_query("SELECT COUNT(*) as nummessages FROM staffmessages WHERE answered='0'") or sqlerr(__FILE__,__LINE__);
       $arre = mysql_fetch_row($rese);
       $nummessages = $arre[0];
	}

    $htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>

			<meta name='generator' content='BitsB' />
			<meta http-equiv='Content-Language' content='en-us' />
            
            <!-- ####################################################### -->
            <!-- #   This website is powered by BitsB source     # -->
            <!-- #   Download and support our code # -->
            <!-- #   By modifying & distributing it for free # -->
            <!-- ####################################################### -->
			
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
			<meta name='MSSmartTagsPreventParsing' content='TRUE' />
			
			<title>{$title}</title>
			<link rel='stylesheet' href='{$TBDEV['stylesheet']}' type='text/css' />
            <link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='{$TBDEV['baseurl']}/rss.php?passkey={$CURUSER['passkey']}' />
            <link rel='SHORTCUT ICON' href='favicon.ico' />
            <script type='text/javascript' src='scripts/java_klappe.js'></script>
            <script type='text/javascript' src='scripts/jquery-1.4.3.min.js'></script>
            <script type='text/javascript 'src='scripts/colorfade.js'></script>
            <script type='text/javascript' src='scripts/keyboard.js' charset='UTF-8'></script>
            <link rel='stylesheet' type='text/css' href='keyboard.css' />
            <link rel='stylesheet' type='text/css' href='image-resize/resize.css' />
            <script type='text/javascript' src='image-resize/jquery.js'></script>
            <script type='text/javascript' src='image-resize/core-resize.js'></script>
            <script type='text/javascript' src='scripts/bookmark.js'></script>
            <script type='text/javascript' src='scripts/script.js'></script>
            <script type='text/javascript' src='scripts/logout_preventation.js'></script>
            <script type='text/javascript' src='scripts/messagepop.js'></script>
            <script type='text/javascript' src='scripts/tooltips.js'></script>
		</head>
    
    <div class='base_around'><div class='base_content'>
    <body>
      <table width='100%' cellspacing='0' cellpadding='0' border ='0' style='background: transparent'>
      <tr>
      <td class='clear'>
      <div id='base_header_line'></div>
      <div id='base_header'>
      <script type='text/javascript'>
      //<![CDATA[
        function showSlidingDiv(){
        $('#slidingDiv').animate({'height': 'toggle'}, { duration: 1000 });
        }
      //]]>
       </script>";
       if ($CURUSER) {
       $upped = mksize($CURUSER['uploaded']);
       $downed = mksize($CURUSER['downloaded']);
	   $res2 = @sql_query("SELECT seeder, COUNT(*) AS pCount FROM peers WHERE userid=".$CURUSER['id']." GROUP BY seeder") or sqlerr(__LINE__,__FILE__);
	
	$seedleech = array('yes' => '1', 'no' => '0');
	
	while( $row = mysql_fetch_assoc($res2) ) {
		if($row['seeder'] == 'yes')
			$seedleech['yes'] = $row['pCount'];
		else
			$seedleech['no'] = $row['pCount'];	
	}
    //connectible stats for stdhead by d6bmg
    $q = sql_query('SELECT connectable FROM peers WHERE userid = '.$CURUSER['id'].' LIMIT 1') or sqlerr(); 
    if($a = mysql_fetch_row($q)){ 
        $connect = $a[0]; 
        if($connect == "yes") { 
            $connectable = "<b><font color='green'><a title='Connectable = Yes'>Yes</a></font></b>"; 
        }else { 
            $connectable = "<b><font color='red'><a title='Connectable = No'>No</a></font></b>"; 
        } 
    }else{ 
        $connectable = "<b><font color='blue'><a title='Connectable = N/A'>N/A</a></font></b>"; 
    } 
    //end of connectible stats

    //Max leeching slots
    if ($CURUSER['class'] < UC_VIP && $TBDEV['max_slots']) { 
        $ratioq = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 1); 
   
        if ($ratioq < 0.95) { 
            switch (true) { 
                case ($ratioq < 0.5): 
                $max = 2; 
                break; 
                case ($ratioq < 0.65): 
                $max = 3; 
                break; 
                case ($ratioq < 0.8): 
                $max = 5; 
                break; 
                case ($ratioq < 0.95): 
                $max = 10; 
                break; 
                default: 
           $max = 10; 
            } 
    } 
    else { 
        switch ($CURUSER['class']) { 
                case UC_USER: 
                $max = 10; 
                break; 
                case UC_POWER_USER: 
                $max = 20; 
                break; 
                default: 
                $max = 99; 
            }        
        }    
    } 
    else 
        $max = 99;
    //End of max leeching slots 
      $htmlout .="<div id='base_header_fly'>
        <div id='base_usermenu' style='font-family:trebuchet MS;'>
        {$lang['gl_msg_welcome']},
        <span style='white-space: nowrap;'><a href='userdetails.php?id={$CURUSER['id']}' class='user_99' target='_blank'>
        <span style='color: rgb(64, 128, 176);'><b>{$CURUSER['username']}</b></span> </a></span></font>
        <span class='base_usermenu_arrow'><a onclick='showSlidingDiv(); return false;' href='#'>
        <img alt='' src='pic/usermenu_arrow.png'></a></span>
        </div>
        <div id='slidingDiv' style='display: none; font-family:trebuchet MS;'>
            <div class='slide_head'>:: Personal Stats</div>
                <div class='slide_a'>User Class</div><div class='slide_b'>&nbsp;<b><font color=#".get_user_class_color(get_user_class()).">".get_user_class_name(get_user_class())."</font></b>&nbsp;</div>
                <div class='slide_c'>Invites</div><div class='slide_d'><a href='invite.php'><font color=#ffffff title='Invites = {$CURUSER['invites']}'>{$CURUSER['invites']}</font></a></div>
                <div class='slide_a'>Bonus Points</div><div class='slide_b'><a href='mybonus.php'><font color=#F7A919 title='Bonus Points = {$CURUSER['seedbonus']}'>{$CURUSER['seedbonus']}</font></a></div>
                <div class='slide_c'>{$lang['gl_bkmrks']}</div><div class='slide_d'><a href='bookmarks.php' style='color: #DBE3EA;' title='{$lang['gl_bkmrks']}'>Click Here</a></div>
                <div class='slide_a'>Friends</div><div class='slide_b'><a href='friends.php' style='color: #DBE3EA;' title='Friends'>Click Here</a></div>
            <div class='slide_head'>:: Torrent Stats</div>
                <div class='slide_a'>Share Ratio</div><div class='slide_b'><span style='color: rgb(0, 255, 0);'>".member_ratio($CURUSER['uploaded'],$CURUSER['downloaded'])."</span></div>
                <div class='slide_c'>Uploaded</div><div class='slide_d'><font color=#06f106 title='Uploaded = $upped'>$upped</font></div>
                <div class='slide_a'>Downloaded</div><div class='slide_b'><font color=#fc1010 title='Downloaded = $downed'>$downed</font></div>
                <div class='slide_c'>Uploading Files</div><div class='slide_d'>{$seedleech['yes']}</div>
                <div class='slide_a'>Downloading Files</div><div class='slide_b'>{$seedleech['no']}/$max</div>
                <div class='slide_c'>Connectable</div><div class='slide_d'><a style='color: #FFFFFF;'>$connectable</a></div>
            <div class='slide_head'>:: Games &amp; Playhouse</div>
                <div class='slide_a'>Play Blackjack</div><div class='slide_b'><a href='blackjack.php' style='color: #DBE3EA;'>Play here</a></div>
                <div class='slide_c'>Play Casino</div><div class='slide_d'><a href='casino.php' style='color: #DBE3EA;'>Play here</a></div>
            <div class='slide_head'>:: Information</div>
                <div class='slide_a'>Contact Staff</div><div class='slide_b'><a href='contactstaff.php' style='color: #DBE3EA;' title='Send Staff-Message'>Send Message</a></div>
                <div class='slide_c'>RSS Feed</div><div class='slide_d'><a href='rss.php' style='color: #DBE3EA;'>Click here</a></div>
                <div class='slide_a'>Bookmark Us!</div><div class='slide_b'><a href=\"javascript: bookmarksite('BitsB', '{$TBDEV['baseurl']}')\" style='color: #DBE3EA;'>Click Here</a></div>
                <div class='slide_c'>Donate us</div><div class='slide_d'><a href='donate.php' style='color: #DBE3EA;'>Click here</a></div>
        </div>
        <div id='base_icons'>
            <ul class='um_menu'>
            <li><a href='bugs.php'><img src='{$TBDEV['pic_base_url']}bug_report.png' alt='{$lang['gl_bug']}' title='{$lang['gl_bug']}'/></a></li>
            <li><a href='messages.php'><img src='{$TBDEV['pic_base_url']}pm.jpg' alt='Messages' title='Your Private Messages'/></a></li>
            <li><a href='my.php'><img src='{$TBDEV['pic_base_url']}usercp.jpg' alt='Settings' title='Personal Settings'/></a></li>";
        if( $CURUSER['class'] >= UC_MODERATOR )
            $htmlout .= "<li><a href='staffpanel.php'><img src='{$TBDEV['pic_base_url']}staffpanel.png' alt='CPanel' title='CPanel'/></a></li>";
        $htmlout .= "<li><a href='logout.php' onclick='return log_out()'><img src='{$TBDEV['pic_base_url']}signout.jpg' alt='SignOut' title='SignOut'/></a></li>
        </ul>
        </div>
      </div>";
      //== 09 Cached Donation progress - d6bmg
    $cache_funds = "./cache/funds.txt"; 
    $cache_funds_life = 1 * 60 * 60; // Hourly 
    if (file_exists($cache_funds) && is_array(unserialize(file_get_contents($cache_funds))) && (time() - filemtime($cache_funds)) < $cache_funds_life) 
    $row = unserialize(@file_get_contents($cache_funds)); 
    else { 
    $funds = mysql_query("SELECT sum(cash) as total_funds FROM funds") or sqlerr(__FILE__, __LINE__); 
    $row = mysql_fetch_assoc($funds); 
    $handle = fopen($cache_funds, "w+"); 
    fwrite($handle, serialize($row)); 
    fclose($handle); 
    } 
    $funds_so_far = $row["total_funds"]; 
    $totalneeded = $TBDEV['funds']; 
    $funds_difference = $totalneeded - $funds_so_far; 
    $Progress_so_far = number_format($funds_so_far / $totalneeded * 100, 1); 
    if($Progress_so_far >= 100) 
    $Progress_so_far = 100; 
    $Progress_so_far = round($Progress_so_far, 0); //round it 
        if ($Progress_so_far >= 0 && $Progress_so_far < 5) $progress = "0"; 
        if ($Progress_so_far >= 5 && $Progress_so_far < 10) $progress = "5";
        if ($Progress_so_far >= 10 && $Progress_so_far < 15) $progress = "10"; 
        if ($Progress_so_far >= 15 && $Progress_so_far < 20) $progress = "15";
        if ($Progress_so_far >= 20 && $Progress_so_far < 25) $progress = "20"; 
        if ($Progress_so_far >= 25 && $Progress_so_far < 30) $progress = "25";
        if ($Progress_so_far >= 30 && $Progress_so_far < 35) $progress = "30"; 
        if ($Progress_so_far >= 35 && $Progress_so_far < 40) $progress = "35"; 
        if ($Progress_so_far >= 40 && $Progress_so_far < 45) $progress = "40"; 
        if ($Progress_so_far >= 45 && $Progress_so_far < 50) $progress = "45";
        if ($Progress_so_far >= 50 && $Progress_so_far < 55) $progress = "50"; 
        if ($Progress_so_far >= 55 && $Progress_so_far < 60) $progress = "55";
        if ($Progress_so_far >= 60 && $Progress_so_far < 65) $progress = "60"; 
        if ($Progress_so_far >= 65 && $Progress_so_far < 70) $progress = "65";
        if ($Progress_so_far >= 70 && $Progress_so_far < 75) $progress = "70"; 
        if ($Progress_so_far >= 75 && $Progress_so_far < 80) $progress = "75"; 
        if ($Progress_so_far >= 80 && $Progress_so_far < 85) $progress = "80"; 
        if ($Progress_so_far >= 85 && $Progress_so_far < 90) $progress = "85";
        if ($Progress_so_far >= 90 && $Progress_so_far < 95) $progress = "90"; 
        if ($Progress_so_far >= 95 && $Progress_so_far < 100) $progress = "95"; 
        if ($Progress_so_far ==100) $progress = "100"; 
                                                 
    //end catched donation
    }
      $htmlout .="<div id='base_logo'>
      <img src='{$TBDEV['pic_base_url']}logo3.png' alt='' />
      </div>";
      
      if ($CURUSER){
      $htmlout .="<div id='base_donate' style='margin-right: 10px;'><a href='donate.php'><img src='{$TBDEV['pic_base_url']}donate.png' 
        style='opacity:0.4;'
        onmouseover='this.style.opacity=1;'
        onmouseout='this.style.opacity=0.5;' border='0' alt='' title='Donate to keep this site running'  /></a><br />
      
        <div width='133' align='center' valign='middle' height='28' style='background-image: url(pic/bars/bar_{$progress}.png); background-repeat: no-repeat; background-position:center; margin-right: -1px; margin-top: 3px; ><font class='small3' color='white' title='$Progress_so_far%' style='font-size:6pt;'><b>$Progress_so_far%</b></font></div> 
  
  </div>";

      }
      $htmlout .="  
      <!--<a href='donate.php'><img src='{$TBDEV['pic_base_url']}x-click-but04.gif' border='0' alt='{$lang['gl_donate']}' title='{$lang['gl_donate']}'  /></a>-->
      </div>
      </td>

      </tr></table>

      <table class='mainouter' width='100%' border='0' cellspacing='0' cellpadding='0'>";

    $htmlout .= "<!-- MENU -->
      <tr>
      <div id='base_menu'>";

    if ($CURUSER) 
    { 
      $htmlout .= "<div id='mover'><ul class='navigation'>
      <li><span class='nav'><a href='index.php'>{$lang['gl_home']}</a></span></li>
      <li><span class='nav'><a href='browse.php'>{$lang['gl_browse']}</a></span></li>
      <li><span class='nav'><a href='viewrequests.php'>{$lang['gl_req']}</a></span></li>
      <li><span class='nav'><a href='upload.php'>{$lang['gl_upload']}</a></span></li>
      <li><span class='nav'><a href='forums.php'>{$lang['gl_forums']}</a></span></li>
      <!--<li><span class='nav'><a href='my.php'>{$lang['gl_profile']}</a></span></li>-->
      <!--<a href='misc/dox.php'>DOX</a>-->
      <li><span class='nav'><a href='topten.php'>{$lang['gl_top_10']}</a></span></li>
      <li><span class='nav'><a href='rules.php'>{$lang['gl_rules']}</a></span></li>
      <li><span class='nav'><a href='faq.php'>{$lang['gl_faq']}</a></span></li>
      <li><span class='nav'><a href='staff.php'>{$lang['gl_staff']}</a></span></li>
      
      <!--<li><span class='nav'><a href=\"javascript: bookmarksite('BitsB', '{$TBDEV['baseurl']}')\">BM</a></span></li>-->";

      if( $CURUSER['class'] >= UC_SYSOP )
      {
        $htmlout .= "<li><span class='nav'><a href='admin.php'>{$lang['gl_admin']}</a></span></li>";
      }

    $htmlout .= "
      </ul></div>";
    } 
    else
    {
      $htmlout .= "<div id='mover'><ul class='navigation'>
      <li><span class='nav'><a href='login.php'>{$lang['gl_login']}</a></span></li>
      <li><span class='nav'><a href='signup.php'>{$lang['gl_signup']}</a></span></li>
      <li><span class='nav'><a href='recover.php'>{$lang['gl_recover']}</a></span></li>
      <li><span class='nav'><a href='resetpw.php'>{$lang['gl_rstpass']}</a></span></li>
      </ul></div>";
    }

    $htmlout .= "</div>
    </tr>
    <!-- STATUSBAR -->";

    //$htmlout .= StatusBar();
    if ($CURUSER){
    $htmlout .="<tr class='base_searchbars' style='border-bottom: 1px solid #9BABBC;'><td class='embedded' align='center'><ul class='search'>
    <li><form method='get' action='browse.php'>
					<input type='text' size='25' name='search' value='Torrents' onblur=\"if (this.value == '') this.value='Torrents';\" onfocus=\"if (this.value == 'Torrents') this.value='';\">
					<input type='hidden' name='submit' value='Search'></form></li>
    <li>
    <form method='get' action='viewrequests.php'>
					<input type='text' size='25' name='search' value='Requests' onblur=\"if (this.value == '') this.value='Requests';\" onfocus=\"if (this.value == 'Requests') this.value='';\">
					<input type='hidden' name='submit' value='Search'>
				</form></li>
    <li>
    <form method='get' action='forums.php'>
					<input type='hidden' name='action' value='search'>
					<input type='text' size='25' name='keywords' value='Forums' onblur=\"if (this.value == '') this.value='Forums';\" onfocus=\"if (this.value == 'Forums') this.value='';\">
					<input type='hidden' name='submit' value='Search'>
				</form></li>
    <li>
    <form method='get' action='users.php'>
					
					<input type='text' size='25' name='search' value='Users' onblur=\"if (this.value == '') this.value='Users';\" onfocus=\"if (this.value == 'Users') this.value='';\">
					<input type='hidden' name='submit' value='Search'>
				</form></li>    
    </ul></td>";
    $htmlout .="</tr>";
    }
    $htmlout .="<tr><td align='center' class='outer' style='padding-top: 20px; padding-bottom: 20px'>";


    //=== free addon start
if ($CURUSER) { 
if (isset($free)) {
  foreach ($free as $fl) {
        switch ($fl['modifier']) {
            case 1:
                $mode = 'All Torrents Free';
                break;

            case 2:
                $mode = 'All Double Upload';
                break;

            case 3:
                $mode = 'All Torrents Free and Double Upload';
                break;

            default:
                $mode = 0;
        }
        
$htmlout .= ($fl['modifier'] != 0 && ($fl['expires'] > TIME_NOW || $fl['expires'] == 1) ? '<table width="50%"><tr>
     <td class="colhead" colspan="3" align="center">'.$fl['title'].'<br />'.$mode.'</td>
   </tr>
   <tr>
     <td width="42" align="center">
     <img src="'.$TBDEV['baseurl'].'/pic/cat_free.gif" alt="FREE!" /></td>
     <td align="center">'.$fl['message'].' set by '.$fl['setby'].'<br />'.($fl['expires'] != 1 ? 
'Until '.get_date($fl['expires'], 'DATE').' ('.mkprettytime($fl['expires'] - TIME_NOW).' to go)' : '').'</td>
     <td width="42" align="center">
     <img src="'.$TBDEV['baseurl'].'/pic/cat_free.gif" alt="FREE!" /></td>
</tr></table>
<br />' : '');
}
}
}
//=== free addon end
    
    //Message alert
    switch ($CURUSER['pmstyle']) { 
        case "clasic": 
    if ($TBDEV['msg_alert'] && isset($unread) && !empty($unread))
    {
        $htmlout .="<div class='notification message2'><span></span>
         <div class='text'><p><strong>Message!</strong>".sprintf($lang['gl_msg_alert'], $unread) . ($unread > 1 ? "s" : "") . "! Please read them to close this box autometically
                  or close this box manually.</p>
         </div>
        </div>";
    }
    break; 
        case "popup": 
        //Start Of Pop-Up Message System  
        if ($TBDEV['msg_alert'] && isset($unread) && !empty($unread)) 
        { 
                define('IN_TBDEV_POPUP_SYSTEM', TRUE);
                require_once("popup_msg.php"); 
        } 
        break; 
        } 
//================================//
    //Staff message alart
    if ($TBDEV['staffmsg_alert'] && isset($nummessages) && !empty($nummessages))
	{
	   if ($CURUSER['class'] >= UC_MODERATOR)
       {
        $htmlout .="<div class='notification secure'><span></span>
         <div class='text'><p><strong>Staff Message!</strong>".sprintf($lang['gl_staffmsg_alert'], $nummessages) . ($nummessages > 1 ? "s" : "") . "! Please solve them or leave them for another staff member
                  or close this box manually.</p>
         </div>
        </div>";
       }
    }
    //end staff message alart
    
    //report alert:
    if($TBDEV['report_alert'] && $CURUSER['class'] >= UC_MODERATOR) {
		$num = mysql_result(sql_query('SELECT COUNT(id) FROM reports WHERE delt_with = 0'),0);
		if($num > 0)
		$htmlout .="<div class='notification warning'><span></span>
         <div class='text'><p><strong>Report!</strong>".sprintf($lang['gl_reportmsg_alert'],  $num) . "! Please solve them or leave them for another staff member
                  or close this box manually.</p>
         </div>
        </div>";
	}
	//==End
    
    //bugs alert
    $bugs = mysql_fetch_row(sql_query("SELECT COUNT(*) FROM bugs WHERE status = 'na'")) or sqlerr(__FILE__, __LINE__);
	if ($CURUSER['class'] >= UC_SYSOP && $bugs[0]) //Change UC_SYSOP to the userclass you want :]
	{
	$htmlout .="<div class='notification info'><span></span>
         <div class='text'><p><strong>Site Bug!</strong>".sprintf($lang['gl_bugs'],  $bugs[0]) . ($bugs[0] > 1 ? "s" : "") . "! Please solve them or leave them for another staff member or coder
                  or close this box manually.</p>
         </div>
        </div>";
	}
    //end

    return $htmlout;
    
} // stdhead

function genbark($x,$y) {
    stdhead($y);
    print("<h2>" . htmlspecialchars($y) . "</h2>\n");
    print("<p>" . htmlspecialchars($x) . "</p>\n");
    stdfoot();
    exit();
}
/*
function mksecret()
{
   $ret = substr(md5(uniqid(mt_rand())), 0, 20);
   return $ret;
}
*/

function httperr($code = 404) {
    header("HTTP/1.0 404 Not found");
    print("<h1>Not Found</h1>\n");
    print("<p>Sorry pal :(</p>\n");
    exit();
}

function hashit($var,$addtext="") 
{ 
return md5("Th15T3xt".$addtext.$var.$addtext."is5add3dto66uddy6he@water..."); 
}

/*
function gmtime()
{
    return strtotime(get_date_time());
}
*/

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff) 
{ 
    set_mycookie( "uid", $id, $expires ); 
    set_mycookie( "pass", $passhash, $expires ); 
    set_mycookie( "hashv", hashit($id,$passhash), $expires ); 
    if ($updatedb) 
      @mysql_query("UPDATE users SET last_login = ".TIME_NOW." WHERE id = $id"); 
}

/*function logincookie($id, $passhash, $expires = 0x7fffffff)
{
	if ($expires != 0x7fffffff)
	{
 $expires = time() + 900;
	}
	set_mycookie("uid", $id, $expires);
	set_mycookie("pass", $passhash, $expires);
 // if ($updatedb)
 sql_query("UPDATE users SET last_login = ".TIME_NOW." WHERE id = $id");
}*/         //decrypted

function set_mycookie( $name, $value="", $expires_in=0, $sticky=1 )
    {
		global $TBDEV;
		
		if ( $sticky == 1 )
    {
      $expires = time() + 60*60*24*365;
    }
		else if ( $expires_in )
		{
			$expires = time() + ( $expires_in * 86400 );
		}
		else
		{
			$expires = FALSE;
		}
		
		$TBDEV['cookie_domain'] = $TBDEV['cookie_domain'] == "" ? ""  : $TBDEV['cookie_domain'];
    $TBDEV['cookie_path']   = $TBDEV['cookie_path']   == "" ? "/" : $TBDEV['cookie_path'];
      	
		if ( PHP_VERSION < 5.2 )
		{
      if ( $TBDEV['cookie_domain'] )
      {
        @setcookie( $TBDEV['cookie_prefix'].$name, $value, $expires, $TBDEV['cookie_path'], $TBDEV['cookie_domain'] . '; HttpOnly' );
      }
      else
      {
        @setcookie( $TBDEV['cookie_prefix'].$name, $value, $expires, $TBDEV['cookie_path'] );
      }
    }
    else
    {
      @setcookie( $TBDEV['cookie_prefix'].$name, $value, $expires, $TBDEV['cookie_path'], $TBDEV['cookie_domain'], NULL, TRUE );
    }
			
}
function get_mycookie($name) 
    {
      global $TBDEV;
      
    	if ( isset($_COOKIE[$TBDEV['cookie_prefix'].$name]) AND !empty($_COOKIE[$TBDEV['cookie_prefix'].$name]) )
    	{
    		return urldecode($_COOKIE[$TBDEV['cookie_prefix'].$name]);
    	}
    	else
    	{
    		return FALSE;
    	}
}

function logoutcookie() { 
    set_mycookie('uid', '-1'); 
    set_mycookie('pass', '-1'); 
    set_mycookie('hashv', '-1'); 
}

function loggedinorreturn() {
    global $CURUSER, $TBDEV;
    if (!$CURUSER) {
        header("Location: {$TBDEV['baseurl']}/login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    }
}


function searchfield($s) {
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist() {
    $ret = array();
    $res = sql_query("SELECT id, name, image FROM categories ORDER BY name");
    while ($row = mysql_fetch_array($res))
        $ret[] = $row;
    return $ret;
}


function get_row_count($table, $suffix = "")
{
  if ($suffix)
    $suffix = " $suffix";
  ($r = sql_query("SELECT COUNT(*) FROM $table$suffix")) or die(mysql_error());
  ($a = mysql_fetch_row($r)) or die(mysql_error());
  return $a[0];
}

function stdmsg($heading, $text)
{
    $htmlout = "<table class='main' width='750' border='0' cellpadding='0' cellspacing='0'>
    <tr><td class='embedded'>\n";
    
    if ($heading)
      $htmlout .= "<h2>$heading</h2>\n";
    
    $htmlout .= "<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>\n";
    $htmlout .= "{$text}</td></tr></table></td></tr></table>\n";
  
    return $htmlout;
}

function stderr($heading, $text)
{
    $htmlout = stdhead();
    $htmlout .= stdmsg($heading, $text);
    $htmlout .= stdfoot();
    
    print $htmlout;
    exit();
}
	
// Basic MySQL error handler

function sqlerr($file = '', $line = '') {
    global $TBDEV, $CURUSER;
    
		$the_error    = mysql_error();
		$the_error_no = mysql_errno();

    	if ( SQL_DEBUG == 0 )
    	{
			exit();
    	}
     	else if ( $TBDEV['sql_error_log'] AND SQL_DEBUG == 1 )
		{
			$_error_string  = "\n===================================================";
			$_error_string .= "\n Date: ". date( 'r' );
			$_error_string .= "\n Error Number: " . $the_error_no;
			$_error_string .= "\n Error: " . $the_error;
			$_error_string .= "\n IP Address: " . $_SERVER['REMOTE_ADDR'];
			$_error_string .= "\n in file ".$file." on line ".$line;
			$_error_string .= "\n URL:".$_SERVER['REQUEST_URI'];
			$_error_string .= "\n Username: {$CURUSER['username']}[{$CURUSER['id']}]";
			
			if ( $FH = @fopen( $TBDEV['sql_error_log'], 'a' ) )
			{
				@fwrite( $FH, $_error_string );
				@fclose( $FH );
			}
			
			print "<html><head><title>MySQL Error</title>
					<style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
		    		   <blockquote><h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
		    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>
				  </body></html>";
		}
		else
		{
    		$the_error = "\nSQL error: ".$the_error."\n";
	    	$the_error .= "SQL error code: ".$the_error_no."\n";
	    	$the_error .= "Date: ".date("l dS \of F Y h:i:s A");
    	
	    	$out = "<html>\n<head>\n<title>MySQL Error</title>\n
	    		   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style>\n</head>\n<body>\n
	    		   <blockquote>\n<h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
	    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
	    		   <br /><br /><b>Error Returned</b><br />
	    		   <form name='mysql'><textarea rows=\"15\" cols=\"60\">".htmlentities($the_error, ENT_QUOTES)."</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>";
    		   
    
	       	print $out;
		}
		
        exit();
}
    
/*    
// Returns the current time in GMT in MySQL compatible format.
function get_date_time($timestamp = 0)
{
  if ($timestamp)
    return date("Y-m-d H:i:s", $timestamp);
  else
    return gmdate("Y-m-d H:i:s");
}
*/

function get_dt_num()
{
  return gmdate("YmdHis");
}



function write_log($text)
{
  $text = sqlesc($text);
  $added = TIME_NOW;
  sql_query("INSERT INTO sitelog (added, txt) VALUES($added, $text)") or sqlerr(__FILE__, __LINE__);
}


function sql_timestamp_to_unix_timestamp($s)
{
  return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}


function get_elapsed_time($ts)
{
  $mins = floor((gmtime() - $ts) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  $days = floor($hours / 24);
  $hours -= $days * 24;
  $weeks = floor($days / 7);
  $days -= $weeks * 7;
//  $t = "";
  if ($weeks > 0)
    return "$weeks week" . ($weeks > 1 ? "s" : "");
  if ($days > 0)
    return "$days day" . ($days > 1 ? "s" : "");
  if ($hours > 0)
    return "$hours hour" . ($hours > 1 ? "s" : "");
  if ($mins > 0)
    return "$mins min" . ($mins > 1 ? "s" : "");
  return "< 1 min";
}


if (!function_exists("stripos")) {
 function stripos($str,$needle,$offset=0)
 {
 return strpos(strtolower($str),strtolower($needle),$offset);
 }
}


function unixstamp_to_human( $unix=0 )
    {
    	$offset = get_time_offset();
    	$tmp    = gmdate( 'j,n,Y,G,i', $unix + $offset );
    	
    	list( $day, $month, $year, $hour, $min ) = explode( ',', $tmp );
  
    	return array( 'day'    => $day,
                    'month'  => $month,
                    'year'   => $year,
                    'hour'   => $hour,
                    'minute' => $min );
    }
    


function get_time_offset() {
    
    	global $CURUSER, $TBDEV;
    	$r = 0;
    	
    	$r = ( ($CURUSER['time_offset'] != "") ? $CURUSER['time_offset'] : $TBDEV['time_offset'] ) * 3600;
			
      if ( $TBDEV['time_adjust'] )
      {
        $r += ($TBDEV['time_adjust'] * 60);
      }
      
      if ( $CURUSER['dst_in_use'] )
      {
        $r += 3600;
      }
        
        return $r;
}
    

function get_date($date, $method, $norelative=0, $full_relative=0)
    {
        global $TBDEV;
        
        static $offset_set = 0;
        static $today_time = 0;
        static $yesterday_time = 0;
        $time_options = array( 
        'JOINED' => $TBDEV['time_joined'],
        'SHORT'  => $TBDEV['time_short'],
				'LONG'   => $TBDEV['time_long'],
				'TINY'   => $TBDEV['time_tiny'] ? $TBDEV['time_tiny'] : 'j M Y - G:i',
				'DATE'   => $TBDEV['time_date'] ? $TBDEV['time_date'] : 'j M Y'
				);
        
        if ( ! $date )
        {
            return '--';
        }
        
        if ( empty($method) )
        {
        	$method = 'LONG';
        }
        
        if ($offset_set == 0)
        {
        	$GLOBALS['offset'] = get_time_offset();
			
          if ( $TBDEV['time_use_relative'] )
          {
            $today_time     = gmdate('d,m,Y', ( time() + $GLOBALS['offset']) );
            $yesterday_time = gmdate('d,m,Y', ( (time() - 86400) + $GLOBALS['offset']) );
          }	
        
          $offset_set = 1;
        }
        
        if ( $TBDEV['time_use_relative'] == 3 )
        {
        	$full_relative = 1;
        }
        
        if ( $full_relative and ( $norelative != 1 ) )
        {
          $diff = time() - $date;
          
          if ( $diff < 3600 )
          {
            if ( $diff < 120 )
            {
              return '< 1 minute ago';
            }
            else
            {
              return sprintf( '%s minutes ago', intval($diff / 60) );
            }
          }
          else if ( $diff < 7200 )
          {
            return '< 1 hour ago';
          }
          else if ( $diff < 86400 )
          {
            return sprintf( '%s hours ago', intval($diff / 3600) );
          }
          else if ( $diff < 172800 )
          {
            return '< 1 day ago';
          }
          else if ( $diff < 604800 )
          {
            return sprintf( '%s days ago', intval($diff / 86400) );
          }
          else if ( $diff < 1209600 )
          {
            return '< 1 week ago';
          }
          else if ( $diff < 3024000 )
          {
            return sprintf( '%s weeks ago', intval($diff / 604900) );
          }
          else
          {
            return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
          }
        }
        else if ( $TBDEV['time_use_relative'] and ( $norelative != 1 ) )
        {
          $this_time = gmdate('d,m,Y', ($date + $GLOBALS['offset']) );
          
          if ( $TBDEV['time_use_relative'] == 2 )
          {
            $diff = time() - $date;
          
            if ( $diff < 3600 )
            {
              if ( $diff < 120 )
              {
                return '< 1 minute ago';
              }
              else
              {
                return sprintf( '%s minutes ago', intval($diff / 60) );
              }
            }
          }
          
            if ( $this_time == $today_time )
            {
              return str_replace( '{--}', 'Today', gmdate($TBDEV['time_use_relative_format'], ($date + $GLOBALS['offset']) ) );
            }
            else if  ( $this_time == $yesterday_time )
            {
              return str_replace( '{--}', 'Yesterday', gmdate($TBDEV['time_use_relative_format'], ($date + $GLOBALS['offset']) ) );
            }
            else
            {
              return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
            }
        }
        else
        {
          return gmdate($time_options[$method], ($date + $GLOBALS['offset']) );
        }
}


function hash_pad($hash) {
    return str_pad($hash, 20);
}

function write_info($text)
{
    $text = sqlesc($text);
    $added = sqlesc(time());
    sql_query("INSERT INTO infolog (added, txt) VALUES($added, $text)") or sqlerr(__FILE__, __LINE__);
}

//Decrypted in BitsB
/*function StatusBar() {

	global $CURUSER, $TBDEV, $lang;
	
	if (!$CURUSER)
		return "<tr><td colspan='0'></td></tr>";

	$upped = mksize($CURUSER['uploaded']);	
	$downed = mksize($CURUSER['downloaded']);
	$ratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded']/$CURUSER['downloaded'] : 0;
	$ratio = number_format($ratio, 2);
	$IsDonor = '';
	if ($CURUSER['donor'] == "yes")
	$IsDonor = "<img src='pic/star.gif' alt='donor' title='donor' />";
     $flssupport = '';
        if ($CURUSER["support"] == "yes")
    $flssupport = "<img src='pic/supt.gif' alt='FLS' title='' />";
	$warn = '';
	if ($CURUSER['warned'] == "yes")
	$warn = "<img src='pic/warned.gif' alt='warned' title='warned' />";
    
	$res1 = @sql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND unread='yes'") or sqlerr(__LINE__,__FILE__);
	$arr1 = mysql_fetch_row($res1);
	$unread = $arr1[0];
	//$inbox = ($unread == 1 ? "$unread&nbsp;{$lang['gl_msg_singular']}" : "$unread&nbsp;{$lang['gl_msg_plural']}");
    $inbox = ($unread >= 1 ? "<img src='pic/pn_inboxnew.gif' alt='$unread New Message!!' title='$unread New Message!!' />" : "<img src='pic/pn_inbox.gif' alt='No New Messages' title='No New Messages' />");
    $sntbox ="<img src='pic/pn_sentbox.gif' alt='Sentbox' title='Sentbox' />";
    $res11 = @sql_query("SELECT COUNT(*) FROM staffmessages WHERE answered='0'") or sqlerr(__LINE__,__FILE__);
	$arr11 = mysql_fetch_row($res11);
	$instaff='';
    $shtlist='';
    $reprts='';
	$nummessages = $arr11[0];
	if ($CURUSER['class'] >= UC_MODERATOR){
	$instaff = "<img src='pic/pn_staffbox.gif' alt='Staffbox' title='Staffbox' />";
    $shtlist = "<img src='pic/smilies/shit.gif' alt='Shitlist' title='Shitlist' />";
    $reprts = "<img src='pic/report_box.gif' alt='Reportbox' title='Reportbox' />";
    }
    $frnds = "<img src='pic/buddylist1.png' alt='Friends' title='Friends' />";
    $getrss = "<img src='pic/rss.gif' alt='RSS Feed' title='RSS Feed' />";
	
	$res2 = @sql_query("SELECT seeder, COUNT(*) AS pCount FROM peers WHERE userid=".$CURUSER['id']." GROUP BY seeder") or sqlerr(__LINE__,__FILE__);
	$seedleech = array('yes' => '0', 'no' => '0');
	while( $row = mysql_fetch_assoc($res2) ) {
		if($row['seeder'] == 'yes')
			$seedleech['yes'] = $row['pCount'];
		else
			$seedleech['no'] = $row['pCount'];
	}
	
/////////////// REP SYSTEM /////////////
//$CURUSER['reputation'] = 49;
	$member_reputation = get_reputation($CURUSER, 1);
////////////// REP SYSTEM END //////////

		$res3 = sql_query(
"SELECT ".
	"(SELECT COUNT(peers.id) FROM peers WHERE userid=" . $CURUSER["id"] . " AND seeder='yes') AS activeseed_count, ".
	"(SELECT COUNT(peers.id) FROM peers WHERE userid=" . $CURUSER["id"] . " AND seeder='no') AS activeleech_count, ".
	"(SELECT connectable FROM peers WHERE userid=" . $CURUSER["id"] . " LIMIT 1) AS connectable");
$arr3 = mysql_fetch_assoc($res3);
    $StatusBar = '';
		$StatusBar = "<tr>".
		"<td colspan='0' style='padding: 0px;'>".
		"<div id='statusbar'>".
		"<div style='float:left;color:black;'> <a href='userdetails.php?id={$CURUSER['id']}'>{$CURUSER['username']} </a>".
		" $flssupport$IsDonor$warn&nbsp; <a  href='{$TBDEV['baseurl']}/bookmarks.php' ><img src='pic/bookmarkt.gif' alt='{$lang['gl_bkmrks']}' title='{$lang['gl_bkmrks']}' /></a>&nbsp;".
        	"<img src='pic/bonus.png' alt='My Bonus' title='My Bonus' />&nbsp;<a href='{$TBDEV['baseurl']}/mybonus.php'><font color=#F7A919>{$CURUSER['seedbonus']}</font></a>
		<img src='pic/invite.png' alt='Invite a friend' title='Invite a friend' /> <a href='{$TBDEV['baseurl']}/invite.php'>{$CURUSER['invites']}</a>
		<img src='pic/ratio1.png' alt='Share Ratio' title='Share Ratio' /> $ratio".
		"&nbsp;&nbsp;<img src='pic/arrowup.gif' alt='{$lang['gl_uploaded']}' title='{$lang['gl_uploaded']}' /><font color=#009F00>$upped</font>".
		"&nbsp;&nbsp;{$lang['gl_downloaded']}:<font color=#FE2E2E>$downed</font>".
		"&nbsp;&nbsp;<img src='pic/active1.png' alt='{$lang['gl_act_torrents']}' title='{$lang['gl_act_torrents']}' /> &nbsp;<img alt='{$lang['gl_seed_torrents']}' title='{$lang['gl_seed_torrents']}' src='pic/up.png' />&nbsp;{$seedleech['yes']}".
		"&nbsp;&nbsp;<img alt='{$lang['gl_leech_torrents']}' title='{$lang['gl_leech_torrents']}' src='pic/dl.png' />&nbsp;{$seedleech['no']}&nbsp;</div>".
		"<p style='text-align:right;'>".
    "&nbsp<a href='{$TBDEV['baseurl']}/bugs.php'><img src='pic/bug_report.png' alt='{$lang['gl_bug']}' title='{$lang['gl_bug']}' />".
    "&nbsp<a href='admin.php?action=reports'>$reprts</a>".
    "&nbsp<a href='staffbox.php'>$instaff</a>".
    "&nbsp<a href='messages.php'>$inbox</a>".
    "&nbsp<a href='messages.php?action=viewmailbox&box=-1'>$sntbox</a>".
    "&nbsp<a href='{$TBDEV['baseurl']}/chat.php'><img src='pic/ico_irc.gif' alt='{$lang['gl_chat']}' title='{$lang['gl_chat']}' />".
    "&nbsp<a href='friends.php'>$frnds</a>".
    "&nbsp<a href='shit_list.php'>$shtlist</a>".
    "&nbsp<a href='getrss.php'>$getrss</a>".
    "&nbsp<a href='{$TBDEV['baseurl']}/logout.php'><img src='pic/signout.png' alt='{$lang['gl_logout']}' title='{$lang['gl_logout']}' />&nbsp;</p></div>".
    "</div></td></tr>";
	
	return $StatusBar;
}*/


function load_language($file='') {

    global $TBDEV;
  
    if( !isset($GLOBALS['CURUSER']) OR empty($GLOBALS['CURUSER']['language']) ){
      if( !file_exists(ROOT_PATH."/lang/{$TBDEV['language']}/lang_{$file}.php") ){
        stderr('SYSTEM ERROR', 'Can\'t find language files');
      }
      require_once ROOT_PATH."/lang/{$TBDEV['language']}/lang_{$file}.php";
      return $lang;
    }
    
    if( !file_exists(ROOT_PATH."/lang/{$GLOBALS['CURUSER']['language']}/lang_{$file}.php") ){
      stderr('SYSTEM ERROR', 'Can\'t find language files');
    }
    else{
      require_once ROOT_PATH."/lang/{$GLOBALS['CURUSER']['language']}/lang_{$file}.php"; 
    }
    
    return $lang;
}
//Auto reporting illeagal file access to admin & auto-warn them
function staffonly() {
  global $CURUSER;
$dt = sqlesc(time());
        if(get_user_class() < UC_MODERATOR){
          $takereason= mysql_real_escape_string("Illegal access to $_SERVER[PHP_SELF]");
          $msg = sqlesc("You have been warned by System, because of trying to access staffpages.Please contact a Staff member as soon as possible otherwise we have to think you want to hack us and ban you!!! ");
          sql_query("INSERT into reports (reported_by,reporting_what,reporting_type,reason,added) VALUES ('2',$CURUSER[id],'User', '$takereason', $dt)") or sqlerr();   
          sql_query("UPDATE users SET warned = 'yes', warneduntil = '0000-00-00 00:00:00' WHERE id=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
        @sql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, $CURUSER[id], $dt, $msg, 0)") or sqlerr(__FILE__, __LINE__);
        }
}
//end report illeagal file access

//usename blacklist system
function blacklist($fo) {
	GLOBAL $TBDEV;
        $fo = strtolower($fo);
	$blacklist = file_exists($TBDEV['nameblacklist']) && is_array(unserialize(file_get_contents($TBDEV['nameblacklist']))) ? unserialize(file_get_contents($TBDEV['nameblacklist'])) : array();
	if(isset($blacklist[$fo]) && $blacklist[$fo] == 1)
		return false;
	
	return true;
}

//Colaspable blocking system (cookie based) modified by d6bmg
function begin_block($caption = "", $caption_t, $per, $tdcls, $img = "", $title="", $center = false, $padding = 10)
	{
	
	$htmlout = '';	
	$hide = "<img src='./pic/minus.png' alt='Show/Hide' title='Show/Hide' border='0'/>"; 
	$show = "<img src='./pic/plus.png' alt='Show/Hide' title='Show/Hide' border='0'/>"; 
    $htmlout .= '<script type="text/javascript" src="scripts/jquery.js"></script>
	   <script type="text/javascript" src="scripts/jquery.cookie.js"></script>
	   <script type="text/javascript">
	   //<![CDATA[
		$(document).ready(function() {
			// the div that will be hidden/shown
			var panel = $("#box'.$caption.'");
			//the button that will toggle the panel
			var button = $("#top'.$caption.' a");
			// do you want the panel to start off collapsed or expanded?
			var initialState = "expanded"; // "expanded" OR "collapsed"
			// the class added when the panel is hidden
			var activeClass = "hidden";
			// the text of the button when the panels expanded
			var visibleHtml = "'.$hide.'";
			// the text of the button when the panels collapsed
			var hiddenHtml = "'.$show.'";
			
			
			//---------------------------
			// dont    edit    below    this    line,
			// unless you really know what youre doing
			//---------------------------
			
			if($.cookie("panelState'.$caption.'") == undefined) {
				$.cookie("panelState'.$caption.'", initialState);
			} 
			
			var state = $.cookie("panelState'.$caption.'");
			
			if(state == "collapsed") {
				panel.hide();
				button.html(hiddenHtml);
				button.addClass(activeClass);
			}
		   
			button.click(function(){
				if($.cookie("panelState'.$caption.'") == "expanded") {
					$.cookie("panelState'.$caption.'", "collapsed");
					button.html(hiddenHtml);
					button.addClass(activeClass);
				} else {
					$.cookie("panelState'.$caption.'", "expanded");
					button.html(visibleHtml);
					button.removeClass(activeClass);
				}
				
				panel.slideToggle("slow");
				
				return false;
			});
		});
		//]]>

	</script>
';
		$htmlout .="<div id='wrap$caption'><table class='main' width='$per%' align='center' border='0' cellspacing='1' cellpadding='0'>
		<tr><td class='$tdcls'><div id='top$caption'>
    <span>$img <b title=\"$title\">".$caption_t."</b> </span><div style='float:right; margin-top: 7px; padding-right: 10px;'><a href='#' id='$caption'>$hide</a></div></div></td>
				  	</tr></table>
					<div id='box".$caption."' ><table class='main' width='$per%' align='center'  cellspacing='0' cellpadding='10' style='border-width: medium 1px 1px;'><tr>
						<td align='center' style='background: none repeat scroll 0 0 #dfe8f4; border-width: 0px 1px 1px;'>";		
						return $htmlout;
	}
function end_block() {
    return "</td></tr>
    </table></div></div><br />\n";
}
//end Colaspable blocking system (cookie based)

    
//==Sql query count (Big thanks to bigjoos for implementing it)
$q['querytime'] = 0;
function sql_query($query) {
    global $queries, $q, $querytime, $query_stat;
	  $q = isset($q) && is_array($q) ? $q : array();
	  $q['query_stat']= isset($q['query_stat']) && is_array($q['query_stat']) ? $q['query_stat'] : array();
    $queries++;
    $query_start_time  = microtime(true); // Start time
    $result            = mysql_query($query);
    $query_end_time    = microtime(true); // End time
    $query_time        = ($query_end_time - $query_start_time);
    $querytime = $querytime + $query_time;
    $q['querytime']    = (isset($q['querytime']) ? $q['querytime'] : 0) + $query_time;
    $query_time        = substr($query_time, 0, 8);
    $q['query_stat'][] = array('seconds' => $query_time, 'query' => $query);
    return $result;
    }
    
function stdfoot($stdfoot = false) {
global $querytime, $CURUSER, $t, $TBDEV, $q, $queries, $query_stat;
    $q['start'] = $t;
    $queries = (!empty($queries) ? $queries : 0);
    $q['debug']       = array(1, 8, 12, 19); //==Add ids
    $q['seconds']     = (microtime(true) - $q['start']);
    $q['phptime']     = $q['seconds'] - $q['querytime'];
    $q['percentphp']  = number_format(($q['phptime'] / $q['seconds']) * 100, 2);
    $q['percentsql']  = number_format(($q['querytime'] / $q['seconds']) * 100, 2);
    $q['howmany']     = ($queries != 1 ? 's ' : ' ');
    $q['serverkillers'] = $queries > 6 ? '<br />'.($queries/2).' Server killers ran to show you this page :) ! =[' : '=]';
    
    $htmlfoot='';
    if(isset($CURUSER)){
    $htmlfoot = "<p align='center'>
    <span class='server'>The {$TBDEV['site_name']}
    Server killers generated this page in ".(round($q['seconds'], 4))." seconds and then took a nap.<br /> 
    They had to raid the server ".$queries." time'".$q['howmany']."using&nbsp;:&nbsp;<b>".$q['percentphp']."</b>&nbsp;&#37;&nbsp;php&nbsp;&#38;&nbsp;<b>".$q['percentsql']."</b>&nbsp;&#37;&nbsp;sql ".$q['serverkillers'].".</span></p>";
    
    if (SQL_DEBUG && in_array($CURUSER['id'], $q['debug'])) { 
    if ($q['query_stat']) {
    $htmlfoot .= "<br />";
	$htmlfoot .= begin_block('Queries',$caption_t='Queries',$per=98,$tdcls="colhead5", $img='', $title='Queries');
	$htmlfoot .= "<table width=\"100%\" align=\"center\" cellspacing=\"5\" cellpadding=\"5\" border=\"0\">
		<tr>
		<td class=\"colhead3\" width=\"5%\"  align=\"center\">ID</td>
		<td class=\"colhead3\" width=\"10%\" align=\"center\">Query Time</td>
		<td class=\"colhead3\" width=\"85%\" align=\"left\">Query String</td>
		</tr>";
    foreach ($q['query_stat'] as $key => $value) {
    $htmlfoot  .= "<tr>
		<td align=\"center\">".($key + 1)."</td>
		<td align=\"center\"><b>". ($value['seconds'] > 0.01 ?
		"<font color=\"red\" title=\"You should optimize this query.\">".
    $value['seconds']."</font>" : "<font color=\"green\" title=\"Query good.\">".
	  $value['seconds']."</font>")."</b></td>
		<td align=\"left\">".htmlspecialchars($value['query'])."<br /></td>
		</tr>";	   		   
    }
    $htmlfoot .='</table></div>';
    $htmlfoot .= end_block();
    }
    }
    }
    $dateFormat="H:i";
    $timeNdate=gmdate($dateFormat, time());
    $htmlfoot .="</table></div><div id='footer1'>
     <div class='clearer' style=' font-family:Times New Roman;' title='BitsB v1.1'>BitsB v1.1</div>
    <div align='right' style=' font-family:Times New Roman; width: 965px; margin: -14px 0px 0pt; text-align: right;'><img style='margin-bottom: 1px;' alt='Server Time' src='pic/clock.png'> <a title='Server Time'>$timeNdate GMT</a></div>
    </div></div>";
    //query stats 
    // include js files needed only for the page being used by pdq
    $htmlfoot .= '<!-- javascript goes here -->';
    if ($stdfoot['js'] != false) {
    foreach ($stdfoot['js'] as $JS)
    $htmlfoot .= '<script type="text/javascript" src="'.$TBDEV['baseurl'].'/scripts/'.$JS.'.js"></script>';
    }
    $htmlfoot .= "</body></html>\n";
    return $htmlfoot;
    }


?>