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
|   Normal torrent browse pae v0.9
+------------------------------------------------
**/

ob_start("ob_gzhandler");

require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/torrenttable_functions.php";
require_once "include/pager_functions.php";
include ROOT_PATH."/mods/freeslots_inc.php";

dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('browse'), load_language('torrenttable_functions') );
    
    parked();
    
    if (isset($_GET['clear_new']) && $_GET['clear_new'] == '1'){
        sql_query("UPDATE users SET last_browse=".TIME_NOW." where id=".$CURUSER['id']);
        header("Location: {$TBDEV['baseurl']}/browse.php");
    }
    $mod = $CURUSER["class"] >= UC_MODERATOR;
    ////Start IP logger in browse.php//// 
    $added = sqlesc(time()); 
    $ip = sqlesc(getip()); 
    $userid = $CURUSER['id']; 
    $res = sql_query("SELECT * FROM ips WHERE ip = $ip AND userid = $userid") or die(mysql_error()); 
    if (mysql_num_rows($res) == 0 ) { 
        sql_query("INSERT INTO ips (userid, ip, lastbrowse, type) VALUES ($userid, $ip , $added, 'browse')") or die(mysql_error()); 
    } 
    else { 
        sql_query("UPDATE ips SET lastbrowse = $added where ip=$ip AND userid = $userid") or sqlerr(__FILE__, __LINE__); 
    } 
    //// End Ip logger /////
    
    $HTMLOUT = '';
    $HTMLOUT .= "<script language='Javascript' src='scripts/suggest.js' type='text/javascript'></script>";
    
    $cats = genrelist();

    if(isset($_GET["search"])) 
    {
      $searchstr = unesc($_GET["search"]);
      $cleansearchstr = searchfield($searchstr);
      if (empty($cleansearchstr))
        unset($cleansearchstr);
    }
    if (isset($_GET['sort']) && isset($_GET['type'])) {
    $column = '';
    $ascdesc = '';

    switch ($_GET['sort']) {
        case '1': $column = "name";
            break;
        case '2': $column = "numfiles";
            break;
        case '3': $column = "comments";
            break;
        case '4': $column = "added";
            break;
        case '5': $column = "size";
            break;
        case '6': $column = "times_completed";
            break;
        case '7': $column = "seeders";
            break;
        case '8': $column = "leechers";
            break;
        case '9': $column = "owner";
            break;
        default: $column = "id";
            break;
    }

    switch ($_GET['type']) {
        case 'asc': $ascdesc = "ASC";
            $linkascdesc = "asc";
            break;
        case 'desc': $ascdesc = "DESC";
            $linkascdesc = "desc";
            break;
        default: $ascdesc = "DESC";
            $linkascdesc = "desc";
            break;
    }

    $orderby = "ORDER BY torrents." . $column . " " . $ascdesc;
    $pagerlink = "sort=" . intval($_GET['sort']) . "&amp;type=" . $linkascdesc . "&amp;";
    } else {
    $orderby = "ORDER BY torrents.sticky ASC, torrents.id DESC";
    $pagerlink = "";
    }

    if ($CURUSER["show_sticky"] != "no")
        $orderby = "ORDER BY torrents.sticky ASC, torrents.id DESC";
	elseif ($CURUSER["show_sticky"] != "yes")
        $orderby = "ORDER BY torrents.id DESC";
    $pagerlink = "";

    $addparam = "";
    $wherea = array();
    $wherecatina = array();

    if (isset($_GET["incldead"]) &&  $_GET["incldead"] == 1)
    {
      $addparam .= "incldead=1&amp;";
      if (!isset($CURUSER) || get_user_class() < UC_ADMINISTRATOR)
        $wherea[] = "banned != 'yes'";
    }
    else
    {
      if (isset($_GET["incldead"]) && $_GET["incldead"] == 2)
      {
      $addparam .= "incldead=2&amp;";
        $wherea[] = "visible = 'no'";
      }
      else
        $wherea[] = "visible = 'yes'";
      if ($CURUSER["view_xxx"] != "yes")
        $wherea[] = "category != '9'";
    }
    
    $category = (isset($_GET["cat"])) ? (int)$_GET["cat"] : false;

    $all = isset($_GET["all"]) ? $_GET["all"] : false;
    $_by = (isset($_GET["_by"]) ? 0 + $_GET["_by"] : 0);

    if (!$all)
    {
      if (!$_GET && $CURUSER["notifs"])
      {
        $all = True;
        foreach ($cats as $cat)
        {
          $all &= $cat['id'];
          if (strpos($CURUSER["notifs"], "[cat" . $cat['id'] . "]") !== False)
          {
            $wherecatina[] = $cat['id'];
            $addparam .= "c{$cat['id']}=1&amp;";
          }
        }
      }
      elseif ($category)
      {
        if (!is_valid_id($category))
          stderr("{$lang['browse_error']}", "{$lang['browse_invalid_cat']}");
        $wherecatina[] = $category;
        $addparam .= "cat=$category&amp;";
      }
      else
      {
        $all = True;
        foreach ($cats as $cat)
        {
          $all &= isset($_GET["c{$cat['id']}"]);
          if (isset($_GET["c{$cat['id']}"]))
          {
            $wherecatina[] = $cat['id'];
            $addparam .= "c{$cat['id']}=1&amp;";
          }
        }
      }
    }
    
    if ($all)
    {
      $wherecatina = array();
      $addparam = "";
    }

    if (count($wherecatina) > 1)
      $wherecatin = implode(",",$wherecatina);
    elseif (count($wherecatina) == 1)
      $wherea[] = "category = $wherecatina[0]";

    $wherebase = $wherea;
    
    
    if (isset($cleansearchstr)) {
 	if ($_by == 0) {
 	$wherea[] = "torrents.name LIKE (" . sqlesc($searchstr) . ")";
 	} elseif ($_by == 1) {
 	$wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
 	} elseif ($_by == 2) {
 	$wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
		} elseif ($_by == 3) {
 	$query = mysql_query("SELECT id FROM users WHERE username = ".sqlesc($searchstr)." LIMIT 1");
 	if (mysql_num_rows($query) > 0)
				{
					$user = mysql_fetch_assoc($query);
					
			$wherea[] = "torrents.owner = ".sqlesc($user['id']).(!$mod ? " AND torrents.anonymous != 'yes'" : "");
				}
				
 	}
 	$addparam .= "search=" . urlencode($searchstr) . "&";
 	$orderby = "";
      
      /////////////// SEARCH CLOUD MALARKY //////////////////////

        $searchcloud = sqlesc($cleansearchstr);
       // $r = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM searchcloud WHERE searchedfor = $searchcloud"), MYSQL_NUM);
        //$a = $r[0];
        //if ($a)
           // mysql_query("UPDATE searchcloud SET howmuch = howmuch + 1 WHERE searchedfor = $searchcloud");
        //else
           // mysql_query("INSERT INTO searchcloud (searchedfor, howmuch) VALUES ($searchcloud, 1)");
        @mysql_query("INSERT INTO searchcloud (searchedfor, howmuch) VALUES ($searchcloud, 1)
                    ON DUPLICATE KEY UPDATE howmuch=howmuch+1");
      /////////////// SEARCH CLOUD MALARKY END ///////////////////
    }

    $where = implode(" AND ", $wherea);
    
    if (isset($wherecatin))
      $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";

    if ($where != "")
      $where = "WHERE $where";

    $res = mysql_query("SELECT COUNT(*) FROM torrents $where") or die(mysql_error());
    $row = mysql_fetch_array($res,MYSQL_NUM);
    $count = $row[0];

if (!$count && isset($cleansearchstr)) 
	{
 	$wherea = $wherebase;
 	$orderby = "ORDER BY id DESC";
 	$searcha = explode(" ", $cleansearchstr);
 	$sc = 0;
 	foreach ($searcha as $searchss) 
 	{
 	if (strlen($searchss) <= 1)
 	continue;
 	$sc++;
 	if ($sc > 5)
 	break;
 	$ssa = array();
		
		if ($_by == 0) {
 foreach (array("torrents.name") as $sss)
			$ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
			$wherea[] = "(" . implode(" OR ", $ssa) . ")";
		} elseif ($_by == 1) {
 foreach (array("search_text", "ori_descr") as $sss)
			$ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
			$wherea[] = "(" . implode(" OR ", $ssa) . ")";
		} elseif ($_by == 2) {
 foreach (array("search_text", "ori_descr") as $sss)
			$ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
			$wherea[] = "(" . implode(" OR ", $ssa) . ")";
			} elseif ($_by == 3) {
 foreach (array("torrents.owner") as $sss)
			$ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
			$wherea[] = "(" . implode(" OR ", $ssa) . ")";
 }
		
 }
    
      if ($sc) 
      {
        $where = implode(" AND ", $wherea);
        if ($where != "")
          $where = "WHERE $where";
        $res = mysql_query("SELECT COUNT(*) FROM torrents $where");
        $row = mysql_fetch_array($res,MYSQL_NUM);
        $count = $row[0];
      }
    }

    $torrentsperpage = $CURUSER["torrentsperpage"];
    if (!$torrentsperpage)
      $torrentsperpage = 15;

    if ($count)
    {
        if ($addparam != "") {
            if ($pagerlink != "") {
                if ($addparam{strlen($addparam)-1} != ";") { // & = &amp;
                    $addparam = $addparam . "&" . $pagerlink;
                } else {
                    $addparam = $addparam . $pagerlink;
                }
            }
        } else {
            $addparam = $pagerlink;
        }
      //list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse.php?" . $addparam);
      $pager = pager($torrentsperpage, $count, "browse.php?" . $addparam);

  $query = "SELECT torrents.id, torrents.category, torrents.nuked, torrents.nukereason, torrents.leechers, torrents.seeders, torrents.name, torrents.sticky, torrents.times_completed, torrents.size, torrents.added, torrents.type, torrents.free, torrents.comments,torrents.numfiles,torrents.filename,torrents.owner,torrents.anonymous,IF(torrents.nfo <> '', 1, 0) as nfoav," .
//	"IF(torrents.numratings < {$TBDEV['minvotes']}, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, categories.name AS cat_name, categories.image AS cat_pic, users.username FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";
	"categories.name AS cat_name, categories.image AS cat_pic, users.username, freeslots.tid, freeslots.uid, freeslots.free AS freeslot, freeslots.double AS doubleup FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id LEFT JOIN freeslots ON (torrents.id=freeslots.tid AND freeslots.uid={$CURUSER['id']}) $where $orderby {$pager['limit']}";
      $res = sql_query($query) or die(mysql_error());
    }
    else
    {
      unset($res);
    }
    
    if (isset($cleansearchstr))
      $title = "{$lang['browse_search']}\"$searchstr\"";
    else
      $title = '';
    // clear new tag manually
if ($CURUSER['clear_new_tag_manually'] == 'yes') {     
$HTMLOUT .="<a href='?clear_new=1'><input type='submit' value='clear new tag' class='btn' /></a>";
} else {     
// clear new tag automatically 
mysql_query("UPDATE users SET last_browse=".TIME_NOW." where id=".$CURUSER['id']);
}



    $HTMLOUT .= "<form method='get' action='browse.php'>
    <table class='bottom'>
    <tr>
    <td class='bottom'>
      <table class='bottom'>
      <tr>";

    $i = 0; 
    $catsperrow = 7; 
    foreach ($cats as $cat) 
    { 
      $HTMLOUT .= ($i && $i % $catsperrow == 0) ? "</tr><tr>" : ""; 
      if ($CURUSER['cats_icons'] == 'no') { 
      $HTMLOUT .= "<td class='bottom' style='padding-bottom: 2px;padding-left: 7px;align:left;border:1px solid;'> 
      <input name='c".$cat['id']."' type=\"checkbox\" " . (in_array($cat['id'],$wherecatina) ? "checked='checked' " : "") . "value='1' /><a class='catlink' href='browse.php?cat={$cat['id']}'>" . htmlspecialchars($cat['name']) . "</a></td>\n"; 
         } else { 
      $HTMLOUT .= " <td class='bottom' style='padding-bottom: 2px;padding-left: 7px;align:left;'>
      <input name=c$cat[id] type=\"checkbox\" " . (in_array($cat['id'],$wherecatina) ? "checked " : "") . "value=1>&nbsp;<a class=catlink href=browse.php?cat=$cat[id]><img src=pic/caticons/" . htmlspecialchars($cat['image']) . " title='". htmlspecialchars($cat['name']) ."'></a></td>\n"; 
      } 
      $i++; 
    }

    $alllink = "<div align='left'>(<a href='browse.php?all=1'><b>{$lang['browse_show_all']}</b></a>)</div>";

    $ncats = count($cats);
    $nrows = ceil($ncats/$catsperrow);
    $lastrowcols = $ncats % $catsperrow;

    if ($lastrowcols != 0)
    {
      if ($catsperrow - $lastrowcols != 1)
        {
          $HTMLOUT .= "<td class='bottom' rowspan='" . ($catsperrow  - $lastrowcols - 1) . "'>&nbsp;</td>";
        }
      $HTMLOUT .= "<td class='bottom' style=\"padding-left: 5px\">$alllink</td>\n";
    }

    $selected = (isset($_GET["incldead"])) ? (int)$_GET["incldead"] : "";

    $HTMLOUT .= "</tr>
    </table>
    </td>

    <td class='bottom'>
    <table class='main'>
      <tr>
        <td class='bottom' style='padding: 1px;padding-left: 10px'>
          <select name='incldead'>
    <option value='0'>{$lang['browse_active']}</option>
    <option value='1'".($selected == 1 ? " selected='selected'" : "").">{$lang['browse_inc_dead']}</option>
    <option value='2'".($selected == 2 ? " selected='selected'" : "").">{$lang['browse_dead']}</option>
          </select>
        </td>";
        

    if ($ncats % $catsperrow == 0)
    {
      $HTMLOUT .= "<td class='bottom' style='padding-left: 15px' rowspan='$nrows' valign='middle' align='right'>$alllink</td>\n";
    }

    $HTMLOUT .= "</tr>
      <tr>
        <td class='bottom' style='padding: 1px;padding-left: 10px'>
        <div align='center'>
          <input type='submit' class='btn' value='{$lang['browse_go']}' />
        </div>
        </td>
      </tr>
      </table>
    </td>
    </tr>
    </table>
    </form><br />";


    $HTMLOUT .= "<table class='bottom' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'>

	<form method='get' action='browse.php'>
	<input type='text' class='search1' id='searchinput' name='search' autocomplete='off' style='width: 240px;' ondblclick='suggest(event.keyCode,this.value);' onkeyup='suggest(event.keyCode,this.value);' onkeypress='return noenter(event.keyCode);' value='' />
 	{$lang['browse_by']}
 <select name='_by'>
 	<option value='0'> {$lang['browse_name']}</option>
 	<option value='1'".($_by == '1' ? ' selected' : '').">{$lang['browse_description']}</option>
 <option value='2'".($_by == '2' ? ' selected' : '')."> {$lang['browse_both']}</option>
 <option value='3'".($_by == '3' ? ' selected' : '')."> {$lang['browse_uploader']}</option>
 </select>
	{$lang['browse_in']}
	<select name='cat'>
	<option value='0'>{$lang['browse_all_types']}</option>";



	$cats = genrelist();
	$catdropdown = "";
	foreach ($cats as $cat) {
 	$catdropdown .= "<option value=\"" . $cat["id"] . "\"";
 	$getcat = (isset($_GET["cat"])?$_GET["cat"]:'');
 	if ($cat["id"] == $getcat)
 	$catdropdown .= " selected='selected'";
 	$catdropdown .= ">" . htmlspecialchars($cat["name"]) . "</option>\n";
	}

	$deadchkbox = "<input type='checkbox' name='incldead' value='1'";
	if (isset($_GET["incldead"]))
 	$deadchkbox .= " checked='checked'";
	$deadchkbox .= " /> {$lang['browse_inc_dead']}";


	$HTMLOUT .= $catdropdown;
	
	$HTMLOUT .= "</select>
	$deadchkbox
	<input type='submit' value='{$lang['browse_search_btn']}' class='btn' />
	</form>

<div id='suggcontainer' style='text-align: left; width: 520px; display: none;'>
<div id='suggestions' style='cursor: default; position: absolute; background-color: #FFFFFF; border: 1px solid #777777;'></div>
</div>
	</td></tr></table>";

    if (isset($cleansearchstr))
    {
      $HTMLOUT .= "<h2>{$lang['browse_search']}\"" . htmlentities($searchstr, ENT_QUOTES) . "\"</h2>\n";
    }
    
    if ($count) 
    {
      $HTMLOUT .= $pager['pagertop'];

      $HTMLOUT .= torrenttable($res);

      $HTMLOUT .= $pager['pagerbottom'];
    }
    else 
    {
      if (isset($cleansearchstr)) 
      {
        $HTMLOUT .= "<h2>{$lang['browse_not_found']}</h2>\n";
        $HTMLOUT .= "<p>{$lang['browse_tryagain']}</p>\n";
      }
      else 
      {
        $HTMLOUT .= "<h2>{$lang['browse_nothing']}</h2>\n";
        $HTMLOUT .= "<p>{$lang['browse_sorry']}(</p>\n";
      }
    }
    $HTMLOUT .="<br /><br />";
    
    /** Legand table for browse **/
    $HTMLOUT .="<table width='480px' cellspacing='5' cellpadding='5' border='0' align='center'>
		<thead><tr>
		<td align='center' colspan='8' class='colhead3' title='Normal torrent browse page legand'>Icon legend</td>
		</tr></thead>
        <tbody><tr>
		<td align='center'><img title='Sticky!' alt='Sticky!' src='{$TBDEV['pic_base_url']}sticky.gif'></td>
        <td>Sticky Torrent</td>
		<td align='center'><img title='Nuked' alt='Nuked' src='{$TBDEV['pic_base_url']}nuked.gif'></td>
		<td>Nuked torrent</td>
		<td align='center'><img title='New!' alt='New!' src='{$TBDEV['pic_base_url']}tag_new.png'></td>
		<td>New Torrent</td>
		</tr>
		<tr>
		<td align='center'><img title='Download!' alt='Download!' src='{$TBDEV['pic_base_url']}download.gif'></td>
		<td>Downlaod Torrent</td>
		<td align='center'><img title='Freeleech slot in use' alt='Freeleech slot in use' src='{$TBDEV['pic_base_url']}freedownload.gif'></td>
		<td>Freeleech slot in use<br></td>
		<td align='center'><img title='Free!' alt='Free!' src='{$TBDEV['pic_base_url']}tag_free.png'></td>
		<td>Freeleech Torrent</td>
		</tr>
		<tr>
		<td align='center'><img title='Bookmark!' alt='Bookmark!' src='{$TBDEV['pic_base_url']}bookmark.gif'></td>
		<td>Bookmark</td>
		<td align='center'><img title='Double-upload slot in use' alt='Double-upload slot in use' src='{$TBDEV['pic_base_url']}doubleseed.gif'></td>
		<td>Double-upload slot in use<br></td>
		<td align='center'><img title='Requested' alt='Requested' src='{$TBDEV['pic_base_url']}tag_request.gif'></td>
		<td>Requested Torrent</td>
		</tr>
		<!---<tr>
		<td align='center'><img height='20' title='1st Post Preview' alt='1st Post Preview' src='pic/forums/mg.gif'></td>
		<td>1st Post Preview<br></td>
		<td align='center'><img title='Last Post' alt='ast post' src='pic/forums/last_post.gif'></td>
		<td>Last Post</td>
		<td align='center'><img title='Thread Icon' alt='Thread Icon' src='pic/forums/topic_normal.gif'></td>
		<td>Thread Icon</td>
		</tr>--->
		</tbody></table><br />";
    
/////////////////////// HTML OUTPUT //////////////////////////////

    where ("{$lang['browse_browse_torrents']}",$CURUSER["id"]);
    print stdhead('Torrents') . $HTMLOUT . stdfoot();

?>