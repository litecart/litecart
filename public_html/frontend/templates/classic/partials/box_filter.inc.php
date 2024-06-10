<div id="box-filter" class="box">
  <?php echo functions::form_begin('filter_form', 'get'); ?>

    <div class="filters">

      <?php echo functions::form_input_search('product_name', true, 'autocomplete="off" data-token-group="name" data-token-title="'. language::translate('title_name', 'Name') .'" placeholder="'. functions::escape_attr(language::translate('text_filter_by_product_name', 'Filter by product name')) .'"'); ?>

      <?php if ($brands) { ?>
      <div class="dropdown">
        <div class="form-input" data-toggle="dropdown">
          <?php echo language::translate('title_brands', 'Brands'); ?>
        </div>
        <ul class="dropdown-menu">
          <?php foreach ($brands as $brand) { ?>
          <li>
            <label class="option"><?php echo functions::form_checkbox('brands[]', $brand['id'], true, 'data-token-group="brand" data-token-title="'. language::translate('title_brand', 'Brand') .'" data-token-value="'. $brand['name'] .'"'); ?>
              <span class="title"><?php echo $brand['name']; ?></span>
            </label>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>

      <?php if ($attributes) foreach ($attributes as $group) { ?>
      <div class="dropdown">
        <div class="form-input" data-toggle="dropdown">
          <?php echo $group['name']; ?>
        </div>
        <ul class="dropdown-menu">
          <?php foreach ($group['values'] as $value) { ?>
          <li>
            <label class="option"><?php echo !empty($group['select_multiple']) ? functions::form_checkbox('attributes['. $group['id'] .'][]', $value['id'], true, 'data-token-group="attribute-'. $group['id'] .'" data-token-title="'. functions::escape_attr($group['name']) .'" data-token-value="'. functions::escape_attr($value['value']) .'"') : functions::form_radio_button('attributes['. $group['id'] .'][]', $value['id'], true, 'data-token-group="attribute-'. $group['id'] .'" data-token-title="'. functions::escape_attr($group['name']) .'" data-token-value="'. functions::escape_attr($value['value']) .'"'); ?>
              <span class="title"><?php echo $value['value']; ?></span>
            </label>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>

      <div class="dropdown" style="flex-grow: 0;">
        <div class="form-input" data-toggle="dropdown">
          <?php echo language::translate('title_sort_by', 'Sort By'); ?>
        </div>
        <ul class="dropdown-menu">
          <?php foreach ($sort_alternatives as $key => $title) { ?>
          <li>
            <label class="option">
              <?php echo functions::form_radio_button('sort', $key, true); ?>
              <span class="title"><?php echo $title; ?></span>
            </label>
          </li>
          <?php } ?>
        </ul>
      </div>

      <div class="btn-group btn-group-inline" data-toggle="buttons" style="flex-grow: 0;">
        <label class="btn btn-default<?php echo ($_GET['list_style'] == 'columns') ? ' active' : ''; ?>"><input type="radio" name="list_style" value="columns"<?php echo (!isset($_GET['list_style']) || $_GET['list_style'] == 'columns') ? ' checked' : ''; ?>><?php echo functions::draw_fonticon('fa-th'); ?></label>
        <label class="btn btn-default<?php echo ($_GET['list_style'] == 'rows') ? ' active' : ''; ?>"><input type="radio" name="list_style" value="rows"<?php echo (isset($_GET['list_style']) && $_GET['list_style'] == 'rows') ? ' checked' : ''; ?>><?php echo functions::draw_fonticon('fa-th-list'); ?></label>
      </div>
    </div>

    <div class="tokens"></div>

  <?php echo functions::form_end(); ?>
</div>

<script>
  $('body').on('input', '#box-filter form[name="filter_form"] :input', function(){
    $('#box-filter .tokens').html('');

    $.each($('#box-filter input[data-token-title][type="search"]'), function(i,el) {
      if (!$(this).val()) return;

      var $token = $('<span class="token"></span>');

      $token.attr('data-group', $(el).data('token-group'))
        .attr('data-name', $(el).attr('name'))
        .attr('data-value', $(el).val())
        .text($(el).data('token-title') +': '+ $(el).val())
        .append('<a href="#" class="remove">×</a></span>');

      $('#box-filter .tokens').append($token);
    });

    $.each($('#box-filter input[data-token-title][type="checkbox"]:checked, #box-filter input[data-token-title][type="radio"]:checked'), function(i,el) {
      if (!$(this).val()) return;

      var $token = $('<span class="token"></span>');

      $token.attr('data-group', $(el).data('token-group'))
        .attr('data-name', $(el).attr('name'))
        .attr('data-value', $(el).val())
        .text($(el).data('token-title') +': '+ $(el).data('token-value'))
        .append('<a href="#" class="remove">×</a></span>');

      $('#box-filter .tokens').append($token);
    });
  });

  $('#box-filter form[name="filter_form"] input[name="product_name"]').trigger('input');

  var xhr_filter = null;
  $('body').on('input', '#box-filter form[name="filter_form"]', function(){
    if (xhr_filter) xhr_filter.abort();
    var url = new URL(location.protocol + '//' + location.host + location.pathname + '?' + $('form[name="filter_form"]').serialize());
    history.replaceState(null, null, url);
    $('.listing.products').hide();
    xhr_filter = $.ajax({
      type: 'get',
      url: url.href,
      dataType: 'html',
      success: function(response){
        var content = $('.listing.products', response).html();
        var classes = $('.listing.products', response).attr('class');
        var pagination = $('.pagination', response).length ? $('.pagination', response).html() : '';
        console.log($('.listing.products').length);
        $('.listing.products').html(content).attr('class', classes).show();
        $('.pagination').html(pagination);
      }
    });
  });

  $('body').on('click', '#box-filter form[name="filter_form"] .tokens .remove', function(e){
    e.preventDefault();
    var token = $(this).closest('.token');
    switch ($(':input[name="'+ $(token).data('name') +'"]').attr('type')) {
      case 'radio':
      case 'checkbox':
        $(':input[name="'+ $(token).data('name') +'"][value="'+ $(token).data('value') +'"]').prop('checked', false).trigger('input');
        break;
      case 'text':
      case 'search':
        $(':input[name="'+ $(token).data('name') +'"]').val('').trigger('input');
        break;
    }
  });
</script>