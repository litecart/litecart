<div id="cart">
  <a href="<?php echo functions::escape_html($link); ?>">
    <img class="image" src="{snippet:template_path}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="" />
    <div class="badge quantity"><?php echo $num_items ? $num_items : ''; ?></div>
  </a>
</div>