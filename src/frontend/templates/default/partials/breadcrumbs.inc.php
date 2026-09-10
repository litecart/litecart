<nav class="breadcrumbs-wrapper">
	<ol class="breadcrumbs" aria-label="<?php echo f::escape_attr(t('title_breadcrumb', 'Breadcrumb')); ?>">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<li>
			<?php
				if (!empty($breadcrumb['link'])) {
						echo '<a class="breadcrumb-item" href="'. f::escape_attr($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a>';
					} else {
						echo '<span class="breadcrumb-item" aria-current="page">'. $breadcrumb['title'] .'</span>';
				}
			?>
		</li>
		<?php } ?>
	</ol>
</nav>
