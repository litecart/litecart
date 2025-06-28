<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_brand_links.inc.php'; ?>
				<?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">

				<article id="box-brand" class="card">

					<div class="card-header">
						<h1 class="card-title">{{title}}</h1>
					</div>

					<div class="card-body">
						<?php if ($_GET['page'] == 1 && $description) { ?>
						<p class="description">{{description}}</p>
						<?php } ?>

						<?php if ($products) { ?>

						<div class="flex flex-gap">
							<div class="dropdown" style="flex-grow: 0;">
								<div class="form-select" data-toggle="dropdown">
									<?php echo t('title_sort_by', 'Sort By'); ?>
								</div>

								<ul class="dropdown-content">
									<?php foreach ($sort_alternatives as $key => $title) { ?>
									<li><?php echo functions::form_radio_button('sort', [$key, $title], true); ?></li>
									<?php } ?>
								</ul>
							</div>

							<div style="flex-grow: 0;">
								<?php echo functions::form_toggle('list_style', ['columns' => functions::draw_fonticon('icon-th-large'), 'rows' => functions::draw_fonticon('icon-bars')], true, 'data-token-group="list_style" data-token-title="'. t('title_list_style', 'List Style') .'"'); ?>
							</div>
						</div>

						<section class="listing products columns">
							<?php foreach ($products as $product) echo functions::draw_listing_product($product, ['brand_id']); ?>
						</section>

						<?php } ?>
					</div>

					<?php if ($pagination) { ?>
					<div class="card-footer">
						{{pagination}}
					</div>
					<?php } ?>
				</article>
			</div>
		</div>
	</div>
</main>