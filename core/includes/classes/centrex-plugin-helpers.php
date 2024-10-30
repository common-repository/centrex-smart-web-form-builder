<?php

// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class CentrexPluginHelpers
{

}

function centrex_log_response_error( $message, $response, $request = null ) {
    $errorLogJson = [
        'response' => $response,
    ];

    if ( $request ) {
        $errorLogJson['request'] = $request;
    }

    error_log(
        'ERROR(' . time() . '):' . $message . PHP_EOL .
        'Response:' . wp_json_encode( $errorLogJson, JSON_PRETTY_PRINT ) . PHP_EOL .
        PHP_EOL,
        3,
        CENTREX_PLUGIN_ERROR_LOG
    );
}

function centrex_log_response_info( $message, $response, $request = null ) {
    $infoLogJson = [
        'response' => $response,
    ];

    if ( $request ) {
        $infoLogJson['request'] = $request;
    }

    centrex_log_info(
        'INFO(' . time() . '):' . $message . PHP_EOL .
        'Response:' . wp_json_encode( $infoLogJson, JSON_PRETTY_PRINT ) . PHP_EOL .
        PHP_EOL
    );
}

function centrex_log_error( $message ) {
    error_log( 'ERROR(' . time() . '):' . $message . PHP_EOL . PHP_EOL, 3, CENTREX_PLUGIN_ERROR_LOG );
}


function centrex_log_info( $message ) {
    error_log( 'INFO(' . time() . '):' . $message . PHP_EOL . PHP_EOL, 3, CENTREX_PLUGIN_INFO_LOG );
}

function centrex_log_debug( $message ) {
    if ( !empty( CENTREX_PLUGIN_DEBUG_LOG ) ) {
        error_log( 'DEBUG(' . time() . '):' . $message . PHP_EOL . PHP_EOL, 3, CENTREX_PLUGIN_DEBUG_LOG );
    }
}
