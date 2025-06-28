<?php

	if (!empty($_GET['page_id'])) {
		$page = new ent_page($_GET['page_id']);

	} else {
		$page = new ent_page();
	}

	if (!$_POST) {
		$_POST = $page->data;

		if ($page->data['dock']) {
			$_POST['parent'] = $page->data['dock'] .':'. $page->data['parent_id'];
		} else {
			$_POST['parent'] = '';
		}
	}

	document::$title[] = !empty($page->data['id']) ? t('title_edit_page', 'Edit Page') : t('title_create_new_page', 'Create New Page');

	breadcrumbs::add(t('title_pages', 'Pages'), document::ilink(__APP__.'/pages'));
	breadcrumbs::add(!empty($page->data['id']) ? t('title_edit_page', 'Edit Page') : t('title_create_new_page', 'Create New Page'), document::ilink());

	if (isset($_POST['save'])) {

		try {

			if (empty($_POST['title'])) {
				throw new Exception(t('error_must_provide_title', 'You must provide a title'));
			}

			if (empty($_POST['status'])) {
				$_POST['status'] = 0;
			}

			list($page->data['dock'], $page->data['parent_id']) = preg_split('#:#', $_POST['parent'], 2, PREG_SPLIT_NO_EMPTY);

			foreach ([
				'status',
				'parent_id',
				'title',
				'content',
				'dock',
				'priority',
				'head_title',
				'meta_description',
			] as $field) {
				if (isset($_POST[$field])) {
					$page->data[$field] = $_POST[$field];
				}
			}

			$page->save();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/pages'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['delete'])) {

		try {
			if (empty($page->data['id'])) throw new Exception(t('error_must_provide_page', 'You must provide a page'));

			$page->delete();

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			redirect(document::ilink(__APP__.'/pages'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

?>
<div class="card">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo !empty($page->data['id']) ? t('title_edit_page', 'Edit Page') : t('title_create_new_page', 'Create New Page'); ?>
		</div>
	</div>

	<div class="card-body">
		<?php echo functions::form_begin('pages_form', 'post', false, false, 'style="max-width: 720px;"'); ?>

			<div class="grid">
				<div class="col-md-6">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_status', 'Status'); ?></div>
						<?php echo functions::form_toggle('status', 'e/d', true); ?>
					</label>
				</div>
			</div>

			<div class="grid">
				<div class="col-md-8">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_parent', 'Parent'); ?></div>
						<?php echo functions::form_select_parent_page('parent', true); ?>
					</label>
				</div>

				<div class="col-md-4">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_priority', 'Priority'); ?></div>
						<?php echo functions::form_input_number('priority', true); ?>
					</label>
				</div>
			</div>

			<?php if (count(language::$languages) > 1) { ?>
			<nav class="tabs">
				<?php foreach (language::$languages as $language) { ?>
				<a class="tab-item<?php if ($language['code'] == language::$selected['code']) echo ' active'; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
				<?php } ?>
			</nav>
			<?php } ?>

			<div class="tab-contents">
				<?php foreach (array_keys(language::$languages) as $language_code) { ?>
				<div id="<?php echo $language_code; ?>" class="tab-content<?php if ($language_code == language::$selected['code']) echo ' active'; ?>">
					<label class="form-group">
						<div class="form-label"><?php echo t('title_title', 'Title'); ?></div>
						<?php echo functions::form_regional_text('title['. $language_code .']', $language_code, true, ''); ?>
					</label>

					<div class="form-group">
						<div class="form-label"><?php echo t('title_content', 'Content'); ?></div>
						<?php echo functions::form_regional_wysiwyg('content['. $language_code .']', $language_code, true, 'style="height: 400px;"'); ?>
					</div>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_head_title', 'Head Title'); ?></div>
						<?php echo functions::form_regional_text('head_title['. $language_code .']', $language_code, true); ?>
					</label>

					<label class="form-group">
						<div class="form-label"><?php echo t('title_meta_description', 'Meta Description'); ?></div>
						<?php echo functions::form_regional_text('meta_description['. $language_code .']', $language_code, true); ?>
					</label>
				</div>
				<?php } ?>
			</div>

			<div class="card-action">
				<?php echo functions::form_button_predefined('save'); ?>
				<?php if (!empty($page->data['id'])) echo functions::form_button_predefined('delete'); ?>
				<?php echo functions::form_button_predefined('cancel'); ?>
			</div>

		<?php echo functions::form_end(); ?>
	</div>
</div>

<script>
	$('input[name^="title"]').on('input', function(e) {
		let language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
		$('.tabs a[href="#'+language_code+'"]').css('opacity', $(this).val() ? 1 : .5);
		$('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
	}).trigger('input');
</script>