<?php
class OII_ECI_External_Content {
    public $order = 1;
    
    public $header = NULL;
    
    public $url = NULL;

    public $start_code = NULL;
    
    public $end_code = NULL;
    
    public function __construct($row = array())
    {
        if(is_array($row) AND count($row))
            $this->instantiate($row);
    }
    
    public function instantiate($row = array())
    {
        foreach($row AS $attribute => $content)
        {
            if(property_exists($this, $attribute))
                $this->$attribute = $content;
        }
    }
    
    public static function get_by_post_id($post_id)
    {
        require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
        
        $post_meta = get_post_meta($post_id, OII_ECI_Metabox::$post_meta, TRUE);
    
        $external_contents = array();
    
        if(is_array($post_meta) == FALSE)
            return $external_contents;
        
        foreach($post_meta AS $meta)
            array_push($external_contents, new self($meta));
        
        return $external_contents;
    }
}