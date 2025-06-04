<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}" class="<?php echo !empty($_COOKIE['dark_mode']) ? 'dark-mode' : ''; ?><?php echo !empty($_COOKIE['condensed']) ? ' condensed' : ''; ?>">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=1600">
<?php echo functions::draw_style('app://backend/template/css/variables.css'); ?>
<?php echo functions::draw_style('app://assets/litecore/css/framework.min.css'); ?>
<?php echo functions::draw_style('app://backend/template/css/app.min.css'); ?>
{{head_tags}}
{{style}}
<style>
:root {
	--default-text-size: <?php echo fallback($_COOKIE['font_size'], '14'); ?>px;
}
</style>
</head>
<body>

<div class="flex-columns flex-nogap">

	<input id="sidebar-compact" type="checkbox" hidden>

	<div id="sidebar" class="hidden-print">

		<a class="logotype" href="<?php echo document::href_ilink(''); ?>">
			<img class="center-block responsive" src="<?php echo document::href_rlink('storage://images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>">
		</a>

		<div class="filter">
			<?php echo functions::form_input_search('filter', false, 'placeholder="'. functions::escape_attr(language::translate('title_filter', 'Filter')) .'&hellip;" autocomplete="off"'); ?>
		</div>

		<?php include 'app://backend/partials/box_apps_menu.inc.php'; ?>

		<div class="text-center">
			<a class="platform" href="<?php echo document::href_ilink('about'); ?>">
				<span class="name"><?php echo PLATFORM_NAME; ?>®</span>
				<span class="version"><?php echo PLATFORM_VERSION; ?></span>
			</a>
		</div>

		<div class="copyright" class="text-center">Copyright &copy; <?php echo date('2012-Y'); ?><br>
			<a href="https://www.litecart.net" target="_blank">www.litecart.net</a>
		</div>
	</div>

	<div class="flex-grow">

		<main id="main">
			<div class="container">

				<?php include 'app://backend/partials/site_top_navigation.inc.php'; ?>

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
<?php echo functions::draw_script('app://assets/litecore/js/framework.min.js'); ?>
<?php echo functions::draw_script('app://backend/template/js/app.min.js'); ?>
</body>
</html>