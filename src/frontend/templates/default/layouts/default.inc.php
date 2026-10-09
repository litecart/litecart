<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= f::draw_style('app://frontend/templates/'.settings::get('template').'/css/variables.css') ?>
<?= f::draw_style('app://assets/litecore/css/framework.min.css') ?>
<?= f::draw_style('app://frontend/templates/'.settings::get('template').'/css/app.min.css') ?>
{{head_tags}}
</head>
<body>

<div id="page">
	<a class="hidden focusable skip-link" href="#main">
		<?= t('title_skip_to_main_content', 'Skip to main content') ?>
	</a>

	<header class="container" role="banner">

		<?php if ($important_notice) { ?>
		<div id="important-notice" role="alert">
			<?= f::escape_html($important_notice) ?>
		</div>
		<?php } ?>

		<?php include 'app://frontend/partials/site_navigation.inc.php'; ?>
	</header>

	{{content}}

	<?php include 'app://frontend/partials/site_footer.inc.php'; ?>
</div>

<?php if (document::$settings['scroll_up'] ?? null) { ?>
<a id="scroll-up" class="hidden-print" href="#" aria-label="<?= f::escape_attr(t('title_back_to_top', 'Back to top')) ?>">
	<?= f::draw_fonticon('icon-chevron-up', 'style="color: #000; font-size: 3rem;" aria-hidden="true"') ?>
</a>
<?php } ?>

<?php include 'app://frontend/partials/site_privacy_consent.inc.php'; ?>

{{foot_tags}}
<?= f::draw_script('app://assets/litecore/js/framework.min.js') ?>
<?= f::draw_script('app://frontend/templates/'.settings::get('template').'/js/app.min.js') ?>
</body>
</html>