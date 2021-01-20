<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}


class BmaAdminClass extends BincomMailAutomation{
    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        add_action('admin_enqueue_scripts',[$this,'add_style_sheet']);
        add_filter( 'set-screen-option', [ $this, 'set_screen' ], 10, 3 );
        add_action('admin_menu',[$this,'bincommail_automation_admin_menu']);
       
    }
   
    public function add_style_sheet(){
        wp_enqueue_style('form', BMA()->get_vars('PATH').'styles/form.css', []);
    }

    public function bincommail_automation_admin_menu() {
        $hook = add_menu_page('bincom_mail_automation','Bincom Mail Automation','manage_options', 'bincom_mail_automation_menu',[$this, 'add_ba_admin_menu'],"dashicons-buddicons-pm",28 );
        
        add_action( "load-$hook", [ $this, 'mail_screen_option' ] );
       
        add_submenu_page('bincom_mail_automation_menu','Mails','All Mails','manage_options','bincom_mail_automation_menu',[$this, 'add_ba_admin_menu']);
        
        add_submenu_page('bincom_mail_automation_menu','add_mail','Add Mail','manage_options','bma_add_mail',[$this,'add_mail_sub_menu']);

         
        // add_submenu_page('bincom_mail_automation_menu','test','test','manage_options','test',[$this,'test']);

        $hook2 = add_submenu_page('bincom_mail_automation_menu','inbound_messages','Inbound messages','manage_options','bma_inbound_messages',[$this,'inbound_messages_sub_menu']);
        
        add_action( "load-$hook2", [ $this, 'screen_option' ] );
     }
    public function screen_option(){
        $option = 'per_page';
        $args   = [
            'label'   => 'classes',
            'default' => 10,
            'option'  => 'classes_per_page'
        ];
	    add_screen_option( $option, $args );
    }
    public function mail_screen_option(){
        $option = 'per_page';
        $args   = [
            'label'   => 'mails',
            'default' => 10,
            'option'  => 'bma_mail_per_page'
        ];
	    add_screen_option( $option, $args );
    }
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }

    public  function add_ba_admin_menu(){
      BMA()->load_files(BMA()->get_vars('PATH').'templates/admin-page.php');
    }

    public function add_mail_sub_menu(){
        BMA()->load_files(BMA()->get_vars('PATH').'templates/add-template.php');
    }
    
    public function add_mail_settings_sub_menu()
    {
        BMA()->load_files(BMA()->get_vars('PATH').'templates/settings.php');
    }

    public function inbound_messages_sub_menu()
    {
        BMA()->load_files(BMA()->get_vars('PATH').'templates/inbound-message-page.php');
    }
    public function test(){
        BMA()::$functions->mail_cron();
    }
}