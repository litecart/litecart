<?php

  if (!empty($_GET['geo_zone_id'])) {
    $geo_zone = new ent_geo_zone($_GET['geo_zone_id']);
  } else {
    $geo_zone = new ent_geo_zone();
  }

  if (empty($_POST)) {
    $_POST = $geo_zone->data;
  }

  document::$snippets['title'][] = !empty($geo_zone->data['id']) ? language::translate('title_edit_geo_zone', 'Edit Geo Zone') : language::translate('title_new_geo_zone', 'Create New Geo Zone');

  breadcrumbs::add(language::translate('title_geo_zones', 'Geo Zones'), document::link(WS_DIR_ADMIN, ['doc' => 'geo_zones'], ['app']));
  breadcrumbs::add(!empty($geo_zone->data['id']) ? language::translate('title_edit_geo_zone', 'Edit Geo Zone') : language::translate('title_new_geo_zone', 'Create New Geo Zone'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['zones'])) $_POST['zones'] = [];

      $fields = [
        'code',
        'name',
        'description',
        'zones',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $geo_zone->data[$field] = $_POST[$field];
      }

      $geo_zone->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'geo_zones'], true, ['geo_zone_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($geo_zone->data['id'])) throw new Exception(language::translate('error_must_provide_geo_zone', 'You must provide a geo zone'));

      $geo_zone->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'geo_zones'], true, ['geo_zone_id']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="panel panel-app">
  <div class="panel-heading">
    <div class="panel-title">
      <?php echo $app_icon; ?> <?php echo !empty($geo_zone->data['id']) ? language::translate('title_edit_geo_zone', 'Edit Geo Zone') : language::translate('title_new_geo_zone', 'Create New Geo Zone'); ?>
    </div>
  </div>

  <div class="panel-body">
    <?php echo functions::form_draw_form_begin('form_geo_zone', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_draw_text_field('code', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_draw_text_field('name', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_description', 'Description'); ?></label>
          <?php echo functions::form_draw_text_field('description', true); ?>
        </div>
      </div>

      <h2><?php echo language::translate('title_zones', 'Zones'); ?></h2>

      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th><?php echo language::translate('title_country', 'Country'); ?></th>
            <th><?php echo language::translate('title_zone', 'Zone'); ?></th>
            <th><?php echo language::translate('title_city', 'City'); ?></th>
            <th></th>
          </tr>
        </thead>

        <tbody>
          <?php if (!empty($_POST['zones'])) foreach (array_keys($_POST['zones']) as $key) { ?>
          <tr>
            <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][id]', true); ?><?php echo $_POST['zones'][$key]['id']; ?></td>
            <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][country_code]', true); ?> <?php echo reference::country($_POST['zones'][$key]['country_code'])->name; ?></td>
            <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][zone_code]', true); ?> <?php echo !empty($_POST['zones'][$key]['zone_code']) ? reference::country($_POST['zones'][$key]['country_code'])->zones[$_POST['zones'][$key]['zone_code']]['name'] : '-- '.language::translate('title_all_zones', 'All Zones') .' --'; ?></td>
            <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][city]', true); ?> <?php echo !empty($_POST['zones'][$key]['city']) ? $_POST['zones'][$key]['city'] : '-- '.language::translate('title_all_cities', 'All Cities') .' --'; ?></td>
            <td class="text-end"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
          </tr>
          <?php } ?>
        </tbody>

        <tfoot>
          <tr>
            <td><?php echo functions::form_draw_hidden_field('new_zone[id]', ''); ?></td>
            <td><?php echo functions::form_draw_countries_list('new_zone[country_code]', ''); ?></td>
            <td><?php echo functions::form_draw_zones_list('', 'new_zone[zone_code]', '', false, '', 'all'); ?></td>
            <td><?php echo functions::form_draw_text_field('new_zone[city]', '', 'placeholder="-- '. language::translate('text_all_cities', 'All cities') .' --"'); ?></td>
            <td><?php echo functions::form_draw_button('add', language::translate('title_add', 'Add'), 'button'); ?></td>
          </tr>
        </tfoot>
      </table>

      <div class="panel-action btn-group">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
        <?php echo (!empty($geo_zone->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  $('select[name$="new_zone[zone_code]"][disabled]').each(function() {
    $(this).html('<option value="">-- <?php echo functions::escape_js(language::translate('title_all_zones', 'All Zones')); ?> --</option>');
  });

  $('body').on('change', 'select[name="new_zone[country_code]"]', function() {
    var zone_field = $(this).closest('tr').find("select[name='new_zone[zone_code]']");
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
      },
      success: function(data) {
        $(zone_field).html('');
        if (data) {
          $(zone_field).append('<option value="">-- <?php echo functions::escape_js(language::translate('title_all_zones', 'All Zones')); ?> --</option>');
          $.each(data, function(i, zone) {
            $(zone_field).append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
          $(zone_field).prop('disabled', false);
        } else {
          $(zone_field).append('<option value="">-- <?php echo functions::escape_js(language::translate('title_all_zones', 'All Zones')); ?> --</option>');
          $(zone_field).prop('disabled', true);
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });

  var new_zone_i = <?php echo isset($_POST['zones']) ? count($_POST['zones']) : '0'; ?>;
  $('form[name="form_geo_zone"]').on('click', 'button[name="add"]', function(e) {
    e.preventDefault();

    if ($('select[name="country[code]"]').find('option:selected').val() == '') return;

    var row = $(this).closest('tr');

    var found = false;
    $.each($('form[name="form_geo_zone"] tbody tr'), function(i, current){
      if ($(current).find(':input[name$="[country_code]"]').val() == $(':input[name="new_zone[country_code]"]').val()
       && $(current).find(':input[name$="[zone_code]"]').val() == $(':input[name="new_zone[zone_code]"]').val()
       && $(current).find(':input[name$="[city]"]').val() == $(':input[name="new_zone[city]"]').val()) {
         found = true;
         return;
       }
    });

    if (found) return;

    var output = '    <tr>'
               + '      <td><?php echo functions::escape_js(functions::form_draw_hidden_field('zones[new_zone_i][id]', '')); ?></td>'
               + '      <td><?php echo functions::escape_js(functions::form_draw_hidden_field('zones[new_zone_i][country_code]', '')); ?>' + $('select[name="new_zone[country_code]"] option:selected').text() + '</td>'
               + '      <td><?php echo functions::escape_js(functions::form_draw_hidden_field('zones[new_zone_i][zone_code]', '')); ?>' + $('select[name="new_zone[zone_code]"] option:selected').text() + '</td>'
               + '      <td><?php echo functions::escape_js(functions::form_draw_hidden_field('zones[new_zone_i][city]', '')); ?>' + $('input[name="new_zone[city]"]').val() + '</td>'
               + '      <td style="text-align: end;"><a class="remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '    </tr>';

    output = output.replace(/new_zone_i/g, 'new_' + new_zone_i++);
    $(this).closest('table').find('tbody').append(output);

    $('form[name="form_geo_zone"] tbody tr:last :input[name$="[country_code]"]').val($(':input[name="new_zone[country_code]"]').val());
    $('form[name="form_geo_zone"] tbody tr:last :input[name$="[zone_code]"]').val($(':input[name="new_zone[zone_code]"]').val());
    $('form[name="form_geo_zone"] tbody tr:last :input[name$="[city]"]').val($(':input[name="new_zone[city]"]').val());

    if ($(':input[name="new_zone[city]"]').val() == '') {
      $(':input[name="new_zone[zone_code]"]').val('');
    }

    $(':input[name="new_zone[city]"]').val('');
  });

  $('form[name="form_geo_zone"]').on('click', '.remove', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
  });
</script>