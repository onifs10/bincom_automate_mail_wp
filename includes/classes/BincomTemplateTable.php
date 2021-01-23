<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BincomTamplateTable extends  WP_List_Table{
	const search_by_name = 'bma_sbn';
	public function __construct(){
		 parent::__construct(
                [
                    'singular' => __('Automated Mail Tamplate', 'bma'),
                    'plural' => __('Automated Mail Templates', 'bma'),
                    'ajax' => false
                ]
            );
	}
	public  function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox"/>',
			'ID' => __('Id','bma'),
			'name' => __('Name' , 'bma'),
            'subject' => __('Mail Subject','bma'),
            'content' => __('Mail Body', 'bma'),
            'title' => __('Input Value for template','bma'),
            'status' => __('Template Body Type'),
            'parent_id' => __('Tamplate For - Mail ','bma')
		];
		return $columns;
    }
    public function column_parent_id($item){
        $post = get_post($item->parent_id);
        return $post ?  $post->post_name : 'none';
    }
	public function prepare_items()
	{
		$this->process_bulk_action();
		$this->_column_headers = [$this->get_columns(),['ID'],$this->get_sortable_columns(),[]];
      
		$per_page = $this->get_items_per_page('bma_Template_per_page', 10);
		$args = [
			'posts_per_page' => $per_page, //remember to set this in the options
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
			'orderby' => 'ID',
			'order' => 'DESC'
		];
		if(array_key_exists(self::search_by_name,$_REQUEST)  && $search_name = $_REQUEST[self::search_by_name]){
			$args = [
				'post_name' => $search_name
			];
		}
        if(array_key_exists('mail',$_REQUEST)  && $mail_id = $_REQUEST['mail']){
            $args = [
                'post_parent' => $mail_id
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


		$this->items = BincomAutomatedMailsTemplates::find($args);
		$total_items = BincomAutomatedMailsTemplates::count();
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
        case 'subject':
          case 'template':
        case 'content':
			return $item->$column_name;
        default:
          return print_r( $item, true ); //Show the whole array for troubleshooting purposes
      }
    }

    public function column_content($item){
	    return htmlspecialchars($item->content);
    }
	
	  public function column_name( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'bma_delete_template' );
    
        $title = '<strong>' . $item->name . '</strong>';
    
        $actions = [
        'delete' => sprintf( '<a onclick="return confirm(\'are you sure you want to delete this template\')" href="?page=%s&action=%s&template=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item->id() ), $delete_nonce ),
        'edit' => sprintf( '<a href="?page=%s&action=%s&template=%s">edit</a>', esc_attr( $_REQUEST['page'] ), 'edit_page', absint( $item->id() ) )
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
		if( !array_key_exists('template', $_GET)){
          return;
        }
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
      
          // In our file that handles the request, verify the nonce.
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );
      
          if ( ! wp_verify_nonce( $nonce, 'bma_delete_template' ) ) {
            die( 'Go get a life script kiddies' );
          }
          else {
            self::delete_template( absint( $_GET['template'] ) );
      
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
            self::delete_template( $id );
          }
      
          // wp_safe_redirect( esc_url( add_query_arg() ) );
          // exit();
        }
	}
	public static function delete_template($id){
			$mail = new BincomAutomatedMailsTemplates($id);
			$mail->delete();
    }


}