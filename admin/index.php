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

if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
}

require_once "include/html_functions.php";
require_once "include/user_functions.php";
staffonly();

    $lang = array_merge( $lang, load_language('ad_index') );

    $HTMLOUT = '';

    $HTMLOUT .= "<br />

    <br />
		<table width='75%' cellpadding='10px'>
		<tr><td class='colhead' title='Site administration tools'>Site administration tools</td></tr>
		<!-- row 1 -->
		<tr><td>
		
			
			<span class='btn'><a href='admin.php?action=bans'><font color=#FFFFFF>{$lang['index_bans']}</font></a></span>
			<span class='btn'><a href='admin.php?action=adduser'><font color=#FFFFFF>{$lang['index_new_user']}</font></a></span>
			<span class='btn'><a href='users.php'><font color=#FFFFFF>{$lang['index_user_list']}</font></a></span>
			<span class='btn'><a href='admin.php?action=latest'><font color=#FFFFFF>{$lang['index_latest']}</font></a></span>
            <span class='btn'><a href='admin.php?action=changename'><font color=#FFFFFF>{$lang['index_changen']}</font></a></span>
            <span class='btn'><a href='admin.php?action=changemail'><font color=#FFFFFF>{$lang['index_changem']}</font></a></span>
            <span class='btn'><a href='admin.php?action=reset'><font color=#FFFFFF>{$lang['index_reset_password']}</font></a></span>
            <span class='btn'><a href='admin.php?action=reports'><font color=#FFFFFF>{$lang['index_reports']}</font></a></span>
            
			</td></tr>
			<!-- row 2 -->
			<tr><td>
			
			<span class='btn'><a href='tags.php'><font color=#FFFFFF>{$lang['index_tags']}</font></a></span>
			<span class='btn'><a href='smilies.php'><font color=#FFFFFF>{$lang['index_emoticons']}</font></a></span>
			<span class='btn'><a href='admin.php?action=delacct'><font color=#FFFFFF>{$lang['index_delacct']}</font></a></span>
			<span class='btn'><a href='admin.php?action=stats'><font color=#FFFFFF>{$lang['index_stats']}</font></a></span>
            <span class='btn'><a href='admin.php?action=stats_extra'><font color=#FFFFFF>{$lang['index_stats_extra']}</font></a></span>
			<span class='btn'><a href='admin.php?action=pmview'><font color=#FFFFFF>Message Spy</font></a></span>
            <span class='btn'><a href='admin.php?action=grouppm'><font color=#FFFFFF>{$lang['index_grouppm']}</font></a></span>
			<span class='btn'><a href='admin.php?action=shistory'><font color=#FFFFFF>{$lang['index_shout_history']}</font></a></span>
			<span class='btn'><a href='admin.php?action=floodlimit'><font color=#FFFFFF>{$lang['index_floodlimit']}</font></a></span>
            
			</td></tr>
			<!-- roow 3 -->
			<tr><td>
			
			<span class='btn'><a href='ipcheck.php'><font color=#FFFFFF>{$lang['index_ip_check']}</font></a></span>
            <span class='btn'><a href='admin.php?action=testip'><font color=#FFFFFF>{$lang['index_testip']}</font></a></span>
            <span class='btn'><a href='admin.php?action=whois'><font color=#FFFFFF>{$lang['index_whois']}</font></a></span>
			<span class='btn'><a href='failedlogins.php'><font color=#FFFFFF>{$lang['index_failed_logins']}</font></a></span>
            <span class='btn'><a href='admin.php?action=bannedemails'><font color=#FFFFFF>{$lang['index_bannedemails']}</font></a></span>
            <span class='btn'><a href='admin.php?action=nameblacklist'><font color=#FFFFFF>{$lang['index_blacklist']}</font></a></span>
			<span class='btn'><a href='admin.php?action=docleanup'><font color=#FFFFFF>{$lang['index_mcleanup']}</font></a></span>
			
			</td></tr>
			<!-- row 4 -->
			<tr><td>
			
            <span class='btn'><a href='admin.php?action=forummanager'><font color=#FFFFFF>{$lang['index_forummanage']}</font></a></span>
            <span class='btn'><a href='admin.php?action=moforums'><font color=#FFFFFF>{$lang['index_moforums']}</font></a></span>
            <span class='btn'><a href='admin.php?action=msubforums'><font color=#FFFFFF>{$lang['index_msubforums']}</font></a></span>
            <span class='btn'><a href='admin.php?action=load'><font color=#FFFFFF>{$lang['index_serverload']}</font></a></span>
            <span class='btn'><a href='admin.php?action=system_view'><font color=#FFFFFF>{$lang['index_sys_oview']}</font></a></span>
	       <span class='btn'><a href='admin.php?action=detectclients'><font color=#FFFFFF>{$lang['index_detect_clients']}</font></a></span>
           <span class='btn'><a href='client_clearban.php'><font color=#FFFFFF>{$lang['index_banned_clients']}</font></a></span>
			
			</td></tr>
			<!-- row 5 -->
			<tr><td>
            
            <span class='btn'><a href='admin.php?action=categories'><font color=#FFFFFF>{$lang['index_categories']}</font></a></span>
			<span class='btn'><a href='admin.php?action=snatched_torrents'><font color=#FFFFFF>Snatched overview</font></a></span>
            <span class='btn'><a href='admin.php?action=comment_overview'><font color=#FFFFFF>Comment overview</font></a></span>
            <span class='btn'><a href='admin.php?action=datareset'><font color=#FFFFFF>{$lang['index_datareset']}</font></a></span>
            <span class='btn'><a href='promo.php'><font color=#FFFFFF>{$lang['index_promo']}</font></a></span>
            <span class='btn'><a href='admin.php?action=parked'><font color=#FFFFFF>{$lang['index_parked_accounts']}</font></a></span>
			
			</td></tr>
			<!-- row 6 -->
			<tr><td>
			
			<span class='btn'><a href='reputation_ad.php'><font color=#FFFFFF>{$lang['index_rep_system']}</font></a></span>
			<span class='btn'><a href='reputation_settings.php'><font color=#FFFFFF>{$lang['index_rep_settings']}</font></a></span>
			<span class='btn'><a href='admin.php?action=news'><font color=#FFFFFF>{$lang['index_news']}</font></a></span>
            <span class='btn'><a href='admin.php?action=snews&do=add'><font color=#FFFFFF>{$lang['index_snews']}</font></a></span>
            <span class='btn'><a href='admin.php?action=snews2'><font color=#FFFFFF>{$lang['index_snews2']}</font></a></span>
			<span class='btn'><a href='admin.php?action=log'><font color=#FFFFFF>{$lang['index_log']}</font></a></span>
            <span class='btn'><a href='admin.php?action=sysoplog'><font color=#FFFFFF>{$lang['index_slog']}</font></a></span>
            <span class='btn'><a href='notepad.php'><font color=#FFFFFF>{$lang['index_notepad']}</font></a></span>
            

            </td></tr>
			<!-- row 8 -->
			<tr><td>
            <span class='btn'><a href='credits.php'><font color=#FFFFFF>{$lang['index_credits']}</font></a></span>
            <span class='btn'><a href='admin.php?action=editlog'><font color=#FFFFFF>{$lang['index_coder_editlog']}</font></a></span>
			<span class='btn'><a href='admin.php?action=mysql_overview'><font color=#FFFFFF>{$lang['index_mysql_overview']}</font></a></span>
			<span class='btn'><a href='admin.php?action=mysql_stats'><font color=#FFFFFF>{$lang['index_mysql_stats']}</font></a></span>
                        
            </td></tr>
			<!-- row 7 -->
			<tr><td>
			
            <span class='btn'><a href='admin.php?action=slotmanage'><font color=#FFFFFF>{$lang['index_slotmanage']}</font></a></span>
            <span class='btn'><a href='admin.php?action=freeleech'><font color=#FFFFFF>Freeleech</font></a></span>
            <span class='btn'><a href='admin.php?action=freeusers'><font color=#FFFFFF>Freeleech Users</font></a></span>
            <span class='btn'><a href='admin.php?action=massbonus'><font color=#FFFFFF>Manage bonus</font></a></span>
            <span class='btn'><a href='admin.php?action=bonusmanage'><font color=#FFFFFF>{$lang['index_bonus_manage']}</font></a></span>
            <span class='btn'><a href='admin.php?action=donations'><font color=#FFFFFF>{$lang['index_donations']}</font></a></span>
            </td></tr>
			<!-- row 8 -->
			<tr><td>
            
			<span class='btn'><a href='admin.php?action=usersearch'><font color=#FFFFFF>{$lang['index_user_search']}</font></a></span>
			<span class='btn'><a href='admin.php?action=uncon'><font color=#FFFFFF>{$lang['index_uncon']}</font></a></span>
			<span class='btn'><a href='admin.php?action=inactive'><font color=#FFFFFF>{$lang['index_inactive']}</font></a></span>
			<span class='btn'><a href='admin.php?action=cheaters'><font color=#FFFFFF>{$lang['index_cheats']}</font></a></span>
            <span class='btn'><a href='admin.php?action=findnotconnectable'><font color=#FFFFFF>{$lang['index_findnotconnectable']}</font></a></span>
            <span class='btn'><a href='bugs.php?action=bugs'><font color=#FFFFFF>Reported Bugs</font></a></span>
            <span class='btn'><a href='admin.php?action=massmail'><font color=#FFFFFF>{$lang['index_massmail']} </font></a></span> 
		</td></tr></table>";
 

    print stdhead("Sysop Tools") . $HTMLOUT . stdfoot();

?>