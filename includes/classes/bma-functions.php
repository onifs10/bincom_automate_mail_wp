<?php

if (!defined('WPINC')) {
	die;
}
class BmaFunctions extends BincomMailAutomation{

    public function __construct()
    {  
        // call when WP loads
        add_action( 'wp_loaded', [$this,'bma_schedule_activation'], 100, 0 );
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
                // var_dump($message);
                // die();
                $this->send_mail($message);
            }
    }

    public  function send_mail($message){
        if($code = $message->input_check){
            $class =  BincomClasses::findByCode($code);
            if($class){
                $mail_body = $this->process_mail($class,$message);
                $mail_to = $message->from_email;
                $sender = BMASETTINGS['mail_sender'];
                $subject = str_replace('[class-name]',$class->name,BMASETTINGS['mail_subject']);
                $log = [
                    'subject' => $subject,
                    'body' => $mail_body
                ];
                $sent = $this->send($mail_to , $sender, $subject, $mail_body);
                if($sent){
                    BMA_Inbound_Message::mailed($message->id());
                }else{
                    BMA_Inbound_Message::failed($message->id());
                }          
                update_post_meta($message->id(),BMA_Inbound_Message::mail_log_meta,$log); 
            }   
        }else{
            BMA_Inbound_Message::failed($message->id());
        }

    }  
    
    private function process_mail($class , $inbound_message){
        $mail = $class->mail_template;  
        $test = str_replace(['[class-name]','[class-link]','[class-starts]','[class-days]','[class-duration]','[recipient-name]','[class-time]'],[''.$class->name, ''.$class->link, ''.$class->starts, ''.$class->days,''. $class->duration, ''.$inbound_message->from_name, ''.$class->time], $mail);
        return $test;
    }

    private function send($mail_to , $sender, $subject, $mail_body, $additional_headers = null){
        $headers = "From: $sender\n";
        $headers .= "X-WPCF7-Content-Type: text/plain\n";
        if ( $additional_headers ) {
			$headers .= $additional_headers . "\n";
        }
        return wp_mail( $mail_to, $subject, $mail_body, $headers);
    }
}