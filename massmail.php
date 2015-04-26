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

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
$lang = array_merge( load_language('global'));

dbconn();
loggedinorreturn();
staffonly();

stdhead();
if( (get_user_class() < UC_SYSOP) || ($CURUSER['id'] > '1')) /* sysop id check */ { 
	stderr("Error", "You have no rights to be here"); 
}	

if (!isset($_POST['submit'])):

$HTMLOUT = '';

$HTMLOUT .= "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
$HTMLOUT .= '
<table align="center" bgcolor="#D6DEE7" width="700" border="0" cellpadding="0" cellspacing="0" class="main">
<tr><td align="center">
<b><h2>MAILING LIST ADMIN</h2></b>
</td></tr>
<tr><td align="center" class="indexbottom">
Send message to:<br />
<select name="to" size="1" style="background-color: #F7F7F7">
<option selected value="all">All Users</option>
<option value="dt">Inactive Users (last login < 30 days)</option>
<option value="class_1">Users </option>
<option value="class_2">Power Users</option>
<option value="class_3">VIPs </option>
<option value="class_4">Uploaders </option>
<option value="class_5">Forum Moderators </option>
<option value="class_6">Moderators </option>
<option value="class_7">Admin </option>
<option value="class_8">Sysop </option>
<option value="class_9">Staff Leader </option>
</select>
</td></tr>
<tr><td align="center" class="indexbottom">
Title or Subject:<br /> 
<input name="subject" type=text maxlength=100 size=140 >
</td></tr>
<tr><td align="center" class="indexbottom">
Message:<br />
<textarea wrap name="message" rows=20 cols=145></textarea>
<br />
<input type=submit name="submit" value="SUBMIT">
</td></tr></table></form>';

else:

    $to = '';

    switch ($_POST['to']) {
        case 'class_1': $to = "FROM users WHERE class = '1' ";
            break;
        case 'class_2': $to = "FROM users WHERE class = '2' ";
            break;
        case 'class_3': $to = "FROM users WHERE class = '3' ";
            break;
        case 'class_4': $to = "FROM users WHERE class = '4' ";
            break;
        case 'class_5': $to = "FROM users WHERE class = '5' ";
            break;
        case 'class_6': $to = "FROM users WHERE class = '6' ";
            break;
        case 'class_7': $to = "FROM users WHERE class = '7' ";
            break;
        case 'class_8': $to = "FROM users WHERE class = '8' ";
            break;
        case 'class_9': $to = "FROM users WHERE class = '9' ";
            break;
        case 'dt': $to = "FROM users WHERE last_login=$dt ";
            break;
        case 'all': $to = "FROM users ";
            break;
    }// end switch

  $subject = $_POST['subject'];
  $message = $_POST['message'];


   $x = 1;
   $hold = 50; // quantity of emails sent before 3 sec delay
   $days = 30;
   $dt = (time() - ($days * 86400));
   
   $emails = mysql_query("SELECT email $to ");

while ($sendemail = mysql_fetch_array($emails)) {
   $email = $sendemail["email"];
   mail($email, $subject,
   $message, "From:".$TBDEV["site_name"]." <".$TBDEV["site_email"].">");

   $x++;
    if($x == $hold) { // When $x is equal to $hold, a 3 sec delay will occur avoiding php to timeout
    sleep(3);
    $x = 0;
    }
} // end of while loop


$HTMLOUT .= '<br /><br /><br /><center><font color=blue size=+3>SUCCESS!</font></center>';

endif; 

print stdhead("Mass Mail Messages", false) . $HTMLOUT . stdfoot();

?>