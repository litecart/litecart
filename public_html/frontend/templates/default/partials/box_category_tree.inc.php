<?php

	$draw_branch = function($category) use (&$draw_branch, &$trail) {

		$output = [
			'<li class="category-'. $category['id'] .'">',
			'  <a class="nav-item'. (!empty($category['opened']) ? ' opened' : '') . (!empty($category['active']) ? ' active' : '') .'" href="'. functions::escape_attr($category['link']) .'">'. functions::draw_fonticon(!empty($category['opened']) ? 'icon-chevron-down float-end' : 'icon-chevron-right float-end') .' '. $category['name'] . ((settings::get('category_tree_product_count') && $category['num_products']) ? ' <small class="float-end">('. $category['num_products'] .')</small>' : '') .'</a>',
		];

		if (!empty($category['subcategories'])) {
			$output[] = '  <ul class="nav nav-stacked nav-pills">';
			foreach ($category['subcategories'] as $subcategory) {
				$output[] = $draw_branch($subcategory);
			}
			$output[] =  '  </ul>';
		}

		$output[] =  '</li>';

		return implode(PHP_EOL, $output);
	};
?>

<section id="box-category-tree" style="margin-bottom: 2em;">

	<ul class="nav nav-stacked nav-pills">
		<?php foreach ($categories as $category) echo $draw_branch($category); ?>
	</ul>

</section>
