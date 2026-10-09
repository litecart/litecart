<!doctype html>
<html lang="<?= $language_code ?>" dir="<?= $text_direction ?>">
<head>
<meta name="viewport" content="width=device-width">
<meta http-equiv="Content-Type" content="text/html; charset=<?= mb_http_output() ?>">
<style>
<?= file_get_contents('app://assets/litecore/css/email.min.css') ?>
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
									<img src="data:image/png;base64,<?= base64_encode(file_get_contents('storage://images/logotype.png')) ?>" title="<?= settings::get('store_name') ?>" width="250">
								</td>
							</tr>

							<tr>
								<td class="content-block powered-by">
									<?= settings::get('store_name') ?><br>
									<a href="<?= document::href_ilink('', [], [], [], $language_code) ?>" target="_blank"><?= document::ilink('', [], [], [], $language_code) ?></a>
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
