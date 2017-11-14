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
    var now = new Date();
    var expires = new Date(now.getTime() + (365 * 24 * 60 * 60 * 1000));
    document.cookie = 'cookies_accepted=1;expires=' + expires.toUTCString();
  });
</script>