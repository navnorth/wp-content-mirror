<?php
class OII_ECI_External_Content {    
    public static $table = "oii_external_contents";
    
    public $id = 0;
    
    public $post_id = 0;
    
    public $order = 1;
    
    public $header = NULL;
    
    public $content = NULL;
    
    public $url = NULL;

    public $start = NULL;
    
    public $end = NULL;
    
    public $date = NULL;
    
    /**
     * Class Constructor
     * Description
     *
     * @param array $row The row.
     */
    public function __construct($row = array())
    {
        if(is_array($row) AND count($row))
            $this->instantiate($row);
    }
    /**
     * Instantiate
     * Description
     *
     * @param array $row The row.
     */
    public function instantiate($row = array())
    {
        foreach($row AS $attribute => $content)
        {
            if(property_exists($this, $attribute))
                $this->$attribute = $content;
        }
    }
    /**
     * Get By Post ID
     * Description
     *
     * @param integer $post_id The post ID.
     *
     * return array
     */
    public static function get_by_post_id($post_id)
    {
        require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
        
        $post_meta = get_post_meta($post_id, OII_ECI_Metabox::$meta_key, TRUE);
    
        $external_contents = array();
    
        if(is_array($post_meta) == FALSE)
            return $external_contents;
        
        foreach($post_meta AS $meta)
            array_push($external_contents, new self($meta));
        
        return $external_contents;
    }
    /**
     * External Content as Postmeta
     * Description
     *
     * @return array The postmeta.
     */
    public function as_postmeta()
    {
        $postmeta = array_fill_keys(array("order", "header", "url", "start", "end"), NULL);
        
        foreach(array_keys($postmeta) AS $meta)
        {
            if(property_exists($this, $meta))
                $postmeta[$meta] = $this->$meta;
        }
        
        return $postmeta;
    }
    /**
     * Load
     * Load contents from database.
     *
     * @param integer $post_id The post ID.
     */
    public function load($post_id = 0)
    {
        global $wpdb;
        
        // Todo
    }
    /**
     * Update
     * Update external content in database.
     *
     * @param integer $post_id The post ID.
     * @param integer $order The external content order.
     */
    public function update($post_id = 0, $order = 1)
    {
        global $wpdb;
        
        $this->post_id = $post_id;
        
        $sql = $wpdb->prepare("SELECT `id` FROM `" . $wpdb->prefix . self::$table . "` WHERE `post_id` = %d AND `order` = %d", $this->post_id, $order);
        
        if($wpdb->get_row($sql))
        {
            // Update
        }
        else
        {
            $this->content = $this->extract();
            
            // Insert
            $sql = $wpdb->prepare("INSERT INTO `" . $wpdb->prefix . self::$table . "` (`post_id`, `order`, `content`, `url`, `date`) VALUES (%d, %d, %s, %s, NOW())", $this->post_id, $order, $this->content, $this->url);
        
            $wpdb->query($sql);
        }
    }
    /**
     * Extract
     * Extract content from URL
     *
     * @return string The extracted content.
     */
    public function extract()
    {
        $html = file_get_contents($this->url);
        
        if($html === FALSE)
            return NULL;
        
        $html = mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8");
        
        $_this_start = htmlspecialchars_decode($this->start);
        $_this_end = htmlspecialchars_decode($this->end);
        
        // Extract by Comment Tag
        if($this->_is_html_comment($_this_start) AND $this->_is_html_comment($_this_end))
        {
            $start = strpos($html, $_this_start);
            
            if($start === FALSE)
                return NULL;
            
            $start = $start + strlen($_this_start);
            
            $sub = substr($html, $start);
            $stop = strpos($sub, $_this_end);
            
            if($stop === FALSE)
                return NULL;
            
            return substr($html, $start, $stop);
        }
        // Extract by HTML Tag 
        else if($this->_is_html_tag($_this_start) AND $this->_is_html_tag($_this_end))
        {
            $start = strpos($html, $_this_start);
            
            if($start === FALSE)
                return NULL;
            
            $start = $start + strlen($_this_start);
            
            $open = $close = 0;
            
            $sub = substr($html, $start);
            
            // Todo: Evaluate End Code
            
            $stop = strpos($sub, $_this_end) + strlen($_this_end);
            $close = 1;
            
            $pattern = '/(<' . $this->_tag_name($_this_start) . ')/';
            preg_match_all($pattern, substr($sub, 0, $stop), $matches);
            
            $open = $open + count($matches[0]);
            
            $_start = $_stop = $stop;
            
            while($open > $close)
            {
                $_stop = $_stop + strpos(substr($sub, $_start), $_this_end) + strlen($_this_end);
                
                preg_match_all($pattern, substr($sub, $_start, $_stop), $matches);
                
                $open = $open + count($matches[0]);
                $_start = $_stop;
                
                $close++;
            }
            
            $_stop = $_stop - strlen($_this_end);
            
            return substr($sub, 0, $_stop);
        }
        
        return NULL;
    }
    /**
     * Format
     * Format extracted Content
     *
     * @param string $content The raw content.
     * 
     * return string The formatted content.
     */
    public function format($content = NULL)
    {
        
        
        return $content;
    }
    /**
     * Tag Name
     * Description
     *
     * @param string $tag The HTML tag.
     *
     * @return string The tag name.
     */
    private function _tag_name($tag)
    {
        $pattern = "/\w+/";
        
        preg_match($pattern, $tag, $matches);
        
        return array_pop($matches);
    }
    /**
     * Is HTML Tag
     * Description
     *
     * @param string $tag The HTML tag.
     *
     * @return boolean Is HTML tag.
     */
    private function _is_html_tag($tag)
    {
        $pattern = "/<[^!][^<>]+>/";
        preg_match($pattern, $tag, $matches);
        
        return (boolean) count($matches);
    }
    /**
     * Is HTML Comment
     * Description
     *
     * @param string $tag The HTML tag.
     *
     * @return boolean Is HTML comment.
     */
    private function _is_html_comment($tag)
    {
        $pattern = "/<!--(.*?)-->/";
        preg_match($pattern, $tag, $matches);
        
        return (boolean) count($matches);
    }
}