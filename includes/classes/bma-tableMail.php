<?php 

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/ClassesModel.php');
BMA()->load_files(BMA()->get_vars('PATH').'includes/classes/BincomAutomatedMails.php');

class AutomationMailDetailsTable extends WP_List_Table {
    /** Class construstor */
    public static $table = BMATABLE;
    public function __construct()
    {
            parent::__construct(
                [
                    'singular' => __('class', 'bma'),
                    'plural' => __('classes', 'bma'),
                    'ajax' => false
                ]
            );
    }

    public static function get_classes($per_page = 5, $page_number = 1){
       return BincomAutomatedClasses::findALL($per_page, $page_number, true);
    }
    public static function delete_class( $id ) {
       BincomAutomatedClasses::delete($id);
    }
    public static function record_count() {
        global $wpdb;
        $table = self::$table;
        $sql = "SELECT COUNT(*) FROM {$table}";
      
        return $wpdb->get_var( $sql );
    }
    public function no_items() {
        _e( 'No class details  added.', 'bma' );
    }
    
    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public function column_class_name( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'bma_delete_class' );
    
        $title = '<strong>' . $item['class_name'] . '</strong>';
    
        $actions = [
        'delete' => sprintf( '<a href="?page=%s&action=%s&class=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
        'edit' => sprintf( '<a href="?page=%s&action=%s&class=%s">edit</a>', esc_attr( $_REQUEST['page'] ), 'edit_page', absint( $item['ID'] ) )
        ];
    
        return $title . $this->row_actions( $actions );
    }
    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
      switch ( $column_name ) {
        case 'ID':
        case 'class_name':
        case 'class_code':
        case 'class_starts':
          // return "<a href='/{$item[$column_name]}' > {$item[$column_name]} </div>";
        case 'class_days':
        case 'class_time':
        case 'class_link':
        case 'mail_template':
        case 'class_duration':
          return $item[ $column_name ];
        default:
          return print_r( $item, true ); //Show the whole array for troubleshooting purposes
      }
    }
        /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
        '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
        );
    } 
     /**
     *  Associative array of columns
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
        'cb'      => '<input type="checkbox" />',
        'ID'  => __('id','bma'),
        'class_name'    => __( 'Name', 'bma' ),
        'class_code' => __( 'Code', 'bma' ),
        'class_days'    => __( 'Days', 'bma' ),
        'class_starts'    => __( 'Starts', 'bma' ),
        'class_time' => __('Time','bma'),
        'class_link' => __('Link','bma'),
        'class_duration' => __('Duration','bma'),
        'mail_template' => __('Mail Template', 'bma')
        ];
        return $columns;
    }
    /**
     * Columns to make sortable.
     *
     * @return array
     */
    
    protected  function get_sortable_columns() {
        $sortable_columns = [
        'class_name'    => ['orderby', true ],
        'class_code' => array( 'class_code', true ),
        'class_days'    => array( 'class_days', true ),
        'class_starts'    => array( 'class_starts', true ),
        'class_time' => array('class_time',true),
        'class_link' => array('class_link',true),
        'class_duration' => array('class_duration',true)
        ];
    
        return $sortable_columns;
    }
    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
        'bulk-delete' => 'Delete'
        ];
    
        return $actions;
    }
    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {


      $this->_column_headers = [$this->get_columns(),['ID'],$this->get_sortable_columns(),[]];
        // $this->_column_headers = $this->get_column_info();
    
        /** Process bulk action */
        $this->process_bulk_action();
    
        $per_page     = $this->get_items_per_page( 'classes_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();
    
        $this->set_pagination_args( [
        'total_items' => $total_items, //WE have to calculate the total number of items
        'per_page'    => $per_page //WE have to determine how many items to show on a page
        ] );
    
    
        $this->items = self::get_classes( $per_page, $current_page );
    }
    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
      
          // In our file that handles the request, verify the nonce.
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );
      
          if ( ! wp_verify_nonce( $nonce, 'bma_delete_class' ) ) {
            die( 'Go get a life script kiddies' );
          }
          else {
            self::delete_class( absint( $_GET['class'] ) );
      
            // wp_safe_redirect( esc_url( add_query_arg() ) );
            // exit();
          }
      
        }
      
        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {
      
          $delete_ids = esc_sql( $_POST['bulk-delete'] );
      
          // loop over the array of record IDs and delete them
          foreach ( $delete_ids as $id ) {
            self::delete_class( $id );
          }
      
          // wp_safe_redirect( esc_url( add_query_arg() ) );
          // exit();
        }
      }
}