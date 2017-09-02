<?php
  if (!function_exists('custom_draw_site_menu_item')) {
    function custom_draw_site_menu_item($item, $indent=0) {
      $output = '<li data-type="'. $item['type'] .'" data-id="'. $item['id'] .'">'
              . '  <a href="'. htmlspecialchars($item['link']) .'">'. $item['title'] .'</a>';
      if (!empty($item['subitems'])) {
        $output .= '  <ul class="list-untyled">' . PHP_EOL;
        foreach ($item['subitems'] as $subitem) {
          $output .= custom_draw_site_menu_item($subitem, $indent+1);
        }
        $output .= '  </ul>' . PHP_EOL;
      }
      $output .= '</li>' . PHP_EOL;
      return $output;
    }
  }
?>
<div id="site-menu" class="twelve-eighty">
	<nav class="navbar navbar-default">

	  <div class="navbar-header">
	    <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search'), false, 'class="navbar-form"'); ?>
	      <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' …"'); ?>
	    <?php echo functions::form_draw_form_end(); ?>

	    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#default-menu">
	      <?php echo functions::draw_fonticon('fa-bars'); ?>
	    </button>
	  </div>

	  <div id="default-menu" class="navbar-collapse collapse">

	    <ul class="nav navbar-nav">
	      <li class="hidden-xs">
	        <a href="<?php echo document::ilink(''); ?>" class="navbar-brand"><i class="fa fa-home"></i></a>
	      </li>

        <?php if ($categories) { ?>
	      <li class="dropdown">
	        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_categories', 'Categories'); ?> <b class="caret"></b></a>
	        <div class="dropdown-menu" style="width: 250px;">
	          <ul class="list-unstyled">
              <?php foreach ($categories as $item) echo custom_draw_site_menu_item($item); ?>
	          </ul>
	        </div>
	      </li>
        <?php } ?>

        <?php if ($manufacturers) { ?>
	      <li class="dropdown">
	        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?> <b class="caret"></b></a>
	        <div class="dropdown-menu" style="width: 250px;">
	          <ul class="list-unstyled">
              <?php foreach ($manufacturers as $item) echo custom_draw_site_menu_item($item); ?>
	          </ul>
	        </div>
	      </li>
        <?php } ?>

        <?php if ($pages) { ?>
	      <li class="dropdown">
	        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><?php echo language::translate('title_information', 'Information'); ?><b class="caret"></b></a>
	        <div class="dropdown-menu" style="width: 640px;">
	          <ul class="list-unstyled">
              <?php foreach ($pages as $item) echo custom_draw_site_menu_item($item); ?>
            </ul>
	        </div>
	      </li>
        <?php } ?>

	    </ul>
	  </div>
	</nav>
</div>