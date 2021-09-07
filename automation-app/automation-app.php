<?php
/**
 * Plugin Name: Automation.app WooCommerce Extension
 * Plugin URI: http://automation.app
 * Description: This extension 2-way sync's WooCommerce order data between WooCommerce and the Automation.app CRM & Automation platform. Go to https://automation.app/ to set up your account if you're don't already have done so. Follow this guide for more information (https://automation.app/blog/woocommerce-crm-plugin-for-automationapp)
 * Version: 1.1.0
 * Author: automationApp
 * Author URI: http://automation.app
 * Developer: Jesper Bisgaard
 * Developer URI: http://automation.app
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 *
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 * WC requires at least: 2.2
 * WC tested up to: 2.3
 *
 * Copyright: © 2009-2015 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    require_once(__DIR__.'/AutomationAppExport.php');
    require_once(__DIR__.'/AutomationAppWC.php');

    if (class_exists('AutomationAppExport') && class_exists('AutomationAppWC')) {
        $exportClass = new AutomationAppExport();
        $GLOBALS['AutomationAppExport'] = $exportClass;
        $wcClass = new AutomationAppExport();
        $GLOBALS['AutomationAppWC'] = $wcClass;

        function automationApp_activatePlugin()
        {
            $wcClass = new AutomationAppWC();
            $wcClass->activate();

            $exportClass = new AutomationAppExport();
            $exportClass->createWebHook();

	        $orders = $wcClass->findOrders();
	        $wcClass->exportOrders( $orders );
        }

        register_activation_hook(__file__, 'automationApp_activatePlugin');

        function automationApp_deactivatePlugin()
        {
            $wcClass = new AutomationAppWC();
            $wcClass->deactivate();

            $exportClass = new AutomationAppExport();
            $exportClass->deleteWebHook();
        }
        register_deactivation_hook(__file__, 'automationApp_deactivatePlugin');

        function automationApp_afterWebhookDelivery($http_args, $response, $duration, $arg, $webhook_id)
        {
            // do whatever you want to here
            $orderData = json_decode($http_args['body']);
            if (!empty($orderData->id)) {
                $order = wc_get_order($orderData->id);
                if (!is_bool($order)) {
                    $order->add_order_note('Order sent to automationApp', 0, false);
                }
            }
        }
        add_action('woocommerce_webhook_delivery', 'automationApp_afterWebhookDelivery', 1, 5);

        function automationApp_filter_http_args($http_args, $arg, $id)
        {
            $http_args['headers']['x-api-key'] = get_option('automation_app_api_key', '');

            return $http_args;
        }
        add_filter('woocommerce_webhook_http_args', 'automationApp_filter_http_args', 10, 3);

        # Add options page.
        function automationApp_registerOptionsPage()
        {
		   add_menu_page(__('Automation App settings','woocommerce'),
						 __('Automation.app','woocommerce'),
						 'manage_options',
						 'automation-app',
        				 'automationApp_option_page',
        				 'dashicons-dashboard'
  		  );
		
		 add_submenu_page('automation-app',
		  				__('Import Orders','woocommerce'),
						__('Import Orders','woocommerce'),
						'manage_options',
						'automation-export',
						apply_filters('automation_import_page_callback', 'automationApp_import_page'));
		  
		 add_submenu_page('automation-app',
		  				__('Tracking','woocommerce'),
						__('Tracking','woocommerce'),
						'manage_options',
						'automation-tracking',
						apply_filters('automation_tracking_page_callback', 'automationApp_tracking_page'));
	    }
		add_action('admin_menu', 'automationApp_registerOptionsPage',49);

        function automationApp_option_page()
        {
            $wcClass = new AutomationAppWC();
            $wcClass->showSettingsForm();
			$wcClass->showAddonPlugins();
	    }
		
		function automationApp_import_page()
        {
            $wcClass = new AutomationAppWC();
            $wcClass->showImportPage();
        }
		
		function automationApp_tracking_page()
        {
            $wcClass = new AutomationAppWC();
            $wcClass->showTrackingPage();
        }        

        add_filter('whitelist_options', function ($whitelist_options) {
            $whitelist_options = [
                'automation_app_options_group' => [
                    'automation_app_api_key',
                    'automation_app_secret_key',
                    'automation_app_api_domain',
                ]
            ];

            return $whitelist_options;
        });

        function overrule_webhook_disable_limit( $number ) {
            return 50;
        }
        add_filter( 'woocommerce_max_webhook_delivery_failures', 'overrule_webhook_disable_limit' );
		
		/*Add Styles*/
		function automation_app_styles()
		{
			$check=(!empty($_GET['page']))?$_GET['page']:'';
			if(strpos($check,'automation-')!==false)
			wp_enqueue_style('automation.style',plugin_dir_url( __FILE__ ).'templates/style.css');
			
		}
		add_action('admin_enqueue_scripts','automation_app_styles',10);
    }
}