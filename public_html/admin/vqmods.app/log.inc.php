<?php
  $log_file = FS_DIR_HTTP_ROOT . WS_DIR_HTTP_HOME .vqmod::$logFolder . date('w_D') .'.log';
  
  if (!empty($_POST['clear'])) {
    file_put_contents($log_file, '');
    header('Location: '. $_SERVER['REQUEST_URI']);
    exit;
  }
?>
<div style="float: right;"><?php echo functions::form_draw_form_begin() . functions::form_draw_button('clear', language::translate('title_clear_log', 'Clear Log'), 'submit', 'onclick="'. htmlspecialchars('if(!confirm("'. language::translate('text_are_you_sure', 'Are you sure?') .'")) return false;') .'"') . functions::form_draw_form_end(); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_log', 'Log'); ?></h1>

<pre><?php  if (is_file($log_file)) echo file_get_contents($log_file); ?></pre>
