<main id="main" class="container">
	<div id="content" class="container">
		{{notices}}

		<?php echo functions::form_begin('checkout_form', 'post', '', false, 'autocomplete="off"'); ?>

		<section id="box-checkout">
			<div class="cart wrapper"></div>

			<div class="row" style="grid-gap: 2rem;">
				<div class="col-md-6">
					<div class="customer wrapper"></div>
				</div>

				<div class="col-md-6">
					<div class="shipping wrapper"></div>

					<div class="payment wrapper"></div>
				</div>
			</div>

			<div class="summary wrapper"></div>
		</section>

		<?php echo functions::form_end(); ?>
	</div>
</main>

<script>
	// Queue Handler

	window.updateQueue = [
		{component: 'cart',     data: null, refresh: true},
		{component: 'customer', data: null, refresh: true},
		{component: 'shipping', data: null, refresh: true},
		{component: 'payment',  data: null, refresh: true},
		{component: 'summary',  data: null, refresh: true}
	]

	window.queueUpdateTask = function(component, data, refresh) {

		updateQueue = jQuery.grep(updateQueue, function(tasks) {
			return (tasks.component == component) ? false : true
		})

		updateQueue.push({
			component: component,
			data: data,
			refresh: refresh
		})

		window.runQueue()
	}

	let queueRunLock = false
	window.runQueue = function() {

		if (queueRunLock) return
		if (!updateQueue.length) return

		queueRunLock = true

		task = updateQueue.shift()

		if (console) console.log('Processing ' + task.component)

		if (!$('body > .loader-wrapper').length) {

			let loader = [
				'<div class="loader-wrapper">'
				'  <div class="loader" style="width: 256px; height: 256px;"></div>',
				'</div>'
			].join('\n')

			$('body').append(loader)
		}

		if (task.refresh) {
			$('#box-checkout .'+ task.component +'.wrapper').fadeTo('fast', 0.15)
		}

		let url = ''
		switch (task.component) {
			case 'cart':
				url = '<?php echo document::ilink('checkout/cart'); ?>'
				break
			case 'customer':
				url = '<?php echo document::ilink('checkout/customer'); ?>'
				break
			case 'shipping':
				url = '<?php echo document::ilink('checkout/shipping'); ?>'
				break
			case 'payment':
				url = '<?php echo document::ilink('checkout/payment'); ?>'
				break
			case 'summary':
				url = '<?php echo document::ilink('checkout/summary'); ?>'
				break
			default:
				alert('Error: Invalid component ' + task.component)
				break
		}

		if (task.data === true) {
			switch (task.component) {
				case 'customer':
					task.data = $('#box-checkout-customer :input').serialize()
					break
				case 'shipping':
					task.data = $('#box-checkout-shipping .option.active :input').serialize()
					break
				case 'payment':
					task.data = $('#box-checkout-payment .option.active :input').serialize()
					break
				case 'summary':
					task.data = $('#box-checkout-summary :input').serialize()
					break
			}
		}

		if (task.component == 'summary') {
			let comments = $(':input[name="comments"]').val()
			let terms_agreed = $(':input[name="terms_agreed"]').prop('checked')
		}

		$.ajax({
			type: task.data ? 'post' : 'get',
			url: url,
			data: task.data,
			dataType: 'html',
			beforeSend: (jqXHR) => {
				jqXHR.overrideMimeType('text/html;charset=<?php echo mb_http_output(); ?>')
			},
			error: (jqXHR, textStatus, errorThrown) => {
				$('#box-checkout .'+ task.component +'.wrapper').html('An unexpected error occurred, try reloading the page.')
			},
			success: (html) => {
				if (task.refresh) $('#box-checkout .'+ task.component +'.wrapper').html(html).fadeTo('fast', 1)
				if (task.component == 'summary') {
					$(':input[name="comments"]').val(comments)
					$(':input[name="terms_agreed"]').prop('checked', terms_agreed)
				}
			},
			complete: (html) => {
				if (!updateQueue.length) {
					$('body > .loader-wrapper').fadeOut('fast', () => {
						$(this).remove()
					})
				}
				queueRunLock = false
				window.runQueue()
			}
		})
	}

	window.runQueue()

	// Customer Form: Process Data

	$('#box-checkout .customer.wrapper').on('click', 'button[name="save_customer_details"]', function(e){
		e.preventDefault()
		let data = [
			$('#box-checkout-customer :input').serialize(),
			'save_customer_details=true'
		].join('&')
		window.queueUpdateTask('customer', data, true)
		window.queueUpdateTask('cart', null, true)
		window.queueUpdateTask('shipping', true, true)
		window.queueUpdateTask('payment', true, true)
		window.queueUpdateTask('summary', null, true)
		window.customer_form_checksum = $('#box-checkout-customer :input').serialize()
		$('#box-checkout-customer :input:first-child').trigger('change')
	})

	// Shipping Form: Process Data

	$('#box-checkout .shipping.wrapper').on('click', '.option:not(.active):not(.disabled)', () => {
		$('#box-checkout-shipping .option').removeClass('active')
		$(this).find('input[name="shipping_address[option_id]"]').prop('checked', true).trigger('change')
		$(this).addClass('active')

		$('#box-checkout-shipping .option.active :input').prop('disabled', false)
		$('#box-checkout-shipping .option:not(.active) :input').prop('disabled', true)

		let data = $('#box-checkout-shipping .option.active :input').serialize()
		window.queueUpdateTask('shipping', data, false)
		window.queueUpdateTask('payment', true, true)
		window.queueUpdateTask('summary', null, true)
	})

	// Payment Form: Process Data

	$('#box-checkout .payment.wrapper').on('click', '.option:not(.active):not(.disabled)', () => {
		$('#box-checkout-payment .option').removeClass('active')
		$(this).find('input[name="payment[option_id]"]').prop('checked', true).trigger('change')
		$(this).addClass('active')

		$('#box-checkout-payment .option.active :input').prop('disabled', false)
		$('#box-checkout-payment .option:not(.active) :input').prop('disabled', true)

		let data = $('#box-checkout-payment .option.active :input').serialize()
		window.queueUpdateTask('payment', data, false)
		window.queueUpdateTask('summary', null, true)
	})

	// Summary Form: Process Data

	$('form[name="checkout_form"]').submit(function(e) {

		if (window.customer_form_changed) {
			e.preventDefault()
			alert("<?php echo language::translate('warning_your_customer_information_unsaved', 'Your customer information contains unsaved changes.')?>")
		}

		let dummy_button = '<div class="btn btn-block btn-default btn-lg disabled"><?php echo functions::draw_fonticon('icon-spinner'); ?> <?php echo functions::escape_js(language::translate('text_please_wait', 'Please wait')); ?>&hellip;</div>'
		$('#box-checkout-summary button[name="confirm_order"]').css('display', 'none').before(dummy_button)
	})
</script>