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

    require_once("include/bittorrent.php");
    require_once "include/user_functions.php";
    require_once "include/html_functions.php";
    require_once "include/bbcode_functions.php";
    staffonly();
    $lang = array_merge( $lang, load_language('ad_snews2') );

    if ($CURUSER["class"] < UC_MODERATOR)
        header( "Location: {$TBDEV['baseurl']}/index.php");
    
    if ($CURUSER['class'] >= UC_SYSOP) {
        $button = "<span style='float:right;'><a href=\"admin.php?action=snews&amp;do=add\"><img src='".$TBDEV['pic_base_url']."add.png' alt='Add Staff News' /></a></span>\n";
    }else {
        $button ="";
    }
    $HTMLOUT ="";
    $HTMLOUT .= "<div class='headline'>&nbsp;{$lang['snew_title']}</span>{$button}</div>";
    $HTMLOUT .= "<div class='headbody'>";
    $res = sql_query("SELECT n.id,n.title,n.body,n.type,n.added,n.last_edit, n.user as uid ,u.username from snews as n LEFT JOIN users as u ON n.user=u.id ORDER BY n.added DESC , n.last_edit DESC") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) == 0)
        $HTMLOUT .='<table width="947" cellpadding="2" cellspacing="0" border="0">
            <tr><td align="center" style="border:none;"><h1>'.$lang['snews_non'].'</h1></td></tr></table>';
    else{
        while ($arr = mysql_fetch_assoc($res)){
            $HTMLOUT .='<table width="947" cellpadding="2" cellspacing="0" border="1"><tr><td align="center"><table width="95%" title="'.$lang['snews_flag'].' '.($arr["type"]).'" cellpadding="2" cellspacing="0" border="1">
                <tr><td style="border:none;"><font color="'.($arr["type"] == "notice" ? "#339900" : ($arr["type"] == "warning" ? "#FF3300" : ($arr["type"] == "important" ? "#990000" : ""))).'"><u><i><b>'.($arr["title"]).'</b></i></u></font>&nbsp;-&nbsp;'.$lang['snews_added'].':&nbsp;<a href="userdetails.php?id='.($arr["uid"]).'">'.($arr["username"]).'</a> on <font class="small">'.(gmdate("d M Y", $arr["added"])).'</font>'.($arr["last_edit"] > 0 ? "<font class=\"small\">(".$lang['snews_edit']." ".(gmdate("d M Y", $arr["last_edit"]))."</font>)" : "").'';
            if ($CURUSER['class'] >= UC_SYSOP){
                $HTMLOUT .='<a href="admin.php?action=snews&amp;do=edit&amp;id='.$arr["id"].'"><img src="pic/button_edit2.gif" alt="Edit News" title="Edit News" style="border:none;padding:2px;" /></a>
                    <a href="admin.php?action=snews&amp;do=delete&amp;id='.$arr["id"].'"><img src="pic/button_delete2.gif" alt="Delete News" title="Delete News" style="border:none;padding:2px;" /></a></div>';
            }
            $HTMLOUT .='</td></tr></table>
                <table width="95%" cellpadding="2" cellspacing="0" border="0"><tr>
                <td style="border:none;">'.(format_comment($arr["body"])).'</td>
                </tr></table>';
            $HTMLOUT .='<br /></td></tr></table>';
        }
    }
    $HTMLOUT .='</div>';
    $HTMLOUT .="</div>";
    
    print stdhead('snews') . $HTMLOUT . stdfoot();
?>