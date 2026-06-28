<?php

	administrator::reset();

	session::regenerate_id();
	security::rotate_csrf_token();

	if (!empty($_COOKIE['remember_me'])) {
		header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
	}

	redirect(document::ilink('login'), 303);
	exit;
