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
|   Staff-contact system v0.7
+------------------------------------------------
**/

    require_once "include/bittorrent.php";
    require_once "include/user_functions.php";
    require_once "include/pager_functions.php";
    require_once "include/html_functions.php";

    dbconn(false);
    loggedinorreturn();
	
	$lang = array_merge(load_language('global'), load_language('contactstaff'));
	
	if($_SERVER['REQUEST_METHOD']  == 'POST') {
	
		$msg = isset($_POST['msg']) ? $_POST['msg'] : '';
		$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
		$returnto = isset($_POST['returnto']) ? $_POST['returnto'] : $_SERVER['PHP_SELF'];

		if (empty($msg))
			stderr($lang['contactstaff_error'],$lang['contactstaff_no_msg']);

		if (empty($subject))
			stderr($lang['contactstaff_error'],$lang['contactstaff_no_sub']);

		if(sql_query('INSERT INTO staffmessages (sender, added, msg, subject) VALUES('.$CURUSER['id'].', '.time().', '.sqlesc($msg).', '.sqlesc($subject).')')) {
			header('Refresh: 3; url='.urldecode($returnto)); //redirect but wait 3 seconds
			stderr($lang['contactstaff_success'],$lang['contactstaff_success_msg']);
		} else
			stderr($lang['contactstaff_error'],sprintf($lang['contactstaff_mysql_err'],mysql_error()));
	} else  {
   
    $HTMLOUT  ="<form method='post' name='message' action='".$_SERVER['PHP_SELF']."'>
				 <table class='main' width='450' border='0' cellspacing='0' cellpadding='2'>
				  <tr><td class='colhead3' align='center' colspan='2' title='Support form'>
					{$lang['contactstaff_title']}</td></tr>
                    <tr><td align='center' colspan='2'>
					<font color=#ff0000><b>{$lang['contactstaff_info']}</b></font>
				  </td></tr>
				  <tr><td align='right' title='Subject'>
					<b>{$lang['contactstaff_subject']} </b>
				  </td><td align='left'>
					<input type='text' size='50' name='subject' style='margin-left: 5px;' />
				  </td></tr>
		<tr><td align='right' title='Problem Description'>
					<b>{$lang['contactstaff_problem']} </b>
				  </td><td align='center'>";
        if (isset($_GET['returnto']))
			$HTMLOUT .="<input type='hidden' name='returnto' value='".urlencode($_GET['returnto'])."' />";
        $HTMLOUT .="<textarea name='msg' cols='80' rows='15'></textarea>
                       </td>
                     </tr>
                    <tr><td align='center' colspan='2'><input type='submit' value='{$lang['contactstaff_sendit']}' class='btn' /></td></tr>
                    </table>
        </form>";

    print stdhead($lang['contactstaff_header']).$HTMLOUT . stdfoot();
	}
?>