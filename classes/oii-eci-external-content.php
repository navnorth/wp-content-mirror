<?php
class OII_ECI_External_Content {    
    public static $table = "external_contents";
    
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
    
    public $active = true;
    
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
     * Get By Nth
     * Description
     *
     * @param integer $post_id The post ID.
     * @param integer $nth The nth.
     *
     * @return null|object
     */
    public static function get_by_id($post_id = 0, $id = 0)
    {
        require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
        
        $post_meta = get_post_meta($post_id, OII_ECI_Metabox::$meta_key, TRUE);
        
        if(is_array($post_meta) == FALSE)
            return NULL;
        
        foreach($post_meta AS $meta)
        {
            if($meta["id"] == $id)
            {
                $meta = array_merge($meta, array("post_id" => $post_id));
                return new self($meta);
            }
        }
        
        return NULL;
    }
    /**
     * External Content as Postmeta
     * Description
     *
     * @return array The postmeta.
     */
    public function as_postmeta()
    {
        $postmeta = array_fill_keys(array("id", "order", "header", "url", "start", "end", "active"), NULL);
        
        foreach(array_keys($postmeta) AS $meta)
        {
            if(property_exists($this, $meta))
                $postmeta[$meta] = $this->$meta;
        }
        
        return $postmeta;
    }
    /**
     * Update
     * Update external content in database.
     *
     * @throws Exception If Post or external content was not found.
     */
    public function update()
    {
        if($this->id AND $this->post_id)
        {
            try
            {
                $this->content = $this->extract();
            }
            catch(Exception $e)
            {
                throw $e;
            }
            
            global $wpdb;
            $sql = $wpdb->prepare("SELECT `id` FROM `" . $wpdb->prefix . self::$table . "` WHERE `post_id` = %d AND `external_content_id` = %d", $this->post_id, $this->id);
            
            if($wpdb->get_row($sql))
                $sql = $wpdb->prepare("UPDATE `" . $wpdb->prefix . self::$table . "` SET `content` = %s, `url` = %s, `date` = NOW() WHERE `post_id` = %d AND `external_content_id` = %d", $this->content, $this->url, $this->post_id, $this->id);
            else
                $sql = $wpdb->prepare("INSERT INTO `" . $wpdb->prefix . self::$table . "` (`post_id`, `external_content_id`, `content`, `url`, `date`) VALUES (%d, %d, %s, %s, NOW())", $this->post_id, $this->id, $this->content, $this->url);
            
            $wpdb->query($sql);
        }
        else
        {
            throw new Exception("Post/external content not found.");
        }
    }
    /**
     * Update
     * Update external content in database.
     *
     * @param integer $post_id The post ID.
     * @param integer $order The external content order.
     * @deprecated
     */
    private function _deprecated_update($post_id = 0, $order = 1)
    {
        global $wpdb;
        
        $this->post_id = $post_id;
        
        $sql = $wpdb->prepare("SELECT `id` FROM `" . $wpdb->prefix . self::$table . "` WHERE `post_id` = %d AND `order` = %d", $this->post_id, $order);
        
        try {
            $this->content = $this->extract();
            
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
        
        if($wpdb->get_row($sql))
        {
            // Update
            $sql = $wpdb->prepare("UPDATE `" . $wpdb->prefix . self::$table . "` SET `content` = %s, `url` = %s, `date` = NOW() WHERE `post_id` = %d AND `order` = %d", $this->content, $this->url, $this->post_id, $order);
            $wpdb->query($sql);
        }
        else
        {
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
     * @throws Exception If there were no contents retrieved.
     */
    public function extract()
    {
        $html = @file_get_contents($this->url);
        
        $html = $this->curl_extract($this->url);
        
        if($html === FALSE){
            $html = $this->curl_extract($this->url);
            if (!$html || strlen(trim($html))<=0)
                throw new Exception("Unable to retrieve contents from " . $this->url);
        }
        
        $html = mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8");
        $html = trim(preg_replace("/>(\\s|\\n|\\r)+</", "><", $html));
        
        $open_tag = stripcslashes(htmlspecialchars_decode($this->start, ENT_QUOTES));
        $close_tag = stripcslashes(htmlspecialchars_decode($this->end, ENT_QUOTES));
        
        $start = strpos($html, $open_tag);
        
        if($start === FALSE)
            return NULL;
        
        $start = $start + strlen($open_tag);
        
        $sub = substr($html, $start);
        $stop = strpos($sub, $close_tag);
        
        if($stop === FALSE)
            return NULL;
        
        // Cleanup Relative Links
        $html = $this->_replace_relative_links(substr($html, $start, $stop), $this->url);
        
        return $this->_apply_format($html);
    }
    
    /**
     * Checks for 404, 302, and 301 HTTP Response code
     *
     * @return boolean True/False
     **/
    public function check_url_status(){
        $headers = get_headers($this->url);
        $status = $headers[0];
        
        if (strpos($status,"302") || strpos($status,"301") || strpos($status,"404"))
            return false;
        else
            return true;
    }
    
    /**
     * Extract
     * Extract content from URL
     *
     * @return string The extracted content.
     * @throws Exception If there were no contents retrieved.
     * @deprecated
     */
    private function _deprecated_extract()
    {
        $html = @file_get_contents($this->url);
        
        if($html === FALSE)
            throw new Exception("Unable to retrieve contents from " . $this->url);
        
        $html = mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8");
        $html = trim(preg_replace('/(\\n)+/', NULL, $html));
        
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
    public function _apply_format($content = NULL)
    {
        require_once(OII_ECI_PATH . "includes/oii-eci-settings-page.php");
        $option = get_option(OII_ECI_Settings_Page::$option_name);
        
        foreach($option["format"] AS $format)
            $content = $this->_replace_content($content, $format["replace"], $format["with"]);
        
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
    private function _replace_content($content = NULL, $replace = NULL, $with = NULL)
    {
        $replace = htmlspecialchars_decode($replace, ENT_QUOTES);
        $with = htmlspecialchars_decode($with, ENT_QUOTES);
        
        require_once(OII_ECI_PATH . "classes/oii-eci-settings-format.php");
        $settings_format = new OII_ECI_Settings_Format();
        
        $replace_type = $settings_format->type("replace", $replace);
        $change = FALSE;
        
        // Paired HTML Tag
        if("paired-attribute" == $replace_type OR "paired" == $replace_type)
        {
            list($replace_open, $replace_close) = explode("(.*)", $replace);
            list($with_open, $with_close) = explode("\\1", $with);
        
            $change = (strpos($content, $replace_open) === FALSE) ? FALSE : TRUE;
        }
        // Single HTML Tag
        else if("single-attribute-any" == $replace_type)
        {
            if($with)
            {
                // Todo
            }
            else
            {
                $search_pattern = "/" . preg_replace("/\s?\(\.\*\)(\s?\/)?>/", "[^<>]*>", preg_replace("/\/[^>]/", "\\\\$0", $replace)) . "/";
                preg_match($search_pattern, $content, $match_single_attribute_any);
                
                $change = (boolean) count($match_single_attribute_any);
            }
        }
        
        while($change)
        {
            // Paired HTML Tag
            if("paired" == $replace_type OR "paired-attribute" == $replace_type)
            {
                $o = $this->_get_tag_offset($content, $replace_open);
            
                $content = substr_replace($content, $with_close, $o["close"]["start"], $o["close"]["boundary"]);
		$content = substr_replace($content, $with_open, $o["open"]["start"], $o["open"]["boundary"]);
            
                $change = (strpos($content, $replace_open) === FALSE) ? FALSE : TRUE;
            }
            // Single HTML Tag
            else if("single-attribute-any" == $replace_type)
            {
                if($with)
                {
                    // Todo
                }
                else
                {
                    $content = preg_replace($search_pattern, $with, $content);
                    $change = FALSE;
                }
            }
            /**
            $o = $this->_get_tag_offset($content, $replace);
            
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
            
            $again = (strpos($content, $replace) === FALSE) ? FALSE : TRUE;
            */
        }
        
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
        if ($this->active!==false) {
            // Section Header
            $content = ($this->header) ? "<h2>" . $this->header . "</h2>" : NULL;
            // Section Anchor
            $content .= "<a id='ext-content-" . $this->order . "'></a>";
            
            if($this->content == NULL)
            {
                global $wpdb;
                
                $sql = $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . self::$table . "` WHERE `post_id` = %d AND `external_content_id` = %d", $this->post_id, $this->id);
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
        $pattern = "/^<!--(.*?)-->/";
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
    /**
     *
     * Replace Relative Links
     *
     **/
    public function _replace_relative_links($html, $repUrl){
        
        $match = array();
        
        //check for href links
        $url   = preg_match_all('/href="([^\s"]+)/', $html, $match);
        
        $base_urls = parse_url($repUrl);
        
        //replace base url
        $base_rep_url = $base_urls['scheme']."://".$base_urls['host'];
        
        //used to hold all links already replaced
        $matched_urls = array();
        
        if(count($match[1]))
        {
            for($j=0; $j<count($match[1]); $j++)
            {
                if ((strpos($match[1][$j],"http")===false) && (strpos($match[1][$j],"mailto")===false)){
                    $extUrlPath = substr($this->url, 0, strrpos($this->url, '/') + 1);
                    
                    if (strpos($match[1][$j], '/')===0){
                        if (!in_array($match[1][$j], $matched_urls))
                            $html = str_replace($match[1][$j], $base_rep_url.$match[1][$j], $html);
                    }
                    else {
                        if (!in_array($match[1][$j], $matched_urls))
                            $html = str_replace($match[1][$j], $extUrlPath.$match[1][$j], $html);
                    }
                } else {
                    if (!in_array($match[1][$j], $matched_urls))
                        $html = str_replace($match[1][$j],$this->_replace_internal_link($match[1][$j]),$html);
                }
                $matched_urls[] = $match[1][$j];
            }
        }
        return $html;
    }
    
    /**
     *
     * Replace Internal Links
     *
     **/
    private function _replace_internal_link($url){
        global $wpdb;
        
        // query external content table if url exists and return post id
        $sql = $wpdb->prepare("SELECT post_id FROM ".$wpdb->prefix.self::$table." WHERE url='%s' LIMIT 0,1", $url);
        $row = $wpdb->get_row($sql);
        
        if ($row) {
            // get url based on post id
            $url = get_permalink($row->post_id);
        }
        
        return $url;
        
    }
    
    private function curl_extract($url) {
        if (!function_exists('curl_init')){ 
            die('CURL is not installed!');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        $output = curl_exec($ch);
        curl_close($ch);
        
        return $output;
    }
}