<?php

  if (!empty($_GET['vmod'])) {
    $vmod = new ent_vmod($_GET['vmod']);
  } else {
    $vmod = new ent_vmod();
  }

  if (!$_POST) {
    $_POST = $vmod->data;
  }

  breadcrumbs::add(!empty($vmod->data['id']) ? language::translate('title_edit_vmod', 'Edit vMod') : language::translate('title_create_new_vmod', 'Create New vMod'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['title'])) throw new Exception(language::translate('error_must_enter_filename', 'You must enter a filename'));
      if (empty($_POST['title'])) throw new Exception(language::translate('error_must_enter_title', 'You must enter a title'));
      if (empty($_POST['files'])) throw new Exception(language::translate('error_must_define_files', 'You must define files'));

      foreach (array_keys($_POST['files']) as $f) {
        foreach (array_keys($_POST['files'][$f]['operations']) as $o) {
          if (empty($_POST['files'][$f]['operations'][$o]['find']['regex'])) $_POST['files'][$f]['operations'][$o]['find']['regex'] = 'true';
          if (empty($_POST['files'][$f]['operations'][$o]['find']['trim'])) $_POST['files'][$f]['operations'][$o]['find']['trim'] = 'true';
          if (empty($_POST['files'][$f]['operations'][$o]['insert']['regex'])) $_POST['files'][$f]['operations'][$o]['insert']['regex'] = 'true';
          if (empty($_POST['files'][$f]['operations'][$o]['insert']['trim'])) $_POST['files'][$f]['operations'][$o]['insert']['trim'] = 'true';
          if (empty($_POST['files'][$f]['operations'][$o]['ignoreif']['regex'])) $_POST['files'][$f]['operations'][$o]['ignoreif']['regex'] = 'true';
          if (empty($_POST['files'][$f]['operations'][$o]['ignoreif']['trim'])) $_POST['files'][$f]['operations'][$o]['ignoreif']['trim'] = 'true';
        }
      }

      $fields = [
        'filename',
        'status',
        'title',
        'description',
        'version',
        'files',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $vmod->data[$field] = $_POST[$field];
      }

      $vmod->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/vmods'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($vmod->data['id'])) throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vmod'));

      $vmod->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/vmods'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $on_error_options = [
    'warning' => language::translate('title_warning', 'Warning'),
    'ignore' => language::translate('title_ignore', 'Ignore'),
    'cancel' => language::translate('title_cancel', 'Cancel'),
  ];

  $position_options = [
    'replace' => language::translate('title_replace', 'Replace'),
    'before' => language::translate('title_before', 'Before'),
    'after' => language::translate('title_after', 'After'),
    'top' => language::translate('title_top', 'Top'),
    'bottom' => language::translate('title_bottom', 'Bottom'),
  ];

?>

<style>
.operation {
  background: #f8f8f8;
  padding: 1em;
  border-radius: 4px;
  margin-bottom: 2em;
}

.fa-times-circle {
  color: #c00;
}
.fa-plus {
  color: #0c0;
}

textarea {
  font-family: monospace;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($vmod->data['id']) ? language::translate('title_edit_vmod', 'Edit vMod') .': '. $vmod->data['id'] : language::translate('title_create_new_vmod', 'Create New vMod'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('vmod_form', 'post', false, true); ?>

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo language::translate('title_status', 'Status'); ?></label>
            <?php echo functions::form_draw_toggle('status', 'e/d', fallback($_POST['status'], '0')); ?>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_filename', 'Filename'); ?></label>
            <?php echo functions::form_draw_text_field('filename', true, 'required placeholder="example.xml"'); ?>
          </div>

          <div class="row">
            <div class="form-group col-md-8">
              <label><?php echo language::translate('title_title', 'Title'); ?></label>
              <?php echo functions::form_draw_text_field('title', true, ''); ?>
            </div>

            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_version', 'Version'); ?></label>
              <?php echo functions::form_draw_text_field('version', true, 'placeholder="'. date('Y-m-d') .'"'); ?>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_description', 'Description'); ?></label>
            <?php echo functions::form_draw_text_field('description', true, ''); ?>
          </div>

          <?php if (!empty($vmod->data['id'])) { ?>
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_date_created', 'Date Created'); ?></label>
              <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($vmod->data['date_created'])); ?></div>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_date_updated', 'Date Updated'); ?></label>
              <div><?php echo language::strftime('%e %b %Y %H:%M', strtotime($vmod->data['date_updated'])); ?></div>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>

      <h2><?php echo language::translate('title_modifications', 'Modifications'); ?></h2>

      <ul class="nav nav-tabs">
        <?php foreach (array_keys($vmod->data['files']) as $f) { ?>
          <li><a data-toggle="tab" href="#tab-<?php echo $f; ?>"><?php echo $_POST['files'][$f]['name']; ?> <span class="remove" title="<?php language::translate('title_remove', 'Remove')?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></span></a></li>
        <?php } ?>
          <li><a class="add" href="#"><?php echo functions::draw_fonticon('fa-plus'); ?></a></li>
      </ul>

      <div class="tab-content">

        <?php foreach (array_keys($_POST['files']) as $f) { ?>
        <div id="tab-<?php echo $f; ?>" data-tab-index="<?php echo $f; ?>" class="tab-pane fade in">

          <div class="row">
            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_path', 'Path'); ?></label>
              <?php echo functions::form_draw_text_field('files['.$f.'][path]', true, 'placeholder="path/to/dir/"'); ?>
            </div>

            <div class="form-group col-md-3">
              <label><?php echo language::translate('title_filename', 'Filename'); ?></label>
              <?php echo functions::form_draw_text_field('files['.$f.'][name]', true, 'placeholder="file.ext,file2.ext"'); ?>
            </div>
          </div>

          <div class="operations">
            <?php foreach (array_keys($_POST['files'][$f]['operations']) as $o) { ?>
            <div class="operation">

              <div class="float-end">
                <a class="move-up" href="#"><?php echo functions::draw_fonticon('move-up'); ?></a>
                <a class="move-down" href="#"><?php echo functions::draw_fonticon('move-down'); ?></a>
                <a class="remove" href="#"><?php echo functions::draw_fonticon('remove'); ?></a>
              </div>

              <div class="row">
                <div class="col-md-6">

                  <h3><?php echo language::translate('title_find', 'Find'); ?></h3>

                  <div class="form-group">
                    <label><?php echo language::translate('title_code', 'Code'); ?></label>
                    <?php echo functions::form_draw_textarea('files['.$f.'][operations]['.$o.'][find][content]', true, 'style="height: 100px;"'); ?>
                  </div>

                  <div class="form-group">
                    <label><?php echo language::translate('title_on_error', 'On Error'); ?></label>
                    <?php echo functions::form_draw_select_field('files['.$f.'][operations]['.$o.'][onerror]', $on_error_options, true); ?>
                  </div>

                  <div class="row" style="font-size: .8em;">
                    <div class="form-group col-md-3">
                      <label><?php echo language::translate('title_regular_expression', 'Regular Expression'); ?></label>
                      <?php echo functions::form_draw_toggle('files['.$f.'][operations]['.$o.'][find][regex]', 'y/n', true); ?>
                    </div>

                    <div class="form-group col-md-3">
                      <label><?php echo language::translate('title_trim', 'Trim'); ?></label>
                      <?php echo functions::form_draw_toggle('files['.$f.'][operations]['.$o.'][find][trim]', 'y/n', true); ?>
                    </div>

                    <div class="form-group col-md-2">
                      <label><?php echo language::translate('title_offset_before', 'Offset Before'); ?></label>
                      <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][find][offset-before]', true, 'placeholder="0"'); ?>
                    </div>

                    <div class="form-group col-md-2">
                      <label><?php echo language::translate('title_offset_after', 'Offset After'); ?></label>
                      <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][find][offset-after]', true, 'placeholder="0"'); ?>
                    </div>

                    <div class="form-group col-md-2">
                      <label><?php echo language::translate('title_index', 'Index'); ?></label>
                      <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][find][index]', true, 'placeholder="1,3,.."'); ?>
                    </div>
                  </div>

                  <h3><?php echo language::translate('title_ignore_if', 'Ignore If'); ?></h3>

                  <div class="form-group">
                    <label><?php echo language::translate('title_code', 'Code'); ?></label>
                    <?php echo functions::form_draw_textarea('files['.$f.'][operations]['.$o.'][ignoreif][content]', true, 'style="height: 50px;"'); ?>
                  </div>

                  <div class="row" style="font-size: .8em;">
                    <div class="form-group col-md-3">
                      <label><?php echo language::translate('title_regular_expression', 'Regular Expression'); ?></label>
                      <?php echo functions::form_draw_toggle('files['.$f.'][operations]['.$o.'][ignoreif][regex]', 'y/n', true); ?>
                    </div>

                    <div class="form-group col-md-3">
                      <label><?php echo language::translate('title_trim', 'Trim'); ?></label>
                      <?php echo functions::form_draw_toggle('files['.$f.'][operations]['.$o.'][ignoreif][trim]', 'y/n', true); ?>
                    </div>
                  </div>

                </div>

                <div class="col-md-6">

                  <h3><?php echo language::translate('title_insert', 'Insert'); ?></h3>

                  <div class="form-group">
                    <label><?php echo language::translate('title_code', 'Code'); ?></label>
                    <?php echo functions::form_draw_textarea('files['.$f.'][operations]['.$o.'][insert][content]', true, 'style="height: 375px;"'); ?>
                  </div>

                  <div class="row" style="font-size: .8em;">
                    <div class="form-group col-md-4">
                      <label><?php echo language::translate('title_position', 'Position'); ?></label>
                      <?php echo functions::form_draw_select_field('files['.$f.'][operations]['.$o.'][insert][position]', $position_options, true); ?>
                    </div>

                    <div class="form-group col-md-4">
                      <label><?php echo language::translate('title_regular_expression', 'Regular Expression'); ?></label>
                      <?php echo functions::form_draw_toggle('files['.$f.'][operations]['.$o.'][insert][regex]', 'y/n', true); ?>
                    </div>

                    <div class="form-group col-md-4">
                      <label><?php echo language::translate('title_trim', 'Trim'); ?></label>
                      <?php echo functions::form_draw_toggle('files['.$f.'][operations]['.$o.'][insert][trim]', 'y/n', true); ?>
                    </div>
                  </div>

                </div>
              </div>

            </div>
            <?php } ?>

          </div>

          <div><a class="add" href="#"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #0c0;"'); ?> <?php echo language::translate('title_add_operation', 'Add Operation'); ?></a></div>

        </div>
        <?php } ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (!empty($vmod->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<div id="new-tab-template" style="display: none;">
  <div id="tab-new_tab_i" class="tab-pane fade in" data-tab-index="new_tab_i">

    <div class="row">
      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_path', 'Path'); ?></label>
        <?php echo functions::form_draw_text_field('files[new_tab_i][path]', true, 'placeholder="path/to/dir/"'); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_filename', 'Filename'); ?></label>
        <?php echo functions::form_draw_text_field('files[new_tab_i][name]', true, 'placeholder="file.ext,file2.ext"'); ?>
      </div>
    </div>

    <div class="operations">
    </div>

    <div><a class="add" href="#"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #0c0;"'); ?> <?php echo language::translate('title_add_operation', 'Add Operation'); ?></a></div>

  </div>
</div>

<div id="new-operation-template" style="display: none;">
  <div class="operation">

    <div class="float-end">
      <a class="move-up" href="#"><?php echo functions::draw_fonticon('move-up'); ?></a>
      <a class="move-down" href="#"><?php echo functions::draw_fonticon('move-down'); ?></a>
      <a class="remove" href="#"><?php echo functions::draw_fonticon('remove'); ?></a>
    </div>

    <div class="row">
      <div class="col-md-6">

        <h3><?php echo language::translate('title_find', 'Find'); ?></h3>

        <div class="form-group">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_draw_textarea('files[tab_i][operations][new_operation_i][find][content]', true, 'style="height: 100px;"'); ?>
        </div>

        <div class="form-group ">
          <label><?php echo language::translate('title_on_error', 'On Error'); ?></label>
          <?php echo functions::form_draw_select_field('files[tab_i][operations][new_operation_i][onerror]', $on_error_options, true); ?>
        </div>

        <div class="row" style="font-size: .8em;">
          <div class="form-group col-md-2">
            <label><?php echo language::translate('title_offset_before', 'Offset Before'); ?></label>
            <?php echo functions::form_draw_text_field('files[tab_i][operations][new_operation_i][find][offset-before]', true, 'placeholder="0"'); ?>
          </div>

          <div class="form-group col-md-2">
            <label><?php echo language::translate('title_offset_after', 'Offset After'); ?></label>
            <?php echo functions::form_draw_text_field('files[tab_i][operations][new_operation_i][find][offset-after]', true, 'placeholder="0"'); ?>
          </div>

          <div class="form-group col-md-2">
            <label><?php echo language::translate('title_index', 'Index'); ?></label>
            <?php echo functions::form_draw_text_field('files[tab_i][operations][new_operation_i][find][index]', true, 'placeholder="1,3,.."'); ?>
          </div>

          <div class="form-group col-md-3">
            <label><?php echo language::translate('title_regular_expression', 'Regular Expression'); ?></label>
            <?php echo functions::form_draw_toggle('files[tab_i][operations][new_operation_i][find][regex]', 'y/n', true); ?>
          </div>

          <div class="form-group col-md-3">
            <label><?php echo language::translate('title_trim', 'Trim'); ?></label>
            <?php echo functions::form_draw_toggle('files[tab_i][operations][new_operation_i][find][trim]', 'y/n', true); ?>
          </div>
        </div>

        <h3><?php echo language::translate('title_ignore_if', 'Ignore If'); ?></h3>

        <div class="form-group">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_draw_textarea('files[tab_i][operations][new_operation_i][ignoreif][content]', true, 'style="height: 50px;"'); ?>
        </div>

        <div class="row" style="font-size: .8em;">
          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_regular_expression', 'Regular Expression'); ?></label>
            <?php echo functions::form_draw_toggle('files[tab_i][operations][new_operation_i][ignoreif][regex]', 'y/n', true); ?>
          </div>

          <div class="form-group col-md-6">
            <label><?php echo language::translate('title_trim', 'Trim'); ?></label>
            <?php echo functions::form_draw_toggle('files[tab_i][operations][new_operation_i][ignoreif][trim]', 'y/n', true); ?>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <h3><?php echo language::translate('title_insert', 'Insert'); ?></h3>

        <div class="form-group">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_draw_textarea('files[tab_i][operations][new_operation_i][insert][content]', true, 'style="height: 375px;"'); ?>
        </div>

        <div class="row" style="font-size: .8em;">
          <div class="form-group col-md-4">
            <label><?php echo language::translate('title_position', 'Position'); ?></label>
            <?php echo functions::form_draw_select_field('files[tab_i][operations][new_operation_i][insert][position]', $position_options, true); ?>
          </div>

          <div class="form-group col-md-4">
            <label><?php echo language::translate('title_regular_expression', 'Regular Expression'); ?></label>
            <?php echo functions::form_draw_toggle('files[tab_i][operations][new_operation_i][insert][regex]', 'y/n', true); ?>
          </div>

          <div class="form-group col-md-4">
            <label><?php echo language::translate('title_trim', 'Trim'); ?></label>
            <?php echo functions::form_draw_toggle('files[tab_i][operations][new_operation_i][insert][trim]', 'y/n', true); ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  $('.tab-content').on('input', 'input[name$="[path]"], input[name$="[name]"]', function() {
    var tab = $(this).closest('.tab-pane');
    var tab_i = $(this).closest('.tab-pane').data('tab-index');
    var tab_name = $(tab).find('input[name$="[path]"]').val() + $(tab).find('input[name$="[name]"]').val();
    $('a[href="#tab-'+ tab_i +'"]').text(tab_name);
  })

  var new_tab_i = 1;
  $('.nav-tabs .add').click(function(e){
    e.preventDefault();
    while ($(':input[name^="files['+ new_tab_i +']"]').length) new_tab_i++;
    $(this).closest('li').before('<li><a data-toggle="tab" href="#tab-'+ new_tab_i +'">new'+ new_tab_i  +' <span class="remove" title="<?php language::translate('title_remove', 'Remove')?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></span></a></li>');
    var html = $('#new-tab-template').html().replace(/new_tab_i/g, new_tab_i);
    $('.tab-content').append(html);
    $(this).closest('li').prev().find('a').click();
    return false;
  });

  $('.nav-tabs').on('click', '.remove', function(e) {
    e.preventDefault();
    if (!confirm("<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>")) return false;
    var tab = $(this).parent().attr('href');
    $(tab).remove();
    $(this).closest('li').remove();
  });

  $('.tab-content').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();
    var row = $(this).closest('.operation');
    if ($(this).is('.move-up') && $(row).prevAll().length > 0) {
      $(row).insertBefore(row.prev());
    } else if ($(this).is('.move-down') && $(row).nextAll().length > 0) {
      $(row).insertAfter($(row).next());
    }
  });

  var new_operation_i = 1;
  $('.tab-content').on('click', '.add', function(e) {
    e.preventDefault();
    while ($(':input[name*="[operations]['+ new_operation_i +']"]').length) new_operation_i++;
    var list = $(this).closest('.tab-pane').find('.operations');
    var html = $('#new-operation-template').html()
    var tab_i = $(this).closest('.tab-pane').data('tab-index');
    html = html.replace(/tab_i/g, tab_i)
               .replace(/new_operation_i/g, new_operation_i);
    $(list).append(html);
  });

  $('.tab-content').on('click', '.remove', function(e) {
    e.preventDefault();
    if (!confirm("<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>")) return;
    $(this).closest('.operation').remove();
  });
</script>