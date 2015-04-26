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
+------------------------------------------------
**/

require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
require_once ("include/html_functions.php");
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global') );

$HTMLOUT='';
$limit="20";
$userid = 0 + $CURUSER["id"];
if (!is_valid_id($userid)) stderr("Error", "Invalid ID");

if ($CURUSER["class"] < UC_USER || ($CURUSER["id"] != $userid && $CURUSER["class"] < UC_MODERATOR))
    stderr("Error", "Permission denied");
// === subscribe to thread
if (isset($_GET["subscribe"])){
    $subscribe = 0 + $_GET["subscribe"];
    if ($subscribe != '1')
        stderr("Error", "I smell a rat!");

    if (!isset($_GET["topicid"]))
        stderr("Error", "No forum selected!");

    if (isset($_GET["topicid"])) {
        $topicid = 0 + htmlspecialchars($_GET["topicid"]);
        if (@ereg("^[0-9]+$", !$topicid))
            stderr("Error", "Bad Topic Id!");
    }

    if ((get_row_count("subscriptions", "WHERE userid=$CURUSER[id] AND topicid = $topicid")) > 0)
        stderr("Error", "Already subscribed to thread number <b>".htmlspecialchars($topicid)."</b> Click <a href='{$TBDEV['baseurl']}/forums.php?action=viewtopic&amp;topicid=$topicid'> <b>Here</b></a> to go back to the thread. Or click <a href='{$TBDEV['baseurl']}/subscriptions.php'> <b>Here</b></a> to view your subscriptions.");

    mysql_query("INSERT INTO subscriptions (userid, topicid) VALUES ($CURUSER[id], $topicid)") or sqlerr(__FILE__, __LINE__);

    $res = mysql_query("SELECT subject FROM `topics` WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr("Error", "Bad forum id!");
    $forumname = $arr["subject"];
    stderr("Success", "Successfully subscribed to thread <b>".htmlspecialchars($forumname)."</b> Click <a href='{$TBDEV['baseurl']}/forums.php?action=viewtopic&amp;topicid=$topicid'> <b>Here</b></a> to go back to the thread. Or click <a href='{$TBDEV['baseurl']}/subscriptions.php'> <b>Here</b></a> to view your subscriptions.");
}
// === end subscribe to thread
// === Action: Delete subscription
if (isset($_GET["delete"])){
    if (!isset($_GET["deletesubscription"]))
        stderr("Error", "Nothing selected");

    $checked = $_GET['deletesubscription'];
    foreach ($checked as $delete) {
    mysql_query ("DELETE FROM subscriptions WHERE userid = $CURUSER[id] AND topicid=" . sqlesc($delete));
    }

    header("Refresh: 0; url={$TBDEV['baseurl']}/subscriptions.php?deleted=1");
}
// ===end
$res = mysql_query("SELECT id, username, donor, warned, support, class, chatpost, leechwarn, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) == 1) {
    $arr = mysql_fetch_assoc($res);

    $subject = "<a class='altlink' href='{$TBDEV['baseurl']}/userdetails.php?id=$userid'><b> $arr[username]</b></a> " . get_user_icons($arr, true);
} else
    $subject = "unknown[$userid]";

$where_is = "p.userid = $userid AND f.minclassread <= " . $CURUSER['class'];
$order_is = "t.id DESC";
$from_is = "subscriptions AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";
$select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.topicid";
$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";

$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);

$HTMLOUT='';
$HTMLOUT.="<h4>Subscribed Forums for{$subject}</h4><p align='center'>To be notified via PM when there is a new post, go to your <a class='altlink' href='{$TBDEV['baseurl']}/my.php'>profile</a> and set <b><i>PM on Subscriptions</i></b> to yes</p>\n";

if (isset($_GET["deleted"])) {
    $HTMLOUT.="<h1>subscription(s) Deleted</h1>";
}
// ------ Print table
$HTMLOUT.= begin_main_frame();

$HTMLOUT.= begin_frame();

if (mysql_num_rows($res) == 0)
   $HTMLOUT.="<p align='center'><font size=\"+2\"><b>No Subscriptions Found</b></font></p><p>You are not yet subscribed to any forums...</p><p>To subscribe to a forum at <b>".$TBDEV['site_name']."</b>, click the <b><i>Subscribe to this Forum</i></b> link at the top of the thread page.</p>";

while ($arr = mysql_fetch_assoc($res)) {
    $topicid = $arr["t_id"];

    $topicname = $arr["subject"];

    $forumid = $arr["f_id"];

    $forumname = $arr["name"];

    $newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;

    $order_is = "p.id DESC";
    $from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id";
    $select_is = "t.id, p.*";
    $where_is = "t.id = $topicid AND f.minclassread <= " . $CURUSER['class'];
    $queryposts = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";
    $res2 = mysql_query($queryposts) or sqlerr(__FILE__, __LINE__);
    $arr2 = mysql_fetch_assoc($res2);

    $postid = $arr2["id"];

    $posterid = $arr2["userid"];

    $queryuser = mysql_query("SELECT username FROM users WHERE id=$arr2[userid]");
    $res3 = mysql_fetch_assoc($queryuser);

    $added = get_date($arr2["added"], 'DATE',1,0) . " GMT (" . (get_date($arr2["added"], 'LONG',1,0)) . ")";
    $count2='';
    // =======change colors
    if ($count2 == 0) {
        $count2 = $count2 + 1;
        $class = "clearalt7";
    } else {
        $count2 = 0;
        $class = "clearalt6";
    }
    // =======end
    $HTMLOUT.="
    <table border='0' cellspacing='0' cellpadding='0' width='100%'>
    <tr><td class='colhead' width='100%'>" . ($newposts ? " <b><font color='red'>New Reply !</font></b>" : "") . "<br /><b>Forum: </b>
<a class='altlink' href='{$TBDEV['baseurl']}/forums.php?action=viewforum&amp;forumid=$forumid'>{$forumname}</a>
<b>Topic: </b>
<a class='altlink' href='{$TBDEV['baseurl']}/forums.php?action=viewtopic&amp;topicid=$topicid'>{$topicname}</a>
<b>Post: </b>
#<a class='altlink' href='{$TBDEV['baseurl']}/forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=p$postid#$postid'>{$postid}</a><br />
<b>Last Post By:</b><a class='altlink' href='{$TBDEV['baseurl']}/userdetails.php?id=$posterid'><b>$res3[username]</b></a> added:{$added}</td>
<td class='colhead' align='right' width='20%'>";
    // === delete subscription
    if (isset($_GET["check"]) == "yes")
    $HTMLOUT.="<input type='checkbox' checked='checked' name='deletesubscription[]' value='{$topicid}' />";
    else
    $HTMLOUT.="<input type='checkbox' name='deletesubscription[]' value='{$topicid}' />";
    // === end
    $HTMLOUT.="<b>un-subscribe</b></td></tr></table>\n";

    $HTMLOUT.= begin_table(true);

    $body = format_comment($arr2["body"]);

    if ((is_valid_id(isset($arr['editedby'])))) {
        $subres = sql_query("SELECT username FROM users WHERE id=$arr[editedby]");
        if (mysql_num_rows($subres) == 1) {
            $subrow = mysql_fetch_assoc($subres);
            $body .= "<p><font size='1' class='small'>Last edited by <a href='{$TBDEV['baseurl']}/userdetails.php?id=$arr[editedby]'><b>$subrow[username]</b></a> at $arr[editedat] GMT</font></p>\n";
        }
    }
   $HTMLOUT.="<tr valign='top'><td class='$class'>{$body}</td></tr>\n";
   $HTMLOUT.= end_table();
}
$HTMLOUT.="<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\">
<table width='100%'>
<tr>
<td align='right' class='colhead'>
<a class='altlink' href='{$TBDEV['baseurl']}/subscriptions.php?action=".isset($_GET["action"])."&amp;box=".isset($_GET["box"])."&amp;check=yes'>select all</a> -
<a class='altlink' href='{$TBDEV['baseurl']}/subscriptions.php?action=".isset($_GET["action"])."&amp;box=".isset($_GET["box"])."&amp;uncheck=yes'>un-select all</a>
<input class='button' type='submit' name='delete' value='Delete' /> selected</td></tr></table></form>";


$HTMLOUT.= end_frame();

$HTMLOUT.= end_main_frame();

print stdhead('Subscriptions') . $HTMLOUT . stdfoot();

die;

?>