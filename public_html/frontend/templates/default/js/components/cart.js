// Add to cart animation
  $('body').on('submit', 'form[name="buy_now_form"]', function(e) {
    e.preventDefault();

    let $form = $(this)
        $button = $(this).find('button[type="submit"]'),
        $target = $('#site-navigation .shopping-cart'),
        target_height = $target.innerHeight(),
        target_width = $target.innerWidth(),
        $object = $('<div id="animated-cart-item"></div>');

    updateCart($form.serialize() + '&add_cart_product=true');

    $object.css({
      position: 'absolute',
      top: $button.offset().top,
      left: $button.offset().left,
      height: $button.height(),
      width: $button.width(),
      border: '1px rgba(0, 136, 204, 1) solid',
      backgroundColor: 'rgba(0, 136, 204, .5)',
      borderRadius: 'var(--border-radius)',
      padding: '.5em',
      zIndex: '999999',
    })
    .appendTo('body')
    .animate({
      top: $target.offset().top,
      left: $target.offset().left,
      height: target_height,
      width: target_width,
      borderRadius: 0
    }, {
      duration: '1000ms',
      easing: 'easeOutCubic'
    })
    .animate({
      opacity: 0
    }, {
      duration: 100,
      complete: function(){
        $object.remove();
        $target.addClass('open');
      }
    });
  });

  $('body').on('click', 'button[name="remove_cart_item"]', function(e) {
    updateCart('remove_cart_item='+ $(this).val());
  });

// Update cart / Keep alive
  if (typeof(window._env) !== 'undefined') {
    window.updateCart = function(data) {
      $.ajax({
        url: window._env.platform.url + 'ajax/cart.json',
        type: data ? 'post' : 'get',
        data: data,
        cache: false,
        async: true,
        dataType: 'json',
        beforeSend: function(jqXHR) {
          jqXHR.overrideMimeType('text/html;charset=' + $('meta[charset]').attr('charset'));
        },
        error: function(jqXHR, textStatus, errorThrown) {
          $('#animated-cart-item').remove();
          if (data) alert('Error while updating cart');
        },
        success: function(result) {

          if (result.alert) {
            $('#animated-cart-item').remove();
            alert(result.alert);
          }

          $('#site-navigation .shopping-cart .badge').text(result.items.length);
          $('#site-navigation .shopping-cart').toggleClass('filled', result.items.length ? true : false);
          $('#site-navigation .shopping-cart ul .item').remove();

          let html = '';
          $.each(result.items, function(key, item){
            html += '<div class="item">' +
                    '  <div class="row">' +
                    '    <div class="col-3">' +
                    '      ' + $('<img class="image img-responsive" />').attr('src', item.thumbnail).attr('alt', item.name).prop('outerHTML') +
                    '    </div>' +
                    '    <div class="col-8">' +
                    '      <div>' + $('<a class="name"></a>').attr('href', item.link).text(item.name).prop('outerHTML') + '</div>' +
                    '      ' + $('<div class="price"></div>').text(item.formatted_price).prop('outerHTML') +
                    '    </div>' +
                    '    <div class="col-1 text-end">' +
                    '      ' + $('<button class="btn btn-danger btn-sm" name="remove_cart_item" type="submit"><i class="fa fa-trash"></i></button>').val(item.key).prop('outerHTML') +
                    '    </div>' +
                    '  </div>' +
                    '</div>';
          });

          $('#site-navigation .shopping-cart ul').prepend(html);
        }
      });
    }

    let timerCart = setInterval('updateCart()', 60e3); // Keeps session alive
  }
