<?php
use Automattic\Jetpack\Constants;

/**
 * Class HelloAutomationFlowWC
 */
class AutomationAppWC {

	const FILE_NAME = '/settings.json';

	/**
	 * Load settings from the generated settings file.
	 *
	 * @return array|mixed|object
	 */
	protected function loadSettingsFile() {
		$filePath = __DIR__ . self::FILE_NAME;
		$settings = file_get_contents( $filePath );
		$json     = json_decode( $settings );

		return $json;
	}

	/**
	 * Remove the generated settings file.
	 */
	protected function removeSettingsFile() {
		unlink( __DIR__ . self::FILE_NAME );
	}

	/**
	 * Add settings when plugin is enabled.
	 */
	public function activate() {
		// global $wp_rewrite;
		// $this->flush_rewrite_rules();
		$settings  = $this->loadSettingsFile();
		$apiKey    = ! empty( $settings->api ) ? $settings->api : '';
		$secretKey = ! empty( $settings->secret ) ? $settings->secret : '';
		$domain    = ! empty( $settings->domain ) ? $settings->domain : '';

		add_settings_section(
			'automation_app_options', // section slug
			'Automation app options', // section title
			array( $this, 'automation_app_options_section' ), // section display callback
			'automation_app_options_group' // page slug
		);

		add_option( 'automation_app_api_key', $apiKey );
		$settings = [
			'type'         => 'string',
			'description'  => 'Hello Automation Flow api key',
			'show_in_rest' => false,
			'default'      => '',
		];
		register_setting( 'automation_app_options_group', 'automation_app_api_key', $settings );

		add_option( 'automation_app_secret_key', $secretKey );
		$settings = [
			'type'         => 'string',
			'description'  => 'Hello Automation Flow secret key',
			'show_in_rest' => false,
			'default'      => '',
		];
		register_setting( 'automation_app_options_group', 'automation_app_secret_key', $settings );

		add_option( 'automation_app_api_domain', $domain );
		$settings = [
			'type'         => 'string',
			'description'  => 'Hello Automation Flow api domain',
			'show_in_rest' => false,
			'default'      => '',
		];
		register_setting( 'automation_app_options_group', 'automation_app_api_domain', $settings );

        $this->removeSettingsFile();
        $this->registerInstall($domain, $apiKey);

        $orders = $this->findOrders();
        $this->exportOrders($orders);
    }

	/**
	 * Remove settings when plugin is disabled.
	 */
	public function deactivate() {
		delete_option( 'automation_app_api_key' );
		unregister_setting( 'automation_app_options_group', 'automation_app_api_key' );

		delete_option( 'automation_app_secret_key' );
		unregister_setting( 'automation_app_options_group', 'automation_app_secret_key' );

		delete_option( 'automation_app_api_domain' );
		unregister_setting( 'automation_app_options_group', 'automation_app_api_domain' );
	}

	/**
	 * Get settings for template.
	 */
	public function showSettingsForm() {
		include_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'templates/show-form.php' );
	}
	
	public function showAddonPlugins() {
		echo '<div id="automation_multiple_addons">';
		$this->showImportPage();
		$this->showTrackingPage();
		echo '</div>';
	}
	
	public function showImportPage() {
		include_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'templates/show-import-page.php' );
	}
	
	public function showTrackingPage() {
		include_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . 'templates/show-tracking-page.php' );
	}
	/**
	 * @return mixed
	 */
	public function findOrders() {
		$begin  = date( 'Y-m-d', strtotime( '-3 months' ) );
		$today  = date( 'Y-m-d' );
		$orders = wc_get_orders( array(
			'date_created' => $begin . '...' . $today,
		) );

		return $orders;
	}

	/**
	 * @param $orders
	 */
	public function exportOrders( $orders ) {
		try {
			$id = get_option( 'automation_app_order_created_wh', '' );
			if ( ! $id ) {
				error_log( "No webhook id found" );

				return;
			}
			$webhook = wc_get_webhook( $id );
			if ( $webhook ) {
				foreach ( $orders as $order ) {
					$webhook->deliver( $order->id );
				}
			}
		} catch ( Exception $exception ) {
			error_log( $exception->getMessage() );
		}
	}

    /**
     * @param $domain
     * @param $apiKey
     */
    public function registerInstall($domain, $apiKey) {
        $url = $domain . "/api/v1/woocommerce/install";
        wp_remote_post($url, [
            'headers' => [
                'x-api-key' => $apiKey,
            ],
        ]);
    }
}