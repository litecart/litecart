<a id="cart" class="text-center" href="<?php echo functions::escape_html($link); ?>">
  <!--<img class="image" src="{snippet:template_path}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="" />-->
  <div class="navbar-icon"><?php echo functions::draw_fonticon('fa-shopping-basket'); ?></div>
  <small class="hidden-xs"><?php echo language::translate('title_cart', 'Cart'); ?></small>
  <div class="badge quantity"><?php echo $num_items ? $num_items : ''; ?></div>
</a>