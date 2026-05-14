<?php

	document::$title[] = t('title_marketplace', 'Marketplace');

	breadcrumbs::add(t('title_marketplace', 'Marketplace'), document::ilink(__APP__ . '/marketplace'));

	if (!empty($_GET['category'])) {
		document::$title[] = $_GET['category'];
		breadcrumbs::add($_GET['category'], document::ilink(__APP__ . '/marketplace', ['category' => $_GET['category']]));
	}

	$categories_options = array_merge([['', '-- ' . t('title_all_categories', 'All Categories') . ' --']], array_column(marketplace_client::get_categories(), 'name'));

	$results = marketplace_client::get_addons([
		'query' => $_GET['query'] ?? '',
		'category' => $_GET['category'] ?? '',
		'page' => $_GET['page'] ?? 1,
	]);

?>
<style>
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

.addon .price {
	font-weight: 500;
	color: var(--success-color);
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_addons_market', 'Add-Ons Market'); ?>
		</div>
	</div>

	<?php echo f::form_begin('search_form', 'get'); ?>
		<div class="card-filter">
			<?php echo f::form_select('category', $categories_options, true, 'style="width: auto;"'); ?>
			<div class="expandable"><?php echo f::form_input_search('query', true, 'placeholder="' . t('text_search_phrase_or_keyword', 'Search phrase or keyword') . '"'); ?></div>
			<?php echo f::form_button('filter', t('title_search', 'Search'), 'submit'); ?>
		</div>
	<?php echo f::form_end(); ?>

	<div class="card-body">

		<h2><?php echo t('title_addons', 'Add-ons'); ?></h2>

		<div class="addons">
			<?php foreach ($results['addons'] as $addon) { ?>
			<a class="addon" href="<?php echo document::ilink(__APP__ . '/marketplace_addon', ['addon_id' => $addon['id']]); ?>">
				<img class="thumbnail fit" src="<?php echo f::escape_html($addon['image']['thumbnail'] ?: document::rlink(FS_DIR_STORAGE . 'images/no_image.svg')); ?>">
				<div class="details">
					<div class="name"><?php echo f::escape_html($addon['name']); ?></div>
					<div class="description"><?php echo f::escape_html($addon['short_description']); ?></div>
					<?php if ($addon['price'] > 0) { ?>
					<div class="price"><?php echo f::escape_html($addon['price']['formatted']); ?></div>
					<?php } elseif ($addon['price'] === 0) { ?>
					<div class="free"><?php echo t('title_free', 'Free'); ?></div>
					<?php } ?>
				</div>
			</a>
			<?php } ?>
		</div>

		<?php echo f::draw_pagination($results['pages']); ?>
	</div>

	<?php echo f::form_begin('market_form', 'post', '', true); ?>

	<?php echo f::form_end(); ?>
</div>

<script>
	$('select[name="category"]').on('change', function() {
		$(this).closest('form').submit();
	});
</script>