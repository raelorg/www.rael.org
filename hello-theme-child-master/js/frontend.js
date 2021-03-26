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

// Gediminas work starts here
// function used in Downloads page to navigate to different languages ebooks
function goToPage(_page) {
    if (_page) {
        _protocol = window.location.protocol;
        _hostname = window.location.hostname;
        if (_protocol && _hostname) {
            window.location.assign(_protocol + '//' + _hostname + _page);
        }
    }
};

// function to navigate user back to previous page from country page after clicking on X
function closeCountryPage() {
	if (document.referrer) {								// if user navigated to country from another rael.org page
		window.location.assign(document.referrer);
	} else {												// if user entered the country page directly
		var _protocol = window.location.protocol;
		var	_hostname = window.location.hostname;
		var _uri = "";

		var _path = window.location.pathname.toLowerCase();
	
		if (_path && !(_path.startsWith('/country'))) {				// if not an English site, we will take the country code
			_uri = _path.substring(0, _path.indexOf("/", 1));
		}

		if(_protocol && _hostname) {
			window.location.assign(_protocol + '//' + _hostname + _uri);
		} else {
			window.location.assign("https://www.rael.org");
		}
	}
}
// Gediminas work ends here
