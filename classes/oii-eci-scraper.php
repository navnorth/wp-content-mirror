<?php
require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

class OII_ECI_Scraper {
    public function __construct()
    {
        
    }
    
    public static function run()
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
                    $external_content->update();
            }
        }
    }
}