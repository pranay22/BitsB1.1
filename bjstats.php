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

//$_NO_COMPRESS = true; //== For pdq's improvements mods
ob_start("ob_gzhandler");
require_once "include/bittorrent.php";
require_once "include/html_functions.php";
require_once "include/user_functions.php";
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global') );

if ($CURUSER['class'] < UC_POWER_USER)
{
        stderr("Sorry...", "You must be a Power User or above to play Blackjack.");
        exit;
}

function bjtable($res, $frame_caption)
{
        $htmlout='';
        $htmlout .= begin_frame($frame_caption, true);
        $htmlout .= begin_table();
        $htmlout .="<tr>
        <td class='colhead3'>Rank</td>
        <td class='colhead3' align='left'>User</td>
        <td class='colhead3' align='right'>Wins</td>
        <td class='colhead3' align='right'>Losses</td>
        <td class='colhead3' align='right'>Games</td>
        <td class='colhead3' align='right'>Percentage</td>
        <td class='colhead3' align='right'>Win/Loss</td>
        </tr>";

        $num = 0;
        while ($a = mysql_fetch_assoc($res))
        {
                ++$num;
                //==Calculate Win %
                $win_perc = number_format(($a['wins'] / $a['games']) * 100, 1);
                //==Add a user's +/- statistic
                $plus_minus = $a['wins'] - $a['losses'];
                if ($plus_minus >= 0)
                {
                $plus_minus = mksize(($a['wins'] - $a['losses']) * 100*1024*1024);
                }
                else
                {
                        $plus_minus = "-";
                        $plus_minus .= mksize(($a['losses'] - $a['wins']) * 100*1024*1024);
                }
                
                $htmlout .="<tr><td>$num</td><td align='left'>".
                "<b><a href='userdetails.php?id=".$a['id']."'>".$a['username']."</a></b></td>".
                "<td align='right'>".number_format($a['wins'], 0)."</td>".
                "<td align='right'>".number_format($a['losses'], 0)."</td>".
                "<td align='right'>".number_format($a['games'], 0)."</td>".
                "<td align='right'>$win_perc</td>".
                "<td align='right'>$plus_minus</td>".
                "</tr>\n";
        }
        $htmlout .= end_table();
        $htmlout .= end_frame();
        return $htmlout;
}



     $cachefile = "./cache/bjstats.txt";
     $cachetime = 60 * 30; // 30 minutes
     //$cachetime = 10 * 3;
     if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile)))
     {
     require_once($cachefile);
     $htmlout .="<p align='center'><font class='small'>This page last updated ".date('Y-m-d H:i:s', filemtime($cachefile)).". </font></p>";
     print stdhead('Blackjack') . $HTMLOUT . stdfoot();
     exit;
     }
     ob_start();
     
$mingames = 1;
$HTMLOUT='';
$HTMLOUT .="<h1>Blackjack Stats</h1>";
$HTMLOUT .="<p>Stats are cached and updated every 30 minutes. You need to play at least $mingames games to be included.</p>";
$HTMLOUT .="<br />";
//==Most Games Played
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games FROM users WHERE bjwins + bjlosses > $mingames ORDER BY games DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "Most Games Played","Users");
$HTMLOUT .="<br /><br />";
//==Most Games Played
//==Highest Win %
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins / (bjwins + bjlosses) AS winperc FROM users WHERE bjwins + bjlosses > $mingames ORDER BY winperc DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "Highest Win Percentage","Users");
$HTMLOUT .="<br /><br />";
//==Highest Win %
//==Most Credit Won
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins - bjlosses AS winnings FROM users WHERE bjwins + bjlosses > $mingames ORDER BY winnings DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "Most Credit Won","Users");
$HTMLOUT .="<br /><br />";
//==Most Credit Won
//==Most Credit Lost
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjlosses - bjwins AS losings FROM users WHERE bjwins + bjlosses > $mingames ORDER BY losings DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "Most Credit Lost","Users");
//==Most Credit Lost
$HTMLOUT .="<br /><br />";
print stdhead('Blackjack Stats') . $HTMLOUT . stdfoot();
?>