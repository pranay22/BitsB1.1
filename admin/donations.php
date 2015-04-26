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
|   Donor Listing System for sysop v1.1
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

require_once ROOT_PATH.'/include/user_functions.php'; 
require_once ROOT_PATH.'/include/html_functions.php'; 
require_once ROOT_PATH.'/include/pager_functions.php';

    staffonly();
    $HTMLOUT='';

    if (get_user_class() < UC_ADMINISTRATOR)
      stderr("{$lang['stderr_error']}", "{$lang['text_denied']}");
      
if (isset($_GET["total_donors"])) {
    $total_donors = 0 + $_GET["total_donors"];
    if ($total_donors != '1')
        stderr("Error", "I smell a rat!");

    $res = sql_query("SELECT COUNT(*) FROM users WHERE total_donated != '0.00'") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_array($res);
    $count = $row[0];
    $perpage = 25;
    
    $pager = pager($perpage, $count, "donations.php?");
    
    if (mysql_num_rows($res) == 0)
        stderr("Sorry", "no donors found!");

    $users = number_format(get_row_count("users", "WHERE total_donated != '0.00'"));
    $HTMLOUT .= begin_frame("Donor List: All Donations [ $users ]", true);
    $res = sql_query("SELECT id,username,email,added,donated,total_donated FROM users WHERE total_donated != '0.00' ORDER BY id DESC ".$pager['limit']."") or sqlerr(__FILE__, __LINE__);
    }
    // ===end total donors
    else {
    $res = sql_query("SELECT COUNT(*) FROM users WHERE donor='yes'") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_array($res);
    $count = $row[0];
    $perpage = 25;
    $pager = pager($perpage, $count, "donations.php?");

    if (mysql_num_rows($res) == 0)
        stderr("Sorry", "no donors found!");

    $users = number_format(get_row_count("users", "WHERE donor='yes'"));
    $HTMLOUT .= begin_frame("Donor List: Current Donors [ $users ]", true);
    $res = sql_query("SELECT id,username,email,added,donated,total_donated FROM users WHERE donor='yes' ORDER BY id DESC ".$pager['limit']."") or sqlerr(__FILE__, __LINE__);
    }

$HTMLOUT .= begin_table();
$HTMLOUT .="<tr><td colspan='9' align='center'><a class='altlink' href='{$TBDEV['baseurl']}/admin.php?action=donations'>Current Donors</a> || <a class='altlink' href='{$TBDEV['baseurl']}/admin.php?action=donations&amp;total_donors=1'>All Donations</a></td></tr>";

$HTMLOUT .= $pager['pagertop'];

$HTMLOUT .="<tr><td class='colhead'><b title='Donor ID'>ID</b></td><td class='colhead' align='left'><b title='Donor username'>Username</b></td><td class='colhead' align='left'><b title='Donor e-mail'>e-mail</b></td>" . "<td class='colhead' align='left'><b title='Joined'>Joined</b></td><td class='colhead' align='left'><b title='Donor Until?'>Donor Until?</b></td><td class='colhead' align='left'>" . 
"<b title='Current donated amount'>Current</b></td><td class='colhead' align='left'><b title='Total amount'>Total</b></td><td class='colhead' align='left'><b title='PM Donor'>PM</b></td></tr>";
while ($arr = @mysql_fetch_assoc($res)) {
   
    // =======change colors
    $count2 ="";
    if ($count2 == 0) {
        $count2 = $count2 + 1;
        $class = "clearalt7";
    } else {
        $count2 = 0;
        $class = "clearalt6";
    }
    // =======end
    $HTMLOUT .="<tr><td valign='bottom' class='$class'><a class='altlink' href='{$TBDEV['baseurl']}/userdetails.php?id=" . htmlspecialchars($arr['id']) . "'>" . htmlspecialchars($arr['id']) . "</a></td>" . "<td align='left' valign='bottom' class='$class'><a class='altlink' href='{$TBDEV['baseurl']}/userdetails.php?id=" . htmlspecialchars($arr['id']) . "'><b>" . htmlspecialchars($arr['username']) . "</b></a>" . "</td><td align='left' valign='bottom' class='$class'><a class='altlink' href='mailto:" . htmlspecialchars($arr['email']) . "'>" . htmlspecialchars($arr['email']) . "</a>" . "</td><td align='left' valign='bottom' class='$class'><font size=\"-3\"> ".get_date($arr['added'], 'DATE'). "</font>" . "</td><td align='left' valign='bottom' class='$class'>";

    $r = @mysql_query("SELECT donoruntil FROM users WHERE id=" . sqlesc($arr[id]) . "") or sqlerr();
    $user = mysql_fetch_array($r);
    $donoruntil = $user['donoruntil'];
    if ($donoruntil == '0')
        $HTMLOUT .="n/a";
    else
        $HTMLOUT .="<font size=\"-3\"> ".get_date($user['donoruntil'], 'DATE'). " [ " . mkprettytime($donoruntil - TIME_NOW) . " ] To go...</font>";

    $HTMLOUT .="</td><td align='left' valign='bottom' class='$class'><b>&#163;" . htmlspecialchars($arr['donated']) . "</b></td>" . "<td align='left' valign='bottom' class='$class'><b>&#163;" . htmlspecialchars($arr['total_donated']) . "</b></td>" . "<td align='left' valign='bottom' class='$class'><b><a class='altlink' href='{$TBDEV['baseurl']}/sendmessage.php?receiver=" . htmlspecialchars($arr['id']) . "'><img src='{$TBDEV['pic_base_url']}pn_sentbox2.gif' title='Send PM'></a></b></td></tr>";
}
$HTMLOUT .= end_table();
$HTMLOUT .= end_frame();

$HTMLOUT .= $pager['pagerbottom'];

print stdhead('Donations') . $HTMLOUT . stdfoot();
?>