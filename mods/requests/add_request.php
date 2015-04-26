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

if ($CURUSER['class'] < $TBDEV['req_min_class']) {
    $HTMLOUT .= "<h1>Oops!</h1>
    <div class='some class'>You must be ".get_user_class_name($TBDEV['req_min_class'])." or above <b>AND</b> have a ratio above <b>".$TBDEV['req_min_ratio']."</b> to make a request.
    <br /><br /> Please see the <a href='faq.php'><b>FAQ</b></a> 
    for more information on different user classes and what they can do.<br /><br />
    <b>".$TBDEV['site_name']." staff</b></div>";

    /////////////////////// HTML OUTPUT //////////////////////////////
    print stdhead('Requests Page').$HTMLOUT.stdfoot();
    die();
}

$gigsneeded = ($TBDEV['req_gigs_upped']*1024*1024*1024);
$gigsupped = $CURUSER['uploaded'];
$ratio = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 0); 

if ($CURUSER['class'] < UC_VIP) {
	$gigsdowned = $CURUSER['downloaded'];
	if ($gigsdowned >= $gigsneeded)
	    $gigs = $CURUSER['uploaded'] / (1024*1024*1024);
}

$HTMLOUT .= '<h3>Request Rules</h3>';

$HTMLOUT .= 'To make a request you must have a ratio of at least<b> '.$TBDEV['req_min_ratio'].'</b> AND have uploaded at least <b>'.$TBDEV['req_gigs_upped'].' GB</b>.<br />'.
($TBDEV['karma'] ? " A request will also cost you <b><a class='altlink' href='mybonus.php'>".$TBDEV['req_cost_bonus']." Karma Points</a></b>....<br /><br />" :'')." 
In your particular case <a class='altlink' href='userdetails.php?id=".$CURUSER['id']."'>".$CURUSER['username'].'</a>, ';	


    if ($TBDEV['karma'] && isset($CURUSER['seedbonus']) && $CURUSER['seedbonus'] < $TBDEV['req_cost_bonus']) {
        $HTMLOUT .= "you do not have enough <a class='altlink' href='mybonus.php'>Karma Points</a> ...
        you can not make requests.<p>To view all requests, click 
        <a class='altlink' href='viewrequests.php'><b>here</b></a></p>\n<br /><br />";
}
elseif ($gigsupped < $gigsneeded && $CURUSER['class'] < UC_VIP) {
    $HTMLOUT .= "you have <b>not</b> yet uploaded <b>".$TBDEV['req_gigs_upped']." GB</b>... you can not make requests.<p>
    To view all requests, click <a class='altlink' href='viewrequests.php'><b>here</b></a></p>\n
    <br /><br />";
}
elseif ($ratio < $TBDEV['req_min_ratio'] && $CURUSER['class'] < UC_VIP) {
        $sss = ($gigsupped < $gigsneeded ? 's' : '');
	    $HTMLOUT .=
	      "your ratio of <b>".member_ratio($CURUSER['uploaded'], $CURUSER['downloaded'])."</b>".
	      ($gigsupped < $gigsneeded ? ' and your total uploaded of<b> '.round($gigs, 2).' GB</b>' : '').
	     " fail$sss to meet the minimum requirements. to Make a Request.<br /><br />
         <p>To view all requests, click <a href='viewrequests.php'><b>here</b></a></p>\n<br /><br />";
	}
else {
    $HTMLOUT .= "you <b>can</b> make requests.<p>To view all requests, click 
    <a class='altlink' href='viewrequests.php'>here</a></p>\n";

/** search first **/
$HTMLOUT .= "<form method='get' action='browse.php'><table width='780px' border='1' cellspacing='0' cellpadding='5'><tr><td class='colhead' align='left'>
Please search torrents before adding a request!</td></tr><tr><td align='left'>
<input type='text' name='search' size='40' value='' class='search1' /> in <select name='cat'> <option value='0'>(all types)</option>
";

$catdropdown = '';
foreach ($cats as $cat) {
   $catdropdown .= "<option value='".$cat['id']."'";
   if ($cat['id'] == (isset($_GET['cat']) ? $_GET['cat'] : ''))
   $catdropdown .= " selected='selected'";
   $catdropdown .= ">".htmlspecialchars($cat['name'])."</option>\n";
}

$deadchkbox = "<input type='checkbox' name='incldead' value='1'";

if (isset($_GET['incldead']))
$deadchkbox .= " checked='checked'";
$deadchkbox .= " /> including dead torrents\n";
$HTMLOUT .= " ".$catdropdown." </select> ".$deadchkbox." 
<input type='submit' value='Search!' class='btn' /></td></tr></table></form>
<br />\n";

$HTMLOUT .= "<form method='post' name='compose' action='viewrequests.php?new_request'><a name='add' id='add'></a>
<table border='1' cellspacing='0' width='750px' cellpadding='5'><tr><td class='colhead' align='left' colspan='2'>
Requests are for Users with a good ratio who have uploaded at least ".$TBDEV['req_gigs_upped']." gigs Only... Share and you shall recieve!</td></tr>
<tr><td align='right'><b>Title</b></td><td align='left'><input type='text' size='40' name='requesttitle' />
<select name='category'><option value='0'>(Select a Category)</option>\n";

$res2 = sql_query('SELECT id, name FROM categories order by name');
$num  = mysql_num_rows($res2);

$catdropdown2 = '';
for ($i = 0; $i < $num; ++$i) {
 $cats2 = mysql_fetch_assoc($res2);  
 $catdropdown2 .= "<option value='".$cats2['id']."'";
 $catdropdown2 .= ">".htmlspecialchars($cats2['name'])."</option>\n";
   }
   
$HTMLOUT .= $catdropdown2." </select></td></tr>
<tr><td align='right' valign='top'><b>Image</b></td>
<td align='left'>
<input type='text' name='picture' size='80' /><br />
(Direct link to image, NO TAGS NEEDED! Will be shown in description)<br />
<!--
<a href='panel.php?tool=bitbucket' rel='external'><strong>Upload Image</strong></a>
-->
</td></tr>

<tr><td align='right'><b>Description</b></td><td align='left'>\n";

if ($TBDEV['textbbcode'] && function_exists('textbbcode')) {
     require_once('include/bbcode_functions.php');
    $HTMLOUT .= textbbcode('add_request', 'body', '');
}
else
    $HTMLOUT .= "<textarea name='body' rows='20' cols='80'></textarea>";

$HTMLOUT .= "</td></tr>
<tr><td align='center' colspan='2'>
<input type='submit' value='Okay' class='btn' /></td></tr></table>
</form>
<br /><br />\n";
}
/*
$rescount = mysql_query('SELECT id FROM requests LIMIT 1') or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($rescount) > 0) {

$res = mysql_query("SELECT users.username, requests.id, requests.userid, requests.cat, requests.request, requests.added, uploaded, downloaded FROM users left join requests ON requests.userid = users.id order by requests.id desc LIMIT 10") or sqlerr();
$num = mysql_num_rows($res);

    $HTMLOUT .= "<table border='1' cellspacing='0' width='750px' cellpadding='5'>
    <tr><td width='50px' class='colhead' align='left'>Category</td>
    <td class='colhead' align='left'>Request</td><td class='colhead' align='center'>Added</td>
    <td class='colhead' align='center'>Requested By</td></tr>\n";
   
foreach($cats as $key => $value)
    $change[$value['id']]=array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
      
while($arr = mysql_fetch_assoc($res)) {
    
    $addedby  = "<td style='padding: 0px' align='center'><b><a href='userdetails.php?id=$arr[userid]'>$arr[username]</a></b></td>";
    $catname  = htmlspecialchars($change[$arr['cat']]['name']);
    $catpic   = htmlspecialchars($change[$arr['cat']]['image']);       	
    $catimage = "<img src='pic/caticons/".$catpic."' title='$catname' alt='$catname' />";
    
    $HTMLOUT .= "<tr>
    <td align='center'>".$catimage."</td>
    <td align='left'><a href='viewrequests.php?id=$arr[id]&amp;req_details'>
    <b>".htmlspecialchars($arr['request'])."</b></a></td>
    <td align='center'>".get_date($arr['added'], '')."</td>
    $addedby
    </tr>\n";
}
$HTMLOUT .= "<tr><td align='center' colspan='4'>
<form method='get' action='viewrequests.php'>
<input type='submit' value='Show All' class='btn' />
</form>
</td></tr>
</table>\n";
}
*/
/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Requests Page').$HTMLOUT.stdfoot();
?>