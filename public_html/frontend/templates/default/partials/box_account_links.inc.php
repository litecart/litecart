<?php

	$draw_menu_item = function($item) {
		return implode(PHP_EOL, [
			'<a class="nav-item'.	($item['active'] ? ' active' : '') .'" href="'. functions::escape_attr($item['link']) .'">',
			'	'. $item['title'],
			'</a>',
		]);
	};

?>
<section id="box-account">

	<h2 class="title">
		<?php echo language::translate('title_account', 'Account'); ?>
	</h2>

	<nav class="nav nav-stacked nav-pills">
		<?php foreach ($menu_items as $item) echo $draw_menu_item($item); ?>
	</nav>

</section>