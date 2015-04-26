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
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);

loggedinorreturn();

$lang = array_merge( load_language('global'), load_language('mybonus') );

$HTMLOUT ='';

function I_smell_a_rat($var){
 if ((0 + $var) == 1)
 	$var = 0 + $var;
 else
 	stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_rat_msg']}");
}

$bonus = htmlspecialchars($CURUSER['seedbonus'], 1);

switch (true){
case (isset($_GET['up_success'])):
I_smell_a_rat($_GET['up_success']);

$amt = (int)$_GET['amt'];

switch ($amt) {
case $amt == 200.0:
	$amt = '2 GB';
	break;
case $amt == 350.0:
	$amt = '5 GB';
	break;
default:
	$amt = '20 GB';
}

$HTMLOUT .= "<table align='center' width='80%'>
<tr>
<td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td>
</tr><tr>
<td class='clearalt6' align='left'><img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td>
<td class='clearalt6' align='left'><b>{$lang['bonus_congrat']}</b>".$CURUSER['username']."{$lang['bonus_uploadinc']}".$amt."!
<img src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='{$lang['bonus_img_woot']}' title='{$lang['bonus_img_woot']}' /><br /><br /><br /><br />{$lang['bonus_goback']}<br /><br /></td>
</tr>
</table>";
print stdhead('Bonus Karma') . $HTMLOUT . stdfoot();
die;

case (isset($_GET['class_success'])):
I_smell_a_rat($_GET['class_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td></tr>
<tr><td align='left' class='clearalt6'><img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td><td align='left' class='clearalt6'>
<b>{$lang['bonus_congrat']}</b>".$CURUSER['username']."{$lang['bonus_vip']}<img src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='{$lang['bonus_img_woot']}' title='{$lang['bonus_img_woot']}' /><br />
<br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['smile_success'])):
I_smell_a_rat($_GET['smile_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td></tr>
<tr><td align='left' class='clearalt6'><img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td><td align='left' class='clearalt6'>
<b>{$lang['bonus_congrat']}</b>".$CURUSER['username']."{$lang['bonus_smilies']}<img src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='{$lang['bonus_img_woot']}' title='{$lang['bonus_img_woot']}' /><br />
<br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['warning_success'])):
I_smell_a_rat($_GET['warning_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td></tr>
<tr><td align='left' class='clearalt6'><img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td><td align='left' class='clearalt6'>
<b>{$lang['bonus_congrat']}</b>".$CURUSER['username']."{$lang['bonus_warning']}<img src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='{$lang['bonus_img_woot']}' title='{$lang['bonus_img_woot']}' /><br />
<br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['invite_success'])):
I_smell_a_rat($_GET['invite_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td></tr><tr><td align='left' class='clearalt6'>
<img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td><td align='left' class='clearalt6'>
<b>{$lang['bonus_congrat']}</b>".$CURUSER['username']."{$lang['bonus_invites']}<img src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='{$lang['bonus_img_woot']}' title='{$lang['bonus_img_woot']}' /><br /><br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['title_success'])):
I_smell_a_rat($_GET['title_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td></tr><tr>
<td align='left' class='clearalt6'><img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td><td align='left' class='clearalt6'>
<b>{$lang['bonus_congrat']}</b>".$CURUSER['username']."{$lang['bonus_knownas']}<b>".$CURUSER['title']."</b>! <img src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='{$lang['bonus_img_woot']}' title='{$lang['bonus_img_woot']}' /><br />
<br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['ratio_success'])):
I_smell_a_rat($_GET['ratio_success']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td></tr><tr>
<td align='left' class='clearalt6'><img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td><td align='left' class='clearalt6'><b>{$lang['bonus_congrat']}</b> ".$CURUSER['username']."{$lang['bonus_ratio']}<img src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='{$lang['bonus_img_woot']}' title='{$lang['bonus_img_woot']}' /><br />
<br />{$lang['bonus_goback']}<br /><br />
</td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['gift_fail'])):
I_smell_a_rat($_GET['gift_fail']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Huh?</h1></td></tr><tr><td align='left' class='clearalt6'>
<img src='{$TBDEV['pic_base_url']}smilies/cry.gif' alt='{$lang['bonus_img_bad']}' title='{$lang['bonus_img_bad']}' /></td><td align='left' class='clearalt6'><b>{$lang['bonus_fancy']}</b><br />
<b>".$CURUSER['username']."...</b>{$lang['bonus_cannot']}<br />
<br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['gift_fail_user'])):
I_smell_a_rat($_GET['gift_fail_user']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_error_msg']}</h1></td></tr><tr><td align='left' class='clearalt6'>
<img src='{$TBDEV['pic_base_url']}smilies/cry.gif' alt='{$lang['bonus_img_bad']}' title='{$lang['bonus_img_bad']}' /></td><td align='left' class='clearalt6'><b>{$lang['bonus_sorry']} ".$CURUSER['username']."...</b>
<br />{$lang['bonus_nouser_msg']}<br /><br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['gift_fail_points'])):
I_smell_a_rat($_GET['gift_fail_points']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Oops!</h1></td></tr><tr><td align='left' class='clearalt6'>
<img src='{$TBDEV['pic_base_url']}smilies/cry.gif' alt='{$lang['bonus_img_bad']}' title='{$lang['bonus_img_bad']}' /></td><td align=left class=clearalt6><b>{$lang['bonus_sorry']} </b>".$CURUSER['username']."{$lang['bonus_notenough_msg']}
<br />{$lang['bonus_goback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
case (isset($_GET['gift_success'])): 
I_smell_a_rat($_GET['gift_success']);
 
$HTMLOUT  .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>{$lang['bonus_success']}</h1></td></tr><tr><td align='left' class='clearalt6'>
<img src='{$TBDEV['pic_base_url']}smilies/karma.gif' alt='{$lang['bonus_img_karma']}' title='{$lang['bonus_img_karma']}' /></td><td align='left' class='clearalt6'><b>{$lang['bonus_congrat']}".$CURUSER['username']." </b>
{$lang['bonus_spread']}".htmlspecialchars($_GET['usernamegift'])."</b>{$lang['bonus_love']}".(0 + $_GET['gift_amount_points'])."{$lang['bonus_by']}".$CURUSER['username']."</p><br />
{$lang['bonus_also']}<a class='altlink' href='{$TBDEV['baseurl']}/sendmessage.php?receiver=".(0 + $_GET['gift_id'])."'>{$lang['bonus_send']}".htmlspecialchars($_GET['usernamegift'])."{$lang['bonus_orgoback']}<br /><br /></td></tr></table>";
print stdhead('Karma Bonus') . $HTMLOUT . stdfoot();
die;
}

//=== exchange
if (isset($_GET['exchange'])){
I_smell_a_rat($_GET['exchange']);

$userid = 0 + $CURUSER['id'];
if (!is_valid_id($userid))
stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_notid_msg']}");

$option = 0 + $_POST['option'];

$res_points = mysql_query("SELECT * FROM bonus WHERE id =" . sqlesc($option));
$arr_points = mysql_fetch_assoc($res_points);

$art = $arr_points['art'];
$points = $arr_points['points'];
if ($points == 0)
stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_rat_msg']}");

$seedbonus=htmlspecialchars($bonus-$points,1);
$upload = $CURUSER['uploaded'];
$download = $CURUSER['downloaded'];
$bonuscomment = $CURUSER['bonuscomment'];
$bpoints = $CURUSER['seedbonus'];

if($bonus < $points)
stderr("{$lang['bonus_sorry']}", "{$lang['bonus_notenough_msg']}");

switch ($art){
case 'traffic':
//=== trade for one upload credit
$up = $upload + $arr_points['menge'];
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points for upload bonus.\n " .$bonuscomment;
mysql_query("UPDATE users SET uploaded = $upload + $arr_points[menge], seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?up_success=1&amt=$points'");
die;
 break;
case 'traffic2':
case 'ratio':
//=== trade for one torrent 1:1 ratio
$torrent_number = 0 + $_POST['torrent_id'];
$res_snatched = mysql_query("SELECT s.uploaded, s.downloaded, t.name FROM snatched AS s LEFT JOIN torrents AS t ON t.id = s.torrentid WHERE s.userid = '$userid' AND torrentid = ".sqlesc($torrent_number)." LIMIT 1") or sqlerr(__FILE__, __LINE__);
$arr_snatched = mysql_fetch_assoc($res_snatched);
if ($arr_snatched['name'] == '')
stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_notorrent_msg']}");
if ($arr_snatched['uploaded'] >= $arr_snatched['downloaded'])
stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_fineratio_msg']}");
mysql_query("UPDATE snatched SET uploaded = '$arr_snatched[downloaded]' WHERE userid = '$userid' AND torrentid = ".sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
$difference = $arr_snatched['downloaded'] - $arr_snatched['uploaded'];
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points for 1 to 1 ratio on torrent: ".$arr_snatched['name']." ".$torrent_number.", ".$difference." added .\n " .$bonuscomment;
mysql_query("UPDATE users SET uploaded = $upload + $difference, bonuscomment = '$bonuscomment', seedbonus = '$seedbonus' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?ratio_success=1'");
die;
 break;
/* case 'class':
//=== trade for one month VIP status 
if ($CURUSER['class'] > UC_VIP)
stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_lower_msg']}");
$vip_until = (86400 * 28 + time());
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points for 1 month VIP Status.\n " .$bonuscomment;
mysql_query("UPDATE users SET class = ".UC_VIP.", vip_added = 'yes', vip_until = '$vip_until', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?class_success=1'");
die;
 break; */
 /* case 'warning':
//=== trade for removal of warning :P
if ($CURUSER['warned'] == 'no')
stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_notthere_msg']}");
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points for removing warning.\n " .$bonuscomment;
$res_warning = mysql_query("SELECT modcomment FROM users WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res_warning);
$modcomment = htmlspecialchars($arr['modcomment']);
$modcomment = get_date( time(), 'DATE', 1 ) . " - Warning removed by - Bribe with Karma.\n". $modcomment;
$modcom = sqlesc($modcomment);
mysql_query("UPDATE users SET warned = 'no', warneduntil = '0', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment', modcomment = $modcom WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
$dt = sqlesc(time());
$subject = sqlesc("Warning removed by Karma.");
$msg = sqlesc("Your warning has been removed by the big Karma payoff... Please keep on your best behaviour from now on.\n");
mysql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $userid, $dt, $msg, $subject)") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?warning_success=1'");
die;
 break; */
 /* case 'smile':
//=== trade for one month special smilies :P
$smile_until = (86400 * 28 + time());
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points for 1 month of custom smilies.\n " .$bonuscomment;
mysql_query("UPDATE users SET smile_until = '$smile_until', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?smile_success=1'");
die;
 break; */
 case 'invite':
//=== trade for invites
$invites = $CURUSER['invites'];
$inv = $invites+$arr_points['menge'];
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points for invites.\n " .$bonuscomment;
mysql_query("UPDATE users SET invites = '$inv', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?invite_success=1'");
die;
 break;
 case 'title':
//=== trade for special title
/**** the $words array are words that you DO NOT want the user to have... use to filter "bad words" & user class...
the user class is just for show, but what the hell :p Add more or edit to your liking.
*note if they try to use a restricted word, they will recieve the special title "I just wasted my karma" *****/

$title = htmlentities($_POST['title']);
$words = array('fuck', 'shit', 'Moderator', 'Administrator', 'Admin', 'pussy', 'Sysop', 'cunt', 'nigger', 'VIP', 'Super User', 'Power User', 'ADMIN', 'SYSOP', 'MODERATOR', 'ADMINISTRATOR');
$title = str_replace($words, "I just wasted my karma", $title);
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points for custom title. Old title was $CURUSER[title] new title is ".$title.".\n " .$bonuscomment;
mysql_query("UPDATE users SET title = '$title', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?title_success=1'");
die;
 break;
 case 'gift_1':
//=== trade for giving the gift of karma
$points = 0 + $_POST['bonusgift'];
$usernamegift = htmlentities(trim($_POST['username']));
$res = mysql_query("SELECT id,seedbonus,bonuscomment,username FROM users WHERE username=" . sqlesc($usernamegift));
$arr = mysql_fetch_assoc($res);
$useridgift = $arr['id'];
$userseedbonus = $arr['seedbonus'];
$bonuscomment_gift = $arr['bonuscomment'];
$usernamegift = $arr['username'];

$check_me = array(100,200,300,400,500,666);
if (!in_array($points, $check_me))
stderr("{$lang['bonus_error_msg']}", "{$lang['bonus_rat_msg']}");

if($bonus >= $points){
$points= htmlspecialchars($points,1);
$bonuscomment = get_date( time(), 'DATE', 1 ) . " - " .$points. " Points as gift to $usernamegift .\n " .$bonuscomment;
$bonuscomment_gift = get_date( time(), 'DATE', 1 ) . " - recieved " .$points. " Points as gift from $CURUSER[username] .\n " .$bonuscomment_gift;
$seedbonus=$bonus-$points;
$giftbonus1=$userseedbonus+$points;
if ($userid==$useridgift){
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?gift_fail=1'");
die;
}
if (!$useridgift){
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?gift_fail_user=1'");
die;
}
mysql_query("SELECT bonuscomment,id FROM users WHERE id = '$useridgift'") or sqlerr(__FILE__, __LINE__);
//=== and to post to the person who gets the gift!
mysql_query("UPDATE users SET seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
mysql_query("UPDATE users SET seedbonus = '$giftbonus1', bonuscomment = '$bonuscomment_gift' WHERE id = '$useridgift'");
//===send message
$subject = sqlesc("Someone Loves you");
$added = sqlesc(time());
$msg = sqlesc("You have been given a gift of $points Karma points by ".$CURUSER['username']);
mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES(0, $subject, $useridgift, $msg, $added)") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?gift_success=1&gift_amount_points=$points&usernamegift=$usernamegift&gift_id=$useridgift'");
die;
}
else{
header("Refresh: 0; url='{$TBDEV['baseurl']}/mybonus.php?gift_fail_points=1'");
die;
}
break;
}
}

//==== this is the default page
$HTMLOUT .= '
 
<script type="text/javascript"> 
$(document).ready(function() {
 
	//Default Action
	$(".tab_content").hide(); //Hide all content
	$("ul.tabs li:first").addClass("active").show(); //Activate first tab
	$(".tab_content:first").show(); //Show first tab content
	
	//On Click Event
	$("ul.tabs li").click(function() {
		$("ul.tabs li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		$(".tab_content").hide(); //Hide all tab content
		var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
		$(activeTab).fadeIn(); //Fade in the active content
		return false;


	});
 
});


</script>
';
$HTMLOUT .= '
<div class="container"> 
	    <ul class="tabs"> 
        <li><a href="#upload">Upload</a></li> 
        <li><a href="#fix">Fix Torrents</a></li>
        <li><a href="#other">Other</a></li>
        <li><a href="#for">How to Get Points</a></li>
    </ul> ';

$HTMLOUT .= '<div class="tab_container"> 
        <div id="upload" class="tab_content"> ';

$HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;padding:5px;'>
<div style='background:transparent;height:25px;'>
<span style='font-weight:bold;font-size:12pt;'>{$lang['bonus_system']}</span></div>";

$HTMLOUT .="<table align='center' width='100%' border='1' cellspacing='0' cellpadding='5'>
<tr>
<td align='center' colspan='4' style='background:transparent; border: 1px solid #97BCC2; height:25px;'>
{$lang['bonus_exchangepoints']}".$bonus."{$lang['bonus_goodies']}
<br /><br />{$lang['bonus_no_buttons']}<br /><br />
</td>
</tr>
<tr>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='left'>{$lang['bonus_head_descr']}</td>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='center'>{$lang['bonus_head_points']}</td>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='center'>{$lang['bonus_head_trade']}</td></tr>";

$res = mysql_query("SELECT * FROM bonus WHERE id < 4 and enabled = 'yes' ORDER BY id ASC");
while ($gets = mysql_fetch_assoc($res)){
//=======change colors
$count1='';
$count1= (++$count1)%2;
$class = 'clearalt'.($count1==0?'6':'7');
$otheroption = "<table align='center' width='100%'>
<tr>
<td class='".$class."'><b>{$lang['bonus_op_username']}</b>
<input type='text' name='username' size='20' maxlength='24' /></td>
<td class='".$class."'> <b>{$lang['bonus_op_given']}</b>
<select name='bonusgift'> 
<option value='100.0'> 100.0</option> 
<option value='200.0'> 200.0</option> 
<option value='300.0'> 300.0</option> 
<option value='400.0'> 400.0</option>
<option value='500.0'> 500.0</option>
<option value='666.0'> 666.0</option></select>{$lang['bonus_op_karma']}</td></tr></table>";


switch (true){
 	case ($gets['id'] == 5):
 	$HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_title']}<input type='text' name='title' size='30' maxlength='30' />{$lang['bonus_op_click']}</td><td align='center' class='".$class."'>".$gets['points']."</td>";
break;
 case ($gets['id'] == 7):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_username2']}<br />".$otheroption."</td><td align=center class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."<br />{$lang['bonus_op_max']}<br />666</td>";
break;
 case ($gets['id'] == 9):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."</td><td align='center' class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."</td>";
break;
 case ($gets['id'] == 10):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_idnum']}<input type='text' name='torrent_id' size='4' maxlength='8' />{$lang['bonus_op_idnum2']}</td><td align='center' class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."</td>";
break;
default:
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."</td><td align='center' class='".$class."'>".$gets['points']."</td>";
}

if($bonus >= $gets['points']) {

switch (true){
case ($gets['id'] == 7):
$HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Karma Gift!' /></td></form>";
break;
default:
$HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Exchange!' /></td></form>";
}
} 
else 
$HTMLOUT .="<td class='".$class."' align='center'>{$lang['bonus_op_more']}</td></form>";
}

$HTMLOUT .="</tr></table>";
$HTMLOUT .= '</div>';


$HTMLOUT .=" 
 <table align='center' width='100%'>
  <tr>

  <div align='center'><br />
  <a class='altlink' href='{$TBDEV['baseurl']}/index.php'><b>{$lang['bonus_table_go_back']}</b></a></div>
  </td></tr></table></div>";

$HTMLOUT .= '<div class="tab_container"> 
        <div id="fix" class="tab_content"> ';

$HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;padding:5px;'>
<div style='background:transparent;height:25px;'>
<span style='font-weight:bold;font-size:12pt;'>{$lang['bonus_system']}</span></div>";

$HTMLOUT .="<table align='center' width='100%' border='1' cellspacing='0' cellpadding='5'>
<tr>
<td align='center' colspan='4' style='background:transparent;border: 1px solid #97BCC2; height:25px;'>
{$lang['bonus_exchangepoints']}".$bonus."{$lang['bonus_goodies']}
<br /><br />{$lang['bonus_no_buttons']}<br /><br />
</td>
</tr>
<tr>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='left'>{$lang['bonus_head_descr']}</td>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='center'>{$lang['bonus_head_points']}</td>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='center'>{$lang['bonus_head_trade']}</td></tr>";

$res = mysql_query("SELECT * FROM bonus WHERE id >= 9 and enabled = 'yes' ORDER BY id ASC");
while ($gets = mysql_fetch_assoc($res)){
//=======change colors
$count1='';
$count1= (++$count1)%2;
$class = 'clearalt'.($count1==0?'6':'7');
$otheroption = "<table align='center' width='100%'>
<tr>
<td class='".$class."'><b>{$lang['bonus_op_username']}</b>
<input type='text' name='username' size='20' maxlength='24' /></td>
<td class='".$class."'> <b>{$lang['bonus_op_given']}</b>
<select name='bonusgift'> 
<option value='100.0'> 100.0</option> 
<option value='200.0'> 200.0</option> 
<option value='300.0'> 300.0</option> 
<option value='400.0'> 400.0</option>
<option value='500.0'> 500.0</option>
<option value='666.0'> 666.0</option></select>{$lang['bonus_op_karma']}</td></tr></table>";


switch (true){
 	case ($gets['id'] == 5):
 	$HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_title']}<input type='text' name='title' size='30' maxlength='30' />{$lang['bonus_op_click']}</td><td align='center' class='".$class."'>".$gets['points']."</td>";
break;
 case ($gets['id'] == 7):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_username2']}<br />".$otheroption."</td><td align=center class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."<br />{$lang['bonus_op_max']}<br />666</td>";
break;
 case ($gets['id'] == 9):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."</td><td align='center' class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."</td>";
break;
 case ($gets['id'] == 10):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_idnum']}<input type='text' name='torrent_id' size='4' maxlength='8' />{$lang['bonus_op_idnum2']}</td><td align='center' class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."</td>";
break;
default:
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."</td><td align='center' class='".$class."'>".$gets['points']."</td>";
}

if($bonus >= $gets['points']) {

switch (true){
case ($gets['id'] == 7):
$HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Karma Gift!' /></td></form>";
break;
default:
$HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Exchange!' /></td></form>";
}
} 
else 
$HTMLOUT .="<td class='".$class."' align='center'>{$lang['bonus_op_more']}</td></form>";
}

$HTMLOUT .="</tr></table>";
$HTMLOUT .= '</div>';




$HTMLOUT .=" 
 <table align='center' width='100%'>
  <tr>

  
  <div align='center'><br />
  <a class='altlink' href='{$TBDEV['baseurl']}/index.php'><b>{$lang['bonus_table_go_back']}</b></a></div>
  </td></tr></table></div>";

$HTMLOUT .= '<div class="tab_container"> 
        <div id="other" class="tab_content"> ';

$HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;padding:5px;'>
<div style='background:transparent;height:25px;'>
<span style='font-weight:bold;font-size:12pt;'>{$lang['bonus_system']}</span></div>";

$HTMLOUT .="<table align='center' width='100%' border='1' cellspacing='0' cellpadding='5'>
<tr>
<td align='center' colspan='4' style='background:transparent; border: 1px solid #97BCC2; height:25px;'>
{$lang['bonus_exchangepoints']}".$bonus."{$lang['bonus_goodies']}
<br /><br />{$lang['bonus_no_buttons']}<br /><br />
</td>
</tr>
<tr>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='left'>{$lang['bonus_head_descr']}</td>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='center'>{$lang['bonus_head_points']}</td>
<td style='background:transparent;border: 1px solid #97BCC2; height:25px;' align='center'>{$lang['bonus_head_trade']}</td></tr>";

$res = mysql_query("SELECT * FROM bonus WHERE id BETWEEN 4 AND 8 and enabled = 'yes' ORDER BY id ASC");
while ($gets = mysql_fetch_assoc($res)){
//=======change colors
$count1='';
$count1= (++$count1)%2;
$class = 'clearalt'.($count1==0?'6':'7');
$otheroption = "<table align='center' width='100%'>
<tr>
<td class='".$class."'><b>{$lang['bonus_op_username']}</b>
<input type='text' name='username' size='20' maxlength='24' /></td>
<td class='".$class."'> <b>{$lang['bonus_op_given']}</b>
<select name='bonusgift'> 
<option value='100.0'> 100.0</option> 
<option value='200.0'> 200.0</option> 
<option value='300.0'> 300.0</option> 
<option value='400.0'> 400.0</option>
<option value='500.0'> 500.0</option>
<option value='666.0'> 666.0</option></select>{$lang['bonus_op_karma']}</td></tr></table>";


switch (true){
 	case ($gets['id'] == 5):
 	$HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_title']}<input type='text' name='title' size='30' maxlength='30' />{$lang['bonus_op_click']}</td><td align='center' class='".$class."'>".$gets['points']."</td>";
break;
 case ($gets['id'] == 7):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_username2']}<br />".$otheroption."</td><td align=center class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."<br />{$lang['bonus_op_max']}<br />666</td>";
break;
 case ($gets['id'] == 9):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."</td><td align='center' class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."</td>";
break;
 case ($gets['id'] == 10):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."<br /><br />{$lang['bonus_op_idnum']}<input type='text' name='torrent_id' size='4' maxlength='8' />{$lang['bonus_op_idnum2']}</td><td align='center' class='".$class."'>{$lang['bonus_op_min']}<br />".$gets['points']."</td>";
break;
default:
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$TBDEV['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".$gets['id']."' /> <input type='hidden' name='art' value='".$gets['art']."' /><h1><font color='#426693'>".$gets['bonusname']."</font></h1>".$gets['description']."</td><td align='center' class='".$class."'>".$gets['points']."</td>";
}

if($bonus >= $gets['points']) {

switch (true){
case ($gets['id'] == 7):
$HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Karma Gift!' /></td></form>";
break;
default:
$HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Exchange!' /></td></form>";
}
} 
else 
$HTMLOUT .="<td class='".$class."' align='center'>{$lang['bonus_op_more']}</td></form>";
}

$HTMLOUT .="</tr></table>";
$HTMLOUT .= '</div>';




$HTMLOUT .=" 
 <table align='center' width='100%'>
  <tr>

  
  <div align='center'><br />
  <a class='altlink' href='{$TBDEV['baseurl']}/index.php'><b>{$lang['bonus_table_go_back']}</b></a></div>
  </td></tr></table></div>";


$HTMLOUT .= '<div class="tab_container"> 
        <div id="for" class="tab_content"> ';
$HTMLOUT .="
 <div style='background:transparent;height:25px;'>
 <span style='font-weight:bold;font-size:12pt;'>{$lang['bonus_table_hell']}</span></div>
 <table align='center' width='100%'>
  <tr>

  <td class='clearalt6'>{$lang['bonus_table_rest_of']}
  <div align='center'><br />
  <a class='altlink' href='{$TBDEV['baseurl']}/index.php'><b>{$lang['bonus_table_go_back']}</b></a></div>
  </td></tr></table></div></div></div></div>";



$HTMLOUT .= '</div>&nbsp;</div>';




print stdhead('Karma Bonus Page') . $HTMLOUT . stdfoot();
?>