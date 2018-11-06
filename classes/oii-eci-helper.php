<?php
require_once(OII_ECI_PATH . "includes/oii-eci-metabox.php");
require_once(OII_ECI_PATH . "classes/oii-eci-external-content.php");
require_once(OII_ECI_PATH . "/includes/oii-eci-settings-page.php");

/**
 * OII Helper Class
 **/
class OII_ECI_Helper {
    private $_debug;
    
    public $contents = array();
    
    public $post_id = 0;
    
    public function __construct(){
        
    }
    
    public function migrate_external_content($post_id) {
        
        $this->post_id = $post_id;
        $this->sync_page();
        
        $acontents = $this->appended_contents();
        
        $post = get_post($post_id);
        $content = $post->post_content;
        
        if ($acontents)
            $content .= $acontents;
        
        $post = array(
                      'ID' => $this->post_id,
                      'post_content' => $content
                      );
        
        $this->deactivate_external_configs();
        
        // Saving updated content with appended external contents        
        $pId = wp_update_post($post, true);
        
        if (is_wp_error($pId)){
            
            if($this->_debug == 1)
                error_log("The following errors occurred: ");
                
            $errors = $pId->get_error_messages();
            foreach ($errors as $error){
                error_log($error);
            }
            
            return $errors;
        }
        
        return true;
    }

    private function sync_page() {
        
        $_option = get_option(OII_ECI_Settings_Page::$option_name);
        $this->_debug = $_option["debug"];

        if($this->_debug == 1)
            error_log("syncing external content on page " . $this->post_id);
        
        $active_contents = array();
        $external_contents = OII_ECI_External_Content::get_by_post_id($this->post_id);
        
        foreach($external_contents as $content){
            if ($content->active==true){
                array_push($active_contents, $content);
            }
        }
        
        $this->contents = $active_contents;
        
    }
    
    private function appended_contents( ) {
        $content = null;
        
        if (count($this->contents)>0){
            foreach($this->contents as $acontent){
                $acontent->update();
                if ($acontent->content) {
                    $timestamp = date("m/d/Y h:i:s");
                    $prep = "<!-- Migrated: " . $timestamp . " --><!-- URL: " . $content->url . " -->";
                    // Section Header
                    $prep .= ($acontent->header) ? "<h2>" . $acontent->header . "</h2>" : NULL;
                    // Section Anchor
                    $prep .= "<a id='ext-content-" . $acontent->order . "'></a>";
                    
                    $acontent->content = $prep.$acontent->content;
                    $content .= $acontent->content;
                    
                }
            }
        }
        
        return $content;
    }
    
    private function deactivate_external_configs(){
        $new_external_contents = array();
        
        $external_contents = OII_ECI_External_Content::get_by_post_id($this->post_id);
        
        if(count($external_contents)) {
            foreach($external_contents AS $external_content) {
                $external_content->active = false;
                array_push($new_external_contents, $external_content->as_postmeta());
            }
        }
        
        if (count($new_external_contents)) 
            update_post_meta($this->post_id, OII_ECI_Metabox::$meta_key, $new_external_contents);
    }
}


?>