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

require_once('include/bittorrent.php');
require_once('include/user_functions.php');

dbconn();
loggedinorreturn();
$lang = array_merge( load_language('global'), load_language('getrss') );

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$cats = isset($_POST['cats']) ? $_POST['cats'] : array();
	if(count($cats) == 0)
		stderr($lang['getrss_error'],$lang['getrss_nocat']);
	$feed = isset($_POST['feed']) && $_POST['feed'] == 'dl' ? 'dl' : 'web';
	
	$rsslink = $TBDEV['baseurl'].'/rss.php?cats='.join(',',$cats).($feed == 'dl' ? '&amp;type=dl' : '').'&amp;passkey='.$CURUSER['passkey'];
	$HTMLOUT = "<div align=\"center\"><h2>{$lang['getrss_result']}</h2><br/>
		<input type=\"text\" size=\"120\" readonly=\"readonly\" value=\"{$rsslink}\" onclick=\"select()\" />
	</div>";
	
	print(stdhead($lang['getrss_head2']).$HTMLOUT.stdfoot());
	
} else {
$HTMLOUT = <<<HTML
<form action="{$_SERVER['PHP_SELF']}" method="post">
<table width="500" cellpadding="2" cellspacing="0" align="center">
<tr>
	<td colspan="2" align="center" class="colhead">{$lang['getrss_title']}</td>
</tr>
<tr>
	<td align="right" valign="top">{$lang['getrss_cat']}</td><td align="left" width="100%">
HTML;
	$q = mysql_query('SELECT id,name,image FROM categories order by id') or sqlerr();
	$i=0;
	while($a = mysql_fetch_assoc($q)) {
		if($i%5 == 0 && $i>0)
			$HTMLOUT .="<br/>";
		$HTMLOUT .= "<label for=\"cat_{$a['id']}\"><img src=\"{$TBDEV['pic_base_url']}caticons/{$a['image']}\" title=\"{$a['name']}\" /><input type=\"checkbox\" name=\"cats[]\" id=\"cat_{$a['id']}\" value=\"{$a['id']}\" /></label>\n";
		$i++;
	}
$HTMLOUT .= <<<HTML
</td>
</tr>
<tr>
	<td align="right">{$lang['getrss_feed']}</td><td align="left"><input type="radio" checked="checked" name="feed" id="std" value="web"/><label for="std">{$lang['getrss_web']}</label><br/><input type="radio" name="feed" id="dl" value="dl"/><label for="dl">{$lang['getrss_dl']}</label></td>
 </tr>
 <tr><td colspan="2" align="center"><input type="submit" value="{$lang['getrss_btn']}" /></td></tr>
</table>
</form>
HTML;

print(stdhead($lang['getrss_head2']).$HTMLOUT.stdfoot());
}
?>
