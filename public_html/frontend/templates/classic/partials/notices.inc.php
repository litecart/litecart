<div id="notices">
<?php
	foreach (array_keys($notices) as $type) {
		foreach ($notices[$type] as $notice) {
			switch ($type) {

				case 'errors':
					echo '<div class="alert alert-danger">' . PHP_EOL
						 . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('icon-times') .'</a>' . PHP_EOL
						 . '  ' . functions::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice . PHP_EOL
						 . '</div>';
					break;

				case 'warnings':
					echo '<div class="alert alert-warning">' . PHP_EOL
						 . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('icon-times') .'</a>' . PHP_EOL
						 . '  ' . functions::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice . PHP_EOL
						 . '</div>';
					break;

				case 'notices':
					echo '<div class="alert alert-default">' . PHP_EOL
						 . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('icon-times') .'</a>' . PHP_EOL
						 . '  ' . functions::draw_fonticon('icon-info') . ' ' . $notice . PHP_EOL
						 . '</div>';
					break;

				case 'success':
					echo '<div class="alert alert-success">' . PHP_EOL
						 . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('icon-times') .'</a>' . PHP_EOL
						 . '  ' . functions::draw_fonticon('icon-check') . ' ' . $notice . PHP_EOL
						 . '</div>';
					break;
			}
		}
	}
?>
</div>

<script>
	setTimeout(() => {
		$('#notices .alert').not('.alert-danger')
	}, 20000)

	$('#notices .alert').on('mouseout', () => {
		$(this).stop().fadeTo(15e3, 0, () => {
			$(this).slideUp('fast', () => {
				$(this).remove()
			})
		})
	}).trigger('mouseout')

	$('#notices .alert').on('mouseover', () => {
		$(this).stop().fadeTo(200, 1)
	})
</script>