<?php
  if (empty(document::$settings['cookie_acceptance'])) return;
  if (!empty($_COOKIE['cookies_accepted'])) return;
?>
<div id="cookies-acceptance" class="text-center">
  <?php echo language::translate('terms_cookies_acceptance', 'We rely on cookies to provide our services. By using our services, you agree to our use of cookies.'); ?> <?php echo functions::form_draw_button('accept_cookies', language::translate('title_ok', 'OK'), 'button'); ?>
</div>

<script>
  $('button[name="accept_cookies"]').click(function(){
    $('#cookies-acceptance').fadeOut();
    $.cookie('cookies_accepted', '1', {path: '<?php echo WS_DIR_HTTP_HOME; ?>', expires: 365});
  });
</script>