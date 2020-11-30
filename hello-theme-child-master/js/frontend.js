// Arun Kumar work start here
jQuery(document).ready(function () {
  if (jQuery(".event-items").length) {
	  // Modified by Juliana Gonzalez Start -- Added conditions and added rtl option to slick for RTL
	  if ( jQuery("body").hasClass('rtl') ){
		  jQuery(".event-items").slick({ autoplay: true, autoplaySpeed: 3000, rtl:true });
	  }else{
		  jQuery(".event-items").slick({ autoplay: true, autoplaySpeed: 3000 });
	  }
	  // Modified by Juliana -- End
  }
});
// Arun Kumar work end here
