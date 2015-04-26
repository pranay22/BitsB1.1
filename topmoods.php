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
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
require_once("include/mood.php");
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global') );

$HTMLOUT ="";

$HTMLOUT .="<table><tr><td class='embedded'>
<small>You may select your mood by clicking on the smiley in the forum !</small></td></tr></table>";

$query1 = "SELECT mood, COUNT(mood) as moodcount FROM users GROUP BY mood ORDER BY moodcount DESC";
$res = sql_query($query1) or sqlerr(__FILE__, __LINE__);

$HTMLOUT = "<h2>Top Moods</h2>" . "    <table border='1' cellspacing='0' cellpadding='5'>" . "<tr><td class='colhead' align='center'>Count</td><td class='colhead' align='center'>Mood</td><td class='colhead' align='center'>Icon</td></tr>\n";
while ($arr = mysql_fetch_assoc($res)) {
    foreach($mood as $key => $value)
    $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
    $mooduname = htmlspecialchars($change[$arr['mood']]['name']);
    $moodupic = htmlspecialchars($change[$arr['mood']]['image']);
    $moodcount = 0 + $arr['moodcount'];

    $HTMLOUT .= "<tr><td align='center'>" . $moodcount . "</td><td align='center'>" . $mooduname . "</td><td align='center'><img src='" . $TBDEV['pic_base_url'] . "smilies/" . $moodupic . "' border='0' alt='" . $mooduname . "'  title='" . $mooduname . "'/></td></tr>\n";
}

$HTMLOUT .= "</table>\n";

print stdhead('User Moods') . $HTMLOUT . stdfoot();
?>