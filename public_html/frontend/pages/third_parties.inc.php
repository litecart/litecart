<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/third_parties.inc.php
	 */

	header('X-Robots-Tag: noindex, nofollow', true);

	document::$title[] = t('title_third_parties', 'Third Parties');
	//document::$description = t('third_parties:meta_description', '');

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/third_parties.inc.php');

	$_page->snippets['third_parties'] =  database::query(
		"select *, JSON_VALUE(name, '$.". language::$selected['code'] ."') as name
		from ". DB_TABLE_PREFIX ."third_parties
		where status
		order by name;"
	)->fetch_all(function($party) {
		$party['active'] = (isset($_GET['third_party_id']) && $_GET['third_party_id'] == $party['id']);
		return $party;
	});

	echo $_page->render();
