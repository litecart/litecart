<?php

	function validate_email(string $email): bool {
		return preg_match('#^([a-zA-Z0-9])+([a-zA-Z0-9\+\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$#', $email) === 1;
	}
