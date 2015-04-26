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

    require_once "include/html_functions.php";
    require_once "include/user_functions.php";

    $lang = array_merge( $lang );
    staffonly();

    $HTMLOUT="";
 
    if ($CURUSER['class'] < UC_MODERATOR)
        stderr("Sorry", "No Permissions.");

    /** Configuration: maxclass, minclass **/
	$maxclass = UC_STAFFLEADER;
	$firstclass = UC_USER;
	
	function mkpositive($n)
	{
		return strstr((string)$n,"-") ? 0 : $n ; // This will return 0 for negative numbers 
	}
	
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$classes = isset($_POST["classes"])? $_POST["classes"] : "";
		$all = ($classes[0] == 255 ? true : false );
		if(empty($classes) && sizeof($classes) == 0 )
		stderr("Err","You need at least one class selected");
		$a_do = array("add","remove","remove_all");
		$do = isset($_POST["do"]) && in_array($_POST["do"],$a_do) ? $_POST["do"] : "";
		if(empty($do))
			stderr("Err","wtf are you trying to do ");
			
		$freeslots = isset($_POST["freeslots"]) ? 0+$_POST["freeslots"] : 0;
		if($freeslots == 0 && ($do == "add" || $do == "remove"))
		stderr("Err","You can't remove/add 0");
			
		$sendpm = isset($_POST["pm"]) && $_POST["pm"] == "yes" ? true : false;
		
		$pms = array();
		$users = array();
		//== Select the users
		$q1 = sql_query("SELECT id,freeslots FROM users ".($all ? "" : "WHERE class in (".join(",",$classes).")" )." ORDER BY id desc ") or sqlerr(__FILE__, __LINE__);
		if(mysql_num_rows($q1) == 0)
		stderr("Sorry","There are no users in the class(es) you selected");
			while($a = mysql_fetch_assoc($q1))
			{
				$users[] = "(".$a["id"].", ".($do == "remove_all" ? 0 : ($do == "add" ? $a["freeslots"] + $freeslots : mkpositive($a["freeslots"] - $freeslots))) .")";
				if($sendpm)
				{
					$subject = sqlesc($do == "remove_all" && $do == "remove" ?  "freeslots removed" : "freeslots added");
					$body = sqlesc("Hey,\n we have decided to ". ($do == "remove_all" ?  "remove all freeslots from your group class" : ($do == "add" ? "add $freeslots freeslot".($freeslots > 1 ? "s" : "")." to your group class" : "remove $freeslots freeslot".($freeslots > 1 ? "s" : "")."  from your group class")). " !\n ".$TBDEV['site_name'] ." staff");
					$pms[] = "(0,".$a["id"].",".sqlesc(time()).",$subject,$body)" ;
				}
			}
			
			if(sizeof($users) > 0)
				$r = sql_query("INSERT INTO users(id,freeslots) VALUES ".join(",",$users)." ON DUPLICATE key UPDATE freeslots=values(freeslots) ") or sqlerr(__FILE__, __LINE__);
			if(sizeof($pms) > 0)
				$r1 = sql_query("INSERT INTO messages (sender, receiver, added, subject, msg) VALUES ".join(",",$pms)." ") or sqlerr(__FILE__, __LINE__);
				
			if($r && ($sendpm ? $r1 : true))
			{
				header("Refresh: 2; url=admin.php?action=slotmanage");
				stderr("Success","Operation done!");
			}
			else
				stderr("Error","Something was wrong");
	}

	$HTMLOUT .= begin_frame();
	
	$HTMLOUT .="<form  action='admin.php?action=slotmanage' method='post'>
	<table width='500' cellpadding='5' cellspacing='0' border='1' align='center'>
	  <tr>
		<td valign='top' align='right'>Classes</td>
		<td width='100%' align='left' colspan='3'>";
	
					$r= "<label for='all'><input type='checkbox' name='classes[]' value='255' id='all' />All classes</label><br />\n";
				  for($i=$firstclass;$i<$maxclass+1; $i++ )
					$r .= "<label for='c$i'><input type='checkbox' name='classes[]' value='$i' id='c$i' />".get_user_class_name($i)." </label><br />\n";
				  $HTMLOUT .= $r;
		
		$HTMLOUT .="</td>
	  </tr>
	  <tr>
		<td valign='top' align='center'>Options</td>
		<td valign='top'>Do
		  <select name='do' >
			<option value='add'>Add freeslots</option>
			<option value='remove'>Remove freeslots</option>
			<option value='remove_all'>Remove all freeslots</option>
		  </select></td>
		<td>Freeslots <input type='text' maxlength='4' name='freeslots' size='5' />
		</td>
		<td>Send pm <select name='pm'><option value='no'>no</option><option value='yes'>yes</option></select></td></tr>
		<tr><td colspan='4' align='center'><input type='submit' value='Do!' /></td></tr>
	</table>
	</form>";

    $HTMLOUT .= end_frame();
    print stdhead('Freeslot Manager') . $HTMLOUT . stdfoot();
    die;
?>