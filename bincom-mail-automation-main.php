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
    protected function load_files($path,$type = 'require'){
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
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        $tablename = BMATABLE;
        $main_sql_create = " 
        CREATE TABLE " . $tablename."(
            `ID` bigint(20)  NOT NULL AUTO_INCREMENT,
            `class_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `class_starts` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `class_days` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `class_time` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `class_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `mail_template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `class_duration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY  (ID)
        )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 
        ";
        if(!$this->checkTable($tablename,$wpdb)){
            dbDelta($main_sql_create);
        }
        $sql = "ALTER TABLE `{$wpdb->prefix}posts`  ADD `contacted` VARCHAR(255) NULL DEFAULT 'pending'  AFTER `comment_count`;";
        maybe_add_column($wpdb->prefix.'posts','contacted',$sql);
        
        $option = get_option('bma_settings');
        if(!$option){
            add_option('bma_settings',  ['input_check' => 'default' , 'mail_sender' => 'proservices@bincom.net', 'mail_subject' => 'Bincom Academy ([class_name])']);    
        }
    }

    private function checkTable($table_name, $wpdb){
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
            return true;
        } else {
           return false;
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

register_activation_hook(__FILE__, [BMA(),'on_activation']);
register_deactivation_hook( __FILE__, [BMA()::$functions, 'bma_schedule_deactivate'] );
// register_uninstall_hook(__FILE__, [BMA(),'']) 
// function example_function(){
//     $string = 'this is an information of a very basic plugin';
//     return $string; 
// }
// add_shortcode('automation_example','example_function'); // add code that can be used on pages and post 