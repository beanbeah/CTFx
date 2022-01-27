<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

	if ($_POST['action'] === 'change_times') {

		require_fields(array('ctf_start_time'), $_POST);
		require_fields(array('ctf_end_time'), $_POST);
		require_fields(array('ctf_timezone'), $_POST);

		$from_raw = $_POST['ctf_start_time'];
		$end_raw = $_POST['ctf_end_time'];
		$timezone = $_POST['ctf_timezone'];

		//shamelessly stolen from https://stackoverflow.com/questions/24375711/mm-dd-yyyy-hhmmss-am-pm-date-validation-regular-expression-in-javascript
		$expression = "/^(((0[1-9]|[12]\d|3[01])[\/\.-](0[13578]|1[02])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|[12]\d|30)[\/\.-](0[13456789]|1[012])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|1\d|2[0-8])[\/\.-](02)[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((29)[\/\.-](02)[\/\.-]((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])))$/";

		//date-time validation
		if (preg_match($expression, $from_raw) && preg_match($expression, $end_raw) && in_array($timezone,timezone_identifiers_list())) {
			update_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE',$timezone,'string');

			$from = new DateTime($from_raw, new DateTimeZone(get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')));
			$end = new DateTime($end_raw, new DateTimeZone(get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')));

			$from = $from->format('U');
			$end = $end->format('U');

			update_db_config('MELLIVORA_CONFIG_CTF_START_TIME',$from,'string');
			update_db_config('MELLIVORA_CONFIG_CTF_END_TIME', $end, 'string');

			db_update_all(
				'challenges',
				array(
					'available_from' => $from,
					'available_until' => $end
				)
			);

			redirect('/admin/edit_ctf.php?generic_success=1');
		} else {
			//something went wrong with input
			redirect('/admin/edit_ctf.php?generic_failure=1');
		}

	} else if ($_POST['action'] === 'scoreboard_freeze') {
		$_POST['freeze'] ? $freeze = false: $freeze = true;
		update_db_config('MELLIVORA_CONFIG_SHOW_SCOREBOARD',$freeze,'bool');

		redirect('/admin/edit_ctf.php?generic_success=1');

	} else if ($_POST['action'] === 'signup_settings') {
		$_POST['signup_allowed'] ? $signup_allowed = true : $signup_allowed = false;
		$_POST['email_whitelist_check'] ? $email_whitelist_check = true : $email_whitelist_check = false;
		$_POST['email_regex_check'] ? $email_regex_check = true : $email_regex_check = false;
		$_POST['accounts_enabled_by_default'] ? $accounts_enabled_by_default = true : $accounts_enabled_by_default = false;
		is_numeric($_POST['username_min_length']) ? $min_length = $_POST['username_min_length'] : $min_length = get_db_config('MELLIVORA_CONFIG_MIN_USERNAME_LENGTH');
		is_numeric($_POST['username_max_length']) ? $max_length = $_POST['username_max_length'] : $max_length = get_db_config('MELLIVORA_CONFIG_MAX_USERNAME_LENGTH');
		$_POST['email_password_on_signup'] ? $email_password_on_signup = true : $email_password_on_signup = false; 
		
		update_db_config('MELLIVORA_CONFIG_ACCOUNTS_SIGNUP_ALLOWED', $signup_allowed, 'bool');
		update_db_config('MELLIVORA_CONFIG_EMAIL_WHITELIST_CHECK', $email_whitelist_check, 'bool');
		update_db_config('MELLIVORA_CONFIG_EMAIL_REGEX_CHECK', $email_regex_check, 'bool');
		update_db_config('MELLIVORA_CONFIG_ACCOUNTS_DEFAULT_ENABLED', $accounts_enabled_by_default, 'bool');
		update_db_config('MELLIVORA_CONFIG_MIN_USERNAME_LENGTH', $min_length, 'int');
		update_db_config('MELLIVORA_CONFIG_MAX_USERNAME_LENGTH',$max_length,'int');
		update_db_config('MELLIVORA_CONFIG_ACCOUNTS_EMAIL_PASSWORD_ON_SIGNUP', $email_password_on_signup, 'bool');
		redirect('/admin/edit_ctf.php?generic_success=1');

	} else if ($_POST['action'] ==='challenge_settings') {
		is_numeric($_POST['challenge_initial_points']) ? $initial_points = $_POST['challenge_initial_points'] : $initial_points = get_db_config('MELLIVORA_CONFIG_CHALL_INITIAL_POINTS');
		is_numeric($_POST['challenge_minimum_points']) ? $min_points = $_POST['challenge_minimum_points'] : $max_length = get_db_config('MELLIVORA_CONFIG_CHALL_MINIMUM_POINTS');
		is_numeric($_POST['dynamic_scoring_lower_bound']) ? $lb = $_POST['dynamic_scoring_lower_bound'] : $lb = get_db_config("MELLIVORA_CONFIG_CHALL_LOWER_BOUND");
		is_numeric($_POST['dynamic_scoring_upper_bound']) ? $ub = $_POST['dynamic_scoring_upper_bound'] : $ub = get_db_config("MELLIVORA_CONFIG_CHALL_UPPER_BOUND");
		
		update_db_config('MELLIVORA_CONFIG_CHALL_INITIAL_POINTS', $initial_points, 'int');
		update_db_config('MELLIVORA_CONFIG_CHALL_MINIMUM_POINTS', $min_points, 'int');
		update_db_config('MELLIVORA_CONFIG_CHALL_LOWER_BOUND', $lb, 'float');
		update_db_config('MELLIVORA_CONFIG_CHALL_UPPER_BOUND', $ub, 'float');
		redirect('/admin/edit_ctf.php?generic_success=1');
	}
}