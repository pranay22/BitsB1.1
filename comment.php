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
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
dbconn(false);

loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('comment') );

$action = (isset($_GET['action']) ? $_GET['action'] : 0);

/** comment stuffs by pdq **/
$locale = 'torrent';
$locale_link = 'details';
$extra_link = '';
$sql_1 = 'name, owner, anonymous FROM torrents';// , anonymous
$name = 'name';
$table_type = $locale.'s';

$_GET['type'] = (isset($_GET['type']) ? $_GET['type'] : (isset($_POST['locale']) ? $_POST['locale'] : ''));

if (isset($_GET['type'])) {
    $type_options = array('torrent' => 'details', 
                          'request' => 'viewrequests', 
                          //'user'    => 'userdetails'
                          );
                       
    if (isset($type_options[$_GET['type']])) {
        $locale_link = $type_options[$_GET['type']];
        $locale = $_GET['type'];
    }
    switch ($_GET['type']) { 
        	case 'request':
            $sql_1 = 'request FROM requests';
            $name = 'request';
            $extra_link = '&req_details';
            $table_type = $locale.'s';
        	break;
        
//	        case 'user':
//          $sql_1 = 'username FROM users';
//          $name = 'username';
//          $table_type = $locale.'s';
//        	break;
            
            default :
           	//case 'torrent':
            $sql_1 = 'name, owner, anonymous FROM torrents';// , anonymous
            $name = 'name';
            $table_type = $locale.'s';
        	break;
        }
}
/** end comment stuffs by pdq **/
//$get_hash  = isset($_POST['hash']) ? $_POST['hash'] : (isset($_GET['hash']) ? $_GET['hash'] : '');

if ($action == 'add') {
        
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $id = (isset($_POST['tid']) ? $_POST['tid'] : 0);
            
        if (!is_valid_id($id))
            stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");
            
        //$res_hash = md5($TBDEV['salt1'].$CURUSER['id']);
        //if ($get_hash != $res_hash)
        //    die('Something went wrong. Please re-submit');
       
        $res = mysql_query("SELECT $sql_1 WHERE id = $id") or sqlerr(__FILE__,__LINE__);
  
        $arr = mysql_fetch_array($res, MYSQL_NUM);
        if (!$arr)
            stderr("{$lang['comment_error']}", "No $locale with that ID.");
              
        $text = (isset($_POST['text']) ? trim($_POST['text']) : '');
          
        if (!$text)
            stderr("{$lang['comment_error']}", "{$lang['comment_body']}");
    
        $owner = (isset($arr['owner']) ? $arr['owner'] : 0);
        $arr['anonymous'] = (isset($arr['anonymous']) && $arr['anonymous'] == 'yes' ? 'yes' : 'no');
    
        if ($CURUSER['id'] == $owner && $arr['anonymous'] == 'yes' || (isset($_POST['anonymous']) && $_POST['anonymous'] == 'yes'))
            $anon = "'yes'";
        else
            $anon = "'no'";
        
        mysql_query("INSERT INTO comments (user, $locale, added, text, ori_text, anonymous) VALUES (".$CURUSER["id"].",$id, ".time().", " . sqlesc($text) .
        "," . sqlesc($text) . ", $anon)");
    
        $newid = mysql_insert_id();
    
        mysql_query("UPDATE $table_type SET comments = comments + 1 WHERE id = $id") or sqlerr(__FILE__, __LINE__);
        
        //if ($locale == 'torrent')
        //    mysql_query("UPDATE users SET tcomments = tcomments + 1 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
    
        if ($TBDEV['karma'] && isset($CURUSER['seedbonus']))
            mysql_query("UPDATE users SET seedbonus = seedbonus+3.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
            
        //$Cache->delete_value('MyUser_'.$_COOKIE['session_key']);
    
    	  header("Refresh: 0; url=$locale_link.php?id=$id$extra_link&viewcomm=$newid#comm$newid");
    	  die;
    }

    $id = (isset($_GET['tid']) ? $_GET['tid'] : 0);
    if (!is_valid_id($id))
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

    $res = mysql_query("SELECT $sql_1 WHERE id = $id") or sqlerr(__FILE__,__LINE__);
      
    $arr = mysql_fetch_assoc($res);
    
    if (!$arr)
        stderr("{$lang['comment_error']}", "No $locale with that ID.");
      
      $HTMLOUT = '';

      $HTMLOUT .= "<h1>{$lang['comment_add']}'".htmlspecialchars($arr[$name])."'</h1>
      <br /><form method='post' name='add' action='comment.php?action=add'>
      <input type='hidden' name='tid' value='{$id}'/>
      <input type='hidden' name='locale' value='$name' />";
      
      if($TBDEV['textbbcode'] && function_exists('textbbcode'))
          $HTMLOUT .= textbbcode("add", "text", "");
      else
          $HTMLOUT .= "<textarea name='text' rows='10' cols='60'></textarea>";
          
      $HTMLOUT .= "<br />
      <label for='anonymous'>Tick this to post anonymously</label>
      <input id='anonymous' type='checkbox' name='anonymous' value='yes' />
      <br /><input type='submit' class='btn' value='{$lang['comment_doit']}' /></form>";

      $res = mysql_query("SELECT comments.id, text, comments.added, comments.$locale, comments.anonymous, comments.editedby, comments.editedat, username, users.id as user, users.title, users.avatar, users.av_w, users.av_h, users.class, users.donor, users.warned FROM comments LEFT JOIN users ON comments.user = users.id WHERE $locale = $id ORDER BY comments.id DESC LIMIT 5");

      $allrows = array();
      while ($row = mysql_fetch_assoc($res))
        $allrows[] = $row;

      if (count($allrows)) {
              require_once "include/torrenttable_functions.php";
              require_once "include/html_functions.php";
              require_once "include/bbcode_functions.php";
          $HTMLOUT .= "<h2>{$lang['comment_recent']}</h2>\n";
          $HTMLOUT .= commenttable($allrows, $locale);
        }

      print stdhead("{$lang['comment_add']}'".$arr[$name]."'").$HTMLOUT.stdfoot();
      die;
}
elseif ($action == "edit") {
    
     $commentid = (isset($_GET['cid']) ? $_GET['cid'] : 0);
     
      if (!is_valid_id($commentid))
          stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

     $res = mysql_query("SELECT c.*, t.$name, t.id as tid FROM comments AS c LEFT JOIN $table_type AS t ON c.$locale = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);

      $arr = mysql_fetch_assoc($res);
      
      if (!$arr)
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}.");

      if ($arr["user"] != $CURUSER["id"] && $CURUSER['class'] < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");

      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
          $text = (isset($_POST['text']) ? $_POST['text'] : '');
    
          if ($text == '')
              stderr("{$lang['comment_error']}", "{$lang['comment_body']}");

      $text = sqlesc($text);

      $editedat = time();

          if (isset($_POST['lasteditedby']) || $CURUSER['class'] < UC_MODERATOR)
            mysql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
        else
            mysql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby=0 WHERE id=$commentid") or sqlerr(__FILE__, __LINE__); 
      
  //$Cache->delete_value('comment_id'.$commentid);	

      header("Refresh: 0; url=$locale_link.php?id=$arr[tid]$extra_link&viewcomm=$commentid#comm$commentid");
		die;
      }

      $HTMLOUT = '';
      $HTMLOUT .= "<h1>{$lang['comment_edit']}'".htmlspecialchars($arr[$name])."'</h1>
      <form method='post' name='edit' action='comment.php?action=edit&amp;cid=$commentid'>
      <input type='hidden' name='locale' value='$name' />
       <input type='hidden' name='tid' value='$arr[tid]' />
      <input type='hidden' name='cid' value='$commentid' />";
      
       if($TBDEV['textbbcode'] && function_exists('textbbcode'))
          $HTMLOUT .= textbbcode("edit", "text", $arr["text"]);
      else
          $HTMLOUT .= "<textarea name='text' rows='10' cols='60'>".$arr["text"]."</textarea>";

      $HTMLOUT .= '
      <br />'.($CURUSER['class'] >= UC_MODERATOR ? '<input type="checkbox" value="lasteditedby" checked="checked" name="lasteditedby" id="lasteditedby" /> Show Last Edited By<br /><br />' : '').
      ' <input type="submit" class="btn" value="'.$lang['comment_doit'].'" /></form>';

      print stdhead("{$lang['comment_edit']}'".$arr[$name]."'").$HTMLOUT.stdfoot();
      die;
    }
    elseif ($action == "delete") {
      if ($CURUSER['class'] < UC_MODERATOR)
        stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");

       $commentid = (isset($_GET['cid']) ? $_GET['cid'] : 0);
       $tid = (isset($_GET['tid']) ? $_GET['tid'] : 0);
      if (!is_valid_id($commentid))
          stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

      $sure = isset($_GET["sure"]) ? (int)$_GET["sure"] : false;

      if (!$sure) {
        //$referer = $_SERVER["HTTP_REFERER"];
        stderr("{$lang['comment_delete']}", "{$lang['comment_about_delete']}\n" .
          "<a href='comment.php?action=delete&amp;cid=$commentid&amp;tid=$tid&amp;sure=1" .
          ($locale == 'request' ? '&amp;type=request' : '')."'>
          here</a> {$lang['comment_delete_sure']}");
      }


      $res = mysql_query("SELECT $locale FROM comments WHERE id=$commentid")  or sqlerr(__FILE__,__LINE__);
	  $arr = mysql_fetch_assoc($res);
    
      $id = 0;
	  if ($arr)
		  $id = $arr[$locale];

      mysql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__,__LINE__);
	  if ($id && mysql_affected_rows() > 0)
		  mysql_query("UPDATE $table_type SET comments = comments - 1 WHERE id = $id");
	
    //$Cache->delete_value('comment_id'.$commentid);	
    //if ($locale == 'torrent')
    //     mysql_query("UPDATE users SET tcomments = tcomments - 1 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);

    if ($TBDEV['karma'] && isset($CURUSER['seedbonus']))
        mysql_query("UPDATE users SET seedbonus = seedbonus+3.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);

    //$Cache->delete_value('MyUser_'.$_COOKIE['session_key']);
    header("Refresh: 0; url=$locale_link.php?id=$tid$extra_link");
    die;
    }
    elseif ($action == "vieworiginal") {
      if ($CURUSER['class'] < UC_MODERATOR)
          stderr("{$lang['comment_error']}", "{$lang['comment_denied']}");

      $commentid = (isset($_GET['cid']) ? $_GET['cid'] : 0);

      if (!is_valid_id($commentid))
          stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']}");

      $res = mysql_query("SELECT c.*, t.$name FROM comments AS c LEFT JOIN $table_type AS t ON c.$locale = t.id WHERE c.id=$commentid") or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_assoc($res);

      if (!$arr)
        stderr("{$lang['comment_error']}", "{$lang['comment_invalid_id']} $commentid.");

      $HTMLOUT = '';
      $HTMLOUT .= "<h1>{$lang['comment_original_content']}#$commentid</h1><p>
      <table width='500' border='1' cellspacing='0' cellpadding='5'>
      <tr><td class='comment'>
      ".htmlspecialchars($arr["ori_text"])."
      </td></tr></table>";

$returnto = (isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 0);

	if ($returnto)
 		$HTMLOUT .= "<p>(<a href='$returnto'>back</a>)</p>\n";
        
  print stdhead("{$lang['comment_original']}").$HTMLOUT.stdfoot();
  die;
}
else
      stderr("{$lang['comment_error']}", "{$lang['comment_unknown']}");
die;
?>