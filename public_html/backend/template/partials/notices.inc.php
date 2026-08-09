<div class="notices">
<?php
	foreach (array_keys($notices) as $type) {
		foreach ($notices[$type] as $notice) {
			echo match($type) {

				'errors' => implode(PHP_EOL, [
					'<div class="notice notice-danger">',
					'  <a href="#" class="close" data-dismiss="notice">&times;</a>',
					'  ' . f::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice,
					'</div>',
				]),

				'warnings' => implode(PHP_EOL, [
					'<div class="notice notice-warning">',
					'  <a href="#" class="close" data-dismiss="notice">&times;</a>',
					'  ' . f::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice,
					'</div>',
				]),

				'notices' => implode(PHP_EOL, [
					'<div class="notice notice-default">',
					'  <a href="#" class="close" data-dismiss="notice">&times;</a>',
					'  ' . f::draw_fonticon('icon-info') . ' ' . $notice,
					'</div>',
				]),

				'success' => implode(PHP_EOL, [
					'<div class="notice notice-success">',
					'  <a href="#" class="close" data-dismiss="notice">&times;</a>',
					'  ' . f::draw_fonticon('icon-check') . ' ' . $notice,
					'</div>',
				]),

				default => implode(PHP_EOL, [
					'<div class="notice notice-default">',
					'  <a href="#" class="close" data-dismiss="notice">&times;</a>',
					'  ' . f::draw_fonticon('icon-info') . ' ' . $notice,
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
	}, 20e3);
</script>