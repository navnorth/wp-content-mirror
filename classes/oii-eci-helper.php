<?php
require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");
require_once(OII_ECI_PATH . "/includes/oii-eci-settings-page.php");

/**
 * OII Helper Class
 **/
class OII_ECI_Helper {
    private $_debug;
    
    public function __construct(){
        
    }

    public function sync_page($page_id) {
        
        $_option = get_option(OII_ECI_Settings_Page::$option_name);
        $this->_debug = $_option["debug"];

        if($this->_debug == 1)
            error_log("syncing external content on page " . $page_id);
        
        $active_contents = array();
        $external_contents = OII_ECI_External_Content::get_by_post_id($page_id);
        
        foreach($external_contents as $content){
            if ($content->active==true){
                array_push($active_contents, $content);
            }
        }
        
        foreach($active_contents as $acontent){
            if ($acontent->check_url_status()){
                $acontent->update();
                $xcontent = $acontent->output_content();
            }
        }
    }
    
}


?>