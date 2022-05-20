<?php

  if (!empty($_GET['brand_id'])) {
    $brand = new ent_brand($_GET['brand_id']);
  } else {
    $brand = new ent_brand();
  }

  if (!$_POST) {
    $_POST = $brand->data;
  }

  document::$snippets['title'][] = !empty($brand->data['id']) ? language::translate('title_edit_brand', 'Edit Brand') :  language::translate('title_create_new_brand', 'Create New Brand');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_brands', 'Brands'), document::ilink(__APP__.'/brands'));
  breadcrumbs::add(!empty($brand->data['id']) ? language::translate('title_edit_brand', 'Edit Brand') :  language::translate('title_create_new_brand', 'Create New Brand'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_name_missing', 'You must enter a name.'));

      if (!empty($_POST['code']) && database::num_rows(database::query("select id from ". DB_TABLE_PREFIX ."brands where id != '". (isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0) ."' and code = '". database::input($_POST['code']) ."' limit 1;"))) throw new Exception(language::translate('error_code_database_conflict', 'Another entry with the given code already exists in the database'));

      $fields = [
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
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $brand->data[$field] = $_POST[$field];
      }

      $brand->save();

      if (!empty($_POST['delete_image'])) $brand->delete_image();

      if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        $brand->save_image($_FILES['image']['tmp_name']);
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/brands'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($brand->data['id'])) throw new Exception(language::translate('error_must_provide_brand', 'You must provide a brand'));

      $brand->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/brands'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($brand->data['id']) ? language::translate('title_edit_brand', 'Edit Brand') :  language::translate('title_create_new_brand', 'Create New Brand'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('brand_form', 'post', false, true); ?>


      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label><?php echo language::translate('title_status', 'Status'); ?></label>
            <?php echo functions::form_draw_toggle('status', 'e/d', (file_get_contents('php://input') != '') ? true : '1'); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_featured', 'Featured'); ?></label>
            <?php echo functions::form_draw_toggle('featured', 'y/n', fallback($_POST['featured'], '1')); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_code', 'Code'); ?></label>
            <?php echo functions::form_draw_text_field('code', true); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_name', 'Name'); ?></label>
            <?php echo functions::form_draw_text_field('name', true); ?>
          </div>

          <div id="image">
            <?php if (!empty($brand->data['image'])) { ?>
            <div style="margin-bottom: 15px;">
              <img class="thumbnail" src="<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $brand->data['image'], 400, 100)); ?>" alt="" />
            </div>
            <?php } ?>

            <div class="form-group">
              <label><?php echo !empty($brand->data['image']) ? language::translate('title_new_image', 'New Image') : language::translate('title_image', 'Image'); ?></label>
              <?php echo functions::form_draw_file_field('image', 'accept="image/*"'); ?>
              <?php if (!empty($brand->data['image'])) { ?>
              <?php echo functions::form_draw_checkbox('delete_image', ['true', language::translate('title_delete', 'Delete')], true); ?>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_keywords', 'Keywords'); ?></label>
            <?php echo functions::form_draw_text_field('keywords', true); ?>
          </div>
        </div>

        <div class="col-md-6">
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
                <?php echo functions::form_draw_regional_text_field('h1_title['. $language_code .']', $language_code, true, ''); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_short_description', 'Short Description'); ?></label>
                <?php echo functions::form_draw_regional_text_field('short_description['. $language_code .']', $language_code, true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_description', 'Description'); ?></label>
                <?php echo functions::form_draw_regional_wysiwyg_field('description['. $language_code .']', $language_code, true, 'style="height: 240px;"'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_link', 'Link'); ?></label>
                <?php echo functions::form_draw_regional_text_field('link['. $language_code .']', $language_code, true); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_head_title', 'Head Title'); ?></label>
                  <?php echo functions::form_draw_regional_text_field('head_title['. $language_code .']', $language_code, true, ''); ?>
                </div>

                <div class="form-group col-md-6">
                  <label><?php echo language::translate('title_meta_description', 'Meta Description'); ?></label>
                  <?php echo functions::form_draw_regional_text_field('meta_description['. $language_code .']', $language_code, true); ?>
                </div>
              </div>
            </div>
            <?php } ?>
          </div>

        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (!empty($brand->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $('input[name="name"]').on('input', function(e){
    $('input[name^="head_title"]').attr('placeholder', $(this).val());
    $('input[name^="h1_title"]').attr('placeholder', $(this).val());
  }).trigger('input');

  $('input[name="image"]').change(function(e) {
    if ($(this).val() != '') {
      var oFReader = new FileReader();
      oFReader.readAsDataURL(this.files[0]);
      oFReader.onload = function(e){
        $('#image img').attr('src', e.target.result);
      };
    } else {
      $('#image img').attr('src', '<?php echo document::href_link(WS_DIR_APP . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $brand->data['image'], 400, 100)); ?>');
    }
  });

  $('input[name^="short_description"]').on('input', function(e){
    var language_code = $(this).attr('name').match(/\[(.*)\]$/)[1];
    $('input[name="meta_description['+language_code+']"]').attr('placeholder', $(this).val());
  }).trigger('input');
</script>