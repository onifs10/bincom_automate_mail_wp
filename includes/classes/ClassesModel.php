<?php 

class  BincomAutomatedClasses {
    public static $table = BMATABLE;

    private $id;

    public $name, $code, $starts, $days, $mail_template, $duration, $time, $link;

    public function __construct($class)
    {
        [
            'ID' => $this->id,
            'class_name' => $this->name,
            'class_code' => $this->code,
            'class_starts' => $this->starts,
            'class_days' => $this->days,
            'class_time' => $this->time,
            'class_link' => $this->link,
            'class_duration' => $this->duration,
            'mail_template' => $this->mail_template
        ] = $class;
    }

    public static function findALL($per_page = 5, $page_number=1, $array = false ){
        global $wpdb; 
        $table = self::$table;
        $sql = "SELECT * FROM  {$table}";
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
          }
        
          $sql .= " LIMIT $per_page";
        
          $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
          $result = $wpdb->get_results( $sql, 'ARRAY_A' );
          if(!$array){
              $classes = [];
              foreach($result as $class){
                    $classes[] = new self($class);
              }
              return $classes;
          }
          return $result; 
    }

    public static function find($id){
        global $wpdb; 
        $table = self::$table;
        $sql = "SELECT * FROM  {$table} WHERE ID = '{$id}'";
        $result = $wpdb->get_row( $sql, 'ARRAY_A' );
        $obj  = new self($result);
        return $obj;
    }

    

    public static function findByCode($code){
        global $wpdb; 
        $table = self::$table;
        $sql = "SELECT * FROM  {$table} WHERE class_code = '{$code}'";
        $result = $wpdb->get_row( $sql, 'ARRAY_A' );
        if($result){
            $obj  = new self($result);
            return $obj;
        }else{
            return false;
        }
       }

    



    public static function insert($details = []){
        global $wpdb; 
        $table = self::$table;
        $details['ID'] = null;
        if($wpdb->insert($table, $details))
        {
            return new self($details);
        }else{
            return false;
        }
    }

    private function getDetails() {
        $details = [
            'ID' => $this->id?? null,
            'class_name' => $this->name,
            'class_code' => $this->code,
            'class_starts' => $this->starts,
            'class_days' => $this->days,
            'class_time' => $this->time,
            'class_link' => $this->link,
            'class_duration' => $this->duration,
            'mail_template' => $this->mail_template
        ];

        return $details;
    }

    public  function update(){
        global $wpdb;
        $details = $this->getDetails();
        return $wpdb->update(self::$table, $details, ['ID' => $details['ID']]);

    }

    public static function delete($id = ''){
        global $wpdb;
        $table = self::$table;
        if(empty($id)){
            $id = self::$id;
        }
        $wpdb->delete($table, ["ID" => $id]);
    }

    public function save(){
        global $wpdb;
        $details = $this->getDetails();
        $wpdb->replace($this->table, $details);
    }
}