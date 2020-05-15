<?php
  if (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1) return;
  if (isset($_COOKIE['cookies_accepted']) && $_COOKIE['cookies_accepted'] != '') return;

  if (!settings::get('cookie_policy')) return;
?>
<div id="box-cookie-notice" class="text-center">
  <?php echo strtr(language::translate('text_cookie_notice', 'We rely on <a href="%url">cookies</a> to understand how our customers use our website.'), ['%url' => document::href_ilink('information', ['page_id' => settings::get('cookie_policy')])]); ?>
  <div class="btn-group">
    <?php echo functions::form_draw_button('accept_cookies', ['1', language::translate('text_i_accept', 'I accept')], 'button', 'style="font-weight: bold;"'); ?>
    <?php echo functions::form_draw_button('decline_cookies', ['0', language::translate('text_i_decline', 'I decline')], 'button'); ?>
  </div>
</div>

<script>
  $('button[name="accept_cookies"]').click(function(){
    $('#box-cookie-notice').fadeOut();
    var now = new Date();
    var expires = new Date(now.getTime() + (365 * 24 * 60 * 60 * 1000));
    document.cookie = 'cookies_accepted=1; Expires=' + expires.toUTCString() +'; Path=<?php echo WS_DIR_APP; ?>; SameSite=Strict';
    if (typeof window.cookiesAccepted === 'function') window.cookiesAccepted();
  });

  $('button[name="decline_cookies"]').click(function(){
    $('#box-cookie-notice').fadeOut();
    var now = new Date();
    var expires = new Date(now.getTime() + (365 * 24 * 60 * 60 * 1000));
    document.cookie = 'cookies_accepted=0; Expires=0; Path=<?php echo WS_DIR_APP; ?>; SameSite=Strict';
  });

  window.cookiesAccepted = function(){
    // Run code here for when cookies are accepted
  }

  if (document.cookie.match(/cookies_accepted=1/)) {
    window.cookiesAccepted();
  }
</script>