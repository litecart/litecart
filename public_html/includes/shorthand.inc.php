<?php

	function t(...$args){
		return forward_static_call_array(['language', 'translate'], $args);
	}
