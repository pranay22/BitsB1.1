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

if (empty($_GET['wantusername'])) {
die('Fuck off - You cant post nothing you fool !');
}
sleep(1);
require_once("include/bittorrent.php");
dbconn();

$HTMLOUT ="";

$lang = array_merge( load_language('global'), load_language('takesignup') );

function validusername($username)
{
global $lang;
if ($username == "")
return false;
$namelength = strlen($username);
if( ($namelength < 3) OR ($namelength > 32) )
{
$HTMLOUT ="";
$HTMLOUT .= "<font color='#cc0000'>{$lang['takesignup_username_length']}</font>";
print $HTMLOUT;
exit();
}
// The following characters are allowed in user names
$allowedchars = $lang['takesignup_allowed_chars'];
for ($i = 0; $i < $namelength; ++$i)
{
if (strpos($allowedchars, $username[$i]) === false)
return false;
}
return true;
}

if (!validusername($_GET["wantusername"])){
$HTMLOUT .= "<font color='#cc0000'>{$lang['takesignup_allowed_chars']}</font>"; 
print $HTMLOUT;
exit();
}

if (strlen($_GET["wantusername"]) > 12){
$HTMLOUT .= "<font color='#cc0000'>{$lang['takesignup_username_length']}</font>";
print $HTMLOUT;
exit();
}

$checkname = sqlesc($_GET["wantusername"]);
$sql = "SELECT username FROM users WHERE username = $checkname";
$result = mysql_query($sql);
$numbers = mysql_num_rows($result); 

if($numbers > 0) 
{
while( $namecheck = mysql_fetch_assoc($result) ) { 
$HTMLOUT .= "<font color='#cc0000'><font size='2'><b><img src='{$TBDEV['pic_base_url']}aff_cross.gif' alt='Cross' title='Username Not Available' align='absmiddle' />Sorry... Username - ".htmlspecialchars($namecheck["username"])." is already in use.</font>"; 
} 
}
else 
{
$HTMLOUT .= "<font color='#33cc33'><font size='2'><b><img src='{$TBDEV['pic_base_url']}aff_tick.gif' alt='Tick' title='Username Available' align='absmiddle' /> Username Available</font>";
}

print $HTMLOUT;
exit();
?>