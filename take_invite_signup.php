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

require_once('include/bittorrent.php');
require_once ROOT_PATH.'/include/user_functions.php';
require_once ROOT_PATH.'/include/password_functions.php';

dbconn();

//get_template(); //==Uncomment if you use theme system
$lang = load_language('global');
$res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res);
if ($arr[0] >= $TBDEV['maxusers']) 	
stderr($lang['stderr_errorhead'], sprintf($lang['stderr_ulimit'], $TBDEV['maxusers']));

if (!mkglobal("wantusername:wantpassword:passagain:email:invite:passhint:hintanswer"))
die();

function validusername($username) {
if ($username == "")
return false;
// The following characters are allowed in user names
$allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
for ($i = 0; $i < strlen($username); ++$i)
if (strpos($allowedchars, $username[$i]) === false)
return false;
return true; 
}

if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($invite)|| empty($passhint) || empty($hintanswer))
stderr("Error","Don't leave any fields blank.");

if (strlen($wantusername) > 12)
stderr("Error","Sorry, username is too long (max is 12 chars)");

if ($wantpassword != $passagain)
stderr("Error","The passwords didn't match! Must've typoed. Try again.");

if (strlen($wantpassword) < 6)
stderr("Error","Sorry, password is too short (min is 6 chars)");

if (strlen($wantpassword) > 40)
stderr("Error","Sorry, password is too long (max is 40 chars)");

if ($wantpassword == $wantusername)
stderr("Error","Sorry, password cannot be same as user name.");

if (!validemail($email))
stderr("Error","That doesn't look like a valid email address.");

if (!validusername($wantusername))
stderr("Error","Invalid username.");

// make sure user agrees to everything...
if ($_POST["rulesverify"] != "yes" || $_POST["faqverify"] != "yes" || $_POST["ageverify"] != "yes")
stderr("Error","Sorry, you're not qualified to become a member of this site.");

// check if email addy is already in use
$a = (@mysql_fetch_row(@mysql_query('SELECT COUNT(*) FROM users WHERE email = ' . sqlesc($email)))) or die(mysql_error());
if ($a[0] != 0)
stderr('Error', 'The e-mail address <b>' . htmlspecialchars($email) . '</b> is already in use.');

$select_inv = mysql_query('SELECT sender, receiver, email, status FROM invite_codes WHERE code = ' . sqlesc($invite)) or die(mysql_error());
$rows = mysql_num_rows($select_inv);
$assoc = mysql_fetch_assoc($select_inv);

if ($rows == 0)
stderr("Error","Invite not found.\nPlease request a invite from one of our members.");

if ($assoc["receiver"]!=0)
stderr("Error","Invite already taken.\nPlease request a new one from your inviter.");
$email1= $assoc["email"];
if ($email!= $email1)
stderr('Error', 'The e-mail address <b>' . htmlspecialchars($email) . '</b> does not correspond to your invite code.<br />Please use the Email Address the Invite Code was sent to .');

$secret = mksecret();
    $wantpasshash = make_passhash( $secret, md5($wantpassword) );
    $editsecret = ( !$arr[0] ? "" : make_passhash_login_key() );
    $wanthintanswer = md5($hintanswer);

$new_user = mysql_query("INSERT INTO users (username, passhash, secret, editsecret, passhint, hintanswer, invitedby, email, ". (!$arr[0]?"class, ":"") ."added) VALUES (" .
implode(",", array_map("sqlesc", array($wantusername, $wantpasshash, $secret, $editsecret, $passhint, $wanthintanswer, (int)$assoc['sender'], $email))).
", ". (!$arr[0]?UC_SYSOP.", ":""). "'".  time() ."')");
if (!$new_user) {
if (mysql_errno() == 1062)
stderr("Error","Username already exists!");
stderr("Error","borked");
}
//===send PM to inviter
$sender = $assoc["sender"];
$added = sqlesc(time());
$msg = sqlesc("Hey there [you] ! :wave:\nIt seems that someone you invited to {$TBDEV['site_name']} has arrived ! :clap2: \n\n Please go to your [url={$TBDEV['baseurl']}/invite.php]Invite page[/url] to confirm them so they can log in.\n\ncheers\n");
$subject = sqlesc("Someone you invited has arrived!");
mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $sender, $msg, $added)") or sqlerr(__FILE__, __LINE__);
//////////////end/////////////////////
$id = mysql_insert_id();
// send welcome message start
$added = time();
$welcome = sqlesc("{$lang['gl_welcome']}");
mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $id, $welcome, $added)") or sqlerr(__FILE__, __LINE__);
// send welcome message end
mysql_query('UPDATE invite_codes SET receiver = ' . sqlesc($id) . ', status = "Confirmed" WHERE sender = ' . sqlesc((int)$assoc['sender']). ' AND code = ' . sqlesc($invite)) or sqlerr(__FILE__, __LINE__);
write_log('User account '.htmlspecialchars($wantusername).' was created!');
stderr('Error','Signup successfull', 'Your inviter needs to confirm your account now!');
?>