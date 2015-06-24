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
    // @deprecated
    public function get_option()
    {
        $this->_option = get_option(OII_ECI_Settings_Page::$option_name);
    }
    
    public function run()
    {
        $pages = array();
        /**
         * Get Pages
         * @code begin
         */
        foreach(OII_ECI_Metabox::$template AS $_template)
        {
            $pages = array_merge($pages, get_posts(
                    array(
                        "post_type" => "page",
                        "meta_key" => "_wp_page_template",
                        "meta_value" => $_template
                    )
                )
            );
        }
        /**
         * Get Pages
         * @code end
         */
        
        if(count($pages))
        {
            foreach($pages AS $page)
            {
                $external_contents = OII_ECI_External_Content::get_by_post_id($page->ID);
                
                foreach($external_contents AS $key => $external_content)
                {
                    //$external_content->format();
                    
                    $external_content->update($page->ID, $key + 1);
                }
            }
        }
    }
}