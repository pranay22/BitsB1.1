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

function linkcolor($num) {
    if (!$num)
        return "red";
//    if ($num == 1)
//        return "yellow";
    return "green";
}

function torrenttable($res, $variant = "index") {
    global $CURUSER, $TBDEV, $lang, $free;

    $wait = 0;
    $htmlout = '';
    
    //We don't need the wait time feature just now...
    /*if ($CURUSER["class"] < UC_VIP)
    {
      $gigs = $CURUSER["uploaded"] / (1024*1024*1024);
      $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
      if ($ratio < 0.5 || $gigs < 5) $wait = 48;
      elseif ($ratio < 0.65 || $gigs < 6.5) $wait = 24;
      elseif ($ratio < 0.8 || $gigs < 8) $wait = 12;
      elseif ($ratio < 0.95 || $gigs < 9.5) $wait = 6;
      else $wait = 0;
    }
    */

$count_get = 0;
    $oldlink = $char = $description = $type = $sort = $row = '';
    foreach ($_GET as $get_name => $get_value) {
        $get_name = strip_tags(str_replace(array("\"", "'"), array("", ""), $get_name));
        $get_value = strip_tags(str_replace(array("\"", "'"), array("", ""), $get_value));
        if ($get_name != "sort" && $get_name != "type") {
            if ($count_get > 0) {
                $oldlink = $oldlink . "&amp;" . $get_name . "=" . $get_value;
            } else {
                $oldlink = ($oldlink) . $get_name . "=" . $get_value;
            }
            $count_get++;
        }
    }

    if ($count_get > 0) {
        $oldlink = $oldlink . "&amp;";
    }
    
    $links = array('link1','link2','link3','link4','link5','link6','link7','link8','link9');
    $i =1;
    foreach($links as $link) {
    if(isset($_GET['sort']) && $_GET['sort'] == $i)
	  $$link = (isset($_GET['type']) && $_GET['type'] == 'desc') ? 'asc' : 'desc';
    else
	  $$link = 'desc';
    $i++;
    }
    
   $htmlout .= "<table width='95%' border='1' cellspacing='0' cellpadding='4'>
   <thead><tr><td align='center' style='text-align:center;' class='colhead' colspan='11' title='Normal Torrents'>Normal Torrents</td></tr></thead>
   <tr>
   <td align='center' class='subheader'><b title='{$lang["torrenttable_type"]}'>{$lang["torrenttable_type"]}</b></td>
   <td align='left' class='subheader'><a class='altlink' href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=1&amp;type={$link1}' title='{$lang["torrenttable_name"]}'>{$lang["torrenttable_name"]}</a></td>";
   if ($variant == "index")
    $htmlout .="
    <td align='center' class='subheader'><b title='Download'>DL</b></td>
    <td align='center' class='subheader'><a href='".$TBDEV['baseurl']."/bookmarks.php'><img src='".$TBDEV['pic_base_url']."bookmark.gif'  border='0' alt='Bookmark' title='Bookmark' /></a></td>"
; 
    //Wait time: disabled
   /*if ($wait)
   {
   $htmlout .="<td class='colhead' align='center'>{$lang["torrenttable_wait"]}</td>\n";
   } */   

    if ($variant == "mytorrents")
    {
  	$htmlout .= "<td align='center' class='subheader'><b title='{$lang["torrenttable_edit"]}'>{$lang["torrenttable_edit"]}</b></td>\n";
    $htmlout .= "<td align='center' class='subheader'><b title='{$lang["torrenttable_visible"]}?'>{$lang["torrenttable_visible"]}?</b></td>\n";
	}

   $htmlout .= "<td align='center' class='subheader'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=2&amp;type={$link2}'><b title='File(s)'>Files</b></a></td>
   <td align='center' class='subheader'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=3&amp;type={$link3}'><img src='".$TBDEV['pic_base_url']."cmt.gif'  border='0' alt='{$lang["torrenttable_comments"]}' title='{$lang["torrenttable_comments"]}' /></a></td>
   <!--<td class='colhead' align='center'>{$lang["torrenttable_rating"]}</td>-->
   <!---<td class='colhead' align='center'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=4&amp;type={$link4}'><img src='".$TBDEV['pic_base_url']."added.gif'  border='0' alt='{$lang["torrenttable_added"]}' title='{$lang["torrenttable_added"]}' /></a></td>-->
   <!---<td class='colhead' align='center'>{$lang["torrenttable_ttl"]}</td>-->
   <td align='center' class='subheader'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=5&amp;type={$link5}'><b title='{$lang["torrenttable_size"]}'>{$lang["torrenttable_size"]}</b></a></td>
   <td align='center' class='subheader'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=6&amp;type={$link6}'><img src='".$TBDEV['pic_base_url']."snatch.png'  border='0' alt='{$lang["torrenttable_snatched"]}' title='{$lang["torrenttable_snatched"]}' /></a></td>
   <td align='center' class='subheader'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=7&amp;type={$link7}'><img src='".$TBDEV['pic_base_url']."arrowup.png'  border='0' alt='{$lang["torrenttable_seeders"]}' title='{$lang["torrenttable_seeders"]}' /></a></td>
   <td align='center' class='subheader'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=8&amp;type={$link8}'><img src='".$TBDEV['pic_base_url']."arrowdown.png'  border='0' alt='{$lang["torrenttable_leechers"]}' title='{$lang["torrenttable_leechers"]}' /></a></td>";

   if ($variant == 'index')
   $htmlout .= "<td align='center' class='subheader'><a href='{$TBDEV['baseurl']}/browse.php?{$oldlink}sort=9&amp;type={$link9}'><img src='".$TBDEV['pic_base_url']."upped.gif'  border='0' alt='{$lang["torrenttable_uppedby"]}' title='{$lang["torrenttable_uppedby"]}' /></a></td>\n";
    $htmlout .= "</tr>\n";

    while ($row = mysql_fetch_assoc($res)) 
    {
        if (($CURUSER['split'] == "yes") && ($_SERVER["REQUEST_URI"] == "/browse.php") && !isset($_GET["page"])) {
 	
 	$day_added = $row['added'];
 	$day_show = date($day_added);
 	$thisdate = date('M d Y', $day_show);



    /** If date already exist, disable $cleandate varible **/
        if (isset($prevdate) && $thisdate == $prevdate) {
 	      $cleandate = '';
    /** If date does not exist, make some varibles **/
        }else{
            $day_added = "{$lang['torrenttable_upped']}" .date('l d M Y',$row['added']); // You can change this to something else
            $cleandate = "<tr><td colspan='15'><b>$day_added</b></td></tr>\n"; // This also...
        }
    /** Prevent that "torrents added..." wont appear again with the same date **/
        $prevdate = $thisdate;
        $man = array('Jan' => 'January',
        'Feb' => 'February',
        'Mar' => 'March',
        'Apr' => 'April',
        'May' => 'May',
        'Jun' => 'June',
        'Jul' => 'July',
        'Aug' => 'August',
        'Sep' => 'September',
        'Oct' => 'October',
        'Nov' => 'November',
        'Dec' => 'December'
        );
        foreach($man as $eng => $ger){
	       $cleandate = str_replace($eng, $ger,$cleandate);
        }
        $dag = array('Mon' => 'Monday',
        'Tues' => 'Tuesday',
        'Wednes' => 'Wednesday',
        'Thurs' => 'Thursday',
        'Fri' => 'Friday',
        'Satur' => 'Saturday',
        'Sun' => 'Sunday'
        );
        foreach($dag as $eng => $ger){
	       $cleandate = str_replace($eng.'day', $ger.'',$cleandate);
        }
        if ($row["sticky"] == "no") // delete this line if you dont have sticky torrents or you 
            $htmlout .= $cleandate."\n";
    }
        $id = $row["id"];
        $highlight = ($row["id"] %2>"" ? " bgcolor=#f0f5f6" : "");
        $htmlout .= "<tr $highlight class='browse' >\n";

        $htmlout .= "<td align='center' style='padding: 0px'>";
        if (isset($row["cat_name"])) 
        {
            $htmlout .= "<a href='browse.php?cat={$row['category']}'>";
            if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
                $htmlout .= "<img border='0' src='{$TBDEV['pic_base_url']}caticons/{$row['cat_pic']}' alt='{$row['cat_name']}' />";
            else
            {
                $htmlout .= $row["cat_name"];
            }
            $htmlout .= "</a>";
        }
        else
        {
            $htmlout .= "-";
        }
        $htmlout .= "</td>\n";

        $added = "" . str_replace(",", "", get_date( $row['added'],'')) . "";
        $dispname = htmlspecialchars($row["name"]);
        
        $htmlout .= "<td align='left'><a href='details.php?";
        if ($variant == "mytorrents")
            $htmlout .= "returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;";
        $htmlout .= "id=$id";
        if ($variant == "index")
            $htmlout .= "&amp;hit=1";
        $nuked = ($row["nuked"] == "yes" ? "<img src='{$TBDEV['pic_base_url']}nuked.gif' style='border:none' alt='Nuked' align='right' title='Reason :".htmlspecialchars($row["nukereason"])."' />" : "");
        $sticky = ($row['sticky']=="yes" ? "<img src='{$TBDEV['pic_base_url']}sticky.gif' border='0' alt='Sticky' title='Sticky !' />" : "");
       /** FREE Torrent **/
            $free_tag = ($row['free'] != 0 ? ' <a class="info" href="#">
            <img src="'.$TBDEV['baseurl'].'/pic/tag_free.png" alt="" />
            <span>'. ($row['free'] > 1 ? '
            Expires: '.get_date($row['free'], 'DATE').'<br />
        ('.mkprettytime($row['free'] - TIME_NOW).' to go)<br />' : 'Unlimited<br />').'</span></a>' : '');

        /** Freeslot Slot in Use **/
        $isdlfree = ($row['tid'] == $id && $row['uid'] == $CURUSER['id'] && 
            $row['freeslot'] != 0 ? '<a class="info" href="#">
            <img src="'.$TBDEV['baseurl'].'/pic/freedownload.gif" alt="" />
            <span>Freeleech slot in use<br />'.($row['freeslot'] != 0 ? ($row['freeslot'] > 1 ? '
            Expires: '.get_date($row['freeslot'], 'DATE').'<br />
        ('.mkprettytime($row['freeslot'] - TIME_NOW).' to go)<br />' : 'Unlimited<br />') : '').'</span></a>' : '');

        /** Double Upload Slot in Use **/
        $isdouble = ($row['tid'] == $id && $row['uid'] == $CURUSER['id'] && 
            $row['doubleup'] != 0 ? ' <a class="info" href="#">
            <img src="'.$TBDEV['baseurl'].'/pic/doubleseed.gif" alt="" />
            <span>Double Upload slot in use<br />'.($row['doubleup'] != 0 ? ($row['doubleup'] > 1 ? '
            Expires: '.get_date($row['doubleup'], 'DATE').'<br />
        ('.mkprettytime($row['doubleup'] - TIME_NOW).' to go)<br />' : 'Unlimited<br />') : '').'</span></a>' : '');

        $htmlout .= "'>$sticky <b>$dispname</b></a> ".($row['added'] >= $CURUSER['last_browse'] ? " <img src='{$TBDEV['pic_base_url']}tag_new.png' border='0' alt='New !' title='New !' />" : "")." ".$free_tag."&nbsp;$nuked&nbsp<br /><font class=small>($added)</font>".$isdlfree.$isdouble;


				if ($wait)
				{
				  $elapsed = floor((time() - $row["added"]) / 3600);
	        if ($elapsed < $wait)
	        {
	          $color = dechex(floor(127*($wait - $elapsed)/48 + 128)*65536);
	          $htmlout .= "<td align='center'><span style='white-space: nowrap;'><a href='faq.php#dl8'><font color='$color'>" . number_format($wait - $elapsed) . " ".$lang["torrenttable_wait_h"]."</font></a></span></td>\n";
	        }
	        else
	          $htmlout .= "<td align='center'><span style='white-space: nowrap;'>{$lang["torrenttable_wait_none"]}</span></td>\n";
        }

/*
        if ($row["nfoav"] && get_user_class() >= UC_POWER_USER)
          print("<a href='viewnfo.php?id=$row[id]''><img src='{$TBDEV['pic_base_url']}viewnfo.gif" border='0' alt='".$lang["torrenttable_view_nfo_alt"]."' /></a>\n");
        */
        if ($variant == "index")
            $htmlout .= "</td><td align='center'><a href='download.php?torrent=$id'><img src='{$TBDEV['pic_base_url']}download.gif' border='0' alt='".$lang["torrenttable_download_alt"]."' /></a>\n";

        else 
        if ($variant == "mytorrents")
            $htmlout .= "</td><td align='center'><a href='edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id={$row['id']}'><img src='{$TBDEV['pic_base_url']}edit.png' border='0' alt='".$lang["torrenttable_edit"]."' /></a>\n";
        $htmlout .= "</td>\n";
        
        if ($variant == "mytorrents") 
        {
            $htmlout .= "<td align='center'>";
            if ($row["visible"] == "no")
                $htmlout .= "<font color=#990000><b>".$lang["torrenttable_no_visible"]."</b></font>";
            else
                $htmlout .= "<font color=#009900><b>".$lang["torrenttable_yes_visible"]."</b></font>";
            $htmlout .= "</td>\n";
        }
        $bookmarked = (!isset($row['bookmark']) ? "<a href='bookmark.php?torrent=" . $id . "&amp;action=add'><img src='" . $TBDEV['pic_base_url'] . "bookmark.gif' border='0' alt='Bookmark it!' title='Bookmark it!' /></a>":"<a href='bookmark.php?torrent=" . $id . "&amp;action=delete'><img src='" . $TBDEV['pic_base_url'] . "plus2.gif' border='0' alt='Delete Bookmark!' title='Delete Bookmark!' /></a>");
        if ($variant == "index")  
        $htmlout.="<td align='right'>{$bookmarked}</td>";

        if ($row["type"] == "single")
        {
            $htmlout .= "<td align='right'>{$row["numfiles"]}</td>\n";
        }
        else 
        {
            if ($variant == "index")
            {
                $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>" . $row["numfiles"] . "</a></b></td>\n";
            }
            else
            {
                $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>" . $row["numfiles"] . "</a></b></td>\n";
            }
        }

        if (!$row["comments"])
        {
            $htmlout .= "<td align='right'>{$row["comments"]}</td>\n";
        }
        else 
        {
            if ($variant == "index")
            {
                $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . $row["comments"] . "</a></b></td>\n";
            }
            else
            {
                $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;page=0#startcomments'>" . $row["comments"] . "</a></b></td>\n";
            }
        }

/*
        print("<td align='center'>");
        if (!isset($row["rating"]))
            print("---");
        else {
            $rating = round($row["rating"] * 2) / 2;
            $rating = ratingpic($row["rating"]);
            if (!isset($rating))
                print("---");
            else
                print($rating);
        }
        print("</td>\n");
*/
        //$htmlout .= "<td align='center'><span style='white-space: nowrap;'>" . str_replace(",", "<br />", get_date( $row['added'],'')) . "</span></td>\n";
        
	/*	$ttl = (2800*24) - floor((time() - $row["added"]) / 3600);
		
		if ($ttl == 1) 
                   $ttl .= "<br />".$lang["torrenttable_hour_singular"].""; 
                else 
                   $ttl .= "<br />".$lang["torrenttable_hour_plural"]."";
    
    $htmlout .= "<td align='center'>$ttl</td>\n
    */
    $htmlout .= "<td align='center'>" . str_replace(" ", "<br />", mksize($row["size"])) . "</td>\n";
        
//        print("<td align='right'>" . $row["views"] . "</td>\n");
//        print("<td align='right'>" . $row["hits"] . "</td>\n");

        
        if ($row["times_completed"] != 1)
          $_s = "".$lang["torrenttable_time_plural"]."";
        else
          $_s = "".$lang["torrenttable_time_singular"]."";
        $htmlout .= "<td align='center'><a href='snatches.php?id=$id'>" . number_format($row["times_completed"]) . "<br />$_s</a></td>\n";
        if ($row["seeders"]) 
        {
            if ($variant == "index")
            {
               if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"]; else $ratio = 1;
                $htmlout .= "<td align='right'><b><a href='peerlist.php?id=$id#seeders'>
                <font color='" .get_slr_color($ratio) . "'>{$row["seeders"]}</font></a></b></td>\n";
            }
            else
            {
                $htmlout .= "<td align='right'><b><a class='" . linkcolor($row["seeders"]) . "' href='peerlist.php?id=$id#seeders'>{$row["seeders"]}</a></b></td>\n";
            }
        }
        else
        {
            $htmlout .= "<td align='right'><span class='" . linkcolor($row["seeders"]) . "'>" . $row["seeders"] . "</span></td>\n";
        }

        if ($row["leechers"]) 
        {
            if ($variant == "index")
                $htmlout .= "<td align='right'><b><a href='peerlist.php?id=$id#leechers'>" .
                   number_format($row["leechers"]) . "</a></b></td>\n";
            else
                $htmlout .= "<td align='right'><b><a class='" . linkcolor($row["leechers"]) . "' href='peerlist.php?id=$id#leechers'>{$row["leechers"]}</a></b></td>\n";
        }
        else
            $htmlout .= "<td align='right'>0</td>\n";

if ($variant == "index") {
       if ($row["anonymous"] == "yes") {
       $htmlout .= "<td align='center'><i>Anonymous</i></td>\n";
       }
       else {
       $htmlout .= "<td align='center'>" . (isset($row["username"]) ? ("<a href='{$TBDEV['baseurl']}/userdetails.php?id=" . $row["owner"] . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>(".$lang["torrenttable_unknown_uploader"].")</i>") . "</td>\n";
       }
       }
       $htmlout .= "</tr>\n";
       }
       $htmlout .= "</table>\n";
       return $htmlout;
       }   
       //$htmlout .= "</tr>\n";
    

function commenttable($rows, $variant = 'torrent') {
	global $CURUSER, $TBDEV;
	
	$lang = load_language( 'torrenttable_functions' );
	
	$htmlout = '';
	$count = 0;
	$variant_options = array('torrent' => 'details', 
                             'request' => 'viewrequests', 
                             //'user'    => 'userdetails'
                             );
                       
    if (isset($variant_options[$variant])) 
        $locale_link = $variant_options[$variant];
    else
       return;

     $extra_link = ($variant == 'request' ? '&amp;type=request' : '');

	$htmlout .= begin_main_frame();
	$htmlout .= begin_frame();
	
	foreach ($rows as $row) {

		$htmlout .= "<p class='sub'>#{$row["id"]} {$lang["commenttable_by"]} ";
    if (isset($row["username"])) {
        if ($row['anonymous'] == 'yes') {
            $htmlout .= ($CURUSER['class'] >= UC_MODERATOR ? 'Anonymous - 
                Posted by: <b>'.$row['username'].'</b> 
                ID: '.$row['user'].'' : 'Anonymous').' ';
            } else {
    			$title = $row["title"];
    			if ($title == "")
    				$title = get_user_class_name($row["class"]);
    			else
    				$title = htmlspecialchars($title);
                $htmlout .= "<a name='comm{$row["id"]}' href='userdetails.php?id={$row["user"]}'><b>" .
            	htmlspecialchars($row["username"]) . "</b></a>" . ($row["donor"] == "yes" ? "<img src='{$TBDEV['pic_base_url']}star.gif' alt='".$lang["commenttable_donor_alt"]."' />" : "") . ($row["warned"] == "yes" ? "<img src=".
        			"'{$TBDEV['pic_base_url']}warned.gif' alt='".$lang["commenttable_warned_alt"]."' />" : "") . " ($title)\n";
    		}
        }
		else
   		$htmlout .= "<a name='comm{$row["id"]}'><i>(".$lang["commenttable_orphaned"].")</i></a>\n";
    
		$htmlout .= get_date( $row['added'],'');
		$htmlout .= ($row["user"] == $CURUSER["id"] || $CURUSER['class'] >= UC_MODERATOR ? "- [<a href='comment.php?action=edit&amp;cid=".$row['id'].$extra_link."&amp;tid=".$row[$variant]."'>".$lang["commenttable_edit"]."</a>]" : "") .
			($CURUSER['class'] >= UC_MODERATOR ? "- [<a href='comment.php?action=delete&amp;cid=".$row['id'].$extra_link."&amp;tid=".$row[$variant]."'>".$lang["commenttable_delete"]."</a>]" : "") .
			($row["editedby"] && $CURUSER['class'] >= UC_MODERATOR ? "- [<a href='comment.php?action=vieworiginal&amp;cid=".$row['id'].$extra_link."&amp;tid=".$row[$variant]."'>".$lang["commenttable_view_original"]."</a>]" : "") . "</p>\n";
		$avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
		
		if (!$avatar)
			$avatar = "{$TBDEV['pic_base_url']}default_avatar.gif";
		$text = format_comment($row["text"]);
    if ($row["editedby"])
    	$text .= "<p><font size='1' class='small'>".$lang["commenttable_last_edited_by"]." <a href='userdetails.php?id={$row['editedby']}'><b>{$row['username']}</b></a> ".$lang["commenttable_last_edited_at"]." ".get_date($row['editedat'],'DATE')."</font></p>\n";
		$htmlout .= begin_table(true);
		$htmlout .= "<tr valign='top'>\n";
		$htmlout .= "<td align='center' width='150' style='padding: 0px'><img width='{$row['av_w']}' height='{$row['av_h']}' src='{$avatar}' alt='' /></td>\n";
		$htmlout .= "<td class='text'>$text</td>\n";
		$htmlout .= "</tr>\n";
     $htmlout .= end_table();
  }
  
	$htmlout .= end_frame();
	$htmlout .= end_main_frame();
	
	return $htmlout;
}


?>