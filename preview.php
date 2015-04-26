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
require_once("include/html_functions.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn();
loggedinorreturn();

$lang = array_merge( load_language('global'));

$HTMLOUT ="";

$body = trim($_POST["body"]);

$HTMLOUT .= begin_main_frame();

$HTMLOUT .= begin_frame("Preview Post", true);

$HTMLOUT .="<form method='post' action='preview.php'>
<div align='center' style='border: 0;'>
<div align='center'>
<p>".format_comment($body)."</p>
</div>
</div>
<div align='center' style='border: 0;'>
<textarea name='body' cols='100' rows='10'>".htmlspecialchars($body)."</textarea><br />
</div>
<div align='center'>
<input type='submit' class='btn' value='Preview' />
</div></form>";

$HTMLOUT .= end_frame();

$HTMLOUT .= end_main_frame();
print stdhead('Preview') . $HTMLOUT . stdfoot();
?>