<?php

/**
 * Class AutomationAppExport
 */
class AutomationAppExport {

	const ORDER_API_PATH = "/api/v1/woocommerce/order";

    const CUSTOMER_API_PATH = "/api/v1/woocommerce/customer";

	const WEB_HOOK_NEW_ORDER = 'AutomationAppNewOrder';

    const WEB_HOOK_NEW_CUSTOMER = 'AutomationAppNewCustomer';

	protected $key = '';

	protected $secret = '';

	/**
	 * Set the request headers.
	 *
	 * @param $order
	 *
	 * @return array
	 */
	protected function getHeaders( $order ) {
		return [
			'Authorization: ' . $this->key,
			'Content-Type: application/json',
			'hash' => $this->getSecretHash( $order ),
		];
	}

	/**
	 * Generate the secret hash.
	 *
	 * @param $order
	 *
	 * @return string
	 */
	protected function getSecretHash( $order ) {
		$orderJson   = serialize( $order );
		$hashedOrder = hash( 'sha256', $orderJson . $this->secret );

		return $hashedOrder;
	}

	/**
	 * Send data to HelloAutomationFlow.
	 *
	 * @param $order
	 * @param $apiKey
	 * @param $apiSecret
	 * @param string $url
	 *
	 * @return bool|string
	 */
	public function sendData( $order, $apiKey, $apiSecret, $url = 'https://automation.app' ) {
		# Set keys.
		$this->key    = $apiKey;
		$this->secret = $apiSecret;
		# Set path.
		$url = $url . $this::ORDER_API_PATH;
		# Create a connection.
		$ch = curl_init( $url );
		# Setting our options.
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $order ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->getHeaders( $order ) );
		# Get the response.
		$response = curl_exec( $ch );
		curl_close( $ch );

		return $response;
	}

	public function createWebHook() {
		// Creating a new order created webhook.
		$webhook = new WC_Webhook();
		$webhook->set_user_id( get_current_user_id() );
		$webhook->set_name( 'Automation App order created export' );
		$webhook->set_topic( 'order.created' );
		$webhook->set_secret( get_option( 'automation_app_secret_key', '' ) );
		$url = get_option( 'automation_app_api_domain', '' );
		$webhook->set_delivery_url( $url . self::ORDER_API_PATH );
		$webhook->set_status( 'active' );
		$webhook->save();
		add_option( 'automation_app_order_created_wh', $webhook->get_id() );
        // Creating a new order updated webhook.
        $webhook = new WC_Webhook();
        $webhook->set_user_id( get_current_user_id() );
        $webhook->set_name( 'Automation App order updated export' );
        $webhook->set_topic( 'order.updated' );
        $webhook->set_secret( get_option( 'automation_app_secret_key', '' ) );
        $url = get_option( 'automation_app_api_domain', '' );
        $webhook->set_delivery_url( $url . self::ORDER_API_PATH );
        $webhook->set_status( 'active' );
        $webhook->save();
        add_option( 'automation_app_order_updated_wh', $webhook->get_id() );

        // Creating a new customer created webhook.
        $webhook = new WC_Webhook();
        $webhook->set_user_id( get_current_user_id() );
        $webhook->set_name( 'Automation App customer created export' );
        $webhook->set_topic( 'customer.created' );
        $webhook->set_secret( get_option( 'automation_app_secret_key', '' ) );
        $url = get_option( 'automation_app_api_domain', '' );
        $webhook->set_delivery_url( $url . self::CUSTOMER_API_PATH );
        $webhook->set_status( 'active' );
        $webhook->save();
        add_option( 'automation_app_customer_created_wh', $webhook->get_id() );
        // Creating a new customer updated webhook.
        $webhook = new WC_Webhook();
        $webhook->set_user_id( get_current_user_id() );
        $webhook->set_name( 'Automation App customer updated export' );
        $webhook->set_topic( 'customer.updated' );
        $webhook->set_secret( get_option( 'automation_app_secret_key', '' ) );
        $url = get_option( 'automation_app_api_domain', '' );
        $webhook->set_delivery_url( $url . self::CUSTOMER_API_PATH );
        $webhook->set_status( 'active' );
        $webhook->save();
        add_option( 'automation_app_customer_updated_wh', $webhook->get_id() );
	}

	/**
	 * Delete webhook.
	 */
	public function deleteWebHook() {
		try {
			$id = get_option( 'automation_app_order_created_wh', '' );
			if ( ! $id ) {
				error_log( "No webhook id found" );
				return;
			}
			$webhook = wc_get_webhook( $id );
			if ( $webhook ) {
				$webhook->delete( true );
			}
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() );
		}
		delete_option( 'automation_app_order_created_wh' );
		try {
			$id = get_option( 'automation_app_order_updated_wh', '' );
			if ( ! $id ) {
				error_log( "No webhook id found" );
				return;
			}
			$webhook = wc_get_webhook( $id );
			if ( $webhook ) {
				$webhook->delete( true );
			}
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() );
		}
		delete_option( 'automation_app_order_updated_wh' );

        try {
            $id = get_option( 'automation_app_customer_created_wh', '' );
            if ( ! $id ) {
                error_log( "No webhook id found" );
                return;
            }
            $webhook = wc_get_webhook( $id );
            if ( $webhook ) {
                $webhook->delete( true );
            }
        } catch ( Exception $exception ) {
            error_log( $exception->getMessage() );
        }
        delete_option( 'automation_app_customer_created_wh' );
		try {
			$id = get_option( 'automation_app_customer_updated_wh', '' );
			if ( ! $id ) {
				error_log( "No webhook id found" );
				return;
			}
			$webhook = wc_get_webhook( $id );
			if ( $webhook ) {
				$webhook->delete( true );
			}
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() );
		}
		delete_option( 'automation_app_customer_updated_wh' );
	}

	/**
	 * Update webhook.
	 *
	 * @throws Exception
	 */
	public function updateWebHook() {
		$webhook = wc_get_webhook( self::WEB_HOOK_NEW_ORDER );
		$webhook->set_status( 'disabled' );
		$webhook->save();
	}

}
