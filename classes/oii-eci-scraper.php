<?php
require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");
require_once(OII_ECI_PATH . "/includes/oii-eci-settings-page.php");

class OII_ECI_Scraper {
    private $_debug;

    public function __construct()
    {

    }
    /**
     * Run
     * Description
     */
    public static function run()
    {
        // Get Debug Mode from Option
        $_option = get_option(OII_ECI_Settings_Page::$option_name);
        $_debug = $_option["debug"];

        if($_debug == 1)
            error_log("running OII ECI Scraper start");

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
                        "meta_value" => $_template,
                        'posts_per_page' => -1
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

                if($_debug == 1)
                    error_log("running OII ECI Scraper for page " . $page->ID);

                foreach($external_contents AS $key => $external_content)
                {
                    if($_debug == 1 && empty($external_content->url))
                       error_log("Skipping scraper due to empty URL for page " . $page->ID);
                    else {
                        try
                        {
                            if ($external_content->active==true) {
                                $external_content->update();
                            }
                        }
                        catch(Exception $e)
                        {
                            error_log($e->getMessage());
                        }
                    }
                }
            }
        }

        if($_debug == 1)
            error_log("running OII ECI Scraper end");
    }
}
