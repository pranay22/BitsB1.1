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

require_once "include/user_functions.php";
staffonly();

if ($CURUSER['class'] < UC_SYSOP)
    stderr('Permission Denied', '...');

$HTMLOUT = '';

$class = (isset($_POST['class']) ? $_POST['class'] : '');
$give = (isset($_POST['give']) ? $_POST['give'] : '');
if ($give)
{

    $slot_options = array('>= 0' => 1, '= 0' => 2, '= 1' => 3, '= 2"' => 4, '>= 3' =>
        5);
    if (!isset($slot_options[$class]))
        stderr('Error', 'Invalid Class!');

    switch ($give)
    {
        case 'Give 1 Slot':
            $amt = 1;
            break;

        case 'Give 2 Slots':
            $amt = 2;
            break;

        case 'Give 3 Slots':
            $amt = 3;
            break;

        case 'Give 5 Slots':
            $amt = 5;
            break;

        case 'Give 10 Slots':
            $amt = 10;
            break;

        case 'Reset Slots to Zero':
            $amt = 0;
            break;

        default:
    }

    if ($amt != 0)
        $res = sql_query("UPDATE users SET freeslots = freeslots + $amt WHERE class $class");
    else
        $res = sql_query("UPDATE users SET freeslots = 0 WHERE class $class");
}



$HTMLOUT .= '<form method="post" action="admin.php?action=freeslots">
	<h1>Give Slots By Class</h1>
	<table>
	<tr>
		<td class="rowhead" colspan="3" align="left">
			All Members
		</td>
		<td class="rowhead">
			<input name="class" type="radio" value=">= 0" checked="checked" />
		</td>
	</tr>
	<tr>
		<td class="rowhead" colspan="3" align="left">
			All Users
		</td>
		<td class="rowhead">
			<input name="class" type="radio" value="= 0" />
		</td>
	</tr>
	<tr>
		<td class="rowhead" colspan="3" align="left">
			All Power Users
		</td>
		<td class="rowhead">
			<input name="class" type="radio" value="= 1" />
		</td>
	</td>
</tr>
<tr>
	<td class="rowhead" colspan="3" align="left">
		All VIP
	</td>
	<td class="rowhead">
		<input name="class" type="radio" value="= 2" />
	</td>
</tr>
<tr>
	<td class="rowhead" colspan="3" align="left">
		All Staff
	</td>
	<td class="rowhead">
		<input name="class" type="radio" value=">= 3" />
	</td>
</tr>
</table>
<table>
<p>
	<input type="submit" name="give" value="Give 1 Slot" />
	<input type="submit" name="give" value="Give 2 Slots" />
	<input type="submit" name="give" value="Give 3 Slots" />
	<input type="submit" name="give" value="Give 5 Slots" />
	<input type="submit" name="give" value="Give 10 Slots" />
</p>
<p>
	<input type="submit" name="0" value="Reset Slots to Zero" onclick="return confirm(\'Are you sure you want to reset this Class" slots to zero?\')" />
</p>
</table>
<br />
</form>';


    echo stdhead('Freeleech Doubleseed Slots Manager') . $HTMLOUT . stdfoot();
    die;
?>