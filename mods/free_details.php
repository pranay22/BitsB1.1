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

if (!defined('BITSB_VERSION')) exit('No direct script access allowed');

/** free mod for TBDev 09 by pdq **/

$pq = $row['slotid'] == $id && $row['slotuid'] == $CURUSER['id'];
$frees = (isset($row['freeslot']) ? $row['freeslot'] : '');
$doubleup = (isset($row['doubleslot']) ? $row['doubleslot'] : '');


if ($pq && $frees != 0 && $doubleup == 0) {
	$HTMLOUT .= '<tr><td align="right" class="heading">Slots</td><td align="left">'.$freeimg.'  
	<b><font color="'.$clr.'">Freeleech Slot In Use!</font></b> 
	(only upload stats are recorded) - Expires: '.get_date($row['freeslot'], 'DATE').
	' ('.mkprettytime($row['freeslot'] - time()).' to go)</td></tr>';

$freeslot = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;or&nbsp;<b>Use:</b> 
<a class=\"index\" href=\"download.php?torrent=$id&amp;slot=double\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\">
<font color='".$clr."'><b>Doubleseed Slot</b></font></a>
&nbsp;- ".(int)$CURUSER['freeslots']." Slots Remaining. " : "");
}
elseif ($pq && $frees == 0 && $doubleup != 0){
	$HTMLOUT .= '<tr><td align="right" class="heading">Slots</td><td align="left">'.$freeimg.'  
	<b><font color="'.$clr.'">Doubleseed Slot In Use!</font></b> 
	(only upload stats are recorded) - Expires: '.get_date($row['doubleslot'], 'DATE').
	' ('.mkprettytime($row['doubleslot'] - time()).' to go)</td></tr>';
	
$freeslot = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;or&nbsp;<b>Use:</b> 
<a class=\"index\" href=\"download.php?torrent=$id&amp;slot=free\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\">
<font color='".$clr."'><b>Freeleech Slot</b></font></a>
&nbsp;- ".(int)$CURUSER['freeslots']." Slots Remaining. " : "");
} 
elseif ($pq && $doubleup != 0 && $frees != 0){
	$HTMLOUT .= '<tr><td align="right" class="heading">Slots</td><td align="left">'.$freeimg.'
	 '.$doubleimg.'  <b><font color="'.$clr.'">Freeleech and Doubleseed Slots In Use!</font></b>
	  (upload stats x2 and no download stats are recorded)
	  <p>Freeleech Expires: '.get_date($row['freeslot'], 'DATE').
	' ('.mkprettytime($row['freeslot'] - time()).' to go) and Doubleseed Expires: '.get_date($row['doubleslot'], 'DATE').
	' ('.mkprettytime($row['doubleslot'] - time()).' to go)</p>
	  </td></tr>';
$freeslot = '';
}
else 
$freeslot = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;or&nbsp;<b>Use:</b> 
<a class=\"index\" href=\"download.php?torrent=$id&amp;slot=free\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\">
<font color='".$clr."'><b>Freeleech Slot</b></font></a>&nbsp;or&nbsp;<b>Use:</b> 
<a class=\"index\" href=\"download.php?torrent=$id&amp;slot=double\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\">
<font color='".$clr."'><b>Doubleseed Slot</b></font></a>&nbsp;- ".(int)$CURUSER['freeslots']." Slots Remaining. " : '');

/** free addon start **/
$is =  '';
$fl = '';

if (isset($free))
{
  foreach ($free as $fl)
    {
        switch ($fl['modifier'])
        {
            case 1:
                $mode = 'All Torrents Free';
                break;

            case 2:
                $mode = 'All Double Upload';
                break;

            case 3:
                $mode = 'All Torrents Free and Double Upload';
                break;

            default:
                $mode = 0;
        }
$isfree['yep'] = ($fl['modifier'] != 0) && ($fl['expires'] > TIME_NOW || $fl['expires'] == 1);
$isfree['expires'] = $fl['expires'];
}
}

	$HTMLOUT .= (($row['free'] != 0 || $CURUSER['free_switch'] != 0 || $isfree['yep']) ? 
'<tr><td align="right" class="heading">Free Status</td><td align="left">'.
($row['free'] != 0  ? $freeimg.
'<b><font color="'.$clr.'">Torrent FREE</font></b> '.($row['free'] > 1 ? 
'Expires: '.get_date($row['free'], 'DATE').' 
('.mkprettytime($row['free'] - time()).' to go)<br />':'Unlimited<br />'):''):'').

/*	$HTMLOUT .= (($row['free'] != 0 || $CURUSER['free_switch'] != 0 || $isfree['yep']) ? 
'<tr><td align="right" class="heading">Free Status</td><td align="left">'.
($row['free'] != 0  ? $freeimg.
'<b><font color="'.$clr.'">Torrent FREE</font></b> '.($row['free'] > 1 ? 
'Expires: '.get_date($row['free'], 'DATE').' 
('.mkprettytime($row['free'] - time()).' to go)<br />':''):($row['free'] == 1 ? 'Unlimited<br />':'')):'').
*/
($CURUSER['free_switch'] != 0 ? $freeimg.
'<b><font color="'.$clr.'">Personal FREE Status</font></b> '.($CURUSER['free_switch'] > 1 ? 
'Expires: '.get_date($CURUSER['free_switch'], 'DATE').' 
('.mkprettytime($CURUSER['free_switch'] - time()).' to go)<br />':'Unlimited<br />'):'').
	
($isfree['yep'] ? $freeimg.
'<b><font color="'.$clr.'">'.$mode.'</font></b> '.($isfree['expires'] != 1 ?
 'Expires: '.get_date($isfree['expires'], 'DATE').' 
 ('.mkprettytime($isfree['expires'] - time()).' to go)<br />':'Unlimited<br />'):'').'

	</td></tr>'.
	
'<tr><td align=\'right\' class=\'heading\' width=\'1%\'>Torrent</td><td align=\'left\'><a rel=\'balloon3\' class="index" href="download.php?torrent='.$id.'"><u>Download torrent</u></a> '.$freeslot.'</td></tr>';
?>