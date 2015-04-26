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

ob_start("ob_gzhandler");

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";

dbconn(true);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('index') );
    //$lang = ;
    
    $HTMLOUT = '';
    //Snow falling mod, 
    //$HTMLOUT = "<script type='text/javascript' src='scripts/snow.js'></script>";
    //$HTMLOUT = "<script type='text/javascript' src='scripts/active_p.js'></script>";
    
   $browser = $_SERVER['HTTP_USER_AGENT'];
   if(preg_match("/MSIE/i",$browser))//browser is IE
   {
        $HTMLOUT .="<div class='notification warning2 autoWidth' style='width: 957px;'><span></span>
         <div class='text'><p style='font-size: 12px;'><strong>Warning!</strong>It appears as though you are running Internet Explorer, this site was <b>NOT</b> intended to be viewed with internet explorer and chances are it will not look right and may not even function correctly.
         You should consider downloading a real browser, Firefox from <a href='http://www.mozilla.com/firefox'><font color=#BB7070><b>HERE</b></font></a>. <strong>Get a SAFER browser !</strong></p>
         </div>
        </div>";
   }

   ///////////09 Cached latest user
    if ($CURUSER) {
    $cache_newuser = "./cache/newuser.txt";
    $cache_newuser_life = 2 * 60 ; //2 min
    if (file_exists($cache_newuser) && is_array(unserialize(file_get_contents($cache_newuser))) && (time() - filemtime($cache_newuser)) < $cache_newuser_life)
    $arr = unserialize(@file_get_contents($cache_newuser));
    else {
    $r_new = mysql_query("select id , username FROM users order by id desc limit 1 ") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($r_new);
    $handle = fopen($cache_newuser, "w+");
    fwrite($handle, serialize($arr));
    fclose($handle);
    }
    $new_user = "&nbsp;<a href=\"{$TBDEV['baseurl']}/userdetails.php?id={$arr["id"]}\">" . htmlspecialchars($arr["username"]) . "</a>\n";
    }

//==Stats Begin
    $cache_stats = "./cache/stats.txt";
    $cache_stats_life = 5 * 60; // 5min
    if (file_exists($cache_stats) && is_array(unserialize(file_get_contents($cache_stats))) && (time() - filemtime($cache_stats)) < $cache_stats_life)
    $row = unserialize(@file_get_contents($cache_stats));
    else {
    $stats = mysql_query("SELECT *, seeders + leechers AS peers, seeders / leechers AS ratio, unconnectables / (seeders + leechers) AS ratiounconn FROM stats WHERE id = '1' LIMIT 1") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_assoc($stats);
    $handle = fopen($cache_stats, "w+");
    fwrite($handle, serialize($row));
    fclose($handle);
    }

    $seeders = number_format($row['seeders']);
    $leechers = number_format($row['leechers']);
    $registered = number_format($row['regusers']);
    $unverified = number_format($row['unconusers']);
    $torrents = number_format($row['torrents']);
    $torrentstoday = number_format($row['torrentstoday']);
    $ratiounconn = $row['ratiounconn'];
    $unconnectables = $row['unconnectables'];
    $ratio = round(($row['ratio'] * 100));
    $peers = number_format($row['peers']);
    $numactive = number_format($row['numactive']);
    $donors = number_format($row['donors']);
    $forumposts = number_format($row['forumposts']);
    $forumtopics = number_format($row['forumtopics']);
    $warnedu = number_format($row['warnedu']);
    $disabled = number_format($row['disabled']);
    $malec = number_format($row['malec']);
    $femalec = number_format($row['femalec']);
    //==End
   
   //Modified news system v0.6
    $adminbutton = '';
    if (get_user_class() >= UC_ADMINISTRATOR)
          $adminbutton = "&nbsp;<span style='float:right;'><a href='admin.php?action=news' style='color: #DBE3EA;' title='Add / Edit'>Add / Edit&nbsp&nbsp&nbsp</a></span>\n";
          
    $HTMLOUT .= "<div <div class='headline' <span title='{$lang['news_title']}'><img align='center' src='pic/news.png' alt='' title='' /> {$lang['news_title']}</span>{$adminbutton}</div>";
    $HTMLOUT .= "<div class='headbody'>";
    $news_flag = 0;
    //value increased for tsting purpose.. normal value: 45 days
    $res = sql_query("SELECT * FROM news WHERE added + ( 3600 *24 *945 ) >
					".time()." ORDER BY added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
					
    if (mysql_num_rows($res) > 0)
    {
      require_once "include/bbcode_functions.php";

      $button = "";
      
      while($array = mysql_fetch_assoc($res))
      {
        if (get_user_class() >= UC_ADMINISTRATOR)
        {
          $button = "<div style='float:right;'><a href='admin.php?action=news&amp;mode=edit&amp;newsid={$array['id']}'><img src='{$TBDEV['pic_base_url']}button_edit2.gif' style='opacity:0.4;' onmouseover='this.style.opacity=1;' onmouseout='this.style.opacity=0.5;' border='0' alt='{$lang['news_edit']}' title='{$lang['news_edit']}' /></a>
          &nbsp;<a href='admin.php?action=news&amp;mode=delete&amp;newsid={$array['id']}'><img src='{$TBDEV['pic_base_url']}button_delete2.gif' style='opacity:0.4;' onmouseover='this.style.opacity=1;' onmouseout='this.style.opacity=0.5;' border='0' alt='{$lang['news_delete']}' title='{$lang['news_delete']}' /></a></div>";
        }
        
        $HTMLOUT .= "<div class='newshead'<span>";
        if ($news_flag < 2) {
            $HTMLOUT .="<a href=\"javascript: klappe_news('a".$array['id']."')\"><img border='0' src='pic/minus.png' id=\"pica".$array['id']."\" alt='Show/Hide'>" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['headline']}{$button}</a></div>";
            $HTMLOUT .="<div id=\"ka".$array['id']."\" style='display: block;margin-left:30px;margin-top:10px'> ".format_comment($array["body"],0)." </div>
             ";
            $news_flag = ($news_flag + 1);
        }
        else {
            $HTMLOUT .="<a href=\"javascript: klappe_news('a".$array['id']."')\">
            <img border='0' src='pic/plus.png' id=\"pica".$array['id']."\" alt='Show/Hide'>" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['headline']}{$button}</a></div>";
            $HTMLOUT .="<div id=\"ka".$array['id']."\" style='display: none;margin-left:30px;margin-top:10px'> ".format_comment($array["body"],0)." </div>
         ";
        }
        $HTMLOUT .= "<div style='margin-top:10px;padding:5px;'></div>\n";
      }
      $HTMLOUT .= "</div>";
    }
    $HTMLOUT .= "</div><br />\n";
    //end news
    
    // === shoutbox 09
       $adminbutton1 = '';
    if (get_user_class() >= UC_ADMINISTRATOR)
          $adminbutton1 = "&nbsp;<span style='float:right;'><a href='admin.php?action=shistory' style='color: #DBE3EA;'>Archive&nbsp&nbsp&nbsp</a></span>\n";
   $HTMLOUT .="<div class='headline' <span title='{$lang['index_shout']}'><img align='center' src='pic/shoutbox.png' alt='' title='' /> {$lang['index_shout']}- General Chit-chat </span>{$adminbutton1}</div>
   <div class='headbody'>";
   if ($CURUSER['show_shout'] === "yes") {
   $commandbutton = '';
   $refreshbutton = '';
   $smilebutton = '';
   $custombutton = '';
   $closebutton="<span style='float:right;'><a href='shoutbox.php?show_shout=1&show=no' title='{$lang['index_shoutbox_close']}'>[ {$lang['index_shoutbox_close']} ]</a></span>";
   //if(get_smile() != '0')
   //$custombutton .="<span style='float:right;'><a href=\"javascript:PopCustomSmiles('shbox','shbox_text')\">{$lang['index_shoutbox_csmilies']}</a></span>";
   if ($CURUSER['class'] >= UC_ADMINISTRATOR){
   $commandbutton = "<span style='float:right;'><a href=\"javascript:popUp('shoutbox_commands.php')\" title='{$lang['index_shoutbox_commands']}'>{$lang['index_shoutbox_commands']}</a></span>\n";}
   $refreshbutton = "<span style='float:right;'><a href='shoutbox.php' target='sbox' title='{$lang['index_shoutbox_refresh']}'>{$lang['index_shoutbox_refresh']}</a></span>\n";
   $smilebutton = "<span style='float:right;'><a href=\"javascript:PopMoreSmiles('shbox','shbox_text')\" title='{$lang['index_shoutbox_smilies']}'>{$lang['index_shoutbox_smilies']}</a></span>\n";
   $HTMLOUT .= "<form action='shoutbox.php' method='get' target='sbox' name='shbox' onsubmit='mysubmit()'>
   
   
   <iframe src='shoutbox.php' width='100%' height='200' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe>
   <br />
   <br />
   <script type=\"text/javascript\" src=\"scripts/shout.js\"></script> 	
   <div align='center'>
   <input type='text' class='sbox' maxlength='180' name='shbox_text' size='100' value='' onblur=\"if (this.value == '') this.value='Shout!';\" onfocus=\"if (this.value == 'Shout!') this.value='';\">
   <input class='button' type='submit' value='{$lang['index_shoutbox_send']}' />
   <input type='hidden' name='sent' value='yes' />
   <br />
	 <a href=\"javascript:SmileIT(':-)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/smile1.gif' alt='Smile' title='Smile' /></a> 
   <a href=\"javascript:SmileIT(':smile:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/smile2.gif' alt='Smiling' title='Smiling' /></a> 
   <a href=\"javascript:SmileIT(':-D','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/grin.gif' alt='Grin' title='Grin' /></a> 
   <a href=\"javascript:SmileIT(':lol:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/laugh.gif' alt='Laughing' title='Laughing' /></a> 
   <a href=\"javascript:SmileIT(':w00t:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='W00t' title='W00t' /></a>
   <a href=\"javascript:SmileIT(';-)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/wink.gif' alt='Wink' title='Wink' /></a> 
   <a href=\"javascript:SmileIT(':devil:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/devil.gif' alt='Devil' title='Devil' /></a> 
   <a href=\"javascript:SmileIT(':yawn:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/yawn.gif' alt='Yawn' title='Yawn' /></a> 
   <a href=\"javascript:SmileIT(':-/','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/confused.gif' alt='Confused' title='Confused' /></a> 
   <a href=\"javascript:SmileIT(':o)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/clown.gif' alt='Clown' title='Clown' /></a> 
   <a href=\"javascript:SmileIT(':innocent:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/innocent.gif' alt='Innocent' title='Innocent' /></a> 
   <a href=\"javascript:SmileIT(':whistle:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/whistle.gif' alt='Whistle' title='Whistle' /></a> 
   <a href=\"javascript:SmileIT(':unsure:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/unsure.gif' alt='Unsure' title='Unsure' /></a> 
   <a href=\"javascript:SmileIT(':blush:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blush.gif' alt='Blush' title='Blush' /></a> 
   <a href=\"javascript:SmileIT(':hmm:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/hmm.gif' alt='Hmm' title='Hmm' /></a> 
   <a href=\"javascript:SmileIT(':hmmm:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/hmmm.gif' alt='Hmmm' title='Hmmm' /></a> 
   <a href=\"javascript:SmileIT(':huh:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/huh.gif' alt='Huh' title='Huh' /></a> 
   <a href=\"javascript:SmileIT(':look:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/look.gif' alt='Look' title='Look' /></a> 
   <a href=\"javascript:SmileIT(':rolleyes:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/rolleyes.gif' alt='Roll Eyes' title='Roll Eyes' /></a> 
   <a href=\"javascript:SmileIT(':kiss:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/kiss.gif' alt='Kiss' title='Kiss' /></a> 
   <a href=\"javascript:SmileIT(':blink:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blink.gif' alt='Blink' title='Blink' /></a>
	 
	 <span style='font-size:8pt;font-family:trebuchet MS;'>{$refreshbutton} {$commandbutton} {$smilebutton} {$custombutton} {$closebutton}</span></div>
	 </div>
   </form><br />\n";
   }
   if ($CURUSER['show_shout'] === "no") {
   $HTMLOUT .="[ <a href='{$TBDEV['baseurl']}/shoutbox.php?show_shout=1&show=yes' title='{$lang['index_shoutbox_open']}'>{$lang['index_shoutbox_open']}</a> ]</div></div><br />\n";
   }
   //==end 09 shoutbox
   
   //==09 users on index (colaspible block, cookie based)
    $active3 ="";
    $file = "./cache/active.txt";
    $expire = 30; // 30 seconds
    if (file_exists($file) && filemtime($file) > (time() - $expire)) {
        $active3 = unserialize(file_get_contents($file));
    } else {
        $dt = sqlesc(time() - 180);
        $active1 = mysql_query("SELECT id, username, class, warned, donor FROM users WHERE last_access >= $dt ORDER BY class DESC") or sqlerr(__FILE__, __LINE__);
        while ($active2 = mysql_fetch_assoc($active1)) {
        $active3[] = $active2;
    }
    $OUTPUT = serialize($active3);
    $fp = fopen($file, "w");
    fputs($fp, $OUTPUT);
    fclose($fp);
    } // end else
    $activeusers = "";
    if (is_array($active3))
        foreach ($active3 as $arr) {
            if ($activeusers) $activeusers .= ",\n";
            $activeusers .= "<span style=\"white-space: nowrap;\">"; 
            $arr["username"] = "<font color='#" . get_user_class_color($arr['class']) . "'> " . htmlspecialchars($arr['username']) . "</font>";
            $donator = $arr["donor"] === "yes";
            $warned = $arr["warned"] === "yes";

            if ($CURUSER)
                $activeusers .= "<a href='{$TBDEV['baseurl']}/userdetails.php?id={$arr["id"]}'><b>{$arr["username"]}</b></a>";
            else
                $activeusers .= "<b>{$arr["username"]}</b>";
            if ($donator)
                $activeusers .= "<img src='{$TBDEV['pic_base_url']}star.gif' alt='Donated' title='Donor' />";
            if ($warned)
                $activeusers .= "<img src='{$TBDEV['pic_base_url']}warned.gif' alt='Warned' title='Warned' />";
            $activeusers .= "</span>";
        }

        $fh = fopen("./cache/active.txt", "r"); 
        $string = file_get_contents("cache/active.txt"); 
        $count = preg_match_all( '/username/', $string, $dummy );
        if (!$activeusers)
            $activeusers = "{$lang['index_noactive']}";
    
    $HTMLOUT .= begin_block("active_u",$caption_t="{$lang['index_active']} ($count)", $per=98, $tdcls="colhead5", $img="<img src='pic/active_users.png' style=' height:28px;' alt='' title='' />", $title="{$lang['index_active']}");
    $HTMLOUT .="<div align='left'>{$activeusers}</div>
    <br /><div align='center'><div id='activeindex2' align='center'><font color=#990000>Owner</font> | <font color=#4080B0>SysOp</font> | <font color=#9E159E>Administrator</font> | <font color=#FE2E2E>Moderator</font> | <font color=#F5886D>Forum Moderator</font> | <font color=#009F00>VIP</font> | <font color=#13D1D1>Uploader</font> | <font color=#F7A919>Power User</font> | <font color=#9C2FE0>User</font> | <font color=#999999>Banned</font> <img src='pic/disabled.gif' alt='' title''></div></div>";
    $HTMLOUT .= end_block();
    $HTMLOUT .="<br />";
//end active users in index

    //latest forum posts [set limit from config] (colaspible block, cookie based)s
        $HTMLOUT .= begin_block("latest_f_p",$caption_t="{$lang['latestposts_title']}", $per=98, $tdcls="colhead5", $img="", $title='Latest Forum Posts'); 
        $page = 1; 
        $num = 0; 
 
    /// latest posts query 
    $topicres = sql_query("SELECT t.id, t.userid, t.anonymous AS top_anon, t.subject, t.locked, t.forumid, t.lastpost, t.sticky, t.views, t.forumid, f.minclassread, f.name ". 
    ", (SELECT COUNT(id) FROM posts WHERE topicid=t.id) AS p_count ". 
    ", p.userid AS puserid, p.anonymous AS pos_anon, p.added ". 
    ", u.id AS uid, u.username ". 
    ", u2.username AS u2_username ". 
    "FROM topics AS t ". 
    "LEFT JOIN forums AS f ON f.id = t.forumid ". 
    "LEFT JOIN posts AS p ON p.id=(SELECT MAX(id) FROM posts WHERE topicid = t.id) ". 
    "LEFT JOIN users AS u ON u.id=p.userid ". 
    "LEFT JOIN users AS u2 ON u2.id=t.userid ". 
    "WHERE f.minclassread <= ".$CURUSER['class']." ". 
    "ORDER BY t.lastpost DESC LIMIT {$TBDEV['latest_posts_limit']}") or sqlerr(__FILE__, __LINE__); 
    if (mysql_num_rows($topicres) > 0) { 
        $HTMLOUT .= "<table width='100%' cellspacing='0' cellpadding='5'><tr> 
        <td class=\"colhead3\"style='text-align:left;'><b>{$lang['latestposts_topic_title']}</b></td> 
        <td class=\"colhead3\"style='text-align:center;'><b>{$lang['latestposts_replies']}</b></td> 
        <td class=\"colhead3\"style='text-align:center;'><b>{$lang['latestposts_views']}</b></td> 
        <td class=\"colhead3\" style='text-align:center;'><b>{$lang['latestposts_last_post']}</b></td></tr>"; 
        while ($topicarr = mysql_fetch_assoc($topicres)) { 
 
        $topicid = 0+$topicarr['id']; 
        $topic_userid = 0+$topicarr['userid']; 
        $perpage = $CURUSER['postsperpage'];; 
 
        if (!$perpage) 
        $perpage = 24; 
        $posts = 0+$topicarr['p_count']; 
        $replies = max(0, $posts - 1); 
        $first = ($page * $perpage) - $perpage + 1; 
        $last = $first + $perpage - 1; 
 
        if ($last > $num) 
        $last = $num; 
        $pages = ceil($posts / $perpage); 
        $menu = ''; 
        for ($i = 1; $i <= $pages; $i++) { 
        if($i == 1 && $i != $pages){ 
        $menu .= "[ "; 
        } 
        if ($pages > 1){ 
        $menu .= "<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i'>$i</a>\n"; 
        } 
        if ($i < $pages) { 
        $menu .= "|\n"; 
        } 
        if($i == $pages && $i > 1){ 
        $menu .= "]"; 
        } 
        } 
 
        $added = get_date($topicarr['added'],'',0,1); 
        if ($topicarr['pos_anon'] == 'yes') { 
        if ($CURUSER['class'] < UC_MODERATOR && $CURUSER['id'] != $topicarr['puserid']) 
        $username = "<i>Anonymous</i>"; 
        else 
        $username = "<i>Anonymous</i><br />(".(!empty($topicarr['username']) ? "<a href='userdetails.php?id=".(int)$topicarr['puserid']."'><b>".htmlspecialchars($topicarr['username'])."</b></a>" : "<i>Unknown[$topic_userid]</i>").")"; 
        } else { 
        $username = (!empty($topicarr['username']) ? "<a href='userdetails.php?id=".(int)$topicarr['puserid']."'><b>".htmlspecialchars($topicarr['username'])."</b></a>" : ($topic_userid == '0' ? "<i>System</i>" : "<i>Unknown[$topic_userid]</i>")); 
        } 
        if ($topicarr['top_anon'] == 'yes') { 
        if ($CURUSER['class'] < UC_MODERATOR && $CURUSER['id'] != $topic_userid) 
        $author = "<i>Anonymous</i>"; 
        else 
    $author = "<i>Anonymous</i>(".(!empty($topicarr['u2_username']) ? "<a href='userdetails.php?id=$topic_userid'><b>".htmlspecialchars($topicarr['u2_username'])."</b></a>" : "<i>Unknown[$topic_userid]</i>").")"; 
        } else { 
        $author = (!empty($topicarr['u2_username']) ? "<a href='userdetails.php?id=$topic_userid'><b>".htmlspecialchars($topicarr['u2_username'])."</b></a>" : ($topic_userid == '0' ? "<i>System</i>" : "<i>Unknown[$topic_userid]</i>")); 
        } 
        $staffimg = ($topicarr['minclassread'] >= UC_MODERATOR ? "<img src='".$TBDEV['pic_base_url']."staff.gif' border='0' alt='Staff forum' title='Staff Forum' />" : ''); 
        $stickyimg = ($topicarr['sticky'] == 'yes' ? "<img src='".$TBDEV['pic_base_url']."sticky.gif' border='0' alt='Sticky' title='Sticky Topic' />&nbsp;&nbsp;" : ''); 
        $lockedimg = ($topicarr['locked'] == 'yes' ? "<img src='".$TBDEV['pic_base_url']."locked.gif' border='0' alt='Locked' title='Locked Topic' />&nbsp;" : ''); 
        $subject = $lockedimg.$stickyimg."<a href='forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=last#".(int)$topicarr['lastpost']."'><b>" . htmlspecialchars($topicarr['subject']) . "</b></a>&nbsp;&nbsp;$staffimg&nbsp;&nbsp;$menu<br /><font class='small'>in <a href='forums.php?action=viewforum&amp;forumid=".(int)$topicarr['forumid']."'>".htmlspecialchars($topicarr['name'])."</a>&nbsp;by&nbsp;$author&nbsp;&nbsp;($added)</font>"; 
 
        $HTMLOUT .="<tr><td style='text-align:left;'>{$subject}</td><td align='center'>{$replies}</td><td align='center'>".number_format($topicarr['views'])."</td><td align='center'>{$username}</td></tr>"; 
    } 
    $HTMLOUT .= "</table>";
    $HTMLOUT .= end_block()."<br />"; 
    } else { 
    //if there are no posts... 
        $HTMLOUT .= "{$lang['latestposts_no_posts']}";
        $HTMLOUT .= end_block()."<br />"; 
    } 
    //end latest forum posts

   //Advanced Stats start (colaspible block, cookie based)
     $HTMLOUT .= begin_block("{$lang['index_stats_title']}",$caption_t="{$lang['index_stats_title']}", $per=98, $tdcls="colhead5", $img="<img src='pic/status.png' style=' height:28px;' alt='' title='' />", $title= 'Site stats');
     $HTMLOUT .="<table class='stats' border='1' cellspacing='0' cellpadding='5' align='center'>
   <tr>
	 <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_regged']}'>{$lang['index_stats_regged']} <img src='{$TBDEV['pic_base_url']}users.png' alt=''></td></td><td align='right'>{$registered}</td>
     <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_male']}'>{$lang['index_stats_male']} <img src='{$TBDEV['pic_base_url']}male.gif' alt=''></td><td align='right'>{$malec}</td>
     <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_peers']}'>{$lang['index_stats_peers']} <img src='{$TBDEV['pic_base_url']}peers.gif' alt=''></td><td align='right'>{$peers}</td>
   </tr>
   <tr>
	 <td class='rowhead' style='font-family:Sans-Serif' title='{$lang['index_stats_uncon']}'>{$lang['index_stats_uncon']} <img src='{$TBDEV['pic_base_url']}confirm.png' alt=''></td><td align='right'>{$unverified}</td>
	 <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_female']}'>{$lang['index_stats_female']} <img src='{$TBDEV['pic_base_url']}female.gif' alt=''></td><td align='right'>{$femalec}</td>
     <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_unconpeer']}'>{$lang['index_stats_unconpeer']} <img src='{$TBDEV['pic_base_url']}unc.gif' alt=''></td><td align='right'>{$unconnectables}</td>
   </tr>
   <tr>
	 <td colspan='4'> </td>
   </tr>
   <tr>
    <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_donor']}'>{$lang['index_stats_donor']} <img src='{$TBDEV['pic_base_url']}star.gif' alt='donor'></td><td align='right'>{$donors}</td> 
    <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_topics']}'>{$lang['index_stats_topics']} <img src='{$TBDEV['pic_base_url']}tpics.gif' alt=''></td><td align='right'>{$forumtopics}</td>
    <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_seeders']}'>{$lang['index_stats_seeders']} <img src='{$TBDEV['pic_base_url']}arrowup.png' alt='seeder'></td><td align='right'>{$seeders}</td> 
   </tr>
   <tr>
    
	 <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_tusers']}'>{$lang['index_stats_tusers']} <img src='{$TBDEV['pic_base_url']}' alt=''></td><td align='right'>{$TBDEV['maxusers']}</td>
     <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_posts']}'>{$lang['index_stats_posts']} <img src='{$TBDEV['pic_base_url']}ts_blog.png' alt=''></td></td><td align='right'>{$forumposts}</td>
     <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_leechers']}'>{$lang['index_stats_leechers']} <img src='{$TBDEV['pic_base_url']}arrowdown.png' alt='leecher'></td><td align='right'>{$leechers}</td>
   </tr>
   <tr>
   <td colspan='4'> </td>
   </tr>
   <tr>
     <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_warned']}'>{$lang['index_stats_warned']} <img src='{$TBDEV['pic_base_url']}warned.gif' alt=''></td><td align='right'>{$warnedu}</td> 
	 <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_torrents']}'>{$lang['index_stats_torrents']} <img src='{$TBDEV['pic_base_url']}torrents.png' alt=''></td><td align='right'>{$torrents}</td>
     <td class='rowhead' align='right' style='font-family:Trebuchet MS' title='{$lang['index_stats_unconratio']}'><b>{$lang['index_stats_unconratio']}</b></td><td align='right'><b>".round($ratiounconn * 100)."</b></td>
   </tr>
   <tr>
    <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_disabled']}'>{$lang['index_stats_disabled']} <img src='{$TBDEV['pic_base_url']}disabled.gif' alt=''></td><td align='right'>{$disabled}</td> 
    <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_newtor']}'>{$lang['index_stats_newtor']} <img src='{$TBDEV['pic_base_url']}tag_new.png' alt=''></td><td align='right'>{$torrentstoday}</td>
    <td class='rowhead' style='font-family:Trebuchet MS' title='{$lang['index_stats_slratio']}'>{$lang['index_stats_slratio']}</td><td align='right'>{$ratio}</td>
   </tr>
   </table>
   <div  style='text-align:center;width:80%;border:0px solid black;padding:5px;'><font class='small'>Welcome to our newest member, <b>$new_user</b>!</font></div>
     </div>";
   $HTMLOUT .= end_block();
   $HTMLOUT .="<br />";
   //Advanced stats end 
   
   //modified disclainer (scrolling supported only in Mozilla)
   $HTMLOUT .="<div class='headline' <span title='Disclaimer'><img src='pic/dis.png' alt='' title='' />Disclaimer</span></div>
     <div class='headbody'>";
   //$browser = $_SERVER['HTTP_USER_AGENT'];
     //$HTMLOUT = $browser;
   //if(preg_match("/Mozilla/",$browser)){//browser is Mozilla
   if((@ereg("Nav", getenv("HTTP_USER_AGENT"))) || (@ereg("Gold", getenv("HTTP_USER_AGENT"))) || 
(@ereg("@X11", getenv("HTTP_USER_AGENT"))) || (@ereg("Mozilla", getenv("HTTP_USER_AGENT"))) || 
(@ereg("@Netscape", getenv("HTTP_USER_AGENT"))) ) 
$browser = "mozilla";
else $browser = "oth"; 
    if ($browser == "mozilla"){
   $HTMLOUT .="<marquee onmouseover=this.stop() onmouseout=this.start() scrollAmount=0.9 direction=up width='100%' height='55'>
     <p><font class='small'> None of the files shown here are actually hosted on this server. The links are provided solely by this site's users.
    The administrator of <b>".$TBDEV['site_name']. "</b> cannot be held responsible for what its users post, or any other actions of its users.
    You may not use this site to distribute or download any material when you do not have the legal rights to do so.
    It is your own responsibility to adhere to these terms.</font></p></marquee>"; 
   }
   else{
   $HTMLOUT .="<font class='small'> None of the files shown here are actually hosted on this server. The links are provided solely by this site's users.
    The administrator of <b>".$TBDEV['site_name']. "</b> cannot be held responsible for what its users post, or any other actions of its users.
    You may not use this site to distribute or download any material when you do not have the legal rights to do so.
    It is your own responsibility to adhere to these terms.</font>";
   }
    $HTMLOUT .="</div>";
   $HTMLOUT .="</div><br />";  
    
    
    //testing area
    $HTMLOUT .= begin_block("test",$caption_t='test me', $per=98, $tdcls="colhead5", $img="<img src='pic/dis.png' style=' height:28px;' alt='' title='' />", $title='Fuck u');
    $HTMLOUT .="He he eh!";
    $HTMLOUT .= end_block();
    //end testing area
/*
<h2>Server load</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='1'0><tr><td align=center>
<table class=main border='0' width=402><tr><td style='padding: 0px; background-image: url("<?php echo $TBDEV['pic_base_url']?>loadbarbg.gif"); background-repeat: repeat-x'>
<?php $percent = min(100, round(exec('ps ax | grep -c apache') / 256 * 100));
if ($percent <= 70) $pic = "loadbargreen.gif";
elseif ($percent <= 90) $pic = "loadbaryellow.gif";
else $pic = "loadbarred.gif";
$width = $percent * 4;
print("<img height='1'5 width=$width src=\"{$TBDEV['pic_base_url']}{$pic}\" alt='$percent%'>"); ?>
</td></tr></table>
</td></tr></table>
*/

/*
//Tracker load function ripped from TemplateShare Free Edition
//Have to implement it in 09 code..
?>
<h2>Tracker Load</h2>
<table width=100% border=1 cellspacing=5 cellpadding=10><tr><td align=center>
<?

function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
    }
$time_start = getmicrotime();


$time = round(getmicrotime() - $time_start,4);
$percent = $time * 60;


$time = round(getmicrotime() - $time_start,4);
$percent = $time * 60;
echo "<div align=\"center\">Our Tracker Load: ($percent %)</div><table class=blocklist align=center border=0 width=400><tr><td style='padding: 0px; background-image: url(pic/loadbarbg.gif); background-repeat: repeat-x'>";

//TRACKER LOAD
if ($percent <= 70) $pic_base_url = "pic/loadbargreen.gif";
     elseif ($percent <= 90) $pic_base_url = "pic/loadbaryellow.gif";
      else $pic_base_url = "pic/loadbarred.gif";
           $width = $percent * 4;
echo "<img height=15 width=$width src=\"$pic_base_url\" alt='$percent%'></td></tr></table><br>";
echo "<center>" . trim(exec('uptime')) . "</center><br>";

if (isset($load))
print("<tr><td class=blocklist>10min load average (%)</td><td align=right>$load</td></tr>\n");
print("<br>");

$time = round(getmicrotime() - $time_start,4);
$percent = $time * 60;
echo "<div align=\"center\">Global Server Load (All websites on current host servers): ($percent %)</div><table class=main align=center border=0 width=400><tr><td style='padding: 0px; background-image: url(pic/loadbarbg.gif); background-repeat: repeat-x'>";

if ($percent <= 70) $pic_base_url = "pic/loadbargreen.gif";
  elseif ($percent <= 90) $pic_base_url = "pic/loadbaryellow.gif";
   else $pic_base_url = "pic/loadbarred.gif";
        $width = $percent * 4;
echo "<img height=15 width=$width src=\"$pic_base_url\" alt='$percent%'></td></tr></table></table>";
//End tracker Load ripped from TemplateShare Free Edition..
*/
    //== Server Load linux

    //$HTMLOUT .= sprintf("<p><font class='small'>{$lang['foot_disclaimer']}</font></p>", $TBDEV['site_name']);
    
    $HTMLOUT .= "";

///////////////////////////// FINAL OUTPUT //////////////////////

    where ("{$lang['index_view_index']}",$CURUSER["id"]);
    print stdhead('Home') . $HTMLOUT . stdfoot();
?>