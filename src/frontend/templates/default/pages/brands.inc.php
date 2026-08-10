<main id="main" class="container">
	<div id="content">
		{{notices}}

		<section id="box-brands" class="card" aria-label="<?php echo f::escape_attr(t('title_brands', 'Brands')); ?>">

			<div class="card-header">
				<h1 class="card-title"><?php echo t('title_brands', 'Brands'); ?></h1>
			</div>

			<div class="card-body">
				<ul class="listing brands" role="list">

					<?php foreach ($brands as $brand) { ?>
					<li class="brand" role="listitem">
						<a class="link" href="<?php echo f::escape_html($brand['link']); ?>" aria-label="<?php echo f::escape_attr($brand['name']); ?>">
							<?php //echo f::draw_thumbnail($brand['image'], 320, 100, 'fit', 'alt="'. f::escape_attr($brand['name']) .'"'); ?>
							<div class="caption"><?php echo f::escape_html($brand['name']); ?></div>
						</a>
					</li>
					<?php } ?>

				</ul>
			</div>
		</section>
	</div>
</main>
