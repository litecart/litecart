<main id="main" class="container">
	<div id="content">
		{{breadcrumbs}}
		{{notices}}

		<section id="box-page" class="card" aria-label="<?php echo f::escape_attr(!empty($title) ? $title : t('title_page', 'Page')); ?>">
			<div class="card-body">
				<?php echo $content; ?>
			</div>
		</section>
	</div>
</main>
