<?php

  if (!empty($_GET['page_id'])) {
    $page = new ent_page($_GET['page_id']);
  } else {
    $page = new ent_page();
  }

  if (empty($_POST)) {
    $_POST = $page->data;
  }

  document::$snippets['title'][] = !empty($page->data['id']) ? language::translate('title_edit_page', 'Edit Page') : language::translate('title_create_new_page', 'Create New Page');

  breadcrumbs::add(language::translate('title_pages', 'Pages'), document::link(WS_DIR_ADMIN, ['doc' => 'pages'], ['app']));
  breadcrumbs::add(!empty($page->data['id']) ? language::translate('title_edit_page', 'Edit Page') : language::translate('title_create_new_page', 'Create New Page'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['title'])) throw new Exception(language::translate('error_missing_title', 'You must enter a title.'));

      if (empty($_POST['status'])) $_POST['status'] = 0;
      if (empty($_POST['dock'])) $_POST['dock'] = [];

      $fields = [
        'status',
        'parent_id',
        'title',
        'content',
        'dock',
        'priority',
        'head_title',
        'meta_description',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $page->data[$field] = $_POST[$field];
      }

      $page->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'pages'], true, ['page_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($page->data['id'])) throw new Exception(language::translate('error_must_provide_page', 'You must provide a page'));

      $page->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'pages'], true, ['page_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo !empty($page->data['id']) ? language::translate('title_edit_page', 'Edit Page') : language::translate('title_create_new_page', 'Create New Page'); ?>
    </div>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('pages_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_status', 'Status'); ?></label>
          <?php echo functions::form_draw_toggle('status', (isset($_POST['status'])) ? $_POST['status'] : '1', 'e/d'); ?>
        </div>
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
          <?php echo functions::form_draw_number_field('priority', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_dock', 'Dock'); ?></label>
          <div class="checkbox">
            <label><?php echo functions::form_draw_checkbox('dock[]', 'menu', true); ?> <?php echo language::translate('text_dock_in_site_menu', 'Dock in site menu'); ?></label><br />
            <label><?php echo functions::form_draw_checkbox('dock[]', 'customer_service', true); ?> <?php echo language::translate('text_dock_in_customer_service', 'Dock in customer service'); ?></label><br />
            <label><?php echo functions::form_draw_checkbox('dock[]', 'information', true); ?> <?php echo language::translate('text_dock_in_information', 'Dock in information'); ?></label>
          </div>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_parent', 'Parent'); ?></label>
          <?php echo functions::form_draw_pages_list('parent_id', true); ?>
        </div>
      </div>

      <ul class="nav nav-tabs">
        <?php foreach (language::$languages as $language) { ?>
          <li<?php echo ($language['code'] == language::$selected['code']) ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a></li>
        <?php } ?>
      </ul>

      <div class="tab-content">
        <?php foreach (array_keys(language::$languages) as $language_code) { ?>
        <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">
          <div class="form-group">
            <label><?php echo language::translate('title_title', 'Title'); ?></label>
            <?php echo functions::form_draw_regional_input_field($language_code, 'title['. $language_code .']', true, ''); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_content', 'Content'); ?></label>
            <?php echo functions::form_draw_regional_wysiwyg_field($language_code, 'content['. $language_code .']', true, 'style="height: 400px;"'); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
            <?php echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
            <?php echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true); ?>
          </div>
        </div>
        <?php } ?>
      </div>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($page->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $('input[name^="title"]').on('input', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('.nav-tabs a[href="#'+language_code+'"]').css('opacity', $(this).val() ? 1 : .5);
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');
</script>