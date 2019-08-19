<?php
if(!class_exists('WP_List_Table')){
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Failed_Imports_List_Table extends WP_List_Table{
     
   function __construct(){
        parent::__construct(array(
            'singular' => 'Import',
            'plural' => 'Imports',
            'ajax' => false
        ));
    }
    
    function extra_tablenav( $which ) {
        if ( $which == "top" ){
           //The code that goes before the table is here
        }
        if ( $which == "bottom" ){
           //The code that goes after the table is there
        }
    }
    
    function get_columns() {
        return $columns= array(
            'Url' => __('Failed Url')
        );
    }
    
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
     
        /* -- Preparing your query -- */
        $imports_table = $wpdb->prefix."eci_failed_imports";
        
        $query = "SELECT * FROM " . $imports_table;
     
        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? esc_sql($_GET["orderby"]) : 'ASC';
        $order = !empty($_GET["order"]) ? esc_sql($_GET["order"]) : '';
        if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
     
        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows
        //How many to display per page?
        $perpage = 20;
        
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';
        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; } //How many pages do we have in total?
           $totalpages = ceil($totalitems/$perpage); //adjust the query to take pagination into account
        
        if(!empty($paged) && !empty($perpage)){ $offset=($paged-1)*$perpage; $query.=' LIMIT '.(int)$offset.','.(int)$perpage; } /* -- Register the pagination -- */
           $this->set_pagination_args( array(
                "total_items" => $totalitems,
                "total_pages" => $totalpages,
                "per_page" => $perpage,
           ) );
        //The pagination links are automatically built according to those parameters
     
        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        
        $hidden = $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
         
        /* -- Fetch the items -- */
        $this->items = $wpdb->get_results($query);
    }
    
    public function column_default($item, $column_name) {
      switch($column_name){
         case 'Url':
            return $item[$column_name];
         default:
            return print_r($item, true);
      }
     }
         
    function display_rows() {

        //Get the records registered in the prepare_items method
        $records = $this->items;
      
        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();
      
        $columns = $this->get_columns();
        //Loop for each record
       
        if(!empty($records)){foreach($records as $rec){
            
            //Open the line
            ?>
            <tr id="record_<?php echo $rec->Id; ?>">
            <?php
            
            foreach ( $columns as $column_name => $column_display_name ) {
     
                //Style attributes for each col
                $class = "class='$column_name column-$column_name'";
                $style = "";
                if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                $attributes = $class . $style;
      
       
                //Display the cell
                switch ( $column_name ) {
                    case "Id":  echo '<td '.$attributes.'>'.stripslashes($rec->Id).'</td>';   break;
                    case "Url": echo '<td '.$attributes.'>'.stripslashes($rec->Url).'</td>'; break;
                }
            }
     
            //Close the line
            ?>
            </tr>
            <?php
            }
        }
        
    }
}

?>