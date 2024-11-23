<?php
		//if (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1) return;
	if (isset($_COOKIE['cookies_accepted']) && $_COOKIE['cookies_accepted'] != '') return;
	if (!settings::get('cookie_policy')) return;
?>
<div id="box-cookie-notice">
	<?php echo strtr(language::translate('text_cookie_notice', 'We rely on <a href="%url">cookies</a> to understand how our customers use our website.'), ['%url' => document::href_ilink('information', ['page_id' => settings::get('cookie_policy')])]); ?>
	<div class="buttons text-center">
		<?php echo functions::form_button('accept_cookies', ['1', language::translate('text_accept_cookies', 'Accept Cookies')], 'button', 'style="font-weight: bold;"'); ?>
		<?php echo functions::form_button('reject_cookies', ['0', language::translate('text_reject', 'Reject')], 'button'); ?>
	</div>
</div>

<script>
	$('button[name="accept_cookies"]').on('click', () => {
		$('#box-cookie-notice').fadeOut()
		document.cookie = 'cookies_accepted=1; Max-Age=' + (365 * 24 * 60 * 60) +'; Path=<?php echo WS_DIR_APP; ?>; SameSite=Lax'
		$('document').trigger('cookiesAccepted')
	})

	$('button[name="reject_cookies"]').on('click', () => {
		$('#box-cookie-notice').fadeOut()
		document.cookie = 'cookies_accepted=0; Expires=0; Path=<?php echo WS_DIR_APP; ?>; SameSite=Lax'
	})

	$(document).on('cookiesAccepted', () => {
			// Run code here for when cookies are accepted
	})

	if (document.cookie.match(/cookies_accepted=1/)) {
		$(document).trigger('cookiesAccepted')
	}
</script>