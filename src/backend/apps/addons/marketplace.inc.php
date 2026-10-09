<?php

	administrator::require_login();

	document::add_csp('img-src', ["'self'", 'data:', 'https://*.litecart.net/']);
	document::add_csp('img-src', ["'self'", 'data:', 'https://*.libravatar.org']);

	document::$title[] = t('title_marketplace');

	breadcrumbs::add(t('title_addons', 'Add-Ons'));
	breadcrumbs::add(t('title_marketplace'), document::ilink(__APP__ . '/marketplace'));

	$marketplace = marketplace_client::get_marketplace(15);
	$profile = marketplace_client::whoami();

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

.whoami {
	position: relative;
	padding: 1em;
	background: var(--pills-background-color);
	color: var(--pills-text-color);
	line-height: 1.75;
}

.whoami .avatar {
	float: left;
	margin-inline-end: 1em;
	background: var(--whitespace-color);
	border-radius: var(--border-radius);
	height: 60px;
}

html[dir="rtl"] .whoami .avatar {
	float: right;
}

.whoami .username {
	font-size: 1.2em;
	font-weight: bold;
}

.whoami .store-name {
}

.whoami .disconnect {
	position: absolute;
	top: 1em;
	right: 1em;
}

.categories {
	display: block;
	columns: 5;
}

.categories a {
	font-weight: 500;
}

.addons {
	columns: auto 250px;
}

.addon {
	display: flex;
	flex-direction: column;
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
	width: 100%;
	margin-inline-end: 1em;
	aspect-ratio: 1/1;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
}

.addon .details {
	display: flex;
	flex-grow: 1;
	gap: 0.5em;
	flex-direction: column;
}

.addon .name {
	font-weight: 500;
}

.addon .description {
	height: 4em;
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

#banners {
	display: grid;
	grid-template: "jumbotron profile" auto "jumbotron banner" auto / 67.3% 31.7%;
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

#profile {
	background: #ddd;
	border-radius: var(--border-radius);
	padding: 1em;
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?= $app_icon ?> <?= t('title_marketplace', 'Marketplace') ?>
		</div>
	</div>

	<div class="card-action">
		<?= f::form_begin('search_form', 'get', document::ilink(__APP__ . '/catalog')) ?>
			<div>
				<?= f::form_input_search('query', true) ?>
			</div>
		<?= f::form_end() ?>
	</div>

	<div class="card-body">

		<div id="banners">

			<div id="jumbotron" style="grid-area: jumbotron;">
				<a href="<?= f::escape_html($marketplace['banners']['jumbotron']['link']) ?>">
					<img class="thumbnail fit" src="<?= f::escape_html($marketplace['banners']['jumbotron']['image']) ?>">
				</a>
			</div>

			<div id="profile" style="grid-area: profile;" style="margin-bottom: 30px;">
				<?php if (!$profile) { ?>
				<a class="connect btn btn-default" href="<?= document::ilink(__APP__ . '/connect') ?>">
					<?= f::draw_fonticon('icon-power-plug') ?>
					<?= t('title_connect_my_litecart_account', 'Connect my LiteCart account') ?>
				</a>
				<?php } else { ?>
				<div id="whoami" style="grid-area: whoami;" style="margin-bottom: 30px;">
					<img class="avatar" src="<?= f::escape_html($profile['user']['avatar']) ?>">
					<div class="username"><?= $profile['user']['username'] ?></div>
					<div class="store-name"><?= $profile['store']['name'] ?></div>
					<a class="disconnect btn btn-default btn-sm" href="<?= document::ilink(__APP__ . '/disconnect') ?>"><?= f::draw_fonticon('icon-chain-broken') ?> <?= t('title_disconnect', 'Disconnect') ?></a>
				</div>
				<?php } ?>
			</div>

			<div id="banner" style="grid-area: banner;">
				<a href="<?= f::escape_html($marketplace['banners']['banner1']['link']) ?>">
					<img class="thumbnail fit" src="<?= f::escape_html($marketplace['banners']['banner1']['image']) ?>">
				</a>
			</div>
		</div>

		<h2><?= t('title_browse_by_category', 'Browse By Category') ?></h2>

		<nav class="categories pills" style="margin-bottom: 2em;">
			<?php foreach ($marketplace['categories'] as $category) { ?>
			<a class="pill-item" href="<?= document::ilink(__APP__ . '/catalog', ['category' => $category['name']]) ?>">
				<?= f::escape_html($category['name']) ?>
			</a>
			<?php } ?>
		</nav>

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