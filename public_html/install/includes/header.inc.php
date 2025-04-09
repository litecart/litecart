<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="<?php echo mb_http_output(); ?>">
<title>LiteCart Installer</title>
<link rel="stylesheet" href="../backend/template/css/variables.css">
<?php if (is_file(__DIR__.'/../../assets/litecore/css/framework.css')) { ?>
<link rel="stylesheet" href="../assets/litecore/css/framework.css">
<?php } else { ?>
<link rel="stylesheet" href="../assets/litecore/css/framework.min.css">
<?php } ?>
<script>window.waitFor=window.waitFor||((i,o)=>{void 0!==window.i?o(window.i):setTimeout((()=>waitFor(i,o)),50)});</script>
<style>
html {
	background: radial-gradient(ellipse at center, #fff 20%, #d2d7de 100%);
}

body {
	padding: 15px;
}

header {
	margin: 2em 0;
}

#logotype {
	max-width: 300px;
	max-height: 100px;
	margin-bottom: 2em;
}

.glass-edges {
	margin: 0 auto;
	margin-bottom: 15px;
	padding: 15px;
	max-width: 800px;
	border: 1px solid rgba(128,128,128,0.5);
	border-radius: 30px;
	box-shadow: 0 2px 6px rgba(0,0,0,0.5), inset 0 1px rgba(255,255,255,0.3), inset 0 10px rgba(255,255,255,0.2), inset 0 10px 20px rgba(255,255,255,0.25), inset 0 -15px 30px rgba(0,0,0,0.3);
}

#content{
	padding: 20px;
	background-color: #fff;
	border: 1px rgba(128,128,128,0.5) solid;
	border-radius: 20px;
}

span.ok {
	color: #0c0;
	font-weight: bold;
}
span.error {
	color: #f00;
	font-weight: bold;
}
span.warning {
	color: #c60;
	font-weight: bold;
}
footer {
	font-size: .8em;
	color: #999;
}
</style>
</head>
<body>

<header class="text-center">
	<img src="data/default/storage/images/logotype.png" alt="LiteCart" style="max-width: 300px; max-height: 100px;">
</header>

<main class="glass-edges">
	<div id="content">
