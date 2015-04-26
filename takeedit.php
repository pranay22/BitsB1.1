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

require_once 'include/bittorrent.php';
require_once 'include/user_functions.php';
require_once "include/page_verify.php";

dbconn();

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('takeedit') );
    //2-way handshake varification
    $newpage = new page_verify();  
    $newpage->check('takeedit');
    //end 2-way varification
    
function bark($msg) {
	genbark($msg, $lang['takedit_failed']);
}

    if (!mkglobal('name:descr:type'))
      bark($lang['takedit_no_data']);

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ( !is_valid_id($id) )
      stderr($lang['takedit_failed'], $lang['takedit_no_data']);
        
    
    $res = mysql_query("SELECT owner, nuked, nukereason, filename, save_as FROM torrents WHERE id = $id");
    
    if ( false == mysql_num_rows($res) )
      stderr($lang['takedit_failed'], $lang['takedit_no_data']);
      
    $row = mysql_fetch_assoc($res);

    if ($CURUSER['id'] != $row['owner'] && $CURUSER['class'] < UC_MODERATOR)
      bark($lang['takedit_not_owner']);

    $updateset = array();

    $fname = $row['filename'];
    preg_match('/^(.+)\.torrent$/si', $fname, $matches);
    $shortfname = $matches[1];
    $dname = $row['save_as'];
    $nfoaction = $_POST['nfoaction'];
    if ($nfoaction == 'update')
    {
      $nfofile = $_FILES['nfo'];
      if (!$nfofile) die("No data " . var_dump($_FILES));
      if ($nfofile['size'] > 65535)
        bark($lang['takedit_nfo_error']);
      $nfofilename = $nfofile['tmp_name'];
      if (@is_uploaded_file($nfofilename) && @filesize($nfofilename) > 0)
        $updateset[] = "nfo = " . sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", file_get_contents($nfofilename)));
    }
    else
      if ($nfoaction == 'remove')
        $updateset[] = 'nfo = ""';

    $updateset[] = "name = " . sqlesc($name);
    $updateset[] = "anonymous = '" . (isset($_POST["anonymous"]) ? "yes" : "no") . "'";
    $updateset[] = "search_text = " . sqlesc(searchfield("$shortfname $dname $name"));
    $updateset[] = "descr = " . sqlesc($descr);
    $updateset[] = "ori_descr = " . sqlesc($descr);
    $updateset[] = "category = " . (0 + $type);
    
    if(in_array(0+$type,$TBDEV['movie_cats'])) {
	if(isset($_POST['youtube']) && preg_match($youtube_pattern,$_POST['youtube'],$temp_youtube)) {
	  if($temp_youtube[0] != $row['youtube'])
	    $updateset[] = "youtube = ".sqlesc($temp_youtube[0]);
	}
	else 
	bark($lang['takedit_youtube']);
	}
    
    //if ($CURUSER["admin"] == "yes") {
if ($CURUSER['class'] > UC_MODERATOR)
{
    if (isset($_POST["banned"]))
    {
        $updateset[] = "banned = 'yes'";
        $_POST["visible"] = 0;
    } else
        $updateset[] = "banned = 'no'";
// ==09 Simple nuke/reason mod
    if (isset($_POST['nuked']) && ($nuked = $_POST['nuked']) != $row['nuked']){
        $updateset[] = 'nuked = ' . sqlesc($nuked);
        }
    if (isset($_POST['nukereason']) && ($nukereason = $_POST['nukereason']) != $row['nukereason']){
        $updateset[] = 'nukereason = ' . sqlesc($nukereason);
        }
        
    if($CURUSER['class'] > UC_MODERATOR){
        $updateset[] = "sticky = '" . (isset($_POST["sticky"]) ? "yes" : "no") . "'";
    }


        /// Set Freeleech on Torrent Time Based
    if (isset($_POST['free_length']) && ($free_length = 0 + $_POST['free_length'])) {
        if ($free_length == 255)
            $Free = 1;

        elseif ($free_length == 42)
            $Free = (86400 + time());

        else
            $Free = (time() + $free_length * 604800);

        $updateset[] = "free = ".sqlesc($Free );
        write_log("Torrent $id ($name) set Free for ".($Free != 1 ? "
	 Until ".get_date($Free, 'DATE') : 'Unlimited')." by $CURUSER[username]");
    }
    
     if (isset($_POST['fl']) && ($_POST['fl'] == 1))
    {
        $updateset[] = "free = '0'";
        write_log("Torrent $id ($name) No Longer Free. Removed by $CURUSER[username]");
    }
    /// end freeleech mod
}
    $updateset[] = "visible = '" . ( isset($_POST['visible']) ? 'yes' : 'no') . "'";

    mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $id");

    write_log(sprintf($lang['takedit_log'], $id, $name, $CURUSER['username']));
    
    $returnto = "{$TBDEV['baseurl']}/details.php?id=$id&amp;edited=1";
    
    header("Location: $returnto");


?>