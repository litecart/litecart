<div class="notices" role="status" aria-live="polite" aria-atomic="false">
<?php
	foreach (array_keys($notices) as $type) {
		foreach ($notices[$type] as $notice) {
			echo match($type) {

				'errors' => implode(PHP_EOL, [
					'<div class="notice notice-danger" role="alert">',
					'  <a href="#" class="close" data-dismiss="notice" aria-label="'. f::escape_attr(t('title_close', 'Close')) .'">&times;</a>',
					'  ' . f::draw_fonticon('icon-exclamation-triangle', 'aria-hidden="true"') . ' <span>' . $notice . '</span>',
					'</div>',
				]),

				'warnings' => implode(PHP_EOL, [
					'<div class="notice notice-warning" role="alert">',
					'  <a href="#" class="close" data-dismiss="notice" aria-label="'. f::escape_attr(t('title_close', 'Close')) .'">&times;</a>',
					'  ' . f::draw_fonticon('icon-exclamation-triangle', 'aria-hidden="true"') . ' <span>' . $notice . '</span>',
					'</div>',
				]),

				'notices' => implode(PHP_EOL, [
					'<div class="notice notice-default">',
					'  <a href="#" class="close" data-dismiss="notice" aria-label="'. f::escape_attr(t('title_close', 'Close')) .'">&times;</a>',
					'  ' . f::draw_fonticon('icon-info', 'aria-hidden="true"') . ' <span>' . $notice . '</span>',
					'</div>',
				]),

				'success' => implode(PHP_EOL, [
					'<div class="notice notice-success">',
					'  <a href="#" class="close" data-dismiss="notice" aria-label="'. f::escape_attr(t('title_close', 'Close')) .'">&times;</a>',
					'  ' . f::draw_fonticon('icon-check', 'aria-hidden="true"') . ' <span>' . $notice . '</span>',
					'</div>',
				]),
			};
		}
	}
?>
</div>

<script>
	setTimeout(function() {
		$('.notices').fadeOut('slow');
	}, 20000);
</script>