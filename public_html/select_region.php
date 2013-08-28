<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once('includes/config.inc.php');
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_header.inc.php');
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      header('Content-type: text/html; charset='. $system->language->selected['charset']);
      $system->document->layout = 'ajax';
    }
    header('X-Robots-Tag: noindex');
    $system->document->snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  }
  
  if (isset($_POST['save'])) {
    
    $system->language->set($_POST['language_code']);
    
    $system->currency->set($_POST['currency_code']);
    
    $system->customer->data['country_code'] = $_POST['country_code'];
    $system->customer->data['zone_code'] = $_POST['zone_code'];
    
    $system->customer->data['shipping_address']['country_code'] = $_POST['country_code'];
    $system->customer->data['shipping_address']['zone_code'] = $_POST['zone_code'];
    
    if (empty($_GET['redirect'])) $_GET['redirect'] = WS_DIR_HTTP_HOME;
    
    header('Location: '. $_GET['redirect']);
    exit;
  }

?>
<h1><?php echo $system->language->translate('title_regional_settings', 'Regional Settings'); ?></h1>
<?php echo $system->functions->form_draw_form_begin('region_form', 'post', $system->document->link()); ?>
<table>
  <tr>
    <td><?php echo $system->language->translate('title_language', 'Language'); ?><br />
      <?php echo $system->functions->form_draw_languages_list('language_code', $system->language->selected['code']); ?></td>
    <td><?php echo $system->language->translate('title_currency', 'Currency'); ?><br />
      <?php echo $system->functions->form_draw_currencies_list('currency_code', $system->currency->selected['code']); ?></td>
  </tr>
  <tr>
    <td><?php echo $system->language->translate('title_country', 'Country'); ?><br />
      <?php echo $system->functions->form_draw_countries_list('country_code', $system->customer->data['country_code']); ?></td>
    <td><?php echo $system->language->translate('title_zone', 'Zone'); ?><br />
      <?php echo $system->functions->form_draw_zones_list($system->customer->data['country_code'], 'zone_code', $system->customer->data['zone_code']); ?></td>
  </tr>
  <tr>
    <td colspan="2"><?php echo $system->functions->form_draw_button('save', $system->language->translate('title_save', 'Save')); ?></td>
  </tr>
</table>
<?php echo $system->functions->form_draw_form_end(); ?>
<script type="text/javascript">
  $("select[name='country_code']").change(function(){
    $('body').css('cursor', 'wait');
    $.ajax({
      url: '<?php echo WS_DIR_AJAX .'zones.json.php'; ?>?country_code=' + $(this).val(),
      type: 'get',
      cache: true,
      async: true,
      dataType: 'json',
      error: function(jqXHR, textStatus, errorThrown) {
        if (console) console.warn(errorThrown.message);
      },
      success: function(data) {
        $("select[name='zone_code']").html('');
        if ($("select[name='zone_code']").attr('disabled')) $("select[name='zone_code']").removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $("select[name='zone_code']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $("select[name='zone_code']").attr('disabled', 'disabled');
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });
</script>
<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
  }
?>