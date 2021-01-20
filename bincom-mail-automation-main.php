<?php 
/**
 * 
 * @package Automation Plugin 
 * Plugin Name:       Bincom Mail Automation Plugins
 * Plugin URI:        https://bincom.net
 * Description:       Plugin for the blog.bincom.net to send mail  automatical to does that files a form 
 * Version:           1.0.0
 * Author:            Onifade Boluwatife Basit 
 * Author URI:        https://bincom.net
 * License: GPLv2 or later
 * Text Domain: sample
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class BincomMailAutomation{
    
    public static $functions;
    protected static $_instance = null;
    public static $admin = null;
    protected $plugin_vars = [];
    public function __construct()
    {
         $this->defineConstant();
        $this->set_vars();
        $this->load_required_files();
        $this->init_class();
    }
    public function do_admin_init(){
        BincomAutomatedMails::register_post_type();
        BincomAutomatedMailsTemplates::register_post_type();
    }
    public function getFunctionInstance()
    {
        return self::$functions;
    }
    private  function setAdminInstance()
    {
        if(self::$admin == null){
            self::$admin = new BmaAdminClass();
        }
        return self::$admin;
    }
    private function setFunctionInstance(){
        if(self::$functions == null){
            self::$functions = new BmaFunctions();
        }
        return  self::$functions;
    }
    private function init_class(){
        if($this->is_request('admin')){
            $this->setAdminInstance();
        }
        if($this->is_request( 'admin') || $this->is_request('cron')){
            $this->setFunctionInstance();
        
        }
    }

    public function defineConstant(){
        global $wpdb;
        $this->define('BMATABLE', $wpdb->prefix."bincom_automated_mail_details" );
        $this->define('BMASETTINGS', get_option('bma_settings'));
    }
    public function define($key, $value){
        if(!defined($key)){
            define($key, $value);
        }
    }
    public function load_files($path,$type = 'require'){
		foreach( glob( $path ) as $files ){
			if($type == 'require'){
				require_once( $files );
			} else if($type == 'include'){
				include_once( $files );
			}
		} 
    }
    private function set_vars(){
		$this->add_vars('URL',plugins_url('', __FILE__ )); 
		$this->add_vars('FILE',plugin_basename( __FILE__ ));
		$this->add_vars('PATH',plugin_dir_path( __FILE__ )); # Plugin DIR
		$this->add_vars('LANGPATH',$this->get_vars('PATH').'languages');
    }
    private function add_vars($key, $val){
		if(!isset($this->plugin_vars[$key])){
			$this->plugin_vars[$key] = $val;
		}
	}
    public function get_vars($key){
		if(isset($this->plugin_vars[$key])){
			return $this->plugin_vars[$key];
		}
		return false;
	}	
    private function load_required_files()
    {
        $this->load_files($this->get_vars('PATH').'includes/classes/Bincom*.php');
        if($this->is_request( 'admin') || $this->is_request('cron')){
            $this->load_files($this->get_vars('PATH').'includes/classes/bma-*.php');
        }

        if($this->is_request('admin')){
            $this->load_files($this->get_vars('PATH').'includes/admin/admin-class-init.php');            
        }

    }
    public static function getInstance()
    {
        if( null == self::$_instance ){
			self::$_instance = new self;
		}
		return self::$_instance;
    }
    public function getfiles(){


    }
    public function on_activation()
    {
        $option = get_option('bma_settings');
        if(!$option){
            add_option('bma_settings',  ['input_check' => 'default' , 'mail_sender' => 'proservices@bincom.net', 'mail_subject' => 'Bincom Academy ([mail-subject])']);
        }
    }


    private function is_request( $type ){
		switch( $type ){
			case 'admin' :
			return is_admin();
			case 'ajax' :
			return defined( 'DOING_AJAX' );
			case 'cron' :
			return defined( 'DOING_CRON' );
			case 'frontend' :
			return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}
}

if(!function_exists('BMA'))
{
    function BMA(){
        return BincomMailAutomation::getInstance(); //instantiate plugin class
    }    
    BMA(); //return an instance of the plugin class
}

add_action('init',[BMA(), 'do_admin_init']);
register_activation_hook(__FILE__, [BMA(),'on_activation']);
register_deactivation_hook( __FILE__, [BMA()::$functions, 'bma_schedule_deactivate'] );

