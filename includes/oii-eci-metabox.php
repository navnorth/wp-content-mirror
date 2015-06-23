<?php
class OII_ECI_Metabox {
    public static $template = array("page-templates/program-template.php", "page-templates/theme-template.php");
    
    public static $meta_key = "_eci_page_external_contents";
    
    public $id = "eci-metabox";
    /**
     * Class Constructor
     * Description
     */
    public function __construct()
    {
        add_action("load-post.php", array($this, "setup"));
        add_action("load-post-new.php", array($this, "setup"));
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
        
        require_once(OII_ECI_PATH . "classes/oii-eci-scraper.php");
        
        $scraper = new OII_ECI_Scraper();
        
        $scraper->run();
        
        include_once(OII_ECI_PATH . "oii-eci-template/oii-eci-metabox.php");
    }
    /**
     * Save Post Meta
     * Description
     *
     * @param integer $id The post ID.
     * @param object $post The post object.
     */
    public function save($id, $post)
    {
        if("page" == $post->post_type)
        {
            if(isset($_POST["external-content-url"]) AND isset($_POST["external-content-header"]) AND isset($_POST["external-content-start"]) AND isset($_POST["external-content-end"]))
            {
                require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");
                
                $external_contents = array();
                
                foreach($_REQUEST["external-content-url"] AS $key => $content)
                {
                    $external_content = new OII_ECI_External_Content();
                    
                    $external_content->order = $key + 1;
                    $external_content->header = sanitize_text_field($_REQUEST["external-content-header"][$key]);
                    $external_content->url = sanitize_text_field($content);
                    
                    $external_content->start = esc_html($_REQUEST["external-content-start"][$key]);
                    $external_content->end = esc_html($_REQUEST["external-content-end"][$key]);
                    
                    $external_contents[] = $external_content->as_postmeta();
                }
                
                update_post_meta($id, self::$meta_key, $external_contents);
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
}