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

require_once "include/bittorrent.php" ;
dbconn();

    ini_set('session.use_trans_sid', '0');

    $lang = array_merge( load_language('global'), load_language('login') );
    
    // Begin the session
    session_start();
    /*if (isset($_SESSION['captcha_time']))
    (time() - $_SESSION['captcha_time'] < 10) ? exit("{$lang['login_spam']}") : NULL;
*/
    $HTMLOUT = '';
    //IE alert
    $browser = $_SERVER['HTTP_USER_AGENT'];
   if(preg_match("/MSIE/i",$browser))//browser is IE
   {
    $HTMLOUT .="<div class='notification warning2 autoWidth' style='width: 957px;'><span></span>
         <div class='text'><p style='font-size: 12px;'><strong>Warning!</strong>It appears as though you are running Internet Explorer, this site was <b>NOT</b> intended to be viewed with internet explorer and chances are it will not look right and may not even function correctly.
    You should consider downloading a real browser, Firefox from <a href='http://www.mozilla.com/firefox'><font color=#BB7070><b>HERE</b></font></a>. <strong>Get a SAFER browser !</strong></p>
         </div>
        </div>";
   }
   //end IE alert
   
   //== 09 failed logins
	function left ()
	{
	global $TBDEV;
	$total = 0;
	$ip = sqlesc(getip());
	$fail = mysql_query("SELECT SUM(attempts) FROM failedlogins WHERE ip=$ip") or sqlerr(__FILE__, __LINE__);
	list($total) = mysql_fetch_row($fail);
	$left = $TBDEV['failedlogins'] - $total;
	if ($left <= 2)
	$left = "<font color='red' size='4'>" . $left . "</font>";
	else
	$left = "<font color='green' size='4'>" . $left . "</font>";
	return $left;
	}
	//== End Failed logins

    unset($returnto);
    if (!empty($_GET["returnto"])) {
      $returnto = $_GET["returnto"];
      if (!isset($_GET["nowarn"])) 
      {
        $HTMLOUT .="";
        //New Info message by d6m4u
        $HTMLOUT .="<div class='notification info1'><span></span>
         <div class='text'><p><strong>Opps!</strong>You are not logged in. Please log in or leave this site. If you are banned and think that is unfair, then feel
         free to contact any staff.</p>
         </div>
        </div>";
        //$HTMLOUT .= "{$lang['login_error']}";
      }
    }

    $value = array('...','...','...','...','...','...');
    $value[rand(1,count($value)-1)] = 'B';
    $HTMLOUT .= "<script type='text/javascript' src='captcha/captcha.js'></script>

    <form method='post' action='takelogin.php'>
    You have <b> " . left () ." </b> login attempt(s) remaining.<br /><br />
    <table border='0' cellpadding='4'>
      <tr><td class='colhead' align='center' colspan='2' title='Sign in to your account'><img src='pic/account.png' alt='' title='Sign in to your account' />  Sign in to your account</td>
      </tr>
      <tr>
        <td class='rowhead'>{$lang['login_username']}</td>
        <td align='left'><input class='user' type='text' style='border: 1px dashed #97BCC2;' size='40' name='username' /></td>
      </tr>
      <tr>
        <td class='rowhead'>{$lang['login_password']}</td>
        <td align='left'><input class='pass & keyboardInput' type='password' style='border: 1px dashed #97BCC2;' size='40' name='password' value='' /></td>
      </tr>";
      /*$HTMLOUT.="<tr>
        <td>&nbsp;</td>
        <td>
          <div id='captchaimage'>
          <a href='login.php' onclick=\"refreshimg(); return false;\" title='{$lang['login_refresh']}'>
          <img class='cimage' src='captcha/GD_Security_image.php?".TIME_NOW."' alt='{$lang['login_captcha']}' />
          </a>
          </div>
         </td>
      </tr>
      <tr>
          <td class='rowhead'>{$lang['login_pin']}</td>
          <td>
            <input type='text' maxlength='6' name='captcha' id='captcha' onblur='check(); return false;'/>
          </td>
      </tr>";*/
       $HTMLOUT.="<tr><td class='rowhead'>{$lang['login_duration']}</td><td align='left'><input type='checkbox' name='logout' value='yes' checked='checked' />{$lang['login_15mins']}</td></tr>
    <tr>
     <td align='center' colspan='2'>Now click the button marked <strong>B</strong></td>
     </tr>
     <tr>
     <td colspan='2' align='center'>";
     for ($i=0; $i < count($value); $i++) {
     $HTMLOUT .= "<input name=\"submitme\" type=\"submit\" value=\"".$value[$i]."\" class=\"btn\" />";
     }
     $HTMLOUT .= "</td></tr></table>";


    if (isset($returnto))
      $HTMLOUT .= "<input type='hidden' name='returnto' value='" . htmlentities($returnto) . "' />\n";


    $HTMLOUT .= "</form><br /><br />";


    print stdhead("{$lang['login_login_btn']}") . $HTMLOUT . stdfoot();

?>