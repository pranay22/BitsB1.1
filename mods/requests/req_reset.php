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
|   BitsB request system v1.2
+------------------------------------------------
**/

if (!defined('IN_BITSB_REQUESTS')) exit('No direct script access allowed');

$HTMLOUT .= "<table class='main' width='750px' border='0' cellspacing='0' cellpadding='0'>" .
      "<tr><td class='embedded'>\n";

$res = sql_query("SELECT userid, filledby, request, torrentid FROM requests WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res);

if (($CURUSER['id'] == $arr['userid']) || ($CURUSER['class'] >= UC_MODERATOR) || ($CURUSER['id'] == $arr['filledby'])) {

 if ($TBDEV['karma'] && isset($CURUSER['seedbonus']) && $arr['torrentid'] != 0)
     sql_query("UPDATE users SET seedbonus = seedbonus-".$TBDEV['req_comment_bonus']." WHERE id = $arr[filledby]") or sqlerr(__FILE__, __LINE__);

 sql_query("UPDATE requests SET torrentid = 0, filledby = 0 WHERE id = $id") or sqlerr(__FILE__, __LINE__);
 
$HTMLOUT .=  "<h1 align='center'>Success!</h1>".
"<p align='center'>Request $id (".htmlspecialchars($arr['request']).") successfully reset.</p>
<p align='center'><a class='altlink' href='viewrequests.php'><b>View all requests</b></a></p><br /><br />";

}
else{
$HTMLOUT .=  "<table>
<tr><td class='colhead' align='left'><h1>Error!</h1></td></tr><tr><td align='left'>".
"Sorry, cannot reset a request when you are not the owner, staff or person filling it.<br /><br /></td></tr>
</table>";
}

$HTMLOUT .= "</td></tr></table>\n"; 

/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Reset Request').$HTMLOUT.stdfoot();
?>