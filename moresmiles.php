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
require_once "include/bbcode_functions.php";
dbconn(false);

$lang = array_merge( load_language('global'));

loggedinorreturn();

		$htmlout = '';
    $htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
    <meta name='generator' content='TBDev.net' />
	  <meta name='MSSmartTagsPreventParsing' content='TRUE' />
		<title>More Smilies</title>
    <link rel='stylesheet' href='./1.css' type='text/css' />
    </head>
    <body>
    <script type='text/javascript'>
    function SmileIT(smile,form,text){
    window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+' '+smile+' ';
    window.opener.document.forms[form].elements[text].focus();
    window.close();
    }
    </script>
    <table class='list' width='100%' cellpadding='1' cellspacing='1'>";
    $count='';
    while ((list($code, $url) = each($smilies))) {
    if ($count % 3 == 0)
    $htmlout .= " \n<tr>";
    $htmlout .= "\n\t<td class=\"list\" align=\"center\"><a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . htmlspecialchars($_GET["form"]) . "','" . htmlspecialchars($_GET["text"]) . "')\"><img border='0' src='./pic/smilies/" . $url . "' alt='' /></a></td>";
    $count++;
    if ($count % 3 == 0)
    $htmlout .= "\n</tr>";
    }
    $htmlout .= "</tr></table><div align='center'><a href='javascript: window.close()'>[ Close Window ]</a></div></body></html>";

print $htmlout;