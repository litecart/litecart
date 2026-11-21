<?php

	administrator::require_login();

	document::add_csp('img-src', ["'self'", 'data:', 'https://*.litecart.net/']);
	document::add_csp('img-src', ["'self'", 'data:', 'https://*.libravatar.org']);

	document::$title[] = t('title_marketplace');

	breadcrumbs::add(t('title_addons', 'Add-Ons'));
	breadcrumbs::add(t('title_marketplace'), document::ilink(__APP__ . '/marketplace'));

	$marketplace = marketplace_client::get_marketplace(15);
	$profile = marketplace_client::whoami();

	$categories_options = array_merge(
		[['', '-- ' . t('title_all_categories', 'All Categories') . ' --']],
		array_column($marketplace['categories'], 'name')
	);

?>
<style>
h2 {
	margin-top: 2em;
}

.placeholder {
	background: #ccc;
	border-radius: var(--border-radius);
	background-image: url('');
	background-size: cover;
	background-position: bottom;
}

#whoami {
	position: relative;
	padding: 1em;
	background: var(--pills-background-color);
	color: var(--pills-text-color);
	line-height: 1.75;
}

#whoami .avatar {
	float: inset-inline-start;
	margin-inline-end: 1em;
	background: var(--whitespace-color);
	border-radius: var(--border-radius);
	height: 60px;
}

#banners {
	display: grid;
	grid-template: "jumbotron profile" auto "jumbotron banner" auto / minmax(0, 65fr) minmax(0, 35fr);
	gap: 15px;
}
#banners > * {
	aspect-ratio: 3/1;
}
#banners > a {
	display: block;
	border-radius: var(--border-radius);
	overflow: hidden;
}
#jumbotron {
	aspect-ratio: auto;
}
#jumbotron a {
	display: block;
	height: 100%;
	border-radius: var(--border-radius);
	overflow: hidden;
}
#jumbotron img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

#profile, #whoami {
	background: #ddd;
	border-radius: var(--border-radius);
	padding: 1em;
}

#whoami .username {
	font-size: 1.2em;
	font-weight: bold;
}

#whoami .store-name {
}

#whoami .disconnect {
	position: absolute;
	top: 1em;
	right: 1em;
}

.marketplace-filter {
	margin-bottom: 2em;
}

.addons {
	columns: 3;
}

.addon {
	display: flex;
	flex-direction: row;
	gap: 1em;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
	padding: 1em;
	width: 100%;
	margin-bottom: 1em;
	color: inherit;
	text-decoration: none !important;
}
.addon:hover {
	background: var(--whitespace-color);
	border-color: #000;
}
.addon img {
	width: 96px;
	flex-shrink: 0;
	margin-inline-end: 1em;
	aspect-ratio: 1/1;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
}
.addon .details {
	display: flex;
	flex-grow: 1;
	min-width: 0;
	gap: 0.5em;
	flex-direction: column;
}
.addon .name {
	font-weight: 500;
}
.addon .price {
	font-weight: 500;
	color: var(--success-color);
}

.leaderboard .text {
	font-size: 3em;
	color: white;
	text-shadow: 0 0 3px #333;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_marketplace', 'Marketplace') ?>
		</div>
	</div>

	<div class="card-body">

		<div id="banners">

			<div id="jumbotron" style="grid-area: jumbotron;">
				<a href="<?= f::escape_html($marketplace['banners']['jumbotron']['link']) ?>">
					<img class="thumbnail fit" src="<?= f::escape_html($marketplace['banners']['jumbotron']['image']) ?>">
				</a>
			</div>

			<?php if ($profile) { ?>
			<div id="whoami" style="grid-area: profile;">
				<img class="avatar" src="<?= f::escape_html($profile['user']['avatar']) ?>">
				<div class="username"><?= $profile['user']['username'] ?></div>
				<div class="store-name"><?= $profile['store']['name'] ?></div>
				<a class="disconnect btn btn-default btn-sm" href="<?= document::ilink(__APP__ . '/disconnect') ?>"><?= f::draw_fonticon('icon-sign-out') ?> <?= t('title_disconnect', 'Disconnect') ?></a>
			</div>
			<?php } else { ?>
			<div id="profile" style="grid-area: profile;">
				<a class="connect btn btn-default" href="<?= document::ilink(__APP__ . '/connect') ?>" class="placeholder" data-aspect-ratio="2:1">
					<?= f::draw_fonticon('icon-power-plug') ?>
					<?= t('title_connect_my_litecart_account', 'Connect my LiteCart account') ?>
				</a>
			</div>
			<?php } ?>

			<div id="banner" style="grid-area: banner;" class="placeholder" data-aspect-ratio="2:1">
				<a href="<?= f::escape_html($marketplace['banners']['banner1']['link']) ?>">
					<img class="thumbnail fit" src="<?= f::escape_html($marketplace['banners']['banner1']['image']) ?>">
				</a>
			</div>
		</div>

		<h2><?= t('title_browse_by_category', 'Browse By Category') ?></h2>

		<?= f::form_begin('marketplace_filter', 'get', document::ilink(__APP__ . '/catalog')) ?>
			<div class="card-filter marketplace-filter">
				<?= f::form_select('category', $categories_options, true, ['style' => 'width: auto;']) ?>
				<div class="expandable"><?= f::form_input_search('query', true, ['placeholder' => t('text_search_phrase_or_keyword', 'Search phrase or keyword')]) ?></div>
				<?= f::form_button('filter', t('title_search', 'Search'), 'submit') ?>
			</div>
		<?= f::form_end() ?>

		<h2><?= t('title_featured_addons', 'Featured Add-ons') ?></h2>

		<div class="addons">
			<?php foreach ($marketplace['featured'] as $addon) { ?>
			<a class="addon" href="<?= document::ilink(__APP__ . '/marketplace_addon', ['addon_id' => $addon['id']]) ?>">
				<img class="thumbnail fit" src="<?= f::escape_html($addon['image']['thumbnail'] ?: document::rlink(FS_DIR_STORAGE . 'images/no_image.svg')) ?>">
				<div class="details">
					<div class="name"><?= f::escape_html($addon['name']) ?></div>
					<div class="description"><?= f::escape_html($addon['short_description']) ?></div>
					<?php if ($addon['price']['value'] > 0) { ?>
					<div class="price"><?= f::escape_html($addon['price']['formatted']) ?></div>
					<?php } elseif ($addon['price']['value'] === 0) { ?>
					<div class="free"><?= t('title_free', 'Free') ?></div>
					<?php } ?>
				</div>
			</a>
			<?php } ?>
		</div>

		<h2><?= t('title_best_selling_addons', 'Best Selling Add-ons') ?></h2>

		<div class="addons">
			<?php foreach ($marketplace['best_selling'] as $addon) { ?>
			<a class="addon" href="<?= document::ilink(__APP__ . '/marketplace_addon', ['addon_id' => $addon['id']]) ?>">
				<img class="thumbnail fit" src="<?= f::escape_html($addon['image']['thumbnail'] ?: document::rlink(FS_DIR_STORAGE . 'images/no_image.svg')) ?>">
				<div class="details">
					<div class="name"><?= f::escape_html($addon['name']) ?></div>
					<div class="description"><?= f::escape_html($addon['short_description']) ?></div>
					<?php if ($addon['price']['value'] > 0) { ?>
					<div class="price"><?= f::escape_html($addon['price']['formatted']) ?></div>
					<?php } elseif ($addon['price']['value'] === 0) { ?>
					<div class="free"><?= t('title_free', 'Free') ?></div>
					<?php } ?>
				</div>
			</a>
			<?php } ?>
		</div>

		<h2><?= t('title_popular_addons', 'Popular Add-ons') ?></h2>

		<div class="addons">
			<?php foreach ($marketplace['most_popular'] as $addon) { ?>
			<a class="addon" href="<?= document::ilink(__APP__ . '/marketplace_addon', ['addon_id' => $addon['id']]) ?>">
				<img class="thumbnail fit" src="<?= f::escape_html($addon['image']['thumbnail'] ?: document::rlink(FS_DIR_STORAGE . 'images/no_image.svg')) ?>">
				<div class="details">
					<div class="name"><?= f::escape_html($addon['name']) ?></div>
					<div class="description"><?= f::escape_html($addon['short_description']) ?></div>
					<?php if ($addon['price']['value'] > 0) { ?>
					<div class="price"><?= f::escape_html($addon['price']['formatted']) ?></div>
					<?php } elseif ($addon['price']['value'] === 0) { ?>
					<div class="free"><?= t('title_free', 'Free') ?></div>
					<?php } ?>
				</div>
			</a>
			<?php } ?>
		</div>
	</div>
</div>

<script>
	$('select[name="category"]').on('change', function() {
		$(this).closest('form').submit();
	});

	$('button[name="connect"]').on('click', function() {
		window.authwindow = window.open('/opener.html');
	});

	$('.data-table :checkbox').on('change', function() {
		$('#actions').prop('disabled', !$('.data-table :checked').length);
	}).first().trigger('change');

	window.callMe = function() {
		alert('yes');
		window.authwindow.close();
	}
</script>