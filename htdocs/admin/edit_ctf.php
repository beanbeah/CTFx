<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();

section_title('Edit CTF Settings');

section_subhead("Challenge Start / End Times:");
form_start('/admin/actions/edit_ctf');
form_input_text('CTF Start Time', date_time(get_db_config('MELLIVORA_CONFIG_CTF_START_TIME'), get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')));
form_input_text('CTF End Time', date_time(get_db_config('MELLIVORA_CONFIG_CTF_END_TIME'), get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')));
form_input_text('CTF Timezone', get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE'));
form_hidden('action', 'change_times');
form_button_submit('Update');
form_end();

section_subhead('Scoreboard Freeze');
form_start('/admin/actions/edit_ctf');
form_input_checkbox('Freeze', !get_db_config('MELLIVORA_CONFIG_SHOW_SCOREBOARD'));
form_hidden('action', 'scoreboard_freeze');
form_button_submit('Update');
form_end();

section_subhead('Signup');
form_start('/admin/actions/edit_ctf');
form_input_checkbox('Signup Allowed', get_db_config('MELLIVORA_CONFIG_ACCOUNTS_SIGNUP_ALLOWED'));
form_input_checkbox('Email Whitelist Check', get_db_config('MELLIVORA_CONFIG_EMAIL_WHITELIST_CHECK'));
form_input_checkbox('Email Regex Check', get_db_config('MELLIVORA_CONFIG_EMAIL_REGEX_CHECK'));
form_input_checkbox('Accounts Enabled by Default', get_db_config('MELLIVORA_CONFIG_ACCOUNTS_DEFAULT_ENABLED'));
form_input_text('Team Name Min Length', get_db_config('MELLIVORA_CONFIG_MIN_TEAM_NAME_LENGTH'));
form_input_text('Team Name Max Length', get_db_config('MELLIVORA_CONFIG_MAX_TEAM_NAME_LENGTH'));
form_input_checkbox('Email Password on Signup', get_db_config('MELLIVORA_CONFIG_ACCOUNTS_EMAIL_PASSWORD_ON_SIGNUP'));
form_hidden('action', 'signup_settings');
form_button_submit('Update');
form_end();

section_subhead('Challenge Default Settings');
form_start('/admin/actions/edit_ctf');
form_input_text('Challenge Initial Points', get_db_config('MELLIVORA_CONFIG_CHALL_INITIAL_POINTS'));
form_input_text('Challenge Minimum Points', get_db_config('MELLIVORA_CONFIG_CHALL_MINIMUM_POINTS'));
form_input_text('Dynamic Scoring Lower Bound', get_db_config('MELLIVORA_CONFIG_CHALL_LOWER_BOUND'));
form_input_text('Dynamic Scoring Upper Bound', get_db_config('MELLIVORA_CONFIG_CHALL_UPPER_BOUND'));
form_hidden('action', 'challenge_settings');
form_button_submit('Update');
form_end();

foot();