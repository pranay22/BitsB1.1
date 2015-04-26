<?php 
/**
+------------------------------------------------
|   BitsB PHP based BitTorrent Tracker
|   =============================================
|   by d6bmg
|   Copyright (C) 2010-2011 BitsB v1.1
|   =============================================
|   svn: http:// coming soon.. :)
|   Licence Info: GPL
|   SQL driven bonus manager v0.8
+------------------------------------------------
**/

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
require_once("include/html_functions.php"); 
 
$lang = array_merge( $lang, load_language('bonusmanager')); 
 
$HTMLOUT=""; 
  
if($CURUSER['class'] < UC_STAFF_LEADER){ 
stderr($lang['bonusmanager_wtf'], "{$lang['bonusmanager_ysbh']}"); 
die(); 
} 
         
          $res = sql_query("SELECT * FROM bonus") or sqlerr(__FILE__, __LINE__); 
          if(isset($_POST["id"]) || isset($_POST["points"]) || isset($_POST["description"]) || isset($_POST["enabled"])){ 
                $id = 0 + $_POST["id"]; 
                $points = 0 + $_POST["bonuspoints"]; 
                $descr = htmlspecialchars($_POST["description"]); 
                $enabled = "yes"; 
                if(isset($_POST["enabled"]) == ''){ 
                $enabled = "no"; 
                } 
                 
                $sql = "UPDATE bonus SET points = '$points', enabled = '$enabled', description = '$descr' WHERE id = '$id'"; 
          switch($id){ 
                case 1: 
                        makeithappen($sql); 
                break; 
                case 2: 
                        makeithappen($sql); 
                break; 
                case 3: 
                        makeithappen($sql); 
                break;   
                case 4: 
                        makeithappen($sql); 
                break; 
                case 5: 
                        makeithappen($sql); 
                break; 
                case 6: 
                        makeithappen($sql); 
                break;           
                case 7: 
                        makeithappen($sql); 
                break;                   
                case 8: 
                        makeithappen($sql); 
                break; 
                case 9: 
                        makeithappen($sql); 
                break; 
                case 10: 
                        makeithappen($sql); 
                break; 
                /* 
                case 11: 
                        makeithappen($sql); 
                break;   
                case 12: 
                        makeithappen($sql); 
                break;   
                case 13: 
                        makeithappen($sql); 
                break; 
          case 14: 
                        makeithappen($sql); 
                break;   
                case 15: 
                        makeithappen($sql); 
                break;   
                case 16: 
                        makeithappen($sql); 
                break; 
                */ 
        } 
        } 
             $HTMLOUT .="
          <div class='headline' style='width:90%;' title='{$lang['bonusmanager_bm']}'> <span>{$lang['bonusmanager_bm']}</span></div> 
          <div class='headbody' style='width:89%;'><table width='100%' border='0' cellpadding='8'> 
          <tr> 
                <td class='subheader'><b title='{$lang['bonusmanager_id']}'>{$lang['bonusmanager_id']}</b></td> 
                <td class='subheader'><b title='{$lang['bonusmanager_enabled']}'>{$lang['bonusmanager_enabled']}</b></td> 
                <td class='subheader'><b title='{$lang['bonusmanager_bonus']}'>{$lang['bonusmanager_bonus']}</b></td> 
                <td class='subheader'><b title='{$lang['bonusmanager_points']}'>{$lang['bonusmanager_points']}</b></td> 
                <td class='subheader'><b title='{$lang['bonusmanager_description']}'>{$lang['bonusmanager_description']}</b></td> 
                <td class='subheader'><b title='{$lang['bonusmanager_type']}'>{$lang['bonusmanager_type']}</b></td> 
                <td class='subheader'><b title='{$lang['bonusmanager_quantity']}'>{$lang['bonusmanager_quantity']}</b></td> 
                <td class='subheader'><b title='{$lang['bonusmanager_action']}'>{$lang['bonusmanager_action']}</b></td></tr>";
while($arr = mysql_fetch_assoc($res)) { 
  $HTMLOUT .="
          <tr><td><form name='bonusmanage' method='post' action='admin.php?action=bonusmanage'>  
                <input name='id' type='hidden' value='" . $arr["id"] ."' />$arr[id]</td> 
                <td><input name='enabled' type='checkbox' ".($arr["enabled"] == "yes" ? " checked='checked'" : ""). " /></td> 
                <td>$arr[bonusname]</td> 
                <td><input type='text' name='bonuspoints' value='" . $arr["points"] ."' size='4' /></td> 
                <td><textarea name='description' rows='4' cols='60'>" . $arr["description"] . "</textarea></td> 
                <td>$arr[art]</td> 
                <td>". (($arr["art"] == "traffic" || $arr["art"] == "gift_1" || $arr["art"] == "gift_2") ? ($arr["menge"] / 1024 / 1024 / 1024) . " GB" : $arr["menge"]) ."</td> 
                <td align='center'><input type='submit' value='{$lang['bonusmanager_submit']}' /></td> 
                </tr></form>"; 
                } 
                $HTMLOUT .="</table></div>";                
                 
  function makeithappen($sql){ 
  global $TBDEV; 
        $done = sql_query($sql) or sqlerr(__FILE__, __LINE__); 
        if($done){ 
        header("Location: {$TBDEV['baseurl']}/admin.php?action=bonusmanage"); 
        } else { 
        stderr($lang['bonusmanager_oops'], "{$lang['bonusmanager_sql']}"); 
        } 
  } 
print stdhead('Bonus Manager') . $HTMLOUT . stdfoot(); 
?>