//=======================
//! Set some gobal vars
//=======================

var oHash = window.location.hash;

//================================
//! Awesome AJAXness starts here
//================================

 
function itemLoader(){
	
	/* Set Link for AJAX */
	hash = window.location.hash;
	
	/* Compare oHash to hash */
	if( (oHash !== hash) && (
		(oHash.indexOf('#!/set=') !== -1 && hash.indexOf('#!/set=') !== -1) ||
		(oHash.indexOf('#!/photo_id') !== -1 && hash.indexOf('#!/photo_id') !== -1) ) ){
		
		/* Just load Photo Gallery */
		target = '#gallery';
		loadingBox = false;
	} else {
	
		/* Load entire Page */
		target = '#content';
		loadingBox = true;
	}
	
	/* Update Hash */
	if(hash.indexOf('#!/') >= 0){
		link = hash.split('#!/');	
	} else {
		link = hash.split('#!'); // Make slash optional in case of bugs	
	}
	data = link[2];
	link = link[1].replace(/\#\!/g,'?');
	hash = '!/'+link;
	targetLink = '?'+link;
	
	/* Update Highlight on sidebar */
	if(link.indexOf('home') == 0) {
		sidelink = '.home';
	} else if(link.indexOf('about') == 0) {
		sidelink = '.about';
	} else if(link.indexOf('recent') == 0) {
		sidelink = '.recent';
	} else {
		sidelink = '';
	}
	$('#primarynav .active').removeClass('active');
	$('#primarynav '+sidelink).addClass('active');

	/* Fadeout Packery */
	$this = $(target);
	
	if(target == '#gallery'){
		$this.find('.packery').css({'height':'0','opacity':'0'});
	} else {
		$this.css('opacity','0');	
	}
	
	/* Add a Loading Box */
	var loadingBox = $('<div id="loading-box" />');
	loadingBox.hide();
	$(target).before(loadingBox);
	loadingBox.fadeIn('slow');	
	
	/* Add Loading Text */
	if(target == '#gallery'){
		$('#content-description').css('opacity','0');
		$('#content-description').before('<h3 id="loading-text">Loading...</h3>');
		$('#content-description').load(targetLink+' '+'#content-description'+' > *',function(){
			$('#loading-text').remove();
			$('#content-description').css('opacity','1');
		});
	}
	
	/* Create new UL */
	$this.load(targetLink+' '+target+' > *',function(){
		
		/* Fadein First */
		if(target == '#gallery'){
			$this.find('.packery').css({'height':'auto','opacity':'1'});
		} else {
			$this.css('opacity','1');	
		}
		
		/* Make sure Images are Loaded */
		imagesLoaded( $this, function() {
		
			$('.packery').packery({
				itemSelector: '.item',
				gutter: 0
			});
			resizeVideo(function(){
				$('.packery').packery('layout');
			});
			parentNavScroller();
			/* Update document Title */
			if($('h3.title')[0]){
				document.title = $('h1 a').html()+" \273 "+$('h3.title').html();	
			} else if (link){
				title = $('h1 a').html()+" \273 "+link.charAt(0).toUpperCase() + link.slice(1);
				if(title.indexOf('&') > 0){
					title = title.split('&');
					title = title[0];
				}
				document.title = title;
			} else {
				document.title = $('h1 a').html();
			}

			/* Remove LoadingBox */
			loadingBox.fadeOut('slow',function(){
				$(this).remove();
			});	
		});
		/* Finall reset Hash */
		oHash = '#'+hash;
	});
};

/* Allows Scrollers to work on Click */
function parentNavScroller(){
	
	$('.parentnav .after').on('click', function(){
		/* Find Child */
		$target = $(this).parent().find('ul');
		
		scroll = $target.scrollLeft() + 160;
		
		/* Animate Scroll */
		$target.animate({scrollLeft: scroll }, 300);
	});
	$('.parentnav .before').on('click', function(){
		/* Find Child */
		$target = $(this).parent().find('ul');
		
		scroll = $target.scrollLeft() - 160;
		
		/* Animate Scroll */
		$target.animate({scrollLeft: scroll }, 300);
	});
}

/* Video Resize */ 
function resizeVideo(callback) {
	
	/* Video fix by Chris Coyer */
	// By Chris Coyier & tweaked by Mathias Bynens
	// Find all Videos
	var $allVideos = $("embed[src^='http://www.flickr.com'], object[data^='http://www.flickr.com']"),
	
		// The element that is fluid width
		$fluidEl = $("li.video");
	
	// Figure out and save aspect ratio for each video
	$allVideos.each(function() {
	
		$(this)
			.data('aspectRatio', this.height / this.width)
			
			// and remove the hard coded width/height
			.removeAttr('height')
			.removeAttr('width');
	
	});
	
	// When the window is resized
	$(window).resize(function() {
	
		var newWidth = $fluidEl.width();
		
		// Resize all videos according to their own aspect ratio
		$allVideos.each(function() {
	
			var $el = $(this);
			$el
				.width(newWidth)
				.height(newWidth * $el.data('aspectRatio'));
	
		});

		// Kick off one resize to fix all videos on page load
	}).resize();
	
	/* Do a callback */
	if (callback && typeof(callback) === "function") {  
        callback();  
    } 
}

/* Passes info after Hash to PHP, Self Executing */
(function(hash){
	if(hash){
		itemLoader();
	}
})(window.location.hash);

/* Run on Window Load */
$(window).load(function(){
	
	/* AJAX goodness */
	$(window).on('hashchange', function(){
		itemLoader();
	});
	
	/* For Search Form */
	$("#search").submit(function(e){
		console.log(e);
		search = encodeURIComponent($(this).find('input[type = search]').val());
		window.location.hash = '!/search='+search;
	    return false;
	});
	
	/* Removes weird transitions on page load */
	$('body').removeClass('firsttime');
	
	/* Packery */
	$('.packery').packery({
		itemSelector: '.item',
		gutter: 0
	})
		
	var pckry = $('.packery').packery( 'on', 'layoutComplete', resizeVideo())
});