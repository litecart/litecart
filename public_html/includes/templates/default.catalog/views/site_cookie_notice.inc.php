<?php
  if (empty(document::$settings['cookie_acceptance'])) return;
  if (isset($_COOKIE['cookies_accepted']) && $_COOKIE['cookies_accepted'] != '') return;
?>
<div id="cookies-acceptance" class="text-center">
  <?php echo language::translate('terms_cookies_acceptance', 'We rely on cookies to provide our services. By using our services, you agree to our use of cookies.'); ?>
  <div class="btn-group">
    <?php echo functions::form_draw_button('accept_cookies', array('1', language::translate('text_i_accept', 'I accept')), 'button', 'style="font-weight: bold;"'); ?>
    <?php echo functions::form_draw_button('decline_cookies', array('0', language::translate('text_i_decline', 'I decline')), 'button'); ?>
  </div>
</div>

<script>
  $('button[name="accept_cookies"]').click(function(){
    $('#cookies-acceptance').fadeOut();
    var now = new Date();
    var expires = new Date(now.getTime() + (365 * 24 * 60 * 60 * 1000));
    document.cookie = 'cookies_accepted=1;expires=' + expires.toUTCString();
  });

  $('button[name="decline_cookies"]').click(function(){
    $('#cookies-acceptance').fadeOut();
    var now = new Date();
    var expires = new Date(now.getTime() + (365 * 24 * 60 * 60 * 1000));
    document.cookie = 'cookies_accepted=0;expires=0';
  });
</script>