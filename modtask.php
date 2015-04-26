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
|   N.B. Patches & some little bugfixes added. (20.02.2011)
+------------------------------------------------
*/

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/page_verify.php";

dbconn(false);

loggedinorreturn();
staffonly();

$lang = array_merge( load_language('global'), load_language('modtask') );
//2-way handshake varification
$newpage = new page_verify();  
$newpage->check('modtask');
//end 2-way varification

if ($CURUSER['class'] < UC_MODERATOR) stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");

// Correct call to script
if ((isset($_POST['action'])) && ($_POST['action'] == "edituser"))
    {
    // Set user id
    if (isset($_POST['userid'])) $userid = $_POST['userid'];
    else stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");

    // and verify...
    if (!is_valid_id($userid)) stderr("{$lang['modtask_error']}", "{$lang['modtask_bad_id']}");

    // Fetch current user data...
    $res = mysql_query("SELECT * FROM users WHERE id=".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $user = mysql_fetch_assoc($res) or sqlerr(__FILE__, __LINE__);
    //== Check to make sure your not editing someone of the same or higher class 
    if ($CURUSER["class"] <= $user['class'] && ($CURUSER['id']!= $userid && $CURUSER["class"] < UC_ADMINISTRATOR)) 
        stderr('Error','You cannot edit someone of the same or higher class.. injecting stuff arent we? Action logged');
    
    if (($user['immunity'] == "yes") && ($CURUSER['class'] < UC_SYSOP))
        stderr("Error", "This user is immune to your commands !");

    //== Sysop log 
    $curclass = $user["class"];
    $curdonor = $user["donor"];
    $curenabled = $user["enabled"];
    $curdownloadpos = $user["downloadpos"];
    $curuploadpos = $user["uploadpos"];
    //$cursendpmpos = $user["sendpmpos"];
    $curchatpost = $user["chatpost"];
    $curimmunity = $user["immunity"];
    $curleechwarn = $user["leechwarn"];
    $curwarned = $user["warned"];
    $curdownloadtoadd = $user["downloaded"];
    $curuploadtoadd = $user["uploaded"];
    $curtitle = $user["title"];
    $curresetpasskey = $user["passkey"];
    $curseedbonus = $user["seedbonus"];
    $curreputation = $user["reputation"];
    $curavatar = $user["avatar"];
    $cursignature = $user["signature"];
    $curinvite_on = $user["invite_rights"];
    $curinvites = $user["invites"];
    $cursupport = $user["support"];
    $cursupportfor = $user["supportfor"];
    $curfreeslots = $user["freeslots"];
    $curfree_switch = $user["free_switch"];
    $curhighspeed = $user["highspeed"];
    //$curparked = $user["parked"];
    $curforum_mod= $user["forum_mod"];
    $curforum_post = $user["forumpost"];
    //$cursignature_post = $user["signature_post"];
    //$curavatar_rights = $user["avatar_rights"];
    $curoffensive_avatar = $user["offavatar"];
    //$curview_offensive_avatar = $user["view_offensive_avatar"];
    
    $updateset = $useredit['update'] = array();

    $modcomment = (isset($_POST['modcomment']) && $CURUSER['class'] == UC_SYSOP) ? $_POST['modcomment'] : $user['modcomment'];

    // Set class

    if ((isset($_POST['class'])) && (($class = $_POST['class']) != $user['class'])) { 
    if ($class >= UC_SYSOP || ($class >= $CURUSER['class']) || ($user['class'] >= $CURUSER['class'])) 
        stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}"); 
    if (!is_valid_user_class($class) || $CURUSER["class"] <= $_POST['class']) 
        stderr( ("Error"), "Bad class :P");

    // Notify user
    $what = ($class > $user['class'] ? "{$lang['modtask_promoted']}" : "{$lang['modtask_demoted']}");
    $msg = sqlesc(sprintf($lang['modtask_have_been'], $what)." '" . get_user_class_name($class) . "' {$lang['modtask_by']} ".$CURUSER['username']);
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);

    $updateset[] = "class = ".sqlesc($class);

    $modcomment = get_date( time(), 'DATE', 1 ) . " - $what to '" . get_user_class_name($class) . "' by $CURUSER[username].\n". $modcomment;
    }
    
    // Set immunity
    if ((isset($_POST['immunity'])) && (($immunity = $_POST['immunity']) != $user['immunity']))
    {
    if ($immunity == 'yes')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - GOD immunity enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $subject = sqlesc("Immunity");
    $msg = sqlesc("You have GOD immunity ! " . $CURUSER['username'] . " is protecting your account ! You are not vulnerable to staff actions.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
    }
    elseif ($immunity == 'no')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - GOD immunity disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $subject = sqlesc("Immunity");
    $msg = sqlesc("Your GOD immunity have been removed by " . $CURUSER['username'] . " !");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
    }
    else
    die(); // Error

    $updateset[] = "immunity = " . sqlesc($immunity);
    }

    // Clear Warning - Code not called for setting warning
    if (isset($_POST['warned']) && (($warned = $_POST['warned']) != $user['warned']))
    {
    $updateset[] = "warned = " . sqlesc($warned);
    $updateset[] = "warneduntil = 0";
    if ($warned == 'no')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_warned']}" . $CURUSER['username'] . ".\n". $modcomment;
    $msg = sqlesc("{$lang['modtask_warned_removed']}" . $CURUSER['username'] . ".");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    }
    
    if (isset($_POST['action']) && ($_POST['action'] == "confirmuser"))
    {
	   $status = isset($_POST["confirm"]) ? $_POST["confirm"] : '';
	   $userid = isset($_POST["userid"]) ? $_POST["userid"] : '';
	   $ret = isset($_POST["ret"]) ? $_POST["ret"] : "userdetails.php?id={$userid}";
	   mysql_query("update users set status = '{$status}' where id = {$userid}") or sqlerr(__FILE__, __LINE__);
	   header("location: {$ret}");
    }

    // Set warning - Time based
    if (isset($_POST['warnlength']) && ($warnlength = 0 + $_POST['warnlength']))
    {
    unset($warnpm);
    if (isset($_POST['warnpm'])) $warnpm = $_POST['warnpm'];

    if ($warnlength == 255)
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_warned_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_reason']} $warnpm\n" . $modcomment;
    $msg = sqlesc("{$lang['modtask_warning_received']}".$CURUSER['username'].($warnpm ? "\n\n{$lang['modtask_reason']} $warnpm" : ""));
    $updateset[] = "warneduntil = 0";
    }
    else
    {
    $warneduntil = (time() + $warnlength * 604800);
    $dur = $warnlength . "{$lang['modtask_week']}" . ($warnlength > 1 ? "s" : "");
    $msg = sqlesc(sprintf($lang['modtask_warning_duration'], $dur).$CURUSER['username'].($warnpm ? "\n\nReason: $warnpm" : ""));
    $modcomment = get_date( time(), 'DATE', 1 ) . sprintf($lang['modtask_warned_for'], $dur) . $CURUSER['username'] . ".\n{$lang['modtask_reason']} $warnpm\n" . $modcomment;
    $updateset[] = "warneduntil = ".$warneduntil;
    }
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    $updateset[] = "warned = 'yes'";
    }
    
    // Over-ride Auto-Leech warnings
    if ((isset($_POST['leechwarn'])) && (($leechwarn = $_POST['leechwarn']) != $user['leechwarn'])) {
        if ($leechwarn == 'yes') {
            $modcomment =  get_date( time(), 'DATE', 1 ) . " - Leech Warned by " . $CURUSER['username'] . ".\n" .$modcomment;
            $subject = sqlesc("System Warning.");
            $msg = sqlesc("You have been Leech warned by " . $CURUSER['username'] . ". Contact admin if this incorrect.");
            $added = sqlesc(time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or sqlerr(__file__, __line__);
        } elseif ($leechwarn == 'no') {
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Leech warning removed by " . $CURUSER['username'] .".\n" . $modcomment;
            $subject = sqlesc("Warning removed.");
            $msg = sqlesc("Your Leech warning has been removed by " . $CURUSER['username'] .", just be careful in future : ).");
            $added = sqlesc(time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "leechwarn = " . sqlesc($leechwarn);
    }
    
    //=== karma bonus
	   if ((isset($_POST['seedbonus'])) && (($seedbonus = $_POST['seedbonus']) != $user['seedbonus']))
     {
     $modcomment = get_date( time(), 'DATE', 1 ) . " - Seeding bonus set to $seedbonus by " . $CURUSER['username'] . ".\n" . $modcomment;
     $updateset[] = "seedbonus = " . sqlesc($seedbonus);
     }
     // == end
     
    //Account parking by mod+ 
    if ((isset($_POST['parked'])) && (($parked = $_POST['parked']) != $user['parked'])) { 
        if ($parked == 'yes') { 
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Account Parked by " . $CURUSER['username'] . ".\n" . $modcomment; 
        } 
        elseif ($parked == 'no') { 
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Account UnParked by " . $CURUSER['username'] . ".\n" . $modcomment; 
        } 
    else 
        stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}"); 
    $updateset[] = "parked = " . sqlesc($parked); 
    }
    //End account parking by mod+ 
    
    // === add donated amount to user and to funds table 
    if ((isset($_POST['donated'])) && (($donated = $_POST['donated']) != $user['donated'])) { 
       $added = sqlesc(time()); 
       mysql_query("INSERT INTO funds (cash, user, added) VALUES ($donated, $userid, $added)") or sqlerr(__file__, __line__); 
       $updateset[] = "donated = " . sqlesc($donated); 
       $updateset[] = "total_donated = $user[total_donated] + " . sqlesc($donated); 
    } 
    // ====end 
     
    // === Set donor - Time based 
    if ((isset($_POST['donorlength'])) && ($donorlength = 0 + $_POST['donorlength'])) { 
       if ($donorlength == 255) {     
       $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_donor_set']}" . $CURUSER['username'] . ".\n" . $modcomment; 
       $msg = sqlesc("You have received donor status from " . $CURUSER['username']); 
       $subject = sqlesc("Thank You for Your Donation!"); 
       $updateset[] = "donoruntil = '0'"; 
       } else { 
       $donoruntil = (time() + $donorlength * 604800); 
       $dur = $donorlength . " week" . ($donorlength > 1 ? "s" : ""); 
       $msg = sqlesc("Dear " . $user['username'] . " 
       :wave: 
       Thanks for your support to {$TBDEV['site_name']} ! 
       Your donation helps us in the costs of running the site! 
       As a donor, you are given some bonus gigs added to your uploaded amount, the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile: 
 
       so, thanks again, and enjoy! 
       cheers, 
       {$TBDEV['site_name']} Staff 
 
       PS. Your donator status will last for $dur and can be found on your user details page and can only be seen by you :smile: It was set by " .$CURUSER['username']); 
       $subject = sqlesc("Thank You for Your Donation!"); 
       $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_donor_set']}" . $CURUSER['username'] . ".\n" . $modcomment; 
       $updateset[] = "donoruntil = " . sqlesc($donoruntil); 
       $updateset[] = "vipclass_before = " . $user["class"]; 
       } 
       $added = sqlesc(time()); 
       mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or sqlerr(__file__, __line__); 
       $updateset[] = "donor = 'yes'"; 
       $res = mysql_query("SELECT class FROM users WHERE id = $userid") or sqlerr(__file__,__line__); 
       $arr = mysql_fetch_array($res); 
       if ($user['class'] < UC_UPLOADER) 
       $updateset[] = "class = ".UC_VIP.""; 
       } 
     
    // === add to donor length // thanks to CF 
    if ((isset($_POST['donorlengthadd'])) && ($donorlengthadd = 0 + $_POST['donorlengthadd'])) { 
       $donoruntil = $user["donoruntil"]; 
       $dur = $donorlengthadd . " week" . ($donorlengthadd > 1 ? "s" : ""); 
       $msg = sqlesc("Dear " . $user['username'] . " 
       :wave: 
       Thanks for your continued support to {$TBDEV['site_name']} ! 
       Your donation helps us in the costs of running the site. Everything above the current running costs will go towards next months costs! 
       As a donor, you are given some bonus gigs added to your uploaded amount, and, you have the the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile: 
 
       so, thanks again, and enjoy! 
       cheers, 
       {$TBDEV['site_name']} Staff 
 
        PS. Your donator status will last for an extra $dur on top of your current donation status, and can be found on your user details page and can only be seen by you :smile: It was set by " .$CURUSER['username']); 
 
        $subject = sqlesc("Thank You for Your Donation... Again!"); 
        $modcomment = get_date( time(), 'DATE', 1 ) . " - Donator status set for another $dur by " . $CURUSER['username'] .".\n" . $modcomment; 
        $donorlengthadd = $donorlengthadd * 7; 
        mysql_query("UPDATE users SET vipclass_before=".$user["class"].", donoruntil = IF(donoruntil=0, ".TIME_NOW." + 86400 * $donorlengthadd, donoruntil + 86400 * $donorlengthadd) WHERE id = $userid") or sqlerr(__file__, __line__);  
        $added = sqlesc(time()); 
        mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or sqlerr(__file__, __line__); 
        $updateset[] = "donated = $user[donated] + " . sqlesc($_POST['donated']); 
        $updateset[] = "total_donated = $user[total_donated] + " . sqlesc($_POST['donated']); 
    } 
    // === end add to donor length 
     
    // === Clear donor if they were bad 
    if (isset($_POST['donor']) && (($donor = $_POST['donor']) != $user['donor'])) { 
        $updateset[] = "donor = " . sqlesc($donor); 
        $updateset[] = "donoruntil = '0'"; 
        $updateset[] = "donated = '0'"; 
        $updateset[] = "class = " . $user["vipclass_before"]; 
        if ($donor == 'no') { 
        $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_donor_removed']} " . $CURUSER['username'] .".\n" . $modcomment; 
        $msg = sqlesc(sprintf($lang['modtask_donor_removed']) . $CURUSER['username']); 
        $added = sqlesc(time()); 
        $subject = sqlesc("Donator status expired."); 
        mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or sqlerr(__file__, __line__); 
        } 
    } 
    // ===end
    
    // invite rights
	   if ((isset($_POST['invite_rights'])) && (($invite_rights = $_POST['invite_rights']) != $user['invite_rights'])){
	   if ($invite_rights == 'yes')
	   {
	   $modcomment = get_date( time(), 'DATE', 1 ) . " - Invite rights enabled by " . htmlspecialchars($CURUSER['username']) . ".\n" . $modcomment;
	   $msg = sqlesc("Your invite rights have been given back by " . htmlspecialchars($CURUSER['username']) . ". You can invite users again.");
	   $added = time();
	   mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
	   }
	   elseif ($invite_rights == 'no'){
	   $modcomment = get_date( time(), 'DATE', 1 ) . " - Invite rights disabled by " . htmlspecialchars($CURUSER['username']) . ".\n" . $modcomment;
	   $msg = sqlesc("Your invite rights have been removed by " . htmlspecialchars($CURUSER['username']) . ", probably because you invited a bad user.");
	   $added = time();
	   mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
	   }
	   $updateset[] = "invite_rights = " . sqlesc($invite_rights);
	   }
	   
    // Set Upload Enable / Disable
    if ((isset($_POST['uploadpos'])) && (($uploadpos = $_POST['uploadpos']) != $user['uploadpos']))
    {
    if ($uploadpos == 'yes')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Upload enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("You have been given upload rights by " . $CURUSER['username'] . ". You can now upload torrents.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    elseif ($uploadpos == 'no')
    {
     $modcomment = get_date( time(), 'DATE', 1 ) . " - Upload disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your upload rights have been removed by " . $CURUSER['username'] . ". Please PM ".$CURUSER['username']." for the reason why.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    $updateset[] = "uploadpos = " . sqlesc($uploadpos);
    }

    // Set Download Enable / Disable
    if ((isset($_POST['downloadpos'])) && (($downloadpos = $_POST['downloadpos']) != $user['downloadpos']))
    {
    if ($downloadpos == 'yes')
    {
     $modcomment = get_date( time(), 'DATE', 1 ) . " - Download enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your download rights have been given back by " . $CURUSER['username'] . ". You can download torrents again.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    elseif ($downloadpos == 'no')
    {
     $modcomment = get_date( time(), 'DATE', 1 ) . " - Download disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your download rights have been removed by " . $CURUSER['username'] . ", Please PM ".$CURUSER['username']." for the reason why.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    $updateset[] = "downloadpos = " . sqlesc($downloadpos);
    }
    
    // Forum Post Enable / Disable
    if ((isset($_POST['forumpost'])) && (($forumpost = $_POST['forumpost']) != $user['forumpost']))
    {
    if ($forumpost == 'yes')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Forum posting enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your Posting rights have been given back by ".$CURUSER['username'].". You can post to forum again.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    else
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Forum posting disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your Posting rights have been removed by ".$CURUSER['username'].", Please PM ".$CURUSER['username']." for the reason why.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    $updateset[] = "forumpost = " . sqlesc($forumpost);
    }
    // comment Enable / Disable
	if ((isset($_POST['disablecom'])) && (($disablecom = $_POST['disablecom']) != $user['disablecom']))
	{
	if ($disablecom == 'yes')
	{
	$modcomment = gmdate("d-m-Y")." - Comments disabled by ".$CURUSER['username'].".\n" . $modcomment;
	$msg = sqlesc("Your comments rights have been removed by ".$CURUSER['username'].", Please PM ".$CURUSER['username']." for the reason why.");
	$added = time();
	mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
	}
	else
	{
	$modcomment = gmdate("d-m-Y")." - Comments enabled by ".$CURUSER['username'].".\n" . $modcomment;
	$msg = sqlesc("Your comment rights have been given back by ".$CURUSER['username'].". You can comment again.");
	$added = time();
	mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
	}
	$updateset[] = "disablecom = " . sqlesc($disablecom);
	} 
    // change invite amount
	   if ((isset($_POST['invites'])) && (($invites = $_POST['invites']) != ($curinvites = $user['invites'])))
	   {
	   $modcomment = get_date( time(), 'DATE', 1 ) . " - Invite amount changed to ".$invites." from ".$curinvites." by " . htmlspecialchars($CURUSER['username']) . ".\n" . $modcomment;
	   $updateset[] = "invites = " . sqlesc($invites);
	   }
    // Clear donor - Code not called for setting donor
    if (isset($_POST['donor']) && (($donor = $_POST['donor']) != $user['donor']))
    {
    $updateset[] = "donor = " . sqlesc($donor);
    $updateset[] = "warneduntil = 0";
    if ($donor == 'no')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_donor_removed']}".$CURUSER['username'].".\n". $modcomment;
    $msg = sqlesc("{$lang['modtask_donor_expired']}");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    }

    // Set donor - Time based
    if ((isset($_POST['donorlength'])) && ($donorlength = 0 + $_POST['donorlength']))
    {
    if ($donorlength == 255)
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_donor_set']}" . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("{$lang['modtask_received_donor']}".$CURUSER['username']);
    $updateset[] = "donoruntil = 0";
    }
    else
    {
    $donoruntil = (time() + $donorlength * 604800);
    $dur = $donorlength . "{$lang['modtask_week']}" . ($donorlength > 1 ? "s" : "");
    $msg = sqlesc(sprintf($lang['modtask_donor_duration'], $dur) . $CURUSER['username']);
    $modcomment = get_date( time(), 'DATE', 1 ) . sprintf($lang['modtask_donor_for'], $dur) . $CURUSER['username']."\n".$modcomment;
    $updateset[] = "donoruntil = ".$donoruntil;
    }
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    $updateset[] = "donor = 'yes'";
    }

    // Enable / Disable
    if ((isset($_POST['enabled'])) && (($enabled = $_POST['enabled']) != $user['enabled']))
    {
    if ($enabled == 'yes')
    $modcomment = get_date( time(), 'DATE', 1 ) . " {$lang['modtask_enabled']}" . $CURUSER['username'] . ".\n" . $modcomment;
    else
    $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_disabled']}" . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "enabled = " . sqlesc($enabled);
    }

    // Change Custom Title
    if ((isset($_POST['title'])) && (($title = $_POST['title']) != ($curtitle = $user['title'])))
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_custom_title']}'".$title."' from '".$curtitle."'{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "title = " . sqlesc($title);
    }

    /// Add remove uploaded
	  if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
		$uploadtoadd = 0 + $_POST["amountup"];
		$downloadtoadd = 0 +  $_POST["amountdown"];
		$formatup = $_POST["formatup"];
		$formatdown = $_POST["formatdown"];
		$mpup = $_POST["upchange"];
		$mpdown = $_POST["downchange"];
		if($uploadtoadd > 0)	{
			if($mpup == "plus"){
				$newupload = $user["uploaded"] + ($formatup == 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
				$modcomment = get_date( time(), 'DATE', 1 ) . " {$lang['modtask_add_upload']} (".$uploadtoadd." ".$formatup .") {$lang['modtask_by']} " . $CURUSER['username'] ."\n" . $modcomment;
			}
			else{
				$newupload = $user["uploaded"] - ($formatup == 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
				if ($newupload >= 0)
						$modcomment =  get_date( time(), 'DATE', 1 ) . " {$lang['modtask_subtract_upload']} (".$uploadtoadd." ".$formatup .") {$lang['modtask_by']} " . $CURUSER['username'] ."\n" . $modcomment;
			}
			if ($newupload >= 0)
				$updateset[] =  "uploaded = ".sqlesc($newupload)."";
		}

		if($downloadtoadd > 0)	 {
			if($mpdown == "plus"){
				$newdownload = $user["downloaded"] + ($formatdown == 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
				$modcomment = get_date( time(), 'DATE', 1 ) . " {$lang['modtask_added_download']} (".$downloadtoadd." ".$formatdown .") {$lang['modtask_by']} " . $CURUSER['username'] ."\n" . $modcomment;
			}
			else{
				$newdownload = $user["downloaded"] - ($formatdown == 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
				if ($newdownload >= 0)						
				$modcomment = get_date( time(), 'DATE', 1 ) . " {$lang['modtask_subtract_download']} (".$downloadtoadd." ".$formatdown .") {$lang['modtask_by']} " . $CURUSER['username'] ."\n" . $modcomment;
			}
			if ($newdownload >= 0)
					$updateset[] =  "downloaded = ".sqlesc($newdownload)."";
		}
	}
	/// End add/remove upload
    
    // Set higspeed Upload Enable / Disable
    if ((isset($_POST['highspeed'])) && (($highspeed = $_POST['highspeed']) != $user['highspeed'])) {
        if ($highspeed == 'yes') {
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Highspeed Upload enabled by " . $CURUSER['username'] .".\n" . $modcomment;
            $subject = sqlesc("Highspeed uploader status.");
            $msg = sqlesc("You  have been set as a high speed uploader by  " . $CURUSER['username'] .". You can now upload torrents using highspeeds without being flagged as a cheater  .");
            $added = sqlesc(time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or sqlerr(__file__, __line__);
        } elseif ($highspeed == 'no') {
            $modcomment = get_date( time(), 'DATE', 1 ) . " - Highspeed Upload disabled by " . $CURUSER['username'] .".\n" . $modcomment;
            $subject = sqlesc("Highspeed uploader status.");
            $msg = sqlesc("Your highspeed upload setting has been disabled by " . $CURUSER['username'] .". Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or sqlerr(__file__, __line__);
        } 
        else
        die(); // Error
        $updateset[] = "highspeed = " . sqlesc($highspeed);
        }
    //End highspeed uploader
    
    // The following code will place the old passkey in the mod comment and create
    // a new passkey. This is good practice as it allows usersearch to find old
    // passkeys by searching the mod comments of members.

    // Reset Passkey
    if ((isset($_POST['resetpasskey'])) && ($_POST['resetpasskey']))
    {
    $newpasskey = md5($user['username'].time().$user['passhash']);
    $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_passkey']}".sqlesc($user['passkey'])."{$lang['modtask_reset']}".sqlesc($newpasskey)."{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "passkey=".sqlesc($newpasskey);
    }

    // Add Comment to ModComment
    if ((isset($_POST['addcomment'])) && ($addcomment = trim($_POST['addcomment'])))
    {
    $modcomment = gmdate("Y-m-d") . " - ".$addcomment." - " . $CURUSER['username'] . ".\n" . $modcomment;
    } 

    // Avatar Changed
    if ((isset($_POST['avatar'])) && (($avatar = $_POST['avatar']) != ($curavatar = $user['avatar'])))
    {
      
      $avatar = trim( urldecode( $avatar ) );
  
      if ( preg_match( "/^http:\/\/$/i", $avatar ) 
        or preg_match( "/[?&;]/", $avatar ) 
        or preg_match("#javascript:#is", $avatar ) 
        or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $avatar ) 
      )
      {
        $avatar='';
      }
      
      if( !empty($avatar) ) 
      {
        $img_size = @GetImageSize( $avatar );

        if($img_size == FALSE || !in_array($img_size['mime'], $TBDEV['allowed_ext']))
          stderr("{$lang['modtask_user_error']}", "{$lang['modtask_not_image']}");

        if($img_size[0] < 5 || $img_size[1] < 5)
          stderr("{$lang['modtask_user_error']}", "{$lang['modtask_image_small']}");
      
        if ( ( $img_size[0] > $TBDEV['av_img_width'] ) OR ( $img_size[1] > $TBDEV['av_img_height'] ) )
        { 
            $image = resize_image( array(
                             'max_width'  => $TBDEV['av_img_width'],
                             'max_height' => $TBDEV['av_img_height'],
                             'cur_width'  => $img_size[0],
                             'cur_height' => $img_size[1]
                        )      );
                        
          }
          else 
          {
            $image['img_width'] = $img_size[0];
            $image['img_height'] = $img_size[1];
          }
      
        $updateset[] = "av_w = " . $image['img_width'];
        $updateset[] = "av_h = " . $image['img_height'];
      }
      
      $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_avatar_change']}".htmlspecialchars($curavatar)."{$lang['modtask_to']}".htmlspecialchars($avatar)."{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;

      $updateset[] = "avatar = ".sqlesc($avatar);
    }
    ////////////sig checks
    if ((isset($_POST['signature'])) && (($signature = $_POST['signature']) != ($cursignature = $user['signature'])))
    {
      
      $signature = trim( urldecode( $signature ) );
  
      if ( preg_match( "/^http:\/\/$/i", $signature ) 
        or preg_match( "/[?&;]/", $signature ) 
        or preg_match("#javascript:#is", $signature ) 
        or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $signature ) 
      )
      {
        $signature='';
      }
      
      if( !empty($signature) ) 
      {
        $img_size = @GetImageSize( $signature );

        if($img_size == FALSE || !in_array($img_size['mime'], $TBDEV['allowed_ext']))
          stderr("{$lang['modtask_user_error']}", "{$lang['modtask_not_image']}");

        if($img_size[0] < 5 || $img_size[1] < 5)
          stderr("{$lang['modtask_user_error']}", "{$lang['modtask_image_small']}");
      
        if ( ( $img_size[0] > $TBDEV['sig_img_width'] ) OR ( $img_size[1] > $TBDEV['sig_img_height'] ) )
        { 
            $image = resize_image( array(
                             'max_width'  => $TBDEV['sig_img_width'],
                             'max_height' => $TBDEV['sig_img_height'],
                             'cur_width'  => $img_size[0],
                             'cur_height' => $img_size[1]
                        )      );
                        
          }
          else 
          {
            $image['img_width'] = $img_size[0];
            $image['img_height'] = $img_size[1];
          }
      
        $updateset[] = "sig_w = " . $image['img_width'];
        $updateset[] = "sig_h = " . $image['img_height'];
      }
      
      $modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_signature_change']}".htmlspecialchars($cursignature)."{$lang['modtask_to']}".htmlspecialchars($signature)."{$lang['modtask_by']}" . $CURUSER['username'] . ".\n" . $modcomment;

    $updateset[] = "signature = " . sqlesc("[img]".$signature."[/img]\n");
    }
    //==End sign change
    
    //Username changing system for admin+
    	if ((isset($_POST['username'])) && (($username = $_POST['username']) != ($curusername = $user['username'])))
	{
	$modcomment = get_date( time(), 'DATE', 1 ) . "{$lang['modtask_chuname']}'".$username."' {$lang['modtask_from']} '".$curusername."' {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;

	$updateset[] = "username = " . sqlesc($username);
	}
    //end of Username changing system
    
    //==09  offensive Avatar
    if ((isset($_POST['offavatar'])) && (($offavatar = $_POST['offavatar']) != $user['offavatar']))
    {
    if ($offavatar == 'yes')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Marked as offensive avatar by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your avatar is set as offensive by  ".htmlspecialchars($CURUSER['username']).", Please PM ".htmlspecialchars($CURUSER['username'])." for the reason why.");
    $added = time();
    $subject = sqlesc("Your avatar is set as Offensive.");
    mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
    }
    elseif ($offavatar == 'no')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - Un-Marked as Not offensive avatar by " . $CURUSER['username'] . ".\n" . $modcomment;
    $msg = sqlesc("Your avatar is set as Not offensive by  ".htmlspecialchars($CURUSER['username']).".");
    $added = time();
    $subject = sqlesc("Your avatar is not Offensive.");
    mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
    }
    else
    die();
    $updateset[] = "offavatar = " . sqlesc($offavatar);
    }
    //==End offensive avatar
    
    // forum moderator mod by putyn tbdev
    // start
    if (isset($_POST["forum_mod"]) && ($forum_mod = $_POST["forum_mod"]) != $user["forum_mod"]) {
        $whatm = ($forum_mod == "yes" ? "added " : "removed");
        if ($forum_mod == "no") {
            $updateset[] = "forums_mod = ''";
            mysql_query("DELETE FROM forum_mods WHERE uid=" . $user["id"]) or sqlerr(__file__,
                __line__);
        }
        $updateset[] = "forum_mod=" . sqlesc($forum_mod);
        $modcomment = get_date( time(), 'DATE', 1 ) . " " . $CURUSER["username"] . " " . $whatm .
            " forum rights\n" . $modcomment;
    }
    // update forums list
    $forumsc = (isset($_POST["forums_count"]) ? 0 + $_POST["forums_count"] : 0);

    if ($forumsc > 0 && $forum_mod != "no") {
        for ($i = 1; $i < $forumsc + 1; $i++) {
            if (substr($_POST["forums_$i"], 0, 3) == "yes")
                $foo[] = (int)substr($_POST["forums_$i"], 4);
        }
        foreach ($foo as $fo) {
            $boo[] = "(" . $fo . "," . $user["id"] . "," . sqlesc($user["username"]) . ")";
            $boo1[] = "[" . $fo . "]";
        }

        mysql_query("DELETE FROM forum_mods WHERE uid=" . $user["id"]) or sqlerr(__file__,
            __line__);
        mysql_query("INSERT INTO forum_mods(fid,uid,user) VALUES " . join(",", $boo)) or
            sqlerr(__file__, __line__);
        $updateset[] = "forums_mod=" . sqlesc(join("", $boo1));
    }
    // end forum moderator mod
    
     // === Enable / Disable chat box rights
    if ((isset($_POST['chatpost'])) && (($chatpost = $_POST['chatpost']) != $user['chatpost'])) {
        $modcomment = get_date( time(), 'DATE', 1 ) . " {$lang['modtask_chatpos']} " . sqlesc($chatpost) .
            " {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = "chatpost = " . sqlesc($chatpost);
    }

    /*// support
    if ((isset($_POST['support'])) && (($support = $_POST['support']) != $user['support'])){
    if ($support == 'yes')
    {
    $modcomment = get_date( time(), 'DATE', 1 ) . " - {$lang['modtask_fls']} " . htmlspecialchars($CURUSER['username']) . ".\n" . $modcomment;
    $msg = sqlesc("{$lang['modtask_fls1']} " . htmlspecialchars($CURUSER['username']) . ".");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    elseif ($support == 'no'){
    $modcomment = get_date( time(), 'DATE', 1 ) . " -{$lang['modtask_fls2']} " . htmlspecialchars($CURUSER['username']) . ".\n" . $modcomment;
    $msg = sqlesc("{$lang['modtask_fls3']}" . htmlspecialchars($CURUSER['username']) . ", {$lang['modtask_fls4']}.");
    $added = time();
    mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    }
    $updateset[] = "support = " . sqlesc($support);
    }
 	  if (isset($_POST['supportfor']) && ($supportfor = $_POST['supportfor']) != $user['supportfor']) {
 	  $updateset[] = "supportfor = " . sqlesc($supportfor);
 	}
 // ====end*/
 // Support 
if ((isset($_POST['support'])) && (($support = $_POST['support']) != $user['support'])) 
{ 
        $what = ($support == "yes" ? "Promoted" : "Demoted"); 
        $modcomment = gmdate("Y-m-d") . " - ".$what." to FLS by " . $CURUSER['username'] . ".\n" . $modcomment; 
            $msg = sqlesc("{$lang['modtask_fls1']} " . htmlspecialchars($CURUSER['username']) . ".");
        $added = time();
        mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__); 
        if($support == "no"){ 
            $updateset[] = "support_lang =''"; 
            $updateset[] = "supportfor ='' "; 
            $msg = sqlesc("{$lang['modtask_fls3']}" . htmlspecialchars($CURUSER['username']) . ", {$lang['modtask_fls4']}.");
            $added = time();
            mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
        } 
        $updateset[] = "support = " . sqlesc($support); 
         
}  
if(isset($_POST["supportfor"]) && ($supportfor = $_POST["supportfor"]) != $user["supportfor"]) 
        $updateset[] = "supportfor = ".sqlesc($supportfor); 
//support language by putyn      
$support_lang = isset($_POST["fls_langs"]) ? join("|",$_POST["fls_langs"]) : ""; 
if($support_lang != $user["support_lang"]) 
        $updateset[] = "support_lang =".sqlesc($support_lang); 
//end support

    // change freeslots
if ((isset($_POST['freeslots'])) && (($freeslots = $_POST['freeslots']) != ($curfreeslots = $user['freeslots'])))
{
    $modcomment = get_date(time(), 'DATE', 1)." - freeslots amount changed to '".$freeslots."' from '".
	$curfreeslots."' by " . $CURUSER['username'] . ".\n" . $modcomment;
}
$updateset[] = 'freeslots = '.sqlesc($freeslots);

/// Set Freeleech Status Time based
 if (isset($_POST['free_switch']) && ($free_switch =
    0 + $_POST['free_switch']))
{
    unset($free_pm);
    if (isset($_POST['free_pm']))
        $free_pm = $_POST['free_pm'];
    $subject = sqlesc('Notification!');
    $added = time();

    if ($free_switch == 255)
    {
        $modcomment = get_date($added, 'DATE', 1)." - Freeleech Status enabled by ".
		$CURUSER['username'].".\nReason: $free_pm\n".$modcomment;
        $msg = sqlesc("You have received Freeleech Status from ".$CURUSER['username'].($free_pm ?
            "\n\nReason: $free_pm" : ''));
        $updateset[] = 'free_switch = 1';
    } elseif ($free_switch == 42)
    {
        $modcomment = get_date($added, 'DATE', 1)." - Freeleech Status removed by ".
		$CURUSER['username'].".\n".$modcomment;
        $msg = sqlesc("Your Freeleech Status has been removed by ".
		$CURUSER['username'].".");
		$updateset[] = 'free_switch = 0';
    } else
    {
        $free_until = ($added + $free_switch * 604800);
        $dur = $free_switch.' week'.($free_switch > 1 ? 's' : '');
        $msg = sqlesc("You have received $dur Freeleech Status from ".
		$CURUSER['username'].($free_pm ? "\n\nReason: $free_pm" : ''));
        $modcomment = get_date($added, 'DATE', 1)." - Freeleech Status for $dur by ".
		$CURUSER['username'].".\nReason: $free_pm\n".$modcomment;
        $updateset[] = "free_switch = ".$free_until;
    }

    mysql_query("INSERT INTO messages (sender, receiver, subject, msg, added) 
	             VALUES (0, $userid, $subject, $msg, $added)") or sqlerr(__file__, __line__);
}
    //end freeleech
    $warned = $user['warned'];
    $leechwarn = $user['leechwarn'];
    // Add ModComment... (if we changed something we update otherwise we dont include this..) 
    if (($CURUSER['class'] == UC_SYSOP && ($user['modcomment'] != $_POST['modcomment'] || $modcomment!=$_POST['modcomment'])) || ($CURUSER['class']<UC_SYSOP && $modcomment != $user['modcomment'])) 
        $updateset[] = "modcomment = " . sqlesc($modcomment);

    // --------------------------------------------
    // promote and demote
    // --------------------------------------------
    if ($curclass != $class) {
        if ($class > $curclass ? "promoted" : "demoted")
        $useredit['update'][] = ''.$what.' to ' . get_user_class_name($class) . '';
    }
    /*// --------------------------------
    // donor
    // --------------------------------
    if ($donor && $curdonor != $donor) {
        if ($donor >= 1)
        $useredit['update'][] = 'Donor = Yes';
    }
    // --------------------------------
    // donor remove
    // --------------------------------
    if ($donor != $curdonor) {
        if ($donor == 0)
        $useredit['update'][] = 'Donor = No';
    }*/
    // --------------------------------------------
    // enable
    // --------------------------------------------
    if ($enabled && $curenabled != $enabled) {
        if ($enabled == 'yes')
        $useredit['update'][] = 'Enabled = Yes';
    }
    // --------------------------------------------
    // disable
    // --------------------------------------------
    if ($enabled && $curenabled != $enabled) {
        if ($enabled == 'no')
        $useredit['update'][] = 'Enabled = No';
    }
    
    // --------------------------------
    // enable download
    // --------------------------------
    if ($downloadpos != $curdownloadpos) {
        if ($downloadpos == 1)
        $useredit['update'][] = 'Download possible = Yes';
    }
    // --------------------------------
    // disable download
    // --------------------------------
    if ($downloadpos && $curdownloadpos != $downloadpos) {
        if ($downloadpos == 0 OR $downloadpos > 1)
        $useredit['update'][] = 'Download possible = No';
    }
    // --------------------------------
    // enable upload possible
    // --------------------------------
    if ($uploadpos && $curuploadpos != $uploadpos) {
        if ($uploadpos == 1)
        $useredit['update'][] = 'Uploads enabled = Yes';
    }
    // --------------------------------
    // upload disable
    // --------------------------------
    if ($uploadpos && $curuploadpos != $uploadpos) {
        if ($uploadpos == 0 OR $uploadpos > 1)
        $useredit['update'][] = 'Uploads enabled = No';
    }
    /*
    // --------------------------------
    // enable pms
    // --------------------------------
    if ($sendpmpos != $sendpmpos) {
        if ($sendpmpos == 1)
        $useredit['update'][] = 'Private messages enabled = Yes';
    }
    // --------------------------------
    // disable pms
    // --------------------------------
    if ($sendpmpos != $sendpmpos) {
        if ($sendpmpos == 0 OR $sendpmpos > 1)
        $useredit['update'][] = 'Private messages enabled = No';
    }
    */
    // --------------------------------
    // shoutbox ban
    // --------------------------------
    if ($chatpost && $curchatpost != $chatpost) {
        if ($chatpost == 0 )
        $useredit['update'][] = 'Shoutbox enabled = No';
    }
    // --------------------------------
    // shoutbox ban
    // --------------------------------
    if ($chatpost && $curchatpost != $chatpost) {
        if ($chatpost == 1)
        $useredit['update'][] = 'Shoutbox enabled = Yes';
    }
    
    // --------------------------------
    // user immune
    // --------------------------------
    if ($immunity && $curimmunity != $immunity) {
        if ($immunity >= 1)
        $useredit['update'][] = 'Immunity enabled = Yes';
    }
    // --------------------------------
    // user immune
    // --------------------------------
    if ($immunity != $curimmunity) {
        if ($immunity == 0)
        $useredit['update'][] = 'Immunity enabled = No';
    }
    // --------------------------------
    // Leechwarn
    // --------------------------------
    if ($leechwarn && $curleechwarn != $leechwarn) {
        if ($leechwarn >= 1)
        $useredit['update'][] = 'Leech warned = Yes';
    }
    // --------------------------------
    // Leechwarn
    // --------------------------------
    if ($leechwarn != $curleechwarn) {
        if ($leechwarn == 0)
        $useredit['update'][] = 'Leech warned = No';
    }
    // --------------------------------------------
    // warned
    // --------------------------------------------
    if ($warned && $curwarned != $warned) {
        if ($warned >= 1)
        $useredit['update'][] = 'Warned = Yes';
    }
    // --------------------------------------------
    // warning remove
    // --------------------------------------------
    if ($warned != $curwarned) {
        if ($warned == 0)
        $useredit['update'][] = 'Warned = No';
    }
    // --------------------------------------------
    // download amount
    // --------------------------------------------
    if ($downloadtoadd && $curdownloadtoadd != $downloadtoadd) {
        if ($newdownload)
        $useredit['update'][] = 'Downloaded total altered';
    }
    // --------------------------------------------
    // upload amount
    // --------------------------------------------
    if ($uploadtoadd  && $curuploadtoadd != $uploadtoadd) {
        if ($newupload)
        $useredit['update'][] = 'Uploaded total altered';
    }
    // --------------------------------------------
    // changed title
    // --------------------------------------------
    if ($title && $curtitle != $title) {
        if ($title)
        $useredit['update'][] = 'Custom title altered';
    }
    /*
    // --------------------------------------------
    // change passkey
    // --------------------------------------------
    if ($passkey && $curpasskey != $passkey) {
        if ($newpasskey)
        $useredit['update'][] = 'Passkey reset = Yes';
    }
    */
    // --------------------------------
    // seedbonus amount
    // --------------------------------
    if ($seedbonus && $curseedbonus != $seedbonus) {
        if ($seedbonus)
        $useredit['update'][] = 'Seedbonus points total adjusted';
    }
    /*
    // --------------------------------
    // reputation amount
    // --------------------------------
    if ($reputation && $curreputation != $reputation) {
        if ($reputation)
        $useredit['update'][] = 'Reputation points total adjusted';
    }
    */
    // --------------------------------------------
    // changed avatar
    // --------------------------------------------
    if ($avatar && $curavatar != $avatar) {
        if ($avatar)
        $useredit['update'][] = 'Avatar changed';
    }
    // --------------------------------------------
    // changed signature
    // --------------------------------------------
    if ($signature && $cursignature != $signature) {
        if ($signature)
        $useredit['update'][] = 'Signature changed';
    }
    // --------------------------------
    // invites rights
    // --------------------------------
    if ($invite_rights && $curinvite_on != $invite_rights) {
        if ($invite_rights == 'yes')
        $useredit['update'][] = 'Invites enabled = Yes';
    }
    // --------------------------------
    // invites rights
    // --------------------------------
    if ($invite_rights && $curinvite_on != $invite_rights) {
        if ($invite_rights == 'no')
         $useredit['update'][] = 'Invites enabled = No';
    }
    // --------------------------------
    // invite amount
    // --------------------------------
    if ($invites && $curinvites != $invites) {
        if ($invites)
        $useredit['update'][] = 'Invites total adjusted';
    }
    // --------------------------------
    // add firstline support
    // --------------------------------
    if ($support && $cursupport != $support) {
        if ($support == 'yes')
        $useredit['update'][] = 'Support enabled = Yes';
    }
    // --------------------------------
    // remove firstline support
    // --------------------------------
    if ($support && $cursupport != $support) {
        if ($support == 'no')
        $useredit['update'][] = 'Support enabled = No';
    }
    
    // --------------------------------
    // changed support for
    // --------------------------------
    if ($supportfor && $cursupportfor != $supportfor) {
        if ($supportfor)
        $useredit['update'][] = 'Support for altered = Yes';
    }
    
    // --------------------------------
    // freeslots amount
    // --------------------------------
    if ($freeslots && $curfreeslots != $freeslots) {
        if ($freeslots)
        $useredit['update'][] = 'Freeeslots total adjusted = Yes';   
    }
    
    // --------------------------------------------
    // pdq's Freeleech
    // --------------------------------------------
    if ($free_switch && $curfree_switch != $free_switch) {
        if ($free_switch == '1' OR $free_switch > '1')
        $useredit['update'][] = 'Freeleech enabled = Yes';
    }
    // --------------------------------------------
    // pdq's Freeleech
    // --------------------------------------------
    if ($free_switch && $curfree_switch != $free_switch) {
        if ($free_switch == '0')
        $useredit['update'][] = 'Freeleech enabled = No';
    }
    
    // --------------------------------------------
    // highspeed seeder
    // --------------------------------------------
    if ($highspeed  && $curhighspeed != $highspeed) {
        if ($highspeed == 'yes')
        $useredit['update'][] = 'Highspeed uploader enabled = Yes';
    }
    // --------------------------------------------
    // highspeed seeder
    // --------------------------------------------
    if ($highspeed && $curhighspeed != $highspeed) {
        if ($highspeed == 'no')
        $useredit['update'][] = 'Highspeed uploader enabled = No';
    }
    /*
    // --------------------------------
    // Parked
    // --------------------------------
    if ($parked && $curparked != $parked ) {
        if ($parked  == 'yes')
        $useredit['update'][] = 'Account parked = Yes';
    }
    // --------------------------------
    // Parked
    // --------------------------------
    if ($parked  && $curparked != $parked  ) {
        if ($parked  == 'no')
        $useredit['update'][] = 'Account parked = No';
    }
    */
    // --------------------------------------------
    // forum mod
    // --------------------------------------------
    if ($forum_mod && $curforum_mod != $forum_mod) {
        if ($forum_mod == 'yes')
        $useredit['update'][] = 'Forum moderator = Yes';
    }
    // --------------------------------------------
    // forum mod
    // --------------------------------------------
    if ($forum_mod && $curforum_mod != $forum_mod) {
        if ($forum_mod == 'no')
        $useredit['update'][] = 'Forum moderator = No';
    }
    // --------------------------------------------
    // forum post
    // --------------------------------------------
    if ($forumpost && $curforum_post != $forumpost) {
        if ($forumpost == 'yes')
        $useredit['update'][] = 'Forum post enabled = Yes';
    }
    // --------------------------------------------
    // forum post
    // --------------------------------------------
    if ($forumpost && $curforum_post != $forumpost) {
        if ($forumpost == 'no')
        $useredit['update'][] = 'Forum post enabled  = No';
     }
    /*
    // --------------------------------------------
    // signature post
    // --------------------------------------------
    if ($signature_post && $cursignature_post != $signature_post) {
        if ($signature_post == 'yes')
        $useredit['update'][] = 'Signature post enabled = Yes';
    }
    // --------------------------------------------
    // signature post
    // --------------------------------------------
    if ($signature_post && $cursignature_post != $signature_post) {
        if ($signature_post == 'no')
        $useredit['update'][] = 'Signature post enabled = No';
    }
    // --------------------------------------------
    // avatar rights
    // --------------------------------------------
    if ($avatar_rights && $curavatar_rights != $avatar_rights) {
        if ($avatar_rights == 'yes')
        $useredit['update'][] = 'Avatar rights enabled = Yes';
    }
    // --------------------------------------------
    // avatar rights
    // --------------------------------------------
    if ($avatar_rights && $curavatar_rights != $avatar_rights) {
        if ($avatar_rights == 'no')
        $useredit['update'][] = 'Avatar rights enabled = No';
    }
    */
    // --------------------------------------------
    // offensive avatar
    // --------------------------------------------
    if ($offavatar && $curoffensive_avatar != $offavatar) {
        if ($offavatar == 'yes')
        $useredit['update'][] = 'Offensive avatar enabled = Yes';
    }
    // --------------------------------------------
    // offensive avatar
    // --------------------------------------------
    if ($offavatar && $curoffensive_avatar != $offavatar) {
        if ($offavatar == 'no')
        $useredit['update'][] = 'Offensive avatar enabled = No';
    }
    /*
    // --------------------------------------------
    // offensive avatar
    // --------------------------------------------
    if ($view_offensive_avatar && $curview_offensive_avatar != $view_offensive_avatar) {
        if ($view_offensive_avatar == 'yes')
        $useredit['update'][] = 'View offensive avatar enabled = Yes';
    }
    // --------------------------------------------
    // offensive avatar
    // --------------------------------------------
    if ($view_offensive_avatar && $curview_offensive_avatar != $view_offensive_avatar) {
        if ($view_offensive_avatar == 'no')
        $useredit['update'][] = 'View offensive avatar enabled = No';
    }
    */
   //end staff log

    mysql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id=".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    //== 09 Updated Sysop log - thanks to pdq
     write_info("User account $userid (<a href='userdetails.php?id=$userid'>$user[username]</a>)\nThings edited: ".join(', ', $useredit['update'])." by <a href='userdetails.php?id=$CURUSER[id]'>$CURUSER[username]</a>");

    $returnto = $_POST["returnto"];
    header("Location: {$TBDEV['baseurl']}/$returnto");

    stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
    }

stderr("{$lang['modtask_user_error']}", "{$lang['modtask_no_idea']}");

?>