<?php

	if (isset($_GET['review_id'])) {
		$review = new ent_review($_GET['review_id']);
	} else {
		$review = new ent_review();
	}

	if (!$_POST) {
		$_POST = $review->data;
	}

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['rating'])) {
				throw new Exception(t('error_missing_rating', 'You must enter a rating'));
			}

			if (empty($_POST['description'])) {
				throw new Exception(t('error_missing_review', 'You must enter a review'));
			}

			if (empty($_POST['product_id'])) {
				throw new Exception(t('error_missing_product', 'You must enter a product'));
			}

			if (empty($_POST['customer_name'])) {
				throw new Exception(t('error_missing_name', 'You must enter a name'));
			}

			if (!isset($_POST['status'])) $_POST['status'] = '0';
			if (empty($_POST['attachments'])) $_POST['attachments'] = [];

			foreach ([
				'status',
				'product_id',
				'customer_id',
				'customer_email',
				'customer_name',
				'title',
				'description',
				'rating',
				'upvotes',
				'downvotes',
				'attachments',
			] as $field) {
				if (isset($_POST[$field])) {
					$review->data[$field] = $_POST[$field];
				}
			}

			if (!empty($_FILES['new_attachments']['tmp_name'])) {
				foreach (array_keys($_FILES['new_attachments']['tmp_name']) as $key) {
					$review->add_attachment($_FILES['new_attachments']['tmp_name'][$key], $_FILES['new_attachments']['name'][$key], $_FILES['new_attachments']['type'][$key]);
				}
			}

			$review->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/reviews'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete']) && $review) {

		$review->delete();

		notices::add('success', t('success_post_deleted', 'Post deleted'));
		redirect(document::ilink(__APP__.'/reviews'));
		exit;
	}

	f::draw_lightbox();

	$account_name = '('. t('title_guest', 'Guest') .')';
	if (!empty($_POST['customer_id'])) {
		$customer = reference::customer((int)$_POST['customer_id']);
		$account_name = $customer->company ? $customer->company : $customer->firstname .' '. $customer->lastname;
	}

?>
<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo (empty($review->data['id'])) ? t('title_create_new_review', 'Create New Review') : t('title_edit_review', 'Edit Review'); ?>
		</div>
	</div>

	<div class="card-body">

		<?php echo f::form_begin(false, 'post', false, true, 'style="max-width: 640px;"'); ?>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_status', 'Status'); ?></label>
						<?php echo f::form_toggle('status', true, 'e/d'); ?>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_rating', 'Rating'); ?></label>
						<?php echo f::form_input_number('rating', true, 1, 5); ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label><?php echo t('title_product', 'product'); ?></label>
				<?php echo f::form_select_product('product_id', true); ?>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_customer', 'Customer'); ?></label>
						<?php echo f::form_input_hidden('customer_id', true); ?>
						<div class="selected-account form-control disabled"><?php echo t('title_id', 'ID'); ?>: <span class="id"><?php echo @(int)$_POST['customer_id']; ?></span> &mdash; <span class="name"><?php echo $account_name; ?></span> <a href="#modal-customer-picker" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-left: 5px;"><?php echo t('title_change', 'Change'); ?></a></div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_email', 'Email'); ?></label>
						<?php echo f::form_input_email('customer_email', true); ?>
					</div>
				</div>

				<div class="col-md-12">
					<div class="form-group">
						<label><?php echo t('title_name', 'Name'); ?></label>
						<?php echo f::form_input_text('customer_name', true); ?>
					</div>
				</div>
			</div>

			<nav class="nav nav-tabs">
				<?php foreach (language::$languages as $language) { ?>
				<a class="nav-link<?php echo ($language['code'] == language::$selected['code']) ? ' active' : ''; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
				<?php } ?>
			</nav>

			<div class="tab-contents">
				<?php foreach (array_keys(language::$languages) as $language_code) { ?>
				<div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">
					<div class="form-group">
						<label><?php echo t('title_title', 'Title'); ?></label>
						<?php echo f::form_regional_text($language_code, 'title['. $language_code .']', true, ''); ?>
					</div>

					<div class="form-group">
						<label><?php echo t('title_review', 'Review'); ?></label>
						<?php echo f::form_regional_textarea($language_code, 'review['. $language_code .']', true, 'style="height: 250px;"'); ?>
					</div>
				</div>
				<?php } ?>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_upvotes', 'Upvotes'); ?></label>
						<?php echo f::form_input_number('upvotes', true, 0); ?>
					</div>
				</div>

				<div class="col-md-6">
					<div class="form-group">
						<label><?php echo t('title_downvotes', 'Downvotes'); ?></label>
						<?php echo f::form_input_number('downvotes', true, 0); ?>
					</div>
				</div>
			</div>

			<div id="attachments">

				<div class="attachments">
					<?php if (!empty($_POST['attachments'])) foreach (array_keys($_POST['attachments']) as $key) { ?>
					<div class="attachment form-group">
						<?php echo f::form_input_hidden('attachments['.$key.'][id]', true); ?>
						<?php echo f::form_input_hidden('attachments['.$key.'][filename]', $_POST['attachments'][$key]['filename']); ?>

						<div class="input-group">
							<?php echo f::form_input_text('attachments['.$key.'][new_filename]', isset($_POST['attachments'][$key]['new_filename']) ? true : $_POST['attachments'][$key]['filename']); ?>
							<div class="input-group-text">
								<a class="move-up" href="#" title="<?php echo t('text_move_up', 'Move up'); ?>"><?php echo f::draw_fonticon('fa-arrow-up fa-lg', 'style="color: #3399cc;"'); ?></a>
								<a class="move-down" href="#" title="<?php echo t('text_move_down', 'Move down'); ?>"><?php echo f::draw_fonticon('fa-arrow-down fa-lg', 'style="color: #3399cc;"'); ?></a>
								<a class="remove" href="#" title="<?php echo t('title_remove', 'Remove'); ?>"><?php echo f::draw_fonticon('fa-times fa-lg', 'style="color: #cc3333;"'); ?></a>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>

				<div class="new-attachments">
					<div class="attachment form-group">
						<label><?php echo t('title_attachment', 'Attachment'); ?></label>
						<div class="input-group">
							<?php echo f::form_input_file('new_attachments[]'); ?>
							<div class="input-group-text">
								<a class="remove" href="#" title="<?php echo t('title_remove', 'Remove'); ?>"><?php echo f::draw_fonticon('fa-times fa-lg', 'style="color: #cc3333;"'); ?></a>
							</div>
						</div>
					</div>
				</div>

				<div class="form-group">
					<a href="#" class="add" title="<?php echo t('text_add', 'Add'); ?>"><?php echo f::draw_fonticon('fa-plus', 'style="color: #66cc66;"'); ?></a>
				</div>
			</div>

			<div class="card-action">
				<?php echo f::form_button('save', t('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
				<?php echo !empty($review->data['id']) ? f::form_button('delete', t('title_delete', 'Delete'), 'submit', 'class="btn btn-danger" onclick="if (!confirm(&quot;'. t('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : ''; ?>
				<?php echo f::form_button('cancel', t('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
			</div>

		<?php echo f::form_end(); ?>
	</div>
</div>

<div id="modal-customer-picker" class="modal fade" style="max-width: 640px; display: none;">

	<h2><?php echo t('title_customer', 'Customer'); ?></h2>

	<div class="modal-body">
		<div class="form-group">
			<?php echo f::form_input_text('query', true, 'placeholder="'. f::escape_html(t('title_search', 'Search')) .'"'); ?>
		</div>

		<div class="form-group results table-responsive">
			<table class="table table-striped table-hover data-table">
				<thead>
					<tr>
						<th><?php echo t('title_id', 'ID'); ?></th>
						<th><?php echo t('title_name', 'Name'); ?></th>
						<th class="main"><?php echo t('title_email', 'Email'); ?></th>
						<th><?php echo t('title_date_registered', 'Date Registered'); ?></th>
					</tr>
				</thead>
				<tbody>
			</table>

			<p class="text-center"><button class="set-guest btn btn-default" type="button"><?php echo t('text_set_as_guest', 'Set As Guest'); ?></button></p>
		</div>
	</div>

</div>

<script>
	var xhr_customer_picker = null;
	$('#modal-customer-picker input[name="query"]').bind('input', function(){
		if ($(this).val() == '') {
			$('#modal-customer-picker .results tbody').html('');
			xhr_customer_picker = null;
			return;
		}
		xhr_customer_picker = $.ajax({
			type: 'get',
			async: true,
			cache: false,
			url: '<?php echo document::link('', ['app' => 'customers', 'doc' => 'customers.json']); ?>&query=' + $(this).val(),
			dataType: 'json',
			beforeSend: function(jqXHR) {
				jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error(textStatus + ': ' + errorThrown);
			},
			success: function(json) {
				$('#modal-customer-picker .results tbody').html('');
				$.each(json, function(i, row){
					if (row) {
						$('#modal-customer-picker .results tbody').append(
							'<tr>' +
							'  <td class="id">' + row.id + '</td>' +
							'  <td class="name">' + row.name + '</td>' +
							'  <td class="email">' + row.email + '</td>' +
							'  <td class="date-created">' + row.date_created + '</td>' +
							'  <td></td>' +
							'</tr>'
						);
					}
				});
				if ($('#modal-customer-picker .results tbody').html() == '') {
					$('#modal-customer-picker .results tbody').html('<tr><td colspan="4"><em><?php echo f::escape_js(t('text_no_results', 'No results')); ?></em></td></tr>');
				}
			},
		});
	});

	$('#modal-customer-picker tbody').on('click', 'td', function() {

		var $row = $(this).closest('tr'),
			id = $row.find('.id').text(),
			name = $row.find('.name').text();

		if (!id) {
			id = 0;
			name = '(<?php echo f::escape_js(t('title_guest', 'Guest')); ?>)';
		}

		$('input[name="customer_id"]').val(id).trigger('change');
		$('.selected-account .id').text(id);
		$('.selected-account .name').text(name);
		$.featherlight.close();
	});

	$('#modal-customer-picker .set-guest').click(function(){
		$('input[name="customer[id]"]').val('0');
		$('.selected-account .id').text('0');
		$('.selected-account .name').text('(<?php echo f::escape_js(t('title_guest', 'Guest')); ?>)');
		$.featherlight.close();
	});

	$(':input[name^="title"]').on('input', function(){
		var language_code = $(this).attr('name').match(/\[([a-z]{2})\]$/)[1];
		if ($(this).val().trim() != '') {
			$('[data-toggle="tab"][href="#'+ language_code +'"]').css('font-weight', '600');
		} else {
			$('[data-toggle="tab"][href="#'+ language_code +'"]').css('font-weight', 'normal');
		}
	}).trigger('input');

	$('#attachments').on('click', '.move-up, .move-down', function(e) {
		e.preventDefault();

		var $row = $(this).closest('.form-group');

		if ($(this).is('.move-up') && $row.prevAll().length > 0) {
			$row.insertBefore(row.prev());
		} else if ($(this).is('.move-down') && $row.nextAll().length > 0) {
			$row.insertAfter($row.next());
		}

		refreshMainImage();
	});

	$('#attachments').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('.form-group').remove();
	});

	$('#attachments .add').click(function(e) {
		e.preventDefault();

		var $output = $([
			'<div class="attachment form-group">'
			'  <label><?php echo f::escape_js(t('title_attachment', 'Attachment')); ?></label>'
			'  <div class="input-group">'
			'    <?php echo f::escape_js(f::form_input_file('new_attachments[]')); ?>',
			'    <div class="input-group-text">',
			'      <a class="remove" href="#" title="<?php echo f::escape_js(t('title_remove', 'Remove')); ?>"><?php echo f::escape_js(f::draw_fonticon('fa-times fa-lg', 'style="color: #cc3333;'), ''); ?></a>',
			'    </div>',
			'  </div>',
			'</div>',
		].join(''));

		$('#attachments .new-attachments').append($output);
	});
</script>
