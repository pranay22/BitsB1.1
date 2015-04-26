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
|   Staff Notepad v0.6
+------------------------------------------------
**/

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";

dbconn();

loggedinorreturn();
staffonly();
$lang = array_merge( load_language('global'), load_language('notepad') );
if ($CURUSER['class'] < UC_MODERATOR )
	stderr("{$lang['stderr_error']}", "{$lang['noaccess']}");
$action = isset($_GET["action"]) ? $_GET["action"] : "";
$HTML = "";
$HTML .= "<script type='text/javascript' src='scripts/java_klappe.js'></script>";
if ($action == 'new') {
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	$title = $_POST["title"];
	$text = $_POST["text"];
	$added = time();
	$addedby = $CURUSER["id"];
	$public = $_POST["public"];
	
	if (empty($title) || (empty($text)))
		stderr("{$lang['stderr_error']}", "{$lang['something_is_empty']}");
	if ($public == "none")
		stderr("{$lang['stderr_error']}", "{$lang['public_none']}");
		
	$title = sqlesc($title);
	$text = sqlesc($text);
	$sql = sql_query("insert into notepad (title, text, added, addedby, public) values ($title, $text, '$added', '$addedby', '$public')") or sqlerr(__FILE__, __LINE__);
	header("location: notepad.php");
}
$HTML .= "<form method='post' action='notepad.php?action=new'>
		  <h1>{$lang['add_new']}</h1>
		  <table width='700' cellpadding='5' cellspacing='0' border='0'>
		  <tr><td align='right'>{$lang['head_title']}</td><td><input type='text' name='title' size='60'/></td></tr>
		  <tr><td align='right'>{$lang['head_body']}</td><td><textarea cols='100' rows='10' name='text'></textarea></td></tr>
		  <tr><td align='right'>{$lang['head_public']}</td><td>
		  <select name='public'>
		  <option value='none'>{$lang['please_select']}</option>
		  <option value='yes'>{$lang['yes']}</option>
		  <option value='no'>{$lang['no']}</option>
		  </select></td></tr>
		  <tr><td colspan='2' align='center'><input type='submit' value='{$lang['add']}' class='btn'/></td></tr>
		  </table></form>";
print stdhead() . $HTML . stdfoot();
}
if ($action == 'edit') {
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$id = $_POST["id"];
	$title = $_POST["title"];
	$text = $_POST["text"];
	$public = $_POST["public"];
	
	if (empty($title) || (empty($text)))
		stderr("{$lang['stderr_error']}", "{$lang['something_is_empty']}");
	if ($public == "none")
		stderr("{$lang['stderr_error']}", "{$lang['public_none']}");
		
	$title = sqlesc($title);
	$text = sqlesc($text);
	sql_query("update notepad set text = $text, title = $title, public = '$public' where id = $id") or sqlerr(__FILE__, __LINE__);
	header("location: notepad.php");
}
$id = isset($_GET["id"]) ? $_GET["id"] : "";
$ar = sql_query("select * from notepad WHERE id = {$id}") or sqlerr(__FILE__, __LINE__);
$q = mysql_fetch_array($ar);
if (!$q || !$id || !is_valid_id($id))
	stderr("{$lang['stderr_error']}", "{$lang['invalid_id']}");
$HTML .= "<form method='post' action='notepad.php?action=edit'>
		  <input type='hidden' name='id' value='{$id}'/>
		  <h1>{$lang['edit']}</h1>
		  <table width='700' cellpadding='5' cellspacing='0' border='0'>
		  <tr><td align='right'>{$lang['head_title']}</td><td><input type='text' name='title' size='60' value='".htmlspecialchars($q['title'])."'/></td></tr>
		  <tr><td align='right'>{$lang['head_body']}</td><td><textarea cols='100' rows='10' name='text'>".htmlspecialchars($q['text'])."</textarea></td></tr>
		  <tr><td align='right'>{$lang['head_public']}</td><td>
		  <select name='public'>
		  <option value='none'>{$lang['please_select']}</option>
		  <option value='yes' ".(($q['public'] == 'yes') ? "selected='selected'":"").">{$lang['yes']}</option>
		  <option value='no' ".(($q['public'] == 'no') ? "selected='selected'":"").">{$lang['no']}</option>
		  </select></td></tr>
		  <tr><td colspan='2' align='center'><input type='submit' value='{$lang['edit']}' class='btn'/></td></tr>
		  </table></form>";
print stdhead() . $HTML . stdfoot();
}
if ($action == 'delete') {
$sure = isset($_GET["sure"]) ? $_GET["sure"] : "";
$id = isset($_GET["id"]) ? $_GET["id"] : "";
if (!$id || !is_valid_id($id))
	stderr("{$lang['stderr_error']}", "{$lang['invalid_id']}");
$q = mysql_fetch_row(mysql_query("select addedby from notepad where id = {$id}")) or sqlerr(__FILE__, __LINE__);
if ($q[0] != $CURUSER['id'])
	stderr("{$lang['stderr_error']}", "{$lang['wtfhapped']}");
if (!$sure)
stderr("{$lang['stderr_sec']}", sprintf($lang['stderr_sure'], $id));

sql_query("delete from notepad where id = {$id}") or sqlerr(__FILE__, __LINE__);
header("location: notepad.php");
}
if (!$action) {
$HTML .= "<h1><a href='notepad.php?action=new' class='altlink'>Add new</a></h1>";
$r = sql_query("select notepad.*, u.username FROM notepad notepad LEFT JOIN users u ON notepad.addedby = u.id WHERE notepad.public = 'yes' OR notepad.addedby = {$CURUSER['id']} ORDER by added DESC") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($r) > 0) {
$HTML .= "<table width='800' cellpadding='5' cellspacing='0' border='0'><tr>
	      <td class='colhead'>{$lang['show_title']}</td>
		  <td class='colhead'>{$lang['show_text']}</td>
		  <td class='colhead'>{$lang['show_addedby']}</td>
		  <td class='colhead'>{$lang['show_added']}</td>
		  <td class='colhead'>{$lang['show_public']}</td>
		  </tr>";
while ($q = mysql_fetch_assoc($r)) {
$id = $q['id'];
$added = get_date($q['added'], "LONG");
$addedby = "<a href='userdetails.php?id={$q['addedby']}'>{$q['username']}</a>";
$title = htmlspecialchars($q['title']);
$text = CutName(htmlspecialchars($q['text']), 20);
$text2 = format_comment($q['text']);
$public = (($q['public'] == 'yes') ? "<font color='green'><b>{$lang['yes']}</b></font>":"<font color='red'><b>{$lang['no']}</b></font>");
$edit = (($q['addedby'] == $CURUSER['id'] || $CURUSER['class'] >= UC_SYSOP) ? "<a href='notepad.php?action=edit&amp;id={$q['id']}'>{$lang['edit']}</a>":"");
$del = (($q['addedby'] == $CURUSER['id'] || $CURUSER['class'] >= UC_SYSOP) ? "<a href='notepad.php?action=delete&amp;id={$q['id']}'>{$lang['delete']}</a>":"");
$HTML .= "<tr>
		  <td>{$title}</td>
		  <td>{$text} <div style='float:right'>{$edit}&nbsp;{$del}</div><a href=\"javascript:%20klappe_news('text{$id}')\"><img src='{$TBDEV['pic_base_url']}plus.png' border='0' alt='Show/Hide' title='Show/Hide' id='pictext{$id}'/></a><div id='ktext{$id}' style='display: none;'><br/><br/>{$text2}</div></td>
		  <td>{$addedby}</td>
		  <td>{$added}</td>
		  <td>{$public}</td>
		  </tr>";
}
$HTML .= "</table>";
}
else
$HTML .= "{$lang['none']}";
print stdhead() . $HTML . stdfoot();
}
?>
		  