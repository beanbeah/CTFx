<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

validate_id($_GET['id']);

$email = db_select_one(
    'email_list',
    array(
        'email',
        'enabled',
        'white',
    ),
    array('id' => $_GET['id'])
);

head('Site management');
menu_management();

section_title('Edit Email');
form_start('/admin/actions/edit_email_whitelist');
form_input_text('Email', $email['email']);
form_input_checkbox('Whitelist', $email['white']);
form_input_checkbox('Enabled', $email['enabled']);
form_hidden('action', 'edit');
form_hidden('id', $_GET['id']);
form_button_submit('Save changes');
form_end();

section_subhead('Delete email');
form_start('/admin/actions/edit_email_whitelist');
form_input_checkbox('Delete confirmation', false, 'red');

form_hidden('action', 'delete');
form_hidden('id', $_GET['id']);
form_button_submit('Delete email', 'danger');
form_end();

foot();
