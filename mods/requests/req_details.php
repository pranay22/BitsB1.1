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

if (!defined('IN_REQUESTS')) exit('No direct script access allowed');

$res = sql_query('SELECT r.*, r.added as utadded, u.username 
                  FROM requests AS r LEFT JOIN users AS u ON (u.id=r.userid) 
                  WHERE r.id = '.$id) or sqlerr(__FILE__, __LINE__);

if (!mysql_num_rows($res))
    stderr('Error', 'Invalid request ID');
   
$num = mysql_fetch_assoc($res);	 
 
$added = get_date($num['utadded'], '');
$s     = htmlspecialchars($num['request']);

$HTMLOUT .=  '<h3>Details Of Request: '.$s.'</h3>';

$HTMLOUT .=  "<table border='1' width='750px' cellspacing='0' cellpadding='5'><tr><td align='center' colspan='2'><h1>$s</h1></td></tr>";

if ($num['descr']) {
    require_once 'include/bbcode_functions.php';
    $HTMLOUT .=  "<tr><td align='right' valign='top'><b>Description</b></td>
    <td align='left' colspan='2' valign='top'>".format_comment($num['descr'])."</td></tr>";
}

$HTMLOUT .=  "<tr><td align='right'><b>Added</b></td>
<td align='left'>$added</td></tr>";
   
if ($CURUSER['id'] == $num['userid'] || $CURUSER['class'] >= UC_MODERATOR) {
    $edit = " | <a class='altlink' href='viewrequests.php?id=".$id."&amp;edit_request'>Edit Request</a> |";
    $delete = " <a class='altlink' href='viewrequests.php?id=".$id."&amp;del_req'>Delete Request</a> "; 

    if ($num['torrentid'] != 0)
        $reset = "| <a class='altlink' href='viewrequests.php?id=".$id."&amp;req_reset'>Re-set Request</a>";
}

$HTMLOUT .=  "<tr>
<td align='right'><b>Requested&nbsp;By</b></td><td align='left'>
<a class='altlink' href='userdetails.php?id=$num[userid]'>{$num['username']}</a>  $edit  $delete $reset  |
<a class='altlink' href='viewrequests.php'><b>All requests</b></a> </td></tr><tr><td align='right'>
<b>Vote for this request</b></td><td align='left'><a href='viewrequests.php?id=".$id."&amp;req_vote'><b>Vote</b></a>
</td></tr>
".($TBDEV['reports'] ? "<tr><td align='right'><b>Report Request</b></td><td align='left'>
for breaking the rules 
<form action='report.php?type=Request&amp;id=$id' method='post'><input class='btn' type='submit' name='submit' value='Report Request' /></form></td>
</tr>" : ''); 

if ($num['torrentid'] == 0) 
    $HTMLOUT .=  "<tr><td align='right' valign='top'><b>Fill This Request</b></td>
<td>".($CURUSER['id'] != $num['userid'] ? "
<form method='post' action='viewrequests.php?id=".$id."&amp;req_filled'>
    <strong>".$TBDEV['baseurl']."/details.php?id=</strong><input type='text' size='10' name='torrentid' value='' /> <input type='submit' value='Fill Request' class='btn' /><br />
Enter the <b>ID</b>  of the torrent. (copy/paste the <strong>ID</strong> from another window/tab the correct ID number)<br /></form>" : 'This Request is yours therefore you may NOT fill it.')."</td>
</tr>\n";
else
    $HTMLOUT .= "<tr><td align='right' valign='top'><b>This Request was filled:</b></td><td><a class='altlink' href='details.php?id=".$num['torrentid']."'><b>".$TBDEV['baseurl']."/details.php?id=".$num['torrentid']."</b></a></td></tr>";	


$HTMLOUT .= "<tr><td class='embedded' colspan='2'><p><a name='startcomments'></a></p>\n";

$commentbar = "<p align='center'><a class='index' href='comment.php?action=add&amp;tid=$id&amp;type=request'>Add Comment</a></p>\n";

$subres = sql_query("SELECT COUNT(*) FROM comments WHERE request = $id");
$subrow = mysql_fetch_array($subres);
$count = $subrow[0];

$HTMLOUT .=  '</td></tr></table>'; 

if (!$count)
    $HTMLOUT .= '<h2>No comments</h2>';
else {
    $pager = pager(25, $count, "viewrequests.php?id=$id&amp;req_details&amp;", array('lastpagedefault' => 1));

$subres = sql_query("SELECT comments.id, comments.text, comments.user, comments.editedat, 
                      comments.editedby, comments.ori_text, comments.request AS request, 
                      comments.added, comments.anonymous, users.avatar, users.av_w ,users.av_h,
                      users.warned, users.username, users.title, users.class, users.last_access, 
                      users.enabled, users.reputation, users.donor, users.downloaded, users.uploaded 
                      FROM comments LEFT JOIN users ON comments.user = users.id 
                      WHERE request = $id ORDER BY comments.id") or sqlerr(__FILE__, __LINE__);
			 
 $allrows = array();
 while ($subrow = mysql_fetch_assoc($subres))
         $allrows[] = $subrow;

 $HTMLOUT .= $commentbar;
 $HTMLOUT .= $pager['pagertop'];
 require_once 'include/html_functions.php';
 $HTMLOUT .= commenttable($allrows, 'request');
 $HTMLOUT .= $pager['pagerbottom'];
}
 $HTMLOUT .= $commentbar; 

/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Request Details').$HTMLOUT.stdfoot();
?>