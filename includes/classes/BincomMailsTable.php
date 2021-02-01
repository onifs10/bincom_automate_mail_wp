<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class BincomAutomatedMailsTable extends  WP_List_Table{
	const search_by_name = 'bma_sbn';
	public function __construct(){
		 parent::__construct(
                [
                    'singular' => __('Automated Mail', 'bma'),
                    'plural' => __('Automated mails', 'bma'),
                    'ajax' => false
                ]
            );
	}
	public  function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox"/>',
			'ID' => __('Id','bma'),
			'name' => __('Name' , 'bma'),
			'title' => __('Mail Title','bma'),
			'content' => __('Template types', 'bma'),
			'form_to_check_slug' => __('Form to Check','bma'),
			'input_to_check' => __('Input field checked','bma'),
            'additional_header' => __('Additional Headers','bma'),
		];
		return $columns;
	}
	public function prepare_items()
	{
		$this->process_bulk_action();
		$this->_column_headers = [$this->get_columns(),['ID'],$this->get_sortable_columns(),[]];
      
		$per_page = $this->get_items_per_page('bma_mail_per_page', 10);
		$args = [
			'posts_per_page' => $per_page, //remember to set this in the options
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
			'orderby' => 'ID',
			'order' => 'DESC'
		];
		if(array_key_exists(self::search_by_name,$_REQUEST) && $search_name = $_REQUEST[self::search_by_name]){
			$args = [
				'post_name' => $search_name
			];
		}
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( 'name' == $_REQUEST['orderby'] ) {
				$args['orderby'] = 'post_name';
				$args['order'] = $_REQUEST['order'];
			} elseif ( 'from' == $_REQUEST['orderby'] ) {
				$args['meta_key'] = '_from';
				$args['orderby'] = 'meta_value';
			}
		}


		$this->items = BincomAutomatedMails::find($args);
		$total_items = BincomAutomatedMails::count();
		$total_pages = ceil( $total_items / $per_page );

		

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page,
		) );
		
	}
	  public function column_default( $item, $column_name ) {
      switch ( $column_name ) {
        case 'ID':
		  return $item->id();
		case 'name':
		case 'status':
		case 'title':
		case 'content':
          case 'additional_header':
		case 'form_to_check_slug':
		case 'input_to_check':
			return htmlspecialchars($item->$column_name);
        default:
          return print_r( $item, true ); //Show the whole array for troubleshooting purposes
      }
	}
	
	  public function column_name( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'bma_delete_mail' );

        $title = '<strong>' . $item->name . '</strong>';
        $template_page = menu_page_url('bincom_mail_template_menu',false);
        $add_template_page = menu_page_url('bma_add_template',false);
        $actions = [
            'delete' => sprintf( '<a onclick="return confirm(\'are you sure you want to delete this mail if you do, all the templates would be deleted\')"  href="?page=%s&action=%s&mail=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item->id() ), $delete_nonce ),
            'edit' => sprintf( '<a  href="?page=%s&action=%s&mail=%s">edit</a>', esc_attr( $_REQUEST['page'] ), 'edit_page', absint( $item->id() ) ),
            'templates' => sprintf( '<a href="%s&mail=%s">templates</a>', $template_page, absint( $item->id() ) ),
            'add_template' => sprintf( '<a href="%s&mail=%s">Add Templates</a>', $add_template_page, absint( $item->id() ) ),
        ];
    
        return $title . $this->row_actions( $actions );
	}
	
	 public function column_cb( $item ) {
        return sprintf(
        '<input type="checkbox" name="bulk[]" value="%s" />', $item->id()
        );
    } 
	
	protected  function get_sortable_columns() {
        $sortable_columns = [
        'name'    => ['name', true ],
        ];
    
        return $sortable_columns;
	}
	public function get_bulk_actions() {
        $actions = [
        'bulk-delete' => 'Delete'
        ];
    
        return $actions;
	}
	public function process_bulk_action(){
		if( !array_key_exists('mail', $_GET)){
          return;
        }
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
      
          // In our file that handles the request, verify the nonce.
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );
      
          if ( ! wp_verify_nonce( $nonce, 'bma_delete_mail' ) ) {
            die( 'Go get a life script kiddies' );
          }
          else {
            self::delete_mail( absint( $_GET['mail'] ) );
      
            // wp_safe_redirect( esc_url( add_query_arg() ) );
            // exit();
          }
      
        }
      
        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {
      
          $delete_ids = esc_sql( $_POST['bulk'] );
      
          // loop over the array of record IDs and delete them
          foreach ( $delete_ids as $id ) {
            self::delete_mail( $id );
          }
      
          // wp_safe_redirect( esc_url( add_query_arg() ) );
          // exit();
        }
	}
	public static function delete_mail($id){
			$mail = new BincomAutomatedMails($id);
			$mail->delete();
	}
}