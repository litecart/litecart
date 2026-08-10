<main id="main" class="container">
	<div class="grid">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_information_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{notices}}

				<section id="box-information" class="card" aria-label="<?php echo f::escape_attr(!empty($title) ? $title : t('title_information', 'Information')); ?>">
					<div class="card-body">
						{{content}}
					</div>
				</section>

			</div>
		</div>
	</div>
</main>
