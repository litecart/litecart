<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}" class="<?php echo !empty($_COOKIE['dark_mode']) ? ' class="dark-mode"' : ''; ?><?php echo !empty($_COOKIE['condensed']) ? ' condensed' : ''; ?>">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}">
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=1600">
<?php echo functions::draw_style('app://backend/template/css/variables.css'); ?>
<?php echo functions::draw_style('app://backend/template/css/framework.min.css'); ?>
<?php echo functions::draw_style('app://backend/template/css/app.min.css'); ?>
{{head_tags}}
{{style}}
<style>
:root {
	--default-text-size: <?php echo !empty($_COOKIE['font_size']) ? $_COOKIE['font_size'] : '14'; ?>px;
}
</style>
</head>
<body>

<div id="backend-wrapper">
	<input id="sidebar-compressed" type="checkbox" hidden>

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

	<main id="main">
		<ul id="top-bar" class="hidden-print">
			<li>
				<div>
					<label class="nav-toggle" for="sidebar-compressed">
						<?php echo functions::draw_fonticon('icon-bars'); ?>
					</label>
				</div>
			</li>

			<li style="flex-grow: 1;">
				<div id="search" class="dropdown">
					<?php echo functions::form_input_search('query', false, 'placeholder="'. functions::escape_attr(language::translate('title_search_entire_platform', 'Search entire platform')) .'&hellip;" autocomplete="off"'); ?>
					<div class="results dropdown-menu"></div>
				</div>
			</li>

			<li>
				<div class="btn-group" data-toggle="buttons">
					<button name="font_size" class="btn btn-default btn-sm" type="button" value="decrease"><span style="font-size: .8em;">A</span></button>
					<button name="font_size" class="btn btn-default btn-sm" type="button" value="increase"><span style="font-size: 1.25em;">A</span></button>
				</div>
			</li>

			<li>
				<?php echo functions::form_toggle('condensed', [['0', functions::draw_fonticon('icon-compress')], ['1', functions::draw_fonticon('icon-expand')]], isset($_COOKIE['condensed']) ? $_COOKIE['condensed'] : ''); ?>
			</li>

			<li>
				<?php echo functions::form_toggle('dark_mode', [['0', functions::draw_fonticon('icon-sun')], ['1', functions::draw_fonticon('icon-moon')]], isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] : ''); ?>
			</li>

			<li class="language dropdown">
				<a href="#" data-toggle="dropdown"><span style="font-family: monospace" title="<?php echo functions::escape_html(language::$selected['name']); ?>"><?php echo language::$selected['code']; ?><span> <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<?php foreach (language::$languages as $language) { ?>
					<li class="dropdown-menu-item">
						<a href="<?php echo document::href_ilink(null, [], [], [], $language['code']); ?>">
							<?php echo $language['name']; ?>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>

			<?php if ($webmail_link = settings::get('webmail_link')) { ?>
			<li>
				<a href="<?php echo ($webmail_link != 'https://') ? functions::escape_html($webmail_link) : document::href_ilink('settings/advanced', ['key' => 'webmail_link', 'action' => 'edit']); ?>" target="_blank" title="<?php echo language::translate('title_webmail', 'Webmail'); ?>">
					<?php echo functions::draw_fonticon('icon-envelope'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($control_panel_link = settings::get('control_panel_link')) { ?>
			<li>
				<a href="<?php echo ($control_panel_link != 'https://') ? functions::escape_html($control_panel_link) : document::href_ilink('settings/advanced', ['key' => 'control_panel_link', 'action' => 'edit']); ?>" target="_blank" title="<?php echo language::translate('title_control_panel', 'Control Panel'); ?>">
					<?php echo functions::draw_fonticon('icon-cogs'); ?>
				</a>
			</li>
			<?php } ?>

			<?php if ($database_admin_link = settings::get('database_admin_link')) { ?>
			<li>
				<a href="<?php echo ($database_admin_link != 'https://') ? functions::escape_html($database_admin_link) : document::href_ilink('settings/advanced', ['key' => 'database_admin_link', 'action' => 'edit']); ?>" target="_blank" title="<?php echo language::translate('title_database_manager', 'Database Manager'); ?>">
					<?php echo functions::draw_fonticon('icon-database'); ?>
				</a>
			</li>
			<?php } ?>

			<li>
				<a href="<?php echo document::href_ilink('f:'); ?>" title="<?php echo language::translate('title_frontend', 'Frontend'); ?>">
					<?php echo functions::draw_fonticon('icon-display'); ?> <?php echo language::translate('title_frontend', 'Frontend'); ?>
				</a>
			</li>

			<li>
				<a class="help" href="https://www.litecart.net/wiki/" target="_blank" title="<?php echo functions::escape_html(language::translate('title_help', 'Help')); ?>">
					<?php echo functions::draw_fonticon('icon-question'); ?> <?php echo language::translate('title_help', 'Help'); ?>
				</a>
			</li>

			<li>
				<a href="<?php echo document::href_ilink('logout'); ?>" title="<?php echo language::translate('title_sign_out', 'Sign Out'); ?>">
					<?php echo functions::draw_fonticon('icon-sign-out'); ?> <?php echo language::translate('title_sign_out', 'Sign Out'); ?>
				</a>
			</li>
		</ul>

		<div id="content">

			{{notices}}

			{{breadcrumbs}}

			{{content}}

		</div>
	</main>
</div>

{{foot_tags}}
<?php echo functions::draw_script('app://backend/template/js/app.min.js'); ?>

<script>
	$('button[name="font_size"]').on('click', function() {
		let new_size = parseInt($(':root').css('--default-text-size').split('px')[0]) + (($(this).val() == 'increase') ? 1 : -1)
		$(':root').css('--default-text-size', new_size + 'px')
		document.cookie = 'font_size='+ new_size +';Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000'
	})

	$('input[name="condensed"]').on('click', function() {
		if ($(this).val() == 1) {
			document.cookie = 'condensed=1;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000'
			$('html').addClass('condensed')
		} else {
			document.cookie = 'condensed=0;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000'
			$('html').removeClass('condensed')
		}
	})

	$('input[name="dark_mode"]').on('click', function() {
		if ($(this).val() == 1) {
			document.cookie = 'dark_mode=1;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000'
			$('html').addClass('dark-mode')
		} else {
			document.cookie = 'dark_mode=0;Path=<?php echo WS_DIR_APP; ?>;Max-Age=2592000'
			$('html').removeClass('dark-mode')
		}
	})
</script>
</body>
</html>