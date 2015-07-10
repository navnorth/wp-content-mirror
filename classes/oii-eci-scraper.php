<?php
require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

$_debug = TRUE;

class OII_ECI_Scraper {
    public function __construct()
    {

    }

    public static function run()
    {
        if($_debug)
            error_log( 'running OII ECI Scraper start' );

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

                if($_debug)
                    error_log( 'running OII ECI Scraper for page ' . $page->ID );

                foreach($external_contents AS $key => $external_content)
                {
                    try
                    {
                        $external_content->update();
                    }
                    catch(Exception $e)
                    {

                    }
                }
            }
        }
        if($_debug)
            error_log( 'running OII ECI Scraper end' );

    }
}
