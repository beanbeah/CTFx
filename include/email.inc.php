<?php

function send_email(
	array $receivers,
		  $subject,
		  $body,
	array $cc_list = null,
	array $bcc_list = null,
		  $from_email = null,
		  $from_name = null,
		  $replyto_email = null,
		  $replyto_name = null,
		  $is_html = false)
{

	if (!$from_email) {
		$from_email = Config::get('MELLIVORA_CONFIG_EMAIL_FROM_EMAIL');
	}
	if (!$from_name) {
		$from_name = Config::get('MELLIVORA_CONFIG_EMAIL_FROM_NAME');
	}
	if (!$replyto_email) {
		$replyto_email = Config::get('MELLIVORA_CONFIG_EMAIL_REPLYTO_EMAIL');
	}
	if (!$replyto_name) {
		$replyto_name = Config::get('MELLIVORA_CONFIG_EMAIL_REPLYTO_NAME');
	}

	$mail = new PHPMailer\PHPMailer\PHPMailer();
	$mail->IsHTML($is_html);
	$mail->XMailer = ' ';
	$mail->CharSet = 'UTF-8';

	$successfully_sent_to = array();

	try {

		if (Config::get('MELLIVORA_CONFIG_EMAIL_USE_SMTP')) {
			$mail->IsSMTP();

			$mail->SMTPDebug = Config::get('MELLIVORA_CONFIG_EMAIL_SMTP_DEBUG_LEVEL');

			$mail->Host = Config::get('MELLIVORA_CONFIG_EMAIL_SMTP_HOST');
			$mail->Port = Config::get('MELLIVORA_CONFIG_EMAIL_SMTP_PORT');
			$mail->SMTPSecure = Config::get('MELLIVORA_CONFIG_EMAIL_SMTP_SECURITY');

			$mail->SMTPAuth = Config::get('MELLIVORA_CONFIG_EMAIL_SMTP_AUTH');
			$mail->Username = Config::get('MELLIVORA_CONFIG_EMAIL_SMTP_USER');
			$mail->Password = Config::get('MELLIVORA_CONFIG_EMAIL_SMTP_PASSWORD');
		}

		$mail->SetFrom($from_email, $from_name);
		if ($replyto_email) {
			$mail->AddReplyTo($replyto_email, $replyto_name);
		}

		// add the "To" receivers
		foreach ($receivers as $receiver) {
			if (!valid_email($receiver)) {
				continue;
			}
			$mail->AddAddress($receiver);
			$successfully_sent_to[] = $receiver;
		}

		if (empty($successfully_sent_to)) {
			message_error('There must be at least one valid "To" receiver of this email');
		}

		// add the "CC" receivers
		if (!empty($cc_list)) {
			foreach ($cc_list as $cc) {
				if (!valid_email($cc)) {
					continue;
				}
				$mail->AddCC($cc);
				$successfully_sent_to[] = $cc;
			}
		}

		// add the "BCC" receivers
		if (!empty($bcc_list)) {
			foreach ($bcc_list as $bcc) {
				if (!valid_email($bcc)) {
					continue;
				}
				$mail->AddBCC($bcc);
				$successfully_sent_to[] = $bcc;
			}
		}

		$mail->Subject = $subject;

		// HTML email
		if ($is_html) {
			// we assume the email has come to us in markdown format
			$mail->MsgHTML(parse_markdown($body));
		} // plain old simple email
		else {
			$mail->Body = $body;
		}

		if (!$mail->Send()) {
			throw new Exception('Could not send email: ' . $mail->ErrorInfo);
		}

	} catch (Exception $e) {
		log_exception($e, false, "Please set up an e-mail address for CTFx to use");
	}

	return $successfully_sent_to;
}

function email_whitelist_search($email)
{

	$emails = db_select_all(
		'email_list',
		array('email'),
		array('enabled' => 1)
	);

	foreach ($emails as $whitelist) {
		//if email whitelisted, allow. else dont allow. 
		if ($email === $whitelist['email'])  return true;
	}

	return false;
}

function email_regex_search($email)
{
	$rules = db_select_all(
		'restrict_email',
		array(
			'rule',
		),
		array(
			'enabled' => 1
		),
		'priority ASC'
	);

	foreach ($rules as $rule) {
		if (preg_match('/' . $rule['rule'] . '/', $email)) return true;
	}
	
	return false;
}

function allowed_email($email)
{

	$allowedEmail = true;

	if (get_db_config('MELLIVORA_CONFIG_EMAIL_WHITELIST_CHECK') && get_db_config('MELLIVORA_CONFIG_EMAIL_REGEX_CHECK')) {
		$allowedEmail = email_regex_search($email) || email_whitelist_search($email);
	} else if (get_db_config('MELLIVORA_CONFIG_EMAIL_WHITELIST_CHECK')) {
		$allowedEmail = email_whitelist_search($email);
	} else if (get_db_config('MELLIVORA_CONFIG_EMAIL_REGEX_CHECK')) {
		$allowedEmail = email_regex_search($email);
	}
	return $allowedEmail;
}

function valid_email($email)
{
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_email($email)
{
	if (!valid_email($email)) {
		// Don't show stack trace here in order to hide any plaintext passwords showing up in the logs
		log_exception(new Exception('Invalid Email'), false, "User tried to register with invalid e-mail: " . $email);

		message_error(lang_get('not_a_valid_email'));
	}
}

function csv_email_list_to_array($list)
{
	return array_map('trim', str_getcsv($list));
}
