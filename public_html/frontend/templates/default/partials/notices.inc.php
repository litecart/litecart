<div id="notices">
<?php
	foreach (array_keys($notices) as $type) {
		foreach ($notices[$type] as $notice) {
			switch ($type) {

				case 'errors':
					echo implode(PHP_EOL, [
						'<div class="alert alert-danger">',
						'  <a href="#" class="close" data-dismiss="alert">&times;</a>',
						'  ' . functions::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice,
						'</div>',
					]);
					break;

				case 'warnings':
					echo implode(PHP_EOL, [
						'<div class="alert alert-warning">',
						'  <a href="#" class="close" data-dismiss="alert">&times;</a>',
						'  ' . functions::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice,
						'</div>',
					]);
					break;

				case 'notices':
					echo implode(PHP_EOL, [
						'<div class="alert alert-default">',
						'  <a href="#" class="close" data-dismiss="alert">&times;</a>',
						'  ' . functions::draw_fonticon('icon-info') . ' ' . $notice,
						'</div>',
					]);
					break;

				case 'success':
					echo implode(PHP_EOL, [
						'<div class="alert alert-success">',
						'  <a href="#" class="close" data-dismiss="alert">&times;</a>',
						'  ' . functions::draw_fonticon('icon-check') . ' ' . $notice,
						'</div>',
					]);
					break;
			}
		}
	}
?>
</div>

<script>
	setTimeout(() => {
		$('#notices').fadeOut('slow')
	}, 20000)
</script>