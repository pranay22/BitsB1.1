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

require_once ("include/html_functions.php");
$lang = array_merge( $lang, load_language('whois') );
staffonly();

$htmlout = '';
if (get_user_class() < UC_ADMINISTRATOR)
stderr("{$lang['stderr_error']}", "{$lang['text_denied']}");
if (phpversion() >= "4.2.0") {
extract($_POST);
extract($_GET);
extract($_SERVER);
extract($_ENV);
}
$htmlout .='<script type="text\javascript">
function m(el) {
if (el.defaultValue==el.value) el.value = ""
}
</script>';
$htmlout .= begin_frame("{$lang['stdhead_whois']}",true);
$htmlout .= '<div align="center"><form method="post" action="admin.php?action=whois">
<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr bgcolor="#9999FF">
<td width="50%" bgcolor="#000000"><font size="2" face="Verdana,Arial, Helvetica, sans-serif" color="#FFFFFF"><b>'.$lang["text_host"].'</b></font></td>
<td bgcolor="#000000"><font size="2" face="Verdana, Arial,Helvetica, sans-serif" color="#FFFFFF"><b>'.$lang["text_host1"].'</b></font></td>
</tr>
<tr valign="top" bgcolor="#CCCCFF">
<td bgcolor="#FF0000">
<p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
<input type="radio" name="queryType" value="lookup"/>'.$lang["text_resolve"].'<br />
<input type="radio" name="queryType" value="dig"/>'.$lang["text_hole"].'<br />
<input type="radio" name="queryType" value="wwwhois"/>'.$lang["text_web"].'<br />
<input type="radio" name="queryType" value="arin"/>'.$lang["text_ipif"].'</font></p>
</td>
<td bgcolor="#FF0000"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
<input type="radio" name="queryType" value="checkp"/>'.$lang["text_chkpo"].'
<input type="text" name="portNum" size="5" maxlength="5" value="80"/><br />
<input type="radio" name="queryType" value="p"/>'.$lang["text_pingh"].'<br />
<!--<input type="radio" name="queryType" value="trace"/>'.$lang["text_tracer"].'<br />-->
<input type="radio" name="queryType" value="all" checked="checked"/>'.$lang["text_all"].'</font></td>
</tr>
</table>
<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr bgcolor="#9999FF">
<td colspan="2" bgcolor="#FFFF00">
<div align="center">
<input type="text" name="target" value="'.isset($_GET["ip"]).'" onfocus="m(this)"/>
<input type="submit" name="Submit" value="'.$lang["btn_doit"].'"/>
</div>
</td>
</tr>
</table>
</form>
</div>';
$htmlout .= end_frame();

// Global kludge for new gethostbyaddr() behavior in PHP 4.1x
$ntarget = "";
function lookup($target)
{
global $ntarget,$lang;
$htmlout ='';
$htmlout .= '<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="padding-left:2cm;border:none;"><font size="2" face="Verdana,
Arial, Helvetica, sans-serif" color="black"><b>'.$lang["result_resolve"].'</b><br />';
$htmlout .= "$target ".$lang["result_resolve1"]." ";
if (@eregi("[a-zA-Z]", $target))
$ntarget = gethostbyname($target);
else
$ntarget = gethostbyaddr($target);
$htmlout .= $ntarget;
$htmlout .= "</font></td></tr></table><br />";
 return $htmlout;
}

function dig($target)
{
global $ntarget,$lang;
$htmlout ='';
$htmlout .='<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="padding-left:2cm;border:none;"><font size="2" face="Verdana,Arial, Helvetica, sans-serif" color="black"><b>'.$lang["result_dns"].'</b><br />';
// $target = gethostbyaddr($target);
// if (! eregi("[a-zA-Z]", ($target = gethostbyaddr($target))) )
if ((!@eregi("[a-zA-Z]", $target) && (!@eregi("[a-zA-Z]", $ntarget))))
$htmlout .= "".$lang["result_dns1"]."";
else {
if (!@eregi("[a-zA-Z]", $target)) $target = $ntarget;
if (! $htmlout .= trim(nl2br(`dig any '$target'`))) // bugfix
$htmlout .= "".$lang["result_dns2"]."";
}
// TODO: Clean up output, remove ;;'s and DiG headers
$htmlout .= "</font></td></tr></table><br />";
return $htmlout;   
}
function wwwhois($target)
{
global $ntarget,$lang;
$buffer='';
$htmlout ='';
$server = "whois.crsnic.net";
$htmlout .='<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="padding-left:2cm;border:none;"><font size="2" face="Verdana,Arial, Helvetica, sans-serif" color="black"><b>'.$lang["result_www"].'</b><br />';
// Determine which WHOIS server to use for the supplied TLD
if ((@eregi("\.com\$|\.net\$|\.edu\$", $target)) || (@eregi("\.com\$|\.net\$|\.edu\$", $ntarget)))
$server = "whois.crsnic.net";
else if ((@eregi("\.info\$", $target)) || (@eregi("\.info\$", $ntarget)))
$server = "whois.afilias.net";
else if ((@eregi("\.org\$", $target)) || (@eregi("\.org\$", $ntarget)))
$server = "whois.corenic.net";
else if ((@eregi("\.name\$", $target)) || (@eregi("\.name\$", $ntarget)))
$server = "whois.nic.name";
else if ((@eregi("\.biz\$", $target)) || (@eregi("\.biz\$", $ntarget)))
$server = "whois.nic.biz";
else if ((@eregi("\.us\$", $target)) || (@eregi("\.us\$", $ntarget)))
$server = "whois.nic.us";
else if ((@eregi("\.cc\$", $target)) || (@eregi("\.cc\$", $ntarget)))
$server = "whois.enicregistrar.com";
else if ((@eregi("\.ws\$", $target)) || (@eregi("\.ws\$", $ntarget)))
$server = "whois.nic.ws";
else if ((@eregi("\.it\$", $target)) || (@eregi("\.it\$", $ntarget)))
$server = "whois.nic.it";
else{
$htmlout .= "".$lang["result_www1"]."";     
return;
}
$htmlout .="".$lang["result_www2"]." $server...<br />";
if (! $sock = fsockopen($server, 43, $num, $error, 10)) {
unset($sock);
$htmlout .= "".$lang["result_www3"]."";
}else{
fputs($sock, "$target\n");
while (!feof($sock))
$buffer .= fgets($sock, 10240);
}
fclose($sock);
if (!@eregi("".$lang["result_www4"]."$server (port 43)", $buffer)) {
if (@eregi("no match", $buffer))
$htmlout .="".$lang["result_www5"]."$target<br />";
else
$htmlout .="".$lang["result_www6"]."$target:<br />";
}else{
$buffer = split("\n", $buffer);
for ($i = 0; $i < sizeof($buffer); $i++) {
if (eregi("".$lang["result_www7"]."", $buffer[$i]))
$buffer = $buffer[$i];
}
$nextServer = substr($buffer, 17, (strlen($buffer)-17));
$nextServer = str_replace("1:Whois Server:", "", trim(rtrim($nextServer)));
$buffer = "";
$htmlout .="".$lang["result_www8"]."$nextServer...<br />";
if (!$sock = fsockopen($nextServer, 43, $num, $error, 10)) {
unset($sock);
$htmlout .= "".$lang["result_www9"]."$nextServer (port 43)";
}else{
fputs($sock, "$target\n");
while (!feof($sock))
$buffer .= fgets($sock, 10240);
fclose($sock);
}
}
$htmlout .= nl2br($buffer);
$htmlout .= "</font></td></tr></table><br />";
return $htmlout;  
}
function arin($target)
{   
global $lang;
$htmlout = '';
$buffer = '';
$extra='';
$nextServer='';
$server = "whois.arin.net";
$Server = $server;
$htmlout .='<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="padding-left:2cm;border:none;"><font size="2" face="Verdana,Arial, Helvetica, sans-serif" color="black"><b>'.$lang["result_arin"].'</b><br />';
if (!$target = gethostbyname($target))
$htmlout .= "Without IP address is the only feasible difficult ;)";
else{
$htmlout .="Connect to $Server ...<br />";
if (!$sock = fsockopen($server, 43, $num, $error, 20)) {
unset($sock);
$htmlout .= "".$lang["result_arin1"]."$server (port 43)";
}else{
fputs($sock, "$target\n");
while (!feof($sock))
$buffer .= fgets($sock, 10240);
fclose($sock);
}
if (@eregi("RIPE.NET", $buffer))
$nextServer = "whois.ripe.net";
else if (@eregi("whois.apnic.net", $buffer))
$nextServer = "whois.apnic.net";
else if (@eregi("nic.ad.jp", $buffer)) {
$nextServer = "whois.nic.ad.jp";
// /e suppresses Japanese character output from JPNIC
$extra = "/e";
}else if (@eregi("whois.registro.br", $buffer))
$nextServer = "whois.registro.br";
if ($nextServer) {
$buffer = "";
$htmlout .="".$lang["result_arin2"]."$nextServer...<br />";
if (!$sock = fsockopen($nextServer, 43, $num, $error, 10)) {
unset($sock);
$htmlout .= "".$lang["result_arin3"]."Time-out connection to the $nextServer (port 43)";
}else{
fputs($sock, "$target$extra\n");
while (!feof($sock))
$buffer .= fgets($sock, 10240);
fclose($sock);
}
}
$buffer = str_replace(" ", "&nbsp;", $buffer);
$htmlout .= nl2br($buffer);
}
$htmlout .= "</font></td></tr></table><br />";
return $htmlout;
}
function checkp($target, $portNum)
{   
global $lang;
$htmlout ='';
$htmlout .='<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="padding-left:2cm;border:none;"><font size="2" face="Verdana,Arial, Helvetica, sans-serif" color="black"><b>'.$lang["result_port"].'Check Port '.$portNum.'</b><br />';
$sock = fsockopen($target, $portNum, $num, $error, 5);
if (!$sock)
$htmlout .= "".$lang["result_port1"]." $portNum ".$lang["result_port2"]."";	
else{
$htmlout .= "".$lang["result_port1"]." $portNum ".$lang["result_port3"]."";
}
$htmlout .= "</font></td></tr></table><br />";
return $htmlout;
}
function p($target)
{
global $lang;
$htmlout = '';
$htmlout .='<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="padding-left:2cm;border:none;"><font size="2" face="Verdana,
Arial, Helvetica, sans-serif" color="black"><b>'.$lang["result_ping"].'</b><br />';
if (!$htmlout .= trim(nl2br(`ping -c5 '$target'`))) // bugfix
$htmlout .= "".$lang["result_ping1"]."";
$htmlout .= "</font></td></tr></table>";
return $htmlout;  
}

function trace($target){
global $lang;
$htmlout ='';
$htmlout .='<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="padding-left:2cm;border:none;"><font size="2" face="Verdana,Arial, Helvetica, sans-serif" color="black"><b>'.$lang["result_trace"].'</b><br />';
if (!$htmlout .= trim(nl2br(`/usr/sbin/traceroute '$target'`))) #bugfix
$htmlout .= "".$lang["result_trace1"]."";
$htmlout .= "</font></td></tr></table>";
return $htmlout;
}
// If the form has been posted, process the query, otherwise there's
// nothing to do y='';et
if (!isset($queryType)){
print stdhead() . $htmlout . stdfoot();
die;
}
// Make sure the target appears valid
if ((!$target) || (!preg_match("/^[\w\d\.\-]+\.[\w\d]{1,4}$/i", $target))) { // bugfix
$htmlout .='<br />'.begin_frame('Error',true).'<table width="70%" border="0" cellspacing="0" cellpadding="1">
<tr>
<td style="border:none;" align="center"><font size="2" face="Verdana,Arial, Helvetica, sans-serif" color="#FFFFFF">Error: You have no valid IP or host.</b></font></td></tr></table>'.end_frame();
print stdhead() . $htmlout . stdfoot();
die;
}
$htmlout .="<br /><br />";
$htmlout .= begin_frame("".$lang["result_result"]." $target",true);
// Figure out which tasks to perform, and do them
if (($queryType == "all") || ($queryType == "lookup"))
$htmlout .= lookup($target);
if (($queryType == "all") || ($queryType == "dig"))
$htmlout .= dig($target);
if (($queryType == "all") || ($queryType == "wwwhois"))
$htmlout .= wwwhois($target);
if (($queryType == "all") || ($queryType == "arin"))
$htmlout .= arin($target);
// Set error reporting for this
error_reporting(E_ERROR);
if (($queryType == "all") || ($queryType == "checkp"))
$htmlout .= checkp($target,$portNum); 
// Restore original error reporting value
@ini_restore('error_reporting');	
if (($queryType == "all") || ($queryType == "p"))
$htmlout .= p($target);
//if (($queryType == "all") || ($queryType == "tr"))
// $htmlout .= trace($target);
$htmlout .= end_frame();	

print stdhead("{$lang['stdhead_whois']}") . $htmlout . stdfoot();
die;
?>
