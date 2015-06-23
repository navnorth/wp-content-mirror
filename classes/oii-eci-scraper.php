<?php
require_once(OII_ECI_PATH . "includes/oii-eci-settings-page.php");
require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

class OII_ECI_Scraper {
    private $_option = NULL;
    
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
        
        foreach($this->_option["regex"] AS &$regex)
        {
            foreach($regex AS &$ex)
                $ex = htmlspecialchars_decode($ex);
        }
    }
    
    public function enqueue()
    {
        $pages = array();
        
        foreach(OII_ECI_Metabox::$template AS $template)
        {
            $pages = array_merge($pages, get_pages(
                    array(
                        "meta_key" => "_wp_page_template",
                        "meta_value" => $template
                    )
                )
            );
        }
    
        if(count($pages))
        {
            foreach($pages AS $page)
            {
                // print_r(OII_ECI_External_Content::get_by_post_id($page->ID));
            }
        }
    }
}