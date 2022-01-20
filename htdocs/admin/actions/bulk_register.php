<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

	if ($_POST['action'] === 'new') {
		//convert array (comma delimited). Iterate thru each email/add it
		$email_raw = $_POST['email'];
		$emails = explode(',', $email_raw);
		$team_name_raw = $_POST['team_name'];
		$team_names = explode(',', $team_name_raw);
		$country = $_POST['country'];

		if (count($emails) != count($team_names)) {
			message_error("Number of emails != Number of Teamnames");
		}

		if (isset($type) && !is_valid_id($type)) {
			message_error(lang_get('invalid_team_type'));
		}

		for ($i = 0; $i < count($emails); $i++) {
			$email = $emails[$i];
			$team_name = $team_names[$i];

			if (!valid_email($email) || strlen($team_name) > get_db_config('MELLIVORA_CONFIG_MAX_TEAM_NAME_LENGTH') || strlen($team_name) < get_db_config('MELLIVORA_CONFIG_MIN_TEAM_NAME_LENGTH')) {
				log_exception(new Exception('Invalid User Details'), false, "Invalid User Details entered, skipping this user. Email: " . $email . " Team name: " . $team_name);
				continue;
			}

			$password = generate_random_string(12);
			if (!register_account(
				$email,
				$password,
				$team_name,
				$country,
				$type,
				false
			)) {
				log_exception(new Exception('Sign Up failed'), false, "Invalid User Details entered, skipping this user. Email: " . $email . " Team name: " . $team_name);
			}
		}
		redirect('/admin/bulk_register?generic_success=1');
	}
} 