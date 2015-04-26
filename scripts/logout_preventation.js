/**
Accidental logout prevention
by d6bmg
**/
function log_out() 
{ 
        ht = document.getElementsByTagName("html"); 
        ht[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)"; 
        if (confirm(l_logout)) 
        { 
                return true; 
        } 
        else 
        { 
                ht[0].style.filter = ""; 
                return false; 
        } 
} 
var l_logout="Are you sure, you want to logout?"; 
