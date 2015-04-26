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

require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";
require_once ROOT_PATH."/cache/timezones.php";
require_once "include/page_verify.php";

dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('my') );
    //2-way handshake varification
    $newpage = new page_verify();  
    $newpage->create('takeprofileedit');
    //end 2-way varification
/*
$res = mysql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND location IN ('in', 'both')") or print(mysql_error());
$arr = mysql_fetch_row($res);
$messages = $arr[0];
$res = mysql_query("SELECT COUNT(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " AND location IN ('in', 'both') AND unread='yes'") or print(mysql_error());
$arr = mysql_fetch_row($res);
$unread = $arr[0];
$res = mysql_query("SELECT COUNT(*) FROM messages WHERE sender=" . $CURUSER["id"] . " AND location IN ('out', 'both')") or print(mysql_error());
$arr = mysql_fetch_row($res);
$outmessages = $arr[0];
*/


    $HTMLOUT = '';
    
    if (isset($_GET["edited"])) 
    {
      $HTMLOUT .= "<h1>{$lang['my_updated']}!</h1>\n";
      if (isset($_GET["mailsent"]))
        $HTMLOUT .= "<h2>{$lang['my_mail_sent']}!</h2>\n";
    }
    elseif (isset($_GET["emailch"]))
    {
      $HTMLOUT .= "<h1>{$lang['my_emailch']}!</h1>\n";
    }
    //else
      //print("<h1>Welcome, <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>!</h1>\n");
    $user_header = "<span style='font-size: 20px;'><a href='userdetails.php?id={$CURUSER['id']}'>{$CURUSER['username']}</a></span>";
    
    if(!empty($CURUSER['avatar']) && $CURUSER['av_w'] > 5 && $CURUSER['av_h'] > 5)
    {
      $avatar = "<img src='{$CURUSER['avatar']}' width='{$CURUSER['av_w']}' height='{$CURUSER['av_h']}' alt='' />";
    }
    else
    {
      $avatar = "<img src='{$TBDEV['pic_base_url']}forumicons/default_avatar.gif' alt='' />";
    }

    $HTMLOUT .= "<script type='text/javascript' src='scripts/jquery.js'></script>
    <script type='text/javascript' src='scripts/jquery.pstrength-min.1.2.js'></script>
    <script type='text/javascript'>
    $(document).ready(function () { 
    $('#password').pstrength();
    });  

    function daylight_show()
    {
      if ( document.getElementById( 'tz-checkdst' ).checked )
      {
        document.getElementById( 'tz-checkmanual' ).style.display = 'none';
      }
      else
      {
        document.getElementById( 'tz-checkmanual' ).style.display = 'block';
      }
    }
    
    </script>


    <table border='1' cellspacing='0' cellpadding='10' align='center' width='98%'>
    <!--<tr>
    <td align='center' width='33%'><a href='logout.php'><b>{$lang['my_logout']}</b></a></td>
    <td align='center' width='33%'><a href='mytorrents.php'><b>{$lang['my_torrents']}</b></a></td>
    <td align='center' width='33%'><a href='friends.php'><b>{$lang['my_users_lists']}</b></a></td>
    </tr>-->
    <tr>
      <td valign='top'>
      $user_header<br />
      $avatar<br />
      <a href='mytorrents.php'>{$lang['my_edit_torrents']}</a><br />
      <a href='friends.php'>{$lang['my_edit_friends']}</a><br />
      <a href='users.php'>{$lang['my_search']}</a>
      </td>
    <td>
      <form method='post' action='takeprofedit.php'>
      <table border='1' cellspacing='0' cellpadding='5' width='100%'>";


    /***********************

    $res = mysql_query("SELECT COUNT(*) FROM ratings WHERE user=" . $CURUSER["id"]);
    $row = mysql_fetch_array($res,MYSQL_NUM);
    tr("Ratings submitted", $row[0]);

    $res = mysql_query("SELECT COUNT(*) FROM comments WHERE user=" . $CURUSER["id"]);
    $row = mysql_fetch_array($res,MYSQL_NUM);
    tr("Written comments", $row[0]);

    ****************/
    $langs = "";
    $LANGS=sql_query("SELECT * FROM lang");
    while($LA=mysql_fetch_array($LANGS)){
	   if(file_exists("lang/".$LA['dir']."/lang_index.php"))
		$langs .= "<option value='{$LA['dir']}' ".($CURUSER['language']==$LA['dir']?"selected='selected'":"").">{$LA['name']}</option>\n";
    }
    $stylesheets ='';
    $ss_r = mysql_query("SELECT * from stylesheets") or die;
    $ss_sa = array();
    while ($ss_a = mysql_fetch_assoc($ss_r))
    {
      $ss_id = $ss_a["id"];
      $ss_name = $ss_a["name"];
      $ss_sa[$ss_name] = $ss_id;
    }
    ksort($ss_sa);
    reset($ss_sa);
    while (list($ss_name, $ss_id) = each($ss_sa))
    {
      if ($ss_id == $CURUSER["stylesheet"])
      { 
        $ss = " selected='selected'";
      }
      else
      {
        $ss = "";
      }
      $stylesheets .= "<option value='$ss_id'$ss>$ss_name</option>\n";
    }

    $countries = "<option value='0'>---- {$lang['my_none']} ----</option>\n";
    $ct_r = sql_query("SELECT id,name FROM countries ORDER BY name") or sqlerr(__FILE__,__LINE__);
    
    while ($ct_a = mysql_fetch_assoc($ct_r))
    {
      $countries .= "<option value='{$ct_a['id']}'" . ($CURUSER["country"] == $ct_a['id'] ? " selected='selected'" : "") . ">{$ct_a['name']}</option>\n";
    }
        //-----------------------------------------
        // Work out the timezone selection
        //-----------------------------------------
        $offset = ($CURUSER['time_offset'] != "") ? (string)$CURUSER['time_offset'] : (string)$TBDEV['time_offset'];
        
        $time_select = "<select name='user_timezone'>";
        
        //-----------------------------------------
        // Loop through the langauge time offsets and names to build our
        // HTML jump box.
        //-----------------------------------------
        
        foreach( $TZ as $off => $words )
        {
          if ( preg_match("/^time_(-?[\d\.]+)$/", $off, $match))
          {
            $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
          }
        }
        
        $time_select .= "</select>";
     
        //-----------------------------------------
        // DST IN USE?
        //-----------------------------------------
        
        if ($CURUSER['dst_in_use'])
        {
          $dst_check = 'checked="checked"';
        }
        else
        {
          $dst_check = '';
        }
        
        //-----------------------------------------
        // DST CORRECTION IN USE?
        //-----------------------------------------
        
        if ($CURUSER['auto_correct_dst'])
        {
          $dst_correction = 'checked="checked"';
        }
        else
        {
          $dst_correction = '';
        }
        
        
    $HTMLOUT .= tr($lang['my_acc_parked'],"<input type='radio' name='parked'" . ($CURUSER["parked"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes
    <input type='radio' name='parked'" .  ($CURUSER["parked"] == "no" ? " checked='checked'" : "") . " value='no' />No
    <br /><font class='small' size='1'>{$lang['my_acc_parked_message']}<br />{$lang['my_acc_parked_message1']}</font>",1);
    
    $HTMLOUT .= tr($lang['my_accept_pm'],
    "<input type='radio' name='acceptpms'" . ($CURUSER["acceptpms"] == "yes" ? " checked='checked'" : "") . " value='yes' />{$lang['my_except_blocks']}
    <input type='radio' name='acceptpms'" .  ($CURUSER["acceptpms"] == "friends" ? " checked='checked'" : "") . " value='friends' />{$lang['my_only_friends']}
    <input type='radio' name='acceptpms'" .  ($CURUSER["acceptpms"] == "no" ? " checked='checked'" : "") . " value='no' />{$lang['my_only_staff']}"
    ,1);
    
    $HTMLOUT .= tr($lang['my_gender'],
    "<input type='radio' name='gender'" . ($CURUSER["gender"] == "Male" ? " checked='checked'" : "") . " value='Male' /><img src='".$TBDEV['pic_base_url']."male.gif'  border='0' alt='{$lang['my_male']}' />
    <input type='radio' name='gender'" .  ($CURUSER["gender"] == "Female" ? " checked='checked'" : "") . " value='Female' /><img src='".$TBDEV['pic_base_url']."female.gif'  border='0' alt='{$lang['my_female']}' />
    <input type='radio' name='gender'" .  ($CURUSER["gender"] == "N/A" ? " checked='checked'" : "") . " value='N/A' /><img src='".$TBDEV['pic_base_url']."na.gif'  border='0' alt='{$lang['my_none']}' />"
    ,1);
    $HTMLOUT .= tr("".$lang['my_shoutback']."", "<input type='radio' name='shoutboxbg'" . ($CURUSER["shoutboxbg"] == "1" ? " checked='checked'" : "") . " value='1' />{$lang['my_shoutback_white']}
    <input type='radio' name='shoutboxbg'" . ($CURUSER["shoutboxbg"] == "2" ? " checked='checked'" : "") . " value='2' />{$lang['my_shoutback_grey']}<input type='radio' name='shoutboxbg'" . ($CURUSER["shoutboxbg"] == "3" ? " checked='checked'" : "") . " value='3' />{$lang['my_shoutback_black']}", 1);



    $HTMLOUT .= tr($lang['my_delete_pms'], "<input type='checkbox' name='deletepms'" . ($CURUSER["deletepms"] == "yes" ? " checked='checked'" : "") . " /> {$lang['my_default_delete']}",1);
    $HTMLOUT .= tr($lang['my_save_pms'], "<input type='checkbox' name='savepms'" . ($CURUSER["savepms"] == "yes" ? " checked='checked'" : "") . " /> {$lang['my_default_save']}",1);
    $HTMLOUT .= tr($lang['my_pmstyle'],"<input type='radio' name='pmstyle'" . ($CURUSER["pmstyle"] == "popup" ? " checked='checked'" : "") . " value='popup' />Pop-Up 
        <input type='radio' name='pmstyle'" . ($CURUSER["pmstyle"] == "clasic" ? " checked='checked'" : "") . " value='clasic' />Clasic 
        <br /><font class='small' size='1'>{$lang['my_pmstyle_info']}</font>",1);
    $HTMLOUT .=tr($lang['my_cats_icons'], 
"<input type='radio' name='cats_icons'" . ($CURUSER["cats_icons"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes 
<input type='radio' name='cats_icons'" . ($CURUSER["cats_icons"] == "no" ? " checked='checked'" : "") . " value='no' />No
<br /><font class='small' size='1'>{$lang['my_default_cats_icons']}</font>",1);
    //$HTMLOUT .= tr($lang['my_cats_icons'], "<input type='checkbox' name='cats_icons'" . ($CURUSER["cats_icons"] == "yes" ? " checked='checked'" : "") . " /> {$lang['my_default_cats_icons']}",1);
    $HTMLOUT .= tr($lang['my_clearnewtagmanually'], "<input type='checkbox' name='clear_new_tag_manually'" . ($CURUSER["clear_new_tag_manually"] == "yes" ? " checked='checked'" : "") . " /> {$lang['my_default_clearnewtagmanually']}",1);

    $categories = '';
    
    $r = sql_query("SELECT id,name FROM categories ORDER BY name") or sqlerr();
    //$categories = "Default browsing categories:<br>\n";
    if (mysql_num_rows($r) > 0)
    {
      $categories .= "<table><tr>\n";
      $i = 0;
      while ($a = mysql_fetch_assoc($r))
      {
        $categories .=  ($i && $i % 2 == 0) ? "</tr><tr>" : "";
        $categories .= "<td class='bottom' style='padding-right: 5px'><input name='cat{$a['id']}' type='checkbox' " . (strpos($CURUSER['notifs'], "[cat{$a['id']}]") !== false ? " checked='checked'" : "") . " value='yes' />&nbsp;" . htmlspecialchars($a["name"]) . "</td>\n";
        ++$i;
      }
      $categories .= "</tr></table>\n";
    }

    $HTMLOUT .= tr($lang['my_email_notif'], "<input type='checkbox' name='pmnotif'" . (strpos($CURUSER['notifs'], "[pm]") !== false ? " checked='checked'" : "") . " value='yes' /> {$lang['my_notify_pm']}<br />\n" .
       "<input type='checkbox' name='emailnotif'" . (strpos($CURUSER['notifs'], "[email]") !== false ? " checked='checked'" : "") . " value='yes' /> {$lang['my_notify_torrent']}\n"
       , 1);
    $HTMLOUT .= tr($lang['my_fpm'], 
        "<input type='radio' name='subscription_pm' " . ($CURUSER["subscription_pm"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes 
        <input type='radio' name='subscription_pm' " . ($CURUSER["subscription_pm"] == "no" ? " checked='checked'" : "") . " value='no' />No<br /><font class='small' size='1'>{$lang['my_fpm_message']}</font>", 
        1);
    $HTMLOUT .=tr($lang['my_split'],
        "<input type='radio' name='split'" . ($CURUSER["split"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes
        <input type='radio' name='split'" . ($CURUSER["split"] == "no" ? " checked='checked'" : "") . " value='no' />No"
        ,1);
    $HTMLOUT .= tr($lang['my_sticky'],
        "<input type='radio' name='show_sticky'" . ($CURUSER["show_sticky"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes
        <input type='radio' name='show_sticky'" .  ($CURUSER["show_sticky"] == "no" ? " checked='checked'" : "") . " value='no' />No<br /><font class='small' size='1'>{$lang['my_sticky_message']}</font>",1);
    $HTMLOUT .= tr($lang['my_xxx'],
        "<input type='radio' name='view_xxx'" . ($CURUSER["view_xxx"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes
        <input type='radio' name='view_xxx'" .  ($CURUSER["view_xxx"] == "no" ? " checked='checked'" : "") . " value='no' />No<br /><font class='small' size='1'>{$lang['my_xxx_message']}</font>"
        ,1);
    //$HTMLOUT .= tr($lang['my_xxx'], "<input type='checkbox' name='view_xxx'" . ($CURUSER["view_xxx"] == "yes" ? " checked='checked'" : "") . "/> If un-selected, torrents in category XXX will not be<br/> visible on the <a href='browse.php'>browse</a> page regardless of if the category is<br/> selected for viewing",1); 
    $HTMLOUT .= tr($lang['my_browse'],$categories,1);
    $HTMLOUT .= tr($lang['my_stylesheet'], "<select name='stylesheet'>\n$stylesheets\n</select>",1);
    $HTMLOUT .= tr($lang['my_language'], "<select name='lang'>$langs</select>",1);
    $HTMLOUT .= tr($lang['my_country'], "<select name='country'>\n$countries\n</select>",1);

    // Timezone stuff //
    $HTMLOUT .= tr($lang['my_tz'], $time_select ,1);
    $HTMLOUT .= tr($lang['my_checkdst'], "<input type='checkbox' name='checkdst' id='tz-checkdst' onclick='daylight_show()' value='1' $dst_correction />&nbsp;{$lang['my_auto_dst']}<br />
    <div id='tz-checkmanual' style='display: none;'><input type='checkbox' name='manualdst' value='1' $dst_check />&nbsp;{$lang['my_is_dst']}</div>",1);
    // Timezone stuff end //

    $HTMLOUT .="<tr><td class='rowhead'>{$lang['my_avatar']}</td><td><input name='avatar' size='50' value='" . htmlspecialchars($CURUSER["avatar"]) . "' /><br />
        <font class='small'>Width should be 150px. (Will be resized if necessary)\n<br />
        If you need a host for the picture, try our  <a href='{$TBDEV['baseurl']}/bitbucket.php'>Bitbucket</a>.</font>
        <br /><input type='checkbox' name='offavatar' ".($CURUSER["offavatar"] == "yes" ? " checked='checked'" : "")." /><b>This avatar may be offensive to some people.</b><br />
        <font class='small'>Please check this box if your avatar contains nudity or may<br />otherwise be potentially offensive to or unsuitable for minors.</font></td></tr>";
    $HTMLOUT .= tr($lang['my_signature'], "<input name='signature' size='50' value='" . htmlspecialchars($CURUSER["signature"]) .
      "' /><br />\n{$lang['my_signature_info']}",1);
    $HTMLOUT .= tr($lang['my_tor_perpage'], "<input type='text' size='10' name='torrentsperpage' value='$CURUSER[torrentsperpage]' /> {$lang['my_default']}",1);
    $HTMLOUT .= tr($lang['my_top_perpage'], "<input type='text' size='10' name='topicsperpage' value='$CURUSER[topicsperpage]' /> {$lang['my_default']}",1);
    $HTMLOUT .= tr($lang['my_post_perpage'], "<input type='text' size='10' name='postsperpage' value='$CURUSER[postsperpage]' /> {$lang['my_default']}",1);
    $HTMLOUT .="<tr><td class='rowhead'>{$lang['my_view_avatars']}</td><td><input type='radio' name='avatars'" . ($CURUSER["avatars"] == "all" ? " checked='checked'" : "") . " value='all' />All
        <input type='radio' name='avatars' " .  ($CURUSER["avatars"] == "some" ? " checked='checked'" : "") . " value='some' />All except potentially offensive
        <input type='radio' name='avatars' " .  ($CURUSER["avatars"] == "none" ? " checked='checked'" : "") . " value='none' />None</td></tr>";
    $HTMLOUT .= tr($lang['my_view_signatures'], "<input type='checkbox' name='signatures'" . ($CURUSER["signatures"] == "yes" ? " checked='checked'" : "") . " /> {$lang['my_low_bw']}",1);
    $HTMLOUT .= tr($lang['my_info'], "<textarea name='info' cols='50' rows='4'>" . htmlentities($CURUSER["info"], ENT_QUOTES) . "</textarea><br />{$lang['my_tags']}", 1);
        $secretqs = "<option value='0'>{$lang['my_none_select']}</option>\n";
			  $questions = array(
			  array("id"=> "1", "question"=> "{$lang['my_q1']}"),
			  array("id"=> "2", "question"=> "{$lang['my_q2']}"),
			  array("id"=> "3", "question"=> "{$lang['my_q3']}"),
			  array("id"=> "4", "question"=> "{$lang['my_q4']}"),
			  array("id"=> "5", "question"=> "{$lang['my_q5']}"),
			  array("id"=> "6", "question"=> "{$lang['my_q6']}")
			  );
			  
			  foreach($questions as $sctq){  
			  $secretqs .= "<option value='".$sctq['id']."'" .  ($CURUSER["passhint"] == $sctq['id'] ? " selected='selected'" : "") .  ">".$sctq['question']."</option>\n"; 
			  }
			  
    $HTMLOUT .= tr($lang['my_question'], "<select name='changeq'>\n$secretqs\n</select>",1);
    $HTMLOUT .= tr($lang['my_sec_answer'], "<input type='text' name='secretanswer' size='40' />", 1);
    $HTMLOUT .= tr($lang['my_email'], "<input type='text' name='email' size='50' value='" . htmlspecialchars($CURUSER["email"]) . "' /><br />{$lang['my_email_pass']}<br /><input type='password' name='chmailpass' class='keyboardInput' size='50' />", 1);
    $HTMLOUT .= "<tr><td colspan='2' align='left'>{$lang['my_note']}</td></tr>\n";
    $HTMLOUT .= tr($lang['my_chpass'], "<input type='password' name='chpassword' class='keyboardInput' id='#passowrd' size='50' />", 1);
    $HTMLOUT .= tr($lang['my_pass_again'], "<input type='password' name='passagain' class='keyboardInput' size='50' />", 1);

    function priv($name, $descr) {
      global $CURUSER;
      if ($CURUSER["privacy"] == $name)
        return "<input type='radio' name='privacy' value='$name' checked='checked' /> $descr";
      return "<input type='radio' name='privacy' value='$name' /> $descr";
    }

    /* tr("Privacy level",  priv("normal", "Normal") . " " . priv("low", "Low (email address will be shown)") . " " . priv("strong", "Strong (no info will be made available)"), 1); */


    $HTMLOUT .= "<tr><td colspan='2' align='center'>
      <input type='submit' value='{$lang['my_submit']}' class='btn' /> 
      <input type='reset' value='{$lang['my_revert']}' class='btn' />
      </td></tr>
      </table>
      </form>
    </td>
    </tr>
    </table>";

    /*
    if ($messages){
      print("<p>You have $messages message" . ($messages != 1 ? "s" : "") . " ($unread new) in your <a href='inbox.php'><b>inbox</b></a>,<br />\n");
      if ($outmessages)
        print("and $outmessages message" . ($outmessages != 1 ? "s" : "") . " in your <a href='inbox.php?out=1'><b>sentbox</b></a>.\n</p>");
      else
        print("and your <a href='inbox.php?out=1'>sentbox</a> is empty.</p>");
    }
    else
    {
      print("<p>Your <a href='inbox.php'>inbox</a> is empty, <br />\n");
      if ($outmessages)
        print("and you have $outmessages message" . ($outmessages != 1 ? "s" : "") . " in your <a href='inbox.php?out=1'><b>sentbox</b></a>.\n</p>");
      else
        print("and so is your <a href='inbox.php?out=1'>sentbox</a>.</p>");
    }
    */
    //print("<p><a href='users.php'><b>Find User/Browse User List</b></a></p>");
    
    
    print stdhead(htmlentities($CURUSER["username"], ENT_QUOTES) . "{$lang['my_stdhead']}", false) . $HTMLOUT . stdfoot();

?>