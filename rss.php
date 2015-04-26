<?
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

require "include/bittorrent.php";
dbconn();

$passkey = (isset($_GET["passkey"]) ? $_GET["passkey"] : '');
$feed = (isset($_GET["type"]) && $_GET['type'] == 'dl'? 'dl' : 'web');
$cats = (isset($_GET["cats"]) ? $_GET["cats"] : "");

if(!empty($passkey))
{
	if(strlen($passkey) !=32)
	die("Your passkey is not long enough! Go to ".$TBDEV['site_name']." and reset your passkey");
	else 
	{
		if(get_row_count("users","where passkey=".sqlesc($passkey)) != 1)
		die("Your passkey is invalid !Go to ".$TBDEV['site_name']." and reset your passkey");
	}
}
else die('Your link doesn\'t have a passkey');

$TBDEV['rssdescr'] = $TBDEV['site_name']." some motto goes here!";

$where = !empty($cats) ? "t.category IN (".$cats.") AND " : '';

header("Content-Type: application/xml");
$HTMLOUT = "<?xml version=\"1.0\" encoding=\"windows-1251\" ?>\n<rss version=\"0.91\">\n<channel>\n" .
"<title>" . $TBDEV['site_name'] . "</title>\n<link>" . $TBDEV['baseurl'] . "</link>\n<description>" . $TBDEV['rssdescr'] . "</description>\n" .
"<language>en-usde</language>\n<copyright>Copyright © ".date('Y')." " . $TBDEV['site_name'] . "</copyright>\n<webMaster>" . $TBDEV['site_email'] . "</webMaster>\n" .
"<image><title>" .$TBDEV['site_name']. "</title>\n<url>" . $TBDEV['site_email'] . "/favicon.ico</url>\n<link>" . $TBDEV['baseurl'] . "</link>\n" .
"<width>16</width>\n<height>16</height>\n<description>" . $TBDEV['rssdescr'] . "</description>\n</image>\n";

$res = mysql_query("SELECT t.id,t.name,t.descr,t.size,t.category,t.seeders,t.leechers,t.added, c.name as catname FROM torrents as t LEFT JOIN categories as c ON t.category = c.id WHERE $where t.visible='yes' ORDER BY t.added DESC LIMIT 15") or sqlerr(__FILE__, __LINE__);
while ($a = mysql_fetch_assoc($res)){
 $link = $TBDEV['baseurl'].($feed == "dl" ? "/download.php?torrent=".$a['id'].'&amp;passkey='.$passkey : "/details.php?id=".$a["id"]."&amp;hit=1");
 $br = "&lt;br/&gt;";
 $HTMLOUT .= "<item><title>{$a["name"]}</title><link>{$link}</link><description>{$br}Category: {$a['catname']} {$br} Size: ".mksize($a["size"])." {$br} Leechers: {$a["leechers"]} {$br} Seeders: {$a["seeders"]} {$br} Added: ".get_date($a['added'],'DATE')." {$br} Description: ".htmlspecialchars(substr($a["descr"],0,450))." {$br}</description>\n</item>\n";
}

$HTMLOUT .= "</channel>\n</rss>\n";
print($HTMLOUT);
?>
