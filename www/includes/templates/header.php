<?php

//Set page starter variables//
$cookieValid	= false;
$activeMiners = false;

require("includes/requiredFunctions.php");
require("includes/universalChecklogin.php");

if (!isset($pageTitle)) $pageTitle = outputPageTitle();
else $pageTitle = outputPageTitle(). " ". $pageTitle;

?>
<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo $pageTitle;?></title>

        <meta http-equiv="X-UA-Compatible" content="IE=7" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
	<link rel="stylesheet" href="css/mainstyle.css" type="text/css" />
	<script type="text/javascript" src="js/jquery-1.9.0.min.js"></script>
	<script type="text/javascript" src="js/jquery.tools.min.js"></script>

        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.img.preload.js"></script>
        <script type="text/javascript" src="js/jquery.filestyle.mini.js"></script>
        <script type="text/javascript" src="js/jquery.wysiwyg.js"></script>
        <script type="text/javascript" src="js/jquery.date_input.pack.js"></script>
        <script type="text/javascript" src="js/facebox.js"></script>
        <script type="text/javascript" src="js/jquery.visualize.js"></script>
        <script type="text/javascript" src="js/jquery.visualize.tooltip.js"></script>
        <script type="text/javascript" src="js/jquery.select_skin.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
        <script type="text/javascript" src="js/ajaxupload.js"></script>
        <script type="text/javascript" src="js/jquery.pngfix.js"></script>
        <script type="text/javascript" src="js/custom.js"></script>

        <!--[if IE]><script type="text/javascript" src="js/excanvas.js"></script><![endif]-->

	<?php
		//If user isn't logged in load the login.js
		if (!$cookieValid) {
	?>
		<script src="/js/login.js"></script>
	<?php } ?>

	    <style type="text/css" media="all">
                @import url("css/style.css");
                @import url("css/jquery.wysiwyg.css");
                @import url("css/facebox.css");
                @import url("css/visualize.css");
                @import url("css/date_input.css");
	    </style>

	<!--[if lt IE 8]><style type="text/css" media="all">@import url("css/ie.css");</style><![endif]-->

</head>

<body>
<div id="hld">
<div class="wrapper">

<div id="siteheader">

    <div id="siteinfo">
	<div style="float: left"><img src="/images/litecoin.png"/></div>
	<div style="float: left;margin-top:25px"><?php if (isset($pageTitle)) { print $pageTitle; } ?>
	<br>
	<span class="slogan"><?php print $settings->getsetting('slogan'); ?></span></div>
    </div>

    <div id="ministats">
	<table border="0">
	<tr>
	<td><li>ltc/usd: $<?php print $settings->getsetting('btceusdlast'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</li></td>
	<td><li>Pool Hashrate: <?php print round($settings->getsetting('currenthashrate')/1000, 2); ?> Mh/s&nbsp;&nbsp;&nbsp;&nbsp;</li></td>
	<td><li>Pool Workers: <?php print $settings->getsetting('currentworkers'); ?>&nbsp;&nbsp;&nbsp;&nbsp;</li></td>
	<!-- <td><li><a href="/osList">DTD+WTD</a>: &#0321;<?php //print round(($settings->getsetting('donatedtodate') + $settings->getsetting('tobedonated')), 4); ?> <font size="1">BTC</font></li></td> -->
	</tr>
	</table>
    </div>

</div>

<?php include ("includes/templates/menu.php"); ?>


