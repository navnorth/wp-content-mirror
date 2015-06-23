<?php

class OII_ECI_Settings_Page {
    private $_option;
    
    private static $_menu_slug = "oii-eci-admin";
    
    public static $_option_name = "oii_eci_settings";
    private static $_option_group = "oii_eci_opotion_group";
    
    private static $_setting_title = "External Content Importer";
    
    /**
     * Class Constructor
     * Description
     */
    public function __construct()
    {
        add_action("admin_menu", array($this, "add_plugin_page"));
        add_action("admin_init", array($this, "page_init"));
    }
    /**
     * Add Plugin Page
     * Description
     */
    public function add_plugin_page()
    {
        add_options_page(
            "Settings Admin", // page_title
            self::$_setting_title, // menu_title
            "manage_options", // capability
            self::$_menu_slug, // menu_slug
            array($this, "create_admin_page") // callback
        );
    }
    /**
     * Create Admin Page
     * Description
     */
    public function create_admin_page()
    {
        $this->_option = get_option(self::$_option_name); ?>
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
        <?php
    }
    
    public function page_init()
    {
        register_setting(
            self::$_option_group,
            self::$_option_name,
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
            "regex",
            "Regular Expression",
            array($this, "regex_callback"),
            self::$_menu_slug, 
            "setting_section_id"
        );
        
        add_settings_field(
            "schedule",
            "Schedule",
            array($this, "schedule_callback"),
            self::$_menu_slug, 
            "setting_section_id"
        );
        
        // Register and Enqueue External Content Importer Script
        wp_register_script("oii-eci-settings-script", OII_ECI_URL . "js/oii-eci-settings-script.js", array("jquery"));
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
                $new_input["regex"][] = array(
                    "replace" => htmlspecialchars(trim($replace)),
                    "with" => htmlspecialchars(trim($input["with"][$key]))
                );
        }
        
        if(isset($input["schedule"]))
            $new_input["schedule"] = $input["schedule"];
            
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
    public function regex_callback()
    {
        $regex = (is_array($this->_option["regex"])) ? $this->_option["regex"] : array(array());
        
        foreach($regex AS $key => $expression)
        {
            echo "<div class='regex'" . ($key ? " style='margin-top: 15px'" : "") . ">";
                echo "Replace <input type='text' class='form-element regex-replace' name='" . self::$_option_name . "[replace][]' value='" . $expression["replace"] . "' /> with <input type='text' class='form-element regex-with' name='" . self::$_option_name . "[with][]' value='" . $expression["with"] . "' />";
            
            if($key == 0)
                echo "<button type='button' data-name='" . self::$_option_name . "' id='oii-eci-new-regex' class='button'>New</button>";
            else
                echo "<a href='#' style='color: #555; text-decoration: none'><span class='delete-regex dashicons dashicons-trash' style='vertical-align: text-bottom'></span></a>";
                
            echo "</div>";
        }
    }
    /**
     * Schedule Callback
     * Description
     */
    public function schedule_callback()
    {
        echo "<select id='schedule' class='form-element' name='" . self::$_option_name ."[schedule]'>";
        
        echo "<option value=''>&nbsp;</option>";
        foreach(array("hourly", "daily", "twice_daily") AS $schedule)
            echo "<option value='" . $schedule . "'" . ($this->_option["schedule"] == $schedule ? " selected" : "" ) . ">" . ucwords(str_replace("_", " ", $schedule)) . "</option>";
        
        echo "</select>";
        echo "<p class='description'>The schedule for updating content.</p>";
    }
}