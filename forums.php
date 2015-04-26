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

require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
require_once("include/html_functions.php");
require_once("include/pager_functions.php");
require_once("include/mood.php");
dbconn();

$lang = array_merge( load_language('global'), load_language('forums') );
parked();

flood_limit('posts');
if ($CURUSER["forumpost"] == 'no')
    {
    stderr($lang['forum_sorry'], $lang['forum_no_auth']);
    }

if ($TBDEV['forums_online'] == 0 AND $CURUSER['class'] < UC_FORUM_MODERATOR)
stderr('Information', 'The forums are currently offline for maintainance work');

if (function_exists('parked'))
parked();

/**
* Configs Start
*/
/**
* The max class, ie: UC_CODER
*
* Is able to delete, edit the forum etc...
*/
define('MAX_CLASS', UC_STAFF_LEADER);
/**
* The max file size allowed to be uploaded
*
* Default: 1024*1024 = 1MB
*/
$maxfilesize = 1024 * 1024;
/**
* Set's the max file size in php.ini, no need to change
*/
ini_set("upload_max_filesize", $maxfilesize);
/**
* Set's the root path, change only if you know what you are doing
*/
// define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
/**
* The path to the attachment dir, no slahses
*/
$attachment_dir = ROOT_PATH . "forum_attachments";
//$attachment_dir = ROOT_DIR . "forum_attachments";
/**
* The width of the forum, in percent, 100% is the full width
*
* Note: the width is also set in the function begin_main_frame()
*/
$forum_width = '100%';
/**
* The extensions that are allowed to be uploaded by the users
*
* Note: you need to have the pics in the $pic_base_url folder, ie zip.gif, rar.gif
*/
$allowed_file_extensions = array('rar', 'zip');
/**
* The max subject lenght in the topic descriptions, forum name etc...
*/
$maxsubjectlength = 80;
/**
* Get's the users posts per page, no need to change
*/
$postsperpage = (empty($CURUSER['postsperpage']) ? 25 : (int)$CURUSER['postsperpage']);
/**
* Set to true if you want to use the flood mod
*/
$use_flood_mod = true;
/**
* If there are more than $limit(default 10) posts in the last $minutes(default 5) minutes, it will give them a error...
*
* Requires the flood mod set to true
*/
$minutes = 5;
$limit = 10;
/**
* Set to true if you want to use the attachment mod
*
* Requires 2 extra tables(attachments, attachmentdownloads), so efore enabling it, make sure you have them...
*/
$use_attachment_mod = true;
/**
* Set to true if you want to use the forum poll mod
*
* Requires 2 extra tables(postpolls, postpollanswers), so efore enabling it, make sure you have them...
*/
$use_poll_mod = true;
/**
* Set to false to disable the forum stats
*/
$use_forum_stats_mod = true;
/**
* Define htmlout and javascripts
*/
$HTMLOUT='';

$HTMLOUT.="<script type='text/javascript' src='./scripts/popup.js'></script>
<script type='text/javascript' src='./scripts/shout.js'></script>";

/**
* Change the pics to the ones you use
*/
$forum_pics = array('default_avatar' => 'default_avatar.gif', 'arrow_up' => 'forumicons/p_up.gif', 'online_btn' => 'forumicons/user_online.gif',
    'offline_btn' => 'forumicons/user_offline.gif', 'pm_btn' => 'forumicons/pm.gif', 'p_report_btn' => 'forumicons/report.gif',
    'p_quote_btn' => 'forumicons/p_quote.gif', 'p_delete_btn' => 'forumicons/p_delete.gif', 'p_edit_btn' => 'forumicons/p_edit.gif');
/**
* Configs End
*/

//== Putyns post icons
function post_icons($s = 0)
{
    $body = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"8\" >
				<tr><td width=\"20%\" valign=\"top\" align=\"right\"><strong>Post Icons</strong> <br/>
				<font class=\"small\">(Optional)</font></td>\n";
    $body .= "<td width=\"80%\" align=\"left\">\n";

    for($i = 1; $i < 15;$i++) {
        $body .= "<input type=\"radio\" value=\"" . $i . "\" name=\"iconid\" " . ($s == $i ? "checked=\"checked\"" : "") . " />\n<img align=\"middle\" alt=\"\" src=\"pic/post_icons/icon" . $i . ".gif\"/>\n";
        if ($i == 7)
            $body .= "<br/>";
    }

    $body .= "<br/><input type=\"radio\" value=\"0\" name=\"iconid\"  " . ($s == 0 ? "checked=\"checked\"" : "") . " />[Use None]\n";
    $body .= "</td></tr></table>\n";

    return $body;
}

//==Putyns subforums
function subforums($arr)
{
    $sub = "<font class=\"small\"><b>Subforums:</b>";
    $i = 0;
    foreach($arr as $k) {
        $sub .= "&nbsp;<img src=\"pic/bullet_" . ($k["new"] == 1 ? "green.png" : "white.png") . "\" width=\"8\" title=\"" . ($k["new"] == 1 ? "New posts" : "Not new post") . "\" border=\"0\" alt='Subforum' /><a href=\"forums.php?action=viewforum&amp;forumid=" . $k["id"] . "\">" . $k["name"] . "</a>" . ((count($arr)-1) == $i ? "" : ",");
        $i++;
    }
    $sub .= "</font>";
    return $sub;
}
function get_count($arr)
{
    $topics = 0;
    $posts = 0;
    foreach($arr as $k) {
        $topics += $k["topics"];
        $posts += $k["posts"];
    }
    return array($posts, $topics);
}
//== End subforum

//== Forum moderator by putyn
function showMods($ars)
{
    $mods = "<font class=\"small\">Led by:&nbsp;";
    $i = 0;
    $count = count($ars);
    foreach($ars as $a) {
        $mods .= "<a href=\"userdetails.php?id=" . $a["id"] . "\">" . $a["user"] . "</a>" . (($count -1) == $i ? "":" ,");
        $i++;
    }
    $mods .= "</font>";
    return $mods;
}
function isMod($fid)
{
    GLOBAL $CURUSER;
    return (stristr($CURUSER["forums_mod"], "[" . $fid . "]") == true ? true : false) ;
}
//== End forum moderator :)

$action = (isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : ''));

if (!function_exists('highlight')) {
    function highlight($search, $subject, $hlstart = '<b><font color=\"red\">', $hlend = '</font></b>')
    {
        $srchlen = strlen($search); // length of searched string
        if ($srchlen == 0)
            return $subject;

        $find = $subject;
        while ($find = stristr($find, $search)) { // find $search text in $subject -case insensitiv
            $srchtxt = substr($find, 0, $srchlen); // get new search text
            $find = substr($find, $srchlen);
            $subject = str_replace($srchtxt, $hlstart . $srchtxt . $hlend, $subject); // highlight founded case insensitive search text
        }

        return $subject;
    }
}

function catch_up($id = 0)
{
    global $CURUSER, $TBDEV;

    $userid = (int)$CURUSER['id'];

    $res = mysql_query("SELECT t.id, t.lastpost, r.id AS r_id, r.lastpostread " . "FROM topics AS t " . "LEFT JOIN posts AS p ON p.id = t.lastpost " . "LEFT JOIN readposts AS r ON r.userid=" . sqlesc($userid) . " AND r.topicid=t.id " . "WHERE p.added > " . sqlesc(time() - $TBDEV['readpost_expiry']) .
        (!empty($id) ? ' AND t.id ' . (is_array($id) ? 'IN (' . implode(', ', $id) . ')' : '= ' . sqlesc($id)) : '')) or sqlerr(__FILE__, __LINE__);

    while ($arr = mysql_fetch_assoc($res)) {
        $postid = (int)$arr['lastpost'];

        if (!is_valid_id($arr['r_id']))
            mysql_query("INSERT INTO readposts (userid, topicid, lastpostread) VALUES($userid, " . (int)$arr['id'] . ", $postid)") or sqlerr(__FILE__, __LINE__);
        else if ($arr['lastpostread'] < $postid)
            mysql_query("UPDATE readposts SET lastpostread = $postid WHERE id = " . $arr['r_id']) or sqlerr(__FILE__, __LINE__);
    }
    mysql_free_result($res);
}

//==Begin cached online users
 	function forum_stats()
 	{
 	//== 09 Active users in forums
 	$htmlout ='';
	global $TBDEV, $forum_width, $lang, $CURUSER;
 	$forum3="";
 	$file = "./cache/forum.txt";
 	$expire = 30; // 30 seconds
 	if (file_exists($file) && filemtime($file) > (time() - $expire)) {
 	$forum3 = unserialize(file_get_contents($file));
 	} else {
 	$dt = sqlesc(time() - 180);
 	$forum1 = mysql_query("SELECT id, username, class, warned, support, donor, anonymous FROM users WHERE forum_access >= $dt ORDER BY class DESC") or sqlerr(__FILE__, __LINE__);
 	while ($forum2 = mysql_fetch_assoc($forum1)) {
 	$forum3[] = $forum2;
 	}
 	$OUTPUT = serialize($forum3);
 	$fp = fopen($file, "w");
 	fputs($fp, $OUTPUT);
 	fclose($fp);
 	} // end else
 	$forumusers = "";
 	if (is_array($forum3))
 	foreach ($forum3 as $arr) {
	if ($forumusers) $forumusers .= ",\n";
	$forumusers .= "<span style=\"white-space: nowrap;\">"; 
	if ($arr["anonymous"] == "yes")
	if ($CURUSER['class'] < UC_MODERATOR && $arr["id"] != $CURUSER["id"])
	$arr["username"] = "<i>Anonymous</i>";
	else
	$arr["username"] = "<font color='#" . get_user_class_color($arr['class']) . "'> " . htmlspecialchars($arr['username']) . "</font>+";
	else
	$arr["username"] = "<font color='#" . get_user_class_color($arr['class']) . "'> " . htmlspecialchars($arr['username']) . "</font>";
	$donator = $arr["donor"] === "yes";
	$warned = $arr["warned"] === "yes";
    $flsupport = $arr["support"] == "yes";

	if ($CURUSER)
	$forumusers .= "<a href='{$TBDEV['baseurl']}/userdetails.php?id={$arr["id"]}'><b>{$arr["username"]}</b></a>";
	else
	$forumusers .= "<b>{$arr["username"]}</b>";
	if ($arr["anonymous"] == "yes")
	if ($CURUSER['class'] < UC_MODERATOR && $arr["id"] != $CURUSER["id"])
	$forumusers .= "";
	else
	if ($donator)
	$forumusers .= "<img src='{$TBDEV['pic_base_url']}star.gif' alt='Donated' />";
	if ($arr["anonymous"] == "yes")
	if ($CURUSER['class'] < UC_MODERATOR && $arr["id"] != $CURUSER["id"])
	$forumusers .= "";
	else
	if ($flsupport)
	$forumusers .= "<img src='{$TBDEV['pic_base_url']}supt.gif' alt='FLS' />";
	if ($arr["anonymous"] == "yes")
	if ($CURUSER['class'] < UC_MODERATOR && $arr["id"] != $CURUSER["id"])
	$forumusers .= "";
	else
	if ($warned)
	$forumusers .= "<img src='{$TBDEV['pic_base_url']}warned.gif' alt='Warned' />";
	$forumusers .= "</span>";
	}
	if (!$forumusers)
 	$forumusers = "Currently No Active users in the Forum";
	
 	$topic_post_res = mysql_query("SELECT SUM(topiccount) AS topics, SUM(postcount) AS posts FROM forums");
	$topic_post_arr = mysql_fetch_assoc($topic_post_res);
	
 	$htmlout .="<br />
	<table width='{$forum_width}' border='0' cellspacing='0' cellpadding='5'>
 	<tr>
 	<td class='colhead' align='center'>Now active in Forums:</td>
 	</tr>
	<tr>
	<td class='text'>";
	if ($CURUSER['anonymous'] == 'yes'){
	$htmlout .="<p align='center'>(+) next to your username indicates you are Anonymous !</p>";
	}
 	$htmlout .="{$forumusers}</td>
 	</tr>
 	<tr>
 	<td class='colhead' align='center'><h2>Our members wrote <b>".number_format($topic_post_arr['posts'])."</b> Posts in <b>".number_format($topic_post_arr['topics'])."</b> Threads</h2></td>
 	</tr>
	</table>";
	return $htmlout;
 	}
 	//== End

    function show_forums($forid, $subforums = false, $sfa = "", $mods_array = "", $show_mods = false)
    {
    global $CURUSER, $TBDEV;
    $htmlout='';
    $forums_res = mysql_query("SELECT f.id, f.name, f.description, f.postcount, f.topiccount, f.minclassread, p.added, p.topicid, p.anonymous, p.userid, p.id AS pid, u.username, t.subject, t.lastpost, r.lastpostread " . "FROM forums AS f " . "LEFT JOIN posts AS p ON p.id = (SELECT MAX(lastpost) FROM topics WHERE forumid = f.id) " . "LEFT JOIN users AS u ON u.id = p.userid " . "LEFT JOIN topics AS t ON t.id = p.topicid " . "LEFT JOIN readposts AS r ON r.userid = " . sqlesc($CURUSER['id']) . " AND r.topicid = p.topicid " . "WHERE " . ($subforums == false ? "f.forid = $forid AND f.place =-1 ORDER BY f.forid ASC" : "f.place=$forid ORDER BY f.id ASC") . "") or sqlerr(__FILE__, __LINE__);

    while ($forums_arr = mysql_fetch_assoc($forums_res)) {
        if ($CURUSER['class'] < $forums_arr["minclassread"])
            continue;

        $forumid = (int)$forums_arr["id"];
        $lastpostid = (int)$forums_arr['lastpost'];

        if ($subforums == false && !empty($sfa[$forumid])) {
        if (($sfa[$forumid]['lastpost']['postid'] > $forums_arr['pid'])) {
        if ($sfa[$forumid]['lastpost']["anonymous"] == "yes") {
        if($CURUSER['class'] < UC_MODERATOR && $sfa[$forumid]['lastpost']['userid'] != $CURUSER['id'])	
        $lastpost1 = "Anonymous<br />";
        else
        $lastpost1 = "Anonymous(<a href='{$TBDEV['baseurl']}/userdetails.php?id=" . (int)$sfa[$forumid]['lastpost']['userid'] . "'><b>" . htmlspecialchars($sfa[$forumid]['lastpost']['user']) . "</b></a>)<br />";
        }
        elseif ($sfa[$forumid]['lastpost']["anonymous"] == "no") { 
        $lastpost1 = "<a href='{$TBDEV['baseurl']}/userdetails.php?id=" . (int)$sfa[$forumid]['lastpost']['userid'] . "'><b>" . htmlspecialchars($sfa[$forumid]['lastpost']['user']) . "</b></a><br />";
        }
        $lastpost = "" . get_date($sfa[$forumid]['lastpost']['added'], 'LONG',1,0) . "<br />" . "by $lastpost1" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$sfa[$forumid]['lastpost']['topic'] . "&amp;page=p" . $sfa[$forumid]['lastpost']['postid'] . "#p" . $sfa[$forumid]['lastpost']['postid'] . "'><b>" . htmlspecialchars($sfa[$forumid]['lastpost']['tname']) . "</b></a>";
        }
        elseif (($sfa[$forumid]['lastpost']['postid'] < $forums_arr['pid'])) {
        if ($forums_arr["anonymous"] == "yes") {
        if($CURUSER['class'] < UC_MODERATOR && $forums_arr["userid"] != $CURUSER["id"])	
        $lastpost2 = "Anonymous<br />";
        else
        $lastpost2 = "Anonymous(<a href='{$TBDEV['baseurl']}/userdetails.php?id=" . (int)$forums_arr["userid"] . "'><b>" . htmlspecialchars($forums_arr['username']) . "</b></a>)<br />";
        }
        elseif ($forums_arr["anonymous"] == "no") { 
        $lastpost2 = "<a href='{$TBDEV['baseurl']}/userdetails.php?id=" . (int)$forums_arr["userid"] . "'><b>" . htmlspecialchars($forums_arr['username']) . "</b></a><br />";
        }
        $lastpost = "" .get_date($forums_arr["added"], 'LONG',1,0) . "<br />" . "by $lastpost2" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$forums_arr["topicid"] . "&amp;page=p$lastpostid#p$lastpostid'><b>" . htmlspecialchars($forums_arr['subject']) . "</b></a>";
        } else
        $lastpost = "N/A";
        } else {
        if (is_valid_id($forums_arr['pid']))
        if ($forums_arr["anonymous"] == "yes") {
        if($CURUSER['class'] < UC_MODERATOR && $forums_arr["userid"] != $CURUSER["id"])
        $lastpost ="" .get_date($forums_arr["added"], 'LONG',1,0) . "<br />" . "by <i>Anonymous</i><br />" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$forums_arr["topicid"] . "&amp;page=p$lastpostid#p$lastpostid'><b>" . htmlspecialchars($forums_arr['subject']) . "</b></a>"; 
        else
        $lastpost ="" .get_date($forums_arr["added"], 'LONG',1,0) . "<br />" . "by <i>Anonymous</i>(<a href='{$TBDEV['baseurl']}/userdetails.php?id=" . (int)$forums_arr["userid"] . "'><b>" . htmlspecialchars($forums_arr['username']) . "</b></a>)<br />" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$forums_arr["topicid"] . "&amp;page=p$lastpostid#p$lastpostid'><b>" . htmlspecialchars($forums_arr['subject']) . "</b></a>";
        }
        else 
        $lastpost = "" .get_date($forums_arr["added"], 'LONG',1,0) . "<br />" . "by <a href='{$TBDEV['baseurl']}/userdetails.php?id=" . (int)$forums_arr["userid"] . "'><b>" . htmlspecialchars($forums_arr['username']) . "</b></a><br />" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$forums_arr["topicid"] . "&amp;page=p$lastpostid#p$lastpostid'><b>" . htmlspecialchars($forums_arr['subject']) . "</b></a>";
        else
        $lastpost = "N/A";
        }

        if (is_valid_id($forums_arr['pid']))
            $img = 'unlocked' . ((($forums_arr['added'] > (time() - $TBDEV['readpost_expiry']))?((int)$forums_arr['pid'] > $forums_arr['lastpostread']):0)?'new':'');
        else
            $img = "unlocked";
        if ($subforums == false && !empty($sfa[$forumid])) {
            list($subposts, $subtopics) = get_count($sfa[$forumid]["count"]);
            $topics = $forums_arr["topiccount"] + $subtopics;
            $posts = $forums_arr["postcount"] + $subposts;
        } else {
            $topics = $forums_arr["topiccount"];
            $posts = $forums_arr["postcount"];
        }

      $htmlout.="<tr>
			<td align='left'>
				<table border='0' cellspacing='0' cellpadding='0' style='border:none;'>
					<tr>
						<td class='embedded' style='padding-right: 5px'><img src='".$TBDEV['pic_base_url'].$img.".gif' alt='' /></td>
						<td class='embedded'>
							<a href='".$_SERVER['PHP_SELF']."?action=viewforum&amp;forumid=".$forumid."'><b>". htmlspecialchars($forums_arr["name"])."</b></a>";
             if ($CURUSER['class'] >= UC_ADMINISTRATOR || isMod($forumid)) {
           
            $htmlout.="&nbsp;<font class='small'>[<a class='altlink' href='".$_SERVER['PHP_SELF']."?action=editforum&amp;forumid=".$forumid."'>Edit</a>][<a class='altlink' href='".$_SERVER['PHP_SELF']."?action=deleteforum&amp;forumid=".$forumid."'>Delete</a>]</font>";
        }

        if (!empty($forums_arr["description"])) {

        $htmlout.="<br />". htmlspecialchars($forums_arr["description"]);
        }
        if ($subforums == false && !empty($sfa[$forumid]))
            $htmlout.="<br/>" . subforums($sfa[$forumid]["topics"]);
        if ($show_mods == true && isset($mods_array[$forumid]))
            $htmlout.="<br/>" . showMods($mods_array[$forumid]);

        $htmlout.="</td>
					</tr>
				</table>
			</td>
			<td align='center'>". number_format($topics)."</td>
			<td align='center'>". number_format($posts)."</td>
			<td align='left' nowrap='nowrap'>".$lastpost."</td>
		</tr>";
    }
return $htmlout;
}
// -------- Returns the minimum read/write class levels of a forum
function get_forum_access_levels($forumid)
{
    $res = mysql_query("SELECT minclassread, minclasswrite, minclasscreate FROM forums WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
        return false;

    $arr = mysql_fetch_assoc($res);

    return array("read" => $arr["minclassread"], "write" => $arr["minclasswrite"], "create" => $arr["minclasscreate"]);
}
// -------- Returns the forum ID of a topic, or false on error
function get_topic_forum($topicid)
{
    $res = mysql_query("SELECT forumid FROM topics WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
        return false;

    $arr = mysql_fetch_assoc($res);

    return (int)$arr['forumid'];
}
// -------- Returns the ID of the last post of a forum
function update_topic_last_post($topicid)
{
    $res = mysql_query("SELECT MAX(id) AS id FROM posts WHERE topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res) or die("No post found");

    mysql_query("UPDATE topics SET lastpost = {$arr['id']} WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
}

function get_forum_last_post($forumid)
{
    $res = mysql_query("SELECT MAX(lastpost) AS lastpost FROM topics WHERE forumid = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res);

    $postid = (int)$arr['lastpost'];

    return (is_valid_id($postid) ? $postid : 0);
}
// -------- Inserts a quick jump menu
function insert_quick_jump_menu($currentforum = 0)
{
	global $CURUSER, $TBDEV;
	$htmlout='';
	$htmlout .="
	<form method='get' action='".$_SERVER['PHP_SELF']."' name='jump'>
	<input type='hidden' name='action' value='viewforum' />
	<div align='center'><b>Quick jump:</b>
	<select name='forumid' onchange=\"if(this.options[this.selectedIndex].value != -1){ forms['jump'].submit() }\">";
	$res = mysql_query("SELECT id, name, minclassread FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);
	while ($arr = mysql_fetch_assoc($res))
	if ($CURUSER['class'] >= $arr["minclassread"])
	$htmlout .="<option value='".$arr["id"].($currentforum == $arr["id"] ? " selected" : "")."'>".$arr["name"]."</option>";
  $htmlout .="</select>
	<input type='submit' value='Go!' class='gobutton' />
	</div>
	</form>";
  return $htmlout;
  }
// -------- Inserts a compose frame
    function insert_compose_frame($id, $newtopic = true, $quote = false, $attachment = false)
   {
    global $maxsubjectlength, $CURUSER, $TBDEV, $maxfilesize,  $use_attachment_mod, $forum_pics;
    
    $htmlout='';
    if ($newtopic) {
        $res = mysql_query("SELECT name FROM forums WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res) or die("Bad forum ID!");

        $htmlout .="<h3>New topic in <a href='". $_SERVER['PHP_SELF']."?action=viewforum&amp;forumid=".$id."'>".htmlspecialchars($arr["name"])."</a> forum</h3>";
        } else {
        $res = mysql_query("SELECT t.forumid, t.subject, t.locked, f.minclassread FROM topics AS t LEFT JOIN forums AS f ON f.id = t.forumid WHERE t.id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res) or die("Forum error, Topic not found.");
  
        if ($arr['locked'] == 'yes') {
            stderr("Sorry", "The topic is locked.");

            $htmlout .= end_table();
            $htmlout .= end_main_frame();
            print stdhead("Compose") . $htmlout . stdfoot();
            exit();
        }
        
        if($CURUSER["class"] < $arr["minclassread"]){
		    $htmlout .= stdmsg("Sorry", "You are not allowed in here.");
				$htmlout .= end_table(); 
				$htmlout .= end_main_frame(); 
				print stdhead("Compose") . $htmlout . stdfoot();
		    exit();
		    }
        $htmlout .="<h3 align='center'>Reply to topic: <a href='".$_SERVER['PHP_SELF']."action=viewtopic&amp;topicid=".$id."'>". htmlspecialchars($arr["subject"])."</a></h3>";

    }
     
    $htmlout .="
    <script  type='text/javascript'>
    /*<![CDATA[*/
    function Preview()
    {
    document.compose.action = './preview.php'
    document.compose.target = '_blank';
    document.compose.submit();
    return true;
    }
    /*]]>*/
    </script>";
      
    $htmlout .= begin_frame("Compose", true);
    $htmlout .="<form method='post' name='compose' action='".$_SERVER['PHP_SELF']."' enctype='multipart/form-data'>
	  <input type='hidden' name='action' value='post' />
	  <input type='hidden' name='". ($newtopic ? 'forumid' : 'topicid')."' value='".$id."' />";

    $htmlout .= begin_table(true);

    if ($newtopic) {

       
		$htmlout .="<tr>
			<td class='rowhead' width='10%'>Subject</td>
			<td align='left'>
				<input type='text' size='100' maxlength='".$maxsubjectlength."' name='subject' style='height: 19px' />
			</td>
		</tr>";
    }

    if ($quote) {
        $postid = (int)$_GET["postid"];
        if (!is_valid_id($postid)) {
            stderr("Error", "Invalid ID!");

            $htmlout .= end_table();
            $htmlout .= end_main_frame();
            print stdhead("Compose") . $htmlout . stdfoot();
            exit();
        }

        $res = mysql_query("SELECT posts.*, users.username FROM posts JOIN users ON posts.userid = users.id WHERE posts.id = $postid") or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 0) {
            stderr("Error", "No post with this ID");

            $htmlout .= end_table();
            $htmlout .= end_main_frame();
            print stdhead("Error - No post with this ID") . $htmlout . stdfoot();
            exit();
        }

        $arr = mysql_fetch_assoc($res);
    }

    $htmlout .="<tr>
		<td class='rowhead' width='10%'>Body</td>
		<td>";
		$qbody = ($quote ? "[quote=".htmlspecialchars($arr["username"])."]".htmlspecialchars(unesc($arr["body"]))."[/quote]" : "");
		if (function_exists('textbbcode'))
		$htmlout .= textbbcode("compose", "body", $qbody);
		else
		{
		$htmlout .="<textarea name='body' style='width:99%' rows='7'>{$qbody}</textarea>";
		}
		$htmlout .="</td></tr>";
		if ($use_attachment_mod && $attachment)
		{
		$htmlout .="<tr>
				<td colspan='2'><fieldset class='fieldset'><legend>Add Attachment</legend>
				<input type='checkbox' name='uploadattachment' value='yes' />
				<input type='file' name='file' size='60' />
        <div class='error'>Allowed Files: rar, zip<br />Size Limit ".mksize($maxfilesize)."</div></fieldset>
				</td>
			</tr>";
		  }
		  
		  $htmlout .="<tr>
   	  <td align='center' colspan='2'>".(post_icons())."</td>
 	    </tr><tr>
 		  <td colspan='2' align='center'>
 	    <input type='submit' value='Submit' /><input type='button' value='Preview' name='button2' onclick='return Preview();' />\n";
      if ($newtopic){
      $htmlout .= "Anonymous Topic<input type='checkbox' name='anonymous' value='yes'/>\n";
      }
      else
      {
      $htmlout .= "Anonymous Post<input type='checkbox' name='anonymous' value='yes'/>\n";
      }
      $htmlout .= "</td></tr>\n";


    $htmlout .= end_table();

    $htmlout .="</form>";
    
    $htmlout .= end_frame();
    // ------ Get 10 last posts if this is a reply
    
    if (!$newtopic) {
        $postres = mysql_query("SELECT p.id, p.added, p.body, p.anonymous, u.id AS uid, u.username, u.avatar, u.offavatar " . "FROM posts AS p " . "LEFT JOIN users AS u ON u.id = p.userid " . "WHERE p.topicid = " . sqlesc($id) . " " . "ORDER BY p.id DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($postres) > 0) {

            $htmlout .="<br />";
            $htmlout .= begin_frame("10 last posts, in reverse order");

            while ($post = mysql_fetch_assoc($postres)) {
                $avatar = ($CURUSER["avatars"] == "all" ? htmlspecialchars($post["avatar"]) : ($CURUSER["avatars"] == "some" && $post["offavatar"] == "no" ? htmlspecialchars($post["avatar"]) : ""));
             
             if ($post['anonymous'] == 'yes') {
             $avatar = $TBDEV['pic_base_url'] . $forum_pics['default_avatar'];
             }
             else {
             $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($post["avatar"]) : '');
             }

             if (empty($avatar))
             $avatar = $TBDEV['pic_base_url'] . $forum_pics['default_avatar'];

             if ($post["anonymous"] == "yes")
             if($CURUSER['class'] < UC_MODERATOR && $post["uid"] != $CURUSER["id"]){	
             $htmlout .= "<p class='sub'>#" . $post["id"] . " by <i>Anonymous</i> at ".get_date($post["added"], 'LONG',1,0)."</p>";
             }
             else{	
             $htmlout .= "<p class='sub'>#" . $post["id"] . " by <i>Anonymous</i> (<b>" . $post["username"] . "</b>) at ".get_date($post["added"], 'LONG',1,0)."</p>"; 
             }
             else
             $htmlout .="<p class='sub'>#".$post["id"]." by ". (!empty($post["username"]) ? $post["username"] : "unknown[{$post['uid']}]")." at ".get_date($post["added"], 'LONG',1,0)."</p>";

                $htmlout .= begin_table(true);

                
					$htmlout .="<tr>
						<td height='100' width='100' align='center' style='padding: 0px' valign='top'><img height='100' width='100' src='".$avatar."' alt='User avvy' /></td>
						<td class='comment' valign='top'>". format_comment($post["body"])."</td>
					</tr>";
           $htmlout .= end_table();
            }

            $htmlout .= end_frame();
        }
    }
    $htmlout .= insert_quick_jump_menu();
    return $htmlout;
    }

if ($action == 'updatetopic') {
    $topicid = (isset($_GET['topicid']) ? (int)$_GET['topicid'] : (isset($_POST['topicid']) ? (int)$_POST['topicid'] : 0));
    if (!is_valid_id($topicid))
        stderr('Error...', 'Invalid topic ID!');

    $topic_res = mysql_query('SELECT t.sticky, t.locked, t.subject, t.forumid, f.minclasswrite, ' . '(SELECT COUNT(id) FROM posts WHERE topicid = t.id) As post_count ' . 'FROM topics AS t ' . 'LEFT JOIN forums AS f ON f.id = t.forumid ' . 'WHERE t.id = ' . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($topic_res) == 0)
        stderr('Error...', 'No topic with that ID!');

    $topic_arr = mysql_fetch_assoc($topic_res);
    if (isMod($topic_arr["forumid"]) || $CURUSER['class'] >= UC_MODERATOR) {
        if (($CURUSER['class'] < (int)$topic_arr['minclasswrite']) && !isMod($topic_arr["forumid"]))
            stderr('Error...', 'You are not allowed to edit this topic.');

        $forumid = (int)$topic_arr['forumid'];
        $subject = $topic_arr['subject'];

        if ((isset($_GET['delete']) ? $_GET['delete'] : (isset($_POST['delete']) ? $_POST['delete'] : '')) == 'yes') {
            if ((isset($_GET['sure']) ? $_GET['sure'] : (isset($_POST['sure']) ? $_POST['sure'] : '')) != 'yes')
                stderr("Sanity check...", "You are about to delete this topic: <b>" . htmlspecialchars($subject) . "</b>. Click <a href='" . $_SERVER['PHP_SELF'] . "?action=$action&amp;topicid=$topicid&amp;delete=yes&amp;sure=yes'>here</a> if you are sure.");

            write_log("topicdelete","Topic <b>" . $subject . "</b> was deleted by <a href='{$TBDEV['baseurl']}/userdetails.php?id=" . $CURUSER['id'] . "'>" . $CURUSER['username'] . "</a>.");

            if ($use_attachment_mod) {
                $res = mysql_query("SELECT attachments.filename " . "FROM posts " . "LEFT JOIN attachments ON attachments.postid = posts.id " . "WHERE posts.topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

                while ($arr = mysql_fetch_assoc($res))
                if (!empty($arr['filename']) && is_file($attachment_dir . "/" . $arr['filename']))
                    unlink($attachment_dir . "/" . $arr['filename']);
            }

            mysql_query("DELETE posts, topics " .
                ($use_attachment_mod ? ", attachments, attachmentdownloads " : "") .
                ($use_poll_mod ? ", postpolls, postpollanswers " : "") . "FROM topics " . "LEFT JOIN posts ON posts.topicid = topics.id " .
                ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") .
                ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id = topics.pollid " . "LEFT JOIN postpollanswers ON postpollanswers.pollid = postpolls.id " : "") . "WHERE topics.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=viewforum&forumid=' . $forumid);
            exit();
        }

        $returnto = $_SERVER['PHP_SELF'] . '?action=viewtopic&topicid=' . $topicid;

        $updateset = array();

        $locked = ($_POST['locked'] == 'yes' ? 'yes' : 'no');
        if ($locked != $topic_arr['locked'])
            $updateset[] = 'locked = ' . sqlesc($locked);

        $sticky = ($_POST['sticky'] == 'yes' ? 'yes' : 'no');
        if ($sticky != $topic_arr['sticky'])
            $updateset[] = 'sticky = ' . sqlesc($sticky);

        $new_subject = $_POST['subject'];
        if ($new_subject != $subject) {
            if (empty($new_subject))
                stderr('Error...', 'Topic name cannot be empty.');

            $updateset[] = 'subject = ' . sqlesc($new_subject);
        }

        $new_forumid = (int)$_POST['new_forumid'];
        if (!is_valid_id($new_forumid))
            stderr('Error...', 'Invalid forum ID!');

        if ($new_forumid != $forumid) {
            $post_count = (int)$topic_arr['post_count'];

            $res = mysql_query("SELECT minclasswrite FROM forums WHERE id = " . sqlesc($new_forumid)) or sqlerr(__FILE__, __LINE__);

            if (mysql_num_rows($res) != 1)
                stderr("Error...", "Forum not found!");

            $arr = mysql_fetch_assoc($res);
            if ($CURUSER['class'] < (int)$arr['minclasswrite'])
                stderr('Error...', 'You are not allowed to move this topic into the selected forum.');

            $updateset[] = 'forumid = ' . sqlesc($new_forumid);

            mysql_query("UPDATE forums SET topiccount = topiccount - 1, postcount = postcount - " . sqlesc($post_count) . " WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
            mysql_query("UPDATE forums SET topiccount = topiccount + 1, postcount = postcount + " . sqlesc($post_count) . " WHERE id = " . sqlesc($new_forumid)) or sqlerr(__FILE__, __LINE__);

            $returnto = $_SERVER['PHP_SELF'] . '?action=viewforum&forumid=' . $new_forumid;
        }

        if (sizeof($updateset) > 0)
            mysql_query("UPDATE topics SET " . implode(', ', $updateset) . " WHERE id = " . sqlesc($topicid));

        header('Location: ' . $returnto);
        exit();
    }
} else if ($action == "editforum") { // -------- Action: Edit Forum
        $forumid = (int)$_GET["forumid"];
        if ($CURUSER['class'] == MAX_CLASS || isMod($forumid)) {
        if (!is_valid_id($forumid))
            stderr('Error', 'Invalid ID!');

        $res = mysql_query("SELECT name, description, minclassread, minclasswrite, minclasscreate FROM forums WHERE id = $forumid") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($res) == 0)
        stderr('Error', 'No forum found with that ID!');

        $forum = mysql_fetch_assoc($res);

        
        if ($TBDEV['forums_online'] == 0)
        $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
        $HTMLOUT .= begin_main_frame();
        $HTMLOUT .= begin_frame("Edit Forum", "center");
        $HTMLOUT .="<form method='post' action='" . $_SERVER['PHP_SELF'] . "?action=updateforum&amp;forumid=$forumid'>\n";
        $HTMLOUT .= begin_table();
        $HTMLOUT .="<tr><td class='rowhead'>Forum name</td>
        <td align='left' style='padding: 0px'><input type='text' size='60' maxlength='$maxsubjectlength' name='name' style='border: 0px; height: 19px' value=\"" . htmlspecialchars($forum['name']) . "\" /></td></tr>
        <tr><td class='rowhead'>Description</td><td align='left' style='padding: 0px'><textarea name='description' cols='68' rows='3' style='border: 0px'>" . htmlspecialchars($forum['description']) . "</textarea></td></tr>
        <tr><td class='rowhead'></td><td align='left' style='padding: 0px'>&nbsp;Minimum <select name='readclass'>";
        for ($i = 0; $i <= MAX_CLASS; ++$i)
        $HTMLOUT .="<option value='$i' " . ($i == $forum['minclassread'] ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";
        $HTMLOUT .="</select> Class required to View<br />\n&nbsp;Minimum <select name='writeclass'>";
        for ($i = 0; $i <= MAX_CLASS; ++$i)
        $HTMLOUT .="<option value='$i' " . ($i == $forum['minclasswrite'] ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";
        $HTMLOUT .="</select> Class required to Post<br />\n&nbsp;Minimum <select name='createclass'>";
        for ($i = 0; $i <= MAX_CLASS; ++$i)
        $HTMLOUT .="<option value='$i' " . ($i == $forum['minclasscreate'] ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";
        $HTMLOUT .="</select> Class required to Create Topics</td></tr>
        <tr><td colspan='2' align='center'><input type='submit' value='Submit' /></td></tr>\n";
        $HTMLOUT .= end_table();
        $HTMLOUT .="</form>";

        $HTMLOUT .= end_frame();
        $HTMLOUT .= end_main_frame();
        print stdhead("{$lang['forums_title']}") . $HTMLOUT . stdfoot();
        exit();
    }
} else if ($action == "updateforum") { // -------- Action: Update Forum
        $forumid = (int)$_GET["forumid"];
    if ($CURUSER['class'] == MAX_CLASS || isMod($forumid)) {
        if (!is_valid_id($forumid))
            stderr('Error', 'Invalid ID!');

        $res = mysql_query('SELECT id FROM forums WHERE id = ' . sqlesc($forumid));
        if (mysql_num_rows($res) == 0)
            stderr('Error', 'No forum with that ID!');

        $name = $_POST['name'];
        $description = $_POST['description'];

        if (empty($name))
            stderr("Error", "You must specify a name for the forum.");

        if (empty($description))
            stderr("Error", "You must provide a description for the forum.");

        mysql_query("UPDATE forums SET name = " . sqlesc($name) . ", description = " . sqlesc($description) . ", minclassread = " . sqlesc((int)$_POST['readclass']) . ", minclasswrite = " . sqlesc((int)$_POST['writeclass']) . ", minclasscreate = " . sqlesc((int)$_POST['createclass']) . " WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
} else if ($action == 'deleteforum') { // -------- Action: Delete Forum
        $forumid = (int)$_GET['forumid'];
    if ($CURUSER['class'] == MAX_CLASS || isMod($forumid)) {
        if (!is_valid_id($forumid))
            stderr('Error', 'Invalid ID!');

        $confirmed = (int)isset($_GET['confirmed']) && (int)$_GET['confirmed'];
        if (!$confirmed) {
            $rt = mysql_query("SELECT topics.id, forums.name " . "FROM topics " . "LEFT JOIN forums ON forums.id=topics.forumid " . "WHERE topics.forumid = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
            $topics = mysql_num_rows($rt);
            $posts = 0;

            if ($topics > 0) {
                while ($topic = mysql_fetch_assoc($rt)) {
                    $ids[] = $topic['id'];
                    $forum = $topic['name'];
                }

                $rp = mysql_query("SELECT COUNT(id) FROM posts WHERE topicid IN (" . join(', ', $ids) . ")");
                foreach ($ids as $id)
                if ($a = mysql_fetch_row($rp))
                    $posts += $a[0];
            }

            if ($use_attachment_mod || $use_poll_mod) {
                $res = mysql_query("SELECT " .
                    ($use_attachment_mod ? "COUNT(attachments.id) AS attachments " : "") .
                    ($use_poll_mod ? ($use_attachment_mod ? ', ' : '') . "COUNT(postpolls.id) AS polls " : "") . "FROM topics " . "LEFT JOIN posts ON topics.id=posts.topicid " .
                    ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " : "") .
                    ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id=topics.pollid " : "") . "WHERE topics.forumid=" . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

                ($use_attachment_mod ? $attachments = 0 : null);
                ($use_poll_mod ? $polls = 0 : null);

                if ($arr = mysql_fetch_assoc($res)) {
                    ($use_attachment_mod ? $attachments = $arr['attachments'] : null);
                    ($use_poll_mod ? $polls = $arr['polls'] : null);
                }
            }
            stderr("** WARNING! **", "Deleting forum with id=$forumid (" . $forumid . ") will also delete " . $posts . " post" . ($posts != 1 ? 's' : '') . ($use_attachment_mod ? ", " . $attachments . " attachment" . ($attachments != 1 ? 's' : '') : "") . ($use_poll_mod ? " and " . ($polls - $attachments) . " poll" . (($polls - $attachments) != 1 ? 's' : '') : "") . " in " . $topics . " topic" . ($topics != 1 ? 's' : '') . ". [<a href=" . $_SERVER['PHP_SELF'] . "?action=deleteforum&amp;forumid=$forumid&amp;confirmed=1>ACCEPT</a>] [<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid>CANCEL</a>]");
        }

        $rt = mysql_query("SELECT topics.id " . ($use_attachment_mod ? ", attachments.filename " : "") . "FROM topics " . "LEFT JOIN posts ON topics.id = posts.topicid " .
            ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " : "") . "WHERE topics.forumid = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

        $topics = mysql_num_rows($rt);
		    if ($topics == 0){
		    mysql_query("DELETE FROM forums WHERE id = ".sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
			  header("Location: {$_SERVER['PHP_SELF']}");
	      exit();
        } 

        while ($topic = mysql_fetch_assoc($rt)) {
            $tids[] = $topic['id'];

            if ($use_attachment_mod && !empty($topic['filename'])) {
                $filename = $attachment_dir . "/" . $topic['filename'];
                if (is_file($filename))
                    unlink($filename);
            }
        }

        mysql_query("DELETE posts.*, topics.*, forums.* " . ($use_attachment_mod ? ", attachments.*, attachmentdownloads.* " : "") . ($use_poll_mod ? ", postpolls.*, postpollanswers.* " : "") . "FROM posts " .
            ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") . "LEFT JOIN topics ON topics.id = posts.topicid " . "LEFT JOIN forums ON forums.id = topics.forumid " .
            ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id = topics.pollid " . "LEFT JOIN postpollanswers ON postpollanswers.pollid = postpolls.id " : "") . "WHERE posts.topicid IN (" . join(', ', $tids) . ")") or sqlerr(__FILE__, __LINE__);

        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
} else if ($action == "newtopic") { // -------- Action: New topic
        $forumid = (int)$_GET["forumid"];
    if (!is_valid_id($forumid))
        stderr('Error', 'Invalid ID!');

   
    $HTMLOUT .= begin_main_frame();
    if ($TBDEV['forums_online'] == 0)
    $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
    $HTMLOUT .= insert_compose_frame($forumid, true, false, true);
    $HTMLOUT .= end_main_frame();
    print stdhead("New Topic") . $HTMLOUT . stdfoot();
    exit();
} else if ($action == "post") { // -------- Action: Post
        $forumid = (isset($_POST['forumid']) ? (int)$_POST['forumid'] : null);
    if (isset($forumid) && !is_valid_id($forumid))
        stderr('Error', 'Invalid forum ID!');

    $posticon = (isset($_POST["iconid"]) ? 0 + $_POST["iconid"] : 0);
    $topicid = (isset($_POST['topicid']) ? (int)$_POST['topicid'] : null);
    if (isset($topicid) && !is_valid_id($topicid))
        stderr('Error', 'Invalid topic ID!');

    $newtopic = is_valid_id($forumid);

    $subject = (isset($_POST["subject"]) ? $_POST["subject"] : '');

    if ($newtopic) {
        $subject = trim($subject);

        if (empty($subject))
            stderr("Error", "You must enter a subject.");

        if (strlen($subject) > $maxsubjectlength)
            stderr("Error", "Subject is limited to " . $maxsubjectlength . " characters.");
    } else
        $forumid = get_topic_forum($topicid) or die("Bad topic ID");


    // ------ Make sure sure user has write access in forum
    $arr = get_forum_access_levels($forumid) or die("Bad forum ID");

    if ($CURUSER['class'] < $arr["write"] || ($newtopic && $CURUSER['class'] < $arr["create"]) && !isMod($forumid))
        stderr("Error", "Permission denied.");

    $body = trim($_POST["body"]);

    if (empty($body))
        stderr("Error", "No body text.");

    $userid = (int)$CURUSER["id"];

    if ($use_flood_mod && $CURUSER['class'] < UC_MODERATOR && !isMod($forumid)) {
        $res = mysql_query("SELECT COUNT(id) AS c FROM posts WHERE userid = " . $CURUSER['id'] . " AND added > '" . (time() - ($minutes * 60)) . "'");
        $arr = mysql_fetch_assoc($res);

        if ($arr['c'] > $limit)
            stderr("Flood", "More than " . $limit . " posts in the last " . $minutes . " minutes.");
    }
    if ($newtopic)
	  {
    $subject = sqlesc($subject);
  	$anonymous = (isset($_POST['anonymous']) && $_POST["anonymous"] != "" ? "yes" : "no");
 	  mysql_query("INSERT INTO topics (userid, forumid, subject, anonymous) VALUES($userid, $forumid, $subject, ".sqlesc($anonymous).")") or sqlerr(__FILE__, __LINE__);
		$topicid = mysql_insert_id() or stderr("Error", "No topic ID returned!");
	  
		$added = sqlesc(time());
	  $body = sqlesc($body);
	  $anonymous = (isset($_POST['anonymous']) && $_POST["anonymous"] != "" ? "yes" : "no");
	  mysql_query("INSERT INTO posts (topicid, userid, added, body, anonymous, posticon) VALUES($topicid, $userid, $added, $body, ".sqlesc($anonymous).",$posticon)") or sqlerr(__FILE__, __LINE__);
		$postid = mysql_insert_id() or stderr("Error", "No post ID returned!");
	  update_topic_last_post($topicid);
	  if($TBDEV['forums_autoshout_on'] == 1){
	  if ($anonymous == 'yes')
	  $message = "(Anonymous) Created a new forum thread [url={$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid&page=last]{$subject}[/url]";
	  else
	  $message = $CURUSER['username'] . " Created a new forum thread [url={$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid&page=last]{$subject}[/url]";
	  //////remember to edit the ids to your staffforum ids :)
	  if (!in_array($forumid, array("18","23","24","25"))) {
  	autoshout($message);
	  }
	  }
	  if($TBDEV['forums_seedbonus_on'] == 1){
	  mysql_query("UPDATE users SET seedbonus = seedbonus+3.0 WHERE id =  ". sqlesc($CURUSER['id']."")) or sqlerr(__FILE__, __LINE__);
	  }
	  }
	  else
	  {
		//---- Make sure topic exists and is unlocked
		$res = mysql_query("SELECT locked, subject FROM topics WHERE id = ".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
		if (mysql_num_rows($res) == 0)
			stderr('Error', 'Inexistent Topic!');
		
		$arr = mysql_fetch_assoc($res);
		$subject = htmlspecialchars($arr["subject"]);
		if ($arr["locked"] == 'yes' && $CURUSER['class'] < UC_MODERATOR)
			stderr("Error", "This topic is locked; No new posts are allowed.");
		 // === PM subscribed members
        $res_sub = mysql_query("SELECT userid FROM subscriptions  WHERE topicid = ".sqlesc($topicid)."") or sqlerr(__FILE__, __LINE__);
        while ($row = mysql_fetch_assoc($res_sub)) {
            $res_yes = mysql_query("SELECT subscription_pm, username FROM users WHERE id = ".sqlesc($row["userid"])."") or sqlerr(__FILE__, __LINE__);
            $arr_yes = mysql_fetch_array($res_yes);
            $msg = "Hey there!!! \n a thread you subscribed to: " .htmlspecialchars($arr["subject"]) . " has had a new post!\n click [url=" . $TBDEV['baseurl'] . "/forums.php?action=viewtopic&topicid=" . $topicid . "&page=last][b]HERE[/b][/url] to read it!\n\nTo view your subscriptions, or un-subscribe, click [url=" . $TBDEV['baseurl'] . "/subscriptions.php][b]HERE[/b][/url].\n\ncheers.";
            if ($arr_yes["subscription_pm"] == 'yes' && $row["userid"] != $CURUSER["id"])
            mysql_query("INSERT INTO messages (sender, subject, receiver, added, msg) VALUES(".$TBDEV['bot_id'].", 'New post in subscribed thread!', $row[userid], '" . time() . "', " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
         }
    // ===end
		//------ Check double post     
		$doublepost = mysql_query("SELECT p.id, p.added, p.userid, p.body, t.lastpost, t.id ".
								  "FROM posts AS p ".
								  "INNER JOIN topics AS t ON p.id = t.lastpost ".
								  "WHERE t.id = $topicid AND p.userid = $userid AND p.added > ".(time() - 1*86400)." ".
								  "ORDER BY p.added asc LIMIT 1") or sqlerr(__FILE__, __LINE__);
		  if (mysql_num_rows($doublepost) == 0 || $CURUSER['class'] >= UC_MODERATOR)
		  {
	    $added = sqlesc(time());
	    $body = sqlesc($body);
	    $anonymous = (isset($_POST['anonymous']) && $_POST["anonymous"] != "" ? "yes" : "no");
	    mysql_query("INSERT INTO posts (topicid, userid, added, body, anonymous, posticon) VALUES($topicid, $userid, $added, $body, ".sqlesc($anonymous).",$posticon)") or sqlerr(__FILE__, __LINE__);
    	
			$postid = mysql_insert_id() or die("Post id n/a");
			
			if($TBDEV['forums_seedbonus_on'] == 1){
 	    mysql_query("UPDATE users SET seedbonus = seedbonus+2.0 WHERE id = ".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
			}
			if($TBDEV['forums_autoshout_on'] == 1){
			if ($anonymous == 'yes')
      $message = "(Anonymous) replied to the thread [url={$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid&page=last]{$subject}[/url]"; 
      else 
      $message = $CURUSER['username'] . " replied to the thread [url={$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid&page=last]{$subject}[/url]"; 	
 	    //////remember to edit the ids to your staffforum ids :)
 	    if (!in_array($forumid, array("18","23","24","25"))) {
 	    autoshout($message);
 	    }
			}
			
			$HTMLOUT .= update_topic_last_post($topicid);
            } else {
            $results = mysql_fetch_assoc($doublepost);
            $postid = (int)$results['lastpost'];
            mysql_query("UPDATE posts SET body = " . sqlesc(trim($results['body']) . "\n\n" . $body) . ", editedat = " . time(). ", editedby = $userid, posticon=$posticon WHERE id=$postid") or sqlerr(__FILE__, __LINE__);
            }
            }

    if ($use_attachment_mod && ((isset($_POST['uploadattachment']) ? $_POST['uploadattachment'] : '') == 'yes')) {
        $file = $_FILES['file'];

        $fname = trim(stripslashes($file['name']));
        $size = $file['size'];
        $tmpname = $file['tmp_name'];
        $tgtfile = $attachment_dir . "/" . $fname;
        $pp = pathinfo($fname = $file['name']);
        $error = $file['error'];
        $type = $file['type'];

        $uploaderror = '';

        if (empty($fname))
            $uploaderror = "Invalid Filename!";

        if (!validfilename($fname))
            $uploaderror = "Invalid Filename!";

        foreach ($allowed_file_extensions as $allowed_file_extension);
        if (!preg_match('/^(.+)\.[' . join(']|[', $allowed_file_extensions) . ']$/si', $fname, $matches))
            $uploaderror = 'Only files with the following extensions are allowed: ' . join(', ', $allowed_file_extensions) . '.';

        if ($size > $maxfilesize)
            $uploaderror = "Sorry, that file is too large.";

        if ($pp['basename'] != $fname)
            $uploaderror = "Bad file name.";

        if (file_exists($tgtfile))
            $uploaderror = "Sorry, a file with the name already exists.";

        if (!is_uploaded_file($tmpname))
            $uploaderror = "Can't Upload file!";

        if (!filesize($tmpname))
            $uploaderror = "Empty file!";

        if ($error != 0)
            $uploaderror = "There was an error while uploading the file.";

        if (empty($uploaderror)) {
            mysql_query("INSERT INTO attachments (topicid, postid, filename, size, owner, added, type) VALUES ('$topicid','$postid'," . sqlesc($fname) . ", " . sqlesc($size) . ", '$userid', " . time() . ", " . sqlesc($type) . ")") or sqlerr(__FILE__, __LINE__);

            move_uploaded_file($tmpname, $tgtfile);
        }
    }

    $headerstr = "Location: " . $_SERVER['PHP_SELF'] . "?action=viewtopic&topicid=$topicid" . ($use_attachment_mod && !empty($uploaderror) ? "&uploaderror=$uploaderror" : "") . "&page=last";

    header($headerstr . ($newtopic ? '' : "#p$postid"));
    exit();
} else if ($action == "viewtopic") { // -------- Action: View topic
        $userid = (int)$CURUSER["id"];

    if ($use_poll_mod && $_SERVER['REQUEST_METHOD'] == "POST") {
        $choice = $_POST['choice'];
        $pollid = (int)$_POST["pollid"];
        if (ctype_digit($choice) && $choice < 256 && $choice == floor($choice)) {
            $res = mysql_query("SELECT pa.id " . "FROM postpolls AS p " . "LEFT JOIN postpollanswers AS pa ON pa.pollid = p.id AND pa.userid = " . sqlesc($userid) . " " . "WHERE p.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);
            $arr = mysql_fetch_assoc($res) or stderr('Sorry', 'Inexistent poll!');

            if (is_valid_id($arr['id']))
                stderr("Error...", "Dupe vote");

            mysql_query("INSERT INTO postpollanswers VALUES(id, " . sqlesc($pollid) . ", " . sqlesc($userid) . ", " . sqlesc($choice) . ")") or sqlerr(__FILE__, __LINE__);

            if (mysql_affected_rows() != 1)
                stderr("Error...", "An error occured. Your vote has not been counted.");
        } else
            stderr("Error..." , "Please select an option.");
    }

    $topicid = (int)$_GET["topicid"];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid topic ID!');

    $page = (isset($_GET["page"]) ? $_GET["page"] : 0);
    // ------ Get topic info
    $res = mysql_query("SELECT " . ($use_poll_mod ? 't.pollid, ' : '') . "t.locked, t.subject, t.sticky, t.userid AS t_userid, t.forumid, f.name AS forum_name, f.minclassread, f.minclasswrite, f.minclasscreate, (SELECT COUNT(id)FROM posts WHERE topicid = t.id) AS p_count " . "FROM topics AS t " . "LEFT JOIN forums AS f ON f.id = t.forumid " . "WHERE t.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr("Error", "Topic not found");
    mysql_free_result($res);

    ($use_poll_mod ? $pollid = (int)$arr["pollid"] : null);
    $t_userid = (int)$arr['t_userid'];
    $locked = ($arr['locked'] == 'yes' ? true : false);
    $subject = $arr['subject'];
    $sticky = ($arr['sticky'] == "yes" ? true : false);
    $forumid = (int)$arr['forumid'];
    $forum = $arr["forum_name"];
    $postcount = (int)$arr['p_count'];
    if ($CURUSER["class"] < $arr["minclassread"])
        stderr("Error", "You are not permitted to view this topic.");
    // ------ Update hits column
    mysql_query("UPDATE topics SET views = views + 1 WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
    //------ Make page menu
	$pagemenu1 = "<p align='center'>";
	$perpage = $postsperpage;
	$pages = ceil($postcount / $perpage);
	
	if ($page[0] == "p")
	{
		$findpost = substr($page, 1);
		$res = mysql_query("SELECT id FROM posts WHERE topicid=$topicid ORDER BY added") or sqlerr(__FILE__, __LINE__);
		$i = 1;
		while ($arr = mysql_fetch_row($res))
		{
			if ($arr[0] == $findpost)
				break;
			++$i;
		}
		$page = ceil($i / $perpage);
	}
	
	if ($page == "last")
		$page = $pages;
	else
	{
		if ($page < 1)
			$page = 1;
		else if ($page > $pages)
			$page = $pages;
	}
	
	$offset = ((int)$page * $perpage) - $perpage;
	$offset = ($offset < 0 ? 0 : $offset);
	
	$pagemenu2 = '';
	for ($i = 1; $i <= $pages; ++$i)
		$pagemenu2 .= ($i == $page ? "<b>[<u>$i</u>]</b>" : "<a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=$topicid&amp;page=$i'><b>$i</b></a>");
	
	$pagemenu1 .= ($page == 1 ? "<b>&lt;&lt;&nbsp;Prev</b>" : "<a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=$topicid&amp;page=".($page - 1)."'><b>&lt;&lt;&nbsp;Prev</b></a>");
	$pmlb = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$pagemenu3 = ($page == $pages ? "<b>Next&nbsp;&gt;&gt;</b></p>" : "<a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=$topicid&amp;page=".($page + 1)."'><b>Next&nbsp;&gt;&gt;</b></a></p>");
	
	$HTMLOUT .= begin_main_frame();

	if ($use_poll_mod && is_valid_id($pollid))
	{
		$res = mysql_query("SELECT p.*, pa.id AS pa_id, pa.selection FROM postpolls AS p LEFT JOIN postpollanswers AS pa ON pa.pollid = p.id AND pa.userid = ".$CURUSER['id']." WHERE p.id = ".sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);
	
		if (mysql_num_rows($res) > 0)
		{
			$arr1 = mysql_fetch_assoc($res);
			
			$userid = (int)$CURUSER['id'];
			$question = htmlspecialchars($arr1["question"]);
			$o = array($arr1["option0"], $arr1["option1"], $arr1["option2"], $arr1["option3"], $arr1["option4"],
		  $arr1["option5"], $arr1["option6"], $arr1["option7"], $arr1["option8"], $arr1["option9"],
		  $arr1["option10"], $arr1["option11"], $arr1["option12"], $arr1["option13"], $arr1["option14"],
		  $arr1["option15"], $arr1["option16"], $arr1["option17"], $arr1["option18"], $arr1["option19"]);
			
			$HTMLOUT .="<table cellpadding='5' width='{$forum_width}' align='center'>
			<tr><td class='colhead' align='left'><h2>Poll";
			if ($userid == $t_userid || $CURUSER['class'] >= UC_MODERATOR)
			{
			$HTMLOUT .="<font class='small'> - [<a href='".$_SERVER['PHP_SELF']."?action=makepoll&amp;subaction=edit&amp;pollid=".$pollid."'><b>Edit</b></a>]</font>";
			if ($CURUSER['class'] >= UC_MODERATOR)
			{
			$HTMLOUT .="<font class='small'> - [<a href='".$_SERVER['PHP_SELF']."?action=deletepoll&amp;pollid=".$pollid."'><b>Delete</b></a>]</font>";
			}
			}
			$HTMLOUT .="</h2></td></tr>";

			$HTMLOUT .="<tr><td align='center' class='clearalt7'>";
			$HTMLOUT .="
			<table width='55%'>
			<tr><td class='clearalt6'>
			<div align='center'><b>
			{$question}</b></div>";
			
			
			$voted = (is_valid_id($arr1['pa_id']) ? true : false);
			
			if (($locked && $CURUSER['class'] < UC_MODERATOR) ? true : $voted)
			{
				$uservote = ($arr1["selection"] != '' ? (int)$arr1["selection"] : -1);
				
				$res3 = mysql_query("SELECT selection FROM postpollanswers WHERE pollid = ".sqlesc($pollid)." AND selection < 20");
				$tvotes = mysql_num_rows($res3);
			   				
			$vs = $os = array();
      for($i=0;$i<20;$i++) $vs[$i]=0;

				
				while ($arr3 = mysql_fetch_row($res3))
					$vs[$arr3[0]] += 1;
				
				reset($o);
				for ($i = 0; $i < count($o); ++$i)
					if ($o[$i])
						$os[$i] = array($vs[$i], $o[$i]);
				
				function srt($a,$b)
				{
					if ($a[0] > $b[0])
						return -1;
						
					if ($a[0] < $b[0])
						return 1;
				
					return 0;
				}

				
				if ($arr1["sort"] == "yes")
					usort($os, "srt");
				
				$HTMLOUT .="<br />
			  <table width='100%' style='border:none;' cellpadding='5'>";
			
         foreach($os as $a) 
				{
					if ($i == $uservote)
						$a[1] .= " *";
					
					$p = ($tvotes == 0 ? 0 : round($a[0] / $tvotes * 100));				
					$c = ($i % 2 ? '' : "poll");
					
					$p = ($tvotes == 0 ? 0 : round($a[0] / $tvotes * 100));				
					$c = ($i % 2 ? '' : "poll");
					$HTMLOUT .="<tr>";
	        $HTMLOUT .="<td width='1%' style='padding:3px;white-space:nowrap;' class='embedded".$c."'>".htmlspecialchars($a[1])."</td>";
					$HTMLOUT .="<td width='99%' class='embedded".$c."' align='center'>";
					$HTMLOUT .="<img src='{$TBDEV['pic_base_url']}bar_left.gif' alt='bar_left.gif' />
					<img src='{$TBDEV['pic_base_url']}bar.gif' alt='bar.gif'  height='9' width='". ($p*3)."' />
					<img src='{$TBDEV['pic_base_url']}bar_right.gif'  alt='bar_right.gif' />&nbsp;".$p."%</td>
					</tr>";
				  }
				  $HTMLOUT .="</table>";
				  $HTMLOUT .="<p align='center'>Votes: <b>".number_format($tvotes)."</b></p>";
			    }
		    	else
			    {
				  $HTMLOUT .="<form method='post' action='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=".$topicid."'>
				  <input type='hidden' name='pollid' value='".$pollid."' />";
				  for ($i=0; $a = $o[$i]; ++$i)
				  $HTMLOUT .="<input type='radio' name='choice' value='$i' />".htmlspecialchars($a)."<br />";
				  $HTMLOUT .="<br />";
				  $HTMLOUT .="<p align='center'><input type='submit' value='Vote!' /></p></form>";
			    }
			    $HTMLOUT .="</td></tr></table>";
			
			    $listvotes = (isset($_GET['listvotes']) ? true : false);
			    if ($CURUSER['class'] >= UC_ADMINISTRATOR)
			    {
			    if (!$listvotes)
			    $HTMLOUT .="<a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=$topicid&amp;listvotes'>List Voters</a>";
				  else
				  {
				  $res4 = mysql_query("SELECT pa.userid, u.username, u.anonymous FROM postpollanswers AS pa LEFT JOIN users AS u ON u.id = pa.userid WHERE pa.pollid = ".sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);
				  $voters = '';
				  while ($arr4 = mysql_fetch_assoc($res4))
				  {
				  if (!empty($voters) && !empty($arr4['username']))
          $voters .= ', ';
 	        if ($arr4["anonymous"] == "yes") {
				  if($CURUSER['class'] < UC_MODERATOR && $arr4["userid"] != $CURUSER["id"])
				  $voters = "<i>Anonymous</i>";
         	else
 	        $voters = "<i>Anonymous</i>(<a href='{$TBDEV['baseurl']}/userdetails.php?id=".(int)$arr4['userid']."'><b>".$arr4['username']."</b></a>)";
 	        }
 	        else
				  $voters .= "<a href='{$TBDEV['baseurl']}/userdetails.php?id=".(int)$arr4['userid']."'><b>".htmlspecialchars($arr4['username'])."</b></a>";
				  }
				  $HTMLOUT .= $voters."<br />(<font class='small'><a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=$topicid'>hide</a></font>)";
				  }
			    }
		      $HTMLOUT .="</td></tr></table>";
		    }
		    else
		    {
			  $HTMLOUT .="<br />";
			  stderr('Sorry', "Poll doesn't exist");
		    }
		    $HTMLOUT .="<br />";
		    }
	      $HTMLOUT .="<a name='top'></a>
        <h1 align='left'><a href='".$_SERVER['PHP_SELF']."?action=viewforum&amp;forumid=".$forumid."'>{$forum}</a> &gt; ".htmlspecialchars($subject)."</h1>";
       $HTMLOUT .="<br /><a href='{$TBDEV['baseurl']}/subscriptions.php?topicid=$topicid&amp;subscribe=1'><b><font color='red'>Subscribe to Forum</font></b></a>";
       $HTMLOUT .="<br /><br />";

    
$HTMLOUT .="
<script  type='text/javascript'>
/*<![CDATA[*/
function confirm_att(id)
{
   if(confirm('Are you sure you want to delete this ?'))
   {
		window.open('".$_SERVER['PHP_SELF']."?action=attachment&amp;subaction=delete&amp;attachmentid='+id,'attachment','toolbar=no, scrollbars=yes, resizable=yes, width=600, height=250, top=50, left=50');
		window.location.reload(true)
   }
}
    function popitup(url) {
    newwindow=window.open(url,'./usermood.php','height=335,width=735,resizable=no,scrollbars=no,toolbar=no,menubar=no');
    if (window.focus) {newwindow.focus()}
    return false;
    }
/*]]>*/
</script>";

    // ------ echo table
    $HTMLOUT .= begin_frame();
    $res = mysql_query("SELECT p.id, p.added, p.userid, p.added, p.body, p.editedby, p.editedat, p.posticon, p.anonymous as p_anon, u.id as uid, u.username as uusername, u.class, u.avatar, u.offavatar, u.donor, u.title, u.username, u.reputation, u.mood, u.anonymous, u.country, u.enabled, u.warned, u.uploaded, u.downloaded, u.signature, u.support, u.last_access, (SELECT COUNT(id)  FROM posts WHERE userid = u.id) AS posts_count, u2.username as u2_username " . ($use_attachment_mod ? ", at.id as at_id, at.filename as at_filename, at.postid as at_postid, at.size as at_size, at.downloads as at_downloads, at.owner as at_owner " : "") . ", (SELECT lastpostread FROM readposts WHERE userid = " . sqlesc((int)$CURUSER['id']) . " AND topicid = p.topicid LIMIT 1) AS lastpostread " . "FROM posts AS p " . "LEFT JOIN users AS u ON p.userid = u.id " .
        ($use_attachment_mod ? "LEFT JOIN attachments AS at ON at.postid = p.id " : "") . "LEFT JOIN users AS u2 ON u2.id = p.editedby " . "WHERE p.topicid = " . sqlesc($topicid) . " ORDER BY id LIMIT $offset, $perpage") or sqlerr(__FILE__, __LINE__);
    $pc = mysql_num_rows($res);
    $pn = 0;

    while ($arr = mysql_fetch_assoc($res)) {
        ++$pn;

        $lpr = $arr['lastpostread'];
        $postid = (int)$arr["id"];
        $postadd = $arr['added'];
        $posterid = (int)$arr['userid'];
        $posticon = ($arr["posticon"] > 0 ? "<img src=\"pic/post_icons/icon" . $arr["posticon"] . ".gif\" style=\"padding-left:3px;\" alt=\"post icon\" title=\"post icon\" />" : "&nbsp;");
        $added = get_date($arr['added'], 'DATE',1,0) . " GMT <font class='small'>(" . (get_date($arr['added'], 'LONG',1,0)) . ")</font>";
        // ---- Get poster details
        $uploaded = mksize($arr['uploaded']);
        $downloaded = mksize($arr['downloaded']);
        $member_reputation = $arr['uusername'] != '' ? get_reputation($arr, 'posts') : '';
        $last_access = get_date($arr['last_access'],'DATE',1,0);
        if ($arr['downloaded'] > 0) {
 	      $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
 	      $ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
       	} 
       	else if ($arr['uploaded'] > 0)
 	      $ratio = "&infin;";
 	      else
 	      $ratio = "---";
        if (($postid > $lpr) && ($postadd > (time() - $TBDEV['readpost_expiry']))){
            $newp = "&nbsp;&nbsp;<span class='red'>(New)</span>";
        }
        foreach($mood as $key => $value)
        $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
        $mooduname = $change[$arr['mood']]['name'];
        $moodupic = $change[$arr['mood']]['image'];
        $title = $arr["title"];
        $signature = ($CURUSER['signatures'] == 'yes' ? format_comment($arr['signature']) : '');
        $postername = $arr['uusername'];
        $avatar = ($CURUSER["avatars"] == "all" ? htmlspecialchars($arr["avatar"]) : ($CURUSER["avatars"] == "some" && $arr["offavatar"] == "no" ? htmlspecialchars($arr["avatar"]) : ""));
        $title = (!empty($postername) ? (empty($arr['title']) ? "(" . get_user_class_name($arr['class']) . ")" : "(" . ($arr['title']) . ")") : '');
        $forumposts = (!empty($postername) ? ($arr['posts_count'] != 0 ? $arr['posts_count'] : 'N/A') : 'N/A');
 			  if ($arr["p_anon"] == "yes") {
        if($CURUSER['class'] < UC_MODERATOR && $arr['userid'] != $CURUSER["id"])
        $by = "<i>Anonymous</i>";
        else
        $by = "<i>Anonymous</i>(<a href='{$TBDEV['baseurl']}/userdetails.php?id=$posterid'>".$postername."</a>)". ($arr['support'] == "yes" ? "<img src='".$TBDEV['pic_base_url']."supt.gif' alt='FLS' />" : '').($arr['donor'] == "yes" ? "<img src='".$TBDEV['pic_base_url']."star.gif' alt='Donor' />" : '').($arr['enabled'] == 'no' ? "<img src='".$TBDEV['pic_base_url']."disabled.gif' alt='This account is disabled' style='margin-left: 2px' />" : ($arr['warned'] == 'yes'? "<img src='".$TBDEV['pic_base_url']."warned.gif' alt='Warned' border='0' />" : ''))."$title";	
        }
        else 
        {	
        $by = (!empty($postername) ? "<a href='{$TBDEV['baseurl']}/userdetails.php?id=$posterid'>".$postername."</a>". ($arr['support'] == "yes" ? "<img src='".$TBDEV['pic_base_url']."supt.gif' alt='FLS' />" : '').($arr['donor'] == "yes" ? "<img src='".$TBDEV['pic_base_url']."star.gif' alt='Donor' />" : '').($arr['enabled'] == 'no' ? "<img src='".$TBDEV['pic_base_url']."disabled.gif' alt='This account is disabled' style='margin-left: 2px' />" : ($arr['warned'] == 'yes'? "<img src='".$TBDEV['pic_base_url']."warned.gif' alt='Warned' border='0' />" : '')) : "unknown[".$posterid."]")."$title";	
        }
        
//Classimage system 0.1 (like vBulletin) for tbdev modded forum by d6bmg
              //$classimg='';
      if ($arr['class']==UC_BANNED) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_banned.gif' border='0' alt='' />";
      }
      else if (($arr['class'] == UC_USER | UC_POWER_USER) & ($forumposts<=1)) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_0.gif' border='0' alt='' />";
      }
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=2 & $forumposts<=9) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_1.gif' border='0' alt='' />";
      }      
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=10 & $forumposts<=49) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_2.gif' border='0' alt='' />";
      }      
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=50 & $forumposts<=99) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_3.gif' border='0' alt='' />";
      }      
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=100 & $forumposts<=299) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_4.gif' border='0' alt='' />";
      }
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=300 & $forumposts<=499) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_5.gif' border='0' alt='' />";
      }
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=500 & $forumposts<=799) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/complete.gif' border='0' alt='' />";
      }
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=800 & $forumposts<=999) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_6.gif' border='0' alt='' />";
      }
      else if (($arr['class']==UC_USER | UC_POWER_USER) & $forumposts>=1000) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_postwhore.gif' border='0' alt='' />";
      }
      else if ($arr['class']==UC_VIP) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_mvp.gif' border='0' alt='' />";
      }      
      else if ($arr['class']==UC_UPLOADER) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/uploader.gif' border='0' alt='' />";
      } 
      else if ($arr['class']==UC_FORUM_MOD) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_moderator.gif' border='0' alt='' />";
      }  
      else if ($arr['class']==UC_MODERATOR) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_global-mod.gif' border='0' alt='' />";
      }  
      else if ($arr['class']==UC_ADMINISTRATOR) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_admin.gif' border='0' alt='' />";
      } 
      else if ($arr['class']==UC_SYSOP) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_admin.gif' border='0' alt='' />";
      }  
      else if ($arr['class']==UC_STAFF_LEADER) {
        $classimg="<img src='{$TBDEV['pic_base_url']}ranks/rank_founder.gif' border='0' alt='' />";
      }
//end classimage for tbdev modded forum by d6bmg

        if (empty($avatar))
            $avatar = $TBDEV['pic_base_url'].$forum_pics['default_avatar'];
        $HTMLOUT .="". ($pn == $pc ? '<a name=\'last\'></a>' : '');

        $HTMLOUT .= begin_table();

        $HTMLOUT .="<tr><td width='737' colspan='2'><table class='main'><tr><td style='border:none;' width='100%'>{$posticon}<a  id='p".$postid."' name='p{$postid}' href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=".$topicid."&amp;page=p".$postid."#p".$postid."'>#".$postid."</a> by ".$by." at ".$added."";
       
        if (isset($newp)) {
            $HTMLOUT .="$newp";
        }
       
        $HTMLOUT .="</td><td style='border:none;'><a href='#top'><img align='right' src='{$TBDEV['pic_base_url']}".$forum_pics['arrow_up']."' alt='Top' /></a></td></tr></table></td></tr>";

        $highlight = (isset($_GET['highlight']) ? $_GET['highlight'] : '');
        $body = (!empty($highlight) ? highlight(htmlspecialchars(trim($highlight)), format_comment($arr['body'])) : format_comment($arr['body']));

       if (is_valid_id($arr['editedby']))
			 $body .= "<p><font size='1' class='small'>Last edited by <a href='{$TBDEV['baseurl']}/userdetails.php?id=".$arr['editedby']."'><b>".$arr['u2_username']."</b></a> at ".get_date($arr['editedat'],'LONG',1,0)." GMT</font></p>";
		
		   if ($use_attachment_mod && ((!empty($arr['at_filename']) && is_valid_id($arr['at_id'])) && $arr['at_postid'] == $postid))
		   {
			 foreach ($allowed_file_extensions as $allowed_file_extension)
				if (substr($arr['at_filename'], -3) == $allowed_file_extension)
					$aimg = $allowed_file_extension;
			
			$body .= "<div style='padding:6px'>
			    <fieldset class='fieldset'>
					<legend>Attached Files</legend>
					<table cellpadding='0' cellspacing='3' border='0'>
					<tr>
					<td><img class='inlineimg' src='{$TBDEV['pic_base_url']}$aimg.gif' alt='' width='16' height='16' border='0' style='vertical-align:baseline' />&nbsp;</td>
					<td><a href='".$_SERVER['PHP_SELF']."?action=attachment&amp;attachmentid=".$arr['at_id']."' target='_blank'>".htmlspecialchars($arr['at_filename'])."</a> (".mksize($arr['at_size']).", ".$arr['at_downloads']." downloads)</td>
					<td>&nbsp;&nbsp;<input type='button' class='none' value='See who downloaded' tabindex='1' onclick=\"window.open('".$_SERVER['PHP_SELF']."?action=whodownloaded&amp;fileid=".$arr['at_id']."','whodownloaded','toolbar=no, scrollbars=yes, resizable=yes, width=600, height=250, top=50, left=50'); return false;\" />".($CURUSER['class'] >= UC_MODERATOR ? "&nbsp;&nbsp;<input type='button' class='gobutton' value='Delete' tabindex='2' onclick=\"window.open('".$_SERVER['PHP_SELF']."?action=attachment&amp;subaction=delete&amp;attachmentid=".$arr['at_id']."','attachment','toolbar=no, scrollbars=yes, resizable=yes, width=600, height=250, top=50, left=50'); return false;\" />" : "")."</td>
					</tr>
					</table>
					</fieldset>
					</div>";
		}
					
		  if (!empty($signature) && $arr["p_anon"] == "no")
			$body .= "<p style='vertical-align:bottom'><br />____________________<br />".$signature."</p>";


               
      $HTMLOUT .="<tr align='center'><td width='150' align='center' style='padding: 0px'>";
      if ($arr["p_anon"] == "yes") {
      if($CURUSER['class'] < UC_MODERATOR && $posterid != $CURUSER["id"])
      $HTMLOUT .="<img width='150' src='pic/default_avatar.gif' alt='Avatar' /><br />";
      else
      $HTMLOUT .="<img width='150' src='{$avatar}' alt='Avatar' /><br />
 	    <fieldset style='text-align:left;border:none:white-space:nowrap;'>
		  <b>Posts:</b>&nbsp;{$forumposts}<br />
		  <b>Ratio:</b>&nbsp;{$ratio}<br />
		  <b>Uploaded:</b>&nbsp;{$uploaded}<br />
		  <b>Downloaded:</b>&nbsp;{$downloaded}<br />
          $classimg<br />";
          $mooduser = (isset($arr['username']) ? ("<b>".htmlspecialchars($arr['username'])."</b>") : "(unknown)");
      $moodanon = ($arr['anonymous'] == 'yes' ? ($CURUSER['class'] < UC_MODERATOR && $arr['userid'] != $CURUSER['id'] ? '' : $mooduser.' - ')."<i>Anonymous</i>" : $mooduser);	
      $HTMLOUT .="&nbsp;&nbsp;&nbsp;<a href='{$TBDEV['baseurl']}/usermood.php' onclick=\"return popitup('usermood.php')\">
      <span class='tool'>
      <img border='0' src='{$TBDEV['pic_base_url']}moods/".htmlspecialchars($moodupic)."' alt='".htmlspecialchars($mooduname)."' />
      <span class='tip'>".$moodanon."&nbsp;".htmlspecialchars($mooduname)."&nbsp;!</span></span></a>";
      $HTMLOUT .="</fieldset>";
      }
      else 
      {
      $HTMLOUT .="<img width='150' src='{$avatar}' alt='Avatar' /><br />
 	    <fieldset style='text-align:left;border:none:white-space:nowrap;'>
		  <b>Posts:</b>&nbsp;{$forumposts}<br />
		  <b>Ratio:</b>&nbsp;{$ratio}<br />
		  <b>Uploaded:</b>&nbsp;{$uploaded}<br />
		  <b>Downloaded:</b>&nbsp;{$downloaded}<br />
		  $classimg<br />
          ";
          $mooduser = (isset($arr['username']) ? ("<b></b>") : "(unknown)");
      $moodanon = ($arr['anonymous'] == 'yes' ? ($CURUSER['class'] < UC_MODERATOR && $arr['userid'] != $CURUSER['id'] ? '' : $mooduser.' - ')."<i>Anonymous</i>" : $mooduser);	
      $HTMLOUT .="&nbsp;&nbsp;&nbsp;<a href='{$TBDEV['baseurl']}/usermood.php' onclick=\"return popitup('usermood.php')\">
      <span class='tool'>
      <img border='0' src='{$TBDEV['pic_base_url']}moods/".htmlspecialchars($moodupic)."' alt='".htmlspecialchars($mooduname)."' />
      <span class='tip'>".$moodanon."&nbsp;</span></span></a>";
      $HTMLOUT .="</fieldset>";
      }
		
		  $HTMLOUT .="</td><td class='text' width='100%' style='vertical-align:text-top;'>{$body}</td></tr><tr><td>";
		  if ($arr["p_anon"] == "yes") {
 	    if($CURUSER['class'] < UC_MODERATOR)
		  $HTMLOUT .="";
		  else
 	    $HTMLOUT .="<img src='".$TBDEV['pic_base_url'].$forum_pics[($last_access > (time()-360) || $posterid == $CURUSER['id'] ? 'on' : 'off').'line_btn']."' border='0' alt='' />&nbsp;<a href='{$TBDEV['baseurl']}/sendmessage.php?receiver=".$posterid."'><img src='".$TBDEV['pic_base_url'].$forum_pics['pm_btn']."' border='0' alt='Pm ".htmlspecialchars($postername)."' /></a>&nbsp;";
		  }
      else 
      {
      $HTMLOUT .="<img src='".$TBDEV['pic_base_url'].$forum_pics[($last_access > (time()-360) || $posterid == $CURUSER['id'] ? 'on' : 'off').'line_btn']."' border='0' alt='' />&nbsp;<a href='{$TBDEV['baseurl']}/sendmessage.php?receiver=".$posterid."'><img src='".$TBDEV['pic_base_url'].$forum_pics['pm_btn']."' border='0' alt='Pm ".htmlspecialchars($postername)."' /></a>&nbsp;";
      }
      
      $HTMLOUT.="<a href='{$TBDEV['baseurl']}/report.php?type=Post&amp;id=".$postid."&amp;id_2=".$topicid."&amp;id_3=".$posterid."'><img src='".$TBDEV['pic_base_url'].$forum_pics['p_report_btn']."' border='0' alt='Report Post' /></a>";
      
      /*$mooduser = (isset($arr['username']) ? ("<b>".htmlspecialchars($arr['username'])."</b>") : "(unknown)");
      $moodanon = ($arr['anonymous'] == 'yes' ? ($CURUSER['class'] < UC_MODERATOR && $arr['userid'] != $CURUSER['id'] ? '' : $mooduser.' - ')."<i>Anonymous</i>" : $mooduser);	
      $HTMLOUT .="&nbsp;&nbsp;&nbsp;<a href='{$TBDEV['baseurl']}/usermood.php' onclick=\"return popitup('usermood.php')\">
      <span class='tool'>
      <img border='0' src='{$TBDEV['pic_base_url']}moods/".htmlspecialchars($moodupic)."' alt='".htmlspecialchars($mooduname)."' />
      <span class='tip'>".$moodanon."&nbsp;".htmlspecialchars($mooduname)."&nbsp;!</span></span></a>";*/
      $HTMLOUT .="</td>";
		  
		  
		  $HTMLOUT .="<td align='right'>";
        
        if (!$locked || $CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {
		    if ($arr["p_anon"] == "yes") {
			  if($CURUSER['class'] < UC_MODERATOR)
		    $HTMLOUT .="";
		    }
		    else
 	      $HTMLOUT .="<a href='".$_SERVER['PHP_SELF']."?action=quotepost&amp;topicid=".$topicid."&amp;postid=".$postid."'><img src='".$TBDEV['pic_base_url'].$forum_pics['p_quote_btn']."' border='0' alt='Quote Post' /></a>&nbsp;"; 
 			  }
        else 
        {
	      $HTMLOUT .="<a href='".$_SERVER['PHP_SELF']."?action=quotepost&amp;topicid=".$topicid."&amp;postid=".$postid."'><img src='".$TBDEV['pic_base_url'].$forum_pics['p_quote_btn']."' border='0' alt='Quote Post' /></a>&nbsp;"; 
		    }

        if ($CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {
        $HTMLOUT .="<a href='".$_SERVER['PHP_SELF']."?action=deletepost&amp;postid=".$postid."'><img src='".$TBDEV['pic_base_url'].$forum_pics['p_delete_btn']."' border='0' alt='Delete Post' /></a>&nbsp;";
        }

        if (($CURUSER["id"] == $posterid && !$locked) || $CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {
        $HTMLOUT .="<a href='".$_SERVER['PHP_SELF']."?action=editpost&amp;postid=".$postid."'><img src='".$TBDEV['pic_base_url'].$forum_pics['p_edit_btn']."' border='0' alt='Edit Post' /></a>&nbsp;";
        }

        $HTMLOUT .="</td></tr>";

        $HTMLOUT .= end_table();

        $HTMLOUT .="<br />";
    }

    if ($use_poll_mod && (($userid == $t_userid || $CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) && !is_valid_id($pollid))) {

		$HTMLOUT .="<table cellpadding='5' width='{$forum_width}'>
        <tr>
        	<td align='right'>
            	<form method='post' action='".$_SERVER['PHP_SELF']."'>
                <input type='hidden' name='action' value='makepoll' />
				<input type='hidden' name='topicid' value='".$topicid."' />
				<input type='submit' value='Add a Poll' />
				</form>
			</td>
        </tr>
        </table>
        <br />";
    }

    if (($postid > $lpr) && ($postadd > (time() - $TBDEV['readpost_expiry']))) {
        if ($lpr)
            mysql_query("UPDATE readposts SET lastpostread = $postid WHERE userid = $userid AND topicid = $topicid") or sqlerr(__FILE__, __LINE__);
        else
            mysql_query("INSERT INTO readposts (userid, topicid, lastpostread) VALUES($userid, $topicid, $postid)") or sqlerr(__FILE__, __LINE__);
    }
    // ------ Mod options
    if ($CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {
	  $HTMLOUT .="<form method='post' action='".$_SERVER['PHP_SELF']."'>
	  <input type='hidden' name='action' value='updatetopic' />
		<input type='hidden' name='topicid' value='{$topicid}' />";
	  
	  $HTMLOUT .= begin_table();
		$HTMLOUT .="
		<tr>
		<td colspan='2' class='colhead'>Staff options</td>
		</tr>
		<tr>
		<td class='rowhead' width='1%'>Sticky</td>
		<td>
		<select name='sticky'>
		<option value='yes'". ($sticky ? " selected='selected'" : '').">Yes</option>
		<option value='no' ". (!$sticky ? " selected='selected'" : '').">No</option>
		</select>
		</td>
		</tr>
		<tr>
		<td class='rowhead'>Locked</td>
		<td>
		<select name='locked'>
		<option value='yes'". ($locked ? " selected='selected'" : '').">Yes</option>
		<option value='no'". (!$locked ? " selected='selected'" : '').">No</option>
		</select>
	  </td>
		</tr>
		<tr>
		<td class='rowhead'>Topic name</td>
		<td>
		<input type='text' name='subject' size='60' maxlength='{$maxsubjectlength}' value='".htmlspecialchars($subject)."' />
		</td>
		</tr>
		<tr>
		<td class='rowhead'>Move topic</td>
		<td>
		<select name='new_forumid'>";
		$res = mysql_query("SELECT id, name, minclasswrite FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);
		while ($arr = mysql_fetch_assoc($res))
	  if ($CURUSER['class'] >= $arr["minclasswrite"])
		$HTMLOUT .= '<option value="' . (int)$arr["id"] . '"' . ($arr["id"] == $forumid ? ' selected="selected"' : '') . '>' . htmlspecialchars($arr["name"]) . '</option>';
		
		$HTMLOUT .="</select>
		</td></tr>
		<tr>
	  <td class='rowhead' style='white-space:nowrap;'>Delete topic</td>
	  <td>
	  <select name='delete'>
		<option value='no' selected='selected'>No</option>
		<option value='yes'>Yes</option>
		</select>
		<br />
		<b>Note:</b> Any changes made to the topic won't take effect if you select 'yes'
		</td>
		</tr>
		<tr>
		<td colspan='2' align='center'>
		<input type='submit' value='Update Topic' />
		</td>
		</tr>";
		$HTMLOUT .= end_table();
	  $HTMLOUT .="</form>";
	  
	  }
	  $HTMLOUT .= end_frame();
	
	 $HTMLOUT .= $pagemenu1.$pmlb.$pagemenu2.$pmlb.$pagemenu3;
   $maypost = ($CURUSER['class'] >= $arr["write"] && $CURUSER['class'] >= $arr["create"]);
    if ($locked && $CURUSER['class'] < UC_MODERATOR && !isMod($forumid)) {

      $HTMLOUT .="<p align='center'>This topic is locked; no new posts are allowed.</p>";
       } else {
        $arr = get_forum_access_levels($forumid);

        if ($CURUSER['class'] < $arr["write"]) {

          $HTMLOUT .="<p align='center'><i>You are not permitted to post in this forum.</i></p>";
          
            $maypost = false;
        } else
            $maypost = true;
    }
    // ------ "View unread" / "Add reply" buttons
    
	$HTMLOUT .="<table align='center' class='main' border='0' cellspacing='0' cellpadding='0'><tr>
	<td class='embedded'>
		<form method='get' action='".$_SERVER['PHP_SELF']."'>
		<input type='hidden' name='action' value='viewunread' />
		<input type='submit' value='Show new' />
		</form>
	</td>";
	
	if ($maypost)
	{
	$HTMLOUT .="<td class='embedded' style='padding-left: 10px'>
	<form method='get' action='".$_SERVER['PHP_SELF']."'>
	<input type='hidden' name='action' value='reply' />
	<input type='hidden' name='topicid' value='".$topicid."' />
	<input type='submit' value='Answer' /></form>
	</td>";
	}
    
    $HTMLOUT .="</tr></table>";

    if ($locked)
		{
		$HTMLOUT .= "";
		}
		else
		{
		    //Quick Reply By putyn
	  $HTMLOUT .="<script type=\"text/javascript\" src=\"./scripts/shout.js\"></script>";
	  if ($maypost)
      {
        $HTMLOUT .= "<br /><br />
        <form name='compose' method='post' action='?action=post'>
        <table align='center'><tr><td>
        <input type='hidden' name='topicid' value='$topicid' />
        ".textbbcode("compose", "body")."<br />
        <tr><td colspan='2' align='center'>Anonymous<input type='checkbox' name='anonymous' value='yes' ".($CURUSER['anonymous'] == 'yes' ? "checked='checked'":'')." /><input type='submit' class='btn' value='{$lang['forum_functions_submit']}' /></td></tr>\n
        </form></td></tr></table>";
      }
//Quick Reply By putyn end
//added by d6m4u
        /*$HTMLOUT .="<table style='border:1px solid #000000;' align='center'><tr>
		<td style='padding:10px;text-align:center;'>
		<b>Quick Reply</b>
		<form name='compose' method='post' action='".$_SERVER['PHP_SELF']."'>
		<input type='hidden' name='action' value='post' />
		<input type='hidden' name='topicid' value='".$topicid."' />
		<textarea name='body' rows='4' cols='70'></textarea><br />
		<input type='submit' class='btn' value='Submit' /><br />
		Anonymous<input type='checkbox' name='anonymous' value='yes' ".($CURUSER['anonymous'] == 'yes' ? "checked='checked'":'')." />
		</form></td></tr></table>";*/
	  }
    // ------ Forum quick jump drop-down
    $HTMLOUT .= insert_quick_jump_menu($forumid);

    $HTMLOUT .= end_main_frame();
    
    print stdhead("Forums :: View Topic: $subject") . $HTMLOUT . stdfoot();

    $uploaderror = (isset($_GET['uploaderror']) ? htmlspecialchars($_GET['uploaderror']) : '');

  if (!empty($uploaderror))
	{
	$HTMLOUT .="<script>alert(\"Upload Failed: {$uploaderror}\nHowever your post was successful saved!\n\nClick 'OK' to continue.\");</script>";
	}
	exit();
	
} else if ($action == "quotepost") { // -------- Action: Quote
        $topicid = (int)$_GET["topicid"];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid ID!');

    $HTMLOUT .= begin_main_frame();
    if ($TBDEV['forums_online'] == 0)
    $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
    $HTMLOUT .= insert_compose_frame($topicid, false, true);
    $HTMLOUT .= end_main_frame();
    print stdhead("Post quote") . $HTMLOUT . stdfoot();
    exit();
} else if ($action == "reply") { // -------- Action: Reply
        $topicid = (int)$_GET["topicid"];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid ID!');

    $HTMLOUT .= begin_main_frame();
    if ($TBDEV['forums_online'] == 0)
    $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
    $HTMLOUT .= insert_compose_frame($topicid, false, false, true);
    $HTMLOUT .= end_main_frame();
    print stdhead("Post reply") . $HTMLOUT . stdfoot();
    exit();
} else if ($action == "editpost") { // -------- Action: Edit post
        $postid = (int)$_GET["postid"];
    if (!is_valid_id($postid))
        stderr('Error', 'Invalid ID!');

    $res = mysql_query("SELECT p.userid, p.topicid, p.posticon, p.body, t.locked,t.forumid  " . "FROM posts AS p " . "LEFT JOIN topics AS t ON t.id = p.topicid " . "WHERE p.id = " . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) == 0)
        stderr("Error", "No post with that ID!");

    $arr = mysql_fetch_assoc($res);

    if (($CURUSER["id"] != $arr["userid"] || $arr["locked"] == 'yes') && $CURUSER['class'] < UC_MODERATOR && !isMod($arr["forumid"]))
        stderr("Error", "Access Denied!");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $body = trim($_POST['body']);
        $posticon = (isset($_POST["iconid"]) ? 0 + $_POST["iconid"] : 0);
        if (empty($body))
            stderr("Error", "Body cannot be empty!");

        if(!isset($_POST['lasteditedby']))
	      mysql_query("UPDATE posts SET body = " . sqlesc($body) . ", editedat = " . time() . ", editedby = {$CURUSER['id']}, posticon = $posticon WHERE id = $postid") or sqlerr(__FILE__, __LINE__);
        else
	      mysql_query("UPDATE posts SET body = " . sqlesc($body) . ", posticon = $posticon WHERE id = $postid") or sqlerr(__FILE__, __LINE__);

        header("Location: {$_SERVER['PHP_SELF']}?action=viewtopic&topicid={$arr['topicid']}&page=p$postid#p$postid");
        exit();
    }

    if ($TBDEV['forums_online'] == 0)
    $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
    $HTMLOUT .= begin_main_frame();
	  $HTMLOUT .="<h3>Edit Post</h3>";
	  $HTMLOUT .="<form name='compose' method='post' action='".$_SERVER['PHP_SELF']."?action=editpost&amp;postid=".$postid."'>
	  <table border='1' cellspacing='0' cellpadding='5' width='100%'>
	  <tr>
		<td class='rowhead' width='10%'>Body</td>
		<td align='left' style='padding: 0px'>";
    $ebody = htmlspecialchars(unesc($arr["body"]));
    if (function_exists('textbbcode'))
    $HTMLOUT .= textbbcode("compose", "body", $ebody);
    else {
    $HTMLOUT .="<textarea name='body' style='width:99%' rows='7'>{$ebody}</textarea>";
    }
    
		$HTMLOUT .="</td></tr>";
	  if ($CURUSER["class"] >= UC_MODERATOR)
    $HTMLOUT.="<tr><td colspan='1' align='center'><input type='checkbox' name='lasteditedby' /></td><td align='left' colspan='1'>Don't show the Last edited by <font class='small'>(Staff Only)</font></td></tr>";
	  $HTMLOUT.="<tr>
		<td align='center' colspan='2'>
		".(post_icons($arr["posticon"]))."
		</td>
	</tr>
	<tr>
		<td align='center' colspan='2'>
		<input type='submit' value='Update post' class='gobutton' />
	</td>
	</tr>
	</table>
	</form>";
	
    $HTMLOUT .= end_main_frame();
    print stdhead("Edit Post") . $HTMLOUT . stdfoot();
    exit();
} elseif ($action == "deletetopic") {
    $topicid = (int)$_GET['topicid'];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid ID');

    $r = mysql_query("SELECT t.id,t.subject " . ($use_poll_mod ? ",t.pollid" : "") . ",t.forumid,(SELECT COUNT(p.id) FROM posts as p where p.topicid=" . $topicid . ") AS posts FROM topics as t WHERE t.id=" . $topicid) or sqlerr(__FILE__, __LINE__);
    $a = mysql_fetch_assoc($r) or stderr("Error", "No topic was found");

    if ($CURUSER["class"] >= UC_MODERATOR || isMod($a["forumid"])) {
        $sure = (int)isset($_GET['sure']) && (int) $_GET['sure'];
        if (!$sure)
            stderr("Sanity check...", "You are about to delete topic " . $a["subject"] . ". Click <a href='" . $_SERVER['PHP_SELF'] . "?action=deletetopic&amp;topicid=$topicid&amp;sure=1'>here</a> if you are sure.");
        else {
            write_log("topicdelete","Topic <b>" . $a["subject"] . "</b> was deleted by <a href='{$TBDEV['baseurl']}/userdetails.php?id=" . $CURUSER['id'] . "'>" . $CURUSER['username'] . "</a>.");

            if ($use_attachment_mod) {
                $res = mysql_query("SELECT attachments.filename " . "FROM posts " . "LEFT JOIN attachments ON attachments.postid = posts.id " . "WHERE posts.topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

                while ($arr = mysql_fetch_assoc($res))
                if (!empty($arr['filename']) && is_file($attachment_dir . "/" . $arr['filename']))
                    unlink($attachment_dir . "/" . $arr['filename']);
            }

            mysql_query("DELETE posts, topics " .
                ($use_attachment_mod ? ", attachments, attachmentdownloads " : "") .
                ($use_poll_mod ? ", postpolls, postpollanswers " : "") . "FROM topics " . "LEFT JOIN posts ON posts.topicid = topics.id " .
                ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") .
                ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id = topics.pollid " . "LEFT JOIN postpollanswers ON postpollanswers.pollid = postpolls.id " : "") . "WHERE topics.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=viewforum&forumid=' . $a["forumid"]);
            exit();
        }
    }
} else if ($action == 'deletepost') { // -------- Action: Delete post
        $postid = (int)$_GET['postid'];
    if (!is_valid_id($postid))
        stderr('Error', 'Invalid ID');

    $res = mysql_query("SELECT p.topicid " . ($use_attachment_mod ? ", a.filename" : "") . ", t.forumid, (SELECT COUNT(id) FROM posts WHERE topicid=p.topicid) AS posts_count, " . "(SELECT MAX(id) FROM posts WHERE topicid=p.topicid AND id < p.id) AS p_id " . "FROM posts AS p " . "LEFT JOIN topics as t on t.id=p.topicid " .
        ($use_attachment_mod ? "LEFT JOIN attachments AS a ON a.postid = p.id " : "") . "WHERE p.id=" . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr("Error", "Post not found");

    if (isMod($arr["forumid"]) || $CURUSER['class'] >= UC_MODERATOR) {
        $topicid = (int)$arr['topicid'];

        if ($arr['posts_count'] < 2)
            stderr("Error", "Can't delete post; it is the only post of the topic. You should<br /><a href='" . $_SERVER['PHP_SELF'] . "?action=deletetopic&amp;topicid=$topicid'>delete the topic</a> instead.");

        $redirtopost = (is_valid_id($arr['p_id']) ? "&page=p" . $arr['p_id'] . "#p" . $arr['p_id'] : '');

        $sure = (int)isset($_GET['sure']) && (int) $_GET['sure'];
        if (!$sure)
            stderr("Sanity check...", "You are about to delete a post. Click <a href='" . $_SERVER['PHP_SELF'] . "?action=deletepost&amp;postid=$postid&amp;sure=1'>here</a> if you are sure.");

        mysql_query("DELETE posts.* " . ($use_attachment_mod ? ", attachments.*, attachmentdownloads.* " : "") . "FROM posts " .
            ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") . "WHERE posts.id = " . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);

        if ($use_attachment_mod && !empty($arr['filename'])) {
            $filename = $attachment_dir . "/" . $arr['filename'];
            if (is_file($filename))
                unlink($filename);
        }

        update_topic_last_post($topicid);
        header("Location: {$_SERVER['PHP_SELF']}?action=viewtopic&topicid=" . $topicid . $redirtopost);
        exit();
    }
} else if ($use_poll_mod && ($action == 'deletepoll' && $CURUSER['class'] >= UC_MODERATOR)) {
    $pollid = (int)$_GET["pollid"];
    if (!is_valid_id($pollid))
        stderr("Error", "Invalid ID!");

    $res = mysql_query("SELECT pp.id, t.id AS tid FROM postpolls AS pp LEFT JOIN topics AS t ON t.pollid = pp.id WHERE pp.id = " . sqlesc($pollid));
    if (mysql_num_rows($res) == 0)
        stderr("Error", "No poll found with that ID.");

    $arr = mysql_fetch_array($res);

    $sure = (int)isset($_GET['sure']) && (int) $_GET['sure'];
    if (!$sure || $sure != 1)
        stderr('Sanity check...', 'You are about to delete a poll. Click <a href=' . $_SERVER['PHP_SELF'] . '?action=' . htmlspecialchars($action) . '&amp;pollid=' . $arr['id'] . '&amp;sure=1>here</a> if you are sure.');

    mysql_query("DELETE pp.*, ppa.* FROM postpolls AS pp LEFT JOIN postpollanswers AS ppa ON ppa.pollid = pp.id WHERE pp.id = " . sqlesc($pollid));

    if (mysql_affected_rows() == 0)
        stderr('Sorry...', 'There was an error while deleting the poll, please re-try.');

    mysql_query("UPDATE topics SET pollid = '0' WHERE pollid = " . sqlesc($pollid));

    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=viewtopic&topicid=' . (int)$arr['tid']);
    exit();
} else if ($use_poll_mod && $action == 'makepoll') {
    $subaction = (isset($_GET["subaction"]) ? $_GET["subaction"] : (isset($_POST["subaction"]) ? $_POST["subaction"] : ''));
    $pollid = (isset($_GET["pollid"]) ? (int)$_GET["pollid"] : (isset($_POST["pollid"]) ? (int)$_POST["pollid"] : 0));

    $topicid = (isset($_POST["topicid"]) ? (int)$_POST["topicid"] : 0);

    if ($subaction == "edit") {
        if (!is_valid_id($pollid))
            stderr("Error", "Invalid ID!");

        $res = mysql_query("SELECT pp.*, t.id AS tid FROM postpolls AS pp LEFT JOIN topics AS t ON t.pollid = pp.id WHERE pp.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 0)
            stderr("Error", "No poll found with that ID.");

        $poll = mysql_fetch_assoc($res);
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !$topicid)
	{
		$topicid = (int)($subaction == "edit" ? $poll['tid'] : $_POST["updatetopicid"]);
		
		$question = $_POST["question"];
		$option0 = $_POST["option0"];
		$option1 = $_POST["option1"];
		$option2 = $_POST["option2"];
		$option3 = $_POST["option3"];
		$option4 = $_POST["option4"];
		$option5 = $_POST["option5"];
		$option6 = $_POST["option6"];
		$option7 = $_POST["option7"];
		$option8 = $_POST["option8"];
		$option9 = $_POST["option9"];
		$option10 = $_POST["option10"];
		$option11 = $_POST["option11"];
		$option12 = $_POST["option12"];
		$option13 = $_POST["option13"];
		$option14 = $_POST["option14"];
		$option15 = $_POST["option15"];
		$option16 = $_POST["option16"];
		$option17 = $_POST["option17"];
		$option18 = $_POST["option18"];
		$option19 = $_POST["option19"];
		$sort = $_POST["sort"];
	
		if (!$question || !$option0 || !$option1)
			stderr("Error", "Missing form data!");
	
		if ($subaction == "edit" && is_valid_id($pollid))
			mysql_query("UPDATE postpolls SET " .
							"question = " . sqlesc($question) . ", " .
							"option0 = " . sqlesc($option0) . ", " .
							"option1 = " . sqlesc($option1) . ", " .
							"option2 = " . sqlesc($option2) . ", " .
							"option3 = " . sqlesc($option3) . ", " .
							"option4 = " . sqlesc($option4) . ", " .
							"option5 = " . sqlesc($option5) . ", " .
							"option6 = " . sqlesc($option6) . ", " .
							"option7 = " . sqlesc($option7) . ", " .
							"option8 = " . sqlesc($option8) . ", " .
							"option9 = " . sqlesc($option9) . ", " .
							"option10 = " . sqlesc($option10) . ", " .
							"option11 = " . sqlesc($option11) . ", " .
							"option12 = " . sqlesc($option12) . ", " .
							"option13 = " . sqlesc($option13) . ", " .
							"option14 = " . sqlesc($option14) . ", " .
							"option15 = " . sqlesc($option15) . ", " .
							"option16 = " . sqlesc($option16) . ", " .
							"option17 = " . sqlesc($option17) . ", " .
							"option18 = " . sqlesc($option18) . ", " .
							"option19 = " . sqlesc($option19) . ", " .
							"sort = " . sqlesc($sort) . " " .
					"WHERE id = ".sqlesc((int)$poll["id"])) or sqlerr(__FILE__, __LINE__);
		else
		{
			if (!is_valid_id($topicid))
				stderr('Error', 'Invalid topic ID!');
	
			mysql_query("INSERT INTO postpolls VALUES(id" .
							", " . sqlesc(time()) .
							", " . sqlesc($question) .
							", " . sqlesc($option0) .
							", " . sqlesc($option1) .
							", " . sqlesc($option2) .
							", " . sqlesc($option3) .
							", " . sqlesc($option4) .
							", " . sqlesc($option5) .
							", " . sqlesc($option6) .
							", " . sqlesc($option7) .
							", " . sqlesc($option8) .
							", " . sqlesc($option9) .
							", " . sqlesc($option10) .
							", " . sqlesc($option11) .
							", " . sqlesc($option12) .
							", " . sqlesc($option13) .
							", " . sqlesc($option14) .
							", " . sqlesc($option15) .
							", " . sqlesc($option16) .
							", " . sqlesc($option17) .
							", " . sqlesc($option18) .
							", " . sqlesc($option19) .
							", " . sqlesc($sort).")") or sqlerr(__FILE__, __LINE__);
	
			$pollnum = mysql_insert_id();
	
			mysql_query("UPDATE topics SET pollid = ".sqlesc($pollnum)." WHERE id = ".sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
		}
		
		header("Location: {$_SERVER['PHP_SELF']}?action=viewtopic&topicid=$topicid");
		exit();
	}
	$HTMLOUT .= begin_main_frame();
	if ($subaction == "edit")
	$HTMLOUT .="<h1>Edit poll</h1>";
	
	$HTMLOUT .="<form method='post' action='".$_SERVER['PHP_SELF']."'>
	
  <input type='hidden' name='action' value='".$action."' />
	<input type='hidden' name='subaction' value='".$subaction."' />
	<input type='hidden' name='updatetopicid' value='". (int)$topicid."' />
	<table border='1' cellspacing='0' cellpadding='25' width='100%'>";

	if ($subaction == "edit")
	{
	$HTMLOUT .="<input type='hidden' name='pollid' value='".(int)$poll["id"]."'>";
	}
	$HTMLOUT .="
	<tr><td class='rowhead'>Question <font color='red'>*</font></td><td align='left'><textarea name='question' cols='70' rows='4'>". ($subaction == "edit" ? htmlspecialchars($poll['question']) : '')."</textarea></td></tr>
	<tr><td class='rowhead'>Option 1 <font color='red'>*</font></td><td align='left'><input name='option0' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option0']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 2 <font color='red'>*</font></td><td align='left'><input name='option1' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option1']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 3</td><td align='left'><input name='option2' size='80' maxlength='40' value='".($subaction == "edit" ? htmlspecialchars($poll['option2']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 4</td><td align='left'><input name='option3' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option3']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 5</td><td align='left'><input name='option4' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option4']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 6</td><td align='left'><input name='option5' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option5']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 7</td><td align='left'><input name='option6' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option6']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 8</td><td align='left'><input name='option7' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option7']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 9</td><td align='left'><input name='option8' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option8']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 10</td><td align='left'><input name='option9' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option9']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 11</td><td align='left'><input name='option10' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option10']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 12</td><td align='left'><input name='option11' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option11']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 13</td><td align='left'><input name='option12' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option12']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 14</td><td align='left'><input name='option13' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option13']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 15</td><td align='left'><input name='option14' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option14']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 16</td><td align='left'><input name='option15' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option15']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 17</td><td align='left'><input name='option16' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option16']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 18</td><td align='left'><input name='option17' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option17']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 19</td><td align='left'><input name='option18' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option18']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Option 20</td><td align='left'><input name='option19' size='80' maxlength='40' value='". ($subaction == "edit" ? htmlspecialchars($poll['option19']) : '')."' /><br /></td></tr>
	<tr><td class='rowhead'>Sort</td><td>
	<input type='radio' name='sort' value='yes' ". ($subaction == "edit" ? ($poll["sort"] != "no" ? " checked='checked'" : "") : '')." />Yes
	<input type='radio' name='sort' value='no' ".  ($subaction == "edit" ? ($poll["sort"] == "no" ? " checked='checked'" : "") : '')." />No
	</td></tr>
	<tr><td colspan='2' align='center'><input type='submit' value='". ($pollid ? 'Edit poll' : 'Create poll')."' style='height: 20pt' /></td></tr>
	</table>
	<p align='center'><font color='red'>*</font> required</p>
	
	</form>";

	$HTMLOUT .= end_main_frame(); 
	print stdhead("Polls") . $HTMLOUT . stdfoot();
}
else if ($use_attachment_mod && $action == "attachment")
{
	@ini_set('zlib.output_compression', 'Off');
	@set_time_limit(0);
	
	if (@ini_get('output_handler') == 'ob_gzhandler' && @ob_get_length() !== false)
	{
		@ob_end_clean();
		header('Content-Encoding:');
	}
	
	$id = (int)$_GET['attachmentid'];
	if (!is_valid_id($id))
		die('Invalid Attachment ID!');
	
	$at = mysql_query("SELECT filename, owner, type FROM attachments WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	$resat = mysql_fetch_assoc($at) or die('No attachment with that ID!');
	$filename = $attachment_dir.'/'.$resat['filename'];
	
	if (!is_file($filename))
		die('Inexistent atachment.');
		
	if (!is_readable($filename))
		die('Attachment is unreadable.');
	
	if ((isset($_GET['subaction']) ? $_GET['subaction'] : '') == 'delete')
	{
		if ($CURUSER['id'] <> $resat["owner"] && $CURUSER['class'] < UC_MODERATOR)
			die('Not your attachment to delete.');
		
		unlink($filename);
		
		mysql_query("DELETE attachments, attachmentdownloads ".
					"FROM attachments ".
					"LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id ".
					"WHERE attachments.id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
		
		die("<font color='red'>File successfully deleted...</font>");
	}
		
	mysql_query("UPDATE attachments SET downloads = downloads + 1 WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	
	$res = mysql_query("SELECT fileid FROM attachmentdownloads WHERE fileid=".sqlesc($id)." AND userid=".sqlesc($CURUSER['id']));
	if (mysql_num_rows($res) == 0)
		mysql_query("INSERT INTO attachmentdownloads (fileid, username, userid, date, downloads) VALUES (".sqlesc($id).", ".sqlesc($CURUSER['username']).", ".sqlesc($CURUSER['id']).", ".time().", 1)") or sqlerr(__FILE__, __LINE__);
	else
		mysql_query("UPDATE attachmentdownloads SET downloads = downloads + 1 WHERE fileid = ".sqlesc($id)." AND userid = ".sqlesc($CURUSER['id']));
	$arr=0;
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false); // required for certain browsers 
	header("Content-Type: ".$arr['type']."");
	header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($filename));
	readfile($filename);
	exit();
} 

else if ($use_attachment_mod && $action == "whodownloaded")
{
	$fileid = (int)$_GET['fileid'];
	if (!is_valid_id($fileid))
		die('Invalid ID!');
	
	$res = mysql_query("SELECT fileid, at.filename, userid, username, atdl.downloads, date, at.downloads AS dl ".
					   "FROM attachmentdownloads AS atdl ".
					   "LEFT JOIN attachments AS at ON at.id=atdl.fileid ".
					   "WHERE fileid = ".sqlesc($fileid).($CURUSER['class'] < UC_MODERATOR ? " AND owner=".$CURUSER['id'] : '')) or sqlerr(__FILE__, __LINE__);
	
	if (mysql_num_rows($res) == 0)
	die("<h2 align='center'>Nothing found!</h2>");
	else
	{
	$HTMLOUT = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
    <meta name='generator' content='TBDev.net' />
	  <meta name='MSSmartTagsPreventParsing' content='TRUE' />
		<title>Who Downloaded</title>
    <link rel='stylesheet' href='./1.css' type='text/css' />
    </head>
  <body>
	<table width='100%' cellpadding='5' border='1'>
	<tr align='center'>
	<td>File Name</td>
	<td style='white-space: nowrap;'>Downloaded by</td>
	<td>Downloads</td>
	<td>Date</td>
	</tr>";
  $dls = 0;
	while ($arr = mysql_fetch_assoc($res))
	{
	$HTMLOUT .="<tr align='center'>".
				 "<td>".htmlspecialchars($arr['filename'])."</td>".
				 "<td><a class='pointer' onclick=\"opener.location=('/userdetails.php?id=".(int)$arr['userid']."'); self.close();\">".htmlspecialchars($arr['username'])."</a></td>".
				 "<td>".(int)$arr['downloads']."</td>".
				 "<td>".get_date($arr['date'], 'DATE',1,0)." (".get_date($arr['date'], 'DATE',1,0).")</td>".
				 "</tr>";
	  $dls += (int)$arr['downloads'];
		}
		$HTMLOUT .="<tr><td colspan='4'><b>Total Downloads:</b><b>".number_format($dls)."</b></td></tr></table></body></html>";
	}
	print($HTMLOUT);
}
 else if ($action == "viewforum") { // -------- Action: View forum
        $forumid = (int)$_GET['forumid'];
    if (!is_valid_id($forumid))
        stderr('Error', 'Invalid ID!');
    $page = (isset($_GET["page"]) ? (int)$_GET["page"] : 0);
    $userid = (int)$CURUSER["id"];
    // ------ Get forum details
    $res = mysql_query("SELECT f.name AS forum_name, f.minclassread, (SELECT COUNT(id) FROM topics WHERE forumid = f.id) AS t_count " . "FROM forums AS f " . "WHERE f.id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr('Error', 'No forum with that ID!');

    if ($CURUSER['class'] < $arr["minclassread"])
        stderr('Error', 'Access Denied!');

    $perpage = (empty($CURUSER['topicsperpage']) ? 20 : (int)$CURUSER['topicsperpage']);
    $num = (int)$arr['t_count'];

    if ($page == 0)
        $page = 1;

    $first = ($page * $perpage) - $perpage + 1;
    $last = $first + $perpage - 1;

    if ($last > $num)
        $last = $num;

    $pages = floor($num / $perpage);

    if ($perpage * $pages < $num)
        ++$pages;
    // ------ Build menu
    $menu1 = "<p class='success' align='center'>";
    $menu2 = '';

    $lastspace = false;
    for ($i = 1; $i <= $pages; ++$i) {
        if ($i == $page)
            $menu2 .= "<b>[<u>$i</u>]</b>\n";

        else if ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3)) {
            if ($lastspace)
                continue;

            $menu2 .= "... \n";

            $lastspace = true;
        } else {
            $menu2 .= "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid&amp;page=$i><b>$i</b></a>\n";

            $lastspace = false;
        }

        if ($i < $pages)
            $menu2 .= "<b>|</b>";
    }

    $menu1 .= ($page == 1 ? "<b>&lt;&lt;&nbsp;Prev</b>" : "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page - 1) . "><b>&lt;&lt;&nbsp;Prev</b></a>");
    $mlb = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $menu3 = ($last == $num ? "<b>Next&nbsp;&gt;&gt;</b></p>" : "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page + 1) . "><b>Next&nbsp;&gt;&gt;</b></a></p>");

    $offset = $first - 1;

    $topics_res = mysql_query("SELECT t.id, t.userid, t.views, t.locked, t.sticky" . ($use_poll_mod ? ', t.pollid' : '') . ", t.subject, t.anonymous, u1.username, r.lastpostread, p.id AS p_id,p2.posticon, p.userid AS p_userid, p.anonymous as p_anon, p.added AS p_added, (SELECT COUNT(id) FROM posts WHERE topicid=t.id) AS p_count, u2.username AS u2_username " . "FROM topics AS t " . "LEFT JOIN users AS u1 ON u1.id=t.userid " . "LEFT JOIN readposts AS r ON r.userid = " . sqlesc($userid) . " AND r.topicid = t.id " . "LEFT JOIN posts AS p ON p.id = (SELECT MAX(id) FROM posts WHERE topicid = t.id) " . "LEFT JOIN posts AS p2 ON p2.id = (SELECT MIN(id) FROM posts WHERE topicid = t.id) " . "LEFT JOIN users AS u2 ON u2.id = p.userid " . "WHERE t.forumid = " . sqlesc($forumid) . " ORDER BY t.sticky, t.lastpost DESC LIMIT $offset, $perpage") or sqlerr(__FILE__, __LINE__);
    // subforums
    $r_subforums = mysql_query("SELECT id FROM forums where place=" . $forumid);
    $subforums = mysql_num_rows($r_subforums);
    $HTMLOUT .= begin_main_frame();
    if ($TBDEV['forums_online'] == 0)
    $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');

    if ($subforums > 0) {
	  $HTMLOUT .="<table border='1' cellspacing='0' cellpadding='5' width='{$forum_width}'>
		<tr><td colspan='4' class='colhead' align='left'>".htmlspecialchars($arr["forum_name"])." : SubForums</td></tr>
		<tr>
    <td align='left' class='subheader'>Forums</td>
    <td  align='right' class='subheader'>Topics</td>
		<td  align='right' class='subheader'>Posts</td>
		<td  align='left' class='subheader'>Last post</td>
	</tr>";

        $HTMLOUT .= show_forums($forumid, true);
        $HTMLOUT .= end_table();
    }

    if (mysql_num_rows($topics_res) > 0) {
    $HTMLOUT .="<br /><table border='1' cellspacing='0' cellpadding='5' width='{$forum_width}'>
		<tr>
		<td colspan='7' class='colhead' align='left'>". htmlspecialchars($arr["forum_name"])." : Forums</td></tr>
		<tr>
			<td  align='left' class='subheader'>Topic</td>
			<td class='subheader'>Replies</td>
			<td class='subheader'>Views</td>
			<td  align='left' class='subheader'>Author</td>
			<td  align='left' class='subheader'>Last&nbsp;post</td>
		</tr>";
		
        while ($topic_arr = mysql_fetch_assoc($topics_res))
		{
			$topicid = (int)$topic_arr['id'];
			$topic_userid = (int)$topic_arr['userid'];
			$sticky = ($topic_arr['sticky'] == "yes");
			$pollim = $topic_arr['pollid'] > "0";
			($use_poll_mod ? $topicpoll = is_valid_id($topic_arr["pollid"]) : NULL);
		
			$tpages = floor($topic_arr['p_count'] / $postsperpage);
			
			if (($tpages * $postsperpage) != $topic_arr['p_count'])
				++$tpages;
			
			if ($tpages > 1)
			{
				$topicpages = "&nbsp;(<img src='".$TBDEV['pic_base_url']."multipage.gif' alt='Multiple pages' title='Multiple pages' />";
				$split = ($tpages > 10) ? true : false;
				$flag = false;
				
				for ($i = 1; $i <= $tpages; ++$i)
				{
					if ($split && ($i > 4 && $i < ($tpages - 3)))
					{
						if (!$flag)
						{
							$topicpages .= '&nbsp;...';
							$flag = true;
						}
						continue;
					}
					$topicpages .= "&nbsp;<a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=$topicid&amp;page=$i'>$i</a>";
				}
				$topicpages .= ")";
			}
			else
				$topicpages = '';
		
			if ($topic_arr["p_anon"] == "yes") {
      if($CURUSER['class'] < UC_MODERATOR && $topic_arr["p_userid"] != $CURUSER["id"])
      $lpusername = "<i>Anonymous</i>";
      else
      $lpusername = "<i>Anonymous</i><br />(<a href='{$TBDEV['baseurl']}/userdetails.php?id=".(int)$topic_arr['p_userid']."'><b>".$topic_arr['u2_username']."</b></a>)";
      }
      else
      $lpusername = (is_valid_id($topic_arr['p_userid']) && !empty($topic_arr['u2_username']) ? "<a href='{$TBDEV['baseurl']}/userdetails.php?id=".(int)$topic_arr['p_userid']."'><b>".$topic_arr['u2_username']."</b></a>" : "unknown[$topic_userid]");
      if ($topic_arr["anonymous"] == "yes") {
      if($CURUSER['class'] < UC_MODERATOR && $topic_arr["userid"] != $CURUSER["id"])
      $lpauthor = "<i>Anonymous</i>";
      else
      $lpauthor = "<i>Anonymous</i><br />(<a href='{$TBDEV['baseurl']}/userdetails.php?id=$topic_userid'><b>".$topic_arr['username']."</b></a>)";
      }
      else
      $lpauthor = (is_valid_id($topic_arr['userid']) && !empty($topic_arr['username']) ? "<a href='{$TBDEV['baseurl']}/userdetails.php?id=$topic_userid'><b>".$topic_arr['username']."</b></a>" : "unknown[$topic_userid]");
			$new = ($topic_arr["p_added"] > (time() - $TBDEV['readpost_expiry'])) ? ((int)$topic_arr['p_id'] > $topic_arr['lastpostread']) : 0;
			$topicpic = ($topic_arr['locked'] == "yes" ? ($new ? "lockednew" : "locked") : ($new ? "unlockednew" : "unlocked"));
			$post_icon = ($sticky ? "<img src=\"pic/sticky.png\" alt=\"Sticky topic\" title=\"Sticky topic\"/>" : ($topic_arr["posticon"] > 0 ? "<img src=\"pic/post_icons/icon".$topic_arr["posticon"].".gif\" alt=\"post icon\" title=\"post icon\" />" : "&nbsp;"));

      $HTMLOUT .="<tr>
				<td align='left' width='100%'>
				<table border='0' cellspacing='0' cellpadding='0'>
				<tr>
				<td class='embedded' style='padding-right: 5px'><img src='".$TBDEV['pic_base_url'].$topicpic.".gif' alt='' /></td>
				<td align='center' nowrap='nowrap' style='padding-right: 5px;border:none'>". ($pollim ? "<img src='{$TBDEV['pic_base_url']}poll.gif' alt='Topic Poll' title='Topic Poll' />&nbsp;" : '')."".$post_icon."</td>
				<td class='embedded' align='left'>". ($sticky ? '&nbsp;' : '')."<a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=".$topicid."'>".htmlspecialchars($topic_arr['subject'])."</a>{$topicpages}</td>
				</tr>
				</table>
				</td>
				<td align='center'>". max(0, $topic_arr['p_count'] - 1)."</td>
				<td align='center'>". number_format($topic_arr['views'])."</td>
				<td align='center'>". $lpauthor ."</td>
				<td align='left' style='white-space: nowrap;'>".get_date($topic_arr["p_added"],'DATE',1,0)."<br />by&nbsp;". $lpusername."</td></tr>";
		    }
		
		$HTMLOUT .= end_table();
	  }
	  else
	  {
		$HTMLOUT .="<p align='center'>No topics found</p>";
	  }
	
	$HTMLOUT .= $menu1.$mlb.$menu2.$mlb.$menu3;

	$HTMLOUT .="<table class='main' border='0' cellspacing='0' cellpadding='0' align='center'>
	<tr align='center'>
		<td class='embedded'><img src='".$TBDEV['pic_base_url']."unlockednew.gif' alt='New Unlocked' style='margin-right: 5px' /></td>
		<td class='embedded'>New posts</td>
		<td class='embedded'><img src='".$TBDEV['pic_base_url']."locked.gif' alt='Locked' style='margin-left: 10px; margin-right: 5px' /></td>
		<td class='embedded'>Locked topic</td>
	</tr>
	</table>";

	$arr = get_forum_access_levels($forumid) or die();
	
	$maypost = ($CURUSER['class'] >= $arr["write"] && $CURUSER['class'] >= $arr["create"]);
	
	if (!$maypost)
	{
	$HTMLOUT .="<p><i>You are not permitted to start new topics in this forum.</i></p>";
	}
	$HTMLOUT .="<table border='0' class='main' cellspacing='0' cellpadding='0' align='center'>
	<tr>
	<td class='embedded'><form method='get' action='".$_SERVER['PHP_SELF']."'>
	<input type='hidden' name='action' value='viewunread' />
	<input type='submit' value='View unread' class='gobutton' /></form></td>";

	if ($maypost)
	{
	$HTMLOUT .="<td class='embedded'>
	<form method='get' action='".$_SERVER['PHP_SELF']."'>
	<input type='hidden' name='action' value='newtopic' />
	<input type='hidden' name='forumid' value='".$forumid."' />
	<input type='submit' value='New topic' class='gobutton' style='margin-left: 10px' /></form></td>";
	}
	
	$HTMLOUT .="</tr></table>";
	$HTMLOUT .= insert_quick_jump_menu($forumid);
	$HTMLOUT .= end_main_frame(); 
	print stdhead("New Topic") . $HTMLOUT . stdfoot();
	exit();
}

else if ($action == 'viewunread') { // -------- Action: View unread posts
        if ((isset($_POST[$action]) ? $_POST[$action] : '') == 'clear') {
            $topic_ids = (isset($_POST['topic_id']) ? $_POST['topic_id'] : array());

            if (empty($topic_ids)) {
                header('Location: ' . $_SERVER['PHP_SELF'] . '?action=' . $action);
                exit();
            }

            foreach ($topic_ids as $topic_id)
            if (!is_valid_id($topic_id))
                stderr('Error...', 'Invalid ID!');

            $HTMLOUT .= catch_up($topic_ids);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=' . $action);
            exit();
        } else {
            $added = (time() - $TBDEV['readpost_expiry']);
            $res = mysql_query('SELECT t.lastpost, r.lastpostread, f.minclassread ' . 'FROM topics AS t ' . 'LEFT JOIN posts AS p ON t.lastpost=p.id ' . 'LEFT JOIN readposts AS r ON r.userid=' . sqlesc((int)$CURUSER['id']) . ' AND r.topicid=t.id ' . 'LEFT JOIN forums AS f ON f.id=t.forumid ' . 'WHERE p.added > ' . $added) or sqlerr(__FILE__, __LINE__);
            $count = 0;
            while ($arr = mysql_fetch_assoc($res)) {
                if ($arr['lastpostread'] >= $arr['lastpost'] || $CURUSER['class'] < $arr['minclassread'])
                    continue;

                $count++;
            }
            mysql_free_result($res);

            if ($count > 0)
		        {
			      $perpage = 25;
            $pager = pager($perpage, $count, $_SERVER['PHP_SELF'].'?action='.$action.'&amp;');

         
                if ($TBDEV['forums_online'] == 0)
                $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
                $HTMLOUT .= begin_main_frame();
                $HTMLOUT .="<h1 align='center'>Topics with unread posts</h1>";
                $HTMLOUT .= $pager['pagertop'];
	
			$HTMLOUT .= "	<script type='text/javascript'>
			             /*<![CDATA[*/
				var checkflag = 'false';
				function check(a)
				{
					if (checkflag == 'false')
					{
						for(i=0; i < a.length; i++)
							a[i].checked = true;
						checkflag = 'true';
						value = 'Uncheck';
					}
					else
					{
						for(i=0; i < a.length; i++)
							a[i].checked = false;
						checkflag = 'false';
						value = 'Check';
					}
					return value + ' All';
				};
			/*]]>*/
			</script>";
	
			$HTMLOUT .= "<form method='post' action='{$TBDEV['baseurl']}/forums.php?action=viewunread'>
			<input type='hidden' name='viewunread' value='clear' />";
		  $HTMLOUT .= "<table cellpadding='5' width='{$forum_width}'>
			<tr align='left'>
				<td class='colhead' colspan='2'>Topic</td>
				<td class='colhead' width='1%'>Clear</td>
			</tr>";

                $res = mysql_query('SELECT t.id, t.forumid, t.subject, t.lastpost, r.lastpostread, f.name, f.minclassread ' . 'FROM topics AS t ' . 'LEFT JOIN posts AS p ON t.lastpost=p.id ' . 'LEFT JOIN readposts AS r ON r.userid=' . sqlesc((int)$CURUSER['id']) . ' AND r.topicid=t.id ' . 'LEFT JOIN forums AS f ON f.id=t.forumid ' . 'WHERE p.added > ' . $added . ' ' . ' ORDER BY t.forumid '.$pager['limit']) or sqlerr(__FILE__, __LINE__);

                while ($arr = mysql_fetch_assoc($res)) {
                    if ($arr['lastpostread'] >= $arr['lastpost'] || $CURUSER['class'] < $arr['minclassread'])
                        continue;

                    
				$HTMLOUT .= "<tr>
					<td align='center' width='1%'>
						<img src='".$TBDEV['pic_base_url']."unlockednew.gif' alt='New Posts' title='New Posts' />
					</td>
					<td align='left'>
						<a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=".(int)$arr['id']."&amp;page=last#last'>".htmlspecialchars($arr['subject'])."</a><br />in&nbsp;<font class='small'><a href='".$_SERVER['PHP_SELF']."?action=viewforum&amp;forumid=".(int)$arr['forumid']."'>". htmlspecialchars($arr['name'])."</a></font>
					 </td>
					<td align='center'>
						<input type='checkbox' name='topic_id[]' value='".(int)$arr['id']."' />
					</td>
				</tr>";
		
                }
                mysql_free_result($res);

                
			$HTMLOUT .= "<tr>
				<td align='center' colspan='3'>
					<input type='button' value='Check All' onclick=\"this.value = check(form);\" />&nbsp;<input type='submit' value='Clear selected' />
				</td>
			</tr>";
			

                $HTMLOUT .= end_table();

               $HTMLOUT .= "</form>";
               $HTMLOUT .= $pager['pagerbottom'];
            

                $HTMLOUT .= "<div align='center'><a href='" . $_SERVER['PHP_SELF'] . "?catchup'>Mark all posts as read</a></div>";

                $HTMLOUT .= end_main_frame();
                print stdhead("Catch Up") . $HTMLOUT . stdfoot();
                die();
            } else
                stderr("Sorry...", "There are no unread posts.<br /><br />Click <a href='" . $_SERVER['PHP_SELF'] . "?action=getdaily'>here</a> to get today's posts (last 24h).");
        }
    } 
  
  else if ($action == "getdaily")
  {
	$res = mysql_query('SELECT COUNT(p.id) AS post_count '.
					   'FROM posts AS p '.
					   'LEFT JOIN topics AS t ON t.id = p.topicid '.
					   'LEFT JOIN forums AS f ON f.id = t.forumid '.
					   'WHERE p.added > '.time().' - 86400 AND f.minclassread <= '.$CURUSER['class']) or sqlerr(__FILE__, __LINE__);
	
	$arr = mysql_fetch_assoc($res);
	mysql_free_result($res);


        $count = (int)$arr['post_count'];
        if (empty($count))
        stderr('Sorry', 'No posts in the last 24 hours.');

     
        if ($TBDEV['forums_online'] == 0)
        $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
        $HTMLOUT .= begin_main_frame();
        $perpage = 20;
        $pager = pager($perpage, $count, $_SERVER['PHP_SELF'].'?action='.$action.'&amp;');
	
	$HTMLOUT .= "<h2 align='center'>Today Posts (Last 24 Hours)</h2>";
	$HTMLOUT .= $pager['pagertop'];

    $HTMLOUT .= "<table cellpadding='5' width='{$forum_width}'>
    <tr class='colhead' align='center'>
		<td width='100%' align='left'>Topic Title</td>
		<td>Views</td>
		<td>Author</td>
		<td>Posted At</td>
	  </tr>";

     $res = mysql_query('SELECT p.id AS pid, p.topicid, p.userid AS userpost, p.added, t.id AS tid, t.subject, t.forumid, t.lastpost, t.views, f.name, f.minclassread, f.topiccount, u.username '.
					   'FROM posts AS p '.
					   'LEFT JOIN topics AS t ON t.id = p.topicid '.
					   'LEFT JOIN forums AS f ON f.id = t.forumid '.
					   'LEFT JOIN users AS u ON u.id = p.userid '.
					   'LEFT JOIN users AS topicposter ON topicposter.id = t.userid '.
					   'WHERE p.added > '.time().' - 86400 AND f.minclassread <= '.$CURUSER['class'].' '.
					   'ORDER BY p.added DESC '.$pager["limit"]) or sqlerr(__FILE__, __LINE__);
        
    while ($getdaily = mysql_fetch_assoc($res))
	  {
		$postid = (int)$getdaily['pid'];
		$posterid = (int)$getdaily['userpost'];
		
		$HTMLOUT .= "<tr>
			<td align='left'>
		  <a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;topicid=".$getdaily['tid']."&amp;page=".$postid."#".$postid ."'>". htmlspecialchars($getdaily['subject'])."</a><br />
      <b>In</b>&nbsp;<a href='". $_SERVER['PHP_SELF']."?action=viewforum&amp;forumid=". (int)$getdaily['forumid']."'>". htmlspecialchars($getdaily['name'])."</a>
      </td>
      <td align='center'>". number_format($getdaily['views'])."</td>
      <td align='center'>";
				
				if (!empty($getdaily['username']))
				{
				$HTMLOUT .= "<a href='{$TBDEV['baseurl']}/userdetails.php?id=".$posterid."'>".htmlspecialchars($getdaily['username'])."</a>";
				}
				else
				{
				$HTMLOUT .= "<b>unknown[".$posterid."]</b>";
				}
			  $HTMLOUT .= "</td>";
		
	      $HTMLOUT .= "<td style='white-space: nowrap;'>".get_date($getdaily['added'], 'LONG',1,0)."</td></tr>";
	   
	}
	mysql_free_result($res);
	
	$HTMLOUT .= end_table();
	$HTMLOUT .= $pager['pagerbottom'];
	$HTMLOUT .= end_main_frame(); 
	print stdhead('Today Posts (Last 24 Hours)') . $HTMLOUT . stdfoot();
}
else if ($action == "search") //-------- Action: Search
{
	$error = false;
	$found = '';
	$keywords = (isset($_GET['keywords']) ? trim($_GET['keywords']) : '');
	if (!empty($keywords))
	{
		$res = mysql_query("SELECT COUNT(id) AS c FROM posts WHERE body LIKE ".sqlesc("%".sqlwildcardesc($keywords)."%")) or sqlerr(__FILE__, __LINE__);
		$arr = mysql_fetch_assoc($res);
		$count = (int)$arr['c'];
		$keywords = htmlspecialchars($keywords);
		
		if ($count == 0)
			$error = true;
		else
		{
			$perpage = 10;
      $pager = pager($perpage, $count, $_SERVER['PHP_SELF'].'?action='.$action.'&keywords='.$keywords.'&');
			$res = mysql_query(
			"SELECT p.id, p.topicid, p.userid, p.added, t.forumid, t.subject, f.name, f.minclassread, u.username ".
			"FROM posts AS p ".
			"LEFT JOIN topics AS t ON t.id=p.topicid ".
			"LEFT JOIN forums AS f ON f.id=t.forumid ".
			"LEFT JOIN users AS u ON u.id=p.userid ".
			"WHERE p.body LIKE ".sqlesc("%".$keywords."%")." ".$pager['limit']."");
	
			$num = mysql_num_rows($res);
			$HTMLOUT .= $pager['pagertop'];
			$HTMLOUT .= begin_main_frame();
			
		
            $HTMLOUT .="<table border='0' cellspacing='0' cellpadding='5' width='100%'>
			       <tr align='left'>
            	<td class='colhead'>Post</td>
                <td class='colhead'>Topic</td>
                <td class='colhead'>Forum</td>
                <td class='colhead'>Posted by</td>
			          </tr>";
      
			          for ($i = 0; $i < $num; ++$i)
			          {
				        $post = mysql_fetch_assoc($res);
	
				        if ($post['minclassread'] > $CURUSER['class'])
				        {
					      --$count;
					      continue;
				        }
	
				$HTMLOUT .="<tr>".
					 	"<td align='center'>".$post['id']."</td>".
						"<td align=left width='100%'><a href='".$_SERVER['PHP_SELF']."?action=viewtopic&amp;highlight=$keywords&amp;topicid=".$post['topicid']."&amp;page=p".$post['id']."#".$post['id']."'><b>" . htmlspecialchars($post['subject']) . "</b></a></td>".
						"<td align=left style='white-space: nowrap;'>".(empty($post['name']) ? 'unknown['.$post['forumid'].']' : "<a href='".$_SERVER['PHP_SELF']."?action=viewforum&amp;forumid=".$post['forumid']."'><b>" . htmlspecialchars($post['name']) . "</b></a>")."</td>".
						"<td align=left style='white-space: nowrap;'>".(empty($post['username']) ? 'unknown['.$post['userid'].']' : "<b><a href='{$TBDEV['baseurl']}/userdetails.php?id=".$post['userid']."'>".$post['username']."</a></b>")."<br />at ".get_date($post['added'], 'DATE',1,0)."</td>".
					 "</tr>";
			}
			$HTMLOUT .= end_table();
			
			$HTMLOUT .= end_main_frame();
			$HTMLOUT .= $pager['pagerbottom'];
			$found ="[<b><font color='red'> Found $count post" . ($count != 1 ? "s" : "")." </font></b> ]";
			
		}
	}
	$HTMLOUT .="<div>
	  <div><center><h1>Search on Forums</h1> ". ($error ? "[<b><font color='red'> Nothing Found</font></b> ]" : $found)."</center></div>
	  <div style='margin-left: 53px; margin-top: 13px;'>
	<form method='get' action='".$_SERVER['PHP_SELF']."' id='search_form' style='margin: 0pt; padding: 0pt; font-family: Tahoma,Arial,Helvetica,sans-serif; font-size: 11px;'>
	<input type='hidden' name='action' value='search' />
	<table border='0' cellpadding='0' cellspacing='0' width='50%'>
	<tbody>
	<tr>
	<td valign='top'><b>By keyword:</b></td>
	</tr>
	<tr>
	<td valign='top'>			
  <input name='keywords' type='text' value='".$keywords."' size='65' /><br />
  <font class='small'><b>Note:</b> Searches <u>only</u> in posts.</font></td>
	<td valign='top'>
	<input type='submit' value='search' /></td>
	</tr>
	</tbody>
	</table>
	</form>
 </div>
	</div>";
	print stdhead("Forum Search") . $HTMLOUT . stdfoot();
	exit();



    } else if ($action == 'forumview') {
        $ovfid = (isset($_GET["forid"]) ? (int)$_GET["forid"] : 0);
        if (!is_valid_id($ovfid))
            stderr('Error', 'Invalid ID!');

        $res = mysql_query("SELECT name FROM overforums WHERE id = $ovfid") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res) or stderr('Sorry', 'No forums with that ID!');

        mysql_query("UPDATE users SET forum_access = " . time() . " WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);

  
        if ($TBDEV['forums_online'] == 0)
        $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
        $HTMLOUT .= begin_main_frame();

     
	$HTMLOUT .="<h1 align='center'><b><a href='".$_SERVER['PHP_SELF']."'>Forums</a></b> -> ". htmlspecialchars($arr["name"])."</h1>

	<table border='1' cellspacing='0' cellpadding='5' width='{$forum_width}'>
		<tr>
        	<td class='colhead' align='left'>Forums</td>
            <td class='colhead' align='right'>Topics</td>
		<td class='colhead' align='right'>Posts</td>
		<td class='colhead' align='left'>Last post</td>
	</tr>";


        $HTMLOUT .= show_forums($ovfid);

        $HTMLOUT .= end_table();

        $HTMLOUT .= end_main_frame();
        print stdhead("Forums") . $HTMLOUT . stdfoot();
        exit();
    
    } else { // -------- Default action: View forums
            if (isset($_GET["catchup"])) {
                catch_up();

                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            $f_mod='';
            mysql_query("UPDATE users SET forum_access = '" . time() . "' WHERE id={$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
            $sub_forums = mysql_query(" SELECT f.id, f2.name, f2.id AS subid,f2.postcount,f2.topiccount, p.added, p.anonymous, p.userid, p.id AS pid, u.username, t.subject,t.id as tid,r.lastpostread,t.lastpost
									FROM forums AS f
									LEFT JOIN forums AS f2 ON f2.place = f.id AND f2.minclassread<=" . sqlesc($CURUSER["class"]) . "
									LEFT JOIN posts AS p ON p.id = (SELECT MAX(lastpost) FROM topics WHERE forumid = f2.id )
									LEFT JOIN users AS u ON u.id = p.userid
									LEFT JOIN topics AS t ON t.id = p.topicid
									LEFT JOIN readposts AS r ON r.userid =" . sqlesc($CURUSER["id"]) . " AND r.topicid = p.topicid
									ORDER BY t.lastpost ASC, f2.name , f.id ASC
									");
            while ($a = mysql_fetch_assoc($sub_forums)) {
                if ($a["subid"] == 0)
                    $forums[$a["id"]] = false;
                else {
                    $forums[$a["id"]]["lastpost"] = array("anonymous" => $a["anonymous"],"postid" => $a["pid"], "userid" => $a["userid"], "user" => $a["username"], "topic" => $a["subject"], "topic" => $a["tid"], "tname" => $a["subject"], "added" => $a["added"]);
                    $forums[$a["id"]]["count"][] = array("posts" => $a["postcount"], "topics" => $a["topiccount"]);
                    $forums[$a["id"]]["topics"][] = array ("id" => $a["subid"], "name" => $a["name"], "new" => ($a["lastpost"]) != $a["lastpostread"] ? 1 : 0);
                }
            }
            $r_mod = mysql_query("SELECT f.id,m.user,m.uid FROM forums as f LEFT JOIN forum_mods as m ON f.id = m.fid ORDER BY f.id ") or sqlerr(__FILE__, __LINE__);

            while ($a_mod = mysql_fetch_assoc($r_mod)) {
                if (!isset($a_mod["uid"]))
                    $f[$a_mod["id"]] = false;
                else
                    $f_mod[$a_mod["id"]][] = array("user" => $a_mod["user"], "id" => $a_mod["uid"]);
            }
           

            if ($TBDEV['forums_online'] == 0)
            $HTMLOUT .= stdmsg('Warning', 'Forums are currently in maintainance mode');
            $HTMLOUT .= begin_main_frame();

           $HTMLOUT .="<h1 align='center'><b>{$TBDEV['site_name']} - Forum</b></h1>
	<br />
	<table border='1' cellspacing='0' cellpadding='5' width='{$forum_width}'>";
	$ovf_res = mysql_query("SELECT id, name, minclassview FROM overforums ORDER BY sort ASC") or sqlerr(__FILE__, __LINE__);
	while ($ovf_arr = mysql_fetch_assoc($ovf_res))
	{
	if ($CURUSER['class'] < $ovf_arr["minclassview"])
	continue;
  $ovfid = (int)$ovf_arr["id"];
  $ovfname = $ovf_arr["name"];
	$HTMLOUT .="<tr>
      <td align='left' class='colhead' width='100%'><a href='".$_SERVER['PHP_SELF']."?action=forumview&amp;forid=".$ovfid."'>
      <b><font color='white'>".htmlspecialchars($ovfname)."</font></b></a></td>
			<td align='right' class='colhead'><font color='white'><b>Topics</b></font></td>
			<td align='right' class='colhead'><font color='white'><b>Posts</b></font></td>
			<td align='left' class='colhead'><font color='white'><b>Last post</b></font></td>
		</tr>";
    $HTMLOUT .= show_forums($ovfid, false, $forums, $f_mod, true);
    }
    $HTMLOUT .= end_table();

            if ($use_forum_stats_mod)
                $HTMLOUT .= forum_stats();

	$HTMLOUT .="<p align='center'>
	<a href='". $_SERVER['PHP_SELF']."?action=search'><b>Search Forums</b></a> | 
	<a href='". $_SERVER['PHP_SELF']."?action=viewunread'><b>New Posts</b></a> | 
	<a href='". $_SERVER['PHP_SELF']."?action=getdaily'><b>Todays Posts (Last 24 h.)</b></a> | 
	<a href='". $_SERVER['PHP_SELF']."?catchup'><b>Mark all as read</b></a>";
	$HTMLOUT .="</p>";
	$HTMLOUT .= end_main_frame(); 
where ("{$lang['forums_view_forums']}",$CURUSER["id"]);
print stdhead("Forum") . $HTMLOUT . stdfoot();
}
?>