<?php

	if (!empty($_GET['product_id'])) {
		$product = new ent_product($_GET['product_id']);
	} else {
		$product = new ent_product();
	}

	if (!$_POST) {
		$_POST = $product->data;

		if (empty($product->data['id']) && isset($_GET['category_id'])) {
			$_POST['categories'][] = $_GET['category_id'];
		}
	}

	document::$title[] = !empty($product->data['id']) ? t('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : t('title_create_new_product', 'Create New Product');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_products', 'Products'), document::ilink(__APP__.'/products'));
	breadcrumbs::add(!empty($product->data['id']) ? t('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : t('title_create_new_product', 'Create New Product'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (!empty($_POST['code'])) {
				if (database::query(
					"select id from ". DB_TABLE_PREFIX ."products
					where id != ". (int)$product->data['id'] ."
					and code = '". database::input($_POST['code']) ."'
					limit 1;"
				)->num_rows) {
					throw new Exception(t('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
				}
			}

			if (!empty($_FILES['new_images']['tmp_name'])) {
				foreach (array_keys($_FILES['new_images']['tmp_name']) as $key) {
					if (!empty($_FILES['new_images']['tmp_name'][$key]) && !empty($_FILES['new_images']['error'][$key])) {
						throw new Exception(t('error_uploaded_image_rejected', 'An uploaded image was rejected').': '. print_r($_FILES['new_images']['error'][$key], true));
					}
				}
			}

			if (empty($_POST['autofill_technical_data'])) {
				$_POST['autofill_technical_data'] = '';
			}

			if (empty($_POST['categories'])) $_POST['categories'] = [];
			if (empty($_POST['images'])) $_POST['images'] = [];
			if (empty($_POST['campaigns'])) $_POST['campaigns'] = [];
			if (empty($_POST['attributes'])) $_POST['attributes'] = [];
			if (empty($_POST['customizations'])) $_POST['customizations'] = [];
			if (empty($_POST['stock_options'])) $_POST['stock_options'] = [];

			foreach ([
				'status',
				'featured',
				'brand_id',
				'delivery_status_id',
				'sold_out_status_id',
				'default_category_id',
				'categories',
				'code',
				'name',
				'short_description',
				'description',
				'technical_data',
				'autofill_technical_data',
				'head_title',
				'meta_description',
				'synonyms',
				'keywords',
				'attributes',
				'prices',
				'campaigns',
				'tax_class_id',
				'recommended_price',
				'images',
				'customizations',
				'stock_option_type',
				'stock_options',
				'quantity_adjustment',
				'quantity_min',
				'quantity_max',
				'quantity_step',
				'quantity_unit_id',
				'valid_from',
				'valid_to',
			] as $field) {
				if (isset($_POST[$field])) {
					$product->data[$field] = $_POST[$field];
				}
			}

			if (!empty($_FILES['new_images']['tmp_name'])) {
				foreach (array_keys($_FILES['new_images']['tmp_name']) as $key) {
					if (is_uploaded_file($_FILES['new_images']['tmp_name'][$key])) {
						$product->add_image($_FILES['new_images']['tmp_name'][$key]);
					}
				}
			}

			$product->save();

			if (!empty($_GET['redirect_url'])) {
				$_GET['redirect_url'] = new ent_link($_GET['redirect_url']);
				$_GET['redirect_url']->host = '';
			} else {
				$_GET['redirect_url'] = document::ilink(__APP__.'/category_tree', ['category_id' => isset($_POST['categories'][0]) ? $_POST['categories'][0] : '']);
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect($_GET['redirect_url']);
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($product->data['id'])) {
				throw new Exception(t('error_must_provide_product', 'You must provide a product'));
			}

			$product->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/category_tree', ['category_id' => $_POST['categories'][0]]));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$language_codes = array_unique(array_merge([language::$selected['code']], [settings::get('store_language_code')], array_keys(language::$languages)));
	$currency_codes = array_unique(array_merge([currency::$selected['code']], [settings::get('store_currency_code')], array_keys(currency::$currencies)));

	$customizations_sort_options = [
		[t('title_list_order', 'List Order'), 'priority'],
		[t('title_alphabetical', 'Alphabetical'), 'alphabetical'],
	];

?>
<style>
#categories {
	max-height: 310px;
	overflow-y: auto;
	overflow-x: hidden;
	transition: all 200ms linear;
}
#categories:hover {
	width: 150%;
	z-index: 999;
}
#categories label {
	white-space: nowrap;
}

#images {
	margin-bottom: 1em;
}
#images .image {
	gap: 1em;
	margin-bottom: 1em;
}
#images img {
	max-width: 50px;
	max-height: 50px;
}
#images .actions {
	text-align: right;
	padding: 0.25em 0;
}

#campaigns .dropdown-menu {
	background: transparent;
}

#tab-customizations li {
	background: #f9f9f9;
	padding: 1em;
	border-radius: 4px;
	margin-bottom: 2em;
	border: 1px solid #ececec;
}
</style>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($product->data['id']) ? t('title_edit_product', 'Edit Product') . ': '. $product->data['name'][language::$selected['code']] : t('title_create_new_product', 'Create New Product'); ?>
		</div>
	</div>

	<nav class="tabs">
		<a class="tab-item active" data-toggle="tab" href="#tab-general"><?php echo t('title_general', 'General'); ?></a>
		<a class="tab-item" data-toggle="tab" href="#tab-information"><?php echo t('title_information', 'Information'); ?></a>
		<a class="tab-item" data-toggle="tab" href="#tab-prices"><?php echo t('title_prices', 'Prices'); ?></a>
		<a class="tab-item" data-toggle="tab" href="#tab-attributes"><?php echo t('title_attributes', 'Attributes'); ?></a>
		<a class="tab-item" data-toggle="tab" href="#tab-customizations"><?php echo t('title_customizations', 'Customizations'); ?></a>
		<a class="tab-item" data-toggle="tab" href="#tab-stock"><?php echo t('title_stock', 'Stock'); ?></a>
	</nav>

	<div class="card-body">
		<?php echo functions::form_begin('product_form', 'post', false, true); ?>

			<div class="tab-contents">
				<div id="tab-general" class="tab-content active" style="max-width: 1400px;">

					<div class="grid">
						<div class="col-md-4">

							<label class="form-group">
								<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
								<?php echo functions::form_toggle('status', 'e/d', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_featured', 'Featured'); ?></div>
								<?php echo functions::form_toggle('featured', 'y/n', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_brand', 'Brand'); ?></div>
								<?php echo functions::form_select_brand('brand_id', true); ?>
							</label>

							<div class="form-group">
								<div class="form-label"><?php echo t('title_categories', 'Categories'); ?></div>
								<?php echo functions::form_select_category('categories[]', true, 'style="max-height: 480px;"'); ?>
							</div>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_default_category', 'Default Category'); ?></div>
								<?php echo functions::form_select('default_category_id', [], true); ?>
							</label>

							<?php if (!empty($product->data['id'])) { ?>
							<div class="grid">
								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_updated_at', 'Updated At'); ?></div>
										<div><?php echo functions::datetime_when($product->data['updated_at']); ?></div>
									</label>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_created_at', 'Created At'); ?></div>
										<div><?php echo functions::datetime_when($product->data['created_at']); ?></div>
									</label>
								</div>
							</div>
							<?php } ?>
						</div>

						<div class="col-md-4">

							<label class="form-group">
								<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
								<?php echo functions::form_regional_text('name['. language::$selected['code'] .']', language::$selected['code'], true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_code', 'Code'); ?></div>
								<?php echo functions::form_input_text('code', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_keywords', 'Keywords'); ?></div>
								<?php echo functions::form_input_tags('keywords', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_valid_from', 'Date Valid From'); ?></div>
								<?php echo functions::form_input_datetime('valid_from', true); ?>
							</label>

							<label class="form-group">
								<div class="form-label"><?php echo t('title_valid_to', 'Date Valid To'); ?></div>
								<?php echo functions::form_input_datetime('valid_to', true); ?>
							</label>
						</div>

						<div class="col-md-4">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_images', 'Images'); ?></div>
<?php
	if ($product->data['images']) {
		$image = current($product->data['images']);
		echo functions::draw_thumbnail('storage://images/'. $image['filename'], 480, 0, 'product', 'id="main-image"');
	} else {
		echo functions::draw_thumbnail('storage://images/no_image.svg', 480, 0, 'product', 'id="main-image"');
  }
?>
							</label>

							<div id="images">

								<?php if (!empty($_POST['images'])) foreach (array_keys($_POST['images']) as $key) { ?>
								<div class="image flex">
									<?php echo functions::form_input_hidden('images['.$key.'][id]', true); ?>
									<?php echo functions::form_input_hidden('images['.$key.'][filename]', $_POST['images'][$key]['filename']); ?>
									<?php echo functions::draw_thumbnail('storage://images/' . $product->data['images'][$key]['filename'], 480, 0, 'product'); ?>
									<?php echo functions::form_input_text('images['.$key.'][new_filename]', fallback($_POST['images'][$key]['new_filename'], $_POST['images'][$key]['filename'])); ?>

									<div style="align-content: center;">
										<div class="btn-group">
											<button name="move_up" class="btn btn-default btn-sm" class="button" title="<?php echo t('text_move_up', 'Move up'); ?>" style="align-content: center;"><?php echo functions::draw_fonticon('move-up'); ?></button>
											<button name="move_down" class="btn btn-default btn-sm" class="button" title="<?php echo t('text_move_down', 'Move down'); ?>" style="align-content: center;"><?php echo functions::draw_fonticon('move-down'); ?></button>
											<button name="remove" class="btn btn-default btn-sm" class="button" title="<?php echo t('title_remove', 'Remove'); ?>" style="align-content: center;"><?php echo functions::draw_fonticon('remove'); ?></button>
										</div>
									</div>
								</div>
								<?php } ?>

							</div>

							<label class="form-group">
								<button name="add_image" type="button" class="add btn btn-default btn-sm">
									<?php echo functions::draw_fonticon('add'); ?> <?php echo t('text_add_image', 'Add Image'); ?>
								</button>
							</label>
						</div>
					</div>

				</div>

				<div id="tab-information" class="tab-contents">

					<nav class="tabs" style="margin-top: -1em;">
						<?php foreach ($language_codes as $language_code) { ?>
						<a class="tab-item<?php if ($language_code == language::$selected['code']) echo ' active'; ?>" data-toggle="tab" href="#<?php echo $language_code; ?>"><?php echo language::$languages[$language_code]['name']; ?></a>
						<?php } ?>
					</nav>

					<div class="tab-contents" style="padding-top: 2em;">
						<?php foreach ($language_codes as $language_code) { ?>
						<div id="<?php echo $language_code; ?>" class="tab-content<?php if ($language_code == language::$selected['code']) echo ' active'; ?>">

							<div class="grid">
								<div class="col-md-6">

									<label class="form-group">
										<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
										<?php echo functions::form_regional_text('name['. $language_code .']', $language_code, true); ?>
									</label>

									<label class="form-group">
										<div class="form-label"><?php echo t('title_short_description', 'Short Description'); ?></div>
										<?php echo functions::form_regional_text('short_description['. $language_code .']', $language_code, true); ?>
									</label>

									<div class="form-group">
										<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
										<?php echo functions::form_regional_wysiwyg('description['. $language_code .']', $language_code, true, 'style="height: 250px;"'); ?>
									</div>

									<label class="form-group">
										<div class="form-label"><?php echo t('title_synonyms', 'Synonyms'); ?></div>
										<?php echo functions::form_input_tags('synonyms['. $language_code .']', true); ?>
									</label>

									<div class="grid">
										<div class="col-md-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_head_title', 'Head Title'); ?></div>
												<?php echo functions::form_regional_text('head_title['. $language_code .']', $language_code, true); ?>
											</label>
										</div>

										<div class="col-md-6">
											<label class="form-group">
												<div class="form-label"><?php echo t('title_meta_description', 'Meta Description'); ?></div>
												<?php echo functions::form_regional_text('meta_description['. $language_code .']', $language_code, true); ?>
											</label>
										</div>
									</div>
								</div>

								<div class="col-md-6">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_technical_data', 'Technical Data'); ?> <a class="technical-data-hint" href="#"><?php echo functions::draw_fonticon('icon-question'); ?></a></div>
										<?php echo functions::form_regional_textarea('technical_data['. $language_code .']', $language_code, true, 'style="height: 640px;"'); ?>
										<div><?php echo functions::form_checkbox('autofill_technical_data', ['1', t('text_autogenerate_from_attributes', 'Generate from attributes')], ''); ?></div>
									</label>
								</div>
							</div>
						</div>
						<?php } ?>

					</div>
				</div>

				<div id="tab-prices" class="tab-content">

					<div class="grid" style="max-width: 720px;">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_recommended_price', 'Recommended Price'); ?> / MSRP</div>
								<?php echo functions::form_input_money('recommended_price', settings::get('store_currency_code'), true); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_tax_class', 'Tax Class'); ?></div>
								<?php echo functions::form_select_tax_class('tax_class_id', true); ?>
							</label>
						</div>
					</div>

					<h2 style="margin-top: 2em;">
						<?php echo t('title_prices', 'Prices'); ?>
					</h2>

					<div style="margin: 0 -2em">
						<table id="prices" class="table data-table">
							<thead>
								<tr>
									<th style="width: 250px;"><?php echo t('title_customer_group', 'Customer Group'); ?></th>
									<th style="width: 50px;"><?php echo t('title_min_quantity', 'Min. Quantity'); ?></th>
									<th style="width: 200px;" class="text-center"><?php echo t('title_net_price', 'Net Price'); ?></th>
									<th style="width: 200px;" class="text-center"><?php echo t('title_gross_price', 'Gross Price'); ?>  <a href="#" id="price-incl-tax-tooltip"><?php echo functions::draw_fonticon('icon-question'); ?></a></th>
									<th></th>
									<th style="width: 50px;"></th>
								</tr>
							</thead>

							<tbody>
								<?php if (!empty($_POST['prices'])) foreach ($_POST['prices'] as $key => $price) { ?>
								<tr>
									<td>
										<?php echo functions::form_input_hidden('prices['.$key.'][id]', true); ?>
										<?php echo functions::form_select_campaign('customer_group_id', true); ?>
									</td>
									<td><?php echo functions::form_input_decimal('prices['.$key.'][min_quantity]', true, 'min="0"'); ?></td>
									<td>
										<div class="dropdown dropdown-end">
											<?php echo functions::form_input_money('prices['.$key.'][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?>
											<ul class="dropdown-menu">
												<?php foreach (array_diff($currency_codes, [settings::get('store_currency_code')]) as $currency_code) { ?>
												<li>
													<?php echo functions::form_input_money('prices['.$key.'][price]['. $currency_code .']', $currency_code, true, 'style="width: 125px;"'); ?>
												</li>
												<?php } ?>
											</ul>
										</div>
									</td>
									<td><?php echo functions::form_input_money('prices['.$key.'][gross]', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?></td>
									<td></td>
									<td>
										<a class="btn btn-default btn-sm remove" href="#" title="<?php echo t('title_remove', 'Remove'); ?>">
											<?php echo functions::draw_fonticon('remove'); ?>
										</a>
									</td>
								</tr>
							<?php } ?>
							</tbody>

							<tfoot>
								<tr>
									<td colspan="99">
										<button class="btn btn-default add" type="button">
											<?php echo functions::draw_fonticon('add'); ?> <?php echo t('text_add_price', 'Add Price'); ?>
										</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>

					<h2 style="margin-top: 2em;">
						<?php echo t('title_campaigns', 'Campaigns'); ?>
					</h2>

					<div style="margin: 0 -2em">
						<table id="campaigns" class="table data-table">
							<thead>
								<tr>
									<th style="width: 250px;"><?php echo t('title_campaign', 'Campaign'); ?></th>
									<th style="width: 200px;"><?php echo t('title_valid_from', 'Valid From'); ?></th>
									<th style="width: 200px;"><?php echo t('title_valid_to', 'Valid To'); ?></th>
									<th style="width: 200px;"><?php echo t('title_price', 'Price'); ?></th>
									<th style="width: 200px;"><?php echo t('title_discount', 'Discount'); ?></th>
									<th></th>
									<th style="width: 50px;"></th>
								</tr>
							</thead>

							<tbody>
								<?php if (!empty($_POST['campaigns'])) foreach ($_POST['campaigns'] as $key => $campaign) { ?>
								<tr>
									<td>
										<?php echo functions::form_input_hidden('campaigns['.$key.'][id]', true); ?>
										<?php echo functions::form_select_campaign('campaigns['.$key.'][campaign_id]', true); ?>
									</td>
									<td><span class="date-valid-from"><?php echo $campaign['valid_from'] ? functions::datetime_when($campaign['valid_from']) : '-'; ?></span></td>
									<td><span class="date-valid-to"><?php echo $campaign['valid_to'] ? functions::datetime_when($campaign['valid_to']) : '-'; ?></span></td>
									<td>
										<div class="dropdown dropdown-end">
											<?php echo functions::form_input_money('campaigns['.$key.'][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?>
											<ul class="dropdown-menu">
												<?php foreach (array_diff($currency_codes, [settings::get('store_currency_code')]) as $currency_code) { ?>
												<li>
													<?php echo functions::form_input_money('campaigns['.$key.'][price]['. $currency_code .']', $currency_code, true, 'style="width: 125px;"'); ?>
												</li>
												<?php } ?>
											</ul>
										</div>
									</td>
									<td><?php echo functions::form_input_percent('campaigns['.$key.'][percentage]', '', 2, 'min="0"'); ?></td>
									<td></td>
									<td>
										<a class="btn btn-default btn-sm remove" href="#" title="<?php echo t('title_remove', 'Remove'); ?>">
											<?php echo functions::draw_fonticon('remove'); ?>
										</a>
									</td>
								</tr>
							<?php } ?>
							</tbody>

							<tfoot>
								<tr>
									<td colspan="7">
										<button class="btn btn-default add" type="button">
											<?php echo functions::draw_fonticon('add'); ?> <?php echo t('text_add_campaign', 'Add Campaign'); ?>
										</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>

				<div id="tab-attributes" class="tab-content" style="max-width: 960px;">

					<table id="attributes" class="table data-table">
						<thead>
							<tr>
								<th style="width: 320px;"><?php echo t('title_attribute_group', 'Attribute Group'); ?></th>
								<th style="width: 320px;"><?php echo t('title_attribute_value', 'Attribute Value'); ?></th>
								<th style="width: 60px;"></th>
							</tr>
						</thead>

						<tbody>
							<?php if (!empty($_POST['attributes'])) foreach (array_keys($_POST['attributes']) as $key) { ?>
							<tr draggable="true">
								<td class="grabbable">
									<?php echo functions::form_input_hidden('attributes['.$key.'][id]', true); ?>
									<?php echo functions::form_input_hidden('attributes['.$key.'][group_id]', true); ?>
									<?php echo functions::form_input_hidden('attributes['.$key.'][group_name]', true); ?>
									<?php echo functions::form_input_hidden('attributes['.$key.'][value_id]', true); ?>
									<?php echo functions::form_input_hidden('attributes['.$key.'][value_name]', true); ?>
									<?php echo functions::form_input_hidden('attributes['.$key.'][custom_value]', true); ?>
									<?php echo $_POST['attributes'][$key]['group_name']; ?>
								</td>
								<td class="grabbable">
									<?php echo $_POST['attributes'][$key]['value_name']; ?>
									<?php echo $_POST['attributes'][$key]['custom_value']; ?>
								</td>
								<td class="text-end">
									<button name="remove" type="button" class="btn btn-default btn-sm" title="<?php echo t('title_remove', 'Remove'); ?>">
										<?php echo functions::draw_fonticon('remove'); ?>
									</button>
								</td>
							</tr>
							<?php } ?>
						</tbody>

						<tfoot>
							<tr>
								<td><?php echo functions::form_select_attribute_group('new_attribute[group_id]', ''); ?></td>
								<td>
									<?php echo functions::form_select('new_attribute[value_id]', [], ''); ?>
									<?php echo functions::form_input_text('new_attribute[custom_value]', '', 'disabled hidden'); ?>
								</td>
								<td class="text-end"><?php echo functions::form_button('add', t('title_add', 'Add'), 'button'); ?></td>
							</tr>
						</tfoot>
					</table>
				</div>

				<div id="tab-customizations" class="tab-content">

					<ul id="customizations" class="list-unstyled">
						<?php foreach ($_POST['customizations'] as $group_id => $customization) { ?>
						<li data-group-id="<?php echo functions::escape_html($group_id); ?>" data-group-name="<?php echo functions::escape_html($customization['name']); ?>">

							<div class="float-end">
								<button name="move-group-up" type="button" class="btn btn-default" title="<?php echo functions::escape_html(t('title_move_up', 'Move Up')); ?>">
									<?php echo functions::draw_fonticon('icon-arrow-up', 'style="color: #3399cc;"'); ?>
								</button>
								<button name="move-group-down" type="button" class="btn btn-default" title="<?php echo functions::escape_html(t('title_move_down', 'Move Down')); ?>">
									<?php echo functions::draw_fonticon('icon-arrow-down', 'style="color: #3399cc;"'); ?>
								</button>
								<button name="remove-group" type="button" class="btn btn-default" title="<?php echo functions::escape_html(t('title_remove', 'Remove')); ?>">
									<?php echo functions::draw_fonticon('icon-times', 'style="color: #cc3333;"'); ?>
								</button>
							</div>

							<h2><?php echo $customization['name']; ?></h2>
							<?php echo functions::form_input_hidden('customizations['.$group_id.'][id]', true) . functions::form_input_hidden('customizations['.$group_id.'][group_id]', true) . functions::form_input_hidden('customizations['.$group_id.'][name]', true); ?>

							<div class="grid">
								<div class="col-sm-4 col-md-2">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_function', 'Function'); ?></div>
										<?php echo functions::form_select('customizations['.$group_id.'][function]', in_array($customization['function'], ['select', 'radio', 'checkbox']) ? ['select', 'radio', 'checkbox'] : ['text', 'textarea'], true); ?>
									</label>
								</div>

								<?php if (in_array($customization['function'], ['select', 'radio', 'checkbox'])) { ?>
								<div class="col-sm-4 col-md-2">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_sort_values', 'Sort Values'); ?></div>
										<?php echo functions::form_select('customizations['.$group_id.'][sort]', $customizations_sort_options, true); ?>
									</label>
								</div>
								<?php } ?>

								<div class="col-sm-4 col-md-2">
									<div class="form-group">
										<div class="form-label"><?php echo functions::escape_js(t('title_required', 'Required')); ?></div>
										<?php echo functions::form_checkbox('customizations['.$group_id.'][required]', ['1', t('title_required', 'Required')], true); ?>
									</div>
								</div>
							</div>

							<?php if (in_array($customization['function'], ['select', 'radio', 'checkbox'])) { ?>
							<div class="table-responsive">
								<table class="table data-table">
									<thead>
										<tr>
											<th class="main"><?php echo t('title_option', 'Option'); ?></th>
											<th style="width: 150px;"><?php echo t('title_price_modifier', 'Price Modifier'); ?></th>
											<th colspan="<?php echo count($currency_codes); ?>"><?php echo t('title_price_adjustment', 'Price Adjustment'); ?></th>
											<th style="width: 85px;">&nbsp;</th>
										</tr>
									</thead>

									<tbody>
										<?php foreach ($customization['values'] as $value_id => $value) { ?>
										<tr draggable="true" data-value-id="<?php echo functions::escape_html($value['value_id']); ?>" data-value-name="<?php echo functions::escape_html($_POST['customizations'][$group_id]['values'][$value_id]['name']); ?>">
											<td class="grabbable"><?php echo functions::form_input_hidden('customizations['.$group_id.'][values]['. $value_id .'][id]', true) . functions::form_input_hidden('customizations['.$group_id.'][values]['. $value_id .'][value_id]', true) . functions::form_input_hidden('customizations['.$group_id.'][values]['. $value_id .'][custom_value]', true) . functions::form_input_hidden('customizations['.$group_id.'][values]['. $value_id .'][name]', true); ?><?php echo $value['name']; ?></td>
											<td class="text-center"><?php echo functions::form_select('customizations['.$group_id.'][values]['. $value_id .'][price_modifier]', ['+','%','*','='], true); ?></td>
											<?php foreach ($currency_codes as $currency_code) echo '<td>'. functions::form_select_currency($currency_code, 'customizations['.$group_id.'][values]['. $value_id .']['. $currency_code. ']', (!empty($_POST['customizations'][$group_id]['values'][$value_id][$currency_code]) || $_POST['customizations'][$group_id]['values'][$value_id][$currency_code] != 0) ? true : '', 'style="width: 100px;"') .'</td>'; ?>
											<td class="text-end">
												<button name="move-up" type="button" class="btn btn-default btn-sm" title="<?php echo functions::escape_html(t('title_move_up', 'Move Up')); ?>">
													<?php echo functions::draw_fonticon('move-up'); ?>
												</button>
												<button name="move-down" type="button" class="btn btn-default btn-sm" title="<?php echo functions::escape_html(t('title_move_down', 'Move Down')); ?>">
													<?php echo functions::draw_fonticon('move-down'); ?>
												</button>
												<button name="remove" type="button" class="btn btn-default btn-sm" title="<?php echo functions::escape_html(t('title_remove', 'Remove')); ?>">
													<?php echo functions::draw_fonticon('remove'); ?>
												</button>
											</td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
							<?php } ?>

						</li>
						<?php } ?>
					</ul>

					<div>
						<a class="btn btn-default" href="#modal-predefined-customization" data-toggle="lightbox">
							<?php echo functions::draw_fonticon('add'); ?> <?php echo t('title_add_predefined_customization', 'Add Predefined Option'); ?>
						</a>
						<a class="btn btn-default" href="#modal-user-input-customization" data-toggle="lightbox">
							<?php echo functions::draw_fonticon('add'); ?> <?php echo t('title_add_user_input_customization', 'Add User Input Option'); ?>
						</a>
					</div>

					<div id="modal-predefined-customization" style="display: none;">
						<fieldset style="max-width: 960px;">

							<legend>
								<?php echo t('title_add_predefined_option', 'Add Predefined Option'); ?>
							</legend>

							<div class="grid">

								<div class="col-md-3">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_attribute_group', 'Attribute Group'); ?></div>
										<?php echo functions::form_select_attribute_group('new_predefined_customization[group_id]', ''); ?>
									</label>
								</div>

								<div class="col-md-3">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_value', 'Value'); ?></div>
										<?php echo functions::form_select('new_predefined_customization[value_id]', [['','']], '', 'disabled'); ?>
									</label>
								</div>

								<div class="col-md-3">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_custom_value', 'Custom Value'); ?></div>
										<?php echo functions::form_input_text('new_predefined_customization[custom_value]', ''); ?>
									</label>
								</div>

								<div class="col-md-3">
									<br>
									<?php echo functions::form_button('add_predefined_customization', t('title_add', 'Add'), 'button', 'class="btn btn-default btn-block"'); ?>
								</div>
							</div>

						</fieldset>
					</div>

					<div id="modal-user-input-customization" style="display: none;">
						<fieldset>

							<legend>
								<?php echo t('title_add_user_input_option', 'Add User Input Option'); ?>
							</legend>

							<div class="grid">
								<div class="col-md-8">
									<label class="form-group">
										<div class="form-label"><?php echo t('title_attribute_group', 'Attribute Group'); ?></div>
										<?php echo functions::form_select_attribute_group('new_user_input_customization[group_id]', ''); ?>
									</label>
								</div>

								<div class="col-md-4">
									<br>
									<?php echo functions::form_button('add_user_input_customization', t('title_add', 'Add'), 'button', 'class="btn btn-default btn-block"'); ?>
								</div>
							</div>

						</fieldset>
					</div>
				</div>

				<div id="tab-stock" class="tab-content">

					<div class="grid" style="max-width: 720px;">
						<div class="col-md-3">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_min_order_qty', 'Min. Order Qty'); ?></div>
								<?php echo functions::form_input_decimal('quantity_min', true, 2, 'min="0"'); ?>
							</label>
						</div>

						<div class="col-md-3">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_max_order_quantity', 'Max. Order Qty'); ?></div>
								<?php echo functions::form_input_decimal('quantity_max', true, 2, 'min="0"'); ?>
							</label>
						</div>

						<div class="col-md-3">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_quantity_step', 'Quantity Step'); ?></div>
								<?php echo functions::form_input_decimal('quantity_step', true, 2, 'min="0"'); ?>
							</label>
						</div>

						<div class="col-md-3">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_quantity_unit', 'Quantity Unit'); ?></div>
								<?php echo functions::form_select_quantity_unit('quantity_unit_id', true); ?>
							</label>
						</div>
					</div>

					<div class="grid" style="max-width: 720px;">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_delivery_status', 'Delivery Status'); ?></div>
								<?php echo functions::form_select_delivery_status('delivery_status_id', true); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_sold_out_status', 'Sold Out Status'); ?></div>
								<?php echo functions::form_select_sold_out_status('sold_out_status_id', true); ?>
							</label>
						</div>
					</div>

					<h3><?php echo t('title_stock_options', 'Stock Options'); ?></h3>

					<div class="grid">
						<div class="col-md-3">
							<div class="form-group">
								<div class="form-label"><?php echo t('title_type', 'Type'); ?></div>
								<?php echo functions::form_toggle('stock_option_type', ['variants' => t('title_variants', 'Variants'), 'bundle' => t('title_bundle', 'Bundle')], true); ?>
							</div>
						</div>
					</div>

					<div style="margin: 0 -2em;">
						<table id="stock-options" class="table data-table">
							<thead>
								<tr>
									<th><?php echo t('title_item', 'Item'); ?></th>
									<th style="width: 150px;"><?php echo t('title_sku', 'SKU'); ?></th>
									<th style="width: 125px;" class="text-center"><?php echo t('title_modifier', 'Modifier'); ?></th>
									<th style="width: 125px;" class="text-center"><?php echo t('title_price', 'Price'); ?></th>
									<th style="width: 125px;" class="text-center"><?php echo t('title_quantity', 'Quantity'); ?></th>
									<th style="width: 175px;" class="text-center"><?php echo t('title_adjust', 'Adjust'); ?></th>
									<th style="width: 175px;" class="text-center"><?php echo t('title_backordered', 'Backordered'); ?></th>
									<th style="width: 85px;"></th>
									<th style="width: 50px;"></th>
								</tr>
							</thead>

							<tbody>
								<?php if (!empty($_POST['stock_options'])) foreach ($_POST['stock_options'] as $key => $stock_option) { ?>
								<tr draggable="true" data-stock-item-id="<?php echo $stock_option['stock_item_id']; ?>">
									<td class="grabbable">
										<?php echo functions::form_input_hidden('stock_options['.$key.'][id]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][stock_item_id]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][sku]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][weight]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][weight_unit]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][length]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][width]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][height]', true); ?>
										<?php echo functions::form_input_hidden('stock_options['.$key.'][length_unit]', true); ?>
										<span class="name"><?php echo $stock_option['name']; ?></span>
									</td>
									<td class="grabbable">
										<span class="sku"><?php echo $_POST['stock_options'][$key]['sku']; ?></span>
									</td>
									<td><?php echo functions::form_select('stock_options['.$key.'][price_modifier]', ['+', '*', '%', '='], '+'); ?></td>
									<td>
										<div class="dropdown">
											<?php echo functions::form_input_money('stock_options['.$key.'][price_adjustment]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"'); ?>
											<ul class="dropdown-menu">
												<?php foreach (currency::$currencies as $currency) { ?>
												<?php if ($currency['code'] == settings::get('store_currency_code')) continue; ?>
												<li>
													<?php echo functions::form_input_money('stock_options['.$key.'][price_adjustment]['. $currency['code'] .']', $currency['code'], true, 'style="width: 125px;"'); ?>
												</li>
												<?php } ?>
											</ul>
										</div>
									</td>
									<td><?php echo functions::form_input_decimal('stock_options['.$key.'][quantity]', true, 2, 'data-quantity="'. (isset($product->data['stock_options'][$key]) ? (float)$product->data['stock_options'][$key]['quantity'] : '0') .'"'); ?></td>
									<td>
										<label class="input-group">
											<span class="input-group-text">&plusmn;</span>
											<?php echo functions::form_input_decimal('stock_options['. $key .'][quantity_adjustment]', true); ?>
										</label>
									</td>
									<td>
										<div class="input-group">
											<?php echo functions::form_button('transfer', functions::draw_fonticon('icon-arrow-left'), 'button'); ?>
											<?php echo functions::form_input_decimal('stock_options['. $key .'][backordered]', true, 2, 'min="0"'); ?>
										</div>
									</td>
									<td class="text-end">
										<a class="remove btn btn-default btn-sm" href="#" title="<?php echo t('title_remove', 'Remove'); ?>">
											<?php echo functions::draw_fonticon('icon-times', 'style="color: #c33;"'); ?>
										</a>
									</td>
									<td class="text-end">
										<a class="edit btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => $_POST['stock_options'][$key]['stock_item_id'], 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" data-seamless="true"data-max-width="980px"  title="<?php echo t('title_edit', 'Edit'); ?>">
											<?php echo functions::draw_fonticon('edit'); ?>
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>

							<tfoot>
								<tr>
									<td colspan="99">
										<a href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox" data-seamless="true" data-width="980px">
											<?php echo functions::draw_fonticon('icon-plus'); ?> <?php echo t('title_create_new_stock_item', 'Create New Stock Item'); ?>
										</a>
										<a href="<?php echo document::href_ilink(__APP__.'/stock_item_picker', ['js_callback' => 'upsert_stock_item']); ?>" class="btn btn-default" data-toggle="lightbox" data-seamless="true" data-width="980px">
											<?php echo functions::draw_fonticon('icon-plus'); ?> <?php echo t('title_add_existing_stock_item', 'Add Existing Stock Item'); ?>
										</a>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($product->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<script>
	// Cross Referencing

	$('input[name="name[<?php echo settings::get('store_language_code'); ?>]"]').on('input change', function() {
		$('input[name="'+ $(this).attr('name') +'"]').not(this).val($(this).val());
	});

	// Initiate

	$('input[name="name[<?php echo settings::get('store_language_code'); ?>]"]').first().trigger('input');

	$('input[name^="name"]').on('input', function(e) {
		let language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
		$('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
		$('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
	});

	$('input[name^="short_description"]').on('input', function(e) {
		let language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
		$('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
	});

	// Default Category

	$('[data-toggle="category-picker"]').on('change', function() {
		let default_category_id = $('select[name="default_category_id"] option:selected').val();

		$('select[name="default_category_id"]').html('');
		$.each($(this).find(':input[name="categories[]"]'), function(category) {
			$('select[name="default_category_id"]').append('<option value="'+ $(this).val() +'">'+ unescape($(this).data('name')) +'</option>');
		});

		if (default_category_id) {
			$('select[name="default_category_id"]').val(default_category_id);
		}

		if (!$('select[name="default_category_id"]').val()) {
			$('select[name="default_category_id"]').val($('select[name="default_category_id"] option:first').val());
		}
	}).trigger('change');

	// Images

	$('#images').on('click', 'button[name="move_up"], button[name="move_down"]', function(e) {
		e.preventDefault();

		let $row = $(this).closest('.image');

		if ($(this).is('button[name="move_up"]') && $row.prevAll().length > 0) {
			$row.insertBefore($row.prev());
		} else if ($(this).is('button[name="move_down"]') && $row.nextAll().length > 0) {
			$row.insertAfter($row.next());
		}

		refreshMainImage();
	});

	$('#images').on('click', 'button[name="remove"]', function(e) {
		e.preventDefault();
		$(this).closest('.image').remove();
		refreshMainImage();
	});

	$('button[name="add_image"]').on('click', function(e) {
		e.preventDefault();

		let $output = $([
			'<div class="image flex">',
			'  <?php echo functions::draw_thumbnail('storage://images/no_image.svg', 64, 0, 'product'); ?>',
			'  <?php echo functions::form_input_file('new_images[]'); ?>',
			'  <div style="align-content: center;">',
			'    <div class="btn-group">',
			'      <button name="move_up" class="btn btn-default btn-sm" type="button" title="<?php echo t('text_move_up', 'Move up'); ?>" style="align-content: center;"><?php echo functions::draw_fonticon('move-up'); ?></button>',
			'      <button name="move_down" class="btn btn-default btn-sm" type="button" title="<?php echo t('text_move_down', 'Move down'); ?>" style="align-content: center;"><?php echo functions::draw_fonticon('move-down'); ?></button>',
			'      <button name="remove" class="btn btn-default btn-sm" type="button" title="<?php echo t('title_remove', 'Remove'); ?>" style="align-content: center;"><?php echo functions::draw_fonticon('remove'); ?></button>',
			'    </div>',
			'  </div>',
			'</div>'
		].join('\n'));

		$('#images').append($output);
		refreshMainImage();
	});

	$('#images').on('change', 'input[type="file"]', function(e) {
		let $img = $(this).closest('.image').find('img');

		if ($img.length && this.files && this.files[0]) {
			let reader = new FileReader();
			reader.onload = function(e) {
				$img.attr('src', e.target.result);
				$img.attr('srcset', e.target.result);
				refreshMainImage();
			};
			reader.readAsDataURL(this.files[0]);
			return;
		}

		$img.attr('src', '<?php echo document::href_rlink('storage://images/no_image.svg'); ?>');
	});

	function refreshMainImage() {

		let source;
		if ($('#images img:first').length) {
			source = $('#images img:first').attr('src')
		} else {
			source = '<?php echo document::href_rlink('storage://images/no_image.svg'); ?>';
		}

		$('#main-image').attr('src', source);
		$('#main-image').attr('srcset', source);
	}

	// Technical Data

	$('a.technical-data-hint').on('click', function(e) {
		e.preventDefault();
		alert('Syntax:\n\nTitle1\nProperty1: Value1\nProperty2: Value2\n\nTitle2\nProperty3: Value3...');
	});

	$('input[name="autogenerate_techdata"]').on('change', function() {
		if ($(this).is(':checked')) {
			$('textarea[name^="technical_data"]').prop('disabled', true);
		} else {
			$('textarea[name^="technical_data"]').prop('disabled', false);
		}
	}).trigger('change');

	// Prices

	$('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').on('input', function() {
		$('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').not(this).val($(this).val());
	});

	function get_tax_rate() {
		switch ($('select[name=tax_class_id]').val()) {
<?php
	database::query(
		"select * from ". DB_TABLE_PREFIX ."tax_classes
		order by name asc;"
	)->each(function($tax_class){
		echo '      case "'. $tax_class['id'] . '": return '. tax::get_tax(100, $tax_class['id'], 'store') .';' . PHP_EOL;
	})
?>
			default: return 0;
		}
	}

	function get_currency_value(currency_code) {
		switch (currency_code) {
			<?php foreach (currency::$currencies as $currency) echo 'case \''. $currency['code'] .'\': return '. (float)$currency['value'] .';' . PHP_EOL; ?>
		}
	}

	function get_currency_decimals(currency_code) {
		switch (currency_code) {
			<?php foreach ($currency_codes as $currency_code) echo 'case \''. $currency_code .'\': return '. ($currency['decimals']+2) .';' . PHP_EOL; ?>
		}
	}
/*
	// Update prices
	$('select[name="tax_class_id"]').on('change', 'input', function() {
		$('input[name^="prices"]').trigger('input');
	});

	// Update gross price
	$('input[name^="prices"]').on('input', function() {

		let currency_code = $(this).attr('name').match(/^prices\[([A-Z]{3})\]$/)[1];
		let decimals = get_currency_decimals(currency_code);
		let gross_field = $('input[name="gross_prices['+ currency_code +']"]');
		let gross_price = parseFloat(Number($(this).val() * (1+(get_tax_rate()/100))).toFixed(decimals));

		if ($(this).val() == 0) {
			$(gross_field).val('');
		} else {
			$(gross_field).val(gross_price);
		}

		update_currency_prices();
	}).trigger('input');

	// Update net price
	$('input[name^="gross_prices"]').on('input', function() {

		let currency_code = $(this).attr('name').match(/^gross_prices\[([A-Z]{3})\]$/)[1];
		let decimals = get_currency_decimals(currency_code);
		let net_field = $('input[name="prices['+ currency_code +']"]');
		let net_price = parseFloat(Number($(this).val() / (1+(get_tax_rate()/100))).toFixed(decimals));

		if ($(this).val() == 0) {
			$(net_field).val('');
		} else {
			$(net_field).val(net_price);
		}

		update_currency_prices();
	});

	// Update price placeholders
	function update_currency_prices() {
		let store_currency_code = '<?php echo settings::get('store_currency_code'); ?>';
		let currencies = ['<?php echo implode("','", array_keys(currency::$currencies)); ?>'];
		let net_price = $('input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val();
		let gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val();

		if (!net_price) {
			net_price = $('input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').attr('placeholder');
		}

		if (!gross_price) {
			gross_price = $('input[name^="gross_prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').attr('placeholder');
		}

		$.each(currencies, function(i, currency_code) {
			if (currency_code == '<?php echo settings::get('store_currency_code'); ?>') return;

			let currency_decimals = get_currency_decimals(currency_code);
			let currency_net_price = net_price / get_currency_value(currency_code);
			let currency_gross_price = gross_price / get_currency_value(currency_code);

			currency_net_price = currency_net_price ? parseFloat(currency_net_price.toFixed(currency_decimals)) : '';
			currency_gross_price = currency_gross_price ? parseFloat(currency_gross_price.toFixed(currency_decimals)) : '';

			$('input[name="prices['+ currency_code +']"]').attr('placeholder', currency_net_price);
			$('input[name="gross_prices['+ currency_code +']"]').attr('placeholder', currency_gross_price);
		});
	}

*/

	// Prices
	$('#price-incl-tax-tooltip').on('click', function(e) {
		e.preventDefault();
		alert('<?php echo str_replace(["\r", "\n", "'"], ["", "", "\\'"], t('tooltip_field_price_incl_tax', 'This field helps you calculate net price based on the tax rates set for the store region. The prices stored in the database are always excluding tax.')); ?>');
	});

	$('#prices').on('focus', 'input[name^="prices"]', function(e) {
		if ($(this).attr('name').match(/\[[A-Z]{3}\]$/)) {
			$(this).closest('.dropdown').addClass('open');
		}
	});

	$('#prices').on('blur', '.dropdown', function(e) {
		$(this).removeClass('open');
	});

	$('#prices').on('input', 'input[name^="prices"][name$="[percentage]"]', function() {
		let parent = $(this).closest('tr'),
			value = 0;

		<?php foreach (currency::$currencies as $currency) { ?>
		if ($('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').val() > 0) {
			value = parseFloat($('input[name="prices[<?php echo $currency['code']; ?>]"]').val() * (100 - $(this).val()) / 100).toFixed(<?php echo $currency['decimals']; ?>);
			$(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val(value);
		} else {
			$(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val('');
		}
		<?php } ?>

		<?php foreach (currency::$currencies as $currency) { ?>
		value = parseFloat($(parent).find('input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>).toFixed(<?php echo $currency['decimals']; ?>);
		$(parent).find('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
		<?php } ?>
	});

	$('#prices').on('input', 'input[name^="prices"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]', function() {
		let parent = $(this).closest('tr');
		let percentage = ($('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() * 100;
		percentage = percentage.toFixed(2);
		$(parent).find('input[name$="[percentage]"]').val(percentage);

		<?php foreach (currency::$currencies as $currency) { ?>
		value = $(parent).find('input[name^="prices"][name*="[price]"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value'] . PHP_EOL; ?>
		value = value.toFixed(<?php echo $currency['decimals']; ?>);
		$(parent).find('input[name^="prices"][name*="[price]"][name$="[<?php echo $currency['code']; ?>]"]').attr("placeholder", value);
		if ($(parent).find('input[name^="prices"][name*="[price]"][name$="[<?php echo $currency['code']; ?>]"]').val() == 0) {
			$(parent).find('input[name^="prices"][name*="[price]"][name$="[<?php echo $currency['code']; ?>]"]').val('');
		}
		<?php } ?>
	});

	$('input[name^="prices"][name*="[price]"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').trigger('input');

	$('#prices').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});

	$('#prices').on('click', '.add', function(e) {
		e.preventDefault();

		let __index__ = 0;
		while ($(':input[name^="prices[' + __index__ + ']"]').length) __index__++;

		let $output = $([
			'<tr>',
			'  <td>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('prices[__index__][id]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_select_customer_group('prices[__index__][customer_group_id]', '')); ?>',
			'  </td>',
			'  <td><?php echo functions::escape_js(functions::form_input_decimal('prices[__index__][min_quantity]', '1', 'min="1"')); ?></td>',
			'  <td>',
			'    <div class="dropdown">',
			'      <?php echo functions::escape_js(functions::form_input_money('prices[__index__][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"')); ?>',
			'      <ul class="dropdown-menu" style="right:0;">',
			<?php echo implode(PHP_EOL, array_map(
				function($currency_code) {
					return '\'      <li>'. functions::escape_js(functions::form_input_money('prices[__index__][price]['. $currency_code .']', $currency_code, true, 'style="width: 125px;"')) .'</li>\',';
				}, array_diff($currency_codes, [settings::get('store_currency_code')])
			)); ?>
			'      </ul>',
			'    </div>',
			'  </td>',
			'  <td><?php echo functions::escape_js(functions::form_input_money('prices[__index__][gross]', settings::get('store_currency_code'), true, 'style="width: 125px;"')); ?></td>',
			'  <td></td>',
			'  <td>',
			'    <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(t('title_remove', 'Remove'), true); ?>">',
			'      <?php echo functions::escape_js(functions::draw_fonticon('remove')); ?>',
			'    </a>',
			'  </td>',
			'</tr>'
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
		);

		$('.price-name', $output).text();
		$('.price-valid-from', $output).text();
		$('.price-valid-to', $output).text();

		$('#prices tbody').append($output);
	});

	$('#prices select[name$="[price_id]"]').on('change', function() {
		let $row = $(this).closest('tr');
		$option = $(this).find('option:selected');

		if ($(this).val() != '') {
			$('.price-valid-from', $row).text($option.data('valid-from'));
			$('.price-valid-to', $row).text($option.data('valid-to'));
		} else {
			$('.price-valid-from', $row).text('');
			$('.price-valid-to', $row).text('');
		}
	});

	// Campaigns

	$('#campaigns').on('change', 'select[name$="[campaign_id]"]', function(e) {
		let $row = $(this).closest('tr');
		$option = $(this).find('option:selected');

		if ($(this).val() != '') {
			$('.date-valid-from', $row).text($option.data('valid-from'));
			$('.date-valid-to', $row).text($option.data('valid-to'));
		} else {
			$('.date-valid-from', $row).text('-');
			$('.date-valid-to', $row).text('-');
		}
	});

	$('#campaigns').on('focus', 'input[name^="campaigns"]', function(e) {
		if ($(this).attr('name').match(/\[[A-Z]{3}\]$/)) {
			$(this).closest('.dropdown').addClass('open');
		}
	});

	$('#campaigns').on('blur', '.dropdown', function(e) {
		$(this).removeClass('open');
	});

	$('#campaigns').on('input', 'input[name^="campaigns"][name$="[percentage]"]', function() {
		let parent = $(this).closest('tr'),
			value = 0;

		<?php foreach (currency::$currencies as $currency) { ?>
		if ($('input[name^="prices"][name$="[<?php echo $currency['code']; ?>]"]').val() > 0) {
			value = parseFloat($('input[name="prices[<?php echo $currency['code']; ?>]"]').val() * (100 - $(this).val()) / 100).toFixed(<?php echo $currency['decimals']; ?>);
			$(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val(value);
		} else {
			$(parent).find('input[name$="[<?php echo $currency['code']; ?>]"]').val('');
		}
		<?php } ?>

		<?php foreach (currency::$currencies as $currency) { ?>
		value = parseFloat($(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>).toFixed(<?php echo $currency['decimals']; ?>);
		$(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr('placeholder', value);
		<?php } ?>
	});

	$('#campaigns').on('input', 'input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]', function() {
		let parent = $(this).closest('tr');
		let percentage = ($('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() - $(this).val()) / $('input[name="prices[<?php echo settings::get('store_currency_code'); ?>]"]').val() * 100;
		percentage = percentage.toFixed(2);
		$(parent).find('input[name$="[percentage]"]').val(percentage);

		<?php foreach (currency::$currencies as $currency) { ?>
		value = $(parent).find('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').val() / <?php echo $currency['value']; ?>;
		value = value.toFixed(<?php echo $currency['decimals']; ?>);
		$(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').attr("placeholder", value);
		if ($(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val() == 0) {
			$(parent).find('input[name^="campaigns"][name$="[<?php echo $currency['code']; ?>]"]').val('');
		}
		<?php } ?>
	});

	$('input[name^="campaigns"][name$="[<?php echo settings::get('store_currency_code'); ?>]"]').trigger('input');

	$('#campaigns').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});


	$('#campaigns').on('click', '.add', function(e) {
		e.preventDefault();

		let __index__ = 0;
		while ($(':input[name^="campaigns[' + __index__ + ']"]').length) __index__++;

		let $output = $([
			'<tr>',
			'  <td>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('campaigns[__index__][id]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_select_campaign('campaigns[__index__][campaign_id]', '', 'style="width: 200px;"')); ?>',
			'  </td>',
			'  <td><span class="date-valid-from"></span></td>',
			'  <td><span class="date-valid-to"></span></td>',
			'  <td>',
			'    <div class="dropdown">',
			'      <?php echo functions::escape_js(functions::form_input_money('campaigns[__index__][price]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), true, 'style="width: 125px;"')); ?>',
			'      <ul class="dropdown-menu" style="right:0;">',
			<?php echo implode(PHP_EOL, array_map(
				function($currency_code) {
					return '\'      <li>'. functions::escape_js(functions::form_input_money('campaigns[__index__]price['. $currency_code .']', $currency_code, true, 'style="width: 125px;"')) .'</li>\',';
				}, array_diff($currency_codes, [settings::get('store_currency_code')])
			)); ?>
			'      </ul>',
			'    </div>',
			'  </td>',
			'  <td><?php echo functions::escape_js(functions::form_input_percent('campaigns[__index__][percentage]', '', 2)); ?></td>',
			'  <td></td>',
			'  <td>',
			'    <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(t('title_remove', 'Remove'), true); ?>">',
			'      <?php echo functions::escape_js(functions::draw_fonticon('remove')); ?>',
			'    </a>',
			'  </td>',
			'</tr>'
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
		);

		$('.campaign-name', $output).text();
		$('.campaign-valid-from', $output).text();
		$('.campaign-valid-to', $output).text();

		$('#campaigns tbody').append($output);
	});

	$('#campaigns select[name$="[campaign_id]"]').on('change', function() {
		let $row = $(this).closest('tr');
		$option = $(this).find('option:selected');

		if ($(this).val() != '') {
			$('.campaign-valid-from', $row).text($option.data('valid-from'));
			$('.campaign-valid-to', $row).text($option.data('valid-to'));
		} else {
			$('.campaign-valid-from', $row).text('');
			$('.campaign-valid-to', $row).text('');
		}
	});

	// Attributes

	$('#attributes button[name="remove"]').on('click', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});

	$('#attributes select[name="new_attribute[group_id]"]').on('change', function(e) {

		let $newAttributeGroup = $(this),
			$newAttributeValue = $('select[name="new_attribute[value_id]"]'),
			$newCustomValue = $('input[name="new_attribute[custom_value]"]');

		if ($newAttributeGroup.val() == '') {
			$newAttributeValue
				.prop('disabled', true)
				.html('<option value=""><?php echo functions::escape_js(t('title_select_attribute_group_first', 'Select attribute group first')); ?></option>');
			$newCustomValue
				.prop('disabled', true)
				.prop('hidden', true)
				.val('');
			return;
		}

		$.ajax({
			url: '<?php echo document::href_ilink(__APP__.'/attribute_values.json'); ?>',
			type: 'get',
			data: {
				group_id: $newAttributeGroup.val(),
			},
			dataType: 'json',
			success: function(values) {

				$newAttributeValue.html('<option value="" style="opacity: .5;"><?php echo functions::escape_js(t('title_custom_value', 'Custom Value')); ?>:</option>');

				if (values?.length) {

					$.each(values, function(i, value) {
						let $output = $('<option></option>', { value: value.id }).text(value.name).appendTo($newAttributeValue);
					});

					$newAttributeValue.prop('disabled', false);

					$newCustomValue.prop({
						disabled:  false,
						hidden: false
					}).val('');

				} else {

					$newCustomValue.prop({
						disabled:  false,
						hidden: false
					}).val('');
				}
			}
		});
	});

	$('#attributes select[name="new_attribute[value_id]"]').on('change', function(e) {

		let $newAttributeGroup = $(this),
			$newAttributeValue = $('select[name="new_attribute[value_id]"]'),
			$newCustomValue = $('input[name="new_attribute[custom_value]"]');

		$newCustomValue.prop({
			disabled: !!$newAttributeValue.val(),
			hidden: !!$newAttributeValue.val()
		}).val('');
	});

	$('#attributes button[name="add"]').on('click', function(e) {
		e.preventDefault();

		let $newAttributeGroup = $('select[name="new_attribute[group_id]"]'),
			$newAttributeValue = $('select[name="new_attribute[value_id]"]'),
			$newCustomValue = $('input[name="new_attribute[custom_value]"]');

		if ($newAttributeGroup.val() == '') {
			alert("<?php echo t('error_must_select_attribute_group', 'You must select an attribute group'); ?>")
			return
		}

		if ($newAttributeValue.val() == '' || $newAttributeValue.val() == '0') {
			if ($newCustomValue.val() == '') {
				alert("<?php echo t('error_must_select_attribute_value', 'You must select an attribute value'); ?>")
				return
			}
		} else {
			if ($newCustomValue.val() != '') {
				alert("<?php echo t('error_cannot_define_both_value_and_custom_value', 'You can not define both a value and a custom value'); ?>")
				return
			}
		}

		// Check if already in list
		// Prevent duplicate attribute group/value/custom_value
		let exists = false;
		$('#attributes tbody tr').each(function() {
			let group_id = $(this).find('input[name^="attributes"][name$="[group_id]"]').val();
			let value_id = $(this).find('input[name^="attributes"][name$="[value_id]"]').val();
			let custom_value = $(this).find('input[name^="attributes"][name$="[custom_value]"]').val();

			if (
				group_id == $newAttributeGroup.val() &&
				value_id == $newAttributeValue.val() &&
				custom_value == $newCustomValue.val()
			) {
				exists = true;
				return false; // break loop
			}
		});
		if (exists) {
			alert("<?php echo t('error_attribute_already_defined', 'This attribute is already defined'); ?>");
			return;
		}

		let __index__ = 0;
		while ($('select[name^="attributes[new_'+__index__+']"]').length) __index__++;

		let $output = $([
			'<tr draggable="true">',
			'  <td class="grabbable">',
			'    <?php echo functions::form_input_hidden('attributes[__index__][group_id]', ''); ?>',
			'  </td>',
			'  <td class="grabbable">',
			'    <?php echo functions::form_input_hidden('attributes[__index__][value_id]', ''); ?>',
			'    <?php echo functions::form_input_hidden('attributes[__index__][custom_value]', ''); ?>',
			'  </td>',
			'  <td class="text-end">',
			'    <button name="remove" type="button" class="btn btn-default btn-sm" title="<?php echo t('title_remove', 'Remove'); ?>">',
			'      <?php echo functions::draw_fonticon('remove'); ?>',
			'    </button>',
			'  </td>',
			'</tr>'
		].join('\n')
			.replace(/__index__/g, 'new_' + __index__)
		);

		$output.find(':input[name^="attributes[new_'+__index__+'][group_id]"]')
			.val( $newAttributeGroup.val() )
			.after( $('select[name="new_attribute[group_id]"] option:selected').text() );

		$output.find(':input[name^="attributes[new_'+__index__+'][value_id]"]')
			.val( $newAttributeValue.val() )
			.after(  $newAttributeValue.val() ? $('select[name="new_attribute[value_id]"] option:selected').text() : '' );

		$output.find(':input[name^="attributes[new_'+__index__+'][custom_value]"]')
			.val( $newCustomValue.val() )
			.after( $newCustomValue.val() );

		$('#attributes tbody').append($output);
	});

	$('#tab-attributes').on('click', 'button[name="remove"]', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});

	// Quantity Unit

	$('select[name="quantity_unit_id"]').on('change', function() {
		if ($('option:selected', this).data('decimals') === undefined) return;

		let decimals = $('option:selected', this).data('decimals');

		$('input[name="quantity_min"]').val( parseFloat($('input[name="quantity_min"]').val() || 0).toFixed(decimals) );
		$('input[name="quantity_max"]').val( parseFloat($('input[name="quantity_max"]').val() || 0).toFixed(decimals) );
		$('input[name="quantity_step"]').val( parseFloat($('input[name="quantity_step"]').val() || 0).toFixed(decimals) );
		$('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val() || 0).toFixed(decimals) );

		$('input[name^="stock_options"][name$="[quantity]"]').each(function() {
			$(this).val( parseFloat($(this).val() || 0).toFixed(decimals) );
		});

		$('input[name^="stock_options"][name$="[quantity_adjustment]"]').each(function() {
			$(this).val( parseFloat($(this).val() || 0).toFixed(decimals) );
		});
	}).trigger('change');

	// Quantity and Adjustments

	$('body').on('input', ':input[name="quantity"], :input[name$="[quantity]"]', function() {
		let $quantity_adjustment_field = $(':input[name="' + $(this).attr('name').replace('quantity', 'quantity_adjustment') + '"]'),
			quantity = parseFloat($(this).val()),
			quantity_adjustment = parseFloat($(this).val()) - parseFloat($(this).data('quantity')),
			decimals = parseInt($('select[name="quantity_unit_id"] option:selected').data('decimals'));

		$(':input[name="'+ $(this).attr('name')+'"]').not(this).val( quantity.toFixed(decimals) );
		$quantity_adjustment_field.val( quantity_adjustment.toFixed(decimals) );
	});

	$('body').on('input', ':input[name="quantity_adjustment"], :input[name$="[quantity_adjustment]"]', function() {
		let $quantity_field = $(':input[name="' + $(this).attr('name').replace('quantity_adjustment', 'quantity') + '"]'),
			quantity = parseFloat($quantity_field.data('quantity') || 0),
			quantity_adjustment = parseFloat($(this).val() || 0),
			decimals = parseInt($('select[name="quantity_unit_id"] option:selected').data('decimals') || 0);

		$(':input[name="'+ $(this).attr('name') +'"]').not(this).val( quantity_adjustment.toFixed(decimals) );
		$quantity_field.val( (quantity + quantity_adjustment).toFixed(decimals) );
	});

	// Transfer Backordered Quantity

	$('body').on('click', 'button[name*="transfer_backordered"]', function() {
		let $quantity_adjustment_field = $(':input[name="' + $(this).attr('name').replace('transfer_backordered', 'quantity_adjustment') +'"]'),
			$backordered_field = $(':input[name="' + $(this).attr('name').replace('transfer_backordered', 'backordered') +'"]'),
			quantity_adjustment = parseFloat($quantity_adjustment_field.val() || 0),
			backordered = parseFloat($backordered_field.val() || 0);

		$quantity_adjustment_field.val( quantity_adjustment + backordered ).trigger('input');
		$backordered_field.val('');
	});

	// Quantity Unit

	$('select[name="quantity_unit_id"]').on('change', function() {
		let decimals = parseInt($('select[name="quantity_unit_id"] option:selected').data('decimals'))
		$('input[name$="[quantity]"], input[name$="[quantity_adjustment]"], input[name$="[backordered]"]').each(function() {
			if ($(this).val() != '') {
				$(this).val( parseFloat($(this).val()).toFixed(decimals) )
			}
		})
	}).trigger('change')

	// Customizations

	$('#customizations').on('click', '.remove-group', function(e) {
		e.preventDefault();
		$(this).closest('li').remove();
	});

	$('#customizations').on('click', '.move-group-up, .move-group-down', function(e) {
		e.preventDefault();
		var $li = $(this).closest('li');
		if ($(this).is('.move-group-up') && $li.prevAll().length > 0) {
			$li.insertBefore($li.prev());
		} else if ($(this).is('.move-group-down') && $li.nextAll().length > 0) {
			$li.insertAfter($li.next());
		}
	});

	$('#customizations').on('click', 'button[name="remove"]', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});

	$('#customizations').on('click', 'button[name="move-up"], button[name="move-down"]', function(e) {
		e.preventDefault();
		var $row = $(this).closest('tr');
		if ($(this).is('.move-up') && $row.prevAll().length > 0) {
			$row.insertBefore($row.prev());
		} else if ($(this).is('.move-down') && $row.nextAll().length > 0) {
			$row.insertAfter($row.next());
		}
	});

	$('body').on('change', '.litebox select[name="new_predefined_customization[group_id]"]', function() {
		$.ajax({
			url: '<?php echo document::ilink('b:catalog/attribute_values.json'); ?>?group_id=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',
			success: function(data) {
				$('select[name="new_predefined_customization[value_id]"]').html('');
				if ($('select[name="new_predefined_customization[value_id]"]').attr('disabled')) $('select[name="new_predefined_customization[value_id]"]').prop('disabled', false);
				if (data) {
					$('select[name="new_predefined_customization[value_id]"]').append('<option value="0">-- <?php echo functions::escape_js(t('title_select', 'Select')); ?> --</option>');
					$.each(data, function(i, zone) {
						$('select[name="new_predefined_customization[value_id]"]').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="new_predefined_customization[value_id]"]').prop('disabled', true);
				}
			},
		});
	});

	$('body').on('change', '.litebox select[name="new_user_input_customization[group_id]"]', function() {
		$.ajax({
			url: '<?php echo document::ilink('b:catalog/attribute_values.json'); ?>?group_id=' + $(this).val(),
			type: 'get',
			cache: true,
			async: true,
			dataType: 'json',

			success: function(data) {
				$('select[name="new_user_input_customization[value_id]"]').html('');
				if ($('select[name="new_user_input_customization[value_id]"]').attr('disabled')) $('select[name="new_user_input_customization[value_id]"]').prop('disabled', false);

				if (data) {
					$('select[name="new_user_input_customization[value_id]"]').append('<option value="0">-- <?php echo functions::escape_js(t('title_select', 'Select')); ?> --</option>');
					$.each(data, function(i, zone) {
						$('select[name="new_user_input_customization[value_id]"]').append('<option value="'+ zone.id +'">'+ zone.name +'</option>');
					});
				} else {
					$('select[name="new_user_input_customization[value_id]"]').prop('disabled', true);
				}
			},
		});
	});

	$('body').on('change', '.litebox select[name="new_predefined_customization[value_id]"]', function() {
		$('input[name="new_predefined_customization[custom_value]"]').val('');
	});

	$('body').on('keydown', '.litebox input[name="new_predefined_customization[custom_value]"]', function() {
		$('select[name="new_predefined_customization[value_id]"]').val('0');
	});

	var new_customization_group_i = 1,
		new_customization_value_i = 1;

	$('body').on('click', '.litebox button[name="add_predefined_customization"]', function(e) {
		e.preventDefault();

		var groupElement = $(this).closest('fieldset').find('select[name="new_predefined_customization[group_id]"]'),
			valueElement = $(this).closest('fieldset').find('select[name="new_predefined_customization[value_id]"]'),
			customValueElement = $(this).closest('fieldset').find('input[name="new_predefined_customization[custom_value]"]');

		if ($(groupElement).val() == '') {
			alert("<?php echo functions::escape_js(t('error_must_select_attribute_group', 'You must select an attribute group')); ?>");
			return;
		}

		if ($(valueElement).val() == '' || $(valueElement).val() == '0') {
			if ($(customValueElement).val() == '') {
				alert("<?php echo functions::escape_js(t('error_must_select_attribute_value', 'You must select an attribute value')); ?>");
			}
		} else {
			if ($(customValueElement).val() != '') {
				alert("<?php echo functions::escape_js(t('error_cannot_define_both_value_and_custom_value', 'You cannot define both a value and a custom value')); ?>");
				return;
			}
		}

		if ($('#customizations :input[name^="customizations"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('input[name$="[value_id]"][value="'+ $(valueElement).val() +'"]').length) {
			if ($(customValueElement).val() != '') {
				if ($('#customizations :input[name^="customizations"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('input[name$="[custom_value]"][value="'+ escape($(customValueElement).val()) +'"]').length) {
					alert("<?php echo functions::escape_js(t('error_option_already_defined', 'This option is already defined')); ?>");
					return;
				}
			} else {
				if ($('#customizations :input[name^="customizations"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('input[name$="[value_id]"][value="'+ $(valueElement).val() +'"]').closest('tr').find('input[name$="[custom_value]"]').val() == $(customValueElement).val()) {
					alert("<?php echo functions::escape_js(t('error_option_already_defined', 'This option is already defined')); ?>");
					return;
				}
			}
		}

		if (!$('#customizations input[name^="customizations"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').length) {

			var $output = $([
				'<li data-group-id="'+ $(groupElement).val().escapeAttr() +'" data-group-name="'+ $(groupElement).find('option:selected').text().escapeAttr() +'">',
				'  <div class="float-end">',
				'    <a class="btn btn-default move-group-up" href="#" title="<?php echo functions::escape_js(t('text_move_up', 'Move up')); ?>"><?php echo functions::draw_fonticon('icon-arrow-up', 'style="color: #3399cc;"'); ?></a>',
				'    <a class="btn btn-default move-group-down" href="#" title="<?php echo functions::escape_js(t('text_move_down', 'Move down')); ?>"><?php echo functions::draw_fonticon('icon-arrow-down', 'style="color: #3399cc;"'); ?></a>',
				'    <a class="btn btn-default remove-group" href="#" title="<?php echo functions::escape_js(t('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('icon-times', 'style="color: #cc3333;"'); ?></a>',
				'  </div>',
				'  <h2>'+ $(this).closest('fieldset').find('select[name="new_predefined_customization[group_id]"] option:selected').text() +'</h2>',
				'  <?php echo functions::escape_js(functions::form_input_hidden('customizations[new_group_id][group_id]', 'new_group_id')); ?>',
				'  <div class="grid">',
				'    <div class="col-sm-4 col-md-2">',
				'      <div class="form-group">',
				'        <label><?php echo functions::escape_js(t('title_function', 'Function')); ?></label>',
				'        <?php echo functions::escape_js(functions::form_select('customizations[new_group_id][function]', ['select', 'radio', 'checkbox'], 'select')); ?>',
				'      </div>',
				'    </div>',
				'    <div class="col-sm-4 col-md-2">',
				'      <div class="form-group">',
				'        <label><?php echo functions::escape_js(t('title_sort_values', 'Sort Values')); ?></label>',
				'        <?php echo functions::escape_js(functions::form_select('customizations[new_group_id][sort]', $customizations_sort_options, 'custom')); ?>',
				'      </div>',
				'    </div>',
				'    <div class="col-sm-4 col-md-2">',
				'      <div class="form-group">',
				'        <label class="form-label"><?php echo functions::escape_js(t('title_required', 'Required')); ?></label>',
				'          <?php echo functions::escape_js(functions::form_checkbox('customizations[new_group_id][required]', ['1', t('title_required', 'Required')], true)); ?>',
				'        </div>',
				'      </div>',
				'    </div>',
				'  </div>',
				'  <div class="table-responsive">',
				'    <table id="table-customizations" class="table data-table">',
				'      <thead>',
				'        <tr draggable="true">',
				'          <th><?php echo functions::escape_js(t('title_option', 'Option')); ?></th>',
				'          <th style="width: 150px;"><?php echo functions::escape_js(t('title_price_modifier', 'Price Modifier')); ?></th>',
				'          <th colspan="<?php echo count(currency::$currencies); ?>"><?php echo functions::escape_js(t('title_price_adjustment', 'Price Adjustment')); ?></th>',
				'          <th style="width: 85px;"></th>',
				'        </tr>',
				'      </thead>',
				'      <tbody>',
				'      </tbody>',
				'    </table>',
				'  </div>',
				'</li>'
			].join('\n')
				.replace(/new_customization_group_i/g, 'new_' + new_customization_group_i++)
				.replace(/new_group_id/g, $(groupElement).val())
				.replace(/new_group_name/g, $(groupElement).find('option:selected').text())
			);

			$('#customizations').append($output);
		}

		var $output = $([
			'<tr draggable="true" data-value-id="'+ escapeHTML($(valueElement).val()) +'" data-value-name="'+ escapeHTML(($(valueElement).val() != 0) ? $(valueElement).find('option:selected').text() : $(customValueElement).val()) +'">',
			'  <td class="grabbable"><?php echo functions::escape_js(functions::form_input_hidden('customizations[new_group_id][values][new_customization_value_i][value_id]', 'new_value_id')) . functions::form_input_hidden('customizations[new_group_id][values][new_customization_value_i][custom_value]', 'new_custom_value'); ?>'+ (($.inArray($(valueElement).val(), ['', '0']) !== -1) ? $(customValueElement).val() : $(valueElement).find('option:selected').text()) +'</td>',
			'  <td class="text-center"><?php echo functions::escape_js(functions::form_select('customizations[new_group_id][values][new_customization_value_i][price_modifier]', ['+','%','*','='], true)); ?></td>',
			'  <?php foreach ($currency_codes as $currency_code) echo '<td style="width: 200px;">'. functions::escape_js(functions::form_select_currency($currency_code, 'customizations[new_group_id][values][new_customization_value_i]['. $currency_code. ']', '')) .'</td>'; ?>',
			'  <td class="text-end"><a class="btn btn-default btn-sm move-up" href="#" title="<?php echo functions::escape_js(t('text_move_up', 'Move up')); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a> <a class="btn btn-default btn-sm move-down" href="#" title="<?php echo functions::escape_js(t('text_move_down', 'Move down')); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a> <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_js(t('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>',
			'</tr>'
		].join('\n')
			.replace(/new_customization_value_i/g, 'new_' + new_customization_value_i++)
			.replace(/new_group_id/g, $(groupElement).val())
			.replace(/new_value_id/g, $(valueElement).val())
			.replace(/new_custom_value/g, $(customValueElement).val().replace('"', '&quot;'))
		);

		$(':input[name^="customizations"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').closest('li').find('tbody').append($output);

		$.litebox.close();
	});

	$('body').on('click', '.litebox button[name="add_user_input_option"]', function(e) {
		e.preventDefault();

		var groupElement = $(this).closest('fieldset').find('select[name="new_user_input_customization[group_id]"]');

		if ($(groupElement).val() == '') {
			alert("<?php echo functions::escape_js(t('error_must_select_attribute_group', 'You must select an attribute group')); ?>");
			return;
		}

		if ($('#customizations :input[name^="customizations"][name$="[group_id]"][value="'+ $(groupElement).val() +'"]').length) {
			alert("<?php echo functions::escape_js(t('error_group_already_defined', 'This group is already defined')); ?>");
			return;
		}

		var $output = $([
			'<li>',
			'  <div class="float-end">',
			'    <a class="move-group-up btn btn-default" href="#" title="<?php echo functions::escape_js(t('text_move_up', 'Move up')); ?>"><?php echo functions::draw_fonticon('icon-arrow-up', 'style="color: #3399cc;"'); ?></a>',
			'    <a class="move-group-down btn btn-default" href="#" title="<?php echo functions::escape_js(t('text_move_down', 'Move down')); ?>"><?php echo functions::draw_fonticon('icon-arrow-down', 'style="color: #3399cc;"'); ?></a>',
			'    <a class="remove-group btn btn-default" href="#" title="<?php echo functions::escape_js(t('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('icon-times', 'style="color: #cc3333;"'); ?></a>',
			'  </div>',
			'  <h2>'+ $(this).closest('fieldset').find('select[name="new_user_input_customization[group_id]"] option:selected').text() +'</h2>',
			'  <?php echo functions::escape_js(functions::form_input_hidden('customizations[new_group_id][group_id]', 'new_group_id')); ?>',
			'  <div class="grid">',
			'    <div class="col-sm-4 col-md-2">',
			'      <div class="form-group">',
			'        <label><?php echo functions::escape_js(t('title_function', 'Function')); ?></label>',
			'        <?php echo functions::escape_js(functions::form_select('customizations[new_group_id][function]', ['text', 'textarea'], 'text')); ?>',
			'      </div>',
			'    </div>',
			'    <div class="col-sm-4 col-md-2">',
			'      <div class="form-group">',
			'        <label><?php echo functions::escape_js(t('title_required', 'Required')); ?></label>',
			'        <?php echo functions::escape_js(functions::form_checkbox('customizations[new_group_id][required]', ['1', t('title_required', 'Required')], true)); ?>',
			'      </div>',
			'    </div>',
			'  </div>',
			'</li>'
		].join('\n')
			.replace(/new_group_id/g, $(groupElement).val())
			.replace(/new_group_name/g, $(groupElement).find('option:selected').text())
		);

		$('#customizations').append($output);

		$.litebox.close();
	});

	// Stock

	<?php if (currency::$currencies > 1) { ?>
	$('#stock-options').on('focusin', 'input[name^="stock_options"][name*="[price_adjustment]"]', function() {
		$(this).closest('.dropdown').addClass('open');
	});
	<?php } ?>

	$('#stock-options').on('input', 'input[name$="[quantity]"]', function() {
		var adjustment_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]');
		$(adjustment_field).val(parseFloat($(this).val() || 0) - parseFloat($(this).data('quantity') || 0));
	});

	$('#stock-options').on('input', 'input[name$="[quantity_adjustment]"]', function() {
		var qty_field = $(this).closest('tr').find('input[name$="[quantity]"]');
		$(qty_field).val(parseFloat($(qty_field).data('quantity') || 0) + parseFloat($(this).val() || 0));
	});

	$('#stock-options button[name="transfer"]').on('click', function() {
		var $quantity_field = $(this).closest('tr').find('input[name$="[quantity_adjustment]"]'),
			$backordered_field = $(this).closest('tr').find('input[name$="[backordered]"]');
		$quantity_field.val(parseFloat($(quantity_field).val() || 0) + parseFloat($(backordered_field).val() || 0)).trigger('input');
		$backordered_field.val(0);
	});

	$('#stock-options').on('click', '.move-up, .move-down', function(e) {
		e.preventDefault();
		var row = $(this).closest('tr');

		if ($(this).is('.move-up') && $(row).prevAll().length > 1) {
			$(row).insertBefore($(row).prev());
		} else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
			$(row).insertAfter($(row).next());
		}
	});

	$('#stock-options').on('click', '.remove', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();

		var total = 0;
		$(this).closest('tbody').find('input[name$="[quantity]"]').each(function() {
			total += parseFloat($(this).val() || 0);
		});

		if (!$('input[name^="stock_options"][name$="[id]"]').length) {

			$('input[name="quantity"]').prop('readonly', false);
			$('input[name="quantity_adjustment"]').prop('readonly', false);
			$('input[name="quantity"]').val('');
			$('input[name="quantity_adjustment"]').val('');

		} else {

			$('input[name="quantity"]').val(0);
			$('input[name^="stock_options"][name$="[quantity]"]').each(function() {
				$('input[name="quantity"]').val( parseFloat($('input[name="quantity"]').val() || 0) + parseFloat($(this).val() || 0) );
			});

			$('input[name="quantity_adjustment"]').val(0);
			$('input[name^="stock_options"][name$="[quantity_adjustment]"]').each(function() {
				$('input[name="quantity_adjustment"]').val( parseFloat($('input[name="quantity_adjustment"]').val() || 0) + parseFloat($(this).val() || 0) );
			});
		}
	});

	let new_stock_item_i = 1;
	while ($('input[name="stock_options[new_'+new_stock_item_i+']"]').length) new_stock_item_i++;

	window.upsert_stock_item = function(stock_item) {

		var $output = $([
			'<tr draggable="true" data-stock-item-id="'+ stock_item.id +'">',
			'  <td class="grabbable">',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][id]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][stock_item_id]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][sku]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][name]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][weight]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][weight_unit]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][length]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][width]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][height]', '')); ?>',
			'    <?php echo functions::escape_js(functions::form_input_hidden('stock_options[new_stock_item_i][length_unit]', '')); ?>',
			'    <span class="name"></name>',
			'  </td>',
			'  <td>',
			'    <span class="sku"></span>',
			'  </td>',
			'  <td><?php echo functions::escape_js(functions::form_select('stock_options[new_stock_item_i][price_modifier]', ['+', '*', '%', '='], '+')); ?></td>',
			'  <td>',
			'    <div class="dropdown">',
			'      <?php echo functions::escape_js(functions::form_input_money('stock_options[new_stock_item_i][price_adjustment]['. settings::get('store_currency_code') .']', settings::get('store_currency_code'), '', 'style="width: 125px;"')); ?>',
			'      <ul class="dropdown-menu">',
			<?php foreach (currency::$currencies as $currency) { ?>
			<?php if ($currency['code'] == settings::get('store_currency_code')) continue; ?>
			'        <li><?php echo functions::escape_js(functions::form_input_money('stock_options[new_stock_item_i][price_adjustment]['. $currency['code'] .']', $currency['code'], '', 'style="width: 125px;"')); ?></li>',
			<?php } ?>
			'      </ul>',
			'    </div>',
			'  </td>',
			'  <td><?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_item_i][quantity]', '0', 2, 'data-quantity="new_stock_item_quantity"')); ?></td>',
			'  <td>',
			'    <label class="input-group">',
			'      <span class="input-group-text">&plusmn;</span>',
			'    <?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_item_i][quantity_adjustment]', '0')); ?>',
			'    </label>',
			'  </td>',
			'  <td>',
			'    <div class="input-group">',
			'      <?php echo functions::escape_js(functions::form_button('transfer', functions::draw_fonticon('icon-arrow-left'), 'button')); ?>',
			'      <?php echo functions::escape_js(functions::form_input_decimal('stock_options[new_stock_item_i][backordered]', '', 2, 'min="0"')); ?>',
			'    </div>',
			'  </td>',
			'  <td class="text-end">',
			'    <a class="remove btn btn-default btn-sm" href="#" title="<?php echo functions::escape_js(t('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('icon-times', 'style="color: #c33;"')); ?></a>',
			'  </td>',
			'  <td class="text-end">',
			'    <a class="edit btn btn-default btn-sm" href="<?php echo document::href_ilink(__APP__.'/edit_stock_item', ['stock_item_id' => 'new_stock_item_id', 'js_callback' => 'upsert_stock_item'], ['app']); ?>" data-toggle="lightbox" data-seamless="true" data-max-width="980px" title="<?php echo t('title_edit', 'Edit'); ?>"><?php echo functions::draw_fonticon('edit'); ?></a>',
			'  </td>',
			'</tr>'
		].join('\n')
			.replace(/new_stock_item_i/g, 'new_'+new_stock_item_i)
			.replace(/new_stock_item_id/g, stock_item.id)
		);

		$.each(Object.keys(stock_item), function(i, key) { // Iterate Object.keys() because jQuery.each() doesn't support a property named length
			switch (key) {

				case 'id':
					key = 'stock_item_id';
					var value = stock_item.id;
					break;

				case 'name':
					if ($.isPlainObject(stock_item.name)) {
						var value = stock_item.name.<?php echo language::$selected['code']; ?>;
					} else {
						var value = stock_item.name;
					}
					break;

				case 'quantity_adjustment':
					break;

				default:
					var value = stock_item[key];
					break;
			}

			$output.find(':input[name$="['+ key +']"]').val(value);
			$output.find('.'+ key).text(value);
		});

		if ($('#stock-options tbody tr[data-stock-item-id="'+ stock_item.id +'"]').length) {
			$('#stock-options tbody tr[data-stock-item-id="'+ stock_item.id +'"]').replaceWith($output);
		} else {
			$('#stock-options tbody').append($output);
		}
	};
</script>