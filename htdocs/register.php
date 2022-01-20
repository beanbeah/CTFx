<?php

require('../include/mellivora.inc.php');

if (user_is_logged_in()) {
	redirect(Config::get('MELLIVORA_CONFIG_REGISTER_REDIRECT_TO'));
	exit();
}

head('Register');

if (get_db_config('MELLIVORA_CONFIG_ACCOUNTS_SIGNUP_ALLOWED')) {
	section_title('Registration');

	echo '<p>',
	lang_get(
		'account_signup_information',
		array(
			'password_information' => (get_db_config('MELLIVORA_CONFIG_ACCOUNTS_EMAIL_PASSWORD_ON_SIGNUP') ? lang_get('email_password_on_signup') : lang_get('password_requirement'))
		)
	), '</p>
    <form method="post" id="registerForm" class="form-signin" action="actions/register">
        <input name="team_name" type="text" class="form-control form-group" placeholder="Team name" minlength="', get_db_config('MELLIVORA_CONFIG_MIN_TEAM_NAME_LENGTH'), '" maxlength="', get_db_config('MELLIVORA_CONFIG_MAX_TEAM_NAME_LENGTH'), '" required />
        <input name="', md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'USR'), '" type="email" class="form-control form-group" placeholder="Email address" id="register-email-input" required />
        ', (!get_db_config('MELLIVORA_CONFIG_ACCOUNTS_EMAIL_PASSWORD_ON_SIGNUP') ? '<input name="' . md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'PWD') . '" type="password" class="form-control form-group" placeholder="Password" id="register-password-input" required /><input name="' . md5(Config::get('MELLIVORA_CONFIG_SITE_NAME') . 'PWD_CONFIRM') . '" type="password" class="form-control form-group" placeholder="Password again" id="register-password-input" required />' : '');

	if (cache_start(CONST_CACHE_NAME_REGISTER, Config::get('MELLIVORA_CONFIG_CACHE_TIME_REGISTER'))) {
		country_select();
		cache_end(CONST_CACHE_NAME_REGISTER);
	}

	if (Config::get('MELLIVORA_CONFIG_RECAPTCHA_ENABLE_PUBLIC')) {
		display_captcha();
	}

	echo '
    <input type="hidden" name="action" value="register" />

    <button class="btn btn-1 btn-lg" type="submit" id="register-team-button">Register</button>
</form>
';

} else {
	message_inline('Registration is closed', "red");
}

foot();
