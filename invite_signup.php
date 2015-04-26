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
dbconn();


$lang = array_merge ( load_language('global'), load_language('signup') );

$HTMLOUT='';
$HTMLOUT .="<script type='text/javascript' src='scripts/jquery.js'></script>
<script type='text/javascript' src='scripts/jquery.pstrength-min.1.2.js'>
</script>
<script type='text/javascript'>
$(function() {
$('.password').pstrength();
});
</script>";

$res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res);
if ($arr[0] >= $TBDEV['maxusers'])
stderr("Sorry", "The current user account limit (" . number_format($TBDEV['maxusers']) . ") has been reached. Inactive accounts are pruned all the time, please check back again later...");

$HTMLOUT .="
<script type='text/javascript' src='scripts/check.js'></script>

<p>{$lang['signup_cookies']}</p>

<form method='post' action='{$TBDEV['baseurl']}/take_invite_signup.php'>
<table border='1' cellspacing='0' cellpadding='10'>
<tr><td align='right' class='heading'>Desired username:</td><td align='left'><input class='user' type='text' size='40' name='wantusername' style='border: 1px dashed #97BCC2;'/></td></tr>
<tr><td align='right' class='heading'>Pick a password:</td><td align='left'><input class='pass & password & keyboardInput' type='password' size='40' name='wantpassword' value='' style='border: 1px dashed #97BCC2;' /></td></tr>
<tr><td align='right' class='heading'>Enter password again:</td><td align='left'><input class='pass & keyboardInput' type='password' size='40' name='passagain' value='' style='border: 1px dashed #97BCC2;'/></td></tr>
<tr><td align='right' class='heading'>Enter invite-code:</td><td align='left'><input type='text' size='40' name='invite' style='border: 1px dashed #97BCC2;'/></td></tr>
<tr valign='top'><td align='right' class='heading'>Email address:</td><td align='left'><input class='email' type='text' size='40' name='email' style='border: 1px dashed #97BCC2;'/>
<table width='250' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'><font class='small'>The email address must be valid.
You will receive a confirmation email which you need to respond to. The email address won't be publicly shown anywhere.</font></td></tr>
</table>";
//==Passhint
     $passhint="";
     $questions = array(
            array("id"=> "1", "question"=> "{$lang['signup_q1']}"),
                        array("id"=> "2", "question"=> "{$lang['signup_q2']}"),
                        array("id"=> "3", "question"=> "{$lang['signup_q3']}"),
                        array("id"=> "4", "question"=> "{$lang['signup_q4']}"),
                        array("id"=> "5", "question"=> "{$lang['signup_q5']}"),
                        array("id"=> "6", "question"=> "{$lang['signup_q6']}"));
                  foreach($questions as $sph){  
                  $passhint .= "<option value='".$sph['id']."'>".$sph['question']."</option>\n"; 
                  }
                  $HTMLOUT .= "<tr><td align='right' class='heading'>{$lang['signup_select']}</td><td align='left'><select name='passhint' style='border: 1px dashed #97BCC2;'>\n$passhint\n</select></td></tr>
                  <tr><td align='right' class='heading'>{$lang['signup_enter']}</td><td align='left'><input type='text' size='40'  name='hintanswer' style='border: 1px dashed #97BCC2;'/><br /><font class='small'>{$lang['signup_this_answer']}<br />{$lang['signup_this_answer1']}</font></td></tr>
</td></tr>
<tr><td align='right' class='heading'></td><td align='left'><input type='checkbox' name='rulesverify' value='yes' /> I will read the site rules page.<br />
<input type='checkbox' name='faqverify' value='yes' /> I agree to read the FAQ before asking questions.<br />
<input type='checkbox' name='ageverify' value='yes' /> I am at least 13 years old.</td></tr>
<tr><td colspan='2' align='center'><input type='submit' value='Sign up! (PRESS ONLY ONCE)' style='height: 25px' /></td></tr>
</table>
</form>";

print stdhead('Invite Signup') . $HTMLOUT . stdfoot();
die;
?>