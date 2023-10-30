<?php

  if (!empty($_GET['category_id'])) {
    $category = new ent_category($_GET['category_id']);
  } else {
    $category = new ent_category();
  }

  if (empty($_POST)) {
    $_POST = $category->data;

    if (empty($category->data['id']) && !empty($_GET['parent_id'])) {
      $_POST['parent_id'] = $_GET['parent_id'];
    }
  }

  document::$snippets['title'][] = !empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') : language::translate('title_create_new_category', 'Create New Category');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(!empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') : language::translate('title_create_new_category', 'Create New Category'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."categories where id != '". (isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0) ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) {
        throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
      }

      if (isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name']) && !empty($_FILES['image']['error'])) {
        throw new Exception(language::translate('error_uploaded_image_rejected', 'An uploaded image was rejected for unknown reason'));
      }

      if (empty($_POST['filters'])) $_POST['filters'] = [];

      $fields = [
        'status',
        'parent_id',
        'code',
        'google_taxonomy_id',
        'list_style',
        'image',
        'name',
        'short_description',
        'description',
        'keywords',
        'head_title',
        'h1_title',
        'meta_description',
        'synonyms',
        'filters',
        'priority',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $category->data[$field] = $_POST[$field];
      }

      if (!empty($_POST['delete_image'])) $category->delete_image();

      if (!empty($_FILES['image']['tmp_name'])) {
        $category->save_image($_FILES['image']['tmp_name']);
      }

      $category->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'catalog', 'category_id' => $category->data['id']], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($category->data['id'])) throw new Exception(language::translate('error_must_provide_category', 'You must provide a category'));

      $parent_id = $category->data['parent_id'];
      $category->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'catalog', 'category_id' => $parent_id], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  list($category_image_width, $category_image_height) = functions::image_scale_by_width(320, settings::get('category_image_ratio'));
  $thumbnail = functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category->data['image'], $category_image_width, $category_image_height, settings::get('category_image_clipping'));

  $list_style_options = [
    [language::translate('title_columns', 'Columns'), 'columns'],
    [language::translate('title_rows', 'Rows'), 'rows'],
  ];

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') .': '. $category->data['name'][language::$selected['code']] : language::translate('title_create_new_category', 'Create New Category'); ?>
    </div>
  </div>

  <nav class="nav nav-tabs">
    <a class="nav-link active" data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a>
    <a class="nav-link" data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a>
    <a class="nav-link" data-toggle="tab" href="#tab-filters"><?php echo language::translate('title_filters', 'Filters'); ?></a>
  </nav>

  <div class="card-body">
    <?php echo functions::form_begin('category_form', 'post', false, true); ?>

      <div class="tab-content">
        <div id="tab-general" class="tab-pane active" style="max-width: 1200px;">

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo language::translate('title_status', 'Status'); ?></label>
                <?php echo functions::form_toggle('status', isset($_POST['status']) ? $_POST['status'] : '0', 'e/d'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_parent_category', 'Parent Category'); ?></label>
                <?php echo functions::form_category_field('parent_id', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_google_taxonomy_id', 'Google Taxonomy ID'); ?> <a href="https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
                <?php echo functions::form_number_field('google_taxonomy_id', true); ?>
              </div>

              <?php if (!empty($category->data['id'])) { ?>
              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_date_updated', 'Date Updated'); ?></label>
                  <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($category->data['date_updated'])); ?></div>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_date_created', 'Date Created'); ?></label>
                  <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($category->data['date_created'])); ?></div>
                </div>
              </div>
              <?php } ?>

              <div class="form-group">
                <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
                <?php echo functions::form_number_field('priority', true); ?>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo language::translate('title_name', 'Name'); ?></label>
                <?php echo functions::form_regional_input_field('name['. settings::get('store_language_code') .']', settings::get('store_language_code'), true, ''); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_code', 'Code'); ?></label>
                <?php echo functions::form_text_field('code', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_list_style', 'List Style'); ?></label>
                <?php echo functions::form_select_field('list_style', $list_style_options, true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
                <?php echo functions::form_text_field('keywords', true); ?>
              </div>
            </div>

            <div class="col-md-4">
              <div id="image">
                <div style="margin-bottom: 15px;">
                  <img class="thumbnail" src="<?php echo document::href_rlink(FS_DIR_STORAGE . $thumbnail); ?>" alt="" />
                </div>

                <div class="form-group">
                  <label><?php echo ((isset($category->data['image']) && $category->data['image'] != '') ? language::translate('title_new_image', 'New Image') : language::translate('title_image', 'Image')); ?></label>
                  <?php echo functions::form_file_field('image', ''); ?>
                  <?php if (!empty($category->data['image'])) { ?><br />
                  <div><?php echo $category->data['image']; ?></div>
                  <div><?php echo functions::form_checkbox('delete_image', 'true', true); ?> <?php echo language::translate('title_delete', 'Delete'); ?></div>
                  <?php } ?>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div id="tab-information" class="tab-pane" style="max-width: 640px;">

          <?php if (count(language::$languages) > 1) { ?>
          <nav class="nav nav-tabs">
            <?php foreach (language::$languages as $language) { ?>
            <a class="nav-link<?php echo ($language['code'] == language::$selected['code']) ? ' class="active"' : ''; ?>" data-toggle="tab" href="#<?php echo $language['code']; ?>"><?php echo $language['name']; ?></a>
            <?php } ?>
          </nav>
          <?php } ?>

          <div class="tab-content">

            <?php foreach (array_keys(language::$languages) as $language_code) { ?>
            <div id="<?php echo $language_code; ?>" class="tab-pane fade in<?php echo ($language_code == language::$selected['code']) ? ' active' : ''; ?>">

              <div class="form-group">
                <label><?php echo language::translate('title_name', 'Name'); ?></label>
                <?php echo functions::form_regional_input_field('name['. $language_code .']', $language_code, true, ''); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_h1_title', 'H1 Title'); ?></label>
                <?php echo functions::form_regional_input_field('h1_title['. $language_code .']', $language_code, true, ''); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
                <?php echo functions::form_regional_input_field('short_description['. $language_code .']', $language_code, true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_description', 'Description'); ?></label>
                <?php echo functions::form_regional_wysiwyg_field('description['. $language_code .']', $language_code, true, 'style="height: 240px;"'); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
                  <?php echo functions::form_regional_input_field('head_title['. $language_code .']', $language_code, true); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
                  <?php echo functions::form_regional_input_field('meta_description['. $language_code .']', $language_code, true); ?>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_synonyms', 'Synonyms'); ?></label>
                <?php echo functions::form_regional_input_field('synonyms['. $language_code .']', $language_code, true); ?>
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
              <tr>
                <td class="grabable">
                  <?php echo functions::form_hidden_field('filters['.$key.'][id]', true); ?>
                  <?php echo functions::form_hidden_field('filters['.$key.'][attribute_group_id]', true); ?>
                  <?php echo functions::form_hidden_field('filters['.$key.'][attribute_group_name]', true); ?>
                  <?php echo functions::escape_html($_POST['filters'][$key]['attribute_group_name']); ?>
                </td>
                <td class="grabable"><?php echo functions::form_checkbox('filters['.$key.'][select_multiple]', '1', true); ?></td>
                <td class="text-end">
                  <a class="btn btn-default btn-sm move-up" href="#" title="<?php echo functions::escape_html(language::translate('title_move_up', 'Move Up')); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a>
                  <a class="btn btn-default btn-sm move-down" href="#" title="<?php echo functions::escape_html(language::translate('title_move_down', 'Move Down')); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a>
                  <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>
                </td>
              </tr>
              <?php } ?>
            </tbody>

            <tfoot>
              <tr>
                <td><?php echo functions::form_select_attribute_group('new_attribute_group', true); ?></td>
                <td><?php echo functions::form_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
                <td></td>
              </tr>
            </tfoot>
          </table>

        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo !empty($category->data['id']) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : ''; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<script>

// Init

  <?php if (!empty($category->data['id'])) { ?>
  $('select[name="parent_id"] option[value="<?php echo $category->data['id']; ?>"]').prop('disabled', true);
  <?php } ?>


// Cross Referencing

  $('input[name="name[<?php echo settings::get('store_language_code'); ?>]"]').on('input change', function(){
    $('input[name="'+ $(this).attr('name') +'"]').not(this).val($(this).val());
  });

// Image

  $('input[name="image"]').change(function(e) {
    if ($(this).val() != '') {
      var oFReader = new FileReader();
      oFReader.readAsDataURL(this.files[0]);
      oFReader.onload = function(e){
        $('#image img').attr('src', e.target.result);
      };
    } else {
      $('#image img').attr('src', '<?php echo document::rlink(FS_DIR_STORAGE . $thumbnail); ?>');
    }
  });

// Head Title & H1 Title

  $('input[name^="name"]').on('input', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('.nav-tabs a[href="#'+language_code+'"]').css('opacity', $(this).val() ? 1 : .5);
    $('input[name="name['+language_code+']"]').not(this).val($(this).val());
    $('input[name="head_title['+language_code+']"]').attr('placeholder', $(this).val());
    $('input[name="h1_title['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Meta Description

  $('input[name^="short_description"]').on('input', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');

// Filters

  var new_attribute_filter_i = 0;
  $('#tab-filters button[name="add"]').click(function(){

    if ($('select[name="new_attribute_group"]').val() == '') {
      alert("<?php echo functions::escape_html(language::translate('error_must_select_attribute_group', 'You must select an attribute group')); ?>");
      return;
    }

    var output = '<tr class="grabable">'
               + '  <?php echo functions::escape_js(functions::form_hidden_field('filters[new_attribute_filter_i][id]', '')); ?>'
               + '  <?php echo functions::escape_js(functions::form_hidden_field('filters[new_attribute_filter_i][attribute_group_id]', 'new_attribute_group_id')); ?>'
               + '  <?php echo functions::escape_js(functions::form_hidden_field('filters[new_attribute_filter_i][attribute_group_name]', 'new_attribute_group_name')); ?>'
               + '  <td>new_attribute_group_name</td>'
               + '  <td><?php echo functions::form_checkbox('filters[new_attribute_filter_i][select_multiple]', true); ?></td>'
               + '  <td class="text-end">'
               + '    <a class="btn btn-default btn-sm move-up" href="#" title="<?php echo functions::escape_html(language::translate('title_move_up', 'Move Up')); ?>"><?php echo functions::draw_fonticon('move-up'); ?></a>'
               + '    <a class="btn btn-default btn-sm move-down" href="#" title="<?php echo functions::escape_html(language::translate('title_move_down', 'Move Down')); ?>"><?php echo functions::draw_fonticon('move-down'); ?></a>'
               + '    <a class="btn btn-default btn-sm remove" href="#" title="<?php echo functions::escape_html(language::translate('title_remove', 'Remove')); ?>"><?php echo functions::draw_fonticon('remove'); ?></a>'
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