function enabledel(){
document.deluser.submit.disabled=document.deluser.submit.checked;
alert ("You can now delete this users account, Are you completely sure.");
}

function disabledel(){
document.deluser.submit.disabled=!document.deluser.submit.checked;
}