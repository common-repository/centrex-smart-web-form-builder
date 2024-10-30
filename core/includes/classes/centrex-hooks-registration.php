<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 *
 *
 * This class is used to bring your plugin to life.
 * All the other registered classes bring features which are
 * controlled and managed by this class.
 *
 * Within the add_hooks() function, you can register all of
 * your WordPress related actions and filters as followed:
 *
 * add_action( 'my_action_hook_to_call', array( $this, 'the_action_hook_callback', 10, 1 ) );
 * or
 * add_filter( 'my_filter_hook_to_call', array( $this, 'the_filter_hook_callback', 10, 1 ) );
 * or
 * add_shortcode( 'my_shortcode_tag', array( $this, 'the_shortcode_callback', 10 ) );
 *
 * Once added, you can create the callback function, within this class, as followed:
 *
 * public function the_action_hook_callback( $some_variable ){}
 * or
 * public function the_filter_hook_callback( $some_variable ){}
 * or
 * public function the_shortcode_callback( $attributes = array(), $content = '' ){}
 *
 *
 *
 */

/**
 * Class Centrex_Software_Smart_App_Builder_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package        CENTREX
 * @subpackage     Classes/Centrex_Software_Smart_App_Builder_Run
 * @author         Centrex Software
 * @since          1.0
 */
class CentrexHooksRegistration
{
    /**
     * The priority of the plugin scripts.
     * Anything that needs to run after the plugin scripts should have a lower priority (higher number).
     */
    const SCRIPT_PRIORITY = 20;
    /**
     * @var Centrex_DebtPayProApi
     */
    private $debt_pay_pro_api;

    /**
     * @var Centrex_PostUrlApi
     */
    private $post_url_api;

    /**
     * @var Centrex_PlaidApi
     */
    private $plaid_api;

    /**
     * @var string
     */
    private $table_name;

    /**
     * Our Centrex_Software_Smart_App_Builder_Run constructor
     * to run the plugin logic.
     *
     * @access    public
     *
     * @param Centrex_DebtPayProApi $debt_pay_pro_api
     * @param Centrex_PostUrlApi    $post_url_api
     * @param Centrex_PlaidApi      $plaid_api
     *
     * @since     1.0
     */
    public function __construct( Centrex_DebtPayProApi $debt_pay_pro_api, Centrex_PostUrlApi $post_url_api, Centrex_PlaidApi $plaid_api ) {
        global $wpdb;
        $this->debt_pay_pro_api = $debt_pay_pro_api;
        $this->post_url_api = $post_url_api;
        $this->plaid_api = $plaid_api;
        $this->table_name = $wpdb->prefix . 'centrex_app_builder';
    }


    /**
     * Registers all WordPress and plugin related hooks
     *
     * @access    private
     * @return    void
     * @since     1.0
     */
    public function add_hooks() {

        add_shortcode( 'centrex_smart_app', [ $this, 'render_form_using_shortcode' ] );

        add_action(
            'plugin_action_links_' . CENTREX_PLUGIN_BASE,
            [
                $this,
                'add_plugin_action_link',
            ],
            self::SCRIPT_PRIORITY
        );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_backend_scripts_and_styles' ], self::SCRIPT_PRIORITY );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts_and_styles' ], self::SCRIPT_PRIORITY );

        // Settings-related actions:
        add_action( 'admin_menu', [ $this, 'register_admin_side_panel_pages' ], self::SCRIPT_PRIORITY );
        add_action( 'admin_init', [ centrex_app()->settings, 'register_admin_settings' ], self::SCRIPT_PRIORITY );

        // Installation/Uninstallation:
        register_activation_hook( CENTREX_PLUGIN_FILE, [ $this, 'activation_hook_callback' ] );
        register_deactivation_hook( CENTREX_PLUGIN_FILE, [ $this, 'deactivation_hook_callback' ] );

        // Admin-level AJAX actions:
        add_action( 'wp_ajax_centrex_plugin_update_form_row', [ $this, 'ajax_admin_update_form_row' ] );
        add_action( 'wp_ajax_centrex_plugin_add_new_form', [ $this, 'ajax_admin_add_new_form' ] );
        add_action( 'wp_ajax_centrex_plugin_delete_form_row', [ $this, 'ajax_admin_delete_form_row' ] );
        add_action( 'wp_ajax_centrex_plugin_clone_form_row', [ $this, 'ajax_admin_clone_form_row' ] );

        // End-user-facing AJAX actions:
        // Note: WordPress requires that you register the same action twice, once for logged-in users and once for non-logged-in users.
        add_action( 'wp_ajax_centrex_plugin_submit_form', [ $this, 'ajax_submit_user_entered_form' ] );
        add_action( 'wp_ajax_nopriv_centrex_plugin_submit_form', [ $this, 'ajax_submit_user_entered_form' ] );

        add_action( 'wp_ajax_centrex_plugin_upload_files', [ $this, 'ajax_upload_files' ] );
        add_action( 'wp_ajax_nopriv_centrex_plugin_upload_files', [ $this, 'ajax_upload_files' ] );

        add_action( 'wp_ajax_centrex_plugin_get_link_token', [ $this, 'ajax_get_link_token' ] );
        add_action( 'wp_ajax_nopriv_centrex_plugin_get_link_token', [ $this, 'ajax_get_link_token' ] );

        add_action( 'wp_ajax_centrex_plugin_process_token', [ $this, 'ajax_process_token' ] );
        add_action( 'wp_ajax_nopriv_centrex_plugin_process_token', [ $this, 'ajax_process_token' ] );

        add_action( 'wp_ajax_centrex_plugin_has_plaid_access', [ $this, 'ajax_has_plaid_access' ] );
        add_action( 'wp_ajax_nopriv_centrex_plugin_has_plaid_access', [ $this, 'ajax_has_plaid_access' ] );
    }

    /**
     * Adds action links to the plugin list table
     *
     * @access    public
     *
     * @param array $links An array of plugin action links.
     *
     * @return    array    An array of plugin action links.
     * @since     1.0
     *
     */
    public function add_plugin_action_link( $links ) {

        $links['our_shop'] = sprintf( '<a href="%s" target="_blank title="Upgrade Plugin" style="font-weight:700;">%s</a>', 'https://www.centrexsoftware.com/', __( 'Upgrade Plugin', 'centrex-software-smart-app-builder' ) );

        return $links;
    }

    /**
     * Add the shortcode callback for [centrex_smart_app id="123"]
     *
     * @access    public
     *
     * @return    string    The customized content by the shortcode.
     * @since     1.0
     *
     */
    public function render_form_using_shortcode( $all_attributes_from_short_code ) {

        $attrs_with_defaults = shortcode_atts( [ 'id' => 1 ], $all_attributes_from_short_code );
        $form_builder_row = $this->get_form_for_id( $attrs_with_defaults['id'] );
        //phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        $valid_form_data_json_escaped = str_replace( [ "\r\n", "\r", "\n", '  ' ], '', $form_builder_row->formData );

        $short_form_script = '
        <script type="text/javascript" defer>
            window.CENTREX_APP = window.CENTREX_APP || {};
            window.CENTREX_APP.options = ' .
            json_encode(
                [
                    'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
                    'nonce'          => wp_create_nonce( 'centrex_plugin_nonce' ),
                    'thankUrl'       => esc_url_raw( $form_builder_row->formThankUrl ),
                    'formId'         => esc_attr( $form_builder_row->formId ),
                    'formSubmitText' => esc_attr( $form_builder_row->formSubmitText ),
                    'formPostUrl'    => esc_url_raw( $form_builder_row->formPostUrl ),
                ]
            ) . ';
            window.CENTREX_APP.formRendererData = JSON.parse(\'' . $valid_form_data_json_escaped . '\');
            window.CENTREX_APP.callbackQueue = window.CENTREX_APP.callbackQueue || [];
            window.CENTREX_APP.callbackQueue.push(function(){
                window.CENTREX_APP.formRenderer.init();
            });
            </script>';
        //phpcs:enable

        add_action(
            'wp_enqueue_scripts',
            function () use ( $short_form_script ) {
                wp_add_inline_script( 'centrex-frontend-run-callback-queue', $short_form_script, 'before' );
            },
            self::SCRIPT_PRIORITY + 1
        );

        return '<div class="centrex-app-container">
                    <form id="formRenderContainer"></form>'
            . $short_form_script .
            '</div>';
    }

    public function ajax_submit_user_entered_form() {
        check_ajax_referer( 'centrex_plugin_nonce' );

        $form_id = sanitize_text_field( $_POST['formId'] );
        if ( !isset( $_POST['formData'] ) || !isset( $form_id ) ) {
            wp_send_json_error( [ 'message' => 'Invalid form data' ] );
            return;
        }

        // $form_data = $_POST['formData'];
        // sanitize the form data:
        $form_data = array_map(
            function ( $formControl ) {
                return [
                    'label' => sanitize_text_field( $formControl['label'] ),
                    'value' => sanitize_text_field( $formControl['value'] ),
                ];
            },
            $_POST['formData']
        );
        $form_row = $this->get_form_for_id( $form_id );

        if ( !$form_row ) {
            $this->sendEmailFromFormData( $form_data, true );
            wp_send_json_error( [ 'message' => 'Form not found' ] );
            return;
        }
        $post_url = $form_row->formPostUrl;

        if ( empty( $post_url ) && !$this->debt_pay_pro_api->generateApiKeyIfInvalid() ) {
            centrex_log_debug( 'Failed to generate API key' . $post_url );
            $this->sendEmailFromFormData( $form_data );
            return;
        }

        $contact_id = !empty( $_POST['contactId'] ) ? sanitize_text_field( $_POST['contactId'] ) : null;

        if ( empty( $post_url ) ) {
            centrex_log_debug( 'Using DebtPayPro API to submit form data' );
            $file_type_id = $form_row->formFileTypeId;
            $successfully_created_or_updated_contact = $this->debt_pay_pro_api->createOrUpdateContact( $form_data, $contact_id, $file_type_id );
        } else {
            centrex_log_debug( 'Using PostUrl API to submit form data:' . $post_url );
            $successfully_created_or_updated_contact = $this->post_url_api->createOrUpdateContact( $post_url, $form_data, $contact_id );
        }

        // If we couldn't create a contact in Centrex then we should send an email instead:
        if ( !$successfully_created_or_updated_contact ) {
            centrex_log_debug( 'Failed to create or update contact' . $successfully_created_or_updated_contact );
            $this->sendEmailFromFormData( $form_data, true );
        }
    }

    public function ajax_upload_files() {
        check_ajax_referer( 'centrex_plugin_nonce' );

        $contact_id = sanitize_text_field( $_POST['contactId'] );
        if ( !isset( $contact_id ) ) {
            wp_send_json_error( [ 'message' => 'Could not upload files to Centrex' ] );
            return;
        }

        $files = $this->incoming_files();

        $upload_success = false;
        foreach ( $files as $file ) {
            $upload_success = $this->debt_pay_pro_api->uploadDocumentContact( $contact_id, $file );
        }

        if ( $upload_success ) {
            wp_send_json_success(
                [
                    'message' => 'Successfully uploaded files to Centrex',
                ]
            );

        } else {
            wp_send_json_error(
                [
                    'message' => 'Could not upload files to Centrex',
                ]
            );
        }
    }

    public function ajax_get_link_token() {
        check_ajax_referer( 'centrex_plugin_nonce' );

        $contact_id = sanitize_text_field( $_POST['contactId'] );
        $api_environment = sanitize_text_field( $_POST['apiEnvironment'] );
        $asset_report = sanitize_text_field( $_POST['assetReport'] );
        if ( empty( $contact_id ) ) {
            wp_send_json_error(
                [
                    'message' => 'Invalid contact id',
                    'success' => false,
                ]
            );
            return;
        }

        $asset_report = filter_var( $asset_report, FILTER_VALIDATE_BOOLEAN );

        centrex_log_info( 'Getting link token for contact id: ' . $contact_id . ' and asset report: ' . $asset_report );

        $apiResponse = $this->plaid_api->get_link_token( $contact_id, $asset_report, $api_environment );
        if ( $apiResponse->success ) {
            wp_send_json_success(
                [
                    'link_token' => $apiResponse->responseValue,
                    'success'    => true,
                ]
            );
        } else {
            wp_send_json_error(
                [
                    'message' => $apiResponse->failureReason,
                    'success' => false,
                ]
            );
        }
    }

    public function ajax_process_token() {
        check_ajax_referer( 'centrex_plugin_nonce' );

        $contact_id = sanitize_text_field( $_POST['contactId'] );
        $api_environment = sanitize_text_field( $_POST['apiEnvironment'] );
        $public_token = sanitize_text_field( $_POST['publicToken'] );

        if ( empty( $contact_id ) ) {
            wp_send_json_error(
                [
                    'success' => false,
                    'message' => 'Invalid contact id',
                ]
            );
        }

        if ( empty( $public_token ) ) {
            wp_send_json_error(
                [
                    'success' => false,
                    'message' => 'Invalid public token',
                ]
            );
            return;
        }

        $process_token = $this->plaid_api->process_token( $public_token, $contact_id, $api_environment );
        if ( $process_token ) {
            wp_send_json_success(
                [
                    'success' => true,
                ]
            );
        } else {
            wp_send_json_error(
                [
                    'success' => false,
                    'message' => 'Could not process public token',
                ]
            );
        }
    }

    public function ajax_has_plaid_access() {
        check_ajax_referer( 'centrex_plugin_nonce' );

        $contact_id = sanitize_text_field( $_POST['contactId'] );
        if ( empty( $contact_id ) ) {
            wp_send_json_error(
                [
                    'message' => 'Invalid contact id',
                ]
            );
        }

        $has_access = $this->plaid_api->has_access();

        if ( $has_access ) {
            wp_send_json_success(
                [
                    'has_access' => true,
                ]
            );
        } else {
            wp_send_json_error(
                [
                    'has_access' => false,
                    'message'    => 'User does not have Plaid access',
                ]
            );
        }
    }

    /**
     * Enqueue the backend related scripts and styles for this plugin.
     * All of the added scripts and styles will be available on every page within the backend.
     *
     * @access    public
     * @return    void
     * @since     1.0
     *
     */
    public function enqueue_backend_scripts_and_styles() {
        // Only load this CSS on our plugin screens
        $screen = get_current_screen();
        if ( $screen !== null
            && ( $screen->id === 'toplevel_page_centrex' || $screen->id === 'centrex_page_centrex-form-builder' ) ) {
            wp_enqueue_style( 'centrex-jquery-ui-styles', CENTREX_PLUGIN_URL . 'core/includes/assets/css/jquery-ui.min.css', [], CENTREX_VERSION, 'all' );
            wp_enqueue_style( 'centrex-backend-styles', CENTREX_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', [], CENTREX_VERSION, 'all' );
            wp_enqueue_style( 'centrex-backend-conditional-field-styles', CENTREX_PLUGIN_URL . 'core/includes/assets/css/backend-conditional-fields.css', [ 'centrex-backend-styles' ], CENTREX_VERSION, 'all' );
            wp_enqueue_style( 'centrex-fontawesome', CENTREX_PLUGIN_URL . 'core/includes/assets/css/all.min.css', [], CENTREX_VERSION, 'all' );
        }
        wp_enqueue_script('jquery-ui-sortable');

        // Form builder and form render dependencies:
        wp_enqueue_script(
            'centrex-form-builder-lib',
            CENTREX_PLUGIN_URL . 'core/includes/assets/js/formBuilder/dist/form-builder.min.js',
            [
                'jquery',
                'jquery-ui-core',
                'jquery-ui-sortable',
            ],
            CENTREX_VERSION,
            true
        );
        wp_enqueue_script(
            'centrex-form-render-lib',
            CENTREX_PLUGIN_URL . 'core/includes/assets/js/formBuilder/dist/form-render.min.js',
            [
                'jquery',
                'jquery-ui-core',
                'jquery-ui-widget',
                'jquery-ui-dialog',
                'jquery-ui-sortable',
            ],
            CENTREX_VERSION,
            true
        );

        // Base JS Files:
        wp_enqueue_script( 'centrex-backend-utils', CENTREX_PLUGIN_URL . 'core/includes/assets/js/utils.js', [ 'jquery' ], CENTREX_VERSION, true );
        wp_enqueue_script(
            'centrex-backend-form-builder',
            CENTREX_PLUGIN_URL . 'core/includes/assets/js/backend-form-builder.js',
            [
                'jquery',
                'jquery-ui-core',
                'jquery-ui-widget',
                'jquery-ui-dialog',
                'centrex-form-builder-lib',
                'centrex-backend-utils',
            ],
            CENTREX_VERSION,
            true
        );

        // Premium plugin specific JS files:
        wp_enqueue_script( 'centrex-backend-multi-page-forms', CENTREX_PLUGIN_URL . 'core/includes/assets/js/backend-multi-page-forms.js', [ 'centrex-backend-form-builder' ], CENTREX_VERSION, true );
        wp_enqueue_script( 'centrex-backend-conditional-fields', CENTREX_PLUGIN_URL . 'core/includes/assets/js/backend-conditional-fields.js', [ 'centrex-backend-form-builder' ], CENTREX_VERSION, true );

        // Only run the callback queue when all required files are loaded:
        wp_enqueue_script(
            'centrex-backend-run-callback-queue',
            CENTREX_PLUGIN_URL . 'core/includes/assets/js/backend-run-callback-queue.js',
            [
                'centrex-backend-conditional-fields',
                'centrex-backend-multi-page-forms',
            ],
            CENTREX_VERSION,
            true
        );

        wp_localize_script(
            'centrex-backend-form-builder',
            'centrexapp',
            [
                'plugin_name' => __( 'Centrex Smart Web Form Builder - Free', 'centrex-software-smart-app-builder' ),
            ]
        );
    }


    /**
     * Enqueue the frontend related scripts and styles for this plugin.
     *
     * @access    public
     * @return    void
     * @since     1.0
     *
     */
    public function enqueue_frontend_scripts_and_styles() {
        wp_enqueue_style( 'centrex-frontend-styles', CENTREX_PLUGIN_URL . 'core/includes/assets/css/frontend-styles.css', [], CENTREX_VERSION, 'all' );
        wp_enqueue_style( 'centrex-fontawesome', CENTREX_PLUGIN_URL . 'core/includes/assets/css/all.min.css', [], CENTREX_VERSION, 'all' );
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script( 'centrex-jquery-validate', CENTREX_PLUGIN_URL . 'core/includes/assets/js/jquery.validate.min.js', [ 'jquery' ], CENTREX_VERSION, true );

        wp_enqueue_script(
            'centrex-form-render-lib',
            CENTREX_PLUGIN_URL . 'core/includes/assets/js/formBuilder/dist/form-render.min.js',
            [
                'jquery',
                'jquery-ui-sortable',
            ],
            CENTREX_VERSION,
            true
        );

        // Base JS Files:
        wp_enqueue_script( 'centrex-frontend-utils', CENTREX_PLUGIN_URL . 'core/includes/assets/js/utils.js', [ 'jquery' ], CENTREX_VERSION, true );
        wp_enqueue_script(
            'centrex-frontend-scripts',
            CENTREX_PLUGIN_URL . 'core/includes/assets/js/frontend-scripts.js',
            [
                'jquery',
                'centrex-jquery-validate',
                'centrex-frontend-utils',
            ],
            CENTREX_VERSION,
            true
        );

        // Premium plugin specific JS files:
        wp_enqueue_script( 'centrex-frontend-conditional-fields', CENTREX_PLUGIN_URL . 'core/includes/assets/js/frontend-conditional-fields.js', [ 'centrex-frontend-scripts' ], CENTREX_VERSION, true );
        wp_enqueue_script( 'centrex-frontend-multi-page-forms', CENTREX_PLUGIN_URL . 'core/includes/assets/js/frontend-multi-page-forms.js', [ 'centrex-frontend-scripts' ], CENTREX_VERSION, true );

        // Only run the callback queue when all required files are loaded:
        wp_enqueue_script(
            'centrex-frontend-run-callback-queue',
            CENTREX_PLUGIN_URL . 'core/includes/assets/js/frontend-run-callback-queue.js',
            [
                'centrex-frontend-conditional-fields',
                'centrex-frontend-multi-page-forms',
            ],
            CENTREX_VERSION,
            true
        );

        wp_localize_script( 'centrex-frontend-scripts', 'centrexapp', [] );
    }

    /**
     * Add custom menu pages
     *
     * @access    public
     * @return    void
     * @since     1.0
     *
     */
    public function register_admin_side_panel_pages() {

        add_menu_page(
            'Centrex Smart App Builder - Free',
            'Centrex',
            centrex_app()->settings->get_capability( 'default' ),
            'centrex',
            [
                centrex_app()->settings,
                'render_settings_page',
            ],
            'dashicons-media-text',
            5
        );
        add_submenu_page( 'centrex', 'Centrex Settings', 'Centrex Settings', centrex_app()->settings->get_capability( 'default' ), 'centrex' );
        add_submenu_page(
            'centrex',
            'Form Builder',
            'Form Builder',
            centrex_app()->settings->get_capability( 'default' ),
            'centrex-form-builder',
            [
                $this,
                'render_form_list_or_builder_page',
            ]
        );
    }

    /**
     * Add custom menu page content for the following
     * menu item: centrex-form-builder
     *
     * @access    public
     * @return    void
     * @since     1.0
     *
     */
    public function render_form_list_or_builder_page() {

        $has_plaid_access = $this->plaid_api->has_access();

        $centrex_options = get_option( 'centrex_options' );
        $post_email = $centrex_options['post_email'];

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $selected_form_id = !empty( $_GET['formId'] ) ? sanitize_text_field( $_GET['formId'] ) : null;

        if ( empty( $post_email ) ) {
            echo ' <b>Please enter an email address on the main settings page to activate the form builder .</b> ';
        } else {
            global $wpdb;
            $table_name = $this->table_name;

            $form_builder_script = "
window.CENTREX_APP = window.CENTREX_APP || {};
window.CENTREX_APP.backendOptions = {
    ajaxUrl: '" . admin_url( 'admin-ajax.php' ) . "',
    isProVersion: true,
    hasPlaidAccess: " . ( $has_plaid_access ? 'true' : 'false' ) . '
};
window . CENTREX_APP . callbackQueue = window . CENTREX_APP . callbackQueue || [];';

            wp_add_inline_script( 'centrex-backend-run-callback-queue', $form_builder_script, 'before' );

            centrex_app()->settings->render_plugin_header();
            if ( empty( $selected_form_id ) ) {
                // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder
                $all_form_builder_rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * from %i', $table_name ) );
                $this->render_forms_list( $all_form_builder_rows );
            } else {
                $form_row_to_edit = $this->get_form_for_id( $selected_form_id );
                $this->render_form_editor( $form_row_to_edit );
            }
        }
    }

    public function render_forms_list( $allFormBuilderRows ) {

        ?>
        <div class="wrap centrex-table-view">
            <button type="submit" id="centrexAddNewFormButton" class="page-title-action centrex-add-new-form-button">Add
                New Form
            </button>
            <table id="centrex_form_list_table" class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Actions</th>
                    <th>ID</th>
                    <th>File Type</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ( $allFormBuilderRows as $row ) {
                    $clone_nonce = wp_create_nonce( 'centrex_plugin_clone_form_row_' . $row->formId );
                    $delete_nonce = wp_create_nonce( 'centrex_plugin_delete_form_row_' . $row->formId );
                    echo '<tr class="centrex-form-row" id="centrexFormRow_' . esc_attr( $row->formId ) . '">';
                    echo '<td class="form-title"><a href="' . esc_url( admin_url( 'admin.php?page=centrex-form-builder&formId=' . esc_attr( $row->formId ) ) ) . '" class="row-title">' . esc_html( $row->formTitle ) . '</a></td>';
                    echo '<td>';
                    echo '  <a href="' . esc_url( admin_url( 'admin.php?page=centrex-form-builder&formId=' . esc_attr( $row->formId ) ) ) . '"><i class="centrex-fas centrex-fa-edit"></i> Edit</a>';
                    echo '  &nbsp; <span class="clone-form" data-form-id="' . esc_attr( $row->formId ) . '" data-form-nonce="' . esc_attr( $clone_nonce ) . '"><i class="centrex-fas centrex-fa-clone"></i> Clone</span>';
                    echo '  &nbsp; <span class="copy-shortcode" data-form-id="' . esc_attr( $row->formId ) . '"><i class="centrex-fas centrex-fa-clipboard"></i> Copy Shortcode</span>';
                    echo '  &nbsp; <span class="delete-form-row" data-form-id="' . esc_attr( $row->formId ) . '" data-form-nonce="' . esc_attr( $delete_nonce ) . '"><i class="centrex-fas centrex-fa-trash"></i> Delete</span>';
                    echo '</td>';
                    echo '<td>' . wp_kses_post( $row->formId ) . '</td>';
                    echo '<td>' . wp_kses_post( $row->formFileTypeId ) . '</td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>

            <div id="centrex_delete_dialog" class="centrex-delete-dialog" title="Confirm Deleting Form">
                <p>
                    <span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
                    This item will be permanently deleted and cannot be recovered. Are you sure?
                </p>
            </div>
        </div>
        <?php

        wp_add_inline_script(
            'centrex-backend-run-callback-queue',
            '
window.CENTREX_APP.callbackQueue.push(function (centrexApp) {
    centrexApp.formsListPage.init({});
});',
            'before'
        );
    }

    public function render_form_editor( $formRowToEdit ) {

        // Remove all newlines and spaces from the JSON string to prevent it from growing in size:
        $validFormDataJSON_escaped = str_replace( [ "\r\n", "\r", "\n", '  ' ], '', $formRowToEdit->formData );
        wp_add_inline_script(
            'centrex-backend-run-callback-queue',
            '
 window.CENTREX_APP.callbackQueue.push(function (centrexApp) {
    centrexApp.formBuilderPage.init({
        currentFormId: ' . esc_attr( $formRowToEdit->formId ) . ",
        formBuilderData: JSON.parse( '" . $validFormDataJSON_escaped . "')
    });
});
        ",
            'before'
        );
        ?>
        <div class="wrap centrex-form-builder-wrapper">
            <form id="formBuilderContainer">
                <div id="centrex-form-settings-toggle">
                    <i class="centrex-fas centrex-fa-chevron-down"></i>
                    <span class="builder-header">Form Information</span>
                </div>
                <div class="centrex-form-settings-container">
                    <div class="centrex-outer-stage-inputs">
                        <label for="formTitle">
                            <b>Form Title</b>
                        </label>
                        <input type="text" id="formTitle"
                               value="<?php echo esc_attr( $formRowToEdit->formTitle ); ?>" required/>
                    </div>

                    <div class="centrex-outer-stage-inputs">
                        <label for="formThankUrl">
                            <b>Thank You URL</b>
                        </label>
                        <input type="url" id="formThankUrl"
                               value="<?php echo esc_url( $formRowToEdit->formThankUrl ); ?>" required/>
                    </div>

                    <div class="centrex-outer-stage-inputs">
                        <label for="formPostUrl">
                            <b>Post URL</b>
                        </label>
                        <input type="text" id="formPostUrl"
                               value="<?php echo esc_url( $formRowToEdit->formPostUrl ); ?>"/>
                        <p class="posturlHelperText">Entering a post URL here will send form submissions using the POST
                            method.</p>
                    </div>

                    <div class="centrex-outer-stage-inputs">
                        <label for="formSubmitText">
                            <b>Submit Button Text</b>
                        </label>
                        <input type="text" id="formSubmitText"
                               value="<?php echo esc_attr( $formRowToEdit->formSubmitText ); ?>" required/>
                    </div>

                    <div class="centrex-outer-stage-inputs">
                        <label for="formFileTypeId">
                            <b>Centrex File Type</b>
                        </label>
                        <select id="formFileTypeId" name="formFileTypeId" required>
                            <?php
                            $fileTypes = [
                                [
                                    'fileTypeId'   => 29,
                                    'fileTypeName' => 'Business Loans',
                                ],
                                [
                                    'fileTypeId'   => 23,
                                    'fileTypeName' => 'Broker / Realtor',
                                ],
                                [
                                    'fileTypeId'   => 58,
                                    'fileTypeName' => 'Investor',
                                ],
                            ];
                            foreach ( $fileTypes as $fileType ) {
                                $selected = $fileType['fileTypeId'] == $formRowToEdit->formFileTypeId ? 'selected' : '';
                                echo ' <option value = "' . esc_attr( $fileType['fileTypeId'] ) . '" ' . $selected . ' > ' . esc_html( $fileType['fileTypeName'] ) . ' </option>';
                            }
                            ?>
                        </select>

                    </div>

                    <?php wp_nonce_field( 'centrex_plugin_update_form_row_' . $formRowToEdit->formId ); ?>

                </div>

                <div id="centrexErrorMessageContainer" class="centrex-form-error-container">
                </div>
                <div id="formBuilder"></div>
            </form>
        </div>
        <div id="centrex_conditional_field_dialog" class="centrex-conditional-dialog"
             title="Configure Conditional Field">
            <div class="form-wrap form-builder" role="document">
                <div class="form-group">
                    <label for="conditional_on_field_selector" class="col-sm-4 col-form-label">
                        Show when this field's value:
                    </label>
                    <div class="input-wrap">
                        <select id="conditional_on_field_selector" class="form-control">
                        </select>
                    </div>
                </div>

                <div class="form-group" id="show_when_value_selector_container">
                    <select id="show_when_value_selector">
                        <option value="IsEqualTo"> is equal to</option>
                        <option value="IsNotEqualTo"> is not equal to</option>
                    </select>
                </div>

                <div class="form-group" id="conditional_value_form_group_container">
                    <label for="conditional_on_field_value" class="col-sm-4 col-form-label"
                           id="conditional_on_field_value_before_label">
                        is equal to:
                    </label>
                    <div id="conditional_value_input_container" class="input-wrap">

                    </div>

                    <p id="conditional_on_field_value_after_label"></p>

                </div>
                <!-- /.modal - content -- >
            </div >
        </div >
        <?php
    }


    /*
     * This function is called on activation of the plugin
     *
     * @access  public
     * @since   1.0
     *
     * @return  void
     */
    public function activation_hook_callback() {
        $this->install_table();
        $this->insert_default_form_row_if_not_exists();
    }

    public function install_table() {
        global $wpdb;
        $installed_db_version = get_option( 'centrex_db_version', '1.0' );
        $expected_db_version = '1.6';

        $table_name = $this->table_name;
        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
        $table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%i'", $table_name ) ) === $table_name;

        if ( $installed_db_version !== $expected_db_version || !$table_exists ) {
            $charset_collate = $wpdb->get_charset_collate();
            $dbSchemaSql = "CREATE TABLE {$table_name} (
                    formId mediumint(9) NOT NULL AUTO_INCREMENT ,
                    formData longtext NOT NULL,
                    formTitle text NOT NULL,
                    formThankUrl text NOT NULL,
                    formSubmitText text NOT NULL,
                    formFileTypeId SMALLINT NOT NULL,
                    formEmailAddress text NOT NULL,
                    formPostUrl text NOT NULL,
                    PRIMARY KEY  (formId)
	                ) $charset_collate;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $dbSchemaSql );

            update_option( 'centrex_db_version', $expected_db_version );
        } else {
            add_option( 'centrex_db_version', $expected_db_version );
        }
    }

    /**
     * Inserts default form builder row into custom table.
     */
    public function insert_default_form_row_if_not_exists() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder
        $form_row_counts = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $this->table_name ) );

        if ( $form_row_counts < 1 ) {
            $this->insert_new_form_row();
        }
    }

    /**
     * Inserts a new form row into the forms table with default data.
     *
     * @return false|int
     */
    public function insert_new_form_row() {
        global $wpdb;

        $defaultFormRowData = [
            'formData'         => '[
                  {
                    "type": "text",
                    "required": true,
                    "label": "First Name",
                    "name": "first_name",
                    "subtype": "text",
                    "className": "field-half form-control"
                  },
                  {
                    "type": "text",
                    "required": true,
                    "label": "Last Name",
                    "name": "last_name",
                    "subtype": "text",
                    "className": "field-half form-control"
                  },
                  {
                    "type": "text",
                    "subtype": "email",
                    "required": true,
                    "label": "Email",
                    "name": "email",
                    "className": "field-full form-control"
                  },
                  {
                    "type": "text",
                    "subtype": "tel",
                    "required": true,
                    "label": "Phone",
                    "name": "phone_number",
                    "className": "field-full form-control"
                  }
                ]',
            'formTitle'        => 'My First Centrex Form',
            'formThankUrl'     => 'https://',
            'formSubmitText'   => 'Submit Form',
            'formFileTypeId'   => 29,
            'formEmailAddress' => '',
            'formPostUrl'      => '',
        ];

        $defaultFormRowData['formData'] = str_replace(
            [
                "\r\n",
                "\r",
                "\n",
                '  ',
            ],
            '',
            $defaultFormRowData['formData']
        );

        $isRowInserted = $wpdb->insert(
            $this->table_name,
            $defaultFormRowData
        );
        if ( !$isRowInserted ) {
            centrex_log_error( 'Was not able to insert new row:' . $wpdb->last_error );
            return false;
        }
        return $wpdb->insert_id;
    }

    /*
     * This function is called on deactivation of the plugin
     *
     * @access  public
     * @since   1.0
     *
     * @return  void
     */
    public function deactivation_hook_callback() {
        // HACK: Only for debugging purposes.
        // Can't be published with this
        // global $wpdb;
        // $table_name = $wpdb->prefix . 'centrex_app_builder';
        // $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    public function ajax_admin_delete_form_row() {
        if ( !is_admin() ) {
            wp_send_json_error( [ 'msg' => 'Invalid' ], 400 );
            return;
        }

        $formId = sanitize_text_field( $_POST['formId'] );

        $sanitized_form_nonce = sanitize_text_field( wp_unslash( $_POST['formNonce'] ) );
        if ( !wp_verify_nonce( $sanitized_form_nonce, 'centrex_plugin_delete_form_row_' . $formId ) ) {
            wp_send_json_error( [ 'msg' => 'Invalid' ], 400 );
            return;
        }

        global $wpdb;
        $tableName = $this->table_name;
        $countOfRowsDeleted = $wpdb->delete(
            $tableName,
            [ 'formId' => $formId ]
        );

        if ( !$countOfRowsDeleted ) {
            wp_send_json_error(
                [
                    'msg' => 'Could not delete form row',
                    500,
                ]
            );
            return;
        }

        wp_send_json_success(
            [
                'msg' => 'Successfully delete form row',
            ]
        );
    }


    public function ajax_admin_clone_form_row() {
        if ( !is_admin() ) {
            wp_send_json_error( [ 'msg' => 'Invalid' ], 400 );
            return;
        }

        $formId = sanitize_text_field( $_POST['formId'] );

        $sanitized_form_nonce = sanitize_text_field( wp_unslash( $_POST['formNonce'] ) );
        if ( !wp_verify_nonce( $sanitized_form_nonce, 'centrex_plugin_clone_form_row_' . $formId ) ) {
            wp_send_json_error( [ 'msg' => 'Invalid' ], 400 );
            return;
        }

        global $wpdb;

        $rowToClone = $this->get_form_for_id( $formId );
        $isRowInserted = $wpdb->insert(
            $this->table_name,
            [
                'formData'         => $rowToClone->formData,
                'formTitle'        => $rowToClone->formTitle . ' Copy',
                'formThankUrl'     => $rowToClone->formThankUrl,
                'formSubmitText'   => $rowToClone->formSubmitText,
                'formFileTypeId'   => $rowToClone->formFileTypeId,
                'formEmailAddress' => $rowToClone->formEmailAddress,
                'formPostUrl'      => $rowToClone->formPostUrl,
            ]
        );

        if ( !$isRowInserted ) {
            wp_send_json_error(
                [
                    'msg' => 'Could not clone form',
                    500,
                ]
            );
            return;
        }

        $insert_id = $wpdb->insert_id;
        wp_send_json_success(
            [
                'msg'         => 'Successfully cloned new form row',
                'formId'      => $insert_id,
                'redirectUrl' => admin_url( 'admin.php?page=centrex-form-builder&formId=' . $insert_id ),
            ]
        );
    }

    public function ajax_admin_add_new_form() {
        if ( !is_admin() ) {
            wp_send_json_error( [ 'msg' => 'Invalid' ], 400 );
            return;
        }

        $newFormId = $this->insert_new_form_row();
        if ( !$newFormId ) {
            wp_send_json_error(
                [
                    'msg' => 'Could not create new form row',
                    500,
                ]
            );
            return;
        }

        wp_send_json_success(
            [
                'msg'         => 'Successfully created new form row',
                'formId'      => $newFormId,
                'redirectUrl' => admin_url( 'admin.php?page=centrex-form-builder&formId=' . $newFormId ),
            ]
        );
    }

    public function ajax_admin_update_form_row() {
        global $wpdb;
        $tableName = $this->table_name;

        if ( !is_admin() ) {
            wp_send_json_error( [ 'msg' => 'Invalid' ], 400 );
            return;
        }

        $formId = sanitize_text_field( $_POST['formId'] );

        $sanitized_form_nonce = sanitize_text_field( wp_unslash( $_POST['formNonce'] ) );
        if ( !wp_verify_nonce( $sanitized_form_nonce, 'centrex_plugin_update_form_row_' . $formId ) ) {
            wp_send_json_error( [ 'msg' => 'Invalid' ], 400 );
            return;
        }

        $countOfRowsUpdated = $wpdb->update(
            $tableName,
            [
                'formData'         => str_replace( [ "\r\n", "\r", "\n", '  ' ], '', wp_kses_post( $_POST['formData'] ) ),
                'formTitle'        => !empty( $_POST['formTitle'] ) ? sanitize_text_field( $_POST['formTitle'] ) : null,
                'formThankUrl'     => !empty( $_POST['formThankUrl'] ) ? sanitize_text_field( $_POST['formThankUrl'] ) : null,
                'formSubmitText'   => !empty( $_POST['formSubmitText'] ) ? sanitize_text_field( $_POST['formSubmitText'] ) : null,
                'formFileTypeId'   => !empty( $_POST['formFileTypeId'] ) ? sanitize_text_field( $_POST['formFileTypeId'] ) : null,
                'formEmailAddress' => !empty( $_POST['formEmailAddress'] ) ? sanitize_text_field( $_POST['formEmailAddress'] ) : null,
                'formPostUrl'      => !empty( $_POST['formPostUrl'] ) ? sanitize_text_field( $_POST['formPostUrl'] ) : null,
            ],
            [ 'formId' => $formId ]
        );
        $updatedRowInDb = $this->get_form_for_id( $formId );

        // DEBUGGING:
        wp_send_json_success(
            [
                'msg'            => 'updated form data',
                'rowsUpdated'    => $countOfRowsUpdated,
                'updatedRowInDb' => $updatedRowInDb,
            ]
        );
    }

    /**
     * Get the form for the given formId.
     *
     * @param $formId
     *
     * @return mixed
     */
    private function get_form_for_id( $formId ) {
        global $wpdb;
        $table_name = $this->table_name;
        return $wpdb->get_row(
            $wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnsupportedIdentifierPlaceholder
                'SELECT * FROM %i WHERE formId = %d',
                [
                    $table_name,
                    $formId,
                ]
            )
        );
    }

    /**
     * Sends an email using `wp_mail` to the `post_email` defined in the `centrex_options`.
     *
     * @param array $formData
     * @param bool  $isErrorEmail
     */
    private function sendEmailFromFormData( array $formData, $isErrorEmail = false ) {
        $centrex_options = get_option( 'centrex_options' );
        $centrex_notification_email = $centrex_options['post_email'];
        $centrex_email_logo = $centrex_options['logo_url'];
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $subject = $isErrorEmail ? 'Centrex Submission due to error' : 'New Centrex Smart App Builder Submission';
        $message = 'Admin,<br><br>' .
        $isErrorEmail ? 'We were not able to create a contact successfully. Please verify your Centrex Plugin Settings. <br/><br/>' : '' .
            'You have a new submission from your Centrex Smart App Builder. Here are the details:<br/><br/>';
        foreach ( $formData as $form_control ) {
            $label = $form_control['label'];
            $message .= $label . ': ' . $form_control['value'] . '<br/>';
        }

        if ( !empty( $centrex_email_logo ) ) {
            $message .= '<br/><br/>'
                . "<img src='" . $centrex_email_logo . "' alt='centrex email logo'>";
        }

        wp_mail( $centrex_notification_email, $subject, $message, $headers );
        wp_send_json_success( [ 'message' => 'Successfully sent form submission to email' ] );
    }

    private function incoming_files() {
        // All files need to be processed as we support multiple file uploads at a time:
        $files = $_FILES;
        centrex_log_debug( 'incoming_files:' . json_encode( $files ) );

        $all_uploaded_files = [];

        foreach ( $files as $input => $infoArr ) {
            $filesByInput = [];
            foreach ( $infoArr as $key => $valueArr ) {
                if ( is_array( $valueArr ) ) { // file input "multiple"
                    foreach ( $valueArr as $i => $value ) {
                        $filesByInput[ $i ][ $key ] = $value;
                    }
                } else { // -> string, normal file input
                    $filesByInput[] = $infoArr;
                    break;
                }
            }
            $all_uploaded_files = array_merge( $all_uploaded_files, $filesByInput );
        }
        $valid_uploaded_fields = [];
        foreach ( $all_uploaded_files as $file ) { // let's filter empty & errors
            if ( !$file['error'] ) {
                $valid_uploaded_fields[] = $file;
            }
        }
        return $valid_uploaded_fields;
    }
}
