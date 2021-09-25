<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();

section_title ('Edit CTF Settings');

section_subhead ("Challenge Start / End Times:");
form_start('/admin/actions/edit_ctf');
form_input_text('CTF Start Time', date_time (Config::get('MELLIVORA_CONFIG_CTF_START_TIME'),Config::get('MELLIVORA_CONFIG_CTF_TIMEZONE')));
form_input_text('CTF End Time', date_time (Config::get('MELLIVORA_CONFIG_CTF_END_TIME'),Config::get('MELLIVORA_CONFIG_CTF_TIMEZONE')));
form_input_checkbox('Write To Config');
message_inline('This WILL write to the config.inc.php file');
form_hidden('action', 'change_times');
form_button_submit('Update');
form_end();

section_subhead('Scoreboard Freeze');
form_start('/admin/actions/edit_ctf');
form_input_checkbox('Freeze',!Config::get('MELLIVORA_CONFIG_SHOW_SCOREBOARD'));
message_inline('This WILL write to the config.inc.php file');
form_hidden('action','scoreboard_freeze');
form_button_submit('Update');
form_end();

foot();
