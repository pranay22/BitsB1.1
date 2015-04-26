<?php
/**
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.0
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
+------------------------------------------------
**/

require_once "include/bittorrent.php";
require_once ROOT_PATH."/cache/timezones.php";
require_once "include/html_functions.php";

dbconn();
    
    if( isset($CURUSER) )
    {
      header("Location: {$TBDEV['baseurl']}/index.php");
      exit();
    }
    
    ini_set('session.use_trans_sid', '0');

    $lang = array_merge( load_language('global'), load_language('signup') );
    if(!$TBDEV['openreg'])
    stderr('Sorry', 'Invite only - Signups are closed presently');
    
    // Begin the session
    /*session_start();
    if (isset($_SESSION['captcha_time']))
    (time() - $_SESSION['captcha_time'] < 10) ? exit($lang['captcha_spam']) : NULL;*/
    
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
    $HTMLOUT .="<script type='text/javascript' src='scripts/jquery.js'></script>
<script type='text/javascript' src='scripts/jquery.pstrength-min.1.2.js'>
</script>
<script type='text/javascript'>
$(function() {
$('.password').pstrength();
});
</script>";
    
    $res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res);
    if ($arr[0] >= $TBDEV['maxusers'])
      stderr($lang['stderr_errorhead'], sprintf($lang['stderr_ulimit'], $TBDEV['maxusers']));

    // TIMEZONE STUFF
        $offset = (string)$TBDEV['time_offset'];
        
        $time_select = "<select name='user_timezone'>";
        
        foreach( $TZ as $off => $words )
        {
          if ( preg_match("/^time_(-?[\d\.]+)$/", $off, $match))
          {
            $time_select .= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
          }
        }
        
        $time_select .= "</select>";
    // TIMEZONE END

    $thistime = time();
    $value = array('...','...','...','...','...','...');
    $value[rand(1,count($value)-1)] = 'B';

    $HTMLOUT .= "<div class='notification info1'><span></span>
         <div class='text'><p><strong>Note!</strong>{$lang['signup_cookies']}</p>
         </div>
        </div>
    
    <script type='text/javascript' src='captcha/captcha.js'></script>
    <script type='text/javascript' src='scripts/check.js'></script>

    <form method='post' action='takesignup.php'>
    <noscript>Javascript must be enabled to login and use this site</noscript>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr><td align='center' colspan='2' class='colhead' title='Register a new account'><img alt='' src='pic/account.png' tooltip='Register a new account'>  Register a new account</td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_uname']}</td><td align='left'><input class='user' type='text' style='border: 1px dashed #97BCC2;' size='40' name='wantusername' id='wantusername' onblur='checkit();' /><div id='namecheck'></div></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_pass']}</td><td align='left'><input class='pass & password & keyboardInput' style='border: 1px dashed #97BCC2;' type='password' size='40' name='wantpassword' value=''  /></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_passa']}</td><td align='left'><input class='pass & keyboardInput' style='border: 1px dashed #97BCC2;' type='password' size='40' name='passagain' value='' /></td></tr>
    <tr valign='top'><td align='right' class='heading'>{$lang['signup_email']}</td><td align='left'><input class='email' type='text' style='border: 1px dashed #97BCC2;' size='40' name='email' />
    <table width='250' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'><font class='small'>{$lang['signup_valemail']}</font></td></tr>
    </table>
    </td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_timez']}</td><td align='left' >{$time_select}</td></tr>
";
     $HTMLOUT .= tr($lang['signup_gender'],
    "<input type='radio' name='gender'" . (" checked='checked'") . " value='Male' />{$lang['signup_male']}
    <input type='radio' name='gender'" .  (" checked='checked'") . " value='Female' />{$lang['signup_female']}
",1);
      //==Passhint
     $passhint="";
     $questions = array(
	    array("id"=> "1", "question"=> "{$lang['signup_q1']}"),
			array("id"=> "2", "question"=> "{$lang['signup_q2']}"),
			array("id"=> "3", "question"=> "{$lang['signup_q3']}"),
			array("id"=> "4", "question"=> "{$lang['signup_q4']}"),
			array("id"=> "5", "question"=> "{$lang['signup_q5']}"),
			array("id"=> "6", "question"=> "{$lang['signup_q6']}"));
		  foreach($questions as $sph){  
		  $passhint .= "<option value='".$sph['id']."'>".$sph['question']."</option>\n"; 
		  }
		  $HTMLOUT .= "<tr><td align='right' class='heading'>{$lang['signup_select']}</td><td align='left'><select name='passhint' style='border: 1px dashed #97BCC2;'>\n$passhint\n</select></td></tr>
		  <tr><td align='right' class='heading'>{$lang['signup_enter']}</td><td align='left'><input type='text' size='40'  style='border: 1px dashed #97BCC2;' name='hintanswer' /><br /><font class='small'>{$lang['signup_this_answer']}<br />{$lang['signup_this_answer1']}</font></td></tr>
      <tr>
        <td>&nbsp;</td>
        <td>
          <div id='captchaimage'>
          <a href='signup.php' onclick=\"refreshimg(); return false;\" title='{$lang['captcha_refresh']}'>
          <img class='cimage' src='captcha/GD_Security_image.php?$thistime' alt='{$lang['captcha_image_alt']}' />
          </a>
          </div>
         </td>
      </tr>
      <tr>
          <td class='rowhead'>{$lang['captcha_pin']}</td>
          <td>
            <input type='text' style='border: 1px dashed #97BCC2;' maxlength='6' name='captcha' id='captcha' onblur='check(); return false;'/>
          </td>
      </tr>
    <tr><td align='right' class='heading'></td><td align='left'><input type='checkbox' name='rulesverify' value='yes' /> {$lang['signup_rules']}<br />
    <input type='checkbox' name='faqverify' value='yes' /> {$lang['signup_faq']}<br />
    <input type='checkbox' name='ageverify' value='yes' /> {$lang['signup_age']}</td></tr>
    <tr><td align='center' colspan='2'>Now click the button marked <strong>B</strong></td></tr><tr>
      <td colspan='2' align='center'>";
      for ($i=0; $i < count($value); $i++) {
      $HTMLOUT .= "<input name=\"submitme\" type=\"submit\" value=\"".$value[$i]."\" class=\"btn\" />";
      }
      $HTMLOUT .= "</td></tr></table></form><br /><br />";


    print stdhead($lang['head_signup']) . $HTMLOUT . stdfoot();

?>