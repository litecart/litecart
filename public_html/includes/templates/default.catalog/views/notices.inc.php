<div id="notices">
<?php
  foreach (array_keys($notices) as $type) {
    foreach ($notices[$type] as $notice) {
      switch ($type) {

        case 'errors':
          echo '<div class="alert alert-danger">' . PHP_EOL
             . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('fa-times') .'</a>' . PHP_EOL
             . '  ' . functions::draw_fonticon('fa-exclamation-triangle') . ' ' . $notice . PHP_EOL
             . '</div>';
          break;

        case 'warnings':
          echo '<div class="alert alert-warning">' . PHP_EOL
             . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('fa-times') .'</a>' . PHP_EOL
             . '  ' . functions::draw_fonticon('fa-exclamation-triangle') . ' ' . $notice . PHP_EOL
             . '</div>';
          break;

        case 'notices':
          echo '<div class="alert alert-info">' . PHP_EOL
             . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('fa-times') .'</a>' . PHP_EOL
             . '  ' . functions::draw_fonticon('fa-info-circle') . ' ' . $notice . PHP_EOL
             . '</div>';
          break;

        case 'success':
          echo '<div class="alert alert-success">' . PHP_EOL
             . '  <a href="#" class="close" data-dismiss="alert">'. functions::draw_fonticon('fa-times') .'</a>' . PHP_EOL
             . '  ' . functions::draw_fonticon('fa-check-circle') . ' ' . $notice . PHP_EOL
             . '</div>';
          break;
      }
    }
  }
?>
</div>

<script>
  setTimeout(function(){
    $('#notices .alert').not('.alert-danger').slideUp('fast');
  }, 20000);

  $('#notices .alert-danger').on('mouseout', function() {
    $(this).stop().fadeTo(15000, 0.15);
  }).trigger('mouseout');

  $('#notices .alert-danger').on('mouseover', function() {
    $(this).stop().fadeTo(200, 1);
  });
</script>