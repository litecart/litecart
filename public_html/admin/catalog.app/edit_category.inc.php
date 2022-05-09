<?php

  if (!empty($_GET['category_id'])) {
    $category = new ent_category($_GET['category_id']);
  } else {
    $category = new ent_category();
  }

  if (empty($_POST)) {
    foreach ($category->data as $key => $value) {
      $_POST[$key] = $value;
    }

    if (empty($category->data['id']) && !empty($_GET['parent_id'])) {
      $_POST['parent_id'] = $_GET['parent_id'];
    }
  }

  document::$snippets['title'][] = !empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') : language::translate('title_add_new_category', 'Add New Category');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(!empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') : language::translate('title_add_new_category', 'Add New Category'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."categories where id != '". (isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0) ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) {
        throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));
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
        'filters',
        'priority',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $category->data[$field] = $_POST[$field];
      }

      if (!empty($_POST['delete_image'])) $category->delete_image();

      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $category->save_image($_FILES['image']['tmp_name']);
      }

      $category->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'catalog', 'category_id' => $_POST['parent_id']], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($category->data['id'])) throw new Exception(language::translate('error_must_provide_category', 'You must provide a category'));

      $category->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'catalog', 'category_id' => $_POST['parent_id']], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  list($category_image_width, $category_image_height) = functions::image_scale_by_width(320, settings::get('category_image_ratio'));
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <?php echo $app_icon; ?> <?php echo !empty($category->data['id']) ? language::translate('title_edit_category', 'Edit Category') .': '. $category->data['name'][language::$selected['code']] : language::translate('title_add_new_category', 'Add New Category'); ?>
  </div>

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tab-general"><?php echo language::translate('title_general', 'General'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-information"><?php echo language::translate('title_information', 'Information'); ?></a></li>
    <li><a data-toggle="tab" href="#tab-filters"><?php echo language::translate('title_filters', 'Filters'); ?></a></li>
  </ul>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('category_form', 'post', false, true); ?>

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
                <?php echo functions::form_draw_category_field('parent_id', true); ?>
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
  $options = [
    [language::translate('title_columns', 'Columns'), 'columns'],
    [language::translate('title_rows', 'Rows'), 'rows'],
  ];
  echo functions::form_draw_select_field('list_style', $options, true);
?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
                <?php echo functions::form_draw_text_field('keywords', true); ?>
              </div>
            </div>

            <div class="col-md-4">
              <div id="image">
                <div class="thumbnail" style="margin-bottom: 15px;">
                  <img src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_APP . 'images/' . $category->data['image'], $category_image_width, $category_image_height, settings::get('category_image_clipping'))); ?>" alt="" />
                </div>

                <div class="form-group">
                  <label><?php echo ((isset($category->data['image']) && $category->data['image'] != '') ? language::translate('title_new_image', 'New Image') : language::translate('title_image', 'Image')); ?></label>
                  <?php echo functions::form_draw_file_field('image', ''); ?>
                  <?php if (!empty($category->data['image'])) { ?><br />
                  <div><?php echo $category->data['image']; ?></div>
                  <div><?php echo functions::form_draw_checkbox('delete_image', 'true', true); ?> <?php echo language::translate('title_delete', 'Delete'); ?></div>
                  <?php } ?>
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
                <label><?php echo language::translate('title_name', 'Name'); ?></label>
                <?php echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, ''); ?>
              </div>

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
              <tr>
                <?php echo functions::form_draw_hidden_field('filters['.$key.'][id]', true); ?>
                <?php echo functions::form_draw_hidden_field('filters['.$key.'][attribute_group_id]', true); ?>
                <?php echo functions::form_draw_hidden_field('filters['.$key.'][attribute_group_name]', true); ?>
                <td class="grabable"><?php echo $_POST['filters'][$key]['attribute_group_name']; ?></td>
                <td class="grabable"><?php echo functions::form_draw_checkbox('filters['.$key.'][select_multiple]', '1', true); ?></td>
                <td class="text-end">
                  <a class="move-up" href="#" title="<?php echo language::translate('text_move_up', 'Move up'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-up fa-lg', 'style="color: #3399cc;"'); ?></a>
                  <a class="move-down" href="#" title="<?php echo language::translate('text_move_down', 'Move down'); ?>"><?php echo functions::draw_fonticon('fa-arrow-circle-down fa-lg', 'style="color: #3399cc;"'); ?></a>
                  <a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>

          <div class="input-group" style="max-width: 320px;">
            <?php echo functions::form_draw_attribute_groups_list('new_attribute_group', true); ?>
            <?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'button'); ?>
          </div>
        </div>
      </div>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (isset($category->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  <?php if (!empty($category->data['id'])) { ?>
  $('select[name="parent_id"] option[value="<?php echo $category->data['id']; ?>"]').prop('disabled', true);
  <?php } ?>

// Image

  $('input[name="image"]').change(function(e) {
    if ($(this).val() != '') {
      var oFReader = new FileReader();
      oFReader.readAsDataURL(this.files[0]);
      oFReader.onload = function(e){
        $('#image img').attr('src', e.target.result);
      };
    } else {
      $('#image img').attr('src', '<?php echo functions::image_thumbnail(FS_DIR_ADMIN . 'images/' . $category->data['image'], $category_image_width, $category_image_height, settings::get('category_image_clipping')); ?>');
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
      alert("<?php echo language::translate('error_must_select_attribute_group', 'You must select an attribute group'); ?>");
      return;
    }

    var output = '<tr class="grabable">'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('filters[new_attribute_filter_i][id]', '')); ?>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('filters[new_attribute_filter_i][attribute_group_id]', 'new_attribute_group_id')); ?>'
               + '  <?php echo functions::escape_js(functions::form_draw_hidden_field('filters[new_attribute_filter_i][attribute_group_name]', 'new_attribute_group_name')); ?>'
               + '  <td>new_attribute_group_name</td>'
               + '  <td><?php echo functions::form_draw_checkbox('filters[new_attribute_filter_i][select_multiple]', true); ?></td>'
               + '  <td class="text-end">'
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