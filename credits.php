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
require_once("include/user_functions.php");
require_once("include/html_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('credits') );

$HTMLOUT = "";

$action = isset($_GET["action"]) ? htmlspecialchars(trim($_GET["action"])) : '';
 
$act_validation = array('', 'add', 'edit', 'delete', 'update');

$id = 0 + (isset($_GET['id']) ? $_GET["id"] : "");

if(!in_array($action, $act_validation))

stderr("Error", "Unknown action.");

/*Check if CutName function exists, if not declare it */

if (!function_exists('CutName'))
{
function CutName ($txt, $len)
{
return (strlen($txt)>$len ? substr($txt,0,$len-4) .'[...]':$txt);
}
}

    if (isset($_POST['action']) == 'add' && $CURUSER['class'] >= UC_SYSOP){
    $name = ($_POST['name']);
    $description = ($_POST['description']);
    $category = ($_POST['category']);
    $link = ($_POST['link']);
    $status = ($_POST['status']);
    $credit = ($_POST['credit']);
    mysql_query("INSERT INTO modscredits (name, description,  category,  tbdevlnk,  status, credit) VALUES(" . sqlesc($name) . ", " . sqlesc($description) . ", " . sqlesc($category) . ", " . sqlesc($link) . ", " . sqlesc($status) . ", " . sqlesc($credit) . ")") or sqlerr(__FILE__, __LINE__);
	  header("Location: {$TBDEV['baseurl']}/credits.php");
	  die();
	  }

	if ($action == 'delete' && $CURUSER['class'] >= UC_SYSOP)
	{
	if (!$id) { stderr("{$lang['credits_error']}", "{$lang['credits_error2']}");}
	mysql_query("DELETE FROM modscredits where id = '$id'") or sqlerr(__FILE__, __LINE__);
	header("Location: {$TBDEV['baseurl']}/credits.php");
	die();
  }

  if ($action == 'edit' && $CURUSER['class'] >= UC_SYSOP){
	$id = 0 + $_GET["id"];
	$res = mysql_query("SELECT name, description, category, tbdevlnk, status, credit FROM modscredits WHERE id =".$id."") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) == 0)
	stderr("{$lang['credits_error']}", "{$lang['credits_nocr']}");
	while($mod = mysql_fetch_assoc($res)){

	$HTMLOUT .= "<form method='post' action='".$_SERVER['PHP_SELF']."?action=update&amp;id=".$id."'>
  <table width='50%' cellpadding='10' cellspacing='1' border='1'>
	<tr><td class='rowhead'>{$lang['credits_mod']}</td>" .
	"<td align='left' style='padding: 0px'><input type='text' size='60' maxlength='120' name='name' " . "value='".htmlspecialchars($mod['name'])."' /></td></tr>\n".
	"<tr>
	<td class='rowhead'>{$lang['credits_description']}</td>" .
	"<td align='left' style='padding: 0px'>
	<input type='text' size='60' maxlength='120' name='description' value='".htmlspecialchars($mod['description'])."' /></td></tr>\n".
	"<tr>
	<td class='rowhead'>{$lang['credits_category']}</td>
  <td align='left' style='padding: 0px'>
  <select name='category'>";

  $result=mysql_query('SHOW COLUMNS FROM modscredits WHERE field=\'category\'');
  while ($row=mysql_fetch_row($result))
  {
  foreach(explode("','",substr($row[1],6,-2)) as $v)
  {
  $HTMLOUT .="<option value='$v". ($mod["category"] == $v ? " selected" : "") . "'>$v</option>";
  }
  }

  $HTMLOUT .="</select></td></tr>";

  $HTMLOUT .="<tr><td class='rowhead'>{$lang['credits_link']}</td>" .
	"<td align='left' style='padding: 0px'><input type='text' size='60' maxlength='120' name='link' " . "value='".htmlspecialchars($mod['tbdevlnk'])."' /></td></tr>\n".
  "<tr>
  <td class='rowhead'>{$lang['credits_status']}</td>
  <td align='left' style='padding: 0px'>
  <select name='modstatus'>";

  $result=mysql_query('SHOW COLUMNS FROM modscredits WHERE field=\'status\'');
  while ($row=mysql_fetch_row($result))
  {
  foreach(explode("','",substr($row[1],6,-2)) as $y)
  {
  $HTMLOUT .="<option value='$y". ($mod["status"] == $y ? " selected" : "") . "'>$y</option>";
  }
  }

  $HTMLOUT .="</select></td></tr>";

  $HTMLOUT .="<tr><td class='rowhead'>{$lang['credits_credits']}</td><td align='left' style='padding: 0px'>
  <input type='text' size='60' maxlength='120' name='credits' value='".htmlspecialchars($mod['credit'])."' /></td></tr>\n";
	$HTMLOUT .="<tr><td colspan='2' align='center'><input type='submit' value='Submit' /></td></tr>\n";
	$HTMLOUT .="</table></form>";
	}
	print stdhead($lang['credits_editmod']) . $HTMLOUT . stdfoot();
	exit();
  }
  else

  if ($action == 'update' && $CURUSER['class'] >= UC_SYSOP){
	$id = 0 + $_GET["id"];
	if (!is_valid_id($id))
	stderr('Error', 'Invalid ID!');
	$res = mysql_query('SELECT id FROM modscredits WHERE id = '.sqlesc($id));
	if (mysql_num_rows($res) == 0)
	stderr("{$lang['credits_error']}", "{$lang['credits_nocr']}");
	
	$name = $_POST['name'];
	$description = $_POST['description'];
	$category = $_POST['category'];
	$link = $_POST['link'];
	$modstatus = $_POST['modstatus'];
	$credit = $_POST['credits'];
	
  if (empty($name))
		stderr("{$lang['credits_error']}", "{$lang['credits_error3']}");
	
	if (empty($description))
		stderr("{$lang['credits_error']}", "{$lang['credits_error4']}");
		
	if (empty($link))
		stderr("{$lang['credits_error']}", "{$lang['credits_error5']}");
		
	if (empty($credit))
		stderr("{$lang['credits_error']}", "{$lang['credits_error6']}");
	
	mysql_query("UPDATE modscredits SET name = ".sqlesc($name).", category = ".sqlesc($category).", status = ".sqlesc($modstatus).",  tbdevlnk = ".sqlesc($link).", credit = ".sqlesc($credit).", description = ".sqlesc($description)." WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
  header("Location: {$_SERVER['PHP_SELF']}");
	exit();
  }

  $HTMLOUT .="<script type='text/javascript'>
  <!--
  function confirm_delete(id)
  {
	if(confirm('Are you sure you want to delete this mod credit?'))
	{
	self.location.href='".$_SERVER["PHP_SELF"]."?action=delete&id='+id;
	}
  }
  //-->
  </script>";
/*
Begin displaying the mods
*/
/*Query the db*/
  $res = mysql_query("SELECT * FROM modscredits") or sqlerr(__FILE__, __LINE__);
//Begin displaying the table
    
    $HTMLOUT .="<table width='80%' cellpadding='10' cellspacing='1' border='1'>
    <tr>
    <td align='center' class='colhead'>{$lang['credits_name']}</td>
    <td align='center' class='colhead'>{$lang['credits_category']}</td>
    <td align='center' class='colhead'>{$lang['credits_status']}</td>
    <td align='center' class='colhead'>{$lang['credits_credits']}</td>
    </tr>";
    
  if($row = mysql_fetch_array($res)){
   do
   {
      $id = $row["id"];
      $name = $row["name"];
      $category =$row["category"];
      if($row["status"]=="In-Progress"){
      $status = "[b][color=#ff0000]".$row["status"]."[/color][/b]";
      }else{
      $status = "[b][color=#018316]".$row["status"]."[/color][/b]";
      }
      $link = $row["tbdevlnk"];
      $credit = $row["credit"];
      $descr = $row["description"];
      
      $HTMLOUT .="<tr><td><a target='_blank' class='altlink' href='".$link."'>".htmlspecialchars(CutName($name,60)) ."</a>";
      if (get_user_class() >= UC_ADMINISTRATOR){
      $HTMLOUT .="&nbsp<a class='altlink_blue' href='?action=edit&amp;id=".$id."'>{$lang['credits_edit']}</a>&nbsp<a class='altlink_blue' href=\"javascript:confirm_delete(".$id.");\">{$lang['credits_delete']}</a>";
      
      }

      $HTMLOUT .="<br /><font class='small'>".htmlspecialchars($descr)."</font></td>";
      $HTMLOUT .="<td><b>".htmlspecialchars($category)."</b></td>";
      $HTMLOUT .="<td><b>".format_comment($status)."</b></td>";
      $HTMLOUT .="<td>".htmlspecialchars($credit)."</td></tr>";
      }
   
    while($row = mysql_fetch_array($res));
    }
    else
    {
    $HTMLOUT .="<tr><td colspan='4'>{$lang['credits_nosofar']}</td></tr>";
    }

    $HTMLOUT .="</table>";
  
	  if ($CURUSER['class'] >= UC_SYSOP) //I recommend a higher class like UC_CODER
	  {
	  $HTMLOUT .="<br/>
	  <form method='post' action='".$_SERVER['PHP_SELF']."'>
	  <table width='80%' cellpadding='8' border='1' cellspacing='0'>
    <tr>
		<td colspan='2' class='colhead'>
    {$lang['credits_add']}
		<input type='hidden' name='action' value='add' /></td>
	  </tr>
  	
  	<tr>
		<td>{$lang['credits_name1']}</td>
		<td><input name='name' type='text' size='120' /></td>
		</tr>
		<tr>
		<td>{$lang['credits_description1']}</td><td><input name='description' type='text' size='120' maxlength='120' /></td>
		</tr>
				
		<tr>
		<td>{$lang['credits_category1']}</td><td>
		
    <select name='category'>
    <option value='Addon'>{$lang['credits_addon']}</option>
    <option value='Forum'>{$lang['credits_forum']}</option>
    <option value='Message/Email'>{$lang['credits_mes']}</option>
    <option value='Display/Style'>{$lang['credits_disp']}</option>
    <option value='Staff/Tools'>{$lang['credits_staff']}</option>
    <option value='Browse/Torrent/Details'>{$lang['credits_btd']}</option>
    <option value='Misc'>{$lang['credits_misc']}</option>
    </select>
        
	  </td>
		</tr>
		<tr>
		<td>{$lang['credits_link1']}</td><td><input name='link' type='text' size='50' /></td>
		</tr>
		<tr>
		<td>{$lang['credits_status1']}</td><td>
		<select name='status'>
    <option value='In-Progress'>{$lang['credits_progress']}</option>
    <option value='Complete'>{$lang['credits_complete']}</option>
    </select>
		
		</td>
		</tr>
	  <tr>
	  <td>{$lang['credits_credits1']}</td><td><input name='credit' type='text' size='120' maxlength='120' /><br  /><font class='small'>{$lang['credits_val']}</font></td>
	  </tr>
	  <tr>
		<td colspan='2'>
		<input type='submit' value='{$lang['credits_addc']}' />
		</td>
	  </tr>
	  </table></form>";   
    }
print stdhead($lang['credits_headers']) . $HTMLOUT . stdfoot();
?>