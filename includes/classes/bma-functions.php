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
            $inbound = BMA_Inbound_Message::getAllPending();
            foreach($inbound as $message){
                $this->send_mail_v2($message);
            }
    }

    public  function send_mail($message){
        if($code = $message->input_check){
            $class =  BincomAutomatedClasses::findByCode($code);
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
    public  function send_mail_v2($message){
            $channel_slug = $message->channel->slug;
            $mails = BincomAutomatedMails::findByFormSLug($channel_slug);
            if(empty($mails)){
                BMA_Inbound_Message::no_template($message->id());
                return;
            }
            foreach ($mails as $mail) {
                $input_checked = null;
                if ($mail->content == 'multiple') {
                    if ($mail->input_to_check) {
                        if (array_key_exists($mail->input_to_check, $message->fields)) {
                            $input_checked = $message->fields[$mail->input_to_check];
                            if(is_array($input_checked)){
                                $input_checked = $input_checked[0];
                            }
                        } else {
                            BMA_Inbound_Message::no_template($message->id());
                            return;
                        }
                    }

                }
                $template = BincomAutomatedMailsTemplates::getTemplateByParentOrInputRequired($mail->id(), $input_checked);
                if (!$template->id()) {
                    BMA_Inbound_Message::no_template($message->id());
                    continue;
                }
                $sender = BMASETTINGS['mail_sender'];
                $mail_to = $message->from_email;
                $fields = $message->fields;
                $replace = [];
                $with = [];
                foreach($fields as $key => $value){
                    $replace[] = '['.$key.']';
                    if(is_array($value)){
                        $with[] = $value[0];
                    }
                }
                $replace[] = '[recipient-name]';
                $additional_header = $mail->additional_header;
                $with[] = $message->from_name; 
                $mail_body = str_replace($replace,$with, $template->content);
                $subject = str_replace($replace, $with,$template->subject);
                $use_html = false;
                if ($template->status == 'html') {
                    $use_html = true;
                }
                $args = [
                    'html' => $use_html,
                    'subject' => $subject,
                    'body' => $mail_body,
                    'to' => $mail_to,
                    'from' => $sender,
                    'additional_headers' => $additional_header,
                ];
                $mail = new BincomMail($args);
                $sent = $mail->send();
                if ($sent) {
                    BMA_Inbound_Message::mailed($message->id());
                } else {
                    BMA_Inbound_Message::failed($message->id());
                }
                $log = $mail->details;
                update_post_meta($message->id(), BMA_Inbound_Message::mail_log_meta, $log);
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