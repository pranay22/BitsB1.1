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
|   Normal Upload v0.2
+------------------------------------------------
**/

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";
require_once "include/bbcode_functions.php";
dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('upload') );
    
    $HTMLOUT = '';
    $HTMLOUT .="<script type=\"text/javascript\" src=\"./scripts/shout.js\"></script>";
if ($CURUSER['class'] < UC_UPLOADER OR $CURUSER["uploadpos"] == 'no')
    {
        stderr($lang['upload_sorry'], $lang['upload_no_auth']);
    }


    $HTMLOUT .= "<div align='center'>
    <form name='compose' enctype='multipart/form-data' action='takeupload.php' method='post'>
    <input type='hidden' name='MAX_FILE_SIZE' value='{$TBDEV['max_torrent_size']}' />
    <div class='headline' style='width: 847px; text-align: center;' title='Upload a torrent'>Upload a torrent</div>
    <div class='headbody' style='margin-bottom: -1px; width: 837px; background: none repeat scroll 0 0 #C4D0DF;' title='Important things to remember before uploading a torrent'><strong>Please Note: Private Tracker Patch is currently enabled so re-download is necessary for seeding after upload this torrent!
    <br />{$lang['upload_announce_url']}: <font color= 'red'>{$TBDEV['announce_urls'][0]}</font></strong></div>";
    //<p>{$lang['upload_announce_url']} <b>{$TBDEV['announce_urls'][0]}</b></p>";


    $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='10'>  
    <tr>
      <td class='heading' valign='top' align='right' title='{$lang['upload_torrent']}'>{$lang['upload_torrent']}</td>
      <td valign='top' align='left'><input type='file' name='file' size='80' /></td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right' title='{$lang['upload_name']}'>{$lang['upload_name']}</td>
      <td valign='top' align='left'><input type='text' name='name' size='80' /><br />({$lang['upload_filename']})</td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right' title='{$lang['upload_youtube']}'>{$lang['upload_youtube']}</td>
      <td valign='top' align='left'><input type='text' name='youtube' size='80' /><br />({$lang['upload_youtube_info']})</td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right' title='{$lang['upload_nfo']}'>{$lang['upload_nfo']}</td>
      <td valign='top' align='left'><input type='file' name='nfo' size='80' /><br />({$lang['upload_nfo_info']})</td>
    </tr>
    <tr>
      <td class='heading' valign='top' align='right' title='Torrent {$lang['upload_description']}'>{$lang['upload_description']}</td>
      <td valign='top' align='left'>". textbbcode("compose","descr")."
      <br />({$lang['upload_html_bbcode']})</td>
    </tr>";

    $s = "<select name='type'>\n<option value='0'>({$lang['upload_choose_one']})</option>\n";

    $cats = genrelist();
    
    foreach ($cats as $row)
    {
      $s .= "<option value='{$row["id"]}'>" . htmlspecialchars($row["name"]) . "</option>\n";
    }
    
    $s .= "</select>\n";
    
    $HTMLOUT .= "<tr>
        <td class='heading' valign='top' align='right'>{$lang['upload_type']}</td>
        <td valign='top' align='left'>$s</td>
      </tr>
      <tr>
        <td class='heading' valign='top' align='right'>Free Leech</td>
        <td valign='top' align='left'>
    <select name='free_length'>
    <option value='0'>Not Free</option>
    <option value='42'>Free for 1 day</option>
    <option value='1'>Free for 1 week</option>
    <option value='2'>Free for 2 weeks</option>
    <option value='4'>Free for 4 weeks</option>
    <option value='8'>Free for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select></td>
      </tr>";
    $HTMLOUT .= tr("{$lang['upload_anonymous']}", "<input type='checkbox' name='uplver' value='yes' />{$lang['upload_anonymous1']}", 1);
    $HTMLOUT .="<tr>
        <td align='center' colspan='2'><input type='submit' class='btn' value='{$lang['upload_submit']}' /></td>
      </tr></table>
    </form>
    </div>";
////////////////////////// HTML OUTPUT //////////////////////////

    where ("{$lang['upload_upload_torrent']}",$CURUSER["id"]);
    print stdhead($lang['upload_stdhead']) . $HTMLOUT . stdfoot();

?>