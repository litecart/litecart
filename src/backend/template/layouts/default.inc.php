<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}" class="<?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') ? 'dark-mode' : ''; ?>">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=1600">
<?php echo f::draw_style('app://backend/template/css/variables.css'); ?>
<?php echo f::draw_style('app://assets/litecore/css/framework.min.css'); ?>
<?php echo f::draw_style('app://backend/template/css/app.min.css'); ?>
{{head_tags}}
<style>
:root {
	--default-text-size: <?php echo isset($_COOKIE['font_size']) ? (int)$_COOKIE['font_size'] : 13; ?>px;
}
</style>
</head>

<body>

<div class="flex-columns flex-nogap">

	<?php include 'app://backend/partials/sidebar.inc.php'; ?>

	<div class="flex-grow">

		<main id="main">
			<div class="container">

				<?php include 'app://backend/partials/top_navigation.inc.php'; ?>

				<div id="content">

					{{notices}}

					{{breadcrumbs}}

					{{content}}

				</div>
			</div>
		</main>
	</div>
</div>

{{foot_tags}}
<?php echo f::draw_script('app://assets/litecore/js/framework.min.js'); ?>
<?php echo f::draw_script('app://backend/template/js/app.min.js'); ?>

</body>
</html>