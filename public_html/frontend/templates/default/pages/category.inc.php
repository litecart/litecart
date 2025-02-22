<main id="main" class="container">
	{{breadcrumbs}}
	{{notices}}

	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar" style="margin-bottom: 2em;">

				<ul class="pills" style="margin-bottom: 2em;">
					<li><a class="nav-item" href="<?php echo document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('icon-chevron-left'); ?> <?php echo language::translate('title_back', 'Back'); ?></a></li>
				</ul>

				<div class="card">
					<div class="card-header">
						<h1 class="card-title"><?php echo $main_category['name']; ?></h1>
					</div>

					<div class="card-body">

						<?php include 'app://frontend/partials/box_category_tree.inc.php'; ?>

						<?php include 'app://frontend/partials/box_category_filter.inc.php'; ?>

					</div>
				</div>

				<?php include 'app://frontend/partials/box_recently_viewed_products.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">

				<?php if ($description) { ?>
				<article id="box-category-description" class="card">
					<div class="card-header">
						<h1 class="card-title"><?php echo $h1_title; ?></h1>
						<div style="margin-top: .5em;">
							<?php echo $short_description; ?>
						</div>
					</div>

					<div class="card-body">
						<div class="flex">

							<div class="description flex-grow" style="flex: 1 1 auto;">
								{{description}}
							</div>

							<?php if ($image) { ?>
							<div style="flex: 0 0 320px;">
								<?php echo functions::draw_thumbnail($image, 480, 0, 'category'); ?>
							</div>
							<?php } ?>

						</div>
					</div>
				</article>
				<?php } ?>

				<article id="box-category" class="card">
					<div class="card-header hidden-xs">
						<div class="flex flex-gap">
							<div>
							<h2 class="card-title"><?php echo $h1_title; ?></h2>
						</div>

							<div class="dropdown" style="flex-grow: 0;">
								<div class="form-select" data-toggle="dropdown">
									<?php echo language::translate('title_sort_by', 'Sort By'); ?>
								</div>

								<ul class="dropdown-content">
									<?php foreach ($sort_alternatives as $key => $title) { ?>
									<li><?php echo functions::form_radio_button('sort', [$key, $title], true); ?></li>
									<?php } ?>
								</ul>
							</div>

							<div style="flex-grow: 0;">
								<?php echo functions::form_toggle('list_style', ['columns' => functions::draw_fonticon('icon-th-large'), 'rows' => functions::draw_fonticon('icon-bars')], true, 'data-token-group="list_style" data-token-title="'. language::translate('title_list_style', 'List Style') .'"'); ?>
							</div>
						</div>
					</div>

					<div class="card-body">

<?php /*
						<nav class="pills hidden-xs" style="margin-bottom: 1em;">
							<a class="nav-item" href="<?php echo !empty($parent_id) ? document::href_ilink('category', ['category_id' => $parent_id]) : document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('icon-chevron-left'); ?> <?php echo language::translate('title_back', 'Back'); ?></a>
							<?php foreach ($subcategories as $subcategory) { ?><a class="nav-item" href="<?php echo document::href_ilink('category', ['category_id' => $subcategory['id']]); ?>"><?php echo $subcategory['name']; ?></a><?php } ?>
						</nav>
*/ ?>

						<section class="listing products <?php echo (isset($_GET['list_style']) && $_GET['list_style'] == 'rows') ? 'rows' : 'columns'; ?>">
							<?php foreach ($products as $product) echo functions::draw_listing_product($product, ['category_id']); ?>
						</section>
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