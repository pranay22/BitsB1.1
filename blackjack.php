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

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/html_functions.php";
dbconn();
loggedinorreturn();

$lang = array_merge( load_language('global') );

$HTMLOUT='';


if ($CURUSER['class'] < UC_POWER_USER)
        stderr("Sorry", "You must be a Power User+ or above to play Blackjack.");

$mb = 100*1024*1024;
$now = sqlesc(time());
$game = isset($_POST["game"]) ? htmlspecialchars(trim($_POST["game"])) : '';
$start = isset($_POST["start"]) ? htmlspecialchars(trim($_POST["start"])) : '';

if ($game)
{
        function cheater_check($arg)
        {       
                if ($arg)
                {
                        header('Location: '.$_SERVER['PHP_SELF']);
                        exit;
                }
        }
        
        $cardcount = 52;
        $points='';
  $showcards='';
  $aces='';
  
        if ($start != 'yes')
        {
                $playeres = mysql_query("SELECT * FROM blackjack WHERE userid = ".sqlesc($CURUSER['id']));
                $playerarr = mysql_fetch_assoc($playeres);
                if ($game == 'hit')
                $points = $aces = 0;
                $gameover = ($playerarr['gameover'] == 'yes' ? true : false);
                $HTMLOUT .= cheater_check($gameover && ($game == 'hit' ^ $game == 'stop'));
                $cards = $playerarr["cards"];
                $usedcards = explode(" ", $cards);

                $arr = array();
                foreach ($usedcards as $array_list)
                $arr[] = $array_list;
                foreach ($arr as $card_id)
                {
                $used_card = mysql_query("SELECT * FROM cards WHERE id=".sqlesc($card_id));
                $used_cards = mysql_fetch_assoc($used_card);
                $showcards .= "<img src='{$TBDEV['pic_base_url']}cards/".$used_cards["pic"]."' width='71' height='96' border='0' alt='Cards' title='Cards' />";
                if ($used_cards["points"] > 1)
                $points += $used_cards['points'];
                else
                $aces++;
                }
          }
        
        if ($_POST["game"] == 'hit')
        {
                if ($start == 'yes')
                {
                        if ($CURUSER["uploaded"] < $mb)
                        stderr("Sorry ".$CURUSER["username"], "You haven't uploaded ".mksize($mb)." yet.");
                        $required_ratio = 0.3;
                        if ($CURUSER["downloaded"] > 0)
                        $ratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 3);
                        elseif ($CURUSER["uploaded"] > 0)
                        $ratio = 999;
                        else
                        $ratio = 0;
                        if ($ratio < $required_ratio)
                        stderr("Sorry ".$CURUSER["username"], "Your ratio is lower than the requirement of ".$required_ratio."%.");
                        $res = mysql_query("SELECT status, gameover FROM blackjack WHERE userid = ".sqlesc($CURUSER['id']));
                        $arr = mysql_fetch_assoc($res);
                        
                        if ($arr['status'] == 'waiting')
                        stderr("Sorry", "You'll have to wait until your last game completes before you play a new one.");
                        elseif ($arr['status'] == 'playing')
                        stderr("Sorry", "You must finish your old game first.<form method='post' action='".$_SERVER['PHP_SELF']."'><input type='hidden' name='game' value='hit' readonly='readonly' /><input type='hidden' name='continue' value='yes' readonly='readonly' /><input type='submit' value='Continue old game' /></form>");
        
                        $HTMLOUT .= cheater_check($arr['gameover'] == 'yes');
                        $cardids = array();
                        for ($i = 0; $i <= 1; $i++)
                        $cardids[] = rand(1, $cardcount);
                        foreach ($cardids as $cardid)
                        {
                        while (in_array($cardid, $cardids))
                        $cardid = rand(1, $cardcount);
                        $cardres = mysql_query("SELECT points, pic FROM cards WHERE id='$cardid'");
                        $cardarr = mysql_fetch_assoc($cardres);
                        if ($cardarr["points"] > 1)
                        $points += $cardarr["points"];
                        else
                        $aces++;
                        $showcards .= "<img src='{$TBDEV['pic_base_url']}cards/".$cardarr['pic']."' width='71' height='96' border='0' alt='Cards' title='Cards' />";
                        $cardids2[] = $cardid;
                        }

                        for ($i = 0; $i < $aces; $i++)
                        $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
                        mysql_query("INSERT INTO blackjack (userid, points, cards, date) VALUES(".sqlesc($CURUSER['id']).", '$points', '".join(" ",$cardids2)."', $now)");
                        
                        if ($points < 21)
                        {
                                $HTMLOUT .="<h1>Welcome, {$CURUSER['username']}!</h1>
                                <table cellspacing='0' cellpadding='3' width='600'>
                                <tr><td colspan='2'>
                                <table class='message' width='100%' cellspacing='0' cellpadding='5' bgcolor='white'>
                                <tr><td align='center'>".trim($showcards)."</td></tr>
                                <tr><td align='center'><b>Points = {$points}</b></td></tr>
                                <tr><td align='center'>
                                <form method='post' action='".$_SERVER['PHP_SELF']."'><input type='hidden' name='game' value='hit' readonly='readonly' /><input type='submit' value='Hitme' /></form>
                                </td></tr>";
                                
                                if ($points >= 10)
                                {
                                $HTMLOUT .="<tr><td align='center'>
                                <form method='post' action='".$_SERVER['PHP_SELF']."'><input type='hidden' name='game' value='stop' readonly='readonly' /><input type='submit' value='Stay' /></form>
                                </td></tr>";
                                }
                                
                                $HTMLOUT .="</table></td></tr></table>";
                                print stdhead('Blackjack') . $HTMLOUT . stdfoot();
                                die();
                        }
                }
                elseif (($start != 'yes' && isset($_POST['continue']) != 'yes') && !$gameover)
                {
                        $HTMLOUT .= cheater_check(empty($playerarr));
                        $cardid = rand(1, $cardcount);
                        while (in_array($cardid, $arr))
                        $cardid = rand(1, $cardcount);
                        $cardres = mysql_query("SELECT points, pic FROM cards WHERE id='$cardid'");
                        $cardarr = mysql_fetch_assoc($cardres);
                        $showcards .= "<img src='{$TBDEV['pic_base_url']}cards/".$cardarr['pic']."' width='71' height='96' border='0' alt='Cards' title='Cards' />";
                        
                        if ($cardarr["points"] > 1)
                        $points += $cardarr["points"];
                        else
                        $aces++;
                                
                        for ($i = 0; $i < $aces; $i++)
                  $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
                        mysql_query("UPDATE blackjack SET points='$points', cards='".$cards." ".$cardid."' WHERE userid=".sqlesc($CURUSER['id']));
                  }
                
                if ($points == 21 || $points > 21)
                {
                        $waitres = mysql_query("SELECT COUNT(userid) AS c FROM blackjack WHERE status = 'waiting' AND userid != ".sqlesc($CURUSER['id']));
                        $waitarr = mysql_fetch_assoc($waitres);
                        $HTMLOUT .="<h1>Game over</h1>
                        <table cellspacing='0' cellpadding='3' width='600'>
                        <tr><td colspan='2'>
                        <table width='100%' cellspacing='0' cellpadding='5' bgcolor='white'>
                        <tr><td align='center'>".trim($showcards)."</td></tr>
                        <tr><td align='center'><b>Points = {$points}</b></td></tr>";
                }

                if ($points == 21)
                {
                        if ($waitarr['c'] > 0)
                        {
                                $r = mysql_query("SELECT bj.*, u.username FROM blackjack AS bj LEFT JOIN users AS u ON u.id=bj.userid WHERE bj.status='waiting' AND bj.userid != ".sqlesc($CURUSER['id'])." ORDER BY bj.date ASC LIMIT 1");
                                $a = mysql_fetch_assoc($r);

                                if ($a["points"] != 21)
                                {
                                        $winorlose = "you won ".mksize($mb);
                                        mysql_query("UPDATE users SET uploaded = uploaded + $mb, bjwins = bjwins + 1 WHERE id=".sqlesc($CURUSER['id']));
                                        mysql_query("UPDATE users SET uploaded = uploaded - $mb, bjlosses = bjlosses + 1 WHERE id=".sqlesc($a['userid']));
                                        $msg = sqlesc("You lost to ".$CURUSER['username']." (You had ".$a['points']." points, ".$CURUSER['username']." had 21 points).\n\n");
                                }
                                else
                                {
                                $subject = sqlesc("Blackjack Results");
                                $winorlose = "nobody won";
                                $msg = sqlesc("You tied with ".$CURUSER['username']." (You both had ".$a['points']." points).\n\n");
                                }

                                mysql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ".$a['userid'].", $now, $msg, $subject)");
                                mysql_query("DELETE FROM blackjack WHERE userid IN (".sqlesc($CURUSER['id']).", ".sqlesc($a['userid']).")");
                          $HTMLOUT .="<tr><td align='center'>Your opponent was ".$a["username"].", he/she had ".$a['points']." points, $winorlose.<br /><br /><b><a href='/blackjack.php'>Play again</a></b></td></tr>";
                          }
                        else
                        {
                        mysql_query("UPDATE blackjack SET status = 'waiting', date=".$now.", gameover = 'yes' WHERE userid = ".sqlesc($CURUSER['id']));
                        $HTMLOUT .="<tr><td align='center'>There are no other players, so you'll have to wait until someone plays against you.<br />You will receive a PM with the game results.<br /><br /><b><a href='/blackjack.php'>Back</a></b><br /></td></tr>";
                        }
                        
                        $HTMLOUT .="</table></td></tr></table><br />";
                        print stdhead('Blackjack') . $HTMLOUT . stdfoot();
                }
                elseif ($points > 21)
                {
                        if ($waitarr['c'] > 0)
                        {
                                $r = mysql_query("SELECT bj.*, u.username FROM blackjack AS bj LEFT JOIN users AS u ON u.id=bj.userid WHERE bj.status='waiting' AND bj.userid != ".sqlesc($CURUSER['id'])." ORDER BY bj.date ASC LIMIT 1");
                                $a = mysql_fetch_assoc($r);
                        
                                if ($a["points"] > 21)
                                {
                                        $subject = sqlesc("Blackjack Results");
                                        $winorlose = "nobody won";
                                        $msg = sqlesc("Your opponent was ".$CURUSER['username'].", nobody won.\n\n");
                                }
                                else
                                {
                                        $subject = sqlesc("Blackjack Results");
                                        $winorlose = "you lost ".mksize($mb);
                                        mysql_query("UPDATE users SET uploaded = uploaded + $mb, bjwins = bjwins + 1 WHERE id=".sqlesc($a['userid']));
                                        mysql_query("UPDATE users SET uploaded = uploaded - $mb, bjlosses = bjlosses + 1 WHERE id=".sqlesc($CURUSER['id']));
                                        $msg = sqlesc("You beat ".$CURUSER['username']." (You had ".$a['points']." points, ".$CURUSER['username']." had $points points).\n\n");
                                }

                                mysql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ".$a['userid'].", $now, $msg, $subject)");
                                mysql_query("DELETE FROM blackjack WHERE userid IN (".sqlesc($CURUSER['id']).", ".sqlesc($a['userid']).")");
                                
                                $HTMLOUT .="<tr><td align='center'>Your opponent was ".$a["username"].", he/she had ".$a['points']." points, $winorlose.<br /><br /><b><a href='blackjack.php'>Play again</a></b></td></tr>";
                        }
                        else
                        {
                                mysql_query("UPDATE blackjack SET status = 'waiting', date=".$now.", gameover='yes' WHERE userid = ".sqlesc($CURUSER['id']));
                                
                        $HTMLOUT .="<tr><td align='center'>There are no other players, so you'll have to wait until someone plays against you.<br />You will receive a PM with the game results.<br /><br /><b><a href='/blackjack.php'>Back</a></b><br /></td></tr>";
                        }
                        $HTMLOUT .="</table></td></tr></table><br />";
                
                        print stdhead('Blackjack') . $HTMLOUT . stdfoot();
                }
                else
                {
                        $HTMLOUT .= cheater_check(empty($playerarr));
                        $HTMLOUT .="<h1>Welcome, {$CURUSER['username']}!</h1>
                        <table cellspacing='0' cellpadding='3' width='600'>
                        <tr><td colspan='2'>
                        <table class='message' width='100%' cellspacing='0' cellpadding='5' bgcolor='white'>
                        <tr><td align='center'>{$showcards}</td></tr>
                        <tr><td align='center'><b>Points = {$points}</b></td></tr>";
                        $HTMLOUT .="<tr>
      <td align='center'><form method='post' action='".$_SERVER['PHP_SELF']."'><input type='hidden' name='game' value='hit' readonly='readonly' /><input type='submit' value='HitMe' /></form></td>
      </tr>";
                        $HTMLOUT .="<tr>
      <td align='center'><form method='post' action='".$_SERVER['PHP_SELF']."'><input type='hidden' name='game' value='stop' readonly='readonly' /><input type='submit' value='Stay' /></form></td>
      </tr>";
                        $HTMLOUT .="</table></td></tr></table><br />";
                        print stdhead('Blackjack') . $HTMLOUT . stdfoot();
                }
        }
        elseif ($_POST["game"] == 'stop')
        {
                $HTMLOUT .= cheater_check(empty($playerarr));
                $waitres = mysql_query("SELECT COUNT(userid) AS c FROM blackjack WHERE status='waiting' AND userid != ".sqlesc($CURUSER['id']));
                $waitarr = mysql_fetch_assoc($waitres);
                $HTMLOUT .="<h1>Game over</h1>
                <table cellspacing='0' cellpadding='3' width='600'>
                <tr><td colspan='2'>
                <table class='message' width='100%' cellspacing='0' cellpadding='5' bgcolor='white'>
                <tr><td align='center'>{$showcards}</td></tr>
                <tr><td align='center'><b>Points = {$playerarr['points']}</b></td></tr>";
                
                if ($waitarr['c'] > 0)
                {
                        $r = mysql_query("SELECT bj.*, u.username FROM blackjack AS bj LEFT JOIN users AS u ON u.id=bj.userid WHERE bj.status='waiting' AND bj.userid != ".sqlesc($CURUSER['id'])." ORDER BY bj.date ASC LIMIT 1");
                        $a = mysql_fetch_assoc($r);
                        
                        if ($a["points"] == $playerarr['points'])
                        {
                                $subject = sqlesc("Blackjack Results");
                                $winorlose = "nobody won";
                                $msg = sqlesc("Your opponent was ".$CURUSER['username'].", you both had ".$a['points']." points - it was a tie.\n\n");
                        }
                        else
                        {
                                if (($a["points"] < $playerarr['points'] && $a['points'] < 21) || ($a["points"] > $playerarr['points'] && $a['points'] > 21))
                                {
                                        $subject = sqlesc("Blackjack Results");
                                        $msg = sqlesc("You lost to ".$CURUSER['username']." (You had ".$a['points']." points, ".$CURUSER['username']." had ".$playerarr['points']." points).\n\n");
                                        $winorlose = "you won ".mksize($mb);
                                        $st_query = "+ ".$mb.", bjwins = bjwins +";
                                        $nd_query = "- ".$mb.", bjlosses = bjlosses +";
                                }
                                elseif (($a["points"] > $playerarr['points'] && $a['points'] < 21) || $a["points"] == 21 || ($a["points"] < $playerarr['points'] && $a['points'] > 21))
                                {
                                        $subject = sqlesc("Blackjack Results");
                                        $msg = sqlesc("You beat ".$CURUSER['username']." (You had ".$a['points']." points, ".$CURUSER['username']." had ".$playerarr['points']." points).\n\n");
                                        $winorlose = "you lost ".mksize($mb);
                                        $st_query = "- ".$mb.", bjlosses = bjlosses +";
                                        $nd_query = "+ ".$mb.", bjwins = bjwins +";
                                }
                                
                        mysql_query("UPDATE users SET uploaded = uploaded ".$st_query." 1 WHERE id=".sqlesc($CURUSER['id']));
                        mysql_query("UPDATE users SET uploaded = uploaded ".$nd_query." 1 WHERE id=".sqlesc($a['userid']));
                        }

                        mysql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ".$a['userid'].", $now, $msg, $subject)");
                        mysql_query("DELETE FROM blackjack WHERE userid IN (".sqlesc($CURUSER['id']).", ".sqlesc($a['userid']).")");
                        $HTMLOUT .="<tr><td align='center'>Your opponent was ".$a["username"].", he/she had ".$a['points']." points, $winorlose.<br /><br /><b><a href='/blackjack.php'>Play again</a></b></td></tr>";
                }
                else
                {
                mysql_query("UPDATE blackjack SET status = 'waiting', date=".$now.", gameover='yes' WHERE userid = ".sqlesc($CURUSER['id']));
                $HTMLOUT .="<tr><td align='center'>There are no other players, so you'll have to wait until someone plays against you.<br />You will receive a PM with the game results.<br /><br /><b><a href='/blackjack.php'>Back</a></b><br /></td></tr>";
                }
                $HTMLOUT .="</table></td></tr></table><br />";
                print stdhead('Blackjack') . $HTMLOUT . stdfoot();
        }
}
else
{
        $tot_wins = $CURUSER['bjwins'];
  $tot_losses = $CURUSER['bjlosses'];
        $tot_games = $tot_wins + $tot_losses;
        $win_perc = ($tot_losses==0?($tot_wins==0?"---":"100%"):($tot_wins==0?"0":number_format(($tot_wins/$tot_games)*100,1)).'%');
        $plus_minus = ($tot_wins-$tot_losses<0?'-':'').mksize((($tot_wins-$tot_losses>=0?($tot_wins-$tot_losses):($tot_losses-$tot_wins)))*$mb);
        $HTMLOUT .="<h3>{$TBDEV['site_name']} Blackjack</h3>
        <table cellspacing='0' cellpadding='3' width='400'>
        <tr><td colspan='2' align='center'>
        <table class='message' width='100%' cellspacing='0' cellpadding='10' bgcolor='white'>
        <tr><td align='center'><img src='{$TBDEV['pic_base_url']}cards/tp.bmp' width='71' height='96' border='0' alt='' />&nbsp;<img src='{$TBDEV['pic_base_url']}cards/vp.bmp' width='71' height='96' border='0' alt='' /></td></tr>
        <tr><td align='left'>You must collect 21 points without going over.<br /><br />
        <b>NOTE:</b> By playing blackjack, you are betting 100 MB of upload credit!</td></tr>
        <tr><td align='center'>
        <form method='post' action='".$_SERVER['PHP_SELF']."'><input type='hidden' name='game' value='hit' readonly='readonly' /><input type='hidden' name='start' value='yes' readonly='readonly' /><input type='submit' value='Start!' /></form>
        </td></tr></table>
        </td></tr></table>
        <br /><br /><br />
  <table cellspacing='0' cellpadding='3' width='400'>
    <tr><td colspan='2' align='center'>
    <strong>Personal Statistics</strong></td></tr>
    <tr><td align='left'><b>Wins</b></td><td align='center'><b>{$tot_wins}</b></td></tr>
    <tr><td align='left'><b>Losses</b></td><td align='center'><b>{$tot_losses}</b></td></tr>
    <tr><td align='left'><b>Games Played</b></td><td align='center'><b>{$tot_games}</b></td></tr>
    <tr><td align='left'><b>Win Percentage</b></td><td align='center'><b>{$win_perc}</b></td></tr>
    <tr><td align='left'><b>+/-</b></td><td align='center'><b>{$plus_minus}</b></td></tr>
    </table>";
        print stdhead('Blackjack') . $HTMLOUT . stdfoot();
}
?>