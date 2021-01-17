<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/inbound_message.php');
class BMA_Inbound_Messages_List_Table extends WP_List_Table {
    public function __construct()
    {
            parent::__construct(
                [
                    'singular' => __('Message', 'bma'),
                    'plural' => __('Messages', 'bma'),
                    'ajax' => false
                ]
            );
    }
    public static function get_messages($per_page = 5, $page_number = 1){
        if(array_key_exists('orderby', $_REQUEST)){
            $orderby = esc_sql( $_REQUEST['orderby'] );
        }else{
            $orderby = null;
        }
        if(array_key_exists('order', $_REQUEST)){
            $order = esc_sql( $_REQUEST['order'] );
        }else{
            $order = null;
        }
        $args = array(
			'posts_per_page' => $per_page,
			'offset' => ( $page_number - 1 ) * $per_page,
			'orderby' => $orderby  ?? 'ID',
			'order' => $order  ?? 'ASC',
			'meta_key' => '',
			'meta_value' => '',
			'post_status' => 'any',
			'tax_query' => array(),
			'channel' => '',
			'channel_id' => 0,
			'hash' => '',
		);
        return  BMA_Inbound_Message::find($args);
     }
    public static function record_count() {
        return BMA_Inbound_Message::count();
    }
    public function no_items() {
        _e( 'No inbound message found', 'bma' );
    }

    public function column_subject( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'bma_delete_class' );
    
        $title = '<strong>' . $item->subject . '</strong>';
    
        $actions = [
        'block' => sprintf( '<a href="?page=%s&action=%s&class=%s">block</a>', esc_attr( $_REQUEST['page'] ), 'block_incoming', absint( $item->id() ) )
        ];
    
        return $title . $this->row_actions( $actions );
    }
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
          case 'ID':
            return $item->id();
          case 'subject':
          case 'from_email':
          case 'from_name':
            // return "<a href='/{$item[$column_name]}' > {$item[$column_name]} </div>";
          case 'feilds':
          case 'class_time':
          case 'class_link':
          case 'mail_template':
          case 'class_duration':
            return $item->$column_name;
          default:
            return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
      }
    public function column_cb( $item ) {
        return sprintf(
        '<input type="checkbox" name="bulk-block[]" value="%s" />', $item->id()
        );
    } 
    public function get_columns() {
        $columns = [
        'cb'      => '<input type="checkbox" />',
        'ID'  => __('id','bma'),
        'subject'    => __( 'Subject', 'bma' ),
        'from_email' => __( 'From Email', 'bma' ),
        'from_name'    => __( 'From Name', 'bma' ),
        // 'class_starts'    => __( 'Starts', 'bma' ),
        // 'class_time' => __('Time','bma'),
        // 'class_link' => __('Link','bma'),
        // 'class_duration' => __('Duration','bma'),
        // 'mail_template' => __('Mail Template', 'bma')
        ];
        return $columns;
    }

    protected  function get_sortable_columns() {
        $sortable_columns = [
        'from_email'    => ['from_email', true ],
        // 'class_code' => array( 'class_code', true ),
        // 'class_days'    => array( 'class_days', true ),
        // 'class_starts'    => array( 'class_starts', true ),
        // 'class_time' => array('class_time',true),
        // 'class_link' => array('class_link',true),
        // 'class_duration' => array('class_duration',true)
        ];
    
        return $sortable_columns;
    }
    public function get_bulk_actions() {
        $actions = [
        'bulk-block' => 'Block'
        ];
    
        return $actions;
    }
    public function prepare_items() {


      $this->_column_headers = [$this->get_columns(),['ID'],$this->get_sortable_columns(),[]];
        // $this->_column_headers = $this->get_column_info();
    
        /** Process bulk action */
        // $this->process_bulk_action();
    
        $per_page     = $this->get_items_per_page( 'classes_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();
    
        $this->set_pagination_args( [
        'total_items' => $total_items, //WE have to calculate the total number of items
        'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );
    
    
        $this->items = self::get_messages( $per_page, $current_page );
    }
}