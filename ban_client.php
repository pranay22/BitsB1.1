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

require_once ("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/html_functions.php";
require_once "include/bt_client_functions.php";
dbconn(false);

loggedinorreturn();

	$lang = array_merge( load_language('global'), load_language('client_ban') );
	
 $HTMLOUT = '';

if (get_user_class() < UC_ADMINISTRATOR)
{
stderr("{$lang['stderr_error']}", "{$lang['text_denied']}");
}
else
{
	(isset($_GET['agent']) ? $agent=urldecode($_GET['agent']) : $agent='');
	(isset($_GET['peer_id']) ? $peer_id=urldecode($_GET['peer_id']) : $peer_id='');
	(isset($_GET['returnto']) ? $url=urldecode($_GET['returnto']) : $url='index.php');
	(isset($_POST['confirm']) ? $confirm=$_POST['confirm'] : $confirm='');
	(isset($_POST['reason']) ? $reason=$_POST['reason'] : $reason='');
	(isset($_POST['banall']) ? $banall='yes' : $banall='no');
	$peer_id_ascii=hex2bin($peer_id);
	$client=getagent($agent, $peer_id);
	
	$filename = 'include/banned_clients.txt';
	if (filesize($filename)==0 || !file_exists($filename))
 	$banned_clients=array();
	else
	{
 	$handle = fopen($filename, 'r');
 	$banned_clients = unserialize(fread($handle, filesize($filename)));
 	fclose($handle);
	}
if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
	if($_POST['confirm'])
	{
 	if($confirm=='Yes' && $reason!='')
 	{
 	$banned=0;

 	foreach($banned_clients as $k => $v)
 	{
 	if(substr($peer_id, 0, (($banall=='yes') ? 6 : 16 )) == $v['peer_id'])
 	$banned=1;
 	}

 	if($banned==1)
 	{
 	stderr("Error", "Already Banned");
 	}
 	
 	
 	if(empty($banned_clients))
 	{
 	if($banall=='yes')
 	{
 	$client=substr($client, 0, stripos($client, ' '))." (All versions)";
 	$banned_clients[1]=array('peer_id' => substr($peer_id, 0, 6), 'peer_id_ascii' => substr($peer_id_ascii, 0, 3), 'user_agent' => 'N/A', 'client_name' => $client, 'reason' => $reason);
 	}
 	else
 	$banned_clients[1]=array('peer_id' => substr($peer_id, 0, 16), 'peer_id_ascii' => substr($peer_id_ascii, 0, 8), 'user_agent' => $agent, 'client_name' => $client, 'reason' => $reason);
 	}
 	else
 	{
 	if($banall=="yes")
 	{
 	$client=substr($client, 0, stripos($client, " "))." (All versions)";
 	$banned_clients[]=array('peer_id' => substr($peer_id, 0, 6), 'peer_id_ascii' => substr($peer_id_ascii, 0, 3), 'user_agent' => 'N/A', 'client_name' => $client, 'reason' => $reason);
 	}
 	else
 	$banned_clients[]=array('peer_id' => substr($peer_id, 0, 16), 'peer_id_ascii' => substr($peer_id_ascii, 0, 8), 'user_agent' => $agent, 'client_name' => $client, 'reason' => $reason); 	
 	}
 	$data=serialize($banned_clients);
 	
 	$fd = fopen($filename, "w") or die("Can't update $filename, please CHMOD it to 777");
 	fwrite($fd,$data) or die("Can't save file");
 	fclose($fd);
 	
 	stderr("{$lang['client_ban_success']}","{$lang['client_ban_added']} <a href='$url'>Return</a>");
 	
 	
 	exit();
 	}
 	elseif($confirm=="No")
 	{
 	stderr("{$lang['client_ban_notadd']}","{$lang['client_ban_notadd1']} <a href='$url'>Return</a>");
exit();
 	}
 	else
 	{
 	stderr("{$lang['client_ban_error']}","{$lang['client_ban_reason1']}");
 	
 	exit();
 	}
	}
	}
	$HTMLOUT .="<p align='center'>{$lang['client_ban_by']}</p>
	<form method='post' name='action'>
	<table align='center' width=70%>
 	<tr>
 <td class='header' align='center'><strong>{$lang['client_ban_client']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_agent']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_peerid']}</strong></td>
 	<td class='header' align='center'><strong>{$lang['client_ban_peerasc']}</strong></td>
 	</tr>
 	<tr>
		<td align='center'>".$client."</td>
 	<td align='center'>".$agent."</td>
 	<td align='center'>".$peer_id."</td>
 	<td align='center'>".$peer_id_ascii."</td>
 	</tr>
 	<tr>
 	<td align='right'><strong>{$lang['client_ban_reason']}</strong></td>
 	<td colspan='3'><input type='text' name='reason' value='' size='70' maxlength='255'>
 	&nbsp;&nbsp;&nbsp;<strong>{$lang['client_ban_all']}</strong><input type='checkbox' name='banall'></td>
 	</tr>
 	<tr>
 	<td class='block' colspan='4'>&nbsp</td>
 	<tr>
	</table>
	<p align='center'>{$lang['client_ban_sure']}</p>
	<center>
	<input type='submit' name='confirm' value='Yes'>&nbsp;<input type='submit' name='confirm' value='No'>
	<center></form>
";
}
print stdhead("{$lang['client_ban_stdhead']}") . $HTMLOUT . stdfoot();




?>