<?php
/**
 * Plugin Name: OII External Content Importer
 * Plugin URI:
 * Description: Plugin for OII External Content Imports
 * Version: 1.0.0
 * Author:
 * Author URI:
 * License: 
 */
define("OII_ECI_PATH", plugin_dir_path(__FILE__));
define("OII_ECI_URL", plugin_dir_url(__FILE__));
define("OII_ECI_PLUGIN_DOMAIN", "oii-external-content-importer");
define("OII_ECI_TEMPLATE_DIRECTORY", "oii-eci-template");
define("OII_ECI_EXTERNAL_TABLE", $wpdb->prefix . "oii_external_contents");

include_once(OII_ECI_PATH . "/includes/oii-eci-settings-page.php");
include_once(OII_ECI_PATH . "/includes/oii-eci-metabox.php");
include_once(OII_ECI_PATH . "/classes/oii-eci-external-content.php");

if(is_admin())
{
    $oii_eci_settings_page = new OII_ECI_Settings_Page();
    $oii_eci_metabox = new OII_ECI_Metabox();
}

register_activation_hook(__FILE__, "activate_oii_eci_plugin");
/**
 * Activate OII External Content Importer Plugin
 *
 * Description
 */
function activate_oii_eci_plugin()
{
    //creating custom external contents table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sql = "CREATE TABLE IF NOT EXISTS `" . OII_ECI_EXTERNAL_TABLE . "` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) NOT NULL DEFAULT '0',
            `order` tinyint(3) NOT NULL,
            `content` longtext CHARACTER SET utf8mb4 NOT NULL,
            `url` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
            `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	dbDelta($sql);
}

/**
 *
 * Add Settings Link on the Plugins page
 *
 */

function oii_eci_settings_link( $links ) {
    $url = get_admin_url() . 'options-general.php?page=oii-eci-admin';
    $settings_link = '<a href="' . $url . '">' . __('Settings', OII_ECI_PLUGIN_DOMAIN) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
 add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'oii_eci_settings_link');

/**
 *
 * Get external content related of the page
 *
 */
function get_external_content($post_id) {
    $content = "";
    
    $rows = OII_ECI_External_Content::get_by_post_id($post_id);
    
    foreach($rows as $row){
	$content .= $row->output_content();
    }
    
    return $content;
}

/**
 *
 * Display external content at the bottom of page content
 *
 */
function oii_eci_content_filter($content){
    global $post;
    $new_content = get_external_content($post->ID);
    return $content.$new_content;
}
add_filter( 'the_content', oii_eci_content_filter );