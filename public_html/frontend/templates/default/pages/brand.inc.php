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

						<?php include 'app://frontend/partials/box_filter.inc.php'; ?>

						<?php if ($products) { ?>
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