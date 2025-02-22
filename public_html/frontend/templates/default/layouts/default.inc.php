<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="description" content="{{description}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php echo functions::draw_style('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>
<?php echo functions::draw_style('app://assets/litecore/css/framework.min.css'); ?>
<?php echo functions::draw_style('app://frontend/templates/'.settings::get('template').'/css/app.min.css'); ?>
{{head_tags}}
</head>
<body>

<div id="page">
	<header>
		<?php include 'app://frontend/partials/site_top_bar.inc.php'; ?>
		<?php include 'app://frontend/partials/site_navigation.inc.php'; ?>
	</header>

	{{content}}

	<?php include 'app://frontend/partials/site_footer.inc.php'; ?>
</div>

<?php if (fallback(document::$settings['scroll_up'])) { ?>
<a id="scroll-up" class="hidden-print" href="#">
	<?php echo functions::draw_fonticon('icon-chevron-up fa-3x', 'style="color: #000;"'); ?>
</a>
<?php } ?>

<?php include 'app://frontend/templates/'.settings::get('template').'/partials/site_cookie_notice.inc.php'; ?>

{{foot_tags}}
<?php echo functions::draw_script('app://frontend/templates/'.settings::get('template').'/js/app.min.js'); ?>
</body>
</html>