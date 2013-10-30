<?php
  if (!in_array(__FILE__, array_slice(get_included_files(), 1))) {
    require_once('includes/config.inc.php');
    require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_header.inc.php');
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      header('Content-type: text/html; charset='. language::$selected['charset']);
      document::$layout = 'ajax';
    }
    header('X-Robots-Tag: noindex');
    document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  }
  
  if (isset($_POST['save'])) {
    
    language::set($_POST['language_code']);
    
    currency::set($_POST['currency_code']);
    
    customer::$data['country_code'] = $_POST['country_code'];
    customer::$data['zone_code'] = $_POST['zone_code'];
    
    customer::$data['shipping_address']['country_code'] = $_POST['country_code'];
    customer::$data['shipping_address']['zone_code'] = $_POST['zone_code'];
    
    if (empty($_GET['redirect'])) $_GET['redirect'] = WS_DIR_HTTP_HOME;
    
    header('Location: '. $_GET['redirect']);
    exit;
  }

?>
<h1<?php echo (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? ' style="margin-top: 0px;"' : ''; ?>><?php echo language::translate('title_regional_settings', 'Regional Settings'); ?></h1>
<?php echo functions::form_draw_form_begin('region_form', 'post', document::link()); ?>
<table>
  <tr>
    <td><?php echo language::translate('title_language', 'Language'); ?><br />
      <?php echo functions::form_draw_languages_list('language_code', language::$selected['code']); ?></td>
    <td><?php echo language::translate('title_currency', 'Currency'); ?><br />
      <?php echo functions::form_draw_currencies_list('currency_code', currency::$selected['code']); ?></td>
  </tr>
  <tr>
    <td><?php echo language::translate('title_country', 'Country'); ?><br />
      <?php echo functions::form_draw_countries_list('country_code', customer::$data['country_code']); ?></td>
    <td><?php echo language::translate('title_zone', 'Zone'); ?><br />
      <?php echo functions::form_draw_zones_list(customer::$data['country_code'], 'zone_code', customer::$data['zone_code']); ?></td>
  </tr>
  <tr>
    <td colspan="2"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save')); ?></td>
  </tr>
</table>
<?php echo functions::form_draw_form_end(); ?>
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