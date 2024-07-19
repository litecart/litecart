<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/site_top_navigation.inc.php
	 */

	$site_navigation = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/site_top_navigation.inc.php');
	echo $site_navigation->render();
