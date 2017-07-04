<?php
  require('includes/header.inc.php');

  ini_set('display_errors', 'On');

// Function to get object from a relative path to this script
  function get_absolute_path($path=null) {
    if (empty($path)) $path = dirname(__FILE__);
    $path = str_replace('\\', '/', $path);
    $parts = array_filter(explode('/', $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
      if ('.' == $part) continue;
      if ('..' == $part) {
        array_pop($absolutes);
      } else {
        $absolutes[] = $part;
      }
    }
    return ((substr(PHP_OS, 0, 3) == 'WIN') ? '' : '/') . implode('/', $absolutes);
  }

  $document_root = get_absolute_path(dirname(__FILE__) . '/..') .'/';

  function return_bytes($val) {
    $val = trim($val);
    switch(strtolower($val[strlen($val)-1])) {
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    }
    return $val;
  }

  $countries = array(
    'AF' => 'Afghanistan',
    'AL' => 'Albania',
    'DZ' => 'Algeria',
    'AS' => 'American Samoa',
    'AD' => 'Andorra',
    'AO' => 'Angola',
    'AI' => 'Anguilla',
    'AQ' => 'Antarctica',
    'AG' => 'Antigua and Barbuda',
    'AR' => 'Argentina',
    'AM' => 'Armenia',
    'AW' => 'Aruba',
    'AU' => 'Australia',
    'AT' => 'Austria',
    'AZ' => 'Azerbaijan',
    'BS' => 'Bahamas',
    'BH' => 'Bahrain',
    'BD' => 'Bangladesh',
    'BB' => 'Barbados',
    'BY' => 'Belarus',
    'BE' => 'Belgium',
    'BZ' => 'Belize',
    'BJ' => 'Benin',
    'BM' => 'Bermuda',
    'BT' => 'Bhutan',
    'BO' => 'Bolivia',
    'BA' => 'Bosnia and Herzegowina',
    'BW' => 'Botswana',
    'BV' => 'Bouvet Island',
    'BR' => 'Brazil',
    'IO' => 'British Indian Ocean Territory',
    'BN' => 'Brunei Darussalam',
    'BG' => 'Bulgaria',
    'BF' => 'Burkina Faso',
    'BI' => 'Burundi',
    'KH' => 'Cambodia',
    'CM' => 'Cameroon',
    'CA' => 'Canada',
    'CV' => 'Cape Verde',
    'KY' => 'Cayman Islands',
    'CF' => 'Central African Republic',
    'TD' => 'Chad',
    'CL' => 'Chile',
    'CN' => 'China',
    'CX' => 'Christmas Island',
    'CC' => 'Cocos (Keeling) Islands',
    'CO' => 'Colombia',
    'KM' => 'Comoros',
    'CG' => 'Congo',
    'CK' => 'Cook Islands',
    'CR' => 'Costa Rica',
    'CI' => 'Cote D\'Ivoire',
    'HR' => 'Croatia',
    'CU' => 'Cuba',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'CD' => 'Democratic Republic of Congo',
    'DK' => 'Denmark',
    'DJ' => 'Djibouti',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'TP' => 'East Timor',
    'EC' => 'Ecuador',
    'EG' => 'Egypt',
    'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea',
    'ER' => 'Eritrea',
    'EE' => 'Estonia',
    'ET' => 'Ethiopia',
    'FK' => 'Falkland Islands (Malvinas)',
    'FO' => 'Faroe Islands',
    'FJ' => 'Fiji',
    'FI' => 'Finland',
    'FR' => 'France',
    'GF' => 'French Guiana',
    'PF' => 'French Polynesia',
    'TF' => 'French Southern Territories',
    'GA' => 'Gabon',
    'GM' => 'Gambia',
    'GE' => 'Georgia',
    'DE' => 'Germany',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GR' => 'Greece',
    'GL' => 'Greenland',
    'GD' => 'Grenada',
    'GP' => 'Guadeloupe',
    'GU' => 'Guam',
    'GT' => 'Guatemala',
    'GN' => 'Guinea',
    'GW' => 'Guinea-bissau',
    'GY' => 'Guyana',
    'HT' => 'Haiti',
    'HM' => 'Heard and Mc Donald Islands',
    'HN' => 'Honduras',
    'HK' => 'Hong Kong',
    'HU' => 'Hungary',
    'IS' => 'Iceland',
    'IN' => 'India',
    'ID' => 'Indonesia',
    'IR' => 'Iran (Islamic Republic of)',
    'IQ' => 'Iraq',
    'IE' => 'Ireland',
    'IL' => 'Israel',
    'IT' => 'Italy',
    'JM' => 'Jamaica',
    'JP' => 'Japan',
    'JO' => 'Jordan',
    'KZ' => 'Kazakhstan',
    'KE' => 'Kenya',
    'KI' => 'Kiribati',
    'KR' => 'Korea, Republic of',
    'KW' => 'Kuwait',
    'KG' => 'Kyrgyzstan',
    'LA' => 'Lao People\'s Democratic Republic',
    'LV' => 'Latvia',
    'LB' => 'Lebanon',
    'LS' => 'Lesotho',
    'LR' => 'Liberia',
    'LY' => 'Libyan Arab Jamahiriya',
    'LI' => 'Liechtenstein',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MO' => 'Macau',
    'MK' => 'Macedonia',
    'MG' => 'Madagascar',
    'MW' => 'Malawi',
    'MY' => 'Malaysia',
    'MV' => 'Maldives',
    'ML' => 'Mali',
    'MT' => 'Malta',
    'MH' => 'Marshall Islands',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MU' => 'Mauritius',
    'YT' => 'Mayotte',
    'MX' => 'Mexico',
    'FM' => 'Micronesia, Federated States of',
    'MD' => 'Moldova, Republic of',
    'MC' => 'Monaco',
    'MN' => 'Mongolia',
    'MS' => 'Montserrat',
    'MA' => 'Morocco',
    'MZ' => 'Mozambique',
    'MM' => 'Myanmar',
    'NA' => 'Namibia',
    'NR' => 'Nauru',
    'NP' => 'Nepal',
    'NL' => 'Netherlands',
    'AN' => 'Netherlands Antilles',
    'NC' => 'New Caledonia',
    'NZ' => 'New Zealand',
    'NI' => 'Nicaragua',
    'NE' => 'Niger',
    'NG' => 'Nigeria',
    'NU' => 'Niue',
    'NF' => 'Norfolk Island',
    'KP' => 'North Korea',
    'MP' => 'Northern Mariana Islands',
    'NO' => 'Norway',
    'OM' => 'Oman',
    'PK' => 'Pakistan',
    'PW' => 'Palau',
    'PA' => 'Panama',
    'PG' => 'Papua New Guinea',
    'PY' => 'Paraguay',
    'PE' => 'Peru',
    'PH' => 'Philippines',
    'PN' => 'Pitcairn',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'PR' => 'Puerto Rico',
    'QA' => 'Qatar',
    'RE' => 'Reunion',
    'RO' => 'Romania',
    'RU' => 'Russian Federation',
    'RW' => 'Rwanda',
    'KN' => 'Saint Kitts and Nevis',
    'LC' => 'Saint Lucia',
    'VC' => 'Saint Vincent and the Grenadines',
    'WS' => 'Samoa',
    'SM' => 'San Marino',
    'ST' => 'Sao Tome and Principe',
    'SA' => 'Saudi Arabia',
    'SN' => 'Senegal',
    'RS' => 'Serbia',
    'SC' => 'Seychelles',
    'SL' => 'Sierra Leone',
    'SG' => 'Singapore',
    'SK' => 'Slovak Republic',
    'SI' => 'Slovenia',
    'SB' => 'Solomon Islands',
    'SO' => 'Somalia',
    'ZA' => 'South Africa',
    'GS' => 'South Georgia &amp; South Sandwich Islands',
    'ES' => 'Spain',
    'LK' => 'Sri Lanka',
    'SH' => 'St. Helena',
    'PM' => 'St. Pierre and Miquelon',
    'SD' => 'Sudan',
    'SR' => 'Suriname',
    'SJ' => 'Svalbard and Jan Mayen Islands',
    'SZ' => 'Swaziland',
    'SE' => 'Sweden',
    'CH' => 'Switzerland',
    'SY' => 'Syrian Arab Republic',
    'TW' => 'Taiwan',
    'TJ' => 'Tajikistan',
    'TZ' => 'Tanzania, United Republic of',
    'TH' => 'Thailand',
    'TG' => 'Togo',
    'TK' => 'Tokelau',
    'TO' => 'Tonga',
    'TT' => 'Trinidad and Tobago',
    'TN' => 'Tunisia',
    'TR' => 'Turkey',
    'TM' => 'Turkmenistan',
    'TC' => 'Turks and Caicos Islands',
    'TV' => 'Tuvalu',
    'UG' => 'Uganda',
    'UA' => 'Ukraine',
    'AE' => 'United Arab Emirates',
    'GB' => 'United Kingdom',
    'US' => 'United States',
    'UM' => 'United States Minor Outlying Islands',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VU' => 'Vanuatu',
    'VA' => 'Vatican City State (Holy See)',
    'VE' => 'Venezuela',
    'VN' => 'Viet Nam',
    'VG' => 'Virgin Islands (British)',
    'VI' => 'Virgin Islands (U.S.)',
    'WF' => 'Wallis and Futuna Islands',
    'EH' => 'Western Sahara',
    'YE' => 'Yemen',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe',
  );

?>

  <h1>Installer</h1>

  <div class="row">
    <div class="col-md-6">
      <h2>System Requirements</h2>

      <ul class="list-unstyled">
        <li>PHP 5.3+ <?php echo version_compare(PHP_VERSION, '5.3', '>=') ? '<span class="ok">['. PHP_VERSION .']</span>' : '<span class="error">['. PHP_VERSION .']</span>'; ?>
          <ul>
            <li>Settings
              <ul>
                <li>register_globals = <?php echo ini_get('register_globals'); ?> <?php echo in_array(strtolower(ini_get('register_globals')), array('off', 'false', '', '0')) ? '<span class="ok">[OK]</span>' : '<span class="error">[Not recommended]</span>'; ?></li>
                <li>arg_separator.output = <?php echo htmlspecialchars(ini_get('arg_separator.output')); ?> <?php echo (ini_get('arg_separator.output') == '&') ? '<span class="ok">[OK]</span>' : '<span class="error">[Not recommended]</span>'; ?></li>
                <li>memory_limit = <?php echo ini_get('memory_limit'); ?> <?php echo (return_bytes(ini_get('memory_limit')) >= 128*1024*1024) ? '<span class="ok">[OK]</span>' : '<span class="error">[Not recommended]</span>'; ?></li>
              </ul>
            </li>
            <li>Extensions
              <ul>
                <li>dom <?php echo extension_loaded('dom') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'; ?></li>
                <li>mbstring <?php echo extension_loaded('mbstring') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'; ?></li>
                <li>mysqli <?php echo extension_loaded('mysqli') ? '<span class="ok">[OK]</span>' : (extension_loaded('mysql') ? '<span class="warning">[Warning] Obsolete extension mysql, install mysqli instead</span>' : '<span class="error">[Missing]</span>'); ?></li>
                <li>gd / imagick <?php echo extension_loaded('imagick') ? '<span class="ok">[OK]</span>' : (extension_loaded('gd') ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'); ?></li>
              </ul>
            </li>
          </ul>
        </li>
        <li>Apache 2 compatible HTTP daemon
          <?php if (function_exists('apache_get_modules')) $installed_apache_modules = apache_get_modules(); ?>
          <ul>
            <li>Allow, Deny</li>
            <li>Options -Indexes</li>
            <li>Modules
              <ul>
                <li>mod_auth_basic <?php if (!empty($installed_apache_modules)) echo (in_array('mod_auth', $installed_apache_modules) || in_array('mod_auth_basic', $installed_apache_modules)) ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'; ?></li>
                <li>mod_deflate <?php if (!empty($installed_apache_modules)) echo in_array('mod_deflate', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'; ?></li>
                <li>mod_env <?php if (!empty($installed_apache_modules)) echo in_array('mod_env', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'; ?></li>
                <li>mod_headers <?php if (!empty($installed_apache_modules)) echo in_array('mod_headers', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'; ?></li>
                <li>mod_rewrite <?php if (!empty($installed_apache_modules)) echo in_array('mod_rewrite', $installed_apache_modules) ? '<span class="ok">[OK]</span>' : '<span class="error">[Missing]</span>'; ?></li>
              </ul>
            </li>
          </ul>
        </li>
        <li>MySQL 5.5+</li>
      </ul>
    </div>

    <div class="col-md-6">

      <h2>Writables</h2>

      <ul class="list-unstyled">
<?php
  $paths = array(
    'admin/.htaccess',
    'admin/.htpasswd',
    'cache/',
    'data/',
    'images/',
    'includes/config.inc.php',
    'vqmod/',
    'vqmod/xml/',
    'vqmod/vqcache/',
    'vqmod/checked.cache',
    'vqmod/mods.cache',
    '.htaccess',
  );
  foreach($paths as $path) {
    if (file_exists($path) && is_writable('../' . $path)) {
      echo '    <li>~/'. $path .' <span class="ok">[OK]</span></li>' . PHP_EOL;
    } else if (is_writable('../' . pathinfo($path, PATHINFO_DIRNAME))) {
      echo '    <li>~/'. $path .' <span class="ok">[OK]</span></li>' . PHP_EOL;
    } else {
      echo '    <li>~/'. $path .' <span class="error">[Read-only, please make path writable]</span></li>' . PHP_EOL;
    }
  }
?>
      </ul>
    </div>
  </div>

  <h2>Installation Parameters</h2>

  <?php if (file_exists('../includes/config.inc.php')) { ?>
  <p class="alert alert-danger">
    Attention: An existing installation has been detected. The existing installation <strong>WILL BE DELETED</strong> if you continue! <a href="upgrade.php">Upgraders click here to for the upgrade tool</a>.
  </p>
  <?php } ?>

  <form name="installation_form" method="post" action="install.php">

    <input class="form-control" name="client_ip" type="hidden" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>" />

    <h3>File System</h3>

    <div class="row">
      <div class="form-group col-md-6">
        <label>Installation Path</label>
        <div class="form-control"><?php echo $document_root; ?></div>
      </div>
    </div>

    <h3>MySQL</h3>

    <div class="row">
      <div class="form-group col-md-6">
      <label>Type</label>
        <select class="form-control" name="db_type" required="required">
          <option value="mysql">MySQL</option>
        </select>
      </div>

      <div class="form-group col-md-6">
        <label>Hostname</label>
        <input class="form-control" name="db_server" type="text" placeholder="localhost" />
      </div>

      <div class="form-group col-md-6">
        <label>Database</label>
        <input class="form-control" type="text" name="db_database" required="required" />
      </div>

      <div class="form-group col-md-6">
        <label>Collation</label>
        <select class="form-control" name="db_collation" required="required">
          <option>utf8_bin</option>
          <option>utf8_general_ci</option>
          <option>utf8_unicode_ci</option>
          <option>utf8_icelandic_ci</option>
          <option>utf8_latvian_ci</option>
          <option>utf8_romanian_ci</option>
          <option>utf8_slovenian_ci</option>
          <option>utf8_polish_ci</option>
          <option>utf8_estonian_ci</option>
          <option>utf8_spanish_ci</option>
          <option selected="selected">utf8_swedish_ci</option>
          <option>utf8_turkish_ci</option>
          <option>utf8_czech_ci</option>
          <option>utf8_danish_ci</option>
          <option>utf8_lithuanian_ci</option>
          <option>utf8_slovak_ci</option>
          <option>utf8_spanish2_ci</option>
          <option>utf8_roman_ci</option>
          <option>utf8_persian_ci</option>
          <option>utf8_esperanto_ci</option>
          <option>utf8_hungarian_ci</option>
          <option>utf8_sinhala_ci</option>
        </select>
      </div>

      <div class="form-group col-md-6">
        <label>Username</label>
        <input class="form-control" type="text" name="db_username" required="required" />
      </div>

      <div class="form-group col-md-6">
        <label>Password</label>
        <input class="form-control" type="password" name="db_password" />
      </div>

      <div class="form-group col-md-6">
        <label>Table Prefix</label>
        <input class="form-control" name="db_table_prefix" type="text" value="lc_" style="max-width: 50%;" />
      </div>

      <div class="form-group col-md-6">
        <label>Demo Data</label>
        <div class="checkbox">
          <label><input name="demo_data" type="checkbox" value="true" <?php echo !file_exists('data/demo/data.sql') ? 'disabled="disabled"' : ''; ?> /> Install demo data</label>
        </div>
      </div>
    </div>

    <h3>Store Information</h3>

    <div class="row">
      <div class="form-group col-md-6">
        <label>Store Name</label>
        <input class="form-control" name="store_name" type="text" value="My Store" required="required" />
      </div>

      <div class="form-group col-md-6">
        <label>Store Email</label>
        <input class="form-control" name="store_email" type="text" value="store@email.com" required="required" />
      </div>


      <div class="form-group col-md-6">
        <label>Country</label>
        <select class="form-control" name="country_code" required="required">
          <option value="">-- Select --</option>
          <?php foreach ($countries as $code => $name) echo '<option value="'. $code .'">'. $name .'</option>' . PHP_EOL; ?>
        </select>
      </div>

      <div class="form-group col-md-6">
        <label>Time Zone<br />
        <select class="form-control" name="store_time_zone" required="required">
<?php
  foreach (timezone_identifiers_list() as $zone) {
    $zone = explode('/', $zone); // 0 => Continent, 1 => City
    if (in_array($zone[0], array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'))) {
      if (!empty($zone[1])) {
        echo '<option>'. $zone[0]. '/' . $zone[1]  .'</option>';
      }
    }
  }
?>
        </select>
      </div>
    </div>

    <h3>Administration</h3>

    <div class="row">
      <div class="form-group col-md-6">
        <label>Folder Name</label>
        <input class="form-control" name="admin_folder" type="text" value="admin" required="required" />
      </div>

      <div class="form-group col-md-6">
        <label>Username</label>
        <input class="form-control" name="username" type="text" id="username" value="admin" required="required" />
      </div>

      <div class="form-group col-md-6">
        <label>Password</label>
        <input class="form-control" name="password" type="text" id="password" required="required" />
      </div>
    </div>

    <hr />

    <p class="text-center">
      This software is licensed under <a href="https://creativecommons.org/licenses/by-nd/4.0/" target="blank">Creative Commons BY-ND 4.0</a>.<br />
      By installing this software you agree to the terms and conditions.
    </p>

    <input class="btn btn-default btn-block" type="submit" name="install" value="Install Now" onclick="if(!confirm('This will now install LiteCart. Any existing databases tables will be overwritten with new data.')) return false;" style="font-size: 1.5em; padding: 0.5em;" />
  </form>
<?php require('includes/footer.inc.php'); ?>
