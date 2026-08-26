<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="{{charset}}">
<title>LiteCart Installer</title>
<link rel="stylesheet" href="../backend/template/css/variables.css" nonce="{{nonce}}">
<link rel="stylesheet" href="../assets/litecore/css/framework{$framework}" nonce="{{nonce}}">
<link rel="stylesheet" href="template/css/install.css" nonce="{{nonce}}">
<script src="template/js/app.js" nonce="{{nonce}}"></script>
</head>
<body>

<header class="text-center">
	<img src="data/default/storage/images/logotype.png" nonce="{{nonce}}" alt="LiteCart" style="max-width: 300px; max-height: 100px;">
</header>

<main class="glass-edges">
	<div id="content">
		{{content}}
	</div>
</main>

<footer class="text-center">
Copyright © 2012-{{year}} LiteCart AB &middot; All Rights Reserved
</footer>

<script src="{{ws_dir_app}}assets/jquery/jquery-4.0.0.min.js" nonce="{{nonce}}"></script>
<script src="{{ws_dir_app}}assets/litecore/js/framework.min.js" nonce="{{nonce}}"></script>

</body>
</html>