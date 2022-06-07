<?php
  document::$layout = 'ajax';
?>
<style>
#modal-product-picker tbody tr {
  cursor: pointer;
}
</style>

<div id="modal-product-picker" class="modal fade" style="max-width: 980px; display: none;">

  <h2><?php echo language::translate('title_products', 'Products'); ?></h2>

  <div class="modal-body">
    <div class="form-group">
      <?php echo functions::form_draw_text_field('query', true, 'placeholder="'. functions::escape_html(language::translate('title_search', 'Search')) .'" autocomplete="off"'); ?>
    </div>

    <div class="form-group table-responsive">
      <table class="table table-striped table-hover data-table">
        <thead>
          <tr>
            <th><?php echo language::translate('title_id', 'ID'); ?></th>
            <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
            <th><?php echo language::translate('title_sku', 'SKU'); ?></th>
            <th><?php echo language::translate('title_quantity', 'Quantity'); ?></th>
            <th><?php echo language::translate('title_price', 'Price'); ?></th>
            <th><?php echo language::translate('title_date_created', 'Date Created'); ?></th>
          </tr>
        </thead>
        <tbody />
      </table>
    </div>
  </div>

</div>

<script>
  var xhr_product_picker = null;
  $('#modal-product-picker input[name="query"]').on('input', function(){

    if ($(this).val() == '') {
      $('#modal-product-picker tbody').html('');
      xhr_product_picker = null;
      return;
    }

    xhr_product_picker = $.ajax({
      type: 'get',
      async: true,
      cache: false,
      url: '<?php echo document::ilink('catalog/products.json'); ?>?query=' + $(this).val(),
      dataType: 'json',
      beforeSend: function(jqXHR) {
        jqXHR.overrideMimeType('text/html;charset=' + $('html meta[charset]').attr('charset'));
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.error(textStatus + ': ' + errorThrown);
      },
      success: function(json) {

        $('#modal-product-picker tbody').html('');

        if (!json) {
          $('#modal-product-picker tbody').html(
            '<tr>' +
            '  <td colspan="6"><em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em></td>' +
            '</tr>'
          );
        }

        $.each(json, function(i, row){
          $('#modal-product-picker tbody').append(
            '<tr>' +
            '  <td class="id">' + row.id + '</td>' +
            '  <td class="name">' + row.name + '</td>' +
            '  <td class="sku">' + row.sku + '</td>' +
            '  <td class="quantity text-end">' + row.quantity + '</td>' +
            '  <td class="price text-end">' + row.price.formatted + '</td>' +
            '  <td class="date-created">' + row.date_created + '</td>' +
            '</tr>'
          );
        });
        if ($('#modal-product-picker .results tbody').html() == '') {
          $('#modal-product-picker .results tbody').html('<tr><td colspan="6"><em><?php echo functions::escape_js(language::translate('text_no_results', 'No results')); ?></em></td></tr>');
        }
      },
    });
  }).focus();

  $('#modal-product-picker tbody').on('click', 'td', function() {

    var callback = $.featherlight.current().$currentTarget.data('callback');

    var $tr = $(this).closest('tr');

    var product = {
      id: $tr.find('.id').text(),
      name: $tr.find('.name').text(),
      sku: $tr.find('.sku').text(),
      quantity: $tr.find('.quantity').text(),
      price: $tr.find('.price').text(),
    }

    if (callback) {
      window[callback](product);
    } else if ($.featherlight.current().$currentTarget[0].closest('.input-group')) {
      var field = $.featherlight.current().$currentTarget[0].closest('.input-group');
      $(field).find(':input').val(product.id).trigger('change');
      $(field).find('.id').text(product.id);
      $(field).find('.name').text(product.name);
      $.featherlight.close();
    }
  });
</script>