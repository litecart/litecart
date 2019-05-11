<?php

  if (!empty($_GET['category_id'])) {
    $category = new ctrl_category($_GET['category_id']);
  } else {
    $category = new ctrl_category();
  }

  if (empty($_POST)) {
    foreach ($category->data as $key => $value) {
      $_POST[$key] = $value;
    }

    if (!empty($_GET['parent_id'])) $_POST['parent_id'] = $_GET['parent_id'];
  }

  breadcrumbs::add(!empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') : language::translate('title_add_new_category', 'Add New Category'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_CATEGORIES ." where id != '". (isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0) ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
      if (!empty($category->data['id']) && $category->data['parent_id'] == $category->data['id']) throw new Exception(language::translate('error_cannot_mount_category_to_self', 'Cannot mount category to itself'));

      if (empty($_POST['images'])) $_POST['images'] = array();
      if (empty($_POST['filters'])) $_POST['filters'] = array();

      $fields = array(
        'status',
        'parent_id',
        'code',
        'google_taxonomy_id',
        'list_style',
        'images',
        'name',
        'short_description',
        'description',
        'keywords',
        'head_title',
        'h1_title',
        'meta_description',
        'filters',
        'priority',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $category->data[$field] = $_POST[$field];
      }

      if (!empty($_FILES['new_images']['tmp_name'])) {
        foreach (array_keys($_FILES['new_images']['tmp_name']) as $key) {
          $category->add_image($_FILES['new_images']['tmp_name'][$key]);
        }
      }

      $category->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link('', array('doc' => 'catalog', 'category_id' => $_POST['parent_id']), array('app')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($category->data['id'])) throw new Exception(language::translate('error_must_provide_category', 'You must provide a category'));

      $category->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved successfully'));
      header('Location: '. document::link('', array('doc' => 'catalog', 'category_id' => $_POST['parent_id']), array('app')));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  list($category_image_width, $category_image_height) = functions::image_scale_by_width(320, settings::get('category_image_ratio'));
?>
<style>
#images .thumbnail {
  margin: 0;
}
#images .image {
  overflow: hidden;
}
#images .thumbnail {
  margin-right: 15px;
}
#images img {
  max-width: 50px;
  max-height: 50px;
}
#images .actions {
  text-align: right;
  padding: 0.25em 0;
}
</style>

<h1><?php echo $app_icon; ?> <?php echo !empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') .': '. $category->data['name'][language::$selected['code']] : language::translate('title_add_new_category', 'Add New Category'); ?></h1>

<?php echo functions::form_draw_form_begin('category_form', 'post', false, true); ?>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-filters"><?php echo language::translate('title_filters', 'Filters'); ?></a></li>
  </ul>

  <div class="tab-content">
    <div id="tab-general" class="tab-pane active" style="max-width: 980px;">

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo language::translate('title_status', 'Status'); ?></label>
            <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '0', 'e/d'); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_parent_category', 'Parent Category'); ?></label>
            <?php echo functions::form_draw_categories_list('parent_id', true); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_google_taxonomy_id', 'Google Taxonomy ID'); ?> <a href="http://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
            <?php echo functions::form_draw_number_field('google_taxonomy_id', true); ?>
          </div>

          <?php if (!empty($category->data['id'])) { ?>
          <div class="form-group">
            <label><?php echo language::translate('title_date_updated', 'Date Updated'); ?></label>
            <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($category->data['date_updated'])); ?></div>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_date_created', 'Date Created'); ?></label>
            <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($category->data['date_created'])); ?></div>
          </div>
          <?php } ?>

          <div class="form-group">
            <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
            <?php echo functions::form_draw_number_field('priority', true); ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo language::translate('title_name', 'Name'); ?></label>
            <?php foreach (array_keys(language::$languages) as $language_code) echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_code', 'Code'); ?></label>
            <?php echo functions::form_draw_text_field('code', true); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_list_style', 'List Style'); ?></label>
<?php
  $options = array(
    array(language::translate('title_columns', 'Columns'), 'columns'),
    array(language::translate('title_rows', 'Rows'), 'rows'),
  );
  echo functions::form_draw_select_field('list_style', $options, true);
?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
            <?php echo functions::form_draw_text_field('keywords', true); ?>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo language::translate('title_images', 'Images'); ?></label>
            <div class="thumbnail">
<?php
  if (isset($category->data['id']) && !empty($category->data['images'])) {
    $image = current($category->data['images']);
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_HTTP_HOME . functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $image['filename'], $category_image_width, $category_image_height, settings::get('category_image_clipping'))) .'" alt="" />';
    reset($category->data['images']);
  } else {
    echo '<img class="main-image" src="'. document::href_link(WS_DIR_HTTP_HOME . functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $category_image_width, $category_image_height, settings::get('category_image_clipping'))) .'" alt="" />';
  }
?>
            </div>
          </div>

          <div id="images">

            <div class="images">
              <?php if (!empty($_POST['images'])) foreach (array_keys($_POST['images']) as $key) { ?>
              <div class="image form-group">
                <?php echo functions::form_draw_hidden_field('images['.$key.'][id]', true); ?>
                <?php echo functions::form_draw_hidden_field('images['.$key.'][filename]', $_POST['images'][$key]['filename']); ?>

                <div class="thumbnail pull-left">
                  <img src="<?php echo document::href_link(WS_DIR_HTTP_HOME . functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $category->data['images'][$key]['filename'], $category_image_width, $category_image_height, settings::get('category_image_clipping'))); ?>" alt="" />
                </div>

                <div class="input-group">
                  <?php echo functions::form_draw_text_field('images['.$key.'][new_filename]', isset($_POST['images'][$key]['new_filename']) ? $_POST['images'][$key]['new_filename'] : $_POST['images'][$key]['filename']); ?>
                  <div class="input-group-addon">
                    <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
                    <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
                    <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                  </div>
                </div>
              </div>
              <?php } ?>
            </div>

            <div class="new-images">
              <div class="image form-group">
                <div class="thumbnail pull-left">
                  <img src="<?php echo document::href_link(WS_DIR_HTTP_HOME . functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $category_image_width, $category_image_height, settings::get('category_image_clipping'))); ?>" alt="" />
                </div>

                <div class="input-group">
                  <?php echo functions::form_draw_file_field('new_images[]'); ?>
                  <div class="input-group-addon">
                    <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <a href="#" class="add" title="<?php echo language::translate('text_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="tab-information" class="tab-pane" style="max-width: 640px;">

      <ul class="nav nav-tabs">
        <?php foreach (language::$languages as $language) { ?>
          <li<?php echo ($language['code'] == language::$selected['code']) ? ' class="active"' : ''; ?>><a data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a></li>
        <?php } ?>
      </ul>

      <div class="tab-content">

        <?php foreach (array_keys(language::$languages) as $language_code) { ?>
        <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">

          <div class="form-group">
            <label><?php echo language::translate('title_h1_title', 'H1 Title'); ?></label>
            <?php echo functions::form_draw_regional_input_field($language_code, 'h1_title['. $language_code .']', true, ''); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
            <?php echo functions::form_draw_regional_input_field($language_code, 'short_description['. $language_code .']', true); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_description', 'Description'); ?></label>
            <?php echo functions::form_draw_regional_wysiwyg_field($language_code, 'description['. $language_code .']', true, 'style="height: 240px;"'); ?>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
              <?php echo functions::form_draw_regional_input_field($language_code, 'head_title['. $language_code .']', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
              <?php echo functions::form_draw_regional_input_field($language_code, 'meta_description['. $language_code .']', true); ?>
            </div>
          </div>

        </div>
        <?php } ?>

      </div>
    </div>

    <div id="tab-filters" class="tab-pane" style="max-width: 640px;">

      <table class="table table-striped data-table table-dragable">
        <thead>
          <tr>
            <th><?php echo language::translate('title_attribute_group', 'Attribute Group'); ?></th>
            <th><?php echo language::translate('title_select_multiple', 'Select Multiple'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($_POST['filters'])) foreach (array_keys($_POST['filters']) as $key) { ?>
          <tr class="grabable">
            <?php echo functions::form_draw_hidden_field('filters['.$key.'][id]', true); ?>
            <?php echo functions::form_draw_hidden_field('filters['.$key.'][attribute_group_id]', true); ?>
            <?php echo functions::form_draw_hidden_field('filters['.$key.'][attribute_group_name]', true); ?>
            <td><?php echo $_POST['filters'][$key]['attribute_group_name']; ?></td>
            <td><?php echo functions::form_draw_checkbox('filters['.$key.'][select_multiple]', '1', true); ?></td>
            <td class="text-right">
              <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
              <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
              <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2">
            </td>
          </tr>
        </tfoot>
      </table>

      <div class="input-group" style="max-width: 320px;">
        <?php echo functions::form_draw_attribute_groups_list('new_attribute_group', true); ?>
        <span class="input-group-btn">
          <?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'button'); ?>
        </span>
      </div>
    </div>
  </div>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($category->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<script>
  <?php if (!empty($category->data['id'])) { ?>
  $('select[name="parent_id"] option[value="<?php echo $category->data['id']; ?>"]').attr('disabled', 'disabled');
  <?php } ?>

// Images

  $('#images').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('.form-group');

    if ($(this).is('.move-up') && $(row).prevAll().length > 0) {
      $(row).insertBefore(row.prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
    refreshMainImage();
  });

  $('#images').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('.form-group').remove();
    refreshMainImage();
  });

  $('#images .add').click(function(e) {
    e.preventDefault();
    var output = '<div class="image form-group">'
               + '  <div class="thumbnail pull-left">'
               + '    <img src="<?php echo document::href_link(WS_DIR_HTTP_HOME . functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $category_image_width, $category_image_height, settings::get('category_image_clipping'))); ?>" alt="" />'
               + '  </div>'
               + '  '
               + '  <div class="input-group">'
               + '    <?php echo functions::form_draw_file_field('new_images[]'); ?>'
               + '    <div class="input-group-addon">'
               + '      <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>'
               + '    </div>'
               + '  </div>'
               + '</div>';
    $('#images .new-images').append(output);
    refreshMainImage();
  });

  $('#images').on('change', 'input[type="file"]', function(e) {
    var img = $(this).closest('.form-group').find('img');

    var oFReader = new FileReader();
    oFReader.readAsDataURL(this.files[0]);
    oFReader.onload = function(e){
      $(img).attr('src', e.target.result);
    };
    oFReader.onloadend = function(e) {
      refreshMainImage();
    };
  });

  function refreshMainImage() {
    if ($('#images img:first').length) {
      $('#tab-general .main-image').attr('src', $('#images img:first').attr('src'));
      return;
    }

    $('#tab-general .main-image').attr('src', '<?php echo document::href_link(WS_DIR_HTTP_HOME . functions::image_thumbnail(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . 'no_image.png', $category_image_width, $category_image_height, settings::get('category_image_clipping'))); ?>');
  }

// Head Title & H1 Title

  $('input[name^="name"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Meta Description

  $('input[name^="short_description"]').bind('input propertyChange', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Filters

  var new_attribute_filter_i = 0;
  $('#tab-filters button[name="add"]').click(function(){

    if ($('select[name="new_attribute_group"]').val() == '') {
      alert("<?php echo language::translate('error_must_select_attribute_group', 'You must select an attribute group'); ?>");
      return;
    }

    var output = '<tr class="grabable">'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('filters[new_attribute_filter_i][id]', '')); ?>'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('filters[new_attribute_filter_i][attribute_group_id]', 'new_attribute_group_id')); ?>'
               + '  <?php echo functions::general_escape_js(functions::form_draw_hidden_field('filters[new_attribute_filter_i][attribute_group_name]', 'new_attribute_group_name')); ?>'
               + '  <td>new_attribute_group_name</td>'
               + '  <td><?php echo functions::form_draw_checkbox('filters[new_attribute_filter_i][select_multiple]', true); ?></td>'
               + '  <td class="text-right">'
               + '    <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>'
               + '    <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>'
               + '    <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>'
               + '  </td>'
               + '</tr>';

    while ($('input[name="filters[new_'+new_attribute_filter_i+']"]').length) new_attribute_filter_i++;
    output = output.replace(/new_attribute_filter_i/g, 'new_' + new_attribute_filter_i);
    output = output.replace(/new_attribute_group_id/g, $('select[name="new_attribute_group"] option:selected').val());
    output = output.replace(/new_attribute_group_name/g, $('select[name="new_attribute_group"] option:selected').text());
    new_attribute_filter_i++;

    $('#tab-filters tbody').append(output);
  });

  $('#tab-filters').on('click', '.move-up, .move-down', function(event) {
    event.preventDefault();
    var row = $(this).closest('tr');

    if ($(this).is('.move-up') && $(row).prevAll().length) {
      $(row).insertBefore($(row).prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length) {
      $(row).insertAfter($(row).next());
    }
  });

  $('#tab-filters').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });
</script>