<?php

	document::$layout = 'ajax';

?>
<style>
#modal-customer-picker tbody > tr {
	cursor: pointer;
}
</style>

<div id="modal-customer-picker" class="modal fade" style="max-width: 720px; display: none;">

	<button class="set-guest btn btn-default btn-sm float-end" type="button"><?php echo language::translate('text_set_as_guest', 'Set As Guest'); ?></button>

	<h2 style="margin-top: 0;"><?php echo language::translate('title_customers', 'Customers'); ?></h2>

	<div class="modal-body">
		<label class="form-group">
			<div class="form-label"><?php echo functions::form_input_search('query', true, 'placeholder="'. functions::escape_attr(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?></div>
		</label>

		<div class="form-group results table-responsive">
			<table class="table data-table">
				<thead>
					<tr>
						<th><?php echo language::translate('title_id', 'ID'); ?></th>
						<th><?php echo language::translate('title_name', 'Name'); ?></th>
						<th class="main"><?php echo language::translate('title_email', 'Email'); ?></th>
						<th><?php echo language::translate('title_date_registered', 'Date Registered'); ?></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>

</div>

<script>
	$('#modal-customer-picker input[name="query"]').trigger('focus');

	var xhr_customer_picker = null;
	$('#modal-customer-picker input[name="query"]').on('input', function() {

		if ($(this).val() == '') {
			$('#modal-customer-picker .results tbody').html('');
			xhr_customer_picker = null;
			return;
		}

		xhr_customer_picker = $.ajax({
			type: 'get',
			async: true,
			cache: false,
			url: '<?php echo document::ilink('customers/customers.json'); ?>?query=' + $(this).val(),
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
			},
			success: function(json) {

				$('#modal-customer-picker .results tbody').html('');

				$.each(json, function(i, row) {

					$row = $([
						'<tr>',
						'  <td class="id">' + row.id + '</td>',
						'  <td class="name">' + row.name + '</td>',
						'  <td class="email">' + row.email + '</td>',
						'  <td class="date-created">' + row.created_at + '</td>',
						'  <td></td>',
						'</tr>'
					].join('\n'));

					$row.find('.id').text(row.id);
					$row.find('.name').text(row.name);
					$row.find('.date-created').text(row.created_at);

					$row.data(row);

					$('#modal-customer-picker .results tbody').append($row);
				});

				if ($('#modal-customer-picker .results tbody').html() == '') {
					$('#modal-customer-picker .results tbody').html([
						'<tr>',
						'	<td colspan="99">',
						'		<em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em>',
						'	</td>',
						'</tr>'
					].join('\n'));
				}
			},
		});
	});

	$('#modal-customer-picker tbody').on('click', 'td', function() {

		let $row = $(this).closest('tr'),
			callback = $.litebox.current().$currentTarget.data('callback'),
			expand = <?php echo (isset($_GET['collect']) && array_intersect(['address', 'stock_option'], $_GET['collect'])) ? 'true' : 'false'; ?>,
			customer = $row.data();

		if (!customer.id) {
			customer = {
				id: 0,
				name: '(<?php echo functions::escape_js(language::translate('title_guest', 'Guest')); ?>)',
			};
		}

		if (callback) {

			if (typeof callback == 'function') {
				callback(product);
			} else {
				window[callback](customer);
			}

		} else {
			let $field = $.litebox.current().$currentTarget.closest('.form-group');
			$field.find(':input').val(customer.id).trigger('change');
			$field.find('.id').text(customer.id);
			$field.find('.name').text(customer.name);
			$.litebox.close();
		}
	});

	$('#modal-customer-picker .set-guest').on('click', function() {

		let field = $.litebox.current().$currentTarget.closest('.form-input');

		$(field).find(':input').val('0').trigger('change');
		$(field).find('.id').text('0');
		$(field).find('.name').text('(<?php echo functions::escape_js(language::translate('title_guest', 'Guest')); ?>)');
		$.litebox.close();
	});
</script>

<?php
	require_once 'app://includes/app_footer.inc.php';
	exit;
