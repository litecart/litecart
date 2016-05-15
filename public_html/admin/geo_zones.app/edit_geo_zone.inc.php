<?php

  if (!empty($_GET['geo_zone_id'])) {
    $geo_zone = new ctrl_geo_zone($_GET['geo_zone_id']);
  } else {
    $geo_zone = new ctrl_geo_zone();
  }

  if (empty($_POST)) {
    foreach ($geo_zone->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($geo_zone->data['id']) ? language::translate('title_edit_geo_zone', 'Edit Geo Zone') : language::translate('title_new_geo_zone', 'Create New Geo Zone'));

  if (isset($_POST['save'])) {

    if (empty(notices::$data['errors'])) {

      $fields = array(
        'name',
        'description',
        'zones',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $geo_zone->data[$field] = $_POST[$field];
      }

      $geo_zone->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'geo_zones'), true, array('geo_zone_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $geo_zone->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'geo_zones'), true, array('geo_zone_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($geo_zone->data['id']) ? language::translate('title_edit_geo_zone', 'Edit Geo Zone') : language::translate('title_new_geo_zone', 'Create New Geo Zone'); ?></h1>

<?php echo functions::form_draw_form_begin('form_geo_zone', 'post'); ?>

  <table>
    <tr>
      <td><strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
        <?php echo functions::form_draw_text_field('name', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_description', 'Description'); ?></strong><br />
        <?php echo functions::form_draw_text_field('description', true, 'data-size="large"'); ?>
      </td>
    </tr>
  </table>

  <h2><?php echo language::translate('title_zones', 'Zones'); ?></h2>
  <table width="100%" class="dataTable" id="table-zones">
    <tr class="header">
      <th style="vertical-align: text-top;"><?php echo language::translate('title_id', 'ID'); ?></th>
      <th style="vertical-align: text-top;"><?php echo language::translate('title_country', 'Country'); ?></th>
      <th style="vertical-align: text-top;"><?php echo language::translate('title_zone', 'Zone'); ?></th>
      <th style="vertical-align: text-top; text-align: center;">&nbsp;</th>
    </tr>
<?php
    if (!empty($_POST['zones'])) {
      foreach (array_keys($_POST['zones']) as $key) {
?>
    <tr>
      <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][id]', true); ?><?php echo $_POST['zones'][$key]['id']; ?></td>
      <td><?php echo functions::form_draw_countries_list('zones['. $key .'][country_code]', true); ?></td>
      <td><?php echo functions::form_draw_zones_list($_POST['zones'][$key]['country_code'], 'zones['. $key .'][zone_code]', true, false, '', 'all'); ?></td>
      <td style="text-align: right;"><a id="remove-zone" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
    </tr>
<?php
      }
    }
?>
    <tr>
      <td colspan="4"><a href="#" id="add_zone" title="<?php echo language::translate('title_add', 'Add'); ?>"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?></a></td>
    </tr>
  </table>

  <script>
    $("select[name$='[zone_code]'][disabled]").each(function() {
      $(this).html('<option value="">-- <?php echo functions::general_escape_js(language::translate('title_all_zones', 'All Zones')); ?> --</option>');
    });

    $("body").on("click", "#remove-zone", function(event) {
      event.preventDefault();
      $(this).closest('tr').remove();
    });

    $("body").on("change", "select[name$='[country_code]']", function() {
      var zone_field = $(this).closest('tr').find("select[name$='[zone_code]']");
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
            $(zone_field).append('<option value="">-- <?php echo functions::general_escape_js(language::translate('title_all_zones', 'All Zones')); ?> --</option>');
            $.each(data, function(i, zone) {
              $(zone_field).append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
            $(zone_field).removeAttr('disabled');
          } else {
            $(zone_field).append('<option value="">-- <?php echo functions::general_escape_js(language::translate('title_all_zones', 'All Zones')); ?> --</option>');
            $(zone_field).attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });

    var new_zone_i = <?php echo isset($_POST['zones']) ? count($_POST['zones']) : '0'; ?>;
    $("body").on("click", "#add_zone", function(event) {
      event.preventDefault();
      if ($("select[name='country[code]']").find("option:selected").val() == "") return;
      new_zone_i++;
      var output = '    <tr>'
                 + '      <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('zones[new_zone_i][id]', '')); ?></td>'
                 + '      <td><?php echo functions::general_escape_js(functions::form_draw_countries_list('zones[new_zone_i][country_code]', '')); ?></td>'
                 + '      <td><?php echo functions::general_escape_js(functions::form_draw_zones_list('', 'zones[new_zone_i][zone_code]', '', false, '', 'all')); ?></td>'
                 + '      <td style="text-align: right;"><a id="remove-zone" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                 + '    </tr>';
      output = output.replace(/new_zone_i/g, 'new_' + new_zone_i);
      $("#table-zones tr:last").before(output);
    });
  </script>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($geo_zone->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>
