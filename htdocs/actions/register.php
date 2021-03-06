<?php

require('../../include/mellivora.inc.php');

$redirect_url = array_get($_POST, 'redirect');

if (user_is_logged_in()) {
	redirect($redirect_url);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['action'] === 'register') {

		if (Config::get('MELLIVORA_CONFIG_RECAPTCHA_ENABLE_PUBLIC')) {
			validate_captcha();
		}

		$email = $_POST[md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'USR')];

		if (get_db_config('MELLIVORA_CONFIG_ACCOUNTS_EMAIL_PASSWORD_ON_SIGNUP')) {
			$password = generate_random_string(12);
		} else {
			$password = $_POST[md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'PWD')];
			$password_confirmation = $_POST[md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'PWD_CONFIRM')];
			password_validation($password, $password_confirmation);
		}

		if (register_account(
			$email,
			$password,
			$_POST['username'],
			$_POST['country'],
			array_get($_POST, 'type')
		)) {
			message_generic('Success', 'Check your email for more details');

		} else {
			message_error('Sign up failed. Please contact admins for help.');
		}
	}
}