<?php
if(array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'edit_page' && array_key_exists('mail', $_REQUEST)){
    // $class
    BMA()->load_files(BMA()->get_vars('PATH').'templates/edit-mail.php');
}else{
    BMA()->load_files(BMA()->get_vars('PATH').'templates/mails-table.php');
}