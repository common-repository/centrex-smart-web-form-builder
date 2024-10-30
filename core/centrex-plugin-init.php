<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * HELPER COMMENT START
 *
 * This is the main class that is responsible for registering
 * the core functions, including the files and setting up all features.
 *
 * To add a new class, here's what you need to do:
 * 1. Add your new class within the following folder: core/includes/classes
 * 2. Create a new variable you want to assign the class to (as e.g. public $helpers)
 * 3. Assign the class within the instance() function ( as e.g. self::$instance->helpers = new CentrexPluginInit_Helpers();)
 * 4. Register the class you added to core/includes/classes within the includes() function
 *
 * HELPER COMMENT END
 */

if ( !class_exists( 'CentrexPluginInit' ) ) :

    /**
     * Main CentrexPluginInit Class.
     *
     * @package        CENTREX
     * @subpackage    Classes/CentrexPluginInit
     * @since        1.0
     * @author        Centrex Software
     */
    final class CentrexPluginInit
    {


        /**
         * The real instance
         *
         * @access    private
         * @since    1.0
         * @var        object|CentrexPluginInit
         */
        private static $instance;

        /**
         * CENTREX helpers object.
         *
         * @access    public
         * @since    1.0
         * @var        object|CentrexPluginHelpers
         */
        public $helpers;

        /**
         * CENTREX settings object.
         *
         * @access    public
         * @since    1.0
         * @var        object|CentrexSettings
         */
        public $settings;

        /**
         * DebtPayProApi
         *
         * @var object|Centrex_DebtPayProApi
         */
        public $debt_pay_pro_api;

        /**
         * PostUrlApi
         *
         * @var object|Centrex_PostUrlApi
         */
        public $post_url_api;

        /**
         * PlaidApi
         *
         * @var object|Centrex_PlaidApi
         */
        public $plaid_api;

        /**
         * Hooks registration
         *
         * @var object|CentrexHooksRegistration
         */
        public $hooks_registration;

        /**
         * Throw error on object clone.
         *
         * Cloning instances of the class is forbidden.
         *
         * @access    public
         * @return    void
         * @since    1.0
         */
        public function __clone() {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            _doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'centrex-software-smart-app-builder' ), '1.0' );
        }

        /**
         * Disable unserializing of the class.
         *
         * @access    public
         * @return    void
         * @since    1.0
         */
        public function __wakeup() {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            _doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'centrex-software-smart-app-builder' ), '1.0' );
        }

        /**
         * Main CentrexPluginInit Instance.
         *
         * Ensures that only one instance of CentrexPluginInit exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @access        public
         * @return        object|CentrexPluginInit    The one true CentrexPluginInit
         * @since        1.0
         * @static
         */
        public static function instance() {
            if ( !isset( self::$instance ) && !( self::$instance instanceof CentrexPluginInit ) ) {
                self::$instance = new CentrexPluginInit();
                self::$instance->base_hooks();
                self::$instance->includes();
                self::$instance->helpers = new CentrexPluginHelpers();
                self::$instance->settings = new CentrexSettings();
                self::$instance->debt_pay_pro_api = new Centrex_DebtPayProApi();
                self::$instance->post_url_api = new Centrex_PostUrlApi();
                self::$instance->plaid_api = new Centrex_PlaidApi();

                // Fire the plugin logic
                self::$instance->hooks_registration = new CentrexHooksRegistration( self::$instance->debt_pay_pro_api, self::$instance->post_url_api, self::$instance->plaid_api );
                self::$instance->hooks_registration->add_hooks();

                /**
                 * Fire a custom action to allow dependencies
                 * after the successful plugin setup
                 */
                do_action( 'centrex_plugin_loaded' );
            }

            return self::$instance;
        }

        /**
         * Include required files.
         *
         * @access  private
         * @return  void
         * @since   1.0
         */
        private function includes() {
            require_once CENTREX_PLUGIN_DIR . 'core/includes/classes/centrex-plugin-helpers.php';
            require_once CENTREX_PLUGIN_DIR . 'core/includes/classes/centrex-settings.php';
            require_once CENTREX_PLUGIN_DIR . 'core/includes/classes/debt-pay-pro-api.php';
            require_once CENTREX_PLUGIN_DIR . 'core/includes/classes/post-url-api.php';
            require_once CENTREX_PLUGIN_DIR . 'core/includes/classes/plaid-api.php';

            require_once CENTREX_PLUGIN_DIR . 'core/includes/classes/centrex-hooks-registration.php';
        }


        /**
         * Add base hooks for the core functionality
         *
         * @access  private
         * @return  void
         * @since   1.0
         */
        private function base_hooks() {
            add_action( 'plugins_loaded', [ self::$instance, 'load_textdomain' ] );
        }

        /**
         * Loads the plugin language files.
         *
         * @access  public
         * @return  void
         * @since   1.0
         */
        public function load_textdomain() {
            //phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
            load_plugin_textdomain( 'centrex-software-smart-app-builder', false, dirname( plugin_basename( CENTREX_PLUGIN_FILE ) ) . '/languages/' );
        }
    }

endif; // End if class_exists check.