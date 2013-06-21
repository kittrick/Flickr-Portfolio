<?php

/* Error Reporting */
	ini_set("display_errors", 1);
	error_reporting(E_ALL);

/*----------------------------------------
# 
# FLICKR PORTFOLIO BY KIT MACALLISTER
# 
----------------------------------------*/

?>
<?php include 'classes.php'; ?>
<?php
	/* This is for the Google Crawler */
	$fgp->fragmentCheck();
	
	/* Load content into $output var */
	$page = $fgp->loadContent();
?>
<?php include 'head.php'; ?>
<div id="container">
	<header>
		<section class="hgroup">
			<h1><a href="/"><? echo $fgp->siteTitle(); ?></a></h1>
			<h3><? echo $fgp->siteDescription(); ?></h3>
		</section>
		<form id="search" action="index.php" method="get" accept-charset="utf-8">
			<input type="search" name="search" />
			<input type="submit" value="search" />
		</form>
		<nav id="primarynav">
			<ul>
				<li class="home <?php $fgp->activeLink('home'); ?>"><a class="sidelink" href="#!/home">Projects</a></li>
				<li class="about <?php $fgp->activeLink('about'); ?>"><a class="sidelink" href="#!/about">About</a></li>
				<li class="recent <?php $fgp->activeLink('recent'); ?>"><a class="sidelink" href="#!/recent&amp;page=1">Recent</a></li>
			</ul>
		</nav>
	</header>
	<div id="content">
		<?
		/* Print page content */
		echo $page;
		?>
	</div>
</div>
<?php include 'foot.php'; ?>
