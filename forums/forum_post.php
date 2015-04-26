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

if ( ! defined( 'IN_TBDEV_FORUM' ) )
{
	print "{$lang['forum_post_access']}";
	exit();
}



    $forumid = isset($_POST["forumid"]) ? (int)$_POST["forumid"] : 0;
    $topicid = isset($_POST["topicid"]) ? (int)$_POST["topicid"] : 0;

    if (!is_valid_id($forumid) && !is_valid_id($topicid))
      stderr("{$lang['forum_post_error']}", "{$lang['forum_post_bad_id']}");

    $newtopic = $forumid > 0;

    

    if ($newtopic)
    {
      $subject = trim(strip_tags($_POST["subject"]));

      if (!$subject)
        stderr("{$lang['forum_post_error']}", "{$lang['forum_post_subject']}");

      if (strlen($subject) > $maxsubjectlength)
        stderr("{$lang['forum_post_error']}", "{$lang['forum_post_subject_limit']}");
    }
    else
      $forumid = get_topic_forum($topicid) or die("{$lang['forum_post_bad_topic']}");

    //------ Make sure sure user has write access in forum

    $arr = get_forum_access_levels($forumid) or die("{$lang['forum_post_bad_forum']}");

    if (get_user_class() < $arr["write"] || ($newtopic && get_user_class() < $arr["create"]))
      stderr("{$lang['forum_post_error']}", "{$lang['forum_post_denied']}");

    $body = trim($_POST["body"]);

    if ($body == "")
      stderr("{$lang['forum_post_error']}", "{$lang['forum_post_body']}");

    $userid = $CURUSER["id"];

    if ($newtopic)
    {
      //---- Create topic

      $subject = sqlesc($subject);

      @mysql_query("INSERT INTO topics (userid, forumid, subject) VALUES($userid, $forumid, $subject)") or sqlerr(__FILE__, __LINE__);

      $topicid = mysql_insert_id() or stderr("{$lang['forum_post_error']}", "{$lang['forum_post_topic_id']}");
      //===add karma 
      @mysql_query("UPDATE users SET seedbonus = seedbonus+2.0 WHERE id = ".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
      //===end
      $message1 = $CURUSER['username'] . " Created a new forum topic [url={$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid&page=last]{$subject}[/url]";
    }
    else
    {
      //---- Make sure topic exists and is unlocked

      $res = mysql_query("SELECT * FROM topics WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);

      $arr = mysql_fetch_assoc($res) or die("{$lang['forum_post_topic_na']}");

      if ($arr["locked"] == 'yes' && get_user_class() < UC_MODERATOR)
        stderr("{$lang['forum_post_error']}", "{$lang['forum_post_locked']}");

      //---- Get forum ID

      $forumid = $arr["forumid"];
    }

    //------ Insert post

    $added = time();

    $body = sqlesc($body);

    @mysql_query("INSERT INTO posts (topicid, userid, added, body) " .
    "VALUES($topicid, $userid, $added, $body)") or sqlerr(__FILE__, __LINE__);

    $postid = mysql_insert_id() or die("{$lang['forum_post_post_na']}");
    $subject = mysql_result(mysql_query("SELECT subject from topics where id=".$topicid),0);
	$message = $CURUSER['username'] . " replied to the thread [url={$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid&page=last]{$subject}[/url]";	
  if ($newtopic ){
  if (!in_array($forumid, array("9"))) {
  autoshout($message1);
  }
  }else{
  if (!in_array($forumid, array("9"))) {
  autoshout($message); 
  }
  }

    //------ Update topic last post

    update_topic_last_post($topicid);
    //===add karma 
    @mysql_query("UPDATE users SET seedbonus = seedbonus+1.0 WHERE id = ".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
    //==end

    //------ All done, redirect user to the post

    $headerstr = "Location: {$TBDEV['baseurl']}/forums.php?action=viewtopic&topicid=$topicid&page=last";

    if ($newtopic)
      header($headerstr);

    else
      header("$headerstr#$postid");

    die;
?>