<?php
	$draw_branch = function($category) use (&$draw_branch, &$trail) {

		$output = [
			'<li class="category-'. $category['id'] .'">',
			'  <a class="nav-item'. (!empty($category['opened']) ? ' opened' : '') . (!empty($category['active']) ? ' active' : '') .'" href="'. functions::escape_attr($category['link']) .'">'. functions::draw_fonticon(!empty($category['opened']) ? 'fa-angle-down fa-fw float-end' : 'fa-angle-right fa-fw float-end') .' '. $category['name'] . ((settings::get('category_tree_product_count') && $category['num_products']) ? ' <small class="float-end">('. $category['num_products'] .')</small>' : '') .'</a>',
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

<section id="box-category-tree" class="hidden-xs">

	<ul class="nav nav-stacked nav-pills">
		<li><a class="nav-item" href="<?php echo functions::escape_html($backlink); ?>"><?php echo functions::draw_fonticon('fa-angle-left fa-fw'); ?> <?php echo language::translate('title_back', 'Back'); ?></a></li>
		<?php foreach ($categories as $category) echo $draw_branch($category); ?>
	</ul>

</section>
