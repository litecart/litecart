<?php

  function password_generate($length=8, $min_lowercases=1, $min_uppercases=1, $min_numbers=1, $min_specials=0) {

    $lowercases = 'abcdefghijklmnopqrstuvwxyz';
    $uppercases = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $specials = '!#%&/(){}[]+-';

    $absolutes = '';
    if ($min_lowercases && !is_bool($min_lowercases)) $absolutes .= substr(str_shuffle(str_repeat($lowercases, $min_lowercases)), 0, $min_lowercases);
    if ($min_uppercases && !is_bool($min_uppercases)) $absolutes .= substr(str_shuffle(str_repeat($uppercases, $min_uppercases)), 0, $min_uppercases);
    if ($min_numbers && !is_bool($min_numbers)) $absolutes .= substr(str_shuffle(str_repeat($numbers, $min_numbers)), 0, $min_numbers);
    if ($min_specials && !is_bool($min_specials)) $absolutes .= substr(str_shuffle(str_repeat($specials, $min_specials)), 0, $min_specials);

    $remaining = $length - strlen($absolutes);

    $characters = '';
    if ($min_lowercases !== false) $characters .= substr(str_shuffle(str_repeat($lowercases, $remaining)), 0, $remaining);
    if ($min_uppercases !== false) $characters .= substr(str_shuffle(str_repeat($uppercases, $remaining)), 0, $remaining);
    if ($min_numbers !== false) $characters .= substr(str_shuffle(str_repeat($numbers, $remaining)), 0, $remaining);
    if ($min_specials !== false) $characters .= substr(str_shuffle(str_repeat($specials, $remaining)), 0, $remaining);

    $password = str_shuffle($absolutes . substr($characters, 0, $remaining));

    return $password;
  }

  function password_check_strength($password) {

    preg_replace('#[a-z]#', '', $password, -1, $lowercases);
    preg_replace('#[A-Z]#', '', $password, -1, $uppercases);
    preg_replace('#[0-9]#', '', $password, -1, $numbers);
    preg_replace('#[^\w]#', '', $password, -1, $symbols);

    $score = ($numbers * 9) + ($lowercases * 11.25) + ($uppercases * 11.25) + ($symbols * 15)
           + ($numbers ? 10 : 0) + ($lowercases ? 10 : 0) + ($uppercases ? 10 : 0) + ($symbols ? 10 : 0);

    return ($score >= 80) ? true : false;
  }

// Deprecated in LiteCart 2.2.0 in favour of PHP password_hash() - Keep for backwards compatibility and migration
  function password_checksum($login, $password) {
    if (!defined('PASSWORD_SALT')) trigger_error('There is no password salt defined.', E_USER_ERROR);
    if (strlen($password) < 2) {
      return hash('sha256', strtolower($login) . $password . PASSWORD_SALT);
    } else {
      $password = str_split($password, ceil(strlen($password)/2));
      return hash('sha256', strtolower($login) . $password[0] . PASSWORD_SALT . $password[1]);
    }
  }
