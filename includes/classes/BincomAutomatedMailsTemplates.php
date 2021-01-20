<?php

class BincomAutomatedMailsTemplates {
    const post_type = 'bma-mail-templates';
    const default_status = 'enabled';
    const  templates_type = 'single';
    const fields_option  = '_bma_mail_template_fields';
    private static $found_items = 0;
    private $id;
    public $name; //name
    public $title;  //description
    public $content; //template
    public $fields;
    public $status;
    private $parent_id;
    private $timestamp = null;

    public function __construct( $post = null ) {
        if ( ! empty( $post ) and $post = get_post( $post ) ) {
            $this->id = $post->ID;
            $this->name = $post->post_name;
            $this->content = $post->post_content;
            $this->title = $post->post_title;
            $this->timestamp = $post->post_date_gmt;
            $this->status = $post->post_status;
            $this->parent_id = $post->post_parent;
            $this->fields = $this->fields_unserialize($this->getPostMeta($post->ID,self::fields_option));
      }
    }

    public static  function register_post_type(){

        register_post_type( self::post_type, array(
            'labels' => array(
                'name' => __( 'Bincom Automated Mails', 'bma' ),
                'singular_name' => __( 'Bincom Automated Mail', 'bma' ),
            ),
            'rewrite' => true,
            'query_var' => false,
        ) );
    }
    public  static  function add($args = ''){
        $args = wp_parse_args( $args, array(
            'status' => '',
            'subject' => '',
            'title' => '',
            'content' => '',
            'timestamp' => null,
            'parent_id' => null,
            'fields' => array()
        ) );
        $obj = new self();

        $obj->title = $args['title'];
        $obj->status = $args['status'];
        $obj->subject = $args['subject'];
        $obj->content = $args['content'];
        $obj->fields = $args['fields'];
        $obj->parent_id = $args['parent_id'];
        if ( $args['timestamp'] ) {
            $obj->timestamp = $args['timestamp'];
        }
        $obj->save();
        return $obj;

    }
    /**
     *add the parent_id to get the count for a particular mall
     */
    public static function count( $args = '' ) {
        if ( $args ) {
            $args = wp_parse_args( $args, array(
                'offset' => 0,
            ) );

            self::find( $args );
        }

        return absint( self::$found_items );
    }
    /**
     *add the parent_id to get the count for a particular mall
     */
    public static function find( $args = '' ) {
        $defaults = array(
//            'posts_per_page' => 10,
            'offset' => 0,
            'orderby' => 'ID',
            'order' => 'DESC',
            'meta_key' => '',
            'meta_value' => '',
            'post_status' => 'any',
        );
        $args = wp_parse_args( $args, $defaults );

        $args['post_type'] = self::post_type;
        $q = new WP_Query();
        $posts = $q->query( $args );

        self::$found_items = $q->found_posts;

        $objs = array();

        foreach ( (array) $posts as $post ) {
            $objs[] = new self( $post );
        }

        return $objs;

    }

    public function id() {
        return $this->id;
    }

    private function  getPostArray(){
        if(!$this->title){
            return false;
        }
        $content = $this->content;
        $status = $this->status ?? self::default_status;
        $title = $this->title;
        $type = self::post_type;
        $parent = $this->parent_id;
        $date = $this->get_post_date();
        $post_array = [
            'ID' => absint( $this->id ),
            'post_type' => $type,
            'post_status' => $status,
            'post_title' => $title,
            'post_content' => $content,
            'post_parent' => $parent,
            'post_name' => $this->name,
            'post_date' => $this->get_post_date(),
        ];
        if ( $this->timestamp
            and $datetime = date_create( '@' . $this->timestamp ) ) {
            $datetime->setTimezone( wp_timezone() );
            $post_array['post_date'] = $datetime->format( 'Y-m-d H:i:s' );
        }
        return  $post_array;
    }
    public function save(){
        $post_array = $this->getPostArray();
        $post_id = wp_insert_post( $post_array );

        if($post_id){
            $this->id = $post_id;
            $fields = $this->fields_serialize($this->fields);
            $this->updatePostMeta($post_id, self::fields_option,$fields);
//            $this->updatePostMeta($post_id, self::form_to_check_slug_meta_name,$this->form_to_check_slug);
        }
    }
    public  static  function update($id,$args = ''){
        $args = wp_parse_args( $args, array(
            'ID' => $id,
            'status' => '',
            'name' => '',
            'title' => '',
            'content' => '',
            'timestamp' => null,
            'fields' => []
        ) );
        $obj = new self();
        $obj->title = $args['title'];
        $obj->status = $args['status'];
        $obj->name = $args['name'];
        $obj->content = $args['content'];
        $obj->fields = $obj->fields_serialize($args['fields']);
        if ( $args['timestamp'] ) {
            $obj->timestamp = $args['timestamp'];
        }
        $obj->updatePost();
        return $obj;

    }
    public function updatePost(){
        $post_array = $this->getPostArray();
        $post_id = wp_update_post( $post_array,false, false );

        if($post_id){
            $this->updatePostMeta($post_id, self::fields_option,$this->fields);
        }
    }
    public function fields_serialize($data){
        if( !is_serialized( $data ) ) {
            $data = maybe_serialize($data);
        }
        return $data;
    }

    public function fields_unserialize($data){
        if( !is_serialized( $data ) ) {
            $data = maybe_unserialize($data);
        }
        return $data;
    }

    private  function updatePostMeta($id, $key, $value ){
        return update_post_meta($id, $key, $value);
    }

    private  function  getPostMeta($id, $key)
    {
        return get_post_meta($id, $key, true);
    }

    public function delete() {
        if ( empty( $this->id ) ) {
            return;
        }

        if ( $post = wp_delete_post( $this->id, true ) ) {
            $this->id = 0;
        }

        return (bool) $post;
    }
    public  static  function  delete_template($id){
        global $wpdb;
        $table = $wpdb->posts;
        if(empty($id)){
            $id = self::$id;
        }
        $wpdb->delete($table, ["ID" => $id,'post_type' => self::post_type]);
    }
}