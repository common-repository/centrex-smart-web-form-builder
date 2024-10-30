<?php
/**
 * Centrex Smart Web Form Builder - Free
 *
 * @package       CENTREX
 * @author        Centrex Software
 * @license       gplv2-or-later
 * @version       1.0
 *
 * @wordpress-plugin
 * Plugin Name:   Centrex Smart Web Form Builder - Free
 * Plugin URI:    https://www.centrexsoftware.com/
 * Description:   This plugin connects your WordPress website to the Centrex CRM through custom-built forms.
 * Version:       1.0
 * Author:        Centrex Software
 * Author URI:    https://www.centrexsoftware.com/
 * Text Domain:   centrex-software-smart-app-builder
 * Domain Path:   /
 * License:       GPLv2 or later
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Centrex Smart Web Form Builder. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * HELPER COMMENT START
 *
 * This file contains the main information about the plugin.
 * It is used to register all components necessary to run the plugin.
 *
 * The comment above contains all information about the plugin
 * that are used by WordPress to differenciate the plugin and register it properly.
 * It also contains further PHPDocs parameter for a better documentation
 *
 * The function CENTREX() is the main function that you will be able to
 * use throughout your plugin to extend the logic. Further information
 * about that is available within the sub classes.
 *
 * HELPER COMMENT END
 */

// Plugin name
define( 'CENTREX_NAME', 'Centrex Smart Web Form Builder - Free' );

// Plugin version
define( 'CENTREX_VERSION', '1.1' );

// Plugin Root File
define( 'CENTREX_PLUGIN_FILE', __FILE__ );

// Plugin base
define( 'CENTREX_PLUGIN_BASE', plugin_basename( CENTREX_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'CENTREX_PLUGIN_DIR', plugin_dir_path( CENTREX_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'CENTREX_PLUGIN_URL', plugin_dir_url( CENTREX_PLUGIN_FILE ) );

// Plugin error log
define( 'CENTREX_PLUGIN_ERROR_LOG', plugin_dir_path( __FILE__ ) . 'error.log' );
// Plugin info log
define( 'CENTREX_PLUGIN_INFO_LOG', plugin_dir_path( __FILE__ ) . 'info.log' );


// Plugin DEBUG log
// define( 'CENTREX_PLUGIN_DEBUG_LOG', null );
// Uncomment the following line to enable debug logging and comment the above line:
define( 'CENTREX_PLUGIN_DEBUG_LOG', plugin_dir_path( __FILE__ ) . 'debug.log' );

/**
 * Load the main class for the core functionality
 */
require_once CENTREX_PLUGIN_DIR . 'core/centrex-plugin-init.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @return  object|CentrexPluginInit
 * @since   1.0
 * @author  Centrex Software
 */
function centrex_app() {
    return CentrexPluginInit::instance();
}

centrex_app();