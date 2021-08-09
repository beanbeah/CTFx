<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

    if ($_POST['action'] == 'new') {

       $id = db_insert(
          'email_list',
          array(
             'email'=>$_POST['email'],
             'white'=>$_POST['whitelist'],
             'enabled'=>$_POST['enabled']
          )
       );

       if ($id) {
          redirect('/admin/list_email_whitelist.php?generic_success=1');
       } else {
          message_error('Could not insert new email restriction.');
       }
    }
}