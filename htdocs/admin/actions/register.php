<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

	if ($_POST['action'] === 'new') {
		//convert array (comma delimited). Iterate thru each email/add it
		$email_raw = $_POST['email'];
		$emails = explode(',', $email_raw);
		$username_raw = $_POST['username'];
		$usernames = explode(',', $username_raw);
		$country = $_POST['country'];

		if (count($emails) != count($usernames)) {
			message_error("Number of emails NOT EQUAL TO Number of usernames");
		}

		if (isset($type) && !is_valid_id($type)) {
			message_error(lang_get('invalid_user_type'));
		}

		for ($i = 0; $i < count($emails); $i++) {
			$email = $emails[$i];
			$username = $usernames[$i];

			if (!valid_email($email) || strlen($username) > get_db_config('MELLIVORA_CONFIG_MAX_USERNAME_LENGTH') || strlen($username) < get_db_config('MELLIVORA_CONFIG_MIN_USERNAME_LENGTH')) {
				log_exception(new Exception('Invalid User Details'), false, "Invalid User Details entered, skipping this user. Email: " . $email . " Username: " . $username);
				continue;
			}

			$password = generate_random_string(12);
			if (!register_account(
				$email,
				$password,
				$username,
				$country,
				$type,
				false
			)) {
				log_exception(new Exception('Sign Up failed'), false, "Invalid User Details entered, skipping this user. Email: " . $email . " Username: " . $username);
			}
		}
		redirect('/admin/register?generic_success=1');
	}
} 