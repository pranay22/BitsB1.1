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

if ( ! defined(( 'IN_TBDEV_POPUP_SYSTEM' )) )
{
	print "You Can't access this file directly.<br/><br/> 
    But wait!! <br /><b>Congratulations!</b> <br/>Your IP address was just added to system's log for trying to access this file illegally.";
	exit();
}
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$pfile = $break[count($break) - 1];
if ( $pfile == 'index.php'|| $pfile == 'staff.php'|| $pfile == 'forums.php'||$pfile == 'viewrequests.php'||$pfile== 'topten.php'||$pfile== 'rules.php')
{
//require_once"include/bbcode_functions.php";


function format_urls2($s){
	return preg_replace(
    	"/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/i",
	    "\\1<a href=\"\\2\">\\2</a>", $s);
}

$lang = array_merge( $lang, load_language('popup_msg') );

$newmessages = str_replace(array("<#UNREAD#>","<#S#>"),array($unread,($unread > 1 ? "s" : "")),$lang['popup_you_have_new_message']);

//you can edit the position of popup box
$div_showimage = "position: absolute; width:400px; left:400px; top:220px;";

$htmlout .= "
<script type='text/javascript' src='scripts/popup_msg.js'></script>
<noscript>Please enable javascript.</noscript>

<div id='showimage' style='{$div_showimage}'>
    <div id='dragbar' onmousedown='initializedrag(event)'>
        <div class='left_txt'>{$newmessages}</div>
        <div class='right_txt'><a href='javascript:%20void();' onclick='hidebox();return false' style='color: #DBE3EA;'>X</a></div>
		<div class='clear'></div>
    </div>
";

$res = mysql_query("SELECT m.*, u.username
					FROM messages as m
					LEFT JOIN users as u on m.sender = u.id
					WHERE m.receiver=" . $CURUSER["id"] . " AND m.location IN ('1','2')
					AND m.unread = 'yes'
					ORDER BY added DESC
					LIMIT 1") or die("barf!");
$arr = mysql_fetch_assoc($res);

if (is_valid_id($arr["sender"])) {
	$sender = "<a href='userdetails.php?id={$arr['sender']}'>". ($arr["username"]?$arr["username"]:$lang['popup_deleted']) ."</a>";
} else {
	$sender = $lang['popup_system'];
}	
	$reply = "sendmessage.php?receiver={$arr['sender']}&amp;replyto={$arr['id']}";
	$delete = "messages.php?action=deletemessage&amp;id=".$arr['id'];
	$subject = "".htmlspecialchars($arr["subject"])."";
    $next = "javascript:location.reload(true)";

$htmlout .= "<span class='subject'>$subject</span><br />";
$htmlout .= "
<span class='from'>
{$lang['popup_from']}<b>$sender</b>, " . get_date($arr["added"],'') . " (".get_date($arr["added"],'',0,1).")
</span>
"
;

	if ($arr["unread"] == "yes") {
		$htmlout .= "<b><font color='red'>{$lang['popup_new']}</font></b>";
	}

$htmlout .= "<div class='msg'>";
$htmlout .= format_comment($arr['msg']);
$htmlout .= "</div>";

$htmlout .= "<div class='msg_links'>";
$htmlout .= "
<a href='messages.php'><b>{$lang['popup_inbox']}</b></a> | 
".($arr["username"] ? "<a href='$reply'><b>{$lang['popup_reply']}</b></a>" : "<font class='gray'><b>{$lang['popup_reply']}</b></font>")." | 
<a href='$delete'><b>{$lang['popup_delete']}</b></a> | <a href='{$next}'><b>Next</b></a>
";

$htmlout .= "</div>
</div>";
}
//Updates unread set of messages to read set
mysql_query("UPDATE messages SET unread='no' WHERE id=" . sqlesc($arr['id']) . " AND receiver=" . sqlesc($CURUSER['id']) . " LIMIT 1");
?>