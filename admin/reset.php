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

require_once 'include/password_functions.php';
require_once 'include/user_functions.php';
staffonly();

if ($CURUSER['class'] < UC_ADMINISTRATOR)
    stderr("{$lang['text_error']}", "{$lang['text_denied']}");
    
//== Reset Lost Password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $secret = mksecret();
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newpassword = "";
    for($i = 0;$i < 10;$i++)
    $newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];
    $passhash =  make_passhash( $secret, md5($newpassword) ) ;
    $res = sql_query('UPDATE users SET secret=' . sqlesc($secret) . ', passhash=' . sqlesc($passhash) . ' WHERE username=' . sqlesc($username) . ' AND class<' . $CURUSER['class']) or sqlerr();
    if (mysql_affected_rows() != 1)
        stderr('Error', 'Password not updated. User not found or higher/equal class to yourself');
    write_log('passwordreset', 'Password reset for ' . $username . ' by ' . $CURUSER['username']);
    stderr('Success', 'The password for account <b>' . htmlspecialchars($username) . '</b> is now <b>' . htmlspecialchars($newpassword) . '</b>.');
}

$HTMLOUT ="";

$HTMLOUT .="<h1>Reset User's Lost Password</h1>
<form method='post' action='admin.php?action=reset'>
<table border='1' cellspacing='0' cellpadding='5'>
<tr>
<td class='rowhead'>User name</td><td>
<input size='40' name='username' /></td></tr>
<tr>
<td colspan='2'>
<input type='submit' class='btn' value='reset' />
</td>
</tr>
</table></form>";

print stdhead("Reset Password") . $HTMLOUT . stdfoot();
?>