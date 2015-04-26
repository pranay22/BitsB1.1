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

if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
}

    require_once "include/user_functions.php";
    require_once "include/html_functions.php";
    require_once "include/bbcode_functions.php";
    staffonly();
  
    $lang = array_merge( $lang, load_language('ad_snews') );
$htmlout ='';
$do = (isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"])? $_POST["do"] : ""));
// print($do);
$id = (isset($_GET["id"]) ? 0 + $_GET["id"] : (isset($_POST["id"])? 0 + $_POST["id"] : "0"));
$sure = (isset($_GET["sure"]) && $_GET["sure"] == "yes" ? "yes" : "no");

if ($do == "delete" && $id > 0) {
    $rs = sql_query("SELECT id,title,user from snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
    $ar = mysql_fetch_assoc($rs);
    if (mysql_num_rows($rs) == 0)
        stderr("{$lang['text_error']}", "{$lang['text_no']}");
    elseif ($ar["user"] != $CURUSER["id"])
        stderr("{$lang['text_error']}", "{$lang['text_noright']}");
    else {
        if ($sure == "no")
            stderr("{$lang['text_sanity']}", "{$lang['text_about']} " . $ar["title"] . ", {$lang['text_about1']}<a href=\"admin.php?action=snews&amp;do=delete&amp;id=$id&amp;sure=yes\">click here</a>");
        elseif ($sure == "yes") {
            sql_query("DELETE FROM snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
            write_log($CURUSER["username"] . " deleted staff news " . $ar["title"]);
            header("Refresh: 2; url=staffpanel.php");
            stderr("{$lang['text_success']}", "{$lang['text_success1']}");
        }
    }
} elseif ($do == "add" || $do == "edit") {
    if ($do == "edit") {
        $rs = sql_query("SELECT * FROM snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
        $ar = mysql_fetch_assoc($rs);
        if (mysql_num_rows($rs) == 0)
            stderr("{$lang['text_error']}", "{$lang['text_no']}");
        elseif ($ar["user"] != $CURUSER["id"])
            stderr("{$lang['text_error']}", "{$lang['text_noright1']}");
        else {
            
            $htmlout .= begin_frame("{$lang['text_edit']}" . $ar["title"],true);
        }
    } elseif ($do == "add") {
        
        $htmlout .= begin_frame("{$lang['text_add']}",true);
    }
			$htmlout .= '<script type="text/javascript">
			function checkit(id)
			{	var button = document.getElementById(id);
				if (button.checked == true)
					button.checked = false;
				if (button.checked == false)
					button.checked = true;
			}
			</script>';
		$htmlout .= '<form name="compose" method="post" action="admin.php?action=snews">
		<table width="500" align="center" border="1" cellspacing="0" cellpadding="7" >

			<tr><td nowrap="nowrap">'.$lang["text_title"].'</td><td width="100%" align="left"><input type="text" name="title" size="80" value="'.($do == "edit" ? $ar["title"] : "").'" /></td></tr>
			<tr><td nowrap="nowrap">'.$lang["text_body"].'</td><td width="100%" align="left">'.textbbcode("compose","body", htmlspecialchars($do == "edit" ? $ar["body"] : "")).'</td></tr>
			<tr><td nowrap="nowrap">'.$lang["text_type"].'</td><td width="100%" align="left">
			  <input type="radio" id="notice" name="type" value="notice" '.((($do == "edit" && $ar["type"] == "notice") || $do == "add")? "checked=\"checked\"" : "").' />
			  <span style="color:#339900; cursor:default" onclick="checkit(notice)">'.$lang["text_note"].'</span>
			  <input type="radio" id="warning" name="type" value="warning" '.(($do == "edit" && $ar["type"] == "warning")? "checked=\"checked\"" : "").' />
			  <span style="color:#FF3300; cursor:default" onclick="checkit(warning)">'.$lang["text_warning"].'</span>
			  <input type="radio" id="important" name="type" value="important" '.(($do == "edit" && $ar["type"] == "important") ? "checked=\"checked\"" : "").' />
			  <span style="color:#990000; cursor:default" onclick="checkit(important)">'.$lang["text_import"].'</span></td></tr>
			<tr><td colspan="2" align="center"><input type="hidden" name="do" value="'.($do == "edit" ? "nedit" : "nadd").'" /><input type="submit"  value="'.($do == "edit" ? "Edit": "Add").'" />';
		
    if ($do == "edit")
        $htmlout .= "<input type=\"hidden\" name=\"id\" value='" . $ar["id"] . "'/>";


			$htmlout .= '</td></tr>
			</table></form>';
    $htmlout .= end_frame();
      print stdhead("") . $htmlout . stdfoot();
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && ($do == "nedit" || $do == "nadd")) {
    $title = $_POST["title"];
    if (empty($title))
        stderr("{$lang['text_error']}", "{$lang['text_title1']}");

    $body = $_POST["body"];
    if (empty($body))
        stderr("{$lang['text_error']}", "{$lang['text_body1']}");
    $t = array("notice", "warning", "important");
    $type = ((isset($_POST["type"]) && in_array($_POST["type"], $t)) ? $_POST["type"] : "notice");

    if ($do == "nedit") {
        $rs = sql_query("select id from snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($rs) == 0)
            stderr("{$lang['text_error']}", "{$lang['text_no']}");
        else {
            $res = mysql_query("UPDATE snews set title=" . sqlesc($title) . ", body=" . sqlesc($body) . ", type=" . sqlesc($type) . ", last_edit=" . time() . " where id=" . $id) or sqlerr(__FILE__, __LINE__);
            if (!$res)
                stderr("{$lang['text_error']}", "{$lang['text_some']}");
            else {
                header("Refresh: 2; url=staffpanel.php");
                stderr("{$lang['text_success']}", "{$lang['text_success2']}");
            }
        }
    } elseif ($do == "nadd") {
        $res = sql_query("INSERT INTO snews (title, body, type, added, user) VALUES(" . sqlesc($title) . ", " . sqlesc($body) . "," . sqlesc($type) . "," . time() . ", " . $CURUSER["id"] . ") ") or sqlerr(__FILE__, __LINE__);
         $res = sql_query("SELECT id FROM users where class >= 6") or sqlerr(__FILE__, __LINE__);
        $msg = "Staffnews";

$msg2 = <<<EOD
There are news in [url={$TBDEV['baseurl']}/admin.php?action=snews2]Staff News[/url].
EOD;

while($arr = mysql_fetch_row($res))
sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES(2,$arr[0], '$msg2', '" . mktime() . "', 'Staffnews!')");
        if (!$res)
            stderr("{$lang['text_error']}", "{$lang['text_some']}");
        else {
            header("Refresh: 2; url=staffpanel.php");
            stderr("{$lang['text_success']}", "{$lang['text_success3']}");
        }
    }
} 
?>