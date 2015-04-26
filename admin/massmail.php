<?php 
/**
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.1
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
|   Massmail System v0.8
+------------------------------------------------
**/
 
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
 
require_once "include/user_functions.php"; 
staffonly();
    
   $lang = array_merge($lang, load_language('ad_massmail') ); 
 
if (get_user_class() < UC_SYSOP){  
        stderr("{$lang['email_error']}", "{$lang['email_error2']}");  
}        
 
if ($_SERVER["REQUEST_METHOD"] == "POST")  
{ 
 
        $to = ''; 
 
        switch($_POST['to']) { 
         
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
   $days = 42; 
   $dt = (time() - ($days * 86400)); 
    
   $emails = sql_query("SELECT email $to "); 
 
while ($sendemail = mysql_fetch_array($emails)) { 
   $email = $sendemail["email"]; 
   $success = mail($email, $subject,$message, "{$lang['email_from']}{$TBDEV["site_name"]} {$TBDEV["site_email"]}"); 
    
 
   $x++; 
        if($x == $hold) { // When $x is equal to $hold, a 3 sec delay will occur avoiding php to timeout 
        sleep(3); 
        $x = 0; 
        } 
} // end of while loop 
 
if ($success) 
        stderr("{$lang['email_success']}", "{$lang['email_queued']}"); 
        else 
        stderr("{$lang['email_error']}", "{$lang['email_failed']}"); 
         
         
 } 
  
 $HTMLOUT = ''; 
 
$HTMLOUT .= "<form method='post' action='admin.php?action=massmail'>"; 
$HTMLOUT .= " 
<table align='center'  width='700' border='0' cellpadding='0' cellspacing='0' class='main'> 
<tr><td align='center' class='colhead'><b><h2>{$lang['email_to']}</h2></b></td></tr> 
 
<tr><td align='center' style='height:25px;'>{$lang['email_send']} 
 
<select name='to' size='1' style='background-color: #F7F7F7'> 
<option value='all'> {$lang['email_all']} </option> 
<option value='dt'> {$lang['email_inactive']}</option> 
<option value='class_1'> {$lang['email_users']}  </option> 
<option value='class_2'> {$lang['email_power_users']} </option> 
<option value='class_3'> {$lang['email_vip']} </option> 
<option value='class_4'> {$lang['email_uploaders']}  </option> 
<option value='class_5'> {$lang['email_forum_moderators']}  </option> 
<option value='class_6'> {$lang['email_moderators']}  </option> 
<option value='class_7'> {$lang['email_administrators']}  </option> 
<option value='class_8'> {$lang['email_sys_op']}  </option> 
<option value='class_9'> {$lang['email_staff_lead']}  </option> 
</select></td></tr> 
 
<tr><td align='center' style='height:25px;'>{$lang['email_subject']}<input name='subject' type='text' maxlength='50' size='70' /></td></tr> 
 
<tr><td align='center'>{$lang['email_message']}<br /><textarea wrap name='message' rows='20' cols='145'></textarea></td></tr> 
<tr><td align='center' style='height:35px;'><input type='submit' name='submit' value='SUBMIT' style='color:green;' /></td></tr> 
 
</table></form>"; 
 
print stdhead("{$lang['email_mass']}", false) . $HTMLOUT . stdfoot(); 
 
?> 