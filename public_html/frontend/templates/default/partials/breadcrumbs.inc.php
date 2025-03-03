<ul class="breadcrumbs-2">
	<?php foreach ($breadcrumbs as $breadcrumb) { ?>
	<li>
		<?php
			if (!empty($breadcrumb['link'])) {
					echo '<a class="breadcrumb-item" href="'. functions::escape_attr($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a>';
				} else {
					echo '<span class="breadcrumb-item">'. $breadcrumb['title'] .'</span>';
			}
		?>
	</li>
	<?php } ?>
</ul>
