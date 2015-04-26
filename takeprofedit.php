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
require_once "include/password_functions.php";
require_once "include/page_verify.php";

function bark($msg) {
	genbark($msg, "Update failed!");
}

dbconn();

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('takeprofedit') );
    //2-way handshake varification
    $newpage = new page_verify();  
    $newpage->check('takeprofileedit');
    //end 2-way varification
    
    if (!mkglobal("email:chpassword:passagain:chmailpass:secretanswer"))
        bark($lang['takeprofedit_no_data']);
    $ip_octets = explode( ".", getenv('REMOTE_ADDR') );

    // $set = array();

    $updateset = array();
    $changedemail = 0;

    if ($chpassword != "") 
    {
      if (strlen($chpassword) > 40)
        bark($lang['takeprofedit_pass_long']);
      if ($chpassword != $passagain)
        bark($lang['takeprofedit_pass_not_match']);
      
      $secret = mksecret();

      $passhash = make_passhash( $secret, md5($chpassword) );

      $updateset[] = "secret = " . sqlesc($secret);
      $updateset[] = "passhash = " . sqlesc($passhash);
      logincookie($CURUSER['id'], md5($passhash."-".$ip_octets[0]."-".$ip_octets[1]));
      //logincookie($CURUSER['id'], $passhash);  //decrypted
    }

    if ($secretanswer != '') { 
			  if (strlen($secretanswer) > 40) bark("Sorry, secret answer is too long (max is 40 chars)");
			  if (strlen($secretanswer) < 6) bark("Sorry, secret answer is too sort (min is 6 chars)"); 
			  
			  $new_secret_answer = md5($secretanswer);
			  $updateset[] = "hintanswer = " . sqlesc($new_secret_answer); 
	}
    if ($email != $CURUSER["email"]) 
    {
      if (!validemail($email))
        bark($lang['takeprofedit_not_valid_email']);
      $r = sql_query("SELECT id FROM users WHERE email=" . sqlesc($email)) or sqlerr();
      if ( mysql_num_rows($r) > 0 || ($CURUSER["passhash"] != make_passhash( $CURUSER['secret'], md5($chmailpass) ) ) )
        bark($lang['takeprofedit_address_taken']);
      $changedemail = 1;
    }


    $parked = $_POST["parked"];
    $split = ($_POST["split"] == "yes" ? "yes" : "no");
    $cats_icons = ($_POST["cats_icons"] == "yes" ? "yes" : "no"); 
    $acceptpms = $_POST["acceptpms"];
    $pmstyle = $_POST["pmstyle"];
    $gender = $_POST["gender"];
    $deletepms = isset($_POST["deletepms"]) ? "yes" : "no";
    $view_xxx = $_POST["view_xxx"];
    $savepms = (isset($_POST['savepms']) && $_POST["savepms"] != "" ? "yes" : "no");
    $pmnotif = isset($_POST["pmnotif"]) ? $_POST["pmnotif"] : '';
    $emailnotif = isset($_POST["emailnotif"]) ? $_POST["emailnotif"] : '';
    $notifs = ($pmnotif == 'yes' ? "[pm]" : "");
    $notifs .= ($emailnotif == 'yes' ? "[email]" : "");
    $subscription_pm = $_POST["subscription_pm"];
    $updateset[] = "subscription_pm = " . sqlesc($subscription_pm);
    $clear_new_tag_manually = (isset($_POST['clear_new_tag_manually']) && $_POST["clear_new_tag_manually"] != "" ? "yes" : "no");
    $updateset[] = "clear_new_tag_manually = ".sqlesc($clear_new_tag_manually);
    $shoutboxbg = 0 + $_POST["shoutboxbg"];
    $updateset[] = "shoutboxbg = " . sqlesc($shoutboxbg);
    $r = mysql_query("SELECT id FROM categories") or sqlerr();
    $rows = mysql_num_rows($r);
    for ($i = 0; $i < $rows; ++$i)
    {
      $a = mysql_fetch_assoc($r);
      if (isset($_POST["cat{$a['id']}"]) && $_POST["cat{$a['id']}"] == 'yes')
        $notifs .= "[cat{$a['id']}]";
    }

/////// do the avatar stuff
    $avatars = (isset($_POST['avatars']) ? $_POST['avatars'] : 'all');
    $offavatar = (isset($_POST['offavatar']) && $_POST["offavatar"] != "" ? "yes" : "no");
    $avatar = trim( urldecode( $_POST["avatar"] ) );
      
      if ( preg_match( "/^http:\/\/$/i", $avatar ) 
          or preg_match( "/[?&;]/", $avatar ) 
          or preg_match("#javascript:#is", $avatar ) 
          or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $avatar ) 
          )
      {
      $avatar='';
      }
      
      if( !empty($avatar) ) 
      {
        $img_size = @GetImageSize( $avatar );

        if($img_size == FALSE || !in_array($img_size['mime'], $TBDEV['allowed_ext']))
          stderr($lang['takeprofedit_user_error'], $lang['takeprofedit_image_error']);

        if($img_size[0] < 5 || $img_size[1] < 5)
          stderr($lang['takeprofedit_user_error'], $lang['takeprofedit_small_image']);
      
        if ( ( $img_size[0] > $TBDEV['av_img_width'] ) OR ( $img_size[1] > $TBDEV['av_img_height'] ) )
        { 
            $image = resize_image( array(
                             'max_width'  => $TBDEV['av_img_width'],
                             'max_height' => $TBDEV['av_img_height'],
                             'cur_width'  => $img_size[0],
                             'cur_height' => $img_size[1]
                        )      );
                        
          }
          else 
          {
            $image['img_width'] = $img_size[0];
            $image['img_height'] = $img_size[1];
          }
          
    $updateset[] = "av_w = " . $image['img_width'];
    $updateset[] = "av_h = " . $image['img_height'];
    }
    /////////////// avatar end /////////////////
    
    /////// do the signature stuff
    $signatures = (isset($_POST['signatures']) && $_POST["signatures"] != "" ? "yes" : "no");
    $signature = trim( urldecode( $_POST["signature"] ) );
      
      if ( preg_match( "/^http:\/\/$/i", $signature ) 
          or preg_match( "/[?&;]/", $signature ) 
          or preg_match("#javascript:#is", $signature ) 
          or !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $signature ) 
          )
      {
        $signature='';
      }
      
      if( !empty($signature) ) 
      {
        $img_size = @GetImageSize( $signature );

        if($img_size == FALSE || !in_array($img_size['mime'], $TBDEV['allowed_ext']))
          stderr('USER ERROR', 'Not an image or unsupported image!');

        if($img_size[0] < 5 || $img_size[1] < 5)
          stderr('USER ERROR', 'Image is too small');
      
        if ( ( $img_size[0] > $TBDEV['sig_img_width'] ) OR ( $img_size[1] > $TBDEV['sig_img_height'] ) )
        { 
            $image = resize_image( array(
                             'max_width'  => $TBDEV['sig_img_width'],
                             'max_height' => $TBDEV['sig_img_height'],
                             'cur_width'  => $img_size[0],
                             'cur_height' => $img_size[1]
                        )      );
                        
          }
          else 
          {
            $image['img_width'] = $img_size[0];
            $image['img_height'] = $img_size[1];
          }
          
    $updateset[] = "sig_w = " . $image['img_width'];
    $updateset[] = "sig_h = " . $image['img_height'];
    }
    //==end
    
    // $ircnick = $_POST["ircnick"];
    // $ircpass = $_POST["ircpass"];
    $info = $_POST["info"];
    $stylesheet = $_POST["stylesheet"];
    $country = $_POST["country"];

    if(isset($_POST["user_timezone"]) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone']))
    $updateset[] = "time_offset = " . sqlesc($_POST['user_timezone']);

    $updateset[] = "auto_correct_dst = " .(isset($_POST['checkdst']) ? 1 : 0);
    $updateset[] = "dst_in_use = " .(isset($_POST['manualdst']) ? 1 : 0);

    /*
    if ($privacy != "normal" && $privacy != "low" && $privacy != "strong")
      bark("whoops");

    $updateset[] = "privacy = '$privacy'";
    */
    $show_sticky = $_POST["show_sticky"];

    $updateset[] = "torrentsperpage = " . min(100, 0 + $_POST["torrentsperpage"]);
    $updateset[] = "topicsperpage = " . min(100, 0 + $_POST["topicsperpage"]);
    $updateset[] = "postsperpage = " . min(100, 0 + $_POST["postsperpage"]);
    if(isset($_POST["changeq"])  && (($changeq = (int)$_POST["changeq"]) !=  $CURUSER["passhint"]) && is_valid_id($changeq))
      $updateset[] = "passhint = " . sqlesc($changeq);

    if (is_valid_id($stylesheet))
      $updateset[] = "stylesheet = '$stylesheet'";
      
    if (is_valid_id($country))
      $updateset[] = "country = $country";


    $updateset[] = "info = " . sqlesc($info);
    $updateset[] = "parked = " . sqlesc($parked);
    $updateset[] = "split = " . sqlesc($split);
    $updateset[] = "acceptpms = " . sqlesc($acceptpms);
    $updateset[] = "pmstyle = " . sqlesc($pmstyle);
    $updateset[] = "gender = " . sqlesc($gender);
    $updateset[] = "deletepms = '$deletepms'";
    $updateset[] = "view_xxx = ". sqlesc($view_xxx);
    $updateset[] = "savepms = '$savepms'";
    $updateset[] = "notifs = '$notifs'";
    $updateset[] = "avatar = " . sqlesc($avatar);
    $updateset[] = "offavatar = ".sqlesc($offavatar);
    $updateset[] = "signature = " . sqlesc("[img]".$signature."[/img]\n");
    $updateset[] = "signatures = '$signatures'";
    $updateset[] = "language=".sqlesc($_POST['lang']);
    $updateset[] = "show_sticky =  " . sqlesc($show_sticky);
    $updateset[] = "cats_icons = " . sqlesc($cats_icons);

    /* ****** */

    $urladd = "";

    if ($changedemail) {
      $sec = mksecret();
      $hash = md5($sec . $email . $sec);
      $obemail = urlencode($email);
      $updateset[] = "editsecret = " . sqlesc($sec);
      //$thishost = $_SERVER["HTTP_HOST"];
      //$thisdomain = preg_replace('/^www\./is', "", $thishost);
      
      $body = str_replace(array('<#USERNAME#>', '<#SITENAME#>', '<#USEREMAIL#>', '<#IP_ADDRESS#>', '<#CHANGE_LINK#>'),
                        array($CURUSER['username'], $TBDEV['site_name'], $email, $_SERVER['REMOTE_ADDR'], "{$TBDEV['baseurl']}/confirmemail.php?uid={$CURUSER['id']}&key=$hash&email=$obemail"),
                        $lang['takeprofedit_email_body']);
      
      
      mail($email, "$thisdomain {$lang['takeprofedit_confirm']}", $body, "From: {$TBDEV['site_email']}");

      $urladd .= "&mailsent=1";
    }

    @mysql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id = " . $CURUSER["id"]) or sqlerr(__FILE__,__LINE__);

    header("Location: {$TBDEV['baseurl']}/my.php?edited=1" . $urladd);

/////////////////////////////////
//worker function
 /////////////////////////////////
function resize_image($in)
    {

        $out = array(
                'img_width'  => $in['cur_width'],
                'img_height' => $in['cur_height']
              );
        
        if ( $in['cur_width'] > $in['max_width'] )
        {
          $out['img_width']  = $in['max_width'];
          $out['img_height'] = ceil( ( $in['cur_height'] * ( ( $in['max_width'] * 100 ) / $in['cur_width'] ) ) / 100 );
          $in['cur_height'] = $out['img_height'];
          $in['cur_width']  = $out['img_width'];
        }
        
        if ( $in['cur_height'] > $in['max_height'] )
        {
          $out['img_height']  = $in['max_height'];
          $out['img_width']   = ceil( ( $in['cur_width'] * ( ( $in['max_height'] * 100 ) / $in['cur_height'] ) ) / 100 );
        }
        
      
        return $out;
    }

?>