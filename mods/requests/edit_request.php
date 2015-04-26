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

$rs = sql_query("SELECT r.*, c.id AS catid, c.name AS catname FROM requests AS r LEFT JOIN categories AS c ON (c.id=r.cat) WHERE r.id = $id") or sqlerr(__FILE__, __LINE__);
$numz = mysql_fetch_assoc($rs);	

if ($CURUSER['id'] != $numz['userid'] && $CURUSER['class'] < UC_MODERATOR)
    stderr('Error!', 'This is not your Request to edit.');

$s       = htmlspecialchars($numz['request']);
$catid   = $numz['catid'];
$body    = htmlspecialchars($numz['descr']);
$catname = $numz['catname'];

$s2 = "<select name='category'><option value='$catid'> $catname </option>\n";

foreach ($cats as $row)
    $s2 .= "<option value='".$row['id']."'>".htmlspecialchars($row['name'])."</option>\n";
$s2 .= "</select>\n";	

$HTMLOUT .=  "<br />
<form method='post' name='compose' action='viewrequests.php?id=$id&amp;take_req_edit'><a name='add' id='add'></a>
<table border='1' cellspacing='0' cellpadding='5'><tr><td align='left' colspan='2'>
<h1 align='center'>Edit Request : $s</h1>
</td></tr>
<tr><td align='right'><b>Title</b></td>
<td align='left'><input type='text' size='40' name='requesttitle' value='{$s}' /><b> Type</b> $s2</td></tr>
<tr><td align='right' valign='top'><b>Image</b></td><td align='left'>
<input type='text' name='picture' size='80' value='' />
<br />(Direct link to image. NO TAG NEEDED! Will be shown in description)</td></tr>
<tr><td align='right'><b>Description</b></td>

<td align='left'>";

if ($TBDEV['textbbcode'] && function_exists('textbbcode')) {
    require_once('include/bbcode_functions.php');
    $HTMLOUT .= textbbcode('edit_request', 'body', $body);
}
else
    $HTMLOUT .= "<textarea name='body' rows='10' cols='60'>$body</textarea>";
      
$HTMLOUT .=  '</td></tr>'; 

if ($CURUSER['class'] >= UC_MODERATOR) {
    $HTMLOUT .=  "<tr><td align='center' colspan='2'>Staff Only</td></tr>
    <tr><td align='right'><b>Filled</b></td>
    <td><input type='checkbox' name='filled'".($numz['torrentid'] != 0 ? " checked='checked'" : '')." /></td></tr>
    <tr><td align='right'><b>Filled by ID</b></td><td>
    <input type='text' size='10' value='$numz[filledby]' name='filledby' /></td></tr>
    <tr><td align='right'>
    <b>Torrent ID</b></td><td><input type='text' size='10' name='torrentid' value='$numz[torrentid]' /></td></tr>";
}

$HTMLOUT .=  "<tr><td align='center' colspan='2'><input type='submit' value='Edit Request' class='btn' /></td></tr></table></form><br />\n"; 

/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Edit Request').$HTMLOUT.stdfoot();
?>