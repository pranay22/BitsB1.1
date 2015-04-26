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

$res2 = sql_query('select count(voted_requests.id) AS c from voted_requests inner join users on voted_requests.userid = users.id inner join requests on voted_requests.requestid = requests.id WHERE voted_requests.requestid ='.$id) or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res2);

$count = (int)$row['c'];

if ($count > 0) {
    $pager = pager(25, $count, 'viewrequests.php?');

    $res = sql_query('select users.id as userid,users.username, users.support, users.leechwarn, users.downloaded, users.title, users.class, users.donor, users.warned, users.enabled, users.uploaded, requests.id as requestid, requests.request, requests.added from voted_requests inner join users on voted_requests.userid = users.id inner join requests on voted_requests.requestid = requests.id WHERE voted_requests.requestid ='.$id.' '.$pager['limit']) or sqlerr(__FILE__, __LINE__);
  
    $res2 = sql_query("select request from requests where id=$id");
    $arr2 = mysql_fetch_assoc($res2);
    
    $HTMLOUT .= "<h1>Voters for <a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>".htmlspecialchars($arr2['request'])."</b></a></h1>";
    
    $HTMLOUT .= "<p>Vote for this <a class='altlink' href='viewrequests.php?id=$id&amp;req_vote'><b>request</b></a></p>";
    
   	$HTMLOUT .= $pager['pagertop'];
    
    if (mysql_num_rows($res) == 0)
        $HTMLOUT .=  "<p align='center'><b>Nothing found</b></p>\n";
    else {
        $HTMLOUT .=  "<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='colhead'>Username</td><td class='colhead' align='left'>Uploaded</td><td class='colhead' align='left'>Downloaded</td>
<td class='colhead' align='left'>Share Ratio</td></tr>\n";

         while ($arr = mysql_fetch_assoc($res)) {
            
            $ratio      = member_ratio($arr['uploaded'], $arr['downloaded']);       
            $uploaded   = mksize($arr['uploaded']);
            $joindate   = get_date($arr['added'], '');
            $downloaded = mksize($arr["downloaded"]);
            $enabled    = ($arr['enabled'] == 'no' ? '<span style="color:red;">No</span>' : '<span style="color:green;">Yes</span>');
            $arr['id'] = $arr['userid'];
            $username   = format_user($arr);
            
             $HTMLOUT .=  "<tr><td><b>$username</b></td>
             <td align='left'>$uploaded</td>
             <td align='left'>$downloaded</td>
             <td align='left'>$ratio</td></tr>\n";
         }
         $HTMLOUT .=  "</table>\n";
    }
    $HTMLOUT .=  $pager['pagerbottom'];
}
else
    $HTMLOUT .=  'Nothing here!';
    
/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Voters').$HTMLOUT.stdfoot();
?>