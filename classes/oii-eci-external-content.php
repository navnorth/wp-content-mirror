<?php
class OII_ECI_External_Content {    
    public static $table = "oii_external_contents";
    
    private static $_html_paired_tags = array(
        "a", "abbr", "address", "article", "aside", "audio",
        "b", "bdi", "bdo", "blockquote", "body", "button",
        "canvas", "caption", "cite", "code", "colgroup",
        "datalist", "dd", "del", "details", "dfn", "dialog", "div", "dl", "dt",
        "em",
        "fieldset", "figcaption", "figure", "footer", "form",
        "h1", "h2", "h3", "h4", "h5", "h6", "head", "header", "html",
        "i", "iframe", "ins",
        "kbd",
        "label", "legend", "li",
        "main", "map", "mark", "menu", "menuitem", "meter",
        "nav", "noscript",
        "object", "ol", "optgroup", "option", "output",
        "p", "pre", "progress",
        "q",
        "rp", "rt", "ruby",
        "s", "samp", "script", "section", "select", "small", "span", "strong", "style", "sub", "summary", "sup",
        "table", "tbody", "td", "textarea", "tfoot", "th", "thead", "time", "title", "tr",
        "u", "ul", "var", "video", "wbr"
    );
    
    private static $_html_unpaired_tags = array(
        "area",
        "base", "br",
        "col",
        "embed",
        "hr",
        "img", "input",
        "keygen",
        "link",
        "meta",
        "param",
        "source",
        "track"
    );
    
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
        {
            $meta = array_merge($meta, array("post_id" => $post_id));
            array_push($external_contents, new self($meta));
        }
        
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
        
        $open_tag = htmlspecialchars_decode($this->start);
        $close_tag = htmlspecialchars_decode($this->end);
        
        // Extract by Comment Tag
        if($this->_is_html_comment($open_tag) AND $this->_is_html_comment($close_tag))
        {
            $start = strpos($html, $open_tag);
            
            if($start === FALSE)
                return NULL;
            
            $start = $start + strlen($open_tag);
            
            $sub = substr($html, $start);
            $stop = strpos($sub, $close_tag);
            
            if($stop === FALSE)
                return NULL;
            
            return $this->_apply_format(substr($html, $start, $stop));
        }
        
        // Extract by HTML Tag 
        else if($this->_is_html_tag($open_tag) AND $this->_is_html_tag($close_tag))
        {
            $open_offset = strpos($html, $open_tag);
            
            if($open_offset === FALSE)
                return NULL;
            
            $open_offset = $open_offset + strlen($open_tag);
            
            $open_tag_count = $close_tag_count = 0;
            
            $sub = substr($html, $open_offset);
            
            // Todo: Evaluate End Code
            
            $close_offset = strpos($sub, $close_tag) + strlen($close_tag);
            $close_tag_count = 1;
            
            $pattern = '/(<' . $this->_tag_name($open_tag) . ')/';
            preg_match_all($pattern, substr($sub, 0, $close_offset), $matches);
            
            $open_tag_count = $open_tag_count + count($matches[0]);
            
            $_open_offset = $_close_offset = $close_offset;
            
            while($open_tag_count > $close_tag_count)
            {
                $_close_offset = $_close_offset + strpos(substr($sub, $_open_offset), $close_tag) + strlen($close_tag);
                
                preg_match_all($pattern, substr($sub, $_open_offset, $_close_offset), $matches);
                
                $open_tag_count = $open_tag_count + count($matches[0]);
                $_open_offset = $_close_offset;
                
                $close_tag_count++;
            }
            
            $_close_offset = $_close_offset - strlen($close_tag);
            
            return $this->_apply_format(substr($sub, 0, $_close_offset));
        }
        
        return NULL;
    }
    /**
     * Apply Format
     * Format in extracted content.
     *
     * @param string $content The raw content.
     * 
     * @return string The formatted content.
     */
    private function _apply_format($content = NULL)
    {
        require_once(OII_ECI_PATH . "includes/oii-eci-settings-page.php");
        $option = get_option(OII_ECI_Settings_Page::$option_name);
        
        foreach($option["format"] AS $format)
            $content = $this->_change_content($content, $format["replace"], $format["with"]);
        
        return $content;
    }
    /**
     * Change Content
     * Description
     *
     * @param string $content The content.
     * @param string $tag The tag.
     * @param string $with
     *
     * @return string
     */
    private function _change_content($content = NULL, $tag = NULL, $with = NULL)
    {
        $tag = htmlspecialchars_decode($tag);
        $with = htmlspecialchars_decode($with);
        
        do
        {
            $o = $this->_get_tag_offset($content, $tag);
            
            if(array_key_exists("close", $o))
            {
                // <span class="content">
                $_w_o = $_w_c = $with;
                
                if($this->_is_html_tag($with))
                {
                    $name = $this->_tag_name($with);
                    
                    if(in_array($name, self::$_html_paired_tags) AND $this->_is_tag_close($with) == FALSE)
                        $_w_c = "</" . $name . ">";
                }
                
                $content = substr_replace($content, $_w_c, $o["close"]["start"], $o["close"]["boundary"]);
		$content = substr_replace($content, $_w_o, $o["open"]["start"], $o["open"]["boundary"]);
            }
            else
            {
                // <!-- Comment -->, <hr>
                $_w = $with;
                
                if($this->_is_html_tag($with))
                {
                    $name = $this->_tag_name($with);
                    
                    if(in_array($name, self::$_html_paired_tags) AND $this->_is_tag_close($with) == FALSE)
                        $_w = $with . "</" . $name . ">";
                }
                
                $content = substr_replace($content, $_w, $o["open"]["start"], $o["open"]["boundary"]);
            }
            
            $again = (strpos($content, $tag) === FALSE) ? FALSE : TRUE;
        }
        while($again);
        
        return $content;
    }
    /**
     * Get Tag Offset
     * Description
     *
     * @param string $content The content.
     * @param string $tag The tag.
     *
     * @return array The offsets.
     */
    private function _get_tag_offset($content, $tag)
    {
        // Get Opening Tag Offsets
        $o = array(
            "open" => array(
                "start" => strpos($content, $tag),
                "boundary" => strlen($tag)
            )
        );
        
        if($this->_is_html_tag($tag))
        {
            $name = $this->_tag_name($tag);
            
            // Opening Tag Has Closing Tag
            if(in_array($name, self::$_html_paired_tags))
            {
                // Create Closing Code
                $close_tag = "</" . $name . ">";
                
                $open_tag_count = $close_tag_count = 0;
                
                $sub = substr($content, $o["open"]["start"]);
                
                $close_offset = strpos($sub, $close_tag) + strlen($close_tag);
                $close_tag_count++;
                
                /**
                 * Find Open Tag Within Found Closing Tag
                 * @code begin
                 */
                $pattern = '/(<' . $name . ')/';
                preg_match_all($pattern, substr($sub, 0, $close_offset), $matches);
                $open_tag_count = $open_tag_count + count($matches[0]);
                /**
                 * Find Open Tag Within Found Closing Tag
                 * @code end
                 */
                
                $_open_offset = $_close_offset = $close_offset;
                
                while($open_tag_count > $close_tag_count)
                {
                    $_close_offset = $_close_offset + strpos(substr($sub, $_open_offset), $close_tag) + strlen($close_tag);
                    preg_match_all($pattern, substr($sub, $_open_offset, $_close_offset), $matches);
                    $open_tag_count = $open_tag_count + count($matches[0]);
                    
                    $_open_offset = $_close_offset;
                    
                    $close_tag_count++;
                }
                
                // Insert Close Tag Offsets
                $o["close"] = array(
                    "start" => ($o["open"]["start"] + $_close_offset) - strlen($close_tag),
                    "boundary" => strlen($close_tag)
                );
            }
        }
        
        return $o;
    }
    /**
     * Display Content
     * Desctiption
     *
     * @return string The content.
     */
    public function output_content()
    {
        // Section Header
        $content = ($this->header) ? "<h2>" . $this->header . "</h2>" : NULL;
        // Section Anchor
        $content .= "<a href='#extcontent" . $this->order . "'></a>";
        
        if($this->content == NULL)
        {
            global $wpdb;
            
            $sql = $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . self::$table . "` WHERE `post_id` = %d AND `order` = %d", $this->post_id, $this->order);
            $row = $wpdb->get_row($sql);
            
            if($row)
            {
                $this->content = $row->content;
                $this->date = $row->date;
                $this->url = $row->url;
            }
        }
        // Section Content
        $content .= $this->content;
        
        // Section Comment
        $content .= "<!-- Copied: " . date("m/d/Y H:i:s", strtotime($this->date)) . "-->\n<!-- URL: " . $this->url . " -->";
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
        
        if(count($matches) == 0)
            return FALSE;
        
        $name = $this->_tag_name($tag);
        
        if(in_array($name, self::$_html_paired_tags) OR in_array($name, self::$_html_unpaired_tags))
            return TRUE;
        
        return FALSE;
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
    /**
     * Is Tag Close
     * 
     */
    private function _is_tag_close($tag)
    {
        $name = $this->_tag_name($tag);
        $pattern = '/\/' . $name . '>$/';
        
        preg_match($pattern, $tag, $matches);
        
        return (boolean) count($matches);
    }
}