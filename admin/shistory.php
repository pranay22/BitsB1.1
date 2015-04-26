<?php
/**
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.0
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
|   Shoutbox history viewer v0.3
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

    require_once "include/html_functions.php";
    require_once "include/pager_functions.php";
    require_once "include/bbcode_functions.php";
    staffonly();

    $lang = array_merge( $lang );

    if ($CURUSER["class"] < UC_ADMINISTRATOR)
        header( "Location: {$TBDEV['baseurl']}/index.php");

    $HTMLOUT ="";
    $count1 = get_row_count('Shoutbox');
    $perpage = 15;      //Number of shouts per page (configurable)
    $pager = pager($perpage, $count1, 'admin.php?action=shistory&amp;');

    $res = sql_query( "SELECT s.id, s.userid, s.date, s.text, s.to_user, u.username, u.enabled, u.class, u.donor, u.warned, u.chatpost FROM shoutbox as s LEFT JOIN users as u ON s.userid=u.id ORDER BY s.date DESC ".$pager['limit']."" ) or sqlerr( __FILE__, __LINE__ );

    if ($count1 > $perpage)
        $HTMLOUT .= $pager['pagertop'];
    
    $HTMLOUT .= begin_main_frame();
    if ( mysql_num_rows( $res ) == 0 )
        $HTMLOUT .="No shouts here";
    else {
        $HTMLOUT .="<table align='center' border='0' cellspacing='0' cellpadding='2' width='100%' class='small'>\n";
        while ( $arr = mysql_fetch_assoc( $res ) ) {
            if(($arr['to_user'] != $CURUSER['id'] && $arr['to_user'] != 0) && $arr['userid'] != $CURUSER['id']) 
                continue;
            if($arr['to_user'] == $CURUSER['id'] || ($arr['userid'] == $CURUSER['id'] && $arr['to_user'] !=0) )
                $private = "<img src='{$TBDEV['pic_base_url']}private-shout.png' alt='Private shout' title='Private shout!' width='16' style='padding-left:2px;padding-right:2px;' border='0' />";
            else
                $private = "<img src='{$TBDEV['pic_base_url']}group.png' alt='Public shout' title='Public shout!' width='16' style='padding-left:2px;padding-right:2px;' border='0' />";
            $date = get_date($arr["date"], 0,1);
            $HTMLOUT .="<tr style='background-color:grey;'><td><span class='size1' style='color:white; '>[$date] [$private]</span>\n <a href='userdetails.php?id=" . $arr["userid"] . "' target='_blank'><font color='#" . get_user_class_color( $arr['class'] ) . "'>" . htmlspecialchars( $arr['username'] ) . "</font></a><span class='size2' style='color:white;'> " . format_comment( $arr["text"] ) . "\n</span></td></tr>\n";
        }
        $HTMLOUT .="</table>";
    }
    if ($count1 > $perpage)
        $HTMLOUT .= $pager['pagerbottom'];

    $HTMLOUT .= end_main_frame();

    print stdhead('Shout History') . $HTMLOUT . stdfoot();
?>