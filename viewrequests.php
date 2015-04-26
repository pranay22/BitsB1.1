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
|   BitsB request system v1.3
+------------------------------------------------
**/

require_once 'include/bittorrent.php';
require_once 'include/user_functions.php';
require_once 'include/pager_functions.php';
require_once 'include/torrenttable_functions.php';
require_once 'include/bbcode_functions.php';

dbconn(false);  
loggedinorreturn();	 
parked();

/** Configuration **/
$TBDEV['req_comment_bonus'] = 50;      // amount karma received when fill request, default: 10
$TBDEV['req_cost_bonus']    = 50;       // amount karma to make request, default: 5
$TBDEV['req_min_class']     = UC_POWER_USER; // minimum class needed to use requests, default: UC_USER
$TBDEV['req_gigs_upped']    = 50;      // Upload amount in GB, default: 10
$TBDEV['req_min_ratio']     = 1.05;     // min ratio needed to use requests, default: 0.5


$lang = array_merge(load_language('global'), load_language('requests'));

define('IN_BITSB_REQUESTS', TRUE);

/** start **/
$HTMLOUT     = $sort['link'] = $filter['sql'] = $filter['link'] = $edit = $delete = $reset = '';
$sort['sql'] = ' ORDER BY added DESC ';
$cats        = genrelist();
$categ       = (isset($_GET['category']) ? (int)$_GET['category'] : 0);
$requestorid = (isset($_GET['requestorid']) ? (int)$_GET['requestorid'] : 0); 
$id          = (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if (isset($_GET['id'])) {
     if ($id < 1)
         stderr('Error', 'Bad ID!');  
}

/** valid request actions **/
$req_array = array('add_request', 
                   'new_request', 
                   'req_details', 
                   'edit_request', 
                   'take_req_edit',
                   'req_filled', 
                   'req_reset', 
                   'req_vote', 
                   'votes_view', 
                   'del_req', 
                   'staff_delete');
          
foreach ($req_array as $key) {
    if (isset($_GET[$key])) {
        require_once 'mods/requests/'.$key.'.php'; // display action
        exit();
    }
}

// else view all requests
if (isset($_GET['sort']) && $_GET['sort'] != '') {
    $sort_options = array('votes'   => ' ORDER BY hits DESC ', 
                          'cat'     => ' ORDER BY cat ', 
                          'request' => ' ORDER BY request ASC ', 
                          'added'   => ' ORDER BY added DESC ');
                          
    if (isset($sort_options[$_GET['sort']])) {
        $sort['sql']  = $sort_options[$_GET['sort']];
        $sort['link'] = $_GET['sort'];
    }
}

if (isset($_GET['filter']) && $_GET['filter'] != '') {
    $filter_options = array('true'  => ' AND requests.torrentid  = 0 ', 
                            'false' => ' AND requests.torrentid  != 0 ');
                            
    if (isset($filter_options[$_GET['filter']])) {
        $filter['sql']  = $filter_options[$_GET['filter']];
        $filter['link'] = $_GET['filter'];
    }
}
//=== end :P
$HTMLOUT .= begin_block("requests_sec_header",$caption_t="Requests section", $per=94, $tdcls="colhead5", $img="", $title='Requests Section');
$HTMLOUT .= "
<p align='right' style='margin-top: -6px; margin-bottom: -6px;'><b align='left'>Options:</b>
<a href='viewrequests.php?add_request'><input type='submit' value='Make a new request' class='btn' /></a>
<a href='viewrequests.php'><input type='submit' value='View all requests' class='btn' /></a>
<a href='?requestorid=$CURUSER[id]'><input type='submit' value='View my requests' class='btn' /></a></p></div>";
$search = (isset($_GET['search']) ? ' AND requests.request like '.sqlesc('%'.$_GET['search'].'%').' ' : '');

if ($requestorid != 0)
    $category = ($categ != 0 ? 'WHERE requests.cat = '.$categ.' AND requests.userid = '.$requestorid : 'WHERE requests.userid = '.$requestorid);
else 
    $category = ($categ != 0 ? 'WHERE requests.cat = '.$categ :  'WHERE requests.cat != ""');
  
$res = sql_query('SELECT count(requests.id) AS c FROM requests 
                  inner join users on requests.userid = users.id 
                  '.$category.' '.$filter['sql'].$search) or sqlerr(__FILE__, __LINE__);

$row = mysql_fetch_assoc($res);

$count = $row['c'];
if ($count > 0) {
    $perpage = 25; // per page
    
    $pager = pager(25, $count, 'viewrequests.php?category='.$categ.'&amp;sort='.$sort['link'].'&amp;filter='.$filter['link'].'&amp;');
    
    $res = sql_query('SELECT
                     u1.downloaded, 
                     u1.uploaded,
                     u1.leechwarn as u1leechwarn,
                     u2.leechwarn as u2leechwarn, 
                     u1.class as u1class, 
                     u2.class as u2class, 
                     u1.donor as u1donor, 
                     u2.donor as u2donor, 
                     u1.support as u1support,
                     u2.support as u2support,
                     u1.enabled as u1enabled, 
                     u2.enabled as u2enabled, 
                     u1.warned as u1warned, 
                     u2.warned as u2warned, 
                     u1.username as requester, 
                     u2.username as filler, 
                     requests.torrentid,
                     requests.filledby, 
                     requests.id, 
                     requests.userid,
                     requests.request, 
                     requests.added,
                     requests.hits, 
                     requests.cat
                     FROM requests
                     LEFT JOIN users as u1 on (requests.userid = u1.id)  
                     LEFT JOIN users as u2 on (requests.filledby = u2.id)
                     LEFT JOIN users on (requests.userid = u2.id) 
                     '.$category.' '.$filter['sql'].$search.$sort['sql'].$pager['limit']) or sqlerr(__FILE__, __LINE__);
    
    //$num = mysql_num_rows($res);
    
    $HTMLOUT .= "
<form method='get' action='viewrequests.php'><br />
<p align='left' style='margin-top: -4px; margin-bottom: -4px;'>Search:
<input type='text' class='search1' size='40' name='search' />

<select name='category'><option value='0'>(Show All)</option>";

    $catdropdown = '';
    foreach ($cats as $cat) {
       $catdropdown .= "<option value='".$cat['id']."'";
       $catdropdown .= ">".htmlspecialchars($cat['name'])."</option>\n";
    }
    
    $HTMLOUT .= "$catdropdown</select>";  
    $HTMLOUT .= "<input class='btn' type='submit' value='Search Requests' />  
<a href='viewrequests.php?category=".$categ."&amp;sort=".$sort['link']."&amp;filter=true'><input type='submit' value='Hide Filled' class='btn' /></a>
".($CURUSER['class'] >= UC_MODERATOR ?
" <a href='viewrequests.php?category=".$categ."&amp;sort=".$sort['link']."&amp;filter=false'><input type='submit' value='Only Filled' class='btn' /></a>" : '').'
</p></form>
';  
    $HTMLOUT .= end_block();
    $HTMLOUT .= '<p align="center">'.$pager['pagertop'].'</p>';
    
    $HTMLOUT .= '

<script type="text/javascript">
<!-- 
var form=\'viewreq\'
function SetChecked(val,chkName) {
dml=document.forms[form];
len = dml.elements.length;
var i=0;
for( i=0 ; i<len ; i++) {
if (dml.elements[i].name==chkName) {
dml.elements[i].checked=val;}}}
// -->
</script>
    ';
    
    $HTMLOUT .= "<form method='post' name='viewreq' action='viewrequests.php?staff_delete' onsubmit='return ValidateForm(this,\"delreq\")'>
<table width='95%' id='reqtable' border='1' cellspacing='0' cellpadding='4'><thead>
<tr><td align='center' style='text-align:center;' class='colhead' colspan='8' title='Requested Torrents'>Requested Torrents</td></tr><tr>
<td align='center' class='subheader'><a class='altlink' href='viewrequests.php?category=".$categ."&amp;filter=".$filter['link']."&amp;sort=cat' title='Type'>Type</a></td>
<td align='center' class='subheader'><a class='altlink' href='viewrequests.php?category=".$categ."&amp;filter=".$filter['link']."&amp;sort=request' title='Name'>Name</a></td>
<td align='center' class='subheader'><a class='altlink' href='viewrequests.php?category=".$categ."&amp;filter=".$filter['link']."&amp;sort=added' title='Added'>Added</a></td>
<td align='center' class='subheader'><b title='Requested By'>Requested By</b></td>
<td align='center' class='subheader'><b title='Filled?'>Filled?</b></td>
<td align='center' class='subheader'><b title='Filled By'>Filled By</b></td>
<td align='center' class='subheader'><a class='altlink' title='Votes' href='viewrequests.php?category=".$categ."&amp;filter=".$filter['link']."&amp;sort=votes'>Votes</a></td>
";
    
    if ($CURUSER['class'] >= UC_MODERATOR)
         $HTMLOUT .= "<td align='center' class='subheader'><b title='Delete'>Delete</b></td>";
    
    $HTMLOUT .= '
</tr></thead>
';
    
  //for ($i = 0; $i < $num; ++$i) {
        while ($arr = mysql_fetch_assoc($res)) {
        
        $ratio          = member_ratio($arr['uploaded'], $arr['downloaded']);
        $u1['id']       = $arr['userid'];
        $u1['username'] = $arr['requester'];
        $u1['class']    = $arr['u1class'];
        $u1['enabled']  = $arr['u1enabled'];
        $u1['donor']    = $arr['u1donor'];
        $u1['warned']   = $arr['u1warned'];
        $u1['leechwarn']   = $arr['u1leechwarn'];
        $u1['support']  = $arr['u1support'];
        $u2['id']       = $arr['filledby'];
        $u2['username'] = $arr['filler'];
        $u2['class']    = $arr['u2class'];
        $u2['enabled']  = $arr['u2enabled'];
        $u2['donor']    = $arr['u2donor'];
        $u2['warned']   = $arr['u2warned'];
        $u2['leechwarn']   = $arr['u2leechwarn'];
        $u2['support']  = $arr['u2support'];
        $u1_username    = format_user($u1);
        $u2_username    = format_user($u2);
        
        $filledby = ($arr['torrentid'] != 0 ? ($arr['filledby'] != 0 ? $u2_username : 'System') : '<small title=\'Not filled\'>Not filled</small>');
        $addedby  = '<strong>'.$u1_username.'</strong><br />Ratio: '.$ratio;
        $filled   = ($arr['torrentid'] != 0 ? '<a href="details.php?id='.$arr['torrentid'].'"><span><img src="pic/yes_r.gif" alt="" title="Filled!"></span></a>' : '<a href="viewrequests.php?id='.$arr['id'].'&amp;req_details"><span><img src="pic/no_r.gif" alt="" title="Not filled"></span></a>');
           
        foreach($cats as $key => $value)
            $change[$value['id']] = array('id'    => $value['id'], 
                                          'name'  => $value['name'], 
                                          'image' => $value['image']);
                  
        $catname = htmlspecialchars($change[$arr['cat']]['name']);
        $catpic  = htmlspecialchars($change[$arr['cat']]['image']);     	              	
        $catimage = "<img src='pic/caticons/".$catpic."' title='$catname' alt='$catname' />";
           
        $HTMLOUT .= '
<tr><td align="center">'.$catimage.'</td>
<td align="left"><a href="viewrequests.php?id='.$arr['id'].'&amp;req_details"><b title='.htmlspecialchars($arr['request']).'>'.htmlspecialchars($arr['request']).'</b></a></td>
<td align="center">'.get_date($arr['added'], '').'</td>
<td align="center">'.$addedby.'</td>
<td align="center">'.$filled.'</td>
<td align="center"><strong>'.$filledby.'</strong></td>
<td align="center"><a href="viewrequests.php?id='.$arr['id'].'&amp;votes_view"><b>'.$arr['hits'].'</b></a></td>
';
         
         if ($CURUSER['class'] >= UC_MODERATOR)
             $HTMLOUT .= '<td><input type="checkbox" name="delreq[]" value="'.$arr['id'].'" /></td>';
             
         $HTMLOUT .= '
</tr>
';
    }
    
    if ($CURUSER['class'] >= UC_MODERATOR)
        $HTMLOUT .= "<tr><td colspan='8' align='right'>
<a class='altlink' href='javascript:SetChecked(1,\"delreq[]\")'>select all</a> | 
<a class='altlink' href='javascript:SetChecked(0,\"delreq[]\")'>un-select all</a> 
<input type='submit' value='Delete Selected' class='btn' /></td></tr>";
    
    $HTMLOUT .= '
</table>
</form>

<p align="center">'.$pager['pagerbottom'].'</p>
';
}
else
    $HTMLOUT .= end_block();
    $HTMLOUT .= '<br />Nothing here! Sorry Pal! <br /><br />';


/////////////////////// HTML OUTPUT //////////////////////////////
where ("{$lang['requests_ing_req']}",$CURUSER["id"]);
print stdhead('View Requests').$HTMLOUT.stdfoot();
?>