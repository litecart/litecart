<?php

if (empty($_GET['addon_id'])) {
	notices::add('errors', t('error_must_provide_addon', 'You must provide an add-on'));
	redirect(document::ilink(__APP__ . '/catalog'), 303);
	exit;
}

if (!($addon = marketplace_client::get_addon($_GET['addon_id']))) {
	notices::add('errors', t('error_invalid_addon', 'Invalid add-on'));
	redirect(document::ilink(__APP__ . '/catalog'), 303);
	exit;
}

$addon['installed'] = false;

foreach (f::file_search('storage://addons/*/vmod.xml') as $file) {
	$dom = new DOMDocument();
	$dom->load($file);

	if ($dom->getElementsByTagName('marketplace')->length) {
		if ($dom->getElementsByTagName('marketplace')->item(0)->getElementsByTagName('addon_id')->item(0)->textContent == $addon['id']) {
			$addon['installed'] = [
				'location' => dirname($file) . '/',
				'version' => $dom->getElementsByTagName('version')->item(0)->textContent,
			];
			break;
		}
	}
}

foreach ($addon['packages'] as $i => $package) {
	$addon['packages'][$i]['installed'] = ($package['version'] == $addon['installed']['version']) ? true: false;
}

if (!($profile = marketplace_client::whoami())) {
	notices::add('warnings', 'Failed to retrieve user profile');
}

if (isset($_POST['install'])) {
	try {
		if (empty($_POST['package_id'])) {
			throw new Exception(t('error_must_select_package', 'You must select a package'));
		}

		$result = marketplace_client::get_addon_package($_POST['package_id']);

		$tmp_file = f::file_create_tempfile();
		file_put_contents($tmp_file, base64_decode($result['data']));

		$zip = new ZipArchive();
		if ($zip->open($tmp_file, ZipArchive::RDONLY) !== true) {
			throw new Exception('Failed opening ZIP archive');
		}

		if (!($vmod = $zip->getFromName('vmod.xml'))) {
			throw new Exception('Could not find vmod.xml in package');
		}

		$dom = new DOMDocument('1.0', 'UTF-8');

		if (!@$dom->loadXML($vmod) || !$dom->getElementsByTagName('vmod')) {
			throw new Exception(t('error_xml_file_is_not_valid_vmod', 'XML file is not a valid vMod file'));
		}

		if (!empty($dom->getElementsByTagName('id'))) {
			$folder_name = $dom->getElementsByTagName('id')->item(0)->textContent;
		} elseif (!empty($dom->getElementsByTagName('name'))) {
			$folder_name = f::format_path_friendly($dom->getElementsByTagName('name')->item(0)->textContent);
		} else {
			throw new Exception(t('error_vmod_has_no_id_or_name', 'vMod has no ID or name'));
		}

		if (empty($folder_name)) {
			throw new Exception(t('error_could_not_determine_storage_location_for_addon', 'Could not determine storage location for add-on'));
		}

		if (!empty($addon['installed']['location']) && is_dir($addon['installed']['location'])) {
			f::file_delete($addon['installed']['location'], true);
		}

		if (!$zip->extractTo(f::file_realpath($folder))) {
			throw new Exception('Failed extracting contents from ZIP archive');
		}

		$zip->close();

		if (!empty($dom->getElementsByTagName('install'))) {
			$tmp_file = f::file_create_tempfile();
			file_put_contents($tmp_file, "<?php\r\n" . $dom->getElementsByTagName('install')->textContent);

			(function () {
				include func_get_arg(0);
			})($tmp_file);
		}

		notices::add('success', t('success_addon_installed', 'Add-on successfully installed'));

		redirect(document::ilink(), 303);
		exit;
	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
	}
}

if (isset($_POST['uninstall'])) {
	try {
		if (empty($addon['installed'])) {
			throw new Exception(t('error_addon_not_installed', 'The add-on is not installed'));
		}

		if (!is_dir($addon['installed']['location'])) {
			throw new Exception(t('error_addon_not_found_in_storage', 'Could not find add-on on storage location'));
		}

		$ent_addon = new ent_addon(basename($addon['installed']['location']));
		$ent_addon->delete(!empty($_POST['cleanup']));

		notices::add('success', t('success_addon_uninstalled', 'Add-on successfully uninstalled'));

		redirect(document::ilink(__APP__ . '/installed'), 303);
		exit;
	} catch (Exception $e) {
		notices::add('errors', $e->getMessage());
	}
}

f::draw_lightbox();
?>
<style>
.images {
	display: flex;
	gap: 1em;
	margin-bottom: 2em;
}

.label {
	padding: 0.25em 0.5em;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
}

.label-success {
	font-weight: bold;
}

.label-danger {
	opacity: .5;
}

.buy-license {
	padding: 1em;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
}

.btn-success.btn-outline{
	background: transparent;
	border: 1px solid var(--button-success-background-color);
	color: var(--button-success-background-color);
}

.license {
	padding: 1em;
	border: 1px solid green;
	margin-bottom: 2em;
	border-radius: var(--border-radius);
	color: green;
}

.license [class*="col-"] {
	align-self: center;
}

#install {
	padding: 2em;
	border: 1px solid var(--default-border-color);
	border-radius: var(--border-radius);
}

#uninstall {
	padding: 2em;
	border: 1px solid var(--button-danger-background-color);
	border-radius: var(--border-radius);
}
</style>

<div class="card card-app">
	<div class="card-header">
		<div class="card-title">
			<?php echo $app_icon; ?> <?php echo t('title_addons_market', 'Add-Ons Market'); ?>
		</div>
	</div>

	<div class="card-body">
		<div class="grid">
			<div class="col-md-6">

				<div class="grid">
					<div class="col-md-6">
						<?php if (!empty($addon['images'])) { ?>
						<div class="image">
							<a href="<?php echo document::href_link($addon['images'][0]['original']); ?>" data-toggle="lightbox">
								<img class="thumbnail" src="<?php echo f::escape_html($addon['images'][0]['thumbnail_2x']); ?>" alt="<?php echo f::escape_html($addon['name']); ?>">
							</a>
						</div>

						<div class="images">
							<?php foreach (array_slice($addon['images'], 1) as $image) { ?>
								<a href="<?php echo document::href_link($image['original']); ?>" data-toggle="lightbox">
									<img class="thumbnail" src="<?php echo f::escape_html($image['thumbnail']); ?>" srcset="<?php echo f::escape_html($image['thumbnail']); ?> 1x, <?php echo f::escape_html($image['thumbnail_2x']); ?> 2x" alt="<?php echo f::escape_html($addon['name']); ?>">
								</a>
							<?php } ?>
						</div>
						<?php } ?>
					</div>

					<div class="col-md-6">
						<h2><?php echo f::escape_html($addon['name']); ?></h2>

						<p class="short-description"><?php echo f::escape_html($addon['short_description']); ?></p>

					</div>
				</div>

				<h3><?php echo t('title_description', 'Description'); ?></h3>

				<p class="description"><?php echo nl2br($addon['description']); ?></p>

			</div>

			<div class="col-md-6">

				<?php if (!empty($addon['license'])) { ?>
				<div class="current-license">
					<div class="grid">
						<div class="col-md-6">
							<h3><?php echo t('title_active_license', 'Active License'); ?></h3>
							<div class="status"><?php echo t('text_valid_license_for_this_addon', 'You have a valid license for this add-on'); ?></div>
						</div>

						<div class="col-md-3">
							<label><?php echo t('title_purchase_date', 'Purchase Date'); ?></label>
							<div class="license-since"><?php echo f::datetime_format('date', $addon['license']['created_at']); ?></div>
						</div>

						<div class="col-md-3">
							<label><?php echo t('title_updates_expiry', 'Updates Expire'); ?></label>
							<?php if (empty($addon['license']['updates_expire'])) { ?>
								<div class="updates-expiry"><?php echo t('title_never', 'Never'); ?></div>
							<?php } else { ?>
								<div class="updates-expiry"><?php echo strtotime($addon['license']['updates_expire']) > time() ? f::datetime_format('date', $addon['license']['updates_expire']) : t('title_expired', 'Expired'); ?></div>
							<?php } ?>
						</div>
					</div>
				</div>

				<?php if (!empty($addon['installed'])) { ?>
				<div id="install" style="margin-top: 2em;">
					<h3><?php echo t('title_change_installed_package', 'Change Installed Package'); ?></h3>

					<div class="form-group" style="display: flex; gap: 1em;">
						<div class="dropdown" style="flex-grow: 1;">
							<div class="form-select" data-toggle="dropdown">-- <?php echo t('title_select', 'Select'); ?> --</div>
							<ul class="dropdown-menu">
								<?php foreach ($addon['packages'] as $package) { ?>
								<li>
									<label class="option">
										<input type="radio" name="package_id" value="<?php echo $package['id']; ?>"<?php if ($package['installed']) {	echo ' checked'; } ?>>
										<span class="title"><?php echo t('title_version', 'Version'); ?> <?php echo $package['installed'] ? ' (<strong>' . t('title_installed', 'Installed') . '</strong>)' : ''; ?></span>
										<div class="compatible-versions">
											<?php foreach ($package['compatible_versions'] as $version) { ?>
											<?php echo $version == PLATFORM_VERSION ? '<span class="label label-success">' . $version . '</span>' : '<span class="label label-danger">' . $version . '</span>'; ?>
											<?php } ?>
										</div>
									</label>
								</li>
								<?php } ?>
							</ul>
						</div>
					</div>

					<?php echo f::form_button('install', t('title_change', 'Change'), 'submit', 'class="btn btn-success"'); ?>
				</div>

				<div id="uninstall" style="margin-top: 2em;">

					<h3><?php echo t('title_uninstall_addon', 'Uninstall Add-on'); ?></h3>

					<?php echo f::form_begin('uninstall_form', 'post'); ?>

						<div class="form-group">
							<?php echo f::form_checkbox('clean', [t('text_clean_up_traces_of_addon', 'Clean up all traces of the add-on')]); ?>
							<p><?php echo t('description_clean_up_traces_of_addon', 'Check this option if you are permanently uninstalling the addon and want to remove all traces of files or database entries from the add-on.'); ?></p>
						</div>

						<div>
							<?php echo f::form_button('uninstall', t('title_uninstall', 'Uninstall'), 'submit', 'class="btn btn-danger"'); ?>
						</div>

					<?php echo f::form_end(); ?>
				</div>

				<?php } else { ?>

				<h3 style="margin-top: 2em;"><?php echo t('text_select_package_to_install', 'Select package to install'); ?></h3>

				<div class="form-group" style="display: flex; gap: 1em;">
					<div class="dropdown" style="flex-grow: 1;">
						<div class="form-select" data-toggle="dropdown">-- <?php echo t('title_select', 'Select'); ?> --</div>
						<ul class="dropdown-menu">
							<?php foreach ($addon['packages'] as $package) { ?>
							<li>
								<label class="option">
									<input type="radio" name="package_id" value="<?php echo $package['id']; ?>">
									<span class="title"><?php echo t('title_version', 'Version'); ?> <?php echo $package['version']; ?></span>
									<div class="compatible-versions">
										<?php foreach ($package['compatible_versions'] as $version) { ?>
											<?php echo $version == PLATFORM_VERSION ? '<span class="label label-success">' . $version . '</span>' : '<span class="label label-danger">' . $version . '</span>'; ?>
										<?php } ?>
									</div>
								</label>
							</li>
							<?php } ?>
						</ul>
					</div>

					<div>
						<?php echo f::form_button('install', t('title_install', 'Install'), 'submit', 'class="btn btn-success"'); ?>
					</div>
				</div>
				<?php } ?>
				<?php } ?>

				<?php if (empty($addon['license'])) { ?>
				<div class="buy-license">
					<h3><?php echo t('title_buy_license', 'Buy License'); ?></h3>

					<div class="grid">

						<?php if (!empty($addon['monthly_fee'])) { ?>
						<div class="col-md-4">
							<label><?php echo t('title_monthly_subscription', 'Monthly Subscription'); ?></label>
							<div class="monthly-fee"><?php echo $addon['monthly_fee_formatted']; ?></div>
						</div>
						<?php } ?>

						<?php if (!empty($addon['price'])) { ?>
						<div class="col-md-4">
							<label><?php echo t('title_one_time_purchase', 'One-Time Purchase'); ?></label>
							<div class="price"><?php echo $addon['price']['formatted']; ?></div>
						</div>
						<?php } ?>
					</div>

					<div>
						<a class="btn btn-success btn-lg" href="<?php echo document::href_link('https://www.litecart.net/addons/addon', ['addon_id' => $addon['id']]); ?>" target="_blank">
							<?php echo t('title_buy_now', 'Buy Now'); ?> <?php echo f::draw_fonticon('icon-square-out'); ?>
						</a>
					</div>
				</div>
				<?php } ?>

			</div>
		</div>
	</div>
</div>

<script>
</script>