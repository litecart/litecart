<style>
#box-filter .filter {
	display: grid;
	grid-auto-flow: row;
	grid-gap: 1em;
	grid-template-columns: 1fr;
	margin-bottom: 1em;
}
@media (min-width: 480px) {
	#box-filter .filter {
		grid-auto-flow: column;
	}
}
@media (min-width: 768px) {
	#box-filter .filter {
		grid-auto-flow: column;
	}
}
</style>

<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div id="content">

		<section id="box-search-results" class="card">
			<div class="card-header">
				<h1 class="card-title">{{title}}</h1>
			</div>

			<div class="card-body">
				<?php if ($products) { ?>

					<section id="box-filter">
					<?php echo functions::form_begin('filter_form', 'get'); ?>

						<div class="filter">
							<div>
								<?php echo functions::form_input_search('query', true, 'autocomplete="off" placeholder="'. functions::escape_attr(t('text_search_products', 'Search products')) .' ..."'); ?>
							</div>

							<div>
								<div class="dropdown">

									<div class="form-select" data-toggle="dropdown">
										<?php echo t('title_sort_by', 'Sort By'); ?>
									</div>

									<ul class="dropdown-menu">
										<?php foreach ($sort_alternatives as $key => $title) { ?>
										<li class="dropdown-item">
											<?php echo functions::form_radio_button('sort', [$key, $title], true); ?>
										</li>
										<?php } ?>
									</ul>
								</div>
							</div>
						</div>

					<?php echo functions::form_end(); ?>
				</section>
				<?php } ?>

				<?php if ($categories) { ?>
				<nav class="pills" style="margin-bottom: 1em;">
					<a class="pill-item" href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>">
						<?php echo functions::draw_fonticon('icon-chevron-left'); ?> <?php echo t('title_back', 'Back'); ?>
					</a>

					<?php foreach ($subcategories as $subcategory) { ?>
					<a class="nav-item" href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>">
						<?php echo $subcategory['name']; ?>
					</a>
					<?php } ?>
				</nav>
				<?php } ?>

				<?php if ($products) { ?>
				<section class="listing products columns">
					<?php foreach ($products as $product) echo functions::draw_listing_product($product, null); ?>
				</section>
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
