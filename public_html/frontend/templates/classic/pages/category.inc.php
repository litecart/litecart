<main id="main" class="container">
	<div class="layout row">

		<div class="hidden-xs hidden-sm col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>
				<?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{notices}}
				{{breadcrumbs}}

				<article id="box-category">

					<div class="row">
						<?php if ($_GET['page'] == 1 && $image) { ?>
						<div class="hidden-xs hidden-sm col-md-4">
							<?php echo functions::draw_thumbnail($image, 320, 0, 'category'); ?>
						</div>
						<?php } ?>

						<div class="<?php echo $image ? 'col-md-8' : 'col-md-12'; ?>">
							<h1 class="title"><?php echo $h1_title; ?></h1>

							<?php if ($_GET['page'] == 1 && trim(strip_tags($description))) { ?>
							<p class="description"><?php echo $description; ?></p>
							<?php } ?>
						</div>
					</div>

					<nav class="nav nav-pills">
						<a class="nav-link" href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('icon-arrow-left'); ?> <?php echo language::translate('title_back', 'Back'); ?></a>
						<?php foreach ($subcategories as $subcategory) { ?><a class="nav-link" href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>"><?php echo $subcategory['name']; ?></a><?php } ?>
					</nav>

					<?php if (isset($_GET['product_name']) || isset($_GET['attributes']) || isset($_GET['brands']) || $products) { ?>
					<div class="card">
						<?php include 'app://frontend/partials/box_filter.inc.php'; ?>

						<section class="listing products <?php echo functions::escape_html($_GET['list_style']); ?>">
							<?php foreach ($products as $product) echo functions::draw_listing_product($product, ['category_id']); ?>
						</section>

						<?php if ($pagination) { ?>
						<div class="card-footer">
							<?php echo $pagination; ?>
						</div>
						<?php } ?>

					</div>
					<?php } ?>

				</article>
			</div>
		</div>
	</div>
</main>