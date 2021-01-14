<?php
require_once(OII_ECI_PATH . "/includes/oii-eci-settings-page.php");

class OII_ECI_Metabox {
    public static $template = array("page-templates/program-template.php", "page-templates/awards-template.php", "page-templates/theme-template.php");

    public static $meta_key = "_eci_page_external_contents";

    private $_debug;

    public $id = "eci-metabox";
    /**
     * Class Constructor
     * Description
     */
    public function __construct()
    {
        $_option = get_option(OII_ECI_Settings_Page::$option_name);
        $this->_debug = isset($_option['debug'])? $_option['debug']: false;
        add_action("load-post.php", array($this, "setup"));
        add_action("load-post-new.php", array($this, "setup"));
        add_action("wp_ajax_refresh_external_content", array($this, "refresh_external_content"));
        add_action("wp_ajax_migrate_external_content", array($this, "migrate_external_content"));
    }
    /**
     * Setup Metabox
     * Description
     */
    public function setup()
    {
        add_action("add_meta_boxes", array($this, "create"));
        add_action("save_post", array($this, "save"), 10, 2);
    }
    /**
     * Create Metabox
     * Description
     */
    public function create()
    {
        /**
         * Add Metabox
         * @code begin
         */
        add_meta_box(
            $this->id, // id
            __("External Content", OII_ECI_PLUGIN_DOMAIN), // title
            array($this, "display"), // callback
            "page", // screen
            "normal", // context
            "high" // priority
        );
         /**
         * Add Metabox
         * @code end
         */

        /**
         * Show or Hide Metabox
         * @code begin
         */
        global $post;

        $page_template = get_post_meta($post->ID, "_wp_page_template", TRUE);

        if(in_array($page_template, self::$template) == FALSE)
            add_filter("postbox_classes_page_" . $this->id, array($this, "hidden"));
        /**
         * Show or Hide Metabox
         * @code end
         */


    }
    /**
     * Metabox Hidden
     * Description
     *
     * @return array
     */
    public function hidden($classes)
    {
        $classes[] = "hidden";
        return $classes;
    }
    /**
     * Display Metabox
     * Description
     */
    public function display($post)
    {
        // Register and Enqueue Script
        wp_register_script("oii-eci-page-script", OII_ECI_URL . "js/oii-eci-page-script.js", array("jquery"));
        wp_enqueue_script("oii-eci-page-script");

        /**
         * Register and Enqueue Styles
         * @code begin
         */
        wp_register_style("oii-eci-grid-style", OII_ECI_URL . "css/oii-eci-grid-style.css");
        wp_enqueue_style("oii-eci-grid-style");

        wp_register_style("oii-eci-page-style", OII_ECI_URL . "css/oii-eci-page-style.css");
        wp_enqueue_style("oii-eci-page-style");
        /**
         * Register and Enqueue Styles
         * @code end
         */

        //include_once(OII_ECI_PATH . "/classes/oii-eci-scraper.php");
        //OII_ECI_Scraper::run();

        include_once(OII_ECI_PATH . "oii-eci-template/oii-eci-metabox.php");
    }
    /**
     * Save Post Meta
     * Description
     *
     * @param integer $post_id The post ID.
     * @param object $post The post object.
     */
    public function save($post_id, $post)
    {
        if("page" == $post->post_type)
        {
            if(isset($_POST["external-content-url"]) AND isset($_POST["external-content-header"]) AND isset($_POST["external-content-start"]) AND isset($_POST["external-content-end"]))
            {
                require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

                $external_contents = array();

                $id = time();

                foreach($_REQUEST["external-content-url"] AS $key => $content)
                {
                    $external_content = new OII_ECI_External_Content();

                    $external_content->order = $key + 1;
                    $external_content->header = sanitize_text_field($_REQUEST["external-content-header"][$key]);
                    $external_content->url = sanitize_text_field($content);

                    $external_content->start = esc_html($_REQUEST["external-content-start"][$key]);
                    $external_content->end = esc_html($_REQUEST["external-content-end"][$key]);
                    $external_content->active = $_REQUEST["external-content-active"][$key]? true: false;

                    $external_content->id = ($_REQUEST["external-content-id"][$key]) ? (int) $_REQUEST["external-content-id"][$key] : $id;

                    if($_REQUEST["external-content-id"][$key] == 0)
                        $id++;

                    $external_contents[] = $external_content->as_postmeta();
                }

                update_post_meta($post_id, self::$meta_key, $external_contents);
            } else {
                delete_post_meta($post_id, self::$meta_key);
            }
        }
    }
    /**
     * Get External Contents
     * Description
     *
     * @param integer $page_id The page ID.
     *
     * @return array
     */
    public function get_external_contents($page_id)
    {
        require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

        $external_contents = OII_ECI_External_Content::get_by_post_id($page_id);

        if(count($external_contents))
            return $external_contents;

        return array(new stdClass());
    }
    /**
     * Refresh External Content
     * Description
     */
    public function refresh_external_content()
    {
        require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");

        $post_id = (int) $_POST["post_id"];
        $id = (int) $_POST["id"];

        if($this->_debug==1)
            error_log( 'running OII ECI Scraper on manual refresh on post_id ' . $post_id );

        $external_contents = OII_ECI_External_Content::get_by_post_id($post_id);

        if(count($external_contents))
        {
            $new_external_contents = array();
            $new_external_content = NULL;

            foreach($external_contents AS $external_content)
            {
                if($external_content->id == $id)
                {
                    //Check if config is active
                    if ($external_content->active==true) {
                        $external_content->url = sanitize_text_field($_POST["url"]);
                        $external_content->header = sanitize_text_field($_POST["header"]);
                        $external_content->start = esc_html($_POST["open_tag"]);
                        $external_content->end = esc_html($_POST["close_tag"]);
    
                        $new_external_content = $external_content;
                    }
                }

                array_push($new_external_contents, $external_content->as_postmeta());
            }
            
            if(count($new_external_contents))
                update_post_meta($post_id, self::$meta_key, $new_external_contents);
            
            if($new_external_content)
            {
                try
                {
                    if ($new_external_content->active==true){
                        $new_external_content->update();
                        $response = array("status" => "success", "success" => array("message" => "Section content is now updated."));
                    } 
                }
                catch(Exception $e)
                {
                    $response = array("status" => "error", "error" => array("message" => $e->getMessage()));
                }
            } else {
                        $response = array("status" => "error", "error" => array("message" => "config is inactive"));
            }
        }
        else
        {
            $response = array("status" => "error", "error" => array("message" => "External Content Not Found."));
        }

        echo json_encode($response);

        wp_die();
    }
    
    
    /**
     * Migrate External Content
     * Description
     */
    public function migrate_external_content()
    {
        require_once(OII_ECI_PATH . "classes/oii-eci-helper.php");
        $oii_eci_helper = new OII_ECI_Helper();
        
        $post_id = (int) $_POST["post_id"];
        
        if($this->_debug==1)
            error_log( 'migrating external content on post_id ' . $post_id );

        try {
            $mSuccess = $oii_eci_helper->migrate_external_content($post_id);
            if ($mSuccess==true)
                $response = array("status" => "success", "success" => array("message" => "Page migration complete!"));
            else
                $response = array("status" => "error", "error" => array("message" => implode(",",$mSuccess)));
        } catch(Exception $e) {
            $response = array("status" => "error", "error" => array("message" => $e->getMessage()));
        }
        
        echo json_encode($response);
            
        wp_die();
    }
}
