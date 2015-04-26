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
|   Friend system v1.2
+------------------------------------------------
**/

require_once "include/bittorrent.php";
require_once "include/user_functions.php";

dbconn(false);
loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('friends') );
    
    $userid = isset($_GET['id']) ? (int)$_GET['id'] : $CURUSER['id'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    //if (!$userid)
    //	$userid = $CURUSER['id'];

    if (!is_valid_id($userid))
      stderr($lang['friends_error'], $lang['friends_invalid_id']);

    if ($userid != $CURUSER["id"])
    stderr($lang['friends_error'], $lang['friends_no_access']);


    // action: add -------------------------------------------------------------

    if ($action == 'add')
    {
      $targetid = 0+$_GET['targetid'];
      $type = $_GET['type'];

      if (!is_valid_id($targetid))
        stderr($lang['friends_error'], $lang['friends_invalid_id']);

      if ($type == 'friend')
      {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
      }
      elseif ($type == 'block')
      {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
      }
      else
       stderr($lang['friends_error'], $lang['friends_unknown']);

      $r = sql_query("SELECT id FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
      //prevents general users from adding staffs to blocklist but allows mod+ to add other mod+ to add in their friendlist
      $rclass = sql_query("SELECT class FROM users WHERE id=".sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);  
      $rclass = mysql_fetch_assoc($rclass) or stderr("Error", "This user does not exists!"); 
      if ($rclass['class'] >= UC_MODERATOR && $CURUSER['class'] < UC_MODERATOR) 
       stderr("Error", "You can't add staff members to your friend list.");
      if (mysql_num_rows($r) == 1)
       stderr($lang['friends_error'], sprintf($lang['friends_already'], htmlentities($table_is)));
       

      sql_query("INSERT INTO $table_is VALUES (0,$userid, $targetid)") or sqlerr(__FILE__, __LINE__);
      header("Location: {$TBDEV['baseurl']}/friends.php?id=$userid#$frag");
      die;
    }

    // action: delete ----------------------------------------------------------

    if ($action == 'delete')
    {
      $targetid = (int)$_GET['targetid'];
      $sure = isset($_GET['sure']) ? htmlentities($_GET['sure']) : false;
      $type = isset($_GET['type']) ? ($_GET['type'] == 'friend' ? 'friend' : 'block') : stderr($lang['friends_error'], 'LoL');

      if (!is_valid_id($targetid))
      stderr($lang['friends_error'], $lang['friends_invalid_id']);

      if (!$sure)
        stderr("{$lang['friends_delete']} $type", sprintf($lang['friends_sure'], $type, $userid, $type, $targetid) );

      if ($type == 'friend')
      {
        sql_query("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);
        if (mysql_affected_rows() == 0)
         stderr($lang['friends_error'], $lang['friends_no_friend']);
        $frag = "friends";
      }
      elseif ($type == 'block')
      {
        sql_query("DELETE FROM blocks WHERE userid=$userid AND blockid=$targetid") or sqlerr(__FILE__, __LINE__);
        if (mysql_affected_rows() == 0)
        stderr($lang['friends_error'], $lang['friends_no_block']);
        $frag = "blocks";
      }
      else
      stderr($lang['friends_error'], $lang['friends_unknown']);

      header("Location: {$TBDEV['baseurl']}/friends.php?id=$userid#$frag");
      die;
    }

    // main body  -----------------------------------------------------------------

    $res = sql_query("SELECT * FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
    $user = mysql_fetch_assoc($res) or stderr($lang['friends_error'], $lang['friends_no_user']);
    //stderr("Error", "No user with ID.");
    
    $HTMLOUT = '';
    
    $donor = ($user["donor"] == "yes") ? "<img src='{$TBDEV['pic_base_url']}starbig.gif' alt='{$lang['friends_donor']}' style='margin-left: 4pt' />" : '';
    $warned = ($user["warned"] == "yes") ? "<img src='{$TBDEV['pic_base_url']}warnedbig.gif' alt='{$lang['friends_warned']}' style='margin-left: 4pt' />" : '';


    
/////////////////////// FRIENDS BLOCK ///////////////////////////////////////
    
    $res = sql_query("SELECT f.friendid as id, u.username AS name, u.class, u.support, u.avatar, u.title, u.donor, u.leechwarn, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE userid=$userid ORDER BY name") or sqlerr(__FILE__, __LINE__);
    
    $count = mysql_num_rows($res);
    $friends = '';
    
    if( !$count)
    {
      $friends = "<em>{$lang['friends_friends_empty']}.</em>";
    }
    else
    {
      
      while ($friend = mysql_fetch_assoc($res))
      {
        $title = $friend["title"];
        if (!$title)
          $title = get_user_class_name($friend["class"]);
        
        $userlink = "<a href='userdetails.php?id={$friend['id']}'><b>".htmlentities($friend['name'], ENT_QUOTES)."</b></a>";
        $userlink .= get_user_icons($friend) . " ($title)<br />{$lang['friends_last_seen']} " . get_date( $friend['last_access'],'');
        
        $delete = "<span class='btn'><a href='friends.php?id=$userid&amp;action=delete&amp;type=friend&amp;targetid={$friend['id']}'><font color=#FFFFFF>{$lang['friends_remove']}</font></a></span>";
          
        $pm = "&nbsp;<span class='btn'><a href='sendmessage.php?receiver={$friend['id']}'><font color=#FFFFFF>{$lang['friends_pm']}</font></a></span>";
          
        $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($friend["avatar"]) : "");
        if (!$avatar)
          $avatar = "{$TBDEV['pic_base_url']}default_avatar.gif";
          
        $friends .= "<div style='border: 1px solid #97BCC2;padding:5px; color: #257579;'>".($avatar ? "<img width='50px' src='$avatar' style='float:right;' alt='' />" : ""). "<p >{$userlink}<br /><br />{$delete}{$pm}</p></div><br />";
        
      }
      
    }
    
    //if ($i % 2 == 1)
      //$HTMLOUT .= "<td class='bottom' width='50%'>&nbsp;</td></tr></table>\n";
    //print($friends);
   // $HTMLOUT .= "</td></tr></table>\n";

    
/////////////////////// FRIENDS BLOCK END///////////////////////////////////////
    
 
       
//////////////////// ENEMIES BLOCK ////////////////////////////

    $res = sql_query("SELECT b.blockid as id, u.username AS name, u.donor, u.warned, u.enabled, u.last_access FROM blocks AS b LEFT JOIN users as u ON b.blockid = u.id WHERE userid=$userid ORDER BY name") or sqlerr(__FILE__, __LINE__);
    
    $blocks = '';
    
    if(mysql_num_rows($res) == 0)
    {
      $blocks = "{$lang['friends_blocks_empty']}<em>.</em>";
    }
    else
    {
      //$i = 0;
      //$blocks = "<table width='100%' cellspacing='0' cellpadding='0'>";
      while ($block = mysql_fetch_assoc($res))
      {
        $blocks .= "<div style='border: 1px solid #97BCC2;padding:5px;'>";
        $blocks .= "<span class='btn' style='float:right;'><a href='friends.php?id=$userid&amp;action=delete&amp;type=block&amp;targetid={$block['id']}'><font color=#FFFFFF>{$lang['friends_delete']}</font></a></span><br />";
        $blocks .= "<p><a href='userdetails.php?id={$block['id']}'><b>" . htmlentities($block['name'], ENT_QUOTES) . "</b></a>";
        $blocks .= get_user_icons($block) . "</p></div><br />";
        
      }
      
    }
//////////////////// ENEMIES BLOCK END ////////////////////////////  

    $HTMLOUT .= "<table class='main' width='750' border='1' cellspacing='0' cellpadding='4'>
    <thead><td align='center' colspan='2' class='colhead' style='text-align: center;' title='Personal Lists'>{$lang['friends_personal']} ".htmlentities($user['username'], ENT_QUOTES)."</td></thead>
    <tbody><tr>
      <td class='subheader' align='center' style='width:50%; '><a name='friends'><b title='{$lang['friends_friends_list']}'>{$lang['friends_friends_list']}</b></a></td>
      <td class='subheader' align='center' style='width:50%;  vertical-align:top;'><a name='blocks'><b title='{$lang['friends_blocks_list']}'>{$lang['friends_blocks_list']}</b></a></td>
    </tr>
    <tr>
      <td style='padding:10px;background-color:#DFE8F4;width:50%;'>$friends</td>
      <td style='padding:10px;background-color:#DFE8F4' valign='top'>$blocks</td>
    </tr></tbody>
    </table>";
    
    $HTMLOUT .= " <p><a href='users.php'><b>{$lang['friends_user_list']}</b></a></p>";
    
    print stdhead("{$lang['friends_stdhead']} {$user['username']}") . $HTMLOUT . stdfoot();
?>