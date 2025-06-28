<?php

	if (!empty($_GET['brand_id'])) {
		$brand = new ent_brand($_GET['brand_id']);
	} else {
		$brand = new ent_brand();
	}

	if (!$_POST) {
		$_POST = $brand->data;
	}

	document::$title[] = !empty($brand->data['id']) ? t('title_edit_brand', 'Edit Brand') :  t('title_create_new_brand', 'Create New Brand');

	breadcrumbs::add(t('title_catalog', 'Catalog'));
	breadcrumbs::add(t('title_brands', 'Brands'), document::ilink(__APP__.'/brands'));
	breadcrumbs::add(!empty($brand->data['id']) ? t('title_edit_brand', 'Edit Brand') :  t('title_create_new_brand', 'Create New Brand'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['name'])) {
				throw new Exception(t('error_must_provide_name', 'You must provide a name'));
			}

			if (!empty($_POST['code'])) {
				if (database::query(
					"select id from ". DB_TABLE_PREFIX ."brands
					where id != '". (isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0) ."'
					and code = '". database::input($_POST['code']) ."' limit 1;"
				)->num_rows) {
					throw new Exception(t('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
				}
			}

			foreach ([
				'status',
				'featured',
				'code',
				'name',
				'short_description',
				'description',
				'keywords',
				'head_title',
				'h1_title',
				'meta_description',
				'link',
			] as $field) {
				if (isset($_POST[$field])) {
					$brand->data[$field] = $_POST[$field];
				}
			}

			$brand->save();

			if (!empty($_POST['delete_image'])) $brand->delete_image();

			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				$brand->save_image($_FILES['image']['tmp_name']);
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/brands'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {

			if (empty($brand->data['id'])) {
				throw new Exception(t('error_must_provide_brand', 'You must provide a brand'));
			}

			$brand->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/brands'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}
?>

<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($brand->data['id']) ? t('title_edit_brand', 'Edit Brand') :  t('title_create_new_brand', 'Create New Brand'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('brand_form', 'post', false, true, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
						<?php echo functions::form_toggle('status', 'e/d', (file_get_contents('php://input') != '') ? true : '1'); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_name', 'Name'); ?></div>
						<?php echo functions::form_input_text('name', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_featured', 'Featured'); ?></div>
						<?php echo functions::form_toggle('featured', 'y/n', fallback($_POST['featured'], '1')); ?>
					</label>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_code', 'Code'); ?></div>
						<?php echo functions::form_input_text('code', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-6">
					<div id="image">
						<?php if (!empty($brand->data['image'])) { ?>
						<div style="margin-bottom: 15px;">
						<?php echo functions::draw_thumbnail('storage://images/' . $brand->data['image'], 360, 120); ?>
						</div>
						<?php } ?>

						<label class="form-group">
							<div class="form-label"><?php echo !empty($brand->data['image']) ? t('title_new_image', 'New Image') : t('title_image', 'Image'); ?></div>
							<?php echo functions::form_input_file('image', 'accept="image/*"'); ?>
							<?php if (!empty($brand->data['image'])) { ?>
							<?php echo functions::form_checkbox('delete_image', ['true', t('title_delete', 'Delete')], true); ?>
							<?php } ?>
						</label>
					</div>
				</div>

				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_keywords', 'Keywords'); ?></div>
						<?php echo functions::form_input_tags('keywords', true); ?>
					</label>
				</div>
			</div>

			<nav class="tabs">
				<?php foreach (language::$languages as $language) { ?>
				<a class="tab-item<?php if ($language['code'] == language::$selected['code']) echo ' active'; ?>"" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
				<?php } ?>
			</nav>

			<div class="tab-contents">

					<?php foreach (array_keys(language::$languages) as $language_code) { ?>
					<div id="<?php echo $language_code; ?>" class="tab-content<?php if ($language_code == language::$selected['code']) echo ' selected'; ?>">

					<label class="form-group">
						<div class="form-label"><?php echo t('title_h1_title', 'H1 Title'); ?></div>
						<?php echo functions::form_regional_text('h1_title['. $language_code .']', $language_code, true, ''); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_short_description', 'Short Description'); ?></div>
						<?php echo functions::form_regional_text('short_description['. $language_code .']', $language_code, true); ?>
					</label>

					<div class="form-group">
						<div class="form-label"><?php echo t('title_description', 'Description'); ?></div>
						<?php echo functions::form_regional_wysiwyg('description['. $language_code .']', $language_code, true, 'style="height: 240px;"'); ?>
					</div>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_link', 'Link'); ?></div>
						<?php echo functions::form_regional_text('link['. $language_code .']', $language_code, true); ?>
					</label>

					<div class="grid">
						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_head_title', 'Head Title'); ?></div>
								<?php echo functions::form_regional_text('head_title['. $language_code .']', $language_code, true, ''); ?>
							</label>
						</div>

						<div class="col-md-6">
							<label class="form-group">
								<div class="form-label"><?php echo t('title_meta_description', 'Meta Description'); ?></div>
								<?php echo functions::form_regional_text('meta_description['. $language_code .']', $language_code, true); ?>
							</label>
						</div>
					</div>
				</nav>
				<?php } ?>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($brand->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<script>
	$('input[name="name"]').on('input', function(e) {
		$('input[name^="head_title"]').attr('placeholder', $(this).val())
		$('input[name^="h1_title"]').attr('placeholder', $(this).val())
	}).trigger('input')

	$('input[name="image"]').on('change', function(e) {
		if ($(this).val() != '') {
			let oFReader = new FileReader()
			oFReader.readAsDataURL(this.files[0])
			oFReader.onload = function(e){
				$('#image img').attr('src', e.target.result)
			}
		} else {
			$('#image img').attr('src', '<?php echo functions::draw_thumbnail('storage://images/' . $brand->data['image'], 400, 100); ?>')
		}
	})

	$('input[name^="short_description"]').on('input', function(e) {
		let language_code = $(this).attr('name').match(/\[(.*)\]$/)[1]
		$('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val())
	}).trigger('input')
</script>