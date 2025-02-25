<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/site_cookie_notice.inc.php
	 */

	$site_cookie_notice = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/site_cookie_notice.inc.php');

	echo $site_cookie_notice->render();
