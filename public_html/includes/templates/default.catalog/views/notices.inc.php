<div id="notices">
<?php
  foreach (array_keys($notices) as $type) {
    foreach ($notices[$type] as $notice) {
      switch ($type) {
        case 'errors':
          echo '<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a>' . functions::draw_fonticon('fa-exclamation-triangle') . ' ' . $notice .'</div>' . PHP_EOL;
          break;
        case 'warnings':
          echo '<div class="alert alert-warning"><a href="#" class="close" data-dismiss="alert">&times;</a>' . functions::draw_fonticon('fa-exclamation-triangle') . ' ' . $notice .'</div>' . PHP_EOL;
          break;
        case 'notices':
          echo '<div class="alert alert-info"><a href="#" class="close" data-dismiss="alert">&times;</a>' . functions::draw_fonticon('fa-info-circle') . ' ' . $notice .'</div>' . PHP_EOL;
          break;
        case 'success':
          echo '<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert">&times;</a>' .functions::draw_fonticon('fa-check-circle') . ' ' . $notice .'</div>' . PHP_EOL;
          break;
      }
    }
  }
?>
</div>

<script>
  setTimeout(function(){$('#notices').fadeOut();}, 30000);
</script>