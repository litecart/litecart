<div id="box-manufacturers" class="box">
  <div class="heading">
    <h1><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h1>
  </div>
  <div class="content">
    <ul class="listing-wrapper" class="manufacturers">
      <?php foreach($manufacturers as $manufacturer) { ?>
      <li class="manufacturer shadow hover-light">
        <a class="link" href="<?php echo htmlspecialchars($manufacturer['link']); ?>" title="<?php echo htmlspecialchars($manufacturer['name']); ?>">
          <div class="image" style="position: relative;">
              <img src="<?php echo htmlspecialchars($manufacturer['image']); ?>" alt="<?php echo htmlspecialchars($manufacturer['name']); ?>" /><br />
            </div>
           <div class="title"><?php echo $manufacturer['name']; ?></div>
         </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</div>