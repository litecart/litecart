<main id="main" class="container">
	<div class="layout row">

		<div class="hidden-xs hidden-sm col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_brand_links.inc.php'; ?>
				<?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{notices}}
				{{breadcrumbs}}

				<article id="box-brand" class="card">

					<div class="card-header">
						<h1 class="card-title">
							<?php echo $title; ?>
						</h1>
					</div>

					<div class="card-body">
						<?php if ($_GET['page'] == 1 && $description) { ?>
						<p class="description"><?php echo $description; ?></p>
						<?php } ?>

						<?php include 'app://frontend/partials/box_filter.inc.php'; ?>

						<?php if ($products) { ?>
						<section class="listing products <?php echo functions::escape_html($_GET['list_style']); ?>">
							<?php foreach ($products as $product) echo functions::draw_listing_product($product, ['brand_id']); ?>
						</section>
						<?php } ?>

						<?php echo $pagination; ?>
					</div>
				</article>
			</div>
		</div>
	</div>
</main>