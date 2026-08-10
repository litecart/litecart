<?php

	$draw_branch = function($category) use (&$draw_branch, &$trail) {

		$output = [
			'<li class="category-'. $category['id'] .'">',
			'  <a class="pill-item'. (!empty($category['opened']) ? ' opened' : '') . (!empty($category['active']) ? ' active' : '') .'" href="'. f::escape_attr($category['link']) .'"'. (!empty($category['active']) ? ' aria-current="page"' : '') .'>'. f::draw_fonticon(!empty($category['opened']) ? 'icon-chevron-down float-end' : 'icon-chevron-right float-end', 'aria-hidden="true"') .' <span>'. $category['name'] .'</span>'. ((settings::get('category_tree_product_count') && $category['num_products']) ? ' <small class="float-end">('. $category['num_products'] .')</small>' : '') .'</a>',
		];

		if (!empty($category['subcategories'])) {
			$output[] = '  <ul class="pills">';
			foreach ($category['subcategories'] as $subcategory) {
				$output[] = $draw_branch($subcategory);
			}
			$output[] =  '  </ul>';
		}

		$output[] =  '</li>';

		return implode(PHP_EOL, $output);
	};
?>

<section id="box-category-tree" style="margin-bottom: 2em;" aria-label="<?php echo f::escape_attr(t('title_categories', 'Categories')); ?>">

	<ul class="pills" role="tree">
		<?php foreach ($categories as $category) echo $draw_branch($category); ?>
	</ul>

</section>
