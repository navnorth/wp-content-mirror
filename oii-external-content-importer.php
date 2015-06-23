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

include_once(OII_ECI_PATH . "/includes/oii-eci-settings-page.php");
include_once(OII_ECI_PATH . "/includes/oii-eci-metabox.php");

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
    
}