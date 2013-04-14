<?php
  
  if (isset($_GET['geo_zone_id'])) {
    $geo_zone = new ctrl_geo_zone($_GET['geo_zone_id']);
  } else {
    $geo_zone = new ctrl_geo_zone();
  }
  
  if (!$_POST) {
    foreach ($geo_zone->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }
  
  if (isset($_POST['save'])) {
  
    if (!$system->notices->get('errors')) {
    
      $fields = array(
        'name',
        'description',
        'zones',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $geo_zone->data[$field] = $_POST[$field];
      }
      
      $geo_zone->save();
      
      $system->notices->add('success', $system->language->translate('success_changes_saved', 'Changes saved'));
      header('Location: '. $system->document->link('', array('doc' => 'geo_zones.php'), true, array('geo_zone_id')));
      exit;
    }
  }
  
  if (isset($_POST['delete'])) {

    $geo_zone->delete();
    
    $system->notices->add('success', $system->language->translate('success_post_deleted', 'Post deleted'));
    header('Location: '. $system->document->link('', array('doc' => 'tax_rates.php'), true, array('geo_zone_id')));
    exit();
  }
  
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($geo_zone->data['id']) ? $system->language->translate('title_edit_geo_zone', 'Edit Geo Zone') : $system->language->translate('title_new_geo_zone', 'Create New Geo Zone'); ?></h1>

<?php echo $system->functions->form_draw_form_begin('form_geo_zone', 'post'); ?>

  <table>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_name', 'Name'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'text', 'style="width: 175px;"'); ?>
      </td>
    </tr>
    <tr>
      <td align="left" nowrap="nowrap"><strong><?php echo $system->language->translate('title_description', 'Description'); ?></strong><br />
        <?php echo $system->functions->form_draw_input_field('description', isset($_POST['description']) ? $_POST['description'] : '', 'text', 'style="width: 360px;"'); ?>
      </td>
    </tr>
  </table>

  <h2><?php echo $system->language->translate('title_zones', 'Zones'); ?></h2>
  <table width="100%" class="dataTable" id="table-zones">
    <tr class="header">
      <th align="left" style="vertical-align: text-top" nowrap="nowrap"><?php echo $system->language->translate('title_id', 'ID'); ?></th>
      <th align="left" style="vertical-align: text-top" nowrap="nowrap"><?php echo $system->language->translate('title_country', 'Country'); ?></th>
      <th align="left" style="vertical-align: text-top" nowrap="nowrap"><?php echo $system->language->translate('title_zone', 'Zone'); ?></th>
      <th align="center" style="vertical-align: text-top" nowrap="nowrap">&nbsp;</th>
    </tr>
<?php
    if (!empty($_POST['zones'])) {
      foreach (array_keys($_POST['zones']) as $key) {
?>
    <tr>
      <td align="left"><?php echo $system->functions->form_draw_hidden_field('zones['. $key .'][id]', $_POST['zones'][$key]['id']); ?><?php echo $_POST['zones'][$key]['id']; ?></td>
      <td align="left"><?php echo $system->functions->form_draw_hidden_field('zones['. $key .'][country_code]', $_POST['zones'][$key]['country_code']); ?><?php echo $system->functions->form_draw_hidden_field('zones['. $key .'][country_name]', $_POST['zones'][$key]['country_name']); ?><?php echo $_POST['zones'][$key]['country_name']; ?></td>
      <td align="left"><?php echo $system->functions->form_draw_hidden_field('zones['. $key .'][zone_code]', $_POST['zones'][$key]['zone_code']); ?><?php echo $system->functions->form_draw_hidden_field('zones['. $key .'][zone_name]', $_POST['zones'][$key]['zone_name']); ?><?php echo $_POST['zones'][$key]['zone_name']; ?></td>
      <td align="right"><a id="remove-zone" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>
    </tr>
<?php
      }
    }
?>
    <tr>
      <td align="left">&nbsp;</td>
      <td align="left"><?php echo $system->functions->form_draw_countries_list('country[code]', ''); ?></td>
      <td align="left"><?php echo $system->functions->form_draw_zones_list('', 'zone[code]', ''); ?></td>
      <td align="right"><?php echo $system->functions->form_draw_button('add_zone', $system->language->translate('title_add', 'Add'), 'button'); ?></td>
    </tr>
  </table>
  
  <script type="text/javascript">
    $("body").on("click", "#remove-zone", function(event) {
      event.preventDefault();
      $(this).closest('tr').remove();
    });
    
    $("select[name='country[code]']").change(function(){
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo WS_DIR_AJAX .'zones.json.php'; ?>?country_code=' + $(this).val(),
        type: 'get',
        cache: true,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
        },
        success: function(data) {
          $('select[name=\'zone[code]\']').html('');
          if ($('select[name=\'zone[code]\']').attr('disabled')) $('select[name=\'zone[code]\']').removeAttr('disabled');
          if (data) {
            $('select[name=\'zone[code]\']').append('<option value="">-- '+ '<?php echo $system->language->translate('title_all_zones', 'All Zones'); ?>' +' --</option>');
            $.each(data, function(i, zone) {
              $('select[name=\'zone[code]\']').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $('select[name=\'zone[code]\']').append('<option value="">-- '+ '<?php echo $system->language->translate('title_all_zones', 'All Zones'); ?>' +' --</option>');
            $('select[name=\'zone[code]\']').attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });
    
    var new_zone_i = <?php echo isset($_POST['zones']) ? count($_POST['zones']) : '0'; ?>;
    $("body").on("click", "button[name=add_zone]", function(event) {
      event.preventDefault();
      if ($("select[name='country[code]']").find("option:selected").val() == "") return;
      new_zone_i++;
      var output = '    <tr>'
                 + '      <td align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('zones[new_zone_i][id]', '')); ?></td>'
                 + '      <td align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('zones[new_zone_i][country_code]', 'new_country_code') . $system->functions->form_draw_hidden_field('zones[new_zone_i][country_name]', 'new_country_name')); ?>new_country_name</td>'
                 + '      <td align="left"><?php echo str_replace(PHP_EOL, '', $system->functions->form_draw_hidden_field('zones[new_zone_i][zone_code]', 'new_zone_code') . $system->functions->form_draw_hidden_field('zones[new_zone_i][zone_name]', 'new_zone_name')); ?>new_zone_name</td>'
                 + '      <td align="right"><a id="remove-zone" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo $system->language->translate('title_remove', 'Remove'); ?>" /></a></td>'
                 + '    </tr>';
      output = output.replace(/new_zone_i/g, 'new_' + new_zone_i);
      output = output.replace(/new_country_code/g, $("select[name='country[code]']").find("option:selected").val());
      output = output.replace(/new_country_name/g, $("select[name='country[code]']").find("option:selected").text());
      output = output.replace(/new_zone_code/g, $("select[name='zone[code]']").find("option:selected").val());
      output = output.replace(/new_zone_name/g, $("select[name='zone[code]']").find("option:selected").text());
      $("#table-zones tr:last").before(output);
    });
  </script>

  <p><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo $system->functions->form_draw_button('cancel', $system->language->translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($geo_zone->data['id'])) ? $system->functions->form_draw_button('delete', $system->language->translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. $system->language->translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></p>
  
<?php echo $system->functions->form_draw_form_end(); ?>