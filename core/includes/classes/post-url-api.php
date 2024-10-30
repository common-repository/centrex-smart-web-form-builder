<?php

class Centrex_PostUrlApi {

	/**
	 * @param $post_url
	 * @param $form_data
	 * @param $contact_id
	 * @return bool - true if successful, false if not
	 */
	public function createOrUpdateContact( $post_url, $form_data, $contact_id ): bool {
		$is_creating_contact = empty( $contact_id );

		$request_body = $this->toFormDataMap( $form_data );
		if ( ! $is_creating_contact ) {
			$request_body['updaterecord'] = $contact_id;
		}

		// The boundary is generated so that a unique string is used to separate the different parts of the HTTP request:
		$boundary = wp_generate_password( 24 );
		$request  = [
			'method'  => 'POST',
			'timeout' => 10,
			'headers' => [
				'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
			],
			'body'    => $this->toFormDataString( $request_body, $boundary ),
		];

		$response = wp_remote_post( $post_url, $request );
		centrex_log_debug(
			'Attempting to ' . ( $is_creating_contact ? 'Create' : 'Update' ) . ' contact:' . json_encode(
				[
					'received'     => $form_data,
					'requestToApi' => $request,
					'postUrl'      => $post_url,
					'response'     => $response,
				]
			)
		);
		$log_failure_prefix = $is_creating_contact ? 'Unable to create a contact through POST URL:' : "Unable to update contactId({$contact_id}) through POST URL:";
		$response_body      = $this->validateAndDecodeResponse( $response, $log_failure_prefix );
		if ( $response_body === false ) {
			return false;
		}

		centrex_log_info( 'Successfully ' . ( $is_creating_contact ? 'created' : 'updated' ) . " contactId({$response_body})" );
		centrex_log_debug(
			( $is_creating_contact ? 'Created' : 'Updated' ) . ' contact successfully through POST URL:' . json_encode(
				[
					'received'            => $form_data,
					'requestToApi'        => $request,
					'responseFromApi'     => $response,
					'responseFromApiBody' => $response_body,
					'postUrl'             => $post_url,
				]
			)
		);

		// Response looks like this:
		// Success:123456
		$response_tuple = explode( ':', $response_body );
		wp_send_json_success(
			[
				'id' => $response_tuple[1],
			]
		);

		return true;
	}

	private function toFormDataMap( $form_data ): array {
		$request_body = [];
		foreach ( $form_data as $form_control_name => $formControl ) {
			if ( isset( $formControl['customFieldId'] ) ) {
				$form_control_name = $formControl['customFieldId'];
			}

			$request_body[ $form_control_name ] = $formControl['value'];
		}

		return $request_body;
	}

	private function toFormDataString( array $request_body, $boundary ): string {
		$body = '';
		foreach ( $request_body as $key => $value ) {
			$body .= "--$boundary\r\n";
			$body .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
			$body .= "$value\r\n";
		}
		$body .= "--$boundary--";

		return $body;
	}

	/**
	 * @param $response WP_Error|array
	 * @param $logFailurePrefix
	 * @return false|string
	 */
	private function validateAndDecodeResponse( $response, $logFailurePrefix ) {
		if ( is_wp_error( $response ) ) {
			centrex_log_error( $logFailurePrefix . $response->get_error_message() );
			return false;
		}

		$response_body   = wp_remote_retrieve_body( $response );
		$response_status = wp_remote_retrieve_response_code( $response );
		if ( $response_status !== 200 || empty( $response_body ) || ! str_contains( $response_body, 'Success' ) ) {
			centrex_log_error(
				$logFailurePrefix . json_encode(
					[
						'response'       => $response,
						'responseBody'   => $response_body,
						'responseStatus' => $response_status,
					]
				)
			);
			return false;
		}

		return $response_body;
	}
}
