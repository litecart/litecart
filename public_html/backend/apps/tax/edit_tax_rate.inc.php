<?php

	if (!empty($_GET['tax_rate_id'])) {
		$tax_rate = new ent_tax_rate($_GET['tax_rate_id']);
	} else {
		$tax_rate = new ent_tax_rate();
	}

	if (!$_POST) {
		$_POST = $tax_rate->data;
	}

	document::$title[] = !empty($tax_rate->data['id']) ? language::translate('title_edit_tax_rate', 'Edit Tax Rate') : language::translate('title_create_new_tax_rate', 'Create New Tax Rate');

	breadcrumbs::add(language::translate('title_tax_rates', 'Tax Rates'), document::ilink(__APP__.'/tax_rates'));
	breadcrumbs::add(!empty($tax_rate->data['id']) ? language::translate('title_edit_tax_rate', 'Edit Tax Rate') : language::translate('title_create_new_tax_rate', 'Create New Tax Rate'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(language::translate('error_must_provide_name', 'You must provide a name'));
			}

			if (empty($_POST['geo_zone_id'])) {
				throw new Exception(language::translate('error_must_select_geo_zone', 'You must select a geo zone'));
			}

			if (empty($_POST['tax_class_id'])) {
				throw new Exception(language::translate('error_must_select_tax_class', 'You must select a tax class'));
			}

			if (empty($_POST['rate'])) {
				throw new Exception(language::translate('error_must_provide_rate', 'You must provide a rate'));
			}

			if (empty($_POST['rule_companies_with_tax_id'])) {
				$_POST['rule_companies_with_tax_id'] = 0;
			}

			if (empty($_POST['rule_companies_without_tax_id'])) {
				$_POST['rule_companies_without_tax_id'] = 0;
			}

			if (empty($_POST['rule_individuals_with_tax_id'])) {
				$_POST['rule_individuals_with_tax_id'] = 0;
			}

			if (empty($_POST['rule_individuals_without_tax_id'])) {
				$_POST['rule_individuals_without_tax_id'] = 0;
			}

			foreach ([
				'tax_class_id',
				'geo_zone_id',
				'code',
				'name',
				'description',
				'rate',
				'address_type',
				'rule_companies_with_tax_id',
				'rule_companies_without_tax_id',
				'rule_individuals_with_tax_id',
				'rule_individuals_without_tax_id',
			] as $field) {
				if (isset($_POST[$field])) {
					$tax_rate->data[$field] = $_POST[$field];
				}
			}

			$tax_rate->save();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/tax_rates'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($tax_rate->data['id'])) {
				throw new Exception(language::translate('error_must_provide_tax_rate', 'You must provide a tax rate'));
			}

			$tax_rate->delete();

			notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/tax_rates'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($tax_rate->data['id']) ? language::translate('title_edit_tax_rate', 'Edit Tax Rate') : language::translate('title_create_new_tax_rate', 'Create New Tax Rate'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('tax_rate_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_code', 'Code'); ?></div>
						<?php echo functions::form_input_text('code', true); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true); ?>
					</label>
				</div>
			</div>

			<label class="form-group">
				<div class="form-label"><?php echo language::translate('title_description', 'Description'); ?></div>
				<?php echo functions::form_input_text('description', true); ?>
			</label>

			<div class="grid">
				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_tax_class', 'Tax Class'); ?></div>
						<?php echo functions::form_select_tax_class('tax_class_id', true); ?>
					</label>
				</div>

				<div class="col-md-5">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_geo_zone', 'Geo Zone'); ?></div>
						<?php echo functions::form_select_geo_zone('geo_zone_id', true); ?>
					</label>
				</div>

				<div class="col-md-3">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_rate', 'Rate'); ?></div>
						<div class="input-group">
							<?php echo functions::form_input_decimal('rate', true, 4); ?>
							<span class="input-group-text">%</span>
						</div>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-5">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_address_type', 'Address Type'); ?></div>
						<div>
							<?php echo functions::form_radio_button('address_type', ['shipping', language::translate('title_shipping_address', 'Shipping Address')], true); ?>
							<?php echo functions::form_radio_button('address_type', ['payment', language::translate('title_payment_address', 'Payment Address')], true); ?>
						</div>
					</label>
				</div>

				<div class="col-md-7">
					<label class="form-group">
						<div class="form-label"><?php echo language::translate('title_conditions', 'Conditions'); ?></div>
						<div class="radio">
							<label><?php echo functions::form_checkbox('rule_companies_with_tax_id', ['1', language::translate('text_applies_to_companies_with_tax_id', 'Applies to companies with a tax ID')], true); ?></label>
						</div>
						<div class="radio">
							<label><?php echo functions::form_checkbox('rule_companies_without_tax_id', ['1', language::translate('rule_applies_to_companies_without_tax_id', 'Applies to companies without a tax ID')], true); ?></label>
						</div>
						<div class="radio">
							<label><?php echo functions::form_checkbox('rule_individuals_with_tax_id', ['1', language::translate('text_applies_to_individuals_with_tax_id', 'Applies to individuals with a tax ID')], true); ?></label>
						</div>
						<div class="radio">
							<label><?php echo functions::form_checkbox('rule_individuals_without_tax_id', ['1', language::translate('rule_applies_to_individuals_without_tax_id', 'Applies to individuals without a tax ID')], true); ?></label>
						</div>
					</label>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if ($tax_rate->data['id']) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>
