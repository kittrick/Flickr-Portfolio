<!DOCTYPE html>
<html class="no-js" lang="en">
	<head>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?php echo htmlspecialchars($fgp->siteTitle()); if($fgp->pageDescription !== '') echo ' &raquo; '.htmlspecialchars($fgp->pageDescription); ?></title>
		<meta name="description" content="<?php echo htmlspecialchars(strip_tags($fgp->contentDescription())); ?>">
		<meta name="keywords" content="<?php echo htmlspecialchars($fgp->tags); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="fragment" content="!" />
		
		<!-- Stylesheets -->
		<link href="http://fonts.googleapis.com/css?family=Oswald:400,300,700" rel="stylesheet prefetch" type="text/css">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		
		<!-- Favicon-->
		<link rel="shortcut icon" href="favicon.ico">
	</head>
	<body class="firsttime">