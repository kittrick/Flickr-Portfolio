<?php

//============================================
//! Flickr Portfolio Site by Kit MacAllister
//  FGP = Flickr Generated Portfolio
//============================================

class FGP {

	/* Userdata for Flickr API */
	public $user = '64006219@N00'; // this is your flickr id ex: 64006219@N00 // Get yours Here: http://idgettr.com/
	public $api_key = '8da0a1fabcad1737a973057c1853d773'; // insert your flickr API key (or use this one);
	public $secret = '524c05a417ee4026';

	/* Used by Google Crawler */
	public $escapeFragment = '_escaped_fragment_';
	
	/* Blank vars used for Sidebar and Page Title and Description */
	public $siteDescription = '';
	public $contentDescription = '';
	public $pageDescription = '';
	public $tags = '';
	public $sidebarNav = '';
	
//=======================================================
//! These functions deal with user Authentication
//=======================================================
	
	/* Builds an OAuth login URL */
	function loginURL($returnURL){
		
		/* Start with the request token URL */
		$request_token_url = 'http://www.flickr.com/services/oauth/request_token';
		
		/* Create a hash key */
		$hashkey = $this->secret.'&';
		
		/* Build an array of OAuth values, must be in alphabetical order */
		$oauth_values = array(
			'oauth_callback' => urlencode($returnURL),
			'oauth_consumer_key' => $this->api_key,
			'oauth_nonce' => md5('FGP'.$this->user.microtime().mt_rand()),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => gmdate('U'), // Must be UTC time
			'oauth_version' => '1.0'
		);
		
		/* Create a basestring */
		$basestring = '';
		foreach($oauth_values as $key => $value) $basestring .= $key.'='.urlencode($value).'&';
		$basestring = rtrim($basestring,'&');
		
		/* Create a baseurl */
		$baseurl = 'GET&'.urlencode($request_token_url).'&'.urlencode($basestring);
		
		/* Create a signature */
		$oauth_signature = base64_encode(hash_hmac('sha1', $baseurl, $hashkey, true));
		
		/* Create a url */
		$url = $request_token_url.'?'.$basestring.'&oauth_signature='.urlencode($oauth_signature);
		return $url;
	}

//==============================================
//! AskFlickr handles CURL requests to Flickr
//==============================================
	
	/* Ask Flickr for some kind of Data */
	function askFlickr($method, $query){

		/* Build URL */
		$query = 'http://api.flickr.com/services/rest/?method=flickr.'.$method.'&api_key='.$this->api_key.'&'.$query.'&format=json';

		/* Set CURL options */
		$ch = curl_init(); // open curl session
		curl_setopt($ch, CURLOPT_URL, $query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch); // execute curl session
		curl_close($ch); // close curl session

		$data = str_replace( 'jsonFlickrApi(', '', $data );
		$data = substr( $data, 0, strlen( $data ) - 1 ); //strip out last paren

		return $object = json_decode( $data ); // returns standard class object.
	}
	
//=====================================
//! LoadContent and printContent
//  handle navigation with $_GET data
//=====================================
	
	/* Primary Navigation */
	function loadContent(){

		/* Initialize output var */
		$output = '';

		/* Set Initial Tags */
		$this->setTags();

		if(isset($_GET['photo_id']) && isset($_GET['secret'])){ // Single Photo Pages

			/* Single Photos */
			$photo_id = $_GET['photo_id'];
			$secret = $_GET['secret'];
			$output .= $this->printPhoto($photo_id,$secret);

		} elseif(isset($_GET['search'])){ // Search Page

			/* For Search & Tags */
			$search = $_GET['search'];
			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$tags = isset($_GET['tags']) ? $_GET['tags'] : false;

			/* Print Search */
			$output .= $this->printSearch($search, $page, 'search', $tags);

		} elseif(isset($_GET['recent'])){ // Recent Photos

			/* Basically the Same as the Search Function but without a Query */
			$page = isset($_GET['page']) ? $_GET['page'] : 1;
			$output .= $this->printSearch(false, $page, 'recent');

		} elseif( isset($_GET['set']) && isset($_GET['id'])) { // Load Set if Called

			/* For Sets */
			$set = $_GET['set'];
			$id  = $_GET['id'];

			/* Print Set */
			$output .= $this->printSet($id);

		} elseif( isset($_GET['about']) ){ // About Page

			/* About Page */
			$output .= $this->printUserInfo();

		} else { // Same as elseif( isset($_GET['home']

			$output .= $this->printCollection() ? $this->printCollection() : $this->printSets();
		}
		return $output;
	}
	
	/* Print Content loaded by loadContent */
	function printContent($input){
		echo $input;
	}

//=================================================
//! Additional functions either set global vars
//  or provide formatting for navigation elements
//=================================================
	
	/* Active Link Site Navigation */
	function activeLink($page){

		/* Compare $page to $_GET */
		switch($page){
		case 'home':
			if(empty($_GET) || isset($_GET['home'])){
				echo ' active';
			}
			break;
		case 'about';
			if(isset($_GET['about'])){
				echo ' active';
			}
			break;
		case 'recent';
			if(isset($_GET['recent'])){
				echo ' active';
			}
			break;
		}
	}

	/* Content description for meta tags */
	function contentDescription(){

		/* Check if description exists */
		if($this->contentDescription == ''){
			$user = $this->getUserInfo();
			$content = $user->person->description->_content;
		} else {
			$content = $this->contentDescription;
		}

		/* Only to first line break */
		if(strpos($content, "\n") !== false){
			$content = explode("\n",$content);
			$content = $content[0];
		}
		return $content;
	}
	
	/* Check for fragment */
	function fragmentCheck(){

		/* Get our URI */
		$uri = $_SERVER['REQUEST_URI'];

		/* Check to see if we're using fragments */
		if( strpos($uri, $this->escapeFragment) ){
			
			/* Remove Fragment and Redirect */
			$uri = str_replace($this->escapeFragment.'/','',$uri);
			header('Location: '.$uri);
		}
	}
	
	/* Builds search navigation */
	function pageNav($page, $lastPage, $pageType){

		/* Lets work with integers */
		$page = (int)$page;

		/* Build navigation */
		$prev = $page - 1;
		$next = $page + 1;

		/* Changes URL prefix based on Page Type */
		$prefix = $_GET[$pageType];

		/* Start HTML */
		$output = '<nav class="pagenav"><ul>';
		if($prev !== 0) $output .= '<li class="prev"><a href="#!/'.$pageType.'='.$prefix.'&amp;page='.$prev.'">Previous</a></li>';
		$output .= '<li class="current"><span class="current_page">'.$page.'</span> of <span class="last">'.$lastPage.'</span></li>';
		if($page < $lastPage)$output .= '<li class="next"><a href="#!/'.$pageType.'&amp;page='.$next.'">Next</a></li>';
		$output .= '</ul></nav>';
		
		/* Return HTML */
		return $output;
	}
	
	/* Sets Global Tags */
	function setTags($photo_id = false){

		/* Lets get some tags */
		if($photo_id){

			/* This is used on Photo Page */
			$tags = $this->getTags($photo_id);

			/* Start Output */
			$output = '';
			foreach($tags->photo->tags->tag as $tag){
				$output .= $tag->raw.', ';
			}
		} else {
			/* This is used on photo page */
			$tags = $this->getUserTags();

			/* Start Output */
			$output = '';
			foreach($tags->who->tags->tag as $tag){
				$output .= $tag->_content.', ';
			}
		}

		/* Sets global var */
		$this->tags = $output;
		return $output;
	}
	
	/* Site Description */
	function siteDescription(){
		
		/* Currently this just returns a string */
		return	$this->siteDescription;
	}
	
	/* Site Title */
	function siteTitle(){
		$user = $this->getUserInfo();

		/* Check for real name */
		if($user->person->realname->_content){
			return $user->person->realname->_content;
		} else {
			return $user->person->username->_content;
		}
	}

//=========================================
//! These functions request info from
//  flickr and turn them into PHP objects
//=========================================
	
	/* Returns a collection based on the search parameter collection */
	function getCollection($collection_id){
		
		/* Fetch collection tree from Flickr */
		$collection = $this->askFlickr('collections.getInfo','&collection_id='.$collection_id);
		
		/* Ask Flickr For User Collections */
		return $collection;
	}
	
	/* Returns a collection based on the search parameter collection */
	function getCollectionTree($search = 'website'){
		
		/* Fetch collection tree from Flickr */
		$collectionTree = $this->askFlickr('collections.getTree','&user_id='.$this->user);
		
		/* This will be false if no collection called website is found */
		$websiteCollection = false;
		
		/* Search for the specified string */
		foreach($collectionTree->collections->collection as $collection){
		
			if(strtolower($collection->title) == $search) {
				$websiteCollection = $collection;
			}
		}
		
		/* Ask Flickr For User Collections */
		return $websiteCollection;
	}
	
	/* Returns a Single Photo Object */
	function getPhoto($photo_id, $secret){

		/* Get Photo */
		$photo = $this->askFlickr('photos.getInfo','&photo_id='.$photo_id.'&secret='.$secret);

		/* Return Photo */
		return $photo;
	}
	
	/* Finds out what sets a photo is in */
	function getPhotoContext($photo_id){ // No Secret Required

		/* Get Context */
		$photoContext = $this->askFlickr('photos.getAllContexts','&photo_id='.$photo_id);

		/* Return Photo Sizes */
		return $photoContext;
	}

	/* Returns a Photoset Object */
	function getPhotoSet($photoset_id){

		/* Get Photoset */
		$photoset_object = $this->askFlickr('photosets.getPhotos','&photoset_id='.$photoset_id.'&extras=url_o,url_z,url_l,url_s,url_q,url_t,media');

		/* Return our Photoset Object */
		return $photoset_object;
	}

	/* Returns Photoset Info */
	function getPhotoSetInfo($photoset_id){

		/* Get Photoset Name and Description */
		$photoset_info = $this->askFlickr('photosets.getInfo','&photoset_id='.$photoset_id);

		/* Return Photo Info Object */
		return $photoset_info;
	}
	
	/* Returns a Size information for a Single Photo */
	function getPhotoSizes($photo_id){ // No Secret Required

		/* Get Photo Size */
		$photoSizes = $this->askFlickr('photos.getSizes','&photo_id='.$photo_id);

		/* Return Photo Sizes */
		return $photoSizes;
	}
	
	/* Search Flickr, also used for recent */
	function getSearch($search, $page, $per_page = 32 , $tag = false){

		/* Build URL */
		if($tag){
			/* Return User's Tags */
			$query = 'tags='.$tag.'&user_id='.$this->user.'&per_page='.$per_page.'&page='.$page.'&extras=title&content_type=7';
		} elseif($search){
			/* Search for tags, name and description within user's photos */
			$query = 'text='.urlencode($search).'&user_id='.$this->user.'&per_page='.$per_page.'&page='.$page.'&extras=title&content_type=7';
		} else {
			/* This is used for Recent Photos */
			$query = 'user_id='.$this->user.'&per_page='.$per_page.'&page='.$page.'&extras=title&content_type=7';
		}

		/* Return Search Query */
		$search = $this->askFlickr('photos.search',$query);
		return $search;
	}
	
	/* Get a Single Set's Info */
	function getSet($set_id){
		
		/* Get Set */
		$set = $this->askFlickr('photosets.getInfo','&photoset_id='.$set_id);
		
		/* Return Set */
		return $set;
	}
	
	/* Fetch Flickr sets for user */
	function getSets($user){

		/* Get Sets */
		$sets = $this->askFlickr('photosets.getList','&user_id='.$user.'&extras=url_o,url_z,url_l,url_q,url_t,url_n,url_s');

		/* Return Sets */
		return $sets;
	}
	
	/* Gets Tags for a Photo */
	function getTags($photo_id){

		/* Fetch user info from Flickr */
		$tags = $this->askFlickr('tags.getListPhoto','&user_id='.$this->user.'&photo_id='.$photo_id);

		/* Return Tags */
		return $tags;
	}
	
	/* Get User info */
	function getUserInfo(){

		/* Fetch user info from Flickr */
		$user = $this->askFlickr('people.getinfo','&user_id='.$this->user);

		/* Return User info */
		return $user;
	}

	/* Gets Tags for a User */
	function getUserTags(){

		/* Fetch user info from Flickr */
		$tags = $this->askFlickr('tags.getListUserPopular','&user_id='.$this->user);

		/* Return Tags */
		return $tags;
	}
	
//============================================
//! These functions typically take info from
//  the get functions and HTML format them.
//============================================
	
	/* HTML Format Collection Tree */
	function printCollection(){
		
		/* Return False if no Collection Exists */
		if($this->getCollectionTree() == false){
			return false;
		}
		
		/* set collection var */
		$collection = $this->getCollectionTree();
		
		/* Check to see if this is a collection of sets or a collection of collections */
		if(isset($collection->set)){
			
			$collection = $this->getCollection($collection->id);
			
			ob_start();
			var_dump($collection);
			$output  = ob_get_clean();
			return $output;
		
			/* set iterator */
			$i = 0;

			foreach($collection->set as $set){	
				$photoset = $this->getSet($set->id);
				$setInfo[] = $photoset->photoset;
			}
			
			/* Create an object that mimics getSets() */
			$collection_object->photosets->photoset = $setInfo;
			$output = $this->printSets($packery = true, $size = 'q', $collection_object);
		}
				
		/* Return HTML */
		return $output;
	}

	/* Blank content funciton is replaced by other functions */
	function printContentDescription($tags = false){
		$output = '<section id="content-description"><h3 class="title">'.$this->pageDescription.'</h3>';
		$output .= '<p class="description">'.$this->contentDescription.'</p>';
		if($tags) $output .= '<h4>Tags:</h4>'.$this->printTags();
		$output .= '</section>';
		$output .= $this->sidebarNav;

		/* Return HTML */
		return $output;
	}

	/* HTML formats a Single Photo */
	function printPhoto($photo_id, $secret){

		/* Gets a Photo */
		$photo = $this->getPhoto($photo_id,$secret);

		/* Redefine Content Description */
		$this->pageDescription = $photo->photo->title->_content;
		$this->contentDescription = $photo->photo->description->_content;
		$this->setTags($photo_id);

		/* Add Content Description */
		$output = $this->printContentDescription(true);

		/* Start HTML */
		$output .= '<section class="photogroup">';

		/* Check for Animated Gifs */
		if($photo->photo->originalformat == 'gif') {
			$secret = $photo->photo->originalsecret.'_o';
			$format = 'gif';
		} else {
			$secret = $photo->photo->secret.'_b';
			$format = 'jpg';
		}

		/* Videos */
		if($photo->photo->media == 'video'){
			$output .= '<div id="gallery"><ul class="video packery"><li class="video item single">'; /* This makes it flexible */
			$output .= $this->printVideo($photo_id, $secret, false);
			$output .= '<li></ul></div>';
		} else {
			/* Photos */
			$output .= '<div id="gallery"><ul class="packery"><li class="photo item single"><img src="http://static.flickr.com/'.$photo->photo->server.'/'.$photo->photo->id.'_'.$secret.'.'.$format.'" alt="'.$photo->photo->title->_content.'" /></li></ul></div>';
		}

		/* Gets that Photo's Context */
		$context = $this->getPhotoContext($photo_id);
		if(isset($context->set[0]->id)){
			$parentNav = $this->printSet($context->set[0]->id, false, $context->set[0],'q');
		} else {
			$parentNav = '';
		}

		/* Add Parent Nav */
		$output .= $parentNav;

		/* End HTML */
		$output .= '</section>';

		/* Return HTML */
		return $output;
	}

	/* HTML format Photoset */
	function printSet($photoset_id, $packery = true, $parentNav = false, $size = 'n'){

		/* Gets Photo Info */
		$info = $this->getPhotoSetInfo($photoset_id);

		/* Redefine loadContentDescription */
		$this->pageDescription = $info->photoset->title->_content;
		$this->contentDescription = $info->photoset->description->_content;

		/* Add Content Description */
		$output = $parentNav ? '' : $this->printContentDescription();

		/* Start HTML */
		if(!$parentNav) $output .= '<section class="photogroup"><div id="gallery">';

		/* Check for Packery */
		$packery ? $packery = 'packery' : $packery = '';
		$output .= '<ul class="set '.$packery.'">';

		/* Gets some photos */
		$photoSet = $this->getPhotoSet($photoset_id);

		foreach($photoSet->photoset->photo as $photo){

			/* Add Special Class to Primary */
			$primary = $photo->isprimary == '1' ? 'primary' : '';

			/* Add Video class if Video */
			$output .= '<li class="item '.$photo->media.' '.$primary.'">';
			$output .= '<a class="imagelink" href="#!/photo_id='.$photo->id.'&secret='.$photo->secret.'">';

			/* Lets get some Photos */
			if($photo->media !== 'video' || $packery == false){ // Photos

				$info = $this->getPhoto($photo->id, $photo->secret);

				/* Check for Animated Gifs */
				if($info->photo->originalformat == 'gif') {
					$secret = $info->photo->originalsecret.'_o';
					$format = 'gif';
				} else {
					$secret = $photo->secret;
					$format = 'jpg';
				}

				$sizeUrl = 'url_'.$size;
				$url = isset($photo->$sizeUrl) ? $photo->$sizeUrl : 'http://farm'.$photo->farm.'.staticflickr.com/'.$photo->server.'/'.$photo->id.'_'.$secret.'.'.$format;
				$output .= '<img src="'.$url.'" alt="'.$photo->title.'">';
			} else {

				/* Videos Baby! */
				$output .= $this->printVideo($photo->id,$photo->secret,false);
			}
			$output .= '</a>';
			$output .= '</li>';
		}
		$output .= '</ul>';
		
		if(!$parentNav) $output .= '</div>';

		/* Add Parent Nav */
		if(!$parentNav) {
			$parentNav = $this->printSets(false);
			$output .= '<section class="parentnav"><div class="before">&laquo;</div>'.$parentNav.'<div class="after">&raquo;</div></section>';
			$output .= '<nav class="pagenav"><ul>'
				.'<li class="back"><a href="#!/home">&laquo; Back to Projects</a></li>'
				.'</ul></nav>';
		} else {

			$title = isset($parentNav->title) ? $parentNav->title : 'Projects';
			$link = isset($parentNav->id) ? strtolower(urlencode($parentNav->title)).'&amp;id='.$parentNav->id : '';

			$output = '<section class="parentnav"><div class="before">&laquo;</div>'.$output.'<div class="after">&raquo;</div></section>';
			$output .= '<nav class="pagenav"><ul>'
				.'<li class="back"><a href="#!/set='.$link.'">&laquo; Back to '.$title.'</a></li>'
				.'</ul></nav>';
		}

		/* End HTML */
		if(!$parentNav) $output .= '</section>';

		/* Output HTML */
		return $output;
	}

	/* HTML format Flickr Sets */
	function printSets($packery = true, $size = 'q', $collection_object = false){ // This one has different formatting

		/* Gets sets for user or prints a collection object */
		$sets = $collection_object ? $collection_object : $this->getSets($this->user);

		/* Generate output */
		$packery ? $packery = 'packery' : $packery = '';
		$output = '<section class="sets"><ul class="photosets '.$packery.'">';
		foreach($sets->photosets->photoset as $set){
			if($set->title->_content[0] !== ' '){ // I use a Space in front of sets to make them private
				$output .= '<li class="set item"><figure>';

				/* Add Link */
				$link = '<a href="#!/set='.strtolower(urlencode($set->title->_content)).'&amp;id='.$set->id.'">';
				$output .= $link;
				$output .= '<img src="http://static.flickr.com/'.$set->server.'/'.$set->primary.'_'.$set->secret.'_'.$size.'.jpg" alt="'.$set->title->_content.'" /></a>';
				$output .= '<figcaption>'.$link.$set->title->_content.'</a></figcaption>';
				$output .= '</figure></li>';
			}
		}
		$output .= '</ul></section>';

		/* Return HTML */
		return $output;
	}

	/* HTML format User info */
	function printUserInfo(){

		/* Get User Info */
		$user = $this->getUserInfo();

		/* Check for Real Name */
		if($user->person->realname->_content){
			$name = $user->person->realname->_content;
		} else {
			$name = $user->person->username->_content;
		}
		$description = $user->person->description->_content;


		/* Add <br> tags */
		$description = str_replace("\n",'<br/>',$description);

		/* Generate output */
		$output = '<section class="person" itemscope itemtype="http://schema.org/Person">'; // Using some Microdata for Additional Searchability
		$output .= '<img src="http://farm'.$user->person->iconfarm.'.staticflickr.com/'.$user->person->iconserver.'/buddyicons/'.$user->person->nsid.'_r.jpg" class="avatar" itemprop="photo" alt="'.$user->person->realname->_content.'" />';
		$output .= '<h3 >Hi! I\'m <span class="name" itemprop="name" rel="author">'.$name.'</span></h3>';
		$output .= '<p class="description span6" itemprop="description">'.$description.'<br/><br/>View my Photos on <a href="'.$user->person->photosurl->_content.'" target="_blank" rel="autor">Flickr</a>.</p>';
		$output .= '</section>';

		/* Return HTML */
		return $output;
	}

	/* HTML format Search Results, Also used for recent */
	function printSearch($search, $page, $pageType = 'search', $tag = false){

		/* Gets Search Results */
		$searchResults = $this->getSearch($search, $page, 32, $tag);

		/* Check for Results */
		if($searchResults->photos->pages){
			/* Generate output */
			$output = '<section class="searchresults">';
			$output .= $this->pageNav($searchResults->photos->page, $searchResults->photos->pages, $pageType);
			$output .= '<ul class="search packery">';
			foreach($searchResults->photos->photo as $photo){
				$output .= '<li class="photo item">';
				$output .= '<a href="#!/photo_id='.$photo->id.'&amp;secret='.$photo->secret.'">';
				$output .= '<img src="http://static.flickr.com/'.$photo->server.'/'.$photo->id.'_'.$photo->secret.'.jpg" alt="'.$photo->title.'" />';
				$output .= '</a></li>';
			}
			$output .= '</ul>';
			$output .= $this->pageNav($searchResults->photos->page, $searchResults->photos->pages, $pageType);
			$output .= '</section>';
		} else {
			$output = '<section class="searchresults">';
			$output .= '<h3>No matching photos found.</h3>';
			$output .= '</section>';
		}

		/* Return HTML */
		return $output;
	}

	/* HTML Formats Tags */
	function printTags(){

		/* Start HTML */
		$output = '<ul class="taglist">';
		$tags = explode(',',$this->tags);
		foreach($tags as $tag){
			$output .= '<li class="tag"><a href="#!/search&tags='.urlencode(strtolower(str_replace(' ','',$tag))).'">'.$tag.'</a></li>';
		}
		$output .= '</ul>';

		/* Return HTML */
		return $output;
	}
	
	/* Return simple user info without photo */
	function printUserDescription(){

		/* Get User Info */
		$user = $this->getUserInfo();

		/* Generate output */
		$output = $user->person->description->_content;

		/* Return HTML */
		return $output;
	}
	
	/* HTML formats Videos */
	function printVideo($photo_id, $secret){

		$video = $this->getPhotoSizes($photo_id);
		$output = '<object style="max-width: 100% important;" type="application/x-shockwave-flash" width="'.$video->sizes->size[9]->width.'" height="'.$video->sizes->size[9]->height.'" data="'.$video->sizes->size[9]->source.'"  classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
		$output .= '<param name="flashvars" value="intl_lang=en-us&amp;photo_secret='.$secret.'&amp;photo_id='.$photo_id.'&amp;flickr_show_info_box=false"></param>';
		$output .= '<param name="movie" value="'.$video->sizes->size[9]->source.'"></param>';
		$output .= '<param name="bgcolor" value="#000000"></param>';
		$output .= '<param name="allowFullScreen" value="true"></param>';
		$output .= '<embed type="application/x-shockwave-flash" src="'.$video->sizes->size[9]->source.'" bgcolor="#000000" allowfullscreen="true" flashvars="intl_lang=en-use&amp;photo_secret='.$secret.'&amp;photo_id='.$photo_id.'&amp;flickr_show_info_box=false" height="'.$video->sizes->size[9]->height.'" width="'.$video->sizes->size[9]->width.'"></embed>';
		$output .= '</object>';

		/* Return HTML */
		return $output;
	}
}

//========================================
//! Initialize FGP class and away we go!
//========================================

$fgp = new FGP;

?>