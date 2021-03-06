<?php

require('../../include/mellivora.inc.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// get auth data
	if (isset($_POST['auth_key']) && is_valid_id($_POST['id'])) {

		$auth = db_select_one(
			'reset_password',
			array(
				'id',
				'user_id',
				'auth_key'
			),
			array(
				'auth_key' => $_POST['auth_key'],
				'user_id' => $_POST['id']
			)
		);

		if (!$auth['user_id']) {
			message_error('No reset data found');
		}
	}

	// stage 1, part 2
	if ($_POST['action'] === 'reset_password') {

		if (Config::get('MELLIVORA_CONFIG_RECAPTCHA_ENABLE_PUBLIC')) {
			validate_captcha();
		}

		$user = db_select_one(
			'users',
			array(
				'id',
				'username',
				'email'
			),
			array(
				'email' => $_POST[md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'EMAIL')]
			)
		);

		if ($user['id']) {

			$auth_key = hash('sha256', generate_random_string(128));

			db_insert(
				'reset_password',
				array(
					'added' => time(),
					'user_id' => $user['id'],
					'ip' => get_ip(true),
					'auth_key' => $auth_key
				)
			);

			$email_subject = 'Password recovery for User ' . htmlspecialchars($user['username']);
			// body
			$email_body = htmlspecialchars($user['username']) . ', please follow the link below to reset your password:' .
				"\r\n" .
				"\r\n" .
				Config::get('MELLIVORA_CONFIG_SITE_URL') . 'reset_password?action=choose_password&auth_key=' . $auth_key . '&id=' . $user['id'] .
				"\r\n" .
				"\r\n" .
				'Regards,' .
				"\r\n" .
				Config::get('MELLIVORA_CONFIG_SITE_NAME');

			// send details to user
			send_email(array($user['email']), $email_subject, $email_body);
		}

		message_generic('Success', 'If the email you provided belongs to a valid user, an email has now been sent to it with further instructions!');
	} // stage 2, part 2
	else if ($_POST['action'] === 'choose_password' && is_valid_id($auth['user_id'])) {

		$new_password = $_POST[md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'PWD')];
		$new_password_confirmation = $_POST[md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'PWD_CONFIRM')];
		password_validation($new_password, $new_password_confirmation);
		$new_passhash = make_passhash($new_password);

		db_update(
			'users',
			array(
				'passhash' => $new_passhash
			),
			array(
				'id' => $auth['user_id']
			)
		);

		db_delete(
			'reset_password',
			array(
				'user_id' => $auth['user_id']
			)
		);

		message_generic('Success', 'Your password has been reset.');
	}
}
