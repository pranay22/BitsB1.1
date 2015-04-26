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
    require_once "include/html_functions.php";
    require_once "include/bbcode_functions.php";

    dbconn(false);
    loggedinorreturn();
	
	function mkint($x) {
		return 0+$x;
	}

    $lang = array_merge(load_language('global'), load_language('staffbox'));

    if ($CURUSER['class'] < UC_MODERATOR)
        stderr($lang['staffbox_err'], $lang['staffbox_class']);
		$valid_do = array('view','delete','setanswered','restart','');
		
	$do = isset($_GET['do']) && in_array($_GET['do'],$valid_do) ? $_GET['do']  : (isset($_POST['do']) && in_array($_POST['do'],$valid_do) ? $_POST['do'] : '');
	$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) && is_array($_POST['id']) ? array_map('mkint',$_POST['id']) : 0);
	$message = isset($_POST['message']) && !empty($_POST['message']) ? $_POST['message'] : '';
	$reply = isset($_POST['reply']) && $_POST['reply'] == 1 ? true : false;
	
	switch($do) {
	case 'delete' : 
		if($id > 0) {
			if(mysql_query('DELETE FROM staffmessages WHERE id IN ('.join(',',$id).')')) {
				header('Refresh: 2; url='.$_SERVER['PHP_SELF']);
				stderr($lang['staffbox_success'],$lang['staffbox_delete_ids']);
			} else 
				stderr($lang['staffbox_err'],sprintf($lang['staffbox_sql_err'],mysql_error()));
		} else 
			stderr($lang['staffbox_err'],$lang['staffbox_odd_err']);
	break;
	case 'setanswered' : 
		if($id > 0) {
			if($reply && empty($message)) {
				stderr($lang['staffbox_err'],$lang['staffbox_no_message']);
				exit;
			}
			
			$q = mysql_query('SELECT s.msg,s.sender,s.subject,u.username FROM staffmessages as s LEFT JOIN users as u ON s.sender=u.id WHERE s.id IN ('.join(',',$id).')') or sqlerr(__FILE__,__LINE__);
			$a = mysql_fetch_assoc($q);
			$response = htmlspecialchars($message)."\n---". $a['username']." wrote ---\n".$a['msg'];
			mysql_query('INSERT INTO messages(sender,receiver,added,subject,msg) VALUES('.$CURUSER['id'].','.$a['sender'].','.time().','.sqlesc('RE: '.$a['subject']).','.sqlesc($response).')') or sqlerr(__FILE__,__LINE__);
			
			$message = ', answer='.sqlesc($message);
			if(mysql_query('UPDATE staffmessages SET answered=\'1\', answeredby='.$CURUSER['id'].' '.$message.' WHERE id IN ('.join(',',$id).')')) {
				header('Refresh: 2; url='.$_SERVER['PHP_SELF']);
				stderr($lang['staffbox_success'],$lang['staffbox_setanswered_ids']);
			} else 
				stderr($lang['staffbox_err'],sprintf($lang['staffbox_sql_err'],mysql_error()));
		} else 
			stderr($lang['staffbox_err'],$lang['staffbox_odd_err']);			
	break;
	case 'view' :
		if($id > 0) {
			$q = mysql_query('SELECT s.id, s.added, s.msg, s.subject, s.answered, s.answer, s.answeredby, s.sender, s.answer, u.username , u2.username as username2 
						FROM staffmessages  as s
						LEFT JOIN users as u ON s.sender = u.id 
						LEFT JOIN users as u2 ON s.answeredby = u2.id 
						WHERE s.id = '.$id) or sqlerr(__FILE__,__LINE__);
			if(mysql_num_rows($q) == 1) {
				$a = mysql_fetch_assoc($q);
				$HTMLOUT = begin_main_frame().begin_frame($lang['staffbox_pm_view']);
					$HTMLOUT .= "<form action='".$_SERVER['PHP_SELF']."' method='post'>
								<table width='80%' border='1' cellspacing='0' cellpadding='5' align='center'>
								 <tr><td>{$lang['staffbox_pm_from']}&nbsp;<a href='userdetails.php?id=".$a['sender']."'>".$a['username']."</a> at ".get_date($a['added'],'DATE',1)."<br/>
								 {$lang['staffbox_pm_subject']} : <b>".htmlspecialchars($a['subject'])."</b><br/>
								 {$lang['staffbox_pm_answered']} : <b>".($a['answeredby'] > 0 ? "<a href='userdetails.php?id=".$a['answeredby']."'>".$a['username2']."</a>" : "<span style='color:#ff0000'>No</span>")."</b>
								</td></tr>
								<tr><td>".format_comment($a['msg'])."
								</td></tr>
								<tr><td>{$lang['staffbox_pm_answer']}<br/>
									".($a['answeredby'] == 0 ? "<textarea rows='5' cols='75' name='message' ></textarea>" : ($a['answer'] ? format_comment($a['answer']) : "<b>{$lang['staffbox_pm_noanswer']}</b>"))."
								</td></tr>
								<tr><td align='left'>
									<select name='do'>
										<option value='setanswered' ".($a['answeredby'] > 0 ? 'disabled=\'disabled\'' : "" )." >{$lang['staffbox_pm_reply']}</option>
										<option value='restart' ".($a['answeredby'] != $CURUSER['id'] ? 'disabled=\'disabled\'' : "" )." >{$lang['staffbox_pm_restart']}</option>
										<option value='delete'>{$lang['staffbox_pm_delete']}</option>
									</select>
									<input type='hidden' name='reply' value='1'/>
									<input type='hidden' name='id[]' value='".$a['id']."'/><input type='submit' value='{$lang['staffbox_confirm']}' />
									</td></tr>
								</table>
								</form>";
				$HTMLOUT.= end_frame().end_main_frame();
				print(stdhead('StaffBox').$HTMLOUT.stdfoot());
			} else 
			stderr($lang['staffbox_err'],$lang['staffbox_msg_noid']);
		} else
			stderr($lang['staffbox_err'],$lang['staffbox_odd_err']);
	break;
	case 'restart' : 
		if($id > 0) {
			if(mysql_query('UPDATE staffmessages SET answered=\'0\', answeredby=\'0\' WHERE id IN ('.join(',',$id).')')) {
				header('Refresh: 2; url='.$_SERVER['PHP_SELF']);
				stderr($lang['staffbox_success'],$lang['staffbox_restart_ids']);
			} else 
				stderr($lang['staffbox_err'],sprintf($lang['staffbox_sql_err'],mysql_error()));
		} else 
			stderr($lang['staffbox_err'],$lang['staffbox_odd_err']);
	break;
	default: 
	$count_msgs = get_row_count('staffmessages');
    
	$perpage = 4;
    $pager = pager($perpage, $count_msgs, 'staffbox.php?');

   

    if (!$count_msgs)
		stderr($lang['staffbox_err'],$lang['staffbox_no_msgs']);
	else {
	
	$HTMLOUT = begin_main_frame().begin_frame($lang['staffbox_info']);
    $HTMLOUT .= $pager['pagertop'];

	$HTMLOUT .="<form method='post' name='staffbox' action='".$_SERVER['PHP_SELF']."'>";
    $HTMLOUT .="<table width='90%' border='1' cellspacing='0' cellpadding='5' align='center'>";
    $HTMLOUT .="<tr>
                 <td class='colhead' align='center' width='100%'>{$lang['staffbox_subject']}</td>
                 <td class='colhead' align='center'>{$lang['staffbox_sender']}</td>
                 <td class='colhead' align='center'>{$lang['staffbox_added']}</td>
                 <td class='colhead' align='center'>{$lang['staffbox_answered']}</td>
                 <td class='colhead' align='center'><input type='checkbox' name='t' onclick=\"checkbox('staffbox')\" /></td>
                </tr>";

    $r = mysql_query('SELECT s.id, s.added, s.subject, s.answered, s.answeredby, s.sender, s.answer, u.username , u2.username as username2 
						FROM staffmessages  as s
						LEFT JOIN users as u ON s.sender = u.id 
						LEFT JOIN users as u2 ON s.answeredby = u2.id 
						ORDER BY id desc '.$pager['limit']) or sqlerr(__FILE__, __LINE__);
						
    while ($a = mysql_fetch_assoc($r))
		$HTMLOUT .="<tr>
                   <td align='center'><a href='".$_SERVER['PHP_SELF']."?do=view&amp;id=".$a['id']."'>" .htmlspecialchars($a['subject']). "</a></td>
                   <td align='center'><b>".($a['username'] ? "<a href='userdetails.php?id=".$a['sender']."'>".$a['username']."</a>" : "Unknown[".$a['sender']."]")."</b></td>
                   <td align='center' nowrap='nowrap'>" .get_date($a['added'],'DATE',1)."<br/><span class='small'>".get_date($a['added'],0,0,1)."</span></td>
				   <td align='center'><b>".($a['answeredby'] > 0 ? "by <a href='userdetails.php?id=".$a['answeredby']."'>".$a['username2']."</a>" : "<span style='color:#ff0000'>No</span>")."</b></td>
                   <td align='center'><input type='checkbox' name='id[]' value='" . $a['id'] . "' /></td>
                  </tr>\n";

	$HTMLOUT .="<tr><td align='right' colspan='5'>
					<select name='do'>
						<option value='delete'>{$lang['staffbox_do_delete']}</option>
						<option value='setanswered'>{$lang['staffbox_do_set']}</option>
					</select>
					<input type='submit' value='{$lang['staffbox_confirm']}' /></td></tr>
				</table></form>";
				
    $HTMLOUT .= $pager['pagerbottom'];

    $HTMLOUT .= end_frame().end_main_frame();
    }
    print stdhead($lang['staffbox_head']) . $HTMLOUT . stdfoot();
	}

?>