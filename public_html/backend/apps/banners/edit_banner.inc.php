<?php

  if (!empty($_GET['banner_id'])) {
    $banner = new ent_banner($_GET['banner_id']);
  } else {
    $banner = new ent_banner();
  }

  if (!$_POST) {
    $_POST = $banner->data;
  }

  document::$snippets['title'][] = !empty($banner->data['id']) ? language::translate('title_edit_banner', 'Edit Banner') : language::translate('title_create_new_banner', 'Create New Banner');

  breadcrumbs::add(language::translate('title_banners', 'Banners'));
  breadcrumbs::add(!empty($banner->data['id']) ? language::translate('title_edit_banner', 'Edit Banner') : language::translate('title_create_new_banner', 'Create New Banner'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

      if (empty($banner->data['id'])) {
        if (empty($_POST['html']) && empty($_FILES['image'])) throw new Exception(language::translate('error_must_upload_image_or_enter_html', 'You must upload an image or enter HTML'));
        if (!empty($_POST['image']) && empty($_POST['link'])) throw new Exception(language::translate('error_must_enter_link', 'You must enter a target link'));
      }

      if (empty($_POST['languages'])) $_POST['languages'] = [];

      $fields = [
        'status',
        'name',
        'languages',
        'link',
        'html',
        'keywords',
        'date_valid_from',
        'date_valid_to',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $banner->data[$field] = $_POST[$field];
      }

      if (is_uploaded_file($_FILES['image']['tmp_name'])) $banner->save_image($_FILES['image']['tmp_name']);

      $banner->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/banners'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      $banner->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes were successfully saved.'));
      header('Location: '. document::ilink(__APP__.'/banners'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>
<style>
table th {
  min-width: 250px;
}
table th:last-child {
  min-width: auto;
}
textarea[name="html"] {
  background: #333;
  color: #fff;
  font-family: monospace;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo (!empty($banner->data['id'])) ? language::translate('title_edit_banner', 'Edit Banner') : language::translate('title_create_new_banner', 'Create New Banner'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('banner_form', 'post', '', true, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_status', 'Status'); ?></label>
          <?php echo functions::form_draw_toggle('status', 'e/d', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_draw_text_field('name', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_languages', 'Languages'); ?> <em>(<?php echo language::translate('text_leave_blank_for_all', 'Leave blank for all'); ?>)</em></label>
          <div><?php echo functions::form_draw_languages_list('languages[]', true); ?></div>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_image', 'Image'); ?></label>
        <?php echo functions::form_draw_file_field('image', 'accept="image/*"'); ?>
        <?php echo (!empty($banner->data['image'])) ? '<div>' . $banner->data['image'] .'</div>' : ''; ?>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_link', 'Link'); ?></label>
        <?php echo functions::form_draw_url_field('link', true); ?>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_html', 'HTML'); ?></label>
        <div class="form-control" style="padding: 0;">
          <?php echo functions::form_draw_textarea('html', true, 'placeholder="'. functions::escape_html('<a href="$target_url"><img class="img-responsive" src="$image_url" /></a>') .'" style="height: 150px;"'); ?>
          <div style="padding: 0.5em; background: #efefef;">
            <?php echo language::translate('title_aliases', 'Aliases'); ?>: <em>$uid, $key, $language_code, $image_url, $target_url</em>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_banner_pools', 'Banner Pools'); ?> / <?php echo language::translate('title_keywords', 'Keywords'); ?></label>
        <?php echo functions::form_draw_text_field('keywords', true, 'placeholder="banner1,banner2,banner3"'); ?>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_date_valid_from', 'Date Valid From'); ?></label>
          <?php echo functions::form_draw_datetime_field('date_valid_from', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_date_valid_to', 'Date Valid To'); ?></label>
          <?php echo functions::form_draw_datetime_field('date_valid_to', true); ?>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (!empty($banner->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $('.data-table').on('input', ':input[name^="keys"]', function(){

    var key = $(this).val();
    var row = $(this).closest('tr');

    $(this).attr('name', $(this).attr('name').replace(/^keys\[([^\]]+)?\]/, 'keys['+ key +']'));

    $.each($(row).find(':input[name^="values["]'), function(i, field) {
      var matches = $(field).attr('name').match(/^values\[(.*?)\]\[(.*?)\]$/);
      $(field).attr('name', 'values['+ matches[1] +']['+ key +']');
    });
  });

  var new_key_i = 1;
  $('.data-table .add').click(function(e){
    e.preventDefault();

    while ($(':input[name="keys[key_'+new_key_i+']"]').length) new_key_i++;

    var output = '<tr>' +
                 '  <td><?php echo functions::form_draw_text_field('keys[key_new_key_i]', 'key_new_key_i', 'required pattern="[0-9A-Za-z_-]+" placeholder="keyname"'); ?></td>' +
                 <?php foreach (language::$languages as $language) { ?>
                 '  <td><?php echo functions::form_draw_text_field('values['. $language['code'] .'][key_new_key_i]', true); ?></td>' +
                 <?php } ?>
                 '  <td><a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>' +
                 '</tr>';

    output = output.replace(/new_key_i/g, new_key_i);

    $('.data-table tbody').append(output);
  });

  $('.data-table').on('click', '.remove', function(e){
    e.preventDefault();
    $(this).closest('tr').remove();
  });

  $('a.tracker-wrapper-help').click(function(e){
    e.preventDefault();
    alert(
      "Encapsulates the code with a div wrapper and makes it trackable.\n\n" +
      "When disabled use the following parameters on an element for tracking:\n" +
      "id=\"banner-$uid\" class=\"banner ...\""
    );
  });

  $('a.keywords-help').click(function(e){
    e.preventDefault();
    alert(
      "Supported Triggers:\n" +
      "* Show for all set 'always' or leave blank\n" +
      "* Show for a certain language set language code e.g. 'en'\n" +
      "* Show for a certain banner set banner code e.g. 'US'\n" +
      "* Language and banner combined e.g. 'en-US'\n" +
      "* Show for users coming from a Google Analytics campaign, set your utm_campaign value\n" +
      "* Show when nothing else was matched set '?'\n" +
      "\n" +
      "Example: en,US,?"
    );
  });
</script>