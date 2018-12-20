<script>
	 
jQuery(".login-trigger").click(function(){
    //alert("The paragraph was clicked.");
	jQuery('body').addClass('login_popup');
});
jQuery(".close").click(function(){
    //alert("The paragraph was clicked.");
	jQuery('body').removeClass('login_popup');
});
</script>
<script>
	
jQuery(document).bind('DOMNodeInserted DOMNodeRemoved', function(e) {
    var element = e.target;
    setTimeout(function() {
		if (e.type == 'DOMNodeInserted') {
			 if( jQuery('.ps-toolbar-zooom').length == 0){
        jQuery('div.ps-carousel').append('<div class="ps-toolbar-zooom">Double tab to zoom</div>');
        }
		else{
		//	alert('Content removed! Current content:');
		}
		}
    }, 1000);
});

jQuery(document).on('DOMNodeInserted DOMNodeRemoved', '.ps-zoom-pan-rotate',function(e) {
  
    var element = e.target;
   
    setTimeout(function() {
		if (e.type == 'DOMNodeInserted') {
			//alert('sdss');
		    if( jQuery('.ps-toolbar-remove').length == 0){
		    	jQuery('div.ps-zoom-pan-rotate').append('<div class="ps-toolbar-remove">Double tab to close</div>');
			}
		else{
		//	alert('Content removed! Current content:');
		}
		}
				
    }, 1000); 
  
});

</script>
