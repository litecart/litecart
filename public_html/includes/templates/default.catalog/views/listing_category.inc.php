<div class="category category-<?php echo $category_id; ?> col-xs-whole col-sm-halfs col-md-thirds">
  <a class="link shadow" href="<?php echo htmlspecialchars($link); ?>">
    <div class="thumbnail">
      <img src="<?php echo htmlspecialchars($image['thumbnail']); ?>" alt="" title="<?php echo htmlspecialchars($name); ?>" />
      <div class="caption">
        <h4><?php echo $name; ?></h4>
        <p><?php echo $short_description; ?></p>
      </div>
    </div>
  </a>
</div>
