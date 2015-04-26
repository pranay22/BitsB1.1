<? 
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
require_once "include/html_functions.php"; 
require_once "include/pager_functions.php"; 
 
dbconn(false); 
loggedinorreturn(); 
 
$id = 0 + $_GET["id"]; 
 
$lang = array_merge( load_language('global'), load_language('torrenttable_functions') ); 
 
 
        if (!is_valid_id($id) || $CURUSER["id"] <> $id && get_user_class() < UC_MODERATOR) 
                $id = $CURUSER["id"]; 
 
        $res = mysql_query("SELECT COUNT(*) FROM userhits WHERE hitid = $id") or die(mysql_error()); 
        $row = mysql_fetch_array($res,MYSQL_NUM); 
        $count = $row[0]; 
 
        if (!$count) 
                stderr("No views", "This user has had no profile views yet."); 
 
        $perpage = 25; 
        $pager = pager($perpage, $count, "userhits.php?id=$id&amp;"); 
 
        $res = mysql_query("SELECT username, hits FROM users WHERE id = $id") or sqlerr(); // remove 'hits' if you do NOT use the cleanup code 
        $user = mysql_fetch_assoc($res); 
 
        $HTMLOUT = ''; 
 
        // replace $user[hits] with $count if you do NOT use the cleanup code 
        $HTMLOUT .= "<h1>Profile views of <a href='userdetails.php?id=$id'>$user[username]</a></h1> 
        <h2>In total $user[hits] views</h2>"; 
 
        if (mysql_num_rows($res) == 0) 
        { 
                $HTMLOUT .= "<table border='1' cellspacing='0' cellpadding='5'><tr><td class='colheadmain'><p align='center'><b>This user has had no profile views yet.</b></p></td></tr></table>"; 
        } else { 
                $HTMLOUT .= $pager['pagertop']; 
 
        $HTMLOUT .= "<table border='0' cellspacing='0' cellpadding='5'> 
                        <tr> 
                                <td class='colhead'>#.</td> 
                                <td class='colhead'>Username</td> 
                                <td class='colhead'>Viewed</td> 
                        </tr>"; 
 
        $res = mysql_query("SELECT uh.*, username FROM userhits uh LEFT JOIN users u ON uh.userid = u.id WHERE hitid = $id ORDER BY uh.id DESC") or sqlerr(); 
        while ($arr = mysql_fetch_assoc($res)){ 
        $hittime = get_date( $arr['added'],'',0,1); 
        $hitby = $arr["username"]; 
        $HTMLOUT .= "<tr> 
                        <td>".number_format($arr["number"])."</td> 
                        <td><b><a href='userdetails.php?id=$arr[userid]'>$hitby</a></b></td> 
                        <td>$hittime</td> 
                     </tr>"; 
        } 
 
        $HTMLOUT .= "</table>"; 
        $HTMLOUT .= $pager['pagerbottom']; 
} 
//header crap goes here// 
    print stdhead("Profile views of $user[username]") . $HTMLOUT . stdfoot();  
?>