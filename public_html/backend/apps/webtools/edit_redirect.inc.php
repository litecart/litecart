<?php

  if (!empty($_GET['redirect_id'])) {
    $redirect = new ent_redirect($_GET['redirect_id']);
  } else {
    $redirect = new ent_redirect();
  }

  if (!$_POST) {
    $_POST = $redirect->data;
  }

  breadcrumbs::add(!empty($redirect->data['id']) ? language::translate('title_edit_redirect', 'Edit Redirect') : language::translate('title_create_new_redirect', 'Create New Redirect'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['status'])) {
				$_POST['status'] = 0;
			}

      foreach ([
        'status',
        'immediate',
        'pattern',
        'destination',
        'http_response_code',
        'date_valid_from',
        'date_valid_to',
      ] as $field) {
        if (isset($_POST[$field])) {
          $redirect->data[$field] = $_POST[$field];
        }
      }

      $redirect->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', ['doc' => 'redirects'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($redirect->data['id'])) {
        throw new Exception(language::translate('error_must_provide_url', 'You must provide a url'));
      }

      $redirect->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', ['doc' => 'redirects'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $hostname = preg_replace('#^www\.#', '', $_SERVER['HTTP_HOST']);

	$protocol_options = [
		['', 'HTTP or HTTPS'],
		['http', 'HTTP only'],
		['https', 'HTTPS only'],
	];

	$domain_options = [
		['', language::translate('text_irrelevant', 'Irrelevant')],
		['=', language::translate('text_matches_exactly', 'Matches exactly')],
		['*', language::translate('text_contains', 'Contains')],
		['^', language::translate('text_starts_with', 'Starts with')],
		['$', language::translate('text_ends_with', 'Ends with')],
		['regex', language::translate('text_matches_regex', 'Matches RegEx')],
	];

	$path_options = [
		['', language::translate('text_irrelevant', 'Irrelevant')],
		['=', language::translate('text_matches_exactly', 'Matches exactly')],
		['*', language::translate('text_contains', 'Contains')],
		['^', language::translate('text_starts_with', 'Starts with')],
		['$', language::translate('text_ends_with', 'Ends with')],
		['regex', language::translate('text_matches_regex', 'Matches RegEx')],
	];

	$query_options = [
		['', language::translate('text_irrelevant', 'Irrelevant')],
		['=', language::translate('text_matches_exactly', 'Matches exactly')],
		['*', language::translate('text_contains', 'Contains')],
		['^', language::translate('text_starts_with', 'Starts with')],
		['$', language::translate('text_ends_with', 'Ends with')],
		['regex', language::translate('text_matches_regex', 'Matches RegEx')],
	];

	if (empty($_POST['pattern'])) {
		$_POST['pattern'] = '^https?://[^/]*.*?(\\?|$)';
	}

	$type_options = [
		[302, 'Redirect'],
		[301, 'Moved Permanently'],
	];

?>
<div class="card">
  <div class="card-header">
    <h1 class="card-title"><?php echo $app_icon; ?> <?php echo !empty($redirect->data['id']) ? language::translate('title_edit_redirect', 'Edit Redirect') : language::translate('title_create_new_redirect', 'Create New Redirect'); ?></h1>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('redirect_form', 'post', false, false, 'autocomplete="off" style="max-width: 720px;"'); ?>

      <div class="grid">
        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_status', 'Status'); ?></div>
            <?php echo functions::form_toggle('status', 'e/d', true); ?>
          </label>
        </div>
      </div>

      <div class="form-group">
        <?php echo functions::form_radio_button('immediate', ['1', language::translate('title_firstly', 'Firstly')], true); ?>
        <div><?php echo language::translate('text_process_rule_firstly', 'Process the rule before processing any logical resource'); ?></div>
      </div>

      <div class="form-group">
        <?php echo functions::form_radio_button('immediate', ['0', language::translate('title_lastly', 'Lastly')], !file_get_contents('php://input') ? '0' : true); ?>
        <div><?php echo language::translate('text_process_rule_lastly', 'Process the rule as a last destination if no logical resource was found'); ?></div>
      </div>

      <fieldset id="regex-helper" style="margin-bottom: 2em;">
        <legend>
          <strong>
            <?php echo functions::form_checkbox('use_helper', ['1', language::translate('text_use_regex_helper', 'Use regex helper')]); ?>
          </strong>
        </legend>

        <div class="grid form-group">
          <div class="col-md-4">
            <?php echo language::translate('title_http_protocol', 'HTTP Protocol'); ?>
          </div>
          <div class="col-md-8">
            <?php echo functions::form_select('regex_helper[protocol][criteria]', $protocol_options, true, 'style="width: auto;"'); ?>
          </div>
        </div>

        <div class="grid form-group">
          <div class="col-md-4">
            <?php echo language::translate('title_domain_name', 'Domain Name'); ?>
          </div>
          <div class="col-md-8">
            <div class="input-group">
              <?php echo functions::form_select('regex_helper[domain][operator]', $domain_options, true, 'style="width: auto;"'); ?>
              <?php echo functions::form_input_text('regex_helper[domain][criteria]', true, 'required placeholder="Example: domain.com"'); ?>
            </div>
          </div>
        </div>

        <div class="grid form-group">
          <div class="col-md-4">
            <?php echo language::translate('title_path', 'Path'); ?>
          </div>
          <div class="col-md-8">
            <div class="input-group">
              <?php echo functions::form_select('regex_helper[path][operator]', $path_options, true, 'style="width: auto;"'); ?>
              <?php echo functions::form_input_text('regex_helper[path][criteria]', true, 'required placeholder="Example: /path/to/document"'); ?>
            </div>
          </div>
        </div>

        <div class="grid form-group">
          <div class="col-md-4">
            <?php echo language::translate('title_query_parameters', 'Query Parameters'); ?>
          </div>
          <div class="col-md-8">
            <div class="input-group">
              <?php echo functions::form_select('regex_helper[query][operator]', $query_options, true, 'style="width: auto;"'); ?>
              <?php echo functions::form_input_text('regex_helper[query][criteria]', true, 'required placeholder="Example: foo=bar" disabled'); ?>
            </div>
          </div>
        </div>

      </fieldset>

      <label class="form-group">
        <div class="form-label"><?php echo language::translate('title_url_regex_pattern', 'URL Regex Pattern'); ?></div>
        <?php echo functions::form_input_text('pattern', true, 'list="sources" required'); ?>
        <datalist id="sources">
          <option value="^https://<?php echo strtr($hostname, ['.' => '\\.']); ?>/path/to/file(\?|$)">Exact match of path with optional query at the end</option>
          <option value="^https://<?php echo strtr($hostname, ['.' => '\\.']); ?>/path/to/file\?foo=bar$">Exact match of path and query on specific domain and protocol</option>
          <option value="^https://<?php echo strtr($hostname, ['.' => '\\.']); ?>/path/to/file?id=(1|2|3)$">Match a query with id 1, 2, or 3 on a specific domain and protocol</option>
          <option value="^https?://(www\.)?<?php echo strtr($hostname, ['.' => '\\.']); ?>/path/to/file$">Match path on a specific domain with our without www</option>
          <option value="^https?://[^/]*/path/to/file">Match path on any domain and protocol</option>
          <option value="^https?://[^/]*/([a-z]{2}/)?path/to/file">Match path with any language prefix on any domain and protocol</option>
          <option value="^...">URL begins with</option>
          <option value="...$">URL ends with</option>
          <option value="\.">Matches a dot</option>
          <option value="\?">Matches a question mark</option>
          <option value="\??">Matches an optional question mark</option>
        </datalist>
      </label>

      <label class="form-group">
        <div class="form-label"><?php echo language::translate('title_destination', 'Destination'); ?></div>
        <div class="input-group">
          <?php echo functions::form_input_text('destination', true, 'list="destinations" required'); ?>
          <?php echo functions::form_select('http_response_code', $type_options, true, 'required'); ?>
        </div>
        <datalist id="destinations">
          <option value="https://<?php echo $hostname; ?>/path/to/file">Exact URL</option>
          <option value="/path/to/file">Absolute path relative to the domain</option>
          <option value="$1">Output first matched paranthesis group</option>
        </datalist>
      </label>

      <div class="grid">
        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_valid_from', 'Valid From'); ?></div>
            <?php echo functions::form_input_datetime('date_valid_from', true); ?>
          </label>
        </div>

        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_valid_to', 'Valid To'); ?></div>
            <?php echo functions::form_input_datetime('date_valid_to', true); ?>
          </label>
        </div>
      </div>

      <?php if (empty($redirect->data['id'])) { ?>
      <div class="grid">
        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_redirects', 'Redirects'); ?></div>
            <div class="form-input" readonly><?php echo (int)$redirect->data['redirects']; ?></div>
          </label>
        </div>

        <div class="col-md-6">
          <label class="form-group">
            <div class="form-label"><?php echo language::translate('title_last_redirected', 'Last Redirected'); ?></div>
            <div class="form-input" readonly><?php echo $redirect->data['date_redirected'] ? functions::datetime_when($redirect->data['date_redirected']): '-'; ?></div>
          </label>
        </div>
      </div>
      <?php } ?>

    <?php echo functions::form_end(); ?>
  </div>

  <div class="card-action">
		<?php echo functions::form_button_predefined('save'); ?>
		<?php echo (!empty($redirect->data['id'])) ? functions::form_button_predefined('delete') : ''; ?>
		<?php echo functions::form_button_predefined('cancel'); ?>
  </div>
</div>

<script>
  $('input[name="use_helper"]').on('change', function() {
    $('#regex-helper').prop('disabled', !$('input[name="use_helper"]').is(':checked'));
    $('input[name="pattern"]').prop('readonly', $('input[name="use_helper"]').is(':checked'));
  }).trigger('change');

  $('select[name="regex_helper[domain][operator]"]').on('change', function() {
    $('input[name="regex_helper[domain][criteria]"]').prop('disabled', $('select[name="regex_helper[domain][operator]"]').val() ? false : true);
  }).trigger('change');

  $('select[name="regex_helper[path][operator]"]').on('change', function() {
    $('input[name="regex_helper[path][criteria]"]').prop('disabled', $('select[name="regex_helper[path][operator]"]').val() ? false : true);
  }).trigger('change');

  $('select[name="regex_helper[query][operator]"]').on('change', function() {
    $('input[name="regex_helper[query][criteria]"]').prop('disabled', $('select[name="regex_helper[query][operator]"]').val() ? false : true);
  }).trigger('change');

  $('#regex-helper').on('input change', ':input', function(e) {

    if (!$('input[name="use_helper"]').is(':checked')) return;

    let protocol = $('#regex-helper :input[name="regex_helper[protocol][criteria]"]').val(),
      domain = $('#regex-helper :input[name="regex_helper[domain][criteria]"]').val() || 'domain.com',
      path = $('#regex-helper :input[name="regex_helper[path][criteria]"]').val() || '/path/to/document',
      query = $('#regex-helper :input[name="regex_helper[query][criteria]"]').val() || 'foo=bar';

		// Escape special characters
    if ($('#regex-helper :input[name="regex_helper[domain][operator]"]').val() != 'regex') {
      domain.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    if ($('#regex-helper :input[name="regex_helper[path][operator]"]').val() != 'regex') {
      path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    if ($('#regex-helper :input[name="regex_helper[query][operator]"]').val() != 'regex') {
      query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    let regex = '^';

    switch (protocol) {
			case '':      regex += 'https?://'; break;
      case 'http':  regex += 'http://'; break;
      case 'https': regex += 'https://'; break;
    }

    switch ($('#regex-helper :input[name="regex_helper[domain][operator]"]').val()) {
      case '^': regex += domain + '[^/]*'; break;
      case '$': regex += '[^/]*' + domain; break;
      case '*': regex += '[^/]*' + domain + '[^/]*'; break;
      case '=': regex += domain; break;
      case 'regex': regex += domain; break;
      default:  regex += '[^/]*'; break;
    }

    switch ($('#regex-helper :input[name="regex_helper[path][operator]"]').val()) {
      case '^': regex += path + '[^\\?]*'; break;
      case '$': regex += '[^\\?]*' + path; break;
      case '*': regex += '[^\\?]*' + path + '[^\\?]*'; break;
      case '=': regex += path; break;
      case 'regex': regex += path; break;
      default:  regex += '[^\\?]*'; break;
    }

    switch ($('#regex-helper :input[name="regex_helper[query][operator]"]').val()) {
      case '^': regex += '\\?' + query; break;
      case '$': regex += '\\?.*' + query + '$'; break;
      case '*': regex += '\\?.*' + query; break;
      case '=': regex += '\\?' + query + '$'; break;
      case 'regex': regex += query; break;
      default:  regex += '(\\?|$)'; break;
    }

    if (regex && regex != '^https?://[^/]*.*?(\\?|$)') {
      $('input[name="pattern"]').val(regex);
    } else {
      $('input[name="pattern"]').val('');
    }
  });
</script>