<?php
if(array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'edit_page' && array_key_exists('class', $_REQUEST)){
    // $class
    BMA()->load_files(BMA()->get_vars('PATH').'templates/edit-class.php');
}else{
    BMA()->load_files(BMA()->get_vars('PATH').'templates/classes-table.php');
    BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/inbound_contact.php');

    // $contacts = BMA_Inbound_Contact::find(null,true);
    // var_dump($contacts);
}