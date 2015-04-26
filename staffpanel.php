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
|   Modified Staff-panel v0.9
+------------------------------------------------
**/
ob_start("ob_gzhandler");

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global') );

$HTMLOUT ='';

/**
* Staff classes config
*
* UC_XYZ  : integer -> the name of the defined class
*
* Options for a selected class
** add    : boolean -> enable/disable page adding
** edit   : boolean -> enable/disable page editing
** delete : boolean -> enable/disable page deletion
** log    : boolean -> enable/disable the loging of the actions
*
* @result $staff_classes array();
*/
$staff_classes = array(
						UC_MODERATOR 		=> array('add' => false, 	'edit' => false, 	'delete' => false,   	'log' => true),
						UC_ADMINISTRATOR 	=> array('add' => false, 	'edit' => false, 	'delete' => false,   	'log' => true),
						UC_SYSOP 			=> array('add' => false, 	'edit' => false, 	'delete' => false,		'log' => true),
                        UC_STAFF_LEADER     => array('add' => true, 	'edit' => true, 	'delete' => true,		'log' => false),
					  );

if (!isset($staff_classes[$CURUSER['class']]))
stderr('Error', 'Access Denied!');

$action = (isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : NULL));
$id = (isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : NULL));
$class_color = (function_exists('get_user_class_color') ? true : false);

    if ($action == 'delete' && is_valid_id($id) && $staff_classes[$CURUSER['class']]['delete'])
    {
	  $sure = ((isset($_GET['sure']) ? $_GET['sure'] : '') == 'yes');

	  $res = sql_query('SELECT av_class'.(!$sure || $staff_classes[$CURUSER['class']]['log'] ? ', page_name' : '').' FROM staffpanel WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	  $arr = mysql_fetch_assoc($res);
	
	  if ($CURUSER['class'] < $arr['av_class'])
		stderr('Error', 'You are not allowed to delete this page.');
	
	  if (!$sure)
		stderr('Sanity check', 'Are you sure you want to delete this page: "'.htmlspecialchars($arr['page_name']).'"? Click <a href="'.$_SERVER['PHP_SELF'].'?action='.$action.'&amp;id='.$id.'&amp;sure=yes">here</a> to delete it or <a href="'.$_SERVER['PHP_SELF'].'">here</a> to go back.');

	  sql_query('DELETE FROM staffpanel WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	
	  if (mysql_affected_rows())
	  {
		if ($staff_classes[$CURUSER['class']]['log'])
			write_log('Page "'.$arr['page_name'].'"('.($class_color ? '<font color="#'.get_user_class_color($arr['av_class']).'">' : '').get_user_class_name($arr['av_class']).($class_color ? '</font>' : '').') was deleted from the staff panel by <a href="/userdetails.php?id='.$CURUSER['id'].'">'.$CURUSER['username'].'</a>('.($class_color ? '<font color="#'.get_user_class_color($CURUSER['class']).'">' : '').get_user_class_name($CURUSER['class']).($class_color ? '</font>' : '').')');
		
		header('Location: '.$_SERVER['PHP_SELF']);
		exit();
	  }
	  else
		stderr('Error', 'There was a database error, please retry.');
    }
    else if (($action == 'add' && $staff_classes[$CURUSER['class']]['add']) || ($action == 'edit' && is_valid_id($id) && $staff_classes[$CURUSER['class']]['edit']))
    {
	 $names = array('page_name', 'file_name', 'description', 'av_class');

	 if ($action == 'edit')
	 {
	 $res = sql_query('SELECT '.implode(', ', $names).' FROM staffpanel WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	 $arr = mysql_fetch_assoc($res);
	 }
	
	 foreach ($names as $name)
	 $$name = htmlspecialchars((isset($_POST[$name]) ? $_POST[$name] : ($action == 'edit' ? $arr[$name] : '')));
	
	 if ($action == 'edit' && $CURUSER['class'] < $av_class)
		stderr('Error', 'You are not allowed to edit this page.');
	
	 if ($_SERVER['REQUEST_METHOD'] == 'POST')
	 {
		$errors = array();
		
		if (empty($page_name))
			$errors[] = 'The page name cannot be empty.';
		
		if (empty($file_name))
			$errors[] = 'The filename cannot be empty.';
		
		if (empty($description))
			$errors[] = 'The description cannot be empty.';
		
		if (!isset($staff_classes[$av_class]))
			$errors[] = 'The selected class is not a valid staff class.';
		
		if (!is_file($file_name.'.php') && !empty($file_name) && !preg_match('/.php/', $file_name))
			$errors[] = 'Inexistent php file.';
		
		if (strlen($page_name) < 4 && !empty($page_name))
			$errors[] = 'The page name is too short (min 4 chars).';
		
		if (strlen($page_name) > 80)
			$errors[] = 'The page name is too long (max 30 chars).';
		
		if (strlen($file_name) > 80)
			$errors[] = 'The filename is too long (max 30 chars).';
		
		if (strlen($description) > 100)
			$errors[] = 'The description is too long (max 100 chars).';
		
		if (empty($errors))
		{
			if ($action == 'add')
			{
				$res = sql_query("INSERT INTO staffpanel (page_name, file_name, description, av_class, added_by, added) ".
								   "VALUES (".implode(", ", array_map("sqlesc", array($page_name, $file_name, $description, (int)$av_class, (int)$CURUSER['id'], time()))).")");
				
				if (!$res)
				{
					if (mysql_errno() == 1062)
						$errors[] = "This filename is already submited.";
					else
						$errors[] = "There was a database error, please retry.";
				}
			}
			else
			{
				$res = sql_query("UPDATE staffpanel SET page_name = ".sqlesc($page_name).", file_name = ".sqlesc($file_name).", description = ".sqlesc($description).", av_class = ".sqlesc((int)$av_class)." WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
				
				if (!$res)
					$errors[] = "There was a database error, please retry.";
			}
			
			if (empty($errors))
			{
				if ($staff_classes[$CURUSER['class']]['log'])
					write_log('Page "'.$page_name.'"('.($class_color ? '<font color="#'.get_user_class_color($av_class).'">' : '').get_user_class_name($av_class).($class_color ? '</font>' : '').') in the staff panel was '.($action == 'add' ? 'added' : 'edited').' by <a href="/userdetails.php?id='.$CURUSER['id'].'">'.$CURUSER['username'].'</a>('.($class_color ? '<font color="#'.get_user_class_color($CURUSER['class']).'">' : '').get_user_class_name($CURUSER['class']).($class_color ? '</font>' : '').')');
				
				header('Location: '.$_SERVER['PHP_SELF']);
				exit();
			}
		}
	}
	

	$HTMLOUT .= begin_main_frame();
	
	if (!empty($errors))
	{
	$HTMLOUT .= stdmsg('There '.(count($errors)>1?'are':'is').' '.count($errors).' error'.(count($errors)>1?'s':'').' in the form.', '<b>'.implode('<br />', $errors).'</b>');
	$HTMLOUT .="<br />";
	}

	
  $HTMLOUT .="<form method='post' action='{$_SERVER['PHP_SELF']}'>
	<input type='hidden' name='action' value='{$action}' />";
	if ($action == 'edit')
	{
  $HTMLOUT .="<input type='hidden' name='id' value='{$id}' />";
	}
	
	
    $HTMLOUT .="<table cellpadding='5' width='100%' align='center'>
    <tr class='colhead'>
    <td colspan='2'>
     ".($action == 'edit' ? 'Edit "'.$page_name.'"' : 'Add a new').' page'."</td>
    </tr>
    <tr>
    <td class='rowhead' width='1%'>Page name</td><td align='left'><input type='text' size='50' name='page_name' value='{$page_name}' /></td>
    </tr>
    <tr>
    <td class='rowhead'>Filename</td><td align='left'><input type='text' size='50' name='file_name' value='{$file_name}' /><b></b></td>
    </tr>
    <tr>
    <td class='rowhead'>Description</td><td align='left'><input type='text' size='50' name='description' value='{$description}' /></td>
    </tr>
    <tr>
    <td class='rowhead'><span style='white-space: nowrap;'>Available for</span></td>
    <td align='left'>
    <select name='av_class'>";
  
     foreach ($staff_classes as $class => $value)
     {
     if ($CURUSER['class'] < $class)
     continue;
     $HTMLOUT .= '<option'.($class_color? ' style="background-color:#'.get_user_class_color($class).';"' : '').' value="'.$class.'"'.($class == $av_class ? ' selected="selected"' : '').'>'.get_user_class_name($class).'</option>';
     }
     
	   $HTMLOUT .="</select>
     </td>
     </tr>
     </table>
    
     <table class='main'>
     <tr>
     <td align='center'></td>
     <td style='border:none;' align='center'><input type='submit' value='Submit' /></td>
     <td style='border:none;'>
     <form method='post' action='{$_SERVER['PHP_SELF']}'><input type='submit' value='Cancel' /></form>
		 </td>
     </tr>
     </table></form>";
	   
	  $HTMLOUT .= end_main_frame(); 
	  print stdhead('Staff Panel :: '.($action == 'edit' ? 'Edit "'.$page_name.'"' : 'Add a new').' page') . $HTMLOUT . stdfoot();
    }
    else
    { 
	  $HTMLOUT .= begin_main_frame();
	  $HTMLOUT .="<h1 align='center'>Welcome {$CURUSER['username']} to the Staff Panel!</h1><br />";

	  if ($staff_classes[$CURUSER['class']]['add'])
	  {
		$HTMLOUT .= stdmsg('Options', '<a href="staffpanel.php?action=add" title="Add a new page">Add a new page</a>');
	  $HTMLOUT .="<br />";
	  }
	
	  $res = sql_query('SELECT staffpanel.*, users.username '.
					   'FROM staffpanel '.
					   'LEFT JOIN users ON users.id = staffpanel.added_by '.
					   'WHERE av_class <= '.sqlesc($CURUSER['class']).' '.
					   'ORDER BY av_class DESC, page_name ASC') or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) > 0)
	{
	$db_classes = $unique_classes = $mysql_data = array();
	while ($arr = mysql_fetch_assoc($res))
	$mysql_data[] = $arr;
		
		foreach ($mysql_data as $key => $value)
		$db_classes[$value['av_class']][] = $value['av_class'];
		
		$i=1;
		foreach ($mysql_data as $key => $arr)
		{
	  $end_table = (count($db_classes[$arr['av_class']]) == $i ? true : false);

			if (!in_array($arr['av_class'], $unique_classes))
			{
			$unique_classes[] = $arr['av_class'];

      $HTMLOUT .="<table cellpadding='5' width='100%' align='center'". (!isset($staff_classes[$arr['av_class']]) ? 'style="background-color:#000000;"' : '').">
      <tr>
      <td colspan='4' align='center'>
      <h2>".($class_color ? '<font color="#'.get_user_class_color($arr['av_class']).'">' : '').get_user_class_name($arr['av_class']).' Panel'.($class_color ? '</font>' : '')."</h2>
      </td>
      </tr>
      <tr align='center'>
      <td class='colhead' align='left' width='100%'>Page name</td>
      <td class='colhead'><span style='white-space: nowrap;'>Added by</span></td>
      <td class='colhead'><span style='white-space: nowrap;'>Date added</span></td>";
      
      if ($staff_classes[$CURUSER['class']]['edit'] || $staff_classes[$CURUSER['class']]['delete'])
      {
      $HTMLOUT .="<td class='colhead'>Links</td>";
      }
      $HTMLOUT .="</tr>";
			}
			
			$HTMLOUT .="<tr align='center'>
			<td align='left'>
      <a href='".htmlspecialchars($arr['file_name'])."' title='".htmlspecialchars($arr['page_name'])."'>
      ".htmlspecialchars($arr['page_name'])."</a><br /><font class='small'>".htmlspecialchars($arr['description'])."</font>
			</td>
      <td>
		  <a href='userdetails.php?id=".(int)$arr['added_by']."'>{$arr['username']}</a>
      </td>
      <td>
      <span style='white-space: nowrap;'>".get_date($arr['added'], 'LONG',0,1)."<br /></span>
      </td>";
			if ($staff_classes[$CURUSER['class']]['edit'] || $staff_classes[$CURUSER['class']]['delete'])
			{
			$HTMLOUT .="<td>
      <span style='white-space: nowrap;'>";
			if ($staff_classes[$CURUSER['class']]['edit'])
			{
			$HTMLOUT .="<a href='staffpanel.php?action=edit&amp;id=".(int)$arr['id']."' <img src='{$TBDEV['pic_base_url']}/staff_tools/tool_edit.png' alt='E' style='opacity:0.4;' onmouseover='this.style.opacity=1;' onmouseout='this.style.opacity=0.5;' title='Edit' /></a>";
			}
						
		  if ($staff_classes[$CURUSER['class']]['delete'])
			{
			$HTMLOUT .="&nbsp<a href='staffpanel.php?action=delete&amp;id=".(int)$arr['id']."' <img src='{$TBDEV['pic_base_url']}/staff_tools/tool_delete.png' alt='D' style='opacity:0.4;' onmouseover='this.style.opacity=1;' onmouseout='this.style.opacity=0.5;' title='Delete' /></a>";
			}
			$HTMLOUT .="</span>
			</td>";
			}
			$HTMLOUT .="</tr>";
			
			$i++;
			if ($end_table)
			{
		  $i=1;
			$HTMLOUT .="</table><br />";
			}
		  }
	    }
	    else
		  $HTMLOUT .= stdmsg('Sorry', 'Nothing found.');
	    $HTMLOUT .= end_main_frame(); 
print stdhead("Staff Panel") . $HTMLOUT . stdfoot();
}
?>