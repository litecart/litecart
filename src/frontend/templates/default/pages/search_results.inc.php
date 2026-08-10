<style>
#box-filter .filter {
	display: grid;
	grid-auto-flow: row;
	gap: 1em;
	grid-template-columns: 1fr;
	margin-bottom: 1em;
}

@media (width >= 480px) {
	#box-filter .filter {
		grid-auto-flow: column;
	}
}

@media (width >= 768px) {
	#box-filter .filter {
		grid-auto-flow: column;
	}
}
</style>

<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div id="content">

		<section id="box-search-results" class="card" aria-label="<?php echo f::escape_attr(!empty($title) ? $title : t('title_search_results', 'Search Results')); ?>">
			<div class="card-header">
				<h1 class="card-title">{{title}}</h1>
			</div>

			<div class="card-body">
				<?php if ($products) { ?>

					<section id="box-filter" role="search" aria-label="<?php echo f::escape_attr(t('title_filter_results', 'Filter Results')); ?>">
					<?php echo f::form_begin('filter_form', 'get', false, false, ['role' => 'search', 'aria-label' => f::escape_attr(t('title_filter_results', 'Filter Results'))]); ?>

						<div class="filter">
							<div>
								<label for="filter_query" class="hidden"><?php echo t('title_search_products', 'Search products'); ?></label>
								<?php echo f::form_input_search('query', true, ['id' => 'filter_query', 'autocomplete' => 'off', 'placeholder' => f::escape_attr(t('text_search_products', 'Search products')) . ' ...']); ?>
							</div>

							<div>
								<div class="dropdown">

									<div class="form-select" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<?php echo t('title_sort_by', 'Sort By'); ?>
									</div>

									<ul class="dropdown-menu" role="menu">
										<?php foreach ($sort_alternatives as $key => $title) { ?>
										<li class="dropdown-item" role="none">
											<?php echo f::form_radio_button('sort', [$key, $title], true); ?>
										</li>
										<?php } ?>
									</ul>
								</div>
							</div>
						</div>

					<?php echo f::form_end(); ?>
				</section>
				<?php } ?>

				<?php if ($categories) { ?>
				<nav class="pills" style="margin-bottom: 1em;" aria-label="<?php echo f::escape_attr(t('title_subcategories', 'Subcategories')); ?>">
					<a class="pill-item" href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>">
						<?php echo f::draw_fonticon('icon-chevron-left', 'aria-hidden="true"'); ?> <?php echo t('title_back', 'Back'); ?>
					</a>

					<?php foreach ($categories as $subcategory) { ?>
					<a class="nav-item" href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>">
						<?php echo $subcategory['name']; ?>
					</a>
					<?php } ?>
				</nav>
				<?php } ?>

				<?php if ($products) { ?>
				<ul class="listing products columns" role="list" aria-label="<?php echo f::escape_attr(t('title_products', 'Products')); ?>">
					<?php foreach ($products as $product) echo '<li role="listitem">' . f::draw_listing_product($product, null) . '</li>'; ?>
				</ul>
				<?php } ?>

				<?php if (!$categories && !$products) { ?>
				<div><em><?php echo t('text_no_matching_results', 'No matching results'); ?></em></div>
				<?php } ?>

			</div>

			<?php if ($pagination) { ?>
			<div class="card-footer">
				{{pagination}}
			</div>
			<?php } ?>
		</section>

	</div>
</main>
