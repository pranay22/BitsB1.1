<?
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

	require_once "include/bittorrent.php";
	require_once "include/user_functions.php";
	staffonly();

	if (get_user_class() < UC_ADMINISTRATOR)
		stderr("Error", "There was an error in the request to load this page.");

	dbconn(false);
	loggedinorreturn();

	$res2 = sql_query("SELECT agent, peer_id FROM peers GROUP BY agent") or sqlerr(__FILE__, __LINE__);

	$HTMLOUT = '';
	$HTMLOUT .= "<table align='center' border='3' cellspacing='0' cellpadding='5'>
			<tr>
				<td class='colhead'>Client</td>
				<td class='colhead'>Peer ID</td>
			</tr>";
	while($arr2 = mysql_fetch_assoc($res2))
	{
		$HTMLOUT .= "<tr>
				<td align='left'><b>$arr2[agent]</b></td>
				<td align='left'><b>$arr2[peer_id]</b></td>
			</tr>";
	}
	$HTMLOUT .= "</table>";
    print stdhead("Detect Clients", false) . $HTMLOUT . stdfoot(); 
?>