<?php
/**
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.1
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
+------------------------------------------------
**/

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";
require_once "include/page_verify.php";
require_once "include/bbcode_functions.php";
require_once "cache/fls_langs.php"; 

dbconn(false);

loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('userdetails') );
$newpage = new page_verify();  
$newpage->create('modtask');

function bark($msg)
{
  stderr("{$lang['userdetails_error']}", $msg);
}

function maketable($res)
    {
      global $TBDEV, $lang;
      
      $htmlout = '';
      
      $htmlout .= "<table class='main' border='1' cellspacing='0' cellpadding='5'>" .
        "<tr><td class='colhead' align='center'>{$lang['userdetails_type']}</td><td class='colhead'>{$lang['userdetails_name']}</td><td class='colhead' align='center'>{$lang['userdetails_ttl']}</td><td class='colhead' align='center'>{$lang['userdetails_size']}</td><td class='colhead' align='right'>{$lang['userdetails_se']}</td><td class='colhead' align='right'>{$lang['userdetails_le']}</td><td class='colhead' align='center'>{$lang['userdetails_upl']}</td>\n" .
        "<td class='colhead' align='center'>{$lang['userdetails_downl']}</td><td class='colhead' align='center'>{$lang['userdetails_ratio']}</td></tr>\n";
      foreach ($res as $arr)
      {
        if ($arr["downloaded"] > 0)
        {
          $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
          $ratio = "<font color='" . get_ratio_color($ratio) . "'>$ratio</font>";
        }
        else
          if ($arr["uploaded"] > 0)
            $ratio = "{$lang['userdetails_inf']}";
          else
            $ratio = "---";
      $catimage = "{$TBDEV['pic_base_url']}caticons/{$arr['image']}";
      $catname = htmlspecialchars($arr["catname"]);
      $catimage = "<img src=\"".htmlspecialchars($catimage) ."\" title=\"$catname\" alt=\"$catname\" width='42' height='42' />";
      $ttl = (28*24) - floor((time() - $arr["added"]) / 3600);
      if ($ttl == 1) $ttl .= "<br />{$lang['userdetails_hour']}"; else $ttl .= "<br />{$lang['userdetails_hours']}";
      $size = str_replace(" ", "<br />", mksize($arr["size"]));
      $uploaded = str_replace(" ", "<br />", mksize($arr["uploaded"]));
      $downloaded = str_replace(" ", "<br />", mksize($arr["downloaded"]));
      $seeders = number_format($arr["seeders"]);
      $leechers = number_format($arr["leechers"]);
        $htmlout .= "<tr><td style='padding: 0px'>$catimage</td>\n" .
        "<td><a href='details.php?id=$arr[torrent]&amp;hit=1'><b>" . htmlspecialchars($arr["torrentname"]) .
        "</b></a></td><td align='center'>$ttl</td><td align='center'>$size</td><td align='right'>$seeders</td><td align='right'>$leechers</td><td align='center'>$uploaded</td>\n" .
        "<td align='center'>$downloaded</td><td align='center'>$ratio</td></tr>\n";
      }
      $htmlout .= "</table>\n";
      return $htmlout;
}
function snatchtable($res) {
global $TBDEV, $lang;
$htmlout = '';
 $htmlout = "<table class='main' border='1' cellspacing='0' cellpadding='5'>
 <tr>
 <td class='colhead'>Category</td>
 <td class='colhead'>Torrent</td>
 <td class='colhead'>Up.</td>
 <td class='colhead'>Rate</td>
 <td class='colhead'>Downl.</td>
 <td class='colhead'>Rate</td>
 <td class='colhead'>Ratio</td>
 <td class='colhead'>Activity</td>
 <td class='colhead'>Finished</td>
 </tr>";

 while ($arr = mysql_fetch_assoc($res)) {

 $upspeed = ($arr["upspeed"] > 0 ? mksize($arr["upspeed"]) : ($arr["seedtime"] > 0 ? mksize($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) : mksize(0)));
 $downspeed = ($arr["downspeed"] > 0 ? mksize($arr["downspeed"]) : ($arr["leechtime"] > 0 ? mksize($arr["downloaded"] / $arr["leechtime"]) : mksize(0)));
 $ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"], 3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));

 $htmlout .= "<tr>
 <td style='padding: 0px'><img src='pic/".htmlspecialchars($arr["catimg"])."' alt='".htmlspecialchars($arr["catname"])."' width='42' height='42' /></td>
 <td><a href='details.php?id=$arr[torrentid]'><b>".(strlen($arr["name"]) > 50 ? substr($arr["name"], 0, 50 - 3)."..." : $arr["name"])."</b></a></td>
 <td>".mksize($arr["uploaded"])."</td>
 <td>$upspeed/s</td>
 <td>".mksize($arr["downloaded"])."</td>
 <td>$downspeed/s</td>
 <td>$ratio</td>
 <td>".mkprettytime($arr["seedtime"] + $arr["leechtime"])."</td>
 <td>".($arr["complete_date"] <> "0" ? "<font color='green'><b>Yes</b></font>" : "<font color='red'><b>No</b></font>")."</td>
 </tr>\n";
 }
 $htmlout .= "</table>\n";

 return $htmlout;
}

    $id = 0 + $_GET["id"];

    if (!is_valid_id($id))
      bark("{$lang['userdetails_bad_id']}");
    //=== delete H&R
	if(isset($_GET['delete_hit_and_run']) && $CURUSER['class'] >= UC_MODERATOR)
	{
		$delete_me = isset($_GET['delete_hit_and_run']) ? intval($_GET['delete_hit_and_run']) : 0;
			if (!is_valid_id($delete_me))
				stderr('Error!','Bad ID');

	@sql_query('UPDATE snatched SET hit_and_run = \'0\', mark_of_cain = \'no\' WHERE id = '.$delete_me) or sqlerr(__FILE__,__LINE__);
		if (@mysql_affected_rows() === 0)
		{
		stderr('Error!','H&R not deleted!');
		}

		header('Location: ?id='.$id.'&finished=1');
	die();
	}
    
    
    $r = @sql_query("SELECT * FROM users WHERE id=$id") or sqlerr();
    $user = mysql_fetch_assoc($r) or bark("{$lang['userdetails_no_user']}");
    //start profile hit function
    # *1 = comment this out if you use the cleanup code 
    /*
    $res = sql_query("SELECT COUNT(*) FROM userhits WHERE hitid = $id") or sqlerr(); // *1 
    $row = mysql_fetch_row($res); // *1 
    $userhits = $row[0]; // *1 
    if (!$_GET["hit"] && $CURUSER["id"] <> $user["id"]) { 
            $hitnumber = $userhits + 1; // *1 
            sql_query("INSERT INTO userhits (userid, hitid, number, added) VALUES($CURUSER[id], $id, $hitnumber, '".time()."')") or sqlerr(); 
            header("Location: $BASEURL$_SERVER[REQUEST_URI]&hit=1"); 
    } 
    */ 
    # *2 = comment this out if you do NOT use the cleanup code 
    if (!isset($_GET["hit"]) && $CURUSER["id"] <> $user["id"]) {
        $hitnumber = $user["hits"] + 1; // *2
        sql_query("UPDATE users SET hits = hits + 1 WHERE id = $id") or sqlerr(); // *2
        sql_query("INSERT INTO userhits (userid, hitid, number, added) VALUES($CURUSER[id], $id, $hitnumber, '".time()."')") or sqlerr();
        header('Location: ?id='.$id.'&hit=1');
    } 
    /* //Alterlet of #2
    if (!isset($_GET["hit"]) && $CURUSER["id"] <> $user["id"]) {
        $hitnumber = $user["hits"] + 1; // *2
        sql_query("UPDATE users SET hits = hits + 1 WHERE id = $id") or sqlerr(); // *2
        sql_query("INSERT INTO userhits (userid, hitid, number, added) VALUES($CURUSER[id], $id, $hitnumber, '".time()."')") or sqlerr();
        header('Location: ?id='.$id.'&hit=1');
    }
    */
    /* 
    # *3 = comment this out if you do NOT want hits to be added only once every hour 
    if (!$_GET["hit"] && $CURUSER["id"] <> $user["id"]) { 
        $res = sql_query("SELECT added FROM userhits WHERE userid = $CURUSER[id] AND hitid = $id LIMIT 1") or sqlerr(); // *3 
        $row = mysql_fetch_row($res); // *3 
        if ($row[0] > get_date(time() - 3600)) { // *3 
            header("Location: $BASEURL$_SERVER[REQUEST_URI]&hit=1"); // *3 
        } else { // *3 
            sql_query("INSERT INTO userhits (userid, hitid, number, added) VALUES($CURUSER[id], $id, $hitnumber, '".time()."')") or sqlerr(); 
            header("Location: $BASEURL$_SERVER[REQUEST_URI]&hit=1"); 
        } // *3 
    } 
    */
    //End profile hit function.
    if ($user["status"] == "pending") die;
    $r = sql_query("SELECT t.id, t.name, t.seeders, t.leechers, c.name AS cname, c.image FROM torrents t LEFT JOIN categories c ON t.category = c.id WHERE t.owner = $id ORDER BY t.name") or sqlerr(__FILE__,__LINE__);
    if (mysql_num_rows($r) > 0)
    {
      $torrents = "<table class='main' border='1' cellspacing='0' cellpadding='5'>\n" .
        "<tr><td class='colhead'>{$lang['userdetails_type']}</td><td class='colhead'>{$lang['userdetails_name']}</td><td class='colhead'>{$lang['userdetails_seeders']}</td><td class='colhead'>{$lang['userdetails_leechers']}</td></tr>\n";
      while ($a = mysql_fetch_assoc($r))
      {
        //$r2 = sql_query("SELECT name, image FROM categories WHERE id=$a[category]") or sqlerr(__FILE__, __LINE__);
        //$a2 = mysql_fetch_assoc($r2);
        $cat = "<img src=\"". htmlspecialchars("{$TBDEV['pic_base_url']}caticons/{$a['image']}") ."\" title=\"{$a['cname']}\" alt=\"{$a['cname']}\" />";
          $torrents .= "<tr><td style='padding: 0px'>$cat</td><td><a href='details.php?id=" . $a['id'] . "&amp;hit=1'><b>" . htmlspecialchars($a["name"]) . "</b></a></td>" .
            "<td align='right'>{$a['seeders']}</td><td align='right'>{$a['leechers']}</td></tr>\n";
      }
      $torrents .= "</table>";
    }

    if ($user['ip'] && ($CURUSER['class'] >= UC_MODERATOR || $user['id'] == $CURUSER['id']))
    {
        $dom = @gethostbyaddr($user['ip']);
        $addr = ($dom == $user['ip'] || @gethostbyname($dom) != $user['ip']) ? $user['ip'] : $user['ip'].' ('.$dom.')';
    }


    if ($user['added'] == 0)
      $joindate = "{$lang['userdetails_na']}";
    else
      $joindate = get_date( $user['added'],'');
    $lastseen = $user["last_access"];
    if ($lastseen == 0)
      $lastseen = "{$lang['userdetails_never']}";
    else
    {
      $lastseen = get_date( $user['last_access'],'',0,1);
    }


      $res = sql_query("SELECT COUNT(*) FROM comments WHERE user=" . $user['id']) or sqlerr();
      $arr3 = mysql_fetch_row($res);
      $torrentcomments = $arr3[0];
      $res = sql_query("SELECT COUNT(*) FROM posts WHERE userid=" . $user['id']) or sqlerr();
      $arr3 = mysql_fetch_row($res);
      $forumposts = $arr3[0];

    //if ($user['donated'] > 0)
    //  $don = "<img src='{$TBDEV['pic_base_url']}starbig.gif' alt='' />";
    $country = '';
    $res = sql_query("SELECT name,flagpic FROM countries WHERE id=".$user['country']." LIMIT 1") or sqlerr();
    if (mysql_num_rows($res) == 1)
    {
      $arr = mysql_fetch_assoc($res);
      $country = "<td class='embedded'><img src=\"{$TBDEV['pic_base_url']}flag/{$arr['flagpic']}\" alt=\"". htmlspecialchars($arr['name']) ."\" style='margin-left: 8pt' /></td>";
    }

    //if ($user["donor"] == "yes") $donor = "<td class='embedded'><img src='{$TBDEV['pic_base_url']}starbig.gif' alt='Donor' style='margin-left: 4pt' /></td>";
    //if ($user["warned"] == "yes") $warned = "<td class='embedded'><img src=\"{$TBDEV['pic_base_url']}warnedbig.gif\" alt='Warned' style='margin-left: 4pt' /></td>";

    $res = sql_query("SELECT p.torrent, p.uploaded, p.downloaded, p.seeder, t.added, t.name as torrentname, t.size, t.category, t.seeders, t.leechers, c.name as catname, c.image FROM peers p LEFT JOIN torrents t ON p.torrent = t.id LEFT JOIN categories c ON t.category = c.id WHERE p.userid=$id") or sqlerr();

    while ($arr = mysql_fetch_assoc($res))
    {
        if ($arr['seeder'] == 'yes')
            $seeding[] = $arr;
        else
            $leeching[] = $arr;
    }

    
    $HTMLOUT = '';
    
    $enabled = $user["enabled"] == 'yes';
    $disablecom=$user["disablecom"] == 'yes';
    $HTMLOUT .= "<p></p><table class='main' border='0' cellspacing='0' cellpadding='0'>".
    "<tr><td class='embedded'><h1 style='margin:0px'>{$user['username']}" . get_user_icons($user, true) . "</h1></td>$country</tr></table><p></p>\n";
    if ($user["parked"] == 'yes') 
        $HTMLOUT .= "<p><blink><b>{$lang['userdetails_parked']}</b></blink></p>\n";
    if (!$enabled)
      $HTMLOUT .= "<p><b>{$lang['userdetails_disabled']}</b></p>\n";
    elseif ($CURUSER["id"] <> $user["id"])
    {
      $r = sql_query("SELECT id FROM friends WHERE userid=$CURUSER[id] AND friendid=$id") or sqlerr(__FILE__, __LINE__);
      $friend = mysql_num_rows($r);
      $r = sql_query("SELECT id FROM blocks WHERE userid=$CURUSER[id] AND blockid=$id") or sqlerr(__FILE__, __LINE__);
      $block = mysql_num_rows($r);
    
      if ($user["class"] < UC_MODERATOR || $CURUSER["class"] >= UC_MODERATOR)
      if ($friend)
        $HTMLOUT .= "<p>(<a href='friends.php?action=delete&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_remove_friends']}</a>)</p>\n";
      elseif($block)
        $HTMLOUT .= "<p>(<a href='friends.php?action=delete&amp;type=block&amp;targetid=$id'>{$lang['userdetails_remove_blocks']}</a>)</p>\n";
      else
      {
        $HTMLOUT .= "<p>(<a href='friends.php?action=add&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_add_friends']}</a>)";
        $HTMLOUT .= " - (<a href='friends.php?action=add&amp;type=block&amp;targetid=$id'>{$lang['userdetails_add_blocks']}</a>)</p>\n";
      }
    }
    //== 09 Shitlist by Sir_Snuggles
    if (($CURUSER['class'] >= UC_MODERATOR) && ($CURUSER["id"] <> $user["id"])){
    $shitty = '';
    $check_if_theyre_shitty = sql_query("SELECT suspect FROM shit_list WHERE userid=$CURUSER[id] AND suspect=".$id) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($check_if_theyre_shitty) !== 0){
    $HTMLOUT .="<br /><b>This member is on your shit list click <a class='altlink' href='shit_list.php'>HERE</a> to see your shit list</b>";
    $shitty = "<img src='pic/smilies/shit.gif' alt='Shit' title='Shit' />";
    }
    else		
    //$HTMLOUT .="<br />".$shitty."&nbsp;<a class='altlink' href='shit_list.php?action=new&amp;shit_list_id=".$id."&amp;return_to=userdetails.php?id=".$id."'><b>Add member to your shit list</b></a>&nbsp;".$shitty."";
    $HTMLOUT .= "<p>(<a href='shit_list.php?action=new&amp;shit_list_id=".$id."&amp;return_to=userdetails.php?id=".$id."'>add to your shit list</a>)</p>\n";
    }
    //end shitlist
    if ($CURUSER['class'] >= UC_MODERATOR)
	 $HTMLOUT .= "(<a href='{$TBDEV['baseurl']}/userimages.php?user=".$user['username']."'>{$lang['userdetails_viewimages']}) - </a>";
    if ($CURUSER['id'] != $user['id'])
        $HTMLOUT .="(<a href='{$TBDEV['baseurl']}/sharemarks.php?id=$id'>View sharemarks</a>)\n";
    $HTMLOUT .="<p></p>";
        //Donor countdown 
    if ($user["donor"] && $CURUSER["id"] == $user["id"] || $CURUSER['class'] == UC_STAFF_LEADER) { 
        $donoruntil = $user['donoruntil']; 
        if ($donoruntil == '0') 
            $HTMLOUT.= ""; 
        else { 
            $HTMLOUT.= "<b>Donated Status Until - ".get_date($user['donoruntil'], 'DATE'). ""; 
            $HTMLOUT.=" [ " . mkprettytime($donoruntil - TIME_NOW) . " ] To go...</b><font size=\"-2\"> To re-new your donation click <a class='altlink' href='{$TBDEV['baseurl']}/donate.php'>Here</a>.</font><br /><br />\n"; 
        } 
    }
   //End donor countdown
    $HTMLOUT .="<p></p><br />";
    $HTMLOUT .= begin_main_frame();
    $HTMLOUT .= "<script type='text/javascript' src='./scripts/todger.js'></script>";
    $weeks = round(((time() - $user['added'])/86400)/7); 
    $HTMLOUT .= "<table width='100%' border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='rowhead' width='1%'>{$lang['userdetails_joined']}</td><td align='left' width='99%'>{$joindate}  ($weeks weeks ago)</td></tr>
    <tr><td class='rowhead'>{$lang['userdetails_seen']}</td><td align='left'>{$lastseen}  (";
    if (!empty($user['page']))
    $HTMLOUT.="$user[page])</td></tr>";
    else
    $HTMLOUT.="{$lang['userdetails_unknown']}<b>)</b></td></tr>";
    

    //total online-time by d6bmg
    if($user['onlinetime'] > 0)
        $onlinetime = time_return($user['onlinetime']);
    else
    $onlinetime = "This user was not online!!";
    $HTMLOUT .="<tr><td class='rowhead' width='1%'>Total Online</td><td align='left' width='99%'>{$onlinetime}</td></tr>";
    
    if ($CURUSER['class'] >= UC_MODERATOR)
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_email']}</td><td align='left'><a href='{$TBDEV['baseurl']}/email-gateway.php?id={$user['id']}'>{$user['email']}</a></td></tr>\n";
    if ($CURUSER["class"] >= UC_MODERATOR) { 
        if (isset($addr)) 
            $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_address']}</td><td align='left' class='colheadmain'>$addr&nbsp;(<a class='altlink_default' href='admin.php?action=iphistory&amp;id=$user[id]'><b>History</b></a>)&nbsp;(<a class='altlink_default' href='admin.php?action=iplist&amp;id=$user[id]'><b>List</b></a>)</td></tr>\n"; 
    }
    /*if (isset($addr))
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_address']}</td><td align='left'>$addr</td></tr>\n";
    */ //decrypted
    if ($CURUSER['class'] >= UC_MODERATOR) {
    $resip = sql_query("SELECT ip FROM iplog WHERE userid = ".sqlesc($id)." GROUP BY ip") or sqlerr(__FILE__, __LINE__);
    $iphistory = mysql_num_rows($resip);
    if ($iphistory > 0)
		$HTMLOUT .="<tr><td class='rowhead'>IP History</td><td align='left'>This user has earlier used <b><a href='{$TBDEV['baseurl']}/iphistory.php?id=" .$user['id'] ."'>{$iphistory} different IP addresses</a></b></td></tr>\n";
    }
    //  if ($user["id"] == $CURUSER["id"] || $CURUSER['class'] >= UC_MODERATOR)
    //	{

    $days = round((time() - $user['added'])/86400);
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_uploaded']}</td><td align='left'>".mksize($user['uploaded'])." <b>(</b>".($days > 1 ? mksize($user['uploaded']/$days) : mksize($user['uploaded']))."{$lang['userdetails_perday']}<b>)</b></td></tr>
	<tr><td class='rowhead'>{$lang['userdetails_downloaded']}</td><td align='left'>".mksize($user['downloaded'])." <b>(</b>".($days > 1 ? mksize($user['downloaded']/$days) : mksize($user['downloaded']))."{$lang['userdetails_perday']}<b>)</b></td></tr>\n";
    if ($user["downloaded"] > 0)
    {
      $sr = $user["uploaded"] / $user["downloaded"];
      if ($sr >= 4)
        $s = "w00t";
      else if ($sr >= 2)
        $s = "grin";
      else if ($sr >= 1)
        $s = "smile1";
      else if ($sr >= 0.5)
        $s = "noexpression";
      else if ($sr >= 0.25)
        $s = "sad";
      else
        $s = "cry";
      $sr = floor($sr * 1000) / 1000;
      $sr = "<table border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'><font color='" . get_ratio_color($sr) . "'>" . number_format($sr, 3) . "</font></td><td class='embedded'>&nbsp;&nbsp;<img src=\"{$TBDEV['pic_base_url']}smilies/{$s}.gif\" alt='' /></td></tr></table>";
      $HTMLOUT .= "<tr><td class='rowhead' style='vertical-align: middle'>Share ratio</td><td align='left' valign='middle' style='padding-top: 1px; padding-bottom: 0px'>$sr</td></tr>\n";
    }
    //}

    //if ($user['donated'] > 0 && ($CURUSER['class'] >= UC_MODERATOR || $CURUSER["id"] == $user["id"]))
    //  print("<tr><td class='rowhead'>Donated</td><td align='left'>$user[donated]</td></tr>\n");
    if ($user["avatar"])
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_avatar']}</td><td align='left'><img src=" . htmlspecialchars($user["avatar"]) . "  alt='' /></td></tr>\n";
    else
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_avatar']}</td><td align='left'><img src=\"".$TBDEV['pic_base_url'] ."default_avatar.gif\" alt='' /></td></tr>";
    //gender pics
    if(htmlspecialchars($user["gender"]) == 'Male'){
        $gen= "<img src='{$TBDEV['pic_base_url']}male.gif' alt=''>";
    }
    else if(htmlspecialchars($user["gender"]) == 'Female'){
        $gen = "<img src='{$TBDEV['pic_base_url']}female.gif' alt='1'>";
    }
    else if (htmlspecialchars($user["gender"]) == 'N/A'){
        $gen = "<img src='{$TBDEV['pic_base_url']}na.gif' alt='2'>";
    }
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_gender']}</td><td align='left'>" .$gen. "</td></tr>\n";
    //end gender pics
    if ($user["title"])
    $title = $user["title"];
    elseif (!$user["title"])
    $title = get_user_class_name($user["class"]);
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_class']}</td><td align='left'>" . get_user_class_image($user["class"]) . "<br /><br /><b>(</b>" . $title . "<b>)</b></td></tr>\n";
    if ($CURUSER["class"] >= UC_MODERATOR && $user["invites"] > 0 || $user["id"] == $CURUSER["id"] && $user["invites"] > 0)
    $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_invites']}</td><td align='left'><a href='{$TBDEV['baseurl']}/invite.php'>".htmlspecialchars($user["invites"])."</a></td></tr>\n";
    if ($CURUSER["class"] >= UC_MODERATOR && $user["invitedby"] > 0 || $user["id"] == $CURUSER["id"] && $user["invitedby"] > 0)
    {
    $invitedby = sql_query("SELECT username FROM users WHERE id=$user[invitedby]");
    $invited_by2 = mysql_fetch_assoc($invitedby);
    $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_invby']}</td><td align='left'><a href='{$TBDEV['baseurl']}/userdetails.php?id=$user[invitedby]'>$invited_by2[username]</a></td></tr>\n";
    }
    if ($CURUSER["class"] >= UC_MODERATOR && $user["invitees"] > 0 || $user["id"] == $CURUSER["id"] && $user["invitees"] > 0)
    {
    $compl = $user["invitees"];
    $compl_list = explode(" ", $compl);
    $arr = array();
    foreach($compl_list as $array_list)
    $arr[] = $array_list;
    $compl_arr = array_reverse($arr, TRUE);
    $f=0;
    foreach($compl_arr as $user_id)
    {
    $compl_user = sql_query("SELECT id, username FROM users WHERE id='$user_id' and status='confirmed'");
    $compl_users = mysql_fetch_assoc($compl_user);
    if ($compl_users["id"] > 0)
    {
    $HTMLOUT .="<tr><td class='rowhead' width='1%'>{$lang['userdetails_invitees']}</td><td>";
    $compl = $user["invitees"];
    $compl_list = explode(" ", $compl);
    $arr = array();
    foreach($compl_list as $array_list)
    $arr[] = $array_list;
    $compl_arr = array_reverse($arr, TRUE);
    $i = 0;
    foreach($compl_arr as $user_id)
    {
    $compl_user = sql_query("SELECT id, username FROM users WHERE id='$user_id' and status='confirmed' ORDER BY username");
    $compl_users = mysql_fetch_assoc($compl_user);
    $HTMLOUT .="<a href='{$TBDEV['baseurl']}/userdetails.php?id=" . $compl_users["id"] . "'>" . $compl_users["username"] . "</a> ";
    if ($i == "9")
    break;
    $i++;
    }
    $HTMLOUT .="</td></tr>";
    $f = 1;
    }
    if ($f == "1")
    break;
    }
    }
    $HTMLOUT .= "<tr><td class='rowhead'>Freeleech Slots</td>
                 <td align='left'>".(int)$user['freeslots']."</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>Freeleech Status</td>
                 <td align='left'>".($user['free_switch'] != 0 ? 'FREE Status '.($user['free_switch'] > 1 ? 'Expires: '.get_date($user['free_switch'], 'DATE').' ('.mkprettytime($user['free_switch'] - time()).' to go) <br />':'Unlimited<br />'):'None')."</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_bonus_points']}</td><td align='left'>".(int)$user['seedbonus']."</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_profile_hits']}</td>";
    if (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || $CURUSER['class'] >= UC_MODERATOR)
        $HTMLOUT .="<td align='left'><a href='userhits.php?id=$id'>".(int)$user['hits']."</td></tr>";
    else
        $HTMLOUT .="<td align='left'>".(int)$user['hits']."</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_comments']}</td>";
    if ($torrentcomments && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || $CURUSER['class'] >= UC_MODERATOR))
      $HTMLOUT .= "<td align='left'><a href='userhistory.php?action=viewcomments&amp;id=$id'>$torrentcomments</a></td></tr>\n";
    else
      $HTMLOUT .= "<td align='left'>$torrentcomments</td></tr>\n";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_posts']}</td>";

    if ($forumposts && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || $CURUSER['class'] >= UC_MODERATOR))
      $HTMLOUT .= "<td align='left'><a href='userhistory.php?action=viewposts&amp;id=$id'>$forumposts</a></td></tr>\n";
    else
      $HTMLOUT .= "<td align='left'>$forumposts</td></tr>\n";

    $q = sql_query('SELECT connectable,port,agent FROM peers WHERE userid = '.$id.' LIMIT 1') or sqlerr();
if($a = mysql_fetch_row($q)){
$connect = $a[0];
if($connect == "yes"){
 $connectable = "<b><font color='green'><a title='Connectable = Yes'>{$lang['userdetails_yes']}</a></font></b>";
}else{
 $connectable = "<b><font color='red'><a title='Connectable = No'>{$lang['userdetails_no']}</a></font></b>";
}
}else{
$connectable = "<b><font color='blue'><a title='Connectable = N/A'>{$lang['userdetails_unknown2']}</a></font></b>";
}
$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_connectable']}</td><td align='left'>".$connectable."</td></tr>";
 	$q = sql_query('SELECT port,agent FROM peers WHERE userid = '.$id.' LIMIT 1') or sqlerr();
if(mysql_num_rows($q) == 1) {
$a = mysql_fetch_assoc($q);
}
$port= $a['port'];
$agent = $a['agent'];
if (!empty($port))
 	$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_port']}</font></td><td class='tablea' align='left'>$port</td></tr>
<tr><td class='rowhead'>{$lang['userdetails_client']}</font></td><td class='tablea' align='left'>".htmlentities($agent)."</td></tr>
";
    if (isset($seeding) || isset($leeching))
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_flush']}</td><td align='left'><a href='flush.php?id=$id'>{$lang['userdetails_flush2']}</a></td></tr>";
    //Start Of Expanding Uploaded torrents
    //if (mysql_num_rows($r) > 0)
    $numbupl = mysql_num_rows($res);  
    if (isset($torrents))
    $HTMLOUT .= "<tr valign=\"top\"><td class=\"rowhead\" width=\"10%\">{$lang['userdetails_uploaded_t']}</td><td align=\"left\" width=\"90%\"><a href=\"javascript: klappe_news('a')\"><img border=\"0\" src=\"pic/plus.png\" id=\"pica\" alt=\"Show/Hide\" /></a><b><font color=\"red\">&nbsp;&nbsp;$numbupl</font></b><div id=\"ka\" style=\"display: none;\">$torrents</div></td></tr>\n";
    //Start Of Expanding Currently Seeding 
    $numbseeding = mysql_num_rows($res);
    if (isset($seeding))
    $HTMLOUT .= "<tr valign=\"top\"><td class=\"rowhead\" width=\"10%\">{$lang['userdetails_cur_seed']}</td><td align=\"left\" width=\"90%\"><a href=\"javascript: klappe_news('a1')\"><img border=\"0\" src=\"pic/plus.png\" id=\"pica1\" alt=\"Show/Hide\" /></a><b><font color=\"red\">&nbsp;&nbsp;$numbseeding</font></b><div id=\"ka1\" style=\"display: none;\">".maketable($seeding)."</div></td></tr>\n";
    //End Of Expanding Currently Seeding
    //Start Of Expanding Currently leeching 
    $numbleeching = mysql_num_rows($res);
    if (isset($leeching))
    $HTMLOUT .= "<tr valign=\"top\"><td class=\"rowhead\" width=\"10%\">{$lang['userdetails_cur_leech']}</td><td align=\"left\" width=\"90%\"><a href=\"javascript: klappe_news('a2')\"><img border=\"0\" src=\"pic/plus.png\" id=\"pica2\" alt=\"Show/Hide\" /></a><b><font color=\"red\">&nbsp;&nbsp;$numbleeching</font></b><div id=\"ka2\" style=\"display: none;\">".maketable($leeching)."</div></td></tr>\n";
    //End Of Expanding Currently leeching

    //==Snatched
    $snatches='';
    $res = sql_query("SELECT s.*, t.name AS name, c.name AS catname, c.image AS catimg FROM snatched AS s INNER JOIN torrents AS t ON s.torrentid = t.id LEFT JOIN categories AS c ON t.category = c.id WHERE s.userid = $user[id]") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0)
    $snatches = snatchtable($res);
    $numbsnatched = mysql_num_rows($res);
    if (isset($snatches))
    $HTMLOUT .= "<tr valign=\"top\"><td class=\"rowhead\" width=\"10%\">{$lang['userdetails_cur_snatched']}</td><td align=\"left\" width=\"90%\"><a href=\"javascript: klappe_news('a3')\"><img border=\"0\" src=\"pic/plus.png\" id=\"pica3\" alt=\"Show/Hide\" /></a><b><font color=\"red\">&nbsp;&nbsp;$numbsnatched</font></b><div id=\"ka3\" style=\"display: none;\">$snatches</div></td></tr>\n";
    //==End
    
    //=== start snatched
    $count_snatched='';
    if ($CURUSER['class'] >= UC_MODERATOR){
    if (isset($_GET["snatched_table"])){
    $HTMLOUT .="<tr><td class='clearalt6' align='right' valign='top'><b>Snatched stuff:</b><br />[ <a href=\"userdetails.php?id=$id\" class=\"sublink\">Hide list</a> ]</td><td class='clearalt6'>";
    
    $res = sql_query(
    "SELECT sn.start_date AS s, sn.complete_date AS c, sn.last_action AS l_a, sn.seedtime AS s_t, sn.seedtime, sn.leechtime AS l_t, sn.leechtime, sn.downspeed, sn.upspeed, sn.uploaded, sn.downloaded, sn.torrentid, sn.start_date, sn.complete_date, sn.seeder, sn.last_action, sn.connectable, sn.agent, sn.seedtime, sn.port, cat.name, cat.image, t.size, t.seeders, t.leechers, t.owner, t.name AS torrent_name ".
    "FROM snatched AS sn ".
    "LEFT JOIN torrents AS t ON t.id = sn.torrentid ".
    "LEFT JOIN categories AS cat ON cat.id = t.category ".
    "WHERE sn.userid=$id ORDER BY sn.start_date DESC") or die(mysql_error());

    $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5' align='center'><tr><td class='colhead' align='center'>Category</td><td class='colhead' align='left'>Torrent</td>".
    "<td class='colhead' align='center'>S / L</td><td class='colhead' align='center'>Up / Down</td><td class='colhead' align='center'>Torrent Size</td>".
    "<td class='colhead' align='center'>Ratio</td><td class='colhead' align='center'>Client</td></tr>";
    while ($arr = mysql_fetch_assoc($res)){
    //=======change colors
    $count2='';
    $count2= (++$count2)%2;
    $class = 'clearalt'.($count2==0?'6':'7');
    //=== speed color red fast green slow ;)
    if ($arr["upspeed"] > 0)
    $ul_speed = ($arr["upspeed"] > 0 ? mksize($arr["upspeed"]) : ($arr["seedtime"] > 0 ? mksize($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) : mksize(0)));
    else
    $ul_speed = mksize(($arr["uploaded"] / ( $arr['l_a'] - $arr['s'] + 1 )));
    if ($arr["downspeed"] > 0)
    $dl_speed = ($arr["downspeed"] > 0 ? mksize($arr["downspeed"]) : ($arr["leechtime"] > 0 ? mksize($arr["downloaded"] / $arr["leechtime"]) : mksize(0)));
    else
    $dl_speed = mksize(($arr["downloaded"] / ( $arr['c'] - $arr['s'] + 1 )));
    
    $dlc="";
    switch (true){
    case ($dl_speed > 600):
    $dlc = 'red';
    break;
    case ($dl_speed > 300 ):
    $dlc = 'orange';
    break;
    case ($dl_speed > 200 ):
    $dlc = 'yellow';
    break;
    case ($dl_speed < 100 ):
    $dlc = 'Chartreuse';
    break;
    }

    if ($arr["downloaded"] > 0){
    $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
    $ratio = "<font color='" . get_ratio_color($ratio) . "'><b>Ratio:</b><br />$ratio</font>";
    }
    else
    if ($arr["uploaded"] > 0)
    $ratio = "Inf.";
    else
    $ratio = "N/A"; 
 
    $HTMLOUT .= "<tr><td class='$class' align='center'>".($arr['owner'] == $id ? "<b><font color='orange'>Torrent owner</font></b><br />" : "".($arr['complete_date'] != '0'  ? "<b><font color='lightgreen'>Finished</font></b><br />" : "<b><font color='red'>Not Finished</font></b><br />")."")."<img src='{$TBDEV['pic_base_url']}caticons/$arr[image]' alt='$arr[name]' title='$arr[name]' /></td>"."
    <td class='$class'><a class='altlink' href='{$TBDEV['baseurl']}/details.php?id=$arr[torrentid]'><b>$arr[torrent_name]</b></a>".($arr['complete_date'] != '0'  ?"<br />"."
    <font color='yellow'>started: ".get_date($arr['start_date'], 0,1) ."</font><br />
    " : " "."<font color='yellow'>started:".get_date($arr['start_date'], 0,1) ."</font><br /><font color='orange'>Last Action:".get_date($arr['last_action'], 0,1) ."</font>"." 
    ".get_date($arr['complete_date'], 0,1) ." ".($arr['complete_date'] == '0'  ? "".($arr['owner'] == $id ? "" : "[ ".mksize($arr["size"] - $arr["downloaded"])." still to go ]")."" : "")."")." ".get_date($arr['complete_date'], 0,1) ." ".($arr['complete_date'] != '0'  ? "<br />"."
    <font color='silver'>Time to download: ".($arr['leechtime'] != '0' ? mkprettytime($arr['leechtime']) : mkprettytime($arr['c'] - $arr['s'])."")."</font> <font color='$dlc'>[ DLed at: $dl_speed ]</font>"."
    <br />" : "<br />")."<font color='lightblue'>".($arr['seedtime'] != '0' ? "Total seeding time: ".mkprettytime($arr['seedtime'])." </font><font color='$dlc'> " : "Total seeding time: N/A").""."
    </font><font color='lightgreen'> [ up speed: ".$ul_speed." ] </font>".get_date($arr['complete_date'], 0,1) ."".($arr['complete_date'] == '0'  ? "<br /><font color='$dlc'>Download speed: $dl_speed</font>" : "")."</td>"."
    <td align='center' class='$class'>Seeds: ".$arr['seeders']."<br />Leech: ".$arr['leechers']."</td><td align='center' class='$class'><font color='lightgreen'>Uploaded:<br />"."
    <b>".$uploaded = mksize($arr["uploaded"])."</b></font><br /><font color='orange'>Downloaded:<br /><b>".$downloaded = mksize($arr["downloaded"])."</b></font></td>"."
    <td align='center' class='$class'>".mksize($arr["size"])."<br />Difference of:<br /><font color='orange'><b>".mksize($arr['size'] - $arr["downloaded"])."</b></font></td>"."
    <td align='center' class='$class'>".$ratio."<br />".($arr['seeder'] == 'yes' ? "<font color='lightgreen'><b>seeding</b></font>" : "<font color='red'><b>Not seeding</b></font>").""."
    </td><td align='center' class='$class'>".$arr["agent"]."<br />port: ".$arr["port"]."<br />".($arr["connectable"] == 'yes' ? "<b>Connectable:</b> <font color='lightgreen'>Yes</font>"."
    " : "<b>Connectable:</b> <font color='red'><b>no</b></font>")."</td></tr>\n";
    }
    $HTMLOUT .= "</table></td></tr>\n";
    }
    else
    $HTMLOUT .= tr("Snatched stuff:<br />","[ <a href=\"userdetails.php?id=$id&amp;snatched_table=1\" class=\"sublink\">Show</a> ]  - $count_snatched <font color='red'><b>staff only!!!</b></font>", 1);
    }
    //=== end snatched
       
    //==09 Hnr mod - sir_snugglebunny
    $completed = "";
    $r = sql_query("SELECT torrents.name,torrents.added AS torrent_added, snatched.start_date AS s, snatched.complete_date AS c, snatched.downspeed, snatched.seedtime, snatched.seeder, snatched.torrentid as tid, snatched.id, categories.id as category, categories.image, categories.name as catname, snatched.uploaded, snatched.downloaded, snatched.hit_and_run, snatched.mark_of_cain, snatched.complete_date, snatched.last_action, torrents.seeders, torrents.leechers, torrents.owner, snatched.start_date AS st, snatched.start_date FROM snatched JOIN torrents ON torrents.id = snatched.torrentid JOIN categories ON categories.id = torrents.category WHERE snatched.finished='yes' AND userid=$id AND torrents.owner != $id ORDER BY snatched.id DESC") or sqlerr(__FILE__, __LINE__);
    //=== completed
    if (mysql_num_rows($r) > 0){ 
    $completed .= "<table class='main' border='1' cellspacing='0' cellpadding='3'>
    <tr>
    <td class='colhead'>{$lang['userdetails_type']}</td>
    <td class='colhead'>{$lang['userdetails_name']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_s']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_l']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_ul']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_dl']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_ratio']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_wcompleted']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_laction']}</td>
    <td class='colhead' align='center'>{$lang['userdetails_speed']}</td></tr>";
    while ($a = mysql_fetch_assoc($r)){
    //=======change colors
    $count2='';
    $count2= (++$count2)%2;
    $class = 'clearalt'.($count2 == 0 ? 6 : 7);
    $torrent_needed_seed_time = ($a['st'] - $a['torrent_added']);
    //=== get times per class
    switch (true)
    { 
    //=== user
    case ($user['class'] < UC_POWER_USER):
    $days_3 = 3*86400; //== 3 days
    $days_14 = 2*86400; //== 2 days
    $days_over_14 = 86400; //== 1 day
    break;
    //=== poweruser
    case ($user['class'] == UC_POWER_USER):
    $days_3 = 2*86400; //== 2 days
    $days_14 = 129600; //== 36 hours
    $days_over_14 = 64800; //== 18 hours
    break;
    //=== vip donor
    case ($user['class'] == UC_VIP):
    $days_3 = 129600; //== 36 hours
    $days_14 = 86400; //== 24 hours
    $days_over_14 = 43200; //== 12 hours
    break;
    //=== uploader / staff and above (we don't need this for uploaders + now do we lol?)
    case ($user['class'] >= UC_UPLOADER):
    $days_3 = 86400; //== 24 hours
    $days_14 = 43200; //== 12 hours
    $days_over_14 = 21600; //== 6 hours
    break;
    }
    //=== times per torrent based on age
    switch(true) 
    {
    case (($a['st'] - $a['torrent_added']) < 7*86400):
    //$minus_ratio = ($days_3 - $a['seedtime']);
    //=== or using ratio in the equation
    $minus_ratio = ($days_3 - $a['seedtime']) - ($a['uploaded'] / $a['downloaded'] * 3 * 86400);
    break;
    case (($a['st'] - $a['torrent_added']) < 21*86400):
    //$minus_ratio = ($days_14 - $a['seedtime']);
    //=== or using ratio in the equation
    $minus_ratio = ($days_14 - $a['seedtime']) - ($a['uploaded'] / $a['downloaded'] * 2 * 86400);
    break;
    case (($a['st'] - $a['torrent_added']) >= 21*86400):
    //$minus_ratio = ($days_over_14 - $a['seedtime']);
    //=== or using ratio in the equation
    $minus_ratio = ($days_over_14 - $a['seedtime']) - ($a['uploaded'] / $a['downloaded'] * 86400);
    break;
    }
    $color = (($minus_ratio > 0 && $a['uploaded'] < $a['downloaded']) ? get_ratio_color($minus_ratio) : 'limegreen');
    $minus_ratio = mkprettytime($minus_ratio); 
    //=== speed color red fast green slow ;)
    if ($arr["downspeed"] > 0)
    $dl_speed = ($a["downspeed"] > 0 ? mksize($a["downspeed"]) : ($a["leechtime"] > 0 ? mksize($a["downloaded"] / $a["leechtime"]) : mksize(0)));
    else
    $dl_speed = mksize(($a["downloaded"] / ( $a['c'] - $a['s'] + 1 )));
    $dlc="";
    switch (true){
    case ($dl_speed > 600):
    $dlc = 'red';
    break;
    case ($dl_speed > 300 ):
    $dlc = 'orange';
    break;
    case ($dl_speed > 200 ):
    $dlc = 'yellow';
    break;
    case ($dl_speed < 100 ):
    $dlc = 'Chartreuse';
    break;
    }
    //=== mark of cain / hit and run
    $checkbox_for_delete = ($CURUSER['class'] >=  UC_MODERATOR ? " [<a href='".$TBDEV['baseurl']."/userdetails.php?id=".$id."&amp;delete_hit_and_run=".$a['id']."'>Remove</a>]" : '');
    $mark_of_cain = ($a['mark_of_cain'] == 'yes' ? "<img src='{$TBDEV['pic_base_url']}moc.gif' alt='Mark Of Cain' title='the mark of Cain!' />".$checkbox_for_delete : '');
    $hit_n_run = ($a['hit_and_run'] > 0 ? "<img src='{$TBDEV['pic_base_url']}hnr.gif' alt='hit and run' title='hit and run!' />" : '');
    $completed .= "<tr><td style='padding: 0px' class='$class'><img src='{$TBDEV['pic_base_url']}caticons/$a[image]' alt='$a[name]' title='$a[name]' /></td>
    <td class='$class'><a class='altlink' href='{$TBDEV['baseurl']}/details.php?id=".$a['tid']."&amp;hit=1'><b>".htmlspecialchars($a['name'])."</b></a>
    <br /><font color='.$color.'>  ".(($CURUSER['class'] >= UC_MODERATOR || $user['id'] == $CURUSER['id']) ? "seeded for</font>: ".mkprettytime($a['seedtime']).(($minus_ratio != '0:00' && $a['uploaded'] < $a['downloaded']) ? "<br />should still seed for: ".$minus_ratio."&nbsp;&nbsp;" : '').
    ($a['seeder'] == 'yes' ? "&nbsp;<font color='limegreen'> [<b>seeding</b>]</font>" : $hit_n_run."&nbsp;".$mark_of_cain) : '')."</td>
    <td align='center' class='$class'>".$a['seeders']."</td>
    <td align='center' class='$class'>".$a['leechers']."</td>
    <td align='center' class='$class'>".mksize($a['uploaded'])."</td>
    <td align='center' class='$class'>".mksize($a['downloaded'])."</td>
    <td align='center' class='$class'>".($a['downloaded'] > 0 ? "<font color='" . get_ratio_color(number_format($a['uploaded'] / $a['downloaded'], 3)) . "'>".number_format($a['uploaded'] / $a['downloaded'], 3)."</font>" : ($a['uploaded'] > 0 ? 'Inf.' : '---'))."<br /></td>
    <td align='center' class='$class'>".get_date($a['complete_date'], 'DATE')."</td>
    <td align='center' class='$class'>".get_date($a['last_action'], 'DATE')."</td>
    <td align='center' class='$class'><font color='$dlc'>[ DLed at: $dl_speed ]</font></td></tr>";
    }
    $completed .= "</table>\n";
    }
    if ($completed && $CURUSER['class'] >= UC_POWER_USER || $completed && $user['id'] == $CURUSER['id']){ 
    if (!isset($_GET['completed']))
    $HTMLOUT .= tr(''.$lang['userdetails_completedt'].'<br />','[ <a href=\'./userdetails.php?id='.$id.'&amp;completed=1#completed\' class=\'sublink\'>Show</a> ]&nbsp;&nbsp;-&nbsp;'.mysql_num_rows($r), 1);
    elseif (mysql_num_rows($r) == 0)
    $HTMLOUT .= tr(''.$lang['userdetails_completedt'].'<br />','[ <a href=\'./userdetails.php?id='.$id.'&amp;completed=1\' class=\'sublink\'>Show</a> ]&nbsp;&nbsp;-&nbsp;'.mysql_num_rows($r), 1);
    else
    $HTMLOUT .= tr('<a name=\'completed\'>'.$lang['userdetails_completedt'].'</a><br />[ <a href=\'./userdetails.php?id='.$id.'#history\' class=\'sublink\'>Hide list</a> ]', $completed, 1);
    } 
    //==End hnr
    if ($user["info"])
     $HTMLOUT .= "<tr valign='top'><td align='left' colspan='2' class='text' bgcolor='#F4F4F0'>" . format_comment($user["info"]) . "</td></tr>\n";
        //==Report User
    $HTMLOUT .="<tr>
      <td colspan='2' align='center'><form method='post' action='report.php?type=User&id={$id}'> <input type='submit' value='Report User' class='btn' /> </form>";
//==End
    if ($CURUSER["id"] != $user["id"])
      if ($CURUSER['class'] >= UC_MODERATOR)
        $showpmbutton = 1;
      elseif ($user["acceptpms"] == "yes")
      {
        $r = sql_query("SELECT id FROM blocks WHERE userid={$user['id']} AND blockid={$CURUSER['id']}") or sqlerr(__FILE__,__LINE__);
        $showpmbutton = (mysql_num_rows($r) == 1 ? 0 : 1);
      }
      elseif ($user["acceptpms"] == "friends")
      {
        $r = sql_query("SELECT id FROM friends WHERE userid=$user[id] AND friendid=$CURUSER[id]") or sqlerr(__FILE__,__LINE__);
        $showpmbutton = (mysql_num_rows($r) == 1 ? 1 : 0);
      }
    if (isset($showpmbutton))
      $HTMLOUT .="<form method='get' action='sendmessage.php'>
        <input type='hidden' name='receiver' value='{$user["id"]}' />
        <input type='submit' value='{$lang['userdetails_msg_btn']}' class='btn' />
      </form>
      </td></tr>";

    $HTMLOUT .= "</table>\n";
          $username = htmlspecialchars($user["username"]);  
    $HTMLOUT .=" <form method='post' action='{$TBDEV['baseurl']}/delacctadmin.php' name='deluser'>\n";
    if ($CURUSER['class'] >= UC_SYSOP)
    $HTMLOUT .= "<table width='100%' border='1' cellspacing='0' cellpadding='5'>
    <tr><td align='left' class='rowhead'>{$lang['userdetails_delete']}<input name='username' size='20' value='" .$username . "' type='hidden' />
    <input name='delenable' type='checkbox' onclick=\"if (this.checked) {enabledel();}else{disabledel();}\" /></td>
    <td colspan='2' align='center'>
    <input name='submit' type='submit' class='btn' value=\"{$lang['userdetails_delete']}\" disabled=\"disabled\" />
    </td></tr></table></form>\n"; 

    $HTMLOUT .="<script type='text/javascript'>
/*<![CDATA[*/
function togglepic(bu, picid, formid){
	var pic = document.getElementById(picid);
	var form = document.getElementById(formid);
	
	if(pic.src == bu + '/pic/plus.gif')	{
		pic.src = bu + '/pic/minus.gif';
		form.value = 'minus';
	}else{
		pic.src = bu + '/pic/plus.gif';
		form.value = 'plus';
	}
}
/*]]>*/
</script>";
    if ($CURUSER['class'] >= UC_MODERATOR && $user["class"] < $CURUSER['class'])
    {
      //$HTMLOUT .= begin_frame("<div class='headline' width='70%' <span>{$lang['userdetails_edit_user']}</span></div>", true);
      $HTMLOUT .= begin_frame("{$lang['userdetails_edit_user']}", true);
      $HTMLOUT .= "<form method='post' action='modtask.php'>\n";
      $HTMLOUT .= "<input type='hidden' name='action' value='edituser' />\n";
      $HTMLOUT .= "<input type='hidden' name='userid' value='$id' />\n";
      $HTMLOUT .= "<input type='hidden' name='returnto' value='userdetails.php?id=$id' />\n";
      $HTMLOUT .= "<table class='main' border='1' cellspacing='0' cellpadding='5'>\n";
      if ($CURUSER["class"] >= UC_ADMINISTRATOR){
        $username = htmlspecialchars($user["username"]);
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_chuname']}</td><td colspan='2' align='left'><input type='text' size='20' name='username' value='" . $username . "' /></td></tr>\n"; 
      }
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_title']}</td><td colspan='2' align='left'><input type='text' size='60' name='title' value='" . htmlspecialchars($user['title']) . "' /></td></tr>\n";
      $avatar = htmlspecialchars($user["avatar"]);
      $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_avatar_url']}</td><td colspan='2' align='left'><input type='text' size='60' name='avatar' value='$avatar' /><br /><b> avatar May Be offensive To Some Users</b><input type='radio' name='offavatar' value='yes' " .($user["offavatar"] == "yes" ? " checked='checked'" : "")." />Yes<input type='radio' name='offavatar' value='no' " .($user["offavatar"] == "no" ? " checked='checked'" : "")." />No</td></tr>\n";
      $signature = htmlspecialchars($user["signature"]);
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_signature_url']}</td><td colspan='2' align='left'><input type='text' size='60' name='signature' value='$signature' /></td></tr>\n";
    // === Donor mod time based by d6bmg 
     if ($CURUSER["class"] == UC_STAFF_LEADER) { 
     $donor = $user["donor"] == "yes"; 
     $HTMLOUT .="<tr><td class='rowhead' align='right'><b>{$lang['userdetails_donor']}</b></td><td colspan='2' align='left'>"; 
     if ($donor) { 
     $donoruntil = $user['donoruntil']; 
     if ($donoruntil == '0') 
     $HTMLOUT .="Arbitrary duration"; 
     else { 
     $HTMLOUT .="<b>".$lang['userdetails_donor2']."</b> ".get_date($user['donoruntil'], 'DATE'). " "; 
     $HTMLOUT .=" [ " . mkprettytime($donoruntil - TIME_NOW) . " ] To go\n"; 
     } 
     } else { 
     $HTMLOUT .="{$lang['userdetails_dfor']}<select name='donorlength'><option value='0'>------</option><option value='4'>1 month</option>" . 
     "<option value='6'>6 weeks</option><option value='8'>2 months</option><option value='10'>10 weeks</option>" . 
     "<option value='12'>3 months</option><option value='255'>Unlimited</option></select>\n"; 
     } 
     $HTMLOUT .="<br /><b>{$lang['userdetails_cdonation']}</b><input type='text' size='6' name='donated' value=\"" .htmlspecialchars($user["donated"]) . "\" />" . "<b>{$lang['userdetails_tdonations']}</b>" . htmlspecialchars($user["total_donated"]) . ""; 
     if ($donor) { 
     $HTMLOUT .="<br /><b>{$lang['userdetails_adonor']}</b> <select name='donorlengthadd'><option value='0'>------</option><option value='4'>1 month</option>" . 
     "<option value='6'>6 weeks</option><option value='8'>2 months</option><option value='10'>10 weeks</option>" . 
     "<option value='12'>3 months</option><option value='255'>Unlimited</option></select>\n"; 
     $HTMLOUT .="<br /><b>{$lang['userdetails_rdonor']}</b><input name='donor' value='no' type='checkbox' /> [ If they were bad ]"; 
     } 
     $HTMLOUT .="</td></tr>\n"; 
     } 
     // ====End time based donor mod

      if ($CURUSER['class'] == UC_MODERATOR && $user["class"] > UC_VIP)
        $HTMLOUT .= "<input type='hidden' name='class' value='{$user['class']}' />\n";
      else
      {
        $HTMLOUT .= "<tr><td class='rowhead'>Class</td><td colspan='2' align='left'><select name='class'>\n";
        if ($CURUSER['class'] == UC_MODERATOR)
          $maxclass = UC_VIP;
        else
          $maxclass = $CURUSER['class'] - 1;
        for ($i = 0; $i <= $maxclass; ++$i)
          $HTMLOUT .= "<option value='$i'" . ($user["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";
        $HTMLOUT .= "</select></td></tr>\n";
      }
      $HTMLOUT.="<tr><td class='rowhead'>Forum Mod</td><td colspan='2' align='left'><input name=\"forum_mod\" value=\"yes\" type=\"radio\" " .
    ($user["forum_mod"] == "yes" ? " checked='checked'" : "") . " />Yes <input name=\"forum_mod\" value=\"no\" type=\"radio\" " .
    ($user["forum_mod"] == "no" ? " checked='checked'" : "") . " />No</td></tr>\n";
    if ($user["forum_mod"] == "yes") {
    $r = sql_query("SELECT id,name,description FROM forums WHERE place = -1 ORDER BY name ASC") or sqlerr();
    $forumsc = mysql_num_rows($r);
		$HTMLOUT.="<tr><td colspan='3' align='center'>
		<input type='hidden' name='forums_count' value='".$forumsc."' />
		<div style='border-style:solid; border-color:#333333; border-width: 1px 1px 1px 3px; width:100%; height:170px; overflow:auto;'>
		<table cellpadding='5' border='0' style='margin:3px;' >";
    $i = 1;
    while ($a = mysql_fetch_assoc($r)) {
    $HTMLOUT.="<tr><td style='border-width: 1px 0px 0px 0px;border-style:dotted;' width='100%'><a href='{$TBDEV['baseurl']}/forums.php?action=viewforum&forumid=".$a["id"]."'>".$a["name"]."</a><br />
    <font style='font-size:10px; padding-left:15px;'>". (strlen($a["description"]) >80 ? substr($a["description"], 0, 80) . "..." : $a["description"]) ."</font>
    </td>
    <td nowrap='nowrap' align='right' style='border:none; border-width: 1px 0px 0px 0px;border-style:dotted'>
    <input type='radio' name='forums_".$i."' value='yes_".$a["id"]."' ".(stristr($user["forums_mod"], "[" . $a["id"] . "]") == true ? "checked='checked'" : "") ." title='Set moderator for this forum' />
    <input type='radio' name='forums_".$i."' value='no_".$a["id"]."' ". (stristr($user["forums_mod"], "[" . $a["id"] . "]") != true ? "checked='checked'" : "") ." title='Unset moderator for this forum' /></td></tr>";
    $i++;
    }
		$HTMLOUT.="</table>
		</div>
		</td></tr>";
    }
     //===first line support
 $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_support']}</td><td colspan='2' align='left'><input type='radio' name='support' value='yes'" .($user["support"] == "yes" ? " checked" : "").">{$lang['userdetails_yes']} <input type='radio' name='support' value='no'" .($user["support"] == "no" ? " checked" : "").">{$lang['userdetails_no']} </td></tr>\n";
 	$supportfor = htmlspecialchars($user["supportfor"]);
 	$HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_supportfor']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='supportfor'>$supportfor</textarea></td></tr>\n";
    $list = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\"><tr>"; 
        $i=1; 
        foreach($_fls as $lang_id=>$lang_name){ 
                $list .= "<td nowrap=\"nowrap\" style=\"border:none\"><label for=\"lang_".$lang_id."\">".$lang_name."</label></td><td style=\"border:none;\"><input id=\"lang_".$lang_id."\" ".(stristr($user["support_lang"],$lang_id) ? "checked=\"checked\"" : "" )."type=\"checkbox\" name=\"fls_langs[]\" value=\"".$lang_id."\" /></td>"; 
                if($i%4==0)     $list .= "</tr><tr>"; $i++; 
                } 
        $list .="</tr></table>"; 
        $HTMLOUT .="<tr><td class='rowhead'>Support<br/>Language(s)</td><td colspan='2' align='left'>".$list."</textarea></td></tr>"; 
        unset($list);unset($_fls);
 //end
    //safe modcomment
    $modcomment = htmlspecialchars($user["modcomment"]);
    if ($CURUSER["class"] < UC_SYSOP) {
        $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='modcomment' readonly='readonly'>$modcomment</textarea></td></tr>\n";
    }
    else {
        $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='modcomment'>$modcomment</textarea></td></tr>\n";
    }
    $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_add_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='addcomment'></textarea></td></tr>\n";
    //end safe modcomment
    //=== bonus comment 
    $bonuscomment = htmlspecialchars($user["bonuscomment"]);
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_bonus_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='bonuscomment' readonly='readonly' style='background:purple;color:yellow;'>$bonuscomment</textarea></td></tr>\n";
    //==end
      $warned = $user["warned"] == "yes";

      $HTMLOUT .= "<tr><td class='rowhead'" . (!$warned ? " rowspan='2'": "") . ">{$lang['userdetails_warned']}</td>
      <td align='left' width='20%'>" .
      ( $warned
      ? "<input name=warned value='yes' type='radio' checked='checked' />{$lang['userdetails_yes']}<input name='warned' value='no' type='radio' />{$lang['userdetails_no']}"
      : "{$lang['userdetails_no']}" ) ."</td>";

      if ($warned)
      {
        $warneduntil = $user['warneduntil'];
        if ($warneduntil == 0)
          $HTMLOUT .= "<td align='center'>{$lang['userdetails_dur']}</td></tr>\n";
        else
        {
          $HTMLOUT .= "<td align='center'>{$lang['userdetails_until']} ".get_date($warneduntil, 'DATE');
          $HTMLOUT .= " (" . mkprettytime($warneduntil - time())  . " {$lang['userdetails_togo']})</td></tr>\n";
        }
      }
      else
      {
        $HTMLOUT .= "<td>{$lang['userdetails_warn_for']} <select name='warnlength'>\n";
        $HTMLOUT .= "<option value='0'>{$lang['userdetails_warn0']}</option>\n";
        $HTMLOUT .= "<option value='1'>{$lang['userdetails_warn1']}</option>\n";
        $HTMLOUT .= "<option value='2'>{$lang['userdetails_warn2']}</option>\n";
        $HTMLOUT .= "<option value='4'>{$lang['userdetails_warn4']}</option>\n";
        $HTMLOUT .= "<option value='8'>{$lang['userdetails_warn8']}</option>\n";
        $HTMLOUT .= "<option value='255'>{$lang['userdetails_warninf']}</option>\n";
        $HTMLOUT .= "</select></td></tr>\n";
        $HTMLOUT .= "<tr><td colspan='2' align='left'>{$lang['userdetails_pm_comm']}<input type='text' size='60' name='warnpm' /></td></tr>";
      }
      	  if ($CURUSER['class'] >= UC_MODERATOR)
$HTMLOUT .= "<tr><td class='rowhead'>Freeleech Slots:</td><td colspan='2' align='left'>
<input type='text' size='6' name='freeslots' value='".(int)$user['freeslots']."' /></td></tr>";

if ($CURUSER['class'] >= UC_SYSOP) {
	
$free_switch = $user['free_switch'] != 0;

$HTMLOUT .= "<tr><td class='rowhead'".(!$free_switch ? ' rowspan="2"' : '').
    ">Freeleech Status</td>
 	<td align='left' width='20%'>".($free_switch ?
    "<input name='free_switch' value='42' type='radio' />Remove Freeleech Status" :
    "No Freeleech Status Set")."</td>\n";

if ($free_switch)
{
    if ($user['free_switch'] == 1)
        $HTMLOUT .= '<td align="center">(Unlimited Duration)</td></tr>';
    else
        $HTMLOUT .= "<td align='center'>Until ".get_date($user['free_switch'], 'DATE'). " (".
            mkprettytime($user['free_switch'] - time()). " to go)</td></tr>";
} else
{
    $HTMLOUT .= "<td>Freeleech for <select name='free_switch'>
    <option value='0'>------</option>
    <option value='1'>1 week</option>
    <option value='2'>2 weeks</option>
    <option value='4'>4 weeks</option>
    <option value='8'>8 weeks</option>
    <option value='255'>Unlimited</option>
    </select></td></tr>
    <tr><td colspan='2' align='left'>{$lang['userdetails_pm_comm']}<input type='text' size='60' name='free_pm' /></td></tr>";
}
}

    // ////////////09 Modified Auto-leech warning system///////////////////
    $leechwarn = $user["leechwarn"] == "yes";
    $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_awarn']}<br /><font size='1'><i>(Low Ratio)</i></font></td>";
    if ($leechwarn) {
    $HTMLOUT .="<td align='left' class='row'><font color='red'>{$lang['userdetails_warned']}</font></td>\n";
    $leechwarnuntil = htmlspecialchars(get_date($user["leechwarnuntil"], 'DATE'));
    if ($leechwarnuntil != '0') {
    $HTMLOUT .="<td align='left' class='row'>Until $leechwarnuntil";
    $HTMLOUT .="<br />(" . mkprettytime($user["leechwarnuntil"] - time()) ." to go)</td></tr>\n";
    } else {
    $HTMLOUT .="<td align='left' class='row'><i>{$lang['userdetails_fwarn']}</i></td></tr>\n";
    }
    } else {
    $HTMLOUT .="<td class='row' colspan='2'>{$lang['userdetails_nowarn']}</td></tr>\n";
    }
    if ($leechwarn)
        $HTMLOUT .="<tr><td class='rowhead'>{$lang['userdetails_oautow']}</td><td colspan='2' align='left'><input type='radio' name='leechwarn' value='yes'" .($user["leechwarn"] == "yes" ? " checked='checked'" : "") ." />No <input type='radio' name='leechwarn' value='no'" . ($user["leechwarn"] == "no" ? " checked='checked'" : "") . " />Yes</td></tr>\n";
    //==Above line allows over-ride and manual leech-warn but its not required - Systems automatic
    // End//////////////////////////////////////////////////////////////////
    
    if ($CURUSER['class']>= UC_ADMINISTRATOR){
      $HTMLOUT .="<tr>
      <td class='rowhead'>{$lang['userdetails_addupload']}</td>
      <td align='center'>
      <img src='{$TBDEV['pic_base_url']}plus.gif' alt='Change Ratio' title='Change Ratio !' id='uppic' onclick=\"togglepic('{$TBDEV['baseurl']}', 'uppic','upchange')\" /> 
      <input type='text' name='amountup' size='10' />
      </td>
      <td>
      <select name='formatup'>\n
      <option value='mb'>{$lang['userdetails_MB']}</option>\n
      <option value='gb'>{$lang['userdetails_GB']}</option></select>\n
      <input type='hidden' id='upchange' name='upchange' value='plus' />
      </td>
      </tr>
      <tr>
      <td class='rowhead'>{$lang['userdetails_adddownload']}</td>
      <td align='center'>
      <img src='{$TBDEV['pic_base_url']}plus.gif' alt='Change Ratio' title='Change Ratio !' id='downpic' onclick=\"togglepic('{$TBDEV['baseurl']}','downpic','downchange')\" /> 
      <input type='text' name='amountdown' size='10' />
      </td>
      <td>
      <select name='formatdown'>\n
      <option value='mb'>{$lang['userdetails_MB']}</option>\n
      <option value='gb'>{$lang['userdetails_GB']}</option></select>\n
      <input type='hidden' id='downchange' name='downchange' value='plus' />
      </td></tr>";
      }
      if ($CURUSER['class'] >= UC_MODERATOR)
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_bonus_points']}</td><td colspan='2' align='left'><input type='text' size='6' name='seedbonus' value='".(int)$user['seedbonus']."' /></td></tr>";
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_invright']}</td><td class='row' colspan='2' align='left'><input type='radio' name='invite_rights' value='yes'" .($user["invite_rights"]=="yes" ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}<input type='radio' name='invite_rights' value='no'" .($user["invite_rights"]=="no" ? " checked='checked'" : "") . " />{$lang['userdetails_no']}</td></tr>\n";
      $HTMLOUT .= "<tr><td class='rowhead' align='right'><b>{$lang['userdetails_invites']}</b></td><td colspan='2' align='left' class='row'><input type='text' size='3' name='invites' value='" . htmlspecialchars($user['invites']) . "' /></td></tr>\n";
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_chatpos']}</td><td colspan='2' align='left'><input type='radio' name='chatpost' value='1'" .($user["chatpost"] == "1" ? " checked='checked'" : "")." />{$lang['userdetails_yes']} <input type='radio' name='chatpost' value='0'" .($user["chatpost"] == "0" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";
      if ($CURUSER['class'] >= UC_SYSOP)
      {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_immunity']}</td><td colspan='2' align='left'><input type='radio' name='immunity' value='yes'" .($user["immunity"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']} <input type='radio' name='immunity' value='no'" .($user["immunity"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";
      }
      if ($CURUSER["class"] < UC_SYSOP)
      $HTMLOUT .="<input type=\"hidden\" name=\"highspeed\" value=\"$user[highspeed]\" />\n";
      else {
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_highspeed']}</td><td class='row' colspan='2' align='left'><input type='radio' name='highspeed' value='yes' " .($user["highspeed"] == "yes" ? " checked='checked'" : "") ." />Yes <input type='radio' name='highspeed' value='no' " . ($user["highspeed"] == "no" ? " checked='checked'" : "") . " />No</td></tr>\n";
      }
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_disable']}</td><td colspan='2' align='left'><input name='disablecom' value='yes' type='radio'" . ($disablecom ? " checked='checked'" : "") . " />{$lang['userdetails_yes']} <input name='disablecom' value='no' type='radio'" . (!$disablecom ? " checked='checked'" : "") . " />{$lang['userdetails_no']}</td></tr>\n";
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_fpost']}</td><td colspan='2' align='left'><input type='radio' name='forumpost' value='yes'" .($user["forumpost"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']} <input type='radio' name='forumpost' value='no'" .($user["forumpost"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";     
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_upos']}</td><td colspan='2' align='left'><input type='radio' name='uploadpos' value='yes'" .($user["uploadpos"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']} <input type='radio' name='uploadpos' value='no'" .($user["uploadpos"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_dpos']}</td><td colspan='2' align='left'><input type='radio' name='downloadpos' value='yes'" .($user["downloadpos"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']} <input type='radio' name='downloadpos' value='no'" .($user["downloadpos"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n"; 
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_park']}</td><td colspan='2' align='left'><input name='parked' value='yes' type='radio'" . 
        ($user["parked"] == "yes" ? " checked='checked'" : "") . " />{$lang['userdetails_yes']} <input name='parked' value='no' type='radio'" . 
        ($user["parked"] == "no" ? " checked='checked'" : "") . " />{$lang['userdetails_no']}</td></tr>\n";
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_enabled']}</td><td colspan='2' align='left'><input name='enabled' value='yes' type='radio'" . ($enabled ? " checked='checked'" : "") . " />{$lang['userdetails_yes']} <input name='enabled' value='no' type='radio'" . (!$enabled ? " checked='checked'" : "") . " />{$lang['userdetails_no']}</td></tr>\n";
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_reset']}</td><td colspan='2'><input type='checkbox' name='resetpasskey' value='1' /><font class='small'>{$lang['userdetails_pass_msg']}</font></td></tr>";
      //$HTMLOUT .= "</td></tr>";
      $HTMLOUT .= "<tr><td colspan='3' align='center'><input type='submit' class='btn' value='{$lang['userdetails_okay']}' /></td></tr>\n";
      $HTMLOUT .= "</table>\n";
      $HTMLOUT .= "</form>\n";
      $HTMLOUT .= end_frame();
    }
    $HTMLOUT .= end_main_frame();
    
    where ("{$lang['userdetails_viewuser']} <b><a href='{$TBDEV['baseurl']}/userdetails.php?id=$id'>$user[username]</a></b>",$CURUSER["id"]);
    print stdhead("{$lang['userdetails_details']} " . $user["username"]) . $HTMLOUT . stdfoot();

?>