<?php

class BMA_Inbound_Message{
    const post_type = 'flamingo_inbound';
    const mail_sent_status = 'bma_contacted';
	const channel_taxonomy = 'flamingo_inbound_channel';
	const mail_log_meta = '_bma_mail_sent_log';
 
	private static $found_items = 0 ;

    private $id;
	public $channel;
	public $submission_status;
	public $subject;
	public $from;
	public $from_name;
	public $from_email;
	public $fields;
	public $meta;
	public $akismet;
	public $recaptcha;
	public $mail_sent_log;
    public $consent;
	public $input_check;
	public $details;
	private $timestamp = null;
    private $hash = null;
    public  $status = 'not at all';
    
    public static function register_post_type() {
		// register_post_status( self::mail_sent_status, array(
		// 	'label' => __( '_bma_mail_sent', 'bma' ),
		// 	'public' => false,
		// 	'exclude_from_search' => true,
		// 	'show_in_admin_all_list' => false,
		// 	'show_in_admin_status_list' => true,
		// ) );
	}

	public static function findAllPending(){
		$arr = array(
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => '',
			'meta_value' => '',
			'post_status' => 'any',
			'contacted' => 'pending',
			'tax_query' => array(),
			'channel' => '',
			'channel_id' => 0,
			'hash' => '',
		);

		return self::find($arr);
	}
	public static function find( $args = '' , $array = false) {
		$defaults = array(
			// 'posts_per_page' => 10,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => '',
			'meta_value' => '',
			'post_status' => 'any',
			'tax_query' => array(),
			'channel' => '',
			'channel_id' => 0,
			'hash' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$args['post_type'] = self::post_type;

		if ( ! empty( $args['channel_id'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => self::channel_taxonomy,
				'terms' => absint( $args['channel_id'] ),
				'field' => 'term_id',
			);
		}

		if ( ! empty( $args['channel'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => self::channel_taxonomy,
				'terms' => $args['channel'],
				'field' => 'slug',
			);
		}

		if ( ! empty( $args['hash'] ) ) {
			$args['meta_query'][] = array(
				'key' => '_hash',
				'value' => $args['hash'],
			);
		}

		$q = new WP_Query();
		$posts = $q->query( $args );

		self::$found_items = $q->found_posts;
		if($array){
			return $posts;
		}
		// die(var_dump($posts));
		$objs = array();
	
		foreach ( (array) $posts as $post ) {
			$objs[] = new self( $post );
		}

		return $objs;
	}

	public static function count( $args=  '' ) {
		if ( $args ) {
			$args = wp_parse_args( $args, array(
				'offset' => 0,
				'channel' => '',
				'channel_id' => 0,
				'post_status' => 'publish',
			) );

			self::find( $args );
		}

		return absint( self::$found_items );
	}

	public function __construct( $post = null ) {
		if ( ! empty( $post ) and $post = get_post( $post ) ) {
			$this->id = $post->ID;
			$this->details = $post;
			$this->subject = get_post_meta( $post->ID, '_subject', true );
			$this->from = get_post_meta( $post->ID, '_from', true );
			$this->from_name = get_post_meta( $post->ID, '_from_name', true );
			$this->from_email = get_post_meta( $post->ID, '_from_email', true );
			$this->fields = get_post_meta( $post->ID, '_fields', true );

			if ( ! empty( $this->fields ) ) {
				foreach ( (array) $this->fields as $key => $value ) {
					$meta_key = sanitize_key( '_field_' . $key );

					if ( metadata_exists( 'post', $post->ID, $meta_key ) ) {
						$value = get_post_meta( $post->ID, $meta_key, true );
						$this->fields[$key] = $value;
					}
				}
			}

			$this->submission_status = get_post_meta( $post->ID,
				'_submission_status', true
			);

			$this->meta = get_post_meta( $post->ID, '_meta', true );
			$this->akismet = get_post_meta( $post->ID, '_akismet', true );
            $this->recaptcha = get_post_meta( $post->ID, '_recaptcha', true );
			$this->mail_sent_log = get_post_meta( $post->ID, '_bma_mail_sent_log', true );
			$this->consent = get_post_meta( $post->ID, '_consent', true );

			$terms = wp_get_object_terms( $this->id, self::channel_taxonomy );

			if ( ! empty( $terms ) and ! is_wp_error( $terms ) ) {
				$this->channel = $terms[0];
			}

            $this->hash = get_post_meta( $post->ID, '_hash', true );
            if($this->fields && array_key_exists(BMASETTINGS['input_check'], $this->fields))
            {
                $this->input_check = $this->fields[BMASETTINGS['input_check']];
            }
            if(metadata_exists( 'post', $post->ID, self::mail_sent_status )){
                $this->status = get_post_meta($post->ID,self::mail_sent_status,true);
            }
		}
	}

	public function __get( $name ) {
		/* translators: 1: Property, 2: Version, 3: Class, 4: Method. */
		$message = __( 'The visibility of the %1$s property has been changed in %2$s. Now the property may only be accessed by the %3$s class. You can use the %4$s method instead.', 'flamingo' );

		if ( 'id' == $name ) {
			if ( WP_DEBUG ) {
				trigger_error( sprintf(
					$message,
					sprintf( '<code>%s</code>', 'id' ),
					esc_html( __( 'Flamingo 2.2', 'flamingo' ) ),
					sprintf( '<code>%s</code>', self::class ),
					sprintf( '<code>%s</code>', 'id()' )
				) );
			}

			return $this->id;
		}
	}

	public function id() {
		return $this->id;
	}

	public function sent_mail($mail_log ='') {
		if ( $this->id ) {
            $post_id = $this->id ;

            // set spam meta time for later use to trash
            update_post_meta( $post_id, '_mail_sent', true );
            $previous_log = get_post_meta($post_id,'_mail_sent_log');

            update_post_meta($post_id,'_mail_sent_log',$previous_log.'\n '.$mail_log);
	    }
	}
	
	public static function block($id){
        $status = 'blocked';
        return self::updateStatus($id,$status);
	}

	public static function pending($id){
        $status = 'pending';
		return self::updateStatus($id,$status);
	}

	public static function mailed($id){
        $status = 'mailed';
        return self::updateStatus($id,$status);
    }

	public static function failed($id){
        $status = 'failed';
        return self::updateStatus($id,$status);
    }

	public static function send($id){

		global $wpdb;
		
		$row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}posts WHERE `ID` = $id ",OBJECT );
		if($row)
		{
			$message = new self($row);
			BMA()->getFunctionInstance()->send_mail_v2($message);
			return true;
		}
		return false;
	}
	public static  function getAllPending()
    {
        $array = [
            'meta_query' =>[
                'relation' => 'OR',
                 [
                    'key' => self::mail_sent_status,
                    'compare' => 'NOT EXISTS' // doesn't work
                ],
                [
                    'key' => self::mail_sent_status,
                    'value' => 'pending'
                ]
            ]
        ];

        return self::find($array);
    }
    public static function  updateStatus($id, $value){
        return update_post_meta($id,self::mail_sent_status,$value);
    }
}

// $data = array(
// 	'ID' => $post_id,
// 	'post_content' => $content,
// 	'meta_input' => array(
// 	  'meta_key' => $meta_value,
// 	  'another_meta_key' => $another_meta_value
// 	 )
//    );
   
//   wp_update_post( $data );