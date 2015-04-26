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

require_once("include/bittorrent.php");
require_once("include/pager_functions.php");
require_once ("include/user_functions.php");
require_once ("include/torrenttable_functions.php");
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('torrenttable_functions') );

$htmlout='';

function sharetable($res, $variant = "index") {
	global $TBDEV, $CURUSER, $lang;
    $htmlout='';
   
    
$htmlout.="<table border='1' cellspacing='0' cellpadding='5'>
<tr>
<td class='colhead' align='center'>Type</td>
<td class='colhead' align='left'>Name</td>";

$userid = (int)$_GET['id'];
if ($CURUSER['id'] == $userid)
$htmlout.= ($variant == 'index' ? '<td class="colhead" align="center">DL</td><td class="colhead" align="right">' : '').'Delete</td>';
else
$htmlout.= ($variant == 'index' ? '<td class="colhead" align="center">DL</td><td class="colhead" align="right">' : '').'Bookmark</td>';
   

   if ($variant == "mytorrents")
   {
   $htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_edit"]}</td>\n";
   $htmlout .= "<td class='colhead' align='center'>{$lang["torrenttable_visible"]}</td>\n";
   }

   $htmlout .= "<td class='colhead' align='right'><img src='".$TBDEV['pic_base_url']."files.gif'  border='0' alt='File(s)' title='File(s)' /></td>
   <td class='colhead' align='right'><img src='".$TBDEV['pic_base_url']."cmt.gif'  border='0' alt='Comments' title='Comments' /></td>
   <td class='colhead' align='center'><img src='".$TBDEV['pic_base_url']."added.gif'  border='0' alt='Added' title='Added' /></td>
   <td class='colhead' align='center'>{$lang["torrenttable_size"]}</td>
   <td class='colhead' align='center'><img src='".$TBDEV['pic_base_url']."snatch.png'  border='0' alt='Snatched' title='Snatched' /></td>
   <td class='colhead' align='right'><img src='".$TBDEV['pic_base_url']."arrowup.png'  border='0' alt='Seeders' title='Seeders' /></td>
   <td class='colhead' align='right'><img src='".$TBDEV['pic_base_url']."arrowdown.png'  border='0' alt='Leechers' title='Leechers' /></td>";

if ($variant == 'index')
   $htmlout .= "<td class='colhead' align='center'><img src='".$TBDEV['pic_base_url']."upped.gif'  border='0' alt='Upped by' title='Upped by' /></td>\n";

    $htmlout .= "</tr>\n";

    while ($row = mysql_fetch_assoc($res)) 
    {
        $id = $row["id"];
        $htmlout .= "<tr>\n";

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

        $dispname = htmlspecialchars($row["name"]);
        $htmlout .= "<td align='left'><a href='details.php?";
        if ($variant == "mytorrents")
            $htmlout .= "returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;";
        $htmlout .= "id=$id";
        if ($variant == "index")
            $htmlout .= "&amp;hit=1";
        
      $htmlout .= "'><b>$dispname</b></a>&nbsp;</td>";
      $htmlout.= ($variant == "index" ? "<td align='center'><a href=\"download.php?torrent=".$id."\"><img src='".$TBDEV['pic_base_url']."download.gif' border='0' alt='Download Bookmark!' title='Download Bookmark!' /></a></td>" : "");
      
      $bm = sql_query("SELECT * FROM bookmarks WHERE torrentid=$id && userid=$CURUSER[id]");
      $bms = mysql_fetch_assoc($bm);
      $bookmarked = (empty($bms)?'<a href=\'bookmark.php?torrent=' . $id . '&amp;action=add\'><img src=\'' . $TBDEV['pic_base_url'] . 'bookmark.gif\' border=\'0\' alt=\'Bookmark it!\' title=\'Bookmark it!\'></a>':'<a href="bookmark.php?torrent=' . $id . '&amp;action=delete"><img src=\'' . $TBDEV['pic_base_url'] . 'aff_cross.gif\' border=\'0\' alt=\'Delete Bookmark!\' title=\'Delete Bookmark!\' /></a>');
      $htmlout.= ($variant == "index" ? "<td align='center'>{$bookmarked}</td>" : "");
      
        if ($variant == "mytorrents")
            $htmlout .= "</td><td align='center'><a href='edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id={$row['id']}'>".$lang["torrenttable_edit"]."</a>\n";
        
        if ($variant == "mytorrents") 
        {
            $htmlout .= "<td align='right'>";
            if ($row["visible"] == "no")
                $htmlout .= "<b>".$lang["torrenttable_not_visible"]."</b>";
            else
                $htmlout .= "".$lang["torrenttable_visible"]."";
            $htmlout .= "</td>\n";
        }
        if ($row["type"] == "single") 
            $htmlout .= "<td align='right'>{$row["numfiles"]}</td>\n";
        else {
            if ($variant == "index")
                $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>" . $row["numfiles"] . "</a></b></td>\n";
            else
                $htmlout .= "<td align='right'><b><a href='filelist.php?id=$id'>" . $row["numfiles"] . "</a></b></td>\n";
        }
        if (!$row["comments"])
            $htmlout .= "<td align='right'>{$row["comments"]}</td>\n";
        else {
            if ($variant == "index")
                $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . $row["comments"] . "</a></b></td>\n";
            else
                $htmlout .= "<td align='right'><b><a href='details.php?id=$id&amp;page=0#startcomments'>" . $row["comments"] . "</a></b></td>\n";
        }
        $htmlout .= "<td align='center'><span style='white-space: nowrap;'>" . str_replace(",", "<br />", get_date( $row['added'],'')) . "</span></td>\n";
    
    $htmlout .= "
    <td align='center'>" . str_replace(" ", "<br />", mksize($row["size"])) . "</td>\n";

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
                $htmlout .= "<td align='right'><b><a class='" . linkcolor($row["seeders"]) . "' href='peerlist.php?id=$id#seeders'>{$row["seeders"]}</a></b></td>\n";
        }
        else
            $htmlout .= "<td align='right'><span class='" . linkcolor($row["seeders"]) . "'>" . $row["seeders"] . "</span></td>\n";

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
        
       if ($variant == "index")
       $htmlout .= "<td align='center'>" . (isset($row["username"]) ? ("<a href='userdetails.php?id=" . $row["owner"] . "'><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>(".$lang["torrenttable_unknown_uploader"].")</i>") . "</td>\n";
       $htmlout .= "</tr>\n";
       }
       $htmlout .= "</table>\n";
       return $htmlout;
       }

//==Sharemarks
$userid = isset($_GET['id']) ? (int)$_GET['id'] : '';

if (!is_valid_id($userid))
stderr("Error", "Invalid ID.");

$res = sql_query("SELECT id, username FROM users WHERE id = $userid") or sqlerr();
$arr = mysql_fetch_array($res);
$htmlout.="<h1>Sharemarks for <a href=\"userdetails.php?id=".$userid."\">".$arr['username']."</a></h1>";
$htmlout.="<b><a href=\"bookmarks.php\">My Bookmarks</a></b>";
	

$res = sql_query("SELECT COUNT(id) FROM bookmarks WHERE userid = $userid");
$row = mysql_fetch_array($res);
$count = $row[0];

$torrentsperpage = $CURUSER["torrentsperpage"];
if (!$torrentsperpage)
$torrentsperpage = 25;

if ($count) {
$pager = pager($torrentsperpage, $count, "sharemarks.php?");
$query1 = "SELECT bookmarks.id as bookmarkid, users.username, users.id as owner, torrents.id, torrents.name, torrents.type, torrents.comments, torrents.leechers, torrents.seeders, categories.name AS cat_name, categories.image AS cat_pic, torrents.save_as, torrents.numfiles, torrents.added, torrents.filename, torrents.size, torrents.views, torrents.visible, torrents.hits, torrents.times_completed, torrents.category FROM bookmarks LEFT JOIN torrents ON bookmarks.torrentid = torrents.id LEFT JOIN users on torrents.owner = users.id LEFT JOIN categories ON torrents.category = categories.id WHERE bookmarks.userid = $userid AND bookmarks.private = 'no' ORDER BY torrents.id DESC {$pager['limit']}" or sqlerr(__FILE__, __LINE__);
$res = sql_query($query1) or sqlerr();
}

if ($count) {
$htmlout .= $pager['pagertop'];
$htmlout .= sharetable($res, "index", TRUE);
$htmlout .= $pager['pagerbottom'];
}

/** Legand table for sharemarks **/
    $htmlout .="<table width='320px' cellspacing='5' cellpadding='5' border='0' align='center'>
		<thead><tr>
		<td align='center' colspan='8' class='colhead3' title='Sharemark page icon legand'>Icon legend</td>
		</tr></thead>
        <tbody><tr>
		<td align='center'><img src='{$TBDEV['pic_base_url']}aff_cross.gif' alt='Delete Bookmark' title='Delete Bookmark'/></td>
        <td>Delete Bookmark</td>
		<td align='center'><img src='{$TBDEV['pic_base_url']}key.gif' alt='title='Private Bookmark' title='Private Bookmark'/></td>
		<td>Bookmark is Private</td>
		</tr>
		<tr>
		<td align='center'><img title='Download!' alt='Download!' src='{$TBDEV['pic_base_url']}download.gif'></td>
		<td>Downlaod Torrent</td>
		<td align='center'><img src='{$TBDEV['pic_base_url']}public.gif' alt='Public Bookmark' title='Public Bookmark'></td>
		<td>Bookmark is Public<br></td>
		</tr>
		</tbody></table><br />";
        
print stdhead("Sharemarks for " . $arr['username']) . $htmlout . stdfoot();
?>