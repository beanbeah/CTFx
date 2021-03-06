<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();

section_title('Test signup rules');

message_inline('Enter an email addess to test.');

form_start('/admin/actions/test_restrict_email');
form_input_text('Email');
form_hidden('action', 'test');
form_button_submit('Test');
form_end();

foot();