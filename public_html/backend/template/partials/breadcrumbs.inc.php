<ul class="breadcrumbs shadow">
<?php
	foreach ($breadcrumbs as $breadcrumb) {
		if (!empty($breadcrumb['link'])) {
			echo '<li class="breadcrumb-item"><a href="'. functions::escape_attr($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a></li>';
		} else {
			echo '<li class="breadcrumb-item">'. $breadcrumb['title'] .'</li>';
		}
	}
?>
</ul>
