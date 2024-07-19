<main id="main" class="container">
	<div class="row layout">
		<div class="col-md-3">
			<div id="sidebar">
				<?php include 'app://frontend/partials/box_information_links.inc.php'; ?>
			</div>
		</div>

		<div class="col-md-9">
			<div id="content">
				{{notices}}

				<section id="box-information" class="card">
					<div class="card-body">
						{{content}}
					</div>
				</section>

			</div>
		</div>
	</div>
</main>
