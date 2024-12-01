<div id="notices" class="alerts">
<?php
	foreach (array_keys($notices) as $type) {
		foreach ($notices[$type] as $notice) {
			switch ($type) {

				case 'errors':
					echo '<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a>' . functions::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice .'</div>' . PHP_EOL;
					break;

				case 'warnings':
					echo '<div class="alert alert-warning"><a href="#" class="close" data-dismiss="alert">&times;</a>' . functions::draw_fonticon('icon-exclamation-triangle') . ' ' . $notice .'</div>' . PHP_EOL;
					break;

				case 'notices':
					echo '<div class="alert alert-default"><a href="#" class="close" data-dismiss="alert">&times;</a>' . functions::draw_fonticon('icon-info') . ' ' . $notice .'</div>' . PHP_EOL;
					break;

				case 'success':
					echo '<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert">&times;</a>' .functions::draw_fonticon('icon-check') . ' ' . $notice .'</div>' . PHP_EOL;
					break;
			}
		}
	}
?>
</div>

<script>
	setTimeout(function() {
		$('#notices').fadeOut('slow')
	}, 20e3)
</script>
