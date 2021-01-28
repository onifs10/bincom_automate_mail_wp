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
    public $status; // html or plan text
    public $parent_id;
    private $timestamp = null;

    public function __get($name)
    {
        if($name == 'subject') {
            return $this->fields;
        }
        if($name == 'type'){
            return $this->status;
        }
        return  $this->$name;
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

    public function __construct( $post = null ) {
        if ( ! empty( $post ) and $post = get_post( $post ) ) {
            $this->id = $post->ID;
            $this->name = $post->post_name;
            $this->content = $post->post_content;
            $this->title = $post->post_title;
            $this->timestamp = $post->post_date_gmt;
            $this->status = $post->post_status;
            $this->parent_id = $post->post_parent;
            $this->fields = $this->getPostMeta($post->ID,self::fields_option);
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
            'status' => 'plain',
            'subject' => '',
            'title' => '',
            'name' => 'sample Template',
            'content' => '',
            'timestamp' => null,
            'parent_id' => null,
            'fields' => array()
        ) );
        $obj = new self();

        $obj->title = $args['title'];
        $obj->status = $args['status'];
        $obj->fields = $args['fields'];
        $obj->content = $args['content'];
        $obj->name = $args['name'];
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
            $fields = trim($this->fields);
            $this->updatePostMeta($post_id, self::fields_option,$fields);
//            $this->updatePostMeta($post_id, self::form_to_check_slug_meta_name,$this->form_to_check_slug);
        }
    }
    public  static  function update($id,$args = ''){
        $args = wp_parse_args( $args, array(
            'ID' => $id,
            'status' => 'plain',
            'name' => '',
            'title' => '',
            'content' => '',
            'timestamp' => null,
            'fields' => []
        ) );
        $obj = new self($id);
        $obj->title = $args['title'];
        $obj->status = $args['status'];
        $obj->name = $args['name'];
        $obj->content = $args['content'];
        $obj->fields = trim($args['fields']);
        if ( $args['timestamp'] ) {
            $obj->timestamp = $args['timestamp'];
        }
        $obj->updatePost();
        return $obj;
        $done = $obj->updatePost();
        
        if($done)
        {
            return $obj;
        }

    }
    public function updatePost(){
        $post_array = $this->getPostArray();
        $post_id = wp_update_post( $post_array,false, false );

        if($post_id){
            $this->updatePostMeta($post_id, self::fields_option,$this->fields);
        }
        return $post_id;
    }

    private  function updatePostMeta($id, $key, $value ){
        return update_post_meta($id, $key, $value);
    }

    private  function  getPostMeta($id, $key)
    {
        return get_post_meta($id, $key, true);
    }

    private function get_post_date() {
        if ( empty( $this->id ) ) {
            return false;
        }

        $post = get_post( $this->id );

        if ( ! $post ) {
            return false;
        }

        return $post->post_date;
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

    public static function  getTemplateByParentOrInputRequired($parent, $input_required =  null){
        global $wpdb;
        $type = self::post_type;
        if($input_required){
            $sql = "SELECT * from  {$wpdb->posts} WHERE post_parent = '{$parent}' AND post_title = '{$input_required}' AND  post_type = '{$type}' ";
            $result = $wpdb->get_row($sql,ARRAY_A);
        }else{
            $sql = "SELECT * from  {$wpdb->posts} WHERE post_parent = '{$parent}' AND  post_type = '{$type}' ";
            $result = $wpdb->get_row($sql,ARRAY_A);
        }
        if(isset($result['ID'])) {
            $obj = new  self($result['ID']);
            return $obj;
        }
        return new self();
    }
}