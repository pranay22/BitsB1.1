
	var maxDim = 360;
jQuery(document).ready(function($){
	
  $("img").click(function () {
      if($(this).hasClass("resized")) {
	  
         var id = $(this).attr("id"); 
         id = id.slice(1); 
		 var image_source = $("#r"+id); 
		 
		 var windowH = $(window).height(); 
		 var windowW = $(window).width(); 
		 
		 var scrolltop = $(window).scrollTop(); 
		 var scrollleft = $(window).scrollLeft(); 
		 
		 if($("#holder_" + id).hasClass("holder")) return; // Setup the Loading DIV
		 
         var loading_left = ((windowW / 2) + scrollleft) - 25; 
		 var loading_top = ((windowH / 2) + scrolltop) - 25; 
		 var loading_bg = $("<div></div>").addClass('loading_bg').css( {"margin-left" : loading_left, "margin-top" : loading_top}); 
		 var loading_icon = $("<div></div>").addClass('loading_icon'); 
			$(loading_bg).prepend(loading_icon); 
			$("body").prepend(loading_bg); 
			
			var image = new Image(); 
			
		$(image).load(function() {
            $(this).hide(); 
			$("body").append("<div id=\"holder_" + id + "\" style=\"display:none;\"></div>"); 
			$("#holder_" + id).append("<div id=\"kill_holder\" title=\"click to close\" class=\"kill_holder\" onclick=\"close_holder(" + id + ")\" ></div>"); 
			$("#holder_" + id).append("<img id=\"n"+id+"\" src=\"" + image_source.attr("src") + "\" />"); 
			$("#holder_" + id).addClass("holder"); 
			
			//find out the real  image  size
			var $timg = $("<img src=\""+image_source.attr("src")+"\" style=\"position:absolute; top:0; left:0; visibility:hidden\" />").prependTo("body");
				
				var obj_height = $("#holder_" + id).height(); 
				var img_height = $timg.height()+100; 
				var obj_width = $("#holder_" + id).width(); 
				var img_width = $timg.width()+100;
					
					if(img_height > windowH )
						var holderTop = (img_height/2) + scrolltop ;
					else
						var holderTop = ((windowH / 2) + scrolltop); 
					if(img_width > windowW )
						var holderLeft = (img_width/2)  + scrollleft; 
					else
						var holderLeft = ((windowW / 2) + scrollleft); 	
						
				//remove temp img	
				$timg.remove();
					
				$("#holder_" + id).css("top", (holderTop - (obj_height / 2)) + "px"); 
				$("#holder_" + id).css("left", (holderLeft - (obj_width / 2)) + "px"); 
				$("#kill_holder").css("left", obj_width - 11); 
				$("#holder_" + id).before($("<div></div>").addClass("overlay").attr("id", "overlay").css("opacity", 0.6)); 
					
					if(img_height > $(document).height())
						$("div.overlay").height(scrolltop+img_height+"px");
					else 
						$("div.overlay").height($(document).height());
					if(img_width > $(document).width())
						$("div.overlay").width(scrollleft+img_width+"px");
					else 
						$("div.overlay").width($(document).width());
			$("div.holder").fadeIn("slow", function() {
               $("div.loading_bg").hide(10,function () { $(this).remove}); }
            ); }
         ).attr("src", image_source.attr("src")); }
      }); 
   
   });
function close_holder(id) {
   $("#holder_" + id).fadeOut(100, function() {
      $("#holder_" + id).remove(); $("#overlay").remove(); $("div.loading_bg").remove(); }
   );
   }
$(document).keyup(function (e) {
   if(e.keyCode == 27) {
   var id = $("div.holder:first").attr("id");
      $("#" + id).fadeOut(100, function () {
         $("#" + id).remove(); $("#overlay").remove();$("div.loading_bg").remove(); }
      ); }
   }
); 