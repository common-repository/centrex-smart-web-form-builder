<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * HELPER COMMENT START
 *
 * This class contains all of the plugin related settings.
 * Everything that is relevant data and used multiple times throughout
 * the plugin.
 *
 * To define the actual values, we recommend adding them as shown above
 * within the __construct() function as a class-wide variable.
 * This variable is then used by the callable functions down below.
 * These callable functions can be called everywhere within the plugin
 * as followed using the get_plugin_name() as an example:
 *
 * CENTREX->settings->get_plugin_name();
 *
 * HELPER COMMENT END
 */

/**
 * Class CentrexSettings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package        CENTREX
 * @subpackage     Classes/CentrexSettings
 * @author         Centrex Software
 * @since          1.0
 */
class CentrexSettings
{


    /**
     * The plugin name
     *
     * @var        string
     * @since   1.0
     */
    private $plugin_name;

    /**
     * The plugin capabilities
     *
     * @var        array
     * @since    1.0
     */
    private $capabilities;

    /**
     * The options panel settings
     *
     * @since    1.0
     * @var        array $options_panel The current options of the options panel
     */
    private $options_panel;

    /**
     * Our CentrexSettings constructor
     * to run the plugin logic.
     *
     * @since 1.0
     */
    function __construct() {

        $this->plugin_name = CENTREX_NAME;
        $this->capabilities = [
            'default' => 'manage_options',
        ];
    }

    /**
     * Return the plugin name
     *
     * @access    public
     * @return    string The plugin name
     * @since     1.0
     */
    public function get_plugin_name() {
        return apply_filters( 'CENTREX/settings/get_plugin_name', $this->plugin_name );
    }

    /**
     * Return the specified plugin capability
     *
     * @access    public
     * @return    string The chosen capability
     * @since     1.0
     */
    public function get_capability( $identifier = 'default' ) {

        $capability = $this->capabilities['default'];
        if ( !empty( $identifier ) && isset( $this->capabilities[ $identifier ] ) ) {
            $capability = $this->capabilities[ $identifier ];
        }

        return apply_filters( 'CENTREX/settings/get_capability', $capability, $identifier, $this->capabilities );
    }

    /**
     * Register and add the settings
     *
     * @access    public
     * @return    void
     * @since     1.0
     */
    public function register_admin_settings() {

        // Register the settings
        register_setting(
            'centrex_options_group', // The option group
            'centrex_options', // The option name
            [ $this, 'sanitize_settings' ]
        );

        // Add the settings section
        add_settings_section(
            'setting_section_id',
            __( 'Centrex Options Panel', 'centrex-software-smart-app-builder' ),
            function () {
                echo 'Thank you for choosing Centrex Software solutions.
        <br><b>An email address is required to send form submissions to if there are no API credentials, but is also used as a fall back if for some reason the form data does not post to Centrex.</b>';
            },
            'centrex-options-panel'
        );

        // Add all settings fields
        add_settings_field(
            'centrex_account_id',
            __( 'Centrex Account ID', 'centrex-software-smart-app-builder' ),
            function () {
                printf(
                    '<input type="text" id="centrex_account_id" name="centrex_options[centrex_account_id]" value="%s" />',
                    isset( $this->options_panel['centrex_account_id'] ) ? esc_attr( $this->options_panel['centrex_account_id'] ) : ''
                );
            },
            'centrex-options-panel',
            'setting_section_id'
        );

        // NOTE: API Key ID was originally client_id but then was renamed to API Key ID
        // Currently DebtPayPro is the only one that uses this.
        // The Plaid API uses `centrex_account_id` and calls the param that is sent is called `client_id` 🤷🏼
        add_settings_field(
            'client_id',
            __( 'API Key ID', 'centrex-software-smart-app-builder' ),
            function () {
                printf(
                    '<input type="text" id="client_id" name="centrex_options[client_id]" value="%s" />',
                    isset( $this->options_panel['client_id'] ) ? esc_attr( $this->options_panel['client_id'] ) : ''
                );
            },
            'centrex-options-panel',
            'setting_section_id'
        );

        add_settings_field(
            'client_secret',
            __( 'API Key Secret', 'centrex-software-smart-app-builder' ),
            function () {
                printf(
                    '<input type="text" id="client_secret" name="centrex_options[client_secret]" value="%s" />',
                    isset( $this->options_panel['client_secret'] ) ? esc_attr( $this->options_panel['client_secret'] ) : ''
                );
            },
            'centrex-options-panel',
            'setting_section_id'
        );

        add_settings_field(
            'post_email',
            __( 'Email Address', 'centrex-software-smart-app-builder' ),
            function () {
                printf(
                    '<input type="email" id="post_email" name="centrex_options[post_email]" value="%s" />',
                    isset( $this->options_panel['post_email'] ) ? esc_attr( $this->options_panel['post_email'] ) : ''
                );
            },
            'centrex-options-panel',
            'setting_section_id'
        );

        add_settings_field(
            'logo_url',
            __( 'Logo Image URL', 'centrex-software-smart-app-builder' ),
            function () {
                printf(
                    '<input type="url" id="logo_url" name="centrex_options[logo_url]" value="%s" />',
                    isset( $this->options_panel['logo_url'] ) ? esc_attr( $this->options_panel['logo_url'] ) : ''
                );
            },
            'centrex-options-panel',
            'setting_section_id'
        );
    }

    /**
     * Sanitize the registered settings
     *
     * @access    public
     *
     * @param array $input Contains all settings fields
     *
     * @return    array    The sanitized $input fields
     * @since     1.0
     */
    public function sanitize_settings( array $input ) {

        if ( isset( $input['centrex_account_id'] ) ) {
            $input['centrex_account_id'] = sanitize_text_field( $input['centrex_account_id'] );
        }

        if ( isset( $input['client_id'] ) ) {
            $input['client_id'] = sanitize_text_field( $input['client_id'] );
        }

        if ( isset( $input['client_secret'] ) ) {
            $input['client_secret'] = sanitize_text_field( $input['client_secret'] );
        }

        if ( isset( $input['post_email'] ) ) {
            $input['post_email'] = sanitize_text_field( $input['post_email'] );
        }

        if ( isset( $input['logo_url'] ) ) {
            $input['logo_url'] = sanitize_text_field( $input['logo_url'] );
        }

        return $input;
    }


    /**
     * Add custom menu page content for the following
     * menu item: centrex
     *
     * @access    public
     * @return    void
     * @since     1.0
     */
    public function render_settings_page() {

        $this->options_panel = get_option( 'centrex_options' );
        $this->render_plugin_header();
        ?>
        <div class="wrap centrex-options-wrapper">
                <form method="post" action="options.php">
                    <?php
                    settings_fields( 'centrex_options_group' );
                    do_settings_sections( 'centrex-options-panel' );
                    submit_button();
                    ?>
                </form>

        </div>
        <?php
    }

    public function render_plugin_header() {
        ?>
        <div class="plugin-header">
            <img alt="centrex logo"
                 src="<?php echo esc_url( CENTREX_PLUGIN_URL . 'core/includes/assets/img/centrex-header.png' ); ?>"/>
            <div class="centrex-plugin-text">
                <strong>Smart App</strong> Builder
            </div>
        </div>
        <?php
    }

    /**
     * Add a new menu item to the WordPress topbar
     *
     * @access    public
     *
     * @param object $admin_bar The WP_Admin_Bar object
     *
     * @return    void
     * @since     1.0
     */
    public function add_admin_bar_menu_items( $admin_bar ) {

        $admin_bar->add_menu(
            [
                'id'     => 'centrex-software-smart-app-builder-id', // The ID of the node.
                'title'  => __( 'Centrex Smart App - Free', 'centrex-software-smart-app-builder' ),
                // The text that will be visible in the Toolbar. Including html tags is allowed.
                'parent' => false, // The ID of the parent node.
                'href'   => '#',
                // The ‘href’ attribute for the link. If ‘href’ is not set the node will be a text node.
                'group'  => false,
                // This will make the node a group (node) if set to ‘true’. Group nodes are not visible in the Toolbar, but nodes added to it are.
                'meta'   => [
                    'title'    => __( 'Centrex Smart App - Free', 'centrex-software-smart-app-builder' ),
                    // The title attribute. Will be set to the link or to a div containing a text node.
                    'target'   => '_blank',
                    // The target attribute for the link. This will only be set if the ‘href’ argument is present.
                    'class'    => 'centrex-software-smart-app-builder-class',
                    // The class attribute for the list item containing the link or text node.
                    'html'     => false, // The html used for the node.
                    'rel'      => false, // The rel attribute.
                    'onclick'  => false,
                    // The onclick attribute for the link. This will only be set if the ‘href’ argument is present.
                    'tabindex' => false,
                    // The tabindex attribute. Will be set to the link or to a div containing a text node.
                ],
            ]
        );

        $admin_bar->add_menu(
            [
                'id'     => 'centrex-software-smart-app-builder-sub-id',
                'title'  => __( 'Modify Form', 'centrex-software-smart-app-builder' ),
                'parent' => 'centrex-software-smart-app-builder-id',
                'href'   => '#',
                'group'  => false,
                'meta'   => [
                    'title'    => __( 'Modify Form', 'centrex-software-smart-app-builder' ),
                    'target'   => '_blank',
                    'class'    => 'centrex-software-smart-app-builder-sub-class',
                    'html'     => false,
                    'rel'      => false,
                    'onclick'  => false,
                    'tabindex' => false,
                ],
            ]
        );
    }
}
