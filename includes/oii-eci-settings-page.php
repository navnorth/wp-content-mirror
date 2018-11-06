<?php
/**
 * OII ECI Settings Page Class
 * Description
 *
 * @version 1.0.1 2015-08-03
 */

require_once(OII_ECI_PATH . "/classes/oii-eci-scraper.php");

class OII_ECI_Settings_Page {
    private $_option;

    private $_debug = FALSE;

    public static $cron_action_hook = "external_content_importer_cron";

    private static $_menu_slug = "oii-eci-admin";

    public static $option_name = "oii_eci_settings";
    private static $_option_group = "oii_eci_opotion_group";

    private static $_setting_title = "OII External Content Importer";

    /**
     * Class Constructor
     * Description
     */
    public function __construct()
    {
        add_action("admin_menu", array($this, "add_plugin_page"));
        add_action("admin_init", array($this, "page_init"));

        add_action("wp_ajax_refresh_all_external_contents", array($this, "refresh_all_external_contents"));
        add_action("wp_ajax_migrate_all_external_contents", array($this, "migrate_all_external_contents"));
        add_action("wp_ajax_reset_all_external_contents", array($this, "_reset_migrate_count"));
    }
    /**
     * Add Plugin Page
     * Description
     */
    public function add_plugin_page()
    {
        $hook = add_options_page(
            "Settings Admin", // page_title
            self::$_setting_title, // menu_title
            "manage_options", // capability
            self::$_menu_slug, // menu_slug
            array($this, "create_admin_page") // callback
        );

        add_action("load-" . $hook, array($this, "setup_cron"));
    }
    /**
     * Create Admin Page
     * Description
     */
    public function create_admin_page()
    {
        $this->_option = get_option(self::$option_name);

        // trying to debug error logging.
        // error_log("OII External Content Importer - settings page loaded.");

        if($this->_debug)
        {
        require_once(OII_ECI_PATH . "classes/oii-eci-settings-format.php");
            $format = new OII_ECI_Settings_Format();

            foreach($this->_option["format"] AS $expression)
            {
                $r = htmlspecialchars_decode($expression["replace"]);
                $w = htmlspecialchars_decode($expression["with"]);
                echo "<b>Replace</b>: " . $format->type("replace", $r) . "<br />";
                foreach(explode("(.*)", $r) AS $key => $_r)
                {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . ($key == 0 ? "<b>Open</b>: " : " <b>Close</b>: ") . htmlspecialchars($_r). "<br />";
                }

                echo "<b>With</b>: " . $format->type("with", $w) . "<br />";
                foreach(explode("\\1", $w) AS $key => $_w)
                {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . ($key == 0 ? "<b>Open</b>: " : " <b>Close</b>: ") . htmlspecialchars($_w). "<br />";
                }
                echo "<br />";
            }
        } ?>
        <div class="wrap">
            <h2><?php echo self::$_setting_title; ?></h2>
            <form method="post" action="options.php">
            <?php
                settings_fields(self::$_option_group);
                do_settings_sections(self::$_menu_slug);
                submit_button();
            ?>
            </form>
        </div>

        <div class="plugin-footer">
            <div class="plugin-info"><?php echo OII_ECI_PLUGIN_NAME . " " . OII_ECI_VERSION .""; ?></div>
            <div class="plugin-link"><a href='<?php echo OII_ECI_PLUGIN_INFO ?>' target='_blank'>More info</a></div>
            <div class="clear"></div>
        </div>
        <?php
    }
    /**
     * Page Initialize
     * Description
     */
    public function page_init()
    {
        register_setting(
            self::$_option_group,
            self::$option_name,
            array($this, "sanitize")
        );

        // Create Settings Section
        add_settings_section(
            "setting_section_id",
            "",
            array($this, "print_section_description"),
            self::$_menu_slug
        );

        // Create Settings Field on Section
        add_settings_field(
            "format",
            "External Content Format",
            array($this, "format_callback"),
            self::$_menu_slug,
            "setting_section_id"
        );

        // Create Schedule Setting on Section
        add_settings_field(
            "schedule",
            "Schedule",
            array($this, "schedule_callback"),
            self::$_menu_slug,
            "setting_section_id"
        );

        // Create Debug Setting on Section
        add_settings_field(
            "debug",
            "Debug Mode",
            array($this, "debug_callback"),
            self::$_menu_slug,
            "setting_section_id"
        );

        add_settings_field(
            "refresh",
            "",
            array($this, "refresh_all_callback"),
            self::$_menu_slug,
            "setting_section_id"
        );
        
         add_settings_field(
            "migrate",
            "",
            array($this, "migrate_all_callback"),
            self::$_menu_slug,
            "setting_section_id"
        );

        // Register and Enqueue External Content Importer Script
        wp_register_script("oii-eci-settings-format-script", OII_ECI_URL . "js/oii-eci-settings-format-script.js", array("jquery"));
        wp_enqueue_script("oii-eci-settings-format-script");

        // Register and Enqueue External Content Importer Script
        wp_register_script("oii-eci-settings-script", OII_ECI_URL . "js/oii-eci-settings-script.js", array("jquery", "oii-eci-settings-format-script"));
        wp_enqueue_script("oii-eci-settings-script");

        wp_register_style("oii-eci-settings-style", OII_ECI_URL . "css/oii-eci-settings-style.css");
        wp_enqueue_style("oii-eci-settings-style");
    }
    /**
     * Sanitize
     * Description
     */
    public function sanitize($input)
    {

        $new_input = array();

        if( isset($input["replace"]) AND isset($input["with"]))
        {
            foreach($input["replace"] AS $key => $replace)
                $new_input["format"][] = array(
                    "replace" => esc_html(trim($replace)),
                    "with" => esc_html(trim($input["with"][$key]))
                );
        }

        if(isset($input["schedule"]))
            $new_input["schedule"] = $input["schedule"];

        if (isset($input["debug"]))
            $new_input["debug"] = $input["debug"];
        else
            $new_input["debug"] = 0;

        return $new_input;

    }
    /**
     * Print Section Description
     * Description
     */
    public function print_section_description()
    {
        //echo "Enter your settings below:";
    }
    /**
     * RegEx Callback
     * Description
     */
    public function format_callback()
    {
        $format = (is_array($this->_option["format"])) ? $this->_option["format"] : array(array());

        foreach($format AS $key => $expression)
        {
            echo "<div class='regex'" . ($key ? " style='margin-top: 15px'" : "") . ">";
                echo "Replace <input type='text' class='form-element regex-replace' name='" . self::$option_name . "[replace][]' value='" . $expression["replace"] . "' /> with <input type='text' class='form-element regex-with' name='" . self::$option_name . "[with][]' value='" . $expression["with"] . "' />";

            if($key == 0)
                echo "<button type='button' data-name='" . self::$option_name . "' id='oii-eci-new-regex' class='button'>New</button>";
            else
                echo "<a href='#' style='color: #555; text-decoration: none'><span class='delete-regex dashicons dashicons-trash' style='vertical-align: text-bottom'></span></a>";

            echo "</div>";
        }
        echo "<p class='description'>
            <span style='display:block; font-weight: bold;'>Replace:</span>"
            . "<div><span class='format-replace'>" . htmlspecialchars("<h1>(.*)</h1>") . "</span> <span style='font-weight: bold; font-style: italic;'>or</span></div>"
            . "<div><span class='format-replace'>" . htmlspecialchars("<span class=\"heading-level-1\">(.*)</span>") . "</span> <span style='font-weight: bold; font-style: italic;'>or</span></div>"
            . "<div><span class='format-replace'>" . htmlspecialchars("<h2 class=\"section-title\" readonly>(.*)</h2>") . "</span> <span style='font-weight: bold; font-style: italic;'>or</span></div>"
            . "<div><span class='format-replace'>" . htmlspecialchars("<label for=\"email\" class=\"input-label\">(.*)</label>") . "</span></div>"
            . "<div><span class='format-replace'>" . htmlspecialchars("<img src=\"/images/brand-logo.png\" (.*) />") . "</span></div>"
            . "<span style='display:block; font-weight: bold;'>With:</span>"
            . "<div><span class='format-replace'>" . htmlspecialchars("<h2>\\1</h2>") . "</span> <span style='font-weight: bold; font-style: italic;'>or</span></div>"
            . "<div><span class='format-replace'>" . htmlspecialchars("<section>\\1</section>") . "</span></div>"
        . "</p>";
    }
    /**
     * Schedule Callback
     * Description
     */
    public function schedule_callback()
    {
        $next_sched = wp_next_scheduled( self::$cron_action_hook );

        echo "<select id='schedule' class='form-element' name='" . self::$option_name ."[schedule]'>";

        echo "<option value=''>&nbsp;</option>";
        foreach(array("hourly" => "Hourly", "twicedaily" => "Twice Daily", "daily" => "Daily") AS $key => $schedule)
            echo "<option value='" . $key . "'" . ($this->_option["schedule"] == $key ? " selected" : "" ) . ">" . $schedule . "</option>";

        echo "</select>";
        echo "<p class='description'>The schedule for updating content.<br /><b>Next Scheduled:</b> " . date("Y-m-d H:i:s", $next_sched) . "</p>";
    }
    /**
     * Debug Mode Callback
     * Description
     */
    public function debug_callback()
    {
        $checked = ($this->_option['debug']==1)?"checked":"";
        echo "<input type='checkbox' id='".self::$option_name."[debug]' name='".self::$option_name."[debug]' value='1' ".$checked." />";
        echo "<p class='description'>Check this to enable debugging.</p>";
    }
    /**
     * Refresh Callback
     * Description
     *
     * @since 1.0.1
     */
    public function refresh_all_callback()
    {
        echo "<div style='display: inline-block;'>
            <div class='updated notice hidden'>
                <p><strong></strong></p>
            </div>
            <button type='button' class='button' id='refresh-all-external-contents' data-loading-text='Refreshing...'>Refresh All Contents Now</button>
            <span class='spinner' style='float: none;'></span>

            <p class='description' data-default-text='Refresh all existing external contents.'>Refresh all existing external contents.</p>
        </div>";

    }
    
    /**
     * Refresh Callback
     * Description
     *
     * @since 1.0.2
     */
    public function migrate_all_callback()
    {
        $batch_msg = "Migrate all external contents.";
        $page_count = 0;
        $disabled = "";
        
        $pages = $this->get_pages_for_migration();
        $page_count = $pages->found_posts; // Get total pages that can be migrated.
        $batch_count = get_option("oii_migrate_count"); // Get processed migrated pages
        
        if ($batch_count) {
            if ($batch_count>=$page_count) {
                $disabled = "disabled";
                $batch_msg = "All external contents have been migrated.";
            } else
                $batch_msg = $count." pages already migrated. Migrate next 50 pages.";
        }
        
        echo "<div style='display: inline-block;'>
            <div class='updated notice hidden'>
                <p><strong></strong></p>
            </div>
            <button type='button' class='button' id='migrate-all-external-contents' data-loading-text='Migrating...' ".$disabled.">Migrate All Contents</button><button type='button' class='button hidden' id='reset-all-external-contents'>Reset Migrate Count</button>
            <span class='spinner' style='float: none;'></span>

            <p class='description' data-default-text='Migrating all external contents.'>".$batch_msg."</p>
        </div>";

    }
    
    /**
     * Refresh All External Contents
     * Description
     */
    public function refresh_all_external_contents()
    {
        OII_ECI_Scraper::run();

        wp_die();
    }
    
    /**
     * Migrate All External Contents
     */
    public function migrate_all_external_contents() {
        
        require_once(OII_ECI_PATH . "/classes/oii-eci-helper.php");
        
        if ($count=get_option("oii_migrate_count")){
            $pages = $this->get_pages_for_migration($count);
        } else {
            $pages = $this->get_pages_for_migration();
        }
        
        $index = 0;
        if ($pages->have_posts()){
            
            $oii_eci_helper = new OII_ECI_Helper();
            
            while($pages->have_posts()) {
                $pages->the_post();
                
                $post_id = get_the_ID();
                
                try {
                    $mSuccess = $oii_eci_helper->migrate_external_content($post_id);
                    if ($mSuccess==true)
                        $response = array("status" => "success", "success" => array("message" => "Page migration complete!"));
                    else
                        $response = array("status" => "error", "error" => array("message" => implode(",",$mSuccess)));
                } catch(Exception $e) {
                    $response = array("status" => "error", "error" => array("message" => $e->getMessage()));
                }
                
                $index++;
            }
        }
        
        wp_reset_postdata();
        
        if (!$count){
            add_option("oii_migrate_count", $index);
        } else {
            update_option("oii_migrate_count", $count+$index);
        }
        
        wp_die();
    }
    
    public function _reset_migrate_count(){
        delete_option("oii_migrate_count");
    }
    
    /**
     * Query All Pages with External Contents
     **/
    private function get_pages_for_migration($offset=null){
        
        require_once(OII_ECI_PATH . "/includes/oii-eci-metabox.php");
        
        $params = array(
            'post_type' => 'page',
            'posts_per_page' => 50,
            'meta_key' => '_wp_page_template',
            'meta_value' => OII_ECI_Metabox::$template        
        );
        
        if ($offset)
            $params['offset'] = $offset;
        
        $pages = new WP_Query($params);
        
        return $pages;
    }
    
    /**
     * Setup Cron
     * Description
     */
    public function setup_cron()
    {
        if(isset($_GET["settings-updated"]) && $_GET["settings-updated"])
        {
            $option = get_option(self::$option_name);

            if($option["schedule"])
            {
                $timestamp = wp_next_scheduled(self::$cron_action_hook);

                // Schedule
                if($timestamp == FALSE)
                {
                    wp_schedule_event(time() + 2 * 60, $option["schedule"], self::$cron_action_hook);
                }
                else
                {
                    // Re-schedule
                    $schedule = wp_get_schedule(self::$cron_action_hook);

                    if(strcmp($schedule, $option["schedule"]))
                    {
                        wp_unschedule_event($timestamp, self::$cron_action_hook);
                        wp_reschedule_event(time() + 2 * 60, $option["schedule"], self::$cron_action_hook);
                    }
                }
            }
        }
    }
}
