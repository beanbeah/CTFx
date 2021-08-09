<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_id($_POST['id']);
    validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

    if ($_POST['action'] == 'edit') {

       db_update(
          'email_list',
          array(
             'email'=>$_POST['email'],
             'enabled'=>$_POST['enabled'],
             'white'=>$_POST['whitelist']
          ),
          array(
             'id'=>$_POST['id']
          )
       );

        redirect('/admin/list_email_whitelist.php?generic_success=1');
    }

    else if ($_POST['action'] == 'delete') {

        if (!$_POST['delete_confirmation']) {
            message_error('Please confirm delete');
        }

        db_delete(
            'email_list',
            array(
                'id'=>$_POST['id']
            )
        );

        redirect('/admin/list_email_whitelist.php?generic_success=1');
    }
}