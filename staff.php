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

ob_start("ob_gzhandler");

require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once("include/html_functions.php");
require_once"include/bbcode_functions.php";
require_once "cache/fls_langs.php";

dbconn(true);

loggedinorreturn();
    
    $lang = array_merge( load_language('global'), load_language('staff') );
    
    $HTMLOUT = '';
    
    $query = mysql_query("SELECT users.id, username, email, last_access, class, title, country, status, countries.flagpic, countries.name FROM users LEFT  JOIN countries ON countries.id = users.country WHERE class >=4 AND status='confirmed' ORDER BY username") or sqlerr();

    while($arr2 = mysql_fetch_assoc($query)) {
      
    /*	if($arr2["class"] == UC_VIP)
        $vips[] =  $arr2;
    */	
      if($arr2["class"] == UC_FORUM_MOD)
        $frmmods[] =  $arr2;

      if($arr2["class"] == UC_MODERATOR)
        $mods[] =  $arr2;
               
      if($arr2["class"] == UC_ADMINISTRATOR)
        $admins[] =  $arr2;
        
      if($arr2["class"] == UC_SYSOP)
        $sysops[] =  $arr2;
        
      if($arr2["class"] == UC_STAFF_LEADER)
        $stafflead[] =  $arr2;
      }
    /*
    print_r($sysops);
    print("<br />");
    print_r($admins);
    print("<br />");
    print_r($mods);
    print("<br />");
    print(count($mods));
    */
    function DoStaff($staff, $staffclass, $cols = 2) 
    {
      global $TBDEV, $lang;
      
      $dt = time() - 180;
      $htmlout = '';
      
      if($staff===false) 
      {
        $htmlout .= "<br /><table width='100%' border='1' cellpadding='0'>";
        $htmlout .= "<tr><td class='colhead'>{$staffclass}</td></tr>";
        $htmlout .= "<tr><td>{$lang['text_none']}</td></tr></table>";
        return;
      }
      $counter = count($staff);
        
      $rows = ceil($counter/$cols);
      $cols = ($counter < $cols) ? $counter : $cols;
      //echo "<br />" . $cols . "   " . $rows;
      $r = 0;
      $htmlout .= "<br /><table width='100%' border='1' cellpadding='0'>";
      $htmlout .= "<tr><td class='colhead' colspan='{$counter}' align='center'>{$staffclass}</td></tr>";
      
      for($ia = 0; $ia < $rows; $ia++)
      {

            $htmlout .= "<tr>";
            for($i = 0; $i < $cols; $i++)
            {
              if( isset($staff[$r]) )  
              {
                $htmlout .= "<td>&nbsp;&nbsp;<a href='userdetails.php?id={$staff[$r]['id']}'>".$staff[$r]["username"]."</a>".
          "   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff".
          ($staff[$r]['last_access']>$dt?"/button_online.gif":"/button_offline.gif" )."' border='0' alt='' />".
          "<a href='sendmessage.php?receiver={$staff[$r]['id']}'>".
          "   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff/button_pm.gif' border='0' title=\"{$lang['alt_pm']}\" alt='' /></a>".
          //Commented out as email link to any staff could be dangerous!! lol. Security comes first!
          //"<a href='email-gateway.php?id={$staff[$r]['id']}'>".
          //"   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff/mail.png' border='0' alt='{$staff[$r]['username']}' title=\"{$lang['alt_sm']}\" /></a>".
          "   <img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}flag/{$staff[$r]['flagpic']}' border='0' alt='{$staff[$r]['name']}' /></td>";
          $r++;
              }
              else
              {
                $htmlout .= "<td>&nbsp;</td>";
              }
            }
            $htmlout .= "</tr>";
        
      }
      $htmlout .= "</table>";
    /*
    print("</table>");
    print("<br /><table border=1><tr>");
    for ($i = 0; $i <= count($staff)-1; $i++) {
        print("<td>{$staff[$i]["username"]}</td>");
        }
        print("</tr></table>");
    */
      return $htmlout;
    }

    //$HTMLOUT .= "<h1>{$lang['text_staff']}</h1>";
    
    $HTMLOUT .= begin_main_frame("");
    $HTMLOUT .= begin_frame("");
            $HTMLOUT .= "<table width='760px' border='1px solid' cellpadding='3'>";
        $HTMLOUT .= "<tr><td class='colhead' align='center'><img src='pic/staff.png' alt='' title='' /> " .$TBDEV['site_name']." Staffs</td></tr>";
        $HTMLOUT .= "<tr><td class='embedded'><br />{$lang['text_hdesk']}<br />";
       { 
    $HTMLOUT .= "&nbsp;".DoStaff($stafflead, "<img src='pic/st-staff_lead.png' alt='' title='' /> {$lang['header_stafflead']}");
    //$HTMLOUT .= "&nbsp;".DoStaff($sysops, "{$lang['header_sysops']}");
    $HTMLOUT .= isset($sysops) ? DoStaff($sysops, "<img src='pic/st-sysop.png' alt='' title='' />{$lang['header_sysops']}") : DoStaff($sysops=false, "{$lang['header_sysops']}");
    $HTMLOUT .= isset($admins) ? DoStaff($admins, "<img src='pic/st-admin.png' alt='' title='' />{$lang['header_admins']}") : DoStaff($admins=false, "{$lang['header_admins']}");
    $HTMLOUT .= isset($mods) ? DoStaff($mods, "<img src='pic/st-mod.png' alt='' title='' />{$lang['header_mods']}") : DoStaff($mods=false, "{$lang['header_mods']}");
    $HTMLOUT .= isset($frmmods) ? DoStaff($frmmods, "<img src='pic/st-forum_mod.png' alt='' title='' />  {$lang['header_frmmods']}") : DoStaff($frmmods=false, "{$lang['header_frmmods']}");
    //$HTMLOUT .= isset($vips) ? DoStaff($vips, "{$lang['header_vips']}") : DoStaff($vips=false, "{$lang['header_vips']}");
    }
    $HTMLOUT .= "</td></tr></table>";
    $HTMLOUT .= end_frame();
    $HTMLOUT .= end_main_frame(); 
    
    //FLS
 	$HTMLOUT .="<br />";
 	$dt = time() - 180;
 	$firstline='';
 	$q = mysql_query("SELECT users.id, username, email, last_access, country, status, support, supportfor, countries.flagpic, countries.name, support_lang FROM users LEFT JOIN countries ON countries.id = users.country WHERE support='yes' AND status='confirmed' ORDER BY username LIMIT 20") or sqlerr();
 	while($a = mysql_fetch_assoc($q)) {
 	  unset($support);
 	  if(stristr($a["support_lang"],"|")) 
        foreach(explode("|",$a["support_lang"]) as $lang_id) 
            $support[] = $_fls[$lang_id]; 
      else 
        $support[] = $_fls[$a["support_lang"]]; 
      $support = join(" | ",$support);
		$firstline .= "<tr><td class='embedded'><a href='userdetails.php?id=".$a['id']."'>".$a['username']."</a></td>
 	<td class='embedded'><img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff".
 	($a['last_access']>$dt?"/button_online.gif":"/button_offline.gif" )."' border='0' alt='' /></td>".
 	"<td class='embedded'><a href='sendmessage.php?receiver=".$a['id']."'>"."<img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff/button_pm.gif' border='0'title=\"{$lang['alt_pm']}\" alt='' /></a></td>".
 	"<td class='embedded'>$support</td>".
 	"<td class='embedded'>".$a['supportfor']."</td></tr>";
 	}
 	$HTMLOUT .= begin_main_frame("");
 	$HTMLOUT .= begin_frame("");
 	$HTMLOUT .= "<table cellspacing='0' width='100%'><tr><td class='colhead' align='center'><img src='pic/fls.png' alt='' title='' /> {$lang['header_first']}</td></tr></table>";
 	$HTMLOUT .= "<fieldset style='width: 740px; border:1px solid'>
 	<legend>{$lang['first_support']}</legend>
 	<table cellspacing='0' width='725'>
		<tr>
		<td class='embedded' colspan='11'>{$lang['text_first']}<br /><br />
		</td>
		</tr>
		<tr>
		<td class='embedded' width='30'><b>{$lang['first_name']}&nbsp; </b></td>
		<td class='embedded' width='5'><b>{$lang['first_active']}&nbsp;&nbsp;&nbsp; </b></td>
		<td class='embedded' width='5'><b>{$lang['first_contact']}&nbsp;&nbsp;&nbsp;&nbsp; </b>
		</td>
		<td class='embedded' width='85'><b>{$lang['first_lang']}</b></td>
		<td class='embedded' width='200'><b>{$lang['first_supportfor']}</b></td>
 	</tr>
 	".$firstline."
 	</table></fieldset>";
		$HTMLOUT .= end_frame();
		$HTMLOUT .= end_main_frame(); 	
 	//end
    print stdhead("{$lang['stdhead_staff']}") . $HTMLOUT . stdfoot();

?>