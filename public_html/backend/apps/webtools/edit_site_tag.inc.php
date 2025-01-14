<?php

  if (!empty($_GET['site_tag_id'])) {
    $site_tag = new ent_site_tag($_GET['site_tag_id']);
  } else {
    $site_tag = new ent_site_tag();
  }

  if (!$_POST) {
    $_POST = $site_tag->data;
  }

  breadcrumbs::add(!empty($site_tag->data['id']) ? language::translate('title_edit_site_tag', 'Edit Site Tag') : language::translate('title_create_new_site_tag', 'Create New Site Tag'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['content'])) {
        throw new Exception(language::translate('error_must_provide_position', 'You must provide position'));
      }

      if (empty($_POST['status'])) {
				$_POST['status'] = 0;
			}
      if (empty($_POST['require_cookie_consent'])) {
				$_POST['require_cookie_consent'] = 0;
			}

      foreach ([
        'status',
        'position',
        'description',
        'content',
        'require_cookie_consent',
        'priority',
      ] as $field) {
        if (isset($_POST[$field])) {
          $site_tag->data[$field] = $_POST[$field];
        }
      }

      $site_tag->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/site_tags'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($site_tag->data['id'])) {
        throw new Exception(language::translate('error_must_provide_site_tag', 'You must provide a site tag'));
      }

      $site_tag->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/site_tags'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

	$position_options = [
		'head' => language::translate('title_head', 'Head'),
		'body' => language::translate('title_body', 'Body'),
	];

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($site_tag->data['id']) ? language::translate('title_edit_site_tag', 'Edit Site Tag') : language::translate('title_create_new_site_tag', 'Create New Site Tag'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('site_tag_form', 'post', false, false, 'autocomplete="off" style="max-width: 960px;"'); ?>

      <div class="grid">
        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_status', 'Status'); ?></div>
            <?php echo functions::form_toggle('status', 'e/d', true); ?>
          </label>
        </div>

        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_priority', 'Priority'); ?></div>
            <?php echo functions::form_input_number('priority', true); ?>
          </label>
        </div>
      </div>

      <div class="grid">
        <div class="col-sm-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_description', 'Description'); ?></div>
            <?php echo functions::form_input_text('description', true, 'required'); ?>
          </label>
        </div>

        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_position', 'Position'); ?></div>
            <?php echo functions::form_select('position', $position_options, true); ?>
          </label>
        </div>
      </div>

      <label class="form-group">
        <div class="form-label"><?php echo language::translate('title_html_content', 'HTML Content'); ?></div>
        <?php echo functions::form_input_code('content', true, 'required style="height: 480px;"'); ?>
      </label>

      <div class="form-group">
        <?php echo functions::form_checkbox('require_cookie_consent', ['1', language::translate('text_require_cookie_consent', 'Require cookie consent')], true); ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (!empty($site_tag->data['id'])) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>
