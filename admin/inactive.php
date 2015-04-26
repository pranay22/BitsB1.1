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
|   Inactive Users emailing system v0.7
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

require_once('include/user_functions.php');
staffonly();

$HTMLOUT ="";

$lang = array_merge($lang, load_language('inactive') );
/** new way **/
if ($CURUSER['class'] < UC_ADMINISTRATOR)
stderr('Permission Denied', '...');
/** configure inactivity timer **/
$replyto = "".$TBDEV['site_email'].""; // The Reply-to email.
$record_mail = true; // set this true or false . If you set this true every time whene you send a mail the time , userid , and the number of mail sent will be recorded
$days = 120; //number of days of inactivity
// end config
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST["action"]) ? htmlspecialchars(trim($_POST["action"])) : '';
    if (empty($_POST["userid"]) && (($action == "deluser") || ($action == "mail")))
        stderr($lang['inactive_error'], "{$lang['inactive_selectuser']}");

    if ($action == "deluser" && (!empty($_POST["userid"]))) {
        sql_query("DELETE FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ");
        stderr($lang['inactive_success'], "{$lang['inactive_deleted']} <a href='".$TBDEV['baseurl']."/inactive.php'>{$lang['inactive_back']}</a>");
    }
    if ($action == "disable" && (!empty($_POST["userid"]))) {
        sql_query("UPDATE users SET enabled='no' WHERE id IN (" . implode(", ", $_POST['userid']) . ") ");
        stderr($lang['inactive_success'], "{$lang['inactive_disabled']} <a href='".$TBDEV['baseurl']."/inactive.php'>{$lang['inactive_back']}</a>");
    }

    if ($action == "mail" && (!empty($_POST["userid"]))) {
        $res = sql_query("SELECT id, email , username, added, last_access FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ORDER BY last_access DESC ");
        $count = mysql_num_rows($res);
        while ($arr = mysql_fetch_array($res)) {
            $id = $arr["id"];
            $username = $arr["username"];
            $email = htmlspecialchars($arr["email"]);
            $added = get_date($arr["added"], 'DATE');
            $last_access = get_date($arr["last_access"], 'DATE');

            $subject = "{$lang['inactive_youracc']}Your account at {$TBDEV['site_name']} !";
            $message = "{$lang['inactive_hey']}
            {$lang['inactive_youracc']} {$TBDEV['site_name']} {$lang['inactive_marked']} {$TBDEV['site_name']}{$lang['inactive_plogin']}\n
            {$lang['inactive_yourusername']} $username\n
            {$lang['inactive_created']} $added\n
            {$lang['inactive_lastaccess']} $last_access\n
            {$lang['inactive_loginat']} {$TBDEV['baseurl']}/login.php\n
            {$lang['inactive_forgotten']} {$TBDEV['baseurl']}/recover.php\n
            {$lang['inactive_welcomeback']} {$TBDEV['site_name']}";
            $headers = 'From: ' . $TBDEV['site_email'] . "\r\n" . 'Reply-To:' . $replyto . "\r\n" . 'X-Mailer: PHP/' . phpversion();
            $mail = @mail($email, $subject, $message, $headers);
            }

        if ($record_mail) {
            $date = time();
            $userid = $CURUSER["id"];
            if ($count > 0 && $mail)
                sql_query("update avps set value_i='$date', value_u='$count', value_s='$userid' WHERE arg='inactivemail' ") or sqlerr(__FILE__, __LINE__);
        }

        if ($mail)
            stderr($lang['inactive_success'], "{$lang['inactive_msgsent']} <a href='".$TBDEV['baseurl']."/admin.php?action=inactive'>{$lang['inactive_back']}</a>");
      
        else
            stderr($lang['inactive_error'], "{$lang['inactive_tryagain']}");
    }
}

$dt = time() - ($days * 86400);
$res = sql_query("SELECT id,username,class,email,uploaded,downloaded,last_access FROM users WHERE last_access<$dt AND status='confirmed' AND enabled='yes' ORDER BY last_access DESC ") or sqlerr(__FILE__, __LINE__);
//Uncomment this whie using the parked account mod, & comment out the previous query
//$res = mysql_query("SELECT id,username,class,email,uploaded,downloaded,last_access FROM users WHERE last_access<$dt AND status='confirmed' AND enabled='yes' AND parked='no' ORDER BY last_access DESC ") or sqlerr(__FILE__, __LINE__);
$count = mysql_num_rows($res);
if ($count > 0) {

  
$HTMLOUT .="<script type='text/javascript'>
/*<![CDATA[*/
var checkflag = 'false';
function check(field) {
if (checkflag == 'false') {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = 'true';
return 'Uncheck All'; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = 'false';
return 'Check All'; }
}
/*]]>*/
</script>";

    $HTMLOUT .="<h2>" . htmlspecialchars($count) . "{$lang['inactive_accounts']} " . htmlspecialchars($days) . " {$lang['inactive_days']}</h2>
    <form method='post' action='admin.php?action=inactive'>
    <table class='main' border='1' cellspacing='0' cellpadding='5'>
    <tr>
    <td class='colhead'>{$lang['inactive_username']}</td>
    <td class='colhead'>{$lang['inactive_class']}</td>
    <td class='colhead'>{$lang['inactive_mail']}</td>
    <td class='colhead'>{$lang['inactive_ratio']}</td>
    <td class='colhead'>{$lang['inactive_lastseen']}</td>
    <td class='colhead' align='center'>{$lang['inactive_x']}</td></tr>";

    while ($arr = mysql_fetch_assoc($res)) {
        $ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"], 3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));
        $last_seen = (($arr["last_access"] == "0") ? "never" : "" . get_date($arr["last_access"], 'DATE') . "&nbsp;");
        $class = get_user_class_name($arr["class"]);
        $HTMLOUT .="<tr>
        <td><a href='{$TBDEV['baseurl']}/userdetails.php?id=" . $arr["id"] . "'>" . $arr["username"] . "</a></td>
        <td>" . $class . "</td>
        <td><a href='mailto:" . $arr["email"] . "'>" . htmlspecialchars($arr["email"]) . "</a></td>
        <td>" . $ratio . "</td>
        <td>" . $last_seen . "</td>
        <td align='center' bgcolor='#FF0000'><input type='checkbox' name='userid[]' value='" . $arr["id"] . "' /></td></tr>
        ";
    }
    $HTMLOUT .="<tr>
    <td colspan='6' class='colhead' align='center'>
<select name='action'>
<option value='mail'>{$lang['inactive_sendmail']}</option>
<option value='deluser' " . ($CURUSER["class"] < UC_ADMINISTRATOR ? "disabled" : "") . ">{$lang['inactive_deleteusers']}</option>
<option value='disable'>{$lang['inactive_disaccounts']}</option>
</select>&nbsp;&nbsp;<input type='submit' name='submit' value='{$lang['inactive_apchanges']}' />&nbsp;&nbsp;<input type='button' value='Check all' onclick='this.value=check(form)' /></td></tr>";

    if ($record_mail) {
        $ress = sql_query("SELECT avps.value_s AS userid, avps.value_i AS last_mail, avps.value_u AS mails, users.username FROM avps LEFT JOIN users ON avps.value_s=users.id WHERE avps.arg='inactivemail' LIMIT 1");
        $date = mysql_fetch_assoc($ress);
        if ($date["last_mail"] > 0)
            $HTMLOUT .="<tr><td colspan='6' class='colhead' align='center' style='color:red;'>{$lang['inactive_lastmail']} <a href='{$TBDEV['baseurl']}/userdetails.php?id=" . htmlspecialchars($date["userid"]) . "'>" . htmlspecialchars($date["username"]) . "</a> {$lang['inactive_on']} <b>" . get_date($date["last_mail"], 'DATE') . " -  " . $date["mails"] . "</b>{$lang['inactive_email']} " . ($date["mails"] > 1 ? "s" : "") . "  {$lang['inactive_sent']}</td></tr>";
    }
    $HTMLOUT .="</table></form>";
} else {
    $HTMLOUT .="<h2>{$lang['inactive_noaccounts']} " . $days . " {$lang['inactive_days']}</h2>";
}

print stdhead($lang['inactive_users']) . $HTMLOUT . stdfoot();
?>
