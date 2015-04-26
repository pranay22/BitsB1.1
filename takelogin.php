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

require_once 'include/bittorrent.php';
require_once "include/password_functions.php";

$sha = sha1($_SERVER['REMOTE_ADDR']);
if (is_file('' . $TBDEV['dictbreaker'] . '/' . $sha) && filemtime('' . $TBDEV['dictbreaker'] . '/' . $sha) > (time()-8)) {
@fclose(@fopen('' . $TBDEV['dictbreaker'] . '/' . $sha, 'w'));
die('Minimum 8 seconds between login attempts :)');
}
    // 09 failed logins
    function failedloginscheck () {
        global $TBDEV;
        $total = 0;
        $ip = sqlesc(getip());
        $res = mysql_query("SELECT SUM(attempts) FROM failedlogins WHERE ip=$ip") or sqlerr(__FILE__, __LINE__);
        list($total) = mysql_fetch_row($res);
        if ($total >= $TBDEV['failedlogins']) {
        mysql_query("UPDATE failedlogins SET banned = 'yes' WHERE ip=$ip") or sqlerr(__FILE__, __LINE__);
        stderr("Login Locked!", "You have been <b>Exceeded</b> the allowed maximum login attempts without successful login, therefore your ip address <b>(".htmlspecialchars($ip).")</b> has been locked for 24 hours.");
        }
    }
//==End

    if (!mkglobal('username:password:submitme'))
      die();
      
    session_start();
      /*if(empty($captcha) || $_SESSION['captcha_id'] != strtoupper($captcha)){
          header('Location: login.php');
          exit();
    }*/

    dbconn();
    
    $lang = array_merge( load_language('global'), load_language('takelogin') );
    $ip_octets = explode( ".", getenv('REMOTE_ADDR') );
    
    function bark($text = 'Username or password incorrect')
    {
    global $lang;
    @fclose(@fopen(''.$TBDEV['dictbreaker'].'/' . sha1($_SERVER['REMOTE_ADDR']), 'w'));
    stderr($lang['tlogin_failed'], $text);
    }
    failedloginscheck ();

    $res = mysql_query("SELECT id, passhash, secret, enabled FROM users WHERE username = " . sqlesc($username) . " AND status = 'confirmed'");
    $row = mysql_fetch_assoc($res);

    if (!$row)
      bark();
    
//==09 Failed logins
    if (!$row)
	  {
    $ip = sqlesc(getip());
    $added = sqlesc(time());
    $fail = (@mysql_fetch_row(@mysql_query("select count(*) from failedlogins where ip=$ip"))) or sqlerr(__FILE__, __LINE__);
    if ($fail[0] == 0)
    mysql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip, $added, 1)") or sqlerr(__FILE__, __LINE__);
    else
    mysql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip=$ip") or sqlerr(__FILE__, __LINE__);
    @fclose(@fopen('' . $TBDEV['dictbreaker'] . '/' . sha1($_SERVER['REMOTE_ADDR']), 'w'));
    bark();
	}
    if ($submitme != 'B')
    stderr('Ha! Ha!', 'You Missed, You plonker !');
    
    if ($row['passhash'] != make_passhash( $row['secret'], md5($password) ) ) {
    $ip = sqlesc(getip());
    $added = sqlesc(time());
    $fail = (@mysql_fetch_row(@mysql_query("select count(*) from failedlogins where ip=$ip"))) or sqlerr(__FILE__, __LINE__);
    if ($fail[0] == 0)
    mysql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip, $added, 1)") or sqlerr(__FILE__, __LINE__);
    else
    mysql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip=$ip") or sqlerr(__FILE__, __LINE__);
    @fclose(@fopen('' . $TBDEV['dictbreaker'] . '/' . sha1($_SERVER['REMOTE_ADDR']), 'w'));
    $to = ($row["id"]);
    $subject="Failed login";
	  $msg = "[color=red]Security alert[/color]\n Account: ID=".$row['id']." Somebody (probably you, ".$username." !) tried to login but failed!". "\nTheir [b]Ip Address [/b] was : ". $ip . "\n If this wasn't you please report this event to a {$TBDEV['site_name']} staff member\n - Thank you.\n";
	  $sql = "INSERT INTO messages (sender, receiver, msg, subject, added) VALUES('System', '$to', ". sqlesc($msg).", ". sqlesc($subject).", $added);";
	  $res = mysql_query($sql) or sqlerr(__FILE__, __LINE__);
	  stderr("Login failed !", "<b>Error</b>: Username or password entry incorrect <br />Have you forgotten your password? <a href='{$TBDEV['baseurl']}/recover.php'><b>Recover</b></a> your password !");
	  //stderr("Login failed !", "<b>Error</b>: Username or password entry incorrect <br />Have you forgotten your password? <a href='{$TBDEV['baseurl']}/resetpw.php'><b>Recover</b></a> your password !");
	  bark();
    }
    //== End
    if ($row['enabled'] == 'no')
      bark($lang['tlogin_disabled']);
    //Logout after 15 minutes of inactivity
    if ((isset ($_POST['logout']) AND $_POST['logout'] == 'yes'))
    {
        $passh = md5($row["passhash"]."-".$ip_octets[0]."-".$ip_octets[1]); 
        logincookie($row['id'], $passh, 15);
        //logincookie ($row['id'], $row["passhash"], 15);  //decrypted
    } 
    else 
    { 
        $passh = md5($row["passhash"]."-".$ip_octets[0]."-".$ip_octets[1]); 
        logincookie($row['id'], $passh);
        //logincookie ($row['id'], $row["passhash"]);  //decrypted
    }
    //end logout after 15 minutes of inactivity
    $ip = sqlesc(getip());
    mysql_query("DELETE FROM failedlogins WHERE ip = $ip");


    ////Start IP logger //// 
    $added = sqlesc(time()); 
    $userid = ($row["id"]); 
    $res = mysql_query("SELECT * FROM ips WHERE ip = $ip AND userid = $userid") or die(mysql_error()); 
    if (mysql_num_rows($res) == 0 ) { 
        mysql_query("INSERT INTO ips (userid, ip, lastlogin, type) VALUES ($userid, $ip , $added, 'Login')") or die(mysql_error()); 
    } 
    else { 
        mysql_query("UPDATE ips SET lastlogin = $added where ip=$ip AND userid = $userid") or sqlerr(__FILE__, __LINE__); 
    } 
        //// End Ip logger /////
//$returnto = str_replace('&amp;', '&', htmlspecialchars($_POST['returnto']));
//$returnto = $_POST['returnto'];
    //if (!empty($returnto))
      //header("Location: ".$returnto);
    //else
      header("Location: {$TBDEV['baseurl']}/index.php");

?>