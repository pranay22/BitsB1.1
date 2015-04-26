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

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/pager_functions.php";
dbconn();
loggedinorreturn();
$HTML = "";
$lang = array_merge(load_language('global'), load_language('bugs') );
$action = (isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : ''));
//Here we see the bug problem.
if ($action == 'viewbug') {
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if ($CURUSER['class'] < UC_SYSOP) stderr("{$lang['stderr_error']}", "{$lang['stderr_only_coder']}"); //Change UC_CODER to your highest class :]
	$id = isset($_POST["id"]) ? $_POST["id"] : '';
	$status = isset($_POST["status"]) ? $_POST["status"] : '';
	if ($status == 'na') stderr("{$lang['stderr_error']}", "{$lang['stderr_no_na']}");
	if (!$id || !is_valid_id($id)) stderr("{$lang['stderr_error']}", "{$lang['stderr_invalid_id']}");
	$query = mysql_query("SELECT b.*, u.username FROM bugs AS b LEFT JOIN users AS u ON b.sender = u.id WHERE b.id = {$id}") or sqlerr(__FILE__, __LINE__);
	while ($q = mysql_fetch_assoc($query)) {
	switch ($status) {
	case 'fixed':
	$msg = sqlesc("Hello {$q['username']}.\nYour bug: [b]{$q['title']}[/b] has been treated by one of our coder, and is done.\n\nWe whould to thank you and therefore we have added [b]2 GB[/b] to your upload ammount :].\n\nBest regards, {$TBDEV['site_name']}'s coders.\n");
	$uq = "UPDATE users SET uploaded = uploaded +". 1024*1024*1024*2 ." WHERE id = {$q['sender']}";
	break;
	case 'ignored':
	$msg = sqlesc("Hello {$q['username']}.\nYour bug: [b]{$q['title']}[/b] has been ignored by one of our coder.\n\nPossibly it was not a bug.\n\nBest regards, {$TBDEV['site_name']}'s coders.\n");
	$uq = "";
	break;
	}
	mysql_query($uq);
	mysql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES (0, {$q['sender']}, ".time().", {$msg})");
	mysql_query("UPDATE bugs SET status='{$status}', staff='{$CURUSER['id']}' WHERE id = {$id}");
	}
	header("location: {$_SERVER["PHP_SELF"]}?action=viewbug&id={$id}");
}
$id = isset($_GET["id"]) ? $_GET["id"] : '';
if (!$id || !is_valid_id($id)) stderr("{$lang['stderr_error']}", "{$lang['stderr_invalid_id']}");
if ($CURUSER['class'] < UC_MODERATOR) stderr("{$lang['stderr_error']}", 'Only staff can view bugs.');
$as = mysql_query("SELECT b.*, u.username, u.class, staff.username AS st, staff.class AS stclass FROM bugs AS b LEFT JOIN users AS u ON b.sender = u.id LEFT JOIN users AS staff ON b.staff = staff.id WHERE b.id = {$id}") or sqlerr(__FILE__, __LINE__);
while ($a = mysql_fetch_assoc($as)) {
$title = htmlspecialchars($a['title']);
$added = get_date($a['added'],'',0,1);
$addedby = "<a href='userdetails.php?id={$a['sender']}'>{$a['username']}</a> <i>(".get_user_class_name($a['class']).")</i>";
switch ($a['priority']) {
case 'low':
$priority = "<font color='green'>{$lang['low']}</font>";
break;
case 'high':
$priority = "<font color='red'>{$lang['high']}</font>";
break;
case 'veryhigh':
$priority = "<font color='red'><b><u>{$lang['veryhigh']}</u></b></font>";
break;
}
$problem = htmlspecialchars($a['problem']);
switch ($a['status']) {
case 'fixed':
$status = "<font color='green'><b>{$lang['fixed']}</b></font>";
break;
case 'ignored':
$status = "<font color='#FF8C00'><b>{$lang['ignored']}</b></font>";
break;
default:
$status = "<select name='status'>
<option value='na'>{$lang['select_one']}</option>
<option value='fixed'>{$lang['fix_problem']}</option>
<option value='ignored'>{$lang['ignore_problem']}</option>
</select>";
}
switch ($a['staff']) {
case 0:
$by = "";
break;
default:
$by = "<a href='userdetails.php?id={$a['staff']}'>{$a['st']}</a> <i>(".get_user_class_name($a['stclass']).")</i>";
break;
}
$HTML .= "<form method='post' action='{$_SERVER["PHP_SELF"]}?action=viewbug'>
<input type='hidden' name='id' value='{$a['id']}'/>
<table cellpadding='5' cellspacing='0' border='0' align='center'>
<tr><td class='rowhead'>{$lang['title']}:</td><td>{$title}</td></tr>
<tr><td class='rowhead'>{$lang['added']} / {$lang['by']}</td><td>{$added} / {$addedby}</td></tr>
<tr><td class='rowhead'>{$lang['priority']}</td><td>{$priority}</td></tr>
<tr><td class='rowhead'>{$lang['problem_bug']}</td><td><textarea cols='60' rows='10' readonly='readonly'>{$problem}</textarea></td></tr>
<tr><td class='rowhead'>{$lang['status']} / {$lang['by']}</td><td>{$status} - {$by}</td></tr>";
if ($a['status'] == 'na') {
$HTML .= "<tr><td colspan='2' align='center'><input type='submit' value='{$lang['submit_btn_fix']}' class='btn'/></td></tr>\n";
}
}
$HTML .= "</table></form><a href='{$_SERVER["PHP_SELF"]}?action=bugs'>{$lang['go_back']}</a>\n";
}
//This is staffs page
elseif ($action == 'bugs') {
if ($CURUSER['class'] < UC_MODERATOR) stderr("{$lang['stderr_error']}", "{$lang['stderr_only_staff_can_view']}");
$cc = get_row_count("bugs");
$perpage = 10;
$pager = pager($perpage, $cc, 'bugs.php?action=bugs&amp;');
$res = mysql_query("SELECT b.*, u.username, staff.username AS staffusername FROM bugs AS b LEFT JOIN users AS u ON b.sender = u.id LEFT JOIN users AS staff ON b.staff = staff.id ORDER BY b.id DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
$r = mysql_query("SELECT * FROM bugs WHERE status = 'na'");
if (mysql_num_rows($res) > 0) {
$count = mysql_num_rows($r);
$HTML .= $pager['pagertop'];
$HTML .= "
<!--<h1 align='center'>There is <font color='#FF0000'>{$count}</font> new bug".($count > 1 ? "s" : "").". Please check them.</h1>-->
<h1 align='center'>".sprintf($lang['h1_count_bugs'], $count, ($count > 1 ? "s" : ""))."</h1>
<font class='small' style='font-weight:bold;'>{$lang['delete_when']}</font><br/>
<table cellpadding='10' cellspacing='0' border='0' align='center'><tr>
<td class='colhead' align='center'>{$lang['title']}</td>
<td class='colhead' align='center'>{$lang['added']} / {$lang['by']}</td>
<td class='colhead' align='center'>{$lang['priority']}</td>
<td class='colhead' align='center'>{$lang['status']}</td>
<td class='colhead' align='center'>{$lang['coder']}</td>
</tr>";
while ($q = mysql_fetch_assoc($res)) {
switch ($q['priority']) {
case 'low':
$priority = "<font color='green'>{$lang['low']}</font>";
break;
case 'high':
$priority = "<font color='red'>{$lang['high']}</font>";
break;
case 'veryhigh':
$priority = "<font color='red'><b><u>{$lang['veryhigh']}</u></b></font>";
break;
}
switch ($q['status']) {
case 'fixed':
$status = "<font color='green'><b>{$lang['fixed']}</b></font>";
break;
case 'ignored':
$status = "<font color='#FF8C00'><b>{$lang['ignored']}</b></font>";
break;
default:
$status = "<font color='black'><b>N/A</b></font>";
break;
}
$HTML .=  "<tr>
<td align='center'><a href='?action=viewbug&amp;id={$q['id']}'>".htmlspecialchars($q['title'])."</a></td>
<td align='center' nowrap='nowrap'>".get_date($q['added'],'TINY')." / <a href='userdetails.php?id={$q['sender']}'>{$q['username']}</a></td>
<td align='center'>{$priority}</td>
<td align='center'>{$status}</td>
<td align='center'>".($q['status'] != 'na' ? "<a href='userdetails.php?id={$q['staff']}'>{$q['staffusername']}</a>" : "---")."</td>
</tr>";
}
$HTML .= "</table>";
$HTML .= $pager['pagerbottom'];
}
else
$HTML .= "{$lang['no_bugs']}";
}
//Here we have our add function xD otherwise we wont receive any bugs :]
elseif ($action == 'add') {
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$title = $_POST['title'];
	$priority = $_POST['priority'];
	$problem = $_POST['problem'];
	if (empty($title) || empty($priority) || empty($problem))
		stderr("{$lang['stderr_error']}", "{$lang['stderr_missing']}");
		
	if (strlen($problem) < 20)
		stderr("{$lang['stderr_error']}", "{$lang['stderr_problem_20']}");
	if (strlen($title) < 10)
		stderr("{$lang['stderr_error']}", "{$lang['stderr_title_10']}");
		
	$q = mysql_query("INSERT INTO bugs (title, priority, problem, sender, added) VALUES (".sqlesc($title).", ".sqlesc($priority).", ".sqlesc($problem).", {$CURUSER['id']}, ".time().")") or sqlerr(__FILE__, __LINE__);
	if ($q)
	stderr("{$lang['stderr_sucess']}", sprintf($lang['stderr_sucess_2'], $priority));
	else
	stderr("{$lang['stderr_error']}", "{$lang['stderr_something_is_wrong']}");
}
}
else
//Default page :]
$HTML .= "<form method='post' action='{$_SERVER["PHP_SELF"]}?action=add'>
		  <table cellpadding='5' cellspacing='0' border='0' align='center'>
		  <tr><td class='rowhead'>{$lang['title']}:</td><td><input type='text' name='title' size='60'/><br/>{$lang['proper_title']}</td></tr>
		  <tr><td class='rowhead'>{$lang['problem_bug']}:</td><td><textarea cols='60' rows='10' name='problem'></textarea><br/>{$lang['describe_problem']}</td></tr>
		  <tr><td class='rowhead'>{$lang['priority']}:</td><td><select name='priority'>
		  <option value='0'>{$lang['select_one']}</option>
		  <option value='low'>{$lang['low']}</option>
		  <option value='high'>{$lang['high']}</option>
		  <option value='veryhigh'>{$lang['veryhigh']}</option>
		  </select>
		  <br/>{$lang['only_veryhigh_when']}</td></tr>
		  <tr><td colspan='2' align='center'><input type='submit' value='{$lang['submit_btn_send']}' class='btn'/></td></tr>
		  </table></form>";


print stdhead("{$lang['header']}") . $HTML . stdfoot();
?>
