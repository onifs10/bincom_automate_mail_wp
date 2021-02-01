<?php


class BincomMail
{

    /**
     * @var mixed
     */
    private $from;
    /**
     * @var mixed
     */
    private $to;
    /**
     * @var mixed
     */
    private $subject;
    /**
     * @var mixed
     */
    private $body;
    /**
     * @var mixed
     */
    private $html;

    /**
     * @var mixed
     */
    public $details;
    /**
     * @var mixed
     */
    public $additional_headers;

    public function __construct($args = '')
    {
            $details = wp_parse_args($args,[
                    'html' => false,
                    'to' => '',
                    'from' => '',
                    'subject' => '',
                    'body' => '',
                    'additional_headers'=> '',
            ]);
            $this->from = $details['from'];
            $this->to = $details['to'];
            $this->subject = $details['subject'];
            $this->body = $details['body'];
            $this->html = $details['html'];
            $this->additional_headers = $details['additional_headers'];
    }

    private function htmlize( $body ) {
        $header =
            '<!doctype html>
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <title>' . esc_html( $this->subject ) . '</title>
                </head>
                <body>
                    ';
        $footer =
            '</body>
        </html>';
        return $header . wpautop( $body ) . $footer;
    }

    public function get_body(){
        if ( $this->html
            and ! preg_match( '%<html[>\s].*</html>%is', $this->body
            ) ) {
            return  $this->htmlize( $this->body );
        }
        return  $this->body;
    }

    public function send(){
        $sender = $this->from;
        $to = $this->to;
        $body = $this->get_body();
        $subject = $this->subject;
        $headers = "From: $sender\n";

        if ( $this->html ) {
            $headers .= "Content-Type: text/html\n";
            $headers .= "X-WPCF7-Content-Type: text/html\n";
        } else {
            $headers .= "X-WPCF7-Content-Type: text/plain\n";
        }
        if($this->additional_headers){
            $headers .= $this->additional_headers;
        }
        $this->details = ['subject' => $subject, 'body' => $body];
        return wp_mail( $to, $subject, $body, $headers);
    }
}