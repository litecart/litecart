<?php

	administrator::reset();

	session::regenerate_id();

	if (!empty($_COOKIE['remember_me'])) {
		header('Set-Cookie: remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
	}

	redirect(document::ilink('login'));
	exit;
