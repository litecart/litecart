<?php

	// Installer page layout. Returns the full HTML document as a string with a
	// {{content}} placeholder that the front controller (install/index.php)
	// replaces with the page output. Must not call ob_start() — it is required
	// from inside the front controller's output buffer callback, where nesting
	// output buffering is not allowed.

	$charset = mb_http_output();
	$nonce = htmlspecialchars(NONCE, ENT_QUOTES);
	$framework = is_file(__DIR__ . '/../../../assets/litecore/css/framework.min.css') ? '.min.css' : '.css';
	$year = date('Y');
	$ws_dir_app = WS_DIR_APP;

	return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="{$charset}">
<title>LiteCart Installer</title>
<link rel="stylesheet" href="../backend/template/css/variables.css" nonce="{$nonce}">
<link rel="stylesheet" href="../assets/litecore/css/framework{$framework}" nonce="{$nonce}">
<link rel="stylesheet" href="template/css/install.css" nonce="{$nonce}">
<script src="template/js/app.js" nonce="{$nonce}"></script>
</head>
<body>

<header class="text-center">
	<img src="data/default/storage/images/logotype.png" nonce="{$nonce}" alt="LiteCart" style="max-width: 300px; max-height: 100px;">
</header>

<main class="glass-edges">
	<div id="content">

		{{content}}

	</div>
</main>

<footer class="text-center">
Copyright © 2012-{$year} LiteCart AB &middot; All Rights Reserved
</footer>

<script src="{$ws_dir_app}assets/jquery/jquery-4.0.0.min.js" nonce="{$nonce}"></script>
<script src="{$ws_dir_app}assets/litecore/js/framework.min.js" nonce="{$nonce}"></script>

</body>
</html>
HTML;
