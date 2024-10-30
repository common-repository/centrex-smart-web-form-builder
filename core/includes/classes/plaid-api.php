<?php

class Centrex_APIEnvironment {

    const DEVELOPMENT = 'development';
    const STAGING     = 'staging';
    const PRODUCTION  = 'production';
}

class Centrex_PlaidAPIResponse {

    /**
     * @var bool
     */
    public bool $success;

    /**
     * @var string
     */
    public string $failureReason;

    /**
     * @var string
     */
    public string $responseValue;

    public function __construct( bool $success, string $failureReason = '', string $responseValue = '' ) {
        $this->success = $success;
        if ( ! $success && empty( $failureReason ) ) {
            $failureReason = Centrex_PlaidApi::$DEFAULT_PLAID_API_ERROR_MESSAGE;
        }
        $this->failureReason = $failureReason;
        $this->responseValue = $responseValue;
    }
}

class Centrex_PlaidApi {

    static string $DEFAULT_PLAID_API_ERROR_MESSAGE = 'An error occurred while trying to create a plaid token.';

    static string $DEVELOPMENT_PLAID_API_BASE = 'https://app-gateway-centrex-dev.azurewebsites.net/transactions/api/plaid-link/';
    static string $STAGING_PLAID_API_BASE     = 'https://api-staging.centrexsoftware.com/transactions/api/plaid-link/';
    static string $PRODUCTION_PLAID_API_BASE  = 'https://api-app.centrexsoftware.com/transactions/api/plaid-link/';


    /**
     * @var string
     */
    private string $client_id;

    public function __construct() {
        $centrex_options = get_option( 'centrex_options' );
        if ( isset( $centrex_options['centrex_account_id'] ) ) {
            $this->client_id = $centrex_options['centrex_account_id'];
        }
    }

    /**
     * Returns a plaid token for the given contact id.
     *
     * @param $contact_id
     * @param bool       $asset_report
     * @param string     $api_environment
     *
     * @return Centrex_PlaidAPIResponse - Returns a PlaidAPIResponse object with the success property set to true and the validationReason property set to the plaid token if the token was created successfully.
     */
    public function get_link_token( $contact_id, bool $asset_report = false, string $api_environment = Centrex_APIEnvironment::STAGING ): Centrex_PlaidAPIResponse {
        $this->ensure_client_id_is_set();

        return new Centrex_PlaidAPIResponse( false, 'Plaid is not available in the Free Version.', '' );
    }

    /**
     * @param $public_token
     * @param $contact_id
     * @return bool - Returns true if the token was processed successfully, false otherwise.
     */
    public function process_token( $public_token, $contact_id, $api_environment = Centrex_APIEnvironment::STAGING ): bool {
        $this->ensure_client_id_is_set();

        $url = $this->get_api_base_url( $api_environment ) . 'process-token';

        $request = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode(
                [
					'client_id'    => $this->client_id,
					'public_token' => $public_token,
					'contact_id'   => $contact_id,
                ]
            ),
            'url'     => $url,
        ];

        $response = wp_remote_post( $url, $request );
        if ( is_wp_error( $response ) ) {
            centrex_log_response_error( 'Could not process plaid token for $contact_id(' . $contact_id . ')', $response, $request );
            return false;
        }

        centrex_log_response_info( 'Plaid token processed for $contact_id(' . $contact_id . ')', $response, $request );

        if ( $response['response']['code'] !== 200 ) {
            centrex_log_response_error( 'Could not process plaid token for $contact_id(' . $contact_id . ')', $response, $request );
            return false;
        }

        return true;
    }


    /**
     * Checks if the client has Plaid API access.
     *
     * @return bool
     */
    public function has_access(): bool {
        $this->ensure_client_id_is_set();

        // Always false in free mode:
        return false;
    }

    public function get_api_base_url( string $api_environment ): string {
        switch ( $api_environment ) {
            case Centrex_APIEnvironment::DEVELOPMENT:
                return self::$DEVELOPMENT_PLAID_API_BASE;
            case Centrex_APIEnvironment::PRODUCTION:
                return self::$PRODUCTION_PLAID_API_BASE;
            case Centrex_APIEnvironment::STAGING:
            default:
                return self::$STAGING_PLAID_API_BASE;
        }
    }

    /**
     * @throws Exception
     */
    private function ensure_client_id_is_set(): void {
        if ( empty( $this->client_id ) ) {
            // throw new Exception('Centrex account ID must be set before using Plaid.');
        }
    }
}
