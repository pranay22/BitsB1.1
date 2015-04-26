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

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
require_once "include/pager_functions.php";
require_once "include/torrenttable_functions.php";
require_once "include/html_functions.php";


function ratingpic($num) {
    global $TBDEV;
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5)
        return;
    return "<img src=\"{$TBDEV['pic_base_url']}{$r}.gif\" border=\"0\" alt=\"rating: $num / 5\" />";
}


dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('details') );

    if (!isset($_GET['id']) || !is_valid_id($_GET['id']))
      stderr("{$lang['details_user_error']}", "{$lang['details_bad_id']}"); 
      
    $id = (int)$_GET["id"];
    
    if (isset($_GET["hit"])) 
    {
      mysql_query("UPDATE torrents SET views = views + 1 WHERE id = $id");
      /* if ($_GET["tocomm"])
        header("Location: {$TBDEV['baseurl']}/details.php?id=$id&page=0#startcomments");
      elseif ($_GET["filelist"])
        header("Location: {$TBDEV['baseurl']}/details.php?id=$id&filelist=1#filelist");
      elseif ($_GET["toseeders"])
        header("Location: {$TBDEV['baseurl']}/peerlist.php?id=$id#seeders");
      elseif ($_GET["todlers"])
        header("Location: {$TBDEV['baseurl']}/peerlist.php?id=$id#leechers");
      else */
        header("Location: {$TBDEV['baseurl']}/details.php?id=$id");
      exit();
    }
	
$res = mysql_query("SELECT torrents.seeders, torrents.nuked, torrents.last_reseed, torrents.nukereason, torrents.anonymous, torrents.banned, torrents.leechers, torrents.info_hash, torrents.filename, LENGTH(torrents.nfo) AS nfosz, torrents.last_action AS lastseed, torrents.numratings, torrents.name, IF(torrents.numratings < {$TBDEV['minvotes']}, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.comments, torrents.owner, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, torrents.free, categories.name AS cat_name, users.username, freeslots.free AS freeslot, freeslots.double AS doubleslot, freeslots.tid AS slotid, freeslots.uid AS slotuid FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON torrents.owner = users.id LEFT JOIN freeslots ON (torrents.id=freeslots.tid AND freeslots.uid = {$CURUSER['id']}) WHERE torrents.id = $id")
	or sqlerr();
$row = mysql_fetch_assoc($res);

$owned = $moderator = 0;
	if (get_user_class() >= UC_MODERATOR)
		$owned = $moderator = 1;
	elseif ($CURUSER["id"] == $row["owner"])
		$owned = 1;
//}

if (!$row || ($row["banned"] == "yes" && !$moderator))
	stderr("{$lang['details_error']}", "{$lang['details_torrent_id']}");



    $HTMLOUT = '';
		

		if ($CURUSER["id"] == $row["owner"] || get_user_class() >= UC_MODERATOR)
			$owned = 1;
		else
			$owned = 0;

		$spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		if (isset($_GET["uploaded"])) {
			$HTMLOUT .= "<h2>{$lang['details_success']}</h2>\n";
			$HTMLOUT .= "<p>{$lang['details_start_seeding']}</p>\n";
		}
		elseif (isset($_GET["edited"])) {
			$HTMLOUT .= "<h2>{$lang['details_success_edit']}</h2>\n";
			if (isset($_GET["returnto"]))
				$HTMLOUT .= "<p><b>{$lang['details_go_back']}<a href='" . htmlspecialchars($_GET["returnto"]) . "'>{$lang['details_whence']}</a>.</b></p>\n";
		}
		/* elseif (isset($_GET["searched"])) {
			print("<h2>Your search for \"" . htmlspecialchars($_GET["searched"]) . "\" gave a single result:</h2>\n");
		} */
		elseif (isset($_GET["rated"]))
			$HTMLOUT .= "<h2>{$lang['details_rating_added']}</h2>\n";

    $s = htmlentities( $row["name"], ENT_QUOTES );
		$HTMLOUT .= "<h1>$s</h1>\n";
  /** free mod for TBDev 09 by pdq **/
    $clr = '#FF6600'; /// font color	
    $freeimg = '<img src="pic/freedownload.gif" border="0" alt="" />';
    $doubleimg = '<img src="pic/doubleseed.gif" border="0" alt="" />';	
	

    {
    $HTMLOUT .= '
    <div id="balloon1" class="balloonstyle">
    Once chosen this torrent will be Freeleech '.$freeimg.' until '.get_date($row['freeslot'], 'DATE').' and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.</div>
    <div id="balloon2" class="balloonstyle">
    Once chosen this torrent will be Doubleseed '.$doubleimg.' until '.get_date($row['doubleslot'], 'DATE').' and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.</div>

    <script type="text/javascript" src="scripts/balloontip.js"></script>';
    /** end **/
    }
    $HTMLOUT .= "<table width='750' border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n";

		$url = "edit.php?id=" . $row["id"];
		if (isset($_GET["returnto"])) {
			$addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
			$url .= $addthis;
			$keepget = $addthis;
		}
		$editlink = "a href=\"$url\" class=\"sublink\"";

//		$s = "<b>" . htmlspecialchars($row["name"]) . "</b>";
//		if ($owned)
//			$s .= " $spacer<$editlink>[Edit torrent]</a>";
//		tr("Name", $s, 1);
    if ($CURUSER["id"] == $row["owner"]) $CURUSER["downloadpos"] = "yes";
        if ($CURUSER["downloadpos"] != "no")
        {
            /** free mod for TBDev 09 by pdq **/
            include ROOT_PATH.'/mods/free_details.php';
            /** end **/
/*
		function hex_esc($matches) {
			return sprintf("%02x", ord($matches[0]));
		}
		$HTMLOUT .= tr("{$lang['details_info_hash']}", preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])));
*/
		$HTMLOUT .= tr("{$lang['details_info_hash']}", $row["info_hash"]);
        }
    else {
        $HTMLOUT .= tr("{$lang['details_download']}", "{$lang['details_dloadpos']}");
    }
		if(!empty($row['youtube']))
	       $HTMLOUT .= tr($lang['details_youtube'],'<object type="application/x-shockwave-flash" style="width:560px; height:340px;" data="'.str_replace('watch?v=','v/',$row['youtube']).'"><param name="movie" value="'.str_replace('watch?v=','v/',$row['youtube']).'" /></object><br/><a 
           href=\''.$row['youtube'].'\' target=\'_blank\'>'.$lang['details_youtube_link'].'</a>',1);
        if (!empty($row["descr"]))
        $HTMLOUT .= "<tr valign=\"top\"><td class=\"rowhead\" width=\"10%\">{$lang['details_description']}</td><td align=\"left\" width=\"100%\"><a href=\"javascript: klappe_news('a')\"><img border=\"0\" src=\"pic/plus.png\" id=\"pica\" alt=\"Show/Hide\" /></a><div id=\"ka\" style=\"display: none;height:350px;overflow: auto;\">". str_replace(array("\n", "  "), array("<br />\n", "&nbsp; "), format_comment( $row["descr"] ))."</div></td></tr>\n";		
    if (get_user_class() >= UC_POWER_USER && $row["nfosz"] > 0)
      $HTMLOUT .= "<tr><td class='rowhead'>{$lang['details_nfo']}</td><td align='left'><a href='viewnfo.php?id=$row[id]'><b>{$lang['details_view_nfo']}</b></a> (" .mksize($row["nfosz"]) . ")</td></tr>\n";
      
		if ($row["visible"] == "no")
			$HTMLOUT .= tr("{$lang['details_visible']}", "<b>{$lang['details_no']}</b>{$lang['details_dead']}", 1);
		if ($moderator)
			$HTMLOUT .= tr("{$lang['details_banned']}", $row["banned"]);
   if ($row["nuked"] == "yes")
    $HTMLOUT .= "<tr><td class='rowhead'><b>Nuked</b></td><td align='left'><img src='{$TBDEV['pic_base_url']}nuked.gif' alt='Nuked' title='Nuked' /></td></tr>\n";
    if (!empty($row["nukereason"]))
    $HTMLOUT .= "<tr><td class='rowhead'><b>Nuke-Reason</b></td><td align='left'>".htmlspecialchars($row["nukereason"])."</td></tr>\n";

		if (isset($row["cat_name"]))
			$HTMLOUT .= tr("{$lang['details_type']}", $row["cat_name"]);
		else
			$HTMLOUT .= tr("{$lang['details_type']}", "{$lang['details_none']}");

		$HTMLOUT .= tr("{$lang['details_last_seeder']}", "{$lang['details_last_activity']}" .get_date( $row['lastseed'],'',0,1));
		$HTMLOUT .= tr("{$lang['details_size']}",mksize($row["size"]) . " (" . number_format($row["size"]) . "{$lang['details_bytes']})");
                ////////////// Similar Torrents mod /////////////////////
        $searchname = substr($row['name'], 0, 8);
        $query1 = str_replace(" ",".",sqlesc("%".$searchname."%"));
        $query2 = str_replace("."," ",sqlesc("%".$searchname."%"));
           $r = mysql_query("SELECT id, name, size, added, seeders, leechers, category FROM torrents WHERE name LIKE {$query1} AND seeders > '0' AND id <> '$id' OR name LIKE {$query2} AND seeders > '0' AND id <> '$id' ORDER BY seeders DESC LIMIT 10") or sqlerr();
           if (mysql_num_rows($r) > 0)
           {
           $torrents = "<table width='100%' class='main' border='1' cellspacing='0' cellpadding='1'>\n" .
           "<tr><td class='colhead'>{$lang['details_name']}</td><td class='colhead' align='center'>{$lang['details_size']}</td><td class='colhead' align='center'>{$lang['details_seeders']}</td><td class='colhead' align='center'>{$lang['details_leechers']}</td></tr>\n";
           while ($a = mysql_fetch_assoc($r))
           {
           $name = $a["name"];
           $torrents .= "<td><a href='details.php?id=" . $a["id"] . "&hit=1'><b>" . htmlspecialchars($name) . "</b></a><br>" . str_replace(",", " ", get_date( $a['added'],'')) . "</br></td><td style='padding: 1px' align='center'>". mksize($a['size']) ."</td><td style='padding: 1px' align='center'>".$a['seeders']."</td><td style='padding: 1px' align='center'>".$a['leechers']."</td></tr>\n";
           }
           $torrents .= "</table>";
           $HTMLOUT .= ("<tr valign='top'><td class='rowhead'>{$lang['details_simillar_torrents']}</td><td align='left'>".$torrents."</td></tr>\n");
           }
        ////////////// Similar Torrents mod /////////////////////
		$s = "";
		$s .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td valign=\"top\" class=embedded>";
		if (!isset($row["rating"])) {
			if ($TBDEV['minvotes'] > 1) {
				$s .= "none yet (needs at least {$TBDEV['minvotes']} votes and has got ";
				if ($row["numratings"])
					$s .= "only " . $row["numratings"];
				else
					$s .= "none";
				$s .= ")";
			}
			else
				$s .= "No votes yet";
		}
		else {
			$rpic = ratingpic($row["rating"]);
			if (!isset($rpic))
				$s .= "invalid?";
			else
				$s .= "$rpic (" . $row["rating"] . " out of 5 with " . $row["numratings"] . " vote(s) total)";
		}
		$s .= "\n";
		$s .= "</td><td class='embedded'>$spacer</td><td valign=\"top\" class='embedded'>";
		if (!isset($CURUSER))
			$s .= "(<a href=\"login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">Log in</a> to rate it)";
		else {
			$ratings = array(
					5 => "Kewl!",
					4 => "Pretty good",
					3 => "Decent",
					2 => "Pretty bad",
					1 => "Sucks!",
	   	);
			if (!$owned || $moderator) {
				if (!empty($row['numratings'])){
$xres = mysql_query("SELECT rating, added FROM ratings WHERE torrent = $id AND user = " . $CURUSER["id"]);
$xrow = mysql_fetch_assoc($xres);
}
if (!empty($xrow))
					$s .= "(you rated this torrent as \"" . $xrow["rating"] . " - " . $ratings[$xrow["rating"]] . "\")";
				else {
					$s .= "<form method=\"post\" action=\"takerate.php\"><input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
					$s .= "<select name=\"rating\">\n";
					$s .= "<option value=\"0\">(add rating)</option>\n";
					foreach ($ratings as $k => $v) {
						$s .= "<option value=\"$k\">$k - $v</option>\n";
					}
					$s .= "</select>\n";
					$s .= "<input type=\"submit\" value=\"Vote!\" />";
					$s .= "</form>\n";
				}
			}
		}
		$s .= "</td></tr></table>";
		$HTMLOUT .= tr("{$lang['details_rating']}", $s, 1);



		$HTMLOUT .= tr("{$lang['details_added']}", get_date( $row['added'],"{$lang['details_long']}"));
		$HTMLOUT .= tr("{$lang['details_views']}", $row["views"]);
		$HTMLOUT .= tr("{$lang['details_hits']}", $row["hits"]);
		$HTMLOUT .= tr("{$lang['details_snatched']}", ($row["times_completed"] > 0 ? "<a href='./snatches.php?id=$id'>$row[times_completed] {$lang['details_times']}</a>" : "0 {$lang['details_times']}"), 1);

    $keepget = "";
		if($row['anonymous'] == 'yes') {
    if ($CURUSER['class'] < UC_UPLOADER)
    $uprow = "<i>Anonymous</i>";
    else
    $uprow = "<i>Anonymous</i> (<a href='userdetails.php?id=$row[owner]'><b>$row[username]</b></a>)";
    }
    else {
		$uprow = (isset($row["username"]) ? ("<a href='userdetails.php?id=" . $row["owner"] . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>{$lang['details_unknown']}</i>");
		}
		if ($owned)
			$uprow .= " $spacer<$editlink><b>{$lang['details_edit']}</b></a>";
		$HTMLOUT .= tr("Upped by", $uprow, 1);

		if ($row["type"] == "multi") {
			if (!isset($_GET["filelist"]))
				$HTMLOUT .= tr("{$lang['details_num_files']}<br /><a href=\"filelist.php?id=$id\" class=\"sublink\">{$lang['details_list']}</a>", $row["numfiles"] . " files", 1);
			else {
				$HTMLOUT .= tr("{$lang['details_num-files']}", $row["numfiles"] . "{$lang['details_files']}", 1);

				
			}
		}

		$HTMLOUT .= tr("{$lang['details_peers']}<br /><a href=\"peerlist.php?id=$id#seeders\" class=\"sublink\">{$lang['details_list']}</a>", $row["seeders"] . " seeder(s), " . $row["leechers"] . " leecher(s) = " . ($row["seeders"] + $row["leechers"]) . "{$lang['details_peer_total']}", 1);
	
    //Reseed request system
    $next_reseed = 0;  
        if ($row["last_reseed"] > 0) 
          $next_reseed = ($row["last_reseed"] + 172800 ); //add 2 days  
          $reseed = "<form method=\"post\" action=\"./takereseed.php\"> 
          <select name=\"pm_what\"> 
          <option value=\"last10\">last10</option> 
          <option value=\"owner\">uploader</option> 
          </select> 
          <input type=\"submit\"  ".(($next_reseed > time()) ? "disabled='disabled'" : "" )." value=\"SendPM\" /> 
          <input type=\"hidden\" name=\"uploader\" value=\"" . $row["owner"] . "\" /> 
          <input type=\"hidden\" name=\"reseedid\" value=\"$id\" /> 
          </form>";      
    $HTMLOUT .= tr("Request reseed", $reseed,1);
    if (isset($_GET["reseed"])) 
        $HTMLOUT.="<h2>PM was sent! Now wait for a seeder !</h2>\n";
    //Reseed Request system
    
    //Modified thanks system	
    $qt = sql_query("SELECT th.userid,u.username,u.class FROM thanks as th INNER JOIN users as u ON u.id=th.userid WHERE th.torrentid={$id} ORDER BY u.class DESC") or sqlerr();
    $list = array(); $th_row = 2;
    if(mysql_num_rows($qt)> 0) {
        while($a = mysql_fetch_assoc($qt)) {
            $list[] = '<a href=\'userdetails.php?id='.$a['userid'].'\'><font style=\'color:#'.get_user_class_color($a['class']).'\'>'.$a['username'].'</font></a>';
            $ids[] = $a['userid'];
        }
    $th_row = (in_array($CURUSER['id'],$ids) ? 1 : 2);
    }
    $HTMLOUT .= "<tr><td class='rowhead' rowspan='{$th_row}'>{$lang['details_thanks']}</td><td>".(count($list) == 0 ? $lang['details_thanks_no'] : join(', ',$list))."</td></tr>";
    if($th_row == 2)  
        $HTMLOUT .= "<tr><td><form action='thanks.php' method='post'><input type='submit' name='submit' value='{$lang['details_thanks_say']}' /><input type='hidden' name='torrentid' value='{$id}' /></form></td></tr>";  
    elseif (isset($_GET['thanks']))
        $HTMLOUT .= "<h2>{$lang['details_thanks_added']}</h2>\n";
    elseif (isset($_GET["rated"]))
        $HTMLOUT .= "<h2>{$lang['details_rating_added']}</h2>\n";
    //Thanks system end

    //==Report Torrent
    $HTMLOUT .= tr("{$lang['details_report']}", "<form action='report.php?type=Torrent&id=$id' method='post'><input class='button' type='submit' name='submit' value='Report This Torrent' /> {$lang['details_report1']} <a href='rules.php'>{$lang['details_report2']}</a></form>", 1);
    //==Report torrent End
    $HTMLOUT .= "</table>";

    //stdhead("Comments for torrent \"" . $row["name"] . "\"");
	$HTMLOUT .= "<h1>{$lang['details_comments']}<a href='details.php?id=$id'>" . htmlentities( $row["name"], ENT_QUOTES ) . "</a></h1>\n";

    $HTMLOUT .= "<p><a name=\"startcomments\"></a></p>\n";

    $commentbar = "<p align=center><a class=index href=comment.php?action=add&amp;tid=$id>Add a full comment</a></p>\n";

	$quickcomment = "<table style='border:1px solid #000000;'><tr>".
 	 "<td style='padding:10px;text-align:center;'><p><b>Quick Comment</b><br />".
 	 "<form name=comment method=\"post\" action=\"comment.php?action=add\">".
 	 "<textarea name=\"body\" rows=\"4\" cols=\"50\"></textarea>".
 	 "<input type=\"hidden\" name=\"tid\" value=\"$id\"/><br />".
 	 "<input type=\"submit\" class=btn value=\"Submit\" />".
 	 "</form></p></td></tr></table>";
     /*$quickcomment= "<br />
<p align='center'><b>Quick Comment</b></p>\n
<form name='compose' method='post' action='comment.php?action=add'>
<table><tr><td>
<input type='hidden' name='tid' value='$id' />
".textbbcode("compose", "body")."<br />
<input type='submit' class='btn' value='Submit' /><br />
</form>\n";*/



    $count = $row['comments'];

    if (!$count) 
    {
      $HTMLOUT .= "<p align='center'>{$lang['details_no_comment']}</h2></p>\n";
      $HTMLOUT .= "<p align='center'>$quickcomment</p>";
      //$HTMLOUT .= "$quickcomment";
	}

    else 
    {
		$pager = pager(20, $count, "details.php?id=$id&amp;", array('lastpagedefault' => 1));

		$subres = mysql_query("SELECT disablecom, comments.id, text, user, comments.added, comments.anonymous, torrent, editedby, editedat, avatar, offavatar, av_w, av_h, warned, username, title, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $id ORDER BY comments.id ".$pager['limit']) or sqlerr(__FILE__, __LINE__);
		
		$allrows = array();
		while ($subrow = mysql_fetch_assoc($subres))
			$allrows[] = $subrow;

		$HTMLOUT .= $quickcomment;
        $HTMLOUT .= $commentbar;
		$HTMLOUT .= $pager['pagertop'];

		$HTMLOUT .= commenttable($allrows);

		$HTMLOUT .= $pager['pagerbottom'];
	}

    $HTMLOUT .= $commentbar;

///////////////////////// HTML OUTPUT ////////////////////////////
    where ("{$lang['details_viewtorrents']} <b><a href='{$TBDEV['baseurl']}/details.php?id=$id'>".htmlspecialchars($row["name"])."</a></b>",$CURUSER["id"]);
    print stdhead("{$lang['details_details']}\"" . htmlentities($row["name"], ENT_QUOTES) . "\"") . $HTMLOUT . stdfoot();

?>