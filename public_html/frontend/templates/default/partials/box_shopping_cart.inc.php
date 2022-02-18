<div id="cart" class="dropdown">
  <a href="{{link|escape}}">
    <img class="image" src="{{template_path}}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="" />
    <div class="badge quantity"><?php echo $num_items ? $num_items : ''; ?></div>
  </a>
  <ul class="dropdown-menu dropdown-menu-right">
    <li><h2><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></li>
    <?php foreach ($items as $item) { ?>
    <li></li>
    <?php } ?>
    <li class="new-item"></li>
    <li class="text-end"><?php echo language::translate('title_subtotal', 'Subtotal'); ?>: <?php echo currency::format(cart::$total['amount']); ?></li>
    <li><hr /></li>
    <li><a class="btn btn-success btn-lg" href="{{link|escape}}"><?php echo language::translate('title_go_to_chekout', 'Go To Checkout'); ?></a></li>
  </ul>
</div>

<script>
  $('#cart > a').click(function(e){
    e.preventDefault();
    $('body').toggleClass('cart-open');
  });
</script>
