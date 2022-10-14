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

      if (empty($_POST['id'])) throw new Exception(language::translate('error_must_enter_id', 'You must enter an ID'));
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      if (empty($_POST['files'])) throw new Exception(language::translate('error_must_define_files', 'You must define files'));

      if (empty($_POST['install'])) $_POST['install'] = '';
      if (empty($_POST['uninstall'])) $_POST['uninstall'] = '';
      if (empty($_POST['upgrades'])) $_POST['upgrades'] = [];
      if (empty($_POST['settings'])) $_POST['settings'] = [];
      if (empty($_POST['aliases'])) $_POST['aliases'] = [];
      if (empty($_POST['files'])) $_POST['files'] = [];

      $fields = [
        'id',
        'status',
        'name',
        'description',
        'author',
        'version',
        'aliases',
        'settings',
        'install',
        'uninstall',
        'upgrades',
        'files',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $vmod->data[$field] = $_POST[$field];
      }

      $vmod->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'vmods'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($vmod->data['id'])) throw new Exception(language::translate('error_must_provide_vmod', 'You must provide a vmod'));

      $vmod->delete(!empty($_POST['cleanup']));

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'vmods'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $on_error_options = [
    [language::translate('title_warning', 'Warning'), 'warning'],
    [language::translate('title_ignore', 'Ignore'), 'ignore'],
    [language::translate('title_cancel', 'Cancel'), 'cancel'],
  ];

  $method_options = [
    [language::translate('title_replace', 'Replace'), 'replace'],
    [language::translate('title_before', 'Before'), 'before'],
    [language::translate('title_after', 'After'), 'after'],
    [language::translate('title_top', 'Top'), 'top'],
    [language::translate('title_bottom', 'Bottom'), 'bottom'],
  ];

  $type_options = [
    [language::translate('title_inline', 'Inline'), 'inline'],
    [language::translate('title_multiline', 'Multiline'), 'multiline'],
    [language::translate('title_regex', 'RegEx'), 'regex'],
  ];

// List of files
  $files_datalist = [];

  $skip_list = [
    '#.*(?<!\.inc\.php)$#',
    '#^assets/#',
    '#^index.php$#',
    '#^includes/app_header.inc.php$#',
    '#^includes/nodes/nod_vmod.inc.php$#',
    '#^includes/wrappers/wrap_app.inc.php$#',
    '#^includes/wrappers/wrap_storage.inc.php$#',
    '#^install/#',
    '#^storage/#',
  ];

  $scripts = functions::file_search(FS_DIR_APP . '**.php', GLOB_BRACE);

  foreach ($scripts as $script) {

    $relative_path = functions::file_relative_path($script);

    foreach ($skip_list as $pattern) {
      if (preg_match($pattern, $relative_path)) continue 2;
    }

    $files_datalist[] = $relative_path;
  }

  functions::draw_lightbox();
?>

<style>
.operation {
  background: #f8f8f8;
  padding: 1em;
  border-radius: 4px;
  margin-bottom: 2em;
}
html.dark-mode .operation {
  background: #232a3e;
}

.nav-tabs .fa-times-circle {
  color: #c00;
}
.nav-tabs .fa-plus {
  color: #0c0;
}
.operations {
  position: sticky;
  top: 0;
}

.script {
  position: relative;
}
.script .script-filename {
  position: absolute;
  display: inline-block;
  top: 0;
  right: 2em;
  padding: .5em 1em;
  border-radius: 0 0 4px 4px;
  background: #fff3;
  color: #fffc
}

.sources .form-code {
  height: max-content;
  max-height: 100vh;
}

fieldset {
  border: none;
  padding: 0;
}

input[name*="[find]"][name$="[content]"],
input[name*="[insert]"][name$="[content]"] {
  height: initial;
}

textarea[name*="[find]"][name$="[content]"],
textarea[name*="[insert]"][name$="[content]"] {
  height: auto;
  transition: all 100ms linear;
}
textarea[name*="[find]"][name$="[content]"] {
  min-height: 50px;
  max-height: 50px;
}
textarea[name*="[find]"][name$="[content]"]:focus {
  max-height: 250px;
}

textarea[name*="[insert]"][name$="[content]"] {
  min-height: 100px;
  max-height: 100px;
}
textarea[name*="[insert]"][name$="[content]"]:focus {
  max-height: 250px;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($vmod->data['id']) ? language::translate('title_edit_vmod', 'Edit vMod') .': '. $vmod->data['id'] : language::translate('title_create_new_vmod', 'Create New vMod'); ?>
    </div>
  </div>

  <?php echo functions::form_draw_form_begin('vmod_form', 'post', false, true); ?>

    <nav class="nav nav-tabs">
      <a class="nav-link active" href="#tab-general" data-toggle="tab"><?php echo language::translate('title_general', 'General'); ?></a>
      <a class="nav-link" href="#tab-settings" data-toggle="tab"><?php echo language::translate('title_settings', 'Settings'); ?></a>
      <a class="nav-link" href="#tab-install" data-toggle="tab"><?php echo language::translate('title_install_uninstall', 'Install/Uninstall'); ?></a>
    </nav>

    <div class="card-body">
      <div class="tab-content">
        <div id="tab-general" class="tab-pane active">

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label><?php echo language::translate('title_status', 'Status'); ?></label>
                <?php echo functions::form_draw_toggle('status', true, 'e/d'); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_id', 'ID'); ?></label>
                <?php echo functions::form_draw_text_field('id', true, 'required placeholder="my_fancy_mod" pattern="^[0-9a-zA-Z_-]+$"'); ?>
              </div>

              <div class="row">
                <div class="form-group col-md-8">
                  <label><?php echo language::translate('title_name', 'Name'); ?></label>
                  <?php echo functions::form_draw_text_field('name', true, 'required placeholder="My Fancy Mod"'); ?>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_version', 'Version'); ?></label>
                  <?php echo functions::form_draw_text_field('version', true, 'placeholder="'. date('Y-m-d') .'"'); ?>
                </div>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_description', 'Description'); ?></label>
                <?php echo functions::form_draw_text_field('description', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_author', 'Author'); ?></label>
                <?php echo functions::form_draw_text_field('author', true); ?>
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

          <nav class="nav nav-tabs">
            <?php foreach (array_keys($vmod->data['files']) as $f) { ?>
            <a class="nav-link" data-toggle="tab" href="#tab-<?php echo $f; ?>">
              <span class="file"><?php echo functions::escape_html($_POST['files'][$f]['name']); ?></span> <span class="remove" title="<?php language::translate('title_remove', 'Remove')?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></span>
            </a>
            <?php } ?>
            <a class="nav-link add" href="#"><?php echo functions::draw_fonticon('fa-plus'); ?></a>
          </nav>

          <div id="files" class="tab-content">

            <?php if (!empty($_POST['files'])) foreach (array_keys($_POST['files']) as $f) { ?>
            <div id="tab-<?php echo $f; ?>" data-tab-index="<?php echo $f; ?>" class="tab-pane">

              <div class="row">
                <div class="col-md-6">

                  <h3><?php echo language::translate('title_file_to_modify', 'File To Modify'); ?></h3>

                  <div class="form-group">
                    <label><?php echo language::translate('title_file_pattern', 'File Pattern'); ?></label>
                    <?php echo functions::form_draw_text_field('files['.$f.'][name]', true, 'placeholder="path/to/file.php" list="scripts"'); ?>
                  </div>

                  <div class="sources"></div>
                </div>

                <div class="col-md-6">

                  <h3><?php echo language::translate('title_operations', 'Operations'); ?></h3>

                  <div class="operations">
                    <?php $i=1; foreach (array_keys($_POST['files'][$f]['operations']) as $o) { ?>
                    <fieldset class="operation">

                      <div class="float-end">
                        <a class="btn btn-default btn-sm move-up" href="#"><?php echo functions::draw_fonticon('move-up'); ?></a>
                        <a class="btn btn-default btn-sm move-down" href="#"><?php echo functions::draw_fonticon('move-down'); ?></a>
                        <a class="btn btn-default btn-sm remove" href="#"><?php echo functions::draw_fonticon('remove'); ?></a>
                      </div>

                      <h3><?php echo language::translate('title_operation', 'Operation'); ?> #<span class="number"><?php echo $i++;?></span></h3>

                      <div class="row">
                        <div class="form-group col-md-3">
                          <label><?php echo language::translate('title_method', 'Method'); ?></label>
                          <?php echo functions::form_draw_select_field('files['.$f.'][operations]['.$o.'][method]', $method_options, true); ?>
                        </div>

                        <div class="form-group col-md-6">
                          <label><?php echo language::translate('title_match_type', 'Match Type'); ?></label>
                          <?php echo functions::form_draw_toggle_buttons('files['.$f.'][operations]['.$o.'][type]', $type_options, (!isset($_POST['files'][$f]['operations'][$o]['type']) || $_POST['files'][$f]['operations'][$o]['type'] == '') ? 'multiline' : true); ?>
                        </div>

                        <div class="form-group col-md-3">
                          <label><?php echo language::translate('title_on_error', 'On Error'); ?></label>
                          <?php echo functions::form_draw_select_field('files['.$f.'][operations]['.$o.'][onerror]', $on_error_options, true); ?>
                        </div>
                      </div>

                      <div class="form-group">
                        <h4><?php echo language::translate('title_find', 'Find'); ?></h4>
                        <?php if (isset($_POST['files'][$f]['operations'][$o]['type']) && in_array($_POST['files'][$f]['operations'][$o]['type'], ['inline', 'regex'])) { ?>
                        <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][find][content]', true, 'class="form-code" required'); ?>
                        <?php } else { ?>
                        <?php echo functions::form_draw_code_field('files['.$f.'][operations]['.$o.'][find][content]', true, 'required'); ?>
                        <?php }?>
                      </div>

                      <div class="row" style="font-size: .8em;">
                        <div class="form-group col-md-2">
                          <label><?php echo language::translate('title_index', 'Index'); ?></label>
                          <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][find][index]', true, 'placeholder="1,3,.."'); ?>
                        </div>

                        <div class="form-group col-md-2">
                          <label><?php echo language::translate('title_offset_before', 'Offset Before'); ?></label>
                          <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][find][offset-before]', true, 'placeholder="0"'); ?>
                        </div>

                        <div class="form-group col-md-2">
                          <label><?php echo language::translate('title_offset_after', 'Offset After'); ?></label>
                          <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][find][offset-after]', true, 'placeholder="0"'); ?>
                        </div>
                      </div>

                      <div class="form-group">
                        <h4><?php echo language::translate('title_insert', 'Insert'); ?></h4>
                        <?php if (isset($_POST['files'][$f]['operations'][$o]['type']) && in_array($_POST['files'][$f]['operations'][$o]['type'], ['inline', 'regex'])) { ?>
                        <?php echo functions::form_draw_text_field('files['.$f.'][operations]['.$o.'][insert][content]', true, 'class="form-code" required'); ?>
                        <?php } else { ?>
                        <?php echo functions::form_draw_code_field('files['.$f.'][operations]['.$o.'][insert][content]', true, 'required'); ?>
                        <?php }?>
                      </div>

                    </fieldset>
                    <?php } ?>

                  </div>

                  <div class="text-end">
                    <a class="btn btn-default add" href="#">
                      <?php echo functions::draw_fonticon('fa-plus', 'style="color: #0c0;"'); ?> <?php echo language::translate('title_add_operation', 'Add Operation'); ?>
                    </a>
                  </div>

                </div>
              </div>

            </div>
            <?php } ?>
          </div>
        </div>

        <div id="tab-settings" class="tab-pane">

          <h2><?php echo language::translate('title_settings', 'Settings'); ?></h2>

          <div class="settings">
            <?php if (!empty($_POST['settings'])) foreach (array_keys($_POST['settings']) as $key) { ?>
            <fieldset class="setting">
              <div class="row">
                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_title', 'Title'); ?></label>
                  <?php echo functions::form_draw_text_field('settings['.$key.'][title]', true, 'required'); ?>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_description', 'Description'); ?></label>
                  <?php echo functions::form_draw_text_field('settings['.$key.'][description]', true, 'required'); ?>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_function', 'Function'); ?></label>
                  <?php echo functions::form_draw_text_field('settings['.$key.'][function]', true, 'required placeholder="text()"'); ?>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_key', 'Key'); ?></label>
                  <div class="input-group">
                    <span class="input-group-text">{$</span>
                    <?php echo functions::form_draw_text_field('settings['.$key.'][key]', true, 'required'); ?>
                    <span class="input-group-text">}</span>
                  </div>
                </div>

                <div class="form-group col-md-4">
                  <label><?php echo language::translate('title_default_value', 'Default Value'); ?></label>
                  <?php echo functions::form_draw_text_field('settings['.$key.'][default_value]', true); ?>
                </div>
              </div>
            </fieldset>
            <?php } ?>
          </div>

          <div class="form-group" style="margin-top: 2em;">
            <?php echo functions::form_draw_button('add_setting', language::translate('title_add_setting', 'Add Setting'), 'button', 'class="btn btn-default"', 'add'); ?>
          </div>

        </div>

        <div id="tab-install" class="tab-pane">

          <div class="row">
            <div class="col-md-6">
              <h2><?php echo language::translate('title_install', 'Install'); ?></h2>

              <div class="form-group">
                <label><?php echo language::translate('title_script', 'Script'); ?></label>
                <?php echo functions::form_draw_code_field('install', true, 'style="height: 200px;"'); ?>
              </div>
            </div>

            <div class="col-md-6">
              <h2><?php echo language::translate('title_uninstall', 'Uninstall'); ?></h2>
              <div class="form-group">
                <label><?php echo language::translate('title_script', 'Script'); ?></label>
                <?php echo functions::form_draw_code_field('uninstall', true, 'style="height: 200px;"'); ?>
              </div>
            </div>
          </div>

          <h2><?php echo language::translate('title_upgrade_patches', 'Upgrade Patches'); ?></h2>

          <div class="upgrades">
            <?php if (!empty($_POST['upgrades'])) foreach (array_keys($_POST['upgrades']) as $key) { ?>
            <fieldset class="upgrade">
              <div class="form-group" style="max-width: 250px;">
                <label><?php echo language::translate('title_version', 'Version'); ?></label>
                <?php echo functions::form_draw_text_field('upgrades['.$key.'][version]', true); ?>
              </div>

              <div class="form-group">
                <label><?php echo language::translate('title_script', 'Script'); ?></label>
                <?php echo functions::form_draw_code_field('upgrades['.$key.'][script]', true, 'style="height: 200px;"'); ?>
              </div>
            </fieldset>
            <?php } ?>
          </div>

          <div class="form-group" style="margin-top: 2em;">
            <?php echo functions::form_draw_button('add_patch', language::translate('title_add_patch', 'Add Patch'), 'button', 'class="btn btn-default"', 'add'); ?>
          </div>

        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (!empty($vmod->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'button', 'class="btn btn-danger"', 'delete') : ''; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>
    </div>
  <?php echo functions::form_draw_form_end(); ?>
</div>

<div id="modal-uninstall" style="display: none;">
  <?php echo functions::form_draw_form_begin('uninstall_form', 'post'); ?>

    <h2><?php echo language::translate('title_uninstall_vmod', 'Uninstall vMod'); ?></h2>

    <p><label><?php echo functions::form_draw_checkbox('cleanup', '1', ''); ?> <?php echo language::translate('text_remove_all_traces_of_the_vmod', 'Remove all traces of the vMod such as database tables, settings, etc.'); ?></label></p>

    <div>
      <?php echo functions::form_draw_button('delete', language::translate('title_uninstall', 'Uninstall'), 'submit', 'class="btn btn-danger"'); ?>
      <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'submit'); ?>
    </div>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<div id="new-tab-pane-template" style="display: none;">
  <div id="tab-new_tab_index" data-tab-index="new_tab_index" class="tab-pane">

    <div class="row">
      <div class="col-md-6">

        <div class="form-group">
          <label><?php echo language::translate('title_file_pattern', 'File Pattern'); ?></label>
          <?php echo functions::form_draw_text_field('files[new_tab_index][name]', true, 'placeholder="path/to/file.php" list="scripts"'); ?>
       </div>

        <div class="sources"></div>
      </div>

      <div class="col-md-6">
        <div class="operations"></div>
        <div><a class="btn btn-default add" href="#"><?php echo functions::draw_fonticon('fa-plus', 'style="color: #0c0;"'); ?> <?php echo language::translate('title_add_operation', 'Add Operation'); ?></a></div>
      </div>
    </div>

  </div>
</div>

<div id="new-operation-template" style="display: none;">
  <fieldset class="operation">

    <div class="float-end">
      <a class="btn btn-default btn-sm move-up" href="#"><?php echo functions::draw_fonticon('move-up'); ?></a>
      <a class="btn btn-default btn-sm move-down" href="#"><?php echo functions::draw_fonticon('move-down'); ?></a>
      <a class="btn btn-default btn-sm remove" href="#"><?php echo functions::draw_fonticon('remove'); ?></a>
    </div>

    <h3><?php echo language::translate('title_operation', 'Operation'); ?> #<span class="number"></span></h3>

    <div class="row">
      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_method', 'Method'); ?></label>
        <?php echo functions::form_draw_select_field('files[current_tab_index][operations][new_operation_index][method]', $method_options, ''); ?>
      </div>

      <div class="form-group col-md-6">
        <label><?php echo language::translate('title_match_type', 'Match Type'); ?></label>
        <?php echo functions::form_draw_toggle_buttons('files[current_tab_index][operations][new_operation_index][type]', $type_options, 'multiline'); ?>
      </div>

      <div class="form-group col-md-3">
        <label><?php echo language::translate('title_on_error', 'On Error'); ?></label>
        <?php echo functions::form_draw_select_field('files[current_tab_index][operations][new_operation_index][onerror]', $on_error_options, ''); ?>
      </div>
    </div>

    <div class="form-group">
      <h4><?php echo language::translate('title_find', 'Find'); ?></h4>
      <?php echo functions::form_draw_code_field('files[current_tab_index][operations][new_operation_index][find][content]', '', 'class="form-code" required'); ?>

    </div>

    <div class="row" style="font-size: .8em;">
      <div class="form-group col-md-2">
        <label><?php echo language::translate('title_index', 'Index'); ?></label>
        <?php echo functions::form_draw_text_field('files[current_tab_index][operations][new_operation_index][find][index]', '', 'placeholder="1,3,.."'); ?>
      </div>

      <div class="form-group col-md-2">
        <label><?php echo language::translate('title_offset_before', 'Offset Before'); ?></label>
        <?php echo functions::form_draw_text_field('files[current_tab_index][operations][new_operation_index][find][offset-before]', '', 'placeholder="0"'); ?>
      </div>

      <div class="form-group col-md-2">
        <label><?php echo language::translate('title_offset_after', 'Offset After'); ?></label>
        <?php echo functions::form_draw_text_field('files[current_tab_index][operations][new_operation_index][find][offset-after]', '', 'placeholder="0"'); ?>
      </div>
    </div>

    <div class="form-group">
      <h4><?php echo language::translate('title_insert', 'Insert'); ?></h4>
      <?php echo functions::form_draw_code_field('files[current_tab_index][operations][new_operation_index][insert][content]', '', 'class="form-code" required'); ?>
    </div>

  </fieldset>
</div>

<datalist id="scripts">
  <?php foreach ($files_datalist as $option) { ?>
  <option><?php echo $option; ?></option>
  <?php } ?>
</datalist>

<script>
// Tabs

  let new_tab_index = 1;
  while ($('.tab-pane[id="tab-'+new_tab_index+'"]').length) new_tab_index++;

  $('.nav-tabs .add').click(function(e){
    e.preventDefault();

    let tab = '<a class="nav-link" data-toggle="tab" href="#tab-'+ new_tab_index +'"><span class="file">new'+ new_tab_index +'</span> <span class="remove" title="<?php language::translate('title_remove', 'Remove')?>"><?php echo functions::draw_fonticon('fa-times-circle'); ?></span></a>'
      .replace(/new_tab_index/g, new_tab_index);

    let tab_pane = $('#new-tab-pane-template').html()
      .replace(/new_tab_index/g, new_tab_index++);

    $tab_pane = $(tab_pane).hide();

    $(this).before(tab);
    $('#files').append($tab_pane);

    $(this).prev().click();
  });

  $('.nav-tabs').on('click', '.remove', function(e) {
    e.preventDefault();

    if (!confirm("<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>")) return false;

    let $tab = $(this).closest('.nav-link'),
      tab_pane = $(this).closest('.nav-link').attr('href');

    if ($tab.prev('[data-toggle="tab"]').length) {
      $tab.prev('[data-toggle="tab"]').trigger('click');

    } else if ($tab.next('[data-toggle="tab"]').length) {
      $tab.next('[data-toggle="tab"').trigger('click');
    }

    $(tab_pane).remove();
    $(this).closest('.nav-link').remove();
  });

// Operations

  let reindex_operations = function($operations) {
    let index = 1;
    $operations.find('.operation').each(function(i, operation){
      $(operation).find('.number').text(index++);
    });
  }

  $('#files').on('change', ':input[name$="[type]"]', function(e) {
    e.preventDefault();
    let match_type = $(this).val();
    $(this).closest('.operation').find(':input[name$="[content]"]').each(function(i, field){
      switch (match_type) {
        case 'inline':
        case 'regex':
          var $newfield = $('<input class="form-code" name="'+ $(field).attr('name') +'" type="text" />').val($(field).val());
          $(field).replaceWith($newfield);
          break;
        default:
          var $newfield = $('<textarea class="form-code" name="'+ $(field).attr('name') +'" /></textarea>').val($(field).val());
          $(field).replaceWith($newfield);
          break;
      }
    });
  });

  $('#files').on('change', ':input[name$="[method]"]', function(e) {
    e.preventDefault();

    let method = $(this).val();

    if ($.inArray(method, ['top', 'bottom']) != -1) {
      $(this).closest('.operation').find(':input[name*="[find]"]').prop('disabled', true);
    } else {
      $(this).closest('.operation').find(':input[name*="[find]"]').prop('disabled', false);
    }
  });

  $('#files :input[name$="[method]"]').trigger('change');

  $('body').on('input', '.form-code', function() {
    $(this).css('height', 'auto');
    $(this).height(this.scrollHeight);
  });

  $('.tab-content').on('input', ':input[name^="files"][name$="[name]"]', function(){
    let $tab_pane = $(this).closest('.tab-pane'),
     tab_index = $(this).closest('.tab-pane').attr('id').replace(/^tab-/, ''),
     tab_name = $tab_pane.find('input[name$="[name]"]').val();

    $('a[href="#tab-'+ tab_index +'"] .file').text(tab_name);

    let file_pattern = $(this).closest('.row').find(':input[name^="files"][name$="[name]"]').val(),
      url = '<?php echo document::link(WS_DIR_ADMIN, ['doc' => 'sources', 'pattern' => 'thepattern'], ['app']); ?>'.replace(/thepattern/, file_pattern);

    $.get(url, function(result) {
      $tab_pane.find('.sources').html('');

      $.each(result, function(file, source_code){
        $tab_pane.find('.sources').append(
          $('<div class="script">').html(
            $('<div class="form-code"></div>').text(source_code).prop('outerHTML') +
            $('<div class="script-filename"></div>').text(file).prop('outerHTML')
          )
        );
      });
    });
  });

  $(':input[name^="files"][name$="[name]"]').trigger('input');

  let new_operation_index = 0;
  while ($(':input[name~="files\[[^\]]+\][operations]['+new_operation_index+']"]').length) new_operation_index++;

  $('#files').on('click', '.add', function(e) {
    e.preventDefault();

    let $operations = $(this).closest('.tab-pane').find('.operations'),
      tab_index = $(this).closest('.tab-pane').data('tab-index');

     let output = $('#new-operation-template').html()
       .replace(/current_tab_index/g, tab_index)
       .replace(/new_operation_index/g, new_operation_index++);

    $operations.append(output);
    reindex_operations($operations);
  });

  $('#files').on('click', '.remove', function(e) {
    e.preventDefault();

    let $operations = $(this).closest('.operations');

    if (!confirm("<?php echo language::translate('text_are_you_sure', 'Are you sure?'); ?>")) return;

    $(this).closest('.operation').remove();
    reindex_operations($operations);
  });

  $('#files').on('click', '.move-up, .move-down', function(e) {
    e.preventDefault();

    let $row = $(this).closest('.operation'),
      $operations = $(this).closest('.operations');

    if ($(this).is('.move-up') && $row.prevAll().length > 0) {
      $row.insertBefore($row.prev());
    } else if ($(this).is('.move-down') && $row.nextAll().length > 0) {
      $row.insertAfter($row.next());
    }

    reindex_operations($operations);
  });

// Settings
  let new_setting_key_index = 0;
  while ($(':input[name^="settings['+new_setting_key_index+']"]').length) new_setting_key_index++;

  $('button[name="add_setting"]').click(function(){

    let output = [
      '<fieldset class="setting">',
      '  <div class="row">',
      '    <div class="form-group col-md-4">',
      '      <label><?php echo functions::escape_js(language::translate('title_title', 'Title')); ?></label>',
      '      <?php echo functions::escape_js(functions::form_draw_text_field('settings[new_setting_key_index][title]', '', 'required')); ?>',
      '    </div>',
      '',
      '    <div class="form-group col-md-4">',
      '      <label><?php echo functions::escape_js(language::translate('title_description', 'Description')); ?></label>',
      '      <?php echo functions::escape_js(functions::form_draw_text_field('settings[new_setting_key_index][description]', '', 'required')); ?>',
      '    </div>',
      '',
      '    <div class="form-group col-md-4">',
      '      <label><?php echo functions::escape_js(language::translate('title_function', 'Function')); ?></label>',
      '      <?php echo functions::escape_js(functions::form_draw_text_field('settings[new_setting_key_index][function]', '', 'required')); ?>',
      '    </div>',
      '',
      '    <div class="form-group col-md-4">',
      '      <label><?php echo functions::escape_js(language::translate('title_key', 'Key')); ?></label>',
      '      <div class="input-group">',
      '        <span class="input-group-text">{$</span>',
      '        <?php echo functions::escape_js(functions::form_draw_text_field('settings[new_setting_key_index][key]', '', 'required')); ?>',
      '        <span class="input-group-text">}</span>',
      '      </div>',
      '    </div>',
      '',
      '    <div class="form-group col-md-4">',
      '      <label><?php echo functions::escape_js(language::translate('title_default_value', 'Default Value')); ?></label>',
      '      <?php echo functions::escape_js(functions::form_draw_text_field('settings[new_setting_key_index][default_value]', '')); ?>',
      '    </div>',
      '  </div>',
      '</fieldset>'
    ].join('\n')
    .replace(/new_setting_key_index/, 'new_' + new_setting_key_index++);

    $('.settings').append(output);
  });

// Upgrade Patches
  let new_upgrade_patch_index = 0;
  while ($(':input[name^="upgrades['+new_upgrade_patch_index+']"]').length) new_upgrade_patch_index++;

  $('button[name="add_patch"]').click(function(){

    let output = [
      '<fieldset class="upgrade">',
      '  <div class="form-group" style="max-width: 250px;">',
      '    <label><?php echo functions::escape_js(language::translate('title_version', 'Version')); ?></label>',
      '    <?php echo functions::escape_js(functions::form_draw_text_field('upgrades[new_upgrade_patch_index][version]', '')); ?>',
      '  </div>',
      '',
      '  <div class="form-group">',
      '    <label><?php echo functions::escape_js(language::translate('title_script', 'Script')); ?></label>',
      '    <?php echo functions::escape_js(functions::form_draw_code_field('upgrades[new_upgrade_patch_index][script]', '', 'style="height: 200px;"')); ?>',
      '  </div>',
      '</fieldset>'
    ].join('\n')
    .replace(/new_upgrade_patch_index/g, 'new_' + new_upgrade_patch_index);

    $('.upgrades').append(output);
  });

  $('.card-action button[name="delete"]').click(function(e){
    e.preventDefault();
    $.featherlight('#modal-uninstall', {
      seamless: true,
    });
  });
</script>