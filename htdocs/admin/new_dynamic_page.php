<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();

section_title('New dynamic page');
form_start('/admin/actions/new_dynamic_page');
form_input_text('Title');
form_textarea('Body');

dynamic_visibility_select();

user_class_select();

form_hidden('action', 'new');

form_button_submit('Create');
form_end();

foot();