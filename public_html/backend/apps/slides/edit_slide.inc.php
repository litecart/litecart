<?php

  if (!empty($_GET['slide_id'])) {
    $slide = new ent_slide($_GET['slide_id']);
  } else {
    $slide = new ent_slide();
    $slide->data['status'] = 1;
  }

  if (!$_POST) {
    $_POST = $slide->data;
  }

  document::$snippets['title'][] = !empty($slide->data['id']) ? language::translate('title_edit_slide', 'Edit Slide') : language::translate('title_create_new_slide', 'Create New Slide');

  breadcrumbs::add(language::translate('title_slides', 'Slides'), document::ilink(__APP__.'/slides'));
  breadcrumbs::add(!empty($slide->data['id']) ? language::translate('title_edit_slide', 'Edit Slide') : language::translate('title_create_new_slide', 'Create New Slide'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name']) && !empty($_FILES['image']['error'])) {
        throw new Exception(language::translate('error_uploaded_image_rejected', 'An uploaded image was rejected for unknown reason'));
      }

      if (empty($_POST['status'])) $_POST['status'] = 0;
      if (empty($_POST['languages'])) $_POST['languages'] = [];

      $fields = [
        'status',
        'languages',
        'name',
        'caption',
        'link',
        'priority',
        'date_valid_from',
        'date_valid_to',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $slide->data[$field] = $_POST[$field];
      }

      if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $slide->save_image($_FILES['image']['tmp_name']);
      }

      $slide->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/slides'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($slide->data['id'])) throw new Exception(language::translate('error_must_provide_slide', 'You must provide a slide'));

      $slide->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/slides'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($slide->data['id']) ? language::translate('title_edit_slide', 'Edit Slide') : language::translate('title_create_new_slide', 'Create New Slide'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('slide_form', 'post', false, true, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_status', 'Status'); ?></label>
          <?php echo functions::form_toggle('status', 'e/d', true); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_languages', 'Languages'); ?> <em>(<?php echo language::translate('text_leave_blank_for_all', 'Leave blank for all'); ?>)</em></label>
        <div><?php echo functions::form_languages_list('languages[]', true); ?></div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_text_field('name', true); ?>
        </div>
      </div>

      <?php if (!empty($slide->data['image'])) echo '<p><img src="'. document::href_rlink('storage://images/' . $slide->data['image']) .'" alt="" class="responsive" /></p>'; ?>

      <div class="form-group">
        <label><?php echo language::translate('title_image', 'Image'); ?></label>
        <?php echo functions::form_file_field('image', 'accept="image/*,.svg"'); ?>
        <?php echo !empty($slide->data['image']) ? '</label>' . $slide->data['image'] : ''; ?>
      </div>

      <nav class="nav nav-tabs">
        <?php foreach (language::$languages as $language) { ?>
        <a class="nav-link<?php echo ($language['code'] == language::$selected['code']) ? ' active' : ''; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
        <?php } ?>
      </nav>

      <div class="tab-content">
        <?php foreach (array_keys(language::$languages) as $language_code) { ?>
        <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">
          <div class="form-group">
            <label><?php echo language::translate('title_caption', 'Caption'); ?></label>
            <?php echo functions::form_regional_wysiwyg_field('caption['. $language_code .']', $language_code, true, 'style="height: 240px;"'); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_link', 'Link'); ?></label>
            <?php echo functions::form_regional_text_field('link['. $language_code .']', $language_code, true, ''); ?>
          </div>
        </div>
        <?php } ?>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></label>
          <?php echo functions::form_datetime_field('date_valid_from', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></label>
          <?php echo functions::form_datetime_field('date_valid_to', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-3">
          <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
          <?php echo functions::form_number_field('priority', true); ?>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo !empty($slide->data['id']) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<script>
  $('input[name^="caption"]').on('input', function(e){
    let language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('.nav-tabs a[href="#'+language_code+'"]').css('opacity', $(this).val() ? 1 : .5);
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');
</script>