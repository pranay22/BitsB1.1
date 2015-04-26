function tabswitch(id,handle) { 
        var boxes = new Array('box1','box2'); 
        var handles = new Array('handle1','handle2'); 
        for(var i=0;i<boxes.length;i++) 
                document.getElementById(boxes[i]).style.display = 'none'; 
        for(var i=0;i<handles.length;i++) 
                document.getElementById(handles[i]).setAttribute('class','tab_handle_inactive'); 
                 
        document.getElementById(id).style.display= ''; 
        handle.setAttribute('class','tab_handle'); 
}