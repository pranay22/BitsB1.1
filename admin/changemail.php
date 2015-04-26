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

if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
}

require_once ("include/bittorrent.php");
require_once ("include/html_functions.php");
require_once ("include/user_functions.php");
$lang = array_merge( $lang , load_language('ad_changem'));
staffonly();
if ($CURUSER['class'] < UC_ADMINISTRATOR)
stderr("{$lang['text_error']}", "{$lang['text_denied']}");



$HTMLOUT = "";
function email_exists ($email){
$query = sql_query ('SELECT email FROM users WHERE email=' . sqlesc ($email) . ' LIMIT 1');
if (1 <= mysql_num_rows ($query)){
return false;
}
return true;
}
function validusername ($username){
if (!preg_match ('|[^a-z\\|A-Z\\|0-9]|', $username)){
return true;
}
return false;
}
function check_banned_emails ($email){	
$expl = explode("@", $email);
$wildemail = "*@".$expl[1];
$res = sql_query("SELECT id, comment FROM bannedemails WHERE email = ".sqlesc($email)." OR email = ".sqlesc($wildemail)."") or sqlerr(__FILE__, __LINE__);
if ($arr = mysql_fetch_assoc($res)){
stderr("{$lang['text_error']}","{$lang['text_banned']} $arr[comment]", false);
}
}
//commented line not working
//if ($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST'){
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
if (($_POST['username'] == '' OR $_POST['email'] == '')){
stderr ("{$lang['text_error']}", "{$lang['text_missing']}");
}

$username = $_POST['username'];

if (!validusername ($username)){
stderr ("{$lang['text_error']}", "{$lang['text_error']}");
}

$username = sqlesc ($username);
	
$email = htmlspecialchars (trim ($_POST['email']));

if ((!validemail($email) || !email_exists ($email))){
stderr("{$lang['text_error']}", "{$lang['text_taken']}");
}
if (check_banned_emails ($email)){
stderr ("{$lang['text_error']}", "{$lang['text_banned']}");
}

$getuser = sql_query ("SELECT email FROM users WHERE username=" . $username) OR sqlerr (__FILE__, 56);
$user1 = mysql_fetch_array ($getuser);
$modcomment = get_date( time(), 'DATE', 1 ) . ' - Email Changed to ' . $email . ' from ' . $user1['email'] . ' by ' . $CURUSER['username'] . '' . PHP_EOL;
$email = sqlesc ($email);
sql_query ('UPDATE users SET email=' . $email . ', modcomment=CONCAT(' . sqlesc ($modcomment . '') . ', modcomment) WHERE username=' . $username) OR sqlerr(__FILE__, __LINE__);
$res = sql_query ('SELECT id FROM users WHERE username=' . $username);
$arr = mysql_fetch_array ($res);
if (empty ($arr)){
stderr ("{$lang['text_error']}", "{$lang['text_able']}");
}
header ('' . 'Location: userdetails.php?id=' . $arr['0']);
exit ();
}
  
$HTMLOUT .=begin_frame(''.$lang['text_change'].'',true);
$HTMLOUT .= '<form method="post" action="admin.php?action=changemail">
<input type="hidden" name="act" value="changemail"/>
<table border="1" cellspacing="0" cellpadding="5" width="500">
<tr><td class="rowhead">'.$lang['text_user'].'</td><td><input type="text" name="username" size="40"/></td></tr>
<tr><td class="rowhead">'.$lang['text_new'].'</td><td><input type="text" name="email" size="40" /> 
<input type="submit" value="'.$lang['text_update'].'" class="btn"/></td></tr>
</table>
</form>';
$HTMLOUT .=end_frame();

print stdhead(''.$lang['text_error'].'').$HTMLOUT.stdfoot();
?>