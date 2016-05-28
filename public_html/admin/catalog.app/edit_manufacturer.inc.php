<?php

  if (!empty($_GET['manufacturer_id'])) {
    $manufacturer = new ctrl_manufacturer($_GET['manufacturer_id']);
  } else {
    $manufacturer = new ctrl_manufacturer();
  }

  if (empty($_POST)) {
    foreach ($manufacturer->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($manufacturer->data['id']) ? language::translate('title_edit_manufacturer', 'Edit Manufacturer') :  language::translate('title_add_new_manufacturer', 'Add New Manufacturer'));

  if (isset($_POST['save'])) {

    if (empty($_POST['name'])) notices::add('errors', language::translate('error_name_missing', 'You must enter a name.'));

    if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_MANUFACTURERS ." where id != '". (isset($_GET['manufacturer_id']) ? (int)$_GET['manufacturer_id'] : 0) ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) notices::add('errors', language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));

    if (empty(notices::$data['errors'])) {

      if (!empty($_POST['remove_image']) && !empty($manufacturer->data['image'])) {
        functions::image_delete_cache(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image']);
        if (file_exists(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image'])) unlink(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image']);
        $manufacturer->data['image'] = '';
      }

      $fields = array(
        'status',
        'code',
        'name',
        'short_description',
        'description',
        'keywords',
        'head_title',
        'h1_title',
        'meta_description',
        'link',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $manufacturer->data[$field] = $_POST[$field];
      }

      $manufacturer->save();

      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $manufacturer->save_image($_FILES['image']['tmp_name']);
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'manufacturers'), array('app')));
      exit;
    }
  }

  // Delete from database
  if (isset($_POST['delete']) && $manufacturer) {

    $manufacturer->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'manufacturers'), array('app')));
    exit();
  }

  document::$snippets['head_tags']['jquery-tabs'] = '<script src="'. WS_DIR_EXT .'jquery/jquery.tabs.js"></script>';

?>

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($manufacturer->data['id']) ? language::translate('title_edit_manufacturer', 'Edit Manufacturer') :  language::translate('title_add_new_manufacturer', 'Add New Manufacturer'); ?></h1>

<?php
  if (!empty($manufacturer->data['image'])) {
    echo '<p><img src="'. functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer->data['image'], 400, 100, 'FIT') .'" alt="" /></p>';
  }
?>

<?php echo functions::form_draw_form_begin(false, 'post', false, true); ?>

  <div class="tabs">

    <ul class="index">
      <li><a href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
      <li><a href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    </ul>

    <div class="content">
      <div id="tab-general">
        <table>
          <tr>
            <td><strong><?php echo language::translate('title_status', 'Status'); ?></strong><br />
              <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
            </td>
          </tr>
          <tr>
            <td>
              <strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
              <?php echo functions::form_draw_text_field('name', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_code', 'Code'); ?></strong><br />
              <?php echo functions::form_draw_text_field('code', true); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo !empty($manufacturer->data['image']) ? language::translate('title_new_image', 'New Image') : language::translate('title_image', 'Image'); ?></strong><br />
            <?php echo functions::form_draw_file_field('image', ''); ?></td>
          </tr>
          <?php if (!empty($manufacturer->data['image'])) { ?>
          <tr>
            <td><?php echo $manufacturer->data['image']; ?><br />
            <?php echo functions::form_draw_checkbox('remove_image', '1', true); ?> <?php echo language::translate('text_remove_image', 'Remove image'); ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td><strong><?php echo language::translate('title_keywords', 'Keywords'); ?></strong><br />
              <?php echo functions::form_draw_text_field('keywords', true, 'data-size="large"'); ?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_short_description', 'Short Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true, 'data-size="large"');  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>

      <div id="tab-information">
        <table>
          <tr>
            <td><strong><?php echo language::translate('title_description', 'Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'data-size="large" style="height: 240px;"');  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_head_title', 'Head Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true, '');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_h1_title', 'H1 Title'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'h1_title['. $language_code .']', true, '');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_meta_description', 'Meta Description'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true, 'data-size="large"');
  $use_br = true;
}
?>
            </td>
          </tr>
          <tr>
            <td><strong><?php echo language::translate('title_link', 'Link'); ?></strong><br />
<?php
$use_br = false;
foreach (array_keys(language::$languages) as $language_code) {
  if ($use_br) echo '<br />';
  echo functions::form_draw_regional_input_field($language_code, 'link['. $language_code .']', true, 'data-size="large"');  $use_br = true;
}
?>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($manufacturer->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>