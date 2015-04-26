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

require_once "include/user_functions.php";
require_once "include/html_functions.php";
require_once "include/pager_functions.php";
staffonly();

        $lang = array_merge( $lang, load_language('ad_log') );

$where = '';
$search = isset($_POST['search']) ? strip_tags($_POST['search']) : '';
if (!empty($search))
$where = "WHERE txt like " . sqlesc("%$search%") . "";

    $res = sql_query("SELECT COUNT(*) FROM sitelog $where") or die(mysql_error());
    $row = mysql_fetch_array($res,MYSQL_NUM);
    $count = $row[0];

        $perpage = 25;
        $pager = pager($perpage, $count, "admin.php?action=log&amp;");

        $res = sql_query("SELECT added, txt FROM sitelog ORDER BY added DESC ".$pager['limit']."") or sqlerr(__FILE__,__LINE__);

        $HTMLOUT = "<h1>{$lang['text_sitelog']}</h1>\n";

        if (mysql_num_rows($res) == 0)
        {
                $HTMLOUT .= "<b>{$lang['text_logempty']}</b>\n";
        } else {

        $HTMLOUT .=  "<table border='1' cellspacing='0' width='115' cellpadding='5'>\n
                        <tr>
                                <td class='tabletitle' align='left'>Search Site Log</td>\n
                        </tr>
                        <tr>
                                <td class='table' align='left'>\n
                                <form method='post' action='admin.php?action=log'>\n
                                <input type='text' name='search' size='40' value='' />\n
                                <input type='submit' value='Search' style='height: 20px' />\n
                                </form></td></tr></table>";

        $HTMLOUT .= $pager['pagertop'];
        $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'>
                        <tr>
                                <td class='colhead' align='left'>{$lang['header_date']}</td>
                                <td class='colhead' align='left'>{$lang['header_time']}</td>
                                <td class='colhead' align='left'>{$lang['header_event']}</td>
                        </tr>\n";

                while ($arr = mysql_fetch_assoc($res))
                {
                $date = explode( ',', get_date( $arr['added'], 'LONG' ) );
                $HTMLOUT .= "<tr>
                        <td>{$date[0]}</td>
                        <td>{$date[1]}</td>
                        <td align='left'>".htmlentities($arr['txt'], ENT_QUOTES)."</td>
                        </tr>\n";
                }

        $HTMLOUT .= "</table>\n";
        $HTMLOUT .= $pager['pagerbottom'];
        }
        $HTMLOUT .= "<p>{$lang['text_times']}</p>\n";

        print stdhead("{$lang['stdhead_log']}") . $HTMLOUT . stdfoot();
?>