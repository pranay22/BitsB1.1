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
require_once "include/html_functions.php";
require_once "include/user_functions.php";
//== Updated casino.php by d6bmg
dbconn(false);

loggedinorreturn();

$lang = array_merge( load_language('global') );

//== Config
    $amnt=0;
    $nobits = 0;
    $dummy ='';
    $abcdefgh =0;
    $player = UC_POWER_USER;
    $mb_basic = 1024 * 1024; 
    $max_download_user = $mb_basic * 1024 * 255; //= 25 Gb
    $max_download_global = $mb_basic * $mb_basic * 233.5; //== 2.5 Tb
    $required_ratio = 0.5; //== Min ratio
    $user_everytimewin_mb = $mb_basic * 20; //== Means users that wins under 70 mb get a cheat_value of 0 -> win every time
    $cheat_value = 8; //== Higher value -> less winner
    $cheat_breakpoint = 10; //== Very important value -> if (win MB > max_download_global/cheat_breakpoint)
    $cheat_value_max = 2; //== Then cheat_value = cheat_value_max -->> i hope you know what i mean. ps: must be higher as cheat_value.
    $cheat_ratio_user = .4; //== If casino_ratio_user > cheat_ratio_user -> $cheat_value = rand($cheat_value,$cheat_value_max)
    $cheat_ratio_global = .4; //== Same as user just global
    $win_amount = 3; //== How much do the player win in the first game eg. bet 300, win_amount=3 ---->>> 300*3= 900 win
    $win_amount_on_number = 6; //== Same as win_amount for the number game
    $show_real_chance = false; //== Shows the user the real chance true or false
    $bet_value1 = $mb_basic * 200; //== This is in MB but you can also choose gb or tb
    $bet_value2 = $mb_basic * 500;
    $bet_value3 = $mb_basic * 1020;
    $bet_value4 = $mb_basic * 2560;
    $bet_value5 = $mb_basic * 5120;
    $bet_value6 = $mb_basic * 10240;
    $bet_value7 = $mb_basic * 20480;

//== Config game 3
$minclass = $player; //== Lowest class allowed to play
$maxusrbet = '4'; //==Amount of bets to allow per person
$maxtotbet = '30'; //== Amount of total open bets allowed
$alwdebt = 'n'; //== Allow users to get into debt
$writelog = 'y'; //== Writes results to log
$delold = 'n'; //== Clear bets once finished
$sendfrom = '0'; //== The id of the user which notification PM's are noted as sent from
$casino = "casino.php"; //== Name of file
//== End of Config

         //== Reset user gamble stats!
         $hours = 2; //== Hours to wait after using all tries, until they will be restarted
         $dt = time() - $hours * 3600;
         $res = sql_query("SELECT userid, trys, date, enableplay FROM casino WHERE date < $dt AND trys >= '51' AND enableplay = 'yes'");
         while ($arr = mysql_fetch_assoc($res)) {
         sql_query("UPDATE casino SET trys='0' WHERE userid='$arr[userid]'") or sqlerr(__FILE__, __LINE__);
         }

         if ($CURUSER['class'] < $player)
         stderr("Sorry", "You must be a ".get_user_class_name($player)." or above to play casino");

         $query = "select * from casino where userid = $CURUSER[id]";
         $result = sql_query($query) or sqlerr(__FILE__, __LINE__);
         if (mysql_affected_rows() != 1) {
         sql_query("INSERT INTO casino (userid, win, lost, trys, date, started) VALUES(" .sqlesc($CURUSER["id"]).", 0, 0, 0, '" . time() . "1')") or mysql_error();
         $result = sql_query($query) or sqlerr(__FILE__, __LINE__);
         }

         $row = mysql_fetch_assoc($result);
         $user_win = $row["win"];
         $user_lost = $row["lost"];
         $user_trys = $row["trys"];
         $user_date = $row["date"];
         $user_deposit = $row["deposit"];
         $user_enableplay = $row["enableplay"];

         if ($user_enableplay == "no")
         stderr("Sorry", "" . htmlspecialchars($CURUSER["username"]), "your banned from casino");

         if (($user_win - $user_lost) > $max_download_user)
         stderr("Sorry","" .htmlspecialchars($CURUSER["username"]), "you have reached the max download for a single user");

         if ($CURUSER["downloaded"] > 0)
         $ratio = number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 2);
         else
         if ($CURUSER["uploaded"] > 0)
         $ratio = 999;
         else
         $ratio = 0;
         if ($ratio < $required_ratio)
         stderr("Sorry", "".htmlspecialchars($CURUSER["username"])." your ratio is under {$required_ratio}");

          $global_down2 = sql_query(" select (sum(win)-sum(lost)) as globaldown,(sum(deposit)) as globaldeposit, sum(win) as win, sum(lost) as lost from casino") or sqlerr(__FILE__, __LINE__);
          $row = mysql_fetch_assoc($global_down2);
          $global_down = $row["globaldown"];
          $global_win = $row["win"];
          $global_lost = $row["lost"];
          $global_deposit = $row["globaldeposit"];

          if ($user_win > 0)
          $casino_ratio_user = number_format($user_lost / $user_win, 2);
          else
          if ($user_lost > 0)
          $casino_ratio_user = 999;
          else
          $casino_ratio_user = 0.00;

          if ($global_win > 0)
          $casino_ratio_global = number_format($global_lost / $global_win, 2);
          else
          if ($global_lost > 0)
          $casino_ratio_global = 999;
          else
          $casino_ratio_global = 0.00;
    
          if ($user_win < $user_everytimewin_mb)
          $cheat_value = 8;
          else {
          if ($global_down > ($max_download_global / $cheat_breakpoint))
          $cheat_value = $cheat_value_max;
          if ($casino_ratio_global < $cheat_ratio_global)
          $cheat_value = rand($cheat_value, $cheat_value_max);

          if (($user_win - $user_lost) > ($max_download_user / $cheat_breakpoint))
          $cheat_value = $cheat_value_max;
          if ($casino_ratio_user < $cheat_ratio_user)
          $cheat_value = rand($cheat_value, $cheat_value_max);
          }

          if ($global_down > $max_download_global)
          stderr("Sorry", "" . htmlspecialchars($CURUSER["username"]), "but global max win is above " . htmlspecialchars(mksize($max_download_global)));
           
          //== Updated post color/number by pdq
           $goback = "<a href='$casino'>Go back</a>";
           $color_options = array('red' => 1, 'black' => 2);
           $number_options = array(1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1);
           $betmb_options = array($bet_value1 => 1, $bet_value2 => 1, $bet_value3 => 1, $bet_value4 => 1, $bet_value5 => 1, $bet_value6 => 1, $bet_value7 => 1);
           $post_color = (isset($_POST['color']) ? $_POST['color'] : '');
           $post_number = (isset($_POST['number']) ? $_POST['number'] : '');
           $post_betmb = (isset($_POST['betmb']) ? $_POST['betmb'] : '');
           if (isset($color_options[$post_color]) && isset($number_options[$post_number]) || isset($betmb_options[$post_betmb])) 
           {
           $betmb = 0 + $_POST["betmb"];
           if (isset($_POST["number"])) {
           $win_amount = $win_amount_on_number;
           $cheat_value = $cheat_value + 5;
           $winner_was = 0 + $_POST["number"];
           } else
           $winner_was = $_POST["color"];
           $win = $win_amount * $betmb;

           if ($CURUSER["uploaded"] < $betmb)
           stderr("Sorry " . htmlspecialchars($CURUSER["username"]), "but you have not uploaded " . htmlspecialchars(mksize($betmb)));

           if (rand(0, $cheat_value) == $cheat_value) {
           sql_query("UPDATE users SET uploaded = uploaded + ".sqlesc($win)." WHERE id=".sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
           sql_query("UPDATE casino SET date = '".time()."', trys = trys + 1, win = win + ".sqlesc($win)."  WHERE userid=" . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
           $mc1->delete_value('user'.$CURUSER["id"]);
           $mc1->delete_value('MyUser_'.$CURUSER["id"]);
           stderr("Yes", "".htmlspecialchars($winner_was)." is the result ".htmlspecialchars($CURUSER["username"])." you got it and win " . htmlspecialchars(mksize($win))."   $goback");
           } else {
           if (isset($_POST["number"])) {
           do {
           $fake_winner = rand(1, 6);
           } while ($_POST["number"] == $fake_winner);
           } else {
           if ($_POST["color"] == "black")
           $fake_winner = "red";
           else
           $fake_winner = "black";
           }
        
           sql_query("UPDATE users SET uploaded = uploaded - ".sqlesc($betmb)." WHERE id=".sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
           sql_query("UPDATE casino SET date = '" . time() . "', trys = trys + 1 ,lost = lost + ".sqlesc($betmb)." WHERE userid=".sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
           $mc1->delete_value('user'.$CURUSER["id"]);
           $mc1->delete_value('MyUser_'.$CURUSER["id"]);
           stderr("Sorry", "".htmlspecialchars($fake_winner)." is the winner and not ".htmlspecialchars($winner_was).", " . htmlspecialchars($CURUSER["username"])." you lost ".htmlspecialchars(mksize($betmb))."   $goback");
           }
           } else {
           //== get user stats
           $betsp = sql_query("SELECT challenged FROM casino_bets WHERE proposed =".sqlesc($CURUSER['username'])."");
           $openbet = 0;
           while ($tbet2 = mysql_fetch_assoc($betsp)) {
           if ($tbet2['challenged'] == 'empty')
           $openbet++;
           }
           //== Convert bet amount into bits
           if (isset($_POST['unit'])) {
           if (0 + $_POST["unit"] == '1')
           $nobits = $amnt * $mb_basic;
            else
           $nobits = $amnt * $mb_basic * 1024;
           }

           if ($CURUSER['uploaded'] == 0 || $CURUSER['downloaded'] == 0)
           $ratio = '0';
           else
           $ratio = number_format(($CURUSER['uploaded'] - $nobits) / $CURUSER['downloaded'], 2);
           $time = time();
           //== Take Bet
           if (isset($_GET["takebet"])) {
           $betid = 0 + $_GET["takebet"];
           $random = rand(0, 1);
           $loc = sql_query("SELECT * FROM casino_bets WHERE id = ".sqlesc($betid)."");
           $tbet = mysql_fetch_assoc($loc);
           $nogb = mksize($tbet['amount']);

            if ($CURUSER['id'] == $tbet['userid'])
            stderr("Sorry", "You want to bet against yourself lol ?   $goback");
            elseif ($tbet['challenged'] != "empty")
            stderr("Sorry", "Someone has already taken that bet!   $goback");

            if ($CURUSER['uploaded'] < $tbet['amount']) {
            $debt = $tbet['amount'] - $CURUSER['uploaded'];
            $newup = $CURUSER['uploaded'] - $debt;
            }

            if (isset($debt) && $alwdebt != 'y')
            stderr("Sorry", "<h2>You are ".htmlspecialchars(mksize(($nobits - $CURUSER['uploaded'])))." short of making that bet !</h2>   $goback");

            if ($random == 1) {
            sql_query("UPDATE users SET uploaded = uploaded+".sqlesc($tbet['amount'])." WHERE id = " . sqlesc($CURUSER['id']) . "") or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE casino SET deposit = deposit-".sqlesc($tbet['amount'])." WHERE userid = " . sqlesc($tbet['userid']) . "") or sqlerr(__FILE__, __LINE__);
            $mc1->delete_value('user'.$CURUSER["id"]);
            $mc1->delete_value('MyUser_'.$CURUSER["id"]);
            if (mysql_affected_rows() == 0)
            sql_query("INSERT INTO casino (userid, date, deposit) VALUES (".sqlesc($tbet['userid']).", '$time', '-" . sqlesc($tbet['amount']) . "')") or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE casino_bets SET challenged = ".sqlesc($CURUSER['username'])." WHERE id =".sqlesc($betid)."") or sqlerr(__FILE__, __LINE__);
            $subject = sqlesc("Casino Results");
            sql_query("INSERT INTO messages (subject, id, sender, receiver, added, msg, unread, poster) VALUES ($subject,'', '$sendfrom', ".sqlesc($tbet['userid']).", $time, 'You lost a bet ! " . htmlspecialchars($CURUSER['username']) . " just won " . htmlspecialchars($nogb) . " of your upload credit !' , 'yes', '$sendfrom')") or sqlerr(__FILE__, __LINE__);
            $mc1->delete_value('inbox_new_'.$tbet['userid']);
            $mc1->delete_value('inbox_new_sb_'.$tbet['userid']);
            if($writelog == 'y')
            write_log($CURUSER['username']." won $nogb of upload credit off $tbet[proposed]");
            if ($delold == 'y')
            sql_query("DELETE * FROM casino_bets WHERE id = ".sqlesc($tbet['id'])."") or sqlerr(__FILE__, __LINE__);
            stderr("You got it", "<h2>You won the bet, ".htmlspecialchars($nogb)." has been credited to your account, at <a href='userdetails.php?id=$tbet[userid]'>$tbet[proposed]'s</a> expense !</h2>   $goback");
            exit();
            } else {
            if (empty($newup))
            $newup = $CURUSER['uploaded'] - $tbet['amount'];
            $newup2 = $tbet['amount'] * 2;
            sql_query("UPDATE users SET uploaded = $newup WHERE id =".sqlesc($CURUSER['id']) . "") or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE users SET uploaded = uploaded + $newup2 WHERE id = ".sqlesc($tbet['userid'])."") or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE casino SET deposit = deposit-".sqlesc($tbet['amount'])." WHERE userid = ".sqlesc($tbet['userid']) . "");
            $mc1->delete_value('user'.$CURUSER["id"]);
            $mc1->delete_value('MyUser_'.$CURUSER["id"]);
            if (mysql_affected_rows() == 0)
            sql_query("INSERT INTO casino (userid, date, deposit) VALUES (".sqlesc($tbet['userid']).", '$time', '-".sqlesc($tbet['amount'])."')") or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE casino_bets SET challenged = ".sqlesc($CURUSER['username'])." WHERE id = ".sqlesc($betid)."") or sqlerr(__FILE__, __LINE__);
            $subject = sqlesc("Casino Results");
            sql_query("INSERT INTO messages (subject, sender, receiver, added, msg, unread, poster) VALUES ($subject, $sendfrom, ".sqlesc($tbet['userid']).", $time, 'You just won " . htmlspecialchars($nogb) . " of upload credit from " . htmlspecialchars($CURUSER['username']) . " !', 'yes', '$sendfrom')") or sqlerr(__FILE__, __LINE__);
            $mc1->delete_value('inbox_new_'.$tbet['userid']);
            $mc1->delete_value('inbox_new_sb_'.$tbet['userid']);
            if($writelog == 'y')
            write_log("$tbet[proposed] won $nogb of upload credit off ".$CURUSER['username']);
            if ($delold == 'y')
            sql_query("DELETE * FROM casino_bets WHERE id = ".sqlesc($tbet['id'])."") or sqlerr(__FILE__, __LINE__);
            stderr("Damn it", "<h2>You lost the bet <a href='userdetails.php?id=$tbet[userid]'>$tbet[proposed]</a> has won ".htmlspecialchars($nogb) . " of your hard earnt upload credit !</h2>    $goback");
            }
            exit();
            }
            
            //== Add a new bet
            $loca = sql_query("SELECT * FROM casino_bets WHERE challenged ='empty'") or sqlerr(__FILE__, __LINE__);
            $totbets = mysql_num_rows($loca);

            if (isset($_POST['unit'])) {
            if (0 + $_POST["unit"] == '1')
            $nobits = 0 + $_POST["amnt"] * $mb_basic;
            else
            $nobits = 0 + $_POST["amnt"] * $mb_basic * 1024;
            }

            if (isset($_POST["unit"])) {
            if ($openbet >= $maxusrbet)
            stderr ("Sorry", "There are already ".htmlspecialchars($openbet)." bets open, take an open bet or wait till someone plays !");
            if ($nobits <= 0)
            stderr ("Sorry", " This won't work enter a positive value, are you trying to cheat?");
            $newup = $CURUSER['uploaded'] - $nobits;
            $debt = $nobits - $CURUSER['uploaded'];
            
            if ($CURUSER['uploaded'] < $nobits) {
            if ($alwdebt != 'y')
            stderr("Sorry", "<h2>Thats ".htmlspecialchars(mksize($debt))." more than you got!</h2>$goback");
            }
            
            $betsp = sql_query("SELECT id, amount FROM casino_bets WHERE userid = ".sqlesc($CURUSER['id'])." ORDER BY time ASC") or sqlerr(__FILE__, __LINE__);
            $tbet2 = mysql_fetch_row($betsp);
            $dummy = "<h2>Bet added, you will receive a PM notifying you of the results when someone has taken it</h2>";
            sql_query("INSERT INTO casino_bets ( userid, proposed, challenged, amount, time) VALUES (".sqlesc($CURUSER['id']).",".sqlesc($CURUSER['username']).", 'empty', '$nobits', '$time')") or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE users SET uploaded = $newup WHERE id = ".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE casino SET deposit = deposit + $nobits WHERE userid = ".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            $mc1->delete_value('user'.$CURUSER["id"]);
            $mc1->delete_value('MyUser_'.$CURUSER["id"]);
            if (mysql_affected_rows() == 0)
            sql_query("INSERT INTO casino (userid, date, deposit) VALUES (".sqlesc($CURUSER['id']).", '$time', ".sqlesc($nobits).")") or sqlerr(__FILE__, __LINE__);
            }

            $loca = sql_query("SELECT * FROM casino_bets WHERE challenged ='empty'");
            $totbets = mysql_num_rows($loca);
            //== Output html begin
            $HTMLOUT='';
            $HTMLOUT .= "<table class='message' width='650' cellspacing='0' cellpadding='5'>
            <tr>
            <td align='center'>";
            $HTMLOUT = $dummy ;
            //== Place bet table
            if ($openbet < $maxusrbet) {
            if ($totbets >= $maxtotbet)
            $HTMLOUT .= "<br />There are already ".htmlspecialchars($maxtotbet)." bets open, take an open bet !<br >";
            else {
            $HTMLOUT .= "<form name=\"p2p\" method=\"post\" action=\"casino.php\">
            <h1>{$TBDEV['site_name']} Casino - Bet P2P with other users:</h1>
            <table width='650' cellspacing='0' cellpadding='3'>";
            $HTMLOUT .= "<tr><td align=\"center\" colspan=\"2\" class=\"colhead\">Place Bet</td></tr>";
            $HTMLOUT .= "<tr><td align=\"center\"><b>Amount to bet</b>
                <input type=\"text\" name=\"amnt\" size=\"5\" value=\"1\" />
                <select name=\"unit\">
                <option value=\"1\">MB</option>
                <option value=\"2\">GB</option>
                </select></td></tr>";
            $HTMLOUT .= "<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Gamble!\" />";
            $HTMLOUT .= "</td></tr></table></form><br />";
            }
            } else
            $HTMLOUT .= "<b>You already have ".htmlspecialchars($maxusrbet)." open bets, wait until they are completed before you start another.</b><br /><br />";
            //== Open Bets table
            $HTMLOUT .= "<table width=\"650\" cellspacing=\"0\" cellpadding=\"3\">";
            $HTMLOUT .= "<tr><td align=\"center\" class=\"colhead\" colspan=\"4\">Open Bets</td></tr>";
            $HTMLOUT .="<tr>
            <td align=\"center\" width=\"15%\"><b>Name</b></td><td width=\"15%\" align=\"center\"><b>Amount</b></td>
            <td width=\"45%\" align=\"center\"><b>Time</b></td><td align=\"center\"><b>Take Bet</b></td>
            </tr>";

            while ($res = mysql_fetch_assoc($loca)) {
            $HTMLOUT .="<tr>
            <td align=\"center\">$res[proposed]</td>
            <td align=\"center\">".htmlspecialchars(mksize($res['amount']))."</td>
            <td align=\"center\">".get_date($res['time'], 'LONG',0,1)."</td>
            <td align=\"center\"><b><a href='{$casino}?takebet=$res[id]'>This</a></b></td>
            </tr>";
            $abcdefgh = 1;
            }
            if ($abcdefgh == false)
            $HTMLOUT .="<tr><td align='center' colspan='4'>Sorry no bets currently.</td></tr>";
            $HTMLOUT .="</table><br />";
            $HTMLOUT .="<form name=\"casino\" method=\"post\" action=\"casino.php\">
            <table class=\"message\" width=\"650\" cellspacing=\"0\" cellpadding=\"5\">\n";
            $HTMLOUT .= "<tr><td align=\"center\" class=\"colhead\" colspan=\"2\">Bet on a colour</td></tr>";
            $HTMLOUT .= tr("Black", "<input name=\"color\" type=\"radio\" checked=\"checked\" value=\"black\" />", 1);
            $HTMLOUT .= tr("Red", "<input name=\"color\" type=\"radio\" checked=\"checked\" value=\"red\" />", 1);
            $HTMLOUT .= tr("How much", "
            <select name=\"betmb\">
            <option value=\"{$bet_value1}\">".mksize($bet_value1)."</option>
            <option value=\"{$bet_value2}\">".mksize($bet_value2)."</option>
            <option value=\"{$bet_value3}\">".mksize($bet_value3)."</option>
            <option value=\"{$bet_value4}\">".mksize($bet_value4)."</option>
            <option value=\"{$bet_value5}\">".mksize($bet_value5)."</option>
            <option value=\"{$bet_value6}\">".mksize($bet_value6)."</option>
            <option value=\"{$bet_value7}\">".mksize($bet_value7)."</option>
            </select>", 1);
    
            if ($show_real_chance)
            $real_chance = $cheat_value + 1;
            else
            $real_chance = 2;
    
            $HTMLOUT .= tr("Your chance", "1 : " . $real_chance, 1);
            $HTMLOUT .= tr("You can win", $win_amount . " * stake", 1);
            $HTMLOUT .= tr("Bet on color", "<input type=\"submit\" value=\"Do it!\" />", 1);
            $HTMLOUT .="</table></form><br />";

            $HTMLOUT .="<form name=\"casino\" method=\"post\" action=\"casino.php\">
            <table class=\"message\" width=\"650\" cellspacing=\"0\" cellpadding=\"5\">\n";
            $HTMLOUT .= "<tr><td align=\"center\" class=\"colhead\" colspan=\"2\">Bet on a number</td></tr>";
            $HTMLOUT .= tr("Number", '<input name="number" type="radio" checked="checked" value="1" />1  <input name="number" type="radio" value="2" />2  <input name="number" type="radio" value="3" />3', 1);
            $HTMLOUT .= tr("", '<input name="number" type="radio" value="4" />4  <input name="number" type="radio" value="5" />5  <input name="number" type="radio" value="6" />6', 1);
            $HTMLOUT .= tr("How much", "
            <select name=\"betmb\">
            <option value=\"{$bet_value1}\">".mksize($bet_value1)."</option>
            <option value=\"{$bet_value2}\">".mksize($bet_value2)."</option>
            <option value=\"{$bet_value3}\">".mksize($bet_value3)."</option>
            <option value=\"{$bet_value4}\">".mksize($bet_value4)."</option>
            <option value=\"{$bet_value5}\">".mksize($bet_value5)."</option>
            <option value=\"{$bet_value6}\">".mksize($bet_value6)."</option>
            <option value=\"{$bet_value7}\">".mksize($bet_value7)."</option>
            </select>", 1);

            if ($show_real_chance)
            $real_chance = $cheat_value + 5;
            else
            $real_chance = 6;
            
            $HTMLOUT .= tr("Your chance", "1 : " . $real_chance, 1);
            $HTMLOUT .= tr("You can win", $win_amount_on_number . " * stake", 1);
            $HTMLOUT .= tr("Bet on number", "<input type=\"submit\" value=\"Do it!\" />", 1);
            $HTMLOUT .="</table></form><br />";
    
            $HTMLOUT .="<table cellspacing='0' width='650' cellpadding='3'>";
            $HTMLOUT .= "<tr><td align=\"center\" class=\"colhead\" colspan=\"3\">{$CURUSER['username']}'s details</td></tr>
            <tr><td align='center'>
            <h1>User @ {$TBDEV['site_name']} Casino</h1>
            <table class='message'  cellspacing='0' cellpadding='5'>";
            $HTMLOUT .= tr("You can win",mksize($max_download_user),1);
            $HTMLOUT .= tr("Won",mksize($user_win),1);
            $HTMLOUT .= tr("Lost",mksize($user_lost),1);
            $HTMLOUT .= tr("Ratio",$casino_ratio_user,1);
            $HTMLOUT .= tr('Deposit on P2P', mksize($user_deposit+$nobits));
            $HTMLOUT .="</table>";
            $HTMLOUT .=" </td><td align='center'>
            <h1>Global stats</h1>
            <table class='message'  cellspacing='0' cellpadding='5'>";
            $HTMLOUT .= tr("Users can win",mksize($max_download_global),1);
            $HTMLOUT .= tr("Won",mksize($global_win),1);
            $HTMLOUT .= tr("Lost",mksize($global_lost),1);
            $HTMLOUT .= tr("Ratio",$casino_ratio_global,1);
            $HTMLOUT .= tr("Deposit",mksize($global_deposit));
            $HTMLOUT .="</table>";
            $HTMLOUT .="</td><td align='center'>
            <h1>User stats</h1>
            <table class='message'  cellspacing='0' cellpadding='5'>";
            $HTMLOUT .= tr('Uploaded',mksize($CURUSER['uploaded'] - $nobits));
            $HTMLOUT .= tr('Downloaded',mksize($CURUSER['downloaded']));
            $HTMLOUT .= tr('Ratio',$ratio);
            $HTMLOUT .="</table></td></tr></table>";
            }
print stdhead("{$TBDEV['site_name']} Casino") . $HTMLOUT . stdfoot();
?>