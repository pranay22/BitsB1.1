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

if (!defined('BITSB_VERSION')) exit('No direct script access allowed');

/** freeleech/doubleseed slots mod by pdq for TBDev.net 2009**/
$slot = isset($_GET['slot']) ? $_GET['slot'] : '';
if ($slot)
{ 

    if ($CURUSER['freeslots'] < 1)
        stderr('USER ERROR', 'No freeleech slots available.');

    $slot_options = array('free' => 1, 'double' => 2);
    if (!isset($slot_options[$slot]))
        stderr('Error', 'Invalid Command!');

    switch ($slot)
    {
        case 'free':
            $value_3 = 'double';
            break;

        case 'double':
            $value_3 = 'free';
            break;
    }

    $added = (TIME_NOW + 14*86400);
    $r = mysql_query("SELECT * FROM `freeslots` WHERE tid = ".sqlesc($id)." AND uid = {$CURUSER['id']}");
    $a = mysql_fetch_assoc($r);

    if ($a['tid'] == $id && $a['uid'] == $CURUSER['id'] && (($a['free'] != 0 && $slot === 'free') || ($a['double'] != 0 && $slot === 'double')))
        stderr('Doh!', ($slot != 'free' ? 'Doubleseed' : 'Freeleech').' slot already in use.');

    mysql_query("UPDATE users SET freeslots = ($CURUSER[freeslots]-1) WHERE id = $CURUSER[id] && $CURUSER[freeslots]>=1") or sqlerr(__file__, __line__);

   if ($a['tid'] == $id && $a['uid'] == $CURUSER['id'] && ($a['free'] != 0 || $a['double'] != 0))
        mysql_query("UPDATE `freeslots` SET `".$slot."` = $added  WHERE `tid` = ".sqlesc($id)." AND `uid` = $CURUSER[id] AND `".$value_3."` != 0") or sqlerr(__file__, __line__);
    else
       mysql_query("INSERT INTO `freeslots` (`tid`, `uid`, `".$slot."`) VALUES (".sqlesc($id).", {$CURUSER['id']}, $added)") or sqlerr(__file__, __line__);
 //     mysql_query("INSERT INTO `freeslots` (`tid`, `uid`, `".$slot."`) VALUES (".sqlesc($id).", {$CURUSER['id']}, $added) ON DUPLICATE KEY UPDATE `".$slot."` = $added") or sqlerr(__file__, __line__);      
}
?>