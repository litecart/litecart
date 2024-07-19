<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/site_footer.inc.php
	 */

	$site_footer_cache_token = cache::token('store_footer', ['language', 'login', 'region']);
	if (cache::capture($site_footer_cache_token)) {

		$site_footer = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/site_footer.inc.php');

		$site_footer->snippets = [
			'pages' => [],
			'modules' => [],
			'social_bookmarks' => [],
		];

		// Pages
		$site_footer->snippets['pages'] = database::query(
			"select p.id, pi.title from ". DB_TABLE_PREFIX ."pages p
			left join ". DB_TABLE_PREFIX ."pages_info pi on (p.id = pi.page_id and pi.language_code = '". database::input(language::$selected['code']) ."')
			where status
			and find_in_set('information', dock)
			order by p.priority, pi.title;"
		)->fetch_all(function($page) {
			return [
				'id' => $page['id'],
				'title' => $page['title'],
				'link' => document::href_ilink('information', ['page_id' => $page['id']]),
			];
		});

		// Modules
		database::query(
			"select id, settings  from ". DB_TABLE_PREFIX ."modules
			where type in ('shipping', 'payment')
			and status
			order by type, id;"
		)->each(function($module) use (&$site_footer) {
			$module['settings'] = json_decode($module['settings'], true);

			if (empty($module['settings']['icon'])) return;

			$icon = 'app://'.$module['settings']['icon'];

			if (!is_file($icon)) return;

			$site_footer->snippets['modules'][] = [
				'id' => $module['id'],
				'icon' => $icon,
			];
		});

			// Social media
		foreach ([
			'facebook',
			'instagram',
			'linkedin',
			'pinterest',
			'twitter',
			'youtube',
		] as $platform) {

			if (!$link = settings::get($platform.'_link')) continue;

			$site_footer->snippets['social_bookmarks'][] = [
				'type' => $platform,
				'title' => ucfirst($platform),
				'icon' => 'fa-'.$platform,
				'link' => $link,
			];
		}

		echo $site_footer->render();

		cache::end_capture($site_footer_cache_token);
	}
