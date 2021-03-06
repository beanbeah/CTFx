<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

	if ($_POST['action'] === 'new') {
		//convert emails to array (comma delimited). Iterate thru each email/add it
		$email_raw = $_POST['email'];
		$emails = explode(',', $email_raw);
		$enabled_post = $_POST['enabled'];

		foreach ($emails as $email_arr) {
			//sanitise email
			if (valid_email($email_arr)) {
				$id = db_insert(
					'email_list',
					array(
						'email' => $email_arr,
						'enabled' => $enabled_post
					)
				);
			} else {
				log_exception(new Exception('Invalid Email'), false, "Invalid Email used in Whitelist: " . $email_arr);
			}
		}

		if ($id) {
			redirect('/admin/list_email_whitelist.php?generic_success=1');
		} else {
			message_error('Could not insert new email restriction.');
		}
	}
}