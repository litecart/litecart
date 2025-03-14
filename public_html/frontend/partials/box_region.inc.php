<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_region.inc.php
	 */

	$box_region = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_region.inc.php');

	echo $box_region->render();
