<?php
require_once(OII_ECI_PATH . "includes/oii-eci-settings-page.php");
require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

class OII_ECI_Scraper {
    private $_option = NULL;
    
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
    
    public function __construct()
    {
        $this->get_option();
    }
    
    public function schedule()
    {
        echo $this->_option["schedule"];
    }
    
    public function get_option()
    {
        $this->_option = get_option(OII_ECI_Settings_Page::$_option_name);
    }
    
    public function run()
    {
        $pages = array();
        
        foreach(OII_ECI_Metabox::$template AS $_template)
        {
            $pages = array_merge($pages, get_pages(
                    array(
                        "meta_key" => "_wp_page_template",
                        "meta_value" => $_template
                    )
                )
            );
        }
    
        if(count($pages))
        {
            foreach($pages AS $page)
            {
                $external_contents = OII_ECI_External_Content::get_by_post_id($page->ID);
                
                foreach($external_contents AS $key => $external_content)
                {
                    $external_content->update($page->ID, $key + 1);
                }
            }
        }
    }
    
    private function _html_tag($tag)
    {
        $pattern = "/\w+/";
        
        preg_match($pattern, $tag, $matches);
        
        return array_pop($matches);
    }
    
    private function _is_html_tag($tag)
    {
        $pattern = "/<[^!][^<>]+>/";
        preg_match($pattern, $tag, $matches);
        
        return (boolean) count($matches);
    }
    
    private function _is_html_comment($tag)
    {
        $pattern = "/<!--(.*?)-->/";
        preg_match($pattern, $tag, $matches);
        
        return (boolean) count($matches);
    }
    /**
     * @deprecated
     * Extract
     * Extract content from URL
     *
     * @param string $url The URL.
     * @param string $start_code The start code.
     * @param string $stop_code The stop code.
     *
     * @return string The extracted content.
     */
    public function extract($url = NULL, $start_code = NULL, $stop_code = NULL)
    {
        $html = file_get_contents($url);
        
        if($html === FALSE)
            return NULL;
        
        $html = mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8");
        
        // Extract by Comment Tag
        if($this->_is_html_comment($start_code) AND $this->_is_html_comment($stop_code))
        {
            $start = strpos($html, $start_code);
            
            if($start === FALSE)
                return NULL;
            
            $start = $start + strlen($start_code);
            
            $sub = substr($html, $start);
            $stop = strpos($sub, $stop_code);
            
            if($stop === FALSE)
                return NULL;
            
            return substr($html, $start, $stop);
        }
        // Extract by HTML Tag 
        else if($this->_is_html_tag($start_code) AND $this->_is_html_tag($stop_code))
        {
            $start = strpos($html, $start_code);
            
            if($start === FALSE)
                return NULL;
            
            $start = $start + strlen($start_code);
            
            $open = $close = 0;
            
            $sub = substr($html, $start);
            
            // Todo: Evaluate End Code
            
            $stop = strpos($sub, $stop_code) + strlen($stop_code);
            $close = 1;
            
            $pattern = '/(<' . $this->_html_tag($start_code) . ')/';
            preg_match_all($pattern, substr($sub, 0, $stop), $matches);
            
            $open = $open + count($matches[0]);
            
            $_start = $_stop = $stop;
            
            while($open > $close)
            {
                $_stop = $_stop + strpos(substr($sub, $_start), $stop_code) + strlen($stop_code);
                
                preg_match_all($pattern, substr($sub, $_start, $_stop), $matches);
                
                $open = $open + count($matches[0]);
                $_start = $_stop;
                
                $close++;
            }
            
            $_stop = $_stop - strlen($stop_code);
            
            return substr($sub, 0, $_stop);
        }
        
        return NULL;
    }
    /**
     * @deprecated
     * Format
     * Format extracted Content
     *
     * @param string $content The raw content.
     * 
     * return string The formatted content.
     */
    public function format($content = NULL)
    {
        
    }
}