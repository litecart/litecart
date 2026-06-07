<!doctype html>
<html lang="<?php echo $language_code; ?>" dir="<?php echo $text_direction; ?>">
<head>
<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo mb_http_output(); ?>">
<style>
<?php echo file_get_contents('app://assets/litecore/css/email.min.css'); ?>
</style>
</head>

<body>

	<table class="body" border="0" cellpadding="0" cellspacing="0">
		<tr>

			<td class="container">
				<div class="content">

					<table class="main">

						<tr>
							<td class="wrapper" align="center">
							{{jumbotron}}
							</td>
						</tr>

						<tr>
							<td class="wrapper">
								{{content}}
							</td>
						</tr>

					</table>

					<div class="footer">
						<table border="0" cellpadding="0" cellspacing="0">

							<tr>
								<td class="content-block" align="center">
									<img src="data:image/png;base64,<?php echo base64_encode(file_get_contents('storage://images/logotype.png')); ?>" title="<?php echo settings::get('store_name'); ?>" width="250">
								</td>
							</tr>

							<tr>
								<td class="content-block powered-by">
									<?php echo settings::get('store_name'); ?><br>
									<a href="<?php echo document::href_ilink('', [], [], [], $language_code); ?>" target="_blank"><?php echo document::ilink('', [], [], [], $language_code); ?></a>
								</td>
							</tr>
						</table>
					</div>

				</div>
			</td>

		</tr>
	</table>

</body>
</html>
