<main id="main" class="container">
  <div id="content">
    {{notices}}

    <h1><?php echo language::translate('title_categories', 'Categories'); ?></h1>

    <?php include vmod::check(FS_DIR_APP . 'frontend/partials/box_categories.inc.php'); ?>
  </div>
</main>
