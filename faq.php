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
|   Standard FAQ system v0.6
+------------------------------------------------
**/

require_once 'include/bittorrent.php';
require_once 'include/user_functions.php';

dbconn(false);

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('faq') );
    
    $HTMLOUT = "";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>Welcome to {$TBDEV['site_name']} FAQ Section!</div>
    <div class='headbody' style='width:730px;'>{$lang['faq_welcome']}</div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;'>{$lang['faq_contents_header']}</div>
    <div class='headbody' style='width:730px;'><a href='#site'><b>{$lang['faq_siteinfo_header']}</b></a>
    {$lang['faq_siteinfo']}
    <a href='#user'><b>{$lang['faq_userinfo_header']}</b></a>
    {$lang['faq_userinfo']}
    <a href='#stats'><b>{$lang['faq_stats_header']}</b></a>
    {$lang['faq_stats']}
    <a href='#up'><b>{$lang['faq_uploading_header']}</b></a>
    {$lang['faq_uploading']}
    <a href='#dl'><b>{$lang['faq_downloading_header']}</b></a>
    {$lang['faq_downloading']}
    <a href='#dlsp'><b>{$lang['faq_improve_header']}</b></a>
    {$lang['faq_improve']}
    <a href='#prox'><b>{$lang['faq_isp_header']}</b></a>
    {$lang['faq_isp']}
    <a href='#conn'><b>{$lang['faq_connect_header']}</b></a>
    {$lang['faq_connect']}
    <a href='#other'>{$lang['faq_problem']}</a>
    </div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_siteinfo_header2']}<a name='site' id='site'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_siteinfo_body']}
    </div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_userinfo_header2']}<a name='user' id='user'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_userinfo_body']}
    {$lang['faq_promotion_header']}
    {$lang['faq_promotion_body']}<a class='altlink' href='userdetails.php?id={$CURUSER['id']}'>{$lang['faq_details_page']}</a>.
    </div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_stats_header2']}<a name='user' id='user'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_stats_body']}
    </div><br />";

    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_uploading_header2']}<a name='user' id='user'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_uploading_body']}
    </div><br />";

    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_downloading_header2']}<a name='user' id='user'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_downloading_body']}";

    if ($CURUSER)
    {
      $byratio = 0; 
      $byul = 0;
      
      // ratio as a string
      function format_ratio($up,$down, $color = True)
      {
        if ($down > 0)
        {
          $r = number_format($up / $down, 2);
          if ($color)
            $r = "<font color='".get_ratio_color($r)."'>$r</font>";
        }
        else
          if ($up > 0)
            $r = "'Inf.'";
          else
            $r = "'---'";
        return $r;
      }

      if ($CURUSER['class'] < UC_VIP)
      {
        $gigs = $CURUSER['uploaded'] / (1024*1024*1024);
        $ratio = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 0);
        if ((0 < $ratio && $ratio < 0.5) || $gigs < 5)
        {
          $wait = 48;
          if (0 < $ratio && $ratio < 0.5) $byratio = 1;
          if ($gigs < 5) $byul = 1;
        }
        elseif ((0 < $ratio && $ratio < 0.65) || $gigs < 6.5)
        {
          $wait = 24;
          if (0 < $ratio && $ratio < 0.65) $byratio = 1;
          if ($gigs < 6.5) $byul = 1;
        }
        elseif ((0 < $ratio && $ratio < 0.8) || $gigs < 8)
        {
          $wait = 12;
          if (0 < $ratio && $ratio < 0.8) $byratio = 1;
          if ($gigs < 8) $byul = 1;
        }
        elseif ((0 < $ratio && $ratio < 0.95) || $gigs < 9.5)
        {
          $wait = 6;
          if (0 < $ratio && $ratio < 0.95) $byratio = 1;
          if ($gigs < 9.5) $byul = 1;
        }
        else $wait = 0;
      }

      $HTMLOUT .= "{$lang['faq_in']}<a class='altlink' href='userdetails.php?id={$CURUSER['id']}'>{$lang['faq_your']}</a>{$lang['faq_case']}";

      if (isset($wait))
      {
        $byboth = $byratio && $byul;
          $HTMLOUT .=
            ($byboth ? "{$lang['faq_both']}" : '') .
            ($byratio ? "{$lang['faq_ratio']}" . format_ratio($CURUSER['uploaded'],$CURUSER['downloaded']) : '') .
            ($byboth ? "{$lang['faq_and']}": '') .
            ($byul ? "{$lang['faq_totalup']}" . round($gigs,2) . ' GB' : '') . ' impl' .
            ($byboth ? 'y' : 'ies') . "{$lang['faq_delay']}$wait{$lang['faq_hours']}" .
            ($byboth ? '' : " ({$lang['faq_even']}" . ($byratio ? "{$lang['faq_totup']}" . round($gigs,2) . ' GB' : "{$lang['faq_ratiois']}" . format_ratio($CURUSER['uploaded'],$CURUSER['downloaded'])) . '.)');
      }
      else
        $HTMLOUT .= "{$lang['faq_nodelay']}";
      $HTMLOUT .= "<br /><br />";
    }
    $HTMLOUT .= "{$lang['faq_downloading_body1']}";
    $HTMLOUT .= "{$lang['faq_downloading_body2']}</div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_improve_speed_title']}<a name='dlsp'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_improve_speed_body']}
    </div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_proxy_title']}<a name='prox' id='prox'></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_proxy_body']}
    {$lang['faq_proxy_body2']}
    </div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_blocked_title']}<a name='conn' id='conn'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_blocked_body']}
    <b>{$lang['faq_alt_port']}</b><a name='conn4'></a>
    {$lang['faq_alt_port_body']}
    </div><br />";
    
    $HTMLOUT .= "<div class='headline2' align='center' style='width:740px;color:#ffffff;'>{$lang['faq_problem_title']}<a name='other' id='other'></a></div>
    <div class='headbody' style='width:730px;'>{$lang['faq_problem_body']}
    </div><br />";
    
    /////////////////////// HTML OUTPUT ///////////////////////
    print stdhead('FAQ') . $HTMLOUT . stdfoot();
?>