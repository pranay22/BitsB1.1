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

require_once("include/user_functions.php");
require_once("include/pager_functions.php");
staffonly();
$lang = array_merge( $lang );

$HTMLOUT="";

if ($CURUSER['class'] < UC_STAFF_LEADER)
stderr("Error", "Only SYSOP + can view the logs");

$search = isset($_POST['search']) ? strip_tags($_POST['search']) : '';
if (isset($search))
$where = "WHERE txt LIKE " . sqlesc("%$search%") . "";

//== Delete items older than 1 month
$secs = 30 * 86400;
sql_query("DELETE FROM infolog WHERE " . time() . " - added > $secs") or sqlerr(__FILE__, __LINE__);

$res = sql_query("SELECT COUNT(*) FROM infolog $where");
$row = mysql_fetch_array($res);
$count = $row[0];
$perpage = 15;

$pager = pager($perpage, $count, "admin.php?action=sysoplog&amp;");

    $res = sql_query("SELECT added, txt FROM infolog $where ORDER BY added DESC {$pager['limit']} ") or sqlerr(__FILE__, __LINE__);
    
    $HTMLOUT .= "<h1>Staff actions log</h1>";
    $HTMLOUT .=  "<table border='1' cellspacing='0' width='115' cellpadding='5'>\n
             <tr>
             <td class='tabletitle' align='left'>Search Log</td>\n
             </tr>
             <tr>
             <td class='table' align='left'>\n
             <form method='post' action='admin.php?action=sysoplog'>\n
             <input type='text' name='search' size='40' value='' />\n
             <input type='submit' value='Search' style='height: 20px' />\n
             </form></td></tr></table>";

    $HTMLOUT .= $pager['pagertop'];
    
    if (mysql_num_rows($res) == 0)
    {
      $HTMLOUT .= "<b>No records found</b>";
    }
    else
    {
      $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'>
      <tr>
        <td class='colhead' align='left'>Date</td>
        <td class='colhead' align='left'>Time</td>
        <td class='colhead' align='left'>Event</td>
      </tr>"; 

while ($arr = mysql_fetch_assoc($res))
{
$color = '#FF4763';
if (strpos($arr['txt'],'Warned')) 
$color = "#FF0000";
if (strpos($arr['txt'],'Leech warned')) 
$color = "#9ED9D0";
if (strpos($arr['txt'],'Download possible')) 
$color = "#62D962";
if (strpos($arr['txt'],'Immunity enabled')) 
$color = "#FFFF00";
if (strpos($arr['txt'],'Enabled')) 
$color = "#47FFE3";
if (strpos($arr['txt'],'Donor')) 
$color = "#FF8112";
if (strpos($arr['txt'],'Upload Total')) 
$color = "#14ED00";
if (strpos($arr['txt'],'Download total')) 
$color = "#5A63C7";
if (strpos($arr['txt'],'Invite total')) 
$color = "#54ACBA";
if (strpos($arr['txt'],'Seedbonus total')) 
$color = "#BA6154";
if (strpos($arr['txt'],'Reputuation total')) 
$color = "#57AD00";
if (strpos($arr['txt'],'Promoted')) 
$color = "#E01E00";
if (strpos($arr['txt'],'Demoted')) 
$color = "#BA5480";
$date = get_date($arr['added'], 'DATE');
$time = get_date($arr['added'], 'LONG',0,1);
$HTMLOUT .="<tr class='tableb'><td style='background-color:$color'><font color='black'>{$date}</font></td>
<td style='background-color:$color'><font color='black'>{$time}</font></td>
<td style='background-color:$color' align='left'><font color='black'>".$arr['txt']."</font></td></tr>\n";
}
$HTMLOUT .="</table>";
}
$HTMLOUT .= $pager['pagerbottom'];

$HTMLOUT .="<p>Times are in GMT.</p>\n";
print stdhead('Sysop Infolog') . $HTMLOUT . stdfoot();
?>
