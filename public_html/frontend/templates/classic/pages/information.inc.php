<main id="main" class="container">
  <div id="sidebar">
    <?php include 'app://frontend/partials/box_information_links.inc.php'; ?>
  </div>

  <div id="content">
    {{breadcrumbs}}
    {{notices}}

    <section id="box-information">
      <?php echo $content; ?>
    </section>
  </div>
</main>