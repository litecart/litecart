<!DOCTYPE html>
<html lang="{snippet:language}">
<head>
<title>{snippet:title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={snippet:charset}" />
<link href="<!--snippet:template_path-->styles/printable.css" rel="stylesheet" type="text/css" media="print, screen" />
<!--snippet:head_tags-->
<!--snippet:javascript-->
</head>
<body>
<?php if (isset($_GET['media']) && $_GET['media'] == 'print') { ?>
<script type="text/javascript">
  window.onload = function() {
    window.print();
  }
</script>
<?php } ?>
<!--snippet:content-->
</body>
</html>