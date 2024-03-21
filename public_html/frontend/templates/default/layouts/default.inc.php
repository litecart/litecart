<!DOCTYPE html>
<html lang="{{language}}" dir="{{text_direction}}">
<head>
<title>{{title}}</title>
<meta charset="{{charset}}" />
<meta name="description" content="{{description}}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/variables.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/framework.min.css'); ?>" />
<link rel="stylesheet" href="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/css/app.min.css'); ?>" />
{{head_tags}}
</head>
<body>

<div id="page">
  <header>
    <?php include 'app://frontend/partials/site_top_navigation.inc.php'; ?>
    <?php include 'app://frontend/partials/site_navigation.inc.php'; ?>
  </header>

  <main>
    {{content}}
  </main>

  <footer>
    <?php include 'app://frontend/partials/site_footer.inc.php'; ?>
  </footer>
</div>

<a id="scroll-up" class="hidden-print" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

<?php include 'app://frontend/templates/'.settings::get('template').'/partials/site_cookie_notice.inc.php'; ?>

{{foot_tags}}
<script src="<?php echo document::href_rlink('app://frontend/templates/'.settings::get('template').'/js/app.min.js'); ?>"></script>
</body>
</html>