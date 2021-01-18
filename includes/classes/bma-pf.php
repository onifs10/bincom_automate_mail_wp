<?php

if (!defined('WPINC')) {
	die;
}
class BmaFunctions extends BincomMailAutomation{

    public function __construct()
    {  
        // call when WP loads
        add_action( 'wp', [$this,'bma_schedule_activation'], 10, 0 );
        // hook bma_schedule_function to schedule event created by the bma_scheule_activation 
        add_action( 'bma_daily_cron_job', [$this, 'bma_schedule_function'], 10, 0 );
       
    }
    public function bma_schedule_activation(){
        if ( ! wp_next_scheduled( 'bma_daily_cron_job' ) ) {
            wp_schedule_event( time(), 'daily', 'bma_daily_cron_job' );
        }
    }
    public function bma_schedule_deactivate() {

        // when the last event was scheduled
        $timestamp = wp_next_scheduled( 'bma_daily_cron_job' );
    
        // unschedule previous event if any
        wp_unschedule_event( $timestamp, 'bma_daily_cron_job' );
    }
    public function bma_schedule_function(){
        $this->mail_cron();
    }
    public function mail_cron(){

        BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/inbound_message.php');

        BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/ClassesModel.php');
            $inbound = BMA_Inbound_Message::findAllPending();
            foreach($inbound as $message){
                $this->send_mail($message);
            }
    }

    private function send_mail($message){
        if($code =  $message->input_check){
            $class =  BincomClasses::findByCode($code);
            die(var_dump($class));
        }else{
            die('not a  class');
        }

    }   
}