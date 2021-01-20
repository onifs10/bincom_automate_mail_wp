<?php
if(array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'edit_page' && array_key_exists('template', $_REQUEST)){
    // $class
    BMA()->load_files(BMA()->get_vars('PATH').'templates/edit-template.php');
}else{
    BMA()->load_files(BMA()->get_vars('PATH').'templates/template-table.php');
}