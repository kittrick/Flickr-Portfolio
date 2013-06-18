<?php include '../classes.php'; $fgp = new FGP; ?>
<!doctype html>
<html lang="en-us">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width">
		<meta name="description" content="">
		<title></title>
		<link rel="stylesheet" href="css/style.css">
		<!--[if lt IE 9]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>
	<body>
		<p>Click <a href="<?php echo $fgp->loginURL('http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]); ?>" target="_blank">here</a> to log in to Flickr.</p>
	</body>
</html>