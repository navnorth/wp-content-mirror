<?php
/*
 Plugin Name: OII External Content Mirror
 Plugin URI: http://www.navigationnorth.com/wordpress/wp-content-mirror
 Description: Automatically import HTML content from external web pages and append to existing WP pages.
 Version: 0.8.1
 Author: Navigation North
 Author URI: http://www.navigationnorth.com

 Copyright (C) 2015 Navigation North

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define("OII_ECI_PATH", plugin_dir_path(__FILE__));
define("OII_ECI_URL", plugin_dir_url(__FILE__));
define("OII_ECI_PLUGIN_DOMAIN", "oii-external-content-importer");
define("OII_ECI_TEMPLATE_DIRECTORY", "oii-eci-template");

include_once(OII_ECI_PATH . "/includes/oii-eci-settings-page.php");
include_once(OII_ECI_PATH . "/includes/oii-eci-metabox.php");
include_once(OII_ECI_PATH . "/classes/oii-eci-external-content.php");
include_once(OII_ECI_PATH . "/classes/oii-eci-scraper.php");

$_option = get_option(OII_ECI_Settings_Page::$option_name);
$_debug = $_option['debug'];

if(is_admin())
{
    $oii_eci_settings_page = new OII_ECI_Settings_Page();
    $oii_eci_metabox = new OII_ECI_Metabox();
}

register_activation_hook(__FILE__, "activate_oii_eci_plugin");
register_deactivation_hook(__FILE__, "deactivate_oii_eci_plugin");
/**
 * Activate OII External Content Importer Plugin
 *
 * Description
 */
function activate_oii_eci_plugin()
{
    global $wpdb;
    //creating custom external contents table
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $table_name = $wpdb->prefix . OII_ECI_External_Content::$table;
    $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) NOT NULL DEFAULT '0',
            `external_content_id` bigint(20) NOT NULL DEFAULT '0',
            `content` longtext CHARACTER SET utf8mb4 NOT NULL,
            `url` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
            `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	dbDelta($sql);
}

/**
 * Deactivate OII External Content Importer Plugin
 */
function deactivate_oii_eci_plugin()
{
    wp_clear_scheduled_hook(OII_ECI_Settings_Page::$cron_action_hook);
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
add_filter( 'the_content', "oii_eci_content_filter" );

/**
 * External Content Importer Cron Job
 * Description
 */
function external_content_importer_cron_job()
{
    if($_debug==1)
        error_log( 'running OII ECI Scraper via cron' );
    OII_ECI_Scraper::run();
}
// External Content Importer Cron Job Hook
add_action(OII_ECI_Settings_Page::$cron_action_hook, "external_content_importer_cron_job");
