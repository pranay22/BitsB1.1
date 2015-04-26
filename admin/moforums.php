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

//require "include/user_functions.php";
require "include/html_functions.php";
staffonly();

if ($CURUSER['class'] < UC_SYSOP)
stderr("Sorry", "SysOp only");


$lang = array_merge( $lang, load_language('forums') );
$id = isset($_GET['id']) && is_valid_id($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) && is_valid_id($_POST['id']) ? $_POST['id'] : 0);
$v_do = array('edit','process_edit','process_add','delete','');
$do = isset($_GET['do']) && in_array($_GET['do'],$v_do) ? $_GET['do'] : (isset($_POST['do']) && in_array($_POST['do'],$v_do) ? $_POST['do'] : '');
$this_url = 'admin.php?action=moforums';
switch($do) {
case 'delete' : 
	if(!$id)
	stderr('Err','Fool what are you doing!?');
	if(sql_query('DELETE FROM overforums where id = '.$id)) {
		header('Refresh:2; url='.$this_url);
		stderr('Success','Over Forum was deleted! wait till redirect');
	} else 
		stderr('Err','Something happened! Mysql Error '.mysql_error());
break;
case 'process_add' :
case 'process_edit' :

	foreach(array('name'=>1,'description'=>1,'minclassview'=>0,'sort'=>0) as $key=>$empty_check) {
		if($empty_check && empty($_POST[$key]))
		stderr('Err','You need to fill all the fields!');
		else 
			$$key = sqlesc($_POST[$key]);
	}
	
	switch(end(explode('_',$do))){
		case 'add':
			$res = 'INSERT INTO overforums(name,description,forid,minclassview,sort) VALUES('.$name.','.$description.', 1,'.$minclassview.','.$sort.')';
			$msg = 'Over Forum was added! Wait till redirect';
		break; 
		case 'edit':
			$res = 'UPDATE overforums set name = '.$name.', description = '.$description.',forid = 1, minclassview = '.$minclassview.', sort = '.$sort.' WHERE id = '.$id;
			$msg = 'Over Forum was edited! Wait till redirect';
		break;
	}
	if(sql_query($res)) {
		header('Refresh:2; url='.$this_url);
		stderr('Success',$msg);
	} else
		stderr('Err','Something happened! Mysql Error '.mysql_error());
break;
case 'edit' : 
default :
$htmlout = begin_main_frame().begin_frame('Over Forum manage');
$r1 = sql_query('select name, id, description, minclassview, forid, sort FROM overforums ORDER BY sort') or  sqlerr(__FILE__,__LINE__);
$f_count = mysql_num_rows($r1);
if(!$f_count)
$htmlout .= stdmsg('Error','There are no overforums, maybe you should add some');
else {
	$htmlout .= "<script type='text/javascript'>
				/*<![CDATA[*/
					function confirm_delete(id)
					{
						if(confirm('Are you sure you want to delete this forum?'))
						{
							self.location.href=\"".$this_url."&do=delete&id=\"+id;
						}
					}
				/*]]>*/
				</script>
				<table width='100%'  border='0' align='center' cellpadding='2' cellspacing='0'>
					<tr>
						<td class='colhead' align='left'>Over forum Name</td>
						<td class='colhead'>Read</td>
						<td class='colhead' colspan='2'>Modify</td>
					</tr>";
	while($a = mysql_fetch_assoc($r1))
		$htmlout .="<tr onmouseover=\"this.bgColor='#999';\" onmouseout=\"this.bgColor='';\">
						<td align='left'><a href='forums.php?action=viewforum&amp;forumid=".$a['id']."'>".htmlspecialchars($a['name'])."</a><br/><span class='small'>".$a['description']."</span></td>
						<td>".get_user_class_name($a['minclassview'])."</td>
					
						<td><a href='".$this_url."&amp;do=edit&amp;id=".$a['id']."#edit'>Edit</a></td>
						<td><a href='javascript:confirm_delete(".$a['id'].");'>Delete</a></td>
					</tr>";
	$htmlout .="</table>";
}
	$edit_action = false;
	if($do == 'edit' && !$id)
		$htmlout .= stdmsg('Edit action','Im not sure what are you trying to do');
	if($do =='edit' && $id) {
		$r3 = sql_query('select name, id, description , minclassview ,forid, sort FROM overforums WHERE id ='.$id) or sqlerr(__FILE__,__LINE__);
		if(!mysql_num_rows($r3))
			$htmlout .= stdmsg('Edit action','The Over forum your looking for does not exist');
		else {
			$edit_action = true;
			$a3 = mysql_fetch_assoc($r3);
		}
	}
	$htmlout .= end_frame().begin_frame($edit_action ? 'Edit forum <u>'.htmlspecialchars($a3['name']).'</u>' : 'Add new Over forum');
	$htmlout .= "<form action='".$this_url."' method='post'>
	<table width='100%'  border='0' align='center' cellpadding='2' cellspacing='0' id='edit'>
	<tr><td colspan='2' align='center' class='colhead'>".($edit_action ? 'Edit forum <u>'.htmlspecialchars($a3['name']).'</u>' : 'Add new Over forum')."</td></tr>
	<tr><td align='right' valign='top'>Over Forum name</td><td align='left'><input type='text' value='".($edit_action ? $a3['name'] : '')."'name='name' size='40' /></td></tr>
	<tr><td align='right' valign='top'>Over Forum description</td><td align='left'><textarea rows='3' cols='38' name='description'>".($edit_action ? $a3['description'] : '')."</textarea></td></tr>";

	$classes = "<select name='#name'>";
	for($i=UC_USER;$i<=UC_SYSOP;$i++)
		$classes .= "<option value='".$i."'>".get_user_class_name($i)."</option>";
	$classes .="</select>";
	
	if($edit_action)
	$htmlout .= "
	<tr><td align='right' valign='top'>Minimum class view</td><td align='left'>".str_replace(array('#name','value=\''.$a3['minclassview'].'\''),array('minclassview','value=\''.$a3['minclassview'].'\' selected=\'selected\''),$classes)."</td></tr>";
	else 
	$htmlout .= "
	<tr><td align='right' valign='top'>Minimum class view</td><td align='left'>".str_replace('#name','minclassview',$classes)."</td></tr>";
	$htmlout .= "<tr><td align='right' valign='top'>Over Forum rank</td>
	<td align='left'><select name='sort'>";
	for($i=0;$i<=$f_count+1;$i++)
	$htmlout .="<option value='".$i."' ".($edit_action && $a3['sort'] == $i ? 'selected=\'selected\'' : '').">".$i."</option>";
	$htmlout .="</select></td></tr>
	<tr><td align='center' class='colhead' colspan='2'>".($edit_action ? "<input type='hidden' name='do' value='process_edit' /><input type='hidden' name='id' value='".$a3['id']."'/><input type='submit' value='Edit forum' />" : "<input type='hidden' name='do' value='process_add' /><input type='submit' value='Add Over forum' />")."</td></tr>
	</table></form>";

	$htmlout .= end_frame().end_main_frame();
	print(stdhead('Over Forum manager').$htmlout.stdfoot());
}

?>