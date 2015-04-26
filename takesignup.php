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

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
require_once "include/password_functions.php";

dbconn();

    $lang = array_merge( load_language('global'), load_language('takesignup') );
    if(!$TBDEV['openreg'])
    stderr('Sorry', 'Invite only - Signups are closed presently!');
    
    $res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res);
    $gender = $_POST["gender"];
    
    if ($arr[0] >= $TBDEV['maxusers'])
      stderr($lang['takesignup_error'], $lang['takesignup_limit']);

//if (!mkglobal("wantusername:wantpassword:passagain:email:captcha"))
//	die();
    foreach( array('wantusername','wantpassword','passagain','email','captcha','gender','submitme','passhint','hintanswer') as $x )
    {
      if( !isset($_POST[ $x ]) )
      {
        stderr($lang['takesignup_user_error'], $lang['takesignup_form_data']);
      }
      
      ${$x} = $_POST[ $x ];
    }
    
    if ($submitme != 'B')
    stderr('Ha Ha', 'You Missed, You plonker !');
    
    session_start();
    
    if(empty($captcha) || $_SESSION['captcha_id'] != strtoupper($captcha))
    {
        header('Location: signup.php');
        exit();
    }


function validusername($username)
  {
    global $lang;
    if ($username == "")
      return false;
    $namelength = strlen($username);
    if( ($namelength < 3) OR ($namelength > 32) ){
      stderr($lang['takesignup_user_error'], $lang['takesignup_username_length']);
    }
    // The following characters are allowed in user names
    $allowedchars = $lang['takesignup_allowed_chars'];
    for ($i = 0; $i < $namelength; ++$i){
	  if (strpos($allowedchars, $username[$i]) === false)
	    return false;
    }
    return true;
  }

//banned email mod
function check_banned_emails ($email) {
global $lang;
$expl = explode("@", $email);
$wildemail = "*@".$expl[1];
/* Ban emails by x0r @tbdev.net */
$res = mysql_query("SELECT id, comment FROM bannedemails WHERE email = ".sqlesc($email)." OR email = ".sqlesc($wildemail)."") or sqlerr(__FILE__, __LINE__);
if ($arr = mysql_fetch_assoc($res))
stderr("{$lang['takesignup_user_error']}","{$lang['takesignup_bannedmail']}$arr[comment]", false);
}
//end banned email mod

/*
function isportopen($port)
{
	$sd = @fsockopen($_SERVER["REMOTE_ADDR"], $port, $errno, $errstr, 1);
	if ($sd)
	{
		fclose($sd);
		return true;
	}
	else
		return false;
}

function isproxy()
{
	$ports = array(80, 88, 1075, 1080, 1180, 1182, 2282, 3128, 3332, 5490, 6588, 7033, 7441, 8000, 8080, 8085, 8090, 8095, 8100, 8105, 8110, 8888, 22788);
	for ($i = 0; $i < count($ports); ++$i)
		if (isportopen($ports[$i])) return true;
	return false;
}
*/

    if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($passhint) || empty($hintanswer))
      stderr($lang['takesignup_user_error'], $lang['takesignup_blank']);
    
    if(!blacklist($wantusername))
      stderr($lang['takesignup_user_error'],sprintf($lang['takesignup_badusername'],htmlspecialchars($wantusername)));
    /*
    if (strlen($wantusername) > 12)
      bark("Sorry, username is too long (max is 12 chars)");
    */
    if ($wantpassword != $passagain)
      stderr($lang['takesignup_user_error'], $lang['takesignup_nomatch']);

    if (strlen($wantpassword) < 6)
      stderr($lang['takesignup_user_error'], $lang['takesignup_pass_short']);

    if (strlen($wantpassword) > 40)
      stderr($lang['takesignup_user_error'], $lang['takesignup_pass_long']);

    if ($wantpassword == $wantusername)
      stderr($lang['takesignup_user_error'], $lang['takesignup_same']);

    if (!validemail($email))
      stderr($lang['takesignup_user_error'], $lang['takesignup_validemail']);

    if (!validusername($wantusername))
      stderr($lang['takesignup_user_error'], $lang['takesignup_invalidname']);

    // make sure user agrees to everything...
    if ($_POST["rulesverify"] != "yes" || $_POST["faqverify"] != "yes" || $_POST["ageverify"] != "yes")
      stderr($lang['takesignup_failed'], $lang['takesignup_qualify']);

    // check if email addy is already in use
    $a = (@mysql_fetch_row(@mysql_query("select count(*) from users where email='$email'"))) or die(mysql_error());
    if ($a[0] != 0)
      stderr($lang['takesignup_user_error'], $lang['takesignup_email_used']);
      
    //=== check if ip addy is already in use 
   $c = (@mysql_fetch_row(@mysql_query("select count(*) from users where ip='" . $_SERVER['REMOTE_ADDR'] . "'"))) or die(mysql_error()); 
   if ($c[0] != 0) 
    stderr("Error", "The ip " . $_SERVER['REMOTE_ADDR'] . " is already in use. We only allow one account per ip address.");

    // TIMEZONE STUFF
    if(isset($_POST["user_timezone"]) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone']))
    {
    $time_offset = sqlesc($_POST['user_timezone']);
    }
    else
    { $time_offset = isset($TBDEV['time_offset']) ? sqlesc($TBDEV['time_offset']) : '0'; }
    // have a stab at getting dst parameter?
    $dst_in_use = localtime(time() + ($time_offset * 3600), true);
    // TIMEZONE STUFF END

    $secret = mksecret();
    $wantpasshash = make_passhash( $secret, md5($wantpassword) );
    $editsecret = ( !$arr[0] ? "" : make_passhash_login_key() );
    $wanthintanswer = md5($hintanswer);
    check_banned_emails($email);

    $ret = mysql_query("INSERT INTO users (username, passhash, gender, secret, editsecret, passhint, hintanswer, email, status, ". (!$arr[0]?"class, ":"") ."added, time_offset, dst_in_use) VALUES (" .
		implode(",", array_map("sqlesc", array($wantusername, $wantpasshash, $gender, $secret, $editsecret, $passhint, $wanthintanswer, $email, (!$arr[0]?'confirmed':'pending')))).
		", ". (!$arr[0]?UC_SYSOP.", ":""). "". time() ." , $time_offset, {$dst_in_use['tm_isdst']})");
  
  $message = "Welcome New {$TBDEV['site_name']} Member : - " . htmlspecialchars($wantusername) . "";

    if (!$ret) 
    {
      if (mysql_errno() == 1062)
        stderr($lang['takesignup_user_error'], $lang['takesignup_user_exists']);
      stderr($lang['takesignup_user_error'], $lang['takesignup_fatal_error']);
    }

    $id = mysql_insert_id();

//write_log("User account $id ($wantusername) was created");
    
    //New member automated PM    
    $added = sqlesc(time());
    $subject = sqlesc("Welcome!");
    $welcome = sqlesc("{$lang['takesignup_welcome']}");
    sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES(0, $subject, $id, $welcome, $added)") or sqlerr(__FILE__, __LINE__);
    //End new member PM

    $psecret = $editsecret; //md5($editsecret);
    autoshout($message);
    
    $body = str_replace(array('<#SITENAME#>', '<#USEREMAIL#>', '<#IP_ADDRESS#>', '<#REG_LINK#>'),
                        array($TBDEV['site_name'], $email, $_SERVER['REMOTE_ADDR'], "{$TBDEV['baseurl']}/confirm.php?id=$id&secret=$psecret"),
                        $lang['takesignup_email_body']);

    if($arr[0])
      mail($email, "{$TBDEV['site_name']} {$lang['takesignup_confirm']}", $body, "{$lang['takesignup_from']} {$TBDEV['site_email']}");
    else 
      logincookie($id, $wantpasshash);
    
    header("Refresh: 0; url=ok.php?type=". (!$arr[0]?"sysop":("signup&email=" . urlencode($email))));

?>