<?php
  $dir = get_template_directory_uri();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html lang="fi" manifest="<?php echo $dir; ?>/cache.cfm">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Infotv</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $dir; ?>/plain.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $dir; ?>/style-tools.css" />
	
	<!--<script type="text/javascript" src="<?php echo $dir; ?>/script.js" async></script>-->
</head>
<body>

<div id="placeholder">
	<img src="<?php echo $dir; ?>/images/infotv-login.png">
	<div><noscript>InfoTV tarvitsee Javascript tuen</noscript></div>
	<div>Jos InfoTV ei kaynnisty, paivita selaimesi.</div>
</div>

<script type="text/javascript" src="<?php echo $dir; ?>/javascript/default_config.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/utils.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/network.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/ui.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/core.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/tools.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/slides.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/buslist.js"></script>
<script type="text/javascript" src="<?php echo $dir; ?>/javascript/loader.js"></script>

</body>
</html>
