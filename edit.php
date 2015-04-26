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

require_once "include/bittorrent.php" ;
require_once "include/user_functions.php" ;
require_once "include/html_functions.php" ;
require_once "include/bbcode_functions.php";
require_once "include/page_verify.php";

if (!mkglobal("id"))
	die();

$id = 0 + $id;
if (!$id)
	die();

dbconn();

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('edit') );
    //2-way handshake varification
    $newpage = new page_verify();  
    $newpage->create('takeedit');
    //end 2-way varification
    
    $res = mysql_query("SELECT * FROM torrents WHERE id = $id");
    $row = mysql_fetch_assoc($res);
    if (!$row)
      stderr($lang['edit_user_error'], $lang['edit_no_torrent']);


    
    if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR)) 
    {
      stderr($lang['edit_user_error'], sprintf($lang['edit_no_permission'], urlencode($_SERVER['REQUEST_URI'])));
    }


    $HTMLOUT = '';
    $HTMLOUT .="<script type=\"text/javascript\" src=\"./scripts/shout.js\"></script>";
    $HTMLOUT  .= "<form name='compose' method='post' action='takeedit.php' enctype='multipart/form-data'>
    <input type='hidden' name='id' value='$id' />";
    
    if (isset($_GET["returnto"]))
      $HTMLOUT  .= "<input type='hidden' name='returnto' value='" . htmlspecialchars($_GET["returnto"]) . "' />\n";
    $HTMLOUT  .=  "<table border='1' cellspacing='0' cellpadding='10'>\n";
    
    $HTMLOUT  .= tr($lang['edit_torrent_name'], "<input type='text' name='name' value='" . htmlspecialchars($row["name"]) . "' size='80' />", 1);
    $HTMLOUT .= "<tr><td class='heading' valign='top' align='right'>{$lang['edit_youtube']}</td>
    <td valign='top' align='left'><input type='text' name='youtube' value='{$row['youtube']}'size='80' /><br />({$lang['edit_youtube_info']})</td></tr>";
    $HTMLOUT  .= tr($lang['edit_nfo'], "<input type='radio' name='nfoaction' value='keep' checked='checked' />{$lang['edit_keep_current']}<br />".
	"<input type='radio' name='nfoaction' value='update' />{$lang['edit_update']}<br /><input type='file' name='nfo' size='80' />", 1);
    if ((strpos($row["ori_descr"], "<") === false) || (strpos($row["ori_descr"], "&lt;") !== false))
    {
      $c = "";
    }
    else
    {
      $c = " checked";
    }
    
    $HTMLOUT  .= tr($lang['edit_description'], "". textbbcode("compose","descr","".htmlspecialchars($row['ori_descr'])."")."<br />({$lang['edit_tags']})", 1);

    $s = "<select name='type'>\n";

    $cats = genrelist();
    
    foreach ($cats as $subrow) 
    {
      $s .= "<option value='" . $subrow["id"] . "'";
      if ($subrow["id"] == $row["category"])
        $s .= " selected='selected'";
      $s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
    }

    $s .= "</select>\n";
    $HTMLOUT  .= tr($lang['edit_type'], $s, 1);
    $HTMLOUT  .= tr($lang['edit_visible'], "<input type='checkbox' name='visible'" . (($row["visible"] == "yes") ? " checked='checked'" : "" ) . " value='1' /> {$lang['edit_visible_mainpage']}<br /><table border='0' cellspacing='0' cellpadding='0' width='420'><tr><td class='embedded'>{$lang['edit_visible_info']}</td></tr></table>", 1);

    if($CURUSER['class'] > UC_MODERATOR)
      $HTMLOUT .= tr("Sticky", "<input type='checkbox' name='sticky'" . (($row["sticky"] == "yes") ? " checked='checked'" : "" ) . " value='yes' />Sticky this torrent !", 1);
    if (get_user_class() >= UC_MODERATOR) //($CURUSER["admin"] == "yes")
    {
      $HTMLOUT  .= tr($lang['edit_banned'], "<input type='checkbox' name='banned'" . (($row["banned"] == "yes") ? " checked='checked'" : "" ) . " value='1' /> {$lang['edit_banned']}", 1);
    }
    $HTMLOUT .= tr($lang['edit_anonymous'], "<input type='checkbox' name='anonymous'" . (($row["anonymous"] == "yes") ? " checked='checked'" : "" ) . " value='1' />{$lang['edit_anonymous1']}", 1);
    $HTMLOUT .= tr("Nuked", "<input type='radio' name='nuked'" . ($row["nuked"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes <input type='radio' name='nuked'" . ($row["nuked"] == "no" ? " checked='checked'" : "") . " value='no' />No",1);
    $HTMLOUT .= tr("Nuke Reason", "<input type='text' name='nukereason' value='" . htmlspecialchars($row["nukereason"]) . "' size='80' />", 1);
    if ($CURUSER['class'] >= UC_MODERATOR)
    {
      $HTMLOUT  .= tr("Free Leech", ($row['free'] != 0 ? 
	  "<input type='checkbox' name='fl' value='1' /> Remove Freeleech" : "
    <select name='free_length'>
    <option value='0'>------</option>
    <option value='42'>Free for 1 day</option>
    <option value='1'>Free for 1 week</option>
    <option value='2'>Free for 2 weeks</option>
    <option value='4'>Free for 4 weeks</option>
    <option value='8'>Free for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select>"), 1);
    }
    
    if ($row['free'] != 0) {
    	 $HTMLOUT  .= tr("Free Leech Duration", 
		 ($row['free'] != 1 ? "Until ".get_date($row['free'],'DATE')." 
		 (".mkprettytime($row['free'] - time())." to go)" : 'Unlimited'), 1);
		 

    }

    $HTMLOUT  .= "<tr><td colspan='2' align='center'><input type='submit' value='{$lang['edit_submit']}' class='btn' /> <input type='reset' value='{$lang['edit_revert']}' class='btn' /></td></tr>
    </table>
    </form>
    <br />
    <form method='post' action='delete.php'>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr>
      <td class='embedded' style='background-color: #F5F4EA;padding-bottom: 5px' colspan='2'><b>{$lang['edit_delete_torrent']}.</b> {$lang['edit_reason']}</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='1' />&nbsp;{$lang['edit_dead']} </td><td> {$lang['edit_peers']}</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='2' />&nbsp;{$lang['edit_dupe']}</td><td><input type='text' size='40' name='reason[]' /></td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='3' />&nbsp;{$lang['edit_nuked']}</td><td><input type='text' size='40' name='reason[]' /></td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='4' />&nbsp;{$lang['edit_rules']}</td><td><input type='text' size='40' name='reason[]' />({$lang['edit_req']})</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='5' checked='checked' />&nbsp;{$lang['edit_other']}</td><td><input type='text' size='40' name='reason[]' />({$lang['edit_req']})<input type='hidden' name='id' value='$id' /></td>
    </tr>";
    
    if (isset($_GET["returnto"]))
    {
      $HTMLOUT  .= "<input type='hidden' name='returnto' value='" . htmlspecialchars($_GET["returnto"]) . "' />\n";
		}
    
    $HTMLOUT  .= "<tr><td colspan='2' align='center'><input type='submit' value='{$lang['edit_delete']}' class='btn' /></td>
    </tr>
    </table>
    </form>";


//////////////////////////// HTML OUTPIT ////////////////////////////////
    print stdhead("{$lang['edit_stdhead']} '{$row["name"]}'") . $HTMLOUT . stdfoot();

?>