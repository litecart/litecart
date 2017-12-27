<div id="cart">
  <a href="<?php echo htmlspecialchars($link); ?>">
    <div class="details">
      <div class="title"><?php echo language::translate('title_shopping_cart', 'Shopping Cart'); ?></div>
      <span class="quantity"><?php echo $num_items; ?></span> <?php echo language::translate('text_items', 'item(s)'); ?> - <span class="formatted_value"><?php echo $cart_total; ?></span>
    </div>
    <img class="image" src="{snippet:template_path}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="" />
  </a>
</div>