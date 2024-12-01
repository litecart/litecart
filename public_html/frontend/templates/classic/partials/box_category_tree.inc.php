<?php
	$draw_branch = function($category, &$category_path) use (&$draw_branch) {
		return implode(PHP_EOL, [
			'<li class="category-'. $category['id'] . (!empty($category['opened']) ? ' opened' : '') . (!empty($category['active']) ? ' active' : '') .'">',
			'  <a href="'. functions::escape_attr($category['link']) .'">'. functions::draw_fonticon('icon-angle-'. (!empty($category['opened']) ? 'down' : ((language::$selected['direction'] == 'rtl') ? 'left' : 'right')) .' float-end') .' '. $category['name'] . ((settings::get('category_tree_product_count') && $category['num_products']) ? ' <small class="float-end">('. $category['num_products'] .')</small>' : '') .'</a>',
		 (!empty($category['subcategories'])) ? implode(PHP_EOL, [
				'  <ul class="nav nav-stacked">',
					array_map(function($subcategory) use ($draw_branch, $category_path) {
						return $draw_branch($subcategory, $category_path);
					}, $category['subcategories']),
				'  </ul>'
			]) : '',
			'</li>'
		]);
	};
?>

<?php if (!empty(document::$settings['compact_category_tree'])) { ?>
<style>
	#box-category-tree > ul.compact > li:not(.opened) {
		display: none;
	}
	#box-category-tree > ul.compact > li.toggle {
		display: block !important;
	}
</style>
<?php } ?>

<section id="box-category-tree" class="box">
	<div class="card-header">
		<h2 class="card-title"><?php echo $title; ?></h2>
	</div>

	<div class="card-body">
		<ul class="nav nav-stacked nav-pills<?php if (!empty(document::$settings['compact_category_tree']) && !empty($category_path)) echo ' compact'; ?>">
			<?php foreach ($categories as $category) echo $draw_branch($category, $category_path); ?>
		</ul>
	</div>
</section>

<?php if (!empty(document::$settings['compact_category_tree'])) { ?>
<script>
	$('#box-category-tree > ul.compact').prepend(
		'<li class="toggle"><a href="#" data-toggle="showall"><?php echo functions::draw_fonticon(((language::$selected['direction'] == 'rtl') ? 'icon-chevron-right' : 'icon-chevron-left') .''); ?> <?php echo language::translate('title_show_all', 'Show All'); ?></a></li>'
	)

	$('#box-category-tree > ul.compact').on('click', 'a[data-toggle="showall"]', function(e) {
		e.preventDefault()
		$(this).parent().slideUp(function() {
			$(this).remove()
		})
		$('#box-category-tree > ul > li:hidden').slideDown()
	})
</script>
<?php } ?>
