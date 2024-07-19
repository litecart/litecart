<main id="main" class="container">
	<div class="layout row">

		<div class="hidden-xs hidden-sm col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_information_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{breadcrumbs}}
				{{notices}}

				<section id="box-information">
					<?php echo $content; ?>
				</section>
			</div>
		</div>
	</div>
</main>