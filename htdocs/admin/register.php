<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();
section_title('Registration');
message_inline('Usernames and Emails must be comma delimited.');
form_start('/admin/actions/register');
form_input_text('Email');
form_input_text('Username');
$opts = db_query_fetch_all('SELECT * FROM countries ORDER BY country_name ASC');
form_select($opts, 'Country', 'id', $user['country_id'], 'country_name');
form_hidden('action', 'new');
form_button_submit('Add Users');
form_end();
foot();
