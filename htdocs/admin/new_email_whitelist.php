<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();

section_title('New email whitelist/blacklist');

message_inline('Add email or emails. Comma delimited.');

form_start('/admin/actions/new_email_whitelist');
form_input_text('Email');
form_input_checkbox('Enabled');
form_hidden('action', 'new');
form_button_submit('Add emails');
form_end();
foot();
