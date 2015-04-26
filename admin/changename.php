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
$lang = array_merge($lang, load_language('ad_changen'));
staffonly();
if ($CURUSER['class'] < UC_ADMINISTRATOR)
stderr("{$lang['text_error']}", "{$lang['text_denied']}");


$HTMLOUT ='';
function validusername($username)
{
global $lang;
    
if ($username == "")
return false;
    
$namelength = strlen($username);
    
if( ($namelength < 3) || ($namelength > 32) ){
stderr($lang['text_error'], $lang['text_username_length']);
}
// The following characters are allowed in user names
$allowedchars = $lang['text_allowed_chars'];
 
for ($i = 0; $i < $namelength; ++$i)
{
if (strpos($allowedchars, $username[$i]) === false)
return false;
} 
return true;
}
function username_exists ($username){
$query = sql_query ('SELECT username FROM users WHERE username=' . sqlesc ($username) . ' LIMIT 1');
if (1 <= mysql_num_rows ($query)){
return false;
}
return true;
}

//commented line not working
//if ($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST'){
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
if ((($_POST['username'] == '' || $_POST['id'] == '') || !is_valid_id ($_POST['id']))){
stderr ("{$lang['text_error']}", "{$lang['text_missing']}");
};
$sure = isset($_POST["sure"]) ? $_POST["sure"] : "";
$id = (int)$_POST['id'];
$username = $_POST['username'];
if ((!validusername ($username) || !username_exists ($username))){
stderr ("{$lang['text_error']}", "{$lang['text_taken']}");
}
$getuser = sql_query ('SELECT id,username FROM users WHERE id=' . $id) OR sqlerr(__FILE__, __LINE__);
$user1 = mysql_fetch_array ($getuser);
$modcomment = get_date( time(), 'DATE', 1 ) . ' - Username Changed to ' . $username . ' from ' . $user1['username'] . ' by ' . $CURUSER['username'] . '' . PHP_EOL;
$username = sqlesc ($username);
if ($sure == 'yes'){
sql_query ('UPDATE users SET username=' . $username . ', modcomment=CONCAT(' . sqlesc ($modcomment . '') . ', modcomment)  WHERE id=' .sqlesc($id));
header ('Location: userdetails.php?id=' . $id);
exit ();
}else{
$get_user = sql_query ('SELECT id,username FROM users WHERE id=' . $id) OR sqlerr(__FILE__, __LINE__);
$user = mysql_fetch_array ($get_user);
if (empty ($user)){
stderr ("{$lang['text_error']}", "{$lang['text_no']}");
}
$HTMLOUT .=begin_frame("".$lang['text_sanity']."",true).'
<form method="post" action="'. $_SERVER['PHP_SELF'].'?action=changename">
<input type="hidden" name="act" value="changeusername"/>		
<table border="1" cellspacing="0" cellpadding="5" width="500">
<tr><td class="rowhead">'.$lang["text_id"].'</td><td>
<input type="text" name="id" value="' . $id . '" /> (' . htmlspecialchars ($user['username'] ). ')
</td></tr>
<tr><td class="rowhead">'.$lang["text_new"].'</td><td>
<input type="text" name="username" value="' . htmlspecialchars (str_replace ('\'', '', $username)) . '" />
<input type="checkbox" name="sure" value="yes" style="vertical-align: middle;" checked="checked" /> <input type="submit" value="'.$lang["text_sure"].'" class="btn"/>
</td></tr>
</table>
</form>'.end_frame();
print  stdhead(''.$lang["text_change"].'').$HTMLOUT.stdfoot ();
exit ();
}
}
$HTMLOUT.= begin_frame(''.$lang["text_change"].'',true).'
<form method="post" action="'. $_SERVER['PHP_SELF'].'?action=changename">
<input type="hidden" name="act" value="changeusername"/>
<table border="1" cellspacing="0" cellpadding="5" width="500">
<tr><td class="rowhead">'.$lang["text_id"].'</td><td>
<input type="text" name="id" />
</td></tr>
<tr><td class="rowhead">'.$lang["text_new"].'</td><td>
<input type="text" name="username" />
<input type="submit" value="'.$lang["text_update"].'" class="btn"/>
</td></tr>
</table>
</form>'.end_frame();
print stdhead(''.$lang["text_change"].'').$HTMLOUT.stdfoot ();
?>