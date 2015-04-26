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
require_once "include/password_functions.php";   //==Uncomment if req.
require_once "include/password_functions.php";
ini_set('session.use_trans_sid', '0');

dbconn();

$lang = array_merge( load_language('global'), load_language('passhint') );

$HTMLOUT = '';

global $CURUSER;

//IE alert
    $browser = $_SERVER['HTTP_USER_AGENT'];
   if(preg_match("/MSIE/i",$browser))//browser is IE
   {
    $HTMLOUT .="<div class='notification warning2 autoWidth' style='width: 957px;'><span></span>
         <div class='text'><p style='font-size: 12px;'><strong>Warning!</strong>It appears as though you are running Internet Explorer, this site was <b>NOT</b> intended to be viewed with internet explorer and chances are it will not look right and may not even function correctly.
    You should consider downloading a real browser, Firefox from <a href='http://www.mozilla.com/firefox'><font color=#BB7070><b>HERE</b></font></a>. <strong>Get a SAFER browser !</strong></p>
         </div>
        </div>";
   }
   //end IE alert
   
if ($CURUSER) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error1']}");

$step = (isset($_GET["step"]) ? (int)$_GET["step"] : (isset($_POST["step"]) ? (int)$_POST["step"] : ''));

if ($step == '1') {
	
if ($_SERVER["REQUEST_METHOD"] == "POST") {		
	
if (!mkglobal("email:captcha")) 
die();

if(empty($captcha) || $_SESSION['captcha_id'] != strtoupper($captcha)){ 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error2']}"); 
}

if (empty($email)) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_invalidemail']}");

if (!validemail($email)) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_invalidemail1']}");
	
$check = mysql_query('SELECT id, status, passhint, hintanswer FROM users WHERE email = ' . sqlesc($email)) or sqlerr(__FILE__,__LINE__); 
$assoc = mysql_fetch_assoc($check) or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_notfound']}"); 

if (empty($assoc['passhint']) || empty($assoc['hintanswer'])) 
{ 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error3']}"); 
} 
if ($assoc['status'] != 'confirmed') 
{ 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error4']}"); 
}else { 
$HTMLOUT .= "<form method='post' action='".$_SERVER['PHP_SELF']."?step=2'>
<table border='1' cellspacing='0' cellpadding='10'>
<tr>
<td class='rowhead'>{$lang['main_question']}</td>";

$id[1] = '/1/';
$id[2] = '/2/';
$id[3] = '/3/';
$id[4] = '/4/';
$id[5] = '/5/';
$id[6] = '/6/';
$question[1] = "{$lang['main_question1']}";
$question[2] = "{$lang['main_question2']}";
$question[3] = "{$lang['main_question3']}";
$question[4] = "{$lang['main_question4']}";
$question[5] = "{$lang['main_question5']}";
$question[6] = "{$lang['main_question6']}";
$passhint = preg_replace($id, $question, (int)$assoc['passhint']);
$HTMLOUT .= "<td><i><b>{$passhint} ?</b></i>
<input type='hidden' name='id' value='".(int)$assoc['id']."' /></td></tr>
<tr><td class='rowhead'>{$lang['main_sec_answer']}</td>
<td><input type='text' size='40' name='answer' /></td></tr><tr><td colspan='2' align='center'><input type='submit' value='{$lang['main_next']}' class='btn' />
</td></tr></table></form>";
print stdhead('Reset Lost Password'). $HTMLOUT . stdfoot();
} 
} 
}

elseif ($step == '2') {
if (!mkglobal('id:answer')) die();	

$select = mysql_query('SELECT id, username, hintanswer FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__); 
$fetch = mysql_fetch_assoc($select);

if (!$fetch) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error5']}");

if (empty($answer)) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error6']}");

if ($fetch['hintanswer'] != md5($answer)) { 

$ip = getip();
$useragent = $_SERVER['HTTP_USER_AGENT'];

$msg = "".htmlspecialchars($fetch['username']).", on ".get_date( time(), '', 1,0 ) . ", {$lang['main_message']}"."\n\n{$lang['main_message1']} " . $ip . " (". @gethostbyaddr($ip) . ")". "\n {$lang['main_message2']} ".$useragent."\n\n {$lang['main_message3']}\n {$lang['main_message4']}\n";
$subject ="Failed password reset";
mysql_query('INSERT INTO messages (receiver, msg, subject, added) VALUES (' .
	      sqlesc((int)$fetch['id']) . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ', ' . sqlesc(time()) . ')') or sqlerr(__FILE__, __LINE__);

stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error7']}"); 
}else { 
$HTMLOUT .= "<form method='post' action='?step=3'>
<table border='1' cellspacing='0' cellpadding='10'>
<tr><td class='rowhead'>{$lang['main_new_pass']}</td>
<td><input type='password' size='40' name='newpass' /></td></tr>
<tr><td class='rowhead'>{$lang['main_new_pass_confirm']}</td><td><input type='password' size='40' name='newpassagain' /></td></tr>
<tr><td colspan='2' align='center'><input type='submit' value='{$lang['main_changeit']}' class='btn' />
<input type='hidden' name='id' value='".(int)$fetch['id']."' /></td></tr></table></form>";
print stdhead('Reset Lost Password'). $HTMLOUT . stdfoot(); 
} 
} 

elseif ($step == '3') {
if (!mkglobal('id:newpass:newpassagain')) die();	

$select = mysql_query('SELECT id, editsecret FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__); 
$fetch = mysql_fetch_assoc($select) or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error8']}");
	
if (empty($newpass)) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error9']}"); 
if ($newpass != $newpassagain) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error10']}"); 
if (strlen($newpass) < 6) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error11']}"); 
if (strlen($newpass) > 40) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error12']}");
	
$secret = mksecret();
$newpassword =  make_passhash( $secret, md5($newpass) ) ;

mysql_query('UPDATE users SET secret = ' . sqlesc($secret) . ', editsecret = "", passhash=' . sqlesc($newpassword) . ' WHERE id = ' . sqlesc($id) . ' AND editsecret = ' . sqlesc($fetch["editsecret"]));

if (!mysql_affected_rows()) 
stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error13']}");
else 
stderr("{$lang['stderr_successhead']}","{$lang['stderr_error14']} <a href='{$TBDEV['baseurl']}/login.php' class='altlink'><b>{$lang['stderr_error15']}</b></a> {$lang['stderr_error16']}", FALSE); 
}else {


$HTMLOUT .= "<script type='text/javascript' src='./scripts/captcha.js'></script>
<p>{$lang['main_body']}</p>
<br />
<form method='post' action='".$_SERVER['PHP_SELF']."?step=1'>
<table border='1' cellspacing='0' cellpadding='5'>
<tr>
<td class='rowhead'>{$lang['main_email_add']}</td><td><input class='email' type='text' size='40' name='email' style='border: 1px dashed #97BCC2;'/></td></tr>
<tr><td class='rowhead'>{$lang['main_captcha']}</td>
<td><div id='captchaimage'><a href='".$_SERVER['PHP_SELF']."' onclick='refreshimg(); return false;'><img class='cimage' src='./captcha/GD_Security_image.php?".time()."' border='0' alt='Click to refresh image' title='Click to refresh image' /></a></div></td></tr>
<tr><td class='rowhead'>{$lang['main_pin']}</td><td>
<input type='text' maxlength='6' name='captcha' id='captcha' onblur='check(); return false;' style='border: 1px dashed #97BCC2;'/></td></tr>
<tr><td colspan='2' align='center'><input type='submit' value='{$lang['main_recover']}' style='height: 25px' /></td></tr></table>
</form>";
print stdhead('Reset Lost Password'). $HTMLOUT . stdfoot();
}
?>