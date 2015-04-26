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

/////////////// REP SYSTEM /////////////
//$CURUSER['reputation'] = 650;

function get_reputation($user, $mode = 0, $rep_is_on = TRUE)
	{
	global $TBDEV;
	
	
	
	$member_reputation = "";
	if( $rep_is_on )
		{
			@include 'cache/rep_cache.php';
			// ok long winded file checking, but it's much better than file_exists
			if( ! isset( $reputations ) || ! is_array( $reputations ) || count( $reputations ) < 1)
			{
				return '<span title="Cache doesn\'t exist or zero length">Reputation: Offline</span>';
			}
			
			$user['g_rep_hide'] = isset( $user['g_rep_hide'] ) ? $user['g_rep_hide'] : 0;
	
			// Hmmm...bit of jiggery-pokery here, couldn't think of a better way.
			$max_rep = max(array_keys($reputations));
			if($user['reputation'] >= $max_rep)
			{
				$user_reputation = $reputations[$max_rep];
			}
			else
			foreach($reputations as $y => $x) 
			{
				if( $y > $user['reputation'] ) { $user_reputation = $old; break; }
				$old = $x;
			}
			
			//$rep_is_on = TRUE;
			//$CURUSER['g_rep_hide'] = FALSE;
					
			$rep_power = $user['reputation'];
			$posneg = '';
			if( $user['reputation'] == 0 )
			{
				$rep_img   = 'balance';
				$rep_power = $user['reputation'] * -1;
			}
			elseif( $user['reputation'] < 0 )
			{
				$rep_img   = 'neg';
				$rep_img_2 = 'highneg';
				$rep_power = $user['reputation'] * -1;
			}
			else
			{
				$rep_img   = 'pos';
				$rep_img_2 = 'highpos';
			}

			if( $rep_power > 500 )
			{
				// work out the bright green shiny bars, cos they cost 100 points, not the normal 100
				$rep_power = ( $rep_power - ($rep_power - 500) ) + ( ($rep_power - 500) / 2 );
			}

			// shiny, shiny, shiny boots...
			// ok, now we can work out the number of bars/pippy things
			$rep_bar = intval($rep_power / 100);
			if( $rep_bar > 10 )
			{
				$rep_bar = 10;
			}

			if( $user['g_rep_hide'] ) // can set this to a group option if required, via admin?
			{
				$posneg = 'off';
				$rep_level = 'rep_off';
			}
			else
			{ // it ain't off then, so get on with it! I wanna see shiny stuff!!
				$rep_level = $user_reputation ? $user_reputation : 'rep_undefined';// just incase

				for( $i = 0; $i <= $rep_bar; $i++ )
				{
					if( $i >= 5 )
					{
						$posneg .= "<img src='pic/rep/reputation_$rep_img_2.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
					}
					else
					{
						$posneg .= "<img src='pic/rep/reputation_$rep_img.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
					}
				}
			}
			
			// now decide if we in a forum or statusbar?
			if( $mode === 0 )
			return "Rep: ".$posneg . "<br /><a href='javascript:;' onclick=\"PopUp('{$TBDEV['baseurl']}/reputation.php?pid={$user['id']}','Reputation',400,241,1,1);\"><img src='./pic/plus.gif' border='0' alt='Add reputation:: {$user['username']}' title='Add reputation:: {$user['username']}' /></a>";
			else
			return "Rep: ".$posneg;
			
		} // END IF ONLINE
		
		// default
		return '<span title="Set offline by admin setting">Rep System Offline</span>';
	}
////////////// REP SYSTEM END //////////

function get_user_icons($arr, $big = false)
  {
    global $TBDEV;
    
    if ($big)
    {
      $donorpic = "starbig.gif";
      $warnedpic = "warnedbig.gif";
      $leechwarnpic = "leechwarnedbig.gif";
      $disabledpic = "disabledbig.gif";
      $style = "style='margin-left: 4pt'";
      $flssupportpic = "suptbig.gif";
    }
    else
    {
      $donorpic = "star.gif";
      $warnedpic = "warned.gif";
      $leechwarnpic = "leechwarned.gif";
      $disabledpic = "disabled.gif";
      $style = "style=\"margin-left: 2pt\"";
      $flssupportpic = "supt.gif";
    }
    $pics = $arr["donor"] == "yes" ? "<img src=\"{$TBDEV['pic_base_url']}{$donorpic}\" alt='Donor' border='0' $style />" : "";
    $pics .= $arr["support"] == "yes" ? "<img src=\"{$TBDEV['pic_base_url']}{$flssupportpic}\" alt='FLS' border='0' $style />" : "";
    if ($arr["enabled"] == "yes")
      $pics .= ($arr["leechwarn"] == "yes" ? "<img src=\"{$TBDEV['pic_base_url']}{$leechwarnpic}\" alt=\"Leechwarned\" border=\"0\" $style />" : "") . ($arr["warned"] == "yes" ? "<img src=\"{$TBDEV['pic_base_url']}{$warnedpic}\" alt=\"Warned\" border=\"0\" $style />" : "");
    else
      $pics .= "<img src=\"{$TBDEV['pic_base_url']}{$disabledpic}\" alt=\"Disabled\" border='0' $style />\n";
    return $pics;
}
function get_user_class_image($class)
{
  switch ($class)
  {
    case UC_BANNED: return "<img src='pic/userimages/banned.gif' alt='Banned' title='Banned' />";
    case UC_USER: return "<img src='pic/userimages/user.gif' alt='User' title='User' />";
    case UC_POWER_USER: return "<img src='pic/userimages/power.gif' alt='Power User' title='Power User' />";
    case UC_UPLOADER: return "<img src='pic/userimages/uploader.gif' alt='Uploader' title='Uploader' />";
    case UC_VIP: return "<img src='pic/userimages/vip.gif' alt='VIP' title='VIP' />";
    case UC_FORUM_MOD: return "<img src='pic/userimages/forummoderator.gif' alt='Forum Moderator' title='Forum Moderator' />";
    case UC_MODERATOR: return "<img src='pic/userimages/mod.gif' alt='Moderator' title='Moderator' />";
    case UC_ADMINISTRATOR: return "<img src='pic/userimages/admin.gif' alt='Administrator' title='Administrator' />";
    case UC_SYSOP: return "<img src='pic/userimages/sysop.gif' alt='SysOp' title='SysOp' />";
    case UC_STAFF_LEADER: return "<img src='pic/userimages/staffleader.gif' alt='Staff Leader' title='Staff Leader' />";
  }
  return "";
}

function get_ratio_color($ratio)
  {
    if ($ratio < 0.1) return "#ff0000";
    if ($ratio < 0.2) return "#ee0000";
    if ($ratio < 0.3) return "#dd0000";
    if ($ratio < 0.4) return "#cc0000";
    if ($ratio < 0.5) return "#bb0000";
    if ($ratio < 0.6) return "#aa0000";
    if ($ratio < 0.7) return "#990000";
    if ($ratio < 0.8) return "#880000";
    if ($ratio < 0.9) return "#770000";
    if ($ratio < 1) return "#660000";
    return "#000000";
  }

function get_slr_color($ratio)
  {
    if ($ratio < 0.025) return "#ff0000";
    if ($ratio < 0.05) return "#ee0000";
    if ($ratio < 0.075) return "#dd0000";
    if ($ratio < 0.1) return "#cc0000";
    if ($ratio < 0.125) return "#bb0000";
    if ($ratio < 0.15) return "#aa0000";
    if ($ratio < 0.175) return "#990000";
    if ($ratio < 0.2) return "#880000";
    if ($ratio < 0.225) return "#770000";
    if ($ratio < 0.25) return "#660000";
    if ($ratio < 0.275) return "#550000";
    if ($ratio < 0.3) return "#440000";
    if ($ratio < 0.325) return "#330000";
    if ($ratio < 0.35) return "#220000";
    if ($ratio < 0.375) return "#110000";
    return "#000000";
  }


function get_user_class()
{
    global $CURUSER;
    return $CURUSER["class"];
}

function get_user_class_name($class)
{
  switch ($class)
  {
    case UC_BANNED: return "Banned";
    case UC_USER: return "User";
    case UC_POWER_USER: return "Power User";
    case UC_UPLOADER: return "Uploader";
    case UC_VIP: return "VIP";
    case UC_FORUM_MOD: return "Forum Moderator";
    case UC_MODERATOR: return "Moderator";
    case UC_ADMINISTRATOR: return "Administrator";
    case UC_SYSOP: return "SysOp";
    case UC_STAFF_LEADER: return "Owner";
  }
  return "";
}

function get_user_class_color($class)
{
    switch ($class)
    {        
        case UC_BANNED: return"999999";
        case UC_USER: return "9C2FE0";
        case UC_POWER_USER: return "F7A919";
        case UC_UPLOADER: return "#13D1D1";
        case UC_VIP: return "009F00";
        case UC_FORUM_MOD: return "F5886D";
        case UC_MODERATOR: return "FE2E2E";
        case UC_ADMINISTRATOR: return "9E159E";
        case UC_SYSOP: return "4080B0";
        case UC_STAFF_LEADER: return "990000";
    }
    return "";
}
function member_ratio($up, $down) {
    switch(true) {
        case ($down > 0 && $up > 0): 
        $ratio = '<span style="color:'.get_ratio_color($up/$down).';">'.number_format($up/$down, 3).'</span>';
        break;
        case ($down > 0 && $up == 0): 
        $ratio = '<span style="color:'.get_ratio_color(1/$down).';">'.number_format(1/$down, 3).'</span>';
        break;
        case ($down == 0 && $up > 0): 
        $ratio=  '<span style="color: '.get_ratio_color($up/1).';">inf</span>';
        break;
       default:
       $ratio = '---';
   }
return $ratio;
}
function where ($scriptname, $userid) {
	global $where;
        if (!is_valid_id($userid))
            die;
		if ($scriptname)
			$where = $scriptname;
		else
			$where = "Unknown Location...";
		$query = sprintf("UPDATE users SET page=".sqlesc($where)." WHERE id ='%s'",
		mysql_real_escape_string($userid));
		$result = mysql_query($query);
		if (!$result)
			sqlerr(__FILE__,__LINE__);
		else
			return $where;
}
function format_user($user) {
    global $TBDEV;
    return '<a href="'.$TBDEV['baseurl'].'/userdetails.php?id='.$user['id'].'" title="'.get_user_class_name($user['class']).'">
            <span style="color:'.get_user_class_color($user['class']).';">'.$user['username'].'</span>
            </a>'.get_user_icons($user).' '; 
}

function get_server_load($windows = 0) {
    if(class_exists("COM")) {
        $wmi = new COM("WinMgmts:\\\\.");
        $cpus = $wmi->InstancesOf("Win32_Processor"); 
        $i = 1;
        // Use the while loop on PHP 4 and foreach on PHP 5
        //while ($cpu = $cpus->Next()) {
        foreach($cpus as $cpu) {
            $cpu_stats=0;
            $cpu_stats += $cpu->LoadPercentage;
            $i++;
        }
        return round($cpu_stats/2); // remove /2 for single processor systems
    }
}

function is_valid_user_class($class){
  return is_numeric($class) && floor($class) == $class && $class >= UC_USER && $class <= UC_SYSOP;
}

function is_valid_id($id){
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

?>