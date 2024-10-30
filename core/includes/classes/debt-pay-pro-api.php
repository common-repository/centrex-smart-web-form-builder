<?php

class Centrex_DebtPayProApi {


	// These fields will directly to a property in the request as opposed to a key-value pair in the `customs` property.
	const DIRECTLY_MAPPED_FIELD_NAMES = [
		'first_name',
		'last_name',
		'phone_number',
		'email',
		'date_of_birth',
		'address',
		'title',
		'business_name',
		'campaign_id',
		'attorney_id',
	];
	const MAPPED_FIELD_NAME_DEFAULTS  = [
		'phone_number'  => '555-555-5555',
		'date_of_birth' => '1800-01-01',
		'campaign_id'   => '0',
		'attorney_id'   => '0',
		'address'       => [
			'address1' => '12345 Default Street',
			'address2' => '',
			'address3' => '',
			'city'     => 'Null',
			'state'    => 'NY',
			'zip'      => '00000',
		],
	];

	const CENTREX_API_KEY_EXPIRY_OPTION = 'centrex_api_key_expiry';
	const CENTREX_API_KEY_OPTION        = 'centrex_api_key';

	// Business Loan is the default "file type":
	const DEFAULT_FILE_TYPE_ID = 29;

	/**
	 * @var string
	 */
	private $api_base_url;

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var string
	 */
	private $client_secret;
	/**
	 * @var string
	 */
	private $client_id;
	/**
	 * @var number
	 */
	private $api_key_expires_at;

	public function __construct() {
		$this->api_base_url = 'https://api.centrexsoftware.com/v1/';

		$centrex_options     = get_option( 'centrex_options' );
		$this->client_secret = $centrex_options['client_secret'];
		$this->client_id     = $centrex_options['client_id'];

		$this->api_key            = get_option( self::CENTREX_API_KEY_OPTION );
		$this->api_key_expires_at = get_option( self::CENTREX_API_KEY_EXPIRY_OPTION );
	}

	/**
	 * Returns whether the current Api key is valid.
	 * If it is currently not valid it will try to get a new one.
	 * If that fails then it will return false.
	 *
	 * @return boolean
	 */
	public function generateApiKeyIfInvalid() {
		$api_key_available = ! empty( $this->api_key ) && ! empty( $this->api_key_expires_at );

		if ( $api_key_available && time() <= $this->api_key_expires_at ) {
			return true;
		}

		// API Key may not be available or may have expired so attempt to renew it:
		$stored_client_secret = $this->client_secret;
		$stored_client_id     = $this->client_id;
		if ( empty( $stored_client_secret ) || empty( $stored_client_id ) ) {
			// this means there is no way to even attempt to get an api_key so don't even try
			return false;
		}

		centrex_log_info( 'API key has expired. Attempting to create a new one. Current API Key expiry time:' . $this->api_key_expires_at );
		return $this->generateApiKey( $stored_client_secret, $stored_client_id );
	}


	/**
	 * @param $form_data
	 * @param $contact_id
	 * @param int        $file_type_id
	 * @return bool
	 */
	public function createOrUpdateContact( $form_data, $contact_id, $file_type_id = self::DEFAULT_FILE_TYPE_ID ) {
		if ( ! $this->generateApiKeyIfInvalid() ) {
			wp_send_json_error(
				[
					'message' => 'Could not get valid API key due to unknown error',
				]
			);
			return false;
		}
		$is_creating_contact = empty( $contact_id );

		$request_body = [
			'file_type' => $file_type_id,
		];

		// Go through all the directly mapped fields and set them based on the field name from the FE:
		foreach ( self::DIRECTLY_MAPPED_FIELD_NAMES as $field_name ) {
			$request_has_field = isset( $form_data[ $field_name ] ) && ! empty( $form_data[ $field_name ]['value'] );
			if ( $request_has_field ) {
				$request_body[ $field_name ] = $form_data[ $field_name ]['value'];
			} elseif ( $is_creating_contact && array_key_exists( $field_name, self::MAPPED_FIELD_NAME_DEFAULTS ) ) {
				// if any of the required fields are missing then fill them out with blanks:
				$request_body[ $field_name ] = self::MAPPED_FIELD_NAME_DEFAULTS[ $field_name ];
			}
		}

		$request_body['customs'] = $this->parseCustomFields( $form_data );
		$request                 = [
			'headers'     => [
				'Content-Type' => 'application/json',
				'Api-Key'      => $this->api_key,
			],
			'timeout'     => 10,
			'body'        => wp_json_encode( $request_body ),
			'data_format' => 'body',
		];

		if ( $is_creating_contact ) {
			$request['method'] = 'POST';
			$api_url           = $this->api_base_url . 'contacts';

		} else {
			$request['method'] = 'PUT';
			$api_url           = $this->api_base_url . 'contacts/' . $contact_id;
		}

		$response              = wp_remote_post( $api_url, $request );
		$log_failure_prefix    = $is_creating_contact ? 'Unable to create a contact:' : "Unable to update contactId({$contact_id}):";
		$decoded_response_body = $this->validateAndDecodeResponse( $response, $log_failure_prefix );

		if ( $decoded_response_body === false ) {
			return false;
		}

		centrex_log_info( 'Successfully ' . ( $is_creating_contact ? 'created' : 'updated' ) . " contactId({$contact_id})" );
		centrex_log_debug(
			( $is_creating_contact ? 'Created' : 'Updated' ) . ' contact successfully:' . json_encode(
				[
					'received'            => $form_data,
					'requestToApi'        => $request,
					'responseFromApi'     => $response,
					'responseFromApiBody' => $decoded_response_body,
				],
				JSON_PRETTY_PRINT
			)
		);

		wp_send_json_success( $decoded_response_body['response'] );

		return true;
	}

	public function uploadDocumentContact( $contactId, $file ) {
		if ( ! $this->generateApiKeyIfInvalid() ) {
			wp_send_json_error(
				[
					'message' => 'Could not get valid API key due to unknown error',
				]
			);
			return false;
		}

        centrex_log_error('Uploading files is not allowed in free version');
        return false;
    }


	/**
	 * @param $response
	 * @param $logFailurePrefix
	 * @return false|mixed
	 */
	private function validateAndDecodeResponse( $response, $logFailurePrefix ) {
		if ( is_wp_error( $response ) ) {
			centrex_log_response_error( $logFailurePrefix, $response );
			return false;
		}

		$decoded_response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		$response_status       = isset( $decoded_response_body['status'] ) ? $decoded_response_body['status']['code'] : 500;
		if ( $response_status === 403 ) {
			// API Key for some reason became invalidated so clear it out so any new API calls will work:
			centrex_log_response_error( 'API call failed due to invalid API key:', $response );
			$this->api_key = $this->api_key_expires_at = null;
			delete_option( self::CENTREX_API_KEY_OPTION );
			delete_option( self::CENTREX_API_KEY_EXPIRY_OPTION );
			return false;
		}

		if ( $response_status !== 200 ) {
			centrex_log_response_error( $logFailurePrefix, $response );
			return false;
		}

		return $decoded_response_body;
	}

	/**
	 * @param array $form_data
	 * @return array[]
	 */
	private function parseCustomFields( array $form_data ) {
		$userCustomizedFields = array_filter(
			$form_data,
			static function ( $key ) {
				return ! in_array( $key, self::DIRECTLY_MAPPED_FIELD_NAMES, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		$fieldsAsArray = array_values( $userCustomizedFields );

		return array_map(
			static function ( $formControl ) {
				$customField = [
					'label' => $formControl['label'],
					'value' => $formControl['value'],
				];

				if ( isset( $formControl['customFieldId'] ) ) {
					$customField['field_id'] = $formControl['customFieldId'];
				}
				return $customField;
			},
			$fieldsAsArray
		);
	}

	/**
	 * Generates an API key from the DebtPayPro API.
	 * Will store the generated API key inside this class as well as in the WP options.
	 *
	 * @param $client_secret
	 * @param $client_id
	 * @return bool - Returns true if we were able to generate one and false if we weren't
	 */
	private function generateApiKey( $client_secret, $client_id ) {
		$auth_request = [
			'headers'     => [
				'Content-Type' => 'application/json',
			],
			'body'        => wp_json_encode(
				[
					'client_secret' => $client_secret,
					'client_id'     => $client_id,
				],
				JSON_PRETTY_PRINT
			),
			'data_format' => 'body',
		];

		$response = wp_remote_post( $this->api_base_url . 'auth/token', $auth_request );
		if ( is_wp_error( $response ) ) {
			centrex_log_response_error( 'Could not make auth call:', $response );

			wp_send_json_error(
				[
					'msg' => 'Could not make auth call',
				]
			);

			return false;
		}

		$auth_response = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $auth_response['response'] ) ) {
			centrex_log_response_error( 'Made successful auth call but received empty response:', $response );

			wp_send_json_error(
				[
					'msg' => 'Made successful auth call but received empty response',
				]
			);
			return false;
		}

		$auth_response    = $auth_response['response'];
		$api_key_response = $auth_response['api_key'];
		$expires_in       = $auth_response['expires_in'];

		if ( empty( $api_key_response ) || empty( $expires_in ) ) {
			centrex_log_response_error( 'API Key call was successful but received invalid response:', $response );

			wp_send_json_error(
				[
					'msg' => 'Made successful response call but received empty response',
				]
			);
			return false;
		}

		$this->api_key            = $api_key_response;
		$this->api_key_expires_at = time() + ( $expires_in - ( 60 * 60 ) );
		update_option( self::CENTREX_API_KEY_OPTION, $this->api_key );
		update_option( self::CENTREX_API_KEY_EXPIRY_OPTION, $this->api_key_expires_at );
		centrex_log_info( 'Received new API Key:' . json_encode( $api_key_response ) );
		return true;
	}
}
