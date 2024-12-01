<?php if (!isset($_COOKIE['cookies_accepted'])) { ?>
<div id="box-cookie-notice">
	<div class="container">
		<div class="notice">
			<?php echo strtr(language::translate('text_cookie_notice', 'We rely on <a href="%url">cookies</a> for storing your shopping cart and regional settings between visits and to understand how our customers use our website.'), ['%url' => document::href_ilink('information', ['page_id' => settings::get('cookie_policy')])]); ?>
		</div>

		<div class="buttons text-center">
			<?php echo functions::form_button('accept_cookies', ['1', language::translate('text_accept_cookies', 'Accept Cookies')], 'button', 'style="font-weight: bold;"'); ?>
			<?php echo functions::form_button('reject_cookies', ['0', language::translate('text_reject', 'Reject')], 'button'); ?>
		</div>
	</div>
</div>
<?php } ?>

<script>
	<?php if (!isset($_COOKIE['cookies_accepted'])) { ?>
	$('#box-cookie-notice button[name="accept_cookies"]').on('click', function() {
		$('#box-cookie-notice').fadeOut()
		document.cookie = 'cookies_accepted=1; Max-Age=' + (365 * 24 * 60 * 60 * 1000) +'; Path=<?php echo WS_DIR_APP; ?>; SameSite=Lax'
		$('document').trigger('cookiesAccepted')
	})

	$('#box-cookie-notice button[name="reject_cookies"]').on('click', function() {
		$('#box-cookie-notice').fadeOut()
		document.cookie = 'cookies_accepted=0; Expires=0; Path=<?php echo WS_DIR_APP; ?>; SameSite=Lax'
	})
	<?php } ?>

	$(document).on('cookiesAccepted', function() {
			// Run code here for when cookies are accepted
	})

	if (document.cookie.match(/cookies_accepted=1/)) {
		$(document).trigger('cookiesAccepted')
	}
</script>