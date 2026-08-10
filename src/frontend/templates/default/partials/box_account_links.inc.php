<?php

	$draw_menu_item = function($item) {
		return implode(PHP_EOL, [
			'<a class="pill-item'.	($item['active'] ? ' active' : '') .'" href="'. f::escape_attr($item['link']) .'"'. ($item['active'] ? ' aria-current="page"' : '') .'>',
			'	'. $item['title'],
			'</a>',
		]);
	};

?>
<section id="box-account" aria-label="<?php echo f::escape_attr(t('title_account', 'Account')); ?>">

	<h2 class="title">
		<?php echo t('title_account', 'Account'); ?>
	</h2>

	<nav class="pills" aria-label="<?php echo f::escape_attr(t('title_account_navigation', 'Account Navigation')); ?>">
		<?php foreach ($menu_items as $item) echo $draw_menu_item($item); ?>
	</nav>

</section>